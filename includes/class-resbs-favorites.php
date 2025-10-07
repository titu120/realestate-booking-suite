<?php
/**
 * Favorites Manager Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Favorites_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        
        // AJAX handlers
        add_action('wp_ajax_resbs_toggle_favorite', array($this, 'ajax_toggle_favorite'));
        add_action('wp_ajax_nopriv_resbs_toggle_favorite', array($this, 'ajax_toggle_favorite'));
        add_action('wp_ajax_resbs_get_favorites', array($this, 'ajax_get_favorites'));
        add_action('wp_ajax_nopriv_resbs_get_favorites', array($this, 'ajax_get_favorites'));
        add_action('wp_ajax_resbs_clear_favorites', array($this, 'ajax_clear_favorites'));
        add_action('wp_ajax_nopriv_resbs_clear_favorites', array($this, 'ajax_clear_favorites'));
        
        // Shortcode
        add_shortcode('resbs_favorites', array($this, 'favorites_shortcode'));
        
        // Widget
        add_action('widgets_init', array($this, 'register_favorites_widget'));
        
        // Add favorite buttons to property displays
        add_action('resbs_property_favorite_button', array($this, 'display_favorite_button'), 10, 2);
    }

    /**
     * Initialize
     */
    public function init() {
        // Add favorite functionality to existing displays
        add_action('resbs_property_card_actions', array($this, 'add_favorite_button_to_card'), 10, 1);
        add_action('resbs_single_property_actions', array($this, 'add_favorite_button_to_single'), 10, 1);
    }

    /**
     * Enqueue assets
     */
    public function enqueue_assets() {
        // Enqueue favorites styles
        wp_enqueue_style(
            'resbs-favorites',
            RESBS_URL . 'assets/css/favorites.css',
            array(),
            '1.0.0'
        );

        // Enqueue favorites scripts
        wp_enqueue_script(
            'resbs-favorites',
            RESBS_URL . 'assets/js/favorites.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Localize script
        wp_localize_script('resbs-favorites', 'resbs_favorites_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('resbs_favorites_nonce'),
            'user_logged_in' => is_user_logged_in(),
            'login_url' => wp_login_url(get_permalink()),
            'messages' => array(
                'add_to_favorites' => esc_html__('Add to Favorites', 'realestate-booking-suite'),
                'remove_from_favorites' => esc_html__('Remove from Favorites', 'realestate-booking-suite'),
                'favorites_updated' => esc_html__('Favorites updated!', 'realestate-booking-suite'),
                'login_required' => esc_html__('Please login to save favorites', 'realestate-booking-suite'),
                'no_favorites' => esc_html__('No favorite properties found.', 'realestate-booking-suite'),
                'clear_favorites' => esc_html__('Clear All Favorites', 'realestate-booking-suite'),
                'clear_confirm' => esc_html__('Are you sure you want to clear all favorites?', 'realestate-booking-suite'),
                'error' => esc_html__('An error occurred. Please try again.', 'realestate-booking-suite'),
                'loading' => esc_html__('Loading...', 'realestate-booking-suite'),
                'view_property' => esc_html__('View Property', 'realestate-booking-suite'),
                'remove_property' => esc_html__('Remove from Favorites', 'realestate-booking-suite'),
                'favorites_count' => esc_html__('Favorites', 'realestate-booking-suite'),
                'my_favorites' => esc_html__('My Favorites', 'realestate-booking-suite'),
                'total_favorites' => esc_html__('Total Favorites', 'realestate-booking-suite')
            )
        ));
    }

    /**
     * AJAX handler for toggling favorites
     */
    public function ajax_toggle_favorite() {
        // Verify nonce using security helper
        RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'resbs_favorites_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('toggle_favorite', 30, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        // Sanitize and validate property ID
        $property_id = RESBS_Security::sanitize_property_id($_POST['property_id']);
        
        if (!$property_id) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid property ID.', 'realestate-booking-suite')
            ));
        }

        // Check if property exists
        if (!get_post($property_id) || get_post_type($property_id) !== 'property') {
            wp_send_json_error(array(
                'message' => esc_html__('Property not found.', 'realestate-booking-suite')
            ));
        }

        if (is_user_logged_in()) {
            // User is logged in - use user meta
            $user_id = get_current_user_id();
            $favorites = get_user_meta($user_id, 'resbs_favorites', true);
            
            if (!is_array($favorites)) {
                $favorites = array();
            }

            if (in_array($property_id, $favorites)) {
                // Remove from favorites
                $favorites = array_diff($favorites, array($property_id));
                $is_favorite = false;
            } else {
                // Add to favorites
                $favorites[] = $property_id;
                $is_favorite = true;
            }

            update_user_meta($user_id, 'resbs_favorites', $favorites);
        } else {
            // User not logged in - use session/cookies
            $favorites = $this->get_session_favorites();
            
            if (in_array($property_id, $favorites)) {
                // Remove from favorites
                $favorites = array_diff($favorites, array($property_id));
                $is_favorite = false;
            } else {
                // Add to favorites
                $favorites[] = $property_id;
                $is_favorite = true;
            }

            $this->set_session_favorites($favorites);
        }

        wp_send_json_success(array(
            'is_favorite' => $is_favorite,
            'favorites_count' => count($favorites),
            'message' => $is_favorite ? 
                esc_html__('Added to favorites!', 'realestate-booking-suite') : 
                esc_html__('Removed from favorites!', 'realestate-booking-suite')
        ));
    }

    /**
     * AJAX handler for getting favorites
     */
    public function ajax_get_favorites() {
        // Verify nonce using security helper
        RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'resbs_favorites_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('get_favorites', 20, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        $favorites = $this->get_user_favorites();
        $properties = array();

        if (!empty($favorites)) {
            $query_args = array(
                'post_type' => 'property',
                'post_status' => 'publish',
                'post__in' => $favorites,
                'posts_per_page' => -1,
                'orderby' => 'post__in'
            );

            $properties_query = new WP_Query($query_args);

            if ($properties_query->have_posts()) {
                while ($properties_query->have_posts()) {
                    $properties_query->the_post();
                    $property_id = get_the_ID();
                    
                    $properties[] = array(
                        'id' => $property_id,
                        'title' => get_the_title(),
                        'permalink' => get_permalink(),
                        'excerpt' => get_the_excerpt(),
                        'featured_image' => get_the_post_thumbnail_url($property_id, 'medium'),
                        'price' => get_post_meta($property_id, '_property_price', true),
                        'bedrooms' => get_post_meta($property_id, '_property_bedrooms', true),
                        'bathrooms' => get_post_meta($property_id, '_property_bathrooms', true),
                        'area' => get_post_meta($property_id, '_property_area', true),
                        'property_type' => wp_get_post_terms($property_id, 'property_type', array('fields' => 'names')),
                        'property_status' => wp_get_post_terms($property_id, 'property_status', array('fields' => 'names')),
                        'location' => wp_get_post_terms($property_id, 'property_location', array('fields' => 'names'))
                    );
                }
                wp_reset_postdata();
            }
        }

        wp_send_json_success(array(
            'properties' => $properties,
            'count' => count($properties)
        ));
    }

    /**
     * AJAX handler for clearing favorites
     */
    public function ajax_clear_favorites() {
        // Verify nonce using security helper
        RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'resbs_favorites_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('clear_favorites', 5, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            delete_user_meta($user_id, 'resbs_favorites');
        } else {
            $this->set_session_favorites(array());
        }

        wp_send_json_success(array(
            'message' => esc_html__('All favorites cleared!', 'realestate-booking-suite')
        ));
    }

    /**
     * Get user favorites
     */
    public function get_user_favorites() {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $favorites = get_user_meta($user_id, 'resbs_favorites', true);
            return is_array($favorites) ? $favorites : array();
        } else {
            return $this->get_session_favorites();
        }
    }

    /**
     * Get session favorites
     */
    private function get_session_favorites() {
        if (!session_id()) {
            session_start();
        }
        
        return isset($_SESSION['resbs_favorites']) ? 
            array_map('intval', $_SESSION['resbs_favorites']) : array();
    }

    /**
     * Set session favorites
     */
    private function set_session_favorites($favorites) {
        if (!session_id()) {
            session_start();
        }
        
        $_SESSION['resbs_favorites'] = array_map('intval', $favorites);
    }

    /**
     * Check if property is favorite
     */
    public function is_favorite($property_id) {
        $favorites = $this->get_user_favorites();
        return in_array(intval($property_id), $favorites);
    }

    /**
     * Get favorites count
     */
    public function get_favorites_count() {
        return count($this->get_user_favorites());
    }

    /**
     * Favorites shortcode
     */
    public function favorites_shortcode($atts) {
        $atts = shortcode_atts(array(
            'layout' => 'grid',
            'columns' => '3',
            'show_image' => 'true',
            'show_price' => 'true',
            'show_details' => 'true',
            'show_actions' => 'true',
            'show_clear_button' => 'true',
            'posts_per_page' => '12',
            'orderby' => 'date',
            'order' => 'DESC'
        ), $atts);

        $layout = sanitize_text_field($atts['layout']);
        $columns = intval($atts['columns']);
        $show_image = $atts['show_image'] !== 'false';
        $show_price = $atts['show_price'] !== 'false';
        $show_details = $atts['show_details'] !== 'false';
        $show_actions = $atts['show_actions'] !== 'false';
        $show_clear_button = $atts['show_clear_button'] !== 'false';
        $posts_per_page = intval($atts['posts_per_page']);
        $orderby = sanitize_text_field($atts['orderby']);
        $order = sanitize_text_field($atts['order']);

        $favorites = $this->get_user_favorites();
        
        if (empty($favorites)) {
            return $this->render_no_favorites_message();
        }

        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'post__in' => $favorites,
            'posts_per_page' => $posts_per_page,
            'orderby' => $orderby,
            'order' => $order
        );

        $properties_query = new WP_Query($query_args);
        
        if (!$properties_query->have_posts()) {
            return $this->render_no_favorites_message();
        }

        ob_start();
        ?>
        <div class="resbs-favorites-container" data-layout="<?php echo esc_attr($layout); ?>" data-columns="<?php echo esc_attr($columns); ?>">
            <div class="resbs-favorites-header">
                <h3 class="resbs-favorites-title">
                    <?php esc_html_e('My Favorites', 'realestate-booking-suite'); ?>
                    <span class="resbs-favorites-count">(<?php echo esc_html(count($favorites)); ?>)</span>
                </h3>
                
                <?php if ($show_clear_button && !empty($favorites)): ?>
                    <button type="button" class="resbs-clear-favorites-btn" data-nonce="<?php echo esc_attr(wp_create_nonce('resbs_favorites_nonce')); ?>">
                        <span class="dashicons dashicons-trash"></span>
                        <?php esc_html_e('Clear All', 'realestate-booking-suite'); ?>
                    </button>
                <?php endif; ?>
            </div>

            <div class="resbs-favorites-grid resbs-favorites-<?php echo esc_attr($layout); ?> resbs-favorites-columns-<?php echo esc_attr($columns); ?>">
                <?php while ($properties_query->have_posts()): $properties_query->the_post(); ?>
                    <?php $this->render_favorite_property_card(get_the_ID(), $show_image, $show_price, $show_details, $show_actions); ?>
                <?php endwhile; ?>
            </div>

            <?php if ($properties_query->max_num_pages > 1): ?>
                <div class="resbs-favorites-pagination">
                    <?php
                    echo paginate_links(array(
                        'total' => $properties_query->max_num_pages,
                        'current' => max(1, get_query_var('paged')),
                        'format' => '?paged=%#%',
                        'prev_text' => esc_html__('Previous', 'realestate-booking-suite'),
                        'next_text' => esc_html__('Next', 'realestate-booking-suite')
                    ));
                    ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Render favorite property card
     */
    private function render_favorite_property_card($property_id, $show_image, $show_price, $show_details, $show_actions) {
        $price = get_post_meta($property_id, '_property_price', true);
        $bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        $area = get_post_meta($property_id, '_property_area', true);
        $property_type = wp_get_post_terms($property_id, 'property_type', array('fields' => 'names'));
        $property_status = wp_get_post_terms($property_id, 'property_status', array('fields' => 'names'));
        $location = wp_get_post_terms($property_id, 'property_location', array('fields' => 'names'));
        
        ?>
        <div class="resbs-favorite-property-card" data-property-id="<?php echo esc_attr($property_id); ?>">
            <?php if ($show_image): ?>
                <div class="resbs-favorite-property-image">
                    <a href="<?php echo esc_url(get_permalink($property_id)); ?>">
                        <?php if (has_post_thumbnail($property_id)): ?>
                            <?php echo get_the_post_thumbnail($property_id, 'medium', array('alt' => get_the_title($property_id))); ?>
                        <?php else: ?>
                            <div class="resbs-favorite-property-placeholder">
                                <span class="dashicons dashicons-camera"></span>
                            </div>
                        <?php endif; ?>
                    </a>
                    
                    <!-- Property Badges -->
                    <?php do_action('resbs_property_badges', $property_id, 'favorites'); ?>
                    
                    <!-- Favorite Button -->
                    <?php if ($show_actions): ?>
                        <button type="button" class="resbs-favorite-btn resbs-favorite-btn-active" data-property-id="<?php echo esc_attr($property_id); ?>">
                            <span class="dashicons dashicons-heart-filled"></span>
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="resbs-favorite-property-content">
                <h4 class="resbs-favorite-property-title">
                    <a href="<?php echo esc_url(get_permalink($property_id)); ?>">
                        <?php echo esc_html(get_the_title($property_id)); ?>
                    </a>
                </h4>

                <?php if ($show_price && $price): ?>
                    <div class="resbs-favorite-property-price">
                        <?php echo esc_html($this->format_price($price)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_details): ?>
                    <div class="resbs-favorite-property-details">
                        <?php if ($bedrooms): ?>
                            <span class="resbs-favorite-property-detail">
                                <span class="dashicons dashicons-bed-alt"></span>
                                <?php echo esc_html($bedrooms); ?> <?php esc_html_e('Bed', 'realestate-booking-suite'); ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($bathrooms): ?>
                            <span class="resbs-favorite-property-detail">
                                <span class="dashicons dashicons-bath"></span>
                                <?php echo esc_html($bathrooms); ?> <?php esc_html_e('Bath', 'realestate-booking-suite'); ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($area): ?>
                            <span class="resbs-favorite-property-detail">
                                <span class="dashicons dashicons-fullscreen-alt"></span>
                                <?php echo esc_html($area); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ($location): ?>
                    <div class="resbs-favorite-property-location">
                        <span class="dashicons dashicons-location"></span>
                        <?php echo esc_html(implode(', ', $location)); ?>
                    </div>
                <?php endif; ?>

                <?php if ($show_actions): ?>
                    <div class="resbs-favorite-property-actions">
                        <a href="<?php echo esc_url(get_permalink($property_id)); ?>" class="resbs-favorite-view-btn">
                            <?php esc_html_e('View Property', 'realestate-booking-suite'); ?>
                        </a>
                        
                        <button type="button" class="resbs-favorite-remove-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
                            <span class="dashicons dashicons-heart-filled"></span>
                            <?php esc_html_e('Remove', 'realestate-booking-suite'); ?>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render no favorites message
     */
    private function render_no_favorites_message() {
        ob_start();
        ?>
        <div class="resbs-favorites-empty">
            <div class="resbs-favorites-empty-icon">
                <span class="dashicons dashicons-heart"></span>
            </div>
            <h3><?php esc_html_e('No Favorite Properties', 'realestate-booking-suite'); ?></h3>
            <p><?php esc_html_e('Start exploring properties and add them to your favorites to see them here.', 'realestate-booking-suite'); ?></p>
            <a href="<?php echo esc_url(get_post_type_archive_link('property')); ?>" class="resbs-favorites-browse-btn">
                <?php esc_html_e('Browse Properties', 'realestate-booking-suite'); ?>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Format price
     */
    private function format_price($price) {
        if (!$price) return '';
        
        $num_price = intval($price);
        if (is_nan($num_price)) return $price;
        
        return '$' . number_format($num_price);
    }

    /**
     * Display favorite button
     */
    public function display_favorite_button($property_id, $context = 'card') {
        $is_favorite = $this->is_favorite($property_id);
        $button_class = $is_favorite ? 'resbs-favorite-btn-active' : '';
        $icon_class = $is_favorite ? 'dashicons-heart-filled' : 'dashicons-heart';
        $text = $is_favorite ? 
            esc_html__('Remove from Favorites', 'realestate-booking-suite') : 
            esc_html__('Add to Favorites', 'realestate-booking-suite');
        
        ?>
        <button type="button" 
                class="resbs-favorite-btn <?php echo esc_attr($button_class); ?>" 
                data-property-id="<?php echo esc_attr($property_id); ?>"
                data-context="<?php echo esc_attr($context); ?>"
                title="<?php echo esc_attr($text); ?>">
            <span class="dashicons <?php echo esc_attr($icon_class); ?>"></span>
            <span class="resbs-favorite-text"><?php echo esc_html($text); ?></span>
        </button>
        <?php
    }

    /**
     * Add favorite button to property card
     */
    public function add_favorite_button_to_card($property_id) {
        ?>
        <div class="resbs-property-card-favorite">
            <?php $this->display_favorite_button($property_id, 'card'); ?>
        </div>
        <?php
    }

    /**
     * Add favorite button to single property
     */
    public function add_favorite_button_to_single($property_id) {
        ?>
        <div class="resbs-single-property-favorite">
            <?php $this->display_favorite_button($property_id, 'single'); ?>
        </div>
        <?php
    }

    /**
     * Register favorites widget
     */
    public function register_favorites_widget() {
        register_widget('RESBS_Favorites_Widget');
    }
}

/**
 * Favorites Widget
 */
class RESBS_Favorites_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'resbs_favorites_widget',
            esc_html__('Property Favorites', 'realestate-booking-suite'),
            array(
                'description' => esc_html__('Display user\'s favorite properties.', 'realestate-booking-suite'),
                'classname' => 'resbs-favorites-widget'
            )
        );
    }

    /**
     * Widget form
     */
    public function form($instance) {
        $defaults = array(
            'title' => esc_html__('My Favorites', 'realestate-booking-suite'),
            'show_count' => true,
            'show_clear_button' => true,
            'max_properties' => 5
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        
        $title = sanitize_text_field($instance['title']);
        $show_count = (bool) $instance['show_count'];
        $show_clear_button = (bool) $instance['show_clear_button'];
        $max_properties = intval($instance['max_properties']);
        ?>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'realestate-booking-suite'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('max_properties')); ?>">
                <?php esc_html_e('Maximum Properties:', 'realestate-booking-suite'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('max_properties')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('max_properties')); ?>" 
                   type="number" min="1" max="20" value="<?php echo esc_attr($max_properties); ?>" />
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_count); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_count')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_count')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_count')); ?>">
                <?php esc_html_e('Show Favorites Count', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_clear_button); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_clear_button')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_clear_button')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_clear_button')); ?>">
                <?php esc_html_e('Show Clear Button', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <?php
    }

    /**
     * Update widget
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['max_properties'] = intval($new_instance['max_properties']);
        $instance['show_count'] = isset($new_instance['show_count']);
        $instance['show_clear_button'] = isset($new_instance['show_clear_button']);
        
        return $instance;
    }

    /**
     * Display widget
     */
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', sanitize_text_field($instance['title']));
        $max_properties = intval($instance['max_properties']);
        $show_count = (bool) $instance['show_count'];
        $show_clear_button = (bool) $instance['show_clear_button'];
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        // Display favorites using shortcode
        echo do_shortcode('[resbs_favorites layout="list" columns="1" posts_per_page="' . esc_attr($max_properties) . '" show_clear_button="' . ($show_clear_button ? 'true' : 'false') . '"]');
        
        echo $args['after_widget'];
    }
}

// Initialize Favorites Manager
new RESBS_Favorites_Manager();
