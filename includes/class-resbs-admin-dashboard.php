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
     */
    public function enqueue_dashboard_assets($hook) {
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
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('resbs_dashboard_nonce'),
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
     */
    public function add_dashboard_widgets() {
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
     */
    public function property_stats_widget() {
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
     */
    public function recent_properties_widget() {
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
                        $price = get_post_meta($property->ID, '_property_price', true);
                        $bedrooms = get_post_meta($property->ID, '_property_bedrooms', true);
                        $bathrooms = get_post_meta($property->ID, '_property_bathrooms', true);
                        $area = get_post_meta($property->ID, '_property_area_sqft', true);
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
                                    <?php if ($price): ?>
                                        <span class="resbs-price"><?php echo esc_html($this->format_currency($price)); ?></span>
                                    <?php endif; ?>
                                    <?php if ($bedrooms): ?>
                                        <span class="resbs-bedrooms"><?php echo esc_html($bedrooms); ?> <?php esc_html_e('bed', 'realestate-booking-suite'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($bathrooms): ?>
                                        <span class="resbs-bathrooms"><?php echo esc_html($bathrooms); ?> <?php esc_html_e('bath', 'realestate-booking-suite'); ?></span>
                                    <?php endif; ?>
                                    <?php if ($area): ?>
                                        <span class="resbs-area"><?php echo esc_html($area); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="resbs-property-status">
                                    <span class="resbs-status resbs-status-<?php echo esc_attr($property->post_status); ?>">
                                        <?php echo esc_html(ucfirst($property->post_status)); ?>
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
     */
    public function quick_actions_widget() {
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
                        <span class="resbs-info-value"><?php echo esc_html($GLOBALS['wpdb']->db_version()); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Get total bookings
     */
    private function get_total_bookings() {
        // This would integrate with your booking system
        // For now, return a placeholder
        return 0;
    }

    /**
     * Get total revenue
     */
    private function get_total_revenue() {
        // This would integrate with your payment system
        // For now, return a placeholder
        return 0;
    }

    /**
     * Format currency
     */
    private function format_currency($amount) {
        // Sanitize amount to ensure it's numeric
        $amount = floatval($amount);
        
        // Sanitize currency option
        $currency = sanitize_text_field(get_option('resbs_default_currency', 'USD'));
        $symbol = '$';
        
        switch ($currency) {
            case 'EUR':
                $symbol = '€';
                break;
            case 'GBP':
                $symbol = '£';
                break;
            case 'JPY':
                $symbol = '¥';
                break;
        }
        
        return $symbol . number_format($amount, 2);
    }
}
