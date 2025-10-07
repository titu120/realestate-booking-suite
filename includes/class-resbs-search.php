<?php
/**
 * Search Class
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
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('resbs_search_nonce'),
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
        ), $atts);
        
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
                                    <option value="1">1+</option>
                                    <option value="2">2+</option>
                                    <option value="3">3+</option>
                                    <option value="4">4+</option>
                                    <option value="5">5+</option>
                                </select>
                            </div>
                            
                            <div class="resbs-filter-group">
                                <label for="search_bathrooms"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                                <select id="search_bathrooms" name="bathrooms">
                                    <option value=""><?php esc_html_e('Any', 'realestate-booking-suite'); ?></option>
                                    <option value="1">1+</option>
                                    <option value="2">2+</option>
                                    <option value="3">3+</option>
                                    <option value="4">4+</option>
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
                                    <option value="pool"><?php esc_html_e('Swimming Pool', 'realestate-booking-suite'); ?></option>
                                    <option value="garage"><?php esc_html_e('Garage', 'realestate-booking-suite'); ?></option>
                                    <option value="garden"><?php esc_html_e('Garden', 'realestate-booking-suite'); ?></option>
                                    <option value="balcony"><?php esc_html_e('Balcony', 'realestate-booking-suite'); ?></option>
                                    <option value="elevator"><?php esc_html_e('Elevator', 'realestate-booking-suite'); ?></option>
                                    <option value="security"><?php esc_html_e('Security', 'realestate-booking-suite'); ?></option>
                                    <option value="parking"><?php esc_html_e('Parking', 'realestate-booking-suite'); ?></option>
                                    <option value="furnished"><?php esc_html_e('Furnished', 'realestate-booking-suite'); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="resbs-filter-row">
                            <div class="resbs-filter-group">
                                <label for="search_sort"><?php esc_html_e('Sort By', 'realestate-booking-suite'); ?></label>
                                <select id="search_sort" name="sort_by">
                                    <option value="date_desc"><?php esc_html_e('Newest First', 'realestate-booking-suite'); ?></option>
                                    <option value="date_asc"><?php esc_html_e('Oldest First', 'realestate-booking-suite'); ?></option>
                                    <option value="price_asc"><?php esc_html_e('Price: Low to High', 'realestate-booking-suite'); ?></option>
                                    <option value="price_desc"><?php esc_html_e('Price: High to Low', 'realestate-booking-suite'); ?></option>
                                    <option value="popularity"><?php esc_html_e('Most Popular', 'realestate-booking-suite'); ?></option>
                                    <option value="area_desc"><?php esc_html_e('Largest Area', 'realestate-booking-suite'); ?></option>
                                </select>
                            </div>
                            
                            <div class="resbs-filter-group">
                                <label for="search_view_type"><?php esc_html_e('View Type', 'realestate-booking-suite'); ?></label>
                                <select id="search_view_type" name="view_type">
                                    <option value="grid"><?php esc_html_e('Grid View', 'realestate-booking-suite'); ?></option>
                                    <option value="list"><?php esc_html_e('List View', 'realestate-booking-suite'); ?></option>
                                    <option value="map"><?php esc_html_e('Map View', 'realestate-booking-suite'); ?></option>
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
                        <button type="button" class="resbs-view-btn active" data-view="grid" title="<?php esc_attr_e('Grid View', 'realestate-booking-suite'); ?>">
                            <span class="dashicons dashicons-grid-view"></span>
                        </button>
                        <button type="button" class="resbs-view-btn" data-view="list" title="<?php esc_attr_e('List View', 'realestate-booking-suite'); ?>">
                            <span class="dashicons dashicons-list-view"></span>
                        </button>
                        <button type="button" class="resbs-view-btn" data-view="map" title="<?php esc_attr_e('Map View', 'realestate-booking-suite'); ?>">
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
     */
    public function handle_search_ajax() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['resbs_search_nonce'], 'resbs_search_nonce')) {
            wp_die(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        // Sanitize search parameters
        $price_min = isset($_POST['price_min']) ? floatval($_POST['price_min']) : 0;
        $price_max = isset($_POST['price_max']) ? floatval($_POST['price_max']) : 0;
        $location = isset($_POST['location']) ? intval($_POST['location']) : 0;
        $property_type = isset($_POST['property_type']) ? intval($_POST['property_type']) : 0;
        $bedrooms = isset($_POST['bedrooms']) ? intval($_POST['bedrooms']) : 0;
        $bathrooms = isset($_POST['bathrooms']) ? intval($_POST['bathrooms']) : 0;
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        $amenities = isset($_POST['amenities']) ? array_map('sanitize_text_field', $_POST['amenities']) : array();
        $sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : 'date_desc';
        $view_type = isset($_POST['view_type']) ? sanitize_text_field($_POST['view_type']) : 'grid';
        $results_per_page = isset($_POST['results_per_page']) ? intval($_POST['results_per_page']) : 12;
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        
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
                $args['meta_key'] = '_resbs_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'price_desc':
                $args['meta_key'] = '_resbs_price';
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
                $args['meta_key'] = '_resbs_view_count';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'area_desc':
                $args['meta_key'] = '_resbs_area';
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
                'key' => '_resbs_price',
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
                'key' => '_resbs_bedrooms',
                'value' => $bedrooms,
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        // Bathrooms filter
        if ($bathrooms > 0) {
            $args['meta_query'][] = array(
                'key' => '_resbs_bathrooms',
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
                    'key' => '_resbs_amenities',
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
                
                $results[] = array(
                    'id' => $post_id,
                    'title' => get_the_title(),
                    'excerpt' => get_the_excerpt(),
                    'permalink' => get_permalink(),
                    'price' => get_post_meta($post_id, '_resbs_price', true),
                    'bedrooms' => get_post_meta($post_id, '_resbs_bedrooms', true),
                    'bathrooms' => get_post_meta($post_id, '_resbs_bathrooms', true),
                    'area' => get_post_meta($post_id, '_resbs_area', true),
                    'latitude' => get_post_meta($post_id, '_resbs_latitude', true),
                    'longitude' => get_post_meta($post_id, '_resbs_longitude', true),
                    'amenities' => get_post_meta($post_id, '_resbs_amenities', true),
                    'thumbnail' => get_the_post_thumbnail_url($post_id, 'medium'),
                    'location' => wp_get_post_terms($post_id, 'property_location', array('fields' => 'names')),
                    'property_type' => wp_get_post_terms($post_id, 'property_type', array('fields' => 'names')),
                    'date' => get_the_date('c')
                );
            }
            wp_reset_postdata();
            
            wp_send_json_success(array(
                'results' => $results,
                'total' => $query->found_posts,
                'pages' => $query->max_num_pages,
                'current_page' => $page,
                'view_type' => $view_type
            ));
        } else {
            wp_reset_postdata();
            wp_send_json_error(esc_html__('No properties found matching your criteria.', 'realestate-booking-suite'));
        }
    }
}

// Initialize the class
new RESBS_Search();
