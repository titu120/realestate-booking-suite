<?php
/**
 * Search Alerts Manager Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Search_Alerts_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_resbs_save_search_alert', array($this, 'ajax_save_search_alert'));
        add_action('wp_ajax_nopriv_resbs_save_search_alert', array($this, 'ajax_save_search_alert'));
        add_action('wp_ajax_resbs_delete_search_alert', array($this, 'ajax_delete_search_alert'));
        add_action('wp_ajax_resbs_get_search_alerts', array($this, 'ajax_get_search_alerts'));
        
        // Cron job for sending alerts
        add_action('resbs_send_search_alerts', array($this, 'send_search_alerts'));
        
        // Schedule cron job if not already scheduled
        if (!wp_next_scheduled('resbs_send_search_alerts')) {
            wp_schedule_event(time(), 'hourly', 'resbs_send_search_alerts');
        }
        
        // Shortcode
        add_shortcode('resbs_search_alerts', array($this, 'search_alerts_shortcode'));
    }

    /**
     * Initialize
     */
    public function init() {
        // Create search alerts table if it doesn't exist
        $this->create_search_alerts_table();
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'resbs-search-alerts',
            RESBS_URL . 'assets/css/search-alerts.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'resbs-search-alerts',
            RESBS_URL . 'assets/js/search-alerts.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('resbs-search-alerts', 'resbs_search_alerts_ajax', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('resbs_search_alerts_nonce')),
            'messages' => array(
                'alert_saved' => esc_html__('Search alert saved successfully!', 'realestate-booking-suite'),
                'alert_deleted' => esc_html__('Search alert deleted successfully!', 'realestate-booking-suite'),
                'alert_save_failed' => esc_html__('Failed to save search alert.', 'realestate-booking-suite'),
                'alert_delete_failed' => esc_html__('Failed to delete search alert.', 'realestate-booking-suite'),
                'invalid_email' => esc_html__('Please enter a valid email address.', 'realestate-booking-suite'),
                'name_required' => esc_html__('Please enter your name.', 'realestate-booking-suite'),
                'email_required' => esc_html__('Please enter your email address.', 'realestate-booking-suite'),
                'search_criteria_required' => esc_html__('Please select at least one search criteria.', 'realestate-booking-suite'),
                'loading' => esc_html__('Loading...', 'realestate-booking-suite'),
                'error' => esc_html__('An error occurred. Please try again.', 'realestate-booking-suite')
            )
        ));
    }

    /**
     * Create search alerts table
     */
    private function create_search_alerts_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'resbs_search_alerts';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            search_criteria longtext NOT NULL,
            frequency varchar(20) DEFAULT 'daily',
            last_sent datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'active',
            user_id bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY email (email),
            KEY status (status),
            KEY last_sent (last_sent)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * AJAX handler for saving search alert
     */
    public function ajax_save_search_alert() {
        // Check if required POST data exists
        if (!isset($_POST['nonce']) || !isset($_POST['name']) || !isset($_POST['email']) || !isset($_POST['search_criteria'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Missing required fields.', 'realestate-booking-suite')
            ));
        }

        // Verify nonce using security helper
        RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'resbs_search_alerts_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('save_search_alert', 5, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        // Sanitize and validate input
        $name = RESBS_Security::sanitize_text($_POST['name']);
        $email = RESBS_Security::sanitize_email($_POST['email']);
        $frequency = isset($_POST['frequency']) ? RESBS_Security::sanitize_text($_POST['frequency']) : 'daily';
        $search_criteria = RESBS_Security::sanitize_textarea($_POST['search_criteria']);

        // Validate required fields
        if (empty($name)) {
            wp_send_json_error(array(
                'message' => esc_html__('Please enter your name.', 'realestate-booking-suite')
            ));
        }

        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array(
                'message' => esc_html__('Please enter a valid email address.', 'realestate-booking-suite')
            ));
        }

        if (empty($search_criteria)) {
            wp_send_json_error(array(
                'message' => esc_html__('Please select at least one search criteria.', 'realestate-booking-suite')
            ));
        }

        // Validate frequency
        $allowed_frequencies = array('hourly', 'daily', 'weekly');
        if (!in_array($frequency, $allowed_frequencies)) {
            $frequency = 'daily';
        }

        // Security: If user is logged in, verify email ownership
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user = get_userdata($user_id);
            if ($user && $user->user_email !== $email) {
                // Check if user has permission to create alerts for other emails (admin only)
                if (!current_user_can('manage_options')) {
                    wp_send_json_error(array(
                        'message' => esc_html__('You can only create alerts for your own email address.', 'realestate-booking-suite')
                    ));
                }
            }
        }

        // Save search alert
        $alert_id = $this->save_search_alert($name, $email, $search_criteria, $frequency);

        if ($alert_id) {
            wp_send_json_success(array(
                'message' => esc_html__('Search alert saved successfully!', 'realestate-booking-suite'),
                'alert_id' => $alert_id
            ));
        } else {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to save search alert.', 'realestate-booking-suite')
            ));
        }
    }

    /**
     * AJAX handler for deleting search alert
     */
    public function ajax_delete_search_alert() {
        // Check if required POST data exists
        if (!isset($_POST['nonce']) || !isset($_POST['alert_id'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Missing required fields.', 'realestate-booking-suite')
            ));
        }

        // Verify nonce using security helper
        RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'resbs_search_alerts_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('delete_search_alert', 10, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        $alert_id = RESBS_Security::sanitize_int($_POST['alert_id']);
        
        if (!$alert_id) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid alert ID.', 'realestate-booking-suite')
            ));
        }

        // Check if user owns this alert or is admin
        $alert = $this->get_search_alert($alert_id);
        if (!$alert) {
            wp_send_json_error(array(
                'message' => esc_html__('Search alert not found.', 'realestate-booking-suite')
            ));
        }

        // Check ownership
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            if ($alert->user_id != $user_id && !current_user_can('manage_options')) {
                wp_send_json_error(array(
                    'message' => esc_html__('You do not have permission to delete this alert.', 'realestate-booking-suite')
                ));
            }
        } else {
            // For non-logged in users, require email verification
            if (!isset($_POST['email'])) {
                wp_send_json_error(array(
                    'message' => esc_html__('Email address is required to delete this alert.', 'realestate-booking-suite')
                ));
            }
            $posted_email = RESBS_Security::sanitize_email($_POST['email']);
            if (empty($posted_email) || !is_email($posted_email)) {
                wp_send_json_error(array(
                    'message' => esc_html__('Please provide a valid email address.', 'realestate-booking-suite')
                ));
            }
            // Verify email matches alert owner
            if ($alert->email !== $posted_email) {
                wp_send_json_error(array(
                    'message' => esc_html__('You do not have permission to delete this alert.', 'realestate-booking-suite')
                ));
            }
        }

        // Delete alert
        $deleted = $this->delete_search_alert($alert_id);

        if ($deleted) {
            wp_send_json_success(array(
                'message' => esc_html__('Search alert deleted successfully!', 'realestate-booking-suite')
            ));
        } else {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to delete search alert.', 'realestate-booking-suite')
            ));
        }
    }

    /**
     * AJAX handler for getting search alerts
     */
    public function ajax_get_search_alerts() {
        // Check if required POST data exists
        if (!isset($_POST['nonce']) || !isset($_POST['email'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Missing required fields.', 'realestate-booking-suite')
            ));
        }

        // Verify nonce using security helper
        RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'resbs_search_alerts_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('get_search_alerts', 20, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        $email = RESBS_Security::sanitize_email($_POST['email']);
        
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid email address.', 'realestate-booking-suite')
            ));
        }

        // Security: Verify email ownership
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $user = get_userdata($user_id);
            // Users can only view their own alerts unless they're admins
            if ($user && $user->user_email !== $email && !current_user_can('manage_options')) {
                wp_send_json_error(array(
                    'message' => esc_html__('You can only view alerts for your own email address.', 'realestate-booking-suite')
                ));
            }
        }
        // For non-logged in users, we allow viewing alerts by email (they need to know the email)
        // This is acceptable for public functionality, but rate limiting provides protection

        $alerts = $this->get_search_alerts_by_email($email);

        // Sanitize alert data before sending
        $sanitized_alerts = array();
        foreach ($alerts as $alert) {
            $sanitized_alerts[] = array(
                'id' => absint($alert->id),
                'name' => esc_html($alert->name),
                'email' => esc_html($alert->email),
                'search_criteria' => esc_html($alert->search_criteria),
                'frequency' => esc_html($alert->frequency),
                'created_at' => esc_html($alert->created_at),
                'last_sent' => esc_html($alert->last_sent),
                'status' => esc_html($alert->status)
            );
        }

        wp_send_json_success(array(
            'alerts' => $sanitized_alerts
        ));
    }

    /**
     * Save search alert
     */
    private function save_search_alert($name, $email, $search_criteria, $frequency) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'resbs_search_alerts';
        
        $user_id = is_user_logged_in() ? get_current_user_id() : null;
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'search_criteria' => $search_criteria,
                'frequency' => $frequency,
                'user_id' => $user_id,
                'status' => 'active',
                'created_at' => current_time('mysql')
            ),
            array(
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s'
            )
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Delete search alert
     */
    private function delete_search_alert($alert_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'resbs_search_alerts';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $alert_id),
            array('%d')
        );
        
        return $result !== false;
    }

    /**
     * Get search alert
     */
    private function get_search_alert($alert_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'resbs_search_alerts';
        // Escape table name for consistency (table name is safe - constructed from $wpdb->prefix)
        $table_name_escaped = esc_sql($table_name);
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM `{$table_name_escaped}` WHERE id = %d",
            $alert_id
        ));
    }

    /**
     * Get search alerts by email
     */
    private function get_search_alerts_by_email($email) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'resbs_search_alerts';
        // Escape table name for consistency (table name is safe - constructed from $wpdb->prefix)
        $table_name_escaped = esc_sql($table_name);
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `{$table_name_escaped}` WHERE email = %s AND status = 'active' ORDER BY created_at DESC",
            $email
        ));
    }

    /**
     * Send search alerts
     */
    public function send_search_alerts() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'resbs_search_alerts';
        // Escape table name for consistency (table name is safe - constructed from $wpdb->prefix)
        $table_name_escaped = esc_sql($table_name);
        
        // Get alerts that need to be sent
        $alerts = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM `{$table_name_escaped}` 
             WHERE status = 'active' 
             AND (last_sent IS NULL OR last_sent < %s)
             ORDER BY created_at ASC",
            $this->get_next_send_time()
        ));
        
        foreach ($alerts as $alert) {
            $this->process_search_alert($alert);
        }
    }

    /**
     * Process individual search alert
     */
    private function process_search_alert($alert) {
        // Parse search criteria
        $criteria = json_decode($alert->search_criteria, true);
        
        // Validate decoded criteria is an array
        if (!is_array($criteria) || empty($criteria)) {
            return;
        }
        
        // Sanitize criteria values to prevent any potential issues
        $criteria = $this->sanitize_search_criteria($criteria);
        
        // Find matching properties
        $matching_properties = $this->find_matching_properties($criteria);
        
        // Only send if there are new properties
        if (!empty($matching_properties)) {
            // Send email
            do_action('resbs_search_alert_triggered', $alert->id, array(
                'name' => $alert->name,
                'email' => $alert->email,
                'criteria' => $this->format_search_criteria($criteria)
            ), $matching_properties);
            
            // Update last sent time
            $this->update_alert_last_sent($alert->id);
        }
    }

    /**
     * Find matching properties
     */
    private function find_matching_properties($criteria) {
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        // Price range
        if (!empty($criteria['price_min']) || !empty($criteria['price_max'])) {
            $price_query = array('relation' => 'AND');
            
            if (!empty($criteria['price_min'])) {
                $price_query[] = array(
                    'key' => '_property_price',
                    'value' => intval($criteria['price_min']),
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }
            
            if (!empty($criteria['price_max'])) {
                $price_query[] = array(
                    'key' => '_property_price',
                    'value' => intval($criteria['price_max']),
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                );
            }
            
            $query_args['meta_query'][] = $price_query;
        }
        
        // Bedrooms
        if (!empty($criteria['bedrooms'])) {
            $query_args['meta_query'][] = array(
                'key' => '_property_bedrooms',
                'value' => intval($criteria['bedrooms']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        // Bathrooms
        if (!empty($criteria['bathrooms'])) {
            $query_args['meta_query'][] = array(
                'key' => '_property_bathrooms',
                'value' => intval($criteria['bathrooms']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        // Property type
        if (!empty($criteria['property_type'])) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($criteria['property_type'])
            );
        }
        
        // Property status
        if (!empty($criteria['property_status'])) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => sanitize_text_field($criteria['property_status'])
            );
        }
        
        // Location
        if (!empty($criteria['location'])) {
            $query_args['tax_query'][] = array(
                'taxonomy' => 'property_location',
                'field' => 'slug',
                'terms' => sanitize_text_field($criteria['location'])
            );
        }
        
        // Only show properties created in the last 24 hours
        $query_args['date_query'] = array(
            array(
                'after' => '24 hours ago'
            )
        );
        
        $properties_query = new WP_Query($query_args);
        
        $properties = array();
        if ($properties_query->have_posts()) {
            while ($properties_query->have_posts()) {
                $properties_query->the_post();
                $properties[] = array(
                    'id' => get_the_ID(),
                    'title' => esc_html(get_the_title()),
                    'url' => esc_url(get_permalink()),
                    'price' => esc_html(get_post_meta(get_the_ID(), '_property_price', true)),
                    'bedrooms' => esc_html(get_post_meta(get_the_ID(), '_property_bedrooms', true)),
                    'bathrooms' => esc_html(get_post_meta(get_the_ID(), '_property_bathrooms', true)),
                    'location' => $this->get_property_location(get_the_ID())
                );
            }
            wp_reset_postdata();
        }
        
        return $properties;
    }

    /**
     * Get property location
     */
    private function get_property_location($property_id) {
        $locations = get_the_terms($property_id, 'property_location');
        if ($locations && !is_wp_error($locations)) {
            return esc_html($locations[0]->name);
        }
        return '';
    }

    /**
     * Format search criteria for display
     */
    private function format_search_criteria($criteria) {
        $formatted = array();
        
        if (!empty($criteria['price_min']) || !empty($criteria['price_max'])) {
            $price_range = '';
            if (!empty($criteria['price_min'])) {
                $price_range .= esc_html(resbs_format_price(intval($criteria['price_min'])));
            }
            $price_range .= ' - ';
            if (!empty($criteria['price_max'])) {
                $price_range .= esc_html(resbs_format_price(intval($criteria['price_max'])));
            }
            $formatted[] = esc_html__('Price', 'realestate-booking-suite') . ': ' . $price_range;
        }
        
        if (!empty($criteria['bedrooms'])) {
            $formatted[] = esc_html__('Bedrooms', 'realestate-booking-suite') . ': ' . esc_html(intval($criteria['bedrooms'])) . '+';
        }
        
        if (!empty($criteria['bathrooms'])) {
            $formatted[] = esc_html__('Bathrooms', 'realestate-booking-suite') . ': ' . esc_html(intval($criteria['bathrooms'])) . '+';
        }
        
        if (!empty($criteria['property_type'])) {
            $type = get_term_by('slug', sanitize_text_field($criteria['property_type']), 'property_type');
            if ($type) {
                $formatted[] = esc_html__('Type', 'realestate-booking-suite') . ': ' . esc_html($type->name);
            }
        }
        
        if (!empty($criteria['location'])) {
            $location = get_term_by('slug', sanitize_text_field($criteria['location']), 'property_location');
            if ($location) {
                $formatted[] = esc_html__('Location', 'realestate-booking-suite') . ': ' . esc_html($location->name);
            }
        }
        
        return implode(', ', $formatted);
    }

    /**
     * Update alert last sent time
     */
    private function update_alert_last_sent($alert_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'resbs_search_alerts';
        
        $wpdb->update(
            $table_name,
            array('last_sent' => current_time('mysql')),
            array('id' => $alert_id),
            array('%s'),
            array('%d')
        );
    }

    /**
     * Get next send time based on frequency
     */
    private function get_next_send_time($frequency = 'daily') {
        $frequencies = array(
            'hourly' => '1 hour ago',
            'daily' => '1 day ago',
            'weekly' => '1 week ago'
        );
        
        // Validate frequency
        if (!isset($frequencies[$frequency])) {
            $frequency = 'daily';
        }
        
        return date('Y-m-d H:i:s', strtotime($frequencies[$frequency]));
    }
    
    /**
     * Sanitize search criteria array
     * 
     * @param array $criteria Search criteria array
     * @return array Sanitized criteria array
     */
    private function sanitize_search_criteria($criteria) {
        $sanitized = array();
        
        // Allowed criteria keys
        $allowed_keys = array('price_min', 'price_max', 'bedrooms', 'bathrooms', 'property_type', 'property_status', 'location');
        
        foreach ($criteria as $key => $value) {
            // Only allow expected keys
            if (!in_array($key, $allowed_keys, true)) {
                continue;
            }
            
            // Sanitize based on key type
            if (in_array($key, array('price_min', 'price_max', 'bedrooms', 'bathrooms'), true)) {
                $sanitized[$key] = absint($value);
            } else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }
        
        return $sanitized;
    }

    /**
     * Search alerts shortcode
     */
    public function search_alerts_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_form' => 'true',
            'show_list' => 'true',
            'email' => ''
        ), $atts);

        $show_form = $atts['show_form'] !== 'false';
        $show_list = $atts['show_list'] !== 'false';
        $email = sanitize_email($atts['email']);

        ob_start();
        ?>
        <div class="resbs-search-alerts-container">
            <?php if ($show_form): ?>
                <div class="resbs-search-alert-form">
                    <h3><?php esc_html_e('Save Search Alert', 'realestate-booking-suite'); ?></h3>
                        <form id="resbs-search-alert-form">
                        <?php wp_nonce_field('resbs_search_alerts_nonce', 'resbs_search_alerts_nonce'); ?>
                        
                        <div class="resbs-form-row">
                            <div class="resbs-form-group">
                                <label for="resbs_alert_name">
                                    <?php esc_html_e('Your Name', 'realestate-booking-suite'); ?> <span class="required">*</span>
                                </label>
                                <input type="text" id="resbs_alert_name" name="name" required>
                            </div>
                            
                            <div class="resbs-form-group">
                                <label for="resbs_alert_email">
                                    <?php esc_html_e('Email Address', 'realestate-booking-suite'); ?> <span class="required">*</span>
                                </label>
                                <input type="email" id="resbs_alert_email" name="email" value="<?php echo esc_attr($email); ?>" required>
                            </div>
                        </div>
                        
                        <div class="resbs-form-row">
                            <div class="resbs-form-group">
                                <label for="resbs_alert_frequency">
                                    <?php esc_html_e('Alert Frequency', 'realestate-booking-suite'); ?>
                                </label>
                                <select id="resbs_alert_frequency" name="frequency">
                                    <option value="daily"><?php esc_html_e('Daily', 'realestate-booking-suite'); ?></option>
                                    <option value="weekly"><?php esc_html_e('Weekly', 'realestate-booking-suite'); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label><?php esc_html_e('Search Criteria', 'realestate-booking-suite'); ?> <span class="required">*</span></label>
                            <div class="resbs-search-criteria">
                                <input type="hidden" id="resbs_search_criteria" name="search_criteria" required>
                                <p><?php esc_html_e('Please use the property search filters above to set your criteria, then save this alert.', 'realestate-booking-suite'); ?></p>
                            </div>
                        </div>
                        
                        <div class="resbs-form-actions">
                            <button type="submit" class="resbs-save-alert-btn">
                                <?php esc_html_e('Save Search Alert', 'realestate-booking-suite'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <?php if ($show_list && !empty($email)): ?>
                <div class="resbs-search-alerts-list">
                    <h3><?php esc_html_e('Your Search Alerts', 'realestate-booking-suite'); ?></h3>
                    <div id="resbs-alerts-list">
                        <!-- Alerts will be loaded here -->
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize Search Alerts Manager
new RESBS_Search_Alerts_Manager();

