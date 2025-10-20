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
    

        
        // Enqueue Enhanced Single Property JS
        wp_enqueue_script(
            'resbs-single-property-enhanced',
            RESBS_URL . 'assets/js/single-property-enhanced.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script for single property template
        if (is_singular('property') || (isset($_GET['property_id']) && $_GET['property_id'])) {
            // Get property data for localization
            $property_id = get_the_ID();
            if (isset($_GET['property_id'])) {
                $property_id = intval($_GET['property_id']);
            }
            
            // Get property data
            $gallery_images = get_post_meta($property_id, 'gallery_images', true);
            $property_title = get_the_title($property_id);
            $full_address = get_post_meta($property_id, 'full_address', true);
            $video_url = get_post_meta($property_id, 'video_url', true);
            $virtual_tour = get_post_meta($property_id, 'virtual_tour', true);
            $floor_plans = get_post_meta($property_id, 'floor_plans', true);
            $contact_success_message = get_option('resbs_contact_success_message', 'Thank you! Your message has been sent to the agent.');
            $mortgage_default_down_payment = get_option('resbs_mortgage_default_down_payment', 20);
            $mortgage_default_interest_rate = get_option('resbs_mortgage_default_interest_rate', 6.5);
            $mortgage_default_loan_term = get_option('resbs_mortgage_default_loan_term', 30);
            
            // Prepare data for JavaScript
            $script_data = array(
                'galleryImages' => is_array($gallery_images) ? $gallery_images : array(),
                'propertyTitle' => $property_title,
                'propertyAddress' => $full_address,
                'videoUrl' => $video_url,
                'virtualTour' => $virtual_tour,
                'floorPlans' => $floor_plans,
                'contactSuccessMessage' => $contact_success_message,
                'mortgageDefaultDownPayment' => $mortgage_default_down_payment,
                'mortgageDefaultInterestRate' => $mortgage_default_interest_rate,
                'mortgageDefaultLoanTerm' => $mortgage_default_loan_term,
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'propertyId' => $property_id,
                'nonce' => wp_create_nonce('contact_message_nonce')
            );
            
            wp_localize_script('resbs-single-property-enhanced', 'resbsPropertyData', $script_data);
        }
}
add_action('wp_enqueue_scripts', 'resbs_enqueue_assets');
add_action('admin_enqueue_scripts', 'resbs_enqueue_assets');

// Load main functionality
require_once RESBS_PATH . 'includes/functions.php';

// Load contact messages functionality
require_once RESBS_PATH . 'includes/class-resbs-contact-messages.php';

// Load admin contact messages functionality
require_once RESBS_PATH . 'includes/class-resbs-admin-contact-messages.php';

