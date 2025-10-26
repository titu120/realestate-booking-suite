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
            array($this, 'settings_page_callback'),
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
            array($this, 'settings_page_callback')
        );
        
        // My Properties
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('My Properties', 'realestate-booking-suite'),
            esc_html__('My Properties', 'realestate-booking-suite'),
            'manage_options',
            'edit.php?post_type=property'
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
        register_setting('resbs_enhanced_settings', 'resbs_language');
        register_setting('resbs_enhanced_settings', 'resbs_area_unit');
        register_setting('resbs_enhanced_settings', 'resbs_lot_size_unit');
        register_setting('resbs_enhanced_settings', 'resbs_date_format');
        register_setting('resbs_enhanced_settings', 'resbs_time_format');
        register_setting('resbs_enhanced_settings', 'resbs_enable_white_label');
        register_setting('resbs_enhanced_settings', 'resbs_enable_rest_support');
        register_setting('resbs_enhanced_settings', 'resbs_logo_image');
        register_setting('resbs_enhanced_settings', 'resbs_main_color');
        register_setting('resbs_enhanced_settings', 'resbs_secondary_color');
        register_setting('resbs_enhanced_settings', 'resbs_disable_tel_country_code');
        
        // Map Settings
        register_setting('resbs_enhanced_settings', 'resbs_google_api_key');
        register_setting('resbs_enhanced_settings', 'resbs_default_latitude');
        register_setting('resbs_enhanced_settings', 'resbs_default_longitude');
        register_setting('resbs_enhanced_settings', 'resbs_default_zoom_level');
        register_setting('resbs_enhanced_settings', 'resbs_single_property_zoom_level');
        register_setting('resbs_enhanced_settings', 'resbs_enable_markers_cluster');
        register_setting('resbs_enhanced_settings', 'resbs_cluster_icon');
        register_setting('resbs_enhanced_settings', 'resbs_cluster_icon_color');
        register_setting('resbs_enhanced_settings', 'resbs_map_marker_type');
        register_setting('resbs_enhanced_settings', 'resbs_use_single_map_marker');
        register_setting('resbs_enhanced_settings', 'resbs_single_marker_icon');
        register_setting('resbs_enhanced_settings', 'resbs_single_marker_color');
        
        // Google reCAPTCHA Settings
        register_setting('resbs_enhanced_settings', 'resbs_recaptcha_version');
        register_setting('resbs_enhanced_settings', 'resbs_recaptcha_site_key');
        register_setting('resbs_enhanced_settings', 'resbs_recaptcha_secret_key');
        register_setting('resbs_enhanced_settings', 'resbs_recaptcha_signup');
        register_setting('resbs_enhanced_settings', 'resbs_recaptcha_signin');
        register_setting('resbs_enhanced_settings', 'resbs_recaptcha_reset_password');
        register_setting('resbs_enhanced_settings', 'resbs_recaptcha_request_form');
        
        // Listings Settings
        register_setting('resbs_enhanced_settings', 'resbs_general_listing_name');
        register_setting('resbs_enhanced_settings', 'resbs_default_property_image');
        register_setting('resbs_enhanced_settings', 'resbs_default_layout_listings');
        register_setting('resbs_enhanced_settings', 'resbs_enable_grid_view');
        register_setting('resbs_enhanced_settings', 'resbs_default_layout_single');
        register_setting('resbs_enhanced_settings', 'resbs_enable_default_archive_template');
        register_setting('resbs_enhanced_settings', 'resbs_enable_collapsed_description');
        register_setting('resbs_enhanced_settings', 'resbs_disable_lightbox_single_page');
        register_setting('resbs_enhanced_settings', 'resbs_enable_request_form_geolocation');
        register_setting('resbs_enhanced_settings', 'resbs_default_tel_code');
        register_setting('resbs_enhanced_settings', 'resbs_hide_request_info_button');
        register_setting('resbs_enhanced_settings', 'resbs_property_headings_font');
        register_setting('resbs_enhanced_settings', 'resbs_property_content_font');
        register_setting('resbs_enhanced_settings', 'resbs_property_item_carousel');
        register_setting('resbs_enhanced_settings', 'resbs_property_item_image_size');
        register_setting('resbs_enhanced_settings', 'resbs_properties_per_page');
        register_setting('resbs_enhanced_settings', 'resbs_enable_sorting');
        register_setting('resbs_enhanced_settings', 'resbs_sort_options');
        register_setting('resbs_enhanced_settings', 'resbs_default_sort_option');
        register_setting('resbs_enhanced_settings', 'resbs_show_price');
        register_setting('resbs_enhanced_settings', 'resbs_show_listing_address');
        register_setting('resbs_enhanced_settings', 'resbs_listing_preview_block');
        register_setting('resbs_enhanced_settings', 'resbs_show_description_listing_box');
        register_setting('resbs_enhanced_settings', 'resbs_enable_map_single_listing');
        register_setting('resbs_enhanced_settings', 'resbs_enable_wishlist');
        register_setting('resbs_enhanced_settings', 'resbs_enable_labels');
        register_setting('resbs_enhanced_settings', 'resbs_enable_sharing');
        register_setting('resbs_enhanced_settings', 'resbs_show_date_added');
        
        // Archive Page Settings
        register_setting('resbs_enhanced_settings', 'resbs_archive_layout');
        register_setting('resbs_enhanced_settings', 'resbs_archive_grid_columns');
        register_setting('resbs_enhanced_settings', 'resbs_archive_items_per_page');
        register_setting('resbs_enhanced_settings', 'resbs_archive_show_filters');
        register_setting('resbs_enhanced_settings', 'resbs_archive_show_search');
        register_setting('resbs_enhanced_settings', 'resbs_archive_show_sorting');
        register_setting('resbs_enhanced_settings', 'resbs_archive_show_pagination');
        register_setting('resbs_enhanced_settings', 'resbs_archive_card_style');
        register_setting('resbs_enhanced_settings', 'resbs_archive_image_size');
        register_setting('resbs_enhanced_settings', 'resbs_archive_show_excerpt');
        register_setting('resbs_enhanced_settings', 'resbs_archive_excerpt_length');
        register_setting('resbs_enhanced_settings', 'resbs_archive_show_meta');
        register_setting('resbs_enhanced_settings', 'resbs_archive_meta_fields');
        
        // Listing Search Settings
        register_setting('resbs_enhanced_settings', 'resbs_address_field_placeholder');
        register_setting('resbs_enhanced_settings', 'resbs_enable_saved_search');
        register_setting('resbs_enhanced_settings', 'resbs_enable_auto_update_search');
        register_setting('resbs_enhanced_settings', 'resbs_enable_autocomplete_locations');
        register_setting('resbs_enhanced_settings', 'resbs_search_filters');
        
        // User Profile Settings
        register_setting('resbs_enhanced_settings', 'resbs_enable_user_profile');
        register_setting('resbs_enhanced_settings', 'resbs_profile_page_title');
        register_setting('resbs_enhanced_settings', 'resbs_profile_page_subtitle');
        
        // Log in & Sign up Settings
        register_setting('resbs_enhanced_settings', 'resbs_enable_login_form');
        register_setting('resbs_enhanced_settings', 'resbs_enable_login_facebook');
        register_setting('resbs_enhanced_settings', 'resbs_enable_login_google');
        register_setting('resbs_enhanced_settings', 'resbs_signin_page_title');
        register_setting('resbs_enhanced_settings', 'resbs_signin_page_subtitle');
        register_setting('resbs_enhanced_settings', 'resbs_enable_signup_buyers');
        register_setting('resbs_enhanced_settings', 'resbs_buyer_signup_title');
        register_setting('resbs_enhanced_settings', 'resbs_buyer_signup_subtitle');
        register_setting('resbs_enhanced_settings', 'resbs_enable_signup_agents');
        
        // SEO Settings
        register_setting('resbs_enhanced_settings', 'resbs_enable_auto_tags');
        register_setting('resbs_enhanced_settings', 'resbs_enable_clickable_tags');
        register_setting('resbs_enhanced_settings', 'resbs_heading_tag_posts_title');
        register_setting('resbs_enhanced_settings', 'resbs_enable_dynamic_content');
        
        // Sharing Settings
        register_setting('resbs_enhanced_settings', 'resbs_enable_sharing_link');
        register_setting('resbs_enhanced_settings', 'resbs_enable_sharing_social');
        register_setting('resbs_enhanced_settings', 'resbs_sharing_options');
        register_setting('resbs_enhanced_settings', 'resbs_enable_sharing_pdf');
        
        // URL Slug Settings
        register_setting('resbs_enhanced_settings', 'resbs_property_slug');
        register_setting('resbs_enhanced_settings', 'resbs_property_category_slug');
        register_setting('resbs_enhanced_settings', 'resbs_property_type_slug');
        register_setting('resbs_enhanced_settings', 'resbs_property_status_slug');
        register_setting('resbs_enhanced_settings', 'resbs_property_label_slug');
        register_setting('resbs_enhanced_settings', 'resbs_property_amenity_slug');
        register_setting('resbs_enhanced_settings', 'resbs_property_feature_slug');
        
        // Privacy Policy & Terms Settings
        register_setting('resbs_enhanced_settings', 'resbs_enable_privacy_terms');
        register_setting('resbs_enhanced_settings', 'resbs_privacy_terms_signup');
        register_setting('resbs_enhanced_settings', 'resbs_privacy_terms_request_form');
        register_setting('resbs_enhanced_settings', 'resbs_privacy_terms_acceptance_type');
        register_setting('resbs_enhanced_settings', 'resbs_privacy_terms_text');
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts() {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_style('jquery-ui-tabs');
    }
    
    /**
     * Settings page callback
     */
    public function settings_page_callback() {
        $this->current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        ?>
        <div class="wrap resbs-enhanced-settings">
            <div class="resbs-settings-header">
                <h1><?php esc_html_e('Settings', 'realestate-booking-suite'); ?></h1>
                <div class="resbs-plugin-info">
                    <span class="resbs-plugin-logo">🏢</span>
                    <span class="resbs-plugin-name">RealEstate Booking Suite</span>
                    <span class="resbs-plugin-version">Version 1.0.0</span>
                </div>
            </div>
            
            <div class="resbs-settings-container">
                <div class="resbs-settings-sidebar">
                    <div class="resbs-settings-nav">
                        <h3><?php esc_html_e('Settings', 'realestate-booking-suite'); ?></h3>
                        <ul class="resbs-nav-tabs">
                            <li><a href="?page=resbs-settings&tab=general" class="<?php echo $this->current_tab === 'general' ? 'active' : ''; ?>"><?php esc_html_e('General', 'realestate-booking-suite'); ?></a></li>
                            <li><a href="?page=resbs-settings&tab=map" class="<?php echo $this->current_tab === 'map' ? 'active' : ''; ?>"><?php esc_html_e('Map', 'realestate-booking-suite'); ?></a></li>
                            <li><a href="?page=resbs-settings&tab=recaptcha" class="<?php echo $this->current_tab === 'recaptcha' ? 'active' : ''; ?>"><?php esc_html_e('Google reCAPTCHA', 'realestate-booking-suite'); ?></a></li>
                            <li><a href="?page=resbs-settings&tab=listings" class="<?php echo $this->current_tab === 'listings' ? 'active' : ''; ?>"><?php esc_html_e('Listings', 'realestate-booking-suite'); ?></a></li>
                            <li><a href="?page=resbs-settings&tab=archive" class="<?php echo $this->current_tab === 'archive' ? 'active' : ''; ?>"><?php esc_html_e('Archive Pages', 'realestate-booking-suite'); ?></a></li>
                            <li><a href="?page=resbs-settings&tab=search" class="<?php echo $this->current_tab === 'search' ? 'active' : ''; ?>"><?php esc_html_e('Listing Search', 'realestate-booking-suite'); ?></a></li>
                            <li><a href="?page=resbs-settings&tab=user-profile" class="<?php echo $this->current_tab === 'user-profile' ? 'active' : ''; ?>"><?php esc_html_e('User Profile', 'realestate-booking-suite'); ?></a></li>
                            <li><a href="?page=resbs-settings&tab=login-signup" class="<?php echo $this->current_tab === 'login-signup' ? 'active' : ''; ?>"><?php esc_html_e('Log in & Sign up', 'realestate-booking-suite'); ?></a></li>
                            <li><a href="?page=resbs-settings&tab=seo" class="<?php echo $this->current_tab === 'seo' ? 'active' : ''; ?>"><?php esc_html_e('SEO', 'realestate-booking-suite'); ?></a></li>
                            <li><a href="?page=resbs-settings&tab=sharing" class="<?php echo $this->current_tab === 'sharing' ? 'active' : ''; ?>"><?php esc_html_e('Sharing', 'realestate-booking-suite'); ?></a></li>
                            <li><a href="?page=resbs-settings&tab=url-slug" class="<?php echo $this->current_tab === 'url-slug' ? 'active' : ''; ?>"><?php esc_html_e('URL Slug', 'realestate-booking-suite'); ?></a></li>
                            <li><a href="?page=resbs-settings&tab=privacy-terms" class="<?php echo $this->current_tab === 'privacy-terms' ? 'active' : ''; ?>"><?php esc_html_e('Privacy Policy & Terms of use', 'realestate-booking-suite'); ?></a></li>
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
                        case 'recaptcha':
                            $this->recaptcha_settings_tab();
                            break;
                        case 'listings':
                            $this->listings_settings_tab();
                            break;
                        case 'archive':
                            $this->archive_settings_tab();
                            break;
                        case 'search':
                            $this->search_settings_tab();
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
                        case 'sharing':
                            $this->sharing_settings_tab();
                            break;
                        case 'url-slug':
                            $this->url_slug_settings_tab();
                            break;
                        case 'privacy-terms':
                            $this->privacy_terms_settings_tab();
                            break;
                        default:
                            $this->general_settings_tab();
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <style>
        .resbs-enhanced-settings {
            margin: 20px 0;
        }
        
        .resbs-settings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .resbs-plugin-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .resbs-plugin-logo {
            font-size: 24px;
        }
        
        .resbs-plugin-name {
            font-weight: bold;
            font-size: 16px;
        }
        
        .resbs-plugin-version {
            color: #666;
            font-size: 14px;
        }
        
        .resbs-settings-container {
            display: flex;
            gap: 30px;
        }
        
        .resbs-settings-sidebar {
            width: 250px;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            height: fit-content;
        }
        
        .resbs-settings-nav h3 {
            margin: 0 0 20px 0;
            font-size: 16px;
            color: #333;
        }
        
        .resbs-nav-tabs {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .resbs-nav-tabs li {
            margin: 0;
        }
        
        .resbs-nav-tabs a {
            display: block;
            padding: 12px 16px;
            text-decoration: none;
            color: #555;
            border-radius: 6px;
            margin-bottom: 4px;
            transition: all 0.3s ease;
        }
        
        .resbs-nav-tabs a:hover {
            background: #e9ecef;
            color: #333;
        }
        
        .resbs-nav-tabs a.active {
            background: #007cba;
            color: white;
            position: relative;
        }
        
        .resbs-nav-tabs a.active::before {
            content: '';
            position: absolute;
            left: -20px;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #007cba;
            border-radius: 0 2px 2px 0;
        }
        
        .resbs-settings-content {
            flex: 1;
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .resbs-settings-content h2 {
            margin: 0 0 30px 0;
            color: #333;
            font-size: 24px;
        }
        
        .resbs-form-group {
            margin-bottom: 25px;
        }
        
        .resbs-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .resbs-form-group input[type="text"],
        .resbs-form-group input[type="email"],
        .resbs-form-group input[type="number"],
        .resbs-form-group input[type="url"],
        .resbs-form-group select,
        .resbs-form-group textarea {
            width: 100%;
            max-width: 500px;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .resbs-form-group input[type="color"] {
            width: 60px;
            height: 40px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }
        
        .resbs-toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .resbs-toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .resbs-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .resbs-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .resbs-slider {
            background-color: #007cba;
        }
        
        input:checked + .resbs-slider:before {
            transform: translateX(26px);
        }
        
        .resbs-checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        
        .resbs-checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .resbs-checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }
        
        .resbs-layout-options {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        
        .resbs-layout-option {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 120px;
        }
        
        .resbs-layout-option:hover {
            border-color: #007cba;
        }
        
        .resbs-layout-option.selected {
            border-color: #007cba;
            background: #f0f8ff;
        }
        
        .resbs-layout-option input[type="radio"] {
            margin-bottom: 10px;
        }
        
        .resbs-layout-preview {
            width: 60px;
            height: 40px;
            background: #f0f0f0;
            margin: 0 auto 10px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #666;
        }
        
        .resbs-save-button {
            background: #ff6b35;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 30px;
        }
        
        .resbs-save-button:hover {
            background: #e55a2b;
        }
        
        .resbs-description {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
            font-style: italic;
        }
        
        .resbs-pro-tag {
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .resbs-page-creation-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .resbs-page-icon {
            width: 40px;
            height: 40px;
            background: #007cba;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        
        .resbs-page-info {
            flex: 1;
        }
        
        .resbs-page-info h4 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .resbs-page-info p {
            margin: 0;
            color: #666;
            font-size: 13px;
        }
        
        .resbs-create-page-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .resbs-create-page-btn:hover {
            background: #5a6268;
        }
        </style>
        <?php
    }
    
    /**
     * General settings tab
     */
    private function general_settings_tab() {
        ?>
        <h2><?php esc_html_e('General', 'realestate-booking-suite'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <label for="resbs_language"><?php esc_html_e('Language', 'realestate-booking-suite'); ?></label>
                <select id="resbs_language" name="resbs_language">
                    <option value=""><?php esc_html_e('Choose language', 'realestate-booking-suite'); ?></option>
                    <option value="en" <?php selected(get_option('resbs_language'), 'en'); ?>><?php esc_html_e('English', 'realestate-booking-suite'); ?></option>
                    <option value="es" <?php selected(get_option('resbs_language'), 'es'); ?>><?php esc_html_e('Spanish', 'realestate-booking-suite'); ?></option>
                    <option value="fr" <?php selected(get_option('resbs_language'), 'fr'); ?>><?php esc_html_e('French', 'realestate-booking-suite'); ?></option>
                    <option value="de" <?php selected(get_option('resbs_language'), 'de'); ?>><?php esc_html_e('German', 'realestate-booking-suite'); ?></option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Units', 'realestate-booking-suite'); ?></label>
                <div style="display: flex; gap: 20px;">
                    <div>
                        <label for="resbs_area_unit"><?php esc_html_e('Area unit', 'realestate-booking-suite'); ?></label>
                        <select id="resbs_area_unit" name="resbs_area_unit">
                            <option value="sq ft" <?php selected(get_option('resbs_area_unit'), 'sq ft'); ?>><?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></option>
                            <option value="sq m" <?php selected(get_option('resbs_area_unit'), 'sq m'); ?>><?php esc_html_e('sq m', 'realestate-booking-suite'); ?></option>
                            <option value="acres" <?php selected(get_option('resbs_area_unit'), 'acres'); ?>><?php esc_html_e('acres', 'realestate-booking-suite'); ?></option>
                        </select>
                    </div>
                    <div>
                        <label for="resbs_lot_size_unit"><?php esc_html_e('Lot size unit', 'realestate-booking-suite'); ?></label>
                        <select id="resbs_lot_size_unit" name="resbs_lot_size_unit">
                            <option value="sq ft" <?php selected(get_option('resbs_lot_size_unit'), 'sq ft'); ?>><?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></option>
                            <option value="sq m" <?php selected(get_option('resbs_lot_size_unit'), 'sq m'); ?>><?php esc_html_e('sq m', 'realestate-booking-suite'); ?></option>
                            <option value="acres" <?php selected(get_option('resbs_lot_size_unit'), 'acres'); ?>><?php esc_html_e('acres', 'realestate-booking-suite'); ?></option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Currency', 'realestate-booking-suite'); ?></label>
                <p><?php esc_html_e('Configure your currency', 'realestate-booking-suite'); ?> <a href="#" target="_blank"><?php esc_html_e('here', 'realestate-booking-suite'); ?></a></p>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_date_format"><?php esc_html_e('Date format', 'realestate-booking-suite'); ?></label>
                <select id="resbs_date_format" name="resbs_date_format">
                    <option value="m/d/Y" <?php selected(get_option('resbs_date_format'), 'm/d/Y'); ?>>10/26/25</option>
                    <option value="d/m/Y" <?php selected(get_option('resbs_date_format'), 'd/m/Y'); ?>>26/10/25</option>
                    <option value="Y-m-d" <?php selected(get_option('resbs_date_format'), 'Y-m-d'); ?>>2025-10-26</option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Time format', 'realestate-booking-suite'); ?></label>
                <div class="resbs-layout-options">
                    <div class="resbs-layout-option">
                        <input type="radio" id="time_12" name="resbs_time_format" value="12" <?php checked(get_option('resbs_time_format'), '12'); ?>>
                        <div class="resbs-layout-preview">12h</div>
                        <label for="time_12"><?php esc_html_e('12-hour clock', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option">
                        <input type="radio" id="time_24" name="resbs_time_format" value="24" <?php checked(get_option('resbs_time_format'), '24'); ?>>
                        <div class="resbs-layout-preview">24h</div>
                        <label for="time_24"><?php esc_html_e('24-hour clock', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <input type="checkbox" name="resbs_enable_white_label" value="1" <?php checked(get_option('resbs_enable_white_label'), 1); ?>>
                    <?php esc_html_e('Enable white label', 'realestate-booking-suite'); ?>
                    <span class="resbs-pro-tag">PRO</span>
                </label>
                <p class="resbs-description"><?php esc_html_e('Enable this option if you want to remove RealEstate Booking Suite logo and "Powered by" link on plugin pages.', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <input type="checkbox" name="resbs_enable_rest_support" value="1" <?php checked(get_option('resbs_enable_rest_support'), 1); ?>>
                    <?php esc_html_e('Enable REST Support', 'realestate-booking-suite'); ?>
                </label>
                <p class="resbs-description"><?php esc_html_e('Please enable REST support if you need to use Gutenberg or pull your listings via wp api.', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_logo_image"><?php esc_html_e('Logo image for admin login page', 'realestate-booking-suite'); ?></label>
                <input type="button" class="button" value="<?php esc_html_e('Upload image', 'realestate-booking-suite'); ?>" onclick="document.getElementById('resbs_logo_image').click();">
                <input type="file" id="resbs_logo_image" name="resbs_logo_image" style="display: none;">
                <p class="resbs-description"><?php esc_html_e('Maximum file size - 2MB. Allowed file types: JPG, PNG, GIF.', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_main_color"><?php esc_html_e('Main color', 'realestate-booking-suite'); ?></label>
                <input type="color" id="resbs_main_color" name="resbs_main_color" value="<?php echo esc_attr(get_option('resbs_main_color', '#ff6b35')); ?>">
                <button type="button" class="button"><?php esc_html_e('Reset', 'realestate-booking-suite'); ?></button>
                <p class="resbs-description"><?php esc_html_e('For large buttons like Search, Request info, etc.', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_secondary_color"><?php esc_html_e('Secondary color', 'realestate-booking-suite'); ?></label>
                <input type="color" id="resbs_secondary_color" name="resbs_secondary_color" value="<?php echo esc_attr(get_option('resbs_secondary_color', '#28a745')); ?>">
                <button type="button" class="button"><?php esc_html_e('Reset', 'realestate-booking-suite'); ?></button>
                <p class="resbs-description"><?php esc_html_e('For smaller buttons like Search on results page, Contact, etc.', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <input type="checkbox" name="resbs_disable_tel_country_code" value="1" <?php checked(get_option('resbs_disable_tel_country_code'), 1); ?>>
                    <?php esc_html_e('Disable tel country code', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * Archive settings tab - This is the main focus for your request
     */
    private function archive_settings_tab() {
        ?>
        <h2><?php esc_html_e('Archive Pages', 'realestate-booking-suite'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Default Layout for Archive Pages', 'realestate-booking-suite'); ?></label>
                <div class="resbs-layout-options">
                    <div class="resbs-layout-option <?php echo get_option('resbs_archive_layout') === 'grid' ? 'selected' : ''; ?>">
                        <input type="radio" id="archive_grid" name="resbs_archive_layout" value="grid" <?php checked(get_option('resbs_archive_layout'), 'grid'); ?>>
                        <div class="resbs-layout-preview">Grid</div>
                        <label for="archive_grid"><?php esc_html_e('Grid layout', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option <?php echo get_option('resbs_archive_layout') === 'large-grid' ? 'selected' : ''; ?>">
                        <input type="radio" id="archive_large_grid" name="resbs_archive_layout" value="large-grid" <?php checked(get_option('resbs_archive_layout'), 'large-grid'); ?>>
                        <div class="resbs-layout-preview">Large Grid</div>
                        <label for="archive_large_grid"><?php esc_html_e('Large grid layout', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option <?php echo get_option('resbs_archive_layout') === 'list' ? 'selected' : ''; ?>">
                        <input type="radio" id="archive_list" name="resbs_archive_layout" value="list" <?php checked(get_option('resbs_archive_layout'), 'list'); ?>>
                        <div class="resbs-layout-preview">List</div>
                        <label for="archive_list"><?php esc_html_e('List layout', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_archive_grid_columns"><?php esc_html_e('Grid Columns', 'realestate-booking-suite'); ?></label>
                <select id="resbs_archive_grid_columns" name="resbs_archive_grid_columns">
                    <option value="2" <?php selected(get_option('resbs_archive_grid_columns'), '2'); ?>><?php esc_html_e('2 Columns', 'realestate-booking-suite'); ?></option>
                    <option value="3" <?php selected(get_option('resbs_archive_grid_columns'), '3'); ?>><?php esc_html_e('3 Columns', 'realestate-booking-suite'); ?></option>
                    <option value="4" <?php selected(get_option('resbs_archive_grid_columns'), '4'); ?>><?php esc_html_e('4 Columns', 'realestate-booking-suite'); ?></option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_archive_items_per_page"><?php esc_html_e('Items Per Page', 'realestate-booking-suite'); ?></label>
                <input type="number" id="resbs_archive_items_per_page" name="resbs_archive_items_per_page" value="<?php echo esc_attr(get_option('resbs_archive_items_per_page', '12')); ?>" min="1" max="100">
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <input type="checkbox" name="resbs_archive_show_filters" value="1" <?php checked(get_option('resbs_archive_show_filters'), 1); ?>>
                    <?php esc_html_e('Show Filters', 'realestate-booking-suite'); ?>
                </label>
                <p class="resbs-description"><?php esc_html_e('Display filter options on archive pages', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <input type="checkbox" name="resbs_archive_show_search" value="1" <?php checked(get_option('resbs_archive_show_search'), 1); ?>>
                    <?php esc_html_e('Show Search Bar', 'realestate-booking-suite'); ?>
                </label>
                <p class="resbs-description"><?php esc_html_e('Display search bar on archive pages', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <input type="checkbox" name="resbs_archive_show_sorting" value="1" <?php checked(get_option('resbs_archive_show_sorting'), 1); ?>>
                    <?php esc_html_e('Show Sorting Options', 'realestate-booking-suite'); ?>
                </label>
                <p class="resbs-description"><?php esc_html_e('Display sorting dropdown on archive pages', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <input type="checkbox" name="resbs_archive_show_pagination" value="1" <?php checked(get_option('resbs_archive_show_pagination'), 1); ?>>
                    <?php esc_html_e('Show Pagination', 'realestate-booking-suite'); ?>
                </label>
                <p class="resbs-description"><?php esc_html_e('Display pagination controls on archive pages', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_archive_card_style"><?php esc_html_e('Card Style', 'realestate-booking-suite'); ?></label>
                <select id="resbs_archive_card_style" name="resbs_archive_card_style">
                    <option value="modern" <?php selected(get_option('resbs_archive_card_style'), 'modern'); ?>><?php esc_html_e('Modern', 'realestate-booking-suite'); ?></option>
                    <option value="classic" <?php selected(get_option('resbs_archive_card_style'), 'classic'); ?>><?php esc_html_e('Classic', 'realestate-booking-suite'); ?></option>
                    <option value="minimal" <?php selected(get_option('resbs_archive_card_style'), 'minimal'); ?>><?php esc_html_e('Minimal', 'realestate-booking-suite'); ?></option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_archive_image_size"><?php esc_html_e('Image Size', 'realestate-booking-suite'); ?></label>
                <select id="resbs_archive_image_size" name="resbs_archive_image_size">
                    <option value="thumbnail" <?php selected(get_option('resbs_archive_image_size'), 'thumbnail'); ?>><?php esc_html_e('Thumbnail (150x150)', 'realestate-booking-suite'); ?></option>
                    <option value="medium" <?php selected(get_option('resbs_archive_image_size'), 'medium'); ?>><?php esc_html_e('Medium (300x300)', 'realestate-booking-suite'); ?></option>
                    <option value="large" <?php selected(get_option('resbs_archive_image_size'), 'large'); ?>><?php esc_html_e('Large (1024x1024)', 'realestate-booking-suite'); ?></option>
                    <option value="custom" <?php selected(get_option('resbs_archive_image_size'), 'custom'); ?>><?php esc_html_e('Custom Size', 'realestate-booking-suite'); ?></option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <input type="checkbox" name="resbs_archive_show_excerpt" value="1" <?php checked(get_option('resbs_archive_show_excerpt'), 1); ?>>
                    <?php esc_html_e('Show Excerpt', 'realestate-booking-suite'); ?>
                </label>
                <p class="resbs-description"><?php esc_html_e('Display property excerpt on archive cards', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_archive_excerpt_length"><?php esc_html_e('Excerpt Length', 'realestate-booking-suite'); ?></label>
                <input type="number" id="resbs_archive_excerpt_length" name="resbs_archive_excerpt_length" value="<?php echo esc_attr(get_option('resbs_archive_excerpt_length', '150')); ?>" min="50" max="500">
                <p class="resbs-description"><?php esc_html_e('Number of characters to show in excerpt', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <input type="checkbox" name="resbs_archive_show_meta" value="1" <?php checked(get_option('resbs_archive_show_meta'), 1); ?>>
                    <?php esc_html_e('Show Meta Information', 'realestate-booking-suite'); ?>
                </label>
                <p class="resbs-description"><?php esc_html_e('Display property meta information (bedrooms, bathrooms, etc.)', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Meta Fields to Display', 'realestate-booking-suite'); ?></label>
                <div class="resbs-checkbox-group">
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="meta_bedrooms" name="resbs_archive_meta_fields[]" value="bedrooms" <?php echo in_array('bedrooms', (array)get_option('resbs_archive_meta_fields', array())) ? 'checked' : ''; ?>>
                        <label for="meta_bedrooms"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="meta_bathrooms" name="resbs_archive_meta_fields[]" value="bathrooms" <?php echo in_array('bathrooms', (array)get_option('resbs_archive_meta_fields', array())) ? 'checked' : ''; ?>>
                        <label for="meta_bathrooms"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="meta_area" name="resbs_archive_meta_fields[]" value="area" <?php echo in_array('area', (array)get_option('resbs_archive_meta_fields', array())) ? 'checked' : ''; ?>>
                        <label for="meta_area"><?php esc_html_e('Area', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="meta_price" name="resbs_archive_meta_fields[]" value="price" <?php echo in_array('price', (array)get_option('resbs_archive_meta_fields', array())) ? 'checked' : ''; ?>>
                        <label for="meta_price"><?php esc_html_e('Price', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="meta_type" name="resbs_archive_meta_fields[]" value="type" <?php echo in_array('type', (array)get_option('resbs_archive_meta_fields', array())) ? 'checked' : ''; ?>>
                        <label for="meta_type"><?php esc_html_e('Property Type', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="meta_status" name="resbs_archive_meta_fields[]" value="status" <?php echo in_array('status', (array)get_option('resbs_archive_meta_fields', array())) ? 'checked' : ''; ?>>
                        <label for="meta_status"><?php esc_html_e('Status', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
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
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <label for="resbs_google_api_key"><?php esc_html_e('Google Maps API Key', 'realestate-booking-suite'); ?></label>
                <p><?php esc_html_e('To load Google Maps correctly you should enter Google API key. If you don\'t have API key already then', 'realestate-booking-suite'); ?> <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank"><?php esc_html_e('generate it', 'realestate-booking-suite'); ?></a>.</p>
                <input type="text" id="resbs_google_api_key" name="resbs_google_api_key" value="<?php echo esc_attr(get_option('resbs_google_api_key')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_default_latitude"><?php esc_html_e('Default Latitude and Longitude - center of the map', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_default_latitude" name="resbs_default_latitude" value="<?php echo esc_attr(get_option('resbs_default_latitude', '40.7128,-74.0060')); ?>" placeholder="ex: 12.381068,-1.492711">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_default_zoom_level"><?php esc_html_e('Default zoom level', 'realestate-booking-suite'); ?></label>
                <p><?php esc_html_e('Choose the zoom level for the map. 0 corresponds to a map of the earth fully zoomed out, and larger zoom levels zoom in at a higher resolution.', 'realestate-booking-suite'); ?></p>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" class="button" onclick="document.getElementById('resbs_default_zoom_level').stepDown()">-</button>
                    <input type="number" id="resbs_default_zoom_level" name="resbs_default_zoom_level" value="<?php echo esc_attr(get_option('resbs_default_zoom_level', '12')); ?>" min="0" max="20" style="width: 80px;">
                    <button type="button" class="button" onclick="document.getElementById('resbs_default_zoom_level').stepUp()">+</button>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_single_property_zoom_level"><?php esc_html_e('Single property map zoom level', 'realestate-booking-suite'); ?></label>
                <p><?php esc_html_e('Choose the zoom level for the map on single property page. 0 corresponds to a map of the earth fully zoomed out, and larger zoom levels zoom in at a higher resolution.', 'realestate-booking-suite'); ?></p>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" class="button" onclick="document.getElementById('resbs_single_property_zoom_level').stepDown()">-</button>
                    <input type="number" id="resbs_single_property_zoom_level" name="resbs_single_property_zoom_level" value="<?php echo esc_attr(get_option('resbs_single_property_zoom_level', '16')); ?>" min="0" max="20" style="width: 80px;">
                    <button type="button" class="button" onclick="document.getElementById('resbs_single_property_zoom_level').stepUp()">+</button>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_markers_cluster" value="1" <?php checked(get_option('resbs_enable_markers_cluster'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable markers cluster', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Select icon (for markers cluster)', 'realestate-booking-suite'); ?></label>
                <div class="resbs-layout-options">
                    <div class="resbs-layout-option">
                        <input type="radio" id="cluster_circle" name="resbs_cluster_icon" value="circle" <?php checked(get_option('resbs_cluster_icon'), 'circle'); ?>>
                        <div class="resbs-layout-preview">●</div>
                        <label for="cluster_circle"><?php esc_html_e('Circle', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option">
                        <input type="radio" id="cluster_bubble" name="resbs_cluster_icon" value="bubble" <?php checked(get_option('resbs_cluster_icon'), 'bubble'); ?>>
                        <div class="resbs-layout-preview">💬</div>
                        <label for="cluster_bubble"><?php esc_html_e('Bubble', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option">
                        <input type="radio" id="cluster_outline" name="resbs_cluster_icon" value="outline" <?php checked(get_option('resbs_cluster_icon'), 'outline'); ?>>
                        <div class="resbs-layout-preview">○</div>
                        <label for="cluster_outline"><?php esc_html_e('Outline', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_cluster_icon_color"><?php esc_html_e('Icon color (for markers cluster)', 'realestate-booking-suite'); ?></label>
                <input type="color" id="resbs_cluster_icon_color" name="resbs_cluster_icon_color" value="<?php echo esc_attr(get_option('resbs_cluster_icon_color', '#333333')); ?>">
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('What to use as map marker?', 'realestate-booking-suite'); ?></label>
                <div class="resbs-layout-options">
                    <div class="resbs-layout-option">
                        <input type="radio" id="marker_icon" name="resbs_map_marker_type" value="icon" <?php checked(get_option('resbs_map_marker_type'), 'icon'); ?>>
                        <div class="resbs-layout-preview">📍</div>
                        <label for="marker_icon"><?php esc_html_e('Icon', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option">
                        <input type="radio" id="marker_price" name="resbs_map_marker_type" value="price" <?php checked(get_option('resbs_map_marker_type'), 'price'); ?>>
                        <div class="resbs-layout-preview">$</div>
                        <label for="marker_price"><?php esc_html_e('Price', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_use_single_map_marker" value="1" <?php checked(get_option('resbs_use_single_map_marker'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Use single map marker?', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Select icon (for single map marker)', 'realestate-booking-suite'); ?></label>
                <div class="resbs-layout-options">
                    <div class="resbs-layout-option">
                        <input type="radio" id="single_pin" name="resbs_single_marker_icon" value="pin" <?php checked(get_option('resbs_single_marker_icon'), 'pin'); ?>>
                        <div class="resbs-layout-preview">📍</div>
                        <label for="single_pin"><?php esc_html_e('Pin', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option">
                        <input type="radio" id="single_outline" name="resbs_single_marker_icon" value="outline" <?php checked(get_option('resbs_single_marker_icon'), 'outline'); ?>>
                        <div class="resbs-layout-preview">📍</div>
                        <label for="single_outline"><?php esc_html_e('Outline Pin', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option">
                        <input type="radio" id="single_person" name="resbs_single_marker_icon" value="person" <?php checked(get_option('resbs_single_marker_icon'), 'person'); ?>>
                        <div class="resbs-layout-preview">👤</div>
                        <label for="single_person"><?php esc_html_e('Person', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_single_marker_color"><?php esc_html_e('Icon color (for single map marker)', 'realestate-booking-suite'); ?></label>
                <input type="color" id="resbs_single_marker_color" name="resbs_single_marker_color" value="<?php echo esc_attr(get_option('resbs_single_marker_color', '#333333')); ?>">
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * Google reCAPTCHA settings tab
     */
    private function recaptcha_settings_tab() {
        ?>
        <h2><?php esc_html_e('Google reCAPTCHA', 'realestate-booking-suite'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <p><?php esc_html_e('If you don\'t have keys already then', 'realestate-booking-suite'); ?> <a href="https://www.google.com/recaptcha/admin" target="_blank"><?php esc_html_e('generate them', 'realestate-booking-suite'); ?></a>.</p>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('reCAPTCHA version', 'realestate-booking-suite'); ?></label>
                <div class="resbs-layout-options">
                    <div class="resbs-layout-option">
                        <input type="radio" id="recaptcha_v2" name="resbs_recaptcha_version" value="v2" <?php checked(get_option('resbs_recaptcha_version'), 'v2'); ?>>
                        <div class="resbs-layout-preview">v2</div>
                        <label for="recaptcha_v2"><?php esc_html_e('reCAPTCHA v2', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option">
                        <input type="radio" id="recaptcha_v3" name="resbs_recaptcha_version" value="v3" <?php checked(get_option('resbs_recaptcha_version'), 'v3'); ?>>
                        <div class="resbs-layout-preview">v3</div>
                        <label for="recaptcha_v3"><?php esc_html_e('reCAPTCHA v3', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_recaptcha_site_key"><?php esc_html_e('reCAPTCHA site key', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_recaptcha_site_key" name="resbs_recaptcha_site_key" value="<?php echo esc_attr(get_option('resbs_recaptcha_site_key')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_recaptcha_secret_key"><?php esc_html_e('reCAPTCHA secret key', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_recaptcha_secret_key" name="resbs_recaptcha_secret_key" value="<?php echo esc_attr(get_option('resbs_recaptcha_secret_key')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Enable Google reCaptcha to submit forms', 'realestate-booking-suite'); ?></label>
                <div class="resbs-checkbox-group">
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="recaptcha_signup" name="resbs_recaptcha_signup" value="1" <?php checked(get_option('resbs_recaptcha_signup'), 1); ?>>
                        <label for="recaptcha_signup"><?php esc_html_e('Sign up', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="recaptcha_signin" name="resbs_recaptcha_signin" value="1" <?php checked(get_option('resbs_recaptcha_signin'), 1); ?>>
                        <label for="recaptcha_signin"><?php esc_html_e('Sign in', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="recaptcha_reset" name="resbs_recaptcha_reset_password" value="1" <?php checked(get_option('resbs_recaptcha_reset_password'), 1); ?>>
                        <label for="recaptcha_reset"><?php esc_html_e('Reset password', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="recaptcha_request" name="resbs_recaptcha_request_form" value="1" <?php checked(get_option('resbs_recaptcha_request_form'), 1); ?>>
                        <label for="recaptcha_request"><?php esc_html_e('Request form', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * Listings settings tab
     */
    private function listings_settings_tab() {
        ?>
        <h2><?php esc_html_e('Listings', 'realestate-booking-suite'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <label for="resbs_general_listing_name"><?php esc_html_e('General listing name', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_general_listing_name" name="resbs_general_listing_name" value="<?php echo esc_attr(get_option('resbs_general_listing_name', 'Property')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_default_property_image"><?php esc_html_e('Default Property Image', 'realestate-booking-suite'); ?></label>
                <input type="button" class="button" value="<?php esc_html_e('Upload', 'realestate-booking-suite'); ?>" onclick="document.getElementById('resbs_default_property_image').click();">
                <input type="file" id="resbs_default_property_image" name="resbs_default_property_image" style="display: none;">
                <p class="resbs-description"><?php esc_html_e('Maximum file size - 2MB. Allowed file types: JPG, PNG, GIF.', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Default Layout for Listings Pages', 'realestate-booking-suite'); ?></label>
                <div class="resbs-layout-options">
                    <div class="resbs-layout-option <?php echo get_option('resbs_default_layout_listings') === 'grid' ? 'selected' : ''; ?>">
                        <input type="radio" id="layout_grid" name="resbs_default_layout_listings" value="grid" <?php checked(get_option('resbs_default_layout_listings'), 'grid'); ?>>
                        <div class="resbs-layout-preview">Grid</div>
                        <label for="layout_grid"><?php esc_html_e('Grid layout', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option <?php echo get_option('resbs_default_layout_listings') === 'large-grid' ? 'selected' : ''; ?>">
                        <input type="radio" id="layout_large_grid" name="resbs_default_layout_listings" value="large-grid" <?php checked(get_option('resbs_default_layout_listings'), 'large-grid'); ?>>
                        <div class="resbs-layout-preview">Large Grid</div>
                        <label for="layout_large_grid"><?php esc_html_e('Large grid layout', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option <?php echo get_option('resbs_default_layout_listings') === 'list' ? 'selected' : ''; ?>">
                        <input type="radio" id="layout_list" name="resbs_default_layout_listings" value="list" <?php checked(get_option('resbs_default_layout_listings'), 'list'); ?>>
                        <div class="resbs-layout-preview">List</div>
                        <label for="layout_list"><?php esc_html_e('List layout', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_grid_view" value="1" <?php checked(get_option('resbs_enable_grid_view'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable Grid View', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Default Layout for Single Listing Pages', 'realestate-booking-suite'); ?></label>
                <div class="resbs-layout-options">
                    <div class="resbs-layout-option">
                        <input type="radio" id="single_slider" name="resbs_default_layout_single" value="slider" <?php checked(get_option('resbs_default_layout_single'), 'slider'); ?>>
                        <div class="resbs-layout-preview">Slider</div>
                        <label for="single_slider"><?php esc_html_e('Single listing with slider', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option">
                        <input type="radio" id="single_tiled" name="resbs_default_layout_single" value="tiled" <?php checked(get_option('resbs_default_layout_single'), 'tiled'); ?>>
                        <div class="resbs-layout-preview">Tiled</div>
                        <label for="single_tiled"><?php esc_html_e('With tiled gallery', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option">
                        <input type="radio" id="single_left" name="resbs_default_layout_single" value="left-slider" <?php checked(get_option('resbs_default_layout_single'), 'left-slider'); ?>>
                        <div class="resbs-layout-preview">Left</div>
                        <label for="single_left"><?php esc_html_e('With left slider', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_default_archive_template" value="1" <?php checked(get_option('resbs_enable_default_archive_template'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable default archive template', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_collapsed_description" value="1" <?php checked(get_option('resbs_enable_collapsed_description'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable collapsed description', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_disable_lightbox_single_page" value="1" <?php checked(get_option('resbs_disable_lightbox_single_page'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Disable lightBox on single page', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_request_form_geolocation" value="1" <?php checked(get_option('resbs_enable_request_form_geolocation'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable request form geolocation', 'realestate-booking-suite'); ?>
                </label>
                <p class="resbs-description"><?php esc_html_e('This option uses for autofill tel code field by user location.', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_default_tel_code"><?php esc_html_e('Selecting a default tel code in the request form', 'realestate-booking-suite'); ?></label>
                <select id="resbs_default_tel_code" name="resbs_default_tel_code">
                    <option value="+1" <?php selected(get_option('resbs_default_tel_code'), '+1'); ?>>+1 (US/Canada)</option>
                    <option value="+44" <?php selected(get_option('resbs_default_tel_code'), '+44'); ?>>+44 (UK)</option>
                    <option value="+33" <?php selected(get_option('resbs_default_tel_code'), '+33'); ?>>+33 (France)</option>
                    <option value="+49" <?php selected(get_option('resbs_default_tel_code'), '+49'); ?>>+49 (Germany)</option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_hide_request_info_button" value="1" <?php checked(get_option('resbs_hide_request_info_button'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Hide Request Info button', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_property_headings_font"><?php esc_html_e('Property headings font', 'realestate-booking-suite'); ?></label>
                <select id="resbs_property_headings_font" name="resbs_property_headings_font">
                    <option value="Lato" <?php selected(get_option('resbs_property_headings_font'), 'Lato'); ?>>Lato</option>
                    <option value="Open Sans" <?php selected(get_option('resbs_property_headings_font'), 'Open Sans'); ?>>Open Sans</option>
                    <option value="Roboto" <?php selected(get_option('resbs_property_headings_font'), 'Roboto'); ?>>Roboto</option>
                    <option value="Montserrat" <?php selected(get_option('resbs_property_headings_font'), 'Montserrat'); ?>>Montserrat</option>
                </select>
                <button type="button" class="button"><?php esc_html_e('Reset', 'realestate-booking-suite'); ?></button>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_property_content_font"><?php esc_html_e('Property content font', 'realestate-booking-suite'); ?></label>
                <select id="resbs_property_content_font" name="resbs_property_content_font">
                    <option value="Open Sans" <?php selected(get_option('resbs_property_content_font'), 'Open Sans'); ?>>Open Sans</option>
                    <option value="Lato" <?php selected(get_option('resbs_property_content_font'), 'Lato'); ?>>Lato</option>
                    <option value="Roboto" <?php selected(get_option('resbs_property_content_font'), 'Roboto'); ?>>Roboto</option>
                    <option value="Montserrat" <?php selected(get_option('resbs_property_content_font'), 'Montserrat'); ?>>Montserrat</option>
                </select>
                <button type="button" class="button"><?php esc_html_e('Reset', 'realestate-booking-suite'); ?></button>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_property_item_carousel" value="1" <?php checked(get_option('resbs_property_item_carousel'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Property Item Carousel', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_property_item_image_size"><?php esc_html_e('Property Item Image Size', 'realestate-booking-suite'); ?></label>
                <select id="resbs_property_item_image_size" name="resbs_property_item_image_size">
                    <option value="1024x1024" <?php selected(get_option('resbs_property_item_image_size'), '1024x1024'); ?>><?php esc_html_e('1024x1024 - Without crop (large)', 'realestate-booking-suite'); ?></option>
                    <option value="800x600" <?php selected(get_option('resbs_property_item_image_size'), '800x600'); ?>><?php esc_html_e('800x600 - With crop', 'realestate-booking-suite'); ?></option>
                    <option value="600x400" <?php selected(get_option('resbs_property_item_image_size'), '600x400'); ?>><?php esc_html_e('600x400 - With crop', 'realestate-booking-suite'); ?></option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_properties_per_page"><?php esc_html_e('Properties Number Per Page', 'realestate-booking-suite'); ?></label>
                <input type="number" id="resbs_properties_per_page" name="resbs_properties_per_page" value="<?php echo esc_attr(get_option('resbs_properties_per_page', '40')); ?>" min="1" max="100">
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_sorting" value="1" <?php checked(get_option('resbs_enable_sorting'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable Sorting', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Sort Options', 'realestate-booking-suite'); ?></label>
                <div class="resbs-checkbox-group">
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="sort_newest" name="resbs_sort_options[]" value="newest" <?php echo in_array('newest', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>>
                        <label for="sort_newest"><?php esc_html_e('Newest', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="sort_oldest" name="resbs_sort_options[]" value="oldest" <?php echo in_array('oldest', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>>
                        <label for="sort_oldest"><?php esc_html_e('Oldest', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="sort_lowest_price" name="resbs_sort_options[]" value="lowest_price" <?php echo in_array('lowest_price', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>>
                        <label for="sort_lowest_price"><?php esc_html_e('Lowest price', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="sort_highest_price" name="resbs_sort_options[]" value="highest_price" <?php echo in_array('highest_price', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>>
                        <label for="sort_highest_price"><?php esc_html_e('Highest price', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="sort_largest_sqft" name="resbs_sort_options[]" value="largest_sqft" <?php echo in_array('largest_sqft', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>>
                        <label for="sort_largest_sqft"><?php esc_html_e('Largest sq ft', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="sort_lowest_sqft" name="resbs_sort_options[]" value="lowest_sqft" <?php echo in_array('lowest_sqft', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>>
                        <label for="sort_lowest_sqft"><?php esc_html_e('Lowest sq ft', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="sort_bedrooms" name="resbs_sort_options[]" value="bedrooms" <?php echo in_array('bedrooms', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>>
                        <label for="sort_bedrooms"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="sort_bathrooms" name="resbs_sort_options[]" value="bathrooms" <?php echo in_array('bathrooms', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>>
                        <label for="sort_bathrooms"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="sort_featured" name="resbs_sort_options[]" value="featured" <?php echo in_array('featured', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>>
                        <label for="sort_featured"><?php esc_html_e('Featured', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_default_sort_option"><?php esc_html_e('Default Sort Options', 'realestate-booking-suite'); ?></label>
                <select id="resbs_default_sort_option" name="resbs_default_sort_option">
                    <option value="newest" <?php selected(get_option('resbs_default_sort_option'), 'newest'); ?>><?php esc_html_e('Newest', 'realestate-booking-suite'); ?></option>
                    <option value="oldest" <?php selected(get_option('resbs_default_sort_option'), 'oldest'); ?>><?php esc_html_e('Oldest', 'realestate-booking-suite'); ?></option>
                    <option value="lowest_price" <?php selected(get_option('resbs_default_sort_option'), 'lowest_price'); ?>><?php esc_html_e('Lowest price', 'realestate-booking-suite'); ?></option>
                    <option value="highest_price" <?php selected(get_option('resbs_default_sort_option'), 'highest_price'); ?>><?php esc_html_e('Highest price', 'realestate-booking-suite'); ?></option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_show_price" value="1" <?php checked(get_option('resbs_show_price'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Show price', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_show_listing_address" value="1" <?php checked(get_option('resbs_show_listing_address'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Show listing address', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('What to show on the listing preview block?', 'realestate-booking-suite'); ?></label>
                <div class="resbs-layout-options">
                    <div class="resbs-layout-option">
                        <input type="radio" id="preview_address" name="resbs_listing_preview_block" value="address" <?php checked(get_option('resbs_listing_preview_block'), 'address'); ?>>
                        <div class="resbs-layout-preview">Address</div>
                        <label for="preview_address"><?php esc_html_e('Address', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option">
                        <input type="radio" id="preview_title" name="resbs_listing_preview_block" value="title" <?php checked(get_option('resbs_listing_preview_block'), 'title'); ?>>
                        <div class="resbs-layout-preview">Title</div>
                        <label for="preview_title"><?php esc_html_e('Title', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_show_description_listing_box" value="1" <?php checked(get_option('resbs_show_description_listing_box'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Show description in listing box', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_map_single_listing" value="1" <?php checked(get_option('resbs_enable_map_single_listing'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable map on single listing page', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_wishlist" value="1" <?php checked(get_option('resbs_enable_wishlist'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable wishlist', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_labels" value="1" <?php checked(get_option('resbs_enable_labels'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable labels', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_sharing" value="1" <?php checked(get_option('resbs_enable_sharing'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable sharing', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_show_date_added" value="1" <?php checked(get_option('resbs_show_date_added'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Show date added', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * Listing Search settings tab
     */
    private function search_settings_tab() {
        ?>
        <h2><?php esc_html_e('Listing Search', 'realestate-booking-suite'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <label for="resbs_address_field_placeholder"><?php esc_html_e('Address field search placeholder', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_address_field_placeholder" name="resbs_address_field_placeholder" value="<?php echo esc_attr(get_option('resbs_address_field_placeholder', 'Address, City, ZIP')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-page-creation-card">
                <div class="resbs-page-icon">🔑</div>
                <div class="resbs-page-info">
                    <h4><?php esc_html_e('Add recommended page', 'realestate-booking-suite'); ?></h4>
                    <p><?php esc_html_e('Default Search results', 'realestate-booking-suite'); ?></p>
                </div>
                <button type="button" class="resbs-create-page-btn"><?php esc_html_e('Create page', 'realestate-booking-suite'); ?></button>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_saved_search" value="1" <?php checked(get_option('resbs_enable_saved_search'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable saved search', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_auto_update_search" value="1" <?php checked(get_option('resbs_enable_auto_update_search'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable auto-update search results for Simple and Advanced RealEstate Booking Suite search', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_autocomplete_locations" value="1" <?php checked(get_option('resbs_enable_autocomplete_locations'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable autocomplete locations for search', 'realestate-booking-suite'); ?>
                </label>
                <p class="resbs-description"><?php esc_html_e('Autocomplete will be done with data used on your database.', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Filters Options', 'realestate-booking-suite'); ?></label>
                <div class="resbs-checkbox-group">
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_price" name="resbs_search_filters[]" value="price" <?php echo in_array('price', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_price"><?php esc_html_e('Price', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_categories" name="resbs_search_filters[]" value="categories" <?php echo in_array('categories', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_categories"><?php esc_html_e('Categories', 'realestate-booking-suite'); ?></label>
                        <a href="#" target="_blank"><?php esc_html_e('Go to Data manager to edit options.', 'realestate-booking-suite'); ?></a>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_types" name="resbs_search_filters[]" value="types" <?php echo in_array('types', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_types"><?php esc_html_e('Types', 'realestate-booking-suite'); ?></label>
                        <a href="#" target="_blank"><?php esc_html_e('Go to Data manager to edit options.', 'realestate-booking-suite'); ?></a>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_rent_periods" name="resbs_search_filters[]" value="rent_periods" <?php echo in_array('rent_periods', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_rent_periods"><?php esc_html_e('Rent Periods', 'realestate-booking-suite'); ?></label>
                        <a href="#" target="_blank"><?php esc_html_e('Go to Data manager to edit options.', 'realestate-booking-suite'); ?></a>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_bedrooms" name="resbs_search_filters[]" value="bedrooms" <?php echo in_array('bedrooms', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_bedrooms"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_bathrooms" name="resbs_search_filters[]" value="bathrooms" <?php echo in_array('bathrooms', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_bathrooms"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_half_baths" name="resbs_search_filters[]" value="half_baths" <?php echo in_array('half_baths', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_half_baths"><?php esc_html_e('Half baths', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_amenities" name="resbs_search_filters[]" value="amenities" <?php echo in_array('amenities', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_amenities"><?php esc_html_e('Amenities', 'realestate-booking-suite'); ?></label>
                        <a href="#" target="_blank"><?php esc_html_e('Go to Data manager to edit options.', 'realestate-booking-suite'); ?></a>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_features" name="resbs_search_filters[]" value="features" <?php echo in_array('features', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_features"><?php esc_html_e('Features', 'realestate-booking-suite'); ?></label>
                        <a href="#" target="_blank"><?php esc_html_e('Go to Data manager to edit options.', 'realestate-booking-suite'); ?></a>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_area" name="resbs_search_filters[]" value="area" <?php echo in_array('area', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_area"><?php esc_html_e('Area', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_lot_size" name="resbs_search_filters[]" value="lot_size" <?php echo in_array('lot_size', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_lot_size"><?php esc_html_e('Lot size', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_floors" name="resbs_search_filters[]" value="floors" <?php echo in_array('floors', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_floors"><?php esc_html_e('Floors', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="filter_floor_level" name="resbs_search_filters[]" value="floor_level" <?php echo in_array('floor_level', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>>
                        <label for="filter_floor_level"><?php esc_html_e('Floor Level', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * User Profile settings tab
     */
    private function user_profile_settings_tab() {
        ?>
        <h2><?php esc_html_e('User Profile', 'realestate-booking-suite'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_user_profile" value="1" <?php checked(get_option('resbs_enable_user_profile'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable User Profile', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-page-creation-card">
                <div class="resbs-page-icon">🔑</div>
                <div class="resbs-page-info">
                    <h4><?php esc_html_e('Add recommended page', 'realestate-booking-suite'); ?></h4>
                    <p><?php esc_html_e('Profile', 'realestate-booking-suite'); ?></p>
                </div>
                <button type="button" class="resbs-create-page-btn"><?php esc_html_e('Create page', 'realestate-booking-suite'); ?></button>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_profile_page_title"><?php esc_html_e('Profile Page Title', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_profile_page_title" name="resbs_profile_page_title" value="<?php echo esc_attr(get_option('resbs_profile_page_title', 'User Profile')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_profile_page_subtitle"><?php esc_html_e('Profile Page Subtitle', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_profile_page_subtitle" name="resbs_profile_page_subtitle" value="<?php echo esc_attr(get_option('resbs_profile_page_subtitle', 'Manage your account and preferences')); ?>" class="regular-text">
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * Log in & Sign up settings tab
     */
    private function login_signup_settings_tab() {
        ?>
        <h2><?php esc_html_e('Log in & Sign up', 'realestate-booking-suite'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_login_form" value="1" <?php checked(get_option('resbs_enable_login_form'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable log in form', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-page-creation-card">
                <div class="resbs-page-icon">🔑</div>
                <div class="resbs-page-info">
                    <h4><?php esc_html_e('Add recommended page', 'realestate-booking-suite'); ?></h4>
                    <p><?php esc_html_e('Log in page', 'realestate-booking-suite'); ?></p>
                </div>
                <button type="button" class="resbs-create-page-btn"><?php esc_html_e('Create page', 'realestate-booking-suite'); ?></button>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_login_facebook" value="1" <?php checked(get_option('resbs_enable_login_facebook'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable log in with Facebook', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_login_google" value="1" <?php checked(get_option('resbs_enable_login_google'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable log in with Google', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_signin_page_title"><?php esc_html_e('Title for sign in page', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_signin_page_title" name="resbs_signin_page_title" value="<?php echo esc_attr(get_option('resbs_signin_page_title', 'Sign in or register')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_signin_page_subtitle"><?php esc_html_e('Subtitle for sign in page', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_signin_page_subtitle" name="resbs_signin_page_subtitle" value="<?php echo esc_attr(get_option('resbs_signin_page_subtitle', 'to save your favourite homes and more')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_signup_buyers" value="1" <?php checked(get_option('resbs_enable_signup_buyers'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable sign up form for buyers', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-page-creation-card">
                <div class="resbs-page-icon">🔑</div>
                <div class="resbs-page-info">
                    <h4><?php esc_html_e('Add recommended page', 'realestate-booking-suite'); ?></h4>
                    <p><?php esc_html_e('Buyer registration page', 'realestate-booking-suite'); ?></p>
                </div>
                <button type="button" class="resbs-create-page-btn"><?php esc_html_e('Create page', 'realestate-booking-suite'); ?></button>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_buyer_signup_title"><?php esc_html_e('Title for buyer sign up page', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_buyer_signup_title" name="resbs_buyer_signup_title" value="<?php echo esc_attr(get_option('resbs_buyer_signup_title', 'Get started with your account')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_buyer_signup_subtitle"><?php esc_html_e('Subtitle for buyer sign up page', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_buyer_signup_subtitle" name="resbs_buyer_signup_subtitle" value="<?php echo esc_attr(get_option('resbs_buyer_signup_subtitle', 'to save your favourite homes and more')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_signup_agents" value="1" <?php checked(get_option('resbs_enable_signup_agents'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable sign up form for agents', 'realestate-booking-suite'); ?>
                    <span class="resbs-pro-tag">PRO</span>
                </label>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * SEO settings tab
     */
    private function seo_settings_tab() {
        ?>
        <h2><?php esc_html_e('SEO', 'realestate-booking-suite'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_auto_tags" value="1" <?php checked(get_option('resbs_enable_auto_tags'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable auto tags', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_clickable_tags" value="1" <?php checked(get_option('resbs_enable_clickable_tags'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable clickable tags', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_heading_tag_posts_title"><?php esc_html_e('Heading Tag for Posts Title', 'realestate-booking-suite'); ?></label>
                <select id="resbs_heading_tag_posts_title" name="resbs_heading_tag_posts_title">
                    <option value="h1" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h1'); ?>>H1</option>
                    <option value="h2" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h2'); ?>>H2</option>
                    <option value="h3" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h3'); ?>>H3</option>
                    <option value="h4" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h4'); ?>>H4</option>
                    <option value="h5" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h5'); ?>>H5</option>
                    <option value="h6" <?php selected(get_option('resbs_heading_tag_posts_title'), 'h6'); ?>>H6</option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_dynamic_content" value="1" <?php checked(get_option('resbs_enable_dynamic_content'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable dynamic content', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * Sharing settings tab
     */
    private function sharing_settings_tab() {
        ?>
        <h2><?php esc_html_e('Sharing', 'realestate-booking-suite'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_sharing_link" value="1" <?php checked(get_option('resbs_enable_sharing_link'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable sharing with link', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_sharing_social" value="1" <?php checked(get_option('resbs_enable_sharing_social'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable sharing via social networks', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Select options', 'realestate-booking-suite'); ?></label>
                <div class="resbs-checkbox-group">
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="share_linkedin" name="resbs_sharing_options[]" value="linkedin" <?php echo in_array('linkedin', (array)get_option('resbs_sharing_options', array())) ? 'checked' : ''; ?>>
                        <label for="share_linkedin"><?php esc_html_e('Linkedin', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="share_facebook" name="resbs_sharing_options[]" value="facebook" <?php echo in_array('facebook', (array)get_option('resbs_sharing_options', array())) ? 'checked' : ''; ?>>
                        <label for="share_facebook"><?php esc_html_e('Facebook', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="share_twitter" name="resbs_sharing_options[]" value="twitter" <?php echo in_array('twitter', (array)get_option('resbs_sharing_options', array())) ? 'checked' : ''; ?>>
                        <label for="share_twitter"><?php esc_html_e('Twitter', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="share_pinterest" name="resbs_sharing_options[]" value="pinterest" <?php echo in_array('pinterest', (array)get_option('resbs_sharing_options', array())) ? 'checked' : ''; ?>>
                        <label for="share_pinterest"><?php esc_html_e('Pinterest', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_sharing_pdf" value="1" <?php checked(get_option('resbs_enable_sharing_pdf'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable sharing with PDF', 'realestate-booking-suite'); ?>
                    <span class="resbs-pro-tag">PRO</span>
                </label>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * URL Slug settings tab
     */
    private function url_slug_settings_tab() {
        ?>
        <h2><?php esc_html_e('URL Slug', 'realestate-booking-suite'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <label for="resbs_property_slug"><?php esc_html_e('Property slug', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_property_slug" name="resbs_property_slug" value="<?php echo esc_attr(get_option('resbs_property_slug', 'property')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_property_category_slug"><?php esc_html_e('Property category slug', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_property_category_slug" name="resbs_property_category_slug" value="<?php echo esc_attr(get_option('resbs_property_category_slug', 'property-category')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_property_type_slug"><?php esc_html_e('Property type slug', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_property_type_slug" name="resbs_property_type_slug" value="<?php echo esc_attr(get_option('resbs_property_type_slug', 'property-type')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_property_status_slug"><?php esc_html_e('Property status slug', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_property_status_slug" name="resbs_property_status_slug" value="<?php echo esc_attr(get_option('resbs_property_status_slug', 'es_status')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_property_label_slug"><?php esc_html_e('Property label slug', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_property_label_slug" name="resbs_property_label_slug" value="<?php echo esc_attr(get_option('resbs_property_label_slug', 'es_label')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_property_amenity_slug"><?php esc_html_e('Property amenity slug', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_property_amenity_slug" name="resbs_property_amenity_slug" value="<?php echo esc_attr(get_option('resbs_property_amenity_slug', 'es_amenity')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_property_feature_slug"><?php esc_html_e('Property feature slug', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_property_feature_slug" name="resbs_property_feature_slug" value="<?php echo esc_attr(get_option('resbs_property_feature_slug', 'es_feature')); ?>" class="regular-text">
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    /**
     * Privacy Policy & Terms settings tab
     */
    private function privacy_terms_settings_tab() {
        ?>
        <h2><?php esc_html_e('Privacy Policy & Terms of use', 'realestate-booking-suite'); ?></h2>
        
        <form method="post" action="options.php">
            <?php settings_fields('resbs_enhanced_settings'); ?>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Enable information about Privacy policy & Terms of use to submit forms', 'realestate-booking-suite'); ?></label>
                <div class="resbs-checkbox-group">
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="privacy_signup" name="resbs_privacy_terms_signup" value="1" <?php checked(get_option('resbs_privacy_terms_signup'), 1); ?>>
                        <label for="privacy_signup"><?php esc_html_e('Sign up', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="checkbox" id="privacy_request" name="resbs_privacy_terms_request_form" value="1" <?php checked(get_option('resbs_privacy_terms_request_form'), 1); ?>>
                        <label for="privacy_request"><?php esc_html_e('Request form', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('What to use as accepting Privacy policy & Terms of use?', 'realestate-booking-suite'); ?></label>
                <div class="resbs-layout-options">
                    <div class="resbs-layout-option">
                        <input type="radio" id="privacy_text" name="resbs_privacy_terms_acceptance_type" value="text" <?php checked(get_option('resbs_privacy_terms_acceptance_type'), 'text'); ?>>
                        <div class="resbs-layout-preview">Text</div>
                        <label for="privacy_text"><?php esc_html_e('Text', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-layout-option">
                        <input type="radio" id="privacy_checkbox" name="resbs_privacy_terms_acceptance_type" value="checkbox" <?php checked(get_option('resbs_privacy_terms_acceptance_type'), 'checkbox'); ?>>
                        <div class="resbs-layout-preview">☑</div>
                        <label for="privacy_checkbox"><?php esc_html_e('Checkbox', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_privacy_terms_text"><?php esc_html_e('Privacy Policy & Terms Text', 'realestate-booking-suite'); ?></label>
                <textarea id="resbs_privacy_terms_text" name="resbs_privacy_terms_text" rows="4" class="large-text"><?php echo esc_textarea(get_option('resbs_privacy_terms_text', 'By clicking the «BUTTON» button you agree to the Terms of Use and Privacy Policy.')); ?></textarea>
            </div>
            
            <div class="resbs-page-creation-card">
                <div class="resbs-page-icon">🔑</div>
                <div class="resbs-page-info">
                    <h4><?php esc_html_e('Add recommended page', 'realestate-booking-suite'); ?></h4>
                    <p><?php esc_html_e('Terms & conditions page', 'realestate-booking-suite'); ?></p>
                </div>
                <button type="button" class="resbs-create-page-btn"><?php esc_html_e('Create page', 'realestate-booking-suite'); ?></button>
            </div>
            
            <div class="resbs-page-creation-card">
                <div class="resbs-page-icon">🔑</div>
                <div class="resbs-page-info">
                    <h4><?php esc_html_e('Add recommended page', 'realestate-booking-suite'); ?></h4>
                    <p><?php esc_html_e('Privacy policy page', 'realestate-booking-suite'); ?></p>
                </div>
                <button type="button" class="resbs-create-page-btn"><?php esc_html_e('Create page', 'realestate-booking-suite'); ?></button>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
        </form>
        <?php
    }
    
    // Placeholder methods for other menu items
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

// Initialize the enhanced settings
new RESBS_Enhanced_Settings();
