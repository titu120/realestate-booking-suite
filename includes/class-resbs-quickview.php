<?php
/**
 * Quick View Manager Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_QuickView_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_resbs_get_quickview', array($this, 'ajax_get_quickview'));
        add_action('wp_ajax_nopriv_resbs_get_quickview', array($this, 'ajax_get_quickview'));
        
        // Add quick view buttons to property cards
        add_action('resbs_property_card_actions', array($this, 'add_quickview_button'), 10, 2);
        
        // Add quick view modal to footer
        add_action('wp_footer', array($this, 'render_quickview_modal'));
    }

    /**
     * Initialize
     */
    public function init() {
        // Add quick view button to property cards
        add_action('resbs_property_card_after_content', array($this, 'add_quickview_button_to_card'), 10, 2);
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'resbs-quickview',
            RESBS_URL . 'assets/css/quickview.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'resbs-quickview',
            RESBS_URL . 'assets/js/quickview.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('resbs-quickview', 'resbs_quickview_ajax', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('resbs_quickview_nonce')),
            'messages' => array(
                'loading' => esc_html__('Loading...', 'realestate-booking-suite'),
                'error' => esc_html__('An error occurred. Please try again.', 'realestate-booking-suite'),
                'no_property' => esc_html__('Property not found.', 'realestate-booking-suite')
            )
        ));
    }

    /**
     * AJAX handler for getting quick view content
     */
    public function ajax_get_quickview() {
        // Check if nonce exists and is string
        if (!isset($_POST['nonce']) || !is_string($_POST['nonce'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed. Nonce is missing.', 'realestate-booking-suite')
            ));
        }

        // Verify nonce using security helper
        RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'resbs_quickview_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('quick_view', 50, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        // Check if property_id exists
        if (!isset($_POST['property_id'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Property ID is required.', 'realestate-booking-suite')
            ));
        }

        // Sanitize property ID as integer first (don't filter by status yet)
        $property_id = RESBS_Security::sanitize_int($_POST['property_id'] ?? 0);
        
        if (!$property_id || $property_id <= 0) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid property ID.', 'realestate-booking-suite')
            ));
        }

        // Verify property exists
        $property = get_post($property_id);
        if (!$property) {
            wp_send_json_error(array(
                'message' => esc_html__('Property not found.', 'realestate-booking-suite')
            ));
        }

        // Check if property post type is correct
        if ($property->post_type !== 'property') {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid property type.', 'realestate-booking-suite')
            ));
        }

        // Check post visibility permissions
        // For published posts, anyone can view
        // For private posts, only author or users with edit_post capability can view
        // For password-protected posts, check if user has access
        if ($property->post_status === 'private') {
            $current_user_id = get_current_user_id();
            $property_author_id = (int) $property->post_author;
            
            // Allow if user is the author or has edit_post capability
            if ($current_user_id !== $property_author_id && !current_user_can('edit_post', $property_id)) {
                wp_send_json_error(array(
                    'message' => esc_html__('You do not have permission to view this property.', 'realestate-booking-suite')
                ));
            }
        } elseif ($property->post_status === 'publish') {
            // For published posts, check if password protected
            if (post_password_required($property_id)) {
                // Password-protected posts require password to view
                // In quick view context, we'll show limited info or require password
                // For now, we'll allow it but the frontend should handle password entry
            }
        } elseif (!is_post_status_viewable($property->post_status)) {
            // For other statuses (draft, pending, etc.), check permissions
            $current_user_id = get_current_user_id();
            $property_author_id = (int) $property->post_author;
            
            // Only allow author or users with edit_post capability
            if ($current_user_id !== $property_author_id && !current_user_can('edit_post', $property_id)) {
                wp_send_json_error(array(
                    'message' => esc_html__('This property is not available for viewing.', 'realestate-booking-suite')
                ));
            }
        }

        // Get property data
        $property_data = $this->get_property_data($property_id);
        
        // Render quick view content
        ob_start();
        $this->render_quickview_content($property_data);
        $content = ob_get_clean();

        wp_send_json_success(array(
            'content' => $content,
            'property_id' => $property_id
        ));
    }

    /**
     * Get property data for quick view
     */
    private function get_property_data($property_id) {
        $property = get_post($property_id);
        
        $data = array(
            'id' => $property_id,
            'title' => sanitize_text_field(get_the_title($property_id)),
            'permalink' => esc_url_raw(get_permalink($property_id)),
            'excerpt' => wp_kses_post(get_the_excerpt($property_id)),
            'content' => wp_kses_post($property->post_content),
            'featured_image' => esc_url_raw(get_the_post_thumbnail_url($property_id, 'large') ?: ''),
            'gallery' => array(),
            'price' => sanitize_text_field(get_post_meta($property_id, '_property_price', true)),
            'bedrooms' => absint(get_post_meta($property_id, '_property_bedrooms', true)),
            'bathrooms' => absint(get_post_meta($property_id, '_property_bathrooms', true)),
            // Try multiple possible area meta keys
            'area' => absint(get_post_meta($property_id, '_property_size', true) ?: get_post_meta($property_id, '_property_area_sqft', true)),
            'latitude' => sanitize_text_field(get_post_meta($property_id, '_property_latitude', true)),
            'longitude' => sanitize_text_field(get_post_meta($property_id, '_property_longitude', true)),
            'amenities' => sanitize_text_field(get_post_meta($property_id, '_property_amenities', true)),
            'video_url' => esc_url_raw(get_post_meta($property_id, '_property_video_url', true)),
            'featured' => (bool) get_post_meta($property_id, '_property_featured', true),
            'new' => (bool) get_post_meta($property_id, '_property_new', true),
            'sold' => (bool) get_post_meta($property_id, '_property_sold', true),
            // Get taxonomy terms and sanitize
            'property_type' => $this->sanitize_terms(wp_get_post_terms($property_id, 'property_type', array('fields' => 'names'))),
            'property_status' => $this->sanitize_terms(wp_get_post_terms($property_id, 'property_status', array('fields' => 'names'))),
            'property_location' => $this->sanitize_terms(wp_get_post_terms($property_id, 'property_location', array('fields' => 'names')))
        );

        // Get gallery images
        $gallery_meta = get_post_meta($property_id, '_property_gallery', true);
        if ($gallery_meta) {
            // Handle both array and comma-separated string formats
            if (is_array($gallery_meta)) {
                $gallery_ids = array_map('absint', $gallery_meta);
            } else {
                // Comma-separated string - sanitize first
                $gallery_meta = sanitize_text_field($gallery_meta);
                $gallery_ids = array_map('absint', explode(',', $gallery_meta));
            }
            
            // Remove duplicates and invalid IDs
            $gallery_ids = array_unique(array_filter($gallery_ids));
            
            foreach ($gallery_ids as $image_id) {
                $image_id = absint($image_id);
                if (!$image_id) {
                    continue;
                }
                
                // Verify attachment exists
                if (!wp_attachment_is_image($image_id)) {
                    continue;
                }
                
                $image_url = wp_get_attachment_image_url($image_id, 'large');
                $image_thumb = wp_get_attachment_image_url($image_id, 'thumbnail');
                if ($image_url) {
                    $alt_text = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                    $data['gallery'][] = array(
                        'id' => $image_id,
                        'url' => esc_url_raw($image_url),
                        'thumb' => esc_url_raw($image_thumb ?: $image_url),
                        'alt' => sanitize_text_field($alt_text ?: '')
                    );
                }
            }
        }

        return $data;
    }

    /**
     * Render quick view content
     */
    private function render_quickview_content($data) {
        ?>
        <div class="resbs-quickview-content" data-property-id="<?php echo esc_attr($data['id']); ?>">
            <!-- Quick View Header -->
            <div class="resbs-quickview-header">
                <h2 class="resbs-quickview-title">
                    <a href="<?php echo esc_url($data['permalink']); ?>">
                        <?php echo esc_html($data['title']); ?>
                    </a>
                </h2>
                <button type="button" class="resbs-quickview-close" aria-label="<?php esc_attr_e('Close', 'realestate-booking-suite'); ?>">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>

            <!-- Quick View Body -->
            <div class="resbs-quickview-body">
                <!-- Gallery Section -->
                <?php if (!empty($data['gallery']) || $data['featured_image']): ?>
                    <div class="resbs-quickview-gallery">
                        <div class="resbs-quickview-main-image">
                            <?php if ($data['featured_image']): ?>
                                <img src="<?php echo esc_url($data['featured_image']); ?>" 
                                     alt="<?php echo esc_attr($data['title']); ?>"
                                     class="resbs-quickview-image">
                            <?php elseif (!empty($data['gallery'])): ?>
                                <img src="<?php echo esc_url($data['gallery'][0]['url']); ?>" 
                                     alt="<?php echo esc_attr(!empty($data['gallery'][0]['alt']) ? $data['gallery'][0]['alt'] : $data['title']); ?>"
                                     class="resbs-quickview-image">
                            <?php endif; ?>
                            
                            <!-- Property Badges -->
                            <div class="resbs-quickview-badges">
                                <?php do_action('resbs_property_badges', $data['id'], 'quickview'); ?>
                            </div>
                        </div>

                        <?php if (!empty($data['gallery']) && count($data['gallery']) > 1): ?>
                            <div class="resbs-quickview-thumbnails">
                                <?php foreach (array_slice($data['gallery'], 0, 4) as $index => $image): ?>
                                    <div class="resbs-quickview-thumb <?php echo esc_attr($index === 0 ? 'active' : ''); ?>">
                                        <img src="<?php echo esc_url($image['thumb']); ?>" 
                                             alt="<?php echo esc_attr(!empty($image['alt']) ? $image['alt'] : $data['title']); ?>"
                                             data-full="<?php echo esc_url($image['url']); ?>">
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($data['gallery']) > 4): ?>
                                    <div class="resbs-quickview-more">
                                        <span class="resbs-more-count">+<?php echo esc_html(count($data['gallery']) - 4); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Property Info Section -->
                <div class="resbs-quickview-info">
                    <!-- Price -->
                    <?php if ($data['price']): ?>
                        <div class="resbs-quickview-price">
                            <span class="resbs-price-amount"><?php echo esc_html($this->format_price($data['price'])); ?></span>
                            <?php if (!empty($data['property_status']) && in_array('Rent', $data['property_status'])): ?>
                                <span class="resbs-price-period"><?php esc_html_e('/month', 'realestate-booking-suite'); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Location -->
                    <?php if (!empty($data['property_location'])): ?>
                        <div class="resbs-quickview-location">
                            <span class="dashicons dashicons-location"></span>
                            <?php echo esc_html(implode(', ', $data['property_location'])); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Meta Info -->
                    <div class="resbs-quickview-meta">
                        <?php if (!empty($data['bedrooms'])): ?>
                            <div class="resbs-meta-item">
                                <span class="dashicons dashicons-bed"></span>
                                <span class="resbs-meta-value"><?php echo esc_html(absint($data['bedrooms'])); ?></span>
                                <span class="resbs-meta-label"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($data['bathrooms'])): ?>
                            <div class="resbs-meta-item">
                                <span class="dashicons dashicons-bath"></span>
                                <span class="resbs-meta-value"><?php echo esc_html(absint($data['bathrooms'])); ?></span>
                                <span class="resbs-meta-label"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($data['area'])): ?>
                            <div class="resbs-meta-item">
                                <span class="dashicons dashicons-fullscreen-alt"></span>
                                <span class="resbs-meta-value"><?php echo esc_html(number_format(absint($data['area']))); ?></span>
                                <span class="resbs-meta-label"><?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Property Type and Status -->
                    <div class="resbs-quickview-taxonomies">
                        <?php if (!empty($data['property_type'])): ?>
                            <div class="resbs-taxonomy-item">
                                <span class="resbs-taxonomy-label"><?php esc_html_e('Type:', 'realestate-booking-suite'); ?></span>
                                <span class="resbs-taxonomy-value"><?php echo esc_html(implode(', ', $data['property_type'])); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($data['property_status'])): ?>
                            <div class="resbs-taxonomy-item">
                                <span class="resbs-taxonomy-label"><?php esc_html_e('Status:', 'realestate-booking-suite'); ?></span>
                                <span class="resbs-taxonomy-value"><?php echo esc_html(implode(', ', $data['property_status'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Description -->
                    <?php if ($data['excerpt'] || $data['content']): ?>
                        <div class="resbs-quickview-description">
                            <h4><?php esc_html_e('Description', 'realestate-booking-suite'); ?></h4>
                            <div class="resbs-description-text">
                                <?php if ($data['excerpt']): ?>
                                    <?php echo wp_kses_post(wp_trim_words($data['excerpt'], 30, '...')); ?>
                                <?php else: ?>
                                    <?php echo wp_kses_post(wp_trim_words($data['content'], 30, '...')); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Amenities Preview -->
                    <?php if ($data['amenities']): ?>
                        <div class="resbs-quickview-amenities">
                            <h4><?php esc_html_e('Key Amenities', 'realestate-booking-suite'); ?></h4>
                            <div class="resbs-amenities-preview">
                                <?php
                                $amenities_raw = sanitize_text_field($data['amenities']);
                                $amenities_list = !empty($amenities_raw) ? explode(',', $amenities_raw) : array();
                                $amenities_list = array_map('trim', $amenities_list);
                                $amenities_list = array_filter($amenities_list);
                                $amenities_preview = array_slice($amenities_list, 0, 3);
                                foreach ($amenities_preview as $amenity):
                                    $amenity = sanitize_text_field(trim($amenity));
                                    if (!empty($amenity)):
                                ?>
                                    <span class="resbs-amenity-tag"><?php echo esc_html($amenity); ?></span>
                                <?php
                                    endif;
                                endforeach;
                                
                                if (count($amenities_list) > 3):
                                ?>
                                    <span class="resbs-amenity-more">+<?php echo esc_html(absint(count($amenities_list) - 3)); ?> <?php esc_html_e('more', 'realestate-booking-suite'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Map Preview -->
                    <?php if (!empty($data['latitude']) && !empty($data['longitude'])): ?>
                        <div class="resbs-quickview-map">
                            <h4><?php esc_html_e('Location', 'realestate-booking-suite'); ?></h4>
                            <div class="resbs-map-preview" 
                                 data-lat="<?php echo esc_attr(sanitize_text_field($data['latitude'])); ?>" 
                                 data-lng="<?php echo esc_attr(sanitize_text_field($data['longitude'])); ?>">
                                <div class="resbs-map-placeholder">
                                    <span class="dashicons dashicons-location"></span>
                                    <?php esc_html_e('View on Map', 'realestate-booking-suite'); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick View Footer -->
            <div class="resbs-quickview-footer">
                <div class="resbs-quickview-actions">
                    <a href="<?php echo esc_url($data['permalink']); ?>" class="resbs-btn resbs-btn-primary resbs-view-details">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php esc_html_e('View Full Details', 'realestate-booking-suite'); ?>
                    </a>
                    
                    <button type="button" class="resbs-btn resbs-btn-secondary resbs-book-now" data-property-id="<?php echo esc_attr($data['id']); ?>">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php esc_html_e('Book Now', 'realestate-booking-suite'); ?>
                    </button>
                    
                    <button type="button" class="resbs-btn resbs-btn-outline resbs-favorite-btn" data-property-id="<?php echo esc_attr($data['id']); ?>">
                        <span class="dashicons dashicons-heart"></span>
                        <span class="resbs-favorite-text"><?php esc_html_e('Add to Favorites', 'realestate-booking-suite'); ?></span>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Add quick view button to property cards
     */
    public function add_quickview_button_to_card($property_id, $context = 'card') {
        ?>
        <div class="resbs-quickview-trigger">
            <button type="button" class="resbs-quickview-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
                <span class="dashicons dashicons-visibility"></span>
                <span class="resbs-quickview-text"><?php esc_html_e('Quick View', 'realestate-booking-suite'); ?></span>
            </button>
        </div>
        <?php
    }

    /**
     * Add quick view button to property card actions
     */
    public function add_quickview_button($property_id, $context = 'card') {
        ?>
        <button type="button" class="resbs-quickview-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
            <span class="dashicons dashicons-visibility"></span>
            <?php esc_html_e('Quick View', 'realestate-booking-suite'); ?>
        </button>
        <?php
    }

    /**
     * Render quick view modal
     */
    public function render_quickview_modal() {
        ?>
        <div id="resbs-quickview-modal" class="resbs-quickview-modal" role="dialog" aria-labelledby="resbs-quickview-title" aria-hidden="true">
            <div class="resbs-quickview-overlay"></div>
            <div class="resbs-quickview-container">
                <div class="resbs-quickview-wrapper">
                    <!-- Content will be loaded here via AJAX -->
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Sanitize taxonomy terms array
     * 
     * @param array|WP_Error $terms Terms array or WP_Error
     * @return array Sanitized terms array
     */
    private function sanitize_terms($terms) {
        if (is_wp_error($terms) || !is_array($terms)) {
            return array();
        }
        
        $sanitized = array();
        foreach ($terms as $term) {
            $sanitized[] = sanitize_text_field($term);
        }
        
        return $sanitized;
    }
    
    /**
     * Format price
     */
    private function format_price($price) {
        $currency_symbol = sanitize_text_field(get_option('resbs_currency_symbol', '$'));
        $currency_position = sanitize_text_field(get_option('resbs_currency_position', 'before'));
        
        // Ensure price is numeric and sanitized
        $price = is_numeric($price) ? abs(floatval($price)) : 0;
        $formatted_price = number_format($price, 2);
        
        // Escape currency symbol for output
        $currency_symbol = esc_html($currency_symbol);
        
        // Validate currency position
        $currency_position = in_array($currency_position, array('before', 'after'), true) ? $currency_position : 'before';
        
        if ($currency_position === 'before') {
            return $currency_symbol . $formatted_price;
        } else {
            return $formatted_price . $currency_symbol;
        }
    }
}

// Initialize Quick View Manager
new RESBS_QuickView_Manager();
