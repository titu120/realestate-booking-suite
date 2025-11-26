<?php
/**
 * Google Maps Manager Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Maps_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        // add_action('admin_menu', array($this, 'add_admin_menu')); // Disabled - handled by main settings class
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_resbs_get_map_properties', array($this, 'ajax_get_map_properties'));
        add_action('wp_ajax_nopriv_resbs_get_map_properties', array($this, 'ajax_get_map_properties'));
        add_action('wp_ajax_resbs_search_map_area', array($this, 'ajax_search_map_area'));
        add_action('wp_ajax_nopriv_resbs_search_map_area', array($this, 'ajax_search_map_area'));
        
        // Shortcode
        add_shortcode('resbs_property_map', array($this, 'property_map_shortcode'));
        
        // Widget
        add_action('widgets_init', array($this, 'register_map_widget'));
    }

    /**
     * Initialize
     */
    public function init() {
        // Add map integration to existing widgets
        add_action('resbs_property_map_display', array($this, 'display_property_map'), 10, 2);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Map Settings', 'realestate-booking-suite'),
            esc_html__('Map Settings', 'realestate-booking-suite'),
            'manage_options',
            'resbs-map-settings',
            array($this, 'admin_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('resbs_map_settings', 'resbs_google_maps_api_key', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('resbs_map_settings', 'resbs_map_default_lat', array('sanitize_callback' => array($this, 'sanitize_latitude')));
        register_setting('resbs_map_settings', 'resbs_map_default_lng', array('sanitize_callback' => array($this, 'sanitize_longitude')));
        register_setting('resbs_map_settings', 'resbs_map_default_zoom', array('sanitize_callback' => array($this, 'sanitize_zoom')));
        register_setting('resbs_map_settings', 'resbs_map_style', array('sanitize_callback' => array($this, 'sanitize_map_style')));
        register_setting('resbs_map_settings', 'resbs_map_cluster_markers', array('sanitize_callback' => array($this, 'sanitize_boolean')));
        register_setting('resbs_map_settings', 'resbs_map_show_search', array('sanitize_callback' => array($this, 'sanitize_boolean')));
        register_setting('resbs_map_settings', 'resbs_map_show_filters', array('sanitize_callback' => array($this, 'sanitize_boolean')));
        register_setting('resbs_map_settings', 'resbs_map_marker_icon', array('sanitize_callback' => 'esc_url_raw'));
        register_setting('resbs_map_settings', 'resbs_map_info_window_style', array('sanitize_callback' => 'sanitize_text_field'));
    }

    /**
     * Sanitize latitude
     */
    private function sanitize_latitude($value) {
        $float_value = floatval($value);
        if ($float_value < -90 || $float_value > 90) {
            return 40.7128; // Default to NYC
        }
        return $float_value;
    }

    /**
     * Sanitize longitude
     */
    private function sanitize_longitude($value) {
        $float_value = floatval($value);
        if ($float_value < -180 || $float_value > 180) {
            return -74.0060; // Default to NYC
        }
        return $float_value;
    }

    /**
     * Sanitize zoom level
     */
    private function sanitize_zoom($value) {
        $int_value = intval($value);
        if ($int_value < 1 || $int_value > 20) {
            return 10; // Default zoom
        }
        return $int_value;
    }

    /**
     * Sanitize map style
     */
    private function sanitize_map_style($value) {
        $allowed_styles = array('default', 'satellite', 'hybrid', 'terrain');
        if (in_array($value, $allowed_styles, true)) {
            return $value;
        }
        return 'default';
    }

    /**
     * Sanitize boolean
     */
    private function sanitize_boolean($value) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        $api_key = get_option('resbs_google_maps_api_key');
        
        // Check if widget is active or shortcode is used
        $has_shortcode = false;
        $post = get_post();
        if ($post && !empty($post->post_content)) {
            $has_shortcode = has_shortcode($post->post_content, 'resbs_property_map');
        }
        
        if ($api_key && (is_active_widget(false, false, 'resbs_property_map_widget') || $has_shortcode)) {
            
            // Enqueue Google Maps API
            wp_enqueue_script(
                'resbs-google-maps-api',
                'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places,geometry',
                array(),
                null,
                true
            );

            // Enqueue map styles
            wp_enqueue_style(
                'resbs-maps',
                RESBS_URL . 'assets/css/maps.css',
                array(),
                '1.0.0'
            );

            // Enqueue map scripts
            wp_enqueue_script(
                'resbs-maps',
                RESBS_URL . 'assets/js/maps.js',
                array('jquery', 'resbs-google-maps-api'),
                '1.0.0',
                true
            );

            // Get currency information for JavaScript
            $currency_symbol = resbs_get_currency_symbol();
            $currency_code = 'USD'; // Default
            if (class_exists('WooCommerce')) {
                $currency_code = get_woocommerce_currency();
            }
            
            // Localize script
            wp_localize_script('resbs-maps', 'resbs_maps_ajax', array(
                'currency_symbol' => esc_js($currency_symbol),
                'currency_code' => esc_js($currency_code),
                'ajax_url' => esc_url(admin_url('admin-ajax.php')),
                'nonce' => esc_js(wp_create_nonce('resbs_maps_nonce')),
                'default_lat' => floatval(get_option('resbs_map_default_lat', '40.7128')),
                'default_lng' => floatval(get_option('resbs_map_default_lng', '-74.0060')),
                'default_zoom' => intval(get_option('resbs_map_default_zoom', '10')),
                'cluster_markers' => get_option('resbs_map_cluster_markers', true),
                'show_search' => get_option('resbs_map_show_search', true),
                'show_filters' => get_option('resbs_map_show_filters', true),
                'marker_icon' => esc_url(get_option('resbs_map_marker_icon', '')),
                'messages' => array(
                    'loading' => esc_js(esc_html__('Loading map...', 'realestate-booking-suite')),
                    'no_properties' => esc_js(esc_html__('No properties found in this area.', 'realestate-booking-suite')),
                    'search_placeholder' => esc_js(esc_html__('Search location...', 'realestate-booking-suite')),
                    'search_button' => esc_js(esc_html__('Search', 'realestate-booking-suite')),
                    'reset_button' => esc_js(esc_html__('Reset', 'realestate-booking-suite')),
                    'filter_button' => esc_js(esc_html__('Filter', 'realestate-booking-suite')),
                    'view_property' => esc_js(esc_html__('View Property', 'realestate-booking-suite')),
                    'quick_view' => esc_js(esc_html__('Quick View', 'realestate-booking-suite')),
                    'add_to_favorites' => esc_js(esc_html__('Add to Favorites', 'realestate-booking-suite')),
                    'remove_from_favorites' => esc_js(esc_html__('Remove from Favorites', 'realestate-booking-suite')),
                    'error' => esc_js(esc_html__('An error occurred. Please try again.', 'realestate-booking-suite'))
                )
            ));
        }
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook === 'property_page_resbs-map-settings') {
            wp_enqueue_style(
                'resbs-maps-admin',
                RESBS_URL . 'assets/css/maps-admin.css',
                array(),
                '1.0.0'
            );
        }
    }

    /**
     * Admin page
     */
    public function admin_page() {
        // Check user permissions
        RESBS_Security::check_capability('manage_options');
        
        if (isset($_POST['submit'])) {
            $this->save_map_settings();
        }
        
        $api_key = get_option('resbs_google_maps_api_key', '');
        $default_lat = get_option('resbs_map_default_lat', '40.7128');
        $default_lng = get_option('resbs_map_default_lng', '-74.0060');
        $default_zoom = get_option('resbs_map_default_zoom', '10');
        $map_style = get_option('resbs_map_style', 'default');
        $cluster_markers = get_option('resbs_map_cluster_markers', true);
        $show_search = get_option('resbs_map_show_search', true);
        $show_filters = get_option('resbs_map_show_filters', true);
        $marker_icon = get_option('resbs_map_marker_icon', '');
        $info_window_style = get_option('resbs_map_info_window_style', 'default');
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Google Maps Settings', 'realestate-booking-suite'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('resbs_map_settings_nonce', 'resbs_map_settings_nonce'); ?>
                
                <div class="resbs-map-settings">
                    <!-- API Key Section -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('Google Maps API Configuration', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="resbs_google_maps_api_key">
                                        <?php esc_html_e('Google Maps API Key', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <!-- CRITICAL FIX: Removed HTML5 required attribute - WordPress handles validation server-side -->
                                    <input type="text" 
                                           id="resbs_google_maps_api_key"
                                           name="resbs_google_maps_api_key"
                                           value="<?php echo esc_attr($api_key); ?>"
                                           class="regular-text">
                                    <p class="description">
                                        <?php esc_html_e('Get your API key from the Google Cloud Console. Enable Maps JavaScript API and Places API.', 'realestate-booking-suite'); ?>
                                        <a href="https://console.cloud.google.com/" target="_blank"><?php esc_html_e('Get API Key', 'realestate-booking-suite'); ?></a>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Default Map Settings -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('Default Map Settings', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="resbs_map_default_lat">
                                        <?php esc_html_e('Default Latitude', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="resbs_map_default_lat"
                                           name="resbs_map_default_lat"
                                           value="<?php echo esc_attr($default_lat); ?>"
                                           step="0.000001"
                                           class="small-text">
                                    <p class="description">
                                        <?php esc_html_e('Default latitude for map center.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_map_default_lng">
                                        <?php esc_html_e('Default Longitude', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="resbs_map_default_lng"
                                           name="resbs_map_default_lng"
                                           value="<?php echo esc_attr($default_lng); ?>"
                                           step="0.000001"
                                           class="small-text">
                                    <p class="description">
                                        <?php esc_html_e('Default longitude for map center.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_map_default_zoom">
                                        <?php esc_html_e('Default Zoom Level', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="resbs_map_default_zoom"
                                           name="resbs_map_default_zoom"
                                           value="<?php echo esc_attr($default_zoom); ?>"
                                           min="1" max="20"
                                           class="small-text">
                                    <p class="description">
                                        <?php esc_html_e('Default zoom level (1-20).', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Map Appearance -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('Map Appearance', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="resbs_map_style">
                                        <?php esc_html_e('Map Style', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="resbs_map_style" name="resbs_map_style">
                                        <option value="default" <?php selected($map_style, 'default'); ?>><?php esc_html_e('Default', 'realestate-booking-suite'); ?></option>
                                        <option value="satellite" <?php selected($map_style, 'satellite'); ?>><?php esc_html_e('Satellite', 'realestate-booking-suite'); ?></option>
                                        <option value="hybrid" <?php selected($map_style, 'hybrid'); ?>><?php esc_html_e('Hybrid', 'realestate-booking-suite'); ?></option>
                                        <option value="terrain" <?php selected($map_style, 'terrain'); ?>><?php esc_html_e('Terrain', 'realestate-booking-suite'); ?></option>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e('Choose the default map style.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_map_marker_icon">
                                        <?php esc_html_e('Custom Marker Icon URL', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="url" 
                                           id="resbs_map_marker_icon"
                                           name="resbs_map_marker_icon"
                                           value="<?php echo esc_url($marker_icon); ?>"
                                           class="regular-text">
                                    <p class="description">
                                        <?php esc_html_e('Optional custom marker icon URL. Leave empty for default markers.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Map Features -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('Map Features', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Enable Features', 'realestate-booking-suite'); ?>
                                </th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="checkbox" 
                                                   name="resbs_map_cluster_markers"
                                                   value="1" 
                                                   <?php checked($cluster_markers); ?>>
                                            <?php esc_html_e('Cluster Markers', 'realestate-booking-suite'); ?>
                                        </label>
                                        <p class="description">
                                            <?php esc_html_e('Group nearby markers together for better performance.', 'realestate-booking-suite'); ?>
                                        </p>
                                        
                                        <br>
                                        
                                        <label>
                                            <input type="checkbox" 
                                                   name="resbs_map_show_search"
                                                   value="1" 
                                                   <?php checked($show_search); ?>>
                                            <?php esc_html_e('Show Search Box', 'realestate-booking-suite'); ?>
                                        </label>
                                        <p class="description">
                                            <?php esc_html_e('Display location search box on the map.', 'realestate-booking-suite'); ?>
                                        </p>
                                        
                                        <br>
                                        
                                        <label>
                                            <input type="checkbox" 
                                                   name="resbs_map_show_filters"
                                                   value="1" 
                                                   <?php checked($show_filters); ?>>
                                            <?php esc_html_e('Show Filters', 'realestate-booking-suite'); ?>
                                        </label>
                                        <p class="description">
                                            <?php esc_html_e('Display property filters on the map.', 'realestate-booking-suite'); ?>
                                        </p>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php submit_button(esc_html__('Save Map Settings', 'realestate-booking-suite')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Save map settings
     */
    private function save_map_settings() {
        // Check nonce using security helper
        $nonce = isset($_POST['resbs_map_settings_nonce']) && is_string($_POST['resbs_map_settings_nonce']) ? $_POST['resbs_map_settings_nonce'] : '';
        RESBS_Security::verify_nonce($nonce, 'resbs_map_settings_nonce');

        // Check permissions using security helper
        RESBS_Security::check_capability('manage_options');

        // Save settings using security helper
        $settings = array(
            'resbs_google_maps_api_key' => RESBS_Security::sanitize_text($_POST['resbs_google_maps_api_key'] ?? ''),
            'resbs_map_default_lat' => RESBS_Security::sanitize_float($_POST['resbs_map_default_lat'] ?? 40.7128),
            'resbs_map_default_lng' => RESBS_Security::sanitize_float($_POST['resbs_map_default_lng'] ?? -74.0060),
            'resbs_map_default_zoom' => RESBS_Security::sanitize_int($_POST['resbs_map_default_zoom'] ?? 10),
            'resbs_map_style' => RESBS_Security::sanitize_text($_POST['resbs_map_style'] ?? 'default'),
            'resbs_map_cluster_markers' => RESBS_Security::sanitize_bool($_POST['resbs_map_cluster_markers'] ?? false),
            'resbs_map_show_search' => RESBS_Security::sanitize_bool($_POST['resbs_map_show_search'] ?? false),
            'resbs_map_show_filters' => RESBS_Security::sanitize_bool($_POST['resbs_map_show_filters'] ?? false),
            'resbs_map_marker_icon' => RESBS_Security::sanitize_url($_POST['resbs_map_marker_icon'] ?? ''),
            'resbs_map_info_window_style' => RESBS_Security::sanitize_text($_POST['resbs_map_info_window_style'] ?? 'default')
        );

        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        add_settings_error('resbs_map_settings', 'settings_updated', 
                          esc_html__('Map settings saved successfully.', 'realestate-booking-suite'), 'updated');
    }

    /**
     * AJAX handler for getting map properties
     */
    public function ajax_get_map_properties() {
        // Verify nonce using security helper (checks if nonce exists)
        $nonce = isset($_POST['nonce']) && is_string($_POST['nonce']) ? $_POST['nonce'] : '';
        RESBS_Security::verify_ajax_nonce($nonce, 'resbs_maps_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('get_map_properties', 30, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        // Get and sanitize parameters using security helper
        $bounds = array(
            'north' => RESBS_Security::sanitize_float($_POST['north'] ?? 0),
            'south' => RESBS_Security::sanitize_float($_POST['south'] ?? 0),
            'east' => RESBS_Security::sanitize_float($_POST['east'] ?? 0),
            'west' => RESBS_Security::sanitize_float($_POST['west'] ?? 0)
        );

        $filters = array(
            'property_type' => RESBS_Security::sanitize_text($_POST['property_type'] ?? ''),
            'property_status' => RESBS_Security::sanitize_text($_POST['property_status'] ?? ''),
            'price_min' => RESBS_Security::sanitize_int($_POST['price_min'] ?? 0),
            'price_max' => RESBS_Security::sanitize_int($_POST['price_max'] ?? 0),
            'bedrooms' => RESBS_Security::sanitize_int($_POST['bedrooms'] ?? 0),
            'bathrooms' => RESBS_Security::sanitize_int($_POST['bathrooms'] ?? 0)
        );

        // Build query
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_property_latitude',
                    'value' => array($bounds['south'], $bounds['north']),
                    'compare' => 'BETWEEN',
                    'type' => 'DECIMAL(10,6)'
                ),
                array(
                    'key' => '_property_longitude',
                    'value' => array($bounds['west'], $bounds['east']),
                    'compare' => 'BETWEEN',
                    'type' => 'DECIMAL(10,6)'
                )
            )
        );

        // Add filters
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $price_query = array();
            
            if (!empty($filters['price_min'])) {
                $price_query[] = array(
                    'key' => '_property_price',
                    'value' => $filters['price_min'],
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }
            
            if (!empty($filters['price_max'])) {
                $price_query[] = array(
                    'key' => '_property_price',
                    'value' => $filters['price_max'],
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                );
            }
            
            if (!empty($price_query)) {
                $query_args['meta_query'][] = $price_query;
            }
        }

        if (!empty($filters['bedrooms'])) {
            $query_args['meta_query'][] = array(
                'key' => '_property_bedrooms',
                'value' => $filters['bedrooms'],
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }

        if (!empty($filters['bathrooms'])) {
            $query_args['meta_query'][] = array(
                'key' => '_property_bathrooms',
                'value' => $filters['bathrooms'],
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }

        // Add taxonomy filters
        $tax_query = array();
        
        if (!empty($filters['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $filters['property_type']
            );
        }

        if (!empty($filters['property_status'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $filters['property_status']
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        $properties = new WP_Query($query_args);
        $map_properties = array();

        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $property_id = get_the_ID();
                
                $property_type_terms = wp_get_post_terms($property_id, 'property_type', array('fields' => 'names'));
                $property_status_terms = wp_get_post_terms($property_id, 'property_status', array('fields' => 'names'));
                
                // Ensure terms are arrays and escape them
                if (!is_array($property_type_terms)) {
                    $property_type_terms = array();
                }
                if (!is_array($property_status_terms)) {
                    $property_status_terms = array();
                }
                
                $map_properties[] = array(
                    'id' => intval($property_id),
                    'title' => esc_html(get_the_title()),
                    'permalink' => esc_url(get_permalink()),
                    'latitude' => floatval(get_post_meta($property_id, '_property_latitude', true)),
                    'longitude' => floatval(get_post_meta($property_id, '_property_longitude', true)),
                    'price' => esc_html(get_post_meta($property_id, '_property_price', true)),
                    'bedrooms' => intval(get_post_meta($property_id, '_property_bedrooms', true)),
                    'bathrooms' => intval(get_post_meta($property_id, '_property_bathrooms', true)),
                    'area' => esc_html(get_post_meta($property_id, '_property_size', true) ?: get_post_meta($property_id, '_property_area_sqft', true) ?: ''),
                    'featured_image' => esc_url(get_the_post_thumbnail_url($property_id, 'medium')),
                    'property_type' => array_map('esc_html', $property_type_terms),
                    'property_status' => array_map('esc_html', $property_status_terms),
                    'featured' => (bool) get_post_meta($property_id, '_property_featured', true),
                    'new' => (bool) get_post_meta($property_id, '_property_new', true),
                    'sold' => (bool) get_post_meta($property_id, '_property_sold', true)
                );
            }
            wp_reset_postdata();
        }

        wp_send_json_success(array(
            'properties' => $map_properties,
            'count' => count($map_properties)
        ));
    }

    /**
     * AJAX handler for map area search
     */
    public function ajax_search_map_area() {
        // Verify nonce using security helper (checks if nonce exists)
        $nonce = isset($_POST['nonce']) && is_string($_POST['nonce']) ? $_POST['nonce'] : '';
        RESBS_Security::verify_ajax_nonce($nonce, 'resbs_maps_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('search_map_area', 20, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        $search_query = RESBS_Security::sanitize_text($_POST['search_query'] ?? '');
        
        if (empty($search_query)) {
            wp_send_json_error(array(
                'message' => esc_html__('Search query is required.', 'realestate-booking-suite')
            ));
        }

        // Use Google Places API to get coordinates
        $api_key = get_option('resbs_google_maps_api_key');
        
        if (empty($api_key)) {
            wp_send_json_error(array(
                'message' => esc_html__('Google Maps API key is not configured.', 'realestate-booking-suite')
            ));
        }

        // Sanitize API key before using in URL
        $api_key_sanitized = sanitize_text_field($api_key);
        $url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($search_query) . '&key=' . urlencode($api_key_sanitized);
        
        // Use wp_remote_get directly - URL components are already sanitized and urlencoded
        $response = wp_remote_get($url, array('timeout' => 15, 'sslverify' => true));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to search location.', 'realestate-booking-suite')
            ));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // Validate JSON decode result
        if (!is_array($data)) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid response from geocoding service.', 'realestate-booking-suite')
            ));
        }

        // Check if status exists and is OK, and results exist
        if (isset($data['status']) && $data['status'] === 'OK' && !empty($data['results']) && is_array($data['results'])) {
            $result = $data['results'][0];
            
            // Validate result structure
            if (!isset($result['geometry']['location']) || !isset($result['formatted_address'])) {
                wp_send_json_error(array(
                    'message' => esc_html__('Invalid location data received.', 'realestate-booking-suite')
                ));
            }
            
            $location = $result['geometry']['location'];
            
            wp_send_json_success(array(
                'lat' => floatval($location['lat']),
                'lng' => floatval($location['lng']),
                'formatted_address' => esc_html($result['formatted_address'])
            ));
        } else {
            wp_send_json_error(array(
                'message' => esc_html__('Location not found.', 'realestate-booking-suite')
            ));
        }
    }

    /**
     * Property map shortcode
     */
    public function property_map_shortcode($atts) {
        $atts = shortcode_atts(array(
            'height' => '400px',
            'width' => '100%',
            'zoom' => '',
            'lat' => '',
            'lng' => '',
            'show_search' => '',
            'show_filters' => '',
            'cluster_markers' => ''
        ), $atts);

        $height = sanitize_text_field($atts['height']);
        $width = sanitize_text_field($atts['width']);
        $zoom = !empty($atts['zoom']) ? intval($atts['zoom']) : get_option('resbs_map_default_zoom', 10);
        $lat = !empty($atts['lat']) ? floatval($atts['lat']) : get_option('resbs_map_default_lat', 40.7128);
        $lng = !empty($atts['lng']) ? floatval($atts['lng']) : get_option('resbs_map_default_lng', -74.0060);
        $show_search = $atts['show_search'] !== 'false';
        $show_filters = $atts['show_filters'] !== 'false';
        $cluster_markers = $atts['cluster_markers'] !== 'false';

        $map_id = 'resbs-map-' . uniqid();

        ob_start();
        ?>
        <div class="resbs-property-map-container" style="width: <?php echo esc_attr($width); ?>; height: <?php echo esc_attr($height); ?>;">
            <?php if ($show_search || $show_filters): ?>
                <div class="resbs-map-controls">
                    <?php if ($show_search): ?>
                        <div class="resbs-map-search">
                            <input type="text" 
                                   id="<?php echo esc_attr($map_id); ?>-search" 
                                   class="resbs-map-search-input" 
                                   placeholder="<?php esc_attr_e('Search location...', 'realestate-booking-suite'); ?>">
                            <button type="button" class="resbs-map-search-btn">
                                <?php esc_html_e('Search', 'realestate-booking-suite'); ?>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?php if ($show_filters): ?>
                        <div class="resbs-map-filters">
                            <form class="resbs-map-filter-form">
                                <select name="property_type" class="resbs-map-filter">
                                    <option value=""><?php esc_html_e('All Types', 'realestate-booking-suite'); ?></option>
                                    <?php
                                    $property_types = get_terms(array(
                                        'taxonomy' => 'property_type',
                                        'hide_empty' => false,
                                    ));
                                    foreach ($property_types as $type) {
                                        echo '<option value="' . esc_attr($type->slug) . '">' . esc_html($type->name) . '</option>';
                                    }
                                    ?>
                                </select>

                                <select name="property_status" class="resbs-map-filter">
                                    <option value=""><?php esc_html_e('All Status', 'realestate-booking-suite'); ?></option>
                                    <?php
                                    $property_statuses = get_terms(array(
                                        'taxonomy' => 'property_status',
                                        'hide_empty' => false,
                                    ));
                                    foreach ($property_statuses as $status) {
                                        echo '<option value="' . esc_attr($status->slug) . '">' . esc_html($status->name) . '</option>';
                                    }
                                    ?>
                                </select>

                                <input type="number" name="price_min" class="resbs-map-filter" placeholder="<?php esc_attr_e('Min Price', 'realestate-booking-suite'); ?>">
                                <input type="number" name="price_max" class="resbs-map-filter" placeholder="<?php esc_attr_e('Max Price', 'realestate-booking-suite'); ?>">

                                <button type="submit" class="resbs-map-filter-btn">
                                    <?php esc_html_e('Filter', 'realestate-booking-suite'); ?>
                                </button>
                                <button type="button" class="resbs-map-reset-btn">
                                    <?php esc_html_e('Reset', 'realestate-booking-suite'); ?>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div id="<?php echo esc_attr($map_id); ?>" 
                 class="resbs-property-map" 
                 data-lat="<?php echo esc_attr($lat); ?>" 
                 data-lng="<?php echo esc_attr($lng); ?>" 
                 data-zoom="<?php echo esc_attr($zoom); ?>"
                 data-cluster="<?php echo esc_attr($cluster_markers ? 'true' : 'false'); ?>">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Register map widget
     */
    public function register_map_widget() {
        register_widget('RESBS_Property_Map_Widget');
    }

    /**
     * Display property map
     */
    public function display_property_map($property_id, $context = 'single') {
        $lat = get_post_meta($property_id, '_property_latitude', true);
        $lng = get_post_meta($property_id, '_property_longitude', true);
        
        if ($lat && $lng) {
            $map_id = 'resbs-property-map-' . $property_id;
            ?>
            <div class="resbs-property-map-single">
                <h3><?php esc_html_e('Location', 'realestate-booking-suite'); ?></h3>
                <div id="<?php echo esc_attr($map_id); ?>" 
                     class="resbs-property-map" 
                     data-lat="<?php echo esc_attr($lat); ?>" 
                     data-lng="<?php echo esc_attr($lng); ?>" 
                     data-zoom="15"
                     data-single="true">
                </div>
            </div>
            <?php
        }
    }
}

/**
 * Property Map Widget
 */
class RESBS_Property_Map_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'resbs_property_map_widget',
            esc_html__('Property Map', 'realestate-booking-suite'),
            array(
                'description' => esc_html__('Display an interactive map with property markers.', 'realestate-booking-suite'),
                'classname' => 'resbs-property-map-widget'
            )
        );
    }

    /**
     * Widget form
     */
    public function form($instance) {
        $defaults = array(
            'title' => esc_html__('Property Map', 'realestate-booking-suite'),
            'height' => '400px',
            'zoom' => '10',
            'show_search' => true,
            'show_filters' => true,
            'cluster_markers' => true
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        
        $title = sanitize_text_field($instance['title']);
        $height = sanitize_text_field($instance['height']);
        $zoom = intval($instance['zoom']);
        $show_search = (bool) $instance['show_search'];
        $show_filters = (bool) $instance['show_filters'];
        $cluster_markers = (bool) $instance['cluster_markers'];
        ?>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'realestate-booking-suite'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('height')); ?>">
                <?php esc_html_e('Map Height:', 'realestate-booking-suite'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('height')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('height')); ?>" 
                   type="text" value="<?php echo esc_attr($height); ?>" />
            <small><?php esc_html_e('e.g., 400px, 50vh', 'realestate-booking-suite'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('zoom')); ?>">
                <?php esc_html_e('Zoom Level:', 'realestate-booking-suite'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('zoom')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('zoom')); ?>" 
                   type="number" min="1" max="20" value="<?php echo esc_attr($zoom); ?>" />
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_search); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_search')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_search')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_search')); ?>">
                <?php esc_html_e('Show Search Box', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_filters); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_filters')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_filters')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_filters')); ?>">
                <?php esc_html_e('Show Filters', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($cluster_markers); ?> 
                   id="<?php echo esc_attr($this->get_field_id('cluster_markers')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('cluster_markers')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('cluster_markers')); ?>">
                <?php esc_html_e('Cluster Markers', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <?php
    }

    /**
     * Update widget
     */
    public function update($new_instance, $old_instance) {
        // Widget updates are handled by WordPress core with nonce verification
        // But we should still sanitize all inputs for security
        $instance = array();
        $instance['title'] = RESBS_Security::sanitize_text($new_instance['title'] ?? '');
        $instance['height'] = RESBS_Security::sanitize_text($new_instance['height'] ?? '400px');
        $instance['zoom'] = RESBS_Security::sanitize_int($new_instance['zoom'] ?? 10);
        $instance['show_search'] = RESBS_Security::sanitize_bool($new_instance['show_search'] ?? false);
        $instance['show_filters'] = RESBS_Security::sanitize_bool($new_instance['show_filters'] ?? false);
        $instance['cluster_markers'] = RESBS_Security::sanitize_bool($new_instance['cluster_markers'] ?? false);
        
        return $instance;
    }

    /**
     * Display widget
     */
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', sanitize_text_field($instance['title']));
        $height = sanitize_text_field($instance['height']);
        $zoom = intval($instance['zoom']);
        $show_search = (bool) $instance['show_search'];
        $show_filters = (bool) $instance['show_filters'];
        $cluster_markers = (bool) $instance['cluster_markers'];
        
        echo wp_kses_post($args['before_widget']);
        
        if (!empty($title)) {
            echo wp_kses_post($args['before_title']) . esc_html($title) . wp_kses_post($args['after_title']);
        }
        
        // Display map using shortcode
        echo do_shortcode('[resbs_property_map height="' . esc_attr($height) . '" zoom="' . esc_attr($zoom) . '" show_search="' . esc_attr($show_search ? 'true' : 'false') . '" show_filters="' . esc_attr($show_filters ? 'true' : 'false') . '" cluster_markers="' . esc_attr($cluster_markers ? 'true' : 'false') . '"]');
        
        echo wp_kses_post($args['after_widget']);
    }
}

// Initialize Maps Manager
new RESBS_Maps_Manager();
