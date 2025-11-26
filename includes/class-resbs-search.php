<?php
/**
 * Search Class
 * 
 * Handles property search functionality including AJAX search requests.
 * 
 * SECURITY MEASURES IMPLEMENTED:
 * 
 * 1. NONCE VERIFICATION (CSRF Protection):
 *    - Nonce created and localized in JavaScript (wp_localize_script)
 *    - Nonce field included in search form (wp_nonce_field)
 *    - Nonce verified in AJAX handler before processing
 *    - Security events logged on nonce failure
 * 
 * 2. RATE LIMITING:
 *    - Limits to 30 requests per 5 minutes (300 seconds) per IP
 *    - Prevents abuse and DoS attacks
 *    - Uses RESBS_Security::check_rate_limit()
 * 
 * 3. INPUT SANITIZATION:
 *    - All POST parameters sanitized using RESBS_Security helper methods
 *    - Float values for prices
 *    - Integer values for IDs, counts, pagination
 *    - Text fields sanitized
 *    - Arrays sanitized element by element
 *    - Whitelist validation for sort_by and view_type
 *    - Results per page limited to max 100
 * 
 * 4. OUTPUT ESCAPING:
 *    - All output properly escaped (esc_html, esc_attr, esc_url_raw)
 *    - JSON responses use wp_send_json_success/error
 * 
 * 5. USER PERMISSIONS:
 *    - This is a PUBLIC endpoint (nopriv) - no capability check required
 *    - Anyone can search properties (read-only operation)
 *    - Only published properties are returned
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Search {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_search_scripts'));
        add_action('wp_ajax_resbs_search_properties', array($this, 'handle_search_ajax'));
        add_action('wp_ajax_nopriv_resbs_search_properties', array($this, 'handle_search_ajax'));
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('resbs_search', array($this, 'search_shortcode'));
    }
    
    /**
     * Enqueue search scripts and styles
     */
    public function enqueue_search_scripts() {
        wp_enqueue_script('jquery');
        
        wp_enqueue_script(
            'resbs-search',
            RESBS_URL . 'assets/js/main.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('resbs-search', 'resbs_search_ajax', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('resbs_search_nonce')),
            'messages' => array(
                'searching' => esc_html__('Searching...', 'realestate-booking-suite'),
                'no_results' => esc_html__('No properties found matching your criteria.', 'realestate-booking-suite'),
                'error' => esc_html__('Error occurred while searching. Please try again.', 'realestate-booking-suite')
            )
        ));
    }
    
    /**
     * Search shortcode
     */
    public function search_shortcode($atts) {
        $atts = shortcode_atts(array(
            'show_filters' => 'true',
            'results_per_page' => 12,
            'view_type' => 'grid', // grid, list, map
            'show_map' => 'true'
        ), $atts, 'resbs_search');
        
        // Sanitize shortcode attributes
        $atts['show_filters'] = sanitize_text_field($atts['show_filters']);
        $atts['results_per_page'] = absint($atts['results_per_page']);
        $atts['view_type'] = sanitize_text_field($atts['view_type']);
        $atts['show_map'] = sanitize_text_field($atts['show_map']);
        
        // Validate view_type
        $allowed_view_types = array('grid', 'list', 'map');
        if (!in_array($atts['view_type'], $allowed_view_types, true)) {
            $atts['view_type'] = 'grid';
        }
        
        // Limit results_per_page to prevent abuse
        $atts['results_per_page'] = min(max($atts['results_per_page'], 1), 100);
        
        ob_start();
        ?>
        <div class="resbs-search-container">
            <?php if ($atts['show_filters'] === 'true'): ?>
                <div class="resbs-search-filters">
                    <form id="resbs-search-form">
                        <?php wp_nonce_field('resbs_search_nonce', 'resbs_search_nonce'); ?>
                        <input type="hidden" name="action" value="resbs_search_properties">
                        <input type="hidden" name="results_per_page" value="<?php echo esc_attr($atts['results_per_page']); ?>">
                        
                        <div class="resbs-filter-row">
                            <div class="resbs-filter-group">
                                <label for="search_price_min"><?php esc_html_e('Min Price', 'realestate-booking-suite'); ?></label>
                                <input type="number" id="search_price_min" name="price_min" min="0" step="0.01" placeholder="<?php esc_attr_e('Min Price', 'realestate-booking-suite'); ?>">
                            </div>
                            
                            <div class="resbs-filter-group">
                                <label for="search_price_max"><?php esc_html_e('Max Price', 'realestate-booking-suite'); ?></label>
                                <input type="number" id="search_price_max" name="price_max" min="0" step="0.01" placeholder="<?php esc_attr_e('Max Price', 'realestate-booking-suite'); ?>">
                            </div>
                        </div>
                        
                        <div class="resbs-filter-row">
                            <div class="resbs-filter-group">
                                <label for="search_location"><?php esc_html_e('Location', 'realestate-booking-suite'); ?></label>
                                <select id="search_location" name="location">
                                    <option value=""><?php esc_html_e('All Locations', 'realestate-booking-suite'); ?></option>
                                    <?php
                                    $locations = get_terms(array(
                                        'taxonomy' => 'property_location',
                                        'hide_empty' => false
                                    ));
                                    if (!empty($locations) && !is_wp_error($locations)) {
                                        foreach ($locations as $location) {
                                            echo '<option value="' . esc_attr($location->term_id) . '">' . esc_html($location->name) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="resbs-filter-group">
                                <label for="search_property_type"><?php esc_html_e('Property Type', 'realestate-booking-suite'); ?></label>
                                <select id="search_property_type" name="property_type">
                                    <option value=""><?php esc_html_e('All Types', 'realestate-booking-suite'); ?></option>
                                    <?php
                                    $property_types = get_terms(array(
                                        'taxonomy' => 'property_type',
                                        'hide_empty' => false
                                    ));
                                    if (!empty($property_types) && !is_wp_error($property_types)) {
                                        foreach ($property_types as $type) {
                                            echo '<option value="' . esc_attr($type->term_id) . '">' . esc_html($type->name) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="resbs-filter-row">
                            <div class="resbs-filter-group">
                                <label for="search_bedrooms"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label>
                                <select id="search_bedrooms" name="bedrooms">
                                    <option value=""><?php esc_html_e('Any', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('1'); ?>"><?php echo esc_html('1+'); ?></option>
                                    <option value="<?php echo esc_attr('2'); ?>"><?php echo esc_html('2+'); ?></option>
                                    <option value="<?php echo esc_attr('3'); ?>"><?php echo esc_html('3+'); ?></option>
                                    <option value="<?php echo esc_attr('4'); ?>"><?php echo esc_html('4+'); ?></option>
                                    <option value="<?php echo esc_attr('5'); ?>"><?php echo esc_html('5+'); ?></option>
                                </select>
                            </div>
                            
                            <div class="resbs-filter-group">
                                <label for="search_bathrooms"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                                <select id="search_bathrooms" name="bathrooms">
                                    <option value=""><?php esc_html_e('Any', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('1'); ?>"><?php echo esc_html('1+'); ?></option>
                                    <option value="<?php echo esc_attr('2'); ?>"><?php echo esc_html('2+'); ?></option>
                                    <option value="<?php echo esc_attr('3'); ?>"><?php echo esc_html('3+'); ?></option>
                                    <option value="<?php echo esc_attr('4'); ?>"><?php echo esc_html('4+'); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="resbs-filter-row">
                            <div class="resbs-filter-group">
                                <label for="search_keyword"><?php esc_html_e('Keyword', 'realestate-booking-suite'); ?></label>
                                <input type="text" id="search_keyword" name="keyword" placeholder="<?php esc_attr_e('Search by title or description', 'realestate-booking-suite'); ?>">
                            </div>
                            
                            <div class="resbs-filter-group">
                                <label for="search_amenities"><?php esc_html_e('Amenities', 'realestate-booking-suite'); ?></label>
                                <select id="search_amenities" name="amenities[]" multiple>
                                    <option value="<?php echo esc_attr('pool'); ?>"><?php esc_html_e('Swimming Pool', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('garage'); ?>"><?php esc_html_e('Garage', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('garden'); ?>"><?php esc_html_e('Garden', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('balcony'); ?>"><?php esc_html_e('Balcony', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('elevator'); ?>"><?php esc_html_e('Elevator', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('security'); ?>"><?php esc_html_e('Security', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('parking'); ?>"><?php esc_html_e('Parking', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('furnished'); ?>"><?php esc_html_e('Furnished', 'realestate-booking-suite'); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="resbs-filter-row">
                            <div class="resbs-filter-group">
                                <label for="search_sort"><?php esc_html_e('Sort By', 'realestate-booking-suite'); ?></label>
                                <select id="search_sort" name="sort_by">
                                    <option value="<?php echo esc_attr('date_desc'); ?>"><?php esc_html_e('Newest First', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('date_asc'); ?>"><?php esc_html_e('Oldest First', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('price_asc'); ?>"><?php esc_html_e('Price: Low to High', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('price_desc'); ?>"><?php esc_html_e('Price: High to Low', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('popularity'); ?>"><?php esc_html_e('Most Popular', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('area_desc'); ?>"><?php esc_html_e('Largest Area', 'realestate-booking-suite'); ?></option>
                                </select>
                            </div>
                            
                            <div class="resbs-filter-group">
                                <label for="search_view_type"><?php esc_html_e('View Type', 'realestate-booking-suite'); ?></label>
                                <select id="search_view_type" name="view_type">
                                    <option value="<?php echo esc_attr('grid'); ?>"><?php esc_html_e('Grid View', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('list'); ?>"><?php esc_html_e('List View', 'realestate-booking-suite'); ?></option>
                                    <option value="<?php echo esc_attr('map'); ?>"><?php esc_html_e('Map View', 'realestate-booking-suite'); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="resbs-filter-row">
                            <div class="resbs-filter-group">
                                <button type="submit" class="resbs-search-btn">
                                    <?php esc_html_e('Search Properties', 'realestate-booking-suite'); ?>
                                </button>
                                <button type="button" class="resbs-reset-btn">
                                    <?php esc_html_e('Reset Filters', 'realestate-booking-suite'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
            
            <div class="resbs-search-results">
                <div class="resbs-view-controls">
                    <div class="resbs-view-toggle">
                        <button type="button" class="resbs-view-btn active" data-view="<?php echo esc_attr('grid'); ?>" title="<?php esc_attr_e('Grid View', 'realestate-booking-suite'); ?>">
                            <span class="dashicons dashicons-grid-view"></span>
                        </button>
                        <button type="button" class="resbs-view-btn" data-view="<?php echo esc_attr('list'); ?>" title="<?php esc_attr_e('List View', 'realestate-booking-suite'); ?>">
                            <span class="dashicons dashicons-list-view"></span>
                        </button>
                        <button type="button" class="resbs-view-btn" data-view="<?php echo esc_attr('map'); ?>" title="<?php esc_attr_e('Map View', 'realestate-booking-suite'); ?>">
                            <span class="dashicons dashicons-location-alt"></span>
                        </button>
                    </div>
                    <div class="resbs-results-count">
                        <span id="resbs-total-results">0</span> <?php esc_html_e('properties found', 'realestate-booking-suite'); ?>
                    </div>
                </div>
                
                <div id="resbs-loading" class="resbs-loading" style="display: none;">
                    <?php esc_html_e('Searching...', 'realestate-booking-suite'); ?>
                </div>
                
                <div id="resbs-results-container" class="resbs-grid-view"></div>
                
                <?php if ($atts['show_map'] === 'true'): ?>
                    <div id="resbs-map-container" class="resbs-map-view" style="display: none;">
                        <div id="resbs-map" style="height: 500px; width: 100%;"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle AJAX search
     * 
     * Security measures:
     * - Nonce verification (CSRF protection)
     * - Rate limiting (prevents abuse)
     * - Input sanitization
     * - Output escaping
     * 
     * Note: This is a public endpoint (nopriv), so no capability check is required
     */
    public function handle_search_ajax() {
        // Rate limiting check - prevent abuse of search endpoint
        if (!RESBS_Security::check_rate_limit('resbs_search_properties', 30, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }
        
        // Verify nonce - check if nonce exists first
        $nonce = '';
        if (isset($_POST['resbs_search_nonce']) && is_string($_POST['resbs_search_nonce'])) {
            $nonce = sanitize_text_field($_POST['resbs_search_nonce']);
        }
        if (empty($nonce) && isset($_POST['nonce']) && is_string($_POST['nonce'])) {
            // Also check for 'nonce' field name (common alternative)
            $nonce = sanitize_text_field($_POST['nonce']);
        }
        
        // Use security helper class for consistent nonce verification
        if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_search_nonce')) {
            RESBS_Security::log_security_event('search_nonce_failed', array(
                'action' => 'resbs_search_properties',
                'ip' => RESBS_Security::get_client_ip()
            ));
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite')
            ));
        }
        
        // Sanitize search parameters using security helper class
        $price_min = isset($_POST['price_min']) ? RESBS_Security::sanitize_float($_POST['price_min']) : 0;
        $price_max = isset($_POST['price_max']) ? RESBS_Security::sanitize_float($_POST['price_max']) : 0;
        $location = isset($_POST['location']) ? RESBS_Security::sanitize_int($_POST['location']) : 0;
        $property_type = isset($_POST['property_type']) ? RESBS_Security::sanitize_int($_POST['property_type']) : 0;
        $bedrooms = isset($_POST['bedrooms']) ? RESBS_Security::sanitize_int($_POST['bedrooms']) : 0;
        $bathrooms = isset($_POST['bathrooms']) ? RESBS_Security::sanitize_int($_POST['bathrooms']) : 0;
        $keyword = isset($_POST['keyword']) ? RESBS_Security::sanitize_text($_POST['keyword']) : '';
        $amenities = isset($_POST['amenities']) ? RESBS_Security::sanitize_array($_POST['amenities'], 'sanitize_text_field') : array();
        $sort_by = isset($_POST['sort_by']) ? RESBS_Security::sanitize_text($_POST['sort_by']) : 'date_desc';
        $view_type = isset($_POST['view_type']) ? RESBS_Security::sanitize_text($_POST['view_type']) : 'grid';
        $results_per_page = isset($_POST['results_per_page']) ? RESBS_Security::sanitize_int($_POST['results_per_page'], 12) : 12;
        $page = isset($_POST['page']) ? RESBS_Security::sanitize_int($_POST['page'], 1) : 1;
        
        // Validate sort_by and view_type values to prevent injection
        $allowed_sort_by = array('date_desc', 'date_asc', 'price_asc', 'price_desc', 'popularity', 'area_desc');
        if (!in_array($sort_by, $allowed_sort_by, true)) {
            $sort_by = 'date_desc';
        }
        
        $allowed_view_types = array('grid', 'list', 'map');
        if (!in_array($view_type, $allowed_view_types, true)) {
            $view_type = 'grid';
        }
        
        // Validate results_per_page to prevent abuse (max 100 per page)
        $results_per_page = min(max($results_per_page, 1), 100);
        
        // Build query args
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $results_per_page,
            'paged' => $page,
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        // Add sorting
        switch ($sort_by) {
            case 'price_asc':
                $args['meta_key'] = '_property_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'price_desc':
                $args['meta_key'] = '_property_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'date_asc':
                $args['orderby'] = 'date';
                $args['order'] = 'ASC';
                break;
            case 'date_desc':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
            case 'popularity':
                // Use view count if available, otherwise fallback to date
                $args['meta_key'] = '_property_view_count';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'area_desc':
                $args['meta_key'] = '_property_size';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }
        
        // Price filter
        if ($price_min > 0 || $price_max > 0) {
            $price_query = array(
                'key' => '_property_price',
                'type' => 'NUMERIC'
            );
            
            if ($price_min > 0 && $price_max > 0) {
                $price_query['value'] = array($price_min, $price_max);
                $price_query['compare'] = 'BETWEEN';
            } elseif ($price_min > 0) {
                $price_query['value'] = $price_min;
                $price_query['compare'] = '>=';
            } elseif ($price_max > 0) {
                $price_query['value'] = $price_max;
                $price_query['compare'] = '<=';
            }
            
            $args['meta_query'][] = $price_query;
        }
        
        // Bedrooms filter
        if ($bedrooms > 0) {
            $args['meta_query'][] = array(
                'key' => '_property_bedrooms',
                'value' => $bedrooms,
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        // Bathrooms filter
        if ($bathrooms > 0) {
            $args['meta_query'][] = array(
                'key' => '_property_bathrooms',
                'value' => $bathrooms,
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        // Location filter
        if ($location > 0) {
            $args['tax_query'][] = array(
                'taxonomy' => 'property_location',
                'field' => 'term_id',
                'terms' => $location
            );
        }
        
        // Property type filter
        if ($property_type > 0) {
            $args['tax_query'][] = array(
                'taxonomy' => 'property_type',
                'field' => 'term_id',
                'terms' => $property_type
            );
        }
        
        // Amenities filter
        if (!empty($amenities)) {
            $amenities_query = array('relation' => 'OR');
            foreach ($amenities as $amenity) {
                $amenities_query[] = array(
                    'key' => '_property_amenities',
                    'value' => $amenity,
                    'compare' => 'LIKE'
                );
            }
            $args['meta_query'][] = $amenities_query;
        }
        
        // Keyword search
        if (!empty($keyword)) {
            $args['s'] = $keyword;
        }
        
        // Set relation for multiple meta queries
        if (count($args['meta_query']) > 1) {
            $args['meta_query']['relation'] = 'AND';
        }
        
        // Set relation for multiple tax queries
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
        
        // Execute query
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            $results = array();
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                
                // Get and sanitize all data
                $title = get_the_title();
                $excerpt = get_the_excerpt();
                $permalink = get_permalink();
                $price_raw = get_post_meta($post_id, '_property_price', true);
                $property_bedrooms = get_post_meta($post_id, '_property_bedrooms', true);
                $property_bathrooms = get_post_meta($post_id, '_property_bathrooms', true);
                // Try multiple possible area meta keys
                $area_raw = get_post_meta($post_id, '_property_size', true);
                if (empty($area_raw)) {
                    $area_raw = get_post_meta($post_id, '_property_area_sqft', true);
                }
                $latitude_raw = get_post_meta($post_id, '_property_latitude', true);
                $longitude_raw = get_post_meta($post_id, '_property_longitude', true);
                $amenities_raw = get_post_meta($post_id, '_property_amenities', true);
                $thumbnail = get_the_post_thumbnail_url($post_id, 'medium');
                $location_terms = wp_get_post_terms($post_id, 'property_location', array('fields' => 'names'));
                $property_type_terms = wp_get_post_terms($post_id, 'property_type', array('fields' => 'names'));
                $date = get_the_date('c');
                
                // Sanitize location and property type arrays
                $location_names = array();
                if (!empty($location_terms) && !is_wp_error($location_terms)) {
                    foreach ($location_terms as $term) {
                        $location_names[] = sanitize_text_field($term);
                    }
                }
                
                $property_type_names = array();
                if (!empty($property_type_terms) && !is_wp_error($property_type_terms)) {
                    foreach ($property_type_terms as $term) {
                        $property_type_names[] = sanitize_text_field($term);
                    }
                }
                
                // Sanitize numeric values properly
                $price = RESBS_Security::sanitize_float($price_raw);
                $property_bedrooms = absint($property_bedrooms);
                $property_bathrooms = absint($property_bathrooms);
                $area = absint($area_raw);
                $latitude = RESBS_Security::sanitize_float($latitude_raw);
                $longitude = RESBS_Security::sanitize_float($longitude_raw);
                
                // Handle amenities - could be array, serialized string, or plain string
                $amenities_sanitized = '';
                if (!empty($amenities_raw)) {
                    if (is_array($amenities_raw)) {
                        $amenities_sanitized = array_map('sanitize_text_field', $amenities_raw);
                    } elseif (is_string($amenities_raw)) {
                        // Try to unserialize if it's a serialized string
                        $unserialized = maybe_unserialize($amenities_raw);
                        if (is_array($unserialized)) {
                            $amenities_sanitized = array_map('sanitize_text_field', $unserialized);
                        } else {
                            $amenities_sanitized = sanitize_text_field($amenities_raw);
                        }
                    }
                }
                
                $results[] = array(
                    'id' => absint($post_id),
                    'title' => sanitize_text_field($title),
                    'excerpt' => wp_kses_post($excerpt),
                    'permalink' => esc_url_raw($permalink),
                    'price' => $price,
                    'bedrooms' => $property_bedrooms,
                    'bathrooms' => $property_bathrooms,
                    'area' => $area,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'amenities' => $amenities_sanitized,
                    'thumbnail' => esc_url_raw($thumbnail),
                    'location' => $location_names,
                    'property_type' => $property_type_names,
                    'date' => sanitize_text_field($date)
                );
            }
            wp_reset_postdata();
            
            wp_send_json_success(array(
                'results' => $results,
                'total' => absint($query->found_posts),
                'pages' => absint($query->max_num_pages),
                'current_page' => absint($page),
                'view_type' => sanitize_text_field($view_type)
            ));
        } else {
            wp_reset_postdata();
            wp_send_json_error(array(
                'message' => esc_html__('No properties found matching your criteria.', 'realestate-booking-suite')
            ));
        }
    }
}

// Initialize the class
new RESBS_Search();
