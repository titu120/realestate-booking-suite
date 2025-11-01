<?php
/**
 * Elementor Widgets Class
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
            array('file' => 'includes/elementor/class-resbs-half-map-widget.php', 'class' => 'RESBS_Half_Map_Widget'),
        );
        
        foreach ($widget_files as $widget) {
            // Only load if class doesn't already exist
            if (class_exists($widget['class'])) {
                continue;
            }
            
            $file_path = RESBS_PATH . $widget['file'];
            if (file_exists($file_path)) {
                require_once $file_path;
            } elseif (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('RESBS Widget file not found: ' . $file_path);
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
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_elementor_styles'));
        add_action('elementor/frontend/after_enqueue_scripts', array($this, 'enqueue_elementor_scripts'));
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
                    } else {
                        if (defined('WP_DEBUG') && WP_DEBUG) {
                            error_log('RESBS Widget does not extend Widget_Base: ' . $widget_class);
                        }
                    }
                } catch (\Exception $e) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log('RESBS Widget Registration Error (' . $widget_class . '): ' . $e->getMessage());
                    }
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
        // Load CSS with high priority after Elementor styles
        wp_enqueue_style(
            'resbs-elementor',
            RESBS_URL . 'assets/css/elementor.css',
            array('elementor-frontend', 'elementor-frontend-css'),
            '1.2.0',
            'all'
        );
        
        // Also add inline critical CSS for immediate application
        $critical_css = "
        .elementor-widget-resbs-property-grid .resbs-property-card,
        .elementor-widget-resbs-listings .resbs-property-card {
            background: #fff !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
            overflow: hidden !important;
            display: flex !important;
            flex-direction: column !important;
        }
        .elementor-widget-resbs-property-grid .resbs-property-image,
        .elementor-widget-resbs-listings .resbs-property-image {
            height: 220px !important;
            overflow: hidden !important;
            position: relative !important;
        }
        .elementor-widget-resbs-property-grid .resbs-property-image img,
        .elementor-widget-resbs-listings .resbs-property-image img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
        .elementor-widget-resbs-search .resbs-search-form {
            background: #fff !important;
            border-radius: 12px !important;
            padding: 30px !important;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08) !important;
        }
        ";
        
        wp_add_inline_style('resbs-elementor', $critical_css);
    }

    /**
     * Enqueue Elementor scripts
     */
    public function enqueue_elementor_scripts() {
        wp_enqueue_script(
            'resbs-elementor',
            RESBS_URL . 'assets/js/elementor.js',
            array('jquery', 'elementor-frontend'),
            '1.1.0',
            true
        );

        wp_localize_script('resbs-elementor', 'resbs_elementor_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('resbs_elementor_nonce'),
            'messages' => array(
                'loading' => esc_html__('Loading...', 'realestate-booking-suite'),
                'no_properties' => esc_html__('No properties found.', 'realestate-booking-suite'),
                'load_more' => esc_html__('Load More', 'realestate-booking-suite'),
                'error' => esc_html__('An error occurred. Please try again.', 'realestate-booking-suite')
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
