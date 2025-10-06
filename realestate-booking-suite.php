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
    
    // Enqueue JS
    wp_enqueue_script(
        'resbs-main',
        RESBS_URL . 'assets/js/main.js',
        array('jquery'),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'resbs_enqueue_assets');
add_action('admin_enqueue_scripts', 'resbs_enqueue_assets');

// Load main functionality
require_once RESBS_PATH . 'includes/functions.php';
