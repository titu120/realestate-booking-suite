<?php
/**
 * Elementor Widgets Class
 * 
 * SECURITY NOTES:
 * - Direct access prevention: ABSPATH check at top of file
 * - Nonce creation: Creates 'resbs_elementor_nonce' for AJAX requests (see enqueue_elementor_scripts())
 * - Nonce verification: Handled in AJAX handlers (class-resbs-frontend.php)
 * - User permissions: No admin functions in this file; widget registration is handled by Elementor
 * - Data sanitization: All output uses esc_* functions (esc_url, esc_js, esc_html, esc_textarea)
 * 
 * AJAX SECURITY REQUIREMENTS:
 * All AJAX handlers that use this nonce must:
 * 1. Verify nonce: wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')
 * 2. Sanitize all user input: sanitize_text_field(), sanitize_email(), intval(), etc.
 * 3. Validate data before processing
 * 4. Use capability checks if modifying data (current_user_can())
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Global flag to ensure widget files are only loaded once
if (!function_exists('resbs_load_elementor_widget_files')) {
    function resbs_load_elementor_widget_files() {
        static $files_loaded = false;
        
        if ($files_loaded) {
    return;
}

        $widget_files = array(
            // Existing widgets
            array('file' => 'includes/elementor/class-resbs-property-grid-widget.php', 'class' => 'RESBS_Property_Grid_Widget'),
            array('file' => 'includes/elementor/class-resbs-property-carousel-widget.php', 'class' => 'RESBS_Property_Carousel_Widget'),
            
            // New Estatik-style widgets
            array('file' => 'includes/elementor/class-resbs-search-widget.php', 'class' => 'RESBS_Search_Widget'),
            array('file' => 'includes/elementor/class-resbs-slider-widget.php', 'class' => 'RESBS_Slider_Widget'),
            array('file' => 'includes/elementor/class-resbs-request-form-widget.php', 'class' => 'RESBS_Request_Form_Widget'),
            array('file' => 'includes/elementor/class-resbs-listings-widget.php', 'class' => 'RESBS_Listings_Widget'),
            array('file' => 'includes/elementor/class-resbs-authentication-widget.php', 'class' => 'RESBS_Authentication_Widget'),

        );
        
        foreach ($widget_files as $widget) {
            // Only load if class doesn't already exist
            if (class_exists($widget['class'])) {
                continue;
            }
            
            // Validate file path to prevent path traversal (defense-in-depth)
            // Even though $widget['file'] is from hardcoded array, validate for safety
            $file_relative = $widget['file'];
            if (strpos($file_relative, '..') !== false || strpos($file_relative, "\0") !== false) {
                continue; // Skip invalid file paths
            }
            
            $file_path = RESBS_PATH . $file_relative;
            // Additional validation: ensure file is within plugin directory
            $real_file_path = realpath($file_path);
            $real_plugin_path = realpath(RESBS_PATH);
            if ($real_file_path && $real_plugin_path && strpos($real_file_path, $real_plugin_path) === 0) {
                if (file_exists($file_path)) {
                    require_once $file_path;
                }
            }
        }
        
        $files_loaded = true;
    }
}

class RESBS_Elementor_Widgets {

    /**
     * Constructor
     */
    public function __construct() {
        // Load widget files first, before registering
        resbs_load_elementor_widget_files();
        
        // Use new Elementor hook (Elementor 3.5+) - primary method
        add_action('elementor/widgets/register', array($this, 'register_widgets_new'), 10);
        add_action('elementor/elements/categories_registered', array($this, 'add_elementor_widget_categories'), 10);
        
        // Enqueue styles with HIGH priority to load AFTER Elementor
        add_action('wp_enqueue_scripts', array($this, 'enqueue_elementor_styles'), 999);
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_elementor_styles'), 999);
        add_action('elementor/frontend/after_enqueue_scripts', array($this, 'enqueue_elementor_scripts'));
        
        // Fallback: Add inline CSS directly in wp_head as last resort
        add_action('wp_head', array($this, 'add_critical_css_inline'), 999);
    }

    /**
     * Register Elementor widgets (Elementor 3.5+)
     */
    public function register_widgets_new($widgets_manager) {
        static $loaded = false;
        if ($loaded) {
            return;
        }
        $loaded = true;
        
        // Widget files are already loaded in constructor
        // Ensure widgets are instances of Widget_Base before registering
        $widgets_to_register = array(
            'RESBS_Property_Grid_Widget',
            'RESBS_Property_Carousel_Widget',
            'RESBS_Search_Widget',
            'RESBS_Slider_Widget',
            'RESBS_Request_Form_Widget',
            'RESBS_Listings_Widget',
            'RESBS_Authentication_Widget',
            'RESBS_Half_Map_Widget',
        );
        
        foreach ($widgets_to_register as $widget_class) {
            if (class_exists($widget_class)) {
                try {
                    $widget_instance = new $widget_class();
                    // Verify it extends Widget_Base
                    if ($widget_instance instanceof \Elementor\Widget_Base) {
                        $widgets_manager->register($widget_instance);
                    }
                } catch (\Exception $e) {
                    // Silent error handling for production
                }
            }
        }
    }

    /**
     * Add Elementor widget categories
     */
    public function add_elementor_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'resbs-widgets',
            array(
                'title' => esc_html__('RealEstate Booking Suite', 'realestate-booking-suite'),
                'icon' => 'fa fa-home',
            )
        );
    }

    /**
     * Enqueue Elementor styles
     */
    public function enqueue_elementor_styles() {
        // Ensure Font Awesome is loaded for Elementor widgets
        if (!wp_style_is('font-awesome', 'enqueued') && !wp_style_is('font-awesome', 'registered')) {
            wp_enqueue_style(
                'font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                array(),
                '6.4.0',
                'all'
            );
        }
        
        // Load CSS with maximum priority
        wp_enqueue_style(
            'resbs-elementor',
            RESBS_URL . 'assets/css/elementor.css',
            array('elementor-frontend', 'elementor-frontend-css', 'font-awesome'),
            '2.0.0',
            'all'
        );
        
        // Load Search Widget CSS
        wp_enqueue_style(
            'resbs-elementor-search-widget',
            RESBS_URL . 'assets/css/elementor-search-widget.css',
            array('resbs-elementor', 'elementor-frontend', 'font-awesome'),
            '1.0.0',
            'all'
        );
        
        // Add comprehensive inline CSS with !important to ensure it applies
        $critical_css = $this->get_critical_css();
        wp_add_inline_style('resbs-elementor', $critical_css);
    }
    
    /**
     * Get critical CSS with maximum specificity
     */
    private function get_critical_css() {
        return "
        /* ============================================
           RESBS ELEMENTOR WIDGETS - CRITICAL CSS
           ============================================ */
        
        /* Reset Elementor container padding */
        .elementor-widget-resbs-property-grid .elementor-widget-container,
        .elementor-widget-resbs-property-carousel .elementor-widget-container,
        .elementor-widget-resbs-search .elementor-widget-container,
        .elementor-widget-resbs-slider .elementor-widget-container,
        .elementor-widget-resbs-request-form .elementor-widget-container,
        .elementor-widget-resbs-listings .elementor-widget-container,
        .elementor-widget-resbs-authentication .elementor-widget-container,
        .elementor-widget-resbs-half-map .elementor-widget-container {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        
        /* Property Grid Container */
        .elementor-widget-resbs-property-grid .resbs-property-grid,
        .elementor-widget-resbs-listings .resbs-properties-container {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
            gap: 25px !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Property Card - Maximum Specificity */
        .elementor-widget-resbs-property-grid .resbs-property-card,
        .elementor-widget-resbs-property-carousel .resbs-property-card,
        .elementor-widget-resbs-listings .resbs-property-card,
        .elementor-widget-resbs-slider .resbs-property-card {
            background: #ffffff !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            display: flex !important;
            flex-direction: column !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            transition: all 0.3s ease !important;
        }
        
        .elementor-widget-resbs-property-grid .resbs-property-card:hover,
        .elementor-widget-resbs-listings .resbs-property-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
        }
        
        /* Property Image */
        .elementor-widget-resbs-property-grid .resbs-property-image,
        .elementor-widget-resbs-listings .resbs-property-image {
            width: 100% !important;
            height: 220px !important;
            overflow: hidden !important;
            position: relative !important;
            background: #f8f9fa !important;
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .elementor-widget-resbs-property-grid .resbs-property-image img,
        .elementor-widget-resbs-listings .resbs-property-image img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        /* Property Content */
        .elementor-widget-resbs-property-grid .resbs-property-content,
        .elementor-widget-resbs-listings .resbs-property-content {
            padding: 20px !important;
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
            background: #ffffff !important;
        }
        
        /* Property Title */
        .elementor-widget-resbs-property-grid .resbs-property-title,
        .elementor-widget-resbs-listings .resbs-property-title {
            font-size: 18px !important;
            font-weight: 600 !important;
            margin: 0 0 10px 0 !important;
            line-height: 1.4 !important;
            color: #333333 !important;
        }
        
        .elementor-widget-resbs-property-grid .resbs-property-title a,
        .elementor-widget-resbs-listings .resbs-property-title a {
            color: #333333 !important;
            text-decoration: none !important;
        }
        
        .elementor-widget-resbs-property-grid .resbs-property-title a:hover,
        .elementor-widget-resbs-listings .resbs-property-title a:hover {
            color: #007cba !important;
        }
        
        /* Property Price */
        .elementor-widget-resbs-property-grid .resbs-property-price,
        .elementor-widget-resbs-listings .resbs-property-price {
            font-size: 22px !important;
            font-weight: 700 !important;
            color: #28a745 !important;
            margin: 0 0 15px 0 !important;
        }
        
        /* Property Meta */
        .elementor-widget-resbs-property-grid .resbs-property-meta,
        .elementor-widget-resbs-listings .resbs-property-meta {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 15px !important;
            margin: 0 0 15px 0 !important;
            font-size: 14px !important;
            color: #6c757d !important;
        }
        
        .elementor-widget-resbs-property-grid .resbs-meta-item,
        .elementor-widget-resbs-listings .resbs-meta-item {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }
        
        /* Buttons */
        .elementor-widget-resbs-property-grid .resbs-btn,
        .elementor-widget-resbs-listings .resbs-btn {
            padding: 10px 20px !important;
            border-radius: 6px !important;
            font-size: 14px !important;
            font-weight: 600 !important;
            text-decoration: none !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            border: none !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
        }
        
        .elementor-widget-resbs-property-grid .resbs-btn-primary,
        .elementor-widget-resbs-listings .resbs-btn-primary {
            background: #007cba !important;
            color: #ffffff !important;
        }
        
        .elementor-widget-resbs-property-grid .resbs-btn-primary:hover,
        .elementor-widget-resbs-listings .resbs-btn-primary:hover {
            background: #005a87 !important;
            color: #ffffff !important;
        }
        
        /* Search Widget */
        .elementor-widget-resbs-search .resbs-search-form {
            background: #ffffff !important;
            border-radius: 12px !important;
            padding: 30px !important;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid #e9ecef !important;
            margin: 0 !important;
        }
        
        .elementor-widget-resbs-search .resbs-search-input,
        .elementor-widget-resbs-search .resbs-search-select {
            width: 100% !important;
            padding: 12px 15px !important;
            border: 1px solid #dddddd !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
            margin: 0 !important;
        }
        
        .elementor-widget-resbs-search .resbs-search-btn {
            background: #007cba !important;
            color: #ffffff !important;
            padding: 14px 30px !important;
            border: none !important;
            border-radius: 8px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            display: inline-block !important;
        }
        
        /* Badges */
        .elementor-widget-resbs-property-grid .resbs-badge,
        .elementor-widget-resbs-listings .resbs-badge {
            padding: 4px 8px !important;
            border-radius: 4px !important;
            font-size: 12px !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            color: #ffffff !important;
            display: inline-block !important;
        }
        
        .elementor-widget-resbs-property-grid .resbs-badge-featured,
        .elementor-widget-resbs-listings .resbs-badge-featured {
            background: #ffc107 !important;
            color: #212529 !important;
        }
        
        /* Favorite Button */
        .elementor-widget-resbs-property-grid .resbs-favorite-btn,
        .elementor-widget-resbs-listings .resbs-favorite-btn {
            position: absolute !important;
            top: 10px !important;
            right: 10px !important;
            width: 40px !important;
            height: 40px !important;
            border-radius: 50% !important;
            background: rgba(255, 255, 255, 0.9) !important;
            border: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            z-index: 10 !important;
            cursor: pointer !important;
        }
        
        /* Property Actions */
        .elementor-widget-resbs-property-grid .resbs-property-actions,
        .elementor-widget-resbs-listings .resbs-property-actions {
            margin-top: auto !important;
            padding-top: 15px !important;
            border-top: 1px solid #e9ecef !important;
        }
        
        /* Request Form Widget */
        .elementor-widget-resbs-request-form .resbs-request-form {
            background: #ffffff !important;
            border-radius: 12px !important;
            padding: 30px !important;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid #e9ecef !important;
        }
        
        .elementor-widget-resbs-request-form input[type=\"text\"],
        .elementor-widget-resbs-request-form input[type=\"email\"],
        .elementor-widget-resbs-request-form input[type=\"tel\"],
        .elementor-widget-resbs-request-form textarea {
            width: 100% !important;
            padding: 12px 15px !important;
            border: 1px solid #dddddd !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
            margin-bottom: 15px !important;
        }
        
        .elementor-widget-resbs-request-form button,
        .elementor-widget-resbs-request-form .resbs-btn {
            background: #007cba !important;
            color: #ffffff !important;
            padding: 14px 30px !important;
            border: none !important;
            border-radius: 8px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            width: 100% !important;
        }
        
        /* Authentication Widget */
        .elementor-widget-resbs-authentication .resbs-auth-form {
            background: #ffffff !important;
            border-radius: 12px !important;
            padding: 30px !important;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid #e9ecef !important;
        }
        
        .elementor-widget-resbs-authentication input[type=\"text\"],
        .elementor-widget-resbs-authentication input[type=\"email\"],
        .elementor-widget-resbs-authentication input[type=\"password\"] {
            width: 100% !important;
            padding: 12px 15px !important;
            border: 1px solid #dddddd !important;
            border-radius: 8px !important;
            font-size: 14px !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
            margin-bottom: 15px !important;
        }
        
        /* Slider Widget */
        .elementor-widget-resbs-slider .resbs-slider-container {
            width: 100% !important;
            position: relative !important;
        }
        
        .elementor-widget-resbs-slider .resbs-slide {
            width: 100% !important;
            display: block !important;
        }
        
        /* Half Map Widget */
        .elementor-widget-resbs-half-map .resbs-half-map-container {
            display: flex !important;
            width: 100% !important;
            height: 600px !important;
        }
        
        .elementor-widget-resbs-half-map .resbs-half-map-map {
            flex: 1 !important;
            height: 100% !important;
        }
        
        .elementor-widget-resbs-half-map .resbs-half-map-listings {
            width: 40% !important;
            background: #ffffff !important;
            overflow-y: auto !important;
        }
        
        /* General Input/Select/Button Reset */
        .elementor-widget-resbs-search input,
        .elementor-widget-resbs-search select,
        .elementor-widget-resbs-search button,
        .elementor-widget-resbs-request-form input,
        .elementor-widget-resbs-request-form textarea,
        .elementor-widget-resbs-request-form button,
        .elementor-widget-resbs-authentication input,
        .elementor-widget-resbs-authentication button {
            font-family: inherit !important;
            font-size: inherit !important;
            line-height: inherit !important;
        }
        
        /* Listings Widget Specific */
        .elementor-widget-resbs-listings .resbs-listings-navbar {
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            margin-bottom: 20px !important;
            padding: 15px 20px !important;
            background: #f8f9fa !important;
            border-radius: 8px !important;
        }
        
        .elementor-widget-resbs-listings .resbs-view-btn {
            padding: 8px 12px !important;
            border: 1px solid #dddddd !important;
            background: #ffffff !important;
            border-radius: 4px !important;
            cursor: pointer !important;
            margin-left: 5px !important;
        }
        
        .elementor-widget-resbs-listings .resbs-view-btn.active {
            background: #007cba !important;
            color: #ffffff !important;
            border-color: #007cba !important;
        }
        
        /* Property Carousel Widget - Swiper Styles */
        .elementor-widget-resbs-property-carousel .resbs-property-carousel.swiper {
            position: relative !important;
            width: 100% !important;
            overflow: visible !important;
            padding-bottom: 50px !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-wrapper {
            display: flex !important;
            align-items: stretch !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide {
            height: auto !important;
            display: flex !important;
        }
        
        /* Swiper Navigation Buttons */
        .elementor-widget-resbs-property-carousel .swiper-button-next,
        .elementor-widget-resbs-property-carousel .swiper-button-prev {
            background: #ffffff !important;
            border: 2px solid #007cba !important;
            border-radius: 50% !important;
            width: 50px !important;
            height: 50px !important;
            color: #007cba !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            transition: all 0.3s ease !important;
            margin-top: 0 !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-button-next::after,
        .elementor-widget-resbs-property-carousel .swiper-button-prev::after {
            font-size: 20px !important;
            font-weight: 700 !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-button-next:hover,
        .elementor-widget-resbs-property-carousel .swiper-button-prev:hover {
            background: #007cba !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2) !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-button-prev {
            left: 10px !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-button-next {
            right: 10px !important;
        }
        
        /* Swiper Pagination Dots */
        .elementor-widget-resbs-property-carousel .swiper-pagination {
            position: absolute !important;
            bottom: 10px !important;
            width: 100% !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-pagination-bullet {
            background: #dddddd !important;
            opacity: 1 !important;
            width: 12px !important;
            height: 12px !important;
            transition: all 0.3s ease !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-pagination-bullet-active {
            background: #007cba !important;
            width: 24px !important;
            border-radius: 6px !important;
        }
        
        /* Carousel Property Cards inside Swiper slides */
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-property-card {
            width: 100% !important;
            height: 100% !important;
            margin: 0 !important;
            background: #ffffff !important;
            border-radius: 12px !important;
            overflow: hidden !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            display: flex !important;
            flex-direction: column !important;
            transition: all 0.3s ease !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-property-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15) !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-property-image {
            height: 220px !important;
            overflow: hidden !important;
            position: relative !important;
            width: 100% !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-property-image img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: block !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-property-content {
            padding: 20px !important;
            flex: 1 !important;
            display: flex !important;
            flex-direction: column !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-property-title {
            font-size: 18px !important;
            font-weight: 600 !important;
            margin: 0 0 10px 0 !important;
            color: #333333 !important;
            line-height: 1.4 !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-property-title a {
            color: #333333 !important;
            text-decoration: none !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-property-title a:hover {
            color: #007cba !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-property-price {
            font-size: 22px !important;
            font-weight: 700 !important;
            color: #28a745 !important;
            margin: 0 0 15px 0 !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-property-meta {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 15px !important;
            margin: 0 0 15px 0 !important;
            font-size: 14px !important;
            color: #6c757d !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-meta-item {
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-btn {
            width: 100% !important;
            margin-top: auto !important;
        }
        
        .elementor-widget-resbs-property-carousel .swiper-slide .resbs-property-location {
            font-size: 14px !important;
            color: #6c757d !important;
            margin-bottom: 10px !important;
            display: flex !important;
            align-items: center !important;
            gap: 5px !important;
        }
        ";
    }

    /**
     * Add critical CSS directly in wp_head as fallback
     * 
     * SECURITY: Uses esc_textarea() to escape CSS output and prevent XSS attacks
     */
    public function add_critical_css_inline() {
        if (!class_exists('\Elementor\Plugin')) {
            return;
        }
        
        $critical_css = $this->get_critical_css();
        // SECURITY: Escape CSS output to prevent XSS attacks
        echo '<style id="resbs-elementor-critical">' . esc_textarea($critical_css) . '</style>' . "\n";
    }
    
    /**
     * Enqueue Elementor scripts
     * 
     * SECURITY NOTES:
     * - Creates nonce for AJAX requests to prevent CSRF attacks
     * - Nonce is verified in AJAX handlers (class-resbs-frontend.php)
     * - All AJAX handlers must verify nonce using: wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')
     * - All user input in AJAX handlers must be sanitized and validated
     */
    public function enqueue_elementor_scripts() {
        // Enqueue Swiper.js for carousel functionality
        wp_enqueue_style(
            'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css',
            array(),
            '11.0.0'
        );
        
        wp_enqueue_script(
            'swiper',
            'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js',
            array(),
            '11.0.0',
            true
        );
        
        wp_enqueue_script(
            'resbs-elementor',
            RESBS_URL . 'assets/js/elementor.js',
            array('jquery', 'elementor-frontend', 'swiper'),
            '1.2.0',
            true
        );
        
        // Enqueue listings widget JavaScript
        wp_enqueue_script(
            'resbs-elementor-listings-widget',
            RESBS_URL . 'assets/js/elementor-listings-widget.js',
            array('jquery', 'elementor-frontend'),
            '1.0.0',
            true
        );

        // SECURITY: Create nonce for AJAX requests to prevent CSRF attacks
        // This nonce must be verified in all AJAX handlers that process user-submitted data
        // See class-resbs-frontend.php for nonce verification examples
        wp_localize_script('resbs-elementor', 'resbs_elementor_ajax', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('resbs_elementor_nonce')),
            'messages' => array(
                'loading' => esc_js(esc_html__('Loading...', 'realestate-booking-suite')),
                'no_properties' => esc_js(esc_html__('No properties found.', 'realestate-booking-suite')),
                'load_more' => esc_js(esc_html__('Load More', 'realestate-booking-suite')),
                'error' => esc_js(esc_html__('An error occurred. Please try again.', 'realestate-booking-suite'))
            )
        ));
    }
}

// Initialize Elementor widgets - use multiple hooks to ensure registration
function resbs_init_elementor_widgets() {
    static $initialized = false;
    
    if ($initialized) {
        return;
    }
    
    // Check if Elementor is available
    if (!class_exists('\Elementor\Widget_Base')) {
        return;
    }
    
    // Initialize the widgets class
    new RESBS_Elementor_Widgets();
    $initialized = true;
}

// Try multiple initialization points
add_action('plugins_loaded', 'resbs_init_elementor_widgets', 20);
add_action('init', 'resbs_init_elementor_widgets', 20);
add_action('elementor/loaded', 'resbs_init_elementor_widgets', 5);
