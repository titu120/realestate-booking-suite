<?php
/**
 * Elementor Property Slider Widget
 * 
 * SECURITY NOTES:
 * ===============
 * 
 * 1. DIRECT ACCESS PREVENTION:
 *    - ABSPATH check at top of file prevents direct file access
 *    - Class redeclaration check prevents conflicts
 * 
 * 2. NONCE REQUIREMENTS:
 *    - Favorite Button AJAX: Uses 'resbs_elementor_nonce' (created in class-resbs-elementor.php)
 *      - Nonce is localized to JavaScript as: resbs_elementor_ajax.nonce
 *      - AJAX handler: 'resbs_toggle_favorite' (handled in class-resbs-favorites.php or class-resbs-widgets.php)
 *      - Nonce verification: wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')
 *      - JavaScript handler: elementor.js -> toggleFavorite() function (line 397-416)
 * 
 * 3. USER PERMISSIONS:
 *    - Widget Display: No permission check needed (public content)
 *    - Favorite Button: Requires user to be logged in (checked server-side in AJAX handlers)
 *      - Capability check: is_user_logged_in() in AJAX handler
 *      - No admin capabilities required for favorites functionality
 * 
 * 4. DATA SANITIZATION:
 *    - All user input sanitized: sanitize_text_field(), intval(), esc_attr(), esc_html(), esc_url()
 *    - Settings from Elementor are already sanitized by Elementor framework
 *    - Property IDs validated: RESBS_Security::sanitize_property_id() in AJAX handlers
 * 
 * 5. AJAX SECURITY (Favorite Button):
 *    - Nonce verification: Required in AJAX handler (class-resbs-favorites.php or class-resbs-widgets.php)
 *    - Rate limiting: RESBS_Security::check_rate_limit() in AJAX handlers
 *    - User authentication: is_user_logged_in() check in AJAX handlers
 *    - Property ID validation: Must be valid post ID of 'property' post type
 * 
 * 6. OUTPUT ESCAPING:
 *    - All output uses esc_* functions: esc_html(), esc_attr(), esc_url()
 *    - JSON data: wp_json_encode() with esc_attr() wrapper
 *    - No direct echo of user input without escaping
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Ensure Elementor Widget_Base class is available
if (!class_exists('\Elementor\Widget_Base')) {
    return;
}

// Prevent class redeclaration
if (class_exists('RESBS_Slider_Widget')) {
    return;
}

class RESBS_Slider_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'resbs-slider';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return esc_html__('Property Slider', 'realestate-booking-suite');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-slider-push';
    }

    /**
     * Get widget categories
     */
    public function get_categories() {
        return array('resbs-widgets');
    }

    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return array('property', 'slider', 'carousel', 'featured', 'real estate');
    }
    
    /**
     * Get style dependencies
     */
    public function get_style_depends() {
        return array('resbs-elementor-slider-grid');
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => esc_html__('Content', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'title',
            array(
                'label' => esc_html__('Title', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Recommended Listings', 'realestate-booking-suite'),
            )
        );

        $this->add_control(
            'layout',
            array(
                'label' => esc_html__('Layout', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => array(
                    'grid' => esc_html__('Grid', 'realestate-booking-suite'),
                    'horizontal' => esc_html__('Horizontal Slider', 'realestate-booking-suite'),
                    'vertical' => esc_html__('Vertical Slider', 'realestate-booking-suite'),
                ),
            )
        );

        $this->add_control(
            'columns',
            array(
                'label' => esc_html__('Columns', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => array(
                    '1' => esc_html__('1 Column', 'realestate-booking-suite'),
                    '2' => esc_html__('2 Columns', 'realestate-booking-suite'),
                    '3' => esc_html__('3 Columns', 'realestate-booking-suite'),
                    '4' => esc_html__('4 Columns', 'realestate-booking-suite'),
                ),
                'condition' => array(
                    'layout' => 'grid',
                ),
            )
        );

        $this->add_control(
            'grid_gap',
            array(
                'label' => esc_html__('Grid Gap', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px', 'rem'),
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 100,
                        'step' => 1,
                    ),
                    'rem' => array(
                        'min' => 0,
                        'max' => 6,
                        'step' => 0.1,
                    ),
                ),
                'default' => array(
                    'unit' => 'rem',
                    'size' => 1.5,
                ),
                'condition' => array(
                    'layout' => 'grid',
                ),
            )
        );

        $this->end_controls_section();

        // Slider Settings
        $this->start_controls_section(
            'slider_settings_section',
            array(
                'label' => esc_html__('Slider Settings', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'posts_per_page',
            array(
                'label' => esc_html__('Number of Properties', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 6,
                'min' => 1,
                'max' => 20,
            )
        );

        $this->add_control(
            'slides_to_show',
            array(
                'label' => esc_html__('Slides to Show', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3,
                'min' => 1,
                'max' => 6,
            )
        );

        $this->add_control(
            'autoplay',
            array(
                'label' => esc_html__('Autoplay', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'autoplay_speed',
            array(
                'label' => esc_html__('Autoplay Speed (ms)', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3000,
                'min' => 1000,
                'max' => 10000,
                'step' => 500,
                'condition' => array(
                    'autoplay' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_arrows',
            array(
                'label' => esc_html__('Show Arrows', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_dots',
            array(
                'label' => esc_html__('Show Dots', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'infinite_loop',
            array(
                'label' => esc_html__('Infinite Loop', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->end_controls_section();

        // Query Filter
        $this->start_controls_section(
            'query_filter_section',
            array(
                'label' => esc_html__('Query Filter', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'orderby',
            array(
                'label' => esc_html__('Order By', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => array(
                    'date' => esc_html__('Date', 'realestate-booking-suite'),
                    'title' => esc_html__('Title', 'realestate-booking-suite'),
                    'price' => esc_html__('Price', 'realestate-booking-suite'),
                    'rand' => esc_html__('Random', 'realestate-booking-suite'),
                    'featured' => esc_html__('Featured', 'realestate-booking-suite'),
                ),
            )
        );

        $this->add_control(
            'order',
            array(
                'label' => esc_html__('Order', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => array(
                    'ASC' => esc_html__('Ascending', 'realestate-booking-suite'),
                    'DESC' => esc_html__('Descending', 'realestate-booking-suite'),
                ),
            )
        );

        $this->add_control(
            'property_type',
            array(
                'label' => esc_html__('Property Type', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_property_types(),
                'multiple' => true,
                'label_block' => true,
            )
        );

        $this->add_control(
            'property_status',
            array(
                'label' => esc_html__('Property Status', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_property_statuses(),
                'multiple' => true,
                'label_block' => true,
            )
        );

        $this->add_control(
            'featured_only',
            array(
                'label' => esc_html__('Featured Properties Only', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Get property types
     * 
     * SECURITY: Safe - Private method, only used for Elementor control options
     * Output is escaped with esc_html() before being used in select options
     */
    private function get_property_types() {
        $types = get_terms(array(
            'taxonomy' => 'property_type',
            'hide_empty' => false,
        ));
        
        $options = array();
        foreach ($types as $type) {
            $options[$type->slug] = esc_html($type->name);
        }
        
        return $options;
    }

    /**
     * Get property statuses
     * 
     * SECURITY: Safe - Private method, only used for Elementor control options
     * Output is escaped with esc_html() before being used in select options
     */
    private function get_property_statuses() {
        $statuses = get_terms(array(
            'taxonomy' => 'property_status',
            'hide_empty' => false,
        ));
        
        $options = array();
        foreach ($statuses as $status) {
            $options[$status->slug] = esc_html($status->name);
        }
        
        return $options;
    }

    /**
     * Render widget output
     * 
     * SECURITY NOTES:
     * - Scripts and nonces are enqueued by class-resbs-elementor.php
     * - Nonce 'resbs_elementor_nonce' is localized as resbs_elementor_ajax.nonce
     * - Favorite button functionality requires JavaScript from elementor.js
     * - All user input from settings is sanitized before use
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $title = sanitize_text_field($settings['title']);
        $layout = sanitize_text_field($settings['layout']);
        $columns = isset($settings['columns']) ? intval($settings['columns']) : 3;
        $grid_gap_size = isset($settings['grid_gap']['size']) ? floatval($settings['grid_gap']['size']) : 1.5;
        $grid_gap_unit = isset($settings['grid_gap']['unit']) && in_array($settings['grid_gap']['unit'], array('px', 'rem', 'em', '%'), true) ? sanitize_text_field($settings['grid_gap']['unit']) : 'rem';
        $grid_gap = $grid_gap_size . $grid_gap_unit;
        $posts_per_page = intval($settings['posts_per_page']);
        $slides_to_show = intval($settings['slides_to_show']);
        $autoplay = $settings['autoplay'] === 'yes';
        $autoplay_speed = intval($settings['autoplay_speed']);
        $show_arrows = $settings['show_arrows'] === 'yes';
        $show_dots = $settings['show_dots'] === 'yes';
        $infinite_loop = $settings['infinite_loop'] === 'yes';
        // Validate orderby and order
        $orderby_raw = sanitize_text_field($settings['orderby']);
        $allowed_orderby = array('date', 'title', 'price', 'rand', 'featured');
        if (!in_array($orderby_raw, $allowed_orderby, true)) {
            $orderby_raw = 'date';
        }
        $orderby = $orderby_raw;
        
        $order_raw = sanitize_text_field($settings['order']);
        $allowed_order = array('ASC', 'DESC');
        if (!in_array($order_raw, $allowed_order, true)) {
            $order_raw = 'DESC';
        }
        $order = $order_raw;
        
        // Validate and sanitize property types and statuses
        $property_types = array();
        if (isset($settings['property_type']) && is_array($settings['property_type'])) {
            foreach ($settings['property_type'] as $type) {
                $type_sanitized = sanitize_text_field($type);
                if (!empty($type_sanitized)) {
                    $property_types[] = $type_sanitized;
                }
            }
        }
        
        $property_statuses = array();
        if (isset($settings['property_status']) && is_array($settings['property_status'])) {
            foreach ($settings['property_status'] as $status) {
                $status_sanitized = sanitize_text_field($status);
                if (!empty($status_sanitized)) {
                    $property_statuses[] = $status_sanitized;
                }
            }
        }
        
        $featured_only = $settings['featured_only'] === 'yes';
        
        $widget_id = 'resbs-slider-' . absint($this->get_id());
        
        // Build query
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'orderby' => $orderby === 'price' ? 'meta_value_num' : ($orderby === 'featured' ? 'meta_value' : $orderby),
            'order' => $order,
        );
        
        if ($orderby === 'price') {
            $query_args['meta_key'] = '_property_price';
        } elseif ($orderby === 'featured') {
            $query_args['meta_key'] = '_property_featured';
            $query_args['meta_value'] = '1';
        }
        
        $meta_query = array();
        if ($featured_only) {
            $meta_query[] = array(
                'key' => '_property_featured',
                'value' => '1',
                'compare' => '='
            );
        }
        
        $tax_query = array();
        if (!empty($property_types)) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $property_types
            );
        }
        
        if (!empty($property_statuses)) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $property_statuses
            );
        }
        
        if (!empty($meta_query)) {
            $query_args['meta_query'] = $meta_query;
        }
        
        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }
        
        $properties = new WP_Query($query_args);
        
        ?>
        <div class="resbs-slider-widget resbs-layout-<?php echo esc_attr($layout); ?>" id="<?php echo esc_attr($widget_id); ?>"
             data-settings="<?php echo esc_attr(wp_json_encode(array(
                 'slides_to_show' => $slides_to_show,
                 'autoplay' => $autoplay,
                 'autoplay_speed' => $autoplay_speed,
                 'show_arrows' => $show_arrows,
                 'show_dots' => $show_dots,
                 'infinite_loop' => $infinite_loop,
                 'layout' => $layout,
                 'columns' => $columns
             ))); ?>">
            
            <?php if (!empty($title)): ?>
                <div class="resbs-slider-header">
                    <h3 class="resbs-slider-title"><?php echo esc_html($title); ?></h3>
                    <?php if ($layout !== 'grid' && $show_arrows): ?>
                        <div class="resbs-slider-nav">
                            <button type="button" class="resbs-slider-arrow resbs-slider-prev">
                                <span class="dashicons dashicons-arrow-left-alt2"></span>
                            </button>
                            <button type="button" class="resbs-slider-arrow resbs-slider-next">
                                <span class="dashicons dashicons-arrow-right-alt2"></span>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($layout === 'grid'): ?>
                <div class="resbs-slider-grid" style="grid-template-columns: repeat(<?php echo esc_attr($columns); ?>, 1fr); gap: <?php echo esc_attr($grid_gap); ?>;">
                    <?php if ($properties->have_posts()): ?>
                        <?php while ($properties->have_posts()): $properties->the_post(); ?>
                            <?php $this->render_property_card_grid(); ?>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    <?php else: ?>
                        <p class="resbs-no-properties"><?php esc_html_e('No properties found.', 'realestate-booking-suite'); ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="resbs-slider-container">
                    <?php if ($show_arrows && empty($title)): ?>
                        <button type="button" class="resbs-slider-arrow resbs-slider-prev">
                            <span class="dashicons dashicons-arrow-left-alt2"></span>
                        </button>
                        <button type="button" class="resbs-slider-arrow resbs-slider-next">
                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                        </button>
                    <?php endif; ?>
                    
                    <div class="resbs-slider-track">
                        <?php if ($properties->have_posts()): ?>
                            <?php while ($properties->have_posts()): $properties->the_post(); ?>
                                <div class="resbs-slider-slide">
                                    <?php $this->render_property_card(); ?>
                                </div>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        <?php else: ?>
                            <p class="resbs-no-properties"><?php esc_html_e('No properties found.', 'realestate-booking-suite'); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($show_dots): ?>
                        <div class="resbs-slider-dots"></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <?php if ($layout === 'grid'): ?>
        <?php
        // Enqueue base CSS
        if (!wp_style_is('resbs-elementor-slider-grid', 'enqueued')) {
            wp_enqueue_style(
                'resbs-elementor-slider-grid',
                RESBS_URL . 'assets/css/elementor-slider-grid.css',
                array(),
                '1.0.0'
            );
        }
        
        // Add widget-specific dynamic styles via wp_add_inline_style
        // Escape CSS values for safety
        $widget_id_css = esc_attr($widget_id);
        $columns_css = absint($columns);
        $grid_gap_css = esc_attr($grid_gap);
        
        $dynamic_css = "
        #{$widget_id_css} .resbs-slider-grid {
            grid-template-columns: repeat({$columns_css}, 1fr) !important;
            gap: {$grid_gap_css} !important;
        }
        ";
        
        wp_add_inline_style('resbs-elementor-slider-grid', $dynamic_css);
        ?>
        <?php endif; ?>
        <?php
    }

    /**
     * Render property card for grid layout
     */
    private function render_property_card_grid() {
        $property_id = get_the_ID();
        $price = get_post_meta($property_id, '_property_price', true);
        $bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        // Get area using helper function that handles unit conversion
        $area_value = resbs_get_property_area($property_id, '_property_area_sqft');
        $city = get_post_meta($property_id, '_property_city', true);
        $city = $city ? sanitize_text_field($city) : '';
        $state = get_post_meta($property_id, '_property_state', true);
        $state = $state ? sanitize_text_field($state) : '';
        $property_status_meta = get_post_meta($property_id, '_property_status', true);
        $property_status_meta = $property_status_meta ? sanitize_text_field($property_status_meta) : '';
        $featured_image = get_the_post_thumbnail_url($property_id, 'medium');
        
        // Format price with currency settings
        if ($price && is_numeric($price)) {
            $currency_symbol = sanitize_text_field(get_option('resbs_currency_symbol', '$'));
            $currency_position = sanitize_text_field(get_option('resbs_currency_position', 'before'));
            $formatted_price_num = number_format(floatval($price), 2);
            $currency_symbol_escaped = esc_html($currency_symbol);
            
            if ($currency_position === 'before') {
                $formatted_price = $currency_symbol_escaped . $formatted_price_num;
            } else {
                $formatted_price = $formatted_price_num . $currency_symbol_escaped;
            }
        } else {
            $formatted_price = esc_html__('Price on request', 'realestate-booking-suite');
        }
        $location = trim($city . ', ' . $state, ', ');
        ?>
        
        <div class="property-card">
            <div class="property-image">
                <?php if ($featured_image): ?>
                    <a href="<?php echo esc_url(get_permalink($property_id)); ?>">
                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                    </a>
                <?php else: ?>
                    <div class="bg-gray-200 h-48 flex items-center justify-center">
                        <i class="fas fa-home text-gray-400 text-4xl"></i>
                    </div>
                <?php endif; ?>
                
                <?php if ($property_status_meta): ?>
                    <span class="property-badge"><?php echo esc_html($property_status_meta); ?></span>
                <?php endif; ?>
            </div>
            <div class="property-info">
                <h4 class="property-card-title">
                    <a href="<?php echo esc_url(get_permalink($property_id)); ?>" class="hover:text-emerald-600 transition-colors">
                        <?php echo esc_html(get_the_title()); ?>
                    </a>
                </h4>
                <?php if ($location): ?>
                    <p class="property-card-location">
                        <i class="fas fa-map-marker text-emerald-500"></i>
                        <?php echo esc_html($location); ?>
                    </p>
                <?php endif; ?>
                <div class="property-card-price"><?php echo esc_html($formatted_price); ?></div>
                <div class="property-card-features">
                    <?php if ($bedrooms): ?>
                        <span><i class="fas fa-bed mr-1"></i><?php echo esc_html($bedrooms); ?> Bed<?php echo esc_html($bedrooms != 1 ? 's' : ''); ?></span>
                    <?php endif; ?>
                    <?php if ($bathrooms): ?>
                        <span><i class="fas fa-bath mr-1"></i><?php echo esc_html($bathrooms); ?> Bath<?php echo esc_html($bathrooms != 1 ? 's' : ''); ?></span>
                    <?php endif; ?>
                    <?php if (!empty($area_value)): ?>
                        <span><i class="fas fa-ruler-combined mr-1"></i><?php echo esc_html(resbs_format_area($area_value)); ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render property card
     */
    private function render_property_card() {
        $property_id = get_the_ID();
        $price = get_post_meta($property_id, '_property_price', true);
        $bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        // Get area using helper function that handles unit conversion
        $area_value = resbs_get_property_area($property_id, '_property_area_sqft');
        // Get taxonomy terms and sanitize
        $location_raw = get_the_terms($property_id, 'property_location');
        $property_type_raw = get_the_terms($property_id, 'property_type');
        $property_status_raw = get_the_terms($property_id, 'property_status');
        
        // Validate and sanitize taxonomy terms
        $location = null;
        if (!is_wp_error($location_raw) && is_array($location_raw) && !empty($location_raw)) {
            $location = $location_raw[0];
        }
        
        $property_type = null;
        if (!is_wp_error($property_type_raw) && is_array($property_type_raw) && !empty($property_type_raw)) {
            $property_type = $property_type_raw[0];
        }
        
        $property_status = null;
        if (!is_wp_error($property_status_raw) && is_array($property_status_raw) && !empty($property_status_raw)) {
            $property_status = $property_status_raw[0];
        }
        
        ?>
        <div class="resbs-property-card">
            <div class="resbs-property-image">
                <?php if (has_post_thumbnail()): ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <?php the_post_thumbnail('medium_large'); ?>
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <div class="resbs-placeholder-image"></div>
                    </a>
                <?php endif; ?>
                
                <?php
                /**
                 * Favorite Button - Security Requirements:
                 * - Nonce: Uses 'resbs_elementor_nonce' (localized as resbs_elementor_ajax.nonce in JavaScript)
                 * - AJAX Handler: 'resbs_toggle_favorite' (handled in class-resbs-favorites.php or class-resbs-widgets.php)
                 * - Permission: Requires user to be logged in (checked server-side in AJAX handler)
                 * - JavaScript: Handled by elementor.js -> toggleFavorite() function
                 * - Rate Limiting: Applied in AJAX handler via RESBS_Security::check_rate_limit()
                 */
                ?>
                <button type="button" class="resbs-favorite-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
                    <span class="dashicons dashicons-heart"></span>
                </button>
                
                <?php do_action('resbs_property_badges', $property_id, 'widget'); ?>
            </div>
            
            <div class="resbs-property-content">
                <?php if ($location && isset($location->name)): ?>
                    <div class="resbs-property-location"><?php echo esc_html($location->name); ?></div>
                <?php endif; ?>
                
                <h3 class="resbs-property-title">
                    <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                </h3>
                
                <?php if ($price && is_numeric($price)): ?>
                    <?php 
                    // Format price with currency settings
                    $currency_symbol = sanitize_text_field(get_option('resbs_currency_symbol', '$'));
                    $currency_position = sanitize_text_field(get_option('resbs_currency_position', 'before'));
                    $formatted_price_num = number_format(floatval($price), 2);
                    $currency_symbol_escaped = esc_html($currency_symbol);
                    
                    if ($currency_position === 'before') {
                        $formatted_price_display = $currency_symbol_escaped . $formatted_price_num;
                    } else {
                        $formatted_price_display = $formatted_price_num . $currency_symbol_escaped;
                    }
                    ?>
                    <div class="resbs-property-price"><?php echo esc_html($formatted_price_display); ?></div>
                <?php endif; ?>
                
                <div class="resbs-property-meta">
                    <?php if (!empty($bedrooms)): ?>
                        <span class="resbs-meta-item">
                            <span class="dashicons dashicons-bed-alt"></span>
                            <?php echo esc_html($bedrooms); ?> <?php esc_html_e('beds', 'realestate-booking-suite'); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($bathrooms)): ?>
                        <span class="resbs-meta-item">
                            <span class="dashicons dashicons-bath"></span>
                            <?php echo esc_html($bathrooms); ?> <?php esc_html_e('baths', 'realestate-booking-suite'); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($area_value)): ?>
                        <span class="resbs-meta-item">
                            <span class="dashicons dashicons-admin-home"></span>
                            <?php echo esc_html(resbs_format_area($area_value)); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if ($property_type && isset($property_type->name)): ?>
                    <div class="resbs-property-type">
                        <?php echo esc_html($property_type->name); ?>
                        <?php if ($property_status && isset($property_status->name)): ?>
                            • <?php echo esc_html($property_status->name); ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <div class="resbs-slider-widget">
            <h3 class="resbs-slider-title">{{ settings.title }}</h3>
            <div class="resbs-slider-container">
                <div class="resbs-slider-track">
                    <div class="resbs-slider-slide">
                        <div class="resbs-property-card">
                            <div class="resbs-property-image">
                                <div class="resbs-placeholder-image"></div>
                            </div>
                            <div class="resbs-property-content">
                                <div class="resbs-property-title"><?php esc_html_e('Sample Property', 'realestate-booking-suite'); ?></div>
                                <div class="resbs-property-price">$500,000</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

