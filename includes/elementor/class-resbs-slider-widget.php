<?php
/**
 * Elementor Property Slider Widget
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
                'default' => 'horizontal',
                'options' => array(
                    'horizontal' => esc_html__('Horizontal', 'realestate-booking-suite'),
                    'vertical' => esc_html__('Vertical', 'realestate-booking-suite'),
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
        
        $title = sanitize_text_field($settings['title']);
        $layout = sanitize_text_field($settings['layout']);
        $posts_per_page = intval($settings['posts_per_page']);
        $slides_to_show = intval($settings['slides_to_show']);
        $autoplay = $settings['autoplay'] === 'yes';
        $autoplay_speed = intval($settings['autoplay_speed']);
        $show_arrows = $settings['show_arrows'] === 'yes';
        $show_dots = $settings['show_dots'] === 'yes';
        $infinite_loop = $settings['infinite_loop'] === 'yes';
        $orderby = sanitize_text_field($settings['orderby']);
        $order = sanitize_text_field($settings['order']);
        $property_types = $settings['property_type'] ?? array();
        $property_statuses = $settings['property_status'] ?? array();
        $featured_only = $settings['featured_only'] === 'yes';
        
        $widget_id = 'resbs-slider-' . $this->get_id();
        
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
            $query_args['meta_value'] = 'yes';
        }
        
        $meta_query = array();
        if ($featured_only) {
            $meta_query[] = array(
                'key' => '_property_featured',
                'value' => 'yes',
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
                 'layout' => $layout
             ))); ?>">
            
            <?php if (!empty($title)): ?>
                <h3 class="resbs-slider-title"><?php echo esc_html($title); ?></h3>
            <?php endif; ?>
            
            <div class="resbs-slider-container">
                <?php if ($show_arrows): ?>
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
        $area_sqft = get_post_meta($property_id, '_property_area_sqft', true);
        $location = get_the_terms($property_id, 'property_location');
        $property_type = get_the_terms($property_id, 'property_type');
        $property_status = get_the_terms($property_id, 'property_status');
        
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
                
                <button type="button" class="resbs-favorite-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
                    <span class="dashicons dashicons-heart"></span>
                </button>
                
                <?php do_action('resbs_property_badges', $property_id, 'widget'); ?>
            </div>
            
            <div class="resbs-property-content">
                <?php if (!empty($location)): ?>
                    <div class="resbs-property-location"><?php echo esc_html($location[0]->name); ?></div>
                <?php endif; ?>
                
                <h3 class="resbs-property-title">
                    <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                </h3>
                
                <?php if (!empty($price)): ?>
                    <div class="resbs-property-price">$<?php echo esc_html(number_format($price)); ?></div>
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
                    
                    <?php if (!empty($area_sqft)): ?>
                        <span class="resbs-meta-item">
                            <span class="dashicons dashicons-admin-home"></span>
                            <?php echo esc_html($area_sqft); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <?php if (!empty($property_type)): ?>
                    <div class="resbs-property-type">
                        <?php echo esc_html($property_type[0]->name); ?>
                        <?php if (!empty($property_status)): ?>
                            • <?php echo esc_html($property_status[0]->name); ?>
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
            <h3 class="resbs-slider-title">{{{ settings.title }}}</h3>
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

