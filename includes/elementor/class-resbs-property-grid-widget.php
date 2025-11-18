<?php
/**
 * Elementor Property Grid Widget
 * 
 * SECURITY NOTES:
 * ===============
 * 
 * 1. DIRECT ACCESS PREVENTION:
 *    - ABSPATH check at top of file prevents direct file access
 *    - Class redeclaration check prevents conflicts
 * 
 * 2. NONCE REQUIREMENTS:
 *    - Filter Form AJAX: Uses 'resbs_elementor_nonce' (created in class-resbs-elementor.php)
 *      - Nonce is localized to JavaScript as: resbs_elementor_ajax.nonce
 *      - AJAX handler: 'resbs_elementor_load_properties' (handled in class-resbs-frontend.php)
 *      - Nonce verification: wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')
 *      - JavaScript handler: elementor.js -> loadProperties() function (line 112-143)
 *      - Note: Filter form nonce field is not needed as AJAX call includes nonce in request
 * 
 *    - Favorite Button AJAX: Uses 'resbs_elementor_nonce' (created in class-resbs-elementor.php)
 *      - Nonce is localized to JavaScript as: resbs_elementor_ajax.nonce
 *      - AJAX handler: 'resbs_toggle_favorite' (handled in class-resbs-favorites.php)
 *      - Nonce verification: wp_verify_nonce($_POST['nonce'], 'resbs_elementor_nonce')
 *      - JavaScript handler: elementor.js -> toggleFavorite() function (line 397-416)
 * 
 * 3. USER PERMISSIONS:
 *    - Widget Display: No permission check needed (public content)
 *    - Filter Form: No permission check needed (public search functionality)
 *    - Favorite Button: Requires user to be logged in (checked server-side in AJAX handlers)
 *      - Capability check: is_user_logged_in() in AJAX handler
 *      - No admin capabilities required for favorites functionality
 * 
 * 4. DATA SANITIZATION:
 *    - All user input sanitized: sanitize_text_field(), intval(), esc_attr(), esc_html(), esc_url()
 *    - Settings from Elementor are already sanitized by Elementor framework
 *    - Filter inputs sanitized in AJAX handler: sanitize_text_field() for text, intval() for numbers
 *    - Property IDs validated: RESBS_Security::sanitize_property_id() in AJAX handlers
 * 
 * 5. AJAX SECURITY:
 *    - Filter Properties: 
 *      - Nonce verification: Required in AJAX handler (class-resbs-frontend.php::handle_elementor_load_properties)
 *      - Rate limiting: Not required for read-only operations
 *      - Input sanitization: All filter values sanitized before use in queries
 *    - Favorite Button:
 *      - Nonce verification: Required in AJAX handler (class-resbs-favorites.php::ajax_toggle_favorite)
 *      - Rate limiting: RESBS_Security::check_rate_limit() in AJAX handlers
 *      - User authentication: is_user_logged_in() check in AJAX handlers
 *      - Property ID validation: Must be valid post ID of 'property' post type
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
if (class_exists('RESBS_Property_Grid_Widget')) {
    return;
}

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
        return array('property', 'grid', 'real estate', 'booking');
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
                'placeholder' => esc_html__('Enter widget title', 'realestate-booking-suite'),
            )
        );

        $this->add_control(
            'posts_per_page',
            array(
                'label' => esc_html__('Number of Properties', 'realestate-booking-suite'),
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
                    '1' => esc_html__('1 Column', 'realestate-booking-suite'),
                    '2' => esc_html__('2 Columns', 'realestate-booking-suite'),
                    '3' => esc_html__('3 Columns', 'realestate-booking-suite'),
                    '4' => esc_html__('4 Columns', 'realestate-booking-suite'),
                ),
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
                    'list' => esc_html__('List', 'realestate-booking-suite'),
                    'carousel' => esc_html__('Carousel', 'realestate-booking-suite'),
                ),
            )
        );

        $this->end_controls_section();

        // Carousel Section
        $this->start_controls_section(
            'carousel_section',
            array(
                'label' => esc_html__('Carousel Settings', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => array(
                    'layout' => 'carousel',
                ),
            )
        );

        $this->add_control(
            'carousel_autoplay',
            array(
                'label' => esc_html__('Enable Autoplay', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->add_control(
            'carousel_autoplay_speed',
            array(
                'label' => esc_html__('Autoplay Speed (ms)', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3000,
                'min' => 1000,
                'max' => 10000,
                'step' => 500,
                'condition' => array(
                    'carousel_autoplay' => 'yes',
                ),
            )
        );

        $this->add_control(
            'carousel_show_dots',
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
            'carousel_show_arrows',
            array(
                'label' => esc_html__('Show Arrows', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->end_controls_section();

        // Filters Section
        $this->start_controls_section(
            'filters_section',
            array(
                'label' => esc_html__('Filters', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
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
            'show_price_filter',
            array(
                'label' => esc_html__('Show Price Filter', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => array(
                    'show_filters' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_location_filter',
            array(
                'label' => esc_html__('Show Location Filter', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => array(
                    'show_filters' => 'yes',
                ),
            )
        );

        $this->add_control(
            'show_type_filter',
            array(
                'label' => esc_html__('Show Property Type Filter', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => array(
                    'show_filters' => 'yes',
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
                'label' => esc_html__('Style', 'realestate-booking-suite'),
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
        
        foreach ($property_types as $type) {
            $types[$type->slug] = esc_html($type->name);
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
        
        foreach ($property_statuses as $status) {
            $statuses[$status->slug] = esc_html($status->name);
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
        $layout = sanitize_text_field($settings['layout']);
        $carousel_autoplay = $settings['carousel_autoplay'] === 'yes';
        $carousel_autoplay_speed = intval($settings['carousel_autoplay_speed']);
        $carousel_show_dots = $settings['carousel_show_dots'] === 'yes';
        $carousel_show_arrows = $settings['carousel_show_arrows'] === 'yes';
        $show_filters = $settings['show_filters'] === 'yes';
        $show_price_filter = $settings['show_price_filter'] === 'yes';
        $show_location_filter = $settings['show_location_filter'] === 'yes';
        $show_type_filter = $settings['show_type_filter'] === 'yes';
        $show_price = $settings['show_price'] === 'yes';
        $show_meta = $settings['show_meta'] === 'yes';
        $show_excerpt = $settings['show_excerpt'] === 'yes';
        $show_badges = $settings['show_badges'] === 'yes';
        $show_favorite_button = $settings['show_favorite_button'] === 'yes';
        $show_book_button = $settings['show_book_button'] === 'yes';
        $orderby = sanitize_text_field($settings['orderby']);
        $order = sanitize_text_field($settings['order']);
        $property_type = sanitize_text_field($settings['property_type']);
        $property_status = sanitize_text_field($settings['property_status']);
        $featured_only = $settings['featured_only'] === 'yes';
        $widget_style = sanitize_text_field($settings['widget_style']);

        // Generate unique widget ID
        $widget_id = 'resbs-elementor-widget-' . $this->get_id();

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
                 'layout' => $layout,
                 'carousel_autoplay' => $carousel_autoplay,
                 'carousel_autoplay_speed' => $carousel_autoplay_speed,
                 'carousel_show_dots' => $carousel_show_dots,
                 'carousel_show_arrows' => $carousel_show_arrows
             ))); ?>">

            <?php if (!empty($title)): ?>
                <h3 class="resbs-widget-title"><?php echo esc_html($title); ?></h3>
            <?php endif; ?>

            <?php if ($show_filters): ?>
                <div class="resbs-widget-filters">
                    <form class="resbs-filter-form" data-target="<?php echo esc_attr($widget_id); ?>">
                        <?php 
                        // Note: Nonce is not needed in form as AJAX requests include nonce from resbs_elementor_ajax.nonce
                        // The nonce is automatically included in all AJAX requests via elementor.js
                        ?>
                        
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
                                    <input type="number" name="price_min" id="price_min_<?php echo esc_attr($widget_id); ?>" placeholder="<?php esc_attr_e('Min Price', 'realestate-booking-suite'); ?>">
                                </div>

                                <div class="resbs-filter-group">
                                    <label for="price_max_<?php echo esc_attr($widget_id); ?>">
                                        <?php esc_html_e('Max Price', 'realestate-booking-suite'); ?>
                                    </label>
                                    <input type="number" name="price_max" id="price_max_<?php echo esc_attr($widget_id); ?>" placeholder="<?php esc_attr_e('Max Price', 'realestate-booking-suite'); ?>">
                                </div>
                            <?php endif; ?>

                            <div class="resbs-filter-group">
                                <button type="submit" class="resbs-filter-btn">
                                    <?php esc_html_e('Filter', 'realestate-booking-suite'); ?>
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
                            <?php $this->render_properties($settings); ?>
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
                    <?php $this->render_properties($settings); ?>
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
    }

    /**
     * Render properties
     */
    private function render_properties($settings) {
        // Build query args
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => intval($settings['posts_per_page']),
            'orderby' => sanitize_text_field($settings['orderby']),
            'order' => sanitize_text_field($settings['order']),
        );

        // Add meta query for featured properties
        if ($settings['featured_only'] === 'yes') {
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

        $properties = new WP_Query($query_args);

        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $this->render_property_card($settings);
            }
            wp_reset_postdata();
        } else {
            echo '<p class="resbs-no-properties">' . esc_html__('No properties found.', 'realestate-booking-suite') . '</p>';
        }
    }

    /**
     * Render individual property card
     */
    private function render_property_card($settings) {
        $property_id = get_the_ID();
        $property_price = get_post_meta($property_id, '_property_price', true);
        $property_bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $property_bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        // Get area using helper function that handles unit conversion
        $property_area_value = resbs_get_property_area($property_id, '_property_area_sqft');
        // Fallback to _property_size if area not found
        if (empty($property_area_value)) {
            $property_size = get_post_meta($property_id, '_property_size', true);
            if (!empty($property_size)) {
                $property_area_value = resbs_get_area_unit() === 'sqm' ? resbs_convert_area($property_size, 'sqft', 'sqm') : floatval($property_size);
            }
        }
        $property_featured = get_post_meta($property_id, '_property_featured', true);
        $property_status = get_the_terms($property_id, 'property_status');
        $property_type = get_the_terms($property_id, 'property_type');
        $property_location = get_the_terms($property_id, 'property_location');

        $show_price = $settings['show_price'] === 'yes';
        $show_meta = $settings['show_meta'] === 'yes';
        $show_excerpt = $settings['show_excerpt'] === 'yes';
        $show_badges = $settings['show_badges'] === 'yes';
        $show_favorite_button = $settings['show_favorite_button'] === 'yes';
        $show_book_button = $settings['show_book_button'] === 'yes';
        $layout = sanitize_text_field($settings['layout']);
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
                    <div class="resbs-property-badges">
                        <?php 
                        $badge_manager = new RESBS_Badge_Manager();
                        $badges = $badge_manager->get_property_badges($property_id, 'widget');
                        foreach ($badges as $badge): ?>
                            <span class="resbs-badge resbs-badge-<?php echo esc_attr($badge['type']); ?>">
                                <?php echo esc_html($badge['text']); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_favorite_button): ?>
                    <?php 
                    // Security: Favorite button uses AJAX with nonce verification
                    // Nonce: resbs_elementor_ajax.nonce (created in class-resbs-elementor.php)
                    // AJAX handler: resbs_toggle_favorite (class-resbs-favorites.php)
                    // User permission: Requires login (checked server-side in AJAX handler)
                    ?>
                    <button type="button" class="resbs-favorite-btn" data-property-id="<?php echo esc_attr($property_id); ?>" aria-label="<?php esc_attr_e('Add to favorites', 'realestate-booking-suite'); ?>">
                        <span class="dashicons dashicons-heart"></span>
                    </button>
                <?php endif; ?>
            </div>

            <div class="resbs-property-content">
                <h3 class="resbs-property-title">
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <?php echo esc_html(get_the_title()); ?>
                    </a>
                </h3>

                <?php if ($show_price && !empty($property_price)): ?>
                    <div class="resbs-property-price">
                        <?php echo esc_html(resbs_format_price($property_price)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_meta): ?>
                    <div class="resbs-property-meta">
                        <?php if (!empty($property_bedrooms)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-bed-alt"></span>
                                <span><?php echo esc_html($property_bedrooms); ?> <?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_bathrooms)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-bath"></span>
                                <span><?php echo esc_html($property_bathrooms); ?> <?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_area_value)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-admin-home"></span>
                                <span><?php echo esc_html(resbs_format_area($property_area_value)); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_location)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-location"></span>
                                <span><?php echo esc_html($property_location[0]->name); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_excerpt): ?>
                    <div class="resbs-property-excerpt">
                        <?php echo wp_kses_post(wp_trim_words(get_the_excerpt(), 20)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_book_button): ?>
                    <div class="resbs-property-actions">
                        <a href="<?php echo esc_url(get_permalink()); ?>" class="resbs-property-btn primary">
                            <?php esc_html_e('View Details', 'realestate-booking-suite'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
