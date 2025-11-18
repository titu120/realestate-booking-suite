<?php
/**
 * Settings Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only enqueue on settings pages
        if (strpos($hook, 'resbs') === false) {
            return;
        }
        
        // Enqueue quick actions CSS
        wp_enqueue_style(
            'resbs-settings-quick-actions',
            RESBS_URL . 'assets/css/settings-quick-actions.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue quick actions JS
        wp_enqueue_script(
            'resbs-settings-quick-actions',
            RESBS_URL . 'assets/js/settings-quick-actions.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script with data
        global $wpdb;
        $quick_actions = get_option('resbs_quick_actions', array());
        if (!is_array($quick_actions)) {
            $quick_actions = array();
        }
        
        wp_localize_script('resbs-settings-quick-actions', 'resbs_quick_actions', array(
            'actionIndex' => absint(count($quick_actions)),
            'labels' => array(
                'action_title' => esc_js(__('Action Title', 'realestate-booking-suite')),
                'action_title_placeholder' => esc_js(__('e.g., Send Message', 'realestate-booking-suite')),
                'icon_class' => esc_js(__('Icon Class', 'realestate-booking-suite')),
                'icon_class_placeholder' => esc_js(__('e.g., fas fa-envelope', 'realestate-booking-suite')),
                'icon_class_description' => esc_js(__('FontAwesome icon class (e.g., fas fa-envelope, fas fa-share-alt)', 'realestate-booking-suite')),
                'js_action' => esc_js(__('JavaScript Action', 'realestate-booking-suite')),
                'js_action_placeholder' => esc_js(__('e.g., openContactModal()', 'realestate-booking-suite')),
                'js_action_description' => esc_js(__('JavaScript function to call when clicked', 'realestate-booking-suite')),
                'button_style' => esc_js(__('Button Style Classes', 'realestate-booking-suite')),
                'button_style_placeholder' => esc_js(__('e.g., bg-gray-700 text-white hover:bg-gray-800', 'realestate-booking-suite')),
                'button_style_description' => esc_js(__('Tailwind CSS classes for button styling', 'realestate-booking-suite')),
                'enable_action' => esc_js(__('Enable this action', 'realestate-booking-suite')),
                'remove_action' => esc_js(__('Remove Action', 'realestate-booking-suite'))
            )
        ));
    }
    
    /**
     * Add unified admin menu structure
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            esc_html__('RealEstate Booking Suite', 'realestate-booking-suite'),
            esc_html__('RealEstate Booking Suite', 'realestate-booking-suite'),
            'manage_options',
            'resbs-main-menu',
            array($this, 'dashboard_page_callback'),
            'dashicons-building',
            25
        );
        
        // Dashboard submenu (same as main page)
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Dashboard', 'realestate-booking-suite'),
            esc_html__('Dashboard', 'realestate-booking-suite'),
            'manage_options',
            'resbs-main-menu',
            array($this, 'dashboard_page_callback')
        );
        
        // General Settings
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('General Settings', 'realestate-booking-suite'),
            esc_html__('General Settings', 'realestate-booking-suite'),
            'manage_options',
            'resbs-general-settings',
            array($this, 'general_settings_callback')
        );
        
        // Badge Settings
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Badge Settings', 'realestate-booking-suite'),
            esc_html__('Badge Settings', 'realestate-booking-suite'),
            'manage_options',
            'resbs-badge-settings',
            array($this, 'badge_settings_callback')
        );
        
        // Map Settings
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Map Settings', 'realestate-booking-suite'),
            esc_html__('Map Settings', 'realestate-booking-suite'),
            'manage_options',
            'resbs-map-settings',
            array($this, 'map_settings_callback')
        );
        
        // Contact Settings
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Contact Settings', 'realestate-booking-suite'),
            esc_html__('Contact Settings', 'realestate-booking-suite'),
            'manage_options',
            'resbs-contact-settings',
            array($this, 'contact_settings_callback')
        );
        
        // Email Settings
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Email Settings', 'realestate-booking-suite'),
            esc_html__('Email Settings', 'realestate-booking-suite'),
            'manage_options',
            'resbs-email-settings',
            array($this, 'email_settings_callback')
        );
        
        // Appearance Settings
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Appearance Settings', 'realestate-booking-suite'),
            esc_html__('Appearance Settings', 'realestate-booking-suite'),
            'manage_options',
            'resbs-appearance-settings',
            array($this, 'appearance_settings_callback')
        );
        
        // Quick Actions Settings
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Quick Actions', 'realestate-booking-suite'),
            esc_html__('Quick Actions', 'realestate-booking-suite'),
            'manage_options',
            'resbs-quick-actions-settings',
            array($this, 'quick_actions_settings_callback')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // General Settings
        register_setting('resbs_general_settings', 'resbs_default_currency');
        register_setting('resbs_general_settings', 'resbs_map_api_key');
        
        // Mortgage Calculator Settings
        register_setting('resbs_general_settings', 'resbs_mortgage_loan_terms');
        register_setting('resbs_general_settings', 'resbs_mortgage_default_loan_term');
        register_setting('resbs_general_settings', 'resbs_mortgage_default_down_payment');
        register_setting('resbs_general_settings', 'resbs_mortgage_default_interest_rate');
        
        // Badge Settings
        register_setting('resbs_badge_settings', 'resbs_badge_color');
        register_setting('resbs_badge_settings', 'resbs_badge_text_color');
        register_setting('resbs_badge_settings', 'resbs_badge_position');
        
        // Map Settings
        register_setting('resbs_map_settings', 'resbs_google_maps_api_key');
        register_setting('resbs_map_settings', 'resbs_map_zoom_level');
        register_setting('resbs_map_settings', 'resbs_map_center_lat');
        register_setting('resbs_map_settings', 'resbs_map_center_lng');
        
        // Contact Settings
        register_setting('resbs_contact_settings', 'resbs_contact_phone');
        register_setting('resbs_contact_settings', 'resbs_contact_email');
        register_setting('resbs_contact_settings', 'resbs_contact_address');
        
        // Email Settings
        register_setting('resbs_email_settings', 'resbs_booking_notification_email');
        register_setting('resbs_email_settings', 'resbs_submission_notification_email');
        register_setting('resbs_email_settings', 'resbs_booking_email_subject');
        register_setting('resbs_email_settings', 'resbs_submission_email_subject');
        
        // Appearance Settings
        register_setting('resbs_appearance_settings', 'resbs_property_card_color');
        register_setting('resbs_appearance_settings', 'resbs_button_color');
        register_setting('resbs_appearance_settings', 'resbs_primary_color');
        
        // Quick Actions Settings
        register_setting('resbs_quick_actions_settings', 'resbs_quick_actions');
    }
    
    /**
     * Dashboard page callback
     */
    public function dashboard_page_callback() {
        // Get property counts
        $property_counts = wp_count_posts('property');
        $total_properties = $property_counts->publish;
        $draft_properties = $property_counts->draft;
        $pending_properties = $property_counts->pending;
        $private_properties = $property_counts->private;
        
        // Get recent properties
        $recent_properties = get_posts(array(
            'post_type' => 'property',
            'posts_per_page' => 5,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        // Get system info
        $wp_version = get_bloginfo('version');
        $php_version = PHP_VERSION;
        $plugin_version = '1.0.0';
        ?>
        <div class="wrap resbs-admin-wrap">
            <!-- Welcome Header -->
            <div class="resbs-welcome-header">
                <div class="resbs-welcome-content">
                    <h1 class="resbs-welcome-title">
                        <span class="resbs-welcome-icon">🏢</span>
                        <?php esc_html_e('RealEstate Booking Suite', 'realestate-booking-suite'); ?>
                    </h1>
                    <p class="resbs-welcome-subtitle"><?php esc_html_e('Professional real estate booking and management system', 'realestate-booking-suite'); ?></p>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="resbs-stats-overview">
                <div class="resbs-stat-card resbs-stat-primary">
                    <div class="resbs-stat-icon">
                        <span class="dashicons dashicons-building"></span>
                    </div>
                    <div class="resbs-stat-content">
                        <div class="resbs-stat-number"><?php echo esc_html($total_properties); ?></div>
                        <div class="resbs-stat-label"><?php esc_html_e('Total Properties', 'realestate-booking-suite'); ?></div>
                        <div class="resbs-stat-change">
                            <span class="resbs-stat-change-positive">+12%</span>
                            <span class="resbs-stat-change-text"><?php esc_html_e('from last month', 'realestate-booking-suite'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-stat-card resbs-stat-warning">
                    <div class="resbs-stat-icon">
                        <span class="dashicons dashicons-edit"></span>
                    </div>
                    <div class="resbs-stat-content">
                        <div class="resbs-stat-number"><?php echo esc_html($draft_properties); ?></div>
                        <div class="resbs-stat-label"><?php esc_html_e('Draft Properties', 'realestate-booking-suite'); ?></div>
                        <div class="resbs-stat-change">
                            <span class="resbs-stat-change-text"><?php esc_html_e('Needs attention', 'realestate-booking-suite'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-stat-card resbs-stat-info">
                    <div class="resbs-stat-icon">
                        <span class="dashicons dashicons-clock"></span>
                    </div>
                    <div class="resbs-stat-content">
                        <div class="resbs-stat-number"><?php echo esc_html($pending_properties); ?></div>
                        <div class="resbs-stat-label"><?php esc_html_e('Pending Review', 'realestate-booking-suite'); ?></div>
                        <div class="resbs-stat-change">
                            <span class="resbs-stat-change-text"><?php esc_html_e('Awaiting approval', 'realestate-booking-suite'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-stat-card resbs-stat-success">
                    <div class="resbs-stat-icon">
                        <span class="dashicons dashicons-lock"></span>
                    </div>
                    <div class="resbs-stat-content">
                        <div class="resbs-stat-number"><?php echo esc_html($private_properties); ?></div>
                        <div class="resbs-stat-label"><?php esc_html_e('Private Properties', 'realestate-booking-suite'); ?></div>
                        <div class="resbs-stat-change">
                            <span class="resbs-stat-change-text"><?php esc_html_e('Confidential listings', 'realestate-booking-suite'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="resbs-dashboard-grid">
                <!-- Quick Actions -->
                <div class="resbs-dashboard-card resbs-card-wide">
                    <div class="resbs-card-body">
                        <div class="resbs-quick-actions-grid">
                            <a href="<?php echo esc_url(admin_url('edit.php?post_type=property')); ?>" class="resbs-quick-action">
                                <div class="resbs-quick-action-icon">
                                    <span class="dashicons dashicons-list-view"></span>
                                </div>
                                <div class="resbs-quick-action-content">
                                    <h3><?php esc_html_e('Manage Properties', 'realestate-booking-suite'); ?></h3>
                                    <p><?php esc_html_e('View and edit all properties', 'realestate-booking-suite'); ?></p>
                                </div>
                            </a>
                            
                            <a href="<?php echo esc_url(admin_url('admin.php?page=resbs-general-settings')); ?>" class="resbs-quick-action">
                                <div class="resbs-quick-action-icon">
                                    <span class="dashicons dashicons-admin-settings"></span>
                                </div>
                                <div class="resbs-quick-action-content">
                                    <h3><?php esc_html_e('Settings', 'realestate-booking-suite'); ?></h3>
                                    <p><?php esc_html_e('Configure plugin options', 'realestate-booking-suite'); ?></p>
                                </div>
                            </a>
                            
                            <a href="<?php echo esc_url(admin_url('admin.php?page=resbs-contact-messages')); ?>" class="resbs-quick-action">
                                <div class="resbs-quick-action-icon">
                                    <span class="dashicons dashicons-email-alt"></span>
                                </div>
                                <div class="resbs-quick-action-content">
                                    <h3><?php esc_html_e('Messages', 'realestate-booking-suite'); ?></h3>
                                    <p><?php esc_html_e('View contact inquiries', 'realestate-booking-suite'); ?></p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Properties -->
                <div class="resbs-dashboard-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title">
                            <span class="dashicons dashicons-clock"></span>
                            <?php esc_html_e('Recent Properties', 'realestate-booking-suite'); ?>
                        </h2>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=property')); ?>" class="resbs-card-action">
                            <?php esc_html_e('View All', 'realestate-booking-suite'); ?>
                        </a>
                    </div>
                    <div class="resbs-card-body">
                        <?php if (!empty($recent_properties)): ?>
                            <div class="resbs-recent-properties">
                                <?php foreach ($recent_properties as $property): ?>
                                    <div class="resbs-recent-property">
                                        <div class="resbs-property-thumbnail">
                                            <?php 
                                            $thumbnail = get_the_post_thumbnail($property->ID, 'thumbnail');
                                            if ($thumbnail): 
                                                echo wp_kses_post($thumbnail);
                                            else: ?>
                                                <div class="resbs-no-thumbnail">
                                                    <span class="dashicons dashicons-format-image"></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="resbs-property-info">
                                            <h4><a href="<?php echo esc_url(get_edit_post_link($property->ID)); ?>"><?php echo esc_html($property->post_title); ?></a></h4>
                                            <p class="resbs-property-date"><?php echo esc_html(human_time_diff(strtotime($property->post_date), current_time('timestamp')) . ' ' . esc_html__('ago', 'realestate-booking-suite')); ?></p>
                                        </div>
                                        <div class="resbs-property-status">
                                            <span class="resbs-status-badge resbs-status-published"><?php esc_html_e('Published', 'realestate-booking-suite'); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="resbs-empty-state">
                                <span class="dashicons dashicons-building"></span>
                                <p><?php esc_html_e('No properties found. Create your first property!', 'realestate-booking-suite'); ?></p>
                                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=property')); ?>" class="resbs-btn resbs-btn-primary">
                                    <?php esc_html_e('Add Property', 'realestate-booking-suite'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- System Status -->
                <div class="resbs-dashboard-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title">
                            <span class="dashicons dashicons-performance"></span>
                            <?php esc_html_e('System Status', 'realestate-booking-suite'); ?>
                        </h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-system-status">
                            <div class="resbs-status-item">
                                <div class="resbs-status-label"><?php esc_html_e('Plugin Version', 'realestate-booking-suite'); ?></div>
                                <div class="resbs-status-value"><?php echo esc_html($plugin_version); ?></div>
                            </div>
                            <div class="resbs-status-item">
                                <div class="resbs-status-label"><?php esc_html_e('WordPress Version', 'realestate-booking-suite'); ?></div>
                                <div class="resbs-status-value"><?php echo esc_html($wp_version); ?></div>
                            </div>
                            <div class="resbs-status-item">
                                <div class="resbs-status-label"><?php esc_html_e('PHP Version', 'realestate-booking-suite'); ?></div>
                                <div class="resbs-status-value"><?php echo esc_html($php_version); ?></div>
                            </div>
                            <div class="resbs-status-item">
                                <div class="resbs-status-label"><?php esc_html_e('Database Status', 'realestate-booking-suite'); ?></div>
                                <div class="resbs-status-value resbs-status-good">
                                    <span class="dashicons dashicons-yes-alt"></span>
                                    <?php esc_html_e('Healthy', 'realestate-booking-suite'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plugin Information -->
            <div class="resbs-info-section">
                <div class="resbs-info-card">
                    <div class="resbs-info-header">
                        <h2><?php esc_html_e('About RealEstate Booking Suite', 'realestate-booking-suite'); ?></h2>
                        <p><?php esc_html_e('A comprehensive WordPress plugin for managing real estate properties with advanced booking functionality, WooCommerce integration, and Elementor support.', 'realestate-booking-suite'); ?></p>
                    </div>
                    <div class="resbs-features-grid">
                        <div class="resbs-feature">
                            <span class="dashicons dashicons-admin-post"></span>
                            <h3><?php esc_html_e('Property Management', 'realestate-booking-suite'); ?></h3>
                            <p><?php esc_html_e('Custom post types for comprehensive property management', 'realestate-booking-suite'); ?></p>
                        </div>
                        <div class="resbs-feature">
                            <span class="dashicons dashicons-cart"></span>
                            <h3><?php esc_html_e('WooCommerce Integration', 'realestate-booking-suite'); ?></h3>
                            <p><?php esc_html_e('Seamless booking and payment processing', 'realestate-booking-suite'); ?></p>
                        </div>
                        <div class="resbs-feature">
                            <span class="dashicons dashicons-layout"></span>
                            <h3><?php esc_html_e('Elementor Widgets', 'realestate-booking-suite'); ?></h3>
                            <p><?php esc_html_e('Easy page building with custom widgets', 'realestate-booking-suite'); ?></p>
                        </div>
                        <div class="resbs-feature">
                            <span class="dashicons dashicons-search"></span>
                            <h3><?php esc_html_e('Advanced Search', 'realestate-booking-suite'); ?></h3>
                            <p><?php esc_html_e('Powerful filtering and search capabilities', 'realestate-booking-suite'); ?></p>
                        </div>
                        <div class="resbs-feature">
                            <span class="dashicons dashicons-location-alt"></span>
                            <h3><?php esc_html_e('Google Maps', 'realestate-booking-suite'); ?></h3>
                            <p><?php esc_html_e('Interactive maps and location services', 'realestate-booking-suite'); ?></p>
                        </div>
                        <div class="resbs-feature">
                            <span class="dashicons dashicons-smartphone"></span>
                            <h3><?php esc_html_e('Responsive Design', 'realestate-booking-suite'); ?></h3>
                            <p><?php esc_html_e('Optimized for all devices and screen sizes', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * General settings callback
     */
    public function general_settings_callback() {
        ?>
        <div class="wrap resbs-admin-wrap">
            <div class="resbs-admin-header">
                <h1 class="resbs-admin-title"><?php esc_html_e('General Settings', 'realestate-booking-suite'); ?></h1>
                <p class="resbs-admin-subtitle"><?php esc_html_e('Configure basic plugin settings', 'realestate-booking-suite'); ?></p>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('resbs_general_settings');
                do_settings_sections('resbs_general_settings');
                ?>
                
                <div class="resbs-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title"><?php esc_html_e('Currency Settings', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_default_currency"><?php esc_html_e('Default Currency', 'realestate-booking-suite'); ?></label>
                            <select id="resbs_default_currency" name="resbs_default_currency">
                                <option value="USD" <?php selected(get_option('resbs_default_currency'), 'USD'); ?>><?php esc_html_e('US Dollar (USD)', 'realestate-booking-suite'); ?></option>
                                <option value="EUR" <?php selected(get_option('resbs_default_currency'), 'EUR'); ?>><?php esc_html_e('Euro (EUR)', 'realestate-booking-suite'); ?></option>
                                <option value="GBP" <?php selected(get_option('resbs_default_currency'), 'GBP'); ?>><?php esc_html_e('British Pound (GBP)', 'realestate-booking-suite'); ?></option>
                                <option value="CAD" <?php selected(get_option('resbs_default_currency'), 'CAD'); ?>><?php esc_html_e('Canadian Dollar (CAD)', 'realestate-booking-suite'); ?></option>
                                <option value="AUD" <?php selected(get_option('resbs_default_currency'), 'AUD'); ?>><?php esc_html_e('Australian Dollar (AUD)', 'realestate-booking-suite'); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e('Select the default currency for property prices', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title"><?php esc_html_e('Google Maps Settings', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_map_api_key"><?php esc_html_e('Google Maps API Key', 'realestate-booking-suite'); ?></label>
                            <input type="text" id="resbs_map_api_key" name="resbs_map_api_key" value="<?php echo esc_attr(get_option('resbs_map_api_key')); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Enter your Google Maps API key for map functionality', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title"><?php esc_html_e('Mortgage Calculator Settings', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_mortgage_loan_terms"><?php esc_html_e('Available Loan Terms', 'realestate-booking-suite'); ?></label>
                            <textarea id="resbs_mortgage_loan_terms" name="resbs_mortgage_loan_terms" rows="4" class="large-text"><?php echo esc_textarea(get_option('resbs_mortgage_loan_terms', '10,15,20,25')); ?></textarea>
                            <p class="description"><?php esc_html_e('Enter each loan term on a new line (e.g., 10, 15, 20, 25)', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_mortgage_default_loan_term"><?php esc_html_e('Default Loan Term (Years)', 'realestate-booking-suite'); ?></label>
                            <input type="number" id="resbs_mortgage_default_loan_term" name="resbs_mortgage_default_loan_term" value="<?php echo esc_attr(get_option('resbs_mortgage_default_loan_term', '10')); ?>" min="1" max="50" />
                            <p class="description"><?php esc_html_e('Default loan term in years', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_mortgage_default_down_payment"><?php esc_html_e('Default Down Payment (%)', 'realestate-booking-suite'); ?></label>
                            <input type="number" id="resbs_mortgage_default_down_payment" name="resbs_mortgage_default_down_payment" value="<?php echo esc_attr(get_option('resbs_mortgage_default_down_payment', '20')); ?>" min="0" max="100" />
                            <p class="description"><?php esc_html_e('Default down payment percentage', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_mortgage_default_interest_rate"><?php esc_html_e('Default Interest Rate (%)', 'realestate-booking-suite'); ?></label>
                            <input type="number" id="resbs_mortgage_default_interest_rate" name="resbs_mortgage_default_interest_rate" value="<?php echo esc_attr(get_option('resbs_mortgage_default_interest_rate', '6.5')); ?>" step="0.1" min="0" max="50" />
                            <p class="description"><?php esc_html_e('Default interest rate percentage', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Badge settings callback
     */
    public function badge_settings_callback() {
        ?>
        <div class="wrap resbs-admin-wrap">
            <div class="resbs-admin-header">
                <h1 class="resbs-admin-title"><?php esc_html_e('Badge Settings', 'realestate-booking-suite'); ?></h1>
                <p class="resbs-admin-subtitle"><?php esc_html_e('Configure property badge appearance', 'realestate-booking-suite'); ?></p>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('resbs_badge_settings');
                do_settings_sections('resbs_badge_settings');
                ?>
                
                <div class="resbs-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title"><?php esc_html_e('Badge Colors', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_badge_color"><?php esc_html_e('Badge Background Color', 'realestate-booking-suite'); ?></label>
                            <input type="color" id="resbs_badge_color" name="resbs_badge_color" value="<?php echo esc_attr(get_option('resbs_badge_color', '#0073aa')); ?>" />
                            <p class="description"><?php esc_html_e('Background color for property status badges', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_badge_text_color"><?php esc_html_e('Badge Text Color', 'realestate-booking-suite'); ?></label>
                            <input type="color" id="resbs_badge_text_color" name="resbs_badge_text_color" value="<?php echo esc_attr(get_option('resbs_badge_text_color', '#ffffff')); ?>" />
                            <p class="description"><?php esc_html_e('Text color for property status badges', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title"><?php esc_html_e('Badge Position', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_badge_position"><?php esc_html_e('Badge Position', 'realestate-booking-suite'); ?></label>
                            <select id="resbs_badge_position" name="resbs_badge_position">
                                <option value="top-left" <?php selected(get_option('resbs_badge_position'), 'top-left'); ?>><?php esc_html_e('Top Left', 'realestate-booking-suite'); ?></option>
                                <option value="top-right" <?php selected(get_option('resbs_badge_position'), 'top-right'); ?>><?php esc_html_e('Top Right', 'realestate-booking-suite'); ?></option>
                                <option value="bottom-left" <?php selected(get_option('resbs_badge_position'), 'bottom-left'); ?>><?php esc_html_e('Bottom Left', 'realestate-booking-suite'); ?></option>
                                <option value="bottom-right" <?php selected(get_option('resbs_badge_position'), 'bottom-right'); ?>><?php esc_html_e('Bottom Right', 'realestate-booking-suite'); ?></option>
                            </select>
                            <p class="description"><?php esc_html_e('Position of badges on property cards', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Map settings callback
     */
    public function map_settings_callback() {
        ?>
        <div class="wrap resbs-admin-wrap">
            <div class="resbs-admin-header">
                <h1 class="resbs-admin-title"><?php esc_html_e('Map Settings', 'realestate-booking-suite'); ?></h1>
                <p class="resbs-admin-subtitle"><?php esc_html_e('Configure Google Maps integration', 'realestate-booking-suite'); ?></p>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('resbs_map_settings');
                do_settings_sections('resbs_map_settings');
                ?>
                
                <div class="resbs-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title"><?php esc_html_e('Google Maps API Configuration', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_google_maps_api_key"><?php esc_html_e('Google Maps API Key', 'realestate-booking-suite'); ?> <span style="color: red;">*</span></label>
                            <input type="text" id="resbs_google_maps_api_key" name="resbs_google_maps_api_key" value="<?php echo esc_attr(get_option('resbs_google_maps_api_key', '')); ?>" class="regular-text" placeholder="AIzaSy..." />
                            <p class="description">
                                <?php esc_html_e('Enter your Google Maps API key. ', 'realestate-booking-suite'); ?>
                                <a href="<?php echo esc_url('https://console.cloud.google.com/google/maps-apis/credentials'); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Get your API key here', 'realestate-booking-suite'); ?></a>
                                <br>
                                <strong><?php esc_html_e('Required APIs:', 'realestate-booking-suite'); ?></strong> <?php esc_html_e('Maps JavaScript API, Places API (optional)', 'realestate-booking-suite'); ?>
                            </p>
                            <?php if (empty(get_option('resbs_google_maps_api_key', ''))): ?>
                                <div class="notice notice-warning inline" style="margin-top: 10px;">
                                    <p><?php esc_html_e('⚠️ Google Maps will not work without an API key. Please add your API key to enable interactive maps.', 'realestate-booking-suite'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title"><?php esc_html_e('Map Configuration', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_map_zoom_level"><?php esc_html_e('Default Zoom Level', 'realestate-booking-suite'); ?></label>
                            <input type="number" id="resbs_map_zoom_level" name="resbs_map_zoom_level" value="<?php echo esc_attr(get_option('resbs_map_zoom_level', '10')); ?>" min="1" max="20" />
                            <p class="description"><?php esc_html_e('Default zoom level for maps when viewing multiple properties (1-20). Individual property maps will auto-fit to show the property location.', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group" style="padding: 15px; background: #f0f9ff; border-left: 4px solid #3b82f6; border-radius: 4px; margin-top: 20px;">
                            <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #1e40af;">
                                <i class="dashicons dashicons-info" style="vertical-align: middle;"></i>
                                <?php esc_html_e('Property Location Coordinates', 'realestate-booking-suite'); ?>
                            </h3>
                            <p style="margin: 0; color: #1e3a8a; font-size: 13px;">
                                <?php esc_html_e('Each property has its own latitude and longitude coordinates. Set these individually in each property\'s "Location" tab when editing properties. The map will automatically center on your properties based on their individual coordinates.', 'realestate-booking-suite'); ?>
                            </p>
                        </div>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Contact settings callback
     */
    public function contact_settings_callback() {
        ?>
        <div class="wrap resbs-admin-wrap">
            <div class="resbs-admin-header">
                <h1 class="resbs-admin-title"><?php esc_html_e('Contact Settings', 'realestate-booking-suite'); ?></h1>
                <p class="resbs-admin-subtitle"><?php esc_html_e('Configure contact information', 'realestate-booking-suite'); ?></p>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('resbs_contact_settings');
                do_settings_sections('resbs_contact_settings');
                ?>
                
                <div class="resbs-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title"><?php esc_html_e('Contact Information', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_contact_phone"><?php esc_html_e('Phone Number', 'realestate-booking-suite'); ?></label>
                            <input type="tel" id="resbs_contact_phone" name="resbs_contact_phone" value="<?php echo esc_attr(get_option('resbs_contact_phone')); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Contact phone number', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_contact_email"><?php esc_html_e('Email Address', 'realestate-booking-suite'); ?></label>
                            <input type="email" id="resbs_contact_email" name="resbs_contact_email" value="<?php echo esc_attr(get_option('resbs_contact_email')); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Contact email address', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_contact_address"><?php esc_html_e('Address', 'realestate-booking-suite'); ?></label>
                            <textarea id="resbs_contact_address" name="resbs_contact_address" rows="3" class="large-text"><?php echo esc_textarea(get_option('resbs_contact_address')); ?></textarea>
                            <p class="description"><?php esc_html_e('Contact address', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Email settings callback
     */
    public function email_settings_callback() {
        ?>
        <div class="wrap resbs-admin-wrap">
            <div class="resbs-admin-header">
                <h1 class="resbs-admin-title"><?php esc_html_e('Email Settings', 'realestate-booking-suite'); ?></h1>
                <p class="resbs-admin-subtitle"><?php esc_html_e('Configure email notifications', 'realestate-booking-suite'); ?></p>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('resbs_email_settings');
                do_settings_sections('resbs_email_settings');
                ?>
                
                <div class="resbs-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title"><?php esc_html_e('Notification Emails', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_booking_notification_email"><?php esc_html_e('Booking Notification Email', 'realestate-booking-suite'); ?></label>
                            <input type="email" id="resbs_booking_notification_email" name="resbs_booking_notification_email" value="<?php echo esc_attr(get_option('resbs_booking_notification_email')); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Email address to receive booking notifications', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_submission_notification_email"><?php esc_html_e('Property Submission Email', 'realestate-booking-suite'); ?></label>
                            <input type="email" id="resbs_submission_notification_email" name="resbs_submission_notification_email" value="<?php echo esc_attr(get_option('resbs_submission_notification_email')); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Email address to receive property submission notifications', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title"><?php esc_html_e('Email Templates', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_booking_email_subject"><?php esc_html_e('Booking Email Subject', 'realestate-booking-suite'); ?></label>
                            <input type="text" id="resbs_booking_email_subject" name="resbs_booking_email_subject" value="<?php echo esc_attr(get_option('resbs_booking_email_subject', 'New Property Booking')); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Subject line for booking notification emails', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_submission_email_subject"><?php esc_html_e('Submission Email Subject', 'realestate-booking-suite'); ?></label>
                            <input type="text" id="resbs_submission_email_subject" name="resbs_submission_email_subject" value="<?php echo esc_attr(get_option('resbs_submission_email_subject', 'New Property Submission')); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e('Subject line for property submission emails', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Appearance settings callback
     */
    public function appearance_settings_callback() {
        ?>
        <div class="wrap resbs-admin-wrap">
            <div class="resbs-admin-header">
                <h1 class="resbs-admin-title"><?php esc_html_e('Appearance Settings', 'realestate-booking-suite'); ?></h1>
                <p class="resbs-admin-subtitle"><?php esc_html_e('Customize plugin appearance', 'realestate-booking-suite'); ?></p>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('resbs_appearance_settings');
                do_settings_sections('resbs_appearance_settings');
                ?>
                
                <div class="resbs-card">
                    <div class="resbs-card-header">
                        <h2 class="resbs-card-title"><?php esc_html_e('Color Scheme', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_primary_color"><?php esc_html_e('Primary Color', 'realestate-booking-suite'); ?></label>
                            <input type="color" id="resbs_primary_color" name="resbs_primary_color" value="<?php echo esc_attr(get_option('resbs_primary_color', '#0073aa')); ?>" />
                            <p class="description"><?php esc_html_e('Primary color for the plugin', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_property_card_color"><?php esc_html_e('Property Card Background', 'realestate-booking-suite'); ?></label>
                            <input type="color" id="resbs_property_card_color" name="resbs_property_card_color" value="<?php echo esc_attr(get_option('resbs_property_card_color', '#ffffff')); ?>" />
                            <p class="description"><?php esc_html_e('Background color for property cards', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_button_color"><?php esc_html_e('Button Color', 'realestate-booking-suite'); ?></label>
                            <input type="color" id="resbs_button_color" name="resbs_button_color" value="<?php echo esc_attr(get_option('resbs_button_color', '#0073aa')); ?>" />
                            <p class="description"><?php esc_html_e('Color for buttons and links', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Quick Actions Settings callback
     */
    public function quick_actions_settings_callback() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'realestate-booking-suite'));
        }
        
        // Handle form submission
        if (isset($_POST['submit']) && isset($_POST['resbs_quick_actions_nonce'])) {
            // Verify nonce
            if (!wp_verify_nonce($_POST['resbs_quick_actions_nonce'], 'resbs_quick_actions_settings')) {
                wp_die(esc_html__('Security check failed. Please try again.', 'realestate-booking-suite'));
            }
            
            // Check user permissions again (defense in depth)
            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('You do not have sufficient permissions to perform this action.', 'realestate-booking-suite'));
            }
            
            if (isset($_POST['resbs_quick_actions'])) {
                $actions = array();
                foreach ($_POST['resbs_quick_actions'] as $index => $action) {
                    if (!empty($action['title'])) {
                        $actions[] = array(
                            'title' => sanitize_text_field($action['title']),
                            'icon' => sanitize_text_field($action['icon']),
                            'action' => sanitize_text_field($action['action']),
                            'style' => sanitize_text_field($action['style']),
                            'enabled' => isset($action['enabled']) ? 1 : 0
                        );
                    }
                }
                update_option('resbs_quick_actions', $actions);
                echo '<div class="notice notice-success"><p>' . esc_html__('Quick Actions settings saved successfully!', 'realestate-booking-suite') . '</p></div>';
            }
        }
        
        $quick_actions = get_option('resbs_quick_actions', array());
        if (empty($quick_actions)) {
            $quick_actions = array(
                array(
                    'title' => 'Send Message',
                    'icon' => 'fas fa-envelope',
                    'action' => 'openContactModal()',
                    'style' => 'bg-gray-700 text-white hover:bg-gray-800',
                    'enabled' => 1
                ),
                array(
                    'title' => 'Share Property',
                    'icon' => 'fas fa-share-alt',
                    'action' => 'shareProperty()',
                    'style' => 'border-2 border-emerald-500 text-emerald-500 hover:bg-emerald-50',
                    'enabled' => 1
                )
            );
        }
        ?>
        <div class="wrap resbs-admin-wrap">
            <h1><?php esc_html_e('Quick Actions Settings', 'realestate-booking-suite'); ?></h1>
            <p><?php esc_html_e('Configure the quick action buttons that appear on property pages.', 'realestate-booking-suite'); ?></p>
            
            <form method="post" action="">
                <?php wp_nonce_field('resbs_quick_actions_settings', 'resbs_quick_actions_nonce'); ?>
                
                <div class="resbs-settings-section">
                    <h2><?php esc_html_e('Quick Actions Configuration', 'realestate-booking-suite'); ?></h2>
                    
                    <div id="quick-actions-container">
                        <?php foreach ($quick_actions as $index => $action): ?>
                        <div class="quick-action-item" data-index="<?php echo esc_attr($index); ?>">
                            <div class="resbs-form-group">
                                <label><?php esc_html_e('Action Title', 'realestate-booking-suite'); ?></label>
                                <input type="text" name="resbs_quick_actions[<?php echo esc_attr($index); ?>][title]" value="<?php echo esc_attr($action['title']); ?>" placeholder="e.g., Send Message" />
                            </div>
                            
                            <div class="resbs-form-group">
                                <label><?php esc_html_e('Icon Class', 'realestate-booking-suite'); ?></label>
                                <input type="text" name="resbs_quick_actions[<?php echo esc_attr($index); ?>][icon]" value="<?php echo esc_attr($action['icon']); ?>" placeholder="e.g., fas fa-envelope" />
                                <p class="description"><?php esc_html_e('FontAwesome icon class (e.g., fas fa-envelope, fas fa-share-alt)', 'realestate-booking-suite'); ?></p>
                            </div>
                            
                            <div class="resbs-form-group">
                                <label><?php esc_html_e('JavaScript Action', 'realestate-booking-suite'); ?></label>
                                <input type="text" name="resbs_quick_actions[<?php echo esc_attr($index); ?>][action]" value="<?php echo esc_attr($action['action']); ?>" placeholder="e.g., openContactModal()" />
                                <p class="description"><?php esc_html_e('JavaScript function to call when clicked', 'realestate-booking-suite'); ?></p>
                            </div>
                            
                            <div class="resbs-form-group">
                                <label><?php esc_html_e('Button Style Classes', 'realestate-booking-suite'); ?></label>
                                <input type="text" name="resbs_quick_actions[<?php echo esc_attr($index); ?>][style]" value="<?php echo esc_attr($action['style']); ?>" placeholder="e.g., bg-gray-700 text-white hover:bg-gray-800" />
                                <p class="description"><?php esc_html_e('Tailwind CSS classes for button styling', 'realestate-booking-suite'); ?></p>
                            </div>
                            
                            <div class="resbs-form-group">
                                <label>
                                    <input type="checkbox" name="resbs_quick_actions[<?php echo esc_attr($index); ?>][enabled]" value="1" <?php checked($action['enabled'], 1); ?> />
                                    <?php esc_html_e('Enable this action', 'realestate-booking-suite'); ?>
                                </label>
                            </div>
                            
                            <button type="button" class="button remove-action"><?php esc_html_e('Remove Action', 'realestate-booking-suite'); ?></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="button" id="add-quick-action" class="button button-secondary"><?php esc_html_e('Add New Action', 'realestate-booking-suite'); ?></button>
                </div>
                
                <?php submit_button(); ?>
            </form>
        </div>
        
        <!-- Quick actions scripts and styles are now enqueued via wp_enqueue_script/wp_enqueue_style -->
        <?php
    }
}