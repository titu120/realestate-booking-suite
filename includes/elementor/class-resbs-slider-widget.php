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
        $columns = isset($settings['columns']) ? intval($settings['columns']) : 3;
        $grid_gap = isset($settings['grid_gap']['size']) ? $settings['grid_gap']['size'] . $settings['grid_gap']['unit'] : '1.5rem';
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
        <style>
            /* Grid Layout */
            #<?php echo esc_attr($widget_id); ?> .resbs-slider-grid {
                display: grid !important;
                width: 100% !important;
            }
            
            @media (max-width: 1024px) {
                #<?php echo esc_attr($widget_id); ?> .resbs-slider-grid {
                    grid-template-columns: repeat(2, 1fr) !important;
                }
            }
            
            @media (max-width: 768px) {
                #<?php echo esc_attr($widget_id); ?> .resbs-slider-grid {
                    grid-template-columns: 1fr !important;
                }
            }
            
            /* Header */
            #<?php echo esc_attr($widget_id); ?> .resbs-slider-header {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                margin-bottom: 1.5rem !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-slider-title {
                font-size: 1.5rem !important;
                font-weight: 700 !important;
                color: #111827 !important;
                margin: 0 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-slider-nav {
                display: flex !important;
                gap: 0.5rem !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-slider-arrow {
                width: 36px !important;
                height: 36px !important;
                border: 1px solid #e5e7eb !important;
                background: #ffffff !important;
                border-radius: 0.375rem !important;
                cursor: pointer !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                transition: all 0.2s !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-slider-arrow:hover {
                background: #f3f4f6 !important;
                border-color: #d1d5db !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .resbs-slider-arrow .dashicons {
                font-size: 18px !important;
                color: #374151 !important;
            }
            
            /* Property Card */
            #<?php echo esc_attr($widget_id); ?> .property-card {
                border: 1px solid #e5e7eb !important;
                border-radius: 0.5rem !important;
                overflow: hidden !important;
                transition: all 0.3s !important;
                display: block !important;
                background: #ffffff !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .property-card:hover {
                transform: translateY(-5px) !important;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1) !important;
            }
            
            /* Property Image */
            #<?php echo esc_attr($widget_id); ?> .property-image {
                position: relative !important;
                display: block !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .property-image img {
                width: 100% !important;
  
                object-fit: cover !important;
                display: block !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .property-image .bg-gray-200 {
                background-color: #e5e7eb !important;
    
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                width: 100% !important;
            }
            
            /* Property Badge */
            #<?php echo esc_attr($widget_id); ?> .property-badge {
                position: absolute !important;
                top: 0.75rem !important;
                left: 0.75rem !important;
                background-color: #10b981 !important;
                color: #ffffff !important;
                padding: 0.25rem 0.75rem !important;
                border-radius: 9999px !important;
                font-size: 0.75rem !important;
                font-weight: 600 !important;
                display: inline-block !important;
                text-transform: uppercase !important;
            }
            
            /* Property Info */
            #<?php echo esc_attr($widget_id); ?> .property-info {
                padding: 1rem !important;
                display: block !important;
            }
            
            /* Property Card Title */
            #<?php echo esc_attr($widget_id); ?> .property-card-title {
                font-size: 1.125rem !important;
                font-weight: 700 !important;
                margin-bottom: 0.5rem !important;
                display: block !important;
                line-height: 1.5 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .property-card-title a {
                color: #111827 !important;
                text-decoration: none !important;
                transition: color 0.3s !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .property-card-title a:hover {
                color: #10b981 !important;
            }
            
            /* Property Card Location */
            #<?php echo esc_attr($widget_id); ?> .property-card-location {
                color: #6b7280 !important;
                font-size: 0.875rem !important;
                margin-bottom: 0.75rem !important;
                display: flex !important;
                align-items: center !important;
                gap: 0.5rem !important;
                line-height: 1.5 !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .property-card-location i {
                color: #10b981 !important;
            }
            
            /* Property Card Price */
            #<?php echo esc_attr($widget_id); ?> .property-card-price {
                font-size: 1.5rem !important;
                font-weight: 700 !important;
                color: #10b981 !important;
                margin-bottom: 0.75rem !important;
                display: block !important;
                line-height: 1.5 !important;
            }
            
            /* Property Card Features */
            #<?php echo esc_attr($widget_id); ?> .property-card-features {
                display: flex !important;
                align-items: center !important;
                gap: 1rem !important;
                font-size: 0.875rem !important;
                color: #6b7280 !important;
                border-top: 1px solid #e5e7eb !important;
                padding-top: 0.75rem !important;
                flex-wrap: wrap !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .property-card-features span {
                display: inline-flex !important;
                align-items: center !important;
                gap: 0.25rem !important;
            }
            
            #<?php echo esc_attr($widget_id); ?> .property-card-features i {
                color: #6b7280 !important;
            }
        </style>
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
        $area_sqft = get_post_meta($property_id, '_property_area_sqft', true);
        $city = get_post_meta($property_id, '_property_city', true);
        $state = get_post_meta($property_id, '_property_state', true);
        $property_status_meta = get_post_meta($property_id, '_property_status', true);
        $featured_image = get_the_post_thumbnail_url($property_id, 'medium');
        
        $formatted_price = $price ? '$' . number_format($price) : 'Price on request';
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
                        <i class="fas fa-map-marker-alt text-emerald-500"></i>
                        <?php echo esc_html($location); ?>
                    </p>
                <?php endif; ?>
                <div class="property-card-price"><?php echo esc_html($formatted_price); ?></div>
                <div class="property-card-features">
                    <?php if ($bedrooms): ?>
                        <span><i class="fas fa-bed mr-1"></i><?php echo esc_html($bedrooms); ?> Bed<?php echo $bedrooms != 1 ? 's' : ''; ?></span>
                    <?php endif; ?>
                    <?php if ($bathrooms): ?>
                        <span><i class="fas fa-bath mr-1"></i><?php echo esc_html($bathrooms); ?> Bath<?php echo $bathrooms != 1 ? 's' : ''; ?></span>
                    <?php endif; ?>
                    <?php if ($area_sqft): ?>
                        <span><i class="fas fa-ruler-combined mr-1"></i><?php echo esc_html($area_sqft); ?> sqft</span>
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

