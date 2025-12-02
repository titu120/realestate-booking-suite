<?php
/**
 * Frontend Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Frontend {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_action('wp_ajax_resbs_submit_property', array($this, 'handle_property_submission'));
        add_action('wp_ajax_nopriv_resbs_submit_property', array($this, 'handle_property_submission'));
        add_action('wp_ajax_resbs_upload_image', array($this, 'handle_image_upload'));
        add_action('wp_ajax_nopriv_resbs_upload_image', array($this, 'handle_image_upload'));
        add_action('wp_ajax_resbs_cancel_booking', array($this, 'handle_cancel_booking'));
        add_action('wp_ajax_resbs_refund_booking', array($this, 'handle_refund_booking'));
        add_action('wp_ajax_resbs_update_profile', array($this, 'handle_update_profile'));
        add_action('wp_ajax_resbs_elementor_load_properties', array($this, 'handle_elementor_load_properties'));
        add_action('wp_ajax_nopriv_resbs_elementor_load_properties', array($this, 'handle_elementor_load_properties'));
        add_action('wp_ajax_resbs_elementor_load_carousel_properties', array($this, 'handle_elementor_load_carousel_properties'));
        add_action('wp_ajax_nopriv_resbs_elementor_load_carousel_properties', array($this, 'handle_elementor_load_carousel_properties'));
        add_action('wp_ajax_resbs_toggle_favorite', array($this, 'handle_toggle_favorite'));
        add_action('wp_ajax_nopriv_resbs_toggle_favorite', array($this, 'handle_toggle_favorite'));
        
        // New widget AJAX handlers
        add_action('wp_ajax_resbs_elementor_submit_request', array($this, 'handle_elementor_submit_request'));
        add_action('wp_ajax_nopriv_resbs_elementor_submit_request', array($this, 'handle_elementor_submit_request'));
        add_action('wp_ajax_resbs_elementor_login', array($this, 'handle_elementor_login'));
        add_action('wp_ajax_nopriv_resbs_elementor_login', array($this, 'handle_elementor_login'));
        add_action('wp_ajax_resbs_elementor_logout', array($this, 'handle_elementor_logout'));
        add_action('wp_ajax_resbs_elementor_load_listings', array($this, 'handle_elementor_load_listings'));
        add_action('wp_ajax_nopriv_resbs_elementor_load_listings', array($this, 'handle_elementor_load_listings'));
        add_action('wp_ajax_resbs_elementor_load_map_properties', array($this, 'handle_elementor_load_map_properties'));
        add_action('wp_ajax_nopriv_resbs_elementor_load_map_properties', array($this, 'handle_elementor_load_map_properties'));
        
        // Frontend styles are now enqueued via wp_enqueue_style in enqueue_frontend_scripts()
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('resbs_submit_property', array($this, 'submit_property_shortcode'));
        add_shortcode('resbs_dashboard', array($this, 'dashboard_shortcode'));
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_script('jquery');
        wp_enqueue_media();
        
        // Enqueue frontend property features CSS
        wp_enqueue_style(
            'resbs-frontend-property-features',
            RESBS_URL . 'assets/css/frontend-property-features.css',
            array(),
            '1.0.0'
        );
        
        // Add dynamic inline styles for color customization
        $this->add_frontend_dynamic_styles();
        
        wp_enqueue_script(
            'resbs-frontend',
            RESBS_URL . 'assets/js/main.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('resbs-frontend', 'resbs_ajax', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('resbs_frontend_nonce')),
            'upload_nonce' => esc_js(wp_create_nonce('resbs_upload_nonce')),
            'messages' => array(
                'success' => esc_html__('Property submitted successfully!', 'realestate-booking-suite'),
                'error' => esc_html__('Error submitting property. Please try again.', 'realestate-booking-suite'),
                'validation_error' => esc_html__('Please fill in all required fields.', 'realestate-booking-suite'),
                'upload_error' => esc_html__('Error uploading file.', 'realestate-booking-suite'),
                'file_too_large' => esc_html__('File is too large.', 'realestate-booking-suite'),
                'invalid_file_type' => esc_html__('Invalid file type.', 'realestate-booking-suite'),
                'submitting' => esc_html__('Submitting...', 'realestate-booking-suite'),
                'submit_property' => esc_html__('Submit Property', 'realestate-booking-suite'),
                'update_property' => esc_html__('Update Property', 'realestate-booking-suite'),
                'select_gallery' => esc_html__('Select Gallery Images', 'realestate-booking-suite'),
                'add_to_gallery' => esc_html__('Add to Gallery', 'realestate-booking-suite'),
                'remove' => esc_html__('Remove', 'realestate-booking-suite')
            )
        ));
    }
    
    /**
     * Add dynamic inline styles for frontend
     */
    private function add_frontend_dynamic_styles() {
        $main_color = resbs_get_main_color();
        $secondary_color = resbs_get_secondary_color();
        
        $main_color_dark = $this->darken_color($main_color, 10);
        $secondary_color_dark = $this->darken_color($secondary_color, 10);
        
        $dynamic_css = "
        :root {
            --resbs-main-color: {$main_color};
            --resbs-secondary-color: {$secondary_color};
            --resbs-main-color-dark: {$main_color_dark};
            --resbs-secondary-color-dark: {$secondary_color_dark};
        }
        
        .resbs-btn-primary:hover,
        .resbs-save-button:hover,
        .resbs-submit-btn:hover,
        button.resbs-primary:hover {
            background-color: {$main_color_dark} !important;
            border-color: {$main_color_dark} !important;
        }
        
        .resbs-btn-secondary:hover,
        .resbs-view-btn:hover,
        .resbs-edit-btn:hover,
        button.resbs-secondary:hover {
            background-color: {$secondary_color_dark} !important;
            border-color: {$secondary_color_dark} !important;
        }
        ";
        
        wp_add_inline_style('resbs-frontend-property-features', $dynamic_css);
    }
    
    /**
     * Submit property shortcode
     */
    public function submit_property_shortcode($atts) {
        $atts = shortcode_atts(array(
            'edit_id' => 0,
            'show_title' => 'true'
        ), $atts);
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="resbs-message resbs-error">' . esc_html__('You must be logged in to submit a property.', 'realestate-booking-suite') . '</div>';
        }
        
        $edit_id = intval($atts['edit_id']);
        $property = null;
        
        // If editing, get the property
        if ($edit_id > 0) {
            $property = get_post($edit_id);
            if (!$property || $property->post_type !== 'property' || $property->post_author != get_current_user_id()) {
                return '<div class="resbs-message resbs-error">' . esc_html__('Property not found or you do not have permission to edit it.', 'realestate-booking-suite') . '</div>';
            }
        }
        
        ob_start();
        ?>
        <div class="resbs-submit-property-form">
            <?php if ($atts['show_title'] === 'true'): ?>
                <h3><?php echo $edit_id > 0 ? esc_html__('Edit Property', 'realestate-booking-suite') : esc_html__('Submit Property', 'realestate-booking-suite'); ?></h3>
            <?php endif; ?>
            
            <form id="resbs-property-form" method="post" enctype="multipart/form-data">
                <?php wp_nonce_field('resbs_submit_property', 'resbs_nonce'); ?>
                <input type="hidden" name="action" value="resbs_submit_property">
                <input type="hidden" name="edit_id" value="<?php echo esc_attr($edit_id); ?>">
                
                <div class="resbs-form-row">
                    <label for="property_title"><?php esc_html_e('Property Title *', 'realestate-booking-suite'); ?></label>
                    <input type="text" id="property_title" name="property_title" value="<?php echo $property ? esc_attr($property->post_title) : ''; ?>" required>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_content"><?php esc_html_e('Property Description *', 'realestate-booking-suite'); ?></label>
                    <textarea id="property_content" name="property_content" rows="5" required><?php echo $property ? esc_textarea($property->post_content) : ''; ?></textarea>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_price"><?php esc_html_e('Price *', 'realestate-booking-suite'); ?></label>
                    <input type="number" id="property_price" name="property_price" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_price', true)) : ''; ?>" step="0.01" min="0" required>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_bedrooms"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label>
                    <input type="number" id="property_bedrooms" name="property_bedrooms" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_bedrooms', true)) : ''; ?>" min="0">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_bathrooms"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                    <input type="number" id="property_bathrooms" name="property_bathrooms" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_bathrooms', true)) : ''; ?>" min="0" step="0.5">
                </div>
                
                <div class="resbs-form-row">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label for="property_half_baths"><?php esc_html_e('Half Baths', 'realestate-booking-suite'); ?></label>
                            <input type="number" id="property_half_baths" name="property_half_baths" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_half_baths', true)) : ''; ?>" min="0">
                        </div>
                        <div>
                            <label for="property_total_rooms"><?php esc_html_e('Total Rooms', 'realestate-booking-suite'); ?></label>
                            <input type="number" id="property_total_rooms" name="property_total_rooms" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_total_rooms', true)) : ''; ?>" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label for="property_floors"><?php esc_html_e('Floors', 'realestate-booking-suite'); ?></label>
                            <input type="number" id="property_floors" name="property_floors" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_floors', true)) : ''; ?>" min="0">
                        </div>
                        <div>
                            <label for="property_floor_level"><?php esc_html_e('Floor Level', 'realestate-booking-suite'); ?></label>
                            <input type="number" id="property_floor_level" name="property_floor_level" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_floor_level', true)) : ''; ?>" min="0">
                        </div>
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_area"><?php esc_html_e('Area (sq ft)', 'realestate-booking-suite'); ?></label>
                    <input type="number" id="property_area" name="property_area" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_size', true) ?: get_post_meta($property->ID, '_property_area_sqft', true)) : ''; ?>" min="0">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_price_per_sqft"><?php esc_html_e('Price per Sq Ft', 'realestate-booking-suite'); ?></label>
                    <input type="number" id="property_price_per_sqft" name="property_price_per_sqft" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_price_per_sqft', true)) : ''; ?>" step="0.01" min="0" placeholder="0">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_lot_size"><?php esc_html_e('Lot Size (sq ft)', 'realestate-booking-suite'); ?></label>
                    <input type="number" id="property_lot_size" name="property_lot_size" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_lot_size_sqft', true)) : ''; ?>" min="0">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_year_built"><?php esc_html_e('Year Built', 'realestate-booking-suite'); ?></label>
                    <input type="number" id="property_year_built" name="property_year_built" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_year_built', true)) : ''; ?>" min="1800" max="<?php echo esc_attr(date('Y')); ?>" placeholder="1990">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_year_remodeled"><?php esc_html_e('Year Remodeled', 'realestate-booking-suite'); ?></label>
                    <input type="number" id="property_year_remodeled" name="property_year_remodeled" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_year_remodeled', true)) : ''; ?>" min="1800" max="<?php echo esc_attr(date('Y')); ?>" placeholder="2000">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_address"><?php esc_html_e('Address', 'realestate-booking-suite'); ?></label>
                    <input type="text" id="property_address" name="property_address" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_address', true)) : ''; ?>" placeholder="<?php esc_attr_e('Enter full address', 'realestate-booking-suite'); ?>">
                </div>
                
                <div class="resbs-form-row">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label for="property_city"><?php esc_html_e('City', 'realestate-booking-suite'); ?></label>
                            <input type="text" id="property_city" name="property_city" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_city', true)) : ''; ?>" placeholder="<?php esc_attr_e('City', 'realestate-booking-suite'); ?>">
                        </div>
                        <div>
                            <label for="property_state"><?php esc_html_e('State/Province', 'realestate-booking-suite'); ?></label>
                            <input type="text" id="property_state" name="property_state" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_state', true)) : ''; ?>" placeholder="<?php esc_attr_e('State/Province', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label for="property_zip"><?php esc_html_e('ZIP/Postal Code', 'realestate-booking-suite'); ?></label>
                            <input type="text" id="property_zip" name="property_zip" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_zip', true)) : ''; ?>" placeholder="<?php esc_attr_e('ZIP/Postal Code', 'realestate-booking-suite'); ?>">
                        </div>
                        <div>
                            <label for="property_country"><?php esc_html_e('Country', 'realestate-booking-suite'); ?></label>
                            <input type="text" id="property_country" name="property_country" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_country', true)) : ''; ?>" placeholder="<?php esc_attr_e('Country', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_latitude"><?php esc_html_e('Latitude', 'realestate-booking-suite'); ?></label>
                    <input type="text" id="property_latitude" name="property_latitude" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_latitude', true)) : ''; ?>" placeholder="23.8103">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_longitude"><?php esc_html_e('Longitude', 'realestate-booking-suite'); ?></label>
                    <input type="text" id="property_longitude" name="property_longitude" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_longitude', true)) : ''; ?>" placeholder="90.4125">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_video_url"><?php esc_html_e('Video URL', 'realestate-booking-suite'); ?></label>
                    <input type="url" id="property_video_url" name="property_video_url" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_property_video_url', true)) : ''; ?>" placeholder="<?php esc_attr_e('YouTube or Vimeo URL', 'realestate-booking-suite'); ?>">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_description"><?php esc_html_e('Additional Description', 'realestate-booking-suite'); ?></label>
                    <textarea id="property_description" name="property_description" rows="3"><?php echo $property ? esc_textarea(get_post_meta($property->ID, '_property_description', true)) : ''; ?></textarea>
                </div>
                
                <div class="resbs-form-row">
                    <label><?php esc_html_e('Property Image', 'realestate-booking-suite'); ?></label>
                    <div class="resbs-image-upload">
                        <input type="file" id="property_image" name="property_image" accept="image/*">
                        <?php if ($property && has_post_thumbnail($property->ID)): ?>
                            <div class="current-image">
                                <?php echo get_the_post_thumbnail($property->ID, 'thumbnail'); ?>
                                <p><?php esc_html_e('Current image', 'realestate-booking-suite'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <label><?php esc_html_e('Gallery Images', 'realestate-booking-suite'); ?></label>
                    <div class="resbs-gallery-upload">
                        <input type="file" id="property_gallery" name="property_gallery[]" accept="image/*" multiple>
                        <div class="gallery-preview"></div>
                        <?php if ($property): ?>
                            <?php
                            $gallery = get_post_meta($property->ID, '_property_gallery', true);
                            if (is_string($gallery)) {
                                $gallery = explode(',', $gallery);
                            }
                            if (!empty($gallery) && is_array($gallery)):
                            ?>
                                <div class="current-gallery">
                                    <h4><?php esc_html_e('Current Gallery', 'realestate-booking-suite'); ?></h4>
                                    <?php foreach ($gallery as $image_id): ?>
                                        <?php if ($image_id): ?>
                                            <div class="gallery-item">
                                                <?php echo wp_get_attachment_image($image_id, 'thumbnail'); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_type"><?php esc_html_e('Property Type', 'realestate-booking-suite'); ?></label>
                    <?php
                    $property_types = get_terms(array(
                        'taxonomy' => 'property_type',
                        'hide_empty' => false
                    ));
                    
                    // If no property types exist or error, create default types
                    if (is_wp_error($property_types) || empty($property_types)) {
                        $default_types = array(
                            'House' => 'house',
                            'Apartment' => 'apartment',
                            'Condo' => 'condo',
                            'Townhouse' => 'townhouse',
                            'Villa' => 'villa',
                            'Commercial' => 'commercial',
                            'Land' => 'land'
                        );
                        
                        $created_terms = array();
                        foreach ($default_types as $name => $slug) {
                            // Check if term exists
                            $term = term_exists($slug, 'property_type');
                            if (!$term) {
                                // Create the term
                                $term = wp_insert_term($name, 'property_type', array('slug' => $slug));
                                if (!is_wp_error($term) && isset($term['term_id'])) {
                                    $created_terms[] = (object) array('term_id' => $term['term_id'], 'name' => $name);
                                }
                            } else {
                                $term_id = is_array($term) ? $term['term_id'] : $term;
                                $created_terms[] = (object) array('term_id' => $term_id, 'name' => $name);
                            }
                        }
                        $property_types = $created_terms;
                    }
                    ?>
                    <select id="property_type" name="property_type" required>
                        <option value=""><?php esc_html_e('Select Property Type', 'realestate-booking-suite'); ?></option>
                        <?php if (!empty($property_types) && !is_wp_error($property_types)): ?>
                            <?php foreach ($property_types as $type): ?>
                                <option value="<?php echo esc_attr($type->term_id); ?>" <?php selected($property ? wp_get_post_terms($property->ID, 'property_type', array('fields' => 'ids')) : array(), $type->term_id); ?>>
                                    <?php echo esc_html($type->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_status"><?php esc_html_e('Property Status', 'realestate-booking-suite'); ?></label>
                    <?php
                    $property_statuses = get_terms(array(
                        'taxonomy' => 'property_status',
                        'hide_empty' => false
                    ));
                    
                    // If no property statuses exist or error, create default statuses
                    if (is_wp_error($property_statuses) || empty($property_statuses)) {
                        $default_statuses = array(
                            'For Sale' => 'for-sale',
                            'For Rent' => 'for-rent',
                            'Sold' => 'sold',
                            'Rented' => 'rented',
                            'Off Market' => 'off-market'
                        );
                        
                        $created_terms = array();
                        foreach ($default_statuses as $name => $slug) {
                            // Check if term exists
                            $term = term_exists($slug, 'property_status');
                            if (!$term) {
                                // Create the term
                                $term = wp_insert_term($name, 'property_status', array('slug' => $slug));
                                if (!is_wp_error($term) && isset($term['term_id'])) {
                                    $created_terms[] = (object) array('term_id' => $term['term_id'], 'name' => $name);
                                }
                            } else {
                                $term_id = is_array($term) ? $term['term_id'] : $term;
                                $created_terms[] = (object) array('term_id' => $term_id, 'name' => $name);
                            }
                        }
                        $property_statuses = $created_terms;
                    }
                    ?>
                    <select id="property_status" name="property_status" required>
                        <option value=""><?php esc_html_e('Select Property Status', 'realestate-booking-suite'); ?></option>
                        <?php if (!empty($property_statuses) && !is_wp_error($property_statuses)): ?>
                            <?php foreach ($property_statuses as $status): ?>
                                <option value="<?php echo esc_attr($status->term_id); ?>" <?php selected($property ? wp_get_post_terms($property->ID, 'property_status', array('fields' => 'ids')) : array(), $status->term_id); ?>>
                                    <?php echo esc_html($status->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_condition"><?php esc_html_e('Property Condition', 'realestate-booking-suite'); ?></label>
                    <?php
                    $property_condition = $property ? get_post_meta($property->ID, '_property_condition', true) : '';
                    ?>
                    <select id="property_condition" name="property_condition">
                        <option value=""><?php esc_html_e('Select Condition', 'realestate-booking-suite'); ?></option>
                        <option value="excellent" <?php selected($property_condition, 'excellent'); ?>><?php esc_html_e('Excellent', 'realestate-booking-suite'); ?></option>
                        <option value="very-good" <?php selected($property_condition, 'very-good'); ?>><?php esc_html_e('Very Good', 'realestate-booking-suite'); ?></option>
                        <option value="good" <?php selected($property_condition, 'good'); ?>><?php esc_html_e('Good', 'realestate-booking-suite'); ?></option>
                        <option value="fair" <?php selected($property_condition, 'fair'); ?>><?php esc_html_e('Fair', 'realestate-booking-suite'); ?></option>
                        <option value="needs-work" <?php selected($property_condition, 'needs-work'); ?>><?php esc_html_e('Needs Work', 'realestate-booking-suite'); ?></option>
                    </select>
                </div>
                
                <h3 style="margin-top: 30px; margin-bottom: 15px; border-bottom: 2px solid #0073aa; padding-bottom: 10px;"><?php esc_html_e('Property Features', 'realestate-booking-suite'); ?></h3>
                
                <div class="resbs-form-row">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label for="property_parking"><?php esc_html_e('Parking', 'realestate-booking-suite'); ?></label>
                            <?php
                            $parking = $property ? get_post_meta($property->ID, '_property_parking', true) : '';
                            ?>
                            <input type="text" id="property_parking" name="property_parking" value="<?php echo esc_attr($parking); ?>" placeholder="e.g. 2 Car Garage, Street Parking">
                        </div>
                        <div>
                            <label for="property_heating"><?php esc_html_e('Heating', 'realestate-booking-suite'); ?></label>
                            <?php
                            $heating = $property ? get_post_meta($property->ID, '_property_heating', true) : '';
                            ?>
                            <input type="text" id="property_heating" name="property_heating" value="<?php echo esc_attr($heating); ?>" placeholder="e.g. Central Heating, Gas">
                        </div>
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label for="property_cooling"><?php esc_html_e('Cooling', 'realestate-booking-suite'); ?></label>
                            <?php
                            $cooling = $property ? get_post_meta($property->ID, '_property_cooling', true) : '';
                            ?>
                            <input type="text" id="property_cooling" name="property_cooling" value="<?php echo esc_attr($cooling); ?>" placeholder="e.g. Central Air, AC Units">
                        </div>
                        <div>
                            <label for="property_basement"><?php esc_html_e('Basement', 'realestate-booking-suite'); ?></label>
                            <?php
                            $basement = $property ? get_post_meta($property->ID, '_property_basement', true) : '';
                            ?>
                            <input type="text" id="property_basement" name="property_basement" value="<?php echo esc_attr($basement); ?>" placeholder="e.g. Finished, Unfinished, None">
                        </div>
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <label for="property_roof"><?php esc_html_e('Roof', 'realestate-booking-suite'); ?></label>
                            <?php
                            $roof = $property ? get_post_meta($property->ID, '_property_roof', true) : '';
                            ?>
                            <input type="text" id="property_roof" name="property_roof" value="<?php echo esc_attr($roof); ?>" placeholder="e.g. Shingle, Tile, Metal">
                        </div>
                        <div>
                            <label for="property_exterior_material"><?php esc_html_e('Exterior Material', 'realestate-booking-suite'); ?></label>
                            <?php
                            $exterior_material = $property ? get_post_meta($property->ID, '_property_exterior_material', true) : '';
                            ?>
                            <input type="text" id="property_exterior_material" name="property_exterior_material" value="<?php echo esc_attr($exterior_material); ?>" placeholder="e.g. Brick, Vinyl, Stucco">
                        </div>
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_floor_covering"><?php esc_html_e('Floor Covering', 'realestate-booking-suite'); ?></label>
                    <?php
                    $floor_covering = $property ? get_post_meta($property->ID, '_property_floor_covering', true) : '';
                    ?>
                    <input type="text" id="property_floor_covering" name="property_floor_covering" value="<?php echo esc_attr($floor_covering); ?>" placeholder="e.g. Hardwood, Carpet, Tile">
                </div>
                
                <h3 style="margin-top: 30px; margin-bottom: 15px; border-bottom: 2px solid #0073aa; padding-bottom: 10px;"><?php esc_html_e('Agent Information', 'realestate-booking-suite'); ?></h3>
                
                <div class="resbs-form-row">
                    <div class="resbs-form-group">
                        <label for="property_agent_name"><?php esc_html_e('Agent Name', 'realestate-booking-suite'); ?></label>
                        <?php
                        $agent_name = $property ? get_post_meta($property->ID, '_property_agent_name', true) : '';
                        ?>
                        <input type="text" id="property_agent_name" name="property_agent_name" value="<?php echo esc_attr($agent_name); ?>" placeholder="John Smith">
                    </div>
                    <div class="resbs-form-group">
                        <label for="property_agent_title"><?php esc_html_e('Agent Title', 'realestate-booking-suite'); ?></label>
                        <?php
                        $agent_title = $property ? get_post_meta($property->ID, '_property_agent_title', true) : '';
                        ?>
                        <input type="text" id="property_agent_title" name="property_agent_title" value="<?php echo esc_attr($agent_title); ?>" placeholder="Real Estate Agent">
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <div class="resbs-form-group">
                        <label for="property_agent_phone"><?php esc_html_e('Agent Phone', 'realestate-booking-suite'); ?></label>
                        <?php
                        $agent_phone = $property ? get_post_meta($property->ID, '_property_agent_phone', true) : '';
                        ?>
                        <input type="tel" id="property_agent_phone" name="property_agent_phone" value="<?php echo esc_attr($agent_phone); ?>" placeholder="+1 (555) 123-4567">
                    </div>
                    <div class="resbs-form-group">
                        <label for="property_agent_email"><?php esc_html_e('Agent Email', 'realestate-booking-suite'); ?></label>
                        <?php
                        $agent_email = $property ? get_post_meta($property->ID, '_property_agent_email', true) : '';
                        ?>
                        <input type="email" id="property_agent_email" name="property_agent_email" value="<?php echo esc_attr($agent_email); ?>" placeholder="agent@example.com">
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <div class="resbs-form-group">
                        <label for="property_agent_experience"><?php esc_html_e('Experience', 'realestate-booking-suite'); ?></label>
                        <?php
                        $agent_experience = $property ? get_post_meta($property->ID, '_property_agent_experience', true) : '';
                        ?>
                        <input type="text" id="property_agent_experience" name="property_agent_experience" value="<?php echo esc_attr($agent_experience); ?>" placeholder="5+ Years">
                        <p class="resbs-input-help"><?php esc_html_e('Years of experience (e.g., 5+ Years)', 'realestate-booking-suite'); ?></p>
                    </div>
                    <div class="resbs-form-group">
                        <label for="property_agent_response_time"><?php esc_html_e('Response Time', 'realestate-booking-suite'); ?></label>
                        <?php
                        $agent_response_time = $property ? get_post_meta($property->ID, '_property_agent_response_time', true) : '';
                        ?>
                        <input type="text" id="property_agent_response_time" name="property_agent_response_time" value="<?php echo esc_attr($agent_response_time); ?>" placeholder="< 1 Hour">
                        <p class="resbs-input-help"><?php esc_html_e('Average response time (e.g., < 1 Hour)', 'realestate-booking-suite'); ?></p>
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <div class="resbs-form-group">
                        <label for="property_agent_properties_sold"><?php esc_html_e('Properties Sold', 'realestate-booking-suite'); ?></label>
                        <?php
                        $agent_properties_sold = $property ? get_post_meta($property->ID, '_property_agent_properties_sold', true) : '';
                        ?>
                        <input type="text" id="property_agent_properties_sold" name="property_agent_properties_sold" value="<?php echo esc_attr($agent_properties_sold); ?>" placeholder="100+">
                        <p class="resbs-input-help"><?php esc_html_e('Number of properties sold (e.g., 100+)', 'realestate-booking-suite'); ?></p>
                    </div>
                    <div class="resbs-form-group">
                        <label for="property_agent_rating"><?php esc_html_e('Agent Rating', 'realestate-booking-suite'); ?></label>
                        <?php
                        $agent_rating = $property ? get_post_meta($property->ID, '_property_agent_rating', true) : '5';
                        ?>
                        <select id="property_agent_rating" name="property_agent_rating">
                            <?php 
                            $current_rating = $agent_rating;
                            for ($i = 1; $i <= 5; $i++): 
                                $star_text = ($i == 1) ? 'Star' : 'Stars';
                                $selected = selected($current_rating, $i, false);
                            ?>
                                <option value="<?php echo esc_attr($i); ?>" <?php echo $selected; // selected() already returns escaped HTML ?>><?php echo esc_html($i . ' ' . $star_text); ?></option>
                            <?php endfor; ?>
                        </select>
                        <p class="resbs-input-help"><?php esc_html_e('Agent rating out of 5 stars', 'realestate-booking-suite'); ?></p>
                    </div>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_location"><?php esc_html_e('Property Location', 'realestate-booking-suite'); ?></label>
                    <?php
                    $property_locations = get_terms(array(
                        'taxonomy' => 'property_location',
                        'hide_empty' => false
                    ));
                    ?>
                    <select id="property_location" name="property_location">
                        <option value=""><?php esc_html_e('Select Property Location', 'realestate-booking-suite'); ?></option>
                        <?php foreach ($property_locations as $location): ?>
                            <option value="<?php echo esc_attr($location->term_id); ?>" <?php selected($property ? wp_get_post_terms($property->ID, 'property_location', array('fields' => 'ids')) : array(), $location->term_id); ?>>
                                <?php echo esc_html($location->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="resbs-form-row">
                    <button type="submit" class="resbs-submit-btn">
                        <?php echo $edit_id > 0 ? esc_html__('Update Property', 'realestate-booking-suite') : esc_html__('Submit Property', 'realestate-booking-suite'); ?>
                    </button>
                </div>
            </form>
            
            <div id="resbs-message" class="resbs-message" style="display: none;"></div>
        </div>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Handle property submission
     */
    public function handle_property_submission() {
        // Check if this is for the shortcode form (different nonce)
        // If nonce is 'resbs_submit_nonce', let the shortcode handler process it
        if (isset($_POST['resbs_submit_nonce']) || isset($_POST['nonce'])) {
            // This is for the shortcode form, let RESBS_Shortcode_AJAX handle it
            return;
        }
        
        // Verify nonce for this handler
        if (!isset($_POST['resbs_nonce']) || !wp_verify_nonce($_POST['resbs_nonce'], 'resbs_submit_property')) {
            wp_die(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_die(esc_html__('You must be logged in to submit a property.', 'realestate-booking-suite'));
        }
        
        $edit_id = isset($_POST['edit_id']) ? intval($_POST['edit_id']) : 0;
        $user_id = get_current_user_id();
        
        // If editing, verify ownership
        if ($edit_id > 0) {
            if (!RESBS_Security::verify_post_ownership($edit_id, $user_id)) {
                wp_send_json_error(esc_html__('You do not have permission to edit this property.', 'realestate-booking-suite'));
            }
        }
        
        // Validate required fields
        $title = isset($_POST['property_title']) ? sanitize_text_field($_POST['property_title']) : '';
        $content = isset($_POST['property_content']) ? sanitize_textarea_field($_POST['property_content']) : '';
        $price = isset($_POST['property_price']) ? floatval($_POST['property_price']) : 0;
        
        if (empty($title) || empty($content) || $price <= 0) {
            wp_send_json_error(esc_html__('Please fill in all required fields.', 'realestate-booking-suite'));
        }
        
        // Prepare post data
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_type' => 'property',
            'post_status' => 'pending', // Submit for review
            'post_author' => $user_id
        );
        
        // If editing, update existing post
        if ($edit_id > 0) {
            $post_data['ID'] = $edit_id;
            $post_id = wp_update_post($post_data);
        } else {
            $post_id = wp_insert_post($post_data);
        }
        
        if (is_wp_error($post_id)) {
            wp_send_json_error(esc_html__('Error saving property.', 'realestate-booking-suite'));
        }
        
        // Save meta fields
        $meta_fields = array(
            'property_price' => array('sanitize' => 'floatval', 'meta_key' => '_property_price'),
            'property_price_per_sqft' => array('sanitize' => 'floatval', 'meta_key' => '_property_price_per_sqft'),
            'property_bedrooms' => array('sanitize' => 'absint', 'meta_key' => '_property_bedrooms'),
            'property_bathrooms' => array('sanitize' => 'floatval', 'meta_key' => '_property_bathrooms'),
            'property_half_baths' => array('sanitize' => 'absint', 'meta_key' => '_property_half_baths'),
            'property_total_rooms' => array('sanitize' => 'absint', 'meta_key' => '_property_total_rooms'),
            'property_floors' => array('sanitize' => 'absint', 'meta_key' => '_property_floors'),
            'property_floor_level' => array('sanitize' => 'absint', 'meta_key' => '_property_floor_level'),
            'property_area' => array('sanitize' => 'absint', 'meta_key' => '_property_size'), // Save area to _property_size for consistency
            'property_size' => array('sanitize' => 'absint', 'meta_key' => '_property_size'),
            'property_lot_size' => array('sanitize' => 'absint', 'meta_key' => '_property_lot_size_sqft'),
            'property_year_built' => array('sanitize' => 'absint', 'meta_key' => '_property_year_built'),
            'property_year_remodeled' => array('sanitize' => 'absint', 'meta_key' => '_property_year_remodeled'),
            'property_latitude' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_latitude'),
            'property_longitude' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_longitude'),
            'property_video_url' => array('sanitize' => 'esc_url_raw', 'meta_key' => '_property_video_url'),
            'property_description' => array('sanitize' => 'sanitize_textarea_field', 'meta_key' => '_property_description'),
            'property_condition' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_condition'),
            'property_city' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_city'),
            'property_state' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_state'),
            'property_zip' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_zip'),
            'property_country' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_country'),
            'property_agent_name' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_agent_name'),
            'property_agent_title' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_agent_title'),
            'property_agent_phone' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_agent_phone'),
            'property_agent_email' => array('sanitize' => 'sanitize_email', 'meta_key' => '_property_agent_email'),
            'property_agent_experience' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_agent_experience'),
            'property_agent_response_time' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_agent_response_time'),
            'property_agent_properties_sold' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_agent_properties_sold'),
            'property_agent_rating' => array('sanitize' => 'absint', 'meta_key' => '_property_agent_rating'),
            'property_parking' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_parking'),
            'property_heating' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_heating'),
            'property_cooling' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_cooling'),
            'property_basement' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_basement'),
            'property_roof' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_roof'),
            'property_exterior_material' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_exterior_material'),
            'property_floor_covering' => array('sanitize' => 'sanitize_text_field', 'meta_key' => '_property_floor_covering')
        );
        
        foreach ($meta_fields as $field => $config) {
            if (isset($_POST[$field]) && is_callable($config['sanitize'])) {
                $value = call_user_func($config['sanitize'], $_POST[$field]);
                // Validate latitude/longitude ranges
                if ($field === 'property_latitude') {
                    $float_value = floatval($value);
                    if ($float_value < -90 || $float_value > 90) {
                        continue; // Skip invalid latitude
                    }
                    $value = $float_value;
                } elseif ($field === 'property_longitude') {
                    $float_value = floatval($value);
                    if ($float_value < -180 || $float_value > 180) {
                        continue; // Skip invalid longitude
                    }
                    $value = $float_value;
                }
                update_post_meta($post_id, $config['meta_key'], $value);
            }
        }
        
        // Save address
        if (!empty($_POST['property_address'])) {
            update_post_meta($post_id, '_property_address', sanitize_text_field($_POST['property_address']));
        }
        
        // Handle location - if it's text, create or find term
        if (!empty($_POST['property_location'])) {
            $location_text = trim(sanitize_text_field($_POST['property_location']));
            
            // Check if it's a number (old term ID format) or text
            if (is_numeric($location_text)) {
                // Old format: term ID
                wp_set_post_terms($post_id, array(intval($location_text)), 'property_location');
            } else {
                // New format: text location - create term if doesn't exist
                $term = term_exists($location_text, 'property_location');
                if ($term === 0 || $term === null) {
                    // Create new term
                    $term = wp_insert_term($location_text, 'property_location');
                    if (!is_wp_error($term)) {
                        wp_set_post_terms($post_id, array($term['term_id']), 'property_location');
                    }
                } else {
                    // Use existing term
                    $term_id = is_array($term) ? $term['term_id'] : $term;
                    wp_set_post_terms($post_id, array($term_id), 'property_location');
                }
                
                // Also save as meta for easy access
                update_post_meta($post_id, '_property_location_text', $location_text);
            }
        }
        
        // Handle featured image
        if (!empty($_FILES['property_image']['name'])) {
            $this->process_image_upload($post_id, 'featured');
        }
        
        // Handle gallery images
        if (!empty($_FILES['property_gallery']['name'][0])) {
            $this->handle_gallery_upload($post_id);
        }
        
        // Set other taxonomies
        $taxonomies = array('property_type', 'property_status');
        foreach ($taxonomies as $taxonomy) {
            if (!empty($_POST[$taxonomy])) {
                $term_id = intval($_POST[$taxonomy]);
                wp_set_post_terms($post_id, array($term_id), $taxonomy);
            }
        }
        
        wp_send_json_success(esc_html__('Property submitted successfully!', 'realestate-booking-suite'));
    }
    
    /**
     * Handle image upload AJAX request
     */
    public function handle_image_upload() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_upload_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('You must be logged in to upload images.', 'realestate-booking-suite'));
        }
        
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $user_id = get_current_user_id();
        
        // If post_id provided, verify ownership
        if ($post_id > 0 && !RESBS_Security::verify_post_ownership($post_id, $user_id)) {
            wp_send_json_error(esc_html__('You do not have permission to upload images for this property.', 'realestate-booking-suite'));
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['property_image']) || $_FILES['property_image']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(esc_html__('No file uploaded or upload error occurred.', 'realestate-booking-suite'));
        }
        
        // Validate file type
        $file_type = wp_check_filetype($_FILES['property_image']['name']);
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (!in_array(strtolower($file_type['ext']), $allowed_types)) {
            wp_send_json_error(esc_html__('Invalid file type. Only images are allowed.', 'realestate-booking-suite'));
        }
        
        // Handle the upload
        $result = $this->process_image_upload($post_id, 'featured');
        
        if ($result) {
            wp_send_json_success(esc_html__('Image uploaded successfully.', 'realestate-booking-suite'));
        } else {
            wp_send_json_error(esc_html__('Failed to upload image.', 'realestate-booking-suite'));
        }
    }
    
    /**
     * Process image upload (internal method)
     */
    private function process_image_upload($post_id, $type = 'featured') {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        // Check if file was uploaded
        if (!isset($_FILES['property_image']) || $_FILES['property_image']['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        $uploadedfile = $_FILES['property_image'];
        
        // Validate file before upload
        $validation = RESBS_Security::validate_file_upload($uploadedfile);
        if (is_wp_error($validation)) {
            return false;
        }
        
        $upload_overrides = array('test_form' => false);
        
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            $filename = $movefile['file'];
            $wp_filetype = wp_check_filetype(basename($filename), null);
            
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            
            $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
            
            if (!is_wp_error($attach_id)) {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
                wp_update_attachment_metadata($attach_id, $attach_data);
                
                if ($type === 'featured') {
                    set_post_thumbnail($post_id, $attach_id);
                }
                
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Handle gallery upload
     */
    private function handle_gallery_upload($post_id) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $gallery_ids = array();
        
        foreach ($_FILES['property_gallery']['name'] as $key => $value) {
            if ($_FILES['property_gallery']['name'][$key]) {
                $file = array(
                    'name' => $_FILES['property_gallery']['name'][$key],
                    'type' => $_FILES['property_gallery']['type'][$key],
                    'tmp_name' => $_FILES['property_gallery']['tmp_name'][$key],
                    'error' => $_FILES['property_gallery']['error'][$key],
                    'size' => $_FILES['property_gallery']['size'][$key]
                );
                
                // Validate file before upload
                $validation = RESBS_Security::validate_file_upload($file);
                if (is_wp_error($validation)) {
                    continue; // Skip invalid file
                }
                
                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($file, $upload_overrides);
                
                if ($movefile && !isset($movefile['error'])) {
                    $filename = $movefile['file'];
                    $wp_filetype = wp_check_filetype(basename($filename), null);
                    
                    $attachment = array(
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );
                    
                    $attach_id = wp_insert_attachment($attachment, $filename, $post_id);
                    
                    if (!is_wp_error($attach_id)) {
                        require_once(ABSPATH . 'wp-admin/includes/image.php');
                        $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
                        wp_update_attachment_metadata($attach_id, $attach_data);
                        
                        $gallery_ids[] = $attach_id;
                    }
                }
            }
        }
        
        if (!empty($gallery_ids)) {
            update_post_meta($post_id, '_property_gallery', $gallery_ids);
        }
    }
    
    /**
     * Dashboard shortcode
     */
    public function dashboard_shortcode($atts) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="resbs-dashboard-login-required">
                <p>' . esc_html__('Please login to access your dashboard.', 'realestate-booking-suite') . '</p>
                <a href="' . esc_url(wp_login_url(get_permalink())) . '" class="resbs-login-btn">' . esc_html__('Login', 'realestate-booking-suite') . '</a>
            </div>';
        }
        
        // Check if user profile is enabled in settings
        $profile_enabled = resbs_is_user_profile_enabled();
        
        $atts = shortcode_atts(array(
            'show_properties' => 'true',
            'show_bookings' => 'true',
            'show_profile' => $profile_enabled ? 'true' : 'false',
            'properties_per_page' => '10',
            'bookings_per_page' => '10'
        ), $atts);
        
        $user_id = get_current_user_id();
        $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'properties';
        
        ob_start();
        ?>
        <div class="resbs-dashboard" data-user-id="<?php echo esc_attr($user_id); ?>">
            <div class="resbs-dashboard-header">
                <h2><?php esc_html_e('My Dashboard', 'realestate-booking-suite'); ?></h2>
                <div class="resbs-dashboard-nav">
                    <?php if ($atts['show_properties'] === 'true'): ?>
                        <a href="?tab=properties" class="resbs-nav-tab <?php echo esc_attr($current_tab === 'properties' ? 'active' : ''); ?>">
                            <?php esc_html_e('My Properties', 'realestate-booking-suite'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_bookings'] === 'true'): ?>
                        <a href="?tab=bookings" class="resbs-nav-tab <?php echo esc_attr($current_tab === 'bookings' ? 'active' : ''); ?>">
                            <?php esc_html_e('My Bookings', 'realestate-booking-suite'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_profile'] === 'true'): ?>
                        <a href="?tab=profile" class="resbs-nav-tab <?php echo esc_attr($current_tab === 'profile' ? 'active' : ''); ?>">
                            <?php esc_html_e('Profile', 'realestate-booking-suite'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="resbs-dashboard-content">
                <?php if ($current_tab === 'properties' && $atts['show_properties'] === 'true'): ?>
                    <?php $this->render_properties_tab($user_id, intval($atts['properties_per_page'])); ?>
                <?php elseif ($current_tab === 'bookings' && $atts['show_bookings'] === 'true'): ?>
                    <?php $this->render_bookings_tab($user_id, intval($atts['bookings_per_page'])); ?>
                <?php elseif ($current_tab === 'profile' && $atts['show_profile'] === 'true'): ?>
                    <?php $this->render_profile_tab($user_id); ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render properties tab
     */
    private function render_properties_tab($user_id, $per_page = 10) {
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        
        $properties = get_posts(array(
            'post_type' => 'property',
            'author' => $user_id,
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'post_status' => array('publish', 'pending', 'draft')
        ));
        
        $total_properties = wp_count_posts('property');
        $user_properties_count = count_user_posts($user_id, 'property');
        ?>
        <div class="resbs-dashboard-tab resbs-properties-tab">
            <div class="resbs-tab-header">
                <h3><?php esc_html_e('My Properties', 'realestate-booking-suite'); ?></h3>
                <a href="?tab=submit" class="resbs-add-property-btn">
                    <span class="dashicons dashicons-plus-alt"></span>
                    <?php esc_html_e('Add New Property', 'realestate-booking-suite'); ?>
                </a>
            </div>
            
            <div class="resbs-properties-stats">
                <div class="resbs-stat-item">
                    <span class="resbs-stat-number"><?php echo esc_html($user_properties_count); ?></span>
                    <span class="resbs-stat-label"><?php esc_html_e('Total Properties', 'realestate-booking-suite'); ?></span>
                </div>
                <div class="resbs-stat-item">
                    <span class="resbs-stat-number"><?php echo esc_html($this->get_published_properties_count($user_id)); ?></span>
                    <span class="resbs-stat-label"><?php esc_html_e('Published', 'realestate-booking-suite'); ?></span>
                </div>
                <div class="resbs-stat-item">
                    <span class="resbs-stat-number"><?php echo esc_html($this->get_pending_properties_count($user_id)); ?></span>
                    <span class="resbs-stat-label"><?php esc_html_e('Pending', 'realestate-booking-suite'); ?></span>
                </div>
            </div>
            
            <?php if (!empty($properties)): ?>
                <div class="resbs-properties-list">
                    <?php foreach ($properties as $property): ?>
                        <?php $this->render_property_item($property); ?>
                    <?php endforeach; ?>
                </div>
                
                <?php $this->render_pagination($user_properties_count, $per_page, $paged); ?>
            <?php else: ?>
                <div class="resbs-no-properties">
                    <p><?php esc_html_e('You haven\'t submitted any properties yet.', 'realestate-booking-suite'); ?></p>
                    <a href="?tab=submit" class="resbs-add-property-btn">
                        <?php esc_html_e('Submit Your First Property', 'realestate-booking-suite'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render bookings tab
     */
    private function render_bookings_tab($user_id, $per_page = 10) {
        $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        
        // Get user's bookings
        $bookings = $this->get_user_bookings($user_id, $per_page, $paged);
        $total_bookings = $this->get_user_bookings_count($user_id);
        ?>
        <div class="resbs-dashboard-tab resbs-bookings-tab">
            <div class="resbs-tab-header">
                <h3><?php esc_html_e('My Bookings', 'realestate-booking-suite'); ?></h3>
            </div>
            
            <div class="resbs-bookings-stats">
                <div class="resbs-stat-item">
                    <span class="resbs-stat-number"><?php echo esc_html($total_bookings); ?></span>
                    <span class="resbs-stat-label"><?php esc_html_e('Total Bookings', 'realestate-booking-suite'); ?></span>
                </div>
                <div class="resbs-stat-item">
                    <span class="resbs-stat-number"><?php echo esc_html($this->get_active_bookings_count($user_id)); ?></span>
                    <span class="resbs-stat-label"><?php esc_html_e('Active', 'realestate-booking-suite'); ?></span>
                </div>
                <div class="resbs-stat-item">
                    <span class="resbs-stat-number"><?php echo esc_html($this->get_completed_bookings_count($user_id)); ?></span>
                    <span class="resbs-stat-label"><?php esc_html_e('Completed', 'realestate-booking-suite'); ?></span>
                </div>
            </div>
            
            <?php if (!empty($bookings)): ?>
                <div class="resbs-bookings-list">
                    <?php foreach ($bookings as $booking): ?>
                        <?php $this->render_booking_item($booking); ?>
                    <?php endforeach; ?>
                </div>
                
                <?php $this->render_pagination($total_bookings, $per_page, $paged); ?>
            <?php else: ?>
                <div class="resbs-no-bookings">
                    <p><?php esc_html_e('You don\'t have any bookings yet.', 'realestate-booking-suite'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Render profile tab
     */
    private function render_profile_tab($user_id) {
        $user = get_userdata($user_id);
        $user_meta = get_user_meta($user_id);
        ?>
        <div class="resbs-dashboard-tab resbs-profile-tab">
            <div class="resbs-tab-header">
                <h3><?php echo esc_html(resbs_get_profile_page_title()); ?></h3>
                <?php if (resbs_get_profile_page_subtitle()): ?>
                    <p class="resbs-profile-subtitle"><?php echo esc_html(resbs_get_profile_page_subtitle()); ?></p>
                <?php endif; ?>
            </div>
            
            <form class="resbs-profile-form" id="resbs-profile-form">
                <?php wp_nonce_field('resbs_update_profile', 'resbs_profile_nonce'); ?>
                
                <div class="resbs-form-row">
                    <div class="resbs-form-group">
                        <label for="first_name"><?php esc_html_e('First Name', 'realestate-booking-suite'); ?></label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo esc_attr($user->first_name); ?>" required>
                    </div>
                    <div class="resbs-form-group">
                        <label for="last_name"><?php esc_html_e('Last Name', 'realestate-booking-suite'); ?></label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo esc_attr($user->last_name); ?>" required>
                    </div>
                </div>
                
                <div class="resbs-form-group">
                    <label for="email"><?php esc_html_e('Email Address', 'realestate-booking-suite'); ?></label>
                    <input type="email" id="email" name="email" value="<?php echo esc_attr($user->user_email); ?>" required>
                </div>
                
                <div class="resbs-form-group">
                    <label for="phone"><?php esc_html_e('Phone Number', 'realestate-booking-suite'); ?></label>
                    <input type="tel" id="phone" name="phone" value="<?php echo esc_attr($user_meta['phone'][0] ?? ''); ?>">
                </div>
                
                <div class="resbs-form-group">
                    <label for="bio"><?php esc_html_e('Bio', 'realestate-booking-suite'); ?></label>
                    <textarea id="bio" name="bio" rows="4"><?php echo esc_textarea($user_meta['description'][0] ?? ''); ?></textarea>
                </div>
                
                <div class="resbs-form-group">
                    <label for="website"><?php esc_html_e('Website', 'realestate-booking-suite'); ?></label>
                    <input type="url" id="website" name="website" value="<?php echo esc_url($user_meta['user_url'][0] ?? ''); ?>">
                </div>
                
                <div class="resbs-form-actions">
                    <button type="submit" class="resbs-save-profile-btn">
                        <?php esc_html_e('Save Profile', 'realestate-booking-suite'); ?>
                    </button>
                </div>
            </form>
        </div>
        <?php
    }
    
    /**
     * Render property item
     */
    private function render_property_item($property) {
        $price = get_post_meta($property->ID, '_property_price', true);
        $bedrooms = get_post_meta($property->ID, '_property_bedrooms', true);
        $bathrooms = get_post_meta($property->ID, '_property_bathrooms', true);
        $area = get_post_meta($property->ID, '_property_size', true) ?: get_post_meta($property->ID, '_property_area_sqft', true);
        $featured_image = get_the_post_thumbnail_url($property->ID, 'medium');
        ?>
        <div class="resbs-property-item" data-property-id="<?php echo esc_attr($property->ID); ?>">
            <div class="resbs-property-image">
                <?php if ($featured_image): ?>
                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($property->post_title); ?>">
                <?php else: ?>
                    <div class="resbs-no-image">
                        <span class="dashicons dashicons-camera"></span>
                    </div>
                <?php endif; ?>
                <div class="resbs-property-status resbs-status-<?php echo esc_attr($property->post_status); ?>">
                    <?php echo esc_html(ucfirst($property->post_status)); ?>
                </div>
            </div>
            
            <div class="resbs-property-details">
                <<?php echo esc_attr(resbs_get_title_heading_tag()); ?> class="resbs-property-title">
                    <a href="<?php echo esc_url(get_permalink($property->ID)); ?>">
                        <?php echo esc_html($property->post_title); ?>
                    </a>
                </<?php echo esc_attr(resbs_get_title_heading_tag()); ?>>
                
                <div class="resbs-property-meta">
                    <?php if ($price): ?>
                        <span class="resbs-property-price"><?php echo esc_html(resbs_format_price($price)); ?></span>
                    <?php endif; ?>
                    
                    <?php if ($bedrooms): ?>
                        <span class="resbs-property-bedrooms">
                            <span class="dashicons dashicons-bed"></span>
                            <?php echo esc_html($bedrooms); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($bathrooms): ?>
                        <span class="resbs-property-bathrooms">
                            <span class="dashicons dashicons-shower"></span>
                            <?php echo esc_html($bathrooms); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($area): ?>
                        <span class="resbs-property-area">
                            <span class="dashicons dashicons-fullscreen-alt"></span>
                            <?php echo esc_html(resbs_format_area($area)); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="resbs-property-actions">
                    <a href="<?php echo esc_url(get_edit_post_link($property->ID)); ?>" class="resbs-edit-btn">
                        <span class="dashicons dashicons-edit"></span>
                        <?php esc_html_e('Edit', 'realestate-booking-suite'); ?>
                    </a>
                    <a href="<?php echo esc_url(get_permalink($property->ID)); ?>" class="resbs-view-btn">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php esc_html_e('View', 'realestate-booking-suite'); ?>
                    </a>
                    <button type="button" class="resbs-delete-btn" data-property-id="<?php echo esc_attr($property->ID); ?>">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e('Delete', 'realestate-booking-suite'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render booking item
     */
    private function render_booking_item($booking) {
        $property = $booking['property_id'] ? get_post($booking['property_id']) : null;
        
        // Get booking details for property_booking post type
        $first_name = '';
        $last_name = '';
        $preferred_date = $booking['preferred_date'] ?? '';
        $preferred_time = $booking['preferred_time'] ?? '';
        $message = $booking['message'] ?? '';
        
        if ($booking['id']) {
            $first_name = get_post_meta($booking['id'], '_booking_first_name', true);
            $last_name = get_post_meta($booking['id'], '_booking_last_name', true);
            if (empty($preferred_date)) {
                $preferred_date = get_post_meta($booking['id'], '_booking_preferred_date', true);
            }
            if (empty($preferred_time)) {
                $preferred_time = get_post_meta($booking['id'], '_booking_preferred_time', true);
            }
        }
        
        ?>
        <div class="resbs-booking-item" data-booking-id="<?php echo esc_attr($booking['id']); ?>">
            <div class="resbs-booking-property">
                <h4 class="resbs-booking-title">
                    <?php if ($property): ?>
                        <a href="<?php echo esc_url(get_permalink($booking['property_id'])); ?>">
                            <?php echo esc_html($property->post_title); ?>
                        </a>
                    <?php else: ?>
                        <?php echo esc_html__('Property not found', 'realestate-booking-suite'); ?>
                    <?php endif; ?>
                </h4>
                <div class="resbs-booking-dates">
                    <?php if (isset($booking['checkin_date']) && isset($booking['checkout_date'])): ?>
                        <span class="resbs-checkin">
                            <strong><?php esc_html_e('Check-in:', 'realestate-booking-suite'); ?></strong>
                            <?php echo esc_html(date('M j, Y', strtotime($booking['checkin_date']))); ?>
                        </span>
                        <span class="resbs-checkout">
                            <strong><?php esc_html_e('Check-out:', 'realestate-booking-suite'); ?></strong>
                            <?php echo esc_html(date('M j, Y', strtotime($booking['checkout_date']))); ?>
                        </span>
                    <?php elseif ($preferred_date || $preferred_time): ?>
                        <?php if ($preferred_date): ?>
                            <span class="resbs-preferred-date">
                                <strong><?php esc_html_e('Preferred Date:', 'realestate-booking-suite'); ?></strong>
                                <?php echo esc_html($preferred_date); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($preferred_time): ?>
                            <span class="resbs-preferred-time">
                                <strong><?php esc_html_e('Preferred Time:', 'realestate-booking-suite'); ?></strong>
                                <?php echo esc_html($preferred_time); ?>
                            </span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="resbs-booking-date">
                            <strong><?php esc_html_e('Booking Date:', 'realestate-booking-suite'); ?></strong>
                            <?php echo esc_html(date('M j, Y', strtotime($booking['date']))); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="resbs-booking-details">
                <?php if (isset($booking['guests']) && isset($booking['nights'])): ?>
                    <div class="resbs-booking-meta">
                        <span class="resbs-booking-guests">
                            <span class="dashicons dashicons-groups"></span>
                            <?php echo esc_html($booking['guests']); ?> <?php esc_html_e('guests', 'realestate-booking-suite'); ?>
                        </span>
                        <span class="resbs-booking-nights">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php echo esc_html($booking['nights']); ?> <?php esc_html_e('nights', 'realestate-booking-suite'); ?>
                        </span>
                    </div>
                <?php endif; ?>
                
                <div class="resbs-booking-status resbs-status-<?php echo esc_attr($booking['status']); ?>">
                    <?php echo esc_html(ucfirst($booking['status'])); ?>
                </div>
            </div>
            
            <div class="resbs-booking-actions">
                <?php if ($booking['status'] === 'confirmed' || $booking['status'] === 'pending'): ?>
                    <button type="button" class="resbs-cancel-booking-btn" data-booking-id="<?php echo esc_attr($booking['id']); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                        <?php esc_html_e('Cancel', 'realestate-booking-suite'); ?>
                    </button>
                <?php endif; ?>
                
                <?php if ($property): ?>
                    <a href="<?php echo esc_url(get_permalink($booking['property_id'])); ?>" class="resbs-view-property-btn">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php esc_html_e('View Property', 'realestate-booking-suite'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render pagination
     */
    private function render_pagination($total_items, $per_page, $current_page) {
        $total_pages = ceil($total_items / $per_page);
        
        if ($total_pages <= 1) {
            return;
        }
        
        echo '<div class="resbs-pagination">';
        
        if ($current_page > 1) {
            echo '<a href="' . esc_url(add_query_arg('paged', $current_page - 1)) . '" class="resbs-pagination-btn resbs-prev">';
            echo '<span class="dashicons dashicons-arrow-left-alt2"></span>';
            esc_html_e('Previous', 'realestate-booking-suite');
            echo '</a>';
        }
        
        for ($i = 1; $i <= $total_pages; $i++) {
            if ($i === $current_page) {
                echo '<span class="resbs-pagination-btn resbs-current">' . esc_html($i) . '</span>';
            } else {
                echo '<a href="' . esc_url(add_query_arg('paged', $i)) . '" class="resbs-pagination-btn">' . esc_html($i) . '</a>';
            }
        }
        
        if ($current_page < $total_pages) {
            echo '<a href="' . esc_url(add_query_arg('paged', $current_page + 1)) . '" class="resbs-pagination-btn resbs-next">';
            esc_html_e('Next', 'realestate-booking-suite');
            echo '<span class="dashicons dashicons-arrow-right-alt2"></span>';
            echo '</a>';
        }
        
        echo '</div>';
    }
    
    /**
     * Get user bookings
     */
    private function get_user_bookings($user_id, $per_page = 10, $paged = 1) {
        $user = get_userdata($user_id);
        if (!$user) {
            return array();
        }
        
        $user_email = $user->user_email;
        
        // Get bookings from property_booking post type by matching email
        $bookings_query = new WP_Query(array(
            'post_type' => 'property_booking',
            'post_status' => 'publish',
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => '_booking_email',
                    'value' => $user_email,
                    'compare' => '='
                )
            )
        ));
        
        $bookings = array();
        if ($bookings_query->have_posts()) {
            while ($bookings_query->have_posts()) {
                $bookings_query->the_post();
                $booking_id = get_the_ID();
                $property_id = get_post_meta($booking_id, '_booking_property_id', true);
                $status = get_post_meta($booking_id, '_booking_status', true);
                $preferred_date = get_post_meta($booking_id, '_booking_preferred_date', true);
                $preferred_time = get_post_meta($booking_id, '_booking_preferred_time', true);
                
                $bookings[] = array(
                    'id' => $booking_id,
                    'property_id' => $property_id,
                    'status' => $status ? $status : 'pending',
                    'date' => get_the_date('Y-m-d', $booking_id),
                    'preferred_date' => $preferred_date,
                    'preferred_time' => $preferred_time,
                    'message' => get_the_content()
                );
            }
            wp_reset_postdata();
        }
        
        return $bookings;
    }
    
    /**
     * Get user bookings count
     */
    private function get_user_bookings_count($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return 0;
        }
        
        $user_email = $user->user_email;
        $count = 0;
        
        // Count bookings from property_booking post type
        $bookings_query = new WP_Query(array(
            'post_type' => 'property_booking',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_booking_email',
                    'value' => $user_email,
                    'compare' => '='
                )
            )
        ));
        
        $count = $bookings_query->found_posts;
        
        return $count;
    }
    
    /**
     * Get published properties count
     */
    private function get_published_properties_count($user_id) {
        return count_user_posts($user_id, 'property', true);
    }
    
    /**
     * Get pending properties count
     */
    private function get_pending_properties_count($user_id) {
        $pending_posts = get_posts(array(
            'post_type' => 'property',
            'author' => $user_id,
            'post_status' => 'pending',
            'posts_per_page' => -1
        ));
        return count($pending_posts);
    }
    
    /**
     * Get active bookings count
     */
    private function get_active_bookings_count($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return 0;
        }
        
        $user_email = $user->user_email;
        $count = 0;
        
        // Count active bookings (pending or confirmed)
        $bookings_query = new WP_Query(array(
            'post_type' => 'property_booking',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_booking_email',
                    'value' => $user_email,
                    'compare' => '='
                ),
                array(
                    'key' => '_booking_status',
                    'value' => array('pending', 'confirmed'),
                    'compare' => 'IN'
                )
            )
        ));
        
        $count = $bookings_query->found_posts;
        
        return $count;
    }
    
    /**
     * Get completed bookings count
     */
    private function get_completed_bookings_count($user_id) {
        $user = get_userdata($user_id);
        if (!$user) {
            return 0;
        }
        
        $user_email = $user->user_email;
        $count = 0;
        
        // Count completed bookings
        $bookings_query = new WP_Query(array(
            'post_type' => 'property_booking',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_booking_email',
                    'value' => $user_email,
                    'compare' => '='
                ),
                array(
                    'key' => '_booking_status',
                    'value' => 'completed',
                    'compare' => '='
                )
            )
        ));
        
        $count = $bookings_query->found_posts;
        
        return $count;
    }
    
    /**
     * Handle cancel booking
     */
    public function handle_cancel_booking() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_cancel_booking')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('Please login to cancel bookings.', 'realestate-booking-suite'));
        }
        
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $user_id = get_current_user_id();
        
        if ($booking_id <= 0) {
            wp_send_json_error(esc_html__('Invalid booking ID.', 'realestate-booking-suite'));
        }
        
        // Verify booking belongs to user
        if (!$this->user_owns_booking($booking_id, $user_id)) {
            wp_send_json_error(esc_html__('You can only cancel your own bookings.', 'realestate-booking-suite'));
        }
        
        // Update booking status
        $updated = update_post_meta($booking_id, '_booking_status', 'cancelled');
        
        if ($updated) {
            wp_send_json_success(esc_html__('Booking cancelled successfully.', 'realestate-booking-suite'));
        } else {
            wp_send_json_error(esc_html__('Failed to cancel booking.', 'realestate-booking-suite'));
        }
    }
    
    /**
     * Handle refund booking
     */
    public function handle_refund_booking() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_refund_booking')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('Please login to request refunds.', 'realestate-booking-suite'));
        }
        
        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $user_id = get_current_user_id();
        
        if ($booking_id <= 0) {
            wp_send_json_error(esc_html__('Invalid booking ID.', 'realestate-booking-suite'));
        }
        
        // Verify booking belongs to user
        if (!$this->user_owns_booking($booking_id, $user_id)) {
            wp_send_json_error(esc_html__('You can only request refunds for your own bookings.', 'realestate-booking-suite'));
        }
        
        // Create refund request
        $refund_request = array(
            'post_type' => 'resbs_refund_request',
            'post_title' => sprintf(esc_html__('Refund Request for Booking #%d', 'realestate-booking-suite'), $booking_id),
            'post_status' => 'pending',
            'post_author' => $user_id
        );
        
        $request_id = wp_insert_post($refund_request);
        
        if ($request_id) {
            update_post_meta($request_id, '_booking_id', $booking_id);
            update_post_meta($request_id, '_refund_status', 'pending');
            update_post_meta($request_id, '_refund_reason', sanitize_textarea_field($_POST['reason'] ?? ''));
            
            wp_send_json_success(esc_html__('Refund request submitted successfully.', 'realestate-booking-suite'));
        } else {
            wp_send_json_error(esc_html__('Failed to submit refund request.', 'realestate-booking-suite'));
        }
    }
    
    /**
     * Handle update profile
     */
    public function handle_update_profile() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_update_profile')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('Please login to update profile.', 'realestate-booking-suite'));
        }
        
        $user_id = get_current_user_id();
        
        // Verify user can only update their own profile
        $profile_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : $user_id;
        if ($profile_user_id !== $user_id && !current_user_can('edit_users')) {
            wp_send_json_error(esc_html__('You can only update your own profile.', 'realestate-booking-suite'));
        }
        
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $bio = isset($_POST['bio']) ? sanitize_textarea_field($_POST['bio']) : '';
        $website = isset($_POST['website']) ? esc_url_raw($_POST['website']) : '';
        
        // Validate required fields
        if (empty($email) || !is_email($email)) {
            wp_send_json_error(esc_html__('Please provide a valid email address.', 'realestate-booking-suite'));
        }
        
        // Check if email is already in use by another user
        $email_exists = email_exists($email);
        if ($email_exists && $email_exists !== $profile_user_id) {
            wp_send_json_error(esc_html__('This email address is already in use.', 'realestate-booking-suite'));
        }
        
        // Update user data
        $user_data = array(
            'ID' => $profile_user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_email' => $email,
            'user_url' => $website,
            'description' => $bio
        );
        
        $updated = wp_update_user($user_data);
        
        if (!is_wp_error($updated)) {
            // Update phone meta
            update_user_meta($profile_user_id, 'phone', $phone);
            
            wp_send_json_success(esc_html__('Profile updated successfully.', 'realestate-booking-suite'));
        } else {
            wp_send_json_error(esc_html__('Failed to update profile.', 'realestate-booking-suite'));
        }
    }
    
    /**
     * Check if user owns booking
     */
    private function user_owns_booking($booking_id, $user_id) {
        if ($booking_id <= 0 || $user_id <= 0) {
            return false;
        }
        
        // Allow admins to access any booking
        if (current_user_can('manage_options')) {
            return true;
        }
        
        $booking_author = get_post_field('post_author', $booking_id);
        return (int) $booking_author === (int) $user_id;
    }
    
    /**
     * Handle Elementor property loading
     */
    public function handle_elementor_load_properties() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        $settings = isset($_POST['settings']) && is_array($_POST['settings']) ? RESBS_Security::sanitize_array($_POST['settings']) : array();
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $filters = isset($_POST['filters']) && is_array($_POST['filters']) ? RESBS_Security::sanitize_array($_POST['filters']) : array();
        
        // Sanitize settings
        $posts_per_page = isset($settings['posts_per_page']) ? intval($settings['posts_per_page']) : 12;
        $orderby = isset($settings['orderby']) ? sanitize_text_field($settings['orderby']) : 'date';
        $order = isset($settings['order']) ? sanitize_text_field($settings['order']) : 'DESC';
        
        // Build query args
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
            'orderby' => $orderby,
            'order' => $order,
        );
        
        // Add meta query for custom ordering
        if ($orderby === 'price') {
            $args['meta_key'] = '_property_price';
            $args['orderby'] = 'meta_value_num';
        }
        
        // Add taxonomy filters
        $tax_query = array();
        
        if (!empty($filters['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($filters['property_type'])
            );
        }
        
        if (!empty($filters['property_status'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => sanitize_text_field($filters['property_status'])
            );
        }
        
        if (!empty($filters['location'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_location',
                'field' => 'slug',
                'terms' => sanitize_text_field($filters['location'])
            );
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        // Add meta filters
        $meta_query = array();
        
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $price_query = array(
                'key' => '_property_price',
                'type' => 'NUMERIC'
            );
            
            if (!empty($filters['price_min'])) {
                $price_query['value'] = floatval($filters['price_min']);
                $price_query['compare'] = '>=';
            }
            
            if (!empty($filters['price_max'])) {
                if (!empty($filters['price_min'])) {
                    $price_query['compare'] = 'BETWEEN';
                    $price_query['value'] = array(
                        floatval($filters['price_min']),
                        floatval($filters['price_max'])
                    );
                } else {
                    $price_query['value'] = floatval($filters['price_max']);
                    $price_query['compare'] = '<=';
                }
            }
            
            $meta_query[] = $price_query;
        }
        
        if (!empty($filters['bedrooms'])) {
            $meta_query[] = array(
                'key' => '_property_bedrooms',
                'value' => intval($filters['bedrooms']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        if (!empty($filters['bathrooms'])) {
            $meta_query[] = array(
                'key' => '_property_bathrooms',
                'value' => floatval($filters['bathrooms']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        // Execute query
        $query = new WP_Query($args);
        
        $properties = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $properties[] = $this->format_property_for_elementor(get_post());
            }
            wp_reset_postdata();
        }
        
        // Prepare pagination data
        $pagination = array(
            'current_page' => $page,
            'total_pages' => $query->max_num_pages,
            'total_posts' => $query->found_posts,
            'has_more' => $page < $query->max_num_pages
        );
        
        wp_send_json_success(array(
            'properties' => $properties,
            'pagination' => $pagination
        ));
    }
    
    /**
     * Format property data for Elementor
     */
    private function format_property_for_elementor($post) {
        $property_id = $post->ID;
        $price = get_post_meta($property_id, '_property_price', true);
        $bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        $area = get_post_meta($property_id, '_property_size', true) ?: get_post_meta($property_id, '_property_area_sqft', true);
        
        // Get featured image
        $featured_image = get_the_post_thumbnail_url($property_id, 'medium');
        
        // Get property badges
        $badges = array();
        $property_status = wp_get_post_terms($property_id, 'property_status', array('fields' => 'names'));
        if (!empty($property_status)) {
            foreach ($property_status as $status) {
                $badges[] = array(
                    'type' => sanitize_title($status),
                    'text' => esc_html($status)
                );
            }
        }
        
        // Check if property is featured (you can add custom logic here)
        if (get_post_meta($property_id, '_property_featured', true)) {
            $badges[] = array(
                'type' => 'featured',
                'text' => esc_html__('Featured', 'realestate-booking-suite')
            );
        }
        
        return array(
            'id' => $property_id,
            'title' => esc_html($post->post_title),
            'url' => esc_url(get_permalink($property_id)),
            'excerpt' => esc_html(wp_trim_words($post->post_content, 20)),
            'featured_image' => $featured_image ? esc_url($featured_image) : '',
            'price' => $price ? floatval($price) : 0,
            'price_formatted' => $price ? esc_html(number_format(floatval($price))) : '',
            'meta' => array(
                'bedrooms' => $bedrooms ? intval($bedrooms) : 0,
                'bathrooms' => $bathrooms ? floatval($bathrooms) : 0,
                'area' => $area ? esc_html(number_format(floatval($area))) : ''
            ),
            'badges' => $badges
        );
    }
    
    /**
     * Handle toggle favorite
     */
    public function handle_toggle_favorite() {
        // Check if nonce exists
        if (!isset($_POST['nonce']) || empty($_POST['nonce'])) {
            return; // Let other handlers process it
        }
        
        // CRITICAL: Do NOT sanitize nonce before verification
        // Only process if this handler's nonce matches
        // If not, let other handlers (Favorites Manager) process it
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_elementor_nonce')) {
            // Not our nonce - let other handlers process it
            return;
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('Please login to add favorites.', 'realestate-booking-suite'));
        }
        
        $property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;
        $user_id = get_current_user_id();
        
        if ($property_id <= 0 || !get_post($property_id)) {
            wp_send_json_error(esc_html__('Invalid property.', 'realestate-booking-suite'));
        }
        
        // Get user's favorites
        $favorites = get_user_meta($user_id, '_resbs_favorites', true);
        if (!is_array($favorites)) {
            $favorites = array();
        }
        
        // Toggle favorite
        if (in_array($property_id, $favorites)) {
            $favorites = array_diff($favorites, array($property_id));
            $is_favorite = false;
        } else {
            $favorites[] = $property_id;
            $is_favorite = true;
        }
        
        // Update user meta
        update_user_meta($user_id, '_resbs_favorites', $favorites);
        
        wp_send_json_success(array(
            'is_favorite' => $is_favorite,
            'message' => $is_favorite ? 
                esc_html__('Added to favorites!', 'realestate-booking-suite') : 
                esc_html__('Removed from favorites!', 'realestate-booking-suite')
        ));
    }
    
    /**
     * Handle Elementor carousel property loading
     */
    public function handle_elementor_load_carousel_properties() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        $settings = isset($_POST['settings']) && is_array($_POST['settings']) ? RESBS_Security::sanitize_array($_POST['settings']) : array();
        
        // Sanitize settings
        $posts_per_page = isset($settings['posts_per_page']) ? intval($settings['posts_per_page']) : 12;
        $orderby = isset($settings['orderby']) ? sanitize_text_field($settings['orderby']) : 'date';
        $order = isset($settings['order']) ? sanitize_text_field($settings['order']) : 'DESC';
        
        // Build query args
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'orderby' => $orderby,
            'order' => $order,
        );
        
        // Add meta query for custom ordering
        if ($orderby === 'price') {
            $args['meta_key'] = '_property_price';
            $args['orderby'] = 'meta_value_num';
        } elseif ($orderby === 'featured') {
            $args['meta_key'] = '_property_featured';
            $args['meta_value'] = '1';
            $args['orderby'] = 'meta_value';
        }
        
        // Add taxonomy filters
        $tax_query = array();
        
        if (!empty($settings['property_types']) && is_array($settings['property_types'])) {
            $property_types = array_map('sanitize_text_field', $settings['property_types']);
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $property_types
            );
        }
        
        if (!empty($settings['property_statuses']) && is_array($settings['property_statuses'])) {
            $property_statuses = array_map('sanitize_text_field', $settings['property_statuses']);
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $property_statuses
            );
        }
        
        if (!empty($tax_query)) {
            $args['tax_query'] = $tax_query;
        }
        
        // Add meta filters
        $meta_query = array();
        
        if (!empty($settings['price_min']) || !empty($settings['price_max'])) {
            $price_query = array(
                'key' => '_property_price',
                'type' => 'NUMERIC'
            );
            
            if (!empty($settings['price_min'])) {
                $price_query['value'] = floatval($settings['price_min']);
                $price_query['compare'] = '>=';
            }
            
            if (!empty($settings['price_max'])) {
                if (!empty($settings['price_min'])) {
                    $price_query['compare'] = 'BETWEEN';
                    $price_query['value'] = array(
                        floatval($settings['price_min']),
                        floatval($settings['price_max'])
                    );
                } else {
                    $price_query['value'] = floatval($settings['price_max']);
                    $price_query['compare'] = '<=';
                }
            }
            
            $meta_query[] = $price_query;
        }
        
        if (!empty($settings['featured_only']) && $settings['featured_only'] === 'yes') {
            $meta_query[] = array(
                'key' => '_property_featured',
                'value' => '1',
                'compare' => '='
            );
        }
        
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        // Execute query
        $query = new WP_Query($args);
        
        $properties = array();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $properties[] = $this->format_property_for_elementor(get_post());
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success(array(
            'properties' => $properties
        ));
    }
    
    /**
     * Add frontend styles for property features
     * DEPRECATED: Styles are now enqueued via wp_enqueue_style in enqueue_frontend_scripts()
     * This method is kept for backward compatibility but does nothing
     */
    public function add_frontend_styles() {
        // Styles are now handled via wp_enqueue_style and wp_add_inline_style
        // This method is kept to prevent errors if still referenced elsewhere
    }
    
    /**
     * Darken a hex color by a percentage
     * @param string $color Hex color (e.g., #0073aa)
     * @param int $percent Percentage to darken (0-100)
     * @return string Darkened hex color
     */
    private function darken_color($color, $percent) {
        $color = ltrim($color, '#');
        
        // Validate hex color format
        if (!preg_match('/^[a-fA-F0-9]{6}$/', $color)) {
            $color = '0073aa'; // Fallback to safe default
        }
        
        $rgb = array(
            hexdec(substr($color, 0, 2)),
            hexdec(substr($color, 2, 2)),
            hexdec(substr($color, 4, 2))
        );
        
        // Validate percent
        $percent = max(0, min(100, absint($percent)));
        
        for ($i = 0; $i < 3; $i++) {
            $rgb[$i] = round($rgb[$i] * (1 - $percent / 100));
            $rgb[$i] = max(0, min(255, $rgb[$i]));
        }
        
        return '#' . str_pad(dechex($rgb[0]), 2, '0', STR_PAD_LEFT) .
                   str_pad(dechex($rgb[1]), 2, '0', STR_PAD_LEFT) .
                   str_pad(dechex($rgb[2]), 2, '0', STR_PAD_LEFT);
    }
    
    /**
     * Display property features on frontend
     */
    public static function display_property_features($post_id = null) {
        if (!$post_id) {
            global $post;
            $post_id = $post->ID;
        }
        
        $features = get_post_meta($post_id, '_property_features', true);
        
        if (empty($features)) {
            return '<div class="resbs-property-features">
                        <h3>' . esc_html__('Property Features', 'realestate-booking-suite') . '</h3>
                        <div class="resbs-no-features">' . esc_html__('No features listed for this property.', 'realestate-booking-suite') . '</div>
                    </div>';
        }
        
        // Sanitize and split features
        $features_array = array_map('trim', explode(',', $features));
        $features_array = array_filter($features_array, function($feature) {
            return !empty($feature);
        });
        
        if (empty($features_array)) {
            return '<div class="resbs-property-features">
                        <h3>' . esc_html__('Property Features', 'realestate-booking-suite') . '</h3>
                        <div class="resbs-no-features">' . esc_html__('No features listed for this property.', 'realestate-booking-suite') . '</div>
                    </div>';
        }
        
        $output = '<div class="resbs-property-features">
                        <h3>' . esc_html__('Property Features', 'realestate-booking-suite') . '</h3>
                        <div class="resbs-features-list">';
        
        foreach ($features_array as $feature) {
            $clean_feature = sanitize_text_field($feature);
            if (!empty($clean_feature)) {
                $output .= '<div class="resbs-feature-item">' . esc_html($clean_feature) . '</div>';
            }
        }
        
        $output .= '</div></div>';
        
        return $output;
    }
    
    /**
     * Display property amenities on frontend
     */
    public static function display_property_amenities($post_id = null) {
        if (!$post_id) {
            global $post;
            $post_id = $post->ID;
        }
        
        $amenities = get_post_meta($post_id, '_property_amenities', true);
        
        if (empty($amenities)) {
            return '';
        }
        
        // Sanitize and split amenities
        $amenities_array = array_map('trim', explode(',', $amenities));
        $amenities_array = array_filter($amenities_array, function($amenity) {
            return !empty($amenity);
        });
        
        if (empty($amenities_array)) {
            return '';
        }
        
        $output = '<div class="resbs-property-features">
                        <h3>' . esc_html__('Property Amenities', 'realestate-booking-suite') . '</h3>
                        <div class="resbs-features-list">';
        
        foreach ($amenities_array as $amenity) {
            $clean_amenity = sanitize_text_field($amenity);
            if (!empty($clean_amenity)) {
                $output .= '<div class="resbs-feature-item">' . esc_html($clean_amenity) . '</div>';
            }
        }
        
        $output .= '</div></div>';
        
        return $output;
    }
    
    /**
     * Handle Elementor request form submission
     * 
     * SECURITY:
     * - Verifies nonce to prevent CSRF attacks
     * - Accepts both AJAX nonce (resbs_elementor_nonce) and form nonce (resbs_request_nonce)
     * - Allows both logged-in and non-logged-in users (public form)
     * - Sanitizes and validates all user input
     * - Rate limiting should be implemented at server level
     */
    public function handle_elementor_submit_request() {
        // SECURITY: Verify nonce - check both AJAX nonce and form nonce for maximum compatibility
        $nonce_verified = false;
        
        // Check AJAX nonce (from JavaScript)
        if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')) {
            $nonce_verified = true;
        }
        
        // Check form nonce (from direct POST submission - fallback)
        if (!$nonce_verified && isset($_POST['resbs_request_nonce']) && wp_verify_nonce($_POST['resbs_request_nonce'], 'resbs_request_form')) {
            $nonce_verified = true;
        }
        
        if (!$nonce_verified) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite')
            ));
            return;
        }
        
        // SECURITY: No permission check needed - this is a public contact form
        // Both logged-in and non-logged-in users can submit
        // Rate limiting should be implemented at server level for spam protection
        
        // Get form data - handle both AJAX (form_data) and direct POST submissions
        $form_data = array();
        
        if (isset($_POST['form_data'])) {
            // AJAX submission - parse serialized form data
            // Note: parse_str expects URL-encoded string, not sanitized text
            // We'll sanitize after parsing
            $form_data_raw = isset($_POST['form_data']) && is_string($_POST['form_data']) ? $_POST['form_data'] : '';
            if (!empty($form_data_raw)) {
                parse_str($form_data_raw, $form_data);
                // Sanitize parsed data - handle arrays properly
                if (is_array($form_data)) {
                    array_walk_recursive($form_data, function(&$value) {
                        if (is_string($value)) {
                            $value = sanitize_text_field($value);
                        }
                    });
                }
            } else {
                $form_data = array();
            }
        } else {
            // Direct POST submission - sanitize $_POST data
            // Note: Nonces are already verified above, so we can safely sanitize here
            $form_data = array();
            foreach ($_POST as $key => $value) {
                $sanitized_key = sanitize_key($key);
                if (is_array($value)) {
                    $form_data[$sanitized_key] = array_map('sanitize_text_field', $value);
                } else {
                    // Use sanitize_text_field for most fields, but preserve structure
                    $form_data[$sanitized_key] = sanitize_text_field($value);
                }
            }
        }
        
        // SECURITY: Sanitize all user input
        $name = isset($form_data['name']) ? sanitize_text_field($form_data['name']) : '';
        $email = isset($form_data['email']) ? sanitize_email($form_data['email']) : '';
        $phone = isset($form_data['phone']) ? sanitize_text_field($form_data['phone']) : '';
        $message = isset($form_data['message']) ? sanitize_textarea_field($form_data['message']) : '';
        $property_id = isset($form_data['property_id']) ? intval($form_data['property_id']) : 0;
        
        // SECURITY: Validate required fields
        if (empty($name) || empty($email)) {
            wp_send_json_error(array(
                'message' => esc_html__('Please fill in all required fields.', 'realestate-booking-suite')
            ));
            return;
        }
        
        // SECURITY: Validate email format
        if (!is_email($email)) {
            wp_send_json_error(array(
                'message' => esc_html__('Please enter a valid email address.', 'realestate-booking-suite')
            ));
            return;
        }
        
        // SECURITY: Validate property ID if provided (must be a valid post)
        if ($property_id > 0) {
            $property = get_post($property_id);
            if (!$property || $property->post_type !== 'property') {
                wp_send_json_error(array(
                    'message' => esc_html__('Invalid property selected.', 'realestate-booking-suite')
                ));
                return;
            }
        }
        
        // Get property and agent information
        $property_title = '';
        $property_url = '';
        $agent_email = '';
        $agent_name = '';
        
        if ($property_id) {
            $property_title = get_the_title($property_id);
            $property_url = get_permalink($property_id);
            $agent_email = get_post_meta($property_id, '_property_agent_email', true);
            $agent_name = get_post_meta($property_id, '_property_agent_name', true);
        }
        
        // Prepare email content - escape property title for subject
        $property_title_escaped = $property_title ? esc_html($property_title) : esc_html(get_bloginfo('name'));
        $subject = sprintf(esc_html__('New Property Inquiry: %s', 'realestate-booking-suite'), $property_title_escaped);
        
        // HTML email content
        $email_message = '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $email_message .= '<h2 style="color: #10b981; border-bottom: 2px solid #10b981; padding-bottom: 10px;">' . esc_html__('New Property Inquiry', 'realestate-booking-suite') . '</h2>';
        $email_message .= '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        $email_message .= '<tr><td style="padding: 8px; font-weight: bold; width: 150px;">' . esc_html__('Name:', 'realestate-booking-suite') . '</td><td style="padding: 8px;">' . esc_html($name) . '</td></tr>';
        $email_message .= '<tr><td style="padding: 8px; font-weight: bold;">' . esc_html__('Email:', 'realestate-booking-suite') . '</td><td style="padding: 8px;"><a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a></td></tr>';
        
        if (!empty($phone)) {
            $email_message .= '<tr><td style="padding: 8px; font-weight: bold;">' . esc_html__('Phone:', 'realestate-booking-suite') . '</td><td style="padding: 8px;"><a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a></td></tr>';
        }
        
        if ($property_title) {
            $email_message .= '<tr><td style="padding: 8px; font-weight: bold;">' . esc_html__('Property:', 'realestate-booking-suite') . '</td><td style="padding: 8px;"><a href="' . esc_url($property_url) . '">' . esc_html($property_title) . '</a></td></tr>';
        }
        
        $email_message .= '</table>';
        $email_message .= '<div style="margin: 20px 0; padding: 15px; background: #f9fafb; border-left: 4px solid #10b981;">';
        $email_message .= '<strong>' . esc_html__('Message:', 'realestate-booking-suite') . '</strong><br>';
        $email_message .= nl2br(esc_html($message));
        $email_message .= '</div>';
        $email_message .= '</div>';
        
        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . esc_html($name) . ' <' . sanitize_email($email) . '>',
            'Reply-To: ' . esc_html($name) . ' <' . sanitize_email($email) . '>'
        );
        
        // Send to admin
        $admin_email = get_option('admin_email');
        $admin_sent = wp_mail($admin_email, $subject, $email_message, $headers);
        
        // Send to property agent if available
        $agent_sent = false;
        if (!empty($agent_email) && is_email($agent_email)) {
            $agent_sent = wp_mail($agent_email, $subject, $email_message, $headers);
        }
        
        // Send confirmation to user
        $user_subject = esc_html__('Thank you for your inquiry', 'realestate-booking-suite');
        $user_message = '<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">';
        $user_message .= '<h2 style="color: #10b981;">' . esc_html__('Thank You!', 'realestate-booking-suite') . '</h2>';
        $user_message .= '<p>' . esc_html__('We have received your inquiry and will get back to you as soon as possible.', 'realestate-booking-suite') . '</p>';
        if ($property_title) {
            $user_message .= '<p><strong>' . esc_html__('Property:', 'realestate-booking-suite') . '</strong> <a href="' . esc_url($property_url) . '">' . esc_html($property_title) . '</a></p>';
        }
        $user_message .= '<hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">';
        $user_message .= '<p style="font-size: 12px; color: #6b7280;">' . esc_html__('This is an automated confirmation email.', 'realestate-booking-suite') . '</p>';
        $user_message .= '</div>';
        
        $user_headers = array('Content-Type: text/html; charset=UTF-8');
        $user_sent = wp_mail($email, $user_subject, $user_message, $user_headers);
        
        // Return success if at least admin or agent email was sent
        if ($admin_sent || $agent_sent) {
            wp_send_json_success(array(
                'message' => esc_html__('Thank you! Your request has been submitted successfully. We will contact you soon.', 'realestate-booking-suite')
            ));
        } else {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to send request. Please try again later or contact us directly.', 'realestate-booking-suite')
            ));
        }
    }
    
    /**
     * Handle Elementor login
     */
    public function handle_elementor_login() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        $username = isset($_POST['username']) ? sanitize_user($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        if (empty($username) || empty($password)) {
            wp_send_json_error(esc_html__('Please enter both username and password.', 'realestate-booking-suite'));
        }
        
        // Rate limiting: Prevent brute force attacks
        $client_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : 'unknown';
        $transient_key = 'resbs_login_attempts_' . md5($username . $client_ip);
        $attempts = get_transient($transient_key);
        
        if ($attempts && $attempts >= 5) {
            wp_send_json_error(esc_html__('Too many login attempts. Please try again later.', 'realestate-booking-suite'));
        }
        
        // Attempt login
        $credentials = array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => true
        );
        
        $user = wp_signon($credentials, false);
        
        if (is_wp_error($user)) {
            // Increment failed attempts
            $attempts = $attempts ? $attempts + 1 : 1;
            set_transient($transient_key, $attempts, 15 * MINUTE_IN_SECONDS);
            
            wp_send_json_error(esc_html($user->get_error_message()));
        } else {
            // Clear failed attempts on success
            delete_transient($transient_key);
            
            wp_send_json_success(array(
                'message' => esc_html__('Login successful!', 'realestate-booking-suite'),
                'redirect' => esc_url(home_url())
            ));
        }
    }
    
    /**
     * Handle Elementor logout
     */
    public function handle_elementor_logout() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        wp_logout();
        
        wp_send_json_success(array(
            'message' => esc_html__('Logged out successfully!', 'realestate-booking-suite')
        ));
    }
    
    /**
     * Handle Elementor listings load
     */
    public function handle_elementor_load_listings() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        $widget_id = isset($_POST['widget_id']) ? sanitize_text_field($_POST['widget_id']) : '';
        $filters = isset($_POST['filters']) && is_array($_POST['filters']) ? RESBS_Security::sanitize_array($_POST['filters']) : array();
        
        // Build query
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => isset($filters['posts_per_page']) ? intval($filters['posts_per_page']) : 12,
            'paged' => isset($filters['page']) ? intval($filters['page']) : 1,
        );
        
        // Handle sorting
        if (!empty($filters['sort'])) {
            $sort_parts = explode('_', $filters['sort']);
            if (count($sort_parts) === 2) {
                $orderby = $sort_parts[0];
                $order = strtoupper($sort_parts[1]);
                
                // Validate orderby value
                $allowed_orderby = array('date', 'title', 'menu_order', 'rand', 'price', 'featured');
                if (!in_array($orderby, $allowed_orderby, true)) {
                    $orderby = 'date';
                }
                
                if ($orderby === 'price') {
                    $args['meta_key'] = '_property_price';
                    $args['orderby'] = 'meta_value_num';
                } else {
                    $args['orderby'] = $orderby;
                }
                
                // Validate order value
                $order = strtoupper($order);
                if ($order !== 'ASC' && $order !== 'DESC') {
                    $order = 'DESC';
                }
                $args['order'] = $order;
            }
        }
        
        $query = new WP_Query($args);
        
        ob_start();
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                // Render property card HTML
                $property_id = get_the_ID();
                $price = get_post_meta($property_id, '_property_price', true);
                $bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
                $bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
                // Get area using helper function
                $area_value = resbs_get_property_area($property_id, '_property_area_sqft');
                $location = get_the_terms($property_id, 'property_location');
                $featured_image = get_the_post_thumbnail_url($property_id, 'medium_large');
                
                echo '<div class="resbs-property-card" data-property-id="' . esc_attr($property_id) . '">';
                echo '<div class="resbs-property-image">';
                if ($featured_image) {
                    echo '<a href="' . esc_url(get_permalink()) . '"><img src="' . esc_url($featured_image) . '" alt="' . esc_attr(get_the_title()) . '"></a>';
                } else {
                    echo '<a href="' . esc_url(get_permalink()) . '"><div class="resbs-placeholder-image"></div></a>';
                }
                echo '<button type="button" class="resbs-favorite-btn" data-property-id="' . esc_attr($property_id) . '"><span class="dashicons dashicons-heart"></span></button>';
                echo '</div>';
                echo '<div class="resbs-property-content">';
                if ($location && !is_wp_error($location) && !empty($location)) {
                    echo '<div class="resbs-property-location">' . esc_html($location[0]->name) . '</div>';
                }
                echo '<h3 class="resbs-property-title"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
                if ($price) {
                    echo '<div class="resbs-property-price">$' . esc_html(number_format($price)) . '</div>';
                }
                echo '<div class="resbs-property-meta">';
                if ($bedrooms) {
                    $bedrooms_text = $bedrooms == 1 ? esc_html__('bed', 'realestate-booking-suite') : esc_html__('beds', 'realestate-booking-suite');
                    echo '<span class="resbs-meta-item"><span class="dashicons dashicons-bed-alt"></span>' . esc_html($bedrooms) . ' ' . $bedrooms_text . '</span>';
                }
                if ($bathrooms) {
                    $bathrooms_text = $bathrooms == 1 ? esc_html__('bath', 'realestate-booking-suite') : esc_html__('baths', 'realestate-booking-suite');
                    echo '<span class="resbs-meta-item"><span class="dashicons dashicons-bath"></span>' . esc_html($bathrooms) . ' ' . $bathrooms_text . '</span>';
                }
                if ($area_value) {
                    echo '<span class="resbs-meta-item"><span class="dashicons dashicons-admin-home"></span>' . esc_html(resbs_format_area($area_value)) . '</span>';
                }
                echo '</div></div></div>';
            }
            wp_reset_postdata();
        }
        $html = ob_get_clean();
        
        wp_send_json_success(array(
            'html' => $html,
            'found_posts' => $query->found_posts,
            'max_pages' => $query->max_num_pages
        ));
    }
    
    /**
     * Handle Elementor map properties load
     */
    public function handle_elementor_load_map_properties() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        $widget_id = isset($_POST['widget_id']) ? sanitize_text_field($_POST['widget_id']) : '';
        
        // Build query
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => 50, // Limit for map display
        );
        
        $query = new WP_Query($args);
        $properties = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $property_id = get_the_ID();
                
                $price_value = get_post_meta($property_id, '_property_price', true);
                $properties[] = array(
                    'id' => $property_id,
                    'title' => esc_html(get_the_title()),
                    'url' => esc_url(get_permalink()),
                    'price' => $price_value ? floatval($price_value) : 0,
                    'price_formatted' => $price_value ? esc_html(resbs_format_price($price_value)) : '',
                    'latitude' => esc_attr(get_post_meta($property_id, '_property_latitude', true)),
                    'longitude' => esc_attr(get_post_meta($property_id, '_property_longitude', true)),
                    'featured_image' => esc_url(get_the_post_thumbnail_url($property_id, 'medium'))
                );
            }
            wp_reset_postdata();
        }
        
        wp_send_json_success(array(
            'properties' => $properties
        ));
    }
}

// Initialize the class
new RESBS_Frontend();
