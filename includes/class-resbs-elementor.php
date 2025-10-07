<?php
/**
 * Elementor Widgets Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Check if Elementor is active
if (!did_action('elementor/loaded')) {
    return;
}

class RESBS_Elementor_Widgets {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'add_elementor_widget_categories'));
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_elementor_styles'));
        add_action('elementor/frontend/after_enqueue_scripts', array($this, 'enqueue_elementor_scripts'));
    }

    /**
     * Register Elementor widgets
     */
    public function register_widgets() {
        require_once RESBS_PATH . 'includes/elementor/class-resbs-property-grid-widget.php';
        require_once RESBS_PATH . 'includes/elementor/class-resbs-property-carousel-widget.php';
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new RESBS_Property_Grid_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new RESBS_Property_Carousel_Widget());
    }

    /**
     * Add Elementor widget categories
     */
    public function add_elementor_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'resbs-widgets',
            array(
                'title' => esc_html__('RealEstate Booking Suite', 'realestate-booking-suite'),
                'icon' => 'fa fa-home',
            )
        );
    }

    /**
     * Enqueue Elementor styles
     */
    public function enqueue_elementor_styles() {
        wp_enqueue_style(
            'resbs-elementor',
            RESBS_URL . 'assets/css/elementor.css',
            array(),
            '1.0.0'
        );
    }

    /**
     * Enqueue Elementor scripts
     */
    public function enqueue_elementor_scripts() {
        wp_enqueue_script(
            'resbs-elementor',
            RESBS_URL . 'assets/js/elementor.js',
            array('jquery', 'elementor-frontend'),
            '1.0.0',
            true
        );

        wp_localize_script('resbs-elementor', 'resbs_elementor_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('resbs_elementor_nonce'),
            'messages' => array(
                'loading' => esc_html__('Loading...', 'realestate-booking-suite'),
                'no_properties' => esc_html__('No properties found.', 'realestate-booking-suite'),
                'load_more' => esc_html__('Load More', 'realestate-booking-suite'),
                'error' => esc_html__('An error occurred. Please try again.', 'realestate-booking-suite')
            )
        ));
    }
}

// Initialize Elementor widgets
new RESBS_Elementor_Widgets();

/**
 * Property Grid Elementor Widget
 */
