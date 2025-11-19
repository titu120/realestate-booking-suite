<?php
/**
 * Property Grid Template
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Property_Grid {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('resbs_property_grid', array($this, 'property_grid_shortcode'));
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_script('jquery');
        
        wp_enqueue_script(
            'resbs-property-grid',
            RESBS_URL . 'assets/js/main.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('resbs-property-grid', 'resbs_grid_ajax', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('resbs_grid_nonce')),
            'messages' => array(
                'loading' => esc_html__('Loading more properties...', 'realestate-booking-suite'),
                'no_more' => esc_html__('No more properties to load.', 'realestate-booking-suite'),
                'error' => esc_html__('Error loading properties. Please try again.', 'realestate-booking-suite')
            )
        ));
    }
    
    /**
     * Property grid shortcode
     */
    public function property_grid_shortcode($atts) {
        $atts = shortcode_atts(array(
            'limit' => 12,
            'columns' => 3,
            'show_pagination' => 'true',
            'show_infinite_scroll' => 'false',
            'property_type' => '',
            'property_status' => '',
            'property_location' => '',
            'featured_only' => 'false',
            'orderby' => 'date',
            'order' => 'DESC',
            'show_badges' => 'true',
            'show_price' => 'true',
            'show_meta' => 'true',
            'show_excerpt' => 'false',
            'image_size' => 'medium'
        ), $atts);
        
        // Sanitize attributes
        $limit = intval($atts['limit']);
        $columns = intval($atts['columns']);
        $show_pagination = $atts['show_pagination'] === 'true';
        $show_infinite_scroll = $atts['show_infinite_scroll'] === 'true';
        $featured_only = $atts['featured_only'] === 'true';
        $show_badges = $atts['show_badges'] === 'true';
        $show_price = $atts['show_price'] === 'true';
        $show_meta = $atts['show_meta'] === 'true';
        $show_excerpt = $atts['show_excerpt'] === 'true';
        
        // Validate image_size
        $image_size = sanitize_text_field($atts['image_size']);
        $allowed_image_sizes = array('thumbnail', 'medium', 'medium_large', 'large', 'full');
        if (!in_array($image_size, $allowed_image_sizes, true)) {
            $image_size = 'medium';
        }
        
        // Validate orderby and order
        $allowed_orderby = array('date', 'title', 'price', 'menu_order', 'rand');
        $orderby = sanitize_text_field($atts['orderby']);
        if (!in_array($orderby, $allowed_orderby, true)) {
            $orderby = 'date';
        }
        
        $allowed_order = array('ASC', 'DESC');
        $order = sanitize_text_field($atts['order']);
        if (!in_array($order, $allowed_order, true)) {
            $order = 'DESC';
        }
        
        // Build query args
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'paged' => 1,
            'orderby' => $orderby,
            'order' => $order,
            'meta_query' => array(),
            'tax_query' => array()
        );
        
        // Handle price ordering
        if ($orderby === 'price') {
            $args['meta_key'] = '_property_price';
            $args['orderby'] = 'meta_value_num';
        }
        
        // Featured properties filter
        if ($featured_only) {
            $args['meta_query'][] = array(
                'key' => '_property_featured',
                'value' => '1',
                'compare' => '='
            );
        }
        
        // Property type filter
        if (!empty($atts['property_type'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['property_type'])
            );
        }
        
        // Property status filter
        if (!empty($atts['property_status'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['property_status'])
            );
        }
        
        // Property location filter
        if (!empty($atts['property_location'])) {
            $args['tax_query'][] = array(
                'taxonomy' => 'property_location',
                'field' => 'slug',
                'terms' => sanitize_text_field($atts['property_location'])
            );
        }
        
        // Set relation for multiple tax queries
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
        
        // Execute query
        $query = new WP_Query($args);
        
        ob_start();
        ?>
        <div class="resbs-property-grid-container" data-columns="<?php echo esc_attr($columns); ?>" data-limit="<?php echo esc_attr($limit); ?>" data-infinite="<?php echo esc_attr($show_infinite_scroll ? 'true' : 'false'); ?>">
            <?php if ($query->have_posts()): ?>
                <div class="resbs-property-grid resbs-grid-<?php echo esc_attr($columns); ?>-columns">
                    <?php while ($query->have_posts()): $query->the_post(); ?>
                        <?php $this->render_property_card(get_the_ID(), $show_badges, $show_price, $show_meta, $show_excerpt, $image_size); ?>
                    <?php endwhile; ?>
                </div>
                
                <?php if ($show_pagination && $query->max_num_pages > 1): ?>
                    <div class="resbs-grid-pagination">
                        <?php
                        echo paginate_links(array(
                            'total' => $query->max_num_pages,
                            'current' => 1,
                            'format' => '?paged=%#%',
                            'prev_text' => esc_html__('Previous', 'realestate-booking-suite'),
                            'next_text' => esc_html__('Next', 'realestate-booking-suite'),
                            'type' => 'list'
                        ));
                        ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($show_infinite_scroll && $query->max_num_pages > 1): ?>
                    <div class="resbs-infinite-scroll">
                        <button type="button" class="resbs-load-more-btn" data-page="2" data-max-pages="<?php echo esc_attr($query->max_num_pages); ?>">
                            <?php esc_html_e('Load More Properties', 'realestate-booking-suite'); ?>
                        </button>
                        <div class="resbs-loading-more" style="display: none;">
                            <?php esc_html_e('Loading more properties...', 'realestate-booking-suite'); ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="resbs-no-properties">
                    <p><?php esc_html_e('No properties found.', 'realestate-booking-suite'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }
    
    /**
     * Render individual property card
     */
    private function render_property_card($post_id, $show_badges, $show_price, $show_meta, $show_excerpt, $image_size) {
        $price = get_post_meta($post_id, '_property_price', true);
        $bedrooms = get_post_meta($post_id, '_property_bedrooms', true);
        $bathrooms = get_post_meta($post_id, '_property_bathrooms', true);
        // Try multiple possible area meta keys
        $area = get_post_meta($post_id, '_property_size', true) ?: get_post_meta($post_id, '_property_area_sqft', true);
        $featured = get_post_meta($post_id, '_property_featured', true);
        $video_url = get_post_meta($post_id, '_property_video_url', true);
        
        // Get taxonomy terms and sanitize
        $property_status_raw = wp_get_post_terms($post_id, 'property_status', array('fields' => 'names'));
        $property_type_raw = wp_get_post_terms($post_id, 'property_type', array('fields' => 'names'));
        $location_raw = wp_get_post_terms($post_id, 'property_location', array('fields' => 'names'));
        
        // Sanitize taxonomy terms
        $property_status = array();
        if (!is_wp_error($property_status_raw) && is_array($property_status_raw)) {
            foreach ($property_status_raw as $status) {
                $property_status[] = sanitize_text_field($status);
            }
        }
        
        $property_type = array();
        if (!is_wp_error($property_type_raw) && is_array($property_type_raw)) {
            foreach ($property_type_raw as $type) {
                $property_type[] = sanitize_text_field($type);
            }
        }
        
        $location = array();
        if (!is_wp_error($location_raw) && is_array($location_raw)) {
            foreach ($location_raw as $loc) {
                $location[] = sanitize_text_field($loc);
            }
        }
        
        $thumbnail = get_the_post_thumbnail_url($post_id, $image_size);
        $permalink = get_permalink($post_id);
        $title = get_the_title($post_id);
        $excerpt = get_the_excerpt($post_id);
        
        // Check if property is new (less than 30 days old)
        $is_new = (strtotime(get_the_date('c', $post_id)) > strtotime('-30 days'));
        
        ?>
        <div class="resbs-property-card" data-property-id="<?php echo esc_attr($post_id); ?>">
            <div class="resbs-property-image-container">
                <?php if ($thumbnail): ?>
                    <a href="<?php echo esc_url($permalink); ?>">
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($title); ?>" class="resbs-property-image">
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url($permalink); ?>">
                        <div class="resbs-no-image">
                            <span class="dashicons dashicons-camera"></span>
                            <p><?php esc_html_e('No Image', 'realestate-booking-suite'); ?></p>
                        </div>
                    </a>
                <?php endif; ?>
                
                <?php if ($show_badges): ?>
                    <div class="resbs-property-badges">
                        <?php if ($featured === '1' || $featured === 'yes'): ?>
                            <span class="resbs-badge resbs-badge-featured"><?php esc_html_e('Featured', 'realestate-booking-suite'); ?></span>
                        <?php endif; ?>
                        
                        <?php if ($is_new): ?>
                            <span class="resbs-badge resbs-badge-new"><?php esc_html_e('New', 'realestate-booking-suite'); ?></span>
                        <?php endif; ?>
                        
                        <?php if (!empty($property_status) && in_array('Sold', $property_status, true)): ?>
                            <span class="resbs-badge resbs-badge-sold"><?php esc_html_e('Sold', 'realestate-booking-suite'); ?></span>
                        <?php endif; ?>
                        
                        <?php if (!empty($property_status) && in_array('Rent', $property_status, true)): ?>
                            <span class="resbs-badge resbs-badge-rent"><?php esc_html_e('For Rent', 'realestate-booking-suite'); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($video_url): ?>
                    <div class="resbs-video-indicator">
                        <span class="dashicons dashicons-video-alt3"></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="resbs-property-content">
                <<?php echo esc_attr(resbs_get_title_heading_tag()); ?> class="resbs-property-title">
                    <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
                </<?php echo esc_attr(resbs_get_title_heading_tag()); ?>>
                
                <?php if (!empty($location)): ?>
                    <div class="resbs-property-location">
                        <span class="dashicons dashicons-location"></span>
                        <?php echo esc_html(implode(', ', $location)); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($show_price && $price && is_numeric($price)): ?>
                    <div class="resbs-property-price">
                        <?php echo esc_html(resbs_format_price($price)); ?>
                        <?php if (!empty($property_status) && in_array('Rent', $property_status, true)): ?>
                            <span class="resbs-price-period"><?php esc_html_e('/month', 'realestate-booking-suite'); ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($show_meta): ?>
                    <div class="resbs-property-meta">
                        <?php if ($bedrooms && is_numeric($bedrooms)): ?>
                            <span class="resbs-meta-item">
                                <span class="dashicons dashicons-bed"></span>
                                <?php echo esc_html(absint($bedrooms)); ?> <?php echo esc_html($bedrooms == 1 ? esc_html__('Bed', 'realestate-booking-suite') : esc_html__('Beds', 'realestate-booking-suite')); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($bathrooms && is_numeric($bathrooms)): ?>
                            <span class="resbs-meta-item">
                                <span class="dashicons dashicons-shower"></span>
                                <?php echo esc_html(floatval($bathrooms)); ?> <?php echo esc_html($bathrooms == 1 ? esc_html__('Bath', 'realestate-booking-suite') : esc_html__('Baths', 'realestate-booking-suite')); ?>
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($area && is_numeric($area)): ?>
                            <span class="resbs-meta-item">
                                <span class="dashicons dashicons-fullscreen-alt"></span>
                                <?php echo esc_html(number_format(floatval($area))); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($show_excerpt && $excerpt): ?>
                    <div class="resbs-property-excerpt">
                        <?php echo esc_html(wp_trim_words($excerpt, 20, '...')); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($property_type)): ?>
                    <div class="resbs-property-type">
                        <?php echo esc_html(implode(', ', $property_type)); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Handle AJAX load more
     */
    public function handle_load_more() {
        // Verify nonce (sanitize before verification)
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_grid_nonce')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed.', 'realestate-booking-suite')));
            return;
        }
        
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 12;
        $columns = isset($_POST['columns']) ? intval($_POST['columns']) : 3;
        $show_badges = isset($_POST['show_badges']) && $_POST['show_badges'] === 'true';
        $show_price = isset($_POST['show_price']) && $_POST['show_price'] === 'true';
        $show_meta = isset($_POST['show_meta']) && $_POST['show_meta'] === 'true';
        $show_excerpt = isset($_POST['show_excerpt']) && $_POST['show_excerpt'] === 'true';
        
        // Validate image_size
        $image_size = isset($_POST['image_size']) ? sanitize_text_field($_POST['image_size']) : 'medium';
        $allowed_image_sizes = array('thumbnail', 'medium', 'medium_large', 'large', 'full');
        if (!in_array($image_size, $allowed_image_sizes, true)) {
            $image_size = 'medium';
        }
        
        // Build query args (same as shortcode)
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'paged' => $page,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            ob_start();
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_property_card(get_the_ID(), $show_badges, $show_price, $show_meta, $show_excerpt, $image_size);
            }
            $html = ob_get_clean();
            wp_reset_postdata();
            
            wp_send_json_success(array(
                'html' => $html,
                'has_more' => $page < $query->max_num_pages
            ));
        } else {
            wp_reset_postdata();
            wp_send_json_error(array('message' => esc_html__('No more properties found.', 'realestate-booking-suite')));
        }
    }
}

// Initialize the class and store instance for AJAX handlers
$GLOBALS['resbs_property_grid'] = new RESBS_Property_Grid();

add_action('wp_ajax_resbs_load_more_properties', function() {
    if (isset($GLOBALS['resbs_property_grid'])) {
        $GLOBALS['resbs_property_grid']->handle_load_more();
    } else {
        wp_send_json_error(array('message' => esc_html__('Property grid handler not available.', 'realestate-booking-suite')));
    }
});
add_action('wp_ajax_nopriv_resbs_load_more_properties', function() {
    if (isset($GLOBALS['resbs_property_grid'])) {
        $GLOBALS['resbs_property_grid']->handle_load_more();
    } else {
        wp_send_json_error(array('message' => esc_html__('Property grid handler not available.', 'realestate-booking-suite')));
    }
});
