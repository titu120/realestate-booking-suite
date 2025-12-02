<?php
/**
 * Template Assets Handler
 * Handles enqueuing of CSS/JS for templates (replaces inline styles/scripts)
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Template_Assets {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_single_property_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_simple_archive_assets'));
    }
    
    /**
     * Enqueue assets for single property template
     */
    public function enqueue_single_property_assets() {
        if (!is_singular('property')) {
            return;
        }
        
        // Enqueue Font Awesome (required for icons)
        wp_enqueue_style(
            'resbs-font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            array(),
            '6.4.0'
        );
        
        // Enqueue Google Fonts (Inter) - scoped to plugin only
        wp_enqueue_style(
            'resbs-google-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
            array(),
            '1.0.0'
        );
        
        // Enqueue Leaflet CSS (required for maps)
        wp_enqueue_style(
            'resbs-leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            array(),
            '1.9.4'
        );
        
        // Enqueue single property CSS
        // Load with HIGHEST priority - after all theme styles
        wp_enqueue_style(
            'resbs-single-property',
            RESBS_URL . 'assets/css/single-property.css',
            array('resbs-font-awesome', 'resbs-leaflet', 'resbs-google-fonts'), // Dependencies
            '2.0.0' // Version bump for maximum priority
        );
        
        // Enqueue amenities CSS
        wp_enqueue_style(
            'resbs-single-property-amenities',
            RESBS_URL . 'assets/css/single-property-amenities.css',
            array('resbs-single-property'),
            '1.0.0'
        );
        
        // Add dynamic inline styles for color customization
        $this->add_single_property_dynamic_styles();
        
        // Enqueue Leaflet JS (required for maps)
        wp_enqueue_script(
            'resbs-leaflet',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            array(),
            '1.9.4',
            true
        );
        
        // Enqueue single property JS
        wp_enqueue_script(
            'resbs-single-property-template',
            RESBS_URL . 'assets/js/single-property-template.js',
            array('jquery', 'resbs-leaflet'),
            '1.0.0',
            true
        );
        
        wp_enqueue_script(
            'resbs-single-property',
            RESBS_URL . 'assets/js/single-property.js',
            array('jquery', 'resbs-single-property-template'),
            '1.0.0',
            true
        );
        
        // Localize script with PHP data
        global $post;
        $all_images = array();
        if ($post) {
            // Use the same meta key and logic as the template
            $gallery_images = get_post_meta($post->ID, '_property_gallery', true);
            
            // Convert gallery attachment IDs to URLs (handle both IDs and URLs) - same logic as template
            if (!empty($gallery_images)) {
                if (is_array($gallery_images)) {
                    foreach ($gallery_images as $image_item) {
                        // Check if it's an attachment ID (numeric) or URL (string)
                        if (is_numeric($image_item)) {
                            $image_url = wp_get_attachment_image_url($image_item, 'full');
                            if ($image_url) {
                                $all_images[] = esc_url($image_url);
                            }
                        } elseif (is_string($image_item) && filter_var($image_item, FILTER_VALIDATE_URL)) {
                            // It's already a URL - escape it for security
                            $all_images[] = esc_url($image_item);
                        }
                    }
                } elseif (is_string($gallery_images)) {
                    // Handle comma-separated string
                    $gallery_array = explode(',', $gallery_images);
                    foreach ($gallery_array as $image_item) {
                        $image_item = trim($image_item);
                        if (is_numeric($image_item)) {
                            $image_url = wp_get_attachment_image_url($image_item, 'full');
                            if ($image_url) {
                                $all_images[] = esc_url($image_url);
                            }
                        } elseif (filter_var($image_item, FILTER_VALIDATE_URL)) {
                            // Escape URL for security
                            $all_images[] = esc_url($image_item);
                        }
                    }
                }
            }
        }
        
        // Don't add placeholder if no images - let JavaScript handle it
        // Only add placeholder if we really have no images at all
        
        wp_localize_script('resbs-single-property', 'resbs_ajax', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('resbs_contact_form_nonce')),
            'booking_nonce' => esc_js(wp_create_nonce('resbs_booking_form_nonce')),
            'property_id' => $post ? absint($post->ID) : 0,
            'gallery_images' => $all_images
        ));
        
        // Enqueue phone code selector JS if enabled
        if (resbs_show_phone_country_code()) {
            wp_enqueue_script(
                'resbs-phone-code-selector',
                RESBS_URL . 'assets/js/phone-code-selector.js',
                array(),
                '1.0.0',
                true
            );
            
            // Localize script for translations
            wp_localize_script('resbs-phone-code-selector', 'resbs_phone_code', array(
                'no_country_found' => esc_js(__('No country found', 'realestate-booking-suite'))
            ));
            
            // Enqueue geolocation script if enabled
            if (resbs_is_request_form_geolocation_enabled()) {
                wp_enqueue_script(
                    'resbs-geolocation-tel-code',
                    RESBS_URL . 'assets/js/geolocation-tel-code.js',
                    array(),
                    '1.0.0',
                    true
                );
            }
        }
    }
    
    /**
     * Add dynamic inline styles for single property
     */
    private function add_single_property_dynamic_styles() {
        $main_color = resbs_get_main_color();
        $secondary_color = resbs_get_secondary_color();
        
        // Sanitize colors before use
        $main_color = $this->sanitize_hex_color($main_color);
        $secondary_color = $this->sanitize_hex_color($secondary_color);
        
        // Helper function to darken color
        $main_color_dark = $this->darken_color($main_color, 10);
        $secondary_color_dark = $this->darken_color($secondary_color, 10);
        $main_color_light = $this->hex_to_rgba($main_color, 0.1);
        
        // Escape CSS values
        $main_color_escaped = esc_attr($main_color);
        $main_color_dark_escaped = esc_attr($main_color_dark);
        $main_color_light_escaped = esc_attr($main_color_light);
        $secondary_color_escaped = esc_attr($secondary_color);
        $secondary_color_dark_escaped = esc_attr($secondary_color_dark);
        
        $dynamic_css = "
        /* Scoped to .single-property to prevent conflicts with theme */
        .single-property {
            --rebs-primary-color: {$main_color_escaped} !important;
            --rebs-primary-dark: {$main_color_dark_escaped} !important;
            --rebs-primary-light: {$main_color_light_escaped} !important;
            --rebs-secondary-color: {$secondary_color_escaped} !important;
            --resbs-main-color: {$main_color_escaped} !important;
            --resbs-secondary-color: {$secondary_color_escaped} !important;
        }
        
        .single-property .btn-primary,
        .single-property .btn.btn-primary,
        .single-property button.btn-primary,
        .single-property .agent-action-primary,
        .single-property .agent-action.agent-action-primary,
        .single-property a.agent-action-primary {
            background-color: {$main_color_escaped} !important;
            border-color: {$main_color_escaped} !important;
            color: white !important;
        }
        
        .single-property .btn-primary:hover,
        .single-property .btn.btn-primary:hover,
        .single-property button.btn-primary:hover,
        .single-property .agent-action-primary:hover,
        .single-property .agent-action.agent-action-primary:hover,
        .single-property a.agent-action-primary:hover {
            background-color: {$main_color_dark_escaped} !important;
            border-color: {$main_color_dark_escaped} !important;
        }
        
        .single-property .agent-action-secondary,
        .single-property .agent-action.agent-action-secondary,
        .single-property button.agent-action-secondary {
            background-color: {$secondary_color_escaped} !important;
            border-color: {$secondary_color_escaped} !important;
            color: white !important;
        }
        
        .single-property .agent-action-secondary:hover,
        .single-property .agent-action.agent-action-secondary:hover,
        .single-property button.agent-action-secondary:hover {
            background-color: {$secondary_color_dark_escaped} !important;
            border-color: {$secondary_color_dark_escaped} !important;
        }
        
        .single-property a[style*=\"background: #007cba\"],
        .single-property a[style*=\"background:#007cba\"] {
            background: {$main_color_escaped} !important;
        }
        
        .single-property button[type=\"submit\"].bg-white.hover\\:bg-green-700,
        .single-property button[type=\"submit\"][class*=\"bg-white\"] {
            background-color: {$main_color_escaped} !important;
        }
        
        .single-property button[type=\"submit\"][class*=\"bg-white\"]:hover {
            background-color: {$main_color_dark_escaped} !important;
        }
        
        .single-property .focus\\:ring-green-500:focus,
        .single-property [class*=\"focus:ring-green\"]:focus {
            --tw-ring-color: {$main_color_escaped} !important;
            border-color: {$main_color_escaped} !important;
        }
        
        .single-property .focus\\:border-green-500:focus,
        .single-property [class*=\"focus:border-green\"]:focus {
            border-color: {$main_color_escaped} !important;
        }
        
        /* Phone field layout - ensure one line */
        .single-property .flex.gap-2.items-stretch {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 8px !important;
            align-items: stretch !important;
        }
        
        .single-property #bookingPhoneCode {
            width: 160px !important;
            max-width: 160px !important;
            min-width: 160px !important;
            flex: 0 0 160px !important;
            padding-left: 8px !important;
            padding-right: 32px !important;
        }
        
        .single-property #bookingPhone {
            flex: 1 1 auto !important;
            min-width: 0 !important;
            width: 100% !important;
        }
        
        /* Make dropdown easier to navigate with many options */
        .single-property #bookingPhoneCode {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .single-property #bookingPhoneCode option {
            padding: 6px 8px;
        }
        
        /* Searchable dropdown styles */
        .single-property .phone-code-wrapper {
            position: relative;
        }
        
        .single-property #bookingPhoneCodeSearch {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            z-index: 10;
            border-bottom: none;
            border-radius: 8px 8px 0 0;
        }
        
        .single-property #bookingPhoneCodeSearch:focus + #bookingPhoneCode,
        .single-property #bookingPhoneCode:focus {
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        ";
        
        wp_add_inline_style('resbs-single-property', $dynamic_css);
        
        // Add CRITICAL container width override with MAXIMUM priority via wp_head
        add_action('wp_head', function() {
            echo '<style id="resbs-single-property-container-override">
            /* MAXIMUM PRIORITY CONTAINER WIDTH - OVERRIDE ALL THEME STYLES */
            body.single.single-property .single-property.resbs-single-property-wrapper#resbs-single-property-page .main-single-container,
            body.singular.post-type-property .single-property.resbs-single-property-wrapper#resbs-single-property-page .main-single-container,
            body.page .single-property.resbs-single-property-wrapper#resbs-single-property-page .main-single-container,
            body .single-property.resbs-single-property-wrapper#resbs-single-property-page .main-single-container,
            .single-property.resbs-single-property-wrapper#resbs-single-property-page .main-single-container,
            #resbs-single-property-page .main-single-container,
            .single-property .main-single-container,
            body.single.single-property .single-property.resbs-single-property-wrapper#resbs-single-property-page .main-single-container.container,
            body.single.single-property .single-property.resbs-single-property-wrapper#resbs-single-property-page .main-single-container.main-content,
            .single-property.resbs-single-property-wrapper#resbs-single-property-page .main-single-container.container,
            .single-property.resbs-single-property-wrapper#resbs-single-property-page .main-single-container.main-content {
                width: 100% !important;
                max-width: 1536px !important;
                min-width: 0 !important;
                margin: 0 auto !important;
                margin-left: auto !important;
                margin-right: auto !important;
                padding: 4rem 1rem !important;
                padding-top: 4rem !important;
                padding-bottom: 4rem !important;
                padding-left: 1rem !important;
                padding-right: 1rem !important;
                box-sizing: border-box !important;
                position: relative !important;
                display: block !important;
            }
            body.single.single-property .single-property.resbs-single-property-wrapper#resbs-single-property-page {
                position: relative !important;
                isolation: isolate !important;
                contain: layout style paint !important;
            }
            </style>' . "\n";
        }, 99999);
    }
    
    /**
     * Enqueue assets for simple archive template
     */
    public function enqueue_simple_archive_assets() {
        if (!is_post_type_archive('property') && 
            !is_tax('property_category')) {
            return;
        }
        
        // Enqueue Font Awesome (required for icons)
        if (!wp_style_is('resbs-font-awesome', 'enqueued') && !wp_style_is('resbs-font-awesome', 'registered')) {
            wp_enqueue_style(
                'resbs-font-awesome',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                array(),
                '6.4.0'
            );
        }
        
        // Enqueue archive layout CSS
        wp_enqueue_style(
            'resbs-simple-archive-layout',
            RESBS_URL . 'assets/css/simple-archive-layout.css',
            array(),
            '1.0.0'
        );
        
        // Add dynamic inline styles for colors
        $this->add_simple_archive_dynamic_styles();
        
        // Get properties data from template via filter (check early for dependencies)
        $archive_data = apply_filters('resbs_archive_js_data', array());
        $use_openstreetmap = isset($archive_data['use_openstreetmap']) ? $archive_data['use_openstreetmap'] : true;
        
        // Enqueue Leaflet CSS and JS if using OpenStreetMap
        if ($use_openstreetmap) {
            // Enqueue Leaflet CSS
            if (!wp_style_is('resbs-leaflet', 'enqueued')) {
                wp_enqueue_style(
                    'resbs-leaflet',
                    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
                    array(),
                    '1.9.4'
                );
            }
            
            // Enqueue Leaflet JS
            if (!wp_script_is('resbs-leaflet', 'enqueued')) {
                wp_enqueue_script(
                    'resbs-leaflet',
                    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
                    array(),
                    '1.9.4',
                    true
                );
            }
        }
        
        // Enqueue archive JS - Make sure Leaflet loads first if using OpenStreetMap
        $dependencies = array('jquery');
        if ($use_openstreetmap) {
            $dependencies[] = 'resbs-leaflet';
        }
        
        wp_enqueue_script(
            'resbs-simple-archive',
            RESBS_URL . 'assets/js/simple-archive.js',
            $dependencies,
            '1.0.0',
            true
        );
        
        // Enqueue archive dropdown JS
        wp_enqueue_script(
            'resbs-archive-dropdown',
            RESBS_URL . 'assets/js/archive-dropdown.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Enqueue property card JS
        wp_enqueue_script(
            'resbs-property-card',
            RESBS_URL . 'assets/js/property-card.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Prepare default data - sanitize all values
        $properties_data = isset($archive_data['properties_data']) && is_array($archive_data['properties_data']) ? $archive_data['properties_data'] : array();
        $map_settings = isset($archive_data['map_settings']) && is_array($archive_data['map_settings']) ? $archive_data['map_settings'] : resbs_get_map_settings('archive');
        $map_center_lat = isset($archive_data['map_center_lat']) ? floatval($archive_data['map_center_lat']) : 23.8103;
        $map_center_lng = isset($archive_data['map_center_lng']) ? floatval($archive_data['map_center_lng']) : 90.4125;
        $map_zoom = isset($archive_data['map_zoom']) ? absint($archive_data['map_zoom']) : absint(resbs_get_default_zoom_level());
        
        // Localize script with all necessary data
        wp_localize_script('resbs-simple-archive', 'resbs_archive', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('resbs_favorites_nonce')),
            'favorites_nonce' => esc_js(wp_create_nonce('resbs_favorites_nonce')),
            'use_openstreetmap' => $use_openstreetmap,
            'properties_data' => $properties_data,
            'map_settings' => $map_settings,
            'map_center_lat' => $map_center_lat,
            'map_center_lng' => $map_center_lng,
            'map_zoom' => $map_zoom,
            'translations' => array(
                'error_occurred' => esc_js(__('An error occurred. Please try again.', 'realestate-booking-suite')),
                'unable_to_generate_token' => esc_js(__('Unable to generate security token. Please refresh the page.', 'realestate-booking-suite'))
            )
        ));
    }
    
    /**
     * Add dynamic inline styles for simple archive
     */
    private function add_simple_archive_dynamic_styles() {
        $main_color = resbs_get_main_color();
        $secondary_color = resbs_get_secondary_color();
        
        // Sanitize colors before use
        $main_color = $this->sanitize_hex_color($main_color);
        $secondary_color = $this->sanitize_hex_color($secondary_color);
        
        $main_color_dark = $this->darken_color($main_color, 10);
        $main_color_rgba = $this->hex_to_rgba($main_color, 0.3);
        
        // Escape CSS values
        $main_color_escaped = esc_attr($main_color);
        $main_color_dark_escaped = esc_attr($main_color_dark);
        $main_color_rgba_escaped = esc_attr($main_color_rgba);
        
        $dynamic_css = "
        .view-btn:hover {
            border-color: {$main_color_escaped};
            color: {$main_color_escaped};
            background: #f0fdf4;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px {$main_color_rgba_escaped};
        }
        
        .view-btn.active {
            background: {$main_color_escaped};
            border-color: {$main_color_escaped};
            color: white;
            box-shadow: 0 4px 12px {$main_color_rgba_escaped};
        }
        
        .view-btn.active:hover {
            background: {$main_color_dark_escaped};
            border-color: {$main_color_dark_escaped};
            color: white;
        }
        
        .sort-select:hover,
        .sort-select:focus {
            border-color: {$main_color_escaped};
            z-index: 102;
        }
        
        .layout-btn:hover,
        .filter-toggle:hover {
            border-color: {$main_color_escaped};
            color: {$main_color_escaped};
            background: #f0fdf4;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px {$main_color_rgba_escaped};
        }
        
        .layout-btn.active,
        .filter-toggle.active {
            background: {$main_color_escaped};
            border-color: {$main_color_escaped};
            color: white;
            box-shadow: 0 4px 12px {$main_color_rgba_escaped};
        }
        
        .layout-btn.active:hover,
        .filter-toggle.active:hover {
            background: {$main_color_dark_escaped};
            border-color: {$main_color_dark_escaped};
            color: white;
        }
        
        .apply-filter-btn {
            background-color: {$main_color_escaped} !important;
            color: white !important;
            font-weight: 600;
        }
        
        .apply-filter-btn:hover {
            background-color: {$main_color_dark_escaped};
            color: white;
        }
        
        .view-details-btn {
            background: {$main_color_escaped};
            color: white;
        }
        
        .view-details-btn:hover {
            background: {$main_color_dark_escaped};
        }
        
        .search-btn {
            background: {$main_color_escaped};
            color: white;
        }
        
        .search-btn:hover {
            background: {$main_color_dark_escaped};
            transform: translateY(-1px);
            box-shadow: 0 4px 12px {$main_color_rgba_escaped};
        }
        ";
        
        wp_add_inline_style('resbs-simple-archive-layout', $dynamic_css);
    }
    
    /**
     * Helper: Sanitize hex color
     * 
     * @param string $color Hex color code (e.g., '#0073aa' or '0073aa')
     * @return string Sanitized hex color code with # prefix
     */
    private function sanitize_hex_color($color) {
        // Remove # if present
        $color = ltrim($color, '#');
        
        // Validate and sanitize - only allow hex characters
        if (!preg_match('/^[a-fA-F0-9]{6}$/', $color)) {
            // Fallback to safe default color if invalid
            $color = '0073aa';
        }
        
        return '#' . strtolower($color);
    }
    
    /**
     * Helper: Darken color
     * 
     * @param string $color Hex color code (e.g., '#0073aa' or '0073aa')
     * @param int $percent Percentage to darken (0-100)
     * @return string Hex color code with # prefix
     */
    private function darken_color($color, $percent = 10) {
        // Validate and sanitize color input
        $color = ltrim($color, '#');
        // Ensure color is exactly 6 hex characters
        if (!preg_match('/^[a-fA-F0-9]{6}$/', $color)) {
            // Fallback to safe default color if invalid
            $color = '0073aa';
        }
        
        $r = hexdec(substr($color, 0, 2));
        $g = hexdec(substr($color, 2, 2));
        $b = hexdec(substr($color, 4, 2));
        $r = max(0, min(255, $r - ($r * $percent / 100)));
        $g = max(0, min(255, $g - ($g * $percent / 100)));
        $b = max(0, min(255, $b - ($b * $percent / 100)));
        return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
    
    /**
     * Helper: Convert hex to rgba
     * 
     * @param string $hex Hex color code (e.g., '#0073aa' or '0073aa')
     * @param float $alpha Alpha value (0.0-1.0)
     * @return string RGBA color string
     */
    private function hex_to_rgba($hex, $alpha = 0.3) {
        // Validate and sanitize color input
        $hex = ltrim($hex, '#');
        // Ensure color is exactly 6 hex characters
        if (!preg_match('/^[a-fA-F0-9]{6}$/', $hex)) {
            // Fallback to safe default color if invalid
            $hex = '0073aa';
        }
        
        // Validate and sanitize alpha value
        $alpha = max(0.0, min(1.0, floatval($alpha)));
        
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "rgba($r, $g, $b, $alpha)";
    }
}

