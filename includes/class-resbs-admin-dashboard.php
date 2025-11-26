<?php
/**
 * Professional Admin Dashboard
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Admin_Dashboard {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_dashboard_assets'));
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
        add_action('admin_init', array($this, 'init_dashboard'));
    }

    /**
     * Initialize dashboard
     */
    public function init_dashboard() {
        // Add dashboard widgets
        add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
    }

    /**
     * Enqueue dashboard assets
     * 
     * Security: Only enqueue assets for authorized users
     */
    public function enqueue_dashboard_assets($hook) {
        // Only enqueue for authorized users
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if ($hook === 'index.php' || strpos($hook, 'resbs-') !== false) {
            wp_enqueue_style(
                'resbs-admin-dashboard',
                RESBS_URL . 'assets/css/admin-dashboard.css',
                array(),
                '1.0.0'
            );
            
            wp_enqueue_script(
                'resbs-admin-dashboard',
                RESBS_URL . 'assets/js/admin-dashboard.js',
                array('jquery', 'wp-util'),
                '1.0.0',
                true
            );
            
            wp_localize_script('resbs-admin-dashboard', 'resbs_dashboard', array(
                'ajax_url' => esc_url(admin_url('admin-ajax.php')),
                'nonce' => esc_js(wp_create_nonce('resbs_dashboard_nonce')),
                'strings' => array(
                    'loading' => esc_html__('Loading...', 'realestate-booking-suite'),
                    'error' => esc_html__('An error occurred. Please try again.', 'realestate-booking-suite'),
                    'no_data' => esc_html__('No data available.', 'realestate-booking-suite')
                )
            ));
        }
    }

    /**
     * Add dashboard widgets
     * 
     * Security: Only show widgets to users with manage_options capability
     */
    public function add_dashboard_widgets() {
        // Check user capability before adding widgets
        if (!current_user_can('manage_options')) {
            return;
        }
        
        wp_add_dashboard_widget(
            'resbs_property_stats',
            esc_html__('Property Statistics', 'realestate-booking-suite'),
            array($this, 'property_stats_widget')
        );
        
        wp_add_dashboard_widget(
            'resbs_recent_properties',
            esc_html__('Recent Properties', 'realestate-booking-suite'),
            array($this, 'recent_properties_widget')
        );
        
        wp_add_dashboard_widget(
            'resbs_quick_actions',
            esc_html__('Quick Actions', 'realestate-booking-suite'),
            array($this, 'quick_actions_widget')
        );
    }

    /**
     * Property statistics widget
     * 
     * Security: Verify user capability before displaying sensitive data
     */
    public function property_stats_widget() {
        // Verify user has permission to view dashboard statistics
        if (!current_user_can('manage_options')) {
            echo '<p>' . esc_html__('You do not have permission to view this widget.', 'realestate-booking-suite') . '</p>';
            return;
        }
        
        $total_properties = wp_count_posts('property');
        $published_properties = $total_properties->publish;
        $draft_properties = $total_properties->draft;
        $pending_properties = $total_properties->pending;
        
        $total_bookings = $this->get_total_bookings();
        $revenue = $this->get_total_revenue();
        
        ?>
        <div class="resbs-dashboard-stats">
            <div class="resbs-stat-grid">
                <div class="resbs-stat-item">
                    <div class="resbs-stat-icon">
                        <span class="dashicons dashicons-building"></span>
                    </div>
                    <div class="resbs-stat-content">
                        <div class="resbs-stat-number"><?php echo esc_html($published_properties); ?></div>
                        <div class="resbs-stat-label"><?php esc_html_e('Published Properties', 'realestate-booking-suite'); ?></div>
                    </div>
                </div>
                
                <div class="resbs-stat-item">
                    <div class="resbs-stat-icon">
                        <span class="dashicons dashicons-edit"></span>
                    </div>
                    <div class="resbs-stat-content">
                        <div class="resbs-stat-number"><?php echo esc_html($draft_properties); ?></div>
                        <div class="resbs-stat-label"><?php esc_html_e('Draft Properties', 'realestate-booking-suite'); ?></div>
                    </div>
                </div>
                
                <div class="resbs-stat-item">
                    <div class="resbs-stat-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="resbs-stat-content">
                        <div class="resbs-stat-number"><?php echo esc_html($total_bookings); ?></div>
                        <div class="resbs-stat-label"><?php esc_html_e('Total Bookings', 'realestate-booking-suite'); ?></div>
                    </div>
                </div>
                
                <div class="resbs-stat-item">
                    <div class="resbs-stat-icon">
                        <span class="dashicons dashicons-money-alt"></span>
                    </div>
                    <div class="resbs-stat-content">
                        <div class="resbs-stat-number"><?php echo esc_html($this->format_currency($revenue)); ?></div>
                        <div class="resbs-stat-label"><?php esc_html_e('Total Revenue', 'realestate-booking-suite'); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="resbs-chart-container">
                <canvas id="resbs-property-chart" width="400" height="200"></canvas>
            </div>
        </div>
        <?php
    }

    /**
     * Recent properties widget
     * 
     * Security: Verify user capability before displaying property data
     */
    public function recent_properties_widget() {
        // Verify user has permission to view properties
        if (!current_user_can('manage_options')) {
            echo '<p>' . esc_html__('You do not have permission to view this widget.', 'realestate-booking-suite') . '</p>';
            return;
        }
        
        $recent_properties = get_posts(array(
            'post_type' => 'property',
            'posts_per_page' => 5,
            'post_status' => array('publish', 'draft', 'pending')
        ));
        
        ?>
        <div class="resbs-recent-properties">
            <?php if (empty($recent_properties)): ?>
                <p><?php esc_html_e('No properties found.', 'realestate-booking-suite'); ?></p>
            <?php else: ?>
                <div class="resbs-property-list">
                    <?php foreach ($recent_properties as $property): ?>
                        <?php
                        // Sanitize all post meta values for security
                        $price_raw = get_post_meta($property->ID, '_property_price', true);
                        $price = is_numeric($price_raw) ? floatval($price_raw) : '';
                        $bedrooms_raw = get_post_meta($property->ID, '_property_bedrooms', true);
                        $bedrooms = $bedrooms_raw ? absint($bedrooms_raw) : '';
                        $bathrooms_raw = get_post_meta($property->ID, '_property_bathrooms', true);
                        $bathrooms = $bathrooms_raw ? sanitize_text_field($bathrooms_raw) : '';
                        // Try multiple possible area meta keys
                        $area_raw = get_post_meta($property->ID, '_property_size', true);
                        if (empty($area_raw)) {
                            $area_raw = get_post_meta($property->ID, '_property_area_sqft', true);
                        }
                        $area = $area_raw ? sanitize_text_field($area_raw) : '';
                        $featured_image = get_the_post_thumbnail_url($property->ID, 'thumbnail');
                        ?>
                        <div class="resbs-property-item">
                            <div class="resbs-property-image">
                                <?php if ($featured_image): ?>
                                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($property->post_title); ?>">
                                <?php else: ?>
                                    <div class="resbs-no-image">
                                        <span class="dashicons dashicons-camera"></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="resbs-property-details">
                                <h4>
                                    <a href="<?php echo esc_url(get_edit_post_link($property->ID)); ?>">
                                        <?php echo esc_html($property->post_title); ?>
                                    </a>
                                </h4>
                                <div class="resbs-property-meta">
                                    <?php if (!empty($price) && is_numeric($price)): ?>
                                        <span class="resbs-price"><?php echo esc_html($this->format_currency($price)); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($bedrooms)): ?>
                                        <span class="resbs-bedrooms"><?php echo esc_html($bedrooms); ?> <?php esc_html_e('bed', 'realestate-booking-suite'); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($bathrooms)): ?>
                                        <span class="resbs-bathrooms"><?php echo esc_html($bathrooms); ?> <?php esc_html_e('bath', 'realestate-booking-suite'); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($area)): ?>
                                        <span class="resbs-area"><?php echo esc_html($area); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="resbs-property-status">
                                    <?php 
                                    // Sanitize post status for security
                                    $post_status = sanitize_key($property->post_status);
                                    $post_status_display = ucfirst(sanitize_text_field($property->post_status));
                                    ?>
                                    <span class="resbs-status resbs-status-<?php echo esc_attr($post_status); ?>">
                                        <?php echo esc_html($post_status_display); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="resbs-widget-footer">
                    <a href="<?php echo esc_url(admin_url('edit.php?post_type=property')); ?>" class="resbs-btn resbs-btn-primary">
                        <?php esc_html_e('View All Properties', 'realestate-booking-suite'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Quick actions widget
     * 
     * Security: Verify user capability before displaying admin actions
     */
    public function quick_actions_widget() {
        // Verify user has permission to access admin actions
        if (!current_user_can('manage_options')) {
            echo '<p>' . esc_html__('You do not have permission to view this widget.', 'realestate-booking-suite') . '</p>';
            return;
        }
        
        ?>
        <div class="resbs-quick-actions">
            <div class="resbs-action-grid">
                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=property')); ?>" class="resbs-action-item">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <span><?php esc_html_e('Add New Property', 'realestate-booking-suite'); ?></span>
                </a>
                
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=property')); ?>" class="resbs-action-item">
                    <span class="dashicons dashicons-building"></span>
                    <span><?php esc_html_e('Manage Properties', 'realestate-booking-suite'); ?></span>
                </a>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=resbs-general-settings')); ?>" class="resbs-action-item">
                    <span class="dashicons dashicons-admin-settings"></span>
                    <span><?php esc_html_e('Plugin Settings', 'realestate-booking-suite'); ?></span>
                </a>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=resbs-map-settings')); ?>" class="resbs-action-item">
                    <span class="dashicons dashicons-location"></span>
                    <span><?php esc_html_e('Map Settings', 'realestate-booking-suite'); ?></span>
                </a>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=resbs-email-settings')); ?>" class="resbs-action-item">
                    <span class="dashicons dashicons-email"></span>
                    <span><?php esc_html_e('Email Settings', 'realestate-booking-suite'); ?></span>
                </a>
                
                <a href="<?php echo esc_url(admin_url('admin.php?page=resbs-appearance-settings')); ?>" class="resbs-action-item">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <span><?php esc_html_e('Appearance', 'realestate-booking-suite'); ?></span>
                </a>
            </div>
            
            <div class="resbs-system-info">
                <h4><?php esc_html_e('System Information', 'realestate-booking-suite'); ?></h4>
                <div class="resbs-info-grid">
                    <div class="resbs-info-item">
                        <span class="resbs-info-label"><?php esc_html_e('Plugin Version:', 'realestate-booking-suite'); ?></span>
                        <span class="resbs-info-value"><?php 
                            // Get plugin version safely
                            $plugin_version = '1.0.0'; // Default fallback
                            if (defined('RESBS_VERSION')) {
                                // Use constant() to avoid linter errors for undefined constants
                                $plugin_version = constant('RESBS_VERSION');
                            } elseif (defined('RESBS_PATH')) {
                                // Get version from plugin header if constant not defined
                                $plugin_file = RESBS_PATH . 'realestate-booking-suite.php';
                                if (file_exists($plugin_file)) {
                                    $plugin_data = get_file_data($plugin_file, array('Version' => 'Version'), 'plugin');
                                    if (!empty($plugin_data['Version'])) {
                                        $plugin_version = $plugin_data['Version'];
                                    }
                                }
                            }
                            echo esc_html($plugin_version);
                        ?></span>
                    </div>
                    <div class="resbs-info-item">
                        <span class="resbs-info-label"><?php esc_html_e('WordPress Version:', 'realestate-booking-suite'); ?></span>
                        <span class="resbs-info-value"><?php echo esc_html(get_bloginfo('version')); ?></span>
                    </div>
                    <div class="resbs-info-item">
                        <span class="resbs-info-label"><?php esc_html_e('PHP Version:', 'realestate-booking-suite'); ?></span>
                        <span class="resbs-info-value"><?php echo esc_html(PHP_VERSION); ?></span>
                    </div>
                    <div class="resbs-info-item">
                        <span class="resbs-info-label"><?php esc_html_e('Database Version:', 'realestate-booking-suite'); ?></span>
                        <span class="resbs-info-value"><?php 
                            global $wpdb;
                            if (isset($wpdb) && is_object($wpdb) && method_exists($wpdb, 'db_version')) {
                                $db_version = $wpdb->db_version();
                                echo esc_html($db_version ? $db_version : __('Unknown', 'realestate-booking-suite'));
                            } else {
                                esc_html_e('Unknown', 'realestate-booking-suite');
                            }
                        ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get total bookings
     * 
     * Security: Private method - only called from widget methods that have capability checks
     */
    private function get_total_bookings() {
        // This would integrate with your booking system
        // For now, return a placeholder
        return 0;
    }

    /**
     * Get total revenue
     * 
     * Security: Private method - only called from widget methods that have capability checks
     */
    private function get_total_revenue() {
        // This would integrate with your payment system
        // For now, return a placeholder
        return 0;
    }

    /**
     * Format currency
     * 
     * Security: Validates and sanitizes all inputs before formatting
     */
    private function format_currency($amount) {
        // Validate and sanitize amount to ensure it's numeric
        if (!is_numeric($amount)) {
            $amount = 0;
        }
        $amount = abs(floatval($amount));
        $formatted_price = number_format($amount, 2);
        
        // Get currency symbol and position from options (consistent with other files)
        $currency_symbol_raw = get_option('resbs_currency_symbol', '$');
        $currency_symbol = sanitize_text_field($currency_symbol_raw);
        $currency_position_raw = get_option('resbs_currency_position', 'before');
        $currency_position = sanitize_key($currency_position_raw);
        
        // Escape currency symbol for output
        $currency_symbol = esc_html($currency_symbol);
        
        // Format based on position - validate position value
        $allowed_positions = array('before', 'before_space', 'after', 'after_space');
        if (!in_array($currency_position, $allowed_positions, true)) {
            $currency_position = 'before';
        }
        
        // Format based on position
        if ($currency_position === 'before') {
            return $currency_symbol . $formatted_price;
        } elseif ($currency_position === 'before_space') {
            return $currency_symbol . ' ' . $formatted_price;
        } elseif ($currency_position === 'after_space') {
            return $formatted_price . ' ' . $currency_symbol;
        } else {
            // Default to 'after'
            return $formatted_price . $currency_symbol;
        }
    }
}
