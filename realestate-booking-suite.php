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

// Force flush rewrite rules on activation
function resbs_flush_rewrite_rules() {
    flush_rewrite_rules();
}

/**
 * Create wishlist page on plugin activation
 * Uses slug "saved-properties" to avoid conflicts with other plugins
 */
function resbs_create_wishlist_page() {
    // Check if page already exists
    $page_slug = 'saved-properties';
    $existing_page = get_page_by_path($page_slug);
    
    // Also check if we stored the page ID in options
    $stored_page_id = get_option('resbs_wishlist_page_id');
    
    if ($stored_page_id) {
        $existing_page = get_post($stored_page_id);
        if ($existing_page && $existing_page->post_status !== 'trash') {
            // Page exists and is not trashed, don't create again
            return $stored_page_id;
        }
    }
    
    // If page exists but not in our option, use it
    if ($existing_page && $existing_page->post_status !== 'trash') {
        update_option('resbs_wishlist_page_id', $existing_page->ID);
        return $existing_page->ID;
    }
    
    // Create the page
    $page_data = array(
        'post_title'    => __('My Saved Properties', 'realestate-booking-suite'),
        'post_content'  => '[resbs_favorites]',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => $page_slug,
        'post_author'   => 1,
        'comment_status' => 'closed',
        'ping_status'   => 'closed'
    );
    
    $page_id = wp_insert_post($page_data);
    
    if ($page_id && !is_wp_error($page_id)) {
        // Store the page ID in options
        update_option('resbs_wishlist_page_id', $page_id);
        return $page_id;
    }
    
    return false;
}

/**
 * Create profile page on plugin activation
 * Uses slug "profile" to avoid conflicts with other plugins
 */
function resbs_create_profile_page() {
    // Check if page already exists
    $page_slug = 'profile';
    $existing_page = get_page_by_path($page_slug);
    
    // Also check if we stored the page ID in options
    $stored_page_id = get_option('resbs_profile_page_id');
    
    if ($stored_page_id) {
        $existing_page = get_post($stored_page_id);
        if ($existing_page && $existing_page->post_status !== 'trash') {
            // Page exists and is not trashed, don't create again
            return $stored_page_id;
        }
    }
    
    // If page exists but not in our option, use it
    if ($existing_page && $existing_page->post_status !== 'trash') {
        update_option('resbs_profile_page_id', $existing_page->ID);
        return $existing_page->ID;
    }
    
    // Get title and subtitle from settings or use defaults
    $page_title = get_option('resbs_profile_page_title', 'User Profile');
    
    // Create the page
    $page_data = array(
        'post_title'    => $page_title,
        'post_content'  => '[resbs_dashboard show_profile="yes"]',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => $page_slug,
        'post_author'   => 1,
        'comment_status' => 'closed',
        'ping_status'   => 'closed'
    );
    
    $page_id = wp_insert_post($page_data);
    
    if ($page_id && !is_wp_error($page_id)) {
        // Store the page ID in options
        update_option('resbs_profile_page_id', $page_id);
        return $page_id;
    }
    
    return false;
}

/**
 * Create submit property page on plugin activation
 * Uses slug "submit-property" to avoid conflicts with other plugins
 */
function resbs_create_submit_property_page() {
    // Check if page already exists
    $page_slug = 'submit-property';
    $existing_page = get_page_by_path($page_slug);
    
    // Also check if we stored the page ID in options
    $stored_page_id = get_option('resbs_submit_property_page_id');
    
    if ($stored_page_id) {
        $existing_page = get_post($stored_page_id);
        if ($existing_page && $existing_page->post_status !== 'trash') {
            // Page exists and is not trashed, don't create again
            return $stored_page_id;
        }
    }
    
    // If page exists but not in our option, use it
    if ($existing_page && $existing_page->post_status !== 'trash') {
        update_option('resbs_submit_property_page_id', $existing_page->ID);
        return $existing_page->ID;
    }
    
    // Create the page
    $page_data = array(
        'post_title'    => __('Submit Property', 'realestate-booking-suite'),
        'post_content'  => '[resbs_submit_property]',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => $page_slug,
        'post_author'   => 1,
        'comment_status' => 'closed',
        'ping_status'   => 'closed'
    );
    
    $page_id = wp_insert_post($page_data);
    
    if ($page_id && !is_wp_error($page_id)) {
        // Store the page ID in options
        update_option('resbs_submit_property_page_id', $page_id);
        return $page_id;
    }
    
    return false;
}

/**
 * Plugin activation hook
 */
function resbs_plugin_activation() {
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Create wishlist page
    resbs_create_wishlist_page();
    
    // Create profile page
    resbs_create_profile_page();
    
    // Create submit property page
    resbs_create_submit_property_page();
}
register_activation_hook(__FILE__, 'resbs_plugin_activation');

// Manual flush rewrite rules function (for debugging)
function resbs_manual_flush_rewrite_rules() {
    if (isset($_GET['resbs_flush']) && $_GET['resbs_flush'] === '1') {
        flush_rewrite_rules();
        echo '<div style="background: green; color: white; padding: 10px; margin: 10px;">Rewrite rules flushed successfully!</div>';
    }
}
add_action('init', 'resbs_manual_flush_rewrite_rules');

// SINGLE PROPERTY TEMPLATE LOADER - HIGH PRIORITY
function resbs_single_property_template_loader($template) {
    if (is_singular('property')) {
        $single_template = RESBS_PATH . 'templates/single-property.php';
        if (file_exists($single_template)) {
            return $single_template;
        }
    }
    return $template;
}
add_filter('template_include', 'resbs_single_property_template_loader', 5);

// Enqueue assets
function resbs_enqueue_assets() {
    // Enqueue Font Awesome 6.4.0 CDN for icons - load early with high priority
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
        array(),
        '6.4.0',
        'all'
    );
    
    // Enqueue CSS
    wp_enqueue_style(
        'resbs-style',
        RESBS_URL . 'assets/css/style.css',
        array('font-awesome'),
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

        wp_enqueue_style(
            'resbs-archive',
            RESBS_URL . 'assets/css/rbs-archive.css',
            array(),
            '1.0.0'
        );
        
        // Re-enable Dynamic Archive JS but with modifications
        wp_enqueue_script(
            'resbs-dynamic-archive',
            RESBS_URL . 'assets/js/dynamic-archive.js',
            array('jquery'),
            '1.0.0',
            true
        );
        

        
    
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
add_action('wp_enqueue_scripts', 'resbs_enqueue_assets', 5);
add_action('admin_enqueue_scripts', 'resbs_enqueue_assets', 5);

// Fallback: Add Font Awesome directly to head if not already loaded
function resbs_add_fontawesome_fallback() {
    if (!wp_style_is('font-awesome', 'enqueued') && !wp_style_is('font-awesome', 'done')) {
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />' . "\n";
    }
}
add_action('wp_head', 'resbs_add_fontawesome_fallback', 1);

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

