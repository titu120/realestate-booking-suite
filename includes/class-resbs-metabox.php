<?php
/**
 * Property Metabox Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Metabox {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_property_metabox'));
        add_action('save_post', array($this, 'save_property_metabox'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_metabox_scripts'));
    }
    
    /**
     * Add property metabox
     */
    public function add_property_metabox() {
        add_meta_box(
            'resbs_property_details',
            esc_html__('Property Details', 'realestate-booking-suite'),
            array($this, 'property_metabox_callback'),
            'property',
            'normal',
            'high'
        );
    }
    
    /**
     * Enqueue metabox scripts and styles
     */
    public function enqueue_metabox_scripts($hook) {
        global $post_type;
        
        if ($post_type == 'property' && ($hook == 'post-new.php' || $hook == 'post.php')) {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');
        }
    }
    
    /**
     * Metabox callback function
     */
    public function property_metabox_callback($post) {
        // Add nonce for security
        wp_nonce_field('resbs_property_metabox', 'resbs_property_metabox_nonce');
        
        // Get existing values
        $price = get_post_meta($post->ID, '_resbs_price', true);
        $bedrooms = get_post_meta($post->ID, '_resbs_bedrooms', true);
        $bathrooms = get_post_meta($post->ID, '_resbs_bathrooms', true);
        $area = get_post_meta($post->ID, '_resbs_area', true);
        $latitude = get_post_meta($post->ID, '_resbs_latitude', true);
        $longitude = get_post_meta($post->ID, '_resbs_longitude', true);
        $gallery = get_post_meta($post->ID, '_resbs_gallery', true);
        $video_url = get_post_meta($post->ID, '_resbs_video_url', true);
        $description = get_post_meta($post->ID, '_resbs_description', true);
        
        // Convert gallery to array if it's a string
        if (is_string($gallery)) {
            $gallery = explode(',', $gallery);
        }
        if (!is_array($gallery)) {
            $gallery = array();
        }
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="resbs_price"><?php esc_html_e('Price', 'realestate-booking-suite'); ?></label>
                </th>
                <td>
                    <input type="number" id="resbs_price" name="resbs_price" value="<?php echo esc_attr($price); ?>" step="0.01" min="0" class="regular-text" />
                    <p class="description"><?php esc_html_e('Property price in your currency', 'realestate-booking-suite'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="resbs_bedrooms"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label>
                </th>
                <td>
                    <input type="number" id="resbs_bedrooms" name="resbs_bedrooms" value="<?php echo esc_attr($bedrooms); ?>" min="0" class="small-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="resbs_bathrooms"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                </th>
                <td>
                    <input type="number" id="resbs_bathrooms" name="resbs_bathrooms" value="<?php echo esc_attr($bathrooms); ?>" min="0" step="0.5" class="small-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="resbs_area"><?php esc_html_e('Area (sq ft)', 'realestate-booking-suite'); ?></label>
                </th>
                <td>
                    <input type="number" id="resbs_area" name="resbs_area" value="<?php echo esc_attr($area); ?>" min="0" class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="resbs_latitude"><?php esc_html_e('Latitude', 'realestate-booking-suite'); ?></label>
                </th>
                <td>
                    <input type="text" id="resbs_latitude" name="resbs_latitude" value="<?php echo esc_attr($latitude); ?>" class="regular-text" />
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="resbs_longitude"><?php esc_html_e('Longitude', 'realestate-booking-suite'); ?></label>
                </th>
                <td>
                    <input type="text" id="resbs_longitude" name="resbs_longitude" value="<?php echo esc_attr($longitude); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('Coordinates for map display', 'realestate-booking-suite'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="resbs_gallery"><?php esc_html_e('Gallery Images', 'realestate-booking-suite'); ?></label>
                </th>
                <td>
                    <div id="resbs_gallery_container">
                        <ul id="resbs_gallery_list" class="resbs-gallery-list">
                            <?php
                            if (!empty($gallery)) {
                                foreach ($gallery as $image_id) {
                                    if ($image_id) {
                                        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                        if ($image_url) {
                                            echo '<li class="resbs-gallery-item" data-id="' . esc_attr($image_id) . '">';
                                            echo '<img src="' . esc_url($image_url) . '" alt="" />';
                                            echo '<a href="#" class="resbs-remove-image">' . esc_html__('Remove', 'realestate-booking-suite') . '</a>';
                                            echo '</li>';
                                        }
                                    }
                                }
                            }
                            ?>
                        </ul>
                        <input type="hidden" id="resbs_gallery" name="resbs_gallery" value="<?php echo esc_attr(implode(',', $gallery)); ?>" />
                        <button type="button" id="resbs_add_gallery" class="button"><?php esc_html_e('Add Images', 'realestate-booking-suite'); ?></button>
                    </div>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="resbs_video_url"><?php esc_html_e('Video URL', 'realestate-booking-suite'); ?></label>
                </th>
                <td>
                    <input type="url" id="resbs_video_url" name="resbs_video_url" value="<?php echo esc_attr($video_url); ?>" class="regular-text" />
                    <p class="description"><?php esc_html_e('YouTube or Vimeo URL', 'realestate-booking-suite'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="resbs_description"><?php esc_html_e('Additional Description', 'realestate-booking-suite'); ?></label>
                </th>
                <td>
                    <textarea id="resbs_description" name="resbs_description" rows="5" cols="50" class="large-text"><?php echo esc_textarea($description); ?></textarea>
                    <p class="description"><?php esc_html_e('Additional property details and features', 'realestate-booking-suite'); ?></p>
                </td>
            </tr>
        </table>
        
        <?php
    }
    
    /**
     * Save metabox data
     */
    public function save_property_metabox($post_id) {
        // Check if nonce is valid
        if (!isset($_POST['resbs_property_metabox_nonce']) || !wp_verify_nonce($_POST['resbs_property_metabox_nonce'], 'resbs_property_metabox')) {
            return;
        }
        
        // Check if user has permission
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check if this is the correct post type
        if (get_post_type($post_id) !== 'property') {
            return;
        }
        
        // Sanitize and save each field
        $fields = array(
            'resbs_price' => 'sanitize_text_field',
            'resbs_bedrooms' => 'absint',
            'resbs_bathrooms' => 'sanitize_text_field',
            'resbs_area' => 'absint',
            'resbs_latitude' => 'sanitize_text_field',
            'resbs_longitude' => 'sanitize_text_field',
            'resbs_gallery' => 'sanitize_text_field',
            'resbs_video_url' => 'esc_url_raw',
            'resbs_description' => 'sanitize_textarea_field'
        );
        
        foreach ($fields as $field => $sanitize_function) {
            if (isset($_POST[$field])) {
                $value = call_user_func($sanitize_function, $_POST[$field]);
                
                // Additional validation for specific fields
                if ($field === 'resbs_price' && $value < 0) {
                    $value = 0;
                }
                if ($field === 'resbs_bedrooms' && $value < 0) {
                    $value = 0;
                }
                if ($field === 'resbs_bathrooms' && $value < 0) {
                    $value = 0;
                }
                if ($field === 'resbs_area' && $value < 0) {
                    $value = 0;
                }
                if ($field === 'resbs_video_url' && !empty($value) && !$this->is_valid_video_url($value)) {
                    $value = '';
                }
                
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
    }
    
    /**
     * Validate video URL
     */
    private function is_valid_video_url($url) {
        $youtube_pattern = '/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/.+/';
        $vimeo_pattern = '/^(https?:\/\/)?(www\.)?vimeo\.com\/.+/';
        
        return preg_match($youtube_pattern, $url) || preg_match($vimeo_pattern, $url);
    }
}

// Initialize the class
new RESBS_Metabox();
