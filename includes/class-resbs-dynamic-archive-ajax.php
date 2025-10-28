<?php
/**
 * AJAX Search Handler for Dynamic Archive
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Dynamic_Archive_AJAX {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_resbs_search_properties', array($this, 'search_properties'));
        add_action('wp_ajax_nopriv_resbs_search_properties', array($this, 'search_properties'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Enqueue AJAX scripts
     */
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        
        wp_localize_script('resbs-dynamic-archive', 'resbs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('resbs_search_nonce'),
        ));
    }
    
    /**
     * AJAX search properties
     */
    public function search_properties() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_search_nonce')) {
            wp_die('Security check failed');
        }
        
        // Get search parameters
        $search_query = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
        $min_price = isset($_POST['min_price']) ? intval($_POST['min_price']) : '';
        $max_price = isset($_POST['max_price']) ? intval($_POST['max_price']) : '';
        $property_type = isset($_POST['property_type']) ? sanitize_text_field($_POST['property_type']) : '';
        $bedrooms = isset($_POST['bedrooms']) ? intval($_POST['bedrooms']) : '';
        $bathrooms = isset($_POST['bathrooms']) ? intval($_POST['bathrooms']) : '';
        $min_sqft = isset($_POST['min_sqft']) ? intval($_POST['min_sqft']) : '';
        $max_sqft = isset($_POST['max_sqft']) ? intval($_POST['max_sqft']) : '';
        $year_built = isset($_POST['year_built']) ? sanitize_text_field($_POST['year_built']) : '';
        $property_status = isset($_POST['property_status']) ? sanitize_text_field($_POST['property_status']) : '';
        $sort_by = isset($_POST['sort_by']) ? sanitize_text_field($_POST['sort_by']) : 'date';
        $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
        
        // Build WP_Query arguments
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'paged' => $paged,
            'meta_query' => array(),
            'tax_query' => array(),
        );
        
        // Add search query
        if (!empty($search_query)) {
            $args['s'] = $search_query;
        }
        
        // Add price range filter
        if (!empty($min_price) || !empty($max_price)) {
            $price_query = array(
                'key' => '_property_price',
                'type' => 'NUMERIC',
            );
            
            if (!empty($min_price)) {
                $price_query['value'] = $min_price;
                $price_query['compare'] = '>=';
            }
            
            if (!empty($max_price)) {
                if (!empty($min_price)) {
                    $price_query['compare'] = 'BETWEEN';
                    $price_query['value'] = array($min_price, $max_price);
                } else {
                    $price_query['value'] = $max_price;
                    $price_query['compare'] = '<=';
                }
            }
            
            $args['meta_query'][] = $price_query;
        }
        
        // Add bedrooms filter
        if (!empty($bedrooms)) {
            $args['meta_query'][] = array(
                'key' => '_property_bedrooms',
                'value' => $bedrooms,
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        // Add bathrooms filter
        if (!empty($bathrooms)) {
            $args['meta_query'][] = array(
                'key' => '_property_bathrooms',
                'value' => $bathrooms,
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        // Add square footage filter
        if (!empty($min_sqft) || !empty($max_sqft)) {
            $sqft_query = array(
                'key' => '_property_area_sqft',
                'type' => 'NUMERIC',
            );
            
            if (!empty($min_sqft)) {
                $sqft_query['value'] = $min_sqft;
                $sqft_query['compare'] = '>=';
            }
            
            if (!empty($max_sqft)) {
                if (!empty($min_sqft)) {
                    $sqft_query['compare'] = 'BETWEEN';
                    $sqft_query['value'] = array($min_sqft, $max_sqft);
                } else {
                    $sqft_query['value'] = $max_sqft;
                    $sqft_query['compare'] = '<=';
                }
            }
            
            $args['meta_query'][] = $sqft_query;
        }
        
        // Add year built filter
        if (!empty($year_built)) {
            $year_value = str_replace('+', '', $year_built);
            $args['meta_query'][] = array(
                'key' => '_property_year_built',
                'value' => $year_value,
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        // Add property type filter
        if (!empty($property_type)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $property_type,
            );
        }
        
        // Add property status filter
        if (!empty($property_status)) {
            $args['tax_query'][] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $property_status,
            );
        }
        
        // Add sorting
        switch ($sort_by) {
            case 'price_low':
                $args['meta_key'] = '_property_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'price_high':
                $args['meta_key'] = '_property_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'newest':
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }
        
        // Set meta_query relation
        if (count($args['meta_query']) > 1) {
            $args['meta_query']['relation'] = 'AND';
        }
        
        // Set tax_query relation
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
        
        // Execute the query
        $properties_query = new WP_Query($args);
        
        // Prepare response data
        $response = array(
            'success' => true,
            'found_posts' => $properties_query->found_posts,
            'max_pages' => $properties_query->max_num_pages,
            'current_page' => $paged,
            'properties' => array(),
            'map_markers' => array()
        );
        
        if ($properties_query->have_posts()) {
            while ($properties_query->have_posts()) {
                $properties_query->the_post();
                
                // Get property meta data
                $price = get_post_meta(get_the_ID(), '_property_price', true);
                $bedrooms = get_post_meta(get_the_ID(), '_property_bedrooms', true);
                $bathrooms = get_post_meta(get_the_ID(), '_property_bathrooms', true);
                $area_sqft = get_post_meta(get_the_ID(), '_property_area_sqft', true);
                $address = get_post_meta(get_the_ID(), '_property_address', true);
                $city = get_post_meta(get_the_ID(), '_property_city', true);
                $state = get_post_meta(get_the_ID(), '_property_state', true);
                $zip = get_post_meta(get_the_ID(), '_property_zip', true);
                $latitude = get_post_meta(get_the_ID(), '_property_latitude', true);
                $longitude = get_post_meta(get_the_ID(), '_property_longitude', true);
                
                // Get property type and status
                $property_types = get_the_terms(get_the_ID(), 'property_type');
                $property_statuses = get_the_terms(get_the_ID(), 'property_status');
                
                $property_type_name = '';
                if ($property_types && !is_wp_error($property_types)) {
                    $property_type_name = $property_types[0]->name;
                }
                
                $property_status_name = '';
                if ($property_statuses && !is_wp_error($property_statuses)) {
                    $property_status_name = $property_statuses[0]->name;
                }
                
                // Get featured image
                $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
                if (!$featured_image) {
                    $featured_image = 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800';
                }
                
                // Format price
                $formatted_price = '';
                if ($price) {
                    $formatted_price = '$' . number_format($price);
                }
                
                // Format location
                $location = '';
                if ($address) $location .= $address;
                if ($city) $location .= ($location ? ', ' : '') . $city;
                if ($state) $location .= ($location ? ', ' : '') . $state;
                if ($zip) $location .= ($location ? ' ' : '') . $zip;
                
                // Determine badge
                $badge_class = 'badge-new';
                $badge_text = 'Just listed';
                $post_date = get_the_date('Y-m-d');
                $days_old = (time() - strtotime($post_date)) / (60 * 60 * 24);
                
                if ($days_old < 7) {
                    $badge_class = 'badge-new';
                    $badge_text = 'Just listed';
                } elseif ($days_old < 30) {
                    $badge_class = 'badge-featured';
                    $badge_text = 'Featured';
                } else {
                    $badge_class = 'badge-standard';
                    $badge_text = 'Available';
                }
                
                // Add property data
                $response['properties'][] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'permalink' => get_permalink(),
                    'featured_image' => $featured_image,
                    'price' => $formatted_price,
                    'bedrooms' => $bedrooms,
                    'bathrooms' => $bathrooms,
                    'area_sqft' => $area_sqft,
                    'location' => $location,
                    'property_type' => $property_type_name,
                    'property_status' => $property_status_name,
                    'badge_class' => $badge_class,
                    'badge_text' => $badge_text
                );
                
                // Add map marker data
                if ($latitude && $longitude) {
                    $response['map_markers'][] = array(
                        'id' => get_the_ID(),
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'title' => get_the_title(),
                        'price' => $formatted_price,
                        'badge_class' => $badge_class
                    );
                }
            }
        }
        
        wp_reset_postdata();
        
        // Return JSON response
        wp_send_json($response);
    }
}

// Initialize the class
new RESBS_Dynamic_Archive_AJAX();
