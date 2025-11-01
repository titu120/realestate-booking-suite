<?php
/**
 * Elementor Property Listings Widget
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
    }

    /**
     * Get pages list
     */
    private function get_pages_list() {
        $pages = array('' => esc_html__('Select Page', 'realestate-booking-suite'));
        $all_pages = get_pages();
        
        foreach ($all_pages as $page) {
            $pages[$page->ID] = $page->post_title;
        }
        
        return $pages;
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
                                <button type="button" class="resbs-view-btn resbs-view-grid <?php echo $layout === 'grid' ? 'active' : ''; ?>" data-view="grid">
                                    <span class="dashicons dashicons-grid-view"></span>
                                </button>
                                <button type="button" class="resbs-view-btn resbs-view-list <?php echo $layout === 'list' ? 'active' : ''; ?>" data-view="list">
                                    <span class="dashicons dashicons-list-view"></span>
                                </button>
                                <button type="button" class="resbs-view-btn resbs-view-map <?php echo $layout === 'map' ? 'active' : ''; ?>" data-view="map">
                                    <span class="dashicons dashicons-location"></span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="resbs-listings-content resbs-layout-<?php echo esc_attr($layout); ?>">
                <?php if ($properties->have_posts()): ?>
                    <div class="resbs-properties-container">
                        <?php while ($properties->have_posts()): $properties->the_post(); ?>
                            <?php $this->render_property_card($layout); ?>
                        <?php endwhile; ?>
                        <?php wp_reset_postdata(); ?>
                    </div>
                <?php else: ?>
                    <p class="resbs-no-properties"><?php esc_html_e('No properties found.', 'realestate-booking-suite'); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (!$disable_pagination && $properties->max_num_pages > 1): ?>
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
    }

    /**
     * Render property card
     */
    private function render_property_card($layout = 'grid') {
        $property_id = get_the_ID();
        $price = get_post_meta($property_id, '_property_price', true);
        $bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        $area_sqft = get_post_meta($property_id, '_property_area_sqft', true);
        $location = get_the_terms($property_id, 'property_location');
        $property_status = get_the_terms($property_id, 'property_status');
        $featured = get_post_meta($property_id, '_property_featured', true);
        $sold = get_post_meta($property_id, '_property_sold', true);
        
        // Get featured image
        $featured_image = get_the_post_thumbnail_url($property_id, 'medium_large');
        if (!$featured_image) {
            $gallery = get_post_meta($property_id, '_property_gallery', true);
            if ($gallery && is_array($gallery) && !empty($gallery)) {
                $featured_image = wp_get_attachment_image_url($gallery[0], 'medium_large');
            }
        }
        
        // Format price
        $formatted_price = '';
        if ($price) {
            $formatted_price = '$' . number_format(floatval($price));
        }
        
        ?>
        <div class="resbs-property-card resbs-layout-<?php echo esc_attr($layout); ?>" data-property-id="<?php echo esc_attr($property_id); ?>">
            <div class="resbs-property-image">
                <?php if ($featured_image): ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
                    </a>
                <?php else: ?>
                    <a href="<?php echo esc_url(get_permalink()); ?>">
                        <div class="resbs-placeholder-image" style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 18px;">
                            <span class="dashicons dashicons-camera" style="font-size: 48px;"></span>
                        </div>
                    </a>
                <?php endif; ?>
                
                <button type="button" class="resbs-favorite-btn" data-property-id="<?php echo esc_attr($property_id); ?>" aria-label="<?php esc_attr_e('Add to favorites', 'realestate-booking-suite'); ?>">
                    <span class="dashicons dashicons-heart"></span>
                </button>
                
                <div class="resbs-property-badges">
                    <?php if ($featured === 'yes'): ?>
                        <span class="resbs-badge resbs-badge-featured"><?php esc_html_e('Featured', 'realestate-booking-suite'); ?></span>
                    <?php endif; ?>
                    <?php if ($sold === 'yes'): ?>
                        <span class="resbs-badge resbs-badge-sold"><?php esc_html_e('Sold', 'realestate-booking-suite'); ?></span>
                    <?php elseif ($property_status && !empty($property_status)): ?>
                        <span class="resbs-badge resbs-badge-status"><?php echo esc_html($property_status[0]->name); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="resbs-property-content">
                <?php if (!empty($location)): ?>
                    <div class="resbs-property-location">
                        <span class="dashicons dashicons-location"></span>
                        <?php echo esc_html($location[0]->name); ?>
                    </div>
                <?php endif; ?>
                
                <h3 class="resbs-property-title">
                    <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                </h3>
                
                <?php if ($formatted_price): ?>
                    <div class="resbs-property-price"><?php echo esc_html($formatted_price); ?></div>
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
                            <?php echo esc_html(number_format(floatval($area_sqft))); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?>
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="resbs-property-actions">
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="resbs-btn resbs-btn-primary">
                        <?php esc_html_e('View Details', 'realestate-booking-suite'); ?>
                    </a>
                </div>
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
                <h3 class="resbs-listings-title">{{{ settings.title }}}</h3>
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

