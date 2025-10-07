<?php
/**
 * Shortcode AJAX Handlers
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Shortcode_AJAX {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_resbs_filter_properties', array($this, 'filter_properties'));
        add_action('wp_ajax_nopriv_resbs_filter_properties', array($this, 'filter_properties'));
        
        add_action('wp_ajax_resbs_search_properties', array($this, 'search_properties'));
        add_action('wp_ajax_nopriv_resbs_search_properties', array($this, 'search_properties'));
        
        add_action('wp_ajax_resbs_update_profile', array($this, 'update_profile'));
        
        add_action('wp_ajax_resbs_submit_property', array($this, 'submit_property'));
        
        add_action('wp_ajax_resbs_clear_favorites', array($this, 'clear_favorites'));
        
        add_action('wp_ajax_resbs_load_more_properties', array($this, 'load_more_properties'));
        add_action('wp_ajax_nopriv_resbs_load_more_properties', array($this, 'load_more_properties'));
    }

    /**
     * Filter properties AJAX handler
     */
    public function filter_properties() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_widget_filter')) {
            wp_die(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }

        // Sanitize form data
        $form_data = array();
        parse_str($_POST['form_data'], $form_data);
        
        $sanitized_data = array(
            'property_type' => RESBS_Security::sanitize_text($form_data['property_type'] ?? ''),
            'location' => RESBS_Security::sanitize_text($form_data['location'] ?? ''),
            'price_min' => RESBS_Security::sanitize_int($form_data['price_min'] ?? 0),
            'price_max' => RESBS_Security::sanitize_int($form_data['price_max'] ?? 0)
        );

        // Sanitize widget settings
        $settings = RESBS_Security::sanitize_array($_POST['widget_settings'] ?? array());

        // Build query args
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $settings['posts_per_page'] ?? 12,
            'orderby' => $settings['orderby'] ?? 'date',
            'order' => $settings['order'] ?? 'DESC',
        );

        // Add meta query for price range
        $meta_query = array();
        
        if (!empty($sanitized_data['price_min'])) {
            $meta_query[] = array(
                'key' => '_property_price',
                'value' => $sanitized_data['price_min'],
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        if (!empty($sanitized_data['price_max'])) {
            $meta_query[] = array(
                'key' => '_property_price',
                'value' => $sanitized_data['price_max'],
                'compare' => '<=',
                'type' => 'NUMERIC'
            );
        }

        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }

        // Add taxonomy query
        $tax_query = array();
        
        if (!empty($sanitized_data['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $sanitized_data['property_type']
            );
        }

        if (!empty($sanitized_data['location'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_location',
                'field' => 'slug',
                'terms' => $sanitized_data['location']
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        // Execute query
        $properties = new WP_Query($query_args);

        // Generate HTML
        ob_start();
        
        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $this->render_property_card($settings);
            }
            wp_reset_postdata();
        } else {
            echo '<p class="resbs-no-properties">' . esc_html__('No properties found matching your criteria.', 'realestate-booking-suite') . '</p>';
        }
        
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'count' => $properties->found_posts
        ));
    }

    /**
     * Search properties AJAX handler
     */
    public function search_properties() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_search_form')) {
            wp_die(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }

        // Sanitize form data
        $form_data = array();
        parse_str($_POST['form_data'], $form_data);
        
        $sanitized_data = array(
            'keyword' => RESBS_Security::sanitize_text($form_data['keyword'] ?? ''),
            'property_type' => RESBS_Security::sanitize_text($form_data['property_type'] ?? ''),
            'location' => RESBS_Security::sanitize_text($form_data['location'] ?? ''),
            'price_min' => RESBS_Security::sanitize_int($form_data['price_min'] ?? 0),
            'price_max' => RESBS_Security::sanitize_int($form_data['price_max'] ?? 0)
        );

        // Build query args
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'orderby' => 'relevance',
            'order' => 'DESC',
        );

        // Add search keyword
        if (!empty($sanitized_data['keyword'])) {
            $query_args['s'] = $sanitized_data['keyword'];
        }

        // Add meta query for price range
        $meta_query = array();
        
        if (!empty($sanitized_data['price_min'])) {
            $meta_query[] = array(
                'key' => '_property_price',
                'value' => $sanitized_data['price_min'],
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        if (!empty($sanitized_data['price_max'])) {
            $meta_query[] = array(
                'key' => '_property_price',
                'value' => $sanitized_data['price_max'],
                'compare' => '<=',
                'type' => 'NUMERIC'
            );
        }

        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }

        // Add taxonomy query
        $tax_query = array();
        
        if (!empty($sanitized_data['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $sanitized_data['property_type']
            );
        }

        if (!empty($sanitized_data['location'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_location',
                'field' => 'slug',
                'terms' => $sanitized_data['location']
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        // Execute query
        $properties = new WP_Query($query_args);

        // Generate HTML
        ob_start();
        
        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $this->render_property_card(array(
                    'show_price' => true,
                    'show_meta' => true,
                    'show_excerpt' => true,
                    'show_badges' => true,
                    'show_favorite_button' => true,
                    'show_book_button' => true
                ));
            }
            wp_reset_postdata();
        } else {
            echo '<p class="resbs-no-properties">' . esc_html__('No properties found matching your search criteria.', 'realestate-booking-suite') . '</p>';
        }
        
        $html = ob_get_clean();

        // Generate markers for map
        $markers = array();
        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $property_id = get_the_ID();
                $latitude = get_post_meta($property_id, '_property_latitude', true);
                $longitude = get_post_meta($property_id, '_property_longitude', true);
                
                if (!empty($latitude) && !empty($longitude)) {
                    $markers[] = array(
                        'id' => $property_id,
                        'title' => get_the_title(),
                        'lat' => floatval($latitude),
                        'lng' => floatval($longitude),
                        'price' => get_post_meta($property_id, '_property_price', true),
                        'url' => get_permalink()
                    );
                }
            }
            wp_reset_postdata();
        }

        wp_send_json_success(array(
            'html' => $html,
            'count' => $properties->found_posts,
            'markers' => $markers
        ));
    }

    /**
     * Update profile AJAX handler
     */
    public function update_profile() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => esc_html__('You must be logged in to update your profile.', 'realestate-booking-suite')
            ));
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_update_profile')) {
            wp_die(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }

        // Sanitize form data
        $form_data = array();
        parse_str($_POST['form_data'], $form_data);
        
        $sanitized_data = array(
            'first_name' => RESBS_Security::sanitize_text($form_data['first_name'] ?? ''),
            'last_name' => RESBS_Security::sanitize_text($form_data['last_name'] ?? ''),
            'email' => RESBS_Security::sanitize_email($form_data['email'] ?? ''),
            'phone' => RESBS_Security::sanitize_text($form_data['phone'] ?? '')
        );

        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        // Update user data
        $user_data = array(
            'ID' => $user_id,
            'first_name' => $sanitized_data['first_name'],
            'last_name' => $sanitized_data['last_name'],
            'user_email' => $sanitized_data['email']
        );

        $result = wp_update_user($user_data);

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to update profile. Please try again.', 'realestate-booking-suite')
            ));
        }

        // Update phone meta
        update_user_meta($user_id, 'phone', $sanitized_data['phone']);

        wp_send_json_success(array(
            'message' => esc_html__('Profile updated successfully.', 'realestate-booking-suite')
        ));
    }

    /**
     * Submit property AJAX handler
     */
    public function submit_property() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => esc_html__('You must be logged in to submit a property.', 'realestate-booking-suite')
            ));
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_submit_property')) {
            wp_die(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }

        // Sanitize form data
        $sanitized_data = array(
            'property_title' => RESBS_Security::sanitize_text($_POST['property_title'] ?? ''),
            'property_type' => RESBS_Security::sanitize_int($_POST['property_type'] ?? 0),
            'property_price' => RESBS_Security::sanitize_int($_POST['property_price'] ?? 0),
            'property_size' => RESBS_Security::sanitize_int($_POST['property_size'] ?? 0),
            'property_bedrooms' => RESBS_Security::sanitize_int($_POST['property_bedrooms'] ?? 0),
            'property_bathrooms' => RESBS_Security::sanitize_int($_POST['property_bathrooms'] ?? 0),
            'property_description' => RESBS_Security::sanitize_textarea($_POST['property_description'] ?? ''),
            'property_address' => RESBS_Security::sanitize_text($_POST['property_address'] ?? ''),
            'property_location' => RESBS_Security::sanitize_int($_POST['property_location'] ?? 0)
        );

        // Validate required fields
        if (empty($sanitized_data['property_title'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Property title is required.', 'realestate-booking-suite')
            ));
        }

        if (empty($sanitized_data['property_type'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Property type is required.', 'realestate-booking-suite')
            ));
        }

        if (empty($sanitized_data['property_price'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Property price is required.', 'realestate-booking-suite')
            ));
        }

        if (empty($sanitized_data['property_description'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Property description is required.', 'realestate-booking-suite')
            ));
        }

        // Create property post
        $post_data = array(
            'post_title' => $sanitized_data['property_title'],
            'post_content' => $sanitized_data['property_description'],
            'post_status' => 'pending', // Require admin approval
            'post_type' => 'property',
            'post_author' => get_current_user_id()
        );

        $property_id = wp_insert_post($post_data);

        if (is_wp_error($property_id)) {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to create property. Please try again.', 'realestate-booking-suite')
            ));
        }

        // Save meta data
        update_post_meta($property_id, '_property_price', $sanitized_data['property_price']);
        update_post_meta($property_id, '_property_size', $sanitized_data['property_size']);
        update_post_meta($property_id, '_property_bedrooms', $sanitized_data['property_bedrooms']);
        update_post_meta($property_id, '_property_bathrooms', $sanitized_data['property_bathrooms']);
        update_post_meta($property_id, '_property_address', $sanitized_data['property_address']);

        // Set taxonomies
        if (!empty($sanitized_data['property_type'])) {
            wp_set_post_terms($property_id, array($sanitized_data['property_type']), 'property_type');
        }

        if (!empty($sanitized_data['property_location'])) {
            wp_set_post_terms($property_id, array($sanitized_data['property_location']), 'property_location');
        }

        // Handle file uploads
        if (!empty($_FILES['property_gallery'])) {
            $this->handle_property_gallery_upload($property_id, $_FILES['property_gallery']);
        }

        wp_send_json_success(array(
            'message' => esc_html__('Property submitted successfully. It will be reviewed before being published.', 'realestate-booking-suite'),
            'property_id' => $property_id
        ));
    }

    /**
     * Clear favorites AJAX handler
     */
    public function clear_favorites() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => esc_html__('You must be logged in to manage favorites.', 'realestate-booking-suite')
            ));
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_favorites_nonce')) {
            wp_die(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }

        $user_id = RESBS_Security::sanitize_int($_POST['user_id'] ?? 0);

        if ($user_id !== get_current_user_id()) {
            wp_send_json_error(array(
                'message' => esc_html__('Unauthorized action.', 'realestate-booking-suite')
            ));
        }

        // Clear favorites
        delete_user_meta($user_id, 'resbs_favorite_properties');

        wp_send_json_success(array(
            'message' => esc_html__('All favorites cleared successfully.', 'realestate-booking-suite')
        ));
    }

    /**
     * Load more properties AJAX handler
     */
    public function load_more_properties() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_widget_filter')) {
            wp_die(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }

        $page = RESBS_Security::sanitize_int($_POST['page'] ?? 2);
        $settings = RESBS_Security::sanitize_array($_POST['settings'] ?? array());

        // Build query args
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $settings['posts_per_page'] ?? 12,
            'paged' => $page,
            'orderby' => $settings['orderby'] ?? 'date',
            'order' => $settings['order'] ?? 'DESC',
        );

        // Add meta query for featured properties
        if (!empty($settings['featured_only'])) {
            $query_args['meta_query'] = array(
                array(
                    'key' => '_property_featured',
                    'value' => 'yes',
                    'compare' => '='
                )
            );
        }

        // Add taxonomy query
        $tax_query = array();
        
        if (!empty($settings['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $settings['property_type']
            );
        }

        if (!empty($settings['property_status'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $settings['property_status']
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        // Execute query
        $properties = new WP_Query($query_args);

        // Generate HTML
        ob_start();
        
        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $this->render_property_card($settings);
            }
            wp_reset_postdata();
        }
        
        $html = ob_get_clean();

        $has_more = $page < $properties->max_num_pages;

        wp_send_json_success(array(
            'html' => $html,
            'has_more' => $has_more,
            'current_page' => $page,
            'max_pages' => $properties->max_num_pages
        ));
    }

    /**
     * Render property card
     * 
     * @param array $settings Widget settings
     */
    private function render_property_card($settings) {
        $property_id = get_the_ID();
        $property_price = get_post_meta($property_id, '_property_price', true);
        $property_bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $property_bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        $property_size = get_post_meta($property_id, '_property_size', true);
        $property_location = get_the_terms($property_id, 'property_location');

        ?>
        <div class="resbs-property-card resbs-layout-<?php echo esc_attr($settings['layout'] ?? 'grid'); ?>">
            <div class="resbs-property-image">
                <?php if (has_post_thumbnail()): ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <?php the_post_thumbnail('medium', array('alt' => esc_attr(get_the_title()))); ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <img src="<?php echo esc_url(RESBS_URL . 'assets/images/placeholder.jpg'); ?>" 
                             alt="<?php esc_attr_e('Property Image', 'realestate-booking-suite'); ?>">
                    </a>
                <?php endif; ?>

                <?php if (!empty($settings['show_badges'])): ?>
                    <?php do_action('resbs_property_badges', $property_id, 'shortcode'); ?>
                <?php endif; ?>

                <?php if (!empty($settings['show_favorite_button'])): ?>
                    <div class="resbs-property-actions">
                        <button type="button" class="resbs-favorite-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
                            <span class="dashicons dashicons-heart"></span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="resbs-property-content">
                <h3 class="resbs-property-title">
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <?php echo esc_html(get_the_title()); ?>
                    </a>
                </h3>

                <?php if (!empty($settings['show_price']) && !empty($property_price)): ?>
                    <div class="resbs-property-price">
                        <?php echo esc_html('$' . number_format($property_price)); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($settings['show_meta'])): ?>
                    <div class="resbs-property-meta">
                        <?php if (!empty($property_bedrooms)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-bed-alt"></span>
                                <span><?php echo esc_html($property_bedrooms); ?> <?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_bathrooms)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-bath"></span>
                                <span><?php echo esc_html($property_bathrooms); ?> <?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_size)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-admin-home"></span>
                                <span><?php echo esc_html($property_size); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_location)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-location"></span>
                                <span><?php echo esc_html($property_location[0]->name); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($settings['show_excerpt'])): ?>
                    <div class="resbs-property-excerpt">
                        <?php echo wp_kses_post(wp_trim_words(get_the_excerpt(), 20)); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($settings['show_book_button'])): ?>
                    <div class="resbs-property-actions">
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="resbs-property-btn primary">
                            <?php esc_html_e('View Details', 'realestate-booking-suite'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Handle property gallery upload
     * 
     * @param int $property_id Property ID
     * @param array $files Uploaded files
     */
    private function handle_property_gallery_upload($property_id, $files) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }

        $uploaded_files = array();
        
        foreach ($files['name'] as $key => $value) {
            if ($files['name'][$key]) {
                $file = array(
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                );

                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($file, $upload_overrides);

                if ($movefile && !isset($movefile['error'])) {
                    $uploaded_files[] = $movefile['url'];
                }
            }
        }

        if (!empty($uploaded_files)) {
            update_post_meta($property_id, '_property_gallery', $uploaded_files);
        }
    }
}

// Initialize AJAX handlers
new RESBS_Shortcode_AJAX();
