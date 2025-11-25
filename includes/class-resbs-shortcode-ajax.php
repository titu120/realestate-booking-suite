<?php
/**
 * Shortcode AJAX Handlers
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Shortcode_AJAX {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_resbs_filter_properties', array($this, 'filter_properties'));
        add_action('wp_ajax_nopriv_resbs_filter_properties', array($this, 'filter_properties'));
        
        add_action('wp_ajax_resbs_search_properties', array($this, 'search_properties'));
        add_action('wp_ajax_nopriv_resbs_search_properties', array($this, 'search_properties'));
        
        add_action('wp_ajax_resbs_update_profile', array($this, 'update_profile'));
        
        add_action('wp_ajax_resbs_submit_property', array($this, 'submit_property'), 5);
        add_action('wp_ajax_nopriv_resbs_submit_property', array($this, 'submit_property'), 5);
        add_action('wp_ajax_resbs_publish_property', array($this, 'publish_property'));
        
        add_action('wp_ajax_resbs_clear_favorites', array($this, 'clear_favorites'));
        
        add_action('wp_ajax_resbs_load_more_properties', array($this, 'load_more_properties'));
        add_action('wp_ajax_nopriv_resbs_load_more_properties', array($this, 'load_more_properties'));
        
        // Allow logged-in users to upload files for property submissions
        add_filter('user_has_cap', array($this, 'allow_upload_files_for_properties'), 10, 4);
    }
    
    /**
     * Temporarily grant upload_files capability to logged-in users for property submissions
     */
    public function allow_upload_files_for_properties($allcaps, $caps, $args, $user) {
        // Only apply during property submission AJAX requests
        if (!defined('DOING_AJAX') || !DOING_AJAX) {
            return $allcaps;
        }
        
        // Check if this is a property submission or upload request
        $action = isset($_POST['action']) ? $_POST['action'] : (isset($_REQUEST['action']) ? $_REQUEST['action'] : '');
        if (!in_array($action, array('resbs_submit_property', 'resbs_upload_image', 'resbs_upload_property_media'))) {
            return $allcaps;
        }
        
        // Grant upload_files capability to logged-in users
        if (is_user_logged_in() && isset($caps[0]) && $caps[0] === 'upload_files') {
            $allcaps['upload_files'] = true;
        }
        
        return $allcaps;
    }

    /**
     * Filter properties AJAX handler
     */
    public function filter_properties() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_widget_filter')) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed.', 'realestate-booking-suite')
            ));
        }

        // Sanitize form data
        $form_data = array();
        parse_str($_POST['form_data'], $form_data);
        
        $sanitized_data = array(
            'property_type' => RESBS_Security::sanitize_text($form_data['property_type'] ?? ''),
            'location' => RESBS_Security::sanitize_text($form_data['location'] ?? ''),
            'price_min' => RESBS_Security::sanitize_int($form_data['price_min'] ?? 0),
            'price_max' => RESBS_Security::sanitize_int($form_data['price_max'] ?? 0)
        );

        // Sanitize widget settings
        $settings = RESBS_Security::sanitize_array($_POST['widget_settings'] ?? array());

        // Build query args
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $settings['posts_per_page'] ?? 12,
            'orderby' => $settings['orderby'] ?? 'date',
            'order' => $settings['order'] ?? 'DESC',
        );

        // Add meta query for price range
        $meta_query = array();
        
        if (!empty($sanitized_data['price_min'])) {
            $meta_query[] = array(
                'key' => '_property_price',
                'value' => $sanitized_data['price_min'],
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        if (!empty($sanitized_data['price_max'])) {
            $meta_query[] = array(
                'key' => '_property_price',
                'value' => $sanitized_data['price_max'],
                'compare' => '<=',
                'type' => 'NUMERIC'
            );
        }

        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }

        // Add taxonomy query
        $tax_query = array();
        
        if (!empty($sanitized_data['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $sanitized_data['property_type']
            );
        }

        if (!empty($sanitized_data['location'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_location',
                'field' => 'slug',
                'terms' => $sanitized_data['location']
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        // Execute query
        $properties = new WP_Query($query_args);

        // Generate HTML
        ob_start();
        
        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $this->render_property_card($settings);
            }
            wp_reset_postdata();
        } else {
            echo '<p class="resbs-no-properties">' . esc_html__('No properties found matching your criteria.', 'realestate-booking-suite') . '</p>';
        }
        
        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'count' => intval($properties->found_posts)
        ));
    }

    /**
     * Search properties AJAX handler
     */
    public function search_properties() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_search_form')) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed.', 'realestate-booking-suite')
            ));
        }

        // Sanitize form data
        $form_data = array();
        parse_str($_POST['form_data'], $form_data);
        
        $sanitized_data = array(
            'keyword' => RESBS_Security::sanitize_text($form_data['keyword'] ?? ''),
            'property_type' => RESBS_Security::sanitize_text($form_data['property_type'] ?? ''),
            'location' => RESBS_Security::sanitize_text($form_data['location'] ?? ''),
            'price_min' => RESBS_Security::sanitize_int($form_data['price_min'] ?? 0),
            'price_max' => RESBS_Security::sanitize_int($form_data['price_max'] ?? 0)
        );

        // Build query args
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'orderby' => 'relevance',
            'order' => 'DESC',
        );

        // Add search keyword
        if (!empty($sanitized_data['keyword'])) {
            $query_args['s'] = $sanitized_data['keyword'];
        }

        // Add meta query for price range
        $meta_query = array();
        
        if (!empty($sanitized_data['price_min'])) {
            $meta_query[] = array(
                'key' => '_property_price',
                'value' => $sanitized_data['price_min'],
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        if (!empty($sanitized_data['price_max'])) {
            $meta_query[] = array(
                'key' => '_property_price',
                'value' => $sanitized_data['price_max'],
                'compare' => '<=',
                'type' => 'NUMERIC'
            );
        }

        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }

        // Add taxonomy query
        $tax_query = array();
        
        if (!empty($sanitized_data['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $sanitized_data['property_type']
            );
        }

        if (!empty($sanitized_data['location'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_location',
                'field' => 'slug',
                'terms' => $sanitized_data['location']
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        // Execute query
        $properties = new WP_Query($query_args);

        // Generate HTML
        ob_start();
        
        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $this->render_property_card(array(
                    'show_price' => true,
                    'show_meta' => true,
                    'show_excerpt' => true,
                    'show_badges' => true,
                    'show_favorite_button' => true,
                    'show_book_button' => true
                ));
            }
            wp_reset_postdata();
        } else {
            echo '<p class="resbs-no-properties">' . esc_html__('No properties found matching your search criteria.', 'realestate-booking-suite') . '</p>';
        }
        
        $html = ob_get_clean();

        // Generate markers for map
        $markers = array();
        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $property_id = get_the_ID();
                $latitude = get_post_meta($property_id, '_property_latitude', true);
                $longitude = get_post_meta($property_id, '_property_longitude', true);
                
                if (!empty($latitude) && !empty($longitude)) {
                    $markers[] = array(
                        'id' => $property_id,
                        'title' => esc_html(get_the_title()),
                        'lat' => floatval($latitude),
                        'lng' => floatval($longitude),
                        'price' => esc_html(get_post_meta($property_id, '_property_price', true)),
                        'url' => esc_url(get_permalink())
                    );
                }
            }
            wp_reset_postdata();
        }

        wp_send_json_success(array(
            'html' => $html,
            'count' => intval($properties->found_posts),
            'markers' => $markers
        ));
    }

    /**
     * Update profile AJAX handler
     */
    public function update_profile() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => esc_html__('You must be logged in to update your profile.', 'realestate-booking-suite')
            ));
        }

        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_update_profile')) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed.', 'realestate-booking-suite')
            ));
        }

        // Sanitize form data
        $form_data = array();
        parse_str($_POST['form_data'], $form_data);
        
        $sanitized_data = array(
            'first_name' => RESBS_Security::sanitize_text($form_data['first_name'] ?? ''),
            'last_name' => RESBS_Security::sanitize_text($form_data['last_name'] ?? ''),
            'email' => RESBS_Security::sanitize_email($form_data['email'] ?? ''),
            'phone' => RESBS_Security::sanitize_text($form_data['phone'] ?? '')
        );

        $user_id = get_current_user_id();
        $user = get_userdata($user_id);

        // Check if user can edit this profile (own profile or has edit_users capability)
        $target_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : $user_id;
        if ($target_user_id !== $user_id && !current_user_can('edit_users')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to update this profile.', 'realestate-booking-suite')
            ));
        }

        // Use target user ID if provided and user has permission
        if ($target_user_id !== $user_id && current_user_can('edit_users')) {
            $user_id = $target_user_id;
            $user = get_userdata($user_id);
            if (!$user) {
                wp_send_json_error(array(
                    'message' => esc_html__('Invalid user ID.', 'realestate-booking-suite')
                ));
            }
        }

        // Update user data
        $user_data = array(
            'ID' => $user_id,
            'first_name' => $sanitized_data['first_name'],
            'last_name' => $sanitized_data['last_name'],
            'user_email' => $sanitized_data['email']
        );

        $result = wp_update_user($user_data);

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to update profile. Please try again.', 'realestate-booking-suite')
            ));
        }

        // Update phone meta
        update_user_meta($user_id, 'phone', $sanitized_data['phone']);

        wp_send_json_success(array(
            'message' => esc_html__('Profile updated successfully.', 'realestate-booking-suite')
        ));
    }

    /**
     * Submit property AJAX handler
     */
    public function submit_property() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => esc_html__('You must be logged in to submit a property.', 'realestate-booking-suite')
            ));
        }

        // Allow any logged-in user to submit properties
        // Removed the edit_posts capability check to allow all users to submit

        // Verify nonce - check both possible nonce field names
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : (isset($_POST['resbs_submit_nonce']) ? $_POST['resbs_submit_nonce'] : '');
        if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_submit_property')) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite')
            ));
        }

        // Sanitize form data
        $sanitized_data = array(
            'property_title' => RESBS_Security::sanitize_text($_POST['property_title'] ?? ''),
            'property_type' => sanitize_text_field($_POST['property_type'] ?? ''),
            'property_price' => RESBS_Security::sanitize_int($_POST['property_price'] ?? 0),
            'property_price_per_sqft' => floatval($_POST['property_price_per_sqft'] ?? 0),
            'property_price_note' => RESBS_Security::sanitize_text($_POST['property_price_note'] ?? ''),
            'property_call_for_price' => isset($_POST['property_call_for_price']) ? 1 : 0,
            'property_size' => RESBS_Security::sanitize_int($_POST['property_size'] ?? 0),
            'property_lot_size_sqft' => RESBS_Security::sanitize_int($_POST['property_lot_size_sqft'] ?? 0),
            'property_bedrooms' => floatval($_POST['property_bedrooms'] ?? 0),
            'property_bathrooms' => floatval($_POST['property_bathrooms'] ?? 0),
            'property_half_baths' => intval($_POST['property_half_baths'] ?? 0),
            'property_total_rooms' => intval($_POST['property_total_rooms'] ?? 0),
            'property_floors' => intval($_POST['property_floors'] ?? 0),
            'property_floor_level' => RESBS_Security::sanitize_text($_POST['property_floor_level'] ?? ''),
            'property_year_built' => intval($_POST['property_year_built'] ?? 0),
            'property_year_remodeled' => intval($_POST['property_year_remodeled'] ?? 0),
            'property_status' => sanitize_text_field($_POST['property_status'] ?? ''),
            'property_condition' => sanitize_text_field($_POST['property_condition'] ?? ''),
            'property_description' => RESBS_Security::sanitize_textarea($_POST['property_description'] ?? ''),
            'property_address' => RESBS_Security::sanitize_text($_POST['property_address'] ?? ''),
            'property_city' => RESBS_Security::sanitize_text($_POST['property_city'] ?? ''),
            'property_state' => RESBS_Security::sanitize_text($_POST['property_state'] ?? ''),
            'property_zip' => RESBS_Security::sanitize_text($_POST['property_zip'] ?? ''),
            'property_country' => RESBS_Security::sanitize_text($_POST['property_country'] ?? ''),
            'property_latitude' => floatval($_POST['property_latitude'] ?? 0),
            'property_longitude' => floatval($_POST['property_longitude'] ?? 0),
            'property_hide_address' => isset($_POST['property_hide_address']) ? 1 : 0,
            'property_features' => RESBS_Security::sanitize_text($_POST['property_features'] ?? ''),
            'property_amenities' => RESBS_Security::sanitize_text($_POST['property_amenities'] ?? ''),
            'property_parking' => RESBS_Security::sanitize_text($_POST['property_parking'] ?? ''),
            'property_heating' => RESBS_Security::sanitize_text($_POST['property_heating'] ?? ''),
            'property_cooling' => RESBS_Security::sanitize_text($_POST['property_cooling'] ?? ''),
            'property_basement' => RESBS_Security::sanitize_text($_POST['property_basement'] ?? ''),
            'property_roof' => RESBS_Security::sanitize_text($_POST['property_roof'] ?? ''),
            'property_exterior_material' => RESBS_Security::sanitize_text($_POST['property_exterior_material'] ?? ''),
            'property_floor_covering' => RESBS_Security::sanitize_text($_POST['property_floor_covering'] ?? ''),
            'property_nearby_schools' => RESBS_Security::sanitize_textarea($_POST['property_nearby_schools'] ?? ''),
            'property_nearby_shopping' => RESBS_Security::sanitize_textarea($_POST['property_nearby_shopping'] ?? ''),
            'property_nearby_restaurants' => RESBS_Security::sanitize_textarea($_POST['property_nearby_restaurants'] ?? ''),
            'property_video_url' => esc_url_raw($_POST['property_video_url'] ?? ''),
            'property_video_embed' => wp_kses_post($_POST['property_video_embed'] ?? ''),
            'property_virtual_tour' => esc_url_raw($_POST['property_virtual_tour'] ?? ''),
            'property_agent_name' => RESBS_Security::sanitize_text($_POST['property_agent_name'] ?? ''),
            'property_agent_phone' => RESBS_Security::sanitize_text($_POST['property_agent_phone'] ?? ''),
            'property_agent_email' => sanitize_email($_POST['property_agent_email'] ?? ''),
            'property_agent_experience' => intval($_POST['property_agent_experience'] ?? 0),
            'property_agent_response_time' => RESBS_Security::sanitize_text($_POST['property_agent_response_time'] ?? '')
        );

        // Validate required fields
        if (empty($sanitized_data['property_title'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Property title is required.', 'realestate-booking-suite')
            ));
        }

        if (empty($sanitized_data['property_type'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Property type is required.', 'realestate-booking-suite')
            ));
        }

        if (empty($sanitized_data['property_price'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Property price is required.', 'realestate-booking-suite')
            ));
        }

        if (empty($sanitized_data['property_description'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Property description is required.', 'realestate-booking-suite')
            ));
        }

        // Create property post
        $post_data = array(
            'post_title' => $sanitized_data['property_title'],
            'post_content' => $sanitized_data['property_description'],
            'post_status' => 'pending', // Require admin approval
            'post_type' => 'property',
            'post_author' => get_current_user_id()
        );

        $property_id = wp_insert_post($post_data);

        if (is_wp_error($property_id)) {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to create property. Please try again.', 'realestate-booking-suite')
            ));
        }

        // Save all meta data
        update_post_meta($property_id, '_property_price', $sanitized_data['property_price']);
        if ($sanitized_data['property_price_per_sqft']) {
            update_post_meta($property_id, '_property_price_per_sqft', $sanitized_data['property_price_per_sqft']);
        }
        if ($sanitized_data['property_price_note']) {
            update_post_meta($property_id, '_property_price_note', $sanitized_data['property_price_note']);
        }
        update_post_meta($property_id, '_property_call_for_price', $sanitized_data['property_call_for_price']);
        
        // Size and lot size
        if ($sanitized_data['property_size']) {
            update_post_meta($property_id, '_property_size', $sanitized_data['property_size']);
            update_post_meta($property_id, '_property_area_sqft', $sanitized_data['property_size']);
        }
        if ($sanitized_data['property_lot_size_sqft']) {
            update_post_meta($property_id, '_property_lot_size_sqft', $sanitized_data['property_lot_size_sqft']);
        }
        
        // Property details
        if ($sanitized_data['property_bedrooms']) {
            update_post_meta($property_id, '_property_bedrooms', $sanitized_data['property_bedrooms']);
        }
        if ($sanitized_data['property_bathrooms']) {
            update_post_meta($property_id, '_property_bathrooms', $sanitized_data['property_bathrooms']);
        }
        if ($sanitized_data['property_half_baths']) {
            update_post_meta($property_id, '_property_half_baths', $sanitized_data['property_half_baths']);
        }
        if ($sanitized_data['property_total_rooms']) {
            update_post_meta($property_id, '_property_total_rooms', $sanitized_data['property_total_rooms']);
        }
        if ($sanitized_data['property_floors']) {
            update_post_meta($property_id, '_property_floors', $sanitized_data['property_floors']);
        }
        if ($sanitized_data['property_floor_level']) {
            update_post_meta($property_id, '_property_floor_level', $sanitized_data['property_floor_level']);
        }
        if ($sanitized_data['property_year_built']) {
            update_post_meta($property_id, '_property_year_built', $sanitized_data['property_year_built']);
        }
        if ($sanitized_data['property_year_remodeled']) {
            update_post_meta($property_id, '_property_year_remodeled', $sanitized_data['property_year_remodeled']);
        }
        if ($sanitized_data['property_condition']) {
            update_post_meta($property_id, '_property_condition', $sanitized_data['property_condition']);
        }
        
        // Location
        if ($sanitized_data['property_address']) {
            update_post_meta($property_id, '_property_address', $sanitized_data['property_address']);
        }
        if ($sanitized_data['property_city']) {
            update_post_meta($property_id, '_property_city', $sanitized_data['property_city']);
        }
        if ($sanitized_data['property_state']) {
            update_post_meta($property_id, '_property_state', $sanitized_data['property_state']);
        }
        if ($sanitized_data['property_zip']) {
            update_post_meta($property_id, '_property_zip', $sanitized_data['property_zip']);
        }
        if ($sanitized_data['property_country']) {
            update_post_meta($property_id, '_property_country', $sanitized_data['property_country']);
        }
        if ($sanitized_data['property_latitude']) {
            update_post_meta($property_id, '_property_latitude', $sanitized_data['property_latitude']);
        }
        if ($sanitized_data['property_longitude']) {
            update_post_meta($property_id, '_property_longitude', $sanitized_data['property_longitude']);
        }
        update_post_meta($property_id, '_property_hide_address', $sanitized_data['property_hide_address']);
        
        // Features and amenities - SAVE AS STRING, NOT ARRAY to prevent errors
        if (!empty($sanitized_data['property_features'])) {
            // Ensure it's a string before sanitizing
            $features_value = $sanitized_data['property_features'];
            if (is_array($features_value)) {
                $features_string = implode(', ', array_filter(array_map('trim', $features_value)));
            } else {
                $features_string = (string)$features_value;
            }
            // Now sanitize the string
            $features_string = sanitize_text_field($features_string);
            if (!empty($features_string)) {
                update_post_meta($property_id, '_property_features', $features_string);
            }
        }
        if (!empty($sanitized_data['property_amenities'])) {
            // Ensure it's a string before sanitizing
            $amenities_value = $sanitized_data['property_amenities'];
            if (is_array($amenities_value)) {
                $amenities_string = implode(', ', array_filter(array_map('trim', $amenities_value)));
            } else {
                $amenities_string = (string)$amenities_value;
            }
            // Now sanitize the string
            $amenities_string = sanitize_text_field($amenities_string);
            if (!empty($amenities_string)) {
                update_post_meta($property_id, '_property_amenities', $amenities_string);
            }
        }
        if ($sanitized_data['property_parking']) {
            update_post_meta($property_id, '_property_parking', $sanitized_data['property_parking']);
        }
        if ($sanitized_data['property_heating']) {
            update_post_meta($property_id, '_property_heating', $sanitized_data['property_heating']);
        }
        if ($sanitized_data['property_cooling']) {
            update_post_meta($property_id, '_property_cooling', $sanitized_data['property_cooling']);
        }
        if ($sanitized_data['property_basement']) {
            update_post_meta($property_id, '_property_basement', $sanitized_data['property_basement']);
        }
        if ($sanitized_data['property_roof']) {
            update_post_meta($property_id, '_property_roof', $sanitized_data['property_roof']);
        }
        if ($sanitized_data['property_exterior_material']) {
            update_post_meta($property_id, '_property_exterior_material', $sanitized_data['property_exterior_material']);
        }
        if ($sanitized_data['property_floor_covering']) {
            update_post_meta($property_id, '_property_floor_covering', $sanitized_data['property_floor_covering']);
        }
        
        // Nearby features
        if ($sanitized_data['property_nearby_schools']) {
            update_post_meta($property_id, '_property_nearby_schools', $sanitized_data['property_nearby_schools']);
        }
        if ($sanitized_data['property_nearby_shopping']) {
            update_post_meta($property_id, '_property_nearby_shopping', $sanitized_data['property_nearby_shopping']);
        }
        if ($sanitized_data['property_nearby_restaurants']) {
            update_post_meta($property_id, '_property_nearby_restaurants', $sanitized_data['property_nearby_restaurants']);
        }
        
        // Video and virtual tour
        if ($sanitized_data['property_video_url']) {
            update_post_meta($property_id, '_property_video_url', $sanitized_data['property_video_url']);
        }
        if ($sanitized_data['property_video_embed']) {
            update_post_meta($property_id, '_property_video_embed', $sanitized_data['property_video_embed']);
        }
        if ($sanitized_data['property_virtual_tour']) {
            update_post_meta($property_id, '_property_virtual_tour', $sanitized_data['property_virtual_tour']);
        }
        
        // Agent information
        if ($sanitized_data['property_agent_name']) {
            update_post_meta($property_id, '_property_agent_name', $sanitized_data['property_agent_name']);
        }
        if ($sanitized_data['property_agent_phone']) {
            update_post_meta($property_id, '_property_agent_phone', $sanitized_data['property_agent_phone']);
        }
        if ($sanitized_data['property_agent_email']) {
            update_post_meta($property_id, '_property_agent_email', $sanitized_data['property_agent_email']);
        }
        if ($sanitized_data['property_agent_experience']) {
            update_post_meta($property_id, '_property_agent_experience', $sanitized_data['property_agent_experience']);
        }
        if ($sanitized_data['property_agent_response_time']) {
            update_post_meta($property_id, '_property_agent_response_time', $sanitized_data['property_agent_response_time']);
        }

        // Set taxonomies - handle both term ID and text
        if (!empty($sanitized_data['property_type'])) {
            $property_type = trim($sanitized_data['property_type']);
            
            // Check if it's a number (term ID) or text
            if (is_numeric($property_type)) {
                // Term ID format
                wp_set_post_terms($property_id, array(intval($property_type)), 'property_type');
            } else {
                // Text format - create term if doesn't exist
                $term = term_exists($property_type, 'property_type');
                if ($term === 0 || $term === null) {
                    // Create new term
                    $term = wp_insert_term($property_type, 'property_type');
                    if (!is_wp_error($term)) {
                        wp_set_post_terms($property_id, array($term['term_id']), 'property_type');
                    }
                } else {
                    // Use existing term
                    $term_id = is_array($term) ? $term['term_id'] : $term;
                    wp_set_post_terms($property_id, array($term_id), 'property_type');
                }
            }
        }

        // Set property status taxonomy
        if (!empty($sanitized_data['property_status'])) {
            $property_status = trim($sanitized_data['property_status']);
            if (is_numeric($property_status)) {
                wp_set_post_terms($property_id, array(intval($property_status)), 'property_status');
            }
        }
        
        // Handle location - save to taxonomy if city is provided
        if (!empty($sanitized_data['property_city'])) {
            $location_text = trim($sanitized_data['property_city']);
            $term = term_exists($location_text, 'property_location');
            if ($term === 0 || $term === null) {
                // Create new term
                $term = wp_insert_term($location_text, 'property_location');
                if (!is_wp_error($term)) {
                    wp_set_post_terms($property_id, array($term['term_id']), 'property_location');
                }
            } else {
                // Use existing term
                $term_id = is_array($term) ? $term['term_id'] : $term;
                wp_set_post_terms($property_id, array($term_id), 'property_location');
            }
        }

        // Check if files are being uploaded
        $has_files = (!empty($_FILES['property_featured_image']) && $_FILES['property_featured_image']['error'] === UPLOAD_ERR_OK) ||
                     (!empty($_FILES['property_gallery'])) ||
                     (!empty($_FILES['property_agent_photo']) && $_FILES['property_agent_photo']['error'] === UPLOAD_ERR_OK);
        
        // Allow any logged-in user to upload files for their properties
        if ($has_files && !is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => esc_html__('You must be logged in to upload files.', 'realestate-booking-suite')
            ));
        }

        // Handle featured image upload
        if (!empty($_FILES['property_featured_image']) && $_FILES['property_featured_image']['error'] === UPLOAD_ERR_OK) {
            // Validate file before upload
            $validation = RESBS_Security::validate_file_upload($_FILES['property_featured_image']);
            if (is_wp_error($validation)) {
                wp_send_json_error(array(
                    'message' => esc_html($validation->get_error_message())
                ));
            }
            $this->handle_featured_image_upload($property_id, $_FILES['property_featured_image']);
        }
        
        // Handle file uploads
        if (!empty($_FILES['property_gallery'])) {
            // Validate each file in gallery
            if (is_array($_FILES['property_gallery']['name'])) {
                foreach ($_FILES['property_gallery']['name'] as $key => $name) {
                    if (!empty($name)) {
                        $file = array(
                            'name' => $_FILES['property_gallery']['name'][$key],
                            'type' => $_FILES['property_gallery']['type'][$key],
                            'tmp_name' => $_FILES['property_gallery']['tmp_name'][$key],
                            'error' => $_FILES['property_gallery']['error'][$key],
                            'size' => $_FILES['property_gallery']['size'][$key]
                        );
                        $validation = RESBS_Security::validate_file_upload($file);
                        if (is_wp_error($validation)) {
                            wp_send_json_error(array(
                                'message' => sprintf(esc_html__('Invalid file in gallery: %s', 'realestate-booking-suite'), esc_html($validation->get_error_message()))
                            ));
                        }
                    }
                }
            }
            $this->handle_property_gallery_upload($property_id, $_FILES['property_gallery']);
        }
        
        // Handle agent photo upload
        if (!empty($_FILES['property_agent_photo']) && $_FILES['property_agent_photo']['error'] === UPLOAD_ERR_OK) {
            // Validate file before upload
            $validation = RESBS_Security::validate_file_upload($_FILES['property_agent_photo']);
            if (is_wp_error($validation)) {
                wp_send_json_error(array(
                    'message' => esc_html($validation->get_error_message())
                ));
            }
            $this->handle_agent_photo_upload($property_id, $_FILES['property_agent_photo']);
        }

        // Get profile page URL for viewing properties
        $profile_url = resbs_get_profile_page_url();
        $view_properties_link = '';
        if ($profile_url) {
            $view_properties_link = '<br><a href="' . esc_url($profile_url) . '" style="color: #0073aa; text-decoration: underline; margin-top: 10px; display: inline-block;">' . esc_html__('View My Properties', 'realestate-booking-suite') . '</a>';
        }
        
        wp_send_json_success(array(
            'message' => esc_html__('Property submitted successfully. It will be reviewed before being published.', 'realestate-booking-suite') . wp_kses_post($view_properties_link),
            'property_id' => intval($property_id),
            'profile_url' => esc_url($profile_url)
        ));
    }

    /**
     * Clear favorites AJAX handler
     */
    public function clear_favorites() {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => esc_html__('You must be logged in to manage favorites.', 'realestate-booking-suite')
            ));
        }

        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_favorites_nonce')) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed.', 'realestate-booking-suite')
            ));
        }

        $user_id = RESBS_Security::sanitize_int($_POST['user_id'] ?? 0);

        if ($user_id !== get_current_user_id()) {
            wp_send_json_error(array(
                'message' => esc_html__('Unauthorized action.', 'realestate-booking-suite')
            ));
        }

        // Clear favorites
        delete_user_meta($user_id, 'resbs_favorite_properties');

        wp_send_json_success(array(
            'message' => esc_html__('All favorites cleared successfully.', 'realestate-booking-suite')
        ));
    }

    /**
     * Load more properties AJAX handler
     */
    public function load_more_properties() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_widget_filter')) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed.', 'realestate-booking-suite')
            ));
        }

        $page = RESBS_Security::sanitize_int($_POST['page'] ?? 2);
        $settings = RESBS_Security::sanitize_array($_POST['settings'] ?? array());

        // Build query args
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $settings['posts_per_page'] ?? 12,
            'paged' => $page,
            'orderby' => $settings['orderby'] ?? 'date',
            'order' => $settings['order'] ?? 'DESC',
        );

        // Add meta query for featured properties
        if (!empty($settings['featured_only'])) {
            $query_args['meta_query'] = array(
                array(
                    'key' => '_property_featured',
                    'value' => 'yes',
                    'compare' => '='
                )
            );
        }

        // Add taxonomy query
        $tax_query = array();
        
        if (!empty($settings['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $settings['property_type']
            );
        }

        if (!empty($settings['property_status'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $settings['property_status']
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        // Execute query
        $properties = new WP_Query($query_args);

        // Generate HTML
        ob_start();
        
        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $this->render_property_card($settings);
            }
            wp_reset_postdata();
        }
        
        $html = ob_get_clean();

        $has_more = $page < $properties->max_num_pages;

        wp_send_json_success(array(
            'html' => $html,
            'has_more' => (bool) $has_more,
            'current_page' => intval($page),
            'max_pages' => intval($properties->max_num_pages)
        ));
    }

    /**
     * Render property card
     * 
     * @param array $settings Widget settings
     */
    private function render_property_card($settings) {
        $property_id = get_the_ID();
        $property_price = get_post_meta($property_id, '_property_price', true);
        $property_bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $property_bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        $property_size = get_post_meta($property_id, '_property_size', true);
        $property_location = get_the_terms($property_id, 'property_location');
        
        // Sanitize settings array
        $settings = wp_parse_args($settings, array(
            'layout' => 'grid',
            'show_price' => false,
            'show_meta' => false,
            'show_excerpt' => false,
            'show_badges' => false,
            'show_favorite_button' => false,
            'show_book_button' => false
        ));

        ?>
        <div class="resbs-property-card resbs-layout-<?php echo esc_attr($settings['layout'] ?? 'grid'); ?>">
            <div class="resbs-property-image">
                <?php if (has_post_thumbnail()): ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <?php the_post_thumbnail('medium', array('alt' => esc_attr(get_the_title()))); ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <img src="<?php echo esc_url(RESBS_URL . 'assets/images/placeholder.jpg'); ?>" 
                             alt="<?php esc_attr_e('Property Image', 'realestate-booking-suite'); ?>">
                    </a>
                <?php endif; ?>

                <?php if (!empty($settings['show_badges'])): ?>
                    <?php do_action('resbs_property_badges', $property_id, 'shortcode'); ?>
                <?php endif; ?>

                <?php if (!empty($settings['show_favorite_button'])): ?>
                    <div class="resbs-property-actions">
                        <button type="button" class="resbs-favorite-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
                            <span class="dashicons dashicons-heart"></span>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="resbs-property-content">
                <h3 class="resbs-property-title">
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <?php echo esc_html(get_the_title()); ?>
                    </a>
                </h3>

                <?php if (!empty($settings['show_price']) && !empty($property_price)): ?>
                    <div class="resbs-property-price">
                        <?php 
                        $formatted_price = resbs_format_price($property_price);
                        echo esc_html($formatted_price); 
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($settings['show_meta'])): ?>
                    <div class="resbs-property-meta">
                        <?php if (!empty($property_bedrooms)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-bed-alt"></span>
                                <span><?php echo esc_html(floatval($property_bedrooms)); ?> <?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_bathrooms)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-bath"></span>
                                <span><?php echo esc_html(floatval($property_bathrooms)); ?> <?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_size)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-admin-home"></span>
                                <span><?php echo esc_html(intval($property_size)); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_location) && !is_wp_error($property_location) && isset($property_location[0])): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-location"></span>
                                <span><?php echo esc_html($property_location[0]->name); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($settings['show_excerpt'])): ?>
                    <div class="resbs-property-excerpt">
                        <?php echo wp_kses_post(wp_trim_words(get_the_excerpt(), 20)); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($settings['show_book_button'])): ?>
                    <div class="resbs-property-actions">
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="resbs-property-btn primary">
                            <?php esc_html_e('View Details', 'realestate-booking-suite'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Handle property gallery upload
     * 
     * @param int $property_id Property ID
     * @param array $files Uploaded files
     */
    private function handle_property_gallery_upload($property_id, $files) {
        // Verify user owns the property or has edit capability
        $property = get_post($property_id);
        if (!$property) {
            return;
        }
        
        $current_user_id = get_current_user_id();
        if ($property->post_author != $current_user_id && !current_user_can('edit_post', $property_id)) {
            return;
        }

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }

        $uploaded_attachment_ids = array();
        
        foreach ($files['name'] as $key => $value) {
            if ($files['name'][$key]) {
                $file = array(
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                );

                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($file, $upload_overrides);

                if ($movefile && !isset($movefile['error'])) {
                    // Create attachment post
                    $attachment = array(
                        'post_mime_type' => $movefile['type'],
                        'post_title'     => sanitize_file_name(pathinfo($movefile['file'], PATHINFO_FILENAME)),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );
                    
                    $attach_id = wp_insert_attachment($attachment, $movefile['file'], $property_id);
                    
                    if (!is_wp_error($attach_id)) {
                        // Generate attachment metadata
                        $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                        
                        $uploaded_attachment_ids[] = $attach_id;
                    }
                }
            }
        }

        if (!empty($uploaded_attachment_ids)) {
            // Merge with existing gallery if any
            $existing_gallery = get_post_meta($property_id, '_property_gallery', true);
            if (is_array($existing_gallery)) {
                $uploaded_attachment_ids = array_merge($existing_gallery, $uploaded_attachment_ids);
            }
            update_post_meta($property_id, '_property_gallery', $uploaded_attachment_ids);
        }
    }
    
    /**
     * Handle featured image upload
     * 
     * @param int $property_id Property post ID
     * @param array $file Uploaded file
     */
    private function handle_featured_image_upload($property_id, $file) {
        // Verify user owns the property or has edit capability
        $property = get_post($property_id);
        if (!$property) {
            return;
        }
        
        $current_user_id = get_current_user_id();
        if ($property->post_author != $current_user_id && !current_user_can('edit_post', $property_id)) {
            return;
        }

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }

        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            // Create attachment post
            $attachment = array(
                'post_mime_type' => $movefile['type'],
                'post_title'     => sanitize_file_name(pathinfo($movefile['file'], PATHINFO_FILENAME)),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $movefile['file'], $property_id);
            
            if (!is_wp_error($attach_id)) {
                // Generate attachment metadata
                $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
                
                // Set as featured image (post thumbnail)
                set_post_thumbnail($property_id, $attach_id);
            }
        }
    }

    /**
     * Handle agent photo upload
     * 
     * @param int $property_id Property post ID
     * @param array $file Uploaded file
     */
    private function handle_agent_photo_upload($property_id, $file) {
        // Verify user owns the property or has edit capability
        $property = get_post($property_id);
        if (!$property) {
            return;
        }
        
        $current_user_id = get_current_user_id();
        if ($property->post_author != $current_user_id && !current_user_can('edit_post', $property_id)) {
            return;
        }

        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }

        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            // Create attachment post
            $attachment = array(
                'post_mime_type' => $movefile['type'],
                'post_title'     => sanitize_file_name(pathinfo($movefile['file'], PATHINFO_FILENAME)),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $movefile['file'], $property_id);
            
            if (!is_wp_error($attach_id)) {
                // Generate attachment metadata
                $attach_data = wp_generate_attachment_metadata($attach_id, $movefile['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
                
                // Save attachment URL to post meta
                $image_url = wp_get_attachment_image_url($attach_id, 'full');
                if ($image_url) {
                    update_post_meta($property_id, '_property_agent_photo', esc_url_raw($image_url));
                }
            }
        }
    }
    
    /**
     * Publish a pending property
     */
    public function publish_property() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_publish_property')) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed.', 'realestate-booking-suite')
            ));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => esc_html__('You must be logged in to publish properties.', 'realestate-booking-suite')
            ));
        }
        
        // Get property ID
        $property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;
        
        if (!$property_id) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid property ID.', 'realestate-booking-suite')
            ));
        }
        
        // Get property
        $property = get_post($property_id);
        
        if (!$property || $property->post_type !== 'property') {
            wp_send_json_error(array(
                'message' => esc_html__('Property not found.', 'realestate-booking-suite')
            ));
        }
        
        // Check if user owns the property or has publish_posts capability
        $current_user_id = get_current_user_id();
        $property_author_id = intval($property->post_author);
        
        // Allow if user has publish_posts capability OR if user owns the property
        if (!current_user_can('publish_posts') && $current_user_id !== $property_author_id) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to publish this property.', 'realestate-booking-suite')
            ));
        }
        
        // Update post status to publish
        $updated = wp_update_post(array(
            'ID' => $property_id,
            'post_status' => 'publish'
        ));
        
        if (is_wp_error($updated)) {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to publish property.', 'realestate-booking-suite')
            ));
        }
        
        wp_send_json_success(array(
            'message' => esc_html__('Property published successfully!', 'realestate-booking-suite'),
            'property_id' => intval($property_id)
        ));
    }

}

// Initialize AJAX handlers
new RESBS_Shortcode_AJAX();
