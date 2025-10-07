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
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // General Settings
        register_setting('resbs_general_settings', 'resbs_default_currency');
        register_setting('resbs_general_settings', 'resbs_map_api_key');
        
        // Badge Settings
        register_setting('resbs_badge_settings', 'resbs_badge_color');
        register_setting('resbs_badge_settings', 'resbs_badge_text_color');
        register_setting('resbs_badge_settings', 'resbs_badge_position');
        
        // Map Settings
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
    }
    
    /**
     * Dashboard page callback
     */
    public function dashboard_page_callback() {
        ?>
        <div class="wrap resbs-admin-wrap">
            <div class="resbs-admin-header">
                <h1 class="resbs-admin-title"><?php esc_html_e('RealEstate Booking Suite', 'realestate-booking-suite'); ?></h1>
                <p class="resbs-admin-subtitle"><?php esc_html_e('Professional real estate booking and management system', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-stats-grid">
                <div class="resbs-stat-card">
                    <div class="resbs-stat-number"><?php echo esc_html(wp_count_posts('property')->publish); ?></div>
                    <div class="resbs-stat-label"><?php esc_html_e('Total Properties', 'realestate-booking-suite'); ?></div>
                </div>
                <div class="resbs-stat-card">
                    <div class="resbs-stat-number"><?php echo esc_html(wp_count_posts('property')->draft); ?></div>
                    <div class="resbs-stat-label"><?php esc_html_e('Draft Properties', 'realestate-booking-suite'); ?></div>
                </div>
                <div class="resbs-stat-card">
                    <div class="resbs-stat-number"><?php echo esc_html(wp_count_posts('property')->pending); ?></div>
                    <div class="resbs-stat-label"><?php esc_html_e('Pending Properties', 'realestate-booking-suite'); ?></div>
                </div>
                <div class="resbs-stat-card">
                    <div class="resbs-stat-number"><?php echo esc_html(wp_count_posts('property')->private); ?></div>
                    <div class="resbs-stat-label"><?php esc_html_e('Private Properties', 'realestate-booking-suite'); ?></div>
                </div>
            </div>
            
            <div class="resbs-card">
                <div class="resbs-card-header">
                    <h2 class="resbs-card-title"><?php esc_html_e('Quick Actions', 'realestate-booking-suite'); ?></h2>
                </div>
                <div class="resbs-card-body">
                    <p>
                        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=property')); ?>" class="resbs-btn resbs-btn-primary">
                            <?php esc_html_e('Add New Property', 'realestate-booking-suite'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=property')); ?>" class="resbs-btn resbs-btn-secondary">
                            <?php esc_html_e('View All Properties', 'realestate-booking-suite'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=resbs-general-settings')); ?>" class="resbs-btn resbs-btn-secondary">
                            <?php esc_html_e('Plugin Settings', 'realestate-booking-suite'); ?>
                        </a>
                    </p>
                </div>
            </div>
            
            <div class="resbs-card">
                <div class="resbs-card-header">
                    <h2 class="resbs-card-title"><?php esc_html_e('Plugin Information', 'realestate-booking-suite'); ?></h2>
                </div>
                <div class="resbs-card-body">
                    <p><?php esc_html_e('RealEstate Booking Suite is a comprehensive WordPress plugin for managing real estate properties with advanced booking functionality, WooCommerce integration, and Elementor support.', 'realestate-booking-suite'); ?></p>
                    <h3><?php esc_html_e('Key Features:', 'realestate-booking-suite'); ?></h3>
                    <ul>
                        <li><?php esc_html_e('Property management with custom post types', 'realestate-booking-suite'); ?></li>
                        <li><?php esc_html_e('WooCommerce integration for bookings and payments', 'realestate-booking-suite'); ?></li>
                        <li><?php esc_html_e('Elementor widgets for easy page building', 'realestate-booking-suite'); ?></li>
                        <li><?php esc_html_e('Advanced search and filtering', 'realestate-booking-suite'); ?></li>
                        <li><?php esc_html_e('Google Maps integration', 'realestate-booking-suite'); ?></li>
                        <li><?php esc_html_e('Responsive design for all devices', 'realestate-booking-suite'); ?></li>
                        <li><?php esc_html_e('Multilingual support', 'realestate-booking-suite'); ?></li>
                    </ul>
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
                        <h2 class="resbs-card-title"><?php esc_html_e('Map Configuration', 'realestate-booking-suite'); ?></h2>
                    </div>
                    <div class="resbs-card-body">
                        <div class="resbs-form-group">
                            <label for="resbs_map_zoom_level"><?php esc_html_e('Default Zoom Level', 'realestate-booking-suite'); ?></label>
                            <input type="number" id="resbs_map_zoom_level" name="resbs_map_zoom_level" value="<?php echo esc_attr(get_option('resbs_map_zoom_level', '10')); ?>" min="1" max="20" />
                            <p class="description"><?php esc_html_e('Default zoom level for maps (1-20)', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_map_center_lat"><?php esc_html_e('Map Center Latitude', 'realestate-booking-suite'); ?></label>
                            <input type="number" id="resbs_map_center_lat" name="resbs_map_center_lat" value="<?php echo esc_attr(get_option('resbs_map_center_lat', '40.7128')); ?>" step="any" />
                            <p class="description"><?php esc_html_e('Default latitude for map center', 'realestate-booking-suite'); ?></p>
                        </div>
                        
                        <div class="resbs-form-group">
                            <label for="resbs_map_center_lng"><?php esc_html_e('Map Center Longitude', 'realestate-booking-suite'); ?></label>
                            <input type="number" id="resbs_map_center_lng" name="resbs_map_center_lng" value="<?php echo esc_attr(get_option('resbs_map_center_lng', '-74.0060')); ?>" step="any" />
                            <p class="description"><?php esc_html_e('Default longitude for map center', 'realestate-booking-suite'); ?></p>
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
}