class RESBS_Property_Grid_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'resbs-property-grid';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return esc_html__('Property Grid', 'realestate-booking-suite');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-posts-grid';
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
        return array('property', 'grid', 'real estate', 'booking', 'listings');
    }

    /**
     * Register widget controls
     */
    protected function _register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => esc_html__('Content', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'posts_per_page',
            array(
                'label' => esc_html__('Properties Per Page', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 50,
            )
        );

        $this->add_control(
            'columns',
            array(
                'label' => esc_html__('Columns', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => array(
                    '2' => esc_html__('2 Columns', 'realestate-booking-suite'),
                    '3' => esc_html__('3 Columns', 'realestate-booking-suite'),
                    '4' => esc_html__('4 Columns', 'realestate-booking-suite'),
                ),
            )
        );

        $this->add_control(
            'show_filters',
            array(
                'label' => esc_html__('Show Filters', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_pagination',
            array(
                'label' => esc_html__('Show Pagination', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_infinite_scroll',
            array(
                'label' => esc_html__('Infinite Scroll', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
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

        $this->end_controls_section();

        // Filter Section
        $this->start_controls_section(
            'filter_section',
            array(
                'label' => esc_html__('Filters', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => array(
                    'show_filters' => 'yes',
                ),
            )
        );

        $this->add_control(
            'property_type_filter',
            array(
                'label' => esc_html__('Property Type Filter', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'property_status_filter',
            array(
                'label' => esc_html__('Property Status Filter', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'price_filter',
            array(
                'label' => esc_html__('Price Range Filter', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'bedrooms_filter',
            array(
                'label' => esc_html__('Bedrooms Filter', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'bathrooms_filter',
            array(
                'label' => esc_html__('Bathrooms Filter', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'location_filter',
            array(
                'label' => esc_html__('Location Filter', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->end_controls_section();

        // Display Section
        $this->start_controls_section(
            'display_section',
            array(
                'label' => esc_html__('Display Options', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_price',
            array(
                'label' => esc_html__('Show Price', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_meta',
            array(
                'label' => esc_html__('Show Meta Info', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_excerpt',
            array(
                'label' => esc_html__('Show Excerpt', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->add_control(
            'show_badges',
            array(
                'label' => esc_html__('Show Status Badges', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_favorite_button',
            array(
                'label' => esc_html__('Show Favorite Button', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_book_button',
            array(
                'label' => esc_html__('Show Book Button', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->end_controls_section();

        // Style Section - Grid
        $this->start_controls_section(
            'grid_style_section',
            array(
                'label' => esc_html__('Grid Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'grid_gap',
            array(
                'label' => esc_html__('Grid Gap', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => 20,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'card_border',
                'label' => esc_html__('Card Border', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-card',
            )
        );

        $this->add_control(
            'card_border_radius',
            array(
                'label' => esc_html__('Card Border Radius', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'card_shadow',
                'label' => esc_html__('Card Shadow', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-card',
            )
        );

        $this->end_controls_section();

        // Style Section - Typography
        $this->start_controls_section(
            'typography_section',
            array(
                'label' => esc_html__('Typography', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'title_typography',
                'label' => esc_html__('Title Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-title',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label' => esc_html__('Title Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'price_typography',
                'label' => esc_html__('Price Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-price',
            )
        );

        $this->add_control(
            'price_color',
            array(
                'label' => esc_html__('Price Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-price' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'meta_typography',
                'label' => esc_html__('Meta Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-meta',
            )
        );

        $this->add_control(
            'meta_color',
            array(
                'label' => esc_html__('Meta Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-meta' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // Style Section - Buttons
        $this->start_controls_section(
            'button_style_section',
            array(
                'label' => esc_html__('Buttons', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'button_typography',
                'label' => esc_html__('Button Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-actions .resbs-btn',
            )
        );

        $this->add_control(
            'button_bg_color',
            array(
                'label' => esc_html__('Button Background Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-actions .resbs-btn' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_text_color',
            array(
                'label' => esc_html__('Button Text Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-actions .resbs-btn' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_hover_bg_color',
            array(
                'label' => esc_html__('Button Hover Background', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-actions .resbs-btn:hover' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_border_radius',
            array(
                'label' => esc_html__('Button Border Radius', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-actions .resbs-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Style Section - Filters
        $this->start_controls_section(
            'filter_style_section',
            array(
                'label' => esc_html__('Filters', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_filters' => 'yes',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'filter_typography',
                'label' => esc_html__('Filter Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-filters',
            )
        );

        $this->add_control(
            'filter_bg_color',
            array(
                'label' => esc_html__('Filter Background Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-filters' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'filter_text_color',
            array(
                'label' => esc_html__('Filter Text Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-filters' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Sanitize settings
        $posts_per_page = intval($settings['posts_per_page']);
        $columns = sanitize_text_field($settings['columns']);
        $show_filters = $settings['show_filters'] === 'yes';
        $show_pagination = $settings['show_pagination'] === 'yes';
        $show_infinite_scroll = $settings['show_infinite_scroll'] === 'yes';
        $orderby = sanitize_text_field($settings['orderby']);
        $order = sanitize_text_field($settings['order']);
        
        // Display options
        $show_price = $settings['show_price'] === 'yes';
        $show_meta = $settings['show_meta'] === 'yes';
        $show_excerpt = $settings['show_excerpt'] === 'yes';
        $show_badges = $settings['show_badges'] === 'yes';
        $show_favorite_button = $settings['show_favorite_button'] === 'yes';
        $show_book_button = $settings['show_book_button'] === 'yes';
        
        // Filter options
        $property_type_filter = $settings['property_type_filter'] === 'yes';
        $property_status_filter = $settings['property_status_filter'] === 'yes';
        $price_filter = $settings['price_filter'] === 'yes';
        $bedrooms_filter = $settings['bedrooms_filter'] === 'yes';
        $bathrooms_filter = $settings['bathrooms_filter'] === 'yes';
        $location_filter = $settings['location_filter'] === 'yes';
        
        // Generate unique ID for this widget instance
        $widget_id = 'resbs-property-grid-' . $this->get_id();
        
        ?>
        <div class="resbs-elementor-property-grid" id="<?php echo esc_attr($widget_id); ?>" 
             data-settings="<?php echo esc_attr(wp_json_encode(array(
                 'posts_per_page' => $posts_per_page,
                 'columns' => $columns,
                 'orderby' => $orderby,
                 'order' => $order,
                 'show_filters' => $show_filters,
                 'show_pagination' => $show_pagination,
                 'show_infinite_scroll' => $show_infinite_scroll,
                 'show_price' => $show_price,
                 'show_meta' => $show_meta,
                 'show_excerpt' => $show_excerpt,
                 'show_badges' => $show_badges,
                 'show_favorite_button' => $show_favorite_button,
                 'show_book_button' => $show_book_button,
                 'property_type_filter' => $property_type_filter,
                 'property_status_filter' => $property_status_filter,
                 'price_filter' => $price_filter,
                 'bedrooms_filter' => $bedrooms_filter,
                 'bathrooms_filter' => $bathrooms_filter,
                 'location_filter' => $location_filter
             ))); ?>">
            
            <?php if ($show_filters): ?>
                <div class="resbs-filters">
                    <form class="resbs-filter-form" data-target="<?php echo esc_attr($widget_id); ?>">
                        <?php wp_nonce_field('resbs_elementor_filter', 'resbs_filter_nonce'); ?>
                        
                        <div class="resbs-filter-row">
                            <?php if ($property_type_filter): ?>
                                <div class="resbs-filter-group">
                                    <label for="property_type"><?php esc_html_e('Property Type', 'realestate-booking-suite'); ?></label>
                                    <select name="property_type" id="property_type">
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
                            
                            <?php if ($property_status_filter): ?>
                                <div class="resbs-filter-group">
                                    <label for="property_status"><?php esc_html_e('Status', 'realestate-booking-suite'); ?></label>
                                    <select name="property_status" id="property_status">
                                        <option value=""><?php esc_html_e('All Status', 'realestate-booking-suite'); ?></option>
                                        <?php
                                        $property_statuses = get_terms(array(
                                            'taxonomy' => 'property_status',
                                            'hide_empty' => false,
                                        ));
                                        foreach ($property_statuses as $status) {
                                            echo '<option value="' . esc_attr($status->slug) . '">' . esc_html($status->name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($price_filter): ?>
                                <div class="resbs-filter-group">
                                    <label for="price_min"><?php esc_html_e('Min Price', 'realestate-booking-suite'); ?></label>
                                    <input type="number" name="price_min" id="price_min" placeholder="<?php esc_attr_e('Min Price', 'realestate-booking-suite'); ?>">
                                </div>
                                <div class="resbs-filter-group">
                                    <label for="price_max"><?php esc_html_e('Max Price', 'realestate-booking-suite'); ?></label>
                                    <input type="number" name="price_max" id="price_max" placeholder="<?php esc_attr_e('Max Price', 'realestate-booking-suite'); ?>">
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($bedrooms_filter): ?>
                                <div class="resbs-filter-group">
                                    <label for="bedrooms"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label>
                                    <select name="bedrooms" id="bedrooms">
                                        <option value=""><?php esc_html_e('Any', 'realestate-booking-suite'); ?></option>
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?>+</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($bathrooms_filter): ?>
                                <div class="resbs-filter-group">
                                    <label for="bathrooms"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                                    <select name="bathrooms" id="bathrooms">
                                        <option value=""><?php esc_html_e('Any', 'realestate-booking-suite'); ?></option>
                                        <?php for ($i = 1; $i <= 10; $i++): ?>
                                            <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?>+</option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($location_filter): ?>
                                <div class="resbs-filter-group">
                                    <label for="location"><?php esc_html_e('Location', 'realestate-booking-suite'); ?></label>
                                    <select name="location" id="location">
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
            
            <div class="resbs-property-grid resbs-grid-<?php echo esc_attr($columns); ?>-cols" 
                 data-columns="<?php echo esc_attr($columns); ?>">
                <!-- Properties will be loaded here via AJAX -->
            </div>
            
            <?php if ($show_pagination && !$show_infinite_scroll): ?>
                <div class="resbs-pagination-wrapper">
                    <!-- Pagination will be loaded here -->
                </div>
            <?php endif; ?>
            
            <?php if ($show_infinite_scroll): ?>
                <div class="resbs-infinite-scroll-wrapper">
                    <button type="button" class="resbs-load-more-btn">
                        <?php esc_html_e('Load More Properties', 'realestate-booking-suite'); ?>
                    </button>
                </div>
            <?php endif; ?>
            
            <div class="resbs-loading" style="display: none;">
                <div class="resbs-spinner"></div>
                <p><?php esc_html_e('Loading properties...', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-no-properties" style="display: none;">
                <p><?php esc_html_e('No properties found matching your criteria.', 'realestate-booking-suite'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor
     */
    protected function _content_template() {
        ?>
        <div class="resbs-elementor-property-grid">
            <div class="resbs-filters">
                <div class="resbs-filter-row">
                    <div class="resbs-filter-group">
                        <label><?php esc_html_e('Property Type', 'realestate-booking-suite'); ?></label>
                        <select>
                            <option><?php esc_html_e('All Types', 'realestate-booking-suite'); ?></option>
                        </select>
                    </div>
                    <div class="resbs-filter-group">
                        <label><?php esc_html_e('Status', 'realestate-booking-suite'); ?></label>
                        <select>
                            <option><?php esc_html_e('All Status', 'realestate-booking-suite'); ?></option>
                        </select>
                    </div>
                    <div class="resbs-filter-actions">
                        <button type="button" class="resbs-filter-btn">
                            <?php esc_html_e('Filter', 'realestate-booking-suite'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="resbs-property-grid resbs-grid-3-cols">
                <div class="resbs-property-card">
                    <div class="resbs-property-image">
                        <img src="<?php echo esc_url(RESBS_URL . 'assets/images/placeholder.jpg'); ?>" alt="Property">
                    </div>
                    <div class="resbs-property-content">
                        <h3 class="resbs-property-title"><?php esc_html_e('Sample Property', 'realestate-booking-suite'); ?></h3>
                        <div class="resbs-property-price">$500,000</div>
                        <div class="resbs-property-meta">
                            <span><?php esc_html_e('3 Bedrooms', 'realestate-booking-suite'); ?></span>
                            <span><?php esc_html_e('2 Bathrooms', 'realestate-booking-suite'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-property-card">
                    <div class="resbs-property-image">
                        <img src="<?php echo esc_url(RESBS_URL . 'assets/images/placeholder.jpg'); ?>" alt="Property">
                    </div>
                    <div class="resbs-property-content">
                        <h3 class="resbs-property-title"><?php esc_html_e('Sample Property', 'realestate-booking-suite'); ?></h3>
                        <div class="resbs-property-price">$750,000</div>
                        <div class="resbs-property-meta">
                            <span><?php esc_html_e('4 Bedrooms', 'realestate-booking-suite'); ?></span>
                            <span><?php esc_html_e('3 Bathrooms', 'realestate-booking-suite'); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-property-card">
                    <div class="resbs-property-image">
                        <img src="<?php echo esc_url(RESBS_URL . 'assets/images/placeholder.jpg'); ?>" alt="Property">
                    </div>
                    <div class="resbs-property-content">
                        <h3 class="resbs-property-title"><?php esc_html_e('Sample Property', 'realestate-booking-suite'); ?></h3>
                        <div class="resbs-property-price">$350,000</div>
                        <div class="resbs-property-meta">
                            <span><?php esc_html_e('2 Bedrooms', 'realestate-booking-suite'); ?></span>
                            <span><?php esc_html_e('1 Bathroom', 'realestate-booking-suite'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

/**
 * Property Carousel Elementor Widget
 */
class RESBS_Property_Carousel_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'resbs-property-carousel';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return esc_html__('Property Carousel', 'realestate-booking-suite');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-posts-carousel';
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
        return array('property', 'carousel', 'slider', 'real estate', 'featured', 'listings');
    }

    /**
     * Register widget controls
     */
    protected function _register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => esc_html__('Content', 'realestate-booking-suite'),
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
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => array(
                    '1' => esc_html__('1 Slide', 'realestate-booking-suite'),
                    '2' => esc_html__('2 Slides', 'realestate-booking-suite'),
                    '3' => esc_html__('3 Slides', 'realestate-booking-suite'),
                    '4' => esc_html__('4 Slides', 'realestate-booking-suite'),
                ),
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
                'label' => esc_html__('Show Navigation Arrows', 'realestate-booking-suite'),
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

        $this->end_controls_section();

        // Filter Section
        $this->start_controls_section(
            'filter_section',
            array(
                'label' => esc_html__('Filters', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
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
            'price_min',
            array(
                'label' => esc_html__('Minimum Price', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'step' => 1000,
            )
        );

        $this->add_control(
            'price_max',
            array(
                'label' => esc_html__('Maximum Price', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'step' => 1000,
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

        // Display Section
        $this->start_controls_section(
            'display_section',
            array(
                'label' => esc_html__('Display Options', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_price',
            array(
                'label' => esc_html__('Show Price', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_meta',
            array(
                'label' => esc_html__('Show Meta Info', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_excerpt',
            array(
                'label' => esc_html__('Show Excerpt', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->add_control(
            'show_badges',
            array(
                'label' => esc_html__('Show Status Badges', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_favorite_button',
            array(
                'label' => esc_html__('Show Favorite Button', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_book_button',
            array(
                'label' => esc_html__('Show Book Button', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->end_controls_section();

        // Style Section - Carousel
        $this->start_controls_section(
            'carousel_style_section',
            array(
                'label' => esc_html__('Carousel Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'slide_spacing',
            array(
                'label' => esc_html__('Slide Spacing', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => 20,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .resbs-carousel-slide' => 'padding: 0 {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'card_border',
                'label' => esc_html__('Card Border', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-card',
            )
        );

        $this->add_control(
            'card_border_radius',
            array(
                'label' => esc_html__('Card Border Radius', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'card_shadow',
                'label' => esc_html__('Card Shadow', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-card',
            )
        );

        $this->end_controls_section();

        // Style Section - Navigation
        $this->start_controls_section(
            'navigation_style_section',
            array(
                'label' => esc_html__('Navigation', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'arrow_size',
            array(
                'label' => esc_html__('Arrow Size', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 20,
                        'max' => 60,
                        'step' => 1,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => 40,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .resbs-carousel-arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
                'condition' => array(
                    'show_arrows' => 'yes',
                ),
            )
        );

        $this->add_control(
            'arrow_color',
            array(
                'label' => esc_html__('Arrow Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-carousel-arrow' => 'color: {{VALUE}};',
                ),
                'condition' => array(
                    'show_arrows' => 'yes',
                ),
            )
        );

        $this->add_control(
            'arrow_bg_color',
            array(
                'label' => esc_html__('Arrow Background', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-carousel-arrow' => 'background-color: {{VALUE}};',
                ),
                'condition' => array(
                    'show_arrows' => 'yes',
                ),
            )
        );

        $this->add_control(
            'dot_size',
            array(
                'label' => esc_html__('Dot Size', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 8,
                        'max' => 20,
                        'step' => 1,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => 12,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .resbs-carousel-dot' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ),
                'condition' => array(
                    'show_dots' => 'yes',
                ),
            )
        );

        $this->add_control(
            'dot_color',
            array(
                'label' => esc_html__('Dot Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-carousel-dot' => 'background-color: {{VALUE}};',
                ),
                'condition' => array(
                    'show_dots' => 'yes',
                ),
            )
        );

        $this->add_control(
            'dot_active_color',
            array(
                'label' => esc_html__('Active Dot Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-carousel-dot.active' => 'background-color: {{VALUE}};',
                ),
                'condition' => array(
                    'show_dots' => 'yes',
                ),
            )
        );

        $this->end_controls_section();

        // Style Section - Typography
        $this->start_controls_section(
            'typography_section',
            array(
                'label' => esc_html__('Typography', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'title_typography',
                'label' => esc_html__('Title Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-title',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label' => esc_html__('Title Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'price_typography',
                'label' => esc_html__('Price Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-price',
            )
        );

        $this->add_control(
            'price_color',
            array(
                'label' => esc_html__('Price Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-price' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'meta_typography',
                'label' => esc_html__('Meta Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-meta',
            )
        );

        $this->add_control(
            'meta_color',
            array(
                'label' => esc_html__('Meta Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-meta' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();

        // Style Section - Buttons
        $this->start_controls_section(
            'button_style_section',
            array(
                'label' => esc_html__('Buttons', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'button_typography',
                'label' => esc_html__('Button Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-actions .resbs-btn',
            )
        );

        $this->add_control(
            'button_bg_color',
            array(
                'label' => esc_html__('Button Background Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-actions .resbs-btn' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_text_color',
            array(
                'label' => esc_html__('Button Text Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-actions .resbs-btn' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_hover_bg_color',
            array(
                'label' => esc_html__('Button Hover Background', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-actions .resbs-btn:hover' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_border_radius',
            array(
                'label' => esc_html__('Button Border Radius', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-actions .resbs-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Get property types for filter
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
     * Get property statuses for filter
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
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Sanitize settings
        $posts_per_page = intval($settings['posts_per_page']);
        $slides_to_show = sanitize_text_field($settings['slides_to_show']);
        $autoplay = $settings['autoplay'] === 'yes';
        $autoplay_speed = intval($settings['autoplay_speed']);
        $show_arrows = $settings['show_arrows'] === 'yes';
        $show_dots = $settings['show_dots'] === 'yes';
        $infinite_loop = $settings['infinite_loop'] === 'yes';
        $orderby = sanitize_text_field($settings['orderby']);
        $order = sanitize_text_field($settings['order']);
        
        // Display options
        $show_price = $settings['show_price'] === 'yes';
        $show_meta = $settings['show_meta'] === 'yes';
        $show_excerpt = $settings['show_excerpt'] === 'yes';
        $show_badges = $settings['show_badges'] === 'yes';
        $show_favorite_button = $settings['show_favorite_button'] === 'yes';
        $show_book_button = $settings['show_book_button'] === 'yes';
        
        // Filter options
        $property_types = $settings['property_type'] ?? array();
        $property_statuses = $settings['property_status'] ?? array();
        $price_min = intval($settings['price_min']);
        $price_max = intval($settings['price_max']);
        $featured_only = $settings['featured_only'] === 'yes';
        
        // Generate unique ID for this widget instance
        $widget_id = 'resbs-property-carousel-' . $this->get_id();
        
        ?>
        <div class="resbs-elementor-property-carousel" id="<?php echo esc_attr($widget_id); ?>" 
             data-settings="<?php echo esc_attr(wp_json_encode(array(
                 'posts_per_page' => $posts_per_page,
                 'slides_to_show' => $slides_to_show,
                 'autoplay' => $autoplay,
                 'autoplay_speed' => $autoplay_speed,
                 'show_arrows' => $show_arrows,
                 'show_dots' => $show_dots,
                 'infinite_loop' => $infinite_loop,
                 'orderby' => $orderby,
                 'order' => $order,
                 'show_price' => $show_price,
                 'show_meta' => $show_meta,
                 'show_excerpt' => $show_excerpt,
                 'show_badges' => $show_badges,
                 'show_favorite_button' => $show_favorite_button,
                 'show_book_button' => $show_book_button,
                 'property_types' => $property_types,
                 'property_statuses' => $property_statuses,
                 'price_min' => $price_min,
                 'price_max' => $price_max,
                 'featured_only' => $featured_only
             ))); ?>">
            
            <div class="resbs-carousel-container">
                <?php if ($show_arrows): ?>
                    <button type="button" class="resbs-carousel-arrow resbs-carousel-prev">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                    </button>
                    <button type="button" class="resbs-carousel-arrow resbs-carousel-next">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                <?php endif; ?>
                
                <div class="resbs-carousel-track" data-slides-to-show="<?php echo esc_attr($slides_to_show); ?>">
                    <!-- Properties will be loaded here via AJAX -->
                </div>
                
                <?php if ($show_dots): ?>
                    <div class="resbs-carousel-dots">
                        <!-- Dots will be generated by JavaScript -->
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="resbs-loading" style="display: none;">
                <div class="resbs-spinner"></div>
                <p><?php esc_html_e('Loading properties...', 'realestate-booking-suite'); ?></p>
            </div>
            
            <div class="resbs-no-properties" style="display: none;">
                <p><?php esc_html_e('No properties found.', 'realestate-booking-suite'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor
     */
    protected function _content_template() {
        ?>
        <div class="resbs-elementor-property-carousel">
            <div class="resbs-carousel-container">
                <button type="button" class="resbs-carousel-arrow resbs-carousel-prev">
                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                </button>
                <button type="button" class="resbs-carousel-arrow resbs-carousel-next">
                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                </button>
                
                <div class="resbs-carousel-track" data-slides-to-show="3">
                    <div class="resbs-carousel-slide">
                        <div class="resbs-property-card">
                            <div class="resbs-property-image">
                                <img src="<?php echo esc_url(RESBS_URL . 'assets/images/placeholder.jpg'); ?>" alt="Property">
                            </div>
                            <div class="resbs-property-content">
                                <h3 class="resbs-property-title"><?php esc_html_e('Sample Property', 'realestate-booking-suite'); ?></h3>
                                <div class="resbs-property-price">$500,000</div>
                                <div class="resbs-property-meta">
                                    <span><?php esc_html_e('3 Bedrooms', 'realestate-booking-suite'); ?></span>
                                    <span><?php esc_html_e('2 Bathrooms', 'realestate-booking-suite'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resbs-carousel-slide">
                        <div class="resbs-property-card">
                            <div class="resbs-property-image">
                                <img src="<?php echo esc_url(RESBS_URL . 'assets/images/placeholder.jpg'); ?>" alt="Property">
                            </div>
                            <div class="resbs-property-content">
                                <h3 class="resbs-property-title"><?php esc_html_e('Sample Property', 'realestate-booking-suite'); ?></h3>
                                <div class="resbs-property-price">$750,000</div>
                                <div class="resbs-property-meta">
                                    <span><?php esc_html_e('4 Bedrooms', 'realestate-booking-suite'); ?></span>
                                    <span><?php esc_html_e('3 Bathrooms', 'realestate-booking-suite'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="resbs-carousel-slide">
                        <div class="resbs-property-card">
                            <div class="resbs-property-image">
                                <img src="<?php echo esc_url(RESBS_URL . 'assets/images/placeholder.jpg'); ?>" alt="Property">
                            </div>
                            <div class="resbs-property-content">
                                <h3 class="resbs-property-title"><?php esc_html_e('Sample Property', 'realestate-booking-suite'); ?></h3>
                                <div class="resbs-property-price">$350,000</div>
                                <div class="resbs-property-meta">
                                    <span><?php esc_html_e('2 Bedrooms', 'realestate-booking-suite'); ?></span>
                                    <span><?php esc_html_e('1 Bathroom', 'realestate-booking-suite'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="resbs-carousel-dots">
                    <span class="resbs-carousel-dot active"></span>
                    <span class="resbs-carousel-dot"></span>
                    <span class="resbs-carousel-dot"></span>
                </div>
            </div>
        </div>
        <?php
    }
}
