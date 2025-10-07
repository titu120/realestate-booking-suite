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
        // Verify nonce
        if (!wp_verify_nonce($_POST['resbs_nonce'], 'resbs_submit_property')) {
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
            'property_bedrooms' => 'absint',
            'property_bathrooms' => 'sanitize_text_field',
            'property_area' => 'absint',
            'property_latitude' => 'sanitize_text_field',
            'property_longitude' => 'sanitize_text_field',
            'property_video_url' => 'esc_url_raw',
            'property_description' => 'sanitize_textarea_field'
        );
        
        foreach ($meta_fields as $field => $sanitize_function) {
            if (isset($_POST[$field])) {
                $value = call_user_func($sanitize_function, $_POST[$field]);
                update_post_meta($post_id, '_resbs_' . str_replace('property_', '', $field), $value);
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
        
        // Set taxonomies
        $taxonomies = array('property_type', 'property_status', 'property_location');
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
        
        $atts = shortcode_atts(array(
            'show_properties' => 'true',
            'show_bookings' => 'true',
            'show_profile' => 'true',
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
                        <a href="?tab=properties" class="resbs-nav-tab <?php echo $current_tab === 'properties' ? 'active' : ''; ?>">
                            <?php esc_html_e('My Properties', 'realestate-booking-suite'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_bookings'] === 'true'): ?>
                        <a href="?tab=bookings" class="resbs-nav-tab <?php echo $current_tab === 'bookings' ? 'active' : ''; ?>">
                            <?php esc_html_e('My Bookings', 'realestate-booking-suite'); ?>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($atts['show_profile'] === 'true'): ?>
                        <a href="?tab=profile" class="resbs-nav-tab <?php echo $current_tab === 'profile' ? 'active' : ''; ?>">
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
                <h3><?php esc_html_e('My Profile', 'realestate-booking-suite'); ?></h3>
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
                <h4 class="resbs-property-title">
                    <a href="<?php echo esc_url(get_permalink($property->ID)); ?>">
                        <?php echo esc_html($property->post_title); ?>
                    </a>
                </h4>
                
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
                            <?php echo esc_html($area); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?>
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
                    <?php echo $order ? $order->get_formatted_order_total() : '$0.00'; ?>
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
                
                <a href="<?php echo $order ? esc_url($order->get_view_order_url()) : '#'; ?>" class="resbs-view-order-btn">
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
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')) {
            wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
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
}

// Initialize the class
new RESBS_Frontend();
