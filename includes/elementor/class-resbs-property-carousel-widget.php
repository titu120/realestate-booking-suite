<?php
/**
 * Elementor Property Carousel Widget
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
        return esc_html__('Property Carousel', 'realestate-booking-suite');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-carousel';
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
        return array('property', 'carousel', 'slider', 'real estate', 'booking');
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
            'items_per_view',
            array(
                'label' => esc_html__('Items Per View', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '3',
                'options' => array(
                    '1' => esc_html__('1 Item', 'realestate-booking-suite'),
                    '2' => esc_html__('2 Items', 'realestate-booking-suite'),
                    '3' => esc_html__('3 Items', 'realestate-booking-suite'),
                    '4' => esc_html__('4 Items', 'realestate-booking-suite'),
                ),
            )
        );

        $this->end_controls_section();

        // Carousel Settings Section
        $this->start_controls_section(
            'carousel_section',
            array(
                'label' => esc_html__('Carousel Settings', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'autoplay',
            array(
                'label' => esc_html__('Enable Autoplay', 'realestate-booking-suite'),
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
            'pause_on_hover',
            array(
                'label' => esc_html__('Pause on Hover', 'realestate-booking-suite'),
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
            $types[$type->slug] = $type->name;
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
            $statuses[$status->slug] = $status->name;
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
        $items_per_view = intval($settings['items_per_view']);
        $autoplay = $settings['autoplay'] === 'yes';
        $autoplay_speed = intval($settings['autoplay_speed']);
        $show_dots = $settings['show_dots'] === 'yes';
        $show_arrows = $settings['show_arrows'] === 'yes';
        $infinite_loop = $settings['infinite_loop'] === 'yes';
        $pause_on_hover = $settings['pause_on_hover'] === 'yes';
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
        $widget_id = 'resbs-carousel-widget-' . $this->get_id();

        ?>
        <div class="resbs-property-carousel-widget resbs-style-<?php echo esc_attr($widget_style); ?>" 
             id="<?php echo esc_attr($widget_id); ?>"
             data-settings="<?php echo esc_attr(wp_json_encode(array(
                 'posts_per_page' => $posts_per_page,
                 'items_per_view' => $items_per_view,
                 'autoplay' => $autoplay,
                 'autoplay_speed' => $autoplay_speed,
                 'show_dots' => $show_dots,
                 'show_arrows' => $show_arrows,
                 'infinite_loop' => $infinite_loop,
                 'pause_on_hover' => $pause_on_hover,
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
                 'featured_only' => $featured_only
             ))); ?>">

            <?php if (!empty($title)): ?>
                <h3 class="resbs-widget-title"><?php echo esc_html($title); ?></h3>
            <?php endif; ?>

            <div class="resbs-property-carousel" 
                 data-autoplay="<?php echo esc_attr($autoplay ? 'true' : 'false'); ?>"
                 data-autoplay-speed="<?php echo esc_attr($autoplay_speed); ?>"
                 data-show-dots="<?php echo esc_attr($show_dots ? 'true' : 'false'); ?>"
                 data-show-arrows="<?php echo esc_attr($show_arrows ? 'true' : 'false'); ?>"
                 data-infinite-loop="<?php echo esc_attr($infinite_loop ? 'true' : 'false'); ?>"
                 data-pause-on-hover="<?php echo esc_attr($pause_on_hover ? 'true' : 'false'); ?>"
                 data-items-per-view="<?php echo esc_attr($items_per_view); ?>">
                
                <div class="resbs-carousel-wrapper">
                    <div class="resbs-carousel-track">
                        <?php $this->render_properties($settings); ?>
                    </div>
                </div>
                
                <?php if ($show_arrows): ?>
                    <button class="resbs-carousel-prev" aria-label="<?php esc_attr_e('Previous', 'realestate-booking-suite'); ?>">‹</button>
                    <button class="resbs-carousel-next" aria-label="<?php esc_attr_e('Next', 'realestate-booking-suite'); ?>">›</button>
                <?php endif; ?>
                
                <?php if ($show_dots): ?>
                    <div class="resbs-carousel-dots"></div>
                <?php endif; ?>
            </div>

            <div class="resbs-widget-loading" style="display: none;">
                <div class="resbs-spinner"></div>
                <p><?php esc_html_e('Loading properties...', 'realestate-booking-suite'); ?></p>
            </div>

            <div class="resbs-widget-no-properties" style="display: none;">
                <p><?php esc_html_e('No properties found.', 'realestate-booking-suite'); ?></p>
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
        $property_size = get_post_meta($property_id, '_property_size', true);
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
        ?>

        <div class="resbs-property-card resbs-layout-carousel">
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

                <?php if ($show_price && !empty($property_price)): ?>
                    <div class="resbs-property-price">
                        <?php echo esc_html('$' . number_format($property_price)); ?>
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

                        <?php if (!empty($property_size)): ?>
                            <div class="resbs-property-meta-item">
                                <span class="dashicons dashicons-admin-home"></span>
                                <span><?php echo esc_html($property_size); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></span>
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
                        <?php echo wp_kses_post(wp_trim_words(get_the_excerpt(), 15)); ?>
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
