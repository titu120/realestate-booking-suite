<?php
/**
 * Elementor Property Listings Widget
 * 
 * SECURITY NOTES:
 * ===============
 * 
 * 1. DIRECT ACCESS PREVENTION:
 *    - ABSPATH check at top of file prevents direct file access
 *    - Class redeclaration check prevents conflicts
 * 
 * 2. NONCE REQUIREMENTS:
 *    - AJAX Handler: 'resbs_elementor_load_listings' (if used)
 *      - Nonce action: 'resbs_elementor_nonce'
 *      - Nonce is localized to JavaScript as: resbs_elementor_ajax.nonce
 *      - AJAX handler location: class-resbs-frontend.php -> handle_elementor_load_listings()
 *      - Nonce verification: wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')
 *      - JavaScript handler: elementor.js -> reloadListings() function
 *    - Note: Current widget implementation doesn't use AJAX, but handler exists for future use
 * 
 * 3. USER PERMISSIONS:
 *    - Widget Display: No permission check needed (public content)
 *    - Widget Registration (Admin): Elementor handles permissions automatically
 *      - Users need 'edit_posts' capability to edit Elementor widgets
 *      - Helper methods (get_pages_list, get_property_types, get_property_statuses) are called
 *        during widget registration in admin - capability check added for security
 *    - AJAX Handler (if used): Public endpoint - allows both logged-in and non-logged-in users
 *      - Registered via: wp_ajax_resbs_elementor_load_listings (logged-in)
 *      - Registered via: wp_ajax_nopriv_resbs_elementor_load_listings (non-logged-in)
 *      - No admin capabilities required for viewing listings
 * 
 * 4. DATA SANITIZATION:
 *    - All user input sanitized: sanitize_text_field(), intval(), esc_attr(), esc_html(), esc_url()
 *    - Settings from Elementor are already sanitized by Elementor framework
 *    - Query parameters validated: intval() for numeric values, sanitize_text_field() for text
 *    - Property IDs validated: Must be valid post IDs of 'property' post type
 * 
 * 5. AJAX SECURITY (if AJAX is used):
 *    - Nonce verification: Required in AJAX handler (class-resbs-frontend.php)
 *    - Input sanitization: All POST data sanitized before use
 *    - Query validation: Only published properties are returned
 *    - No user permissions required for public listings display
 * 
 * 6. OUTPUT ESCAPING:
 *    - All output uses esc_* functions: esc_html(), esc_attr(), esc_url(), esc_js()
 *    - JSON data: wp_json_encode() with esc_attr() wrapper
 *    - No direct echo of user input without escaping
 *    - Property data: All meta values escaped before output
 * 
 * 7. WIDGET REGISTRATION SECURITY:
 *    - Helper methods called during widget registration check user capabilities
 *    - get_pages_list(): Checks current_user_can('edit_posts') before retrieving pages
 *    - get_property_types(): Checks current_user_can('edit_posts') before retrieving terms
 *    - get_property_statuses(): Checks current_user_can('edit_posts') before retrieving terms
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
if (class_exists('RESBS_Listings_Widget')) {
    return;
}

class RESBS_Listings_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'resbs-listings';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return esc_html__('Property Listings', 'realestate-booking-suite');
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
        return array('property', 'listings', 'grid', 'list', 'map', 'real estate');
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
                'default' => esc_html__('Properties', 'realestate-booking-suite'),
            )
        );

        $this->add_control(
            'layout',
            array(
                'label' => esc_html__('Default Layout', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => array(
                    'grid' => esc_html__('Grid', 'realestate-booking-suite'),
                    'list' => esc_html__('List', 'realestate-booking-suite'),
                    'map' => esc_html__('Map', 'realestate-booking-suite'),
                ),
            )
        );

        $this->add_control(
            'columns',
            array(
                'label' => esc_html__('Columns (Grid Layout)', 'realestate-booking-suite'),
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
            )
        );

        $this->add_control(
            'disable_pagination',
            array(
                'label' => esc_html__('Disable Pagination', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->add_control(
            'view_all_link_name',
            array(
                'label' => esc_html__('View All Link Name', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('View all', 'realestate-booking-suite'),
            )
        );

        $this->add_control(
            'view_all_page',
            array(
                'label' => esc_html__('View All Page', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_pages_list(),
            )
        );

        $this->end_controls_section();

        // Navbar Section
        $this->start_controls_section(
            'navbar_section',
            array(
                'label' => esc_html__('Navbar', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_navbar',
            array(
                'label' => esc_html__('Show Navbar', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_sorting',
            array(
                'label' => esc_html__('Show Sorting', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => array(
                    'show_navbar' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_view_toggle',
            array(
                'label' => esc_html__('Show View Toggle', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => array(
                    'show_navbar' => 'yes',
                ),
            )
        );

        $this->end_controls_section();

        // Query Filter Section
        $this->start_controls_section(
            'query_filter_section',
            array(
                'label' => esc_html__('Query Filter', 'realestate-booking-suite'),
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

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => esc_html__('Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'title_typography',
                'label' => esc_html__('Title Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-listings-title',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label' => esc_html__('Title Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-listings-title' => 'color: {{VALUE}};',
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
                    '{{WRAPPER}} .resbs-layout-grid .resbs-property-card' => 'background-color: {{VALUE}};',
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
                    '{{WRAPPER}} .resbs-layout-grid .resbs-property-card' => 'border-color: {{VALUE}};',
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
                    '{{WRAPPER}} .resbs-layout-grid .resbs-property-card' => 'border-radius: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'card_box_shadow',
                'label' => esc_html__('Box Shadow', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-layout-grid .resbs-property-card',
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
                'label' => esc_html__('Price Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#10b981',
                'selectors' => array(
                    '{{WRAPPER}} .resbs-property-price' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'price_typography',
                'label' => esc_html__('Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-property-price',
            )
        );

        $this->end_controls_section();
    }

    /**
     * Get pages list
     * 
     * Security: Checks user capability before retrieving pages
     * This method is called during widget registration in admin area
     */
    private function get_pages_list() {
        // Check user capability - only allow users who can edit posts to see pages list
        // This prevents unauthorized access to page data during widget registration
        if (!current_user_can('edit_posts')) {
            return array('' => esc_html__('Select Page', 'realestate-booking-suite'));
        }
        
        $pages = array('' => esc_html__('Select Page', 'realestate-booking-suite'));
        $all_pages = get_pages();
        
        foreach ($all_pages as $page) {
            $pages[$page->ID] = esc_html($page->post_title);
        }
        
        return $pages;
    }

    /**
     * Get property types
     * 
     * Security: Checks user capability before retrieving taxonomy terms
     * This method is called during widget registration in admin area
     */
    private function get_property_types() {
        // Check user capability - only allow users who can edit posts to see property types
        // This prevents unauthorized access to taxonomy data during widget registration
        if (!current_user_can('edit_posts')) {
            return array();
        }
        
        $types = get_terms(array(
            'taxonomy' => 'property_type',
            'hide_empty' => false,
        ));
        
        $options = array();
        if (!is_wp_error($types) && is_array($types)) {
            foreach ($types as $type) {
                $options[$type->slug] = esc_html($type->name);
            }
        }
        
        return $options;
    }

    /**
     * Get property statuses
     * 
     * Security: Checks user capability before retrieving taxonomy terms
     * This method is called during widget registration in admin area
     */
    private function get_property_statuses() {
        // Check user capability - only allow users who can edit posts to see property statuses
        // This prevents unauthorized access to taxonomy data during widget registration
        if (!current_user_can('edit_posts')) {
            return array();
        }
        
        $statuses = get_terms(array(
            'taxonomy' => 'property_status',
            'hide_empty' => false,
        ));
        
        $options = array();
        if (!is_wp_error($statuses) && is_array($statuses)) {
            foreach ($statuses as $status) {
                $options[$status->slug] = esc_html($status->name);
            }
        }
        
        return $options;
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $title = sanitize_text_field($settings['title']);
        
        // Validate layout against whitelist
        $layout_raw = sanitize_text_field($settings['layout']);
        $allowed_layouts = array('grid', 'list', 'map');
        if (!in_array($layout_raw, $allowed_layouts, true)) {
            $layout_raw = 'grid';
        }
        $layout = $layout_raw;
        
        $columns = isset($settings['columns']) ? intval($settings['columns']) : 3;
        // Validate columns
        if ($columns < 2 || $columns > 4) {
            $columns = 3;
        }
        
        // Validate and sanitize grid gap
        $grid_gap = '1.5rem'; // Default
        if (isset($settings['grid_gap']['size']) && isset($settings['grid_gap']['unit'])) {
            $gap_size = is_numeric($settings['grid_gap']['size']) ? floatval($settings['grid_gap']['size']) : 1.5;
            $gap_unit = sanitize_text_field($settings['grid_gap']['unit']);
            $allowed_units = array('px', 'rem');
            if (!in_array($gap_unit, $allowed_units, true)) {
                $gap_unit = 'rem';
            }
            // Store raw value, will be escaped when used
            $grid_gap = $gap_size . $gap_unit;
        }
        
        $disable_pagination = $settings['disable_pagination'] === 'yes';
        $view_all_link_name = sanitize_text_field($settings['view_all_link_name']);
        $view_all_page = intval($settings['view_all_page']);
        $show_navbar = $settings['show_navbar'] === 'yes';
        $show_sorting = $settings['show_sorting'] === 'yes';
        $show_view_toggle = $settings['show_view_toggle'] === 'yes';
        $posts_per_page = intval($settings['posts_per_page']);
        
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
        
        // Sanitize property types and statuses arrays
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
        
        // Sanitize widget ID
        $widget_id = 'resbs-listings-' . absint($this->get_id());
        
        // Build query
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
            'orderby' => $orderby === 'price' ? 'meta_value_num' : $orderby,
            'order' => $order,
        );
        
        if ($orderby === 'price') {
            $query_args['meta_key'] = '_property_price';
        }
        
        $tax_query = array();
        if (!empty($property_types)) {
            // Sanitize each term in the array
            $property_types_sanitized = array_map('sanitize_text_field', $property_types);
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $property_types_sanitized
            );
        }
        
        if (!empty($property_statuses)) {
            // Sanitize each term in the array
            $property_statuses_sanitized = array_map('sanitize_text_field', $property_statuses);
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $property_statuses_sanitized
            );
        }
        
        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }
        
        $properties = new WP_Query($query_args);
        
        ?>
        <div class="resbs-listings-widget" id="<?php echo esc_attr($widget_id); ?>"
             data-settings="<?php echo esc_attr(wp_json_encode(array(
                 'layout' => $layout,
                 'disable_pagination' => $disable_pagination,
                 'posts_per_page' => $posts_per_page,
                 'orderby' => $orderby,
                 'order' => $order
             ))); ?>"
             data-columns="<?php echo esc_attr($columns); ?>"
             data-grid-gap="<?php echo esc_attr($grid_gap); ?>">
            
            <?php if ($show_navbar): ?>
                <div class="resbs-listings-navbar">
                    <?php if (!empty($title)): ?>
                        <h3 class="resbs-listings-title"><?php echo esc_html($title); ?></h3>
                    <?php endif; ?>
                    
                    <?php if ($properties->found_posts > 0): ?>
                        <span class="resbs-listings-count"><?php echo esc_html($properties->found_posts); ?> <?php esc_html_e('results', 'realestate-booking-suite'); ?></span>
                    <?php endif; ?>
                    
                    <div class="resbs-listings-controls">
                        <?php if ($show_sorting): ?>
                            <div class="resbs-sort-control">
                                <label><?php esc_html_e('Sort by', 'realestate-booking-suite'); ?></label>
                                <select class="resbs-sort-select">
                                    <option value="date_desc" <?php selected($orderby, 'date'); selected($order, 'DESC'); ?>><?php esc_html_e('Newest', 'realestate-booking-suite'); ?></option>
                                    <option value="date_asc" <?php selected($orderby, 'date'); selected($order, 'ASC'); ?>><?php esc_html_e('Oldest', 'realestate-booking-suite'); ?></option>
                                    <option value="price_asc" <?php selected($orderby, 'price'); selected($order, 'ASC'); ?>><?php esc_html_e('Price: Low to High', 'realestate-booking-suite'); ?></option>
                                    <option value="price_desc" <?php selected($orderby, 'price'); selected($order, 'DESC'); ?>><?php esc_html_e('Price: High to Low', 'realestate-booking-suite'); ?></option>
                                    <option value="title_asc" <?php selected($orderby, 'title'); selected($order, 'ASC'); ?>><?php esc_html_e('Title: A-Z', 'realestate-booking-suite'); ?></option>
                                </select>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($show_view_toggle): ?>
                            <div class="resbs-view-toggle">
                                <button type="button" class="resbs-view-btn resbs-view-grid <?php echo esc_attr($layout === 'grid' ? 'active' : ''); ?>" data-view="grid">
                                    <span class="dashicons dashicons-grid-view"></span>
                                </button>
                                <button type="button" class="resbs-view-btn resbs-view-list <?php echo esc_attr($layout === 'list' ? 'active' : ''); ?>" data-view="list">
                                    <span class="dashicons dashicons-list-view"></span>
                                </button>
                                <button type="button" class="resbs-view-btn resbs-view-map <?php echo esc_attr($layout === 'map' ? 'active' : ''); ?>" data-view="map">
                                    <span class="dashicons dashicons-location"></span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php
            // Prepare map data - collect all properties with location data
            $map_properties = array();
            if ($properties->have_posts()) {
                while ($properties->have_posts()): $properties->the_post();
                    $prop_id = get_the_ID();
                    $latitude = get_post_meta($prop_id, '_property_latitude', true);
                    $longitude = get_post_meta($prop_id, '_property_longitude', true);
                    
                    if ($latitude && $longitude) {
                        $price_meta = get_post_meta($prop_id, '_property_price', true);
                        $bedrooms_meta = get_post_meta($prop_id, '_property_bedrooms', true);
                        $bathrooms_meta = get_post_meta($prop_id, '_property_bathrooms', true);
                        
                        $map_properties[] = array(
                            'id' => absint($prop_id),
                            'title' => esc_html(get_the_title()),
                            'lat' => floatval($latitude),
                            'lng' => floatval($longitude),
                            'price' => is_numeric($price_meta) ? floatval($price_meta) : '',
                            'bedrooms' => !empty($bedrooms_meta) ? absint($bedrooms_meta) : '',
                            'bathrooms' => !empty($bathrooms_meta) ? absint($bathrooms_meta) : '',
                            'permalink' => esc_url(get_permalink($prop_id)),
                            'image' => esc_url(get_the_post_thumbnail_url($prop_id, 'thumbnail')),
                        );
                    }
                endwhile;
                $properties->rewind_posts();
            }
            ?>
            
            <div class="resbs-listings-content resbs-layout-<?php echo esc_attr($layout); ?>">
                <!-- Map View (hidden initially if not default) -->
                <div class="resbs-listings-map-view" style="display: <?php echo esc_attr($layout === 'map' ? 'block' : 'none'); ?>;">
                    <div class="resbs-listings-map-container" id="resbs-listings-map-<?php echo esc_attr($widget_id); ?>">
                        <div class="resbs-map-wrapper">
                            <div id="resbs-map-canvas-<?php echo esc_attr($widget_id); ?>" class="resbs-map-canvas"></div>
                        </div>
                        <script type="application/json" class="resbs-map-data"><?php echo wp_json_encode($map_properties, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?></script>
                    </div>
                </div>
                
                <!-- Grid/List View (hidden if map is default) -->
                <div class="resbs-listings-grid-view" style="display: <?php echo esc_attr($layout === 'map' ? 'none' : 'block'); ?>;">
                    <?php if ($properties->have_posts()): ?>
                        <?php
                        $container_style = '';
                        if ($layout === 'grid') {
                            $container_style = 'grid-template-columns: repeat(' . absint($columns) . ', 1fr); gap: ' . esc_attr($grid_gap) . ';';
                        }
                        ?>
                        <div class="resbs-properties-container" 
                             style="<?php echo esc_attr($container_style); ?>">
                            <?php while ($properties->have_posts()): $properties->the_post(); ?>
                                <?php $this->render_property_card($layout); ?>
                            <?php endwhile; ?>
                            <?php wp_reset_postdata(); ?>
                        </div>
                    <?php else: ?>
                        <p class="resbs-no-properties"><?php esc_html_e('No properties found.', 'realestate-booking-suite'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!$disable_pagination && $properties->max_num_pages > 1 && $layout !== 'map'): ?>
                <div class="resbs-listings-pagination">
                    <?php
                    echo paginate_links(array(
                        'total' => $properties->max_num_pages,
                        'current' => max(1, get_query_var('paged')),
                        'prev_text' => '&laquo; ' . esc_html__('Previous', 'realestate-booking-suite'),
                        'next_text' => esc_html__('Next', 'realestate-booking-suite') . ' &raquo;',
                    ));
                    ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($view_all_link_name) && $view_all_page): ?>
                <div class="resbs-view-all-link">
                    <a href="<?php echo esc_url(get_permalink($view_all_page)); ?>">
                        <?php echo esc_html($view_all_link_name); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        
        <?php
        // Enqueue base CSS once
        if (!wp_style_is('resbs-elementor-listings-widget', 'enqueued')) {
            wp_enqueue_style(
                'resbs-elementor-listings-widget',
                RESBS_URL . 'assets/css/elementor-listings-widget.css',
                array(),
                '1.0.0'
            );
        }
        
        // Enqueue Leaflet CSS
        if (!wp_style_is('leaflet', 'enqueued')) {
            wp_enqueue_style(
                'leaflet',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
                array(),
                '1.9.4'
            );
        }
        
        // Add dynamic inline styles for this widget instance (grid gap and columns)
        // Escape widget ID and values for CSS
        $widget_id_css = esc_attr($widget_id);
        $columns_css = absint($columns);
        $grid_gap_css = esc_attr($grid_gap);
        
        $dynamic_css = "
        #{$widget_id_css} .resbs-properties-container {
            grid-template-columns: repeat({$columns_css}, 1fr) !important;
            gap: {$grid_gap_css} !important;
        }
        ";
        
        wp_add_inline_style('resbs-elementor-listings-widget', $dynamic_css);
    }

    /**
     * Render property card
     */
    private function render_property_card($layout = 'grid') {
        $property_id = get_the_ID();
        $price = get_post_meta($property_id, '_property_price', true);
        $bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        // Get area using helper function that handles unit conversion
        $area_value = resbs_get_property_area($property_id, '_property_area_sqft');
        $city = get_post_meta($property_id, '_property_city', true);
        $state = get_post_meta($property_id, '_property_state', true);
        $property_status_meta = get_post_meta($property_id, '_property_status', true);
        $featured = get_post_meta($property_id, '_property_featured', true);
        $sold = get_post_meta($property_id, '_property_sold', true);
        
        // Get featured image
        $featured_image = get_the_post_thumbnail_url($property_id, 'medium');
        if (!$featured_image) {
            $gallery = get_post_meta($property_id, '_property_gallery', true);
            if ($gallery && is_array($gallery) && !empty($gallery)) {
                // Validate and sanitize gallery array
                $first_image_id = absint($gallery[0]);
                if ($first_image_id > 0) {
                    $featured_image = wp_get_attachment_image_url($first_image_id, 'medium');
                    if (!$featured_image) {
                        $featured_image = '';
                    }
                }
            }
        }
        
        // Format price using currency settings
        $formatted_price = '';
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
        
        // Sanitize and format location
        $city_sanitized = sanitize_text_field($city);
        $state_sanitized = sanitize_text_field($state);
        $location = trim($city_sanitized . ', ' . $state_sanitized, ', ');
        
        ?>
        <div class="resbs-property-card resbs-layout-<?php echo esc_attr($layout); ?>" data-property-id="<?php echo esc_attr($property_id); ?>">
            <div class="resbs-property-image">
                <?php if ($featured_image): ?>
                    <a href="<?php echo esc_url(get_permalink($property_id)); ?>">
                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url(get_permalink($property_id)); ?>">
                        <div class="resbs-placeholder-image" style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                            <span class="dashicons dashicons-camera" style="font-size: 48px;"></span>
                        </div>
                    </a>
                <?php endif; ?>
                
                <div class="resbs-property-badges">
                    <?php if ($featured === 'yes' || $featured === '1'): ?>
                        <span class="resbs-badge resbs-badge-featured"><?php esc_html_e('Featured', 'realestate-booking-suite'); ?></span>
                    <?php endif; ?>
                    <?php if ($sold === 'yes' || $sold === '1'): ?>
                        <span class="resbs-badge resbs-badge-sold"><?php esc_html_e('Sold', 'realestate-booking-suite'); ?></span>
                    <?php elseif ($property_status_meta): ?>
                        <span class="resbs-badge resbs-badge-status"><?php echo esc_html(sanitize_text_field($property_status_meta)); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="resbs-property-content">
                <div class="resbs-property-header-line">
                    <div class="resbs-property-header-left">
                        <h3 class="resbs-property-title">
                            <a href="<?php echo esc_url(get_permalink($property_id)); ?>"><?php echo esc_html(get_the_title()); ?></a>
                        </h3>
                        <?php if ($location): ?>
                            <div class="resbs-property-location">
                                <i class="fas fa-map-marker"></i>
                                <span><?php echo esc_html($location); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="resbs-property-price"><?php echo esc_html($formatted_price); ?></div>
                </div>
                
                <div class="resbs-property-meta">
                    <?php if (!empty($bedrooms)): ?>
                        <span class="resbs-meta-item">
                            <i class="fas fa-bed"></i>
                            <?php echo esc_html($bedrooms); ?> Bed<?php echo esc_html($bedrooms != 1 ? 's' : ''); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($bathrooms)): ?>
                        <span class="resbs-meta-item">
                            <i class="fas fa-bath"></i>
                            <?php echo esc_html($bathrooms); ?> Bath<?php echo esc_html($bathrooms != 1 ? 's' : ''); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if (!empty($area_value)): ?>
                        <span class="resbs-meta-item">
                            <i class="fas fa-ruler-combined"></i>
                            <?php echo esc_html(resbs_format_area($area_value)); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if ($layout === 'grid'): ?>
                    <div class="resbs-property-actions">
                        <a href="<?php echo esc_url(get_permalink($property_id)); ?>" class="resbs-btn resbs-btn-primary">
                            <?php esc_html_e('View Details', 'realestate-booking-suite'); ?>
                        </a>
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
        <div class="resbs-listings-widget">
            <div class="resbs-listings-navbar">
                <h3 class="resbs-listings-title">{{ settings.title }}</h3>
                <span class="resbs-listings-count">2 <?php esc_html_e('results', 'realestate-booking-suite'); ?></span>
                <div class="resbs-listings-controls">
                    <div class="resbs-sort-control">
                        <label><?php esc_html_e('Sort by', 'realestate-booking-suite'); ?></label>
                        <select class="resbs-sort-select">
                            <option><?php esc_html_e('Newest', 'realestate-booking-suite'); ?></option>
                        </select>
                    </div>
                    <div class="resbs-view-toggle">
                        <button type="button" class="resbs-view-btn active"><?php esc_html_e('Grid', 'realestate-booking-suite'); ?></button>
                        <button type="button" class="resbs-view-btn"><?php esc_html_e('List', 'realestate-booking-suite'); ?></button>
                        <button type="button" class="resbs-view-btn"><?php esc_html_e('Map', 'realestate-booking-suite'); ?></button>
                    </div>
                </div>
            </div>
            <div class="resbs-listings-content">
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
        <?php
    }
}

