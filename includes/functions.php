<?php
/**
 * Main Functions File
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load Custom Post Type class
require_once RESBS_PATH . 'includes/class-resbs-cpt.php';
new RESBS_CPT();

// Load Professional Property Metabox class
require_once RESBS_PATH . 'includes/class-resbs-property-metabox.php';
new RESBS_Property_Metabox();

// Load Property Metabox AJAX handlers
require_once RESBS_PATH . 'includes/class-resbs-metabox-ajax.php';
new RESBS_Metabox_AJAX();

// Load Settings class
require_once RESBS_PATH . 'includes/class-resbs-settings.php';
new RESBS_Settings();

// Load Admin Dashboard class
require_once RESBS_PATH . 'includes/class-resbs-admin-dashboard.php';
new RESBS_Admin_Dashboard();

// Load Dashboard AJAX handlers
require_once RESBS_PATH . 'includes/class-resbs-dashboard-ajax.php';
new RESBS_Dashboard_AJAX();

// Load Frontend class
require_once RESBS_PATH . 'includes/class-resbs-frontend.php';
new RESBS_Frontend();

// Load Search class
require_once RESBS_PATH . 'includes/class-resbs-search.php';
new RESBS_Search();

// Load Property Grid template
require_once RESBS_PATH . 'includes/templates/property-grid.php';

// Load WooCommerce Integration
require_once RESBS_PATH . 'includes/class-resbs-woocommerce.php';

// Initialize WooCommerce Integration
new RESBS_WooCommerce();

// Load Elementor Integration (only if Elementor is active)
if (did_action('elementor/loaded') && class_exists('\Elementor\Widget_Base')) {
    require_once RESBS_PATH . 'includes/class-resbs-elementor.php';
    new RESBS_Elementor_Widgets();
}

// Load WordPress Widgets
require_once RESBS_PATH . 'includes/class-resbs-widgets.php';
// Widgets are registered via resbs_register_widgets() function

// Load Badge Manager
require_once RESBS_PATH . 'includes/class-resbs-badges.php';
new RESBS_Badge_Manager();

// Load Badge Templates
require_once RESBS_PATH . 'includes/templates/property-badges.php';

// Load Maps Manager
require_once RESBS_PATH . 'includes/class-resbs-maps.php';
new RESBS_Maps_Manager();

// Load Favorites Manager
require_once RESBS_PATH . 'includes/class-resbs-favorites.php';
new RESBS_Favorites_Manager();

// Load Contact Settings
require_once RESBS_PATH . 'includes/class-resbs-contact-settings.php';
new RESBS_Contact_Settings();

// Load Contact Widget
require_once RESBS_PATH . 'includes/class-resbs-contact-widget.php';
// Contact widget is registered via add_action('widgets_init') in the file

// Load Infinite Scroll Manager
require_once RESBS_PATH . 'includes/class-resbs-infinite-scroll.php';
new RESBS_Infinite_Scroll_Manager();

// Load Security Helper
require_once RESBS_PATH . 'includes/class-resbs-security.php';
// Security class has static methods, no instantiation needed

// Load Email Manager
require_once RESBS_PATH . 'includes/class-resbs-email-manager.php';
new RESBS_Email_Manager();

// Load Search Alerts Manager
require_once RESBS_PATH . 'includes/class-resbs-search-alerts.php';
new RESBS_Search_Alerts_Manager();

// Load Booking Manager
require_once RESBS_PATH . 'includes/class-resbs-booking-manager.php';
new RESBS_Booking_Manager();

// Load Quick View Manager
require_once RESBS_PATH . 'includes/class-resbs-quickview.php';
new RESBS_QuickView_Manager();

// Load Shortcodes Manager
require_once RESBS_PATH . 'includes/class-resbs-shortcodes.php';
new RESBS_Shortcodes();

// Load Shortcode AJAX Handlers
require_once RESBS_PATH . 'includes/class-resbs-shortcode-ajax.php';
new RESBS_Shortcode_AJAX();