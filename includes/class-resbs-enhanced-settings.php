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
        
        // Add AJAX handlers
        add_action('admin_post_resbs_save_settings', array($this, 'handle_settings_save'));
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
            esc_html__('RealEstate Booking Suite', 'realestate-booking-suite'),
            esc_html__('RealEstate Booking Suite', 'realestate-booking-suite'),
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
        register_setting('resbs_enhanced_settings', 'resbs_enable_default_archive_template');
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
        
        // Additional Archive Settings
        register_setting('resbs_enhanced_settings', 'resbs_archive_title');
        register_setting('resbs_enhanced_settings', 'resbs_archive_description');
        register_setting('resbs_enhanced_settings', 'resbs_default_archive_layout');
        register_setting('resbs_enhanced_settings', 'resbs_grid_columns');
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
        
        
        
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts() {
        // Minimal enqueue - just what we need
        wp_enqueue_script('jquery');
        
        // Make ajaxurl available globally
        wp_add_inline_script('jquery', 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";');
    }
    
    /**
     * Settings page callback
     */
    public function settings_page_callback() {
        $this->current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
        ?>
        <div class="wrap resbs-enhanced-settings">
            <div class="resbs-settings-header">
                <div class="resbs-header-left">
                    <h1><?php esc_html_e('Settings', 'realestate-booking-suite'); ?></h1>
                </div>
                <div class="resbs-header-right">
                    <div class="resbs-plugin-branding">
                        <div class="resbs-logo-container">
                            <div class="resbs-logo-icon">🏢</div>
                            <div class="resbs-logo-text">
                                <span class="resbs-plugin-name">REALESTATE BOOKING SUITE</span>
                                <span class="resbs-plugin-version">Version 1.0.0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="resbs-settings-container">
                <div class="resbs-settings-sidebar">
                    <div class="resbs-settings-nav">
                        <ul class="resbs-nav-tabs">
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="general" class="resbs-nav-link <?php echo $this->current_tab === 'general' ? 'active' : ''; ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('General', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="map" class="resbs-nav-link <?php echo $this->current_tab === 'map' ? 'active' : ''; ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('Map', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="listings" class="resbs-nav-link <?php echo $this->current_tab === 'listings' ? 'active' : ''; ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('Listings', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="archive" class="resbs-nav-link <?php echo $this->current_tab === 'archive' ? 'active' : ''; ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('Archive Pages', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="search" class="resbs-nav-link <?php echo $this->current_tab === 'search' ? 'active' : ''; ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('Listing search', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="user-profile" class="resbs-nav-link <?php echo $this->current_tab === 'user-profile' ? 'active' : ''; ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('User profile', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="login-signup" class="resbs-nav-link <?php echo $this->current_tab === 'login-signup' ? 'active' : ''; ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('Log in & Sign up', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="#" data-tab="seo" class="resbs-nav-link <?php echo $this->current_tab === 'seo' ? 'active' : ''; ?>">
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
                        default:
                            $this->general_settings_tab();
                    }
                    ?>
                </div>
            </div>
        </div>
        
        <style>
        /* Estatik-style Settings Design */
        .resbs-enhanced-settings {
            margin: 0;
            background: #f1f1f1;
        }
        
        /* Header - Modern Professional */
        .resbs-settings-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 40px;
            margin: -20px -20px 30px -20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .resbs-header-left h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 300;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .resbs-plugin-branding {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .resbs-logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(255,255,255,0.1);
            padding: 10px 15px;
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }
        
        .resbs-logo-icon {
            font-size: 24px;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .resbs-plugin-name {
            font-size: 16px;
            font-weight: 600;
            color: white;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .resbs-plugin-version {
            font-size: 12px;
            color: rgba(255,255,255,0.8);
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        /* Container Layout - Modern Card Style */
        .resbs-settings-container {
            display: flex;
            margin: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            overflow: hidden;
            min-height: 600px;
        }
        
        /* Sidebar - Modern Professional */
        .resbs-settings-sidebar {
            width: 250px;
            background: linear-gradient(180deg, #f8f9fa 0%, #e9ecef 100%);
            border-right: 1px solid #e9ecef;
        }
        
        .resbs-settings-nav {
            padding: 0;
        }
        
        .resbs-nav-tabs {
            list-style: none;
            margin: 0;
            padding: 20px 0;
        }
        
        .resbs-nav-item {
            margin: 0;
        }
        
        .resbs-nav-link {
            display: block;
            padding: 15px 25px;
            text-decoration: none;
            color: #6c757d;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            border-left: 3px solid transparent;
        }
        
        .resbs-nav-link:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border-left-color: rgba(102, 126, 234, 0.3);
        }
        
        .resbs-nav-link.active {
            background: rgba(102, 126, 234, 0.15);
            color: #667eea;
            font-weight: 600;
            border-left-color: #667eea;
        }
        
        .resbs-nav-text {
            font-size: 14px;
        }
        
        /* Content Area - Modern Professional */
        .resbs-settings-content {
            flex: 1;
            background: white;
            padding: 40px;
            min-height: 600px;
        }
        
        .resbs-settings-content h2 {
            margin: 0 0 30px 0;
            font-size: 24px;
            font-weight: 300;
            color: #2c3e50;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            position: relative;
        }
        
        .resbs-settings-content h2::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
        }
        
        /* Form Styling - Modern Professional */
        .resbs-form-group {
            margin-bottom: 25px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .resbs-form-group:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
        }
        
        .resbs-form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 10px;
            color: #2c3e50;
            font-size: 14px;
        }
        
        .resbs-form-group input[type="text"],
        .resbs-form-group input[type="email"],
        .resbs-form-group input[type="number"],
        .resbs-form-group input[type="color"],
        .resbs-form-group select,
        .resbs-form-group textarea {
            width: 100%;
            max-width: 400px;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            transition: all 0.3s ease;
        }
        
        .resbs-form-group input[type="text"]:focus,
        .resbs-form-group input[type="email"]:focus,
        .resbs-form-group input[type="number"]:focus,
        .resbs-form-group input[type="color"]:focus,
        .resbs-form-group select:focus,
        .resbs-form-group textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }
        
        /* Toggle Switch - Modern Professional */
        .resbs-toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
            margin-right: 15px;
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
            background: linear-gradient(45deg, #e9ecef, #dee2e6);
            transition: all 0.4s ease;
            border-radius: 30px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .resbs-slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 3px;
            bottom: 3px;
            background: white;
            transition: all 0.4s ease;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        input:checked + .resbs-slider {
            background: linear-gradient(45deg, #667eea, #764ba2);
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
        }
        
        input:checked + .resbs-slider:before {
            transform: translateX(30px);
        }
        
        /* Layout Options - Simple Style */
        .resbs-layout-options {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .resbs-layout-option {
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: none;
            min-width: 100px;
            background: #fff;
        }
        
        .resbs-layout-option:hover {
            border-color: #5b9dd9;
        }
        
        .resbs-layout-option.selected {
            border-color: #00a0d2;
            background: #f0f8fc;
        }
        
        .resbs-layout-preview {
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
            font-size: 14px;
        }
        
        /* Checkbox Groups */
        .resbs-checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 10px;
        }
        
        /* Checkbox and Radio Styling - Modern with Clear Indicators */
        .resbs-checkbox-group {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 15px;
        }
        
        .resbs-checkbox-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            min-width: 120px;
        }
        
        .resbs-checkbox-item:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
        }
        
        .resbs-checkbox-item input[type="checkbox"],
        .resbs-checkbox-item input[type="radio"] {
            margin: 0;
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #667eea;
        }
        
        .resbs-checkbox-item label {
            margin: 0;
            font-weight: 500;
            cursor: pointer;
            color: #6c757d;
            font-size: 14px;
            flex: 1;
        }
        
        .resbs-checkbox-item input[type="checkbox"]:checked + label,
        .resbs-checkbox-item input[type="radio"]:checked + label {
            color: #667eea;
            font-weight: 600;
        }
        
        .resbs-checkbox-item:has(input[type="radio"]:checked) {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
        }
        
        .resbs-checkbox-item:has(input[type="checkbox"]:checked) {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
        }
        
        /* Add a checkmark icon for selected items */
        .resbs-checkbox-item:has(input[type="radio"]:checked)::after {
            content: "✓";
            position: absolute;
            top: 8px;
            right: 8px;
            color: #667eea;
            font-weight: bold;
            font-size: 12px;
        }
        
        .resbs-checkbox-item:has(input[type="checkbox"]:checked)::after {
            content: "✓";
            position: absolute;
            top: 8px;
            right: 8px;
            color: #667eea;
            font-weight: bold;
            font-size: 12px;
        }
        
        /* Page Creation Cards - Simple Style */
        .resbs-page-creation-card {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 3px;
            padding: 15px;
            margin: 15px 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .resbs-page-icon {
            width: 32px;
            height: 32px;
            background: #00a0d2;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        
        .resbs-page-info h4 {
            margin: 0 0 5px 0;
            color: #23282d;
            font-size: 14px;
        }
        
        .resbs-page-info p {
            margin: 0;
            color: #666;
            font-size: 13px;
        }
        
        .resbs-create-page-btn {
            background: #00a0d2;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 13px;
        }
        
        .resbs-create-page-btn:hover {
            background: #0085ba;
        }
        
        /* Save Button - Modern Professional */
        .resbs-save-button {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .resbs-save-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }
        
        /* Description Text - Modern */
        .resbs-description {
            font-size: 13px;
            color: #6c757d;
            margin-top: 8px;
            font-style: italic;
            line-height: 1.5;
        }
        
        /* Pro Tag - Modern */
        .resbs-pro-tag {
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            font-size: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            margin-left: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(255, 107, 53, 0.3);
        }
        
        /* Success Messages */
        .notice.notice-success {
            background: #d1edcc;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 3px;
            padding: 12px;
            margin: 15px 0;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .resbs-settings-container {
                flex-direction: column;
            }
            
            .resbs-settings-sidebar {
                width: 100%;
            }
            
            .resbs-nav-tabs {
                display: flex;
                flex-wrap: wrap;
            }
            
            .resbs-nav-item {
                flex: 1;
                min-width: 150px;
            }
            
            .resbs-nav-link {
                border-bottom: none;
                border-right: 1px solid #e1e1e1;
            }
        }
        
        /* Loading animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        </style>
        
        <script>
        // MINIMAL WORKING VERSION
        jQuery(document).ready(function($) {
            console.log('=== RESBS MINIMAL JS LOADED ===');
            
            // Test if jQuery and basic elements exist
            console.log('jQuery version:', $.fn.jquery);
            console.log('Nav links found:', $('.resbs-nav-link').length);
            console.log('Content area found:', $('.resbs-settings-content').length);
            
            // Tab switching
            $('.resbs-nav-link').on('click', function(e) {
                e.preventDefault();
                console.log('=== TAB CLICKED ===');
                
                // Get tab data
                var tab = $(this).data('tab');
                console.log('Tab data:', tab);
                
                // Don't reload if same tab
                if ($(this).hasClass('active')) {
                    console.log('Same tab, skipping');
                    return;
                }
                
                // Update active state
                $('.resbs-nav-link').removeClass('active');
                $(this).addClass('active');
                
                // Show loading spinner with minimum time
                var loadingStartTime = Date.now();
                $('.resbs-settings-content').html('<div style="text-align: center; padding: 30px;"><div style="display: inline-block; width: 30px; height: 30px; border: 3px solid #f3f3f3; border-top: 3px solid #00a0d2; border-radius: 50%; animation: spin 1s linear infinite;"></div><p style="margin-top: 15px; font-size: 14px;">Loading...</p></div>');
                
                // Load tab content via AJAX
                $.post(ajaxurl, {
                    action: 'resbs_load_tab_content',
                    tab: tab,
                    nonce: '<?php echo wp_create_nonce('resbs_load_tab_content'); ?>'
                })
                .done(function(response) {
                    var loadingTime = Date.now() - loadingStartTime;
                    var minLoadingTime = 500; // Minimum 500ms loading time
                    
                    if (response.success) {
                        // Ensure minimum loading time for smooth UX
                        setTimeout(function() {
                            $('.resbs-settings-content').html(response.data);
                        }, Math.max(0, minLoadingTime - loadingTime));
                    } else {
                        setTimeout(function() {
                            $('.resbs-settings-content').html('<div class="notice notice-error"><p>Error loading tab content.</p></div>');
                        }, Math.max(0, minLoadingTime - loadingTime));
                    }
                })
                .fail(function(xhr, status, error) {
                    var loadingTime = Date.now() - loadingStartTime;
                    var minLoadingTime = 500;
                    
                    setTimeout(function() {
                        $('.resbs-settings-content').html('<div class="notice notice-error"><p>AJAX Error: ' + error + '</p></div>');
                    }, Math.max(0, minLoadingTime - loadingTime));
                });
            });
            
            console.log('=== EVENT HANDLERS ATTACHED ===');
        });
        </script>
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
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
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
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="general">
            
            <div class="resbs-form-group">
                <label for="resbs_language"><?php esc_html_e('Language', 'realestate-booking-suite'); ?></label>
                <select id="resbs_language" name="resbs_language">
                    <option value="en" <?php selected(get_option('resbs_language'), 'en'); ?>><?php esc_html_e('English', 'realestate-booking-suite'); ?></option>
                    <option value="es" <?php selected(get_option('resbs_language'), 'es'); ?>><?php esc_html_e('Spanish', 'realestate-booking-suite'); ?></option>
                    <option value="fr" <?php selected(get_option('resbs_language'), 'fr'); ?>><?php esc_html_e('French', 'realestate-booking-suite'); ?></option>
                    <option value="de" <?php selected(get_option('resbs_language'), 'de'); ?>><?php esc_html_e('German', 'realestate-booking-suite'); ?></option>
                </select>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Units', 'realestate-booking-suite'); ?></label>
                <div class="resbs-checkbox-group">
                    <div class="resbs-checkbox-item">
                        <input type="radio" id="area_sqft" name="resbs_area_unit" value="sqft" <?php checked(get_option('resbs_area_unit'), 'sqft'); ?>>
                        <label for="area_sqft"><?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="radio" id="area_sqm" name="resbs_area_unit" value="sqm" <?php checked(get_option('resbs_area_unit'), 'sqm'); ?>>
                        <label for="area_sqm"><?php esc_html_e('sq m', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Lot size unit', 'realestate-booking-suite'); ?></label>
                <div class="resbs-checkbox-group">
                    <div class="resbs-checkbox-item">
                        <input type="radio" id="lot_sqft" name="resbs_lot_size_unit" value="sqft" <?php checked(get_option('resbs_lot_size_unit'), 'sqft'); ?>>
                        <label for="lot_sqft"><?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="radio" id="lot_sqm" name="resbs_lot_size_unit" value="sqm" <?php checked(get_option('resbs_lot_size_unit'), 'sqm'); ?>>
                        <label for="lot_sqm"><?php esc_html_e('sq m', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_date_format"><?php esc_html_e('Date format', 'realestate-booking-suite'); ?></label>
                <input type="text" id="resbs_date_format" name="resbs_date_format" value="<?php echo esc_attr(get_option('resbs_date_format', 'm/d/Y')); ?>" class="regular-text">
            </div>
            
            <div class="resbs-form-group">
                <label><?php esc_html_e('Time format', 'realestate-booking-suite'); ?></label>
                <div class="resbs-checkbox-group">
                    <div class="resbs-checkbox-item">
                        <input type="radio" id="time_12h" name="resbs_time_format" value="12h" <?php checked(get_option('resbs_time_format'), '12h'); ?>>
                        <label for="time_12h"><?php esc_html_e('12h 12-hour clock', 'realestate-booking-suite'); ?></label>
                    </div>
                    <div class="resbs-checkbox-item">
                        <input type="radio" id="time_24h" name="resbs_time_format" value="24h" <?php checked(get_option('resbs_time_format'), '24h'); ?>>
                        <label for="time_24h"><?php esc_html_e('24h 24-hour clock', 'realestate-booking-suite'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_white_label" value="1" <?php checked(get_option('resbs_enable_white_label'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable white label', 'realestate-booking-suite'); ?>
                    <span class="resbs-pro-tag">PRO</span>
                </label>
                <p class="resbs-description"><?php esc_html_e('Enable this option if you want to remove RealEstate Booking Suite logo and "Powered by" link on plugin pages.', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label>
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_enable_rest_support" value="1" <?php checked(get_option('resbs_enable_rest_support'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Enable REST Support', 'realestate-booking-suite'); ?>
                </label>
                <p class="resbs-description"><?php esc_html_e('Please enable REST support if you need to use Gutenberg or pull your listings via wp api.', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_main_color"><?php esc_html_e('Main color', 'realestate-booking-suite'); ?></label>
                <input type="color" id="resbs_main_color" name="resbs_main_color" value="<?php echo esc_attr(get_option('resbs_main_color', '#0073aa')); ?>">
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
                    <div class="resbs-toggle-switch">
                        <input type="checkbox" name="resbs_disable_tel_country_code" value="1" <?php checked(get_option('resbs_disable_tel_country_code'), 1); ?>>
                        <span class="resbs-slider"></span>
                    </div>
                    <?php esc_html_e('Disable tel country code', 'realestate-booking-suite'); ?>
                </label>
            </div>
            
            <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
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
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="appearance">
            
            <div class="resbs-form-group">
                <label for="resbs_primary_color"><?php esc_html_e('Primary Color', 'realestate-booking-suite'); ?></label>
                <input type="color" id="resbs_primary_color" name="resbs_primary_color" value="<?php echo esc_attr(get_option('resbs_primary_color', '#007cba')); ?>">
            </div>
            
            <div class="resbs-form-group">
                <label for="resbs_secondary_color"><?php esc_html_e('Secondary Color', 'realestate-booking-suite'); ?></label>
                <input type="color" id="resbs_secondary_color" name="resbs_secondary_color" value="<?php echo esc_attr(get_option('resbs_secondary_color', '#666666')); ?>">
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
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
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
    
    /**
     * Archive settings tab - This is the main focus for your request
     */
    private function archive_settings_tab() {
        ?>
        <h2><?php esc_html_e('Archive Pages', 'realestate-booking-suite'); ?></h2>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="archive">
            
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
                <label>
                    <input type="checkbox" name="resbs_enable_default_archive_template" value="1" <?php checked(get_option('resbs_enable_default_archive_template', true), 1); ?>>
                    <?php esc_html_e('Use Plugin Archive Template', 'realestate-booking-suite'); ?>
                </label>
                <p class="resbs-description"><?php esc_html_e('Override theme archive pages with plugin\'s custom archive template', 'realestate-booking-suite'); ?></p>
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
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="map">
            
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
     * Listings settings tab
     */
    private function listings_settings_tab() {
        ?>
        <h2><?php esc_html_e('Listings', 'realestate-booking-suite'); ?></h2>
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="listings">
            
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
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="search">
            
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
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="user-profile">
            
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
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="login-signup">
            
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
        
        <?php if (isset($_GET['updated']) && $_GET['updated'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="seo">
            
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
     * Handle settings save
     */
    public function handle_settings_save() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!wp_verify_nonce($_POST['_wpnonce'], 'resbs_enhanced_settings-options')) {
            wp_die('Security check failed');
        }
        
        $tab = sanitize_text_field($_POST['current_tab']);
        
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
            case 'archive':
                $this->save_archive_settings();
                break;
            case 'search':
                $this->save_search_settings();
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
        
        wp_redirect(add_query_arg(array('page' => 'resbs-settings', 'tab' => $tab, 'updated' => '1'), admin_url('admin.php')));
        exit;
    }
    
    /**
     * Save general settings
     */
    private function save_general_settings() {
        $settings = array(
            'resbs_language',
            'resbs_area_unit',
            'resbs_lot_size_unit',
            'resbs_date_format',
            'resbs_time_format',
            'resbs_enable_white_label',
            'resbs_enable_rest_support',
            'resbs_main_color',
            'resbs_secondary_color',
            'resbs_disable_tel_country_code'
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
     * Save map settings
     */
    private function save_map_settings() {
        $settings = array(
            'resbs_google_api_key',
            'resbs_default_latitude',
            'resbs_default_longitude',
            'resbs_default_zoom_level',
            'resbs_single_property_zoom_level',
            'resbs_enable_markers_cluster',
            'resbs_cluster_icon',
            'resbs_cluster_icon_color',
            'resbs_map_marker_type',
            'resbs_use_single_map_marker',
            'resbs_single_marker_icon',
            'resbs_single_marker_color'
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
     * Save listings settings
     */
    private function save_listings_settings() {
        $settings = array(
            'resbs_general_listing_name',
            'resbs_default_layout_listings',
            'resbs_enable_grid_view',
            'resbs_default_layout_single',
            'resbs_enable_default_archive_template',
            'resbs_enable_collapsed_description',
            'resbs_disable_lightbox_single_page',
            'resbs_enable_request_form_geolocation',
            'resbs_default_tel_code',
            'resbs_hide_request_info_button',
            'resbs_property_headings_font',
            'resbs_property_content_font',
            'resbs_property_item_carousel',
            'resbs_property_item_image_size',
            'resbs_properties_per_page',
            'resbs_enable_sorting',
            'resbs_default_sort_option',
            'resbs_show_price',
            'resbs_show_listing_address',
            'resbs_listing_preview_block',
            'resbs_show_description_listing_box',
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
        if (isset($_POST['resbs_sort_options'])) {
            update_option('resbs_sort_options', array_map('sanitize_text_field', $_POST['resbs_sort_options']));
        }
    }
    
    /**
     * Save archive settings
     */
    private function save_archive_settings() {
        $settings = array(
            'resbs_enable_default_archive_template',
            'resbs_archive_layout',
            'resbs_archive_grid_columns',
            'resbs_archive_items_per_page',
            'resbs_archive_show_filters',
            'resbs_archive_show_search',
            'resbs_archive_show_sorting',
            'resbs_archive_show_pagination',
            'resbs_archive_card_style',
            'resbs_archive_image_size',
            'resbs_archive_show_excerpt',
            'resbs_archive_excerpt_length',
            'resbs_archive_show_meta',
            'resbs_archive_title',
            'resbs_archive_description',
            'resbs_default_archive_layout',
            'resbs_grid_columns',
            'resbs_properties_per_page',
            'resbs_show_view_toggle',
            'resbs_show_sorting',
            'resbs_show_filters',
            'resbs_show_search',
            'resbs_show_pagination',
            'resbs_show_property_image',
            'resbs_show_property_price',
            'resbs_show_property_title',
            'resbs_show_property_location',
            'resbs_show_property_details',
            'resbs_show_property_type',
            'resbs_show_property_status',
            'resbs_show_favorite_button',
            'resbs_show_quick_view',
            'resbs_default_sort',
            'resbs_filter_price',
            'resbs_filter_type',
            'resbs_filter_bedrooms',
            'resbs_filter_bathrooms',
            'resbs_filter_status',
            'resbs_filter_area',
            'resbs_archive_meta_description',
            'resbs_archive_meta_keywords'
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
        if (isset($_POST['resbs_archive_meta_fields'])) {
            update_option('resbs_archive_meta_fields', array_map('sanitize_text_field', $_POST['resbs_archive_meta_fields']));
        } else {
            update_option('resbs_archive_meta_fields', array());
        }
    }
    
    /**
     * Save search settings
     */
    private function save_search_settings() {
        $settings = array(
            'resbs_address_field_placeholder',
            'resbs_enable_saved_search',
            'resbs_enable_auto_update_search',
            'resbs_enable_autocomplete_locations'
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
        if (isset($_POST['resbs_search_filters'])) {
            update_option('resbs_search_filters', array_map('sanitize_text_field', $_POST['resbs_search_filters']));
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
            'resbs_enable_login_facebook',
            'resbs_enable_login_google',
            'resbs_signin_page_title',
            'resbs_signin_page_subtitle',
            'resbs_enable_signup_buyers',
            'resbs_buyer_signup_title',
            'resbs_buyer_signup_subtitle',
            'resbs_enable_signup_agents'
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
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $page_type = sanitize_text_field($_POST['page_type']);
        $page_title = sanitize_text_field($_POST['page_title']);
        $page_content = sanitize_textarea_field($_POST['page_content']);
        
        $page_data = array(
            'post_title' => $page_title,
            'post_content' => $page_content,
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => get_current_user_id()
        );
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id) {
            wp_send_json_success(array('page_id' => $page_id, 'message' => 'Page created successfully'));
        } else {
            wp_send_json_error('Failed to create page');
        }
    }
    
    /**
     * Handle AJAX tab content loading
     */
    public function handle_load_tab_content() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_load_tab_content')) {
            wp_die('Security check failed');
        }
        
        $tab = sanitize_text_field($_POST['tab']);
        
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
            default:
                $this->general_settings_tab();
        }
        $content = ob_get_clean();
        
        wp_send_json_success($content);
    }
    
    /**
     * Test AJAX handler
     */
    public function handle_test_ajax() {
        error_log('RESBS TEST AJAX: Called');
        wp_send_json_success(array('message' => 'AJAX is working!', 'timestamp' => time()));
    }
    
    // Placeholder methods for other menu items
    public function dashboard_callback() {
        echo '<h1>' . esc_html__('Dashboard', 'realestate-booking-suite') . '</h1>';
        echo '<p>' . esc_html__('Welcome to RealEstate Booking Suite Dashboard!', 'realestate-booking-suite') . '</p>';
        echo '<p>' . esc_html__('Use the Settings menu to configure your plugin.', 'realestate-booking-suite') . '</p>';
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
