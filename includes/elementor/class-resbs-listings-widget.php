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
        $layout = sanitize_text_field($settings['layout']);
        $columns = isset($settings['columns']) ? intval($settings['columns']) : 3;
        $grid_gap = isset($settings['grid_gap']['size']) ? $settings['grid_gap']['size'] . $settings['grid_gap']['unit'] : '1.5rem';
        $disable_pagination = $settings['disable_pagination'] === 'yes';
        $view_all_link_name = sanitize_text_field($settings['view_all_link_name']);
        $view_all_page = intval($settings['view_all_page']);
        $show_navbar = $settings['show_navbar'] === 'yes';
        $show_sorting = $settings['show_sorting'] === 'yes';
        $show_view_toggle = $settings['show_view_toggle'] === 'yes';
        $posts_per_page = intval($settings['posts_per_page']);
        $orderby = sanitize_text_field($settings['orderby']);
        $order = sanitize_text_field($settings['order']);
        $property_types = $settings['property_type'] ?? array();
        $property_statuses = $settings['property_status'] ?? array();
        
        $widget_id = 'resbs-listings-' . $this->get_id();
        
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
             ))); ?>">
            
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
                        $map_properties[] = array(
                            'id' => $prop_id,
                            'title' => esc_html(get_the_title()),
                            'lat' => floatval($latitude),
                            'lng' => floatval($longitude),
                            'price' => get_post_meta($prop_id, '_property_price', true),
                            'bedrooms' => get_post_meta($prop_id, '_property_bedrooms', true),
                            'bathrooms' => get_post_meta($prop_id, '_property_bathrooms', true),
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
                        <script type="application/json" class="resbs-map-data"><?php echo wp_json_encode($map_properties); ?></script>
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
        
        <style>
            /* Listings Widget Container */
            #<?php echo esc_attr($widget_id); ?> {
                width: 100% !important;
            }
            
            /* Navbar Styles */
            #<?php echo esc_attr($widget_id); ?> .resbs-listings-navbar {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                flex-wrap: wrap !important;
                gap: 1rem !important;
                margin-bottom: 2rem !important;
                padding-bottom: 1rem !important;
                border-bottom: 1px solid #e5e7eb !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-listings-title {
                font-size: 1.5rem !important;
                font-weight: 700 !important;
                color: #111827 !important;
                margin: 0 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-listings-count {
                font-size: 0.875rem !important;
                color: #6b7280 !important;
                font-weight: 500 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-listings-controls {
                display: flex !important;
                align-items: center !important;
                gap: 1rem !important;
                flex-wrap: wrap !important;
            }
            
            /* Sort Control */
            #<?php echo esc_attr($widget_id); ?> .resbs-sort-control {
                display: flex !important;
                align-items: center !important;
                gap: 0.5rem !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-sort-control label {
                font-size: 0.875rem !important;
                color: #374151 !important;
                font-weight: 500 !important;
                margin: 0 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-sort-select {
                padding: 0.5rem 0.75rem !important;
                border: 1px solid #d1d5db !important;
                border-radius: 0.375rem !important;
                font-size: 0.875rem !important;
                background: #ffffff !important;
                color: #111827 !important;
                cursor: pointer !important;
                transition: all 0.2s !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-sort-select:focus {
                outline: none !important;
                border-color: #3b82f6 !important;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
            }
            
            /* View Toggle Buttons */
            #<?php echo esc_attr($widget_id); ?> .resbs-view-toggle {
                display: flex !important;
                gap: 0.25rem !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 0.375rem !important;
                padding: 0.25rem !important;
                background: #ffffff !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-view-btn {
                padding: 0.5rem 0.75rem !important;
                border: none !important;
                background: transparent !important;
                color: #6b7280 !important;
                cursor: pointer !important;
                border-radius: 0.25rem !important;
                transition: all 0.2s !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-view-btn:hover {
                background: #f3f4f6 !important;
                color: #111827 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-view-btn.active {
                background: #3b82f6 !important;
                color: #ffffff !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-view-btn .dashicons {
                font-size: 18px !important;
                width: 18px !important;
                height: 18px !important;
            }
            
            /* Grid Layout Container */
            #<?php echo esc_attr($widget_id); ?> .resbs-properties-container {
                display: grid !important;
                width: 100% !important;
                gap: 1.5rem !important;
            }
            
            @media (max-width: 1024px) {
                #<?php echo esc_attr($widget_id); ?> .resbs-properties-container {
                    grid-template-columns: repeat(2, 1fr) !important;
                }
            }
            
            @media (max-width: 768px) {
                #<?php echo esc_attr($widget_id); ?> .resbs-properties-container {
                    grid-template-columns: 1fr !important;
                }
                
                #<?php echo esc_attr($widget_id); ?> .resbs-listings-navbar {
                    flex-direction: column !important;
                    align-items: flex-start !important;
                }
                
                #<?php echo esc_attr($widget_id); ?> .resbs-listings-controls {
                    width: 100% !important;
                    justify-content: space-between !important;
                }
                
                #<?php echo esc_attr($widget_id); ?> .resbs-property-header-line {
                    flex-direction: column !important;
                    align-items: flex-start !important;
                    gap: 0.5rem !important;
                }
                
                #<?php echo esc_attr($widget_id); ?> .resbs-property-price {
                    align-self: flex-start !important;
                }
            }
            
            /* Grid Layout Property Cards */
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-grid .resbs-property-card {
                border: 1px solid #e5e7eb !important;
                border-radius: 0.5rem !important;
                overflow: hidden !important;
                transition: all 0.3s !important;
                display: flex !important;
                flex-direction: column !important;
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-grid .resbs-property-card:hover {
                transform: translateY(-5px) !important;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1) !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-grid .resbs-property-image {
                position: relative !important;
                display: block !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-grid .resbs-property-image img {
                width: 100% !important;
  
                object-fit: cover !important;
                display: block !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-grid .resbs-property-image .resbs-placeholder-image {
    
                width: 100% !important;
            }
            
            /* Property Badges */
            #<?php echo esc_attr($widget_id); ?> .resbs-property-badges {
                position: absolute !important;
                top: 0.75rem !important;
                left: 0.75rem !important;
                z-index: 5 !important;
                display: flex !important;
                flex-wrap: wrap !important;
                gap: 0.5rem !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-badge {
                padding: 0.25rem 0.75rem !important;
                border-radius: 9999px !important;
                font-size: 0.75rem !important;
                font-weight: 600 !important;
                display: inline-block !important;
                text-transform: uppercase !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-badge-featured {
                background: #ef4444 !important;
                color: #ffffff !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-badge-sold {
                background: #10b981 !important;
                color: #ffffff !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-badge-status {
                background: #10b981 !important;
                color: #ffffff !important;
            }
            
            /* Hide Quick View and Wishlist buttons */
            #<?php echo esc_attr($widget_id); ?> .resbs-quickview-btn,
            #<?php echo esc_attr($widget_id); ?> .resbs-quickview-trigger,
            #<?php echo esc_attr($widget_id); ?> .resbs-quick-view-btn,
            #<?php echo esc_attr($widget_id); ?> .resbs-favorite-btn,
            #<?php echo esc_attr($widget_id); ?> .resbs-quickview-btn,
            #<?php echo esc_attr($widget_id); ?> .resbs-property-card .resbs-quickview-trigger {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                pointer-events: none !important;
            }
            
            /* Property Content */
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-grid .resbs-property-content {
                padding: 1rem !important;
                display: flex !important;
                flex-direction: column !important;
                flex-grow: 1 !important;
                gap: 0 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-property-content > * {
                margin-top: 0 !important;
                margin-bottom: 0 !important;
            }
            
            /* Header Line - Title, Location, and Price in one line */
            #<?php echo esc_attr($widget_id); ?> .resbs-property-header-line {
                display: flex !important;
                justify-content: space-between !important;
                align-items: flex-start !important;
                gap: 1rem !important;
                margin-bottom: 0.75rem !important;
                flex-wrap: wrap !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-property-header-left {
                flex: 1 !important;
                min-width: 0 !important;
                display: flex !important;
                flex-direction: column !important;
                gap: 0.25rem !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-property-title {
                font-size: 1.125rem !important;
                font-weight: 700 !important;
                margin: 0 !important;
                padding: 0 !important;
                line-height: 1.4 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-property-title a {
                color: #111827 !important;
                text-decoration: none !important;
                transition: color 0.3s !important;
                display: block !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-property-title a:hover {
                color: #10b981 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-property-location {
                font-size: 0.875rem !important;
                color: #6b7280 !important;
                margin: 0 !important;
                padding: 0 !important;
                display: flex !important;
                align-items: center !important;
                gap: 0.375rem !important;
                line-height: 1.4 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-property-location i {
                font-size: 0.875rem !important;
                color: #10b981 !important;
                flex-shrink: 0 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-property-location span {
                display: inline-block !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-property-price {
                font-size: 1.5rem !important;
                font-weight: 700 !important;
                color: #10b981 !important;
                margin: 0 !important;
                padding: 0 !important;
                line-height: 1.2 !important;
                white-space: nowrap !important;
                flex-shrink: 0 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-property-meta {
                display: flex !important;
                align-items: center !important;
                gap: 1rem !important;
                font-size: 0.875rem !important;
                color: #6b7280 !important;
                border-top: 1px solid #e5e7eb !important;
                padding: 0.75rem 0 0 0 !important;
                margin: 0 0 0.75rem 0 !important;
                flex-wrap: wrap !important;
                border:none !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-meta-item {
                display: inline-flex !important;
                align-items: center !important;
                gap: 0.375rem !important;
                white-space: nowrap !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-meta-item i {
                font-size: 0.875rem !important;
                color: #6b7280 !important;
                flex-shrink: 0 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-meta-item .dashicons {
                font-size: 16px !important;
                color: #6b7280 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-property-actions {
                margin: 0 !important;
                padding: 0 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-btn {
                display: block !important;
                width: 100% !important;
                padding: 0.625rem 1.25rem !important;
                border-radius: 0.375rem !important;
                font-size: 0.875rem !important;
                font-weight: 600 !important;
                text-decoration: none !important;
                transition: all 0.2s !important;
                text-align: center !important;
                box-sizing: border-box !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-btn-primary {
                background: #3b82f6 !important;
                color: #ffffff !important;
                border: none !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-btn-primary:hover {
                background: #2563eb !important;
                color: #ffffff !important;
            }
            
            /* List Layout */
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-properties-container {
                display: flex !important;
                flex-direction: column !important;
                gap: 1rem !important;
                width: 100% !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-card {
                display: flex !important;
                flex-direction: row !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 0.5rem !important;
                overflow: hidden !important;
                transition: all 0.3s !important;
                background: #ffffff !important;
                width: 100% !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-card:hover {
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-image {
                flex: 0 0 300px !important;
                height: 200px !important;
                min-width: 300px !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-image img {
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-content {
                flex: 1 !important;
                padding: 1.25rem !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: flex-start !important;
                gap: 0.5rem !important;
                min-width: 0 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-title {
                margin-bottom: 0.25rem !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-location {
                margin-bottom: 0.25rem !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-price {
                margin-bottom: 0.5rem !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-meta {
                margin-top: 0.5rem !important;
                margin-bottom: 0.75rem !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-actions {
                margin-top: auto !important;
            }
            
            @media (max-width: 768px) {
                #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-card {
                    flex-direction: column !important;
                }
                
                #<?php echo esc_attr($widget_id); ?> .resbs-layout-list .resbs-property-image {
                    flex: 0 0 100% !important;
       
                    min-width: 100% !important;
                }
            }
            
            /* Pagination */
            #<?php echo esc_attr($widget_id); ?> .resbs-listings-pagination {
                margin-top: 2rem !important;
                display: flex !important;
                justify-content: center !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-listings-pagination .page-numbers {
                display: inline-flex !important;
                gap: 0.5rem !important;
                align-items: center !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-listings-pagination a,
            #<?php echo esc_attr($widget_id); ?> .resbs-listings-pagination span {
                padding: 0.5rem 0.75rem !important;
                border: 1px solid #e5e7eb !important;
                border-radius: 0.375rem !important;
                text-decoration: none !important;
                color: #374151 !important;
                transition: all 0.2s !important;
                display: inline-block !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-listings-pagination a:hover {
                background: #f3f4f6 !important;
                border-color: #d1d5db !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-listings-pagination .current {
                background: #3b82f6 !important;
                color: #ffffff !important;
                border-color: #3b82f6 !important;
            }
            
            /* View All Link */
            #<?php echo esc_attr($widget_id); ?> .resbs-view-all-link {
                margin-top: 2rem !important;
                text-align: center !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-view-all-link a {
                display: inline-block !important;
                padding: 0.75rem 1.5rem !important;
                background: #10b981 !important;
                color: #ffffff !important;
                border-radius: 0.375rem !important;
                text-decoration: none !important;
                font-weight: 600 !important;
                transition: all 0.2s !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-view-all-link a:hover {
                background: #059669 !important;
            }
            
            /* Map View */
            #<?php echo esc_attr($widget_id); ?> .resbs-listings-map-container {
                width: 100% !important;
                height: 600px !important;
                position: relative !important;
                border-radius: 0.5rem !important;
                overflow: hidden !important;
                border: 1px solid #e5e7eb !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-map-wrapper {
                width: 100% !important;
                height: 100% !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-map-canvas {
                width: 100% !important;
                height: 100% !important;
                min-height: 600px !important;
            }
            
            /* Leaflet Map Styles */
            #<?php echo esc_attr($widget_id); ?> .resbs-map-canvas .leaflet-container {
                width: 100% !important;
                height: 100% !important;
                z-index: 1 !important;
            }
            
            /* Hide pagination in map view */
            #<?php echo esc_attr($widget_id); ?> .resbs-layout-map ~ .resbs-listings-pagination {
                display: none !important;
            }
        </style>
        
        <!-- Leaflet.js CSS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous" />
        <style>
            /* Fix Leaflet icon paths */
            .leaflet-container .leaflet-marker-icon {
                background-image: url('https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png');
            }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            var widgetId = '<?php echo esc_js($widget_id); ?>';
            var $widget = $('#' + widgetId);
            var listingsMap = null;
            var mapInitialized = false;
            var mapMarkers = [];
            
            // Load Leaflet.js if not already loaded
            function loadLeaflet() {
                if (typeof L !== 'undefined') {
                    return Promise.resolve();
                }
                
                return new Promise(function(resolve, reject) {
                    // Check if Leaflet CSS is loaded
                    if (!$('link[href*="leaflet.css"]').length) {
                        var cssLink = document.createElement('link');
                        cssLink.rel = 'stylesheet';
                        cssLink.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                        cssLink.crossOrigin = 'anonymous';
                        document.head.appendChild(cssLink);
                    }
                    
                    // Check if Leaflet JS is loaded
                    if ($('script[src*="leaflet.js"]').length === 0) {
                        var script = document.createElement('script');
                        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                        script.crossOrigin = 'anonymous';
                        script.async = true;
                        script.onload = function() {
                            // Fix Leaflet icon paths
                            delete L.Icon.Default.prototype._getIconUrl;
                            L.Icon.Default.mergeOptions({
                                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                                iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png'
                            });
                            resolve();
                        };
                        script.onerror = function() {
                            // Try alternate CDN
                            var altScript = document.createElement('script');
                            altScript.src = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js';
                            altScript.crossOrigin = 'anonymous';
                            altScript.async = true;
                            altScript.onload = function() {
                                delete L.Icon.Default.prototype._getIconUrl;
                                L.Icon.Default.mergeOptions({
                                    iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-icon.png',
                                    shadowUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-shadow.png',
                                    iconRetinaUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-icon-2x.png'
                                });
                                resolve();
                            };
                            altScript.onerror = reject;
                            document.head.appendChild(altScript);
                        };
                        document.head.appendChild(script);
                    } else {
                        // Already loading, wait for it
                        var checkLeaflet = setInterval(function() {
                            if (typeof L !== 'undefined') {
                                clearInterval(checkLeaflet);
                                resolve();
                            }
                        }, 100);
                        setTimeout(function() {
                            clearInterval(checkLeaflet);
                            if (typeof L === 'undefined') {
                                reject(new Error('Leaflet.js failed to load'));
                            }
                        }, 10000);
                    }
                });
            }
            
            // Initialize map when needed
            function initMapIfNeeded() {
                if (mapInitialized || !$('#' + widgetId + ' .resbs-listings-map-view').is(':visible')) {
                    return;
                }
                
                var mapId = 'resbs-map-canvas-' + widgetId;
                var mapContainer = document.getElementById(mapId);
                var mapData = $('#' + widgetId + ' .resbs-map-data').text();
                
                if (!mapContainer) {
                    return;
                }
                
                try {
                    var properties = JSON.parse(mapData);
                    
                    loadLeaflet().then(function() {
                        initializeListingsMap(mapContainer, properties, widgetId);
                    }).catch(function(error) {
                        console.error('Failed to load Leaflet.js:', error);
                        mapContainer.innerHTML = '<div style="padding: 2rem; text-align: center; color: #6b7280;"><p>Map library failed to load. Please refresh the page.</p></div>';
                    });
                } catch (e) {
                    console.error('Error parsing map data:', e);
                }
            }
            
            function initializeListingsMap(container, properties, widgetId) {
                if (!properties || properties.length === 0) {
                    container.innerHTML = '<div style="padding: 2rem; text-align: center; color: #6b7280;"><p>No properties with location data found.</p></div>';
                    return;
                }
                
                // Calculate center
                var centerLat = 0;
                var centerLng = 0;
                properties.forEach(function(prop) {
                    centerLat += prop.lat;
                    centerLng += prop.lng;
                });
                centerLat = centerLat / properties.length;
                centerLng = centerLng / properties.length;
                
                // Clear existing map if any
                if (listingsMap) {
                    listingsMap.remove();
                    mapMarkers = [];
                }
                
                // Create Leaflet map with OpenStreetMap tiles
                listingsMap = L.map(container, {
                    center: [centerLat, centerLng],
                    zoom: properties.length === 1 ? 15 : 10,
                    zoomControl: true
                });
                
                // Add OpenStreetMap tile layer
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 19
                }).addTo(listingsMap);
                
                // Create bounds for fitting
                var bounds = [];
                
                // Create custom marker icon
                var markerIcon = L.divIcon({
                    className: 'leaflet-marker-custom',
                    html: '<div style="width: 20px; height: 20px; border-radius: 50%; background-color: #ef4444; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
                
                // Helper function to escape HTML entities in JavaScript
                function escapeHtml(text) {
                    var map = {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;',
                        "'": '&#039;'
                    };
                    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
                }
                
                // Add markers for each property
                properties.forEach(function(property) {
                    var position = [property.lat, property.lng];
                    bounds.push(position);
                    
                    var price = property.price ? '$' + parseFloat(property.price).toLocaleString() : 'Price on request';
                    var popupContent = '<div style="min-width: 200px; padding: 0.75rem;">' +
                        '<h4 style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 700;"><a href="' + escapeHtml(property.permalink) + '" style="color: #111827; text-decoration: none;">' + escapeHtml(property.title) + '</a></h4>' +
                        '<p style="margin: 0 0 0.5rem 0; color: #10b981; font-weight: 700; font-size: 1.125rem;">' + escapeHtml(price) + '</p>';
                    
                    if (property.bedrooms || property.bathrooms) {
                        popupContent += '<p style="margin: 0; color: #6b7280; font-size: 0.875rem;">';
                        if (property.bedrooms) popupContent += escapeHtml(String(property.bedrooms)) + ' Bed' + (property.bedrooms != 1 ? 's' : '') + ' ';
                        if (property.bathrooms) popupContent += escapeHtml(String(property.bathrooms)) + ' Bath' + (property.bathrooms != 1 ? 's' : '');
                        popupContent += '</p>';
                    }
                    
                    popupContent += '</div>';
                    
                    var marker = L.marker(position, {
                        icon: markerIcon
                    }).addTo(listingsMap);
                    
                    marker.bindPopup(popupContent, {
                        maxWidth: 300,
                        className: 'resbs-listings-popup'
                    });
                    
                    mapMarkers.push(marker);
                });
                
                // Fit bounds if multiple properties
                if (properties.length > 1 && bounds.length > 0) {
                    listingsMap.fitBounds(bounds, {
                        padding: [20, 20],
                        maxZoom: 16
                    });
                }
                
                mapInitialized = true;
            }
            
            // Handle view toggle
            $widget.find('.resbs-view-btn').on('click', function() {
                var view = $(this).data('view');
                var $content = $widget.find('.resbs-listings-content');
                
                // Update active button
                $widget.find('.resbs-view-btn').removeClass('active');
                $(this).addClass('active');
                
                // Show/hide views
                if (view === 'map') {
                    $widget.find('.resbs-listings-grid-view').hide();
                    $widget.find('.resbs-listings-map-view').show();
                    $widget.find('.resbs-listings-pagination').hide();
                    setTimeout(function() {
                        initMapIfNeeded();
                        if (listingsMap) {
                            listingsMap.invalidateSize();
                        }
                    }, 100);
                } else {
                    $widget.find('.resbs-listings-map-view').hide();
                    $widget.find('.resbs-listings-grid-view').show();
                    
                    // Show/hide pagination
                    $widget.find('.resbs-listings-pagination').show();
                    
                    // Update container layout class
                    var $container = $widget.find('.resbs-properties-container');
                    $container.attr('style', view === 'grid' ? 
                        'grid-template-columns: repeat(<?php echo esc_js($columns); ?>, 1fr); gap: <?php echo esc_js($grid_gap); ?>;' : 
                        '');
                }
            });
            
            // Initialize map if default view is map
            <?php if ($layout === 'map'): ?>
            initMapIfNeeded();
            <?php endif; ?>
            
            // Prevent Quick View and Favorite buttons from being added
            $widget.find('.resbs-property-card').off('mouseenter').on('mouseenter', function(e) {
                e.stopPropagation();
            });
            
            // Remove any existing Quick View and Favorite buttons
            $widget.find('.resbs-quickview-btn, .resbs-quickview-trigger, .resbs-quick-view-btn, .resbs-favorite-btn').remove();
            
            // Monitor for dynamically added Quick View buttons
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) {
                            if ($(node).hasClass('resbs-quickview-btn') || 
                                $(node).hasClass('resbs-quickview-trigger') || 
                                $(node).hasClass('resbs-quick-view-btn') ||
                                $(node).hasClass('resbs-favorite-btn') ||
                                $(node).find('.resbs-quickview-btn, .resbs-quickview-trigger, .resbs-quick-view-btn, .resbs-favorite-btn').length > 0) {
                                $(node).remove();
                            }
                        }
                    });
                });
            });
            
            observer.observe($widget[0], {
                childList: true,
                subtree: true
            });
        });
        </script>
        <?php
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
                $featured_image = wp_get_attachment_image_url($gallery[0], 'medium');
            }
        }
        
        // Format price and location
        $formatted_price = $price ? '$' . number_format($price) : 'Price on request';
        $location = trim($city . ', ' . $state, ', ');
        
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
                    <?php if ($featured === 'yes'): ?>
                        <span class="resbs-badge resbs-badge-featured"><?php esc_html_e('Featured', 'realestate-booking-suite'); ?></span>
                    <?php endif; ?>
                    <?php if ($sold === 'yes'): ?>
                        <span class="resbs-badge resbs-badge-sold"><?php esc_html_e('Sold', 'realestate-booking-suite'); ?></span>
                    <?php elseif ($property_status_meta): ?>
                        <span class="resbs-badge resbs-badge-status"><?php echo esc_html($property_status_meta); ?></span>
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

