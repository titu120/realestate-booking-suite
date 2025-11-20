<?php
/**
 * Enhanced Settings Class - Estatik Style
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Enhanced_Settings {
    
    private $current_tab = 'general';
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Fix menu highlighting for All Properties vs My Properties
        add_filter('parent_file', array($this, 'fix_properties_menu_highlight'));
        add_filter('submenu_file', array($this, 'fix_properties_submenu_highlight'));
        
        // Add AJAX handlers
        add_action('admin_post_resbs_save_settings', array($this, 'handle_settings_save'));
        add_action('admin_post_resbs_reset_settings', array($this, 'handle_reset_settings'));
        add_action('wp_ajax_resbs_create_page', array($this, 'handle_create_page'));
        add_action('wp_ajax_resbs_load_tab_content', array($this, 'handle_load_tab_content'));
        add_action('wp_ajax_resbs_test_ajax', array($this, 'handle_test_ajax'));
    }
    
    /**
     * Add unified admin menu structure
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            esc_html__('RealEstate  Suite', 'realestate-booking-suite'),
            esc_html__('RealEstate  Suite', 'realestate-booking-suite'),
            'manage_options',
            'resbs-main-menu',
            array($this, 'dashboard_callback'),
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
            array($this, 'dashboard_callback')
        );
        
        // My Properties - filter by current user
        $current_user_id = get_current_user_id();
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('My Properties', 'realestate-booking-suite'),
            esc_html__('My Properties', 'realestate-booking-suite'),
            'manage_options',
            'edit.php?post_type=property&author=' . $current_user_id
        );
        
        // Add New Property
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Add New Property', 'realestate-booking-suite'),
            esc_html__('Add New Property', 'realestate-booking-suite'),
            'manage_options',
            'post-new.php?post_type=property'
        );
        
        // Data Manager
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Data Manager', 'realestate-booking-suite'),
            esc_html__('Data Manager', 'realestate-booking-suite'),
            'manage_options',
            'resbs-data-manager',
            array($this, 'data_manager_callback')
        );
        
        // Fields Builder
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Fields Builder', 'realestate-booking-suite'),
            esc_html__('Fields Builder', 'realestate-booking-suite'),
            'manage_options',
            'resbs-fields-builder',
            array($this, 'fields_builder_callback')
        );
        
        // Settings
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Settings', 'realestate-booking-suite'),
            esc_html__('Settings', 'realestate-booking-suite'),
            'manage_options',
            'resbs-settings',
            array($this, 'settings_page_callback')
        );
        
        // Demo Content
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Demo Content', 'realestate-booking-suite'),
            esc_html__('Demo Content', 'realestate-booking-suite'),
            'manage_options',
            'resbs-demo-content',
            array($this, 'demo_content_callback')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // General Settings
        register_setting('resbs_enhanced_settings', 'resbs_area_unit');
        register_setting('resbs_enhanced_settings', 'resbs_lot_size_unit');
        register_setting('resbs_enhanced_settings', 'resbs_date_format');
        register_setting('resbs_enhanced_settings', 'resbs_logo_image');
        register_setting('resbs_enhanced_settings', 'resbs_main_color');
        register_setting('resbs_enhanced_settings', 'resbs_secondary_color');
        
        // Map Settings
        register_setting('resbs_enhanced_settings', 'resbs_mapbox_access_token');
        // Keep Google API key for backwards compatibility
        register_setting('resbs_enhanced_settings', 'resbs_google_api_key');
        // Default latitude/longitude removed - maps now use individual property coordinates
        register_setting('resbs_enhanced_settings', 'resbs_default_zoom_level');
        register_setting('resbs_enhanced_settings', 'resbs_single_property_zoom_level');
        register_setting('resbs_enhanced_settings', 'resbs_enable_markers_cluster');
        register_setting('resbs_enhanced_settings', 'resbs_cluster_icon');
        register_setting('resbs_enhanced_settings', 'resbs_cluster_icon_color');
        register_setting('resbs_enhanced_settings', 'resbs_map_marker_type');
        register_setting('resbs_enhanced_settings', 'resbs_use_single_map_marker');
        register_setting('resbs_enhanced_settings', 'resbs_single_marker_icon');
        register_setting('resbs_enhanced_settings', 'resbs_single_marker_color');
        
        
        // Listings Settings
        register_setting('resbs_enhanced_settings', 'resbs_disable_lightbox_single_page');
        register_setting('resbs_enhanced_settings', 'resbs_enable_request_form_geolocation');
        register_setting('resbs_enhanced_settings', 'resbs_show_phone_country_code');
        register_setting('resbs_enhanced_settings', 'resbs_properties_per_page');
        register_setting('resbs_enhanced_settings', 'resbs_enable_sorting');
        register_setting('resbs_enhanced_settings', 'resbs_sort_options');
        register_setting('resbs_enhanced_settings', 'resbs_default_sort_option');
        register_setting('resbs_enhanced_settings', 'resbs_show_price');
        register_setting('resbs_enhanced_settings', 'resbs_show_listing_address');
        register_setting('resbs_enhanced_settings', 'resbs_enable_map_single_listing');
        register_setting('resbs_enhanced_settings', 'resbs_enable_wishlist');
        register_setting('resbs_enhanced_settings', 'resbs_enable_labels');
        register_setting('resbs_enhanced_settings', 'resbs_enable_sharing');
        register_setting('resbs_enhanced_settings', 'resbs_show_date_added');
        
        // Archive Page Settings removed - not used in templates, causes conflicts with General/Listings settings
        register_setting('resbs_enhanced_settings', 'resbs_properties_per_page');
        register_setting('resbs_enhanced_settings', 'resbs_show_view_toggle');
        register_setting('resbs_enhanced_settings', 'resbs_show_sorting');
        register_setting('resbs_enhanced_settings', 'resbs_show_filters');
        register_setting('resbs_enhanced_settings', 'resbs_show_search');
        register_setting('resbs_enhanced_settings', 'resbs_show_pagination');
        register_setting('resbs_enhanced_settings', 'resbs_show_property_image');
        register_setting('resbs_enhanced_settings', 'resbs_show_property_price');
        register_setting('resbs_enhanced_settings', 'resbs_show_property_title');
        register_setting('resbs_enhanced_settings', 'resbs_show_property_location');
        register_setting('resbs_enhanced_settings', 'resbs_show_property_details');
        register_setting('resbs_enhanced_settings', 'resbs_show_property_type');
        register_setting('resbs_enhanced_settings', 'resbs_show_property_status');
        register_setting('resbs_enhanced_settings', 'resbs_show_favorite_button');
        register_setting('resbs_enhanced_settings', 'resbs_show_quick_view');
        register_setting('resbs_enhanced_settings', 'resbs_default_sort');
        register_setting('resbs_enhanced_settings', 'resbs_filter_price');
        register_setting('resbs_enhanced_settings', 'resbs_filter_type');
        register_setting('resbs_enhanced_settings', 'resbs_filter_bedrooms');
        register_setting('resbs_enhanced_settings', 'resbs_filter_bathrooms');
        register_setting('resbs_enhanced_settings', 'resbs_filter_status');
        register_setting('resbs_enhanced_settings', 'resbs_filter_area');
        register_setting('resbs_enhanced_settings', 'resbs_archive_meta_description');
        register_setting('resbs_enhanced_settings', 'resbs_archive_meta_keywords');
        
        // Listing Search Settings removed - not used in templates, archive page has hardcoded search
        
        // User Profile Settings
        register_setting('resbs_enhanced_settings', 'resbs_enable_user_profile');
        register_setting('resbs_enhanced_settings', 'resbs_profile_page_title');
        register_setting('resbs_enhanced_settings', 'resbs_profile_page_subtitle');
        
        // Log in & Sign up Settings
        register_setting('resbs_enhanced_settings', 'resbs_enable_login_form');
        register_setting('resbs_enhanced_settings', 'resbs_signin_page_title');
        register_setting('resbs_enhanced_settings', 'resbs_signin_page_subtitle');
        register_setting('resbs_enhanced_settings', 'resbs_enable_signup_buyers');
        register_setting('resbs_enhanced_settings', 'resbs_buyer_signup_title');
        register_setting('resbs_enhanced_settings', 'resbs_buyer_signup_subtitle');
        
        // SEO Settings
        register_setting('resbs_enhanced_settings', 'resbs_enable_auto_tags');
        register_setting('resbs_enhanced_settings', 'resbs_enable_clickable_tags');
        register_setting('resbs_enhanced_settings', 'resbs_heading_tag_posts_title');
        register_setting('resbs_enhanced_settings', 'resbs_enable_dynamic_content');
        
        
        
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        // Only load on settings page
        if ($hook !== 'resbs-main-menu_page_resbs-settings') {
            return;
        }
        
        // Enqueue enhanced settings CSS
        wp_enqueue_style(
            'resbs-enhanced-settings',
            RESBS_URL . 'assets/css/enhanced-settings.css',
            array(),
            '1.0.0'
        );
        
        // Minimal enqueue - just what we need
        wp_enqueue_script('jquery');
        
        // Make ajaxurl available globally
        wp_add_inline_script('jquery', 'var ajaxurl = "' . esc_js(admin_url('admin-ajax.php')) . '";');
        
        // Enqueue enhanced settings JS
        wp_enqueue_script(
            'resbs-enhanced-settings',
            RESBS_URL . 'assets/js/enhanced-settings.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script with nonces
        wp_localize_script('resbs-enhanced-settings', 'resbsEnhancedSettings', array(
            'nonceLoadTab' => wp_create_nonce('resbs_load_tab_content'),
            'nonceCreatePage' => wp_create_nonce('resbs_create_page_nonce')
        ));
    }
    
    /**
     * Settings page callback
     */
    public function settings_page_callback() {
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        // Validate tab value against allowed tabs
        $allowed_tabs = array('general', 'map', 'listings', 'user-profile', 'login-signup', 'seo');
        if (!in_array($tab, $allowed_tabs, true)) {
            $tab = 'general';
        }
        $this->current_tab = $tab;
        ?>
        <div class="wrap resbs-enhanced-settings">

            
            <div class="resbs-settings-container">
                <div class="resbs-settings-sidebar">
                    <div class="resbs-settings-nav">
                        <ul class="resbs-nav-tabs">
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="general" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'general' ? 'active' : ''); ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('General', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="map" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'map' ? 'active' : ''); ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('Map', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="listings" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'listings' ? 'active' : ''); ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('Listings', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="user-profile" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'user-profile' ? 'active' : ''); ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('User profile', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="login-signup" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'login-signup' ? 'active' : ''); ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('Log in & Sign up', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="seo" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'seo' ? 'active' : ''); ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('SEO', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="resbs-settings-content">
                    <?php
                    switch ($this->current_tab) {
                        case 'general':
                            $this->general_settings_tab();
                            break;
                        case 'map':
                            $this->map_settings_tab();
                            break;
                        case 'listings':
                            $this->listings_settings_tab();
                            break;
                        case 'user-profile':
                            $this->user_profile_settings_tab();
                            break;
                        case 'login-signup':
                            $this->login_signup_settings_tab();
                            break;
                        case 'seo':
                            $this->seo_settings_tab();
                            break;
                        default:
                            $this->general_settings_tab();
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <?php
    }
    
    /**
     * Email settings tab
     */
    private function email_settings_tab() {
        ?>
        <h2><?php esc_html_e('Email Settings', 'realestate-booking-suite'); ?></h2>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="email">
            
            <div class="resbs-form-group">
                <label for="resbs_email_from_name"><?php esc_html_e('From Name', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_email_from_name" name="resbs_email_from_name" value="<?php echo esc_attr(get_option('resbs_email_from_name', get_bloginfo('name'))); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_email_from_email"><?php esc_html_e('From Email', 'realestate-booking-suite'); ?></label>
                <input type="email" id="resbs_email_from_email" name="resbs_email_from_email" value="<?php echo esc_attr(get_option('resbs_email_from_email', get_option('admin_email'))); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_email_reply_to"><?php esc_html_e('Reply To Email', 'realestate-booking-suite'); ?></label>
                <input type="email" id="resbs_email_reply_to" name="resbs_email_reply_to" value="<?php echo esc_attr(get_option('resbs_email_reply_to', get_option('admin_email'))); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_email_notifications" value="1" <?php checked(get_option('resbs_enable_email_notifications'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable Email Notifications', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_admin_notifications" value="1" <?php checked(get_option('resbs_enable_admin_notifications'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable Admin Notifications', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_email_template"><?php esc_html_e('Email Template', 'realestate-booking-suite'); ?></label>
                <select id="resbs_email_template" name="resbs_email_template">
                    <option value="default" <?php selected(get_option('resbs_email_template'), 'default'); ?>><?php esc_html_e('Default Template', 'realestate-booking-suite'); ?></option>
                    <option value="modern" <?php selected(get_option('resbs_email_template'), 'modern'); ?>><?php esc_html_e('Modern Template', 'realestate-booking-suite'); ?></option>
                    <option value="minimal" <?php selected(get_option('resbs_email_template'), 'minimal'); ?>><?php esc_html_e('Minimal Template', 'realestate-booking-suite'); ?></option>
                </select>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * General settings tab - This was missing!
     */
    private function general_settings_tab() {
        ?>
        <h2><?php esc_html_e('General', 'realestate-booking-suite'); ?></h2>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && $_GET['reset'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings reset to defaults successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="general">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Units', 'realestate-booking-suite'); ?></th>
                    <td>
                        <fieldset>
                            <?php $current_area_unit = get_option('resbs_area_unit', 'sqft'); ?>
                            <label><input type="radio" id="area_sqft" name="resbs_area_unit" value="sqft" <?php checked($current_area_unit, 'sqft'); ?>> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="radio" id="area_sqm" name="resbs_area_unit" value="sqm" <?php checked($current_area_unit, 'sqm'); ?>> <?php esc_html_e('sq m', 'realestate-booking-suite'); ?></label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Lot size unit', 'realestate-booking-suite'); ?></th>
                    <td>
                        <fieldset>
                            <?php $current_lot_unit = get_option('resbs_lot_size_unit', 'sqft'); ?>
                            <label><input type="radio" id="lot_sqft" name="resbs_lot_size_unit" value="sqft" <?php checked($current_lot_unit, 'sqft'); ?>> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="radio" id="lot_sqm" name="resbs_lot_size_unit" value="sqm" <?php checked($current_lot_unit, 'sqm'); ?>> <?php esc_html_e('sq m', 'realestate-booking-suite'); ?></label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_date_format"><?php esc_html_e('Date format', 'realestate-booking-suite'); ?></label></th>
                    <td><input type="text" id="resbs_date_format" name="resbs_date_format" value="<?php echo esc_attr(get_option('resbs_date_format', 'm/d/Y')); ?>" class="regular-text"></td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_main_color"><?php esc_html_e('Main color', 'realestate-booking-suite'); ?></label></th>
                    <td>
                        <input type="color" id="resbs_main_color" name="resbs_main_color" value="<?php echo esc_attr(get_option('resbs_main_color', '#28a745')); ?>" style="width: 60px; height: 30px; vertical-align: middle;">
                        <input type="text" class="resbs-color-hex" value="<?php echo esc_attr(get_option('resbs_main_color', '#28a745')); ?>" placeholder="#28a745" maxlength="7" style="width: 100px; margin-left: 10px;">
                        <button type="button" class="resbs-color-reset button" data-default="#28a745" style="margin-left: 10px;"><?php esc_html_e('Reset', 'realestate-booking-suite'); ?></button>
                        <p class="description"><?php esc_html_e('Large buttons', 'realestate-booking-suite'); ?></p>
                    </td>
                </tr>
          
                <tr>
                    <th scope="row"><label for="resbs_secondary_color"><?php esc_html_e('Secondary color', 'realestate-booking-suite'); ?></label></th>
                    <td>
                        <input type="color" id="resbs_secondary_color" name="resbs_secondary_color" value="<?php echo esc_attr(get_option('resbs_secondary_color', '#0073aa')); ?>" style="width: 60px; height: 30px; vertical-align: middle;">
                        <input type="text" class="resbs-color-hex" value="<?php echo esc_attr(get_option('resbs_secondary_color', '#0073aa')); ?>" placeholder="#0073aa" maxlength="7" style="width: 100px; margin-left: 10px;">
                        <button type="button" class="resbs-color-reset button" data-default="#0073aa" style="margin-left: 10px;"><?php esc_html_e('Reset', 'realestate-booking-suite'); ?></button>
                        <p class="description"><?php esc_html_e('Small buttons', 'realestate-booking-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div style="margin-top: 20px;">
                <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
                <button type="button" class="button button-secondary resbs-reset-button" onclick="if(confirm('<?php esc_attr_e('Are you sure you want to reset all General settings to default values? This cannot be undone.', 'realestate-booking-suite'); ?>')) { document.getElementById('resetGeneralForm').submit(); }" style="margin-left: 10px;"><?php esc_html_e('Reset to Defaults', 'realestate-booking-suite'); ?></button>
            </div>
        </form>
        
        <!-- Reset Form -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="resetGeneralForm" style="display: none;">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_reset_settings">
            <input type="hidden" name="current_tab" value="general">
        </form>
        <?php
    }
    
    /**
     * Appearance settings tab
     */
    private function appearance_settings_tab() {
        ?>
        <h2><?php esc_html_e('Appearance', 'realestate-booking-suite'); ?></h2>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="appearance">
            
            <div class="resbs-form-group">
                <label for="resbs_primary_color"><?php esc_html_e('Primary Color', 'realestate-booking-suite'); ?></label>
                <input type="color" id="resbs_primary_color" name="resbs_primary_color" value="<?php echo esc_attr(get_option('resbs_primary_color', '#007cba')); ?>">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_secondary_color"><?php esc_html_e('Secondary Color', 'realestate-booking-suite'); ?></label>
                <input type="color" id="resbs_secondary_color" name="resbs_secondary_color" value="<?php echo esc_attr(get_option('resbs_secondary_color', '#28a745')); ?>">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_accent_color"><?php esc_html_e('Accent Color', 'realestate-booking-suite'); ?></label>
                <input type="color" id="resbs_accent_color" name="resbs_accent_color" value="<?php echo esc_attr(get_option('resbs_accent_color', '#28a745')); ?>">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_font_family"><?php esc_html_e('Font Family', 'realestate-booking-suite'); ?></label>
                <select id="resbs_font_family" name="resbs_font_family">
                    <option value="default" <?php selected(get_option('resbs_font_family'), 'default'); ?>><?php esc_html_e('Default', 'realestate-booking-suite'); ?></option>
                    <option value="roboto" <?php selected(get_option('resbs_font_family'), 'roboto'); ?>><?php esc_html_e('Roboto', 'realestate-booking-suite'); ?></option>
                    <option value="opensans" <?php selected(get_option('resbs_font_family'), 'opensans'); ?>><?php esc_html_e('Open Sans', 'realestate-booking-suite'); ?></option>
                    <option value="lato" <?php selected(get_option('resbs_font_family'), 'lato'); ?>><?php esc_html_e('Lato', 'realestate-booking-suite'); ?></option>
                    <option value="montserrat" <?php selected(get_option('resbs_font_family'), 'montserrat'); ?>><?php esc_html_e('Montserrat', 'realestate-booking-suite'); ?></option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_button_style"><?php esc_html_e('Button Style', 'realestate-booking-suite'); ?></label>
                <select id="resbs_button_style" name="resbs_button_style">
                    <option value="rounded" <?php selected(get_option('resbs_button_style'), 'rounded'); ?>><?php esc_html_e('Rounded', 'realestate-booking-suite'); ?></option>
                    <option value="square" <?php selected(get_option('resbs_button_style'), 'square'); ?>><?php esc_html_e('Square', 'realestate-booking-suite'); ?></option>
                    <option value="pill" <?php selected(get_option('resbs_button_style'), 'pill'); ?>><?php esc_html_e('Pill', 'realestate-booking-suite'); ?></option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_dark_mode" value="1" <?php checked(get_option('resbs_enable_dark_mode'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable Dark Mode', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_animations" value="1" <?php checked(get_option('resbs_enable_animations'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable Animations', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * Currency settings tab
     */
    private function currency_settings_tab() {
        ?>
        <h2><?php esc_html_e('Currency Settings', 'realestate-booking-suite'); ?></h2>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="currency">
            
            <div class="resbs-form-group">
                <label for="resbs_default_currency"><?php esc_html_e('Default Currency', 'realestate-booking-suite'); ?></label>
                <select id="resbs_default_currency" name="resbs_default_currency">
                    <option value="USD" <?php selected(get_option('resbs_default_currency'), 'USD'); ?>>US Dollar (USD)</option>
                    <option value="EUR" <?php selected(get_option('resbs_default_currency'), 'EUR'); ?>>Euro (EUR)</option>
                    <option value="GBP" <?php selected(get_option('resbs_default_currency'), 'GBP'); ?>>British Pound (GBP)</option>
                    <option value="CAD" <?php selected(get_option('resbs_default_currency'), 'CAD'); ?>>Canadian Dollar (CAD)</option>
                    <option value="AUD" <?php selected(get_option('resbs_default_currency'), 'AUD'); ?>>Australian Dollar (AUD)</option>
                    <option value="JPY" <?php selected(get_option('resbs_default_currency'), 'JPY'); ?>>Japanese Yen (JPY)</option>
                    <option value="CNY" <?php selected(get_option('resbs_default_currency'), 'CNY'); ?>>Chinese Yuan (CNY)</option>
                    <option value="INR" <?php selected(get_option('resbs_default_currency'), 'INR'); ?>>Indian Rupee (INR)</option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_currency_position"><?php esc_html_e('Currency Position', 'realestate-booking-suite'); ?></label>
                <select id="resbs_currency_position" name="resbs_currency_position">
                    <option value="before" <?php selected(get_option('resbs_currency_position'), 'before'); ?>><?php esc_html_e('Before amount ($100)', 'realestate-booking-suite'); ?></option>
                    <option value="after" <?php selected(get_option('resbs_currency_position'), 'after'); ?>><?php esc_html_e('After amount (100$)', 'realestate-booking-suite'); ?></option>
                    <option value="before_space" <?php selected(get_option('resbs_currency_position'), 'before_space'); ?>><?php esc_html_e('Before with space ($ 100)', 'realestate-booking-suite'); ?></option>
                    <option value="after_space" <?php selected(get_option('resbs_currency_position'), 'after_space'); ?>><?php esc_html_e('After with space (100 $)', 'realestate-booking-suite'); ?></option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_thousand_separator"><?php esc_html_e('Thousand Separator', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_thousand_separator" name="resbs_thousand_separator" value="<?php echo esc_attr(get_option('resbs_thousand_separator', ',')); ?>" class="small-text" maxlength="1">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_decimal_separator"><?php esc_html_e('Decimal Separator', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_decimal_separator" name="resbs_decimal_separator" value="<?php echo esc_attr(get_option('resbs_decimal_separator', '.')); ?>" class="small-text" maxlength="1">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_decimal_places"><?php esc_html_e('Number of Decimal Places', 'realestate-booking-suite'); ?></label>
                <input type="number" id="resbs_decimal_places" name="resbs_decimal_places" value="<?php echo esc_attr(get_option('resbs_decimal_places', 2)); ?>" class="small-text" min="0" max="4">
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_currency_conversion" value="1" <?php checked(get_option('resbs_enable_currency_conversion'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable Currency Conversion', 'realestate-booking-suite'); ?>
                    <span class="resbs-pro-tag">PRO</span>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_currency_api_key"><?php esc_html_e('Currency API Key', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_currency_api_key" name="resbs_currency_api_key" value="<?php echo esc_attr(get_option('resbs_currency_api_key')); ?>" class="regular-text">
                <p class="resbs-description"><?php esc_html_e('Get your free API key from', 'realestate-booking-suite'); ?> <a href="https://exchangerate-api.com/" target="_blank"><?php esc_html_e('ExchangeRate-API', 'realestate-booking-suite'); ?></a></p>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    // Additional tab methods will be implemented in the next part...
    
    /**
     * Map settings tab
     */
    private function map_settings_tab() {
        ?>
        <h2><?php esc_html_e('Map', 'realestate-booking-suite'); ?></h2>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && $_GET['reset'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings reset to defaults successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="map">
            <input type="hidden" name="resbs_mapbox_access_token" value="">
            

            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="resbs_default_zoom_level"><?php esc_html_e('Default zoom level', 'realestate-booking-suite'); ?></label></th>
                    <td>
                        <input type="number" id="resbs_default_zoom_level" name="resbs_default_zoom_level" value="<?php echo esc_attr(get_option('resbs_default_zoom_level', '12')); ?>" min="0" max="20" class="small-text">
                        <button type="button" class="button" onclick="document.getElementById('resbs_default_zoom_level').stepDown()" style="margin-left: 5px;">-</button>
                        <button type="button" class="button" onclick="document.getElementById('resbs_default_zoom_level').stepUp()" style="margin-left: 5px;">+</button>
                        <p class="description"><?php esc_html_e('Choose the zoom level for the map. 0 corresponds to a map of the earth fully zoomed out, and larger zoom levels zoom in at a higher resolution.', 'realestate-booking-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_single_property_zoom_level"><?php esc_html_e('Zoom level for single property page', 'realestate-booking-suite'); ?></label></th>
                    <td>
                        <input type="number" id="resbs_single_property_zoom_level" name="resbs_single_property_zoom_level" value="<?php echo esc_attr(get_option('resbs_single_property_zoom_level', '16')); ?>" min="0" max="20" class="small-text">
                        <button type="button" class="button" onclick="document.getElementById('resbs_single_property_zoom_level').stepDown()" style="margin-left: 5px;">-</button>
                        <button type="button" class="button" onclick="document.getElementById('resbs_single_property_zoom_level').stepUp()" style="margin-left: 5px;">+</button>
                        <p class="description"><?php esc_html_e('Choose the zoom level for the map on single property page.', 'realestate-booking-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable markers cluster', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_markers_cluster" value="1" <?php checked(get_option('resbs_enable_markers_cluster'), 1); ?>>
                            <?php esc_html_e('Enable markers cluster', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Select icon (for markers cluster)', 'realestate-booking-suite'); ?></th>
                    <td>
                        <fieldset>
                            <label><input type="radio" id="cluster_circle" name="resbs_cluster_icon" value="circle" <?php checked(get_option('resbs_cluster_icon'), 'circle'); ?>> ● <?php esc_html_e('Circle', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="radio" id="cluster_bubble" name="resbs_cluster_icon" value="bubble" <?php checked(get_option('resbs_cluster_icon'), 'bubble'); ?>> 💬 <?php esc_html_e('Bubble', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="radio" id="cluster_outline" name="resbs_cluster_icon" value="outline" <?php checked(get_option('resbs_cluster_icon'), 'outline'); ?>> ○ <?php esc_html_e('Outline', 'realestate-booking-suite'); ?></label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_cluster_icon_color"><?php esc_html_e('Icon color (for markers cluster)', 'realestate-booking-suite'); ?></label></th>
                    <td>
                        <input type="color" id="resbs_cluster_icon_color" name="resbs_cluster_icon_color" value="<?php echo esc_attr(get_option('resbs_cluster_icon_color', '#333333')); ?>" style="width: 60px; height: 30px; vertical-align: middle;">
                        <input type="text" class="resbs-color-hex" value="<?php echo esc_attr(get_option('resbs_cluster_icon_color', '#333333')); ?>" placeholder="#333333" maxlength="7" style="width: 100px; margin-left: 10px;">
                        <button type="button" class="resbs-color-reset button" data-default="#333333" style="margin-left: 10px;"><?php esc_html_e('Reset', 'realestate-booking-suite'); ?></button>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('What to use as map marker?', 'realestate-booking-suite'); ?></th>
                    <td>
                        <fieldset>
                            <label><input type="radio" id="marker_icon" name="resbs_map_marker_type" value="icon" <?php checked(get_option('resbs_map_marker_type'), 'icon'); ?>> 📍 <?php esc_html_e('Icon', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="radio" id="marker_price" name="resbs_map_marker_type" value="price" <?php checked(get_option('resbs_map_marker_type'), 'price'); ?>> $ <?php esc_html_e('Price', 'realestate-booking-suite'); ?></label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Use single map marker?', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_use_single_map_marker" value="1" <?php checked(get_option('resbs_use_single_map_marker'), 1); ?>>
                            <?php esc_html_e('Use single map marker?', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Select icon (for single map marker)', 'realestate-booking-suite'); ?></th>
                    <td>
                        <fieldset>
                            <label><input type="radio" id="single_pin" name="resbs_single_marker_icon" value="pin" <?php checked(get_option('resbs_single_marker_icon'), 'pin'); ?>> 📍 <?php esc_html_e('Pin', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="radio" id="single_outline" name="resbs_single_marker_icon" value="outline" <?php checked(get_option('resbs_single_marker_icon'), 'outline'); ?>> 📍 <?php esc_html_e('Outline Pin', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="radio" id="single_person" name="resbs_single_marker_icon" value="person" <?php checked(get_option('resbs_single_marker_icon'), 'person'); ?>> 👤 <?php esc_html_e('Person', 'realestate-booking-suite'); ?></label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_single_marker_color"><?php esc_html_e('Icon color (for single map marker)', 'realestate-booking-suite'); ?></label></th>
                    <td>
                        <input type="color" id="resbs_single_marker_color" name="resbs_single_marker_color" value="<?php echo esc_attr(get_option('resbs_single_marker_color', '#333333')); ?>" style="width: 60px; height: 30px; vertical-align: middle;">
                        <input type="text" class="resbs-color-hex" value="<?php echo esc_attr(get_option('resbs_single_marker_color', '#333333')); ?>" placeholder="#333333" maxlength="7" style="width: 100px; margin-left: 10px;">
                        <button type="button" class="resbs-color-reset button" data-default="#333333" style="margin-left: 10px;"><?php esc_html_e('Reset', 'realestate-booking-suite'); ?></button>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="resbs-save-button button button-primary"><?php esc_html_e('Save Changes', 'realestate-booking-suite'); ?></button>
                <button type="button" class="button button-secondary resbs-reset-button" onclick="if(confirm('<?php esc_attr_e('Are you sure you want to reset all Map settings to default values? This cannot be undone.', 'realestate-booking-suite'); ?>')) { document.getElementById('resetMapForm').submit(); }" style="margin-left: 10px;"><?php esc_html_e('Reset to Defaults', 'realestate-booking-suite'); ?></button>
            </p>
        </form>
        
        <!-- Reset Form -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="resetMapForm" style="display: none;">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_reset_settings">
            <input type="hidden" name="current_tab" value="map">
        </form>
        <?php
    }
    
    
    /**
     * Listings settings tab
     */
    private function listings_settings_tab() {
        ?>
        <h2><?php esc_html_e('Listings', 'realestate-booking-suite'); ?></h2>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && $_GET['reset'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings reset to defaults successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="listings">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Disable lightBox on single page', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_disable_lightbox_single_page" value="1" <?php checked(get_option('resbs_disable_lightbox_single_page'), 1); ?>>
                            <?php esc_html_e('Disable lightBox on single page', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable request form geolocation', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_request_form_geolocation" value="1" <?php checked(get_option('resbs_enable_request_form_geolocation'), 1); ?>>
                            <?php esc_html_e('Enable request form geolocation', 'realestate-booking-suite'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('This option uses for autofill tel code field by user location.', 'realestate-booking-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Show phone country code dropdown', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_show_phone_country_code" value="1" <?php checked(get_option('resbs_show_phone_country_code', true), 1); ?>>
                            <?php esc_html_e('Show phone country code dropdown', 'realestate-booking-suite'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('If enabled, users can select their country code. If disabled, shows a simple phone input field.', 'realestate-booking-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_properties_per_page"><?php esc_html_e('Properties Number Per Page', 'realestate-booking-suite'); ?></label></th>
                    <td><input type="number" id="resbs_properties_per_page" name="resbs_properties_per_page" value="<?php echo esc_attr(get_option('resbs_properties_per_page', '40')); ?>" min="1" max="100" class="small-text"></td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable Sorting', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_sorting" value="1" <?php checked(get_option('resbs_enable_sorting', 1), 1); ?>>
                            <?php esc_html_e('Enable Sorting', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Sort Options', 'realestate-booking-suite'); ?></th>
                    <td>
                        <fieldset>
                            <?php $sort_options = (array)get_option('resbs_sort_options', array()); ?>
                            <label><input type="checkbox" id="sort_newest" name="resbs_sort_options[]" value="newest" <?php checked(in_array('newest', $sort_options), true); ?>> <?php esc_html_e('Newest', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_oldest" name="resbs_sort_options[]" value="oldest" <?php checked(in_array('oldest', $sort_options), true); ?>> <?php esc_html_e('Oldest', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_lowest_price" name="resbs_sort_options[]" value="lowest_price" <?php checked(in_array('lowest_price', $sort_options), true); ?>> <?php esc_html_e('Lowest price', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_highest_price" name="resbs_sort_options[]" value="highest_price" <?php checked(in_array('highest_price', $sort_options), true); ?>> <?php esc_html_e('Highest price', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_largest_sqft" name="resbs_sort_options[]" value="largest_sqft" <?php checked(in_array('largest_sqft', $sort_options), true); ?>> <?php esc_html_e('Largest sq ft', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_lowest_sqft" name="resbs_sort_options[]" value="lowest_sqft" <?php checked(in_array('lowest_sqft', $sort_options), true); ?>> <?php esc_html_e('Lowest sq ft', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_bedrooms" name="resbs_sort_options[]" value="bedrooms" <?php checked(in_array('bedrooms', $sort_options), true); ?>> <?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_bathrooms" name="resbs_sort_options[]" value="bathrooms" <?php checked(in_array('bathrooms', $sort_options), true); ?>> <?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_featured" name="resbs_sort_options[]" value="featured" <?php checked(in_array('featured', $sort_options), true); ?>> <?php esc_html_e('Featured', 'realestate-booking-suite'); ?></label>
                        </fieldset>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_default_sort_option"><?php esc_html_e('Default Sort Options', 'realestate-booking-suite'); ?></label></th>
                    <td>
                        <select id="resbs_default_sort_option" name="resbs_default_sort_option">
                            <option value="newest" <?php selected(get_option('resbs_default_sort_option'), 'newest'); ?>><?php esc_html_e('Newest', 'realestate-booking-suite'); ?></option>
                            <option value="oldest" <?php selected(get_option('resbs_default_sort_option'), 'oldest'); ?>><?php esc_html_e('Oldest', 'realestate-booking-suite'); ?></option>
                            <option value="lowest_price" <?php selected(get_option('resbs_default_sort_option'), 'lowest_price'); ?>><?php esc_html_e('Lowest price', 'realestate-booking-suite'); ?></option>
                            <option value="highest_price" <?php selected(get_option('resbs_default_sort_option'), 'highest_price'); ?>><?php esc_html_e('Highest price', 'realestate-booking-suite'); ?></option>
                            <option value="largest_sqft" <?php selected(get_option('resbs_default_sort_option'), 'largest_sqft'); ?>><?php esc_html_e('Largest sq ft', 'realestate-booking-suite'); ?></option>
                            <option value="lowest_sqft" <?php selected(get_option('resbs_default_sort_option'), 'lowest_sqft'); ?>><?php esc_html_e('Lowest sq ft', 'realestate-booking-suite'); ?></option>
                            <option value="bedrooms" <?php selected(get_option('resbs_default_sort_option'), 'bedrooms'); ?>><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></option>
                            <option value="bathrooms" <?php selected(get_option('resbs_default_sort_option'), 'bathrooms'); ?>><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></option>
                            <option value="featured" <?php selected(get_option('resbs_default_sort_option'), 'featured'); ?>><?php esc_html_e('Featured', 'realestate-booking-suite'); ?></option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Show price', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_show_price" value="1" <?php checked(get_option('resbs_show_price', 1), 1); ?>>
                            <?php esc_html_e('Show price', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Show listing address', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_show_listing_address" value="1" <?php checked(get_option('resbs_show_listing_address', 1), 1); ?>>
                            <?php esc_html_e('Show listing address', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable map on single listing page', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_map_single_listing" value="1" <?php checked(get_option('resbs_enable_map_single_listing'), 1); ?>>
                            <?php esc_html_e('Enable map on single listing page', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable wishlist', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_wishlist" value="1" <?php checked(get_option('resbs_enable_wishlist'), 1); ?>>
                            <?php esc_html_e('Enable wishlist', 'realestate-booking-suite'); ?>
                        </label>
                        <?php 
                        $wishlist_page_id = get_option('resbs_wishlist_page_id');
                        $wishlist_page_url = resbs_get_wishlist_page_url();
                        if ($wishlist_page_url): 
                        ?>
                        <p class="description" style="margin-top: 8px;">
                            <?php esc_html_e('Wishlist page:', 'realestate-booking-suite'); ?> 
                            <a href="<?php echo esc_url($wishlist_page_url); ?>" target="_blank"><?php echo esc_html($wishlist_page_url); ?></a>
                            <?php if ($wishlist_page_id): ?>
                                | <a href="<?php echo esc_url(admin_url('post.php?post=' . absint($wishlist_page_id) . '&action=edit')); ?>"><?php esc_html_e('Edit Page', 'realestate-booking-suite'); ?></a>
                            <?php endif; ?>
                        </p>
                        <?php else: ?>
                        <p class="description" style="margin-top: 8px; color: #d63638;">
                            <?php esc_html_e('Wishlist page not found. Please deactivate and reactivate the plugin to create it.', 'realestate-booking-suite'); ?>
                        </p>
                        <?php endif; ?>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable labels', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_labels" value="1" <?php checked(get_option('resbs_enable_labels'), 1); ?>>
                            <?php esc_html_e('Enable labels', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable sharing', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_sharing" value="1" <?php checked(get_option('resbs_enable_sharing'), 1); ?>>
                            <?php esc_html_e('Enable sharing', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Show date added', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_show_date_added" value="1" <?php checked(get_option('resbs_show_date_added'), 1); ?>>
                            <?php esc_html_e('Show date added', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="resbs-save-button button button-primary"><?php esc_html_e('Save Changes', 'realestate-booking-suite'); ?></button>
                <button type="button" class="button button-secondary resbs-reset-button" onclick="if(confirm('<?php esc_attr_e('Are you sure you want to reset all Listings settings to default values? This cannot be undone.', 'realestate-booking-suite'); ?>')) { document.getElementById('resetListingsForm').submit(); }"><?php esc_html_e('Reset to Defaults', 'realestate-booking-suite'); ?></button>
            </p>
        </form>
        
        <!-- Reset Form -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="resetListingsForm" style="display: none;">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_reset_settings">
            <input type="hidden" name="current_tab" value="listings">
        </form>
        <?php
    }
    
    /**
     * User Profile settings tab
     */
    private function user_profile_settings_tab() {
        ?>
        <h2><?php esc_html_e('User Profile', 'realestate-booking-suite'); ?></h2>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && $_GET['reset'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings reset to defaults successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="user-profile">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable User Profile', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_user_profile" value="1" <?php checked(get_option('resbs_enable_user_profile', 1), 1); ?>>
                            <?php esc_html_e('Enable User Profile', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Add recommended page', 'realestate-booking-suite'); ?></th>
                    <td>
                        <p><?php esc_html_e('Profile', 'realestate-booking-suite'); ?></p>
                        <?php 
                        $profile_page_id = resbs_get_profile_page_id();
                        $profile_page_url = resbs_get_profile_page_url();
                        if ($profile_page_url): 
                        ?>
                        <p class="description" style="margin-top: 8px;">
                            <?php esc_html_e('Profile page:', 'realestate-booking-suite'); ?> 
                            <a href="<?php echo esc_url($profile_page_url); ?>" target="_blank"><?php echo esc_html($profile_page_url); ?></a>
                            <?php if ($profile_page_id): ?>
                                | <a href="<?php echo esc_url(admin_url('post.php?post=' . absint($profile_page_id) . '&action=edit')); ?>"><?php esc_html_e('Edit Page', 'realestate-booking-suite'); ?></a>
                            <?php endif; ?>
                        </p>
                        <?php else: ?>
                        <button type="button" class="button resbs-create-page-btn" data-page-type="profile"><?php esc_html_e('Create page', 'realestate-booking-suite'); ?></button>
                        <?php endif; ?>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_profile_page_title"><?php esc_html_e('Profile Page Title', 'realestate-booking-suite'); ?></label></th>
                    <td><input type="text" id="resbs_profile_page_title" name="resbs_profile_page_title" value="<?php echo esc_attr(get_option('resbs_profile_page_title', 'User Profile')); ?>" class="regular-text"></td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_profile_page_subtitle"><?php esc_html_e('Profile Page Subtitle', 'realestate-booking-suite'); ?></label></th>
                    <td><input type="text" id="resbs_profile_page_subtitle" name="resbs_profile_page_subtitle" value="<?php echo esc_attr(get_option('resbs_profile_page_subtitle', 'Manage your account and preferences')); ?>" class="regular-text"></td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="resbs-save-button button button-primary"><?php esc_html_e('Save Changes', 'realestate-booking-suite'); ?></button>
                <button type="button" class="button button-secondary resbs-reset-button" onclick="if(confirm('<?php esc_attr_e('Are you sure you want to reset all User Profile settings to default values? This cannot be undone.', 'realestate-booking-suite'); ?>')) { document.getElementById('resetUserProfileForm').submit(); }" style="margin-left: 10px;"><?php esc_html_e('Reset to Defaults', 'realestate-booking-suite'); ?></button>
            </p>
        </form>
        
        <!-- Reset Form -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="resetUserProfileForm" style="display: none;">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_reset_settings">
            <input type="hidden" name="current_tab" value="user-profile">
        </form>
        <?php
    }
    
    /**
     * Log in & Sign up settings tab
     */
    private function login_signup_settings_tab() {
        ?>
        <h2><?php esc_html_e('Log in & Sign up', 'realestate-booking-suite'); ?></h2>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && $_GET['reset'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings reset to defaults successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="login-signup">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable log in form', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_login_form" value="1" <?php checked(get_option('resbs_enable_login_form'), 1); ?>>
                            <?php esc_html_e('Enable log in form', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Add recommended page', 'realestate-booking-suite'); ?></th>
                    <td>
                        <p><?php esc_html_e('Log in page', 'realestate-booking-suite'); ?></p>
                        <button type="button" class="button resbs-create-page-btn" data-page-type="login"><?php esc_html_e('Create page', 'realestate-booking-suite'); ?></button>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_signin_page_title"><?php esc_html_e('Title for sign in page', 'realestate-booking-suite'); ?></label></th>
                    <td><input type="text" id="resbs_signin_page_title" name="resbs_signin_page_title" value="<?php echo esc_attr(get_option('resbs_signin_page_title', 'Sign in or register')); ?>" class="regular-text"></td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_signin_page_subtitle"><?php esc_html_e('Subtitle for sign in page', 'realestate-booking-suite'); ?></label></th>
                    <td><input type="text" id="resbs_signin_page_subtitle" name="resbs_signin_page_subtitle" value="<?php echo esc_attr(get_option('resbs_signin_page_subtitle', 'to save your favourite homes and more')); ?>" class="regular-text"></td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable sign up form for buyers', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_signup_buyers" value="1" <?php checked(get_option('resbs_enable_signup_buyers'), 1); ?>>
                            <?php esc_html_e('Enable sign up form for buyers', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Add recommended page', 'realestate-booking-suite'); ?></th>
                    <td>
                        <p><?php esc_html_e('Buyer registration page', 'realestate-booking-suite'); ?></p>
                        <button type="button" class="button resbs-create-page-btn" data-page-type="buyer-registration"><?php esc_html_e('Create page', 'realestate-booking-suite'); ?></button>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_buyer_signup_title"><?php esc_html_e('Title for buyer sign up page', 'realestate-booking-suite'); ?></label></th>
                    <td><input type="text" id="resbs_buyer_signup_title" name="resbs_buyer_signup_title" value="<?php echo esc_attr(get_option('resbs_buyer_signup_title', 'Get started with your account')); ?>" class="regular-text"></td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_buyer_signup_subtitle"><?php esc_html_e('Subtitle for buyer sign up page', 'realestate-booking-suite'); ?></label></th>
                    <td><input type="text" id="resbs_buyer_signup_subtitle" name="resbs_buyer_signup_subtitle" value="<?php echo esc_attr(get_option('resbs_buyer_signup_subtitle', 'to save your favourite homes and more')); ?>" class="regular-text"></td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Email Verification', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_email_verification" value="1" <?php checked(get_option('resbs_enable_email_verification', 1), 1); ?>>
                            <?php esc_html_e('Require email verification before account activation', 'realestate-booking-suite'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('If enabled, users must verify their email address before they can log in. This helps prevent spam and fake accounts.', 'realestate-booking-suite'); ?></p>
                    </td>
                </tr>
                
            </table>
            
            <p class="submit">
                <button type="submit" class="resbs-save-button button button-primary"><?php esc_html_e('Save Changes', 'realestate-booking-suite'); ?></button>
                <button type="button" class="button button-secondary resbs-reset-button" onclick="if(confirm('<?php esc_attr_e('Are you sure you want to reset all Log in & Sign up settings to default values? This cannot be undone.', 'realestate-booking-suite'); ?>')) { document.getElementById('resetLoginSignupForm').submit(); }" style="margin-left: 10px;"><?php esc_html_e('Reset to Defaults', 'realestate-booking-suite'); ?></button>
            </p>
        </form>
        
        <!-- Reset Form -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="resetLoginSignupForm" style="display: none;">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_reset_settings">
            <input type="hidden" name="current_tab" value="login-signup">
        </form>
        <?php
    }
    
    /**
     * SEO settings tab
     */
    private function seo_settings_tab() {
        ?>
        <h2><?php esc_html_e('SEO', 'realestate-booking-suite'); ?></h2>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && $_GET['reset'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings reset to defaults successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="seo">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable auto tags', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_auto_tags" value="1" <?php checked(get_option('resbs_enable_auto_tags'), 1); ?>>
                            <?php esc_html_e('Enable auto tags', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable clickable tags', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_clickable_tags" value="1" <?php checked(get_option('resbs_enable_clickable_tags'), 1); ?>>
                            <?php esc_html_e('Enable clickable tags', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="resbs_heading_tag_posts_title"><?php esc_html_e('Heading Tag for Posts Title', 'realestate-booking-suite'); ?></label></th>
                    <td>
                        <select id="resbs_heading_tag_posts_title" name="resbs_heading_tag_posts_title">
                            <option value="h1" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h1'); ?>>H1</option>
                            <option value="h2" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h2'); ?>>H2</option>
                            <option value="h3" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h3'); ?>>H3</option>
                            <option value="h4" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h4'); ?>>H4</option>
                            <option value="h5" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h5'); ?>>H5</option>
                            <option value="h6" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h6'); ?>>H6</option>
                        </select>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable dynamic content', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_dynamic_content" value="1" <?php checked(get_option('resbs_enable_dynamic_content'), 1); ?>>
                            <?php esc_html_e('Enable dynamic content', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="resbs-save-button button button-primary"><?php esc_html_e('Save Changes', 'realestate-booking-suite'); ?></button>
                <button type="button" class="button button-secondary resbs-reset-button" onclick="if(confirm('<?php esc_attr_e('Are you sure you want to reset all SEO settings to default values? This cannot be undone.', 'realestate-booking-suite'); ?>')) { document.getElementById('resetSeoForm').submit(); }" style="margin-left: 10px;"><?php esc_html_e('Reset to Defaults', 'realestate-booking-suite'); ?></button>
            </p>
        </form>
        
        <!-- Reset Form -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="resetSeoForm" style="display: none;">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_reset_settings">
            <input type="hidden" name="current_tab" value="seo">
        </form>
        <?php
    }
    
    
    
    
    
    
    
    /**
     * Handle settings save
     */
    public function handle_settings_save() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized', 'realestate-booking-suite'), esc_html__('Error', 'realestate-booking-suite'), array('response' => 403));
        }
        
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'resbs_enhanced_settings-options')) {
            wp_die(esc_html__('Security check failed', 'realestate-booking-suite'), esc_html__('Error', 'realestate-booking-suite'), array('response' => 403));
        }
        
        // Get and sanitize tab
        $tab = isset($_POST['current_tab']) ? sanitize_text_field($_POST['current_tab']) : 'general';
        
        // Validate tab value against allowed tabs
        $allowed_tabs = array('general', 'map', 'listings', 'user-profile', 'login-signup', 'seo');
        if (!in_array($tab, $allowed_tabs, true)) {
            $tab = 'general';
        }
        
        // Handle different tabs
        switch ($tab) {
            case 'general':
                $this->save_general_settings();
                break;
            case 'map':
                $this->save_map_settings();
                break;
            case 'listings':
                $this->save_listings_settings();
                break;
            case 'user-profile':
                $this->save_user_profile_settings();
                break;
            case 'login-signup':
                $this->save_login_signup_settings();
                break;
            case 'seo':
                $this->save_seo_settings();
                break;
        }
        
        wp_safe_redirect(add_query_arg(array('page' => 'resbs-settings', 'tab' => $tab, 'updated' => '1'), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Handle reset settings
     */
    public function handle_reset_settings() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized', 'realestate-booking-suite'), esc_html__('Error', 'realestate-booking-suite'), array('response' => 403));
        }
        
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'resbs_enhanced_settings-options')) {
            wp_die(esc_html__('Security check failed', 'realestate-booking-suite'), esc_html__('Error', 'realestate-booking-suite'), array('response' => 403));
        }
        
        // Get and sanitize tab
        $tab = isset($_POST['current_tab']) ? sanitize_text_field($_POST['current_tab']) : 'general';
        
        // Validate tab value against allowed tabs
        $allowed_tabs = array('general', 'map', 'listings', 'user-profile', 'login-signup', 'seo');
        if (!in_array($tab, $allowed_tabs, true)) {
            $tab = 'general';
        }
        
        // Handle different tabs
        switch ($tab) {
            case 'listings':
                $this->reset_listings_settings();
                break;
            case 'general':
                $this->reset_general_settings();
                break;
            case 'map':
                $this->reset_map_settings();
                break;
            case 'user-profile':
                $this->reset_user_profile_settings();
                break;
            case 'login-signup':
                $this->reset_login_signup_settings();
                break;
            case 'seo':
                $this->reset_seo_settings();
                break;
        }
        
        wp_safe_redirect(add_query_arg(array('page' => 'resbs-settings', 'tab' => $tab, 'reset' => '1'), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Reset listings settings to defaults
     */
    private function reset_listings_settings() {
        // Default values for Listings settings
        $defaults = array(
            'resbs_disable_lightbox_single_page' => '',
            'resbs_enable_request_form_geolocation' => '1',
            'resbs_show_phone_country_code' => '1',
            'resbs_properties_per_page' => '40',
            'resbs_enable_sorting' => '1',
            'resbs_default_sort_option' => 'newest',
            'resbs_show_price' => '1',
            'resbs_show_listing_address' => '1',
            'resbs_enable_map_single_listing' => '1',
            'resbs_enable_wishlist' => '1',
            'resbs_enable_labels' => '1',
            'resbs_enable_sharing' => '1',
            'resbs_show_date_added' => ''
        );
        
        foreach ($defaults as $key => $value) {
            update_option($key, $value);
        }
        
        // Reset sort options to defaults
        update_option('resbs_sort_options', array('newest', 'oldest', 'lowest_price', 'highest_price', 'largest_sqft'));
    }
    
    /**
     * Reset general settings to defaults
     */
    private function reset_general_settings() {
        // Default values for General settings
        $defaults = array(
            'resbs_area_unit' => 'sqft', // First option
            'resbs_lot_size_unit' => 'sqft', // First option
            'resbs_date_format' => 'm/d/Y',
            'resbs_main_color' => '#28a745',
            'resbs_secondary_color' => '#0073aa'
        );
        
        foreach ($defaults as $key => $value) {
            update_option($key, $value);
        }
    }
    
    /**
     * Reset map settings to defaults
     */
    private function reset_map_settings() {
        // Default values for Map settings
        $defaults = array(
            'resbs_default_zoom_level' => '12',
            'resbs_single_property_zoom_level' => '16',
            'resbs_enable_markers_cluster' => '0', // Unchecked by default
            'resbs_cluster_icon' => 'circle', // First option
            'resbs_cluster_icon_color' => '#333333',
            'resbs_map_marker_type' => 'icon', // First option
            'resbs_use_single_map_marker' => '0', // Unchecked by default
            'resbs_single_marker_icon' => 'pin', // First option (Pin) - THIS IS WHAT USER WANTS!
            'resbs_single_marker_color' => '#333333'
        );
        
        foreach ($defaults as $key => $value) {
            update_option($key, $value);
        }
    }
    
    /**
     * Reset user profile settings to defaults
     */
    private function reset_user_profile_settings() {
        // Default values for User Profile settings
        $defaults = array(
            'resbs_enable_user_profile' => '1', // Enabled by default
            'resbs_profile_page_title' => 'User Profile',
            'resbs_profile_page_subtitle' => 'Manage your account and preferences'
        );
        
        foreach ($defaults as $key => $value) {
            update_option($key, $value);
        }
    }
    
    /**
     * Reset login/signup settings to defaults
     */
    private function reset_login_signup_settings() {
        // Default values for Login/Signup settings
        $defaults = array(
            'resbs_enable_login_form' => '0', // Disabled by default
            'resbs_signin_page_title' => 'Sign in or register',
            'resbs_signin_page_subtitle' => 'to save your favourite homes and more',
            'resbs_enable_signup_buyers' => '0', // Disabled by default
            'resbs_buyer_signup_title' => 'Get started with your account',
            'resbs_buyer_signup_subtitle' => 'to save your favourite homes and more',
            'resbs_enable_email_verification' => '1' // Enabled by default (recommended for security)
        );
        
        foreach ($defaults as $key => $value) {
            update_option($key, $value);
        }
    }
    
    /**
     * Reset SEO settings to defaults
     */
    private function reset_seo_settings() {
        // Default values for SEO settings
        $defaults = array(
            'resbs_enable_auto_tags' => '0', // Disabled by default
            'resbs_enable_clickable_tags' => '0', // Disabled by default
            'resbs_heading_tag_posts_title' => 'h1', // First option
            'resbs_enable_dynamic_content' => '0' // Disabled by default
        );
        
        foreach ($defaults as $key => $value) {
            update_option($key, $value);
        }
    }
    
    /**
     * Save general settings
     */
    private function save_general_settings() {
        // Handle area unit radio button (always present in form, so always check)
        if (isset($_POST['resbs_area_unit'])) {
            $area_unit = sanitize_text_field($_POST['resbs_area_unit']);
            // Validate it's either sqft or sqm
            if (in_array($area_unit, array('sqft', 'sqm'))) {
                update_option('resbs_area_unit', $area_unit);
            }
        }
        
        // Handle lot size unit radio button (always present in form, so always check)
        if (isset($_POST['resbs_lot_size_unit'])) {
            $lot_unit = sanitize_text_field($_POST['resbs_lot_size_unit']);
            // Validate it's either sqft or sqm
            if (in_array($lot_unit, array('sqft', 'sqm'))) {
                update_option('resbs_lot_size_unit', $lot_unit);
            }
        }
        
        // Handle date format
        if (isset($_POST['resbs_date_format'])) {
            update_option('resbs_date_format', sanitize_text_field($_POST['resbs_date_format']));
        }
        
        // Handle colors
        if (isset($_POST['resbs_main_color'])) {
            $main_color = sanitize_text_field($_POST['resbs_main_color']);
            // Validate hex color
            if (preg_match('/^#[a-fA-F0-9]{6}$/', $main_color)) {
                update_option('resbs_main_color', $main_color);
            }
        }
        
        if (isset($_POST['resbs_secondary_color'])) {
            $secondary_color = sanitize_text_field($_POST['resbs_secondary_color']);
            // Validate hex color
            if (preg_match('/^#[a-fA-F0-9]{6}$/', $secondary_color)) {
                update_option('resbs_secondary_color', $secondary_color);
            }
        }
        
        // Handle checkboxes - they only appear in POST if checked
        $checkbox_settings = array(
        );
        
        foreach ($checkbox_settings as $setting) {
            if (isset($_POST[$setting]) && $_POST[$setting] == '1') {
                update_option($setting, '1');
            } else {
                update_option($setting, '0');
            }
        }
    }
    
    /**
     * Save map settings
     */
    private function save_map_settings() {
        // Handle default zoom level
        if (isset($_POST['resbs_default_zoom_level'])) {
            $zoom = intval($_POST['resbs_default_zoom_level']);
            if ($zoom >= 0 && $zoom <= 20) {
                update_option('resbs_default_zoom_level', $zoom);
            }
        }
        
        // Handle single property zoom level
        if (isset($_POST['resbs_single_property_zoom_level'])) {
            $zoom = intval($_POST['resbs_single_property_zoom_level']);
            if ($zoom >= 0 && $zoom <= 20) {
                update_option('resbs_single_property_zoom_level', $zoom);
            }
        }
        
        // Handle markers cluster checkbox
        if (isset($_POST['resbs_enable_markers_cluster']) && $_POST['resbs_enable_markers_cluster'] == '1') {
            update_option('resbs_enable_markers_cluster', '1');
        } else {
            update_option('resbs_enable_markers_cluster', '0');
        }
        
        // Handle cluster icon radio button
        if (isset($_POST['resbs_cluster_icon'])) {
            $cluster_icon = sanitize_text_field($_POST['resbs_cluster_icon']);
            if (in_array($cluster_icon, array('circle', 'bubble', 'outline'))) {
                update_option('resbs_cluster_icon', $cluster_icon);
            }
        }
        
        // Handle cluster icon color
        if (isset($_POST['resbs_cluster_icon_color'])) {
            $cluster_color = sanitize_text_field($_POST['resbs_cluster_icon_color']);
            if (preg_match('/^#[a-fA-F0-9]{6}$/', $cluster_color)) {
                update_option('resbs_cluster_icon_color', $cluster_color);
            }
        }
        
        // Handle map marker type radio button
        if (isset($_POST['resbs_map_marker_type'])) {
            $marker_type = sanitize_text_field($_POST['resbs_map_marker_type']);
            if (in_array($marker_type, array('icon', 'price'))) {
                update_option('resbs_map_marker_type', $marker_type);
            }
        }
        
        // Handle use single map marker checkbox
        if (isset($_POST['resbs_use_single_map_marker']) && $_POST['resbs_use_single_map_marker'] == '1') {
            update_option('resbs_use_single_map_marker', '1');
        } else {
            update_option('resbs_use_single_map_marker', '0');
        }
        
        // Handle single marker icon radio button
        if (isset($_POST['resbs_single_marker_icon'])) {
            $single_icon = sanitize_text_field($_POST['resbs_single_marker_icon']);
            if (in_array($single_icon, array('pin', 'outline', 'person'))) {
                update_option('resbs_single_marker_icon', $single_icon);
            }
        }
        
        // Handle single marker color
        if (isset($_POST['resbs_single_marker_color'])) {
            $marker_color = sanitize_text_field($_POST['resbs_single_marker_color']);
            if (preg_match('/^#[a-fA-F0-9]{6}$/', $marker_color)) {
                update_option('resbs_single_marker_color', $marker_color);
            }
        }
        
        // Legacy settings (keep for backwards compatibility)
        if (isset($_POST['resbs_mapbox_access_token'])) {
            update_option('resbs_mapbox_access_token', sanitize_text_field($_POST['resbs_mapbox_access_token']));
        }
        if (isset($_POST['resbs_google_api_key'])) {
            update_option('resbs_google_api_key', sanitize_text_field($_POST['resbs_google_api_key']));
        }
    }
    
    
    /**
     * Save listings settings
     */
    private function save_listings_settings() {
        $settings = array(
            'resbs_disable_lightbox_single_page',
            'resbs_enable_request_form_geolocation',
            'resbs_show_phone_country_code',
            'resbs_properties_per_page',
            'resbs_enable_sorting',
            'resbs_default_sort_option',
            'resbs_show_price',
            'resbs_show_listing_address',
            'resbs_enable_map_single_listing',
            'resbs_enable_wishlist',
            'resbs_enable_labels',
            'resbs_enable_sharing',
            'resbs_show_date_added'
        );
        
        foreach ($settings as $setting) {
            if (isset($_POST[$setting])) {
                $value = sanitize_text_field($_POST[$setting]);
                update_option($setting, $value);
            } else {
                update_option($setting, '');
            }
        }
        
        // Handle array settings
        if (isset($_POST['resbs_sort_options']) && is_array($_POST['resbs_sort_options'])) {
            update_option('resbs_sort_options', array_map('sanitize_text_field', $_POST['resbs_sort_options']));
        }
    }
    
    /**
     * Save user profile settings
     */
    private function save_user_profile_settings() {
        $settings = array(
            'resbs_enable_user_profile',
            'resbs_profile_page_title',
            'resbs_profile_page_subtitle'
        );
        
        foreach ($settings as $setting) {
            if (isset($_POST[$setting])) {
                $value = sanitize_text_field($_POST[$setting]);
                update_option($setting, $value);
            } else {
                update_option($setting, '');
            }
        }
    }
    
    /**
     * Save login/signup settings
     */
    private function save_login_signup_settings() {
        $settings = array(
            'resbs_enable_login_form',
            'resbs_signin_page_title',
            'resbs_signin_page_subtitle',
            'resbs_enable_signup_buyers',
            'resbs_buyer_signup_title',
            'resbs_buyer_signup_subtitle',
            'resbs_enable_email_verification'
        );
        
        foreach ($settings as $setting) {
            if (isset($_POST[$setting])) {
                $value = sanitize_text_field($_POST[$setting]);
                update_option($setting, $value);
            } else {
                update_option($setting, '');
            }
        }
    }
    
    /**
     * Save SEO settings
     */
    private function save_seo_settings() {
        $settings = array(
            'resbs_enable_auto_tags',
            'resbs_enable_clickable_tags',
            'resbs_heading_tag_posts_title',
            'resbs_enable_dynamic_content'
        );
        
        foreach ($settings as $setting) {
            if (isset($_POST[$setting])) {
                $value = sanitize_text_field($_POST[$setting]);
                update_option($setting, $value);
            } else {
                update_option($setting, '');
            }
        }
    }
    
    
    
    
    /**
     * Handle create page AJAX
     */
    public function handle_create_page() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Unauthorized', 'realestate-booking-suite')));
            return;
        }
        
        // Verify nonce
        check_ajax_referer('resbs_create_page_nonce', 'nonce');
        
        // Get and sanitize page type
        if (!isset($_POST['page_type'])) {
            wp_send_json_error(array('message' => esc_html__('Page type is required', 'realestate-booking-suite')));
            return;
        }
        
        $page_type = sanitize_text_field($_POST['page_type']);
        
        // Validate page type against allowed values
        $allowed_page_types = array('profile', 'search', 'login', 'buyer-registration');
        if (!in_array($page_type, $allowed_page_types, true)) {
            wp_send_json_error(array('message' => esc_html__('Invalid page type', 'realestate-booking-suite')));
            return;
        }
        
        // Define page content based on type
        $page_config = array();
        
        switch ($page_type) {
            case 'profile':
                $page_config = array(
                    'title' => resbs_get_profile_page_title(),
                    'content' => '[resbs_dashboard show_profile="yes"]',
                    'slug' => 'profile'
                );
                break;
            case 'search':
                $page_config = array(
                    'title' => 'Search Properties',
                    'content' => '[resbs_search]',
                    'slug' => 'search-properties'
                );
                break;
            case 'login':
                $page_config = array(
                    'title' => get_option('resbs_signin_page_title', 'Sign In'),
                    'content' => '[resbs_login_form]',
                    'slug' => 'sign-in'
                );
                break;
            case 'buyer-registration':
                $page_config = array(
                    'title' => get_option('resbs_buyer_signup_title', 'Get started with your account'),
                    'content' => '[resbs_buyer_registration]',
                    'slug' => 'register'
                );
                break;
            default:
                wp_send_json_error(array('message' => esc_html__('Invalid page type', 'realestate-booking-suite')));
                return;
        }
        
        // Check if page already exists
        $existing_page = get_page_by_path($page_config['slug']);
        if ($existing_page) {
            wp_send_json_error(array(
                'message' => esc_html__('Page already exists', 'realestate-booking-suite'),
                'page_id' => absint($existing_page->ID)
            ));
            return;
        }
        
        $page_data = array(
            'post_title' => $page_config['title'],
            'post_content' => $page_config['content'],
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => $page_config['slug'],
            'post_author' => get_current_user_id()
        );
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id && !is_wp_error($page_id)) {
            // Store page ID in options for profile page
            if ($page_type === 'profile') {
                update_option('resbs_profile_page_id', $page_id);
            }
            
            wp_send_json_success(array(
                'page_id' => absint($page_id),
                'message' => esc_html__('Page created successfully', 'realestate-booking-suite'),
                'edit_url' => esc_url_raw(admin_url('post.php?post=' . absint($page_id) . '&action=edit')),
                'view_url' => esc_url_raw(get_permalink($page_id))
            ));
        } else {
            wp_send_json_error(array('message' => esc_html__('Failed to create page', 'realestate-booking-suite')));
        }
    }
    
    /**
     * Handle AJAX tab content loading
     */
    public function handle_load_tab_content() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Unauthorized', 'realestate-booking-suite')));
            return;
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_load_tab_content')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed', 'realestate-booking-suite')));
            return;
        }
        
        // Get and sanitize tab
        $tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'general';
        
        // Validate tab value against allowed tabs
        $allowed_tabs = array('general', 'map', 'listings', 'user-profile', 'login-signup', 'seo');
        if (!in_array($tab, $allowed_tabs, true)) {
            $tab = 'general';
        }
        
        ob_start();
        switch ($tab) {
            case 'general':
                $this->general_settings_tab();
                break;
            case 'map':
                $this->map_settings_tab();
                break;
            case 'listings':
                $this->listings_settings_tab();
                break;
            case 'user-profile':
                $this->user_profile_settings_tab();
                break;
            case 'login-signup':
                $this->login_signup_settings_tab();
                break;
            case 'seo':
                $this->seo_settings_tab();
                break;
            default:
                $this->general_settings_tab();
        }
        $content = ob_get_clean();
        
        // Content is HTML output from trusted admin functions, but we should still validate it's not empty
        if (empty($content)) {
            wp_send_json_error(array('message' => esc_html__('No content available', 'realestate-booking-suite')));
            return;
        }
        
        wp_send_json_success(array('content' => $content));
    }
    
    /**
     * Test AJAX handler
     */
    public function handle_test_ajax() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Unauthorized'));
            return;
        }
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_test_ajax_nonce')) {
            wp_send_json_error(array('message' => 'Security check failed'));
            return;
        }
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('RESBS TEST AJAX: Called');
        }
        wp_send_json_success(array('message' => 'AJAX is working!', 'timestamp' => time()));
    }
    
    /**
     * Fix menu highlighting for Properties pages
     * Ensures correct parent menu is highlighted
     */
    public function fix_properties_menu_highlight($parent_file) {
        global $typenow;
        
        // Only apply to property post type pages
        if ($typenow === 'property') {
            return 'resbs-main-menu';
        }
        
        return $parent_file;
    }
    
    /**
     * Fix submenu highlighting for All Properties vs My Properties
     * Ensures only the correct submenu item is highlighted
     */
    public function fix_properties_submenu_highlight($submenu_file) {
        global $typenow, $pagenow;
        
        // Only apply to property post type pages
        if ($typenow !== 'property' || $pagenow !== 'edit.php') {
            return $submenu_file;
        }
        
        // Check if we're viewing "My Properties" (author filter is set and matches current user)
        $current_user_id = get_current_user_id();
        $author_filter = isset($_GET['author']) ? intval($_GET['author']) : 0;
        
        if ($author_filter > 0 && $author_filter === $current_user_id) {
            // We're viewing "My Properties" - highlight that menu item
            return 'edit.php?post_type=property&author=' . $current_user_id;
        }
        
        // For "All Properties" (no author filter or different author), let WordPress handle it
        // Return the default to ensure "All Properties" is highlighted
        return $submenu_file;
    }
    
    // Placeholder methods for other menu items
    public function dashboard_callback() {
        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'realestate-booking-suite'));
        }
        
        // Get property counts
        $property_counts = wp_count_posts('property');
        $total_properties = $property_counts->publish;
        $draft_properties = $property_counts->draft;
        $pending_properties = $property_counts->pending;
        $private_properties = $property_counts->private;
        $trash_properties = $property_counts->trash;
        
        // Get current user's properties count
        $current_user_id = get_current_user_id();
        $my_properties_count = count_user_posts($current_user_id, 'property', true);
        
        // Get recent properties
        $recent_properties = get_posts(array(
            'post_type' => 'property',
            'posts_per_page' => 5,
            'post_status' => array('publish', 'pending', 'draft'),
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        // Get system info
        $wp_version = get_bloginfo('version');
        $php_version = PHP_VERSION;
        $plugin_version = '1.0.0';
        
        // Get property types count
        $property_types = get_terms(array(
            'taxonomy' => 'property_type',
            'hide_empty' => false,
        ));
        $property_types_count = !is_wp_error($property_types) ? count($property_types) : 0;
        
        // Get property locations count
        $property_locations = get_terms(array(
            'taxonomy' => 'property_location',
            'hide_empty' => false,
        ));
        $property_locations_count = !is_wp_error($property_locations) ? count($property_locations) : 0;
        
        ?>
        <div class="wrap resbs-admin-wrap">
            <!-- Welcome Header -->
            <div class="resbs-welcome-header" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ddd; border-radius: 4px;">
                <div class="resbs-welcome-content">
                    <h1 style="margin: 0; font-size: 24px; color: #333;">
                        <span class="dashicons dashicons-building" style="vertical-align: middle; margin-right: 8px;"></span>
                        <?php esc_html_e('RealEstate Booking Suite', 'realestate-booking-suite'); ?>
                    </h1>
                    <p style="margin: 8px 0 0 0; font-size: 14px; color: #666;">
                        <?php esc_html_e('Professional real estate booking and management system', 'realestate-booking-suite'); ?>
                    </p>
                </div>
            </div>

            <!-- Statistics Overview -->
            <div class="resbs-stats-overview" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
                <div class="resbs-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; color: #666;">
                            <span class="dashicons dashicons-building"></span>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #333;"><?php echo esc_html($total_properties); ?></div>
                            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Published Properties', 'realestate-booking-suite'); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; color: #666;">
                            <span class="dashicons dashicons-edit"></span>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #333;"><?php echo esc_html($draft_properties); ?></div>
                            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Draft Properties', 'realestate-booking-suite'); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; color: #666;">
                            <span class="dashicons dashicons-admin-users"></span>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #333;"><?php echo esc_html($my_properties_count); ?></div>
                            <div style="color: #666; font-size: 14px;"><?php esc_html_e('My Properties', 'realestate-booking-suite'); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; color: #666;">
                            <span class="dashicons dashicons-clock"></span>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #333;"><?php echo esc_html($pending_properties); ?></div>
                            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Pending Review', 'realestate-booking-suite'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin: 20px 0;">
                <!-- Quick Actions -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <h2 style="margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                        <span class="dashicons dashicons-admin-tools" style="vertical-align: middle;"></span>
                        <?php esc_html_e('Quick Actions', 'realestate-booking-suite'); ?>
                    </h2>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 20px;">
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=property')); ?>" style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333;">
                            <span class="dashicons dashicons-list-view" style="font-size: 24px; color: #666;"></span>
                            <div>
                                <strong><?php esc_html_e('All Properties', 'realestate-booking-suite'); ?></strong>
                                <div style="font-size: 12px; color: #666;"><?php esc_html_e('View all properties', 'realestate-booking-suite'); ?></div>
                            </div>
                        </a>
                        
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=property&author=' . $current_user_id)); ?>" style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333;">
                            <span class="dashicons dashicons-admin-users" style="font-size: 24px; color: #666;"></span>
                            <div>
                                <strong><?php esc_html_e('My Properties', 'realestate-booking-suite'); ?></strong>
                                <div style="font-size: 12px; color: #666;"><?php esc_html_e('View my properties', 'realestate-booking-suite'); ?></div>
                            </div>
                        </a>
                        
                        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=property')); ?>" style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333;">
                            <span class="dashicons dashicons-plus-alt" style="font-size: 24px; color: #666;"></span>
                            <div>
                                <strong><?php esc_html_e('Add New Property', 'realestate-booking-suite'); ?></strong>
                                <div style="font-size: 12px; color: #666;"><?php esc_html_e('Create new listing', 'realestate-booking-suite'); ?></div>
                            </div>
                        </a>
                        
                        <a href="<?php echo esc_url(admin_url('admin.php?page=resbs-settings')); ?>" style="display: flex; align-items: center; gap: 10px; padding: 15px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #333;">
                            <span class="dashicons dashicons-admin-settings" style="font-size: 24px; color: #666;"></span>
                            <div>
                                <strong><?php esc_html_e('Settings', 'realestate-booking-suite'); ?></strong>
                                <div style="font-size: 12px; color: #666;"><?php esc_html_e('Configure plugin', 'realestate-booking-suite'); ?></div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Recent Properties -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                        <h2 style="margin: 0;">
                            <span class="dashicons dashicons-clock" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Recent Properties', 'realestate-booking-suite'); ?>
                        </h2>
                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=property')); ?>" style="font-size: 12px; color: #0073aa; text-decoration: none;">
                            <?php esc_html_e('View All', 'realestate-booking-suite'); ?>
                        </a>
                    </div>
                    <div>
                        <?php if (!empty($recent_properties)): ?>
                            <?php foreach ($recent_properties as $property): ?>
                                <div style="display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f0f0f0;">
                                    <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                        <?php 
                                        $thumbnail = get_the_post_thumbnail($property->ID, array(50, 50));
                                        if ($thumbnail): 
                                            echo wp_kses_post($thumbnail);
                                        else: ?>
                                            <span class="dashicons dashicons-format-image" style="color: #999;"></span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <a href="<?php echo esc_url(get_edit_post_link($property->ID)); ?>" style="font-weight: 600; color: #333; text-decoration: none; display: block; margin-bottom: 4px;">
                                            <?php echo esc_html(wp_trim_words($property->post_title, 5)); ?>
                                        </a>
                                        <div style="font-size: 12px; color: #666;">
                                            <?php echo esc_html(human_time_diff(strtotime($property->post_date), current_time('timestamp')) . ' ' . esc_html__('ago', 'realestate-booking-suite')); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <span style="display: inline-block; padding: 4px 8px; background: #e0f2fe; color: #0369a1; border-radius: 4px; font-size: 11px; font-weight: 600;">
                                            <?php echo esc_html(ucfirst($property->post_status)); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; padding: 40px 20px; color: #999;">
                                <span class="dashicons dashicons-building" style="font-size: 48px; display: block; margin-bottom: 10px;"></span>
                                <p><?php esc_html_e('No properties found.', 'realestate-booking-suite'); ?></p>
                                <a href="<?php echo esc_url(admin_url('post-new.php?post_type=property')); ?>" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: #0073aa; color: white; text-decoration: none; border-radius: 4px;">
                                    <?php esc_html_e('Add Property', 'realestate-booking-suite'); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 20px 0;">
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="margin-top: 0;">
                        <span class="dashicons dashicons-category" style="vertical-align: middle;"></span>
                        <?php esc_html_e('Property Types', 'realestate-booking-suite'); ?>
                    </h3>
                    <div style="font-size: 32px; font-weight: bold; color: #333; margin: 10px 0;">
                        <?php echo esc_html($property_types_count); ?>
                    </div>
                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=property_type&post_type=property')); ?>" style="font-size: 12px; color: #0073aa; text-decoration: none;">
                        <?php esc_html_e('Manage Types →', 'realestate-booking-suite'); ?>
                    </a>
                </div>
                
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="margin-top: 0;">
                        <span class="dashicons dashicons-location" style="vertical-align: middle;"></span>
                        <?php esc_html_e('Locations', 'realestate-booking-suite'); ?>
                    </h3>
                    <div style="font-size: 32px; font-weight: bold; color: #333; margin: 10px 0;">
                        <?php echo esc_html($property_locations_count); ?>
                    </div>
                    <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=property_location&post_type=property')); ?>" style="font-size: 12px; color: #0073aa; text-decoration: none;">
                        <?php esc_html_e('Manage Locations →', 'realestate-booking-suite'); ?>
                    </a>
                </div>
                
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <h3 style="margin-top: 0;">
                        <span class="dashicons dashicons-info" style="vertical-align: middle;"></span>
                        <?php esc_html_e('System Info', 'realestate-booking-suite'); ?>
                    </h3>
                    <div style="font-size: 12px; color: #666; line-height: 1.8;">
                        <div><strong><?php esc_html_e('Plugin:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html($plugin_version); ?></div>
                        <div><strong><?php esc_html_e('WordPress:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html($wp_version); ?></div>
                        <div><strong><?php esc_html_e('PHP:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html($php_version); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    public function data_manager_callback() {
        echo '<h1>' . esc_html__('Data Manager', 'realestate-booking-suite') . '</h1>';
        echo '<p>' . esc_html__('Data manager functionality will be implemented here.', 'realestate-booking-suite') . '</p>';
    }
    
    public function fields_builder_callback() {
        echo '<h1>' . esc_html__('Fields Builder', 'realestate-booking-suite') . '</h1>';
        echo '<p>' . esc_html__('Fields builder functionality will be implemented here.', 'realestate-booking-suite') . '</p>';
    }
    
    public function demo_content_callback() {
        echo '<h1>' . esc_html__('Demo Content', 'realestate-booking-suite') . '</h1>';
        echo '<p>' . esc_html__('Demo content functionality will be implemented here.', 'realestate-booking-suite') . '</p>';
    }
}
