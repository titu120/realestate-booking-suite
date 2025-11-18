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
        
        // AJAX handlers - use priority 5 to run before other handlers
        add_action('wp_ajax_resbs_toggle_favorite', array($this, 'ajax_toggle_favorite'), 5);
        add_action('wp_ajax_nopriv_resbs_toggle_favorite', array($this, 'ajax_toggle_favorite'), 5);
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
        global $post;
        
        // Always enqueue - the shortcode will be on the saved properties page
        // This ensures CSS is loaded regardless of detection method
        
        // Enqueue archive CSS files (needed for property card styling)
        wp_enqueue_style(
            'resbs-archive',
            RESBS_URL . 'assets/css/archive.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_style(
            'resbs-rbs-archive',
            RESBS_URL . 'assets/css/rbs-archive.css',
            array('resbs-archive'),
            '1.0.0'
        );
        
        // Enqueue main style.css for general styles
        wp_enqueue_style(
            'resbs-style',
            RESBS_URL . 'assets/css/style.css',
            array('resbs-archive', 'resbs-rbs-archive'),
            '1.0.0'
        );
        
        // Enqueue favorites styles
        wp_enqueue_style(
            'resbs-favorites',
            RESBS_URL . 'assets/css/favorites.css',
            array('resbs-archive', 'resbs-rbs-archive', 'resbs-style'),
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
        
        // Enqueue archive JS for wishlist button functionality
        // Enqueue toast notification CSS
        wp_enqueue_style(
            'resbs-toast-notification',
            RESBS_URL . 'assets/css/toast-notification.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue toast notification JS
        wp_enqueue_script(
            'resbs-favorites-toast',
            RESBS_URL . 'assets/js/favorites-toast.js',
            array(),
            '1.0.0',
            true
        );
        
        // Enqueue saved properties page JS
        wp_enqueue_script(
            'resbs-favorites-saved-properties',
            RESBS_URL . 'assets/js/favorites-saved-properties.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        // Localize script for saved properties
        wp_localize_script('resbs-favorites-saved-properties', 'resbs_favorites', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('resbs_favorites_nonce')),
            'messages' => array(
                'error' => esc_js(__('An error occurred. Please try again.', 'realestate-booking-suite'))
            )
        ));
        
        wp_enqueue_script(
            'resbs-archive',
            RESBS_URL . 'assets/js/archive.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Localize script
        wp_localize_script('resbs-favorites', 'resbs_favorites_ajax', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('resbs_favorites_nonce')),
            'user_logged_in' => is_user_logged_in(),
            'login_url' => esc_url(wp_login_url(get_permalink())),
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
        // Check if nonce exists
        if (!isset($_POST['nonce']) || empty($_POST['nonce'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Security token missing.', 'realestate-booking-suite')
            ));
        }
        
        // IMPORTANT: Do NOT sanitize nonce before verification - it breaks verification
        $nonce = $_POST['nonce'];
        
        // Check which nonce was sent - only process if it's our nonce
        // This prevents conflicts with other handlers (Frontend and Widgets classes)
        $nonce_verified = wp_verify_nonce($nonce, 'resbs_favorites_nonce');
        
        // If this handler's nonce doesn't match, let other handlers process it
        // Don't send error, just return early (other handlers will process it)
        if ($nonce_verified === false) {
            // Check if it's a different nonce (from other handlers)
            $elementor_nonce = wp_verify_nonce($nonce, 'resbs_elementor_nonce');
            $widget_nonce = wp_verify_nonce($nonce, 'resbs_widget_nonce');
            $archive_nonce = wp_verify_nonce($nonce, 'resbs_archive_nonce');
            
            // If it's a valid nonce from another handler, let that handler process it
            if ($elementor_nonce !== false || $widget_nonce !== false) {
                return; // Let other handler process it
            }
            
            // Accept archive nonce for archive template compatibility
            if ($archive_nonce !== false) {
                // Continue processing with archive nonce
                $nonce_verified = true;
            } else {
                // If none of the nonces match, it's invalid
                wp_send_json_error(array(
                    'message' => esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite')
                ));
            }
        }
        
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
            // Verify user is authenticated and can modify their own favorites
            $user_id = get_current_user_id();
            if (!$user_id) {
                wp_send_json_error(array(
                    'message' => esc_html__('Authentication required.', 'realestate-booking-suite')
                ));
            }
            
            $favorites = get_user_meta($user_id, 'resbs_favorites', true);
            
            if (!is_array($favorites)) {
                $favorites = array();
            }

            // Ensure all favorites are integers for proper comparison
            $favorites = array_map('intval', $favorites);
            $property_id = intval($property_id);
            
            if (in_array($property_id, $favorites)) {
                // Remove from favorites
                $favorites = array_diff($favorites, array($property_id));
                $favorites = array_values($favorites); // Re-index array
                $is_favorite = false;
            } else {
                // Add to favorites
                $favorites[] = $property_id;
                $favorites = array_unique($favorites); // Remove duplicates
                $favorites = array_values($favorites); // Re-index array
                $is_favorite = true;
            }

            // Save favorites - ensure it's saved as array
            // Make sure it's a proper array before saving
            $favorites = array_filter($favorites, 'is_numeric'); // Remove any non-numeric values
            $favorites = array_map('intval', $favorites); // Ensure all are integers
            $favorites = array_values(array_unique($favorites)); // Remove duplicates and re-index
            
            $saved = update_user_meta($user_id, 'resbs_favorites', $favorites);
            
            // Verify it was saved correctly
            $verify = get_user_meta($user_id, 'resbs_favorites', true);
            if (!is_array($verify) || !in_array($property_id, $verify)) {
                // If save failed, try again with explicit array
                update_user_meta($user_id, 'resbs_favorites', $favorites, false);
            }
        } else {
            // User not logged in - use session/cookies
            $favorites = $this->get_session_favorites();
            
            // Ensure all favorites are integers for proper comparison
            $favorites = array_map('intval', $favorites);
            $property_id = intval($property_id);
            
            if (in_array($property_id, $favorites)) {
                // Remove from favorites
                $favorites = array_diff($favorites, array($property_id));
                $favorites = array_values($favorites); // Re-index array
                $is_favorite = false;
            } else {
                // Add to favorites
                $favorites[] = $property_id;
                $favorites = array_unique($favorites); // Remove duplicates
                $favorites = array_values($favorites); // Re-index array
                $is_favorite = true;
            }

            // Make sure it's a proper array before saving
            $favorites = array_filter($favorites, 'is_numeric'); // Remove any non-numeric values
            $favorites = array_map('intval', $favorites); // Ensure all are integers
            $favorites = array_values(array_unique($favorites)); // Remove duplicates and re-index
            
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
        // Check if nonce exists
        if (!isset($_POST['nonce']) || empty($_POST['nonce'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Security token missing.', 'realestate-booking-suite')
            ));
        }
        
        // Verify nonce using security helper (do NOT sanitize before verification)
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
                    
                    $property_type_terms = wp_get_post_terms($property_id, 'property_type', array('fields' => 'names'));
                    $property_status_terms = wp_get_post_terms($property_id, 'property_status', array('fields' => 'names'));
                    $property_location_terms = wp_get_post_terms($property_id, 'property_location', array('fields' => 'names'));
                    
                    $properties[] = array(
                        'id' => $property_id,
                        'title' => esc_html(get_the_title()),
                        'permalink' => esc_url(get_permalink()),
                        'excerpt' => esc_html(get_the_excerpt()),
                        'featured_image' => esc_url(get_the_post_thumbnail_url($property_id, 'medium')),
                        'price' => esc_html(get_post_meta($property_id, '_property_price', true)),
                        'bedrooms' => esc_html(get_post_meta($property_id, '_property_bedrooms', true)),
                        'bathrooms' => esc_html(get_post_meta($property_id, '_property_bathrooms', true)),
                        'area' => esc_html(get_post_meta($property_id, '_property_area', true)),
                        'property_type' => is_array($property_type_terms) && !is_wp_error($property_type_terms) ? array_map('esc_html', $property_type_terms) : array(),
                        'property_status' => is_array($property_status_terms) && !is_wp_error($property_status_terms) ? array_map('esc_html', $property_status_terms) : array(),
                        'location' => is_array($property_location_terms) && !is_wp_error($property_location_terms) ? array_map('esc_html', $property_location_terms) : array()
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
        // Check if nonce exists
        if (!isset($_POST['nonce']) || empty($_POST['nonce'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Security token missing.', 'realestate-booking-suite')
            ));
        }
        
        // Verify nonce using security helper (do NOT sanitize before verification)
        RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'resbs_favorites_nonce');
        
        // Rate limiting check
        if (!RESBS_Security::check_rate_limit('clear_favorites', 5, 300)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
            ));
        }

        if (is_user_logged_in()) {
            // Verify user is authenticated
            $user_id = get_current_user_id();
            if (!$user_id) {
                wp_send_json_error(array(
                    'message' => esc_html__('Authentication required.', 'realestate-booking-suite')
                ));
            }
            
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
            
            // Ensure it's a proper array
            if (!is_array($favorites)) {
                return array();
            }
            
            // Clean and normalize the array
            $favorites = array_filter($favorites, 'is_numeric');
            $favorites = array_map('intval', $favorites);
            $favorites = array_values(array_unique($favorites));
            
            return $favorites;
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

        // Ensure post__in has valid IDs
        $favorites = array_filter($favorites, 'is_numeric');
        $favorites = array_map('intval', $favorites);
        $favorites = array_values(array_unique($favorites));
        
        if (empty($favorites)) {
            return $this->render_no_favorites_message();
        }

        $query_args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'post__in' => $favorites,
            'posts_per_page' => $posts_per_page,
            'orderby' => $orderby,
            'order' => $order,
            'ignore_sticky_posts' => true
        );

        $properties_query = new WP_Query($query_args);
        
        if (!$properties_query->have_posts()) {
            return $this->render_no_favorites_message();
        }

        // Ensure CSS is loaded when shortcode is rendered
        wp_enqueue_style('resbs-archive', RESBS_URL . 'assets/css/archive.css', array(), '1.0.0');
        wp_enqueue_style('resbs-rbs-archive', RESBS_URL . 'assets/css/rbs-archive.css', array('resbs-archive'), '1.0.0');
        wp_enqueue_style('resbs-style', RESBS_URL . 'assets/css/style.css', array('resbs-archive', 'resbs-rbs-archive'), '1.0.0');
        
        // Enqueue Font Awesome (needed for icons)
        wp_enqueue_style('font-awesome', esc_url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'), array(), '6.4.0');
        
        ob_start();
        ?>
        <div class="rbs-archive">
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

                <div class="property-grid">
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
        </div>
        
        <!-- Toast notification scripts and styles are now enqueued via wp_enqueue_script/wp_enqueue_style -->
        <!-- Favorite button functionality is now enqueued via wp_enqueue_script in favorites-saved-properties.js -->
            // Initialize favorite button states on page load
            function initializeFavoriteButtons() {
                const favoriteButtons = document.querySelectorAll('.favorite-btn, .resbs-favorite-btn');
                favoriteButtons.forEach(function(btn) {
                    const propertyId = btn.getAttribute('data-property-id');
                    if (!propertyId) return;
                    
                    const icon = btn.querySelector('i');
                    if (!icon) return;
                    
                    // Check if button already has favorited class (set by PHP)
                    if (btn.classList.contains('favorited')) {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                });
            }
            
            // Initialize buttons on page load
            initializeFavoriteButtons();
            
            // Handle favorite button clicks
            document.addEventListener('click', function(e) {
                const favoriteBtn = e.target.closest('.favorite-btn, .resbs-favorite-btn');
                if (!favoriteBtn) return;
                
                e.preventDefault();
                e.stopPropagation();
                
                const propertyId = favoriteBtn.getAttribute('data-property-id');
                if (!propertyId) {
                    console.error('Property ID not found');
                    return;
                }
                
                const icon = favoriteBtn.querySelector('i');
                if (!icon) return;
                
                // Toggle visual state immediately for better UX
                const isFavorited = favoriteBtn.classList.contains('favorited');
                if (isFavorited) {
                    favoriteBtn.classList.remove('favorited');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                } else {
                    favoriteBtn.classList.add('favorited');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                }
                
                // Make AJAX request
                const formData = new FormData();
                formData.append('action', 'resbs_toggle_favorite');
                formData.append('property_id', propertyId);
                
                // Generate nonce
                const nonce = '<?php echo esc_js(wp_create_nonce('resbs_favorites_nonce')); ?>';
                if (!nonce) {
                    alert('<?php echo esc_js(__('Unable to generate security token. Please refresh the page.', 'realestate-booking-suite')); ?>');
                    return;
                }
                formData.append('nonce', nonce);
                
                fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (!data) {
                        throw new Error('Invalid response from server');
                    }
                    
                    if (data.success) {
                        // Success - update button state
                        if (data.data && data.data.is_favorite) {
                            favoriteBtn.classList.add('favorited');
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                        } else {
                            favoriteBtn.classList.remove('favorited');
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                            // If removed from favorites, reload page to update list
                            if (window.location.href.indexOf('saved-properties') !== -1) {
                                setTimeout(function() {
                                    window.location.reload();
                                }, 500);
                            }
                        }
                        
                        // Show success message as toast notification
                        if (data.data && data.data.message) {
                            if (typeof showToastNotification === 'function') {
                                const safeMessage = String(data.data.message).replace(/[<>]/g, '');
                                showToastNotification(safeMessage, 'success');
                            }
                        }
                    } else {
                        // Error - revert visual state
                        if (isFavorited) {
                            favoriteBtn.classList.add('favorited');
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                        } else {
                            favoriteBtn.classList.remove('favorited');
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                        }
                        
                        // Show error message
                        let errorMessage = '<?php echo esc_js(__('An error occurred. Please try again.', 'realestate-booking-suite')); ?>';
                        
                        if (data && data.data) {
                            if (typeof data.data === 'string') {
                                errorMessage = String(data.data).replace(/[<>]/g, '');
                            } else if (data.data.message) {
                                errorMessage = String(data.data.message).replace(/[<>]/g, '');
                            }
                        }
                        
                        alert(errorMessage);
                    }
                })
                .catch(error => {
                    console.error('Favorite button error:', error);
                    
                    // Revert visual state on error
                    if (isFavorited) {
                        favoriteBtn.classList.add('favorited');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        favoriteBtn.classList.remove('favorited');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                    
                    alert('<?php echo esc_js(__('An error occurred. Please try again.', 'realestate-booking-suite')); ?>');
                });
            });
        });
        -->
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    /**
     * Render favorite property card - matches archive page design
     */
    private function render_favorite_property_card($property_id, $show_image, $show_price, $show_details, $show_actions) {
        // Get property meta data (same as archive page)
        $price = get_post_meta($property_id, '_property_price', true);
        $bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        $area_sqft = get_post_meta($property_id, '_property_area_sqft', true);
        $address = get_post_meta($property_id, '_property_address', true);
        $city = get_post_meta($property_id, '_property_city', true);
        $state = get_post_meta($property_id, '_property_state', true);
        $zip = get_post_meta($property_id, '_property_zip', true);
        
        // Get property type and status
        $property_types = get_the_terms($property_id, 'property_type');
        $property_statuses = get_the_terms($property_id, 'property_status');
        
        $property_type_name = '';
        if ($property_types && !is_wp_error($property_types)) {
            $property_type_name = $property_types[0]->name;
        }
        
        $property_status_name = '';
        if ($property_statuses && !is_wp_error($property_statuses)) {
            $property_status_name = $property_statuses[0]->name;
        }
        
        // Get featured image
        $featured_image = get_the_post_thumbnail_url($property_id, 'large');
        if (!$featured_image) {
            $featured_image = esc_url('https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800');
        } else {
            $featured_image = esc_url($featured_image);
        }
        
        // Format price
        $formatted_price = '';
        if ($price) {
            $formatted_price = resbs_format_price($price);
        }
        
        // Format location
        $location = '';
        if ($address) $location .= esc_html($address);
        if ($city) $location .= ($location ? ', ' : '') . esc_html($city);
        if ($state) $location .= ($location ? ', ' : '') . esc_html($state);
        if ($zip) $location .= ($location ? ' ' : '') . esc_html($zip);
        
        // Determine badge (same logic as archive page)
        $badge_class = 'badge-new';
        $badge_text = 'Just listed';
        $post_date = get_the_date('Y-m-d', $property_id);
        $days_old = (time() - strtotime($post_date)) / (60 * 60 * 24);
        
        if ($days_old < 7) {
            $badge_class = 'badge-new';
            $badge_text = 'Just listed';
        } elseif ($days_old < 30) {
            $badge_class = 'badge-featured';
            $badge_text = 'Featured';
        } else {
            $badge_class = 'badge-standard';
            $badge_text = 'Available';
        }
        
        // Check if favorited
        $is_favorited = resbs_is_property_favorited($property_id);
        
        // Use same HTML structure as archive page
        ?>
        <div class="property-card" data-property-id="<?php echo esc_attr($property_id); ?>">
            <div class="property-image">
                <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title($property_id)); ?>">
                <div class="gradient-overlay"></div>
                <div class="property-badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($badge_text); ?></div>
                <?php if (resbs_is_wishlist_enabled()): ?>
                <button class="favorite-btn resbs-favorite-btn <?php echo esc_attr($is_favorited ? 'favorited' : ''); ?>" data-property-id="<?php echo esc_attr($property_id); ?>">
                    <i class="<?php echo esc_attr($is_favorited ? 'fas' : 'far'); ?> fa-heart"></i>
                </button>
                <?php endif; ?>
                <div class="property-info-overlay">
                    <h3 class="property-title"><?php echo esc_html(get_the_title($property_id)); ?></h3>
                    <?php if (resbs_should_show_listing_address() && $location): ?>
                        <p class="property-location"><?php echo esc_html($location); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="property-details">
                <div class="property-price-container">
                    <?php if (resbs_should_show_price() && $formatted_price): ?>
                        <span class="property-price"><?php echo esc_html($formatted_price); ?></span>
                    <?php endif; ?>
                    <span class="property-status"><?php echo esc_html($property_status_name); ?></span>
                </div>
                <div class="property-features">
                    <?php if ($bedrooms): ?>
                        <div class="property-feature">
                            <i class="fas fa-bed"></i>
                            <span><?php echo esc_html($bedrooms); ?> beds</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($bathrooms): ?>
                        <div class="property-feature">
                            <i class="fas fa-bath"></i>
                            <span><?php echo esc_html($bathrooms); ?> baths</span>
                        </div>
                    <?php endif; ?>
                    <?php if ($area_sqft): ?>
                        <div class="property-feature">
                            <i class="fas fa-ruler-combined"></i>
                            <span><?php echo esc_html(resbs_format_area($area_sqft)); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="property-footer">
                    <span class="property-type"><?php echo esc_html($property_type_name); ?></span>
                    <a href="<?php echo esc_url(get_permalink($property_id)); ?>" class="view-details-btn">
                        View Details <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
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
        
        $currency_symbol = sanitize_text_field(get_option('resbs_currency_symbol', '$'));
        return $currency_symbol . number_format($num_price);
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
        // Check user capability - only users who can edit widgets should be able to update them
        if (!current_user_can('edit_theme_options')) {
            return $old_instance; // Return old instance if user doesn't have permission
        }
        
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
        
        echo wp_kses_post($args['before_widget']);
        
        if (!empty($title)) {
            echo wp_kses_post($args['before_title']) . esc_html($title) . wp_kses_post($args['after_title']);
        }
        
        // Display favorites using shortcode
        echo do_shortcode('[resbs_favorites layout="list" columns="1" posts_per_page="' . esc_attr($max_properties) . '" show_clear_button="' . esc_attr($show_clear_button ? 'true' : 'false') . '"]');
        
        echo wp_kses_post($args['after_widget']);
    }
}

// Initialize Favorites Manager
new RESBS_Favorites_Manager();
