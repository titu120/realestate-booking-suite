<?php
/**
 * Shortcodes Manager Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Shortcodes {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_shortcodes'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_shortcode_assets'));
    }

    /**
     * Register all shortcodes
     */
    public function register_shortcodes() {
        add_shortcode('resbs_property_grid', array($this, 'property_grid_shortcode'));
        add_shortcode('resbs_property_list', array($this, 'property_list_shortcode'));
        add_shortcode('resbs_search', array($this, 'search_shortcode'));
        add_shortcode('resbs_dashboard', array($this, 'dashboard_shortcode'));
        add_shortcode('resbs_submit_property', array($this, 'submit_property_shortcode'));
        // Note: resbs_favorites shortcode is handled by RESBS_Favorites_Manager class
        // Removed duplicate registration to avoid conflicts
    }

    /**
     * Enqueue shortcode assets
     */
    public function enqueue_shortcode_assets() {
        global $post;
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'resbs_property_grid')) {
            wp_enqueue_style('resbs-layouts');
            wp_enqueue_script('resbs-layouts');
        }
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'resbs_search')) {
            wp_enqueue_style('resbs-maps');
            wp_enqueue_script('resbs-maps');
        }
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'resbs_dashboard')) {
            wp_enqueue_style('resbs-dashboard');
            wp_enqueue_script('resbs-dashboard');
        }
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'resbs_submit_property')) {
            wp_enqueue_style('resbs-forms');
            wp_enqueue_script('resbs-forms');
        }
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'resbs_favorites')) {
            wp_enqueue_style('resbs-favorites');
            wp_enqueue_script('resbs-favorites');
        }
    }

    /**
     * Property Grid Shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function property_grid_shortcode($atts) {
        // Default attributes
        $default_atts = array(
            'title' => esc_html__('Properties', 'realestate-booking-suite'),
            'posts_per_page' => 12,
            'columns' => 3,
            'layout' => 'grid',
            'show_filters' => 'yes',
            'show_price' => 'yes',
            'show_meta' => 'yes',
            'show_excerpt' => 'yes',
            'show_badges' => 'yes',
            'show_favorite_button' => 'yes',
            'show_book_button' => 'yes',
            'orderby' => 'date',
            'order' => 'DESC',
            'property_type' => '',
            'property_status' => '',
            'featured_only' => 'no',
            'carousel_autoplay' => 'no',
            'carousel_autoplay_speed' => 3000,
            'carousel_show_dots' => 'yes',
            'carousel_show_arrows' => 'yes',
            'enable_infinite_scroll' => 'no',
            'show_pagination' => 'yes'
        );

        // Sanitize attributes
        $atts = shortcode_atts($default_atts, $atts, 'resbs_property_grid');
        
        // Sanitize each attribute
        $sanitized_atts = array(
            'title' => RESBS_Security::sanitize_text($atts['title']),
            'posts_per_page' => RESBS_Security::sanitize_int($atts['posts_per_page'], 12),
            'columns' => RESBS_Security::sanitize_int($atts['columns'], 3),
            'layout' => in_array($atts['layout'], array('grid', 'list', 'carousel')) ? $atts['layout'] : 'grid',
            'show_filters' => RESBS_Security::sanitize_bool($atts['show_filters']),
            'show_price' => RESBS_Security::sanitize_bool($atts['show_price']),
            'show_meta' => RESBS_Security::sanitize_bool($atts['show_meta']),
            'show_excerpt' => RESBS_Security::sanitize_bool($atts['show_excerpt']),
            'show_badges' => RESBS_Security::sanitize_bool($atts['show_badges']),
            'show_favorite_button' => RESBS_Security::sanitize_bool($atts['show_favorite_button']),
            'show_book_button' => RESBS_Security::sanitize_bool($atts['show_book_button']),
            'orderby' => in_array($atts['orderby'], array('date', 'title', 'price', 'rand')) ? $atts['orderby'] : 'date',
            'order' => in_array($atts['order'], array('ASC', 'DESC')) ? $atts['order'] : 'DESC',
            'property_type' => RESBS_Security::sanitize_text($atts['property_type']),
            'property_status' => RESBS_Security::sanitize_text($atts['property_status']),
            'featured_only' => RESBS_Security::sanitize_bool($atts['featured_only']),
            'carousel_autoplay' => RESBS_Security::sanitize_bool($atts['carousel_autoplay']),
            'carousel_autoplay_speed' => RESBS_Security::sanitize_int($atts['carousel_autoplay_speed'], 3000),
            'carousel_show_dots' => RESBS_Security::sanitize_bool($atts['carousel_show_dots']),
            'carousel_show_arrows' => RESBS_Security::sanitize_bool($atts['carousel_show_arrows']),
            'enable_infinite_scroll' => RESBS_Security::sanitize_bool($atts['enable_infinite_scroll']),
            'show_pagination' => RESBS_Security::sanitize_bool($atts['show_pagination'])
        );

        // Generate unique ID
        $shortcode_id = 'resbs-shortcode-grid-' . uniqid();

        // Start output buffering
        ob_start();
        ?>
        <div class="resbs-property-grid-widget resbs-shortcode resbs-layout-<?php echo esc_attr($sanitized_atts['layout']); ?>" 
             id="<?php echo esc_attr($shortcode_id); ?>"
             data-settings="<?php echo esc_attr(wp_json_encode($sanitized_atts)); ?>">

            <?php if (!empty($sanitized_atts['title'])): ?>
                <h3 class="resbs-widget-title"><?php echo esc_html($sanitized_atts['title']); ?></h3>
            <?php endif; ?>

            <?php if ($sanitized_atts['show_filters']): ?>
                <div class="resbs-widget-filters">
                    <form class="resbs-filter-form" data-target="<?php echo esc_attr($shortcode_id); ?>">
                        <?php wp_nonce_field('resbs_widget_filter', 'resbs_filter_nonce'); ?>
                        
                        <div class="resbs-filter-row">
                            <div class="resbs-filter-group">
                                <label for="property_type_<?php echo esc_attr($shortcode_id); ?>">
                                    <?php esc_html_e('Property Type', 'realestate-booking-suite'); ?>
                                </label>
                                <select name="property_type" id="property_type_<?php echo esc_attr($shortcode_id); ?>">
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

                            <div class="resbs-filter-group">
                                <label for="location_<?php echo esc_attr($shortcode_id); ?>">
                                    <?php esc_html_e('Location', 'realestate-booking-suite'); ?>
                                </label>
                                <select name="location" id="location_<?php echo esc_attr($shortcode_id); ?>">
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

                            <div class="resbs-filter-group">
                                <label for="price_min_<?php echo esc_attr($shortcode_id); ?>">
                                    <?php esc_html_e('Min Price', 'realestate-booking-suite'); ?>
                                </label>
                                <input type="number" name="price_min" id="price_min_<?php echo esc_attr($shortcode_id); ?>" 
                                       placeholder="<?php esc_attr_e('Min Price', 'realestate-booking-suite'); ?>">
                            </div>

                            <div class="resbs-filter-group">
                                <label for="price_max_<?php echo esc_attr($shortcode_id); ?>">
                                    <?php esc_html_e('Max Price', 'realestate-booking-suite'); ?>
                                </label>
                                <input type="number" name="price_max" id="price_max_<?php echo esc_attr($shortcode_id); ?>" 
                                       placeholder="<?php esc_attr_e('Max Price', 'realestate-booking-suite'); ?>">
                            </div>

                            <div class="resbs-filter-group">
                                <button type="submit" class="resbs-filter-btn">
                                    <?php esc_html_e('Filter', 'realestate-booking-suite'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($sanitized_atts['layout'] === 'carousel'): ?>
                <div class="resbs-property-carousel" 
                     data-autoplay="<?php echo esc_attr($sanitized_atts['carousel_autoplay'] ? 'true' : 'false'); ?>"
                     data-autoplay-speed="<?php echo esc_attr($sanitized_atts['carousel_autoplay_speed']); ?>"
                     data-show-dots="<?php echo esc_attr($sanitized_atts['carousel_show_dots'] ? 'true' : 'false'); ?>"
                     data-show-arrows="<?php echo esc_attr($sanitized_atts['carousel_show_arrows'] ? 'true' : 'false'); ?>">
                    <div class="resbs-carousel-wrapper">
                        <div class="resbs-carousel-track">
                            <?php $this->render_properties($sanitized_atts); ?>
                        </div>
                    </div>
                    <?php if ($sanitized_atts['carousel_show_arrows']): ?>
                        <button class="resbs-carousel-prev" aria-label="<?php esc_attr_e('Previous', 'realestate-booking-suite'); ?>">‹</button>
                        <button class="resbs-carousel-next" aria-label="<?php esc_attr_e('Next', 'realestate-booking-suite'); ?>">›</button>
                    <?php endif; ?>
                    <?php if ($sanitized_atts['carousel_show_dots']): ?>
                        <div class="resbs-carousel-dots"></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="resbs-property-grid resbs-grid-<?php echo esc_attr($sanitized_atts['columns']); ?>-cols resbs-layout-<?php echo esc_attr($sanitized_atts['layout']); ?>" 
                     data-columns="<?php echo esc_attr($sanitized_atts['columns']); ?>"
                     data-layout="<?php echo esc_attr($sanitized_atts['layout']); ?>">
                    <?php $this->render_properties($sanitized_atts); ?>
                </div>
            <?php endif; ?>

            <div class="resbs-widget-loading" style="display: none;">
                <div class="resbs-spinner"></div>
                <p><?php esc_html_e('Loading properties...', 'realestate-booking-suite'); ?></p>
            </div>

            <div class="resbs-widget-no-properties" style="display: none;">
                <p><?php esc_html_e('No properties found matching your criteria.', 'realestate-booking-suite'); ?></p>
            </div>

            <?php if ($sanitized_atts['enable_infinite_scroll']): ?>
                <div class="resbs-infinite-scroll" data-target="<?php echo esc_attr($shortcode_id); ?>">
                    <button class="resbs-load-more-btn">
                        <?php esc_html_e('Load More Properties', 'realestate-booking-suite'); ?>
                    </button>
                </div>
            <?php endif; ?>

            <?php if ($sanitized_atts['show_pagination'] && !$sanitized_atts['enable_infinite_scroll']): ?>
                <div class="resbs-pagination">
                    <?php $this->render_pagination($sanitized_atts); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Property List Shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function property_list_shortcode($atts) {
        // Use property grid shortcode with list layout
        $atts['layout'] = 'list';
        return $this->property_grid_shortcode($atts);
    }

    /**
     * Search Shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function search_shortcode($atts) {
        // Default attributes
        $default_atts = array(
            'title' => esc_html__('Search Properties', 'realestate-booking-suite'),
            'show_map' => 'yes',
            'show_filters' => 'yes',
            'show_results' => 'yes',
            'results_per_page' => 12,
            'map_height' => 400
        );

        // Sanitize attributes
        $atts = shortcode_atts($default_atts, $atts, 'resbs_search');
        
        $sanitized_atts = array(
            'title' => RESBS_Security::sanitize_text($atts['title']),
            'show_map' => RESBS_Security::sanitize_bool($atts['show_map']),
            'show_filters' => RESBS_Security::sanitize_bool($atts['show_filters']),
            'show_results' => RESBS_Security::sanitize_bool($atts['show_results']),
            'results_per_page' => RESBS_Security::sanitize_int($atts['results_per_page'], 12),
            'map_height' => RESBS_Security::sanitize_int($atts['map_height'], 400)
        );

        $shortcode_id = 'resbs-shortcode-search-' . uniqid();

        ob_start();
        ?>
        <div class="resbs-search-widget resbs-shortcode" id="<?php echo esc_attr($shortcode_id); ?>">
            <?php if (!empty($sanitized_atts['title'])): ?>
                <h3 class="resbs-widget-title"><?php echo esc_html($sanitized_atts['title']); ?></h3>
            <?php endif; ?>

            <?php if ($sanitized_atts['show_filters']): ?>
                <div class="resbs-search-filters">
                    <form class="resbs-search-form" data-target="<?php echo esc_attr($shortcode_id); ?>">
                        <?php wp_nonce_field('resbs_search_form', 'resbs_search_nonce'); ?>
                        
                        <div class="resbs-search-row">
                            <div class="resbs-search-group">
                                <label for="search_keyword_<?php echo esc_attr($shortcode_id); ?>">
                                    <?php esc_html_e('Keyword', 'realestate-booking-suite'); ?>
                                </label>
                                <input type="text" name="keyword" id="search_keyword_<?php echo esc_attr($shortcode_id); ?>" 
                                       placeholder="<?php esc_attr_e('Enter location, property type...', 'realestate-booking-suite'); ?>">
                            </div>

                            <div class="resbs-search-group">
                                <label for="search_type_<?php echo esc_attr($shortcode_id); ?>">
                                    <?php esc_html_e('Property Type', 'realestate-booking-suite'); ?>
                                </label>
                                <select name="property_type" id="search_type_<?php echo esc_attr($shortcode_id); ?>">
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

                            <div class="resbs-search-group">
                                <label for="search_location_<?php echo esc_attr($shortcode_id); ?>">
                                    <?php esc_html_e('Location', 'realestate-booking-suite'); ?>
                                </label>
                                <select name="location" id="search_location_<?php echo esc_attr($shortcode_id); ?>">
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

                            <div class="resbs-search-group">
                                <label for="search_price_min_<?php echo esc_attr($shortcode_id); ?>">
                                    <?php esc_html_e('Min Price', 'realestate-booking-suite'); ?>
                                </label>
                                <input type="number" name="price_min" id="search_price_min_<?php echo esc_attr($shortcode_id); ?>" 
                                       placeholder="<?php esc_attr_e('Min Price', 'realestate-booking-suite'); ?>">
                            </div>

                            <div class="resbs-search-group">
                                <label for="search_price_max_<?php echo esc_attr($shortcode_id); ?>">
                                    <?php esc_html_e('Max Price', 'realestate-booking-suite'); ?>
                                </label>
                                <input type="number" name="price_max" id="search_price_max_<?php echo esc_attr($shortcode_id); ?>" 
                                       placeholder="<?php esc_attr_e('Max Price', 'realestate-booking-suite'); ?>">
                            </div>

                            <div class="resbs-search-group">
                                <button type="submit" class="resbs-search-btn">
                                    <?php esc_html_e('Search Properties', 'realestate-booking-suite'); ?>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if ($sanitized_atts['show_map']): ?>
                <div class="resbs-search-map" style="height: <?php echo esc_attr($sanitized_atts['map_height']); ?>px;">
                    <div class="resbs-map-container" id="resbs-search-map-<?php echo esc_attr($shortcode_id); ?>"></div>
                </div>
            <?php endif; ?>

            <?php if ($sanitized_atts['show_results']): ?>
                <div class="resbs-search-results">
                    <div class="resbs-results-header">
                        <h4><?php esc_html_e('Search Results', 'realestate-booking-suite'); ?></h4>
                        <div class="resbs-results-count">
                            <span class="resbs-count-text"><?php esc_html_e('0 properties found', 'realestate-booking-suite'); ?></span>
                        </div>
                    </div>
                    <div class="resbs-results-grid">
                        <!-- Results will be loaded here via AJAX -->
                    </div>
                    <div class="resbs-results-loading" style="display: none;">
                        <div class="resbs-spinner"></div>
                        <p><?php esc_html_e('Searching properties...', 'realestate-booking-suite'); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Dashboard Shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function dashboard_shortcode($atts) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="resbs-dashboard-login-required">
                <p>' . esc_html__('Please log in to access your dashboard.', 'realestate-booking-suite') . '</p>
                <a href="' . esc_url(wp_login_url(get_permalink())) . '" class="resbs-login-btn">
                    ' . esc_html__('Login', 'realestate-booking-suite') . '
                </a>
            </div>';
        }

        // Default attributes
        $default_atts = array(
            'title' => esc_html__('My Dashboard', 'realestate-booking-suite'),
            'show_properties' => 'yes',
            'show_favorites' => 'yes',
            'show_bookings' => 'yes',
            'show_profile' => 'yes'
        );

        // Sanitize attributes
        $atts = shortcode_atts($default_atts, $atts, 'resbs_dashboard');
        
        $sanitized_atts = array(
            'title' => RESBS_Security::sanitize_text($atts['title']),
            'show_properties' => RESBS_Security::sanitize_bool($atts['show_properties']),
            'show_favorites' => RESBS_Security::sanitize_bool($atts['show_favorites']),
            'show_bookings' => RESBS_Security::sanitize_bool($atts['show_bookings']),
            'show_profile' => RESBS_Security::sanitize_bool($atts['show_profile'])
        );

        $user_id = get_current_user_id();
        $shortcode_id = 'resbs-shortcode-dashboard-' . uniqid();

        ob_start();
        ?>
        <div class="resbs-dashboard-widget resbs-shortcode" id="<?php echo esc_attr($shortcode_id); ?>">
            <?php if (!empty($sanitized_atts['title'])): ?>
                <h3 class="resbs-widget-title"><?php echo esc_html($sanitized_atts['title']); ?></h3>
            <?php endif; ?>

            <div class="resbs-dashboard-tabs">
                <nav class="resbs-tab-nav">
                    <?php if ($sanitized_atts['show_properties']): ?>
                        <button class="resbs-tab-btn active" data-tab="properties">
                            <?php esc_html_e('My Properties', 'realestate-booking-suite'); ?>
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($sanitized_atts['show_favorites']): ?>
                        <button class="resbs-tab-btn" data-tab="favorites">
                            <?php esc_html_e('Favorites', 'realestate-booking-suite'); ?>
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($sanitized_atts['show_bookings']): ?>
                        <button class="resbs-tab-btn" data-tab="bookings">
                            <?php esc_html_e('Bookings', 'realestate-booking-suite'); ?>
                        </button>
                    <?php endif; ?>
                    
                    <?php if ($sanitized_atts['show_profile']): ?>
                        <button class="resbs-tab-btn" data-tab="profile">
                            <?php esc_html_e('Profile', 'realestate-booking-suite'); ?>
                        </button>
                    <?php endif; ?>
                </nav>

                <div class="resbs-tab-content">
                    <?php if ($sanitized_atts['show_properties']): ?>
                        <div class="resbs-tab-panel active" id="properties">
                            <div class="resbs-dashboard-section">
                                <h4><?php esc_html_e('My Properties', 'realestate-booking-suite'); ?></h4>
                                <div class="resbs-properties-list">
                                    <?php $this->render_user_properties($user_id); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($sanitized_atts['show_favorites']): ?>
                        <div class="resbs-tab-panel" id="favorites">
                            <div class="resbs-dashboard-section">
                                <h4><?php esc_html_e('Favorite Properties', 'realestate-booking-suite'); ?></h4>
                                <div class="resbs-favorites-list">
                                    <?php $this->render_user_favorites($user_id); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($sanitized_atts['show_bookings']): ?>
                        <div class="resbs-tab-panel" id="bookings">
                            <div class="resbs-dashboard-section">
                                <h4><?php esc_html_e('My Bookings', 'realestate-booking-suite'); ?></h4>
                                <div class="resbs-bookings-list">
                                    <?php $this->render_user_bookings($user_id); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($sanitized_atts['show_profile']): ?>
                        <div class="resbs-tab-panel" id="profile">
                            <div class="resbs-dashboard-section">
                                <h4><?php esc_html_e('Profile Settings', 'realestate-booking-suite'); ?></h4>
                                <div class="resbs-profile-form">
                                    <?php $this->render_profile_form($user_id); ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Submit Property Shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function submit_property_shortcode($atts) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="resbs-submit-login-required">
                <p>' . esc_html__('Please log in to submit a property.', 'realestate-booking-suite') . '</p>
                <a href="' . esc_url(wp_login_url(get_permalink())) . '" class="resbs-login-btn">
                    ' . esc_html__('Login', 'realestate-booking-suite') . '
                </a>
            </div>';
        }

        // Default attributes
        $default_atts = array(
            'title' => esc_html__('Submit Property', 'realestate-booking-suite'),
            'show_gallery' => 'yes',
            'show_map' => 'yes',
            'show_amenities' => 'yes',
            'show_video' => 'yes'
        );

        // Sanitize attributes
        $atts = shortcode_atts($default_atts, $atts, 'resbs_submit_property');
        
        $sanitized_atts = array(
            'title' => RESBS_Security::sanitize_text($atts['title']),
            'show_gallery' => RESBS_Security::sanitize_bool($atts['show_gallery']),
            'show_map' => RESBS_Security::sanitize_bool($atts['show_map']),
            'show_amenities' => RESBS_Security::sanitize_bool($atts['show_amenities']),
            'show_video' => RESBS_Security::sanitize_bool($atts['show_video'])
        );

        $shortcode_id = 'resbs-shortcode-submit-' . uniqid();

        ob_start();
        ?>
        <div class="resbs-submit-widget resbs-shortcode" id="<?php echo esc_attr($shortcode_id); ?>">
            <?php if (!empty($sanitized_atts['title'])): ?>
                <h3 class="resbs-widget-title"><?php echo esc_html($sanitized_atts['title']); ?></h3>
            <?php endif; ?>

            <form class="resbs-submit-form" data-target="<?php echo esc_attr($shortcode_id); ?>">
                <?php wp_nonce_field('resbs_submit_property', 'resbs_submit_nonce'); ?>
                
                <div class="resbs-form-section">
                    <h4><?php esc_html_e('Basic Information', 'realestate-booking-suite'); ?></h4>
                    
                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_title_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Property Title', 'realestate-booking-suite'); ?> <span class="required">*</span>
                            </label>
                            <input type="text" name="property_title" id="property_title_<?php echo esc_attr($shortcode_id); ?>" 
                                   required placeholder="<?php esc_attr_e('Enter property title', 'realestate-booking-suite'); ?>">
                        </div>

                        <div class="resbs-form-group">
                            <label for="property_type_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Property Type', 'realestate-booking-suite'); ?> <span class="required">*</span>
                            </label>
                            <select name="property_type" id="property_type_<?php echo esc_attr($shortcode_id); ?>" required>
                                <option value=""><?php esc_html_e('Select Property Type', 'realestate-booking-suite'); ?></option>
                                <?php
                                $property_types = get_terms(array(
                                    'taxonomy' => 'property_type',
                                    'hide_empty' => false,
                                ));
                                foreach ($property_types as $type) {
                                    echo '<option value="' . esc_attr($type->term_id) . '">' . esc_html($type->name) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_price_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Price', 'realestate-booking-suite'); ?> <span class="required">*</span>
                            </label>
                            <input type="number" name="property_price" id="property_price_<?php echo esc_attr($shortcode_id); ?>" 
                                   required placeholder="<?php esc_attr_e('Enter price', 'realestate-booking-suite'); ?>">
                        </div>

                        <div class="resbs-form-group">
                            <label for="property_size_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Size (sq ft)', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_size" id="property_size_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('Enter size', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>

                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_bedrooms_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?>
                            </label>
                            <select name="property_bedrooms" id="property_bedrooms_<?php echo esc_attr($shortcode_id); ?>">
                                <option value=""><?php esc_html_e('Select Bedrooms', 'realestate-booking-suite'); ?></option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="resbs-form-group">
                            <label for="property_bathrooms_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?>
                            </label>
                            <select name="property_bathrooms" id="property_bathrooms_<?php echo esc_attr($shortcode_id); ?>">
                                <option value=""><?php esc_html_e('Select Bathrooms', 'realestate-booking-suite'); ?></option>
                                <?php for ($i = 1; $i <= 10; $i++): ?>
                                    <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="resbs-form-group">
                        <label for="property_description_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Description', 'realestate-booking-suite'); ?> <span class="required">*</span>
                        </label>
                        <textarea name="property_description" id="property_description_<?php echo esc_attr($shortcode_id); ?>" 
                                  required rows="5" placeholder="<?php esc_attr_e('Enter property description', 'realestate-booking-suite'); ?>"></textarea>
                    </div>
                </div>

                <?php if ($sanitized_atts['show_gallery']): ?>
                    <div class="resbs-form-section">
                        <h4><?php esc_html_e('Property Gallery', 'realestate-booking-suite'); ?></h4>
                        <div class="resbs-gallery-upload">
                            <input type="file" name="property_gallery[]" multiple accept="image/*" class="resbs-gallery-input">
                            <div class="resbs-gallery-preview"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($sanitized_atts['show_map']): ?>
                    <div class="resbs-form-section">
                        <h4><?php esc_html_e('Location', 'realestate-booking-suite'); ?></h4>
                        <div class="resbs-form-row">
                            <div class="resbs-form-group">
                                <label for="property_address_<?php echo esc_attr($shortcode_id); ?>">
                                    <?php esc_html_e('Address', 'realestate-booking-suite'); ?>
                                </label>
                                <input type="text" name="property_address" id="property_address_<?php echo esc_attr($shortcode_id); ?>" 
                                       placeholder="<?php esc_attr_e('Enter address', 'realestate-booking-suite'); ?>">
                            </div>
                            <div class="resbs-form-group">
                                <label for="property_location_<?php echo esc_attr($shortcode_id); ?>">
                                    <?php esc_html_e('Location', 'realestate-booking-suite'); ?>
                                </label>
                                <select name="property_location" id="property_location_<?php echo esc_attr($shortcode_id); ?>">
                                    <option value=""><?php esc_html_e('Select Location', 'realestate-booking-suite'); ?></option>
                                    <?php
                                    $locations = get_terms(array(
                                        'taxonomy' => 'property_location',
                                        'hide_empty' => false,
                                    ));
                                    foreach ($locations as $location) {
                                        echo '<option value="' . esc_attr($location->term_id) . '">' . esc_html($location->name) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="resbs-map-container" style="height: 300px;">
                            <div id="resbs-submit-map-<?php echo esc_attr($shortcode_id); ?>"></div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="resbs-form-actions">
                    <button type="submit" class="resbs-submit-btn">
                        <?php esc_html_e('Submit Property', 'realestate-booking-suite'); ?>
                    </button>
                    <button type="reset" class="resbs-reset-btn">
                        <?php esc_html_e('Reset Form', 'realestate-booking-suite'); ?>
                    </button>
                </div>
            </form>

            <div class="resbs-submit-loading" style="display: none;">
                <div class="resbs-spinner"></div>
                <p><?php esc_html_e('Submitting property...', 'realestate-booking-suite'); ?></p>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Favorites Shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function favorites_shortcode($atts) {
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<div class="resbs-favorites-login-required">
                <p>' . esc_html__('Please log in to view your favorite properties.', 'realestate-booking-suite') . '</p>
                <a href="' . esc_url(wp_login_url(get_permalink())) . '" class="resbs-login-btn">
                    ' . esc_html__('Login', 'realestate-booking-suite') . '
                </a>
            </div>';
        }

        // Default attributes
        $default_atts = array(
            'title' => esc_html__('My Favorite Properties', 'realestate-booking-suite'),
            'layout' => 'grid',
            'columns' => 3,
            'show_price' => 'yes',
            'show_meta' => 'yes',
            'show_excerpt' => 'yes',
            'show_badges' => 'yes'
        );

        // Sanitize attributes
        $atts = shortcode_atts($default_atts, $atts, 'resbs_favorites');
        
        $sanitized_atts = array(
            'title' => RESBS_Security::sanitize_text($atts['title']),
            'layout' => in_array($atts['layout'], array('grid', 'list', 'carousel')) ? $atts['layout'] : 'grid',
            'columns' => RESBS_Security::sanitize_int($atts['columns'], 3),
            'show_price' => RESBS_Security::sanitize_bool($atts['show_price']),
            'show_meta' => RESBS_Security::sanitize_bool($atts['show_meta']),
            'show_excerpt' => RESBS_Security::sanitize_bool($atts['show_excerpt']),
            'show_badges' => RESBS_Security::sanitize_bool($atts['show_badges'])
        );

        $user_id = get_current_user_id();
        $shortcode_id = 'resbs-shortcode-favorites-' . uniqid();

        ob_start();
        ?>
        <div class="resbs-favorites-widget resbs-shortcode resbs-layout-<?php echo esc_attr($sanitized_atts['layout']); ?>" 
             id="<?php echo esc_attr($shortcode_id); ?>">
            <?php if (!empty($sanitized_atts['title'])): ?>
                <h3 class="resbs-widget-title"><?php echo esc_html($sanitized_atts['title']); ?></h3>
            <?php endif; ?>

            <div class="resbs-favorites-actions">
                <button class="resbs-clear-favorites-btn" data-user-id="<?php echo esc_attr($user_id); ?>">
                    <?php esc_html_e('Clear All Favorites', 'realestate-booking-suite'); ?>
                </button>
            </div>

            <div class="resbs-favorites-grid resbs-grid-<?php echo esc_attr($sanitized_atts['columns']); ?>-cols resbs-layout-<?php echo esc_attr($sanitized_atts['layout']); ?>">
                <?php $this->render_user_favorites($user_id, $sanitized_atts); ?>
            </div>

            <div class="resbs-favorites-empty" style="display: none;">
                <p><?php esc_html_e('You haven\'t added any properties to your favorites yet.', 'realestate-booking-suite'); ?></p>
                <a href="<?php echo esc_url(home_url('/properties')); ?>" class="resbs-browse-btn">
                    <?php esc_html_e('Browse Properties', 'realestate-booking-suite'); ?>
                </a>
            </div>

            <div class="resbs-favorites-loading" style="display: none;">
                <div class="resbs-spinner"></div>
                <p><?php esc_html_e('Loading favorites...', 'realestate-booking-suite'); ?></p>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Render properties for shortcodes
     * 
     * @param array $atts Sanitized attributes
     */
    private function render_properties($atts) {
        // Build query args
        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $atts['posts_per_page'],
            'orderby' => $atts['orderby'],
            'order' => $atts['order'],
        );

        // Add meta query for featured properties
        if ($atts['featured_only']) {
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
        
        if (!empty($atts['property_type'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_type',
                'field' => 'slug',
                'terms' => $atts['property_type']
            );
        }

        if (!empty($atts['property_status'])) {
            $tax_query[] = array(
                'taxonomy' => 'property_status',
                'field' => 'slug',
                'terms' => $atts['property_status']
            );
        }

        if (!empty($tax_query)) {
            $query_args['tax_query'] = $tax_query;
        }

        $properties = new WP_Query($query_args);

        if ($properties->have_posts()) {
            while ($properties->have_posts()) {
                $properties->the_post();
                $this->render_property_card($atts);
            }
            wp_reset_postdata();
        } else {
            echo '<p class="resbs-no-properties">' . esc_html__('No properties found.', 'realestate-booking-suite') . '</p>';
        }
    }

    /**
     * Render individual property card
     * 
     * @param array $atts Sanitized attributes
     */
    private function render_property_card($atts) {
        $property_id = get_the_ID();
        $property_price = get_post_meta($property_id, '_property_price', true);
        $property_bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $property_bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        $property_size = get_post_meta($property_id, '_property_size', true);
        $property_featured = get_post_meta($property_id, '_property_featured', true);
        $property_status = get_the_terms($property_id, 'property_status');
        $property_type = get_the_terms($property_id, 'property_type');
        $property_location = get_the_terms($property_id, 'property_location');

        ?>
        <div class="resbs-property-card resbs-layout-<?php echo esc_attr($atts['layout']); ?>">
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

                <?php if ($atts['show_badges']): ?>
                    <?php do_action('resbs_property_badges', $property_id, 'shortcode'); ?>
                <?php endif; ?>

                <?php if ($atts['show_favorite_button']): ?>
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

                <?php if ($atts['show_price'] && !empty($property_price)): ?>
                    <div class="resbs-property-price">
                        <?php echo esc_html('$' . number_format($property_price)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($atts['show_meta']): ?>
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

                <?php if ($atts['show_excerpt']): ?>
                    <div class="resbs-property-excerpt">
                        <?php echo wp_kses_post(wp_trim_words(get_the_excerpt(), 20)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($atts['show_book_button']): ?>
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

    /**
     * Render pagination
     * 
     * @param array $atts Sanitized attributes
     */
    private function render_pagination($atts) {
        // This would be implemented based on the query results
        echo '<div class="resbs-pagination-placeholder">';
        esc_html_e('Pagination will be implemented here', 'realestate-booking-suite');
        echo '</div>';
    }

    /**
     * Render user properties
     * 
     * @param int $user_id User ID
     */
    private function render_user_properties($user_id) {
        $properties = get_posts(array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'author' => $user_id,
            'posts_per_page' => -1
        ));

        if (!empty($properties)) {
            echo '<div class="resbs-user-properties">';
            foreach ($properties as $property) {
                echo '<div class="resbs-user-property-item">';
                echo '<h5><a href="' . esc_url(get_permalink($property->ID)) . '">' . esc_html($property->post_title) . '</a></h5>';
                echo '<p>' . esc_html__('Status:', 'realestate-booking-suite') . ' ' . esc_html($property->post_status) . '</p>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<p>' . esc_html__('No properties found.', 'realestate-booking-suite') . '</p>';
        }
    }

    /**
     * Render user favorites
     * 
     * @param int $user_id User ID
     * @param array $atts Sanitized attributes
     */
    private function render_user_favorites($user_id, $atts = array()) {
        $favorites = get_user_meta($user_id, 'resbs_favorite_properties', true);
        
        if (!empty($favorites) && is_array($favorites)) {
            $properties = get_posts(array(
                'post_type' => 'property',
                'post_status' => 'publish',
                'post__in' => $favorites,
                'posts_per_page' => -1
            ));

            if (!empty($properties)) {
                foreach ($properties as $property) {
                    setup_postdata($property);
                    $this->render_property_card($atts);
                }
                wp_reset_postdata();
            } else {
                echo '<div class="resbs-favorites-empty">';
                echo '<p>' . esc_html__('No favorite properties found.', 'realestate-booking-suite') . '</p>';
                echo '</div>';
            }
        } else {
            echo '<div class="resbs-favorites-empty">';
            echo '<p>' . esc_html__('You haven\'t added any properties to your favorites yet.', 'realestate-booking-suite') . '</p>';
            echo '</div>';
        }
    }

    /**
     * Render user bookings
     * 
     * @param int $user_id User ID
     */
    private function render_user_bookings($user_id) {
        // This would be implemented based on the booking system
        echo '<div class="resbs-bookings-placeholder">';
        esc_html_e('Bookings will be displayed here', 'realestate-booking-suite');
        echo '</div>';
    }

    /**
     * Render profile form
     * 
     * @param int $user_id User ID
     */
    private function render_profile_form($user_id) {
        $user = get_userdata($user_id);
        
        ?>
        <form class="resbs-profile-form">
            <?php wp_nonce_field('resbs_update_profile', 'resbs_profile_nonce'); ?>
            
            <div class="resbs-form-row">
                <div class="resbs-form-group">
                    <label for="first_name"><?php esc_html_e('First Name', 'realestate-booking-suite'); ?></label>
                    <input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($user->first_name); ?>">
                </div>
                <div class="resbs-form-group">
                    <label for="last_name"><?php esc_html_e('Last Name', 'realestate-booking-suite'); ?></label>
                    <input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($user->last_name); ?>">
                </div>
            </div>
            
            <div class="resbs-form-group">
                <label for="email"><?php esc_html_e('Email', 'realestate-booking-suite'); ?></label>
                <input type="email" name="email" id="email" value="<?php echo esc_attr($user->user_email); ?>">
            </div>
            
            <div class="resbs-form-group">
                <label for="phone"><?php esc_html_e('Phone', 'realestate-booking-suite'); ?></label>
                <input type="tel" name="phone" id="phone" value="<?php echo esc_attr(get_user_meta($user_id, 'phone', true)); ?>">
            </div>
            
            <div class="resbs-form-actions">
                <button type="submit" class="resbs-update-profile-btn">
                    <?php esc_html_e('Update Profile', 'realestate-booking-suite'); ?>
                </button>
            </div>
        </form>
        <?php
    }
}

// Initialize the shortcodes
new RESBS_Shortcodes();
