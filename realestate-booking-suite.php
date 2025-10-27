<?php 
/**
 * Plugin Name: RealEstate Booking Suite
 * Description: Professional real estate booking plugin that allows users and agents to submit properties, manage bookings, integrate with WooCommerce for payments, and display properties in responsive layouts. Plugin includes Elementor and Appearance widgets, advanced AJAX search, map integration, property details page, frontend dashboard, favorites, and booking history. Fully multilingual-ready.
 * Author: Softivus
 * Author URI: https://softivus.com
 * Version: 1.0.0
 * Text Domain: realestate-booking-suite
 * Requires at least: 5.2
 * Requires PHP: 7.1
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RESBS_PATH', plugin_dir_path(__FILE__));
define('RESBS_URL', plugin_dir_url(__FILE__));

// Enqueue assets
function resbs_enqueue_assets() {
    // Enqueue CSS
    wp_enqueue_style(
        'resbs-style',
        RESBS_URL . 'assets/css/style.css',
        array(),
        '1.0.0'
    );
    
        // Enqueue Contact Widget CSS
        wp_enqueue_style(
            'resbs-contact-widget',
            RESBS_URL . 'assets/css/contact-widget.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue Layout CSS
        wp_enqueue_style(
            'resbs-layouts',
            RESBS_URL . 'assets/css/layouts.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue Single Property Responsive CSS
        wp_enqueue_style(
            'resbs-single-property-responsive',
            RESBS_URL . 'assets/css/single-property-responsive.css',
            array(),
            '1.0.0'
        );
        
        
        // Enqueue Shortcodes CSS
        wp_enqueue_style(
            'resbs-shortcodes',
            RESBS_URL . 'assets/css/shortcodes.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue Modern Dashboard CSS (Admin Only)
        if (is_admin()) {
            wp_enqueue_style(
                'resbs-modern-dashboard',
                RESBS_URL . 'assets/css/modern-dashboard.css',
                array(),
                '1.0.0'
            );
        }
        
        // Enqueue Modern Dashboard CSS (Admin Only)
        if (is_admin()) {
            wp_enqueue_style(
                'resbs-archive',
                RESBS_URL . 'assets/css/rbs-archive.css',
                array(),
                '1.0.0'
            );
        }
    
    // DISABLED: Main JS to prevent conflicts with tabs
    // wp_enqueue_script(
    //     'resbs-main',
    //     RESBS_URL . 'assets/js/main.js',
    //     array('jquery'),
    //     '1.0.0',
    //     true
    // );
    
        // DISABLED: Layout JS to prevent conflicts with tabs
        // wp_enqueue_script(
        //     'resbs-layouts',
        //     RESBS_URL . 'assets/js/layouts.js',
        //     array('jquery'),
        //     '1.0.0',
        //     true
        // );
        
        // DISABLED: Shortcodes JS to prevent conflicts with tabs
        // wp_enqueue_script(
        //     'resbs-shortcodes',
        //     RESBS_URL . 'assets/js/shortcodes.js',
        //     array('jquery'),
        //     '1.0.0',
        //     true
        // );
}
add_action('wp_enqueue_scripts', 'resbs_enqueue_assets');
add_action('admin_enqueue_scripts', 'resbs_enqueue_assets');

// Load main functionality
require_once RESBS_PATH . 'includes/functions.php';

// Load contact messages functionality
require_once RESBS_PATH . 'includes/class-resbs-contact-messages.php';

// Load admin contact messages functionality
require_once RESBS_PATH . 'includes/class-resbs-admin-contact-messages.php';

// Load email handler functionality
require_once RESBS_PATH . 'includes/class-resbs-email-handler.php';

// Load booking manager functionality (moved to functions.php to avoid double loading)
// require_once RESBS_PATH . 'includes/class-resbs-booking-manager.php';

// DISABLED: Old settings class to prevent conflicts
// require_once RESBS_PATH . 'includes/class-resbs-settings.php';

// Load enhanced settings functionality (NEW ESTATIK-STYLE)
require_once RESBS_PATH . 'includes/class-resbs-enhanced-settings.php';
new RESBS_Enhanced_Settings();

// Load simple archive handler
require_once RESBS_PATH . 'includes/class-resbs-simple-archive.php';


