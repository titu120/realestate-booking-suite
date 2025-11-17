<?php
/**
 * Property Metabox AJAX Handlers
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Metabox_AJAX {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_resbs_get_attachment_data', array($this, 'get_attachment_data'));
        add_action('wp_ajax_resbs_auto_save_property', array($this, 'auto_save_property'));
        add_action('wp_ajax_resbs_validate_property_data', array($this, 'validate_property_data'));
        add_action('wp_ajax_resbs_duplicate_property', array($this, 'duplicate_property'));
        add_action('wp_ajax_resbs_get_property_preview', array($this, 'get_property_preview'));
    }

    /**
     * Get attachment data for display
     */
    public function get_attachment_data() {
        check_ajax_referer('resbs_metabox_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to access this data.', 'realestate-booking-suite')
            ));
        }
        
        $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
        
        if (!$attachment_id) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid attachment ID.', 'realestate-booking-suite')
            ));
        }
        
        $attachment = get_post($attachment_id);
        
        if (!$attachment || $attachment->post_type !== 'attachment') {
            wp_send_json_error(array(
                'message' => esc_html__('Attachment not found.', 'realestate-booking-suite')
            ));
        }
        
        $data = array(
            'id' => $attachment_id,
            'title' => esc_html($attachment->post_title),
            'alt' => esc_attr(get_post_meta($attachment_id, '_wp_attachment_image_alt', true)),
            'caption' => esc_html($attachment->post_excerpt),
            'description' => wp_kses_post($attachment->post_content),
            'url' => esc_url(wp_get_attachment_url($attachment_id)),
            'thumbnail' => esc_url(wp_get_attachment_image_url($attachment_id, 'thumbnail')),
            'medium' => esc_url(wp_get_attachment_image_url($attachment_id, 'medium')),
            'large' => esc_url(wp_get_attachment_image_url($attachment_id, 'large')),
            'full' => esc_url(wp_get_attachment_image_url($attachment_id, 'full'))
        );
        
        wp_send_json_success($data);
    }

    /**
     * Auto-save property data
     */
    public function auto_save_property() {
        check_ajax_referer('resbs_metabox_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to save posts.', 'realestate-booking-suite')
            ));
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $form_data = isset($_POST['form_data']) ? sanitize_text_field($_POST['form_data']) : '';
        
        if (!$post_id || get_post_type($post_id) !== 'property') {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid property ID.', 'realestate-booking-suite')
            ));
        }
        
        // Check if user can edit this specific post
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to edit this property.', 'realestate-booking-suite')
            ));
        }
        
        // Parse form data
        parse_str($form_data, $data);
        
        // Sanitize parsed data
        $data = array_map(function($value) {
            if (is_array($value)) {
                return array_map('sanitize_text_field', $value);
            }
            return sanitize_text_field($value);
        }, $data);
        
        // Save basic post data
        if (isset($data['post_title'])) {
            $update_data = array(
                'ID' => $post_id,
                'post_title' => sanitize_text_field($data['post_title'])
            );
            
            if (isset($data['content'])) {
                $update_data['post_content'] = wp_kses_post($data['content']);
            }
            
            if (isset($data['excerpt'])) {
                $update_data['post_excerpt'] = sanitize_textarea_field($data['excerpt']);
            }
            
            wp_update_post($update_data);
        }
        
        // Save property meta fields
        $meta_fields = array(
            'property_price', 'property_price_per_sqft', 'property_price_note', 'property_call_for_price',
            'property_bedrooms', 'property_bathrooms', 'property_half_baths', 'property_total_rooms',
            'property_floors', 'property_floor_level', 'property_area_sqft', 'property_lot_size_sqft',
            'property_year_built', 'property_year_remodeled', 'property_type', 'property_status', 'property_condition',
            'property_address', 'property_city', 'property_state', 'property_zip', 'property_country',
            'property_latitude', 'property_longitude', 'property_hide_address',
            'property_features', 'property_amenities', 'property_parking', 'property_heating', 'property_cooling',
            'property_basement', 'property_roof', 'property_exterior_material', 'property_floor_covering',
            'property_featured', 'property_new', 'property_sold', 'property_foreclosure', 'property_open_house',
            'property_booking_enabled', 'property_min_stay', 'property_max_stay', 'property_check_in_time',
            'property_check_out_time', 'property_cancellation_policy', 'property_virtual_tour',
            'property_video_url', 'property_video_embed'
        );
        
        foreach ($meta_fields as $field) {
            if (isset($data[$field])) {
                $value = sanitize_text_field($data[$field]);
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
        
        wp_send_json_success(array(
            'message' => esc_html__('Property auto-saved successfully.', 'realestate-booking-suite'),
            'timestamp' => current_time('mysql')
        ));
    }

    /**
     * Validate property data
     */
    public function validate_property_data() {
        check_ajax_referer('resbs_metabox_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to validate property data.', 'realestate-booking-suite')
            ));
        }
        
        // Check specific post edit permission if post_id is provided
        if (isset($_POST['post_id']) && !empty($_POST['post_id'])) {
            $post_id = intval($_POST['post_id']);
            if ($post_id && !current_user_can('edit_post', $post_id)) {
                wp_send_json_error(array(
                    'message' => esc_html__('You do not have permission to edit this property.', 'realestate-booking-suite')
                ));
            }
        }
        
        $errors = array();
        // Sanitize input data while preserving structure for validation
        $data = array();
        foreach ($_POST as $key => $value) {
            if (is_array($value)) {
                $data[$key] = array_map('sanitize_text_field', $value);
            } else {
                $data[$key] = sanitize_text_field($value);
            }
        }
        
        // Validate required fields
        $required_fields = array(
            'property_price' => esc_html__('Property price is required.', 'realestate-booking-suite'),
            'property_bedrooms' => esc_html__('Number of bedrooms is required.', 'realestate-booking-suite'),
            'property_bathrooms' => esc_html__('Number of bathrooms is required.', 'realestate-booking-suite'),
            'property_area_sqft' => esc_html__('Property area is required.', 'realestate-booking-suite'),
            'property_address' => esc_html__('Property address is required.', 'realestate-booking-suite'),
            'property_city' => esc_html__('City is required.', 'realestate-booking-suite')
        );
        
        foreach ($required_fields as $field => $message) {
            if (empty($data[$field])) {
                $errors[$field] = $message;
            }
        }
        
        // Validate numeric fields
        $numeric_fields = array(
            'property_price' => esc_html__('Price must be a valid number.', 'realestate-booking-suite'),
            'property_bedrooms' => esc_html__('Bedrooms must be a valid number.', 'realestate-booking-suite'),
            'property_bathrooms' => esc_html__('Bathrooms must be a valid number.', 'realestate-booking-suite'),
            'property_area_sqft' => esc_html__('Area must be a valid number.', 'realestate-booking-suite')
        );
        
        foreach ($numeric_fields as $field => $message) {
            if (!empty($data[$field]) && !is_numeric($data[$field])) {
                $errors[$field] = $message;
            }
        }
        
        // Validate email if provided
        if (!empty($data['property_contact_email']) && !is_email($data['property_contact_email'])) {
            $errors['property_contact_email'] = esc_html__('Please enter a valid email address.', 'realestate-booking-suite');
        }
        
        // Validate URL if provided
        if (!empty($data['property_video_url']) && !filter_var($data['property_video_url'], FILTER_VALIDATE_URL)) {
            $errors['property_video_url'] = esc_html__('Please enter a valid URL.', 'realestate-booking-suite');
        }
        
        if (empty($errors)) {
            wp_send_json_success(array(
                'message' => esc_html__('All data is valid.', 'realestate-booking-suite')
            ));
        } else {
            wp_send_json_error(array(
                'message' => esc_html__('Please fix the following errors:', 'realestate-booking-suite'),
                'errors' => $errors
            ));
        }
    }

    /**
     * Duplicate property
     */
    public function duplicate_property() {
        check_ajax_referer('resbs_metabox_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to duplicate posts.', 'realestate-booking-suite')
            ));
        }
        
        if (!current_user_can('publish_posts')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to create posts.', 'realestate-booking-suite')
            ));
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id || get_post_type($post_id) !== 'property') {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid property ID.', 'realestate-booking-suite')
            ));
        }
        
        // Check if user can edit the original post
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to duplicate this property.', 'realestate-booking-suite')
            ));
        }
        
        $original_post = get_post($post_id);
        
        if (!$original_post) {
            wp_send_json_error(array(
                'message' => esc_html__('Property not found.', 'realestate-booking-suite')
            ));
        }
        
        // Create new post
        $new_post = array(
            'post_title' => sanitize_text_field($original_post->post_title . ' (Copy)'),
            'post_content' => wp_kses_post($original_post->post_content),
            'post_excerpt' => sanitize_textarea_field($original_post->post_excerpt),
            'post_status' => 'draft',
            'post_type' => 'property',
            'post_author' => get_current_user_id()
        );
        
        $new_post_id = wp_insert_post($new_post);
        
        if (is_wp_error($new_post_id)) {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to create duplicate property.', 'realestate-booking-suite')
            ));
        }
        
        // Copy meta fields
        $meta_fields = get_post_meta($post_id);
        
        foreach ($meta_fields as $key => $values) {
            if (strpos($key, '_property_') === 0) {
                foreach ($values as $value) {
                    add_post_meta($new_post_id, $key, maybe_unserialize($value));
                }
            }
        }
        
        // Copy taxonomies
        $taxonomies = get_object_taxonomies('property');
        
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_object_terms($post_id, $taxonomy);
            if (!is_wp_error($terms) && !empty($terms)) {
                $term_ids = wp_list_pluck($terms, 'term_id');
                wp_set_object_terms($new_post_id, $term_ids, $taxonomy);
            }
        }
        
        wp_send_json_success(array(
            'message' => esc_html__('Property duplicated successfully.', 'realestate-booking-suite'),
            'new_post_id' => $new_post_id,
            'edit_url' => esc_url(get_edit_post_link($new_post_id))
        ));
    }

    /**
     * Get property preview data
     */
    public function get_property_preview() {
        check_ajax_referer('resbs_metabox_nonce', 'nonce');
        
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to view property preview.', 'realestate-booking-suite')
            ));
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        
        if (!$post_id || get_post_type($post_id) !== 'property') {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid property ID.', 'realestate-booking-suite')
            ));
        }
        
        // Check if user can edit this specific post
        if (!current_user_can('edit_post', $post_id)) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have permission to view this property.', 'realestate-booking-suite')
            ));
        }
        
        $property = get_post($post_id);
        $meta = get_post_meta($post_id);
        
        $preview_data = array(
            'title' => esc_html($property->post_title),
            'content' => wp_kses_post($property->post_content),
            'excerpt' => esc_html($property->post_excerpt),
            'price' => isset($meta['_property_price'][0]) ? esc_html($meta['_property_price'][0]) : '',
            'bedrooms' => isset($meta['_property_bedrooms'][0]) ? esc_html($meta['_property_bedrooms'][0]) : '',
            'bathrooms' => isset($meta['_property_bathrooms'][0]) ? esc_html($meta['_property_bathrooms'][0]) : '',
            'area' => isset($meta['_property_area_sqft'][0]) ? esc_html($meta['_property_area_sqft'][0]) : '',
            'address' => isset($meta['_property_address'][0]) ? esc_html($meta['_property_address'][0]) : '',
            'city' => isset($meta['_property_city'][0]) ? esc_html($meta['_property_city'][0]) : '',
            'state' => isset($meta['_property_state'][0]) ? esc_html($meta['_property_state'][0]) : '',
            'zip' => isset($meta['_property_zip'][0]) ? esc_html($meta['_property_zip'][0]) : '',
            'featured_image' => esc_url(get_the_post_thumbnail_url($post_id, 'medium')),
            'gallery' => isset($meta['_property_gallery'][0]) ? maybe_unserialize($meta['_property_gallery'][0]) : array(),
            'features' => isset($meta['_property_features'][0]) ? wp_kses_post($meta['_property_features'][0]) : '',
            'amenities' => isset($meta['_property_amenities'][0]) ? wp_kses_post($meta['_property_amenities'][0]) : '',
            'property_type' => isset($meta['_property_type'][0]) ? esc_html($meta['_property_type'][0]) : '',
            'property_status' => isset($meta['_property_status'][0]) ? esc_html($meta['_property_status'][0]) : '',
            'year_built' => isset($meta['_property_year_built'][0]) ? esc_html($meta['_property_year_built'][0]) : '',
            'virtual_tour' => isset($meta['_property_virtual_tour'][0]) ? esc_url($meta['_property_virtual_tour'][0]) : '',
            'video_url' => isset($meta['_property_video_url'][0]) ? esc_url($meta['_property_video_url'][0]) : ''
        );
        
        // Get gallery images
        if (!empty($preview_data['gallery'])) {
            $gallery_images = array();
            foreach ($preview_data['gallery'] as $image_id) {
                $gallery_images[] = array(
                    'id' => absint($image_id),
                    'url' => esc_url(wp_get_attachment_image_url($image_id, 'medium')),
                    'thumbnail' => esc_url(wp_get_attachment_image_url($image_id, 'thumbnail')),
                    'full' => esc_url(wp_get_attachment_image_url($image_id, 'full'))
                );
            }
            $preview_data['gallery_images'] = $gallery_images;
        }
        
        wp_send_json_success($preview_data);
    }
}
