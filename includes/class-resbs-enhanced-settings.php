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
        
        /* Form Styling - Simple WordPress Table Style */
        .resbs-settings-content .form-table {
            margin-top: 0;
        }
        
        .resbs-settings-content .form-table th {
            width: 200px;
            padding: 20px 10px 20px 0;
            font-weight: 600;
        }
        
        .resbs-settings-content .form-table td {
            padding: 15px 10px;
        }
        
        .resbs-settings-content .form-table fieldset {
            margin: 0;
            padding: 0;
            border: none;
        }
        
        .resbs-settings-content .form-table fieldset label {
            display: block;
            margin-bottom: 8px;
        }
        
        .resbs-color-hex {
            font-family: 'Courier New', monospace;
            text-transform: uppercase;
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
        
        /* Save Button - WordPress Style */
        .resbs-save-button {
            background: #2271b1;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 400;
        }
        
        .resbs-save-button:hover {
            background: #135e96;
        }
        
        /* Description Text - Simple */
        .resbs-description {
            font-size: 13px;
            color: #646970;
            margin-top: 6px;
            line-height: 1.5;
        }
        
        /* Pro Tag - Simple */
        .resbs-pro-tag {
            background: #f0b849;
            color: #23282d;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 3px;
            margin-left: 8px;
            font-weight: 600;
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
            
            // Enhanced Color Picker Functionality
            function updateColorHex(colorInput, hexInput) {
                var color = colorInput.val();
                hexInput.val(color.toUpperCase());
            }
            
            // Initialize color pickers
            $('input[type="color"]').each(function() {
                var $colorInput = $(this);
                var $hexInput = $colorInput.siblings('.resbs-color-hex');
                
                // Update on color input change
                $colorInput.on('input change', function() {
                    updateColorHex($colorInput, $hexInput);
                });
                
                // Update on hex input change
                $hexInput.on('input', function() {
                    var hex = $(this).val();
                    if (/^#[0-9A-F]{6}$/i.test(hex)) {
                        $colorInput.val(hex);
                    }
                });
                
                // Reset button
                $colorInput.siblings('.resbs-color-reset').on('click', function() {
                    var defaultColor = $(this).data('default');
                    $colorInput.val(defaultColor);
                    updateColorHex($colorInput, $hexInput);
                });
            });
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
        
        <?php if (isset($_GET['reset']) && $_GET['reset'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings reset to defaults successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
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
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="resetGeneralForm" style="display: none;">
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
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
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
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="resetMapForm" style="display: none;">
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
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
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
                            <label><input type="checkbox" id="sort_newest" name="resbs_sort_options[]" value="newest" <?php echo in_array('newest', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Newest', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_oldest" name="resbs_sort_options[]" value="oldest" <?php echo in_array('oldest', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Oldest', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_lowest_price" name="resbs_sort_options[]" value="lowest_price" <?php echo in_array('lowest_price', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Lowest price', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_highest_price" name="resbs_sort_options[]" value="highest_price" <?php echo in_array('highest_price', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Highest price', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_largest_sqft" name="resbs_sort_options[]" value="largest_sqft" <?php echo in_array('largest_sqft', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Largest sq ft', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_lowest_sqft" name="resbs_sort_options[]" value="lowest_sqft" <?php echo in_array('lowest_sqft', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Lowest sq ft', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_bedrooms" name="resbs_sort_options[]" value="bedrooms" <?php echo in_array('bedrooms', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_bathrooms" name="resbs_sort_options[]" value="bathrooms" <?php echo in_array('bathrooms', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="sort_featured" name="resbs_sort_options[]" value="featured" <?php echo in_array('featured', (array)get_option('resbs_sort_options', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Featured', 'realestate-booking-suite'); ?></label>
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
                                | <a href="<?php echo esc_url(admin_url('post.php?post=' . $wishlist_page_id . '&action=edit')); ?>"><?php esc_html_e('Edit Page', 'realestate-booking-suite'); ?></a>
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
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="resetListingsForm" style="display: none;">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_reset_settings">
            <input type="hidden" name="current_tab" value="listings">
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
        
        <?php if (isset($_GET['reset']) && $_GET['reset'] == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings reset to defaults successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="search">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="resbs_address_field_placeholder"><?php esc_html_e('Address field search placeholder', 'realestate-booking-suite'); ?></label></th>
                    <td><input type="text" id="resbs_address_field_placeholder" name="resbs_address_field_placeholder" value="<?php echo esc_attr(get_option('resbs_address_field_placeholder', 'Address, City, ZIP')); ?>" class="regular-text"></td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Add recommended page', 'realestate-booking-suite'); ?></th>
                    <td>
                        <p><?php esc_html_e('Default Search results', 'realestate-booking-suite'); ?></p>
                        <button type="button" class="button resbs-create-page-btn" data-page-type="search"><?php esc_html_e('Create page', 'realestate-booking-suite'); ?></button>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable saved search', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_saved_search" value="1" <?php checked(get_option('resbs_enable_saved_search'), 1); ?>>
                            <?php esc_html_e('Enable saved search', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable auto-update search results', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_auto_update_search" value="1" <?php checked(get_option('resbs_enable_auto_update_search'), 1); ?>>
                            <?php esc_html_e('Enable auto-update search results for Simple and Advanced RealEstate Booking Suite search', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable autocomplete locations for search', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_autocomplete_locations" value="1" <?php checked(get_option('resbs_enable_autocomplete_locations'), 1); ?>>
                            <?php esc_html_e('Enable autocomplete locations for search', 'realestate-booking-suite'); ?>
                        </label>
                        <p class="description"><?php esc_html_e('Autocomplete will be done with data used on your database.', 'realestate-booking-suite'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Filters Options', 'realestate-booking-suite'); ?></th>
                    <td>
                        <fieldset>
                            <label><input type="checkbox" id="filter_price" name="resbs_search_filters[]" value="price" <?php echo in_array('price', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Price', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="filter_categories" name="resbs_search_filters[]" value="categories" <?php echo in_array('categories', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Categories', 'realestate-booking-suite'); ?> <a href="<?php echo admin_url('admin.php?page=resbs-data-manager'); ?>" target="_blank"><?php esc_html_e('Go to Data manager to edit options.', 'realestate-booking-suite'); ?></a></label><br>
                            <label><input type="checkbox" id="filter_types" name="resbs_search_filters[]" value="types" <?php echo in_array('types', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Types', 'realestate-booking-suite'); ?> <a href="<?php echo admin_url('admin.php?page=resbs-data-manager'); ?>" target="_blank"><?php esc_html_e('Go to Data manager to edit options.', 'realestate-booking-suite'); ?></a></label><br>
                            <label><input type="checkbox" id="filter_rent_periods" name="resbs_search_filters[]" value="rent_periods" <?php echo in_array('rent_periods', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Rent Periods', 'realestate-booking-suite'); ?> <a href="<?php echo admin_url('admin.php?page=resbs-data-manager'); ?>" target="_blank"><?php esc_html_e('Go to Data manager to edit options.', 'realestate-booking-suite'); ?></a></label><br>
                            <label><input type="checkbox" id="filter_bedrooms" name="resbs_search_filters[]" value="bedrooms" <?php echo in_array('bedrooms', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="filter_bathrooms" name="resbs_search_filters[]" value="bathrooms" <?php echo in_array('bathrooms', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="filter_half_baths" name="resbs_search_filters[]" value="half_baths" <?php echo in_array('half_baths', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Half baths', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="filter_amenities" name="resbs_search_filters[]" value="amenities" <?php echo in_array('amenities', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Amenities', 'realestate-booking-suite'); ?> <a href="<?php echo admin_url('admin.php?page=resbs-data-manager'); ?>" target="_blank"><?php esc_html_e('Go to Data manager to edit options.', 'realestate-booking-suite'); ?></a></label><br>
                            <label><input type="checkbox" id="filter_features" name="resbs_search_filters[]" value="features" <?php echo in_array('features', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Features', 'realestate-booking-suite'); ?> <a href="<?php echo admin_url('admin.php?page=resbs-data-manager'); ?>" target="_blank"><?php esc_html_e('Go to Data manager to edit options.', 'realestate-booking-suite'); ?></a></label><br>
                            <label><input type="checkbox" id="filter_area" name="resbs_search_filters[]" value="area" <?php echo in_array('area', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Area', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="filter_lot_size" name="resbs_search_filters[]" value="lot_size" <?php echo in_array('lot_size', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Lot size', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="filter_floors" name="resbs_search_filters[]" value="floors" <?php echo in_array('floors', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Floors', 'realestate-booking-suite'); ?></label><br>
                            <label><input type="checkbox" id="filter_floor_level" name="resbs_search_filters[]" value="floor_level" <?php echo in_array('floor_level', (array)get_option('resbs_search_filters', array())) ? 'checked' : ''; ?>> <?php esc_html_e('Floor Level', 'realestate-booking-suite'); ?></label>
                        </fieldset>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="resbs-save-button button button-primary"><?php esc_html_e('Save Changes', 'realestate-booking-suite'); ?></button>
                <button type="button" class="button button-secondary resbs-reset-button" onclick="if(confirm('<?php esc_attr_e('Are you sure you want to reset all Listing Search settings to default values? This cannot be undone.', 'realestate-booking-suite'); ?>')) { document.getElementById('resetSearchForm').submit(); }" style="margin-left: 10px;"><?php esc_html_e('Reset to Defaults', 'realestate-booking-suite'); ?></button>
            </p>
        </form>
        
        <!-- Reset Form -->
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="resetSearchForm" style="display: none;">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_reset_settings">
            <input type="hidden" name="current_tab" value="search">
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
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <?php wp_nonce_field('resbs_enhanced_settings-options'); ?>
            <input type="hidden" name="action" value="resbs_save_settings">
            <input type="hidden" name="current_tab" value="user-profile">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable User Profile', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_user_profile" value="1" <?php checked(get_option('resbs_enable_user_profile'), 1); ?>>
                            <?php esc_html_e('Enable User Profile', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Add recommended page', 'realestate-booking-suite'); ?></th>
                    <td>
                        <p><?php esc_html_e('Profile', 'realestate-booking-suite'); ?></p>
                        <button type="button" class="button resbs-create-page-btn" data-page-type="profile"><?php esc_html_e('Create page', 'realestate-booking-suite'); ?></button>
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
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="resetUserProfileForm" style="display: none;">
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
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
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
                    <th scope="row"><?php esc_html_e('Enable log in with Facebook', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_login_facebook" value="1" <?php checked(get_option('resbs_enable_login_facebook'), 1); ?>>
                            <?php esc_html_e('Enable log in with Facebook', 'realestate-booking-suite'); ?>
                        </label>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php esc_html_e('Enable log in with Google', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_login_google" value="1" <?php checked(get_option('resbs_enable_login_google'), 1); ?>>
                            <?php esc_html_e('Enable log in with Google', 'realestate-booking-suite'); ?>
                        </label>
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
                    <th scope="row"><?php esc_html_e('Enable sign up form for agents', 'realestate-booking-suite'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="resbs_enable_signup_agents" value="1" <?php checked(get_option('resbs_enable_signup_agents'), 1); ?>>
                            <?php esc_html_e('Enable sign up form for agents', 'realestate-booking-suite'); ?>
                            <span class="description"><?php esc_html_e('PRO', 'realestate-booking-suite'); ?></span>
                        </label>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <button type="submit" class="resbs-save-button button button-primary"><?php esc_html_e('Save Changes', 'realestate-booking-suite'); ?></button>
                <button type="button" class="button button-secondary resbs-reset-button" onclick="if(confirm('<?php esc_attr_e('Are you sure you want to reset all Log in & Sign up settings to default values? This cannot be undone.', 'realestate-booking-suite'); ?>')) { document.getElementById('resetLoginSignupForm').submit(); }" style="margin-left: 10px;"><?php esc_html_e('Reset to Defaults', 'realestate-booking-suite'); ?></button>
            </p>
        </form>
        
        <!-- Reset Form -->
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="resetLoginSignupForm" style="display: none;">
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
        
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
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
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="resetSeoForm" style="display: none;">
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
     * Handle reset settings
     */
    public function handle_reset_settings() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        if (!wp_verify_nonce($_POST['_wpnonce'], 'resbs_enhanced_settings-options')) {
            wp_die('Security check failed');
        }
        
        $tab = sanitize_text_field($_POST['current_tab']);
        
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
            case 'search':
                $this->reset_search_settings();
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
        
        wp_redirect(add_query_arg(array('page' => 'resbs-settings', 'tab' => $tab, 'reset' => '1'), admin_url('admin.php')));
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
        // Add default values for General settings if needed
        // This is a placeholder - add actual defaults based on your General settings
    }
    
    /**
     * Reset map settings to defaults
     */
    private function reset_map_settings() {
        // Add default values for Map settings if needed
        // This is a placeholder - add actual defaults based on your Map settings
    }
    
    /**
     * Reset search settings to defaults
     */
    private function reset_search_settings() {
        // Add default values for Search settings if needed
        // This is a placeholder - add actual defaults based on your Search settings
    }
    
    /**
     * Reset user profile settings to defaults
     */
    private function reset_user_profile_settings() {
        // Add default values for User Profile settings if needed
        // This is a placeholder - add actual defaults based on your User Profile settings
    }
    
    /**
     * Reset login/signup settings to defaults
     */
    private function reset_login_signup_settings() {
        // Add default values for Login/Signup settings if needed
        // This is a placeholder - add actual defaults based on your Login/Signup settings
    }
    
    /**
     * Reset SEO settings to defaults
     */
    private function reset_seo_settings() {
        // Add default values for SEO settings if needed
        // This is a placeholder - add actual defaults based on your SEO settings
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
        if (isset($_POST['resbs_sort_options'])) {
            update_option('resbs_sort_options', array_map('sanitize_text_field', $_POST['resbs_sort_options']));
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
