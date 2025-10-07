<?php
/**
 * Infinite Scroll Manager Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Infinite_Scroll_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_resbs_load_more_properties', array($this, 'ajax_load_more_properties'));
        add_action('wp_ajax_nopriv_resbs_load_more_properties', array($this, 'ajax_load_more_properties'));
        
        // Shortcode
        add_shortcode('resbs_infinite_properties', array($this, 'infinite_properties_shortcode'));
        
        // Widget
        add_action('widgets_init', array($this, 'register_infinite_scroll_widget'));
    }

    /**
     * Initialize
     */
    public function init() {
        // Add infinite scroll support to existing widgets
        add_action('resbs_property_grid_after', array($this, 'add_infinite_scroll_support'), 10, 2);
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        // Enqueue infinite scroll styles
        wp_enqueue_style(
            'resbs-infinite-scroll',
            RESBS_URL . 'assets/css/infinite-scroll.css',
            array(),
            '1.0.0'
        );

        // Enqueue infinite scroll scripts
        wp_enqueue_script(
            'resbs-infinite-scroll',
            RESBS_URL . 'assets/js/infinite-scroll.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Localize script
        wp_localize_script('resbs-infinite-scroll', 'resbs_infinite_scroll_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('resbs_infinite_scroll_nonce'),
            'messages' => array(
                'loading' => esc_html__('Loading more properties...', 'realestate-booking-suite'),
                'load_more' => esc_html__('Load More Properties', 'realestate-booking-suite'),
                'no_more' => esc_html__('No more properties to load', 'realestate-booking-suite'),
                'error' => esc_html__('Failed to load more properties. Please try again.', 'realestate-booking-suite'),
                'retry' => esc_html__('Retry', 'realestate-booking-suite'),
                'previous' => esc_html__('Previous', 'realestate-booking-suite'),
                'next' => esc_html__('Next', 'realestate-booking-suite'),
                'page' => esc_html__('Page', 'realestate-booking-suite'),
                'of' => esc_html__('of', 'realestate-booking-suite'),
                'properties_found' => esc_html__('Properties Found', 'realestate-booking-suite'),
                'showing' => esc_html__('Showing', 'realestate-booking-suite'),
                'to' => esc_html__('to', 'realestate-booking-suite')
            )
        ));
    }

    /**
     * AJAX handler for loading more properties
     */
    public function ajax_load_more_properties() {
        // Verify nonce using security helper
        RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'resbs_infinite_scroll_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('load_more_properties', 30, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        // Get and sanitize parameters using security helper
        $page = RESBS_Security::sanitize_int($_POST['page'], 1);
        $posts_per_page = RESBS_Security::sanitize_int($_POST['posts_per_page'], 12);
        $widget_id = RESBS_Security::sanitize_text($_POST['widget_id']);
        $filters = array(
            'property_type' => RESBS_Security::sanitize_text($_POST['property_type'] ?? ''),
            'property_status' => RESBS_Security::sanitize_text($_POST['property_status'] ?? ''),
            'location' => RESBS_Security::sanitize_text($_POST['location'] ?? ''),
            'price_min' => RESBS_Security::sanitize_int($_POST['price_min'] ?? 0),
            'price_max' => RESBS_Security::sanitize_int($_POST['price_max'] ?? 0),
            'bedrooms' => RESBS_Security::sanitize_int($_POST['bedrooms'] ?? 0),
            'bathrooms' => RESBS_Security::sanitize_int($_POST['bathrooms'] ?? 0),
            'featured_only' => RESBS_Security::sanitize_bool($_POST['featured_only'] ?? false)
        );

        // Get widget settings
        $widget_settings = get_option('widget_resbs_property_grid_widget');
        $instance = null;
        
        if ($widget_settings && is_array($widget_settings)) {
            foreach ($widget_settings as $key => $setting) {
                if (is_array($setting) && isset($setting['_multiwidget']) && $setting['_multiwidget']) {
                    continue;
                }
                if (is_array($setting) && isset($setting['widget_id']) && $setting['widget_id'] === $widget_id) {
                    $instance = $setting;
                    break;
                }
            }
        }

        if (!$instance) {
            wp_send_json_error(array(
                'message' => esc_html__('Widget settings not found.', 'realestate-booking-suite')
            ));
        }

        // Build query
        $query_args = $this->build_property_query($instance, $filters, $page, $posts_per_page);
        $properties_query = new WP_Query($query_args);

        $properties_html = '';
        $has_more = false;

        if ($properties_query->have_posts()) {
            ob_start();
            while ($properties_query->have_posts()) {
                $properties_query->the_post();
                $this->render_property_card($instance);
            }
            $properties_html = ob_get_clean();
            wp_reset_postdata();

            // Check if there are more pages
            $has_more = $properties_query->max_num_pages > $page;
        }

        wp_send_json_success(array(
            'html' => $properties_html,
            'has_more' => $has_more,
            'current_page' => $page,
            'total_pages' => $properties_query->max_num_pages,
            'total_properties' => $properties_query->found_posts,
            'properties_loaded' => $properties_query->post_count
        ));
    }

    /**
     * Build property query
     */
    private function build_property_query($instance, $filters, $page, $posts_per_page) {
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $page,
            'orderby' => sanitize_text_field($instance['orderby']),
            'order' => sanitize_text_field($instance['order'])
        );

        // Add meta query
        $meta_query = array('relation' => 'AND');

        // Price filter
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $price_query = array();
            
            if (!empty($filters['price_min'])) {
                $price_query[] = array(
                    'key' => '_property_price',
                    'value' => $filters['price_min'],
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }
            
            if (!empty($filters['price_max'])) {
                $price_query[] = array(
                    'key' => '_property_price',
                    'value' => $filters['price_max'],
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                );
            }
            
            if (!empty($price_query)) {
                $meta_query[] = $price_query;
            }
        }

        // Bedrooms filter
        if (!empty($filters['bedrooms'])) {
            $meta_query[] = array(
                'key' => '_property_bedrooms',
                'value' => $filters['bedrooms'],
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }

        // Bathrooms filter
        if (!empty($filters['bathrooms'])) {
            $meta_query[] = array(
                'key' => '_property_bathrooms',
                'value' => $filters['bathrooms'],
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }

        // Featured filter
        if (!empty($filters['featured_only'])) {
            $meta_query[] = array(
                'key' => '_property_featured',
                'value' => '1',
                'compare' => '='
            );
        }

        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }

        // Add taxonomy query
        $tax_query = array();
        
        if (!empty($filters['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $filters['property_type']
            );
        }

        if (!empty($filters['property_status'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $filters['property_status']
            );
        }

        if (!empty($filters['location'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_location',
                'field' => 'slug',
                'terms' => $filters['location']
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        return $query_args;
    }

    /**
     * Render property card
     */
    private function render_property_card($instance) {
        $property_id = get_the_ID();
        $property_price = get_post_meta($property_id, '_property_price', true);
        $property_bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $property_bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        $property_size = get_post_meta($property_id, '_property_size', true);
        $property_featured = get_post_meta($property_id, '_property_featured', true);
        $property_status = get_the_terms($property_id, 'property_status');
        $property_type = get_the_terms($property_id, 'property_type');
        $property_location = get_the_terms($property_id, 'property_location');

        $show_price = (bool) $instance['show_price'];
        $show_meta = (bool) $instance['show_meta'];
        $show_excerpt = (bool) $instance['show_excerpt'];
        $show_badges = (bool) $instance['show_badges'];
        $show_favorite_button = (bool) $instance['show_favorite_button'];
        $show_book_button = (bool) $instance['show_book_button'];
        ?>

        <div class="resbs-property-card resbs-infinite-scroll-item">
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

                <?php if ($show_badges): ?>
                    <?php do_action('resbs_property_badges', $property_id, 'infinite-scroll'); ?>
                <?php endif; ?>

                <?php if ($show_favorite_button): ?>
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

                <?php if ($property_location && !is_wp_error($property_location)): ?>
                    <div class="resbs-property-location">
                        <span class="dashicons dashicons-location"></span>
                        <?php echo esc_html($property_location[0]->name); ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_price && !empty($property_price)): ?>
                    <div class="resbs-property-price">
                        <?php echo esc_html($this->format_price($property_price)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_meta): ?>
                    <div class="resbs-property-meta">
                        <?php if (!empty($property_bedrooms)): ?>
                            <span class="resbs-meta-item">
                                <span class="dashicons dashicons-bed"></span>
                                <?php echo esc_html($property_bedrooms); ?> <?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($property_bathrooms)): ?>
                            <span class="resbs-meta-item">
                                <span class="dashicons dashicons-bath"></span>
                                <?php echo esc_html($property_bathrooms); ?> <?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?>
                            </span>
                        <?php endif; ?>

                        <?php if (!empty($property_size)): ?>
                            <span class="resbs-meta-item">
                                <span class="dashicons dashicons-fullscreen-alt"></span>
                                <?php echo esc_html($property_size); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_excerpt): ?>
                    <div class="resbs-property-excerpt">
                        <?php echo wp_kses_post(wp_trim_words(get_the_excerpt(), 15, '...')); ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_book_button): ?>
                    <div class="resbs-property-actions">
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="resbs-book-btn">
                            <?php esc_html_e('View Details', 'realestate-booking-suite'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Format price
     */
    private function format_price($price) {
        if (!$price) return '';
        
        $num_price = intval($price);
        if (is_nan($num_price)) return $price;
        
        return '$' . number_format($num_price);
    }

    /**
     * Infinite properties shortcode
     */
    public function infinite_properties_shortcode($atts) {
        $atts = shortcode_atts(array(
            'posts_per_page' => '12',
            'columns' => '3',
            'show_filters' => 'true',
            'show_price' => 'true',
            'show_meta' => 'true',
            'show_excerpt' => 'true',
            'show_badges' => 'true',
            'show_favorite_button' => 'true',
            'show_book_button' => 'true',
            'orderby' => 'date',
            'order' => 'DESC',
            'property_type' => '',
            'property_status' => '',
            'featured_only' => 'false',
            'infinite_scroll' => 'true',
            'show_pagination' => 'true'
        ), $atts);

        $posts_per_page = intval($atts['posts_per_page']);
        $columns = intval($atts['columns']);
        $show_filters = $atts['show_filters'] !== 'false';
        $show_price = $atts['show_price'] !== 'false';
        $show_meta = $atts['show_meta'] !== 'false';
        $show_excerpt = $atts['show_excerpt'] !== 'false';
        $show_badges = $atts['show_badges'] !== 'false';
        $show_favorite_button = $atts['show_favorite_button'] !== 'false';
        $show_book_button = $atts['show_book_button'] !== 'false';
        $orderby = sanitize_text_field($atts['orderby']);
        $order = sanitize_text_field($atts['order']);
        $property_type = sanitize_text_field($atts['property_type']);
        $property_status = sanitize_text_field($atts['property_status']);
        $featured_only = $atts['featured_only'] !== 'false';
        $infinite_scroll = $atts['infinite_scroll'] !== 'false';
        $show_pagination = $atts['show_pagination'] !== 'false';

        $container_id = 'resbs-infinite-properties-' . uniqid();

        ob_start();
        ?>
        <div class="resbs-infinite-properties-container" id="<?php echo esc_attr($container_id); ?>">
            <?php if ($show_filters): ?>
                <div class="resbs-infinite-filters">
                    <form class="resbs-infinite-filter-form" data-target="<?php echo esc_attr($container_id); ?>">
                        <?php wp_nonce_field('resbs_infinite_scroll_nonce', 'resbs_infinite_nonce'); ?>
                        
                        <div class="resbs-filter-row">
                            <div class="resbs-filter-group">
                                <label for="property_type_<?php echo esc_attr($container_id); ?>">
                                    <?php esc_html_e('Property Type', 'realestate-booking-suite'); ?>
                                </label>
                                <select name="property_type" id="property_type_<?php echo esc_attr($container_id); ?>">
                                    <option value=""><?php esc_html_e('All Types', 'realestate-booking-suite'); ?></option>
                                    <?php
                                    $property_types = get_terms(array(
                                        'taxonomy' => 'property_type',
                                        'hide_empty' => false,
                                    ));
                                    foreach ($property_types as $type) {
                                        $selected = selected($property_type, $type->slug, false);
                                        echo '<option value="' . esc_attr($type->slug) . '"' . $selected . '>' . esc_html($type->name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="resbs-filter-group">
                                <label for="property_status_<?php echo esc_attr($container_id); ?>">
                                    <?php esc_html_e('Status', 'realestate-booking-suite'); ?>
                                </label>
                                <select name="property_status" id="property_status_<?php echo esc_attr($container_id); ?>">
                                    <option value=""><?php esc_html_e('All Status', 'realestate-booking-suite'); ?></option>
                                    <?php
                                    $property_statuses = get_terms(array(
                                        'taxonomy' => 'property_status',
                                        'hide_empty' => false,
                                    ));
                                    foreach ($property_statuses as $status) {
                                        $selected = selected($property_status, $status->slug, false);
                                        echo '<option value="' . esc_attr($status->slug) . '"' . $selected . '>' . esc_html($status->name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="resbs-filter-group">
                                <label for="price_min_<?php echo esc_attr($container_id); ?>">
                                    <?php esc_html_e('Min Price', 'realestate-booking-suite'); ?>
                                </label>
                                <input type="number" name="price_min" id="price_min_<?php echo esc_attr($container_id); ?>" 
                                       placeholder="<?php esc_attr_e('Min Price', 'realestate-booking-suite'); ?>">
                            </div>

                            <div class="resbs-filter-group">
                                <label for="price_max_<?php echo esc_attr($container_id); ?>">
                                    <?php esc_html_e('Max Price', 'realestate-booking-suite'); ?>
                                </label>
                                <input type="number" name="price_max" id="price_max_<?php echo esc_attr($container_id); ?>" 
                                       placeholder="<?php esc_attr_e('Max Price', 'realestate-booking-suite'); ?>">
                            </div>

                            <div class="resbs-filter-group">
                                <button type="submit" class="resbs-filter-btn">
                                    <?php esc_html_e('Filter', 'realestate-booking-suite'); ?>
                                </button>
                                <button type="button" class="resbs-reset-btn">
                                    <?php esc_html_e('Reset', 'realestate-booking-suite'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <div class="resbs-infinite-properties-grid resbs-columns-<?php echo esc_attr($columns); ?>" 
                 data-settings="<?php echo esc_attr(wp_json_encode(array(
                     'posts_per_page' => $posts_per_page,
                     'columns' => $columns,
                     'show_price' => $show_price,
                     'show_meta' => $show_meta,
                     'show_excerpt' => $show_excerpt,
                     'show_badges' => $show_badges,
                     'show_favorite_button' => $show_favorite_button,
                     'show_book_button' => $show_book_button,
                     'orderby' => $orderby,
                     'order' => $order,
                     'property_type' => $property_type,
                     'property_status' => $property_status,
                     'featured_only' => $featured_only,
                     'infinite_scroll' => $infinite_scroll,
                     'show_pagination' => $show_pagination
                 ))); ?>">
                <!-- Properties will be loaded here -->
            </div>

            <div class="resbs-infinite-scroll-controls">
                <?php if ($infinite_scroll): ?>
                    <button type="button" class="resbs-load-more-btn" style="display: none;">
                        <span class="resbs-load-more-text"><?php esc_html_e('Load More Properties', 'realestate-booking-suite'); ?></span>
                        <span class="resbs-loading-spinner" style="display: none;">
                            <span class="dashicons dashicons-update"></span>
                        </span>
                    </button>
                <?php endif; ?>

                <?php if ($show_pagination): ?>
                    <div class="resbs-pagination-fallback" style="display: none;">
                        <!-- Pagination will be loaded here -->
                    </div>
                <?php endif; ?>
            </div>

            <div class="resbs-infinite-scroll-info">
                <div class="resbs-properties-count">
                    <span class="resbs-count-text"><?php esc_html_e('Properties Found', 'realestate-booking-suite'); ?>: <span class="resbs-count-number">0</span></span>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Add infinite scroll support to existing widgets
     */
    public function add_infinite_scroll_support($widget_id, $instance) {
        if (!isset($instance['enable_infinite_scroll']) || !$instance['enable_infinite_scroll']) {
            return;
        }

        ?>
        <div class="resbs-infinite-scroll-controls" data-widget-id="<?php echo esc_attr($widget_id); ?>">
            <button type="button" class="resbs-load-more-btn" style="display: none;">
                <span class="resbs-load-more-text"><?php esc_html_e('Load More Properties', 'realestate-booking-suite'); ?></span>
                <span class="resbs-loading-spinner" style="display: none;">
                    <span class="dashicons dashicons-update"></span>
                </span>
            </button>
            
            <div class="resbs-pagination-fallback" style="display: none;">
                <!-- Pagination will be loaded here -->
            </div>
        </div>
        <?php
    }

    /**
     * Register infinite scroll widget
     */
    public function register_infinite_scroll_widget() {
        register_widget('RESBS_Infinite_Scroll_Widget');
    }
}

/**
 * Infinite Scroll Widget
 */
class RESBS_Infinite_Scroll_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'resbs_infinite_scroll_widget',
            esc_html__('Infinite Scroll Properties', 'realestate-booking-suite'),
            array(
                'description' => esc_html__('Display properties with infinite scroll and pagination fallback.', 'realestate-booking-suite'),
                'classname' => 'resbs-infinite-scroll-widget'
            )
        );
    }

    /**
     * Widget form
     */
    public function form($instance) {
        $defaults = array(
            'title' => esc_html__('Properties', 'realestate-booking-suite'),
            'posts_per_page' => '12',
            'columns' => '3',
            'show_filters' => true,
            'show_price' => true,
            'show_meta' => true,
            'show_excerpt' => true,
            'show_badges' => true,
            'show_favorite_button' => true,
            'show_book_button' => true,
            'orderby' => 'date',
            'order' => 'DESC',
            'enable_infinite_scroll' => true,
            'show_pagination' => true
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        
        $title = sanitize_text_field($instance['title']);
        $posts_per_page = intval($instance['posts_per_page']);
        $columns = intval($instance['columns']);
        $show_filters = (bool) $instance['show_filters'];
        $show_price = (bool) $instance['show_price'];
        $show_meta = (bool) $instance['show_meta'];
        $show_excerpt = (bool) $instance['show_excerpt'];
        $show_badges = (bool) $instance['show_badges'];
        $show_favorite_button = (bool) $instance['show_favorite_button'];
        $show_book_button = (bool) $instance['show_book_button'];
        $orderby = sanitize_text_field($instance['orderby']);
        $order = sanitize_text_field($instance['order']);
        $enable_infinite_scroll = (bool) $instance['enable_infinite_scroll'];
        $show_pagination = (bool) $instance['show_pagination'];
        ?>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'realestate-booking-suite'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>">
                <?php esc_html_e('Properties per page:', 'realestate-booking-suite'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('posts_per_page')); ?>" 
                   type="number" min="1" max="50" value="<?php echo esc_attr($posts_per_page); ?>" />
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('columns')); ?>">
                <?php esc_html_e('Columns:', 'realestate-booking-suite'); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('columns')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('columns')); ?>">
                <option value="1" <?php selected($columns, 1); ?>><?php esc_html_e('1 Column', 'realestate-booking-suite'); ?></option>
                <option value="2" <?php selected($columns, 2); ?>><?php esc_html_e('2 Columns', 'realestate-booking-suite'); ?></option>
                <option value="3" <?php selected($columns, 3); ?>><?php esc_html_e('3 Columns', 'realestate-booking-suite'); ?></option>
                <option value="4" <?php selected($columns, 4); ?>><?php esc_html_e('4 Columns', 'realestate-booking-suite'); ?></option>
            </select>
        </p>
        
        <h4><?php esc_html_e('Display Options:', 'realestate-booking-suite'); ?></h4>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_filters); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_filters')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_filters')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_filters')); ?>">
                <?php esc_html_e('Show Filters', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_price); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_price')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_price')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_price')); ?>">
                <?php esc_html_e('Show Price', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_meta); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_meta')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_meta')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_meta')); ?>">
                <?php esc_html_e('Show Meta', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_excerpt); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_excerpt')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_excerpt')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_excerpt')); ?>">
                <?php esc_html_e('Show Excerpt', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_badges); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_badges')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_badges')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_badges')); ?>">
                <?php esc_html_e('Show Badges', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_favorite_button); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_favorite_button')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_favorite_button')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_favorite_button')); ?>">
                <?php esc_html_e('Show Favorite Button', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_book_button); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_book_button')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_book_button')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_book_button')); ?>">
                <?php esc_html_e('Show Book Button', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <h4><?php esc_html_e('Pagination Options:', 'realestate-booking-suite'); ?></h4>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($enable_infinite_scroll); ?> 
                   id="<?php echo esc_attr($this->get_field_id('enable_infinite_scroll')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('enable_infinite_scroll')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('enable_infinite_scroll')); ?>">
                <?php esc_html_e('Enable Infinite Scroll', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_pagination); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_pagination')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_pagination')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_pagination')); ?>">
                <?php esc_html_e('Show Pagination Fallback', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <?php
    }

    /**
     * Update widget
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['posts_per_page'] = intval($new_instance['posts_per_page']);
        $instance['columns'] = intval($new_instance['columns']);
        $instance['show_filters'] = isset($new_instance['show_filters']);
        $instance['show_price'] = isset($new_instance['show_price']);
        $instance['show_meta'] = isset($new_instance['show_meta']);
        $instance['show_excerpt'] = isset($new_instance['show_excerpt']);
        $instance['show_badges'] = isset($new_instance['show_badges']);
        $instance['show_favorite_button'] = isset($new_instance['show_favorite_button']);
        $instance['show_book_button'] = isset($new_instance['show_book_button']);
        $instance['orderby'] = sanitize_text_field($new_instance['orderby']);
        $instance['order'] = sanitize_text_field($new_instance['order']);
        $instance['enable_infinite_scroll'] = isset($new_instance['enable_infinite_scroll']);
        $instance['show_pagination'] = isset($new_instance['show_pagination']);
        
        return $instance;
    }

    /**
     * Display widget
     */
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', sanitize_text_field($instance['title']));
        $posts_per_page = intval($instance['posts_per_page']);
        $columns = intval($instance['columns']);
        $show_filters = (bool) $instance['show_filters'];
        $show_price = (bool) $instance['show_price'];
        $show_meta = (bool) $instance['show_meta'];
        $show_excerpt = (bool) $instance['show_excerpt'];
        $show_badges = (bool) $instance['show_badges'];
        $show_favorite_button = (bool) $instance['show_favorite_button'];
        $show_book_button = (bool) $instance['show_book_button'];
        $orderby = sanitize_text_field($instance['orderby']);
        $order = sanitize_text_field($instance['order']);
        $enable_infinite_scroll = (bool) $instance['enable_infinite_scroll'];
        $show_pagination = (bool) $instance['show_pagination'];
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        // Display properties using shortcode
        echo do_shortcode('[resbs_infinite_properties posts_per_page="' . esc_attr($posts_per_page) . '" columns="' . esc_attr($columns) . '" show_filters="' . ($show_filters ? 'true' : 'false') . '" show_price="' . ($show_price ? 'true' : 'false') . '" show_meta="' . ($show_meta ? 'true' : 'false') . '" show_excerpt="' . ($show_excerpt ? 'true' : 'false') . '" show_badges="' . ($show_badges ? 'true' : 'false') . '" show_favorite_button="' . ($show_favorite_button ? 'true' : 'false') . '" show_book_button="' . ($show_book_button ? 'true' : 'false') . '" orderby="' . esc_attr($orderby) . '" order="' . esc_attr($order) . '" infinite_scroll="' . ($enable_infinite_scroll ? 'true' : 'false') . '" show_pagination="' . ($show_pagination ? 'true' : 'false') . '"]');
        
        echo $args['after_widget'];
    }
}

// Initialize Infinite Scroll Manager
new RESBS_Infinite_Scroll_Manager();
