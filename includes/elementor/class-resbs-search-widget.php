<?php
/**
 * Elementor Property Search Widget
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
if (class_exists('RESBS_Search_Widget')) {
    return;
}

class RESBS_Search_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'resbs-search';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return esc_html__('Property Search', 'realestate-booking-suite');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-search';
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
        return array('property', 'search', 'filter', 'real estate', 'find');
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {
        // Security: Check if user can edit with Elementor
        if (!current_user_can('edit_posts')) {
            return;
        }
        
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
                'default' => esc_html__('Find Your Perfect Home', 'realestate-booking-suite'),
                'placeholder' => esc_html__('Enter search form title', 'realestate-booking-suite'),
            )
        );

        $this->add_control(
            'search_type',
            array(
                'label' => esc_html__('Search Type', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'advanced',
                'options' => array(
                    'simple' => esc_html__('Simple', 'realestate-booking-suite'),
                    'advanced' => esc_html__('Advanced', 'realestate-booking-suite'),
                    'main' => esc_html__('Main', 'realestate-booking-suite'),
                ),
            )
        );

        $this->add_control(
            'background_color',
            array(
                'label' => esc_html__('Background Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-search-form' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'enable_saved_search',
            array(
                'label' => esc_html__('Enable Saved Search', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'search_results_page',
            array(
                'label' => esc_html__('Search Results Page', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'default',
                'options' => $this->get_pages_list(),
            )
        );

        $this->add_control(
            'search_by_location',
            array(
                'label' => esc_html__('Search by Location', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->end_controls_section();

        // Simple & Main Search Fields
        $this->start_controls_section(
            'simple_fields_section',
            array(
                'label' => esc_html__('Simple & Main Search Fields', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'show_address_field',
            array(
                'label' => esc_html__('Show Address Field', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_price_field',
            array(
                'label' => esc_html__('Show Price Fields', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->end_controls_section();

        // Advanced Search Fields
        $this->start_controls_section(
            'advanced_fields_section',
            array(
                'label' => esc_html__('Advanced Search Fields', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => array(
                    'search_type' => 'advanced',
                ),
            )
        );

        $this->add_control(
            'show_category_field',
            array(
                'label' => esc_html__('Show Category Field', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_type_field',
            array(
                'label' => esc_html__('Show Type Field', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_bedrooms_field',
            array(
                'label' => esc_html__('Show Bedrooms Field', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_bathrooms_field',
            array(
                'label' => esc_html__('Show Bathrooms Field', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_half_baths_field',
            array(
                'label' => esc_html__('Show Half Baths Field', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->add_control(
            'show_area_field',
            array(
                'label' => esc_html__('Show Area Field', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_lot_size_field',
            array(
                'label' => esc_html__('Show Lot Size Field', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );

        $this->add_control(
            'show_amenities_field',
            array(
                'label' => esc_html__('Show Amenities Field', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->add_control(
            'show_features_field',
            array(
                'label' => esc_html__('Show Features Field', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->add_control(
            'show_floors_field',
            array(
                'label' => esc_html__('Show Floors Field', 'realestate-booking-suite'),
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

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'title_typography',
                'label' => esc_html__('Title Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-search-title',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label' => esc_html__('Title Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-search-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'form_padding',
            array(
                'label' => esc_html__('Form Padding', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .resbs-search-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'form_border',
                'label' => esc_html__('Form Border', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-search-form',
            )
        );

        $this->add_control(
            'form_border_radius',
            array(
                'label' => esc_html__('Form Border Radius', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .resbs-search-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Get pages list
     * 
     * Security: Checks user capability to read pages
     */
    private function get_pages_list() {
        $pages = array('default' => esc_html__('WP Default', 'realestate-booking-suite'));
        
        // Security: Check if user can read pages
        if (!current_user_can('read')) {
            return $pages;
        }
        
        $all_pages = get_pages();
        
        foreach ($all_pages as $page) {
            $pages[$page->ID] = esc_html($page->post_title);
        }
        
        return $pages;
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $title = sanitize_text_field($settings['title']);
        $search_type = sanitize_text_field($settings['search_type']);
        $enable_saved_search = $settings['enable_saved_search'] === 'yes';
        $search_results_page = sanitize_text_field($settings['search_results_page']);
        $search_by_location = $settings['search_by_location'] === 'yes';
        
        // Field visibility
        $show_address = $settings['show_address_field'] === 'yes';
        $show_price = $settings['show_price_field'] === 'yes';
        $show_category = $settings['show_category_field'] === 'yes';
        $show_type = $settings['show_type_field'] === 'yes';
        $show_bedrooms = $settings['show_bedrooms_field'] === 'yes';
        $show_bathrooms = $settings['show_bathrooms_field'] === 'yes';
        $show_half_baths = $settings['show_half_baths_field'] === 'yes';
        $show_area = $settings['show_area_field'] === 'yes';
        $show_lot_size = $settings['show_lot_size_field'] === 'yes';
        $show_amenities = $settings['show_amenities_field'] === 'yes';
        $show_features = $settings['show_features_field'] === 'yes';
        $show_floors = $settings['show_floors_field'] === 'yes';
        
        $widget_id = 'resbs-search-' . $this->get_id();
        $results_url = $search_results_page !== 'default' ? get_permalink($search_results_page) : get_post_type_archive_link('property');
        
        // Security: Create nonce for search alerts AJAX (if save search is enabled and user is logged in)
        $search_alerts_nonce = '';
        if ($enable_saved_search && is_user_logged_in()) {
            $search_alerts_nonce = wp_create_nonce('resbs_search_alerts_nonce');
        }
        
        ?>
        <div class="resbs-search-widget" id="<?php echo esc_attr($widget_id); ?>">
            <div class="resbs-search-form resbs-search-type-<?php echo esc_attr($search_type); ?>">
                <?php if (!empty($title)): ?>
                    <h3 class="resbs-search-title"><?php echo esc_html($title); ?></h3>
                <?php endif; ?>
                
                <form class="resbs-search-form-inner" action="<?php echo esc_url($results_url); ?>" method="get">
                    <?php 
                    // Security: Add nonce for form submission and AJAX requests
                    wp_nonce_field('resbs_search_form', 'resbs_search_nonce');
                    ?>
                    
                    <div class="resbs-search-fields">
                        <?php if ($show_address && $search_by_location): ?>
                            <div class="resbs-search-field resbs-field-address">
                                <label><?php esc_html_e('Address, City, ZIP', 'realestate-booking-suite'); ?></label>
                                <div class="resbs-input-group">
                                    <input type="text" name="keyword" placeholder="<?php esc_attr_e('Enter address, city, or ZIP', 'realestate-booking-suite'); ?>" class="resbs-search-input">
                                    <button type="button" class="resbs-search-location-btn">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($show_price): ?>
                            <div class="resbs-search-field-group">
                                <div class="resbs-search-field resbs-field-price-min">
                                    <label><?php esc_html_e('Min Price', 'realestate-booking-suite'); ?></label>
                                    <select name="price_min" class="resbs-search-select">
                                        <option value=""><?php esc_html_e('No min', 'realestate-booking-suite'); ?></option>
                                        <?php
                                        $price_steps = array(0, 50000, 100000, 150000, 200000, 250000, 300000, 350000, 400000, 450000, 500000, 600000, 700000, 800000, 900000, 1000000);
                                        foreach ($price_steps as $price) {
                                            echo '<option value="' . esc_attr($price) . '">$' . esc_html(number_format($price)) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="resbs-search-field resbs-field-price-max">
                                    <label><?php esc_html_e('Max Price', 'realestate-booking-suite'); ?></label>
                                    <select name="price_max" class="resbs-search-select">
                                        <option value=""><?php esc_html_e('No max', 'realestate-booking-suite'); ?></option>
                                        <?php
                                        foreach ($price_steps as $price) {
                                            echo '<option value="' . esc_attr($price) . '">$' . esc_html(number_format($price)) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($search_type === 'advanced'): ?>
                            <?php if ($show_category): ?>
                                <div class="resbs-search-field resbs-field-category">
                                    <label><?php esc_html_e('Category', 'realestate-booking-suite'); ?></label>
                                    <select name="category" class="resbs-search-select">
                                        <option value=""><?php esc_html_e('Select category', 'realestate-booking-suite'); ?></option>
                                        <?php
                                        $categories = get_terms(array(
                                            'taxonomy' => 'property_category',
                                            'hide_empty' => false,
                                        ));
                                        if (!empty($categories) && !is_wp_error($categories)) {
                                            foreach ($categories as $category) {
                                                echo '<option value="' . esc_attr($category->slug) . '">' . esc_html($category->name) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                    <button type="button" class="resbs-select-btn"><?php esc_html_e('yes', 'realestate-booking-suite'); ?></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_type): ?>
                                <div class="resbs-search-field resbs-field-type">
                                    <label><?php esc_html_e('Type', 'realestate-booking-suite'); ?></label>
                                    <select name="property_type" class="resbs-search-select">
                                        <option value=""><?php esc_html_e('All Types', 'realestate-booking-suite'); ?></option>
                                        <?php
                                        $property_types = get_terms(array(
                                            'taxonomy' => 'property_type',
                                            'hide_empty' => false,
                                        ));
                                        if (!empty($property_types) && !is_wp_error($property_types)) {
                                            foreach ($property_types as $type) {
                                                echo '<option value="' . esc_attr($type->slug) . '">' . esc_html($type->name) . '</option>';
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_bedrooms): ?>
                                <div class="resbs-search-field resbs-field-bedrooms">
                                    <label><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label>
                                    <div class="resbs-radio-group">
                                        <label><input type="radio" name="bedrooms" value=""> <?php esc_html_e('Any', 'realestate-booking-suite'); ?></label>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <label><input type="radio" name="bedrooms" value="<?php echo esc_attr($i); ?>"> <?php echo esc_html($i); ?>+</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_bathrooms): ?>
                                <div class="resbs-search-field resbs-field-bathrooms">
                                    <label><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                                    <div class="resbs-radio-group">
                                        <label><input type="radio" name="bathrooms" value=""> <?php esc_html_e('Any', 'realestate-booking-suite'); ?></label>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <label><input type="radio" name="bathrooms" value="<?php echo esc_attr($i); ?>"> <?php echo esc_html($i); ?>+</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_half_baths): ?>
                                <div class="resbs-search-field resbs-field-half-baths">
                                    <label><?php esc_html_e('Half baths', 'realestate-booking-suite'); ?></label>
                                    <div class="resbs-radio-group">
                                        <label><input type="radio" name="half_baths" value=""> <?php esc_html_e('Any', 'realestate-booking-suite'); ?></label>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <label><input type="radio" name="half_baths" value="<?php echo esc_attr($i); ?>"> <?php echo esc_html($i); ?>+</label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_area): ?>
                                <div class="resbs-search-field-group">
                                    <div class="resbs-search-field resbs-field-area-min">
                                        <label><?php esc_html_e('Area, sq ft (Min)', 'realestate-booking-suite'); ?></label>
                                        <select name="area_min" class="resbs-search-select">
                                            <option value=""><?php esc_html_e('No min', 'realestate-booking-suite'); ?></option>
                                            <?php
                                            $area_steps = array(500, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 5000);
                                            foreach ($area_steps as $area) {
                                                echo '<option value="' . esc_attr($area) . '">' . esc_html(number_format($area)) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="resbs-search-field resbs-field-area-max">
                                        <label><?php esc_html_e('Area, sq ft (Max)', 'realestate-booking-suite'); ?></label>
                                        <select name="area_max" class="resbs-search-select">
                                            <option value=""><?php esc_html_e('No max', 'realestate-booking-suite'); ?></option>
                                            <?php
                                            foreach ($area_steps as $area) {
                                                echo '<option value="' . esc_attr($area) . '">' . esc_html(number_format($area)) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_lot_size): ?>
                                <div class="resbs-search-field-group">
                                    <div class="resbs-search-field resbs-field-lot-size-min">
                                        <label><?php esc_html_e('Lot size, sq ft (Min)', 'realestate-booking-suite'); ?></label>
                                        <select name="lot_size_min" class="resbs-search-select">
                                            <option value=""><?php esc_html_e('No min', 'realestate-booking-suite'); ?></option>
                                            <?php
                                            $lot_size_steps = array(500, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 5000);
                                            foreach ($lot_size_steps as $lot_size) {
                                                echo '<option value="' . esc_attr($lot_size) . '">' . esc_html(number_format($lot_size)) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    
                                    <div class="resbs-search-field resbs-field-lot-size-max">
                                        <label><?php esc_html_e('Lot size, sq ft (Max)', 'realestate-booking-suite'); ?></label>
                                        <select name="lot_size_max" class="resbs-search-select">
                                            <option value=""><?php esc_html_e('No max', 'realestate-booking-suite'); ?></option>
                                            <?php
                                            foreach ($lot_size_steps as $lot_size) {
                                                echo '<option value="' . esc_attr($lot_size) . '">' . esc_html(number_format($lot_size)) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_amenities): ?>
                                <div class="resbs-search-field resbs-field-amenities">
                                    <label><?php esc_html_e('Amenities', 'realestate-booking-suite'); ?></label>
                                    <label><input type="checkbox" name="amenities[]" value="yes"> <?php esc_html_e('Yes', 'realestate-booking-suite'); ?></label>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_features): ?>
                                <div class="resbs-search-field resbs-field-features">
                                    <label><?php esc_html_e('Features', 'realestate-booking-suite'); ?></label>
                                    <label><input type="checkbox" name="features[]" value="yes"> <?php esc_html_e('Yes', 'realestate-booking-suite'); ?></label>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($show_floors): ?>
                                <div class="resbs-search-field-group">
                                    <div class="resbs-search-field resbs-field-floors-min">
                                        <label><?php esc_html_e('Floors (Min)', 'realestate-booking-suite'); ?></label>
                                        <input type="text" name="floors_min" placeholder="<?php esc_attr_e('No min', 'realestate-booking-suite'); ?>" class="resbs-search-input">
                                    </div>
                                    
                                    <div class="resbs-search-field resbs-field-floors-max">
                                        <label><?php esc_html_e('Floors (Max)', 'realestate-booking-suite'); ?></label>
                                        <input type="text" name="floors_max" placeholder="<?php esc_attr_e('No max', 'realestate-booking-suite'); ?>" class="resbs-search-input">
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <div class="resbs-search-submit">
                            <button type="submit" class="resbs-search-btn">
                                <i class="fas fa-search"></i>
                                <?php esc_html_e('Search', 'realestate-booking-suite'); ?>
                            </button>
                            
                            <?php if ($enable_saved_search && is_user_logged_in()): ?>
                                <button type="button" class="resbs-save-search-btn" 
                                        data-nonce="<?php echo esc_attr($search_alerts_nonce); ?>"
                                        data-widget-id="<?php echo esc_attr($widget_id); ?>">
                                    <?php esc_html_e('Save Search', 'realestate-booking-suite'); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor
     * 
     * Security: Checks user capability to edit with Elementor
     */
    protected function content_template() {
        // Security: Check if user can edit with Elementor
        if (!current_user_can('edit_posts')) {
            return;
        }
        ?>
        <div class="resbs-search-widget">
            <div class="resbs-search-form">
                <h3 class="resbs-search-title">{{ settings.title }}</h3>
                <div class="resbs-search-form-inner">
                    <div class="resbs-search-fields">
                        <div class="resbs-search-field">
                            <label><?php esc_html_e('Address, City, ZIP', 'realestate-booking-suite'); ?></label>
                            <input type="text" placeholder="<?php esc_attr_e('Enter address', 'realestate-booking-suite'); ?>" class="resbs-search-input">
                        </div>
                        <div class="resbs-search-field">
                            <label><?php esc_html_e('Min Price', 'realestate-booking-suite'); ?></label>
                            <select class="resbs-search-select">
                                <option><?php esc_html_e('No min', 'realestate-booking-suite'); ?></option>
                            </select>
                        </div>
                        <div class="resbs-search-field">
                            <label><?php esc_html_e('Max Price', 'realestate-booking-suite'); ?></label>
                            <select class="resbs-search-select">
                                <option><?php esc_html_e('No max', 'realestate-booking-suite'); ?></option>
                            </select>
                        </div>
                        <div class="resbs-search-submit">
                            <button type="button" class="resbs-search-btn">
                                <?php esc_html_e('Search', 'realestate-booking-suite'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}

