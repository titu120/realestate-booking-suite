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

// DISABLED: Old settings class - replaced with enhanced settings
// require_once RESBS_PATH . 'includes/class-resbs-settings.php';
// new RESBS_Settings();

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

// Load Elementor Integration
// File is always loaded; widgets will be initialized via elementor/loaded hook inside the file
require_once RESBS_PATH . 'includes/class-resbs-elementor.php';

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
$GLOBALS['resbs_favorites_manager'] = new RESBS_Favorites_Manager();

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

// Load Dynamic Archive AJAX Handlers
require_once RESBS_PATH . 'includes/class-resbs-dynamic-archive-ajax.php';
new RESBS_Dynamic_Archive_AJAX();

/**
 * Helper Functions for General Settings
 * These functions make it easy to retrieve General settings throughout the plugin
 * General settings apply globally to ALL pages: archive pages, single pages, widgets, etc.
 */

/**
 * Get language setting
 * @return string Language code (en, es, fr, de)
 */
function resbs_get_language() {
    return get_option('resbs_language', 'en');
}

/**
 * Get area unit setting
 * @return string Unit (sqft or sqm)
 */
function resbs_get_area_unit() {
    return get_option('resbs_area_unit', 'sqft');
}

/**
 * Get lot size unit setting
 * @return string Unit (sqft or sqm)
 */
function resbs_get_lot_size_unit() {
    return get_option('resbs_lot_size_unit', 'sqft');
}

/**
 * Get date format setting
 * @return string Date format (e.g., 'm/d/Y')
 */
function resbs_get_date_format() {
    return get_option('resbs_date_format', 'm/d/Y');
}

/**
 * Get time format setting
 * @return string Time format (12h or 24h)
 */
function resbs_get_time_format() {
    return get_option('resbs_time_format', '12h');
}

/**
 * Get main color setting
 * @return string Hex color code (e.g., '#0073aa')
 */
function resbs_get_main_color() {
    return get_option('resbs_main_color', '#0073aa');
}

/**
 * Get secondary color setting
 * @return string Hex color code (e.g., '#28a745')
 */
function resbs_get_secondary_color() {
    return get_option('resbs_secondary_color', '#28a745');
}

/**
 * Convert area between sqft and sqm
 * @param float|int $area Area value
 * @param string $from_unit Source unit (sqft or sqm)
 * @param string $to_unit Target unit (sqft or sqm)
 * @return float Converted area value
 */
function resbs_convert_area($area, $from_unit, $to_unit) {
    if ($from_unit === $to_unit || empty($area)) {
        return floatval($area);
    }
    
    // Convert to sqft first (base unit)
    if ($from_unit === 'sqm') {
        $area_sqft = floatval($area) * 10.764; // 1 sqm = 10.764 sqft
    } else {
        $area_sqft = floatval($area);
    }
    
    // Convert to target unit
    if ($to_unit === 'sqm') {
        return $area_sqft / 10.764; // Convert back to sqm
    } else {
        return $area_sqft;
    }
}

/**
 * Get area value from property (returns raw value, does NOT convert)
 * @param int $property_id Property ID
 * @param string $meta_key Meta key to retrieve (default: '_resbs_area')
 * @return float|string Area value (assumed to be stored in sqft), or empty string if not found
 */
function resbs_get_property_area($property_id, $meta_key = '_resbs_area') {
    // Try multiple possible meta keys
    $area = get_post_meta($property_id, $meta_key, true);
    if (empty($area)) {
        $area = get_post_meta($property_id, '_property_area_sqft', true);
    }
    if (empty($area)) {
        $area = get_post_meta($property_id, '_resbs_area_sqft', true);
    }
    if (empty($area)) {
        $area = get_post_meta($property_id, '_resbs_area', true);
    }
    
    if (empty($area)) {
        return '';
    }
    
    // Return raw value (assumed to be stored in sqft)
    return floatval($area);
}

/**
 * Format area value with unit
 * @param float|int $area Area value (assumed to be in sqft if not specified)
 * @param string $unit Optional unit override (sqft or sqm)
 * @param string $stored_unit Optional stored unit (if area is already in a specific unit)
 * @return string Formatted area with unit
 */
function resbs_format_area($area, $unit = null, $stored_unit = 'sqft') {
    if (empty($area)) {
        return '';
    }
    
    if ($unit === null) {
        $unit = resbs_get_area_unit();
    }
    
    // Convert if needed
    $converted_area = resbs_convert_area($area, $stored_unit, $unit);
    $unit_label = ($unit === 'sqm') ? 'sq m' : 'sq ft';
    
    return number_format($converted_area, 0) . ' ' . $unit_label;
}

