<?php
/**
 * Elementor Property Carousel Widget
 * 
 * SECURITY NOTES:
 * ===============
 * 
 * 1. DIRECT ACCESS PREVENTION:
 *    - ABSPATH check at top of file prevents direct file access
 *    - Class redeclaration check prevents conflicts
 * 
 * 2. NONCE REQUIREMENTS:
 *    - This widget displays properties and includes favorite/book buttons
 *    - Favorite Button AJAX: Uses 'resbs_elementor_nonce' (created in class-resbs-elementor.php)
 *      - Nonce is localized to JavaScript as: resbs_elementor_ajax.nonce
 *      - AJAX handler: 'resbs_toggle_favorite' (handled in class-resbs-frontend.php and class-resbs-favorites.php)
 *      - Nonce verification: wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')
 *      - JavaScript handler: elementor.js -> toggleFavorite() function
 *      - Alternative handler: favorites.js -> handleFavoriteToggle() function
 * 
 *    - Book Button: Currently redirects to property page (no AJAX, no nonce needed)
 *      - If AJAX booking is added in future, nonce verification will be required
 * 
 * 3. USER PERMISSIONS:
 *    - Widget Display: No permission check needed (public content)
 *    - Widget Controls (register_controls): Protected by Elementor's own permission system
 *      - Elementor verifies: current_user_can('edit_posts') before allowing widget editing
 *    - Favorite Button: Requires user to be logged in (checked server-side in AJAX handlers)
 *      - Capability check: is_user_logged_in() in AJAX handler
 *      - User can only modify their own favorites (user_id from get_current_user_id())
 *    - Book Button: Public action (redirects to property page)
 * 
 * 4. DATA SANITIZATION:
 *    - All user input from widget settings is sanitized in render() method:
 *      - Title: sanitize_text_field()
 *      - Posts per page: intval()
 *      - Columns: intval()
 *      - Orderby/Order: sanitize_text_field()
 *      - Property type/status: sanitize_text_field()
 *    - All output uses esc_* functions:
 *      - esc_html() for text content
 *      - esc_attr() for HTML attributes
 *      - esc_url() for URLs
 *    - Query parameters are sanitized in get_properties() method
 * 
 * 5. AJAX SECURITY (Handled in Other Files):
 *    - All AJAX handlers must verify nonce: wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')
 *    - All user input in AJAX handlers must be sanitized and validated
 *    - Property IDs must be validated: intval() and get_post() check
 *    - User authentication must be verified: is_user_logged_in()
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
if (class_exists('RESBS_Property_Carousel_Widget')) {
    return;
}

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
        return array('property', 'grid', 'listings', 'real estate', 'booking');
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
                'default' => esc_html__('Featured Properties', 'realestate-booking-suite'),
                'placeholder' => esc_html__('Enter widget title', 'realestate-booking-suite'),
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
            )
        );

        $this->add_responsive_control(
            'columns_tablet',
            array(
                'label' => esc_html__('Columns (Tablet)', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '2',
                'options' => array(
                    '1' => esc_html__('1 Column', 'realestate-booking-suite'),
                    '2' => esc_html__('2 Columns', 'realestate-booking-suite'),
                    '3' => esc_html__('3 Columns', 'realestate-booking-suite'),
                    '4' => esc_html__('4 Columns', 'realestate-booking-suite'),
                ),
            )
        );

        $this->add_responsive_control(
            'columns_mobile',
            array(
                'label' => esc_html__('Columns (Mobile)', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '1',
                'options' => array(
                    '1' => esc_html__('1 Column', 'realestate-booking-suite'),
                    '2' => esc_html__('2 Columns', 'realestate-booking-suite'),
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
                'selectors' => array(
                    '{{WRAPPER}} .similar-properties-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ),
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
                'default' => 'yes',
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

        // Query Section
        $this->start_controls_section(
            'query_section',
            array(
                'label' => esc_html__('Query', 'realestate-booking-suite'),
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
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->get_property_types(),
            )
        );

        $this->add_control(
            'property_status',
            array(
                'label' => esc_html__('Property Status', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => $this->get_property_statuses(),
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

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => esc_html__('Grid Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'widget_style',
            array(
                'label' => esc_html__('Widget Style', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'default',
                'options' => array(
                    'default' => esc_html__('Default', 'realestate-booking-suite'),
                    'modern' => esc_html__('Modern', 'realestate-booking-suite'),
                    'classic' => esc_html__('Classic', 'realestate-booking-suite'),
                ),
            )
        );

        $this->end_controls_section();

        // Card Style Section
        $this->start_controls_section(
            'card_style_section',
            array(
                'label' => esc_html__('Card Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'card_background',
            array(
                'label' => esc_html__('Background Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .property-card' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'card_border_color',
            array(
                'label' => esc_html__('Border Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e5e7eb',
                'selectors' => array(
                    '{{WRAPPER}} .property-card' => 'border-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'card_border_width',
            array(
                'label' => esc_html__('Border Width', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 10,
                        'step' => 1,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => 1,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .property-card' => 'border-width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'card_border_radius',
            array(
                'label' => esc_html__('Border Radius', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px', 'rem'),
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ),
                    'rem' => array(
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ),
                ),
                'default' => array(
                    'unit' => 'rem',
                    'size' => 0.5,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .property-card' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'card_box_shadow',
                'label' => esc_html__('Box Shadow', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .property-card',
            )
        );

        $this->add_control(
            'card_hover_transform',
            array(
                'label' => esc_html__('Hover Transform (px)', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => -20,
                        'max' => 20,
                        'step' => 1,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => -5,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .property-card:hover' => 'transform: translateY({{SIZE}}{{UNIT}});',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'card_hover_box_shadow',
                'label' => esc_html__('Hover Box Shadow', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .property-card:hover',
            )
        );

        $this->end_controls_section();

        // Image Style Section
        $this->start_controls_section(
            'image_style_section',
            array(
                'label' => esc_html__('Image Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'image_height',
            array(
                'label' => esc_html__('Image Height', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px', 'rem'),
                'range' => array(
                    'px' => array(
                        'min' => 100,
                        'max' => 500,
                        'step' => 10,
                    ),
                    'rem' => array(
                        'min' => 6,
                        'max' => 30,
                        'step' => 0.5,
                    ),
                ),
                'default' => array(
                    'unit' => 'rem',
                    'size' => 12,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .property-image img' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .property-image .bg-gray-200' => 'height: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'image_border_radius',
            array(
                'label' => esc_html__('Image Border Radius', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px', 'rem'),
                'range' => array(
                    'px' => array(
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ),
                    'rem' => array(
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ),
                ),
                'default' => array(
                    'unit' => 'px',
                    'size' => 0,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .property-image img' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Badge Style Section
        $this->start_controls_section(
            'badge_style_section',
            array(
                'label' => esc_html__('Badge Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'badge_background',
            array(
                'label' => esc_html__('Background Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#10b981',
                'selectors' => array(
                    '{{WRAPPER}} .property-badge' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'badge_text_color',
            array(
                'label' => esc_html__('Text Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => array(
                    '{{WRAPPER}} .property-badge' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'badge_padding',
            array(
                'label' => esc_html__('Padding', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'rem'),
                'default' => array(
                    'top' => '0.25',
                    'right' => '0.75',
                    'bottom' => '0.25',
                    'left' => '0.75',
                    'unit' => 'rem',
                    'isLinked' => false,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .property-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'badge_border_radius',
            array(
                'label' => esc_html__('Border Radius', 'realestate-booking-suite'),
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
                    'size' => 9999,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .property-badge' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'badge_typography',
                'label' => esc_html__('Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .property-badge',
            )
        );

        $this->end_controls_section();

        // Title Style Section
        $this->start_controls_section(
            'title_style_section',
            array(
                'label' => esc_html__('Title Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label' => esc_html__('Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#111827',
                'selectors' => array(
                    '{{WRAPPER}} .property-card-title a' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'title_hover_color',
            array(
                'label' => esc_html__('Hover Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#10b981',
                'selectors' => array(
                    '{{WRAPPER}} .property-card-title a:hover' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'title_typography',
                'label' => esc_html__('Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .property-card-title',
            )
        );

        $this->add_control(
            'title_margin',
            array(
                'label' => esc_html__('Margin', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'rem'),
                'selectors' => array(
                    '{{WRAPPER}} .property-card-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Location Style Section
        $this->start_controls_section(
            'location_style_section',
            array(
                'label' => esc_html__('Location Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'location_color',
            array(
                'label' => esc_html__('Text Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#6b7280',
                'selectors' => array(
                    '{{WRAPPER}} .property-card-location' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'location_icon_color',
            array(
                'label' => esc_html__('Icon Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#10b981',
                'selectors' => array(
                    '{{WRAPPER}} .property-card-location i' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'location_typography',
                'label' => esc_html__('Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .property-card-location',
            )
        );

        $this->end_controls_section();

        // Price Style Section
        $this->start_controls_section(
            'price_style_section',
            array(
                'label' => esc_html__('Price Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'price_color',
            array(
                'label' => esc_html__('Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#10b981',
                'selectors' => array(
                    '{{WRAPPER}} .property-card-price' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'price_typography',
                'label' => esc_html__('Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .property-card-price',
            )
        );

        $this->end_controls_section();

        // Features Style Section
        $this->start_controls_section(
            'features_style_section',
            array(
                'label' => esc_html__('Features Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'features_text_color',
            array(
                'label' => esc_html__('Text Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#6b7280',
                'selectors' => array(
                    '{{WRAPPER}} .property-card-features' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'features_border_color',
            array(
                'label' => esc_html__('Border Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#e5e7eb',
                'selectors' => array(
                    '{{WRAPPER}} .property-card-features' => 'border-top-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'features_typography',
                'label' => esc_html__('Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .property-card-features',
            )
        );

        $this->add_control(
            'features_padding',
            array(
                'label' => esc_html__('Padding', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'rem'),
                'selectors' => array(
                    '{{WRAPPER}} .property-card-features' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();

        // Info Section Style
        $this->start_controls_section(
            'info_style_section',
            array(
                'label' => esc_html__('Info Section Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'info_padding',
            array(
                'label' => esc_html__('Padding', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'rem'),
                'default' => array(
                    'top' => '1',
                    'right' => '1',
                    'bottom' => '1',
                    'left' => '1',
                    'unit' => 'rem',
                    'isLinked' => true,
                ),
                'selectors' => array(
                    '{{WRAPPER}} .property-info' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Get property types
     */
    private function get_property_types() {
        $types = array('' => esc_html__('All Types', 'realestate-booking-suite'));
        
        $property_types = get_terms(array(
            'taxonomy' => 'property_type',
            'hide_empty' => false,
        ));
        
        // Validate get_terms result for WP_Error
        if (!is_wp_error($property_types) && is_array($property_types)) {
            foreach ($property_types as $type) {
                if (isset($type->slug) && isset($type->name)) {
                    $types[sanitize_text_field($type->slug)] = esc_html($type->name);
                }
            }
        }
        
        return $types;
    }

    /**
     * Get property statuses
     */
    private function get_property_statuses() {
        $statuses = array('' => esc_html__('All Status', 'realestate-booking-suite'));
        
        $property_statuses = get_terms(array(
            'taxonomy' => 'property_status',
            'hide_empty' => false,
        ));
        
        // Validate get_terms result for WP_Error
        if (!is_wp_error($property_statuses) && is_array($property_statuses)) {
            foreach ($property_statuses as $status) {
                if (isset($status->slug) && isset($status->name)) {
                    $statuses[sanitize_text_field($status->slug)] = esc_html($status->name);
                }
            }
        }
        
        return $statuses;
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Sanitize settings
        $title = sanitize_text_field($settings['title']);
        $posts_per_page = intval($settings['posts_per_page']);
        $columns = intval($settings['columns']);
        $columns_tablet = isset($settings['columns_tablet']) ? intval($settings['columns_tablet']) : 2;
        $columns_mobile = isset($settings['columns_mobile']) ? intval($settings['columns_mobile']) : 1;
        $show_price = $settings['show_price'] === 'yes';
        $show_meta = $settings['show_meta'] === 'yes';
        $show_excerpt = isset($settings['show_excerpt']) && $settings['show_excerpt'] === 'yes';
        $show_badges = isset($settings['show_badges']) && $settings['show_badges'] === 'yes';
        $show_favorite_button = isset($settings['show_favorite_button']) && $settings['show_favorite_button'] === 'yes';
        $show_book_button = isset($settings['show_book_button']) && $settings['show_book_button'] === 'yes';
        
        // Validate orderby and order against whitelist
        $orderby_raw = sanitize_text_field($settings['orderby']);
        $allowed_orderby = array('date', 'title', 'price', 'rand');
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
        
        $property_type = sanitize_text_field($settings['property_type']);
        $property_status = sanitize_text_field($settings['property_status']);
        $featured_only = $settings['featured_only'] === 'yes';
        
        // Validate widget style against whitelist
        $widget_style_raw = sanitize_text_field($settings['widget_style']);
        $allowed_styles = array('default', 'modern', 'classic');
        if (!in_array($widget_style_raw, $allowed_styles, true)) {
            $widget_style_raw = 'default';
        }
        $widget_style = $widget_style_raw;

        // Sanitize widget ID
        $widget_id = 'resbs-property-grid-widget-' . absint($this->get_id());

        // Build grid CSS classes
        $grid_classes = 'similar-properties-grid';
        
        // Get grid gap from settings - validate unit for security
        $grid_gap_size = isset($settings['grid_gap']['size']) ? floatval($settings['grid_gap']['size']) : 1.5;
        $grid_gap_unit = isset($settings['grid_gap']['unit']) && in_array($settings['grid_gap']['unit'], array('px', 'rem', 'em', '%'), true) ? sanitize_text_field($settings['grid_gap']['unit']) : 'rem';
        $grid_gap = $grid_gap_size . $grid_gap_unit;

        // Get properties
        $properties = $this->get_properties($settings);

        ?>
        <div class="resbs-property-grid-widget resbs-style-<?php echo esc_attr($widget_style); ?>" 
             id="<?php echo esc_attr($widget_id); ?>">

            <?php if (!empty($title)): ?>
                <h3 class="resbs-widget-title"><?php echo esc_html($title); ?></h3>
            <?php endif; ?>

            <?php if (!empty($properties)): ?>
                <div class="<?php echo esc_attr($grid_classes); ?>" 
                     data-columns="<?php echo esc_attr($columns); ?>"
                     data-columns-tablet="<?php echo esc_attr($columns_tablet); ?>"
                     data-columns-mobile="<?php echo esc_attr($columns_mobile); ?>">
                    <?php foreach ($properties as $property): ?>
                    <?php 
                        // Sanitize all post meta values
                        $property_price_raw = get_post_meta($property->ID, '_property_price', true);
                        $property_price = is_numeric($property_price_raw) ? floatval($property_price_raw) : '';
                        $property_bedrooms = absint(get_post_meta($property->ID, '_property_bedrooms', true));
                        $property_bathrooms = absint(get_post_meta($property->ID, '_property_bathrooms', true));
                        // Get area using helper function that handles unit conversion
                        $property_area = resbs_get_property_area($property->ID, '_property_area_sqft');
                        $property_city = sanitize_text_field(get_post_meta($property->ID, '_property_city', true));
                        $property_state = sanitize_text_field(get_post_meta($property->ID, '_property_state', true));
                        $property_featured_image = esc_url_raw(get_the_post_thumbnail_url($property->ID, 'medium'));
                        $property_status_meta = sanitize_text_field(get_post_meta($property->ID, '_property_status', true));
                        
                        // Format price using currency settings
                        $formatted_price = '';
                        if ($property_price && is_numeric($property_price)) {
                            $currency_symbol = sanitize_text_field(get_option('resbs_currency_symbol', '$'));
                            $currency_position = sanitize_text_field(get_option('resbs_currency_position', 'before'));
                            $formatted_price_num = number_format(floatval($property_price), 2);
                            $currency_symbol_escaped = esc_html($currency_symbol);
                            
                            // Ensure formatted price number is safe (number_format returns safe numeric string)
                            $formatted_price_num_safe = esc_html($formatted_price_num);
                            
                            if ($currency_position === 'before') {
                                $formatted_price = $currency_symbol_escaped . $formatted_price_num_safe;
                            } else {
                                $formatted_price = $formatted_price_num_safe . $currency_symbol_escaped;
                            }
                        } else {
                            $formatted_price = esc_html__('Price on request', 'realestate-booking-suite');
                        }
                        
                        // Format location (already sanitized above)
                        $location_parts = array();
                        if (!empty($property_city)) {
                            $location_parts[] = $property_city;
                        }
                        if (!empty($property_state)) {
                            $location_parts[] = $property_state;
                        }
                        $location = !empty($location_parts) ? implode(', ', $location_parts) : '';
                        ?>
                        
                        <div class="property-card">
                            <div class="property-image">
                                <?php if ($property_featured_image): ?>
                                    <a href="<?php echo esc_url(get_permalink($property->ID)); ?>">
                                        <img src="<?php echo esc_url($property_featured_image); ?>" alt="<?php echo esc_attr($property->post_title); ?>">
                                    </a>
                                <?php else: ?>
                                    <div class="bg-gray-200 h-48 flex items-center justify-center">
                                        <i class="fas fa-home text-gray-400 text-4xl"></i>
                                    </div>
                <?php endif; ?>
                
                                <?php if ($show_badges && $property_status_meta): ?>
                                    <span class="property-badge"><?php echo esc_html($property_status_meta); ?></span>
                <?php endif; ?>
            </div>
                            <div class="property-info">
                                <h4 class="property-card-title">
                                    <a href="<?php echo esc_url(get_permalink($property->ID)); ?>" class="hover:text-emerald-600 transition-colors">
                                        <?php echo esc_html($property->post_title); ?>
                                    </a>
                                </h4>
                                <?php if ($location): ?>
                                    <p class="property-card-location">
                                        <i class="fas fa-map-marker text-emerald-500"></i>
                                        <?php echo esc_html($location); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if ($show_price): ?>
                                    <div class="property-card-price"><?php echo esc_html($formatted_price); ?></div>
                                <?php endif; ?>
                                <?php if ($show_meta): ?>
                                    <div class="property-card-features">
                                        <?php if ($property_bedrooms): ?>
                                            <span><i class="fas fa-bed mr-1"></i><?php echo esc_html($property_bedrooms); ?> Bed<?php echo esc_html($property_bedrooms != 1 ? 's' : ''); ?></span>
                                        <?php endif; ?>
                                        <?php if ($property_bathrooms): ?>
                                            <span><i class="fas fa-bath mr-1"></i><?php echo esc_html($property_bathrooms); ?> Bath<?php echo esc_html($property_bathrooms != 1 ? 's' : ''); ?></span>
                                        <?php endif; ?>
                                        <?php if ($property_area): ?>
                                            <span><i class="fas fa-ruler-combined mr-1"></i><?php echo resbs_format_area($property_area); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($show_excerpt && !empty($property->post_excerpt)): ?>
                                    <div class="property-card-excerpt">
                                        <?php echo esc_html(wp_trim_words($property->post_excerpt, 20, '...')); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="property-card-actions">
                                    <?php if ($show_favorite_button): ?>
                                        <?php
                                        $favorite_nonce = wp_create_nonce('resbs_elementor_nonce');
                                        $is_favorite = is_user_logged_in() ? resbs_is_property_favorited($property->ID) : false;
                                        $favorite_class = $is_favorite ? 'active favorited' : '';
                                        ?>
                                        <button type="button" 
                                                class="resbs-favorite-btn <?php echo esc_attr($favorite_class); ?>" 
                                                data-property-id="<?php echo esc_attr(absint($property->ID)); ?>"
                                                data-nonce="<?php echo esc_attr($favorite_nonce); ?>"
                                                aria-label="<?php esc_attr_e('Toggle favorite', 'realestate-booking-suite'); ?>">
                                            <i class="<?php echo esc_attr($is_favorite ? 'fas' : 'far'); ?> fa-heart"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($show_book_button): ?>
                                        <a href="<?php echo esc_url(get_permalink($property->ID)); ?>" 
                                           class="resbs-book-btn"
                                           aria-label="<?php esc_attr_e('Book property', 'realestate-booking-suite'); ?>">
                                            <?php esc_html_e('Book Now', 'realestate-booking-suite'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-8">
                    <i class="fas fa-home text-gray-400 text-4xl mb-4"></i>
                    <p class="text-gray-500"><?php esc_html_e('No properties found.', 'realestate-booking-suite'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <?php
        // Enqueue base CSS once
        if (!wp_style_is('resbs-elementor-property-carousel', 'enqueued')) {
            wp_enqueue_style(
                'resbs-elementor-property-carousel',
                RESBS_URL . 'assets/css/elementor-property-carousel.css',
                array(),
                '1.0.0'
            );
        }
        
        // Add dynamic inline styles for this widget instance
        // Sanitize all values for CSS context
        // Widget ID is already sanitized via absint() and contains only alphanumeric and hyphens
        $widget_id_safe = preg_replace('/[^a-zA-Z0-9_-]/', '', $widget_id);
        $columns_css = absint($columns);
        $columns_tablet_css = absint($columns_tablet);
        $columns_mobile_css = absint($columns_mobile);
        
        // Sanitize grid gap - ensure only valid CSS units and numbers
        $grid_gap_safe = '';
        if (preg_match('/^[\d.]+(px|rem|em|%)$/', $grid_gap)) {
            // Additional sanitization: strip any potentially dangerous characters
            $grid_gap_safe = preg_replace('/[^0-9a-z.%]/i', '', $grid_gap);
            // Validate again after sanitization
            if (!preg_match('/^[\d.]+(px|rem|em|%)$/', $grid_gap_safe)) {
                $grid_gap_safe = '1.5rem';
            }
        } else {
            // Fallback to safe default
            $grid_gap_safe = '1.5rem';
        }
        
        // Build CSS with proper escaping - all values are already sanitized
        $dynamic_css = sprintf(
            "#%s .similar-properties-grid {
            gap: %s !important;
            grid-template-columns: repeat(%d, 1fr) !important;
        }
        
        @media (max-width: 1024px) {
            #%s .similar-properties-grid {
                grid-template-columns: repeat(%d, 1fr) !important;
            }
        }
        
        @media (max-width: 768px) {
            #%s .similar-properties-grid {
                grid-template-columns: repeat(%d, 1fr) !important;
            }
        }",
            esc_attr($widget_id_safe),
            esc_attr($grid_gap_safe),
            absint($columns_css),
            esc_attr($widget_id_safe),
            absint($columns_tablet_css),
            esc_attr($widget_id_safe),
            absint($columns_mobile_css)
        );
        
        wp_add_inline_style('resbs-elementor-property-carousel', $dynamic_css);
        ?>
        <?php
    }

    /**
     * Get properties based on query settings
     */
    private function get_properties($settings) {
        // Build query args
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => intval($settings['posts_per_page']),
        );
        
        // Validate orderby and order against whitelist
        $orderby_raw = sanitize_text_field($settings['orderby']);
        $allowed_orderby = array('date', 'title', 'price', 'rand');
        if (!in_array($orderby_raw, $allowed_orderby, true)) {
            $orderby_raw = 'date';
        }
        $query_args['orderby'] = $orderby_raw;
        
        $order_raw = sanitize_text_field($settings['order']);
        $allowed_order = array('ASC', 'DESC');
        if (!in_array($order_raw, $allowed_order, true)) {
            $order_raw = 'DESC';
        }
        $query_args['order'] = $order_raw;
        
        // Handle price ordering
        if ($orderby_raw === 'price') {
            $query_args['orderby'] = 'meta_value_num';
            $query_args['meta_key'] = '_property_price';
        }

        // Add meta query for featured properties
        if ($settings['featured_only'] === 'yes') {
            $query_args['meta_query'] = array(
                array(
                    'key' => '_property_featured',
                    'value' => array('yes', '1'),
                    'compare' => 'IN'
                )
            );
        }

        // Add taxonomy query
        $tax_query = array();
        
        if (!empty($settings['property_type'])) {
            $property_type_sanitized = sanitize_text_field($settings['property_type']);
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $property_type_sanitized
            );
        }

        if (!empty($settings['property_status'])) {
            $property_status_sanitized = sanitize_text_field($settings['property_status']);
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $property_status_sanitized
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        $properties_query = new WP_Query($query_args);
        $properties = array();

        if ($properties_query->have_posts()) {
            $properties = $properties_query->posts;
            wp_reset_postdata();
        }

        return $properties;
    }

}
