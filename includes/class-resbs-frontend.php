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
        
        // Add frontend display hooks
        add_action('wp_head', array($this, 'add_frontend_styles'));
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
        
        wp_enqueue_script(
            'resbs-frontend',
            RESBS_URL . 'assets/js/main.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('resbs-frontend', 'resbs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('resbs_frontend_nonce'),
            'upload_nonce' => wp_create_nonce('resbs_upload_nonce'),
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
                    <input type="number" id="property_price" name="property_price" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_resbs_price', true)) : ''; ?>" step="0.01" min="0" required>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_bedrooms"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label>
                    <input type="number" id="property_bedrooms" name="property_bedrooms" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_resbs_bedrooms', true)) : ''; ?>" min="0">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_bathrooms"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                    <input type="number" id="property_bathrooms" name="property_bathrooms" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_resbs_bathrooms', true)) : ''; ?>" min="0" step="0.5">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_area"><?php esc_html_e('Area (sq ft)', 'realestate-booking-suite'); ?></label>
                    <input type="number" id="property_area" name="property_area" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_resbs_area', true)) : ''; ?>" min="0">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_latitude"><?php esc_html_e('Latitude', 'realestate-booking-suite'); ?></label>
                    <input type="text" id="property_latitude" name="property_latitude" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_resbs_latitude', true)) : ''; ?>">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_longitude"><?php esc_html_e('Longitude', 'realestate-booking-suite'); ?></label>
                    <input type="text" id="property_longitude" name="property_longitude" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_resbs_longitude', true)) : ''; ?>">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_video_url"><?php esc_html_e('Video URL', 'realestate-booking-suite'); ?></label>
                    <input type="url" id="property_video_url" name="property_video_url" value="<?php echo $property ? esc_attr(get_post_meta($property->ID, '_resbs_video_url', true)) : ''; ?>" placeholder="<?php esc_attr_e('YouTube or Vimeo URL', 'realestate-booking-suite'); ?>">
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_description"><?php esc_html_e('Additional Description', 'realestate-booking-suite'); ?></label>
                    <textarea id="property_description" name="property_description" rows="3"><?php echo $property ? esc_textarea(get_post_meta($property->ID, '_resbs_description', true)) : ''; ?></textarea>
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
                            $gallery = get_post_meta($property->ID, '_resbs_gallery', true);
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
                    ?>
                    <select id="property_type" name="property_type">
                        <option value=""><?php esc_html_e('Select Property Type', 'realestate-booking-suite'); ?></option>
                        <?php foreach ($property_types as $type): ?>
                            <option value="<?php echo esc_attr($type->term_id); ?>" <?php selected($property ? wp_get_post_terms($property->ID, 'property_type', array('fields' => 'ids')) : array(), $type->term_id); ?>>
                                <?php echo esc_html($type->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="resbs-form-row">
                    <label for="property_status"><?php esc_html_e('Property Status', 'realestate-booking-suite'); ?></label>
                    <?php
                    $property_statuses = get_terms(array(
                        'taxonomy' => 'property_status',
                        'hide_empty' => false
                    ));
                    ?>
                    <select id="property_status" name="property_status">
                        <option value=""><?php esc_html_e('Select Property Status', 'realestate-booking-suite'); ?></option>
                        <?php foreach ($property_statuses as $status): ?>
                            <option value="<?php echo esc_attr($status->term_id); ?>" <?php selected($property ? wp_get_post_terms($property->ID, 'property_status', array('fields' => 'ids')) : array(), $status->term_id); ?>>
                                <?php echo esc_html($status->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
        
        $edit_id = intval($_POST['edit_id']);
        $user_id = get_current_user_id();
        
        // Validate required fields
        $title = sanitize_text_field($_POST['property_title']);
        $content = sanitize_textarea_field($_POST['property_content']);
        $price = floatval($_POST['property_price']);
        
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
            'property_price' => 'floatval',
            'property_bedrooms' => 'floatval',
            'property_bathrooms' => 'floatval',
            'property_area' => 'absint',
            'property_size' => 'absint',
            'property_latitude' => 'sanitize_text_field',
            'property_longitude' => 'sanitize_text_field',
            'property_video_url' => 'esc_url_raw',
            'property_description' => 'sanitize_textarea_field'
        );
        
        foreach ($meta_fields as $field => $sanitize_function) {
            if (isset($_POST[$field])) {
                $value = call_user_func($sanitize_function, $_POST[$field]);
                $meta_key = '_property_' . str_replace('property_', '', $field);
                update_post_meta($post_id, $meta_key, $value);
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
            $this->handle_image_upload($post_id, 'featured');
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
     * Handle image upload
     */
    private function handle_image_upload($post_id, $type = 'featured') {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploadedfile = $_FILES['property_image'];
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
            }
        }
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
            update_post_meta($post_id, '_resbs_gallery', implode(',', $gallery_ids));
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
        
        // Get user's bookings (this would need to be integrated with WooCommerce orders)
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
        $price = get_post_meta($property->ID, '_resbs_price', true);
        $bedrooms = get_post_meta($property->ID, '_resbs_bedrooms', true);
        $bathrooms = get_post_meta($property->ID, '_resbs_bathrooms', true);
        $area = get_post_meta($property->ID, '_resbs_area', true);
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
                        <span class="resbs-property-price">$<?php echo esc_html(number_format(floatval($price))); ?></span>
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
        $property = get_post($booking['property_id']);
        $order = wc_get_order($booking['order_id']);
        ?>
        <div class="resbs-booking-item" data-booking-id="<?php echo esc_attr($booking['id']); ?>">
            <div class="resbs-booking-property">
                <h4 class="resbs-booking-title">
                    <a href="<?php echo esc_url(get_permalink($booking['property_id'])); ?>">
                        <?php echo esc_html($property ? $property->post_title : esc_html__('Property not found', 'realestate-booking-suite')); ?>
                    </a>
                </h4>
                <div class="resbs-booking-dates">
                    <span class="resbs-checkin">
                        <strong><?php esc_html_e('Check-in:', 'realestate-booking-suite'); ?></strong>
                        <?php echo esc_html(date('M j, Y', strtotime($booking['checkin_date']))); ?>
                    </span>
                    <span class="resbs-checkout">
                        <strong><?php esc_html_e('Check-out:', 'realestate-booking-suite'); ?></strong>
                        <?php echo esc_html(date('M j, Y', strtotime($booking['checkout_date']))); ?>
                    </span>
                </div>
            </div>
            
            <div class="resbs-booking-details">
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
                
                <div class="resbs-booking-status resbs-status-<?php echo esc_attr($booking['status']); ?>">
                    <?php echo esc_html(ucfirst($booking['status'])); ?>
                </div>
                
                <div class="resbs-booking-total">
                    <?php echo $order ? wp_kses_post($order->get_formatted_order_total()) : esc_html('$0.00'); ?>
                </div>
            </div>
            
            <div class="resbs-booking-actions">
                <?php if ($booking['status'] === 'confirmed'): ?>
                    <button type="button" class="resbs-cancel-booking-btn" data-booking-id="<?php echo esc_attr($booking['id']); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                        <?php esc_html_e('Cancel', 'realestate-booking-suite'); ?>
                    </button>
                <?php endif; ?>
                
                <?php if ($booking['status'] === 'completed'): ?>
                    <button type="button" class="resbs-refund-booking-btn" data-booking-id="<?php echo esc_attr($booking['id']); ?>">
                        <span class="dashicons dashicons-money-alt"></span>
                        <?php esc_html_e('Request Refund', 'realestate-booking-suite'); ?>
                    </button>
                <?php endif; ?>
                
                <a href="<?php echo $order ? esc_url($order->get_view_order_url()) : esc_url('#'); ?>" class="resbs-view-order-btn">
                    <span class="dashicons dashicons-visibility"></span>
                    <?php esc_html_e('View Order', 'realestate-booking-suite'); ?>
                </a>
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
        // This would integrate with WooCommerce orders
        // For now, return empty array
        return array();
    }
    
    /**
     * Get user bookings count
     */
    private function get_user_bookings_count($user_id) {
        // This would integrate with WooCommerce orders
        return 0;
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
        // This would integrate with WooCommerce orders
        return 0;
    }
    
    /**
     * Get completed bookings count
     */
    private function get_completed_bookings_count($user_id) {
        // This would integrate with WooCommerce orders
        return 0;
    }
    
    /**
     * Handle cancel booking
     */
    public function handle_cancel_booking() {
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_cancel_booking')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('Please login to cancel bookings.', 'realestate-booking-suite'));
        }
        
        $booking_id = intval($_POST['booking_id']);
        $user_id = get_current_user_id();
        
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
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_refund_booking')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('Please login to request refunds.', 'realestate-booking-suite'));
        }
        
        $booking_id = intval($_POST['booking_id']);
        $user_id = get_current_user_id();
        
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
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_update_profile')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('Please login to update profile.', 'realestate-booking-suite'));
        }
        
        $user_id = get_current_user_id();
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $bio = sanitize_textarea_field($_POST['bio']);
        $website = esc_url_raw($_POST['website']);
        
        // Update user data
        $user_data = array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'user_email' => $email,
            'user_url' => $website,
            'description' => $bio
        );
        
        $updated = wp_update_user($user_data);
        
        if (!is_wp_error($updated)) {
            // Update phone meta
            update_user_meta($user_id, 'phone', $phone);
            
            wp_send_json_success(esc_html__('Profile updated successfully.', 'realestate-booking-suite'));
        } else {
            wp_send_json_error(esc_html__('Failed to update profile.', 'realestate-booking-suite'));
        }
    }
    
    /**
     * Check if user owns booking
     */
    private function user_owns_booking($booking_id, $user_id) {
        $booking_author = get_post_field('post_author', $booking_id);
        return $booking_author == $user_id;
    }
    
    /**
     * Handle Elementor property loading
     */
    public function handle_elementor_load_properties() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        $settings = $_POST['settings'];
        $page = intval($_POST['page']);
        $filters = $_POST['filters'] ?? array();
        
        // Sanitize settings
        $posts_per_page = intval($settings['posts_per_page']);
        $orderby = sanitize_text_field($settings['orderby']);
        $order = sanitize_text_field($settings['order']);
        
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
            $args['meta_key'] = '_resbs_price';
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
                'key' => '_resbs_price',
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
                'key' => '_resbs_bedrooms',
                'value' => intval($filters['bedrooms']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        if (!empty($filters['bathrooms'])) {
            $meta_query[] = array(
                'key' => '_resbs_bathrooms',
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
        $price = get_post_meta($property_id, '_resbs_price', true);
        $bedrooms = get_post_meta($property_id, '_resbs_bedrooms', true);
        $bathrooms = get_post_meta($property_id, '_resbs_bathrooms', true);
        $area = get_post_meta($property_id, '_resbs_area', true);
        
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
        if (get_post_meta($property_id, '_resbs_featured', true)) {
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
        
        // Only process if this handler's nonce matches
        // If not, let other handlers (Favorites Manager) process it
        $nonce = sanitize_text_field($_POST['nonce']);
        if (!wp_verify_nonce($nonce, 'resbs_elementor_nonce')) {
            // Not our nonce - let other handlers process it
            return;
        }
        
        if (!is_user_logged_in()) {
            wp_send_json_error(esc_html__('Please login to add favorites.', 'realestate-booking-suite'));
        }
        
        $property_id = intval($_POST['property_id']);
        $user_id = get_current_user_id();
        
        if (!$property_id || !get_post($property_id)) {
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
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        $settings = $_POST['settings'];
        
        // Sanitize settings
        $posts_per_page = intval($settings['posts_per_page']);
        $orderby = sanitize_text_field($settings['orderby']);
        $order = sanitize_text_field($settings['order']);
        
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
            $args['meta_key'] = '_resbs_price';
            $args['orderby'] = 'meta_value_num';
        } elseif ($orderby === 'featured') {
            $args['meta_key'] = '_resbs_featured';
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
                'key' => '_resbs_price',
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
                'key' => '_resbs_featured',
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
     */
    public function add_frontend_styles() {
        // Get general color settings
        $main_color = resbs_get_main_color();
        $secondary_color = resbs_get_secondary_color();

        ?>
        <style>
        /* General Settings CSS Variables - Available on ALL pages (archive, single, widgets, etc.) */
        :root {
            --resbs-main-color: <?php echo esc_attr($main_color); ?>;
            --resbs-secondary-color: <?php echo esc_attr($secondary_color); ?>;
        }
        
        /* Note: Fonts are controlled by the active theme, not the plugin */
        
        /* Apply main color to large buttons */
        .resbs-btn-primary,
        .resbs-save-button,
        .resbs-submit-btn,
        button.resbs-primary {
            background-color: var(--resbs-main-color) !important;
            border-color: var(--resbs-main-color) !important;
            color: #fff !important;
        }
        
        .resbs-btn-primary:hover,
        .resbs-save-button:hover,
        .resbs-submit-btn:hover,
        button.resbs-primary:hover {
            background-color: <?php echo esc_attr($this->darken_color($main_color, 10)); ?> !important;
            border-color: <?php echo esc_attr($this->darken_color($main_color, 10)); ?> !important;
        }
        
        /* Apply secondary color to small buttons */
        .resbs-btn-secondary,
        .resbs-view-btn,
        .resbs-edit-btn,
        button.resbs-secondary {
            background-color: var(--resbs-secondary-color) !important;
            border-color: var(--resbs-secondary-color) !important;
        }
        
        .resbs-btn-secondary:hover,
        .resbs-view-btn:hover,
        .resbs-edit-btn:hover,
        button.resbs-secondary:hover {
            background-color: <?php echo esc_attr($this->darken_color($secondary_color, 10)); ?> !important;
            border-color: <?php echo esc_attr($this->darken_color($secondary_color, 10)); ?> !important;
        }
        
        <?php if (is_singular('property')): ?>
            /* Frontend Property Features Styles */
            .resbs-property-features {
                margin: 30px 0;
                padding: 25px;
                background: #f8f9fa;
                border-radius: 12px;
                border: 1px solid #e9ecef;
            }
            
            .resbs-property-features h3 {
                margin: 0 0 20px 0;
                font-size: 20px;
                font-weight: 600;
                color: #333;
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .resbs-property-features h3::before {
                content: '✓';
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                width: 24px;
                height: 24px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                font-weight: bold;
            }
            
            .resbs-features-list {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
            }
            
            .resbs-feature-item {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 16px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 20px;
                font-size: 14px;
                font-weight: 500;
                box-shadow: 0 2px 8px rgba(102, 126, 234, 0.2);
                transition: all 0.3s ease;
            }
            
            .resbs-feature-item:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
            }
            
            .resbs-feature-item::before {
                content: '✓';
                font-weight: bold;
                font-size: 12px;
            }
            
            .resbs-no-features {
                color: #6c757d;
                font-style: italic;
                text-align: center;
                padding: 20px;
                background: #fff;
                border-radius: 8px;
                border: 2px dashed #dee2e6;
            }
            </style>
        <?php endif; ?>
        <?php
    }
    
    /**
     * Darken a hex color by a percentage
     * @param string $color Hex color (e.g., #0073aa)
     * @param int $percent Percentage to darken (0-100)
     * @return string Darkened hex color
     */
    private function darken_color($color, $percent) {
        $color = str_replace('#', '', $color);
        $rgb = array(
            hexdec(substr($color, 0, 2)),
            hexdec(substr($color, 2, 2)),
            hexdec(substr($color, 4, 2))
        );
        
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
     */
    public function handle_elementor_submit_request() {
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
        }
        
        // Get form data
        parse_str($_POST['form_data'], $form_data);
        
        $name = isset($form_data['name']) ? sanitize_text_field($form_data['name']) : '';
        $email = isset($form_data['email']) ? sanitize_email($form_data['email']) : '';
        $phone = isset($form_data['phone']) ? sanitize_text_field($form_data['phone']) : '';
        $message = isset($form_data['message']) ? sanitize_textarea_field($form_data['message']) : '';
        $property_id = isset($form_data['property_id']) ? intval($form_data['property_id']) : 0;
        
        // Validate required fields
        if (empty($name) || empty($email)) {
            wp_send_json_error(esc_html__('Please fill in all required fields.', 'realestate-booking-suite'));
        }
        
        if (!is_email($email)) {
            wp_send_json_error(esc_html__('Please enter a valid email address.', 'realestate-booking-suite'));
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
        
        // Attempt login
        $credentials = array(
            'user_login' => $username,
            'user_password' => $password,
            'remember' => true
        );
        
        $user = wp_signon($credentials, false);
        
        if (is_wp_error($user)) {
            wp_send_json_error(esc_html($user->get_error_message()));
        } else {
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
        $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
        
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
                
                if ($orderby === 'price') {
                    $args['meta_key'] = '_property_price';
                    $args['orderby'] = 'meta_value_num';
                } else {
                    $args['orderby'] = $orderby;
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
                if ($location) {
                    echo '<div class="resbs-property-location">' . esc_html($location[0]->name) . '</div>';
                }
                echo '<h3 class="resbs-property-title"><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></h3>';
                if ($price) {
                    echo '<div class="resbs-property-price">$' . esc_html(number_format($price)) . '</div>';
                }
                echo '<div class="resbs-property-meta">';
                if ($bedrooms) {
                    echo '<span class="resbs-meta-item"><span class="dashicons dashicons-bed-alt"></span>' . esc_html($bedrooms) . ' beds</span>';
                }
                if ($bathrooms) {
                    echo '<span class="resbs-meta-item"><span class="dashicons dashicons-bath"></span>' . esc_html($bathrooms) . ' baths</span>';
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
                    'price_formatted' => $price_value ? esc_html('$' . number_format(floatval($price_value))) : '',
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