/**
 * Format lot size value with unit
 * @param float|int $lot_size Lot size value (assumed to be in sqft if not specified)
 * @param string $unit Optional unit override (sqft or sqm)
 * @param string $stored_unit Optional stored unit (if lot size is already in a specific unit)
 * @return string Formatted lot size with unit
 */
function resbs_format_lot_size($lot_size, $unit = null, $stored_unit = 'sqft') {
    if (empty($lot_size)) {
        return '';
    }
    
    if ($unit === null) {
        $unit = resbs_get_lot_size_unit();
    }
    
    // Convert if needed
    $converted_lot = resbs_convert_area($lot_size, $stored_unit, $unit);
    $unit_label = ($unit === 'sqm') ? 'sq m' : 'sq ft';
    
    return number_format($converted_lot, 0) . ' ' . $unit_label;
}

/**
 * Format date according to plugin settings
 * @param string|int $date Date string or timestamp
 * @param string $format Optional format override
 * @return string Formatted date
 */
function resbs_format_date($date, $format = null) {
    if ($format === null) {
        $format = resbs_get_date_format();
    }
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Format time according to plugin settings
 * @param string|int $time Time string or timestamp
 * @param string $format Optional format override
 * @return string Formatted time
 */
function resbs_format_time($time, $format = null) {
    if ($format === null) {
        $time_format = resbs_get_time_format();
        $format = ($time_format === '24h') ? 'H:i' : 'g:i A';
    }
    $timestamp = is_numeric($time) ? $time : strtotime($time);
    return date($format, $timestamp);
}


/**
 * Helper Functions for Map Settings
 * These functions make it easy to retrieve Map settings throughout the plugin
 * Map settings apply dynamically to ALL maps: archive pages, single pages, widgets, etc.
 */

/**
 * Get default zoom level for maps
 * @return int Zoom level (0-20)
 */
function resbs_get_default_zoom_level() {
    return intval(get_option('resbs_default_zoom_level', 12));
}

/**
 * Get zoom level for single property page maps
 * @return int Zoom level (0-20)
 */
function resbs_get_single_property_zoom_level() {
    return intval(get_option('resbs_single_property_zoom_level', 16));
}

/**
 * Check if markers cluster is enabled
 * @return bool True if enabled, false otherwise
 */
function resbs_is_markers_cluster_enabled() {
    return (bool) get_option('resbs_enable_markers_cluster', false);
}

/**
 * Get cluster icon type
 * @return string Icon type (circle, bubble, outline)
 */
function resbs_get_cluster_icon() {
    return get_option('resbs_cluster_icon', 'circle');
}

/**
 * Get cluster icon color
 * @return string Hex color code (e.g., '#333333')
 */
function resbs_get_cluster_icon_color() {
    return get_option('resbs_cluster_icon_color', '#333333');
}

/**
 * Get map marker type
 * @return string Marker type (icon or price)
 */
function resbs_get_map_marker_type() {
    return get_option('resbs_map_marker_type', 'icon');
}

/**
 * Check if single map marker is enabled
 * @return bool True if enabled, false otherwise
 */
function resbs_is_single_map_marker_enabled() {
    return (bool) get_option('resbs_use_single_map_marker', false);
}

/**
 * Get single marker icon type
 * @return string Icon type (pin, outline, person)
 */
function resbs_get_single_marker_icon() {
    return get_option('resbs_single_marker_icon', 'pin');
}

/**
 * Get single marker color
 * @return string Hex color code (e.g., '#333333')
 */
function resbs_get_single_marker_color() {
    return get_option('resbs_single_marker_color', '#333333');
}

/**
 * Get map settings as array for JavaScript
 * @param string $context Context: 'archive' or 'single'
 * @return array Map settings array
 */
function resbs_get_map_settings($context = 'archive') {
    $settings = array(
        'defaultZoom' => resbs_get_default_zoom_level(),
        'singlePropertyZoom' => resbs_get_single_property_zoom_level(),
        'enableCluster' => resbs_is_markers_cluster_enabled(),
        'clusterIcon' => resbs_get_cluster_icon(),
        'clusterIconColor' => resbs_get_cluster_icon_color(),
        'markerType' => resbs_get_map_marker_type(),
        'useSingleMarker' => resbs_is_single_map_marker_enabled(),
        'singleMarkerIcon' => resbs_get_single_marker_icon(),
        'singleMarkerColor' => resbs_get_single_marker_color(),
    );
    
    // Set zoom based on context
    if ($context === 'single') {
        $settings['zoom'] = resbs_get_single_property_zoom_level();
    } else {
        $settings['zoom'] = resbs_get_default_zoom_level();
    }
    
    return $settings;
}

/**
 * Helper Functions for Listings Settings
 * These functions make it easy to retrieve Listings settings throughout the plugin
 * Listings settings apply dynamically to archive pages, single pages, widgets, etc.
 */

/**
 * Get properties per page
 * @return int Number of properties per page
 */
function resbs_get_properties_per_page() {
    return intval(get_option('resbs_properties_per_page', 40));
}

/**
 * Get default layout for listings (archive) pages
 * @return string Layout type (grid, large-grid, list)
 */
function resbs_get_default_layout_listings() {
    return get_option('resbs_default_layout_listings', 'grid');
}

/**
 * Get default layout for single property pages
 * @return string Layout type (slider, tiled, left-slider)
 */
function resbs_get_default_layout_single() {
    return get_option('resbs_default_layout_single', 'slider');
}

/**
 * Check if price should be shown
 * @return bool True if price should be shown
 */
function resbs_should_show_price() {
    return (bool) get_option('resbs_show_price', true);
}

/**
 * Check if listing address should be shown
 * @return bool True if address should be shown
 */
function resbs_should_show_listing_address() {
    return (bool) get_option('resbs_show_listing_address', true);
}

/**
 * Get what to show on listing preview block
 * @return string Preview type (address or title)
 */
function resbs_get_listing_preview_block() {
    return get_option('resbs_listing_preview_block', 'title');
}

/**
 * Check if description should be shown in listing box
 * @return bool True if description should be shown
 */
function resbs_should_show_description_listing_box() {
    return (bool) get_option('resbs_show_description_listing_box', false);
}

/**
 * Check if sorting is enabled
 * @return bool True if sorting is enabled
 */
function resbs_is_sorting_enabled() {
    return (bool) get_option('resbs_enable_sorting', true);
}

/**
 * Get sort options
 * @return array Array of enabled sort options
 */
function resbs_get_sort_options() {
    return (array) get_option('resbs_sort_options', array('newest', 'oldest', 'lowest_price', 'highest_price'));
}

/**
 * Get default sort option
 * @return string Default sort option
 */
function resbs_get_default_sort_option() {
    return get_option('resbs_default_sort_option', 'newest');
}

/**
 * Check if labels should be shown (Featured, New, etc.)
 * @return bool True if labels should be shown
 */
function resbs_should_show_labels() {
    return (bool) get_option('resbs_enable_labels', true);
}

/**
 * Check if date added should be shown
 * @return bool True if date should be shown
 */
function resbs_should_show_date_added() {
    return (bool) get_option('resbs_show_date_added', false);
}

/**
 * Check if map should be shown on single listing page
 * @return bool True if map should be shown
 */
function resbs_should_show_map_single_listing() {
    return (bool) get_option('resbs_enable_map_single_listing', true);
}

/**
 * Check if sharing is enabled
 * @return bool True if sharing is enabled
 */
function resbs_is_sharing_enabled() {
    return (bool) get_option('resbs_enable_sharing', true);
}

/**
 * Check if collapsed description is enabled
 * @return bool True if collapsed description is enabled
 */
function resbs_is_collapsed_description_enabled() {
    return (bool) get_option('resbs_enable_collapsed_description', false);
}

/**
 * Check if lightbox is disabled on single page
 * @return bool True if lightbox is disabled
 */
function resbs_is_lightbox_disabled_single_page() {
    return (bool) get_option('resbs_disable_lightbox_single_page', false);
}

/**
 * Check if request form geolocation is enabled
 * @return bool True if geolocation is enabled
 */
function resbs_is_request_form_geolocation_enabled() {
    return (bool) get_option('resbs_enable_request_form_geolocation', false);
}

/**
 * Check if phone country code dropdown should be shown
 * @return bool True if country code dropdown should be shown
 */
function resbs_show_phone_country_code() {
    return (bool) get_option('resbs_show_phone_country_code', true);
}

/**
 * Check if wishlist is enabled
 * @return bool True if wishlist is enabled
 */
function resbs_is_wishlist_enabled() {
    return (bool) get_option('resbs_enable_wishlist', true);
}

/**
 * Get wishlist page URL
 * @return string|false Wishlist page URL or false if page doesn't exist
 */
function resbs_get_wishlist_page_url() {
    $page_id = get_option('resbs_wishlist_page_id');
    
    if ($page_id) {
        $page_url = get_permalink($page_id);
        if ($page_url) {
            return $page_url;
        }
    }
    
    // Fallback: try to find page by slug
    $page = get_page_by_path('saved-properties');
    if ($page && $page->post_status === 'publish') {
        update_option('resbs_wishlist_page_id', $page->ID);
        return get_permalink($page->ID);
    }
    
    return false;
}

/**
 * Get wishlist page ID
 * @return int|false Wishlist page ID or false if page doesn't exist
 */
function resbs_get_wishlist_page_id() {
    $page_id = get_option('resbs_wishlist_page_id');
    
    if ($page_id && get_post($page_id)) {
        return $page_id;
    }
    
    // Fallback: try to find page by slug
    $page = get_page_by_path('saved-properties');
    if ($page && $page->post_status === 'publish') {
        update_option('resbs_wishlist_page_id', $page->ID);
        return $page->ID;
    }
    
    return false;
}

/**
 * Check if a property is in favorites
 * @param int $property_id Property ID
 * @return bool True if property is favorited
 */
function resbs_is_property_favorited($property_id) {
    if (!isset($GLOBALS['resbs_favorites_manager'])) {
        return false;
    }
    
    return $GLOBALS['resbs_favorites_manager']->is_favorite($property_id);
}

/**
 * Check if user profile is enabled
 * @return bool True if user profile is enabled
 */
function resbs_is_user_profile_enabled() {
    return get_option('resbs_enable_user_profile', 1) == 1;
}

/**
 * Get profile page title
 * @return string Profile page title
 */
function resbs_get_profile_page_title() {
    return get_option('resbs_profile_page_title', 'User Profile');
}

/**
 * Get profile page subtitle
 * @return string Profile page subtitle
 */
function resbs_get_profile_page_subtitle() {
    return get_option('resbs_profile_page_subtitle', 'Manage your account and preferences');
}

/**
 * Get profile page URL
 * @return string|false Profile page URL or false if page doesn't exist
 */
function resbs_get_profile_page_url() {
    $page_id = get_option('resbs_profile_page_id');
    
    if ($page_id && get_post($page_id)) {
        return get_permalink($page_id);
    }
    
    // Fallback: try to find page by slug
    $page = get_page_by_path('profile');
    if ($page && $page->post_status === 'publish') {
        update_option('resbs_profile_page_id', $page->ID);
        return get_permalink($page->ID);
    }
    
    return false;
}

/**
 * Get profile page ID
 * @return int|false Profile page ID or false if page doesn't exist
 */
function resbs_get_profile_page_id() {
    $page_id = get_option('resbs_profile_page_id');
    
    if ($page_id && get_post($page_id)) {
        return $page_id;
    }
    
    // Fallback: try to find page by slug
    $page = get_page_by_path('profile');
    if ($page && $page->post_status === 'publish') {
        update_option('resbs_profile_page_id', $page->ID);
        return $page->ID;
    }
    
    return false;
}

/**
 * Get submit property page URL
 * @return string|false Submit property page URL or false if page doesn't exist
 */
function resbs_get_submit_property_page_url() {
    $page_id = get_option('resbs_submit_property_page_id');
    
    if ($page_id && get_post($page_id)) {
        return get_permalink($page_id);
    }
    
    // Fallback: try to find page by slug
    $page = get_page_by_path('submit-property');
    if ($page && $page->post_status === 'publish') {
        update_option('resbs_submit_property_page_id', $page->ID);
        return get_permalink($page->ID);
    }
    
    return false;
}

/**
 * Get submit property page ID
 * @return int|false Submit property page ID or false if page doesn't exist
 */
function resbs_get_submit_property_page_id() {
    $page_id = get_option('resbs_submit_property_page_id');
    
    if ($page_id && get_post($page_id)) {
        return $page_id;
    }
    
    // Fallback: try to find page by slug
    $page = get_page_by_path('submit-property');
    if ($page && $page->post_status === 'publish') {
        update_option('resbs_submit_property_page_id', $page->ID);
        return $page->ID;
    }
    
    return false;
}
