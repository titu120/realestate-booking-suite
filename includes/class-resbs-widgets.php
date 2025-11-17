<?php
/**
 * WordPress Appearance Widgets Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Property Grid Widget Class
 */
class RESBS_Property_Grid_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'resbs_property_grid_widget',
            esc_html__('Property Grid', 'realestate-booking-suite'),
            array(
                'description' => esc_html__('Display properties in a grid layout with filters for price, location, and property type.', 'realestate-booking-suite'),
                'classname' => 'resbs-property-grid-widget'
            )
        );

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_widget_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_resbs_filter_widget_properties', array($this, 'ajax_filter_properties'));
        add_action('wp_ajax_nopriv_resbs_filter_widget_properties', array($this, 'ajax_filter_properties'));
        add_action('wp_ajax_resbs_toggle_favorite', array($this, 'ajax_toggle_favorite'));
        add_action('wp_ajax_nopriv_resbs_toggle_favorite', array($this, 'ajax_toggle_favorite'));
    }

    /**
     * Enqueue widget assets
     */
    public function enqueue_widget_assets() {
        if (is_active_widget(false, false, $this->id_base)) {
            wp_enqueue_style(
                'resbs-widget-style',
                RESBS_URL . 'assets/css/widgets.css',
                array(),
                '1.0.0'
            );

            wp_enqueue_script(
                'resbs-widget-script',
                RESBS_URL . 'assets/js/widgets.js',
                array('jquery'),
                '1.0.0',
                true
            );

            wp_localize_script('resbs-widget-script', 'resbs_widget_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('resbs_widget_nonce'),
                'messages' => array(
                    'loading' => esc_html__('Loading...', 'realestate-booking-suite'),
                    'no_properties' => esc_html__('No properties found.', 'realestate-booking-suite'),
                    'error' => esc_html__('An error occurred. Please try again.', 'realestate-booking-suite')
                )
            ));
        }
    }

    /**
     * Widget form
     */
    public function form($instance) {
        $defaults = array(
            'title' => esc_html__('Properties', 'realestate-booking-suite'),
            'posts_per_page' => 6,
            'columns' => 3,
            'show_filters' => true,
            'show_price_filter' => true,
            'show_location_filter' => true,
            'show_type_filter' => true,
            'show_price' => true,
            'show_meta' => true,
            'show_excerpt' => false,
            'show_badges' => true,
            'show_favorite_button' => true,
            'show_book_button' => true,
            'orderby' => 'date',
            'order' => 'DESC',
            'property_type' => '',
            'property_status' => '',
            'featured_only' => false,
            'widget_style' => 'default',
            'enable_infinite_scroll' => false,
            'show_pagination' => true,
            'layout' => 'grid',
            'carousel_autoplay' => false,
            'carousel_autoplay_speed' => 3000,
            'carousel_show_dots' => true,
            'carousel_show_arrows' => true
        );

        $instance = wp_parse_args((array) $instance, $defaults);

        // Sanitize form values
        $title = sanitize_text_field($instance['title']);
        $posts_per_page = intval($instance['posts_per_page']);
        $columns = intval($instance['columns']);
        $show_filters = (bool) $instance['show_filters'];
        $show_price_filter = (bool) $instance['show_price_filter'];
        $show_location_filter = (bool) $instance['show_location_filter'];
        $show_type_filter = (bool) $instance['show_type_filter'];
        $show_price = (bool) $instance['show_price'];
        $show_meta = (bool) $instance['show_meta'];
        $show_excerpt = (bool) $instance['show_excerpt'];
        $show_badges = (bool) $instance['show_badges'];
        $show_favorite_button = (bool) $instance['show_favorite_button'];
        $show_book_button = (bool) $instance['show_book_button'];
        $orderby = sanitize_text_field($instance['orderby']);
        $order = sanitize_text_field($instance['order']);
        $property_type = sanitize_text_field($instance['property_type']);
        $property_status = sanitize_text_field($instance['property_status']);
        $featured_only = (bool) $instance['featured_only'];
        $widget_style = sanitize_text_field($instance['widget_style']);
        $enable_infinite_scroll = (bool) $instance['enable_infinite_scroll'];
        $show_pagination = (bool) $instance['show_pagination'];
        $layout = sanitize_text_field($instance['layout']);
        $carousel_autoplay = (bool) $instance['carousel_autoplay'];
        $carousel_autoplay_speed = intval($instance['carousel_autoplay_speed']);
        $carousel_show_dots = (bool) $instance['carousel_show_dots'];
        $carousel_show_arrows = (bool) $instance['carousel_show_arrows'];
        ?>

        <div class="resbs-widget-form">
            <!-- Title -->
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                    <?php esc_html_e('Title:', 'realestate-booking-suite'); ?>
                </label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                       type="text" value="<?php echo esc_attr($title); ?>" />
            </p>

            <!-- Posts Per Page -->
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>">
                    <?php esc_html_e('Number of Properties:', 'realestate-booking-suite'); ?>
                </label>
                <input class="widefat" id="<?php echo esc_attr($this->get_field_id('posts_per_page')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('posts_per_page')); ?>" 
                       type="number" min="1" max="20" value="<?php echo esc_attr($posts_per_page); ?>" />
            </p>

            <!-- Columns -->
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

            <!-- Widget Style -->
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('widget_style')); ?>">
                    <?php esc_html_e('Widget Style:', 'realestate-booking-suite'); ?>
                </label>
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('widget_style')); ?>" 
                        name="<?php echo esc_attr($this->get_field_name('widget_style')); ?>">
                    <option value="default" <?php selected($widget_style, 'default'); ?>><?php esc_html_e('Default', 'realestate-booking-suite'); ?></option>
                    <option value="modern" <?php selected($widget_style, 'modern'); ?>><?php esc_html_e('Modern', 'realestate-booking-suite'); ?></option>
                    <option value="minimal" <?php selected($widget_style, 'minimal'); ?>><?php esc_html_e('Minimal', 'realestate-booking-suite'); ?></option>
                    <option value="card" <?php selected($widget_style, 'card'); ?>><?php esc_html_e('Card Style', 'realestate-booking-suite'); ?></option>
                </select>
            </p>

            <!-- Show Filters -->
            <p>
                <input class="checkbox" type="checkbox" <?php checked($show_filters); ?> 
                       id="<?php echo esc_attr($this->get_field_id('show_filters')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('show_filters')); ?>" />
                <label for="<?php echo esc_attr($this->get_field_id('show_filters')); ?>">
                    <?php esc_html_e('Show Filters', 'realestate-booking-suite'); ?>
                </label>
            </p>

            <!-- Filter Options -->
            <div class="resbs-filter-options" style="<?php echo esc_attr($show_filters ? '' : 'display: none;'); ?>">
                <p>
                    <input class="checkbox" type="checkbox" <?php checked($show_price_filter); ?> 
                           id="<?php echo esc_attr($this->get_field_id('show_price_filter')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('show_price_filter')); ?>" />
                    <label for="<?php echo esc_attr($this->get_field_id('show_price_filter')); ?>">
                        <?php esc_html_e('Show Price Filter', 'realestate-booking-suite'); ?>
                    </label>
                </p>

                <p>
                    <input class="checkbox" type="checkbox" <?php checked($show_location_filter); ?> 
                           id="<?php echo esc_attr($this->get_field_id('show_location_filter')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('show_location_filter')); ?>" />
                    <label for="<?php echo esc_attr($this->get_field_id('show_location_filter')); ?>">
                        <?php esc_html_e('Show Location Filter', 'realestate-booking-suite'); ?>
                    </label>
                </p>

                <p>
                    <input class="checkbox" type="checkbox" <?php checked($show_type_filter); ?> 
                           id="<?php echo esc_attr($this->get_field_id('show_type_filter')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('show_type_filter')); ?>" />
                    <label for="<?php echo esc_attr($this->get_field_id('show_type_filter')); ?>">
                        <?php esc_html_e('Show Property Type Filter', 'realestate-booking-suite'); ?>
                    </label>
                </p>
            </div>

            <!-- Display Options -->
            <h4><?php esc_html_e('Display Options', 'realestate-booking-suite'); ?></h4>
            
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
                    <?php esc_html_e('Show Meta Info (Bedrooms, Bathrooms)', 'realestate-booking-suite'); ?>
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
                    <?php esc_html_e('Show Status Badges', 'realestate-booking-suite'); ?>
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

            <!-- Order By -->
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('orderby')); ?>">
                    <?php esc_html_e('Order By:', 'realestate-booking-suite'); ?>
                </label>
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('orderby')); ?>" 
                        name="<?php echo esc_attr($this->get_field_name('orderby')); ?>">
                    <option value="date" <?php selected($orderby, 'date'); ?>><?php esc_html_e('Date', 'realestate-booking-suite'); ?></option>
                    <option value="title" <?php selected($orderby, 'title'); ?>><?php esc_html_e('Title', 'realestate-booking-suite'); ?></option>
                    <option value="price" <?php selected($orderby, 'price'); ?>><?php esc_html_e('Price', 'realestate-booking-suite'); ?></option>
                    <option value="rand" <?php selected($orderby, 'rand'); ?>><?php esc_html_e('Random', 'realestate-booking-suite'); ?></option>
                </select>
            </p>

            <!-- Order -->
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('order')); ?>">
                    <?php esc_html_e('Order:', 'realestate-booking-suite'); ?>
                </label>
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('order')); ?>" 
                        name="<?php echo esc_attr($this->get_field_name('order')); ?>">
                    <option value="ASC" <?php selected($order, 'ASC'); ?>><?php esc_html_e('Ascending', 'realestate-booking-suite'); ?></option>
                    <option value="DESC" <?php selected($order, 'DESC'); ?>><?php esc_html_e('Descending', 'realestate-booking-suite'); ?></option>
                </select>
            </p>

            <!-- Property Type Filter -->
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('property_type')); ?>">
                    <?php esc_html_e('Filter by Property Type:', 'realestate-booking-suite'); ?>
                </label>
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('property_type')); ?>" 
                        name="<?php echo esc_attr($this->get_field_name('property_type')); ?>">
                    <option value=""><?php esc_html_e('All Types', 'realestate-booking-suite'); ?></option>
                    <?php
                    $property_types = get_terms(array(
                        'taxonomy' => 'property_type',
                        'hide_empty' => false,
                    ));
                    foreach ($property_types as $type) {
                        echo '<option value="' . esc_attr($type->slug) . '" ' . selected($property_type, $type->slug, false) . '>' . esc_html($type->name) . '</option>';
                    }
                    ?>
                </select>
            </p>

            <!-- Property Status Filter -->
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('property_status')); ?>">
                    <?php esc_html_e('Filter by Property Status:', 'realestate-booking-suite'); ?>
                </label>
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('property_status')); ?>" 
                        name="<?php echo esc_attr($this->get_field_name('property_status')); ?>">
                    <option value=""><?php esc_html_e('All Status', 'realestate-booking-suite'); ?></option>
                    <?php
                    $property_statuses = get_terms(array(
                        'taxonomy' => 'property_status',
                        'hide_empty' => false,
                    ));
                    foreach ($property_statuses as $status) {
                        echo '<option value="' . esc_attr($status->slug) . '" ' . selected($property_status, $status->slug, false) . '>' . esc_html($status->name) . '</option>';
                    }
                    ?>
                </select>
            </p>

            <!-- Featured Only -->
            <p>
                <input class="checkbox" type="checkbox" <?php checked($featured_only); ?> 
                       id="<?php echo esc_attr($this->get_field_id('featured_only')); ?>" 
                       name="<?php echo esc_attr($this->get_field_name('featured_only')); ?>" />
                <label for="<?php echo esc_attr($this->get_field_id('featured_only')); ?>">
                    <?php esc_html_e('Show Featured Properties Only', 'realestate-booking-suite'); ?>
                </label>
            </p>

            <!-- Layout Options -->
            <h4><?php esc_html_e('Layout Options', 'realestate-booking-suite'); ?></h4>
            
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('layout')); ?>">
                    <?php esc_html_e('Layout', 'realestate-booking-suite'); ?>
                </label>
                <select class="widefat" id="<?php echo esc_attr($this->get_field_id('layout')); ?>" 
                        name="<?php echo esc_attr($this->get_field_name('layout')); ?>">
                    <option value="grid" <?php selected($layout, 'grid'); ?>><?php esc_html_e('Grid', 'realestate-booking-suite'); ?></option>
                    <option value="list" <?php selected($layout, 'list'); ?>><?php esc_html_e('List', 'realestate-booking-suite'); ?></option>
                    <option value="carousel" <?php selected($layout, 'carousel'); ?>><?php esc_html_e('Carousel', 'realestate-booking-suite'); ?></option>
                </select>
            </p>

            <!-- Carousel Options (only show when carousel is selected) -->
            <div class="resbs-carousel-options" style="<?php echo esc_attr($layout === 'carousel' ? '' : 'display: none;'); ?>">
                <p>
                    <input class="checkbox" type="checkbox" <?php checked($carousel_autoplay); ?> 
                           id="<?php echo esc_attr($this->get_field_id('carousel_autoplay')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('carousel_autoplay')); ?>" />
                    <label for="<?php echo esc_attr($this->get_field_id('carousel_autoplay')); ?>">
                        <?php esc_html_e('Enable Autoplay', 'realestate-booking-suite'); ?>
                    </label>
                </p>

                <p>
                    <label for="<?php echo esc_attr($this->get_field_id('carousel_autoplay_speed')); ?>">
                        <?php esc_html_e('Autoplay Speed (ms)', 'realestate-booking-suite'); ?>
                    </label>
                    <input class="widefat" type="number" id="<?php echo esc_attr($this->get_field_id('carousel_autoplay_speed')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('carousel_autoplay_speed')); ?>" 
                           value="<?php echo esc_attr($carousel_autoplay_speed); ?>" min="1000" max="10000" step="500" />
                </p>

                <p>
                    <input class="checkbox" type="checkbox" <?php checked($carousel_show_dots); ?> 
                           id="<?php echo esc_attr($this->get_field_id('carousel_show_dots')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('carousel_show_dots')); ?>" />
                    <label for="<?php echo esc_attr($this->get_field_id('carousel_show_dots')); ?>">
                        <?php esc_html_e('Show Dots', 'realestate-booking-suite'); ?>
                    </label>
                </p>

                <p>
                    <input class="checkbox" type="checkbox" <?php checked($carousel_show_arrows); ?> 
                           id="<?php echo esc_attr($this->get_field_id('carousel_show_arrows')); ?>" 
                           name="<?php echo esc_attr($this->get_field_name('carousel_show_arrows')); ?>" />
                    <label for="<?php echo esc_attr($this->get_field_id('carousel_show_arrows')); ?>">
                        <?php esc_html_e('Show Arrows', 'realestate-booking-suite'); ?>
                    </label>
                </p>
            </div>

            <!-- Infinite Scroll Options -->
            <h4><?php esc_html_e('Pagination Options', 'realestate-booking-suite'); ?></h4>
            
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
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('#<?php echo esc_js($this->get_field_id('show_filters')); ?>').change(function() {
                if ($(this).is(':checked')) {
                    $('.resbs-filter-options').show();
                } else {
                    $('.resbs-filter-options').hide();
                }
            });
            
            $('#<?php echo esc_js($this->get_field_id('layout')); ?>').change(function() {
                if ($(this).val() === 'carousel') {
                    $('.resbs-carousel-options').show();
                } else {
                    $('.resbs-carousel-options').hide();
                }
            });
        });
        </script>
        <?php
    }

    /**
     * Update widget settings
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        
        // Sanitize all inputs
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['posts_per_page'] = intval($new_instance['posts_per_page']);
        $instance['columns'] = intval($new_instance['columns']);
        $instance['show_filters'] = isset($new_instance['show_filters']) ? (bool) $new_instance['show_filters'] : false;
        $instance['show_price_filter'] = isset($new_instance['show_price_filter']) ? (bool) $new_instance['show_price_filter'] : false;
        $instance['show_location_filter'] = isset($new_instance['show_location_filter']) ? (bool) $new_instance['show_location_filter'] : false;
        $instance['show_type_filter'] = isset($new_instance['show_type_filter']) ? (bool) $new_instance['show_type_filter'] : false;
        $instance['show_price'] = isset($new_instance['show_price']) ? (bool) $new_instance['show_price'] : false;
        $instance['show_meta'] = isset($new_instance['show_meta']) ? (bool) $new_instance['show_meta'] : false;
        $instance['show_excerpt'] = isset($new_instance['show_excerpt']) ? (bool) $new_instance['show_excerpt'] : false;
        $instance['show_badges'] = isset($new_instance['show_badges']) ? (bool) $new_instance['show_badges'] : false;
        $instance['show_favorite_button'] = isset($new_instance['show_favorite_button']) ? (bool) $new_instance['show_favorite_button'] : false;
        $instance['show_book_button'] = isset($new_instance['show_book_button']) ? (bool) $new_instance['show_book_button'] : false;
        $instance['orderby'] = sanitize_text_field($new_instance['orderby']);
        $instance['order'] = sanitize_text_field($new_instance['order']);
        $instance['property_type'] = sanitize_text_field($new_instance['property_type']);
        $instance['property_status'] = sanitize_text_field($new_instance['property_status']);
        $instance['featured_only'] = isset($new_instance['featured_only']) ? (bool) $new_instance['featured_only'] : false;
        $instance['widget_style'] = sanitize_text_field($new_instance['widget_style']);
        $instance['enable_infinite_scroll'] = isset($new_instance['enable_infinite_scroll']) ? (bool) $new_instance['enable_infinite_scroll'] : false;
        $instance['show_pagination'] = isset($new_instance['show_pagination']) ? (bool) $new_instance['show_pagination'] : false;
        $instance['layout'] = sanitize_text_field($new_instance['layout']);
        $instance['carousel_autoplay'] = isset($new_instance['carousel_autoplay']) ? (bool) $new_instance['carousel_autoplay'] : false;
        $instance['carousel_autoplay_speed'] = isset($new_instance['carousel_autoplay_speed']) ? intval($new_instance['carousel_autoplay_speed']) : 3000;
        $instance['carousel_show_dots'] = isset($new_instance['carousel_show_dots']) ? (bool) $new_instance['carousel_show_dots'] : true;
        $instance['carousel_show_arrows'] = isset($new_instance['carousel_show_arrows']) ? (bool) $new_instance['carousel_show_arrows'] : true;

        return $instance;
    }

    /**
     * Display widget
     */
    public function widget($args, $instance) {
        // Sanitize instance values
        $title = apply_filters('widget_title', sanitize_text_field($instance['title']));
        $posts_per_page = intval($instance['posts_per_page']);
        $columns = intval($instance['columns']);
        $show_filters = (bool) $instance['show_filters'];
        $show_price_filter = (bool) $instance['show_price_filter'];
        $show_location_filter = (bool) $instance['show_location_filter'];
        $show_type_filter = (bool) $instance['show_type_filter'];
        $show_price = (bool) $instance['show_price'];
        $show_meta = (bool) $instance['show_meta'];
        $show_excerpt = (bool) $instance['show_excerpt'];
        $show_badges = (bool) $instance['show_badges'];
        $show_favorite_button = (bool) $instance['show_favorite_button'];
        $show_book_button = (bool) $instance['show_book_button'];
        $orderby = sanitize_text_field($instance['orderby']);
        $order = sanitize_text_field($instance['order']);
        $property_type = sanitize_text_field($instance['property_type']);
        $property_status = sanitize_text_field($instance['property_status']);
        $featured_only = (bool) $instance['featured_only'];
        $widget_style = sanitize_text_field($instance['widget_style']);
        $enable_infinite_scroll = (bool) $instance['enable_infinite_scroll'];
        $show_pagination = (bool) $instance['show_pagination'];
        $layout = sanitize_text_field($instance['layout']);
        $carousel_autoplay = (bool) $instance['carousel_autoplay'];
        $carousel_autoplay_speed = intval($instance['carousel_autoplay_speed']);
        $carousel_show_dots = (bool) $instance['carousel_show_dots'];
        $carousel_show_arrows = (bool) $instance['carousel_show_arrows'];

        // Generate unique widget ID
        $widget_id = 'resbs-widget-' . $this->id;

        // Allow common HTML tags used in widget wrappers
        $allowed_widget_html = array(
            'div' => array('class' => array(), 'id' => array()),
            'section' => array('class' => array(), 'id' => array()),
            'aside' => array('class' => array(), 'id' => array()),
            'h1' => array('class' => array()),
            'h2' => array('class' => array()),
            'h3' => array('class' => array()),
            'h4' => array('class' => array()),
            'h5' => array('class' => array()),
            'h6' => array('class' => array()),
        );

        echo wp_kses($args['before_widget'], $allowed_widget_html);

        if (!empty($title)) {
            echo wp_kses($args['before_title'], $allowed_widget_html) . esc_html($title) . wp_kses($args['after_title'], $allowed_widget_html);
        }

        ?>
        <div class="resbs-property-grid-widget resbs-style-<?php echo esc_attr($widget_style); ?> resbs-layout-<?php echo esc_attr($layout); ?>" 
             id="<?php echo esc_attr($widget_id); ?>"
             data-settings="<?php echo esc_attr(wp_json_encode(array(
                 'posts_per_page' => $posts_per_page,
                 'columns' => $columns,
                 'show_filters' => $show_filters,
                 'show_price_filter' => $show_price_filter,
                 'show_location_filter' => $show_location_filter,
                 'show_type_filter' => $show_type_filter,
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
                 'enable_infinite_scroll' => $enable_infinite_scroll,
                 'show_pagination' => $show_pagination,
                 'layout' => $layout,
                 'carousel_autoplay' => $carousel_autoplay,
                 'carousel_autoplay_speed' => $carousel_autoplay_speed,
                 'carousel_show_dots' => $carousel_show_dots,
                 'carousel_show_arrows' => $carousel_show_arrows
             ))); ?>">

            <?php if ($show_filters): ?>
                <div class="resbs-widget-filters">
                    <form class="resbs-filter-form" data-target="<?php echo esc_attr($widget_id); ?>">
                        <?php wp_nonce_field('resbs_widget_filter', 'resbs_filter_nonce'); ?>
                        
                        <div class="resbs-filter-row">
                            <?php if ($show_type_filter): ?>
                                <div class="resbs-filter-group">
                                    <label for="property_type_<?php echo esc_attr($widget_id); ?>">
                                        <?php esc_html_e('Property Type', 'realestate-booking-suite'); ?>
                                    </label>
                                    <select name="property_type" id="property_type_<?php echo esc_attr($widget_id); ?>">
                                        <option value=""><?php esc_html_e('All Types', 'realestate-booking-suite'); ?></option>
                                        <?php
                                        $property_types = get_terms(array(
                                            'taxonomy' => 'property_type',
                                            'hide_empty' => false,
                                        ));
                                        foreach ($property_types as $type) {
                                            echo '<option value="' . esc_attr($type->slug) . '">' . esc_html($type->name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <?php if ($show_location_filter): ?>
                                <div class="resbs-filter-group">
                                    <label for="location_<?php echo esc_attr($widget_id); ?>">
                                        <?php esc_html_e('Location', 'realestate-booking-suite'); ?>
                                    </label>
                                    <select name="location" id="location_<?php echo esc_attr($widget_id); ?>">
                                        <option value=""><?php esc_html_e('All Locations', 'realestate-booking-suite'); ?></option>
                                        <?php
                                        $locations = get_terms(array(
                                            'taxonomy' => 'property_location',
                                            'hide_empty' => false,
                                        ));
                                        foreach ($locations as $location) {
                                            echo '<option value="' . esc_attr($location->slug) . '">' . esc_html($location->name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <?php if ($show_price_filter): ?>
                                <div class="resbs-filter-group">
                                    <label for="price_min_<?php echo esc_attr($widget_id); ?>">
                                        <?php esc_html_e('Min Price', 'realestate-booking-suite'); ?>
                                    </label>
                                    <input type="number" name="price_min" id="price_min_<?php echo esc_attr($widget_id); ?>" 
                                           placeholder="<?php esc_attr_e('Min Price', 'realestate-booking-suite'); ?>">
                                </div>
                                <div class="resbs-filter-group">
                                    <label for="price_max_<?php echo esc_attr($widget_id); ?>">
                                        <?php esc_html_e('Max Price', 'realestate-booking-suite'); ?>
                                    </label>
                                    <input type="number" name="price_max" id="price_max_<?php echo esc_attr($widget_id); ?>" 
                                           placeholder="<?php esc_attr_e('Max Price', 'realestate-booking-suite'); ?>">
                                </div>
                            <?php endif; ?>

                            <div class="resbs-filter-actions">
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

            <?php if ($layout === 'carousel'): ?>
                <div class="resbs-property-carousel" 
                     data-autoplay="<?php echo esc_attr($carousel_autoplay ? 'true' : 'false'); ?>"
                     data-autoplay-speed="<?php echo esc_attr($carousel_autoplay_speed); ?>"
                     data-show-dots="<?php echo esc_attr($carousel_show_dots ? 'true' : 'false'); ?>"
                     data-show-arrows="<?php echo esc_attr($carousel_show_arrows ? 'true' : 'false'); ?>">
                    <div class="resbs-carousel-wrapper">
                        <div class="resbs-carousel-track">
                            <?php $this->render_properties($instance, $widget_id); ?>
                        </div>
                    </div>
                    <?php if ($carousel_show_arrows): ?>
                        <button class="resbs-carousel-prev" aria-label="<?php esc_attr_e('Previous', 'realestate-booking-suite'); ?>">‹</button>
                        <button class="resbs-carousel-next" aria-label="<?php esc_attr_e('Next', 'realestate-booking-suite'); ?>">›</button>
                    <?php endif; ?>
                    <?php if ($carousel_show_dots): ?>
                        <div class="resbs-carousel-dots"></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="resbs-property-grid resbs-grid-<?php echo esc_attr($columns); ?>-cols resbs-layout-<?php echo esc_attr($layout); ?>" 
                     data-columns="<?php echo esc_attr($columns); ?>"
                     data-layout="<?php echo esc_attr($layout); ?>">
                    <?php $this->render_properties($instance, $widget_id); ?>
                </div>
            <?php endif; ?>

            <div class="resbs-widget-loading" style="display: none;">
                <div class="resbs-spinner"></div>
                <p><?php esc_html_e('Loading properties...', 'realestate-booking-suite'); ?></p>
            </div>

            <div class="resbs-widget-no-properties" style="display: none;">
                <p><?php esc_html_e('No properties found matching your criteria.', 'realestate-booking-suite'); ?></p>
            </div>
        </div>
        <?php

        // Allow common HTML tags used in widget wrappers
        $allowed_widget_html = array(
            'div' => array('class' => array(), 'id' => array()),
            'section' => array('class' => array(), 'id' => array()),
            'aside' => array('class' => array(), 'id' => array()),
        );

        echo wp_kses($args['after_widget'], $allowed_widget_html);
    }

    /**
     * Render properties
     */
    private function render_properties($instance, $widget_id = '') {
        // Build query args
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => intval($instance['posts_per_page']),
            'orderby' => sanitize_text_field($instance['orderby']),
            'order' => sanitize_text_field($instance['order']),
        );

        // Add meta query for featured properties
        if (!empty($instance['featured_only'])) {
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
        
        if (!empty($instance['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($instance['property_type'])
            );
        }

        if (!empty($instance['property_status'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => sanitize_text_field($instance['property_status'])
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        $properties = new WP_Query($query_args);

        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $this->render_property_card($instance, $instance['layout']);
            }
            wp_reset_postdata();
        } else {
            echo '<p class="resbs-no-properties">' . esc_html__('No properties found.', 'realestate-booking-suite') . '</p>';
        }

        // Add infinite scroll support
        do_action('resbs_property_grid_after', $widget_id, $instance);
    }

    /**
     * Render individual property card
     */
    private function render_property_card($instance, $layout = 'grid') {
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

        <div class="resbs-property-card resbs-layout-<?php echo esc_attr($layout); ?>">
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
                    <?php do_action('resbs_property_badges', $property_id, 'widget'); ?>
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
                    <div class="resbs-property-buttons">
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="resbs-btn resbs-btn-primary">
                            <?php esc_html_e('View Details', 'realestate-booking-suite'); ?>
                        </a>
                        <button type="button" class="resbs-btn resbs-btn-secondary resbs-book-btn" 
                                data-property-id="<?php echo esc_attr($property_id); ?>">
                            <?php esc_html_e('Book Now', 'realestate-booking-suite'); ?>
                        </button>
                    </div>
                <?php endif; ?>

                <!-- Quick View Button -->
                <?php do_action('resbs_property_card_after_content', $property_id, 'widget'); ?>
            </div>
        </div>
        <?php
    }

    /**
     * Format price
     */
    private function format_price($price) {
        $currency_symbol = esc_html(get_option('resbs_currency_symbol', '$'));
        $currency_position = get_option('resbs_currency_position', 'before');
        
        $formatted_price = number_format($price);
        
        if ($currency_position === 'before') {
            return $currency_symbol . $formatted_price;
        } else {
            return $formatted_price . $currency_symbol;
        }
    }

    /**
     * AJAX handler for filtering properties
     */
    public function ajax_filter_properties() {
        // Verify nonce using security helper
        RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'resbs_widget_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('filter_properties', 20, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        // Sanitize input using security helper
        $widget_id = RESBS_Security::sanitize_text($_POST['widget_id']);
        $settings = RESBS_Security::sanitize_array($_POST['settings'], 'RESBS_Security::sanitize_text');
        $filters = RESBS_Security::sanitize_array($_POST['filters'], 'RESBS_Security::sanitize_text');

        // Build query args
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => intval($settings['posts_per_page']),
            'orderby' => sanitize_text_field($settings['orderby']),
            'order' => sanitize_text_field($settings['order']),
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
        
        if (!empty($filters['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($filters['property_type'])
            );
        }

        if (!empty($filters['location'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_location',
                'field' => 'slug',
                'terms' => sanitize_text_field($filters['location'])
            );
        }

        if (!empty($settings['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => sanitize_text_field($settings['property_type'])
            );
        }

        if (!empty($settings['property_status'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => sanitize_text_field($settings['property_status'])
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        // Add price range meta query
        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $price_query = array();
            
            if (!empty($filters['price_min'])) {
                $price_query[] = array(
                    'key' => '_property_price',
                    'value' => intval($filters['price_min']),
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }
            
            if (!empty($filters['price_max'])) {
                $price_query[] = array(
                    'key' => '_property_price',
                    'value' => intval($filters['price_max']),
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                );
            }
            
            if (!empty($price_query)) {
                if (isset($query_args['meta_query'])) {
                    $query_args['meta_query']['relation'] = 'AND';
                    $query_args['meta_query'][] = $price_query;
                } else {
                    $query_args['meta_query'] = $price_query;
                }
            }
        }

        $properties = new WP_Query($query_args);

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
            'found_posts' => $properties->found_posts
        ));
    }

    /**
     * AJAX handler for toggling favorite
     */
    public function ajax_toggle_favorite() {
        // Check if nonce exists
        if (!isset($_POST['nonce']) || empty($_POST['nonce'])) {
            return; // Let other handlers process it
        }
        
        // Only process if this handler's nonce matches
        // If not, let other handlers (Favorites Manager) process it
        $nonce = sanitize_text_field($_POST['nonce']);
        if (!wp_verify_nonce($nonce, 'resbs_widget_nonce')) {
            // Not our nonce - let other handlers process it
            return;
        }
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('toggle_favorite', 30, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        // Check if user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => esc_html__('Please log in to add favorites.', 'realestate-booking-suite')
            ));
        }

        // Sanitize and validate property ID
        $property_id = RESBS_Security::sanitize_property_id($_POST['property_id']);
        $user_id = get_current_user_id();

        if (!$property_id) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid property ID.', 'realestate-booking-suite')
            ));
        }

        // Get user's favorites
        $favorites = get_user_meta($user_id, 'resbs_favorite_properties', true);
        if (!is_array($favorites)) {
            $favorites = array();
        }

        // Toggle favorite
        if (in_array($property_id, $favorites)) {
            // Remove from favorites
            $favorites = array_diff($favorites, array($property_id));
            $is_favorite = false;
            $message = esc_html__('Property removed from favorites.', 'realestate-booking-suite');
        } else {
            // Add to favorites
            $favorites[] = $property_id;
            $is_favorite = true;
            $message = esc_html__('Property added to favorites.', 'realestate-booking-suite');
        }

        // Update user meta
        update_user_meta($user_id, 'resbs_favorite_properties', $favorites);

        wp_send_json_success(array(
            'is_favorite' => $is_favorite,
            'message' => $message
        ));
    }
}

/**
 * Register widgets
 */
function resbs_register_widgets() {
    register_widget('RESBS_Property_Grid_Widget');
}
add_action('widgets_init', 'resbs_register_widgets');
