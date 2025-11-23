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
        add_action('admin_head', array($this, 'add_critical_styles'));
        
        // Fix menu highlighting for All Properties vs My Properties
        add_filter('parent_file', array($this, 'fix_properties_menu_highlight'));
        add_filter('submenu_file', array($this, 'fix_properties_submenu_highlight'));
        
        // Add AJAX handlers
        add_action('admin_post_resbs_save_settings', array($this, 'handle_settings_save'));
        add_action('admin_post_resbs_reset_settings', array($this, 'handle_reset_settings'));
        add_action('wp_ajax_resbs_create_page', array($this, 'handle_create_page'));
        add_action('wp_ajax_resbs_load_tab_content', array($this, 'handle_load_tab_content'));
        add_action('wp_ajax_resbs_test_ajax', array($this, 'handle_test_ajax'));
        
        // Data Manager AJAX handlers
        add_action('admin_post_resbs_export_properties', array($this, 'handle_export_properties'));
        add_action('wp_ajax_resbs_cleanup_orphaned_data', array($this, 'handle_cleanup_orphaned_data'));
        add_action('wp_ajax_resbs_get_data_stats', array($this, 'handle_get_data_stats'));
        
        // Fields Builder AJAX handlers
        add_action('wp_ajax_resbs_save_custom_field', array($this, 'handle_save_custom_field'));
        add_action('wp_ajax_resbs_delete_custom_field', array($this, 'handle_delete_custom_field'));
        add_action('wp_ajax_resbs_get_custom_fields', array($this, 'handle_get_custom_fields'));
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
            time() // Force reload on every page load
        );
        
        // Add critical override styles inline (WordPress-approved method)
        $critical_css = '
        /* FORCE ESTATIK STYLE - INLINE TO OVERRIDE EVERYTHING */
        .resbs-enhanced-settings { margin: 0 !important; background: #fff !important; }
        .resbs-enhanced-settings .wrap { margin: 0 !important; padding: 0 !important; max-width: 100% !important; background: #fff !important; padding-top: 20px !important; }
        .resbs-settings-container { display: flex !important; margin: 0 !important; background: #fff !important; border: none !important; box-shadow: none !important; width: 100% !important; margin-top: 0 !important; padding-top: 20px !important; }
        .resbs-settings-sidebar { width: 220px !important; background: #fff !important; border-right: 1px solid #e5e5e5 !important; flex-shrink: 0 !important; padding: 0 !important; min-width: 220px !important; }
        .resbs-settings-nav { background: #fff !important; }
        .resbs-nav-tabs { background: #fff !important; list-style: none !important; margin: 0 !important; padding: 0 !important; }
        .resbs-nav-item { background: #fff !important; margin: 0 !important; border-bottom: 1px solid #e5e5e5 !important; }
        .resbs-nav-link { background: #fff !important; display: block !important; padding: 12px 20px !important; text-decoration: none !important; color: #555 !important; font-size: 13px !important; font-weight: 600 !important; border-left: 3px solid transparent !important; outline: none !important; border: none !important; }
        .resbs-nav-link:hover { background: #f9f9f9 !important; color: #23282d !important; outline: none !important; }
        .resbs-nav-link.active { background: #f9f9f9 !important; color: #23282d !important; font-weight: 600 !important; border-left-color: #46b450 !important; outline: none !important; }
        .resbs-nav-link:focus { outline: none !important; border: none !important; border-left: 3px solid transparent !important; }
        .resbs-nav-link:active { outline: none !important; border: none !important; border-left: 3px solid transparent !important; }
        .resbs-nav-link.active:focus { border-left-color: #46b450 !important; }
        .resbs-nav-link.active:active { border-left-color: #46b450 !important; }
        .resbs-settings-content { flex: 1 !important; background: #fff !important; padding: 30px 40px 30px 50px !important; }
        .resbs-settings-content h2 { margin: 0 0 25px 0 !important; font-size: 23px !important; font-weight: 600 !important; color: #23282d !important; padding: 0 !important; border: none !important; }
        .resbs-color-picker-group { display: flex !important; align-items: center !important; gap: 10px !important; }
        .resbs-color-input { width: 50px !important; height: 30px !important; border: 1px solid #8c8f94 !important; border-radius: 3px !important; }
        .resbs-color-hex { width: 100px !important; padding: 5px 8px !important; border: 1px solid #8c8f94 !important; border-radius: 3px !important; font-size: 13px !important; }
        .resbs-form-actions { margin-top: 25px !important; display: flex !important; gap: 10px !important; padding-top: 20px !important; border-top: 1px solid #e5e5e5 !important; }
        .resbs-save-button { background: #2271b1 !important; color: #fff !important; border-color: #2271b1 !important; padding: 0 12px !important; min-height: 30px !important; border-radius: 3px !important; font-size: 13px !important; }
        .resbs-save-button:hover { background: #135e96 !important; border-color: #135e96 !important; }
        .resbs-settings-page-title { margin: 0 0 20px 0 !important; padding: 20px 0 0 20px !important; font-size: 23px !important; font-weight: 600 !important; color: #23282d !important; line-height: 1.3 !important; }
        .resbs-settings-content .form-table th { padding: 20px 10px 20px 0 !important; background: #fff !important; }
        .resbs-settings-content .form-table td { padding: 20px 10px !important; background: #fff !important; }
        .resbs-settings-content .form-table { background: #fff !important; }
        .resbs-settings-content form { background: #fff !important; }
        .resbs-settings-sidebar { background: #fff !important; }
        .resbs-settings-content fieldset { background: #fff !important; }
        .resbs-settings-content label { background: transparent !important; }
        .resbs-settings-content input, .resbs-settings-content select, .resbs-settings-content textarea { background: #fff !important; }
        ';
        wp_add_inline_style('resbs-enhanced-settings', $critical_css);
        
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
     * Add critical styles to admin head (backup method)
     */
    public function add_critical_styles() {
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'resbs-settings') === false) {
            return;
        }
        
        $critical_css = '
        /* FORCE ESTATIK STYLE - INLINE TO OVERRIDE EVERYTHING */
        .resbs-enhanced-settings { margin: 0 !important; background: #fff !important; }
        .resbs-enhanced-settings .wrap { margin: 0 !important; padding: 0 !important; max-width: 100% !important; background: #fff !important; padding-top: 20px !important; }
        .resbs-settings-container { display: flex !important; margin: 0 !important; background: #fff !important; border: none !important; box-shadow: none !important; width: 100% !important; margin-top: 0 !important; padding-top: 20px !important; }
        .resbs-settings-sidebar { width: 220px !important; background: #fff !important; border-right: 1px solid #e5e5e5 !important; flex-shrink: 0 !important; padding: 0 !important; min-width: 220px !important; }
        .resbs-settings-nav { background: #fff !important; }
        .resbs-nav-tabs { background: #fff !important; list-style: none !important; margin: 0 !important; padding: 0 !important; }
        .resbs-nav-item { background: #fff !important; margin: 0 !important; border-bottom: 1px solid #e5e5e5 !important; }
        .resbs-nav-link { background: #fff !important; display: block !important; padding: 12px 20px !important; text-decoration: none !important; color: #555 !important; font-size: 13px !important; font-weight: 600 !important; border-left: 3px solid transparent !important; outline: none !important; border: none !important; }
        .resbs-nav-link:hover { background: #f9f9f9 !important; color: #23282d !important; outline: none !important; }
        .resbs-nav-link.active { background: #f9f9f9 !important; color: #23282d !important; font-weight: 600 !important; border-left-color: #46b450 !important; outline: none !important; }
        .resbs-nav-link:focus { outline: none !important; border: none !important; border-left: 3px solid transparent !important; }
        .resbs-nav-link:active { outline: none !important; border: none !important; border-left: 3px solid transparent !important; }
        .resbs-nav-link.active:focus { border-left-color: #46b450 !important; }
        .resbs-nav-link.active:active { border-left-color: #46b450 !important; }
        .resbs-settings-content { flex: 1 !important; background: #fff !important; padding: 30px 40px 30px 50px !important; }
        .resbs-settings-content h2 { margin: 0 0 25px 0 !important; font-size: 23px !important; font-weight: 600 !important; color: #23282d !important; padding: 0 !important; border: none !important; }
        .resbs-color-picker-group { display: flex !important; align-items: center !important; gap: 10px !important; }
        .resbs-color-input { width: 50px !important; height: 30px !important; border: 1px solid #8c8f94 !important; border-radius: 3px !important; }
        .resbs-color-hex { width: 100px !important; padding: 5px 8px !important; border: 1px solid #8c8f94 !important; border-radius: 3px !important; font-size: 13px !important; }
        .resbs-form-actions { margin-top: 25px !important; display: flex !important; gap: 10px !important; padding-top: 20px !important; border-top: 1px solid #e5e5e5 !important; }
        .resbs-save-button { background: #2271b1 !important; color: #fff !important; border-color: #2271b1 !important; padding: 0 12px !important; min-height: 30px !important; border-radius: 3px !important; font-size: 13px !important; }
        .resbs-save-button:hover { background: #135e96 !important; border-color: #135e96 !important; }
        .resbs-settings-page-title { margin: 0 0 20px 0 !important; padding: 20px 0 0 20px !important; font-size: 23px !important; font-weight: 600 !important; color: #23282d !important; line-height: 1.3 !important; }
        .resbs-settings-content .form-table th { padding: 20px 10px 20px 0 !important; background: #fff !important; }
        .resbs-settings-content .form-table td { padding: 20px 10px !important; background: #fff !important; }
        .resbs-settings-content .form-table { background: #fff !important; }
        .resbs-settings-content form { background: #fff !important; }
        .resbs-settings-sidebar { background: #fff !important; }
        .resbs-settings-content fieldset { background: #fff !important; }
        .resbs-settings-content label { background: transparent !important; }
        .resbs-settings-content input, .resbs-settings-content select, .resbs-settings-content textarea { background: #fff !important; }
        ';
        
        // Output CSS directly (safe - controlled content, no user input)
        echo '<style id="resbs-enhanced-settings-critical">' . "\n";
        echo $critical_css;
        echo '</style>' . "\n";
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
            <h1 class="resbs-settings-page-title"><?php esc_html_e('Settings', 'realestate-booking-suite'); ?></h1>
            
            <div class="resbs-settings-container">
                <div class="resbs-settings-sidebar">
                    <div class="resbs-settings-nav">
                        <ul class="resbs-nav-tabs">
                            <li class="resbs-nav-item">
                                <a href="<?php echo esc_url(add_query_arg(array('page' => 'resbs-settings', 'tab' => 'general'), admin_url('admin.php'))); ?>" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'general' ? 'active' : ''); ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('General', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="<?php echo esc_url(add_query_arg(array('page' => 'resbs-settings', 'tab' => 'map'), admin_url('admin.php'))); ?>" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'map' ? 'active' : ''); ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('Map', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="<?php echo esc_url(add_query_arg(array('page' => 'resbs-settings', 'tab' => 'listings'), admin_url('admin.php'))); ?>" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'listings' ? 'active' : ''); ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('Listings', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="<?php echo esc_url(add_query_arg(array('page' => 'resbs-settings', 'tab' => 'user-profile'), admin_url('admin.php'))); ?>" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'user-profile' ? 'active' : ''); ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('User profile', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="<?php echo esc_url(add_query_arg(array('page' => 'resbs-settings', 'tab' => 'login-signup'), admin_url('admin.php'))); ?>" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'login-signup' ? 'active' : ''); ?>">
                                    <span class="resbs-nav-text"><?php esc_html_e('Log in & Sign up', 'realestate-booking-suite'); ?></span>
                                </a>
                            </li>
                            <li class="resbs-nav-item">
                                <a href="<?php echo esc_url(add_query_arg(array('page' => 'resbs-settings', 'tab' => 'seo'), admin_url('admin.php'))); ?>" class="resbs-nav-link <?php echo esc_attr($this->current_tab === 'seo' ? 'active' : ''); ?>">
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
        
        <?php if (isset($_GET['updated']) && sanitize_text_field($_GET['updated']) == '1'): ?>
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
        
        <?php if (isset($_GET['updated']) && sanitize_text_field($_GET['updated']) == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && sanitize_text_field($_GET['reset']) == '1'): ?>
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
                        <div class="resbs-color-picker-group">
                            <input type="color" id="resbs_main_color" name="resbs_main_color" value="<?php echo esc_attr(get_option('resbs_main_color', '#28a745')); ?>" class="resbs-color-input">
                            <input type="text" class="resbs-color-hex" value="<?php echo esc_attr(get_option('resbs_main_color', '#28a745')); ?>" placeholder="#28a745" maxlength="7">
                            <button type="button" class="resbs-color-reset button" data-default="#28a745"><?php esc_html_e('Reset', 'realestate-booking-suite'); ?></button>
                        </div>
                        <p class="description"><?php esc_html_e('Large buttons', 'realestate-booking-suite'); ?></p>
                    </td>
                </tr>
          
                <tr>
                    <th scope="row"><label for="resbs_secondary_color"><?php esc_html_e('Secondary color', 'realestate-booking-suite'); ?></label></th>
                    <td>
                        <div class="resbs-color-picker-group">
                            <input type="color" id="resbs_secondary_color" name="resbs_secondary_color" value="<?php echo esc_attr(get_option('resbs_secondary_color', '#0073aa')); ?>" class="resbs-color-input">
                            <input type="text" class="resbs-color-hex" value="<?php echo esc_attr(get_option('resbs_secondary_color', '#0073aa')); ?>" placeholder="#0073aa" maxlength="7">
                            <button type="button" class="resbs-color-reset button" data-default="#0073aa"><?php esc_html_e('Reset', 'realestate-booking-suite'); ?></button>
                        </div>
                        <p class="description"><?php esc_html_e('Small buttons', 'realestate-booking-suite'); ?></p>
                    </td>
                </tr>
            </table>
            
            <div class="resbs-form-actions">
                <button type="submit" class="resbs-save-button"><?php esc_html_e('SAVE CHANGES', 'realestate-booking-suite'); ?></button>
                <button type="button" class="button button-secondary resbs-reset-button" onclick="if(confirm('<?php esc_attr_e('Are you sure you want to reset all General settings to default values? This cannot be undone.', 'realestate-booking-suite'); ?>')) { document.getElementById('resetGeneralForm').submit(); }"><?php esc_html_e('Reset to Defaults', 'realestate-booking-suite'); ?></button>
            </div>
        </form>
        
        <!-- Reset Form -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" id="resetGeneralForm" class="resbs-hidden-form">
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
        
        <?php if (isset($_GET['updated']) && sanitize_text_field($_GET['updated']) == '1'): ?>
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
        
        <?php if (isset($_GET['updated']) && sanitize_text_field($_GET['updated']) == '1'): ?>
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
        
        <?php if (isset($_GET['updated']) && sanitize_text_field($_GET['updated']) == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && sanitize_text_field($_GET['reset']) == '1'): ?>
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
        
        <?php if (isset($_GET['updated']) && sanitize_text_field($_GET['updated']) == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && sanitize_text_field($_GET['reset']) == '1'): ?>
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
        
        <?php if (isset($_GET['updated']) && sanitize_text_field($_GET['updated']) == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && sanitize_text_field($_GET['reset']) == '1'): ?>
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
        
        <?php if (isset($_GET['updated']) && sanitize_text_field($_GET['updated']) == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && sanitize_text_field($_GET['reset']) == '1'): ?>
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
        
        <?php if (isset($_GET['updated']) && sanitize_text_field($_GET['updated']) == '1'): ?>
            <div class="notice notice-success"><p><?php esc_html_e('Settings saved successfully!', 'realestate-booking-suite'); ?></p></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['reset']) && sanitize_text_field($_GET['reset']) == '1'): ?>
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
        
        // Handle colors - check both color input and hex input
        if (isset($_POST['resbs_main_color'])) {
            $main_color = sanitize_text_field($_POST['resbs_main_color']);
            // Validate hex color (accepts both with and without #)
            if (preg_match('/^#?[a-fA-F0-9]{6}$/', $main_color)) {
                // Ensure it starts with #
                if (substr($main_color, 0, 1) !== '#') {
                    $main_color = '#' . $main_color;
                }
                update_option('resbs_main_color', $main_color);
            }
        }
        
        if (isset($_POST['resbs_secondary_color'])) {
            $secondary_color = sanitize_text_field($_POST['resbs_secondary_color']);
            // Validate hex color (accepts both with and without #)
            if (preg_match('/^#?[a-fA-F0-9]{6}$/', $secondary_color)) {
                // Ensure it starts with #
                if (substr($secondary_color, 0, 1) !== '#') {
                    $secondary_color = '#' . $secondary_color;
                }
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
        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'realestate-booking-suite'));
        }
        
        // Get data statistics
        $property_counts = wp_count_posts('property');
        $total_properties = $property_counts->publish + $property_counts->draft + $property_counts->pending;
        $published_properties = $property_counts->publish;
        $draft_properties = $property_counts->draft;
        $pending_properties = $property_counts->pending;
        $trash_properties = $property_counts->trash;
        
        // Get property types and locations
        $property_types = get_terms(array(
            'taxonomy' => 'property_type',
            'hide_empty' => false,
        ));
        $property_types_count = !is_wp_error($property_types) ? count($property_types) : 0;
        
        $property_locations = get_terms(array(
            'taxonomy' => 'property_location',
            'hide_empty' => false,
        ));
        $property_locations_count = !is_wp_error($property_locations) ? count($property_locations) : 0;
        
        // Get bookings count
        $bookings_count = 0;
        if (post_type_exists('property_booking')) {
            $booking_counts = wp_count_posts('property_booking');
            $bookings_count = $booking_counts->publish + $booking_counts->pending;
        }
        
        // Get orphaned meta count (approximate)
        global $wpdb;
        $orphaned_meta = $wpdb->get_var("
            SELECT COUNT(*) 
            FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.ID IS NULL AND pm.meta_key LIKE '_property_%'
        ");
        
        // Get database size info
        $db_size = $wpdb->get_var("
            SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB'
            FROM information_schema.tables
            WHERE table_schema = DATABASE()
        ");
        
        // Create nonces
        $export_nonce = wp_create_nonce('resbs_export_properties');
        $cleanup_nonce = wp_create_nonce('resbs_cleanup_orphaned_data');
        $stats_nonce = wp_create_nonce('resbs_get_data_stats');
        
        ?>
        <div class="wrap resbs-admin-wrap">
            <!-- Header -->
            <div class="resbs-welcome-header" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ddd; border-radius: 4px;">
                <h1 style="margin: 0; font-size: 28px; font-weight: 600;">
                    <span class="dashicons dashicons-database" style="vertical-align: middle; margin-right: 10px;"></span>
                    <?php esc_html_e('Data Manager', 'realestate-booking-suite'); ?>
                </h1>
                <p style="margin: 10px 0 0 0; color: #666;">
                    <?php esc_html_e('Manage, export, import, and optimize your property data.', 'realestate-booking-suite'); ?>
                </p>
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
                            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Total Properties', 'realestate-booking-suite'); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; color: #666;">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #333;"><?php echo esc_html($published_properties); ?></div>
                            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Published', 'realestate-booking-suite'); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; color: #666;">
                            <span class="dashicons dashicons-category"></span>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #333;"><?php echo esc_html($property_types_count); ?></div>
                            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Property Types', 'realestate-booking-suite'); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; color: #666;">
                            <span class="dashicons dashicons-location"></span>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #333;"><?php echo esc_html($property_locations_count); ?></div>
                            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Locations', 'realestate-booking-suite'); ?></div>
                        </div>
                    </div>
                </div>
                
                <?php if ($bookings_count > 0): ?>
                <div class="resbs-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; color: #666;">
                            <span class="dashicons dashicons-calendar-alt"></span>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #333;"><?php echo esc_html($bookings_count); ?></div>
                            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Bookings', 'realestate-booking-suite'); ?></div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Main Content Grid -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin: 20px 0;">
                <!-- Export/Import Section -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <h2 style="margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                        <span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
                        <?php esc_html_e('Export & Import', 'realestate-booking-suite'); ?>
                    </h2>
                    
                    <div style="margin-top: 20px;">
                        <h3 style="margin-top: 0; font-size: 16px;"><?php esc_html_e('Export Properties', 'realestate-booking-suite'); ?></h3>
                        <p style="color: #666; font-size: 14px;">
                            <?php esc_html_e('Export your properties to CSV or JSON format for backup or migration purposes.', 'realestate-booking-suite'); ?>
                        </p>
                        
                        <div style="display: flex; gap: 10px; margin-top: 15px;">
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;">
                                <input type="hidden" name="action" value="resbs_export_properties">
                                <input type="hidden" name="format" value="csv">
                                <input type="hidden" name="nonce" value="<?php echo esc_attr($export_nonce); ?>">
                                <button type="submit" class="button button-primary">
                                    <span class="dashicons dashicons-media-spreadsheet" style="vertical-align: middle;"></span>
                                    <?php esc_html_e('Export as CSV', 'realestate-booking-suite'); ?>
                                </button>
                            </form>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;">
                                <input type="hidden" name="action" value="resbs_export_properties">
                                <input type="hidden" name="format" value="json">
                                <input type="hidden" name="nonce" value="<?php echo esc_attr($export_nonce); ?>">
                                <button type="submit" class="button button-primary">
                                    <span class="dashicons dashicons-media-code" style="vertical-align: middle;"></span>
                                    <?php esc_html_e('Export as JSON', 'realestate-booking-suite'); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                        <h3 style="margin-top: 0; font-size: 16px;"><?php esc_html_e('Import Properties', 'realestate-booking-suite'); ?></h3>
                        <p style="color: #666; font-size: 14px;">
                            <?php esc_html_e('Import properties from a previously exported CSV or JSON file.', 'realestate-booking-suite'); ?>
                        </p>
                        
                        <form id="resbs-import-form" style="margin-top: 15px;">
                            <input type="file" id="resbs-import-file" accept=".csv,.json" style="margin-bottom: 10px;">
                            <br>
                            <button type="button" class="button button-secondary" id="resbs-import-btn" data-nonce="<?php echo esc_attr($export_nonce); ?>">
                                <span class="dashicons dashicons-upload" style="vertical-align: middle;"></span>
                                <?php esc_html_e('Import Properties', 'realestate-booking-suite'); ?>
                            </button>
                        </form>
                        
                        <div id="resbs-import-status" style="margin-top: 15px; display: none;"></div>
                    </div>
                </div>

                <!-- Data Management Tools -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <h2 style="margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                        <span class="dashicons dashicons-admin-tools" style="vertical-align: middle;"></span>
                        <?php esc_html_e('Data Tools', 'realestate-booking-suite'); ?>
                    </h2>
                    
                    <div style="margin-top: 20px;">
                        <h3 style="margin-top: 0; font-size: 16px;"><?php esc_html_e('Cleanup Orphaned Data', 'realestate-booking-suite'); ?></h3>
                        <p style="color: #666; font-size: 14px;">
                            <?php esc_html_e('Remove orphaned post meta data that is no longer associated with properties.', 'realestate-booking-suite'); ?>
                        </p>
                        <p style="color: #d63638; font-size: 13px; margin-top: 5px;">
                            <strong><?php esc_html_e('Found:', 'realestate-booking-suite'); ?></strong> 
                            <?php echo esc_html($orphaned_meta); ?> <?php esc_html_e('orphaned meta entries', 'realestate-booking-suite'); ?>
                        </p>
                        <button type="button" class="button button-secondary resbs-cleanup-btn" data-nonce="<?php echo esc_attr($cleanup_nonce); ?>" style="margin-top: 10px;">
                            <span class="dashicons dashicons-trash" style="vertical-align: middle;"></span>
                            <?php esc_html_e('Cleanup Orphaned Data', 'realestate-booking-suite'); ?>
                        </button>
                        <div id="resbs-cleanup-status" style="margin-top: 15px; display: none;"></div>
                    </div>
                    
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
                        <h3 style="margin-top: 0; font-size: 16px;"><?php esc_html_e('Database Information', 'realestate-booking-suite'); ?></h3>
                        <div style="font-size: 13px; color: #666; line-height: 1.8; margin-top: 10px;">
                            <div><strong><?php esc_html_e('Database Size:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html($db_size ? $db_size . ' MB' : 'N/A'); ?></div>
                            <div><strong><?php esc_html_e('Draft Properties:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html($draft_properties); ?></div>
                            <div><strong><?php esc_html_e('Pending Properties:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html($pending_properties); ?></div>
                            <div><strong><?php esc_html_e('Trashed Properties:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html($trash_properties); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px;">
                <h2 style="margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                    <span class="dashicons dashicons-info" style="vertical-align: middle;"></span>
                    <?php esc_html_e('Data Management Tips', 'realestate-booking-suite'); ?>
                </h2>
                <ul style="color: #666; line-height: 1.8; margin: 15px 0; padding-left: 20px;">
                    <li><?php esc_html_e('Always backup your database before performing cleanup operations.', 'realestate-booking-suite'); ?></li>
                    <li><?php esc_html_e('Export your data regularly to prevent data loss.', 'realestate-booking-suite'); ?></li>
                    <li><?php esc_html_e('Orphaned data cleanup is safe and only removes metadata not linked to any property.', 'realestate-booking-suite'); ?></li>
                    <li><?php esc_html_e('CSV exports are best for spreadsheet applications, JSON for programmatic use.', 'realestate-booking-suite'); ?></li>
                </ul>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Cleanup functionality
            $('.resbs-cleanup-btn').on('click', function() {
                if (!confirm('<?php esc_html_e('Are you sure you want to cleanup orphaned data? This action cannot be undone.', 'realestate-booking-suite'); ?>')) {
                    return;
                }
                
                var nonce = $(this).data('nonce');
                var $status = $('#resbs-cleanup-status');
                var $btn = $(this);
                
                $status.html('<span class="spinner is-active" style="float: none; margin: 0 10px;"></span> <?php esc_html_e('Cleaning up...', 'realestate-booking-suite'); ?>').show();
                $btn.prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'resbs_cleanup_orphaned_data',
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $status.html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                        }
                        $btn.prop('disabled', false);
                    },
                    error: function() {
                        $status.html('<div class="notice notice-error"><p><?php esc_html_e('Cleanup failed. Please try again.', 'realestate-booking-suite'); ?></p></div>');
                        $btn.prop('disabled', false);
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Handle export properties request (admin-post action)
     */
    public function handle_export_properties() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_export_properties')) {
            wp_die(esc_html__('Security check failed', 'realestate-booking-suite'), esc_html__('Error', 'realestate-booking-suite'), array('response' => 403));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Unauthorized', 'realestate-booking-suite'), esc_html__('Error', 'realestate-booking-suite'), array('response' => 403));
        }
        
        $format = isset($_POST['format']) ? sanitize_text_field($_POST['format']) : 'csv';
        
        if (!in_array($format, array('csv', 'json'))) {
            wp_die(esc_html__('Invalid format', 'realestate-booking-suite'), esc_html__('Error', 'realestate-booking-suite'), array('response' => 400));
        }
        
        // Get all properties
        $properties = get_posts(array(
            'post_type' => 'property',
            'posts_per_page' => -1,
            'post_status' => array('publish', 'draft', 'pending'),
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        if (empty($properties)) {
            wp_die(esc_html__('No properties found to export', 'realestate-booking-suite'), esc_html__('Error', 'realestate-booking-suite'), array('response' => 404));
        }
        
        // Prepare data
        $export_data = array();
        foreach ($properties as $property) {
            $meta = get_post_meta($property->ID);
            $property_data = array(
                'ID' => $property->ID,
                'title' => $property->post_title,
                'content' => $property->post_content,
                'excerpt' => $property->post_excerpt,
                'status' => $property->post_status,
                'author' => $property->post_author,
                'date' => $property->post_date,
                'modified' => $property->post_modified,
            );
            
            // Add meta fields - properly handle arrays
            foreach ($meta as $key => $value) {
                if (strpos($key, '_property_') === 0) {
                    // Handle array values properly
                    if (is_array($value)) {
                        if (count($value) === 1) {
                            $property_data[$key] = $value[0];
                        } else {
                            // Convert array to JSON string for export
                            $property_data[$key] = json_encode($value);
                        }
                    } else {
                        $property_data[$key] = $value;
                    }
                    
                    // Handle serialized data (like gallery)
                    if (is_string($property_data[$key]) && is_serialized($property_data[$key])) {
                        $unserialized = maybe_unserialize($property_data[$key]);
                        if (is_array($unserialized)) {
                            $property_data[$key] = json_encode($unserialized);
                        }
                    }
                }
            }
            
            // Add taxonomies
            $types = wp_get_post_terms($property->ID, 'property_type', array('fields' => 'names'));
            $statuses = wp_get_post_terms($property->ID, 'property_status', array('fields' => 'names'));
            $locations = wp_get_post_terms($property->ID, 'property_location', array('fields' => 'names'));
            $tags = wp_get_post_terms($property->ID, 'property_tag', array('fields' => 'names'));
            
            $property_data['property_types'] = !is_wp_error($types) ? implode(', ', $types) : '';
            $property_data['property_statuses'] = !is_wp_error($statuses) ? implode(', ', $statuses) : '';
            $property_data['property_locations'] = !is_wp_error($locations) ? implode(', ', $locations) : '';
            $property_data['property_tags'] = !is_wp_error($tags) ? implode(', ', $tags) : '';
            
            $export_data[] = $property_data;
        }
        
        // Set headers to force download
        $filename = 'properties-export-' . date('Y-m-d-H-i-s') . '.' . $format;
        
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        if ($format === 'json') {
            // Output JSON directly with download headers
            header('Content-Type: application/json; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . esc_attr($filename) . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            
            echo json_encode($export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            // Output CSV directly with download headers
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . esc_attr($filename) . '"');
            header('Pragma: no-cache');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            
            // Output UTF-8 BOM for Excel compatibility
            echo "\xEF\xBB\xBF";
            
            $output = fopen('php://output', 'w');
            
            // Write headers
            if (!empty($export_data)) {
                fputcsv($output, array_keys($export_data[0]));
            }
            
            // Write data
            foreach ($export_data as $row) {
                fputcsv($output, $row);
            }
            
            fclose($output);
            exit;
        }
    }
    
    /**
     * Handle cleanup orphaned data AJAX request
     */
    public function handle_cleanup_orphaned_data() {
        check_ajax_referer('resbs_cleanup_orphaned_data', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Unauthorized', 'realestate-booking-suite'));
        }
        
        global $wpdb;
        
        // Delete orphaned post meta
        // Using esc_like for LIKE pattern and prepare for safety
        $like_pattern = $wpdb->esc_like('_property_') . '%';
        $deleted = $wpdb->query($wpdb->prepare("
            DELETE pm FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.ID IS NULL AND pm.meta_key LIKE %s
        ", $like_pattern));
        
        if ($deleted === false) {
            wp_send_json_error(esc_html__('Failed to cleanup orphaned data', 'realestate-booking-suite'));
        }
        
        wp_send_json_success(array(
            'message' => sprintf(esc_html__('Successfully removed %d orphaned meta entries.', 'realestate-booking-suite'), $deleted)
        ));
    }
    
    /**
     * Handle get data stats AJAX request
     */
    public function handle_get_data_stats() {
        check_ajax_referer('resbs_get_data_stats', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Unauthorized', 'realestate-booking-suite'));
        }
        
        $property_counts = wp_count_posts('property');
        
        wp_send_json_success(array(
            'total' => $property_counts->publish + $property_counts->draft + $property_counts->pending,
            'published' => $property_counts->publish,
            'draft' => $property_counts->draft,
            'pending' => $property_counts->pending
        ));
    }
    
    public function fields_builder_callback() {
        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to access this page.', 'realestate-booking-suite'));
        }
        
        // Get existing custom fields
        $custom_fields = get_option('resbs_custom_fields', array());
        
        // Get standard fields list
        $standard_fields = $this->get_standard_property_fields();
        
        // Create nonces
        $save_nonce = wp_create_nonce('resbs_save_custom_field');
        $delete_nonce = wp_create_nonce('resbs_delete_custom_field');
        $get_nonce = wp_create_nonce('resbs_get_custom_fields');
        
        ?>
        <div class="wrap resbs-admin-wrap">
            <!-- Header -->
            <div class="resbs-welcome-header" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #ddd; border-radius: 4px;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1 style="margin: 0; font-size: 28px; font-weight: 600;">
                            <span class="dashicons dashicons-admin-generic" style="vertical-align: middle; margin-right: 10px;"></span>
                            <?php esc_html_e('Fields Builder', 'realestate-booking-suite'); ?>
                        </h1>
                        <p style="margin: 10px 0 0 0; color: #666;">
                            <?php esc_html_e('Create and manage custom fields for your properties.', 'realestate-booking-suite'); ?>
                        </p>
                    </div>
                    <button type="button" class="button button-primary" id="resbs-add-field-btn">
                        <span class="dashicons dashicons-plus-alt" style="vertical-align: middle;"></span>
                        <?php esc_html_e('Add Custom Field', 'realestate-booking-suite'); ?>
                    </button>
                </div>
            </div>

            <!-- Statistics -->
            <div class="resbs-stats-overview" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
                <div class="resbs-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; color: #666;">
                            <span class="dashicons dashicons-admin-generic"></span>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #333;"><?php echo esc_html(count($standard_fields)); ?></div>
                            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Standard Fields', 'realestate-booking-suite'); ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-stat-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 32px; color: #666;">
                            <span class="dashicons dashicons-plus-alt"></span>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #333;"><?php echo esc_html(count($custom_fields)); ?></div>
                            <div style="color: #666; font-size: 14px;"><?php esc_html_e('Custom Fields', 'realestate-booking-suite'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin: 20px 0;">
                <!-- Custom Fields List -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <h2 style="margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                        <span class="dashicons dashicons-list-view" style="vertical-align: middle;"></span>
                        <?php esc_html_e('Custom Fields', 'realestate-booking-suite'); ?>
                    </h2>
                    
                    <div id="resbs-custom-fields-list">
                        <?php if (empty($custom_fields)): ?>
                            <div style="text-align: center; padding: 40px 20px; color: #999;">
                                <span class="dashicons dashicons-admin-generic" style="font-size: 48px; display: block; margin-bottom: 10px;"></span>
                                <p><?php esc_html_e('No custom fields yet. Click "Add Custom Field" to create one.', 'realestate-booking-suite'); ?></p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($custom_fields as $field_id => $field): ?>
                                <div class="resbs-field-item" data-field-id="<?php echo esc_attr($field_id); ?>" style="padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 15px; background: #f9f9f9;">
                                    <div style="display: flex; justify-content: space-between; align-items: start;">
                                        <div style="flex: 1;">
                                            <h3 style="margin: 0 0 10px 0; font-size: 16px;">
                                                <?php echo esc_html($field['label']); ?>
                                                <?php if (!empty($field['required'])): ?>
                                                    <span style="color: #d63638; font-size: 12px;">*</span>
                                                <?php endif; ?>
                                            </h3>
                                            <div style="font-size: 13px; color: #666; margin-bottom: 8px;">
                                                <strong><?php esc_html_e('Type:', 'realestate-booking-suite'); ?></strong> 
                                                <?php echo esc_html(ucfirst(str_replace('_', ' ', $field['type']))); ?>
                                            </div>
                                            <?php if (!empty($field['meta_key'])): ?>
                                                <div style="font-size: 12px; color: #999; font-family: monospace;">
                                                    <?php echo esc_html($field['meta_key']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div style="display: flex; gap: 5px;">
                                            <button type="button" class="button button-small resbs-edit-field" data-field-id="<?php echo esc_attr($field_id); ?>">
                                                <span class="dashicons dashicons-edit" style="font-size: 16px;"></span>
                                            </button>
                                            <button type="button" class="button button-small resbs-delete-field" data-field-id="<?php echo esc_attr($field_id); ?>" data-nonce="<?php echo esc_attr($delete_nonce); ?>">
                                                <span class="dashicons dashicons-trash" style="font-size: 16px;"></span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Standard Fields Info -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px;">
                    <h2 style="margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                        <span class="dashicons dashicons-info" style="vertical-align: middle;"></span>
                        <?php esc_html_e('Standard Fields', 'realestate-booking-suite'); ?>
                    </h2>
                    
                    <p style="color: #666; font-size: 14px; margin-bottom: 15px;">
                        <?php esc_html_e('These are the default property fields that come with the plugin. They cannot be modified but you can add custom fields to extend functionality.', 'realestate-booking-suite'); ?>
                    </p>
                    
                    <div style="max-height: 500px; overflow-y: auto;">
                        <ul style="list-style: none; padding: 0; margin: 0;">
                            <?php foreach ($standard_fields as $field_key => $field_label): ?>
                                <li style="padding: 8px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px;">
                                    <span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 16px; vertical-align: middle;"></span>
                                    <?php echo esc_html($field_label); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Field Modal -->
        <div id="resbs-field-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 100000; overflow-y: auto;">
            <div style="max-width: 600px; margin: 50px auto; background: #fff; border-radius: 4px; padding: 30px; position: relative;">
                <button type="button" class="resbs-close-modal" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
                
                <h2 id="resbs-modal-title" style="margin-top: 0;"><?php esc_html_e('Add Custom Field', 'realestate-booking-suite'); ?></h2>
                
                <form id="resbs-field-form">
                    <input type="hidden" id="resbs-field-id" name="field_id" value="">
                    <input type="hidden" name="nonce" value="<?php echo esc_attr($save_nonce); ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="resbs-field-label"><?php esc_html_e('Field Label', 'realestate-booking-suite'); ?> <span style="color: #d63638;">*</span></label>
                            </th>
                            <td>
                                <!-- CRITICAL FIX: Removed HTML5 required attribute - WordPress handles validation server-side -->
                                <input type="text" id="resbs-field-label" name="label" class="regular-text">
                                <p class="description"><?php esc_html_e('The label displayed to users', 'realestate-booking-suite'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="resbs-field-type"><?php esc_html_e('Field Type', 'realestate-booking-suite'); ?> <span style="color: #d63638;">*</span></label>
                            </th>
                            <td>
                                <!-- CRITICAL FIX: Removed HTML5 required attribute - WordPress handles validation server-side -->
                                <select id="resbs-field-type" name="type" class="regular-text">
                                    <option value="text"><?php esc_html_e('Text', 'realestate-booking-suite'); ?></option>
                                    <option value="textarea"><?php esc_html_e('Textarea', 'realestate-booking-suite'); ?></option>
                                    <option value="number"><?php esc_html_e('Number', 'realestate-booking-suite'); ?></option>
                                    <option value="email"><?php esc_html_e('Email', 'realestate-booking-suite'); ?></option>
                                    <option value="url"><?php esc_html_e('URL', 'realestate-booking-suite'); ?></option>
                                    <option value="select"><?php esc_html_e('Select', 'realestate-booking-suite'); ?></option>
                                    <option value="checkbox"><?php esc_html_e('Checkbox', 'realestate-booking-suite'); ?></option>
                                    <option value="radio"><?php esc_html_e('Radio', 'realestate-booking-suite'); ?></option>
                                    <option value="date"><?php esc_html_e('Date', 'realestate-booking-suite'); ?></option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr id="resbs-options-row" style="display: none;">
                            <th scope="row">
                                <label for="resbs-field-options"><?php esc_html_e('Options', 'realestate-booking-suite'); ?></label>
                            </th>
                            <td>
                                <textarea id="resbs-field-options" name="options" class="large-text" rows="4" placeholder="<?php esc_attr_e('One option per line', 'realestate-booking-suite'); ?>"></textarea>
                                <p class="description"><?php esc_html_e('Enter one option per line (for Select/Radio fields)', 'realestate-booking-suite'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="resbs-field-meta-key"><?php esc_html_e('Meta Key', 'realestate-booking-suite'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="resbs-field-meta-key" name="meta_key" class="regular-text" placeholder="property_custom_field">
                                <p class="description"><?php esc_html_e('Unique identifier (auto-generated if left empty)', 'realestate-booking-suite'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="resbs-field-placeholder"><?php esc_html_e('Placeholder', 'realestate-booking-suite'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="resbs-field-placeholder" name="placeholder" class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="resbs-field-default"><?php esc_html_e('Default Value', 'realestate-booking-suite'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="resbs-field-default" name="default_value" class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="resbs-field-group"><?php esc_html_e('Field Group', 'realestate-booking-suite'); ?></label>
                            </th>
                            <td>
                                <select id="resbs-field-group" name="group" class="regular-text">
                                    <option value="general"><?php esc_html_e('General', 'realestate-booking-suite'); ?></option>
                                    <option value="details"><?php esc_html_e('Property Details', 'realestate-booking-suite'); ?></option>
                                    <option value="location"><?php esc_html_e('Location', 'realestate-booking-suite'); ?></option>
                                    <option value="features"><?php esc_html_e('Features & Amenities', 'realestate-booking-suite'); ?></option>
                                    <option value="media"><?php esc_html_e('Media', 'realestate-booking-suite'); ?></option>
                                    <option value="other"><?php esc_html_e('Other', 'realestate-booking-suite'); ?></option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row"><?php esc_html_e('Settings', 'realestate-booking-suite'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" id="resbs-field-required" name="required" value="1">
                                    <?php esc_html_e('Required Field', 'realestate-booking-suite'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" id="resbs-field-show-in-search" name="show_in_search" value="1">
                                    <?php esc_html_e('Show in Search Filters', 'realestate-booking-suite'); ?>
                                </label>
                                <br>
                                <label>
                                    <input type="checkbox" id="resbs-field-show-in-listing" name="show_in_listing" value="1" checked>
                                    <?php esc_html_e('Show in Property Listing', 'realestate-booking-suite'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary"><?php esc_html_e('Save Field', 'realestate-booking-suite'); ?></button>
                        <button type="button" class="button resbs-cancel-field"><?php esc_html_e('Cancel', 'realestate-booking-suite'); ?></button>
                    </p>
                </form>
                
                <div id="resbs-field-message" style="margin-top: 15px; display: none;"></div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $modal = $('#resbs-field-modal');
            var $form = $('#resbs-field-form');
            var $fieldType = $('#resbs-field-type');
            var $optionsRow = $('#resbs-options-row');
            
            // Show/hide options field based on field type
            $fieldType.on('change', function() {
                if ($(this).val() === 'select' || $(this).val() === 'radio') {
                    $optionsRow.show();
                } else {
                    $optionsRow.hide();
                }
            });
            
            // Open modal for new field
            $('#resbs-add-field-btn').on('click', function() {
                $form[0].reset();
                $('#resbs-field-id').val('');
                $('#resbs-modal-title').text('<?php esc_html_e('Add Custom Field', 'realestate-booking-suite'); ?>');
                $optionsRow.hide();
                $modal.show();
            });
            
            // Edit field
            $('.resbs-edit-field').on('click', function() {
                var fieldId = $(this).data('field-id');
                // Load field data via AJAX
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'resbs_get_custom_fields',
                        field_id: fieldId,
                        nonce: '<?php echo esc_js($get_nonce); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            var field = response.data;
                            $('#resbs-field-id').val(fieldId);
                            $('#resbs-field-label').val(field.label || '');
                            $('#resbs-field-type').val(field.type || 'text').trigger('change');
                            $('#resbs-field-meta-key').val(field.meta_key || '');
                            $('#resbs-field-placeholder').val(field.placeholder || '');
                            $('#resbs-field-default').val(field.default_value || '');
                            $('#resbs-field-group').val(field.group || 'general');
                            $('#resbs-field-required').prop('checked', field.required == 1);
                            $('#resbs-field-show-in-search').prop('checked', field.show_in_search == 1);
                            $('#resbs-field-show-in-listing').prop('checked', field.show_in_listing == 1);
                            $('#resbs-field-options').val(field.options ? field.options.join('\n') : '');
                            $('#resbs-modal-title').text('<?php esc_html_e('Edit Custom Field', 'realestate-booking-suite'); ?>');
                            $modal.show();
                        }
                    }
                });
            });
            
            // Close modal
            $('.resbs-close-modal, .resbs-cancel-field').on('click', function() {
                $modal.hide();
                $form[0].reset();
                $('#resbs-field-message').hide().html('');
            });
            
            // Save field
            $form.on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                formData += '&action=resbs_save_custom_field';
                
                $('#resbs-field-message').html('<span class="spinner is-active" style="float: none; margin: 0 10px;"></span> <?php esc_html_e('Saving...', 'realestate-booking-suite'); ?>').show();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#resbs-field-message').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                            setTimeout(function() {
                                location.reload();
                            }, 1500);
                        } else {
                            $('#resbs-field-message').html('<div class="notice notice-error"><p>' + response.data + '</p></div>');
                        }
                    },
                    error: function() {
                        $('#resbs-field-message').html('<div class="notice notice-error"><p><?php esc_html_e('Failed to save field. Please try again.', 'realestate-booking-suite'); ?></p></div>');
                    }
                });
            });
            
            // Delete field
            $('.resbs-delete-field').on('click', function() {
                if (!confirm('<?php esc_html_e('Are you sure you want to delete this field?', 'realestate-booking-suite'); ?>')) {
                    return;
                }
                
                var fieldId = $(this).data('field-id');
                var nonce = $(this).data('nonce');
                var $item = $(this).closest('.resbs-field-item');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'resbs_delete_custom_field',
                        field_id: fieldId,
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $item.fadeOut(function() {
                                $(this).remove();
                                if ($('#resbs-custom-fields-list .resbs-field-item').length === 0) {
                                    $('#resbs-custom-fields-list').html('<div style="text-align: center; padding: 40px 20px; color: #999;"><span class="dashicons dashicons-admin-generic" style="font-size: 48px; display: block; margin-bottom: 10px;"></span><p><?php esc_html_e('No custom fields yet. Click "Add Custom Field" to create one.', 'realestate-booking-suite'); ?></p></div>');
                                }
                            });
                        } else {
                            alert(response.data);
                        }
                    },
                    error: function() {
                        alert('<?php esc_html_e('Failed to delete field. Please try again.', 'realestate-booking-suite'); ?>');
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    /**
     * Get standard property fields
     */
    private function get_standard_property_fields() {
        return array(
            '_property_price' => __('Price', 'realestate-booking-suite'),
            '_property_bedrooms' => __('Bedrooms', 'realestate-booking-suite'),
            '_property_bathrooms' => __('Bathrooms', 'realestate-booking-suite'),
            '_property_area_sqft' => __('Area (sqft)', 'realestate-booking-suite'),
            '_property_address' => __('Address', 'realestate-booking-suite'),
            '_property_city' => __('City', 'realestate-booking-suite'),
            '_property_state' => __('State', 'realestate-booking-suite'),
            '_property_zip' => __('ZIP Code', 'realestate-booking-suite'),
            '_property_country' => __('Country', 'realestate-booking-suite'),
            '_property_latitude' => __('Latitude', 'realestate-booking-suite'),
            '_property_longitude' => __('Longitude', 'realestate-booking-suite'),
            '_property_year_built' => __('Year Built', 'realestate-booking-suite'),
            '_property_features' => __('Features', 'realestate-booking-suite'),
            '_property_amenities' => __('Amenities', 'realestate-booking-suite'),
        );
    }
    
    /**
     * Handle save custom field AJAX request
     */
    public function handle_save_custom_field() {
        check_ajax_referer('resbs_save_custom_field', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Unauthorized', 'realestate-booking-suite'));
        }
        
        $field_id = isset($_POST['field_id']) ? sanitize_text_field($_POST['field_id']) : '';
        $label = isset($_POST['label']) ? sanitize_text_field($_POST['label']) : '';
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'text';
        $meta_key = isset($_POST['meta_key']) ? sanitize_text_field($_POST['meta_key']) : '';
        $placeholder = isset($_POST['placeholder']) ? sanitize_text_field($_POST['placeholder']) : '';
        $default_value = isset($_POST['default_value']) ? sanitize_text_field($_POST['default_value']) : '';
        $group = isset($_POST['group']) ? sanitize_text_field($_POST['group']) : 'general';
        $required = isset($_POST['required']) ? 1 : 0;
        $show_in_search = isset($_POST['show_in_search']) ? 1 : 0;
        $show_in_listing = isset($_POST['show_in_listing']) ? 1 : 0;
        $options = isset($_POST['options']) ? sanitize_textarea_field($_POST['options']) : '';
        
        if (empty($label)) {
            wp_send_json_error(esc_html__('Field label is required', 'realestate-booking-suite'));
        }
        
        // Generate meta key if not provided
        if (empty($meta_key)) {
            $meta_key = '_property_' . sanitize_key(str_replace(' ', '_', strtolower($label)));
        } else {
            // Remove _property_ prefix if already present to avoid double prefix
            $meta_key = preg_replace('/^_property_/', '', $meta_key);
            $meta_key = '_property_' . sanitize_key($meta_key);
        }
        
        // Process options
        $options_array = array();
        if (!empty($options) && in_array($type, array('select', 'radio'))) {
            $options_lines = explode("\n", $options);
            foreach ($options_lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $options_array[] = $line;
                }
            }
        }
        
        // Get existing fields
        $custom_fields = get_option('resbs_custom_fields', array());
        
        // Create or update field
        if (empty($field_id)) {
            $field_id = 'field_' . time() . '_' . wp_generate_password(6, false);
        }
        
        $custom_fields[$field_id] = array(
            'label' => $label,
            'type' => $type,
            'meta_key' => $meta_key,
            'placeholder' => $placeholder,
            'default_value' => $default_value,
            'group' => $group,
            'required' => $required,
            'show_in_search' => $show_in_search,
            'show_in_listing' => $show_in_listing,
            'options' => $options_array,
        );
        
        update_option('resbs_custom_fields', $custom_fields);
        
        wp_send_json_success(array(
            'message' => esc_html__('Field saved successfully!', 'realestate-booking-suite')
        ));
    }
    
    /**
     * Handle delete custom field AJAX request
     */
    public function handle_delete_custom_field() {
        check_ajax_referer('resbs_delete_custom_field', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Unauthorized', 'realestate-booking-suite'));
        }
        
        $field_id = isset($_POST['field_id']) ? sanitize_text_field($_POST['field_id']) : '';
        
        if (empty($field_id)) {
            wp_send_json_error(esc_html__('Field ID is required', 'realestate-booking-suite'));
        }
        
        $custom_fields = get_option('resbs_custom_fields', array());
        
        if (isset($custom_fields[$field_id])) {
            unset($custom_fields[$field_id]);
            update_option('resbs_custom_fields', $custom_fields);
            wp_send_json_success(array(
                'message' => esc_html__('Field deleted successfully', 'realestate-booking-suite')
            ));
        } else {
            wp_send_json_error(esc_html__('Field not found', 'realestate-booking-suite'));
        }
    }
    
    /**
     * Handle get custom fields AJAX request
     */
    public function handle_get_custom_fields() {
        check_ajax_referer('resbs_get_custom_fields', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(esc_html__('Unauthorized', 'realestate-booking-suite'));
        }
        
        $field_id = isset($_POST['field_id']) ? sanitize_text_field($_POST['field_id']) : '';
        
        $custom_fields = get_option('resbs_custom_fields', array());
        
        if (!empty($field_id) && isset($custom_fields[$field_id])) {
            wp_send_json_success($custom_fields[$field_id]);
        } else {
            wp_send_json_success($custom_fields);
        }
    }
}
