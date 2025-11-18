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
        // Handle registration form submission BEFORE any output (to avoid headers already sent errors)
        add_action('template_redirect', array($this, 'handle_buyer_registration_submit'), 1);
        // Handle email verification link clicks
        add_action('template_redirect', array($this, 'handle_email_verification'), 1);
    }

    /**
     * Handle buyer registration form submission (runs BEFORE any output)
     * This prevents "headers already sent" errors
     */
    public function handle_buyer_registration_submit() {
        // Only process if form was submitted
        if (!isset($_POST['resbs_register_nonce']) || !wp_verify_nonce($_POST['resbs_register_nonce'], 'resbs_buyer_register')) {
            return;
        }
        
        // Check if buyer signup is enabled
        $enable_buyer_signup = get_option('resbs_enable_signup_buyers', 0) === '1';
        if (!$enable_buyer_signup) {
            return;
        }
        
        // Don't process if user is already logged in
        if (is_user_logged_in()) {
            return;
        }
        
        $username = isset($_POST['user_login']) ? sanitize_user($_POST['user_login']) : '';
        $email = isset($_POST['user_email']) ? sanitize_email($_POST['user_email']) : '';
        $password = isset($_POST['user_pass']) ? $_POST['user_pass'] : '';
        $password_confirm = isset($_POST['user_pass_confirm']) ? $_POST['user_pass_confirm'] : '';
        
        $errors = new WP_Error();
        
        // Validation
        if (empty($username)) {
            $errors->add('empty_username', esc_html__('Please enter a username.', 'realestate-booking-suite'));
        } elseif (!validate_username($username)) {
            $errors->add('invalid_username', esc_html__('This username is invalid because it uses illegal characters. Please enter a valid username.', 'realestate-booking-suite'));
        } elseif (username_exists($username)) {
            $errors->add('username_exists', esc_html__('This username is already registered. Please choose another one.', 'realestate-booking-suite'));
        }
        
        if (empty($email)) {
            $errors->add('empty_email', esc_html__('Please enter an email address.', 'realestate-booking-suite'));
        } elseif (!is_email($email)) {
            $errors->add('invalid_email', esc_html__('Please enter a valid email address.', 'realestate-booking-suite'));
        } elseif (email_exists($email)) {
            $errors->add('email_exists', esc_html__('This email is already registered. Please use another email or log in.', 'realestate-booking-suite'));
        }
        
        if (empty($password)) {
            $errors->add('empty_password', esc_html__('Please enter a password.', 'realestate-booking-suite'));
        } elseif (strlen($password) < 6) {
            $errors->add('weak_password', esc_html__('Password must be at least 6 characters long.', 'realestate-booking-suite'));
        }
        
        if ($password !== $password_confirm) {
            $errors->add('password_mismatch', esc_html__('Passwords do not match.', 'realestate-booking-suite'));
        }
        
        // If no errors, create user and redirect
        if (!is_wp_error($errors) || !$errors->has_errors()) {
            $user_id = wp_create_user($username, $password, $email);
            
            if (!is_wp_error($user_id)) {
                // User role will be assigned by RESBS_User_Roles class
                
                // Check if email verification is enabled
                $enable_email_verification = get_option('resbs_enable_email_verification', 1); // Default: enabled
                
                if ($enable_email_verification) {
                    // Email verification enabled - create unverified user and send verification email
                    update_user_meta($user_id, 'resbs_email_verified', '0');
                    $verification_token = wp_generate_password(32, false);
                    update_user_meta($user_id, 'resbs_verification_token', $verification_token);
                    update_user_meta($user_id, 'resbs_verification_expires', time() + (7 * DAY_IN_SECONDS)); // 7 days expiry
                    
                    // Send verification email
                    $this->send_verification_email($user_id, $email, $verification_token);
                    
                    // Store success message
                    $success_key = 'resbs_reg_success_' . md5($_SERVER['REMOTE_ADDR'] . time());
                    set_transient($success_key, 'email_verification', 300); // 5 minutes
                    wp_safe_redirect(add_query_arg('reg_success', $success_key, get_permalink()));
                    exit;
                } else {
                    // Email verification disabled - auto-login (old behavior)
                    update_user_meta($user_id, 'resbs_email_verified', '1'); // Mark as verified
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                    
                    // Redirect to home page - keep default WordPress/WooCommerce My Account design unchanged
                    $redirect_url = home_url('/');
                    wp_safe_redirect($redirect_url);
                    exit;
                }
            } else {
                // Store errors in transient to display after redirect
                $transient_key = 'resbs_registration_errors_' . md5($_SERVER['REMOTE_ADDR'] . time());
                set_transient($transient_key, $user_id->get_error_messages(), 30);
                wp_safe_redirect(add_query_arg('reg_errors', $transient_key, get_permalink()));
                exit;
            }
        } else {
            // Store errors in transient to display after redirect
            $transient_key = 'resbs_registration_errors_' . md5($_SERVER['REMOTE_ADDR'] . time());
            set_transient($transient_key, $errors->get_error_messages(), 30);
            wp_safe_redirect(add_query_arg('reg_errors', $transient_key, get_permalink()));
            exit;
        }
    }

    /**
     * Handle email verification link clicks
     */
    public function handle_email_verification() {
        if (!isset($_GET['resbs_verify']) || empty($_GET['resbs_verify'])) {
            return;
        }
        
        $token = sanitize_text_field($_GET['resbs_verify']);
        
        // Find user by verification token
        $users = get_users(array(
            'meta_key' => 'resbs_verification_token',
            'meta_value' => $token,
            'number' => 1
        ));
        
        if (empty($users)) {
            // Invalid token - redirect with error
            $error_key = 'resbs_verify_error_' . md5($_SERVER['REMOTE_ADDR'] . time());
            set_transient($error_key, 'invalid_token', 300);
            wp_safe_redirect(add_query_arg('verify_error', $error_key, home_url('/register/')));
            exit;
        }
        
        $user = $users[0];
        $user_id = $user->ID;
        
        // Check if token has expired
        $expires = get_user_meta($user_id, 'resbs_verification_expires', true);
        if ($expires && time() > $expires) {
            // Token expired - redirect with error
            $error_key = 'resbs_verify_error_' . md5($_SERVER['REMOTE_ADDR'] . time());
            set_transient($error_key, 'expired_token', 300);
            wp_safe_redirect(add_query_arg('verify_error', $error_key, home_url('/register/')));
            exit;
        }
        
        // Check if already verified
        $verified = get_user_meta($user_id, 'resbs_email_verified', true);
        if ($verified === '1') {
            // Already verified - redirect with message
            $success_key = 'resbs_verify_success_' . md5($_SERVER['REMOTE_ADDR'] . time());
            set_transient($success_key, 'already_verified', 300);
            wp_safe_redirect(add_query_arg('verify_success', $success_key, home_url('/register/')));
            exit;
        }
        
        // Verify the user
        update_user_meta($user_id, 'resbs_email_verified', '1');
        delete_user_meta($user_id, 'resbs_verification_token');
        delete_user_meta($user_id, 'resbs_verification_expires');
        
        // Auto-login the user
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
        
        // Redirect with success message
        $success_key = 'resbs_verify_success_' . md5($_SERVER['REMOTE_ADDR'] . time());
        set_transient($success_key, 'verified', 300);
        wp_safe_redirect(add_query_arg('verify_success', $success_key, home_url('/')));
        exit;
    }

    /**
     * Send verification email to user
     */
    private function send_verification_email($user_id, $email, $token) {
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }
        
        $verification_url = add_query_arg(
            array('resbs_verify' => $token),
            home_url('/')
        );
        
        $site_name = get_bloginfo('name');
        $from_name = get_option('resbs_email_from_name', $site_name);
        $from_email = get_option('resbs_email_from_email', get_option('admin_email'));
        
        // Sanitize email header values
        $from_name = sanitize_text_field($from_name);
        $from_email = sanitize_email($from_email);
        
        $subject = sprintf(__('Verify your email address - %s', 'realestate-booking-suite'), $site_name);
        
        $message = sprintf(
            __('Hello %s,

Thank you for registering with %s!

Please click the link below to verify your email address and activate your account:

%s

This link will expire in 7 days.

If you did not create an account, please ignore this email.

Best regards,
%s Team', 'realestate-booking-suite'),
            $user->display_name,
            $site_name,
            $verification_url,
            $site_name
        );
        
        $headers = array(
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Content-Type: text/html; charset=UTF-8'
        );
        
        // Send email
        return wp_mail($email, $subject, nl2br($message), $headers);
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
        add_shortcode('resbs_login_form', array($this, 'login_form_shortcode'));
        add_shortcode('resbs_buyer_registration', array($this, 'buyer_registration_shortcode'));
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
            // layouts.js is disabled to prevent conflicts - functionality moved to elementor.js
            // wp_enqueue_script('resbs-layouts');
        }
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'resbs_search')) {
            wp_enqueue_style('resbs-maps');
            wp_enqueue_script('resbs-maps');
        }
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'resbs_dashboard')) {
            // Register and enqueue shortcodes CSS if not already done
            if (!wp_style_is('resbs-shortcodes', 'enqueued')) {
                wp_enqueue_style(
                    'resbs-shortcodes',
                    RESBS_URL . 'assets/css/shortcodes.css',
                    array(),
                    '1.0.0'
                );
            }
            
            // Enqueue archive CSS for property grid design
            if (!wp_style_is('resbs-rbs-archive', 'enqueued')) {
                wp_enqueue_style(
                    'resbs-rbs-archive',
                    RESBS_URL . 'assets/css/rbs-archive.css',
                    array(),
                    '1.0.0'
                );
            }
            
            // Register and enqueue shortcodes JS if not already done
            if (!wp_script_is('resbs-shortcodes', 'enqueued')) {
                wp_enqueue_script(
                    'resbs-shortcodes',
                    RESBS_URL . 'assets/js/shortcodes.js',
                    array('jquery'),
                    '1.0.0',
                    true
                );
                
                // Localize script with AJAX data
                wp_localize_script('resbs-shortcodes', 'resbsShortcodes', array(
                    'ajax_url' => esc_url(admin_url('admin-ajax.php')),
                    'publish_nonce' => esc_js(wp_create_nonce('resbs_publish_property'))
                ));
            }
        }
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'resbs_submit_property')) {
            wp_enqueue_style('resbs-shortcodes'); // Use shortcodes CSS instead of non-existent forms.css
            // forms.js does not exist - using shortcodes.js instead
            // wp_enqueue_script('resbs-forms');
            
            // Also enqueue shortcodes script for submit functionality
            if (!wp_script_is('resbs-shortcodes', 'enqueued')) {
                wp_enqueue_script(
                    'resbs-shortcodes',
                    RESBS_URL . 'assets/js/shortcodes.js',
                    array('jquery'),
                    '1.0.0',
                    true
                );
            }
        }
        
        if (is_a($post, 'WP_Post') && (has_shortcode($post->post_content, 'resbs_login_form') || has_shortcode($post->post_content, 'resbs_buyer_registration'))) {
            // Enqueue shortcodes CSS for login/registration form styling
            if (!wp_style_is('resbs-shortcodes', 'enqueued')) {
                wp_enqueue_style(
                    'resbs-shortcodes',
                    RESBS_URL . 'assets/css/shortcodes.css',
                    array(),
                    '1.0.0'
                );
            }
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

        // Check if user profile is enabled in settings
        $profile_enabled = resbs_is_user_profile_enabled();
        
        // Default attributes
        $default_atts = array(
            'title' => esc_html__('My Dashboard', 'realestate-booking-suite'),
            'show_properties' => 'yes',
            'show_favorites' => 'yes',
            'show_bookings' => 'yes',
            'show_profile' => $profile_enabled ? 'yes' : 'no'
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
                                <div class="rbs-archive">
                                    <div class="listings-container">
                                        <div class="properties-list">
                                            <div id="propertyGrid" class="property-grid">
                                                <?php $this->render_user_properties($user_id); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($sanitized_atts['show_favorites']): ?>
                        <div class="resbs-tab-panel" id="favorites">
                            <div class="resbs-dashboard-section">
                                <h4><?php esc_html_e('Favorite Properties', 'realestate-booking-suite'); ?></h4>
                                <div class="resbs-favorites-list">
                                    <?php $this->render_user_favorites($user_id, $sanitized_atts); ?>
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
                                <h4><?php echo esc_html(resbs_get_profile_page_title()); ?></h4>
                                <?php if (resbs_get_profile_page_subtitle()): ?>
                                    <p class="resbs-profile-subtitle"><?php echo esc_html(resbs_get_profile_page_subtitle()); ?></p>
                                <?php endif; ?>
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
            'show_amenities' => 'yes',
            'show_video' => 'yes'
        );

        // Sanitize attributes
        $atts = shortcode_atts($default_atts, $atts, 'resbs_submit_property');
        
        $sanitized_atts = array(
            'title' => RESBS_Security::sanitize_text($atts['title']),
            'show_gallery' => RESBS_Security::sanitize_bool($atts['show_gallery']),
            'show_amenities' => RESBS_Security::sanitize_bool($atts['show_amenities']),
            'show_video' => RESBS_Security::sanitize_bool($atts['show_video'])
        );

        $shortcode_id = 'resbs-shortcode-submit-' . uniqid();

        ob_start();
        ?>
        <div class="resbs-submit-widget resbs-shortcode" id="<?php echo esc_attr($shortcode_id); ?>">
            <form class="resbs-submit-form" data-target="<?php echo esc_attr($shortcode_id); ?>" enctype="multipart/form-data">
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
                            <label for="property_price_per_sqft_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Price per sq ft', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_price_per_sqft" id="property_price_per_sqft_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('Enter price per sq ft', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>

                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_price_note_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Price Note', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_price_note" id="property_price_note_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., Negotiable, Best Offer', 'realestate-booking-suite'); ?>">
                        </div>

                        <div class="resbs-form-group">
                            <label class="resbs-checkbox-label">
                                <input type="checkbox" name="property_call_for_price" id="property_call_for_price_<?php echo esc_attr($shortcode_id); ?>" value="1" class="resbs-checkbox-input">
                                <span><?php esc_html_e('Call for Price', 'realestate-booking-suite'); ?></span>
                            </label>
                        </div>
                    </div>

                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_size_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Size (sq ft)', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_size" id="property_size_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('Enter size', 'realestate-booking-suite'); ?>">
                        </div>

                        <div class="resbs-form-group">
                            <label for="property_lot_size_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Lot Size (sq ft)', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_lot_size_sqft" id="property_lot_size_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('Enter lot size', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>

                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_bedrooms_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_bedrooms" id="property_bedrooms_<?php echo esc_attr($shortcode_id); ?>" 
                                   min="0" step="1" placeholder="<?php esc_attr_e('Enter number of bedrooms', 'realestate-booking-suite'); ?>">
                        </div>

                        <div class="resbs-form-group">
                            <label for="property_bathrooms_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_bathrooms" id="property_bathrooms_<?php echo esc_attr($shortcode_id); ?>" 
                                   min="0" step="0.5" placeholder="<?php esc_attr_e('Enter number of bathrooms', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>

                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_half_baths_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Half Baths', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_half_baths" id="property_half_baths_<?php echo esc_attr($shortcode_id); ?>" 
                                   min="0" step="1" placeholder="<?php esc_attr_e('Enter number of half baths', 'realestate-booking-suite'); ?>">
                        </div>

                        <div class="resbs-form-group">
                            <label for="property_total_rooms_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Total Rooms', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_total_rooms" id="property_total_rooms_<?php echo esc_attr($shortcode_id); ?>" 
                                   min="0" step="1" placeholder="<?php esc_attr_e('Enter total number of rooms', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>

                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_floors_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Floors', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_floors" id="property_floors_<?php echo esc_attr($shortcode_id); ?>" 
                                   min="0" step="1" placeholder="<?php esc_attr_e('Enter number of floors', 'realestate-booking-suite'); ?>">
                        </div>

                        <div class="resbs-form-group">
                            <label for="property_floor_level_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Floor Level', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_floor_level" id="property_floor_level_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., Ground Floor, 2nd Floor', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>

                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_year_built_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Year Built', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_year_built" id="property_year_built_<?php echo esc_attr($shortcode_id); ?>" 
                                   min="1800" max="<?php echo esc_attr(date('Y')); ?>" placeholder="<?php esc_attr_e('e.g., 2020', 'realestate-booking-suite'); ?>">
                        </div>

                        <div class="resbs-form-group">
                            <label for="property_year_remodeled_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Year Remodeled', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_year_remodeled" id="property_year_remodeled_<?php echo esc_attr($shortcode_id); ?>" 
                                   min="1800" max="<?php echo esc_attr(date('Y')); ?>" placeholder="<?php esc_attr_e('e.g., 2023', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>

                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_status_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Property Status', 'realestate-booking-suite'); ?>
                            </label>
                            <select name="property_status" id="property_status_<?php echo esc_attr($shortcode_id); ?>">
                                <option value=""><?php esc_html_e('Select Status', 'realestate-booking-suite'); ?></option>
                                <?php
                                $property_statuses = get_terms(array(
                                    'taxonomy' => 'property_status',
                                    'hide_empty' => false,
                                ));
                                
                                // If no terms exist, create default ones
                                if (empty($property_statuses) || is_wp_error($property_statuses)) {
                                    // Create default property status terms
                                    $default_statuses = array(
                                        'For Sale',
                                        'For Rent',
                                        'Sold',
                                        'Rented',
                                        'Available',
                                        'Pending',
                                        'Under Contract'
                                    );
                                    
                                    foreach ($default_statuses as $status_name) {
                                        $term = wp_insert_term($status_name, 'property_status');
                                        if (!is_wp_error($term) && isset($term['term_id'])) {
                                            echo '<option value="' . esc_attr($term['term_id']) . '">' . esc_html($status_name) . '</option>';
                                        }
                                    }
                                    
                                    // Re-fetch terms after creating defaults
                                    $property_statuses = get_terms(array(
                                        'taxonomy' => 'property_status',
                                        'hide_empty' => false,
                                    ));
                                }
                                
                                // Display existing terms
                                if (!empty($property_statuses) && !is_wp_error($property_statuses)) {
                                    foreach ($property_statuses as $status) {
                                        echo '<option value="' . esc_attr($status->term_id) . '">' . esc_html($status->name) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="resbs-form-group">
                            <label for="property_condition_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Property Condition', 'realestate-booking-suite'); ?>
                            </label>
                            <select name="property_condition" id="property_condition_<?php echo esc_attr($shortcode_id); ?>">
                                <option value=""><?php esc_html_e('Select Condition', 'realestate-booking-suite'); ?></option>
                                <option value="excellent"><?php esc_html_e('Excellent', 'realestate-booking-suite'); ?></option>
                                <option value="good"><?php esc_html_e('Good', 'realestate-booking-suite'); ?></option>
                                <option value="fair"><?php esc_html_e('Fair', 'realestate-booking-suite'); ?></option>
                                <option value="needs-renovation"><?php esc_html_e('Needs Renovation', 'realestate-booking-suite'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="resbs-form-group" style="grid-column: 1 / -1;">
                        <label for="property_description_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Description', 'realestate-booking-suite'); ?> <span class="required">*</span>
                        </label>
                        <textarea name="property_description" id="property_description_<?php echo esc_attr($shortcode_id); ?>" 
                                  required rows="6" placeholder="<?php esc_attr_e('Enter property description', 'realestate-booking-suite'); ?>"></textarea>
                    </div>
                </div>

                <div class="resbs-form-section">
                    <h4><?php esc_html_e('Featured Image', 'realestate-booking-suite'); ?></h4>
                    <div class="resbs-form-group">
                        <label for="property_featured_image_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Featured Image', 'realestate-booking-suite'); ?>
                        </label>
                        <input type="file" name="property_featured_image" id="property_featured_image_<?php echo esc_attr($shortcode_id); ?>" 
                               accept="image/*" class="resbs-file-input">
                        <p class="resbs-input-help"><?php esc_html_e('Upload the main featured image for this property (recommended)', 'realestate-booking-suite'); ?></p>
                        <div class="resbs-featured-image-preview" style="margin-top: 10px; display: none;">
                            <img src="" alt="Preview" style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px;">
                        </div>
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

                <div class="resbs-form-section">
                    <h4><?php esc_html_e('Location', 'realestate-booking-suite'); ?></h4>
                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_address_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Address', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_address" id="property_address_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., 123 Main Street, Apt 4B', 'realestate-booking-suite'); ?>">
                        </div>
                        <div class="resbs-form-group">
                            <label for="property_city_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('City', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_city" id="property_city_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., New York', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>
                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_state_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('State/Province', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_state" id="property_state_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., NY, California', 'realestate-booking-suite'); ?>">
                        </div>
                        <div class="resbs-form-group">
                            <label for="property_zip_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('ZIP/Postal Code', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_zip" id="property_zip_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., 10001', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>
                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_country_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Country', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_country" id="property_country_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., United States', 'realestate-booking-suite'); ?>">
                        </div>
                        <div class="resbs-form-group">
                            <label class="resbs-checkbox-label">
                                <input type="checkbox" name="property_hide_address" id="property_hide_address_<?php echo esc_attr($shortcode_id); ?>" value="1" class="resbs-checkbox-input">
                                <span><?php esc_html_e('Hide Address on Public Listing', 'realestate-booking-suite'); ?></span>
                            </label>
                        </div>
                    </div>
                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_latitude_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Latitude (optional)', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_latitude" id="property_latitude_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., 40.7128', 'realestate-booking-suite'); ?>">
                        </div>
                        <div class="resbs-form-group">
                            <label for="property_longitude_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Longitude (optional)', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_longitude" id="property_longitude_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., -74.0060', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>
                </div>

                <div class="resbs-form-section">
                    <h4><?php esc_html_e('Property Features & Amenities', 'realestate-booking-suite'); ?></h4>
                    <div class="resbs-form-group" style="grid-column: 1 / -1;">
                        <label for="property_features_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Interior Features (comma separated)', 'realestate-booking-suite'); ?>
                        </label>
                        <input type="text" name="property_features" id="property_features_<?php echo esc_attr($shortcode_id); ?>" 
                               placeholder="<?php esc_attr_e('e.g., Fireplace, Hardwood Floors, Walk-in Closet, Built-in Shelves', 'realestate-booking-suite'); ?>">
                        <p class="resbs-input-help"><?php esc_html_e('These will appear under "Interior" category', 'realestate-booking-suite'); ?></p>
                    </div>
                    <div class="resbs-form-group" style="grid-column: 1 / -1;">
                        <label for="property_amenities_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Exterior Amenities (comma separated)', 'realestate-booking-suite'); ?>
                        </label>
                        <input type="text" name="property_amenities" id="property_amenities_<?php echo esc_attr($shortcode_id); ?>" 
                               placeholder="<?php esc_attr_e('e.g., Swimming Pool, Garage, Garden, Patio, Balcony', 'realestate-booking-suite'); ?>">
                        <p class="resbs-input-help"><?php esc_html_e('These will appear under "Exterior" category', 'realestate-booking-suite'); ?></p>
                    </div>
                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_parking_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Parking', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_parking" id="property_parking_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., 2 Car Garage, Street Parking', 'realestate-booking-suite'); ?>">
                        </div>
                        <div class="resbs-form-group">
                            <label for="property_heating_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Heating', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_heating" id="property_heating_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., Central Heating, Gas', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>
                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_cooling_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Cooling', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_cooling" id="property_cooling_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., Central Air, AC Units', 'realestate-booking-suite'); ?>">
                        </div>
                        <div class="resbs-form-group">
                            <label for="property_basement_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Basement', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_basement" id="property_basement_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., Finished, Unfinished, None', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>
                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_roof_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Roof', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_roof" id="property_roof_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., Shingle, Tile, Metal', 'realestate-booking-suite'); ?>">
                        </div>
                        <div class="resbs-form-group">
                            <label for="property_exterior_material_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Exterior Material', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_exterior_material" id="property_exterior_material_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., Brick, Vinyl, Stucco', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>
                    <div class="resbs-form-group" style="grid-column: 1 / -1;">
                        <label for="property_floor_covering_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Floor Covering', 'realestate-booking-suite'); ?>
                        </label>
                        <input type="text" name="property_floor_covering" id="property_floor_covering_<?php echo esc_attr($shortcode_id); ?>" 
                               placeholder="<?php esc_attr_e('e.g., Hardwood, Carpet, Tile', 'realestate-booking-suite'); ?>">
                    </div>
                </div>

                <div class="resbs-form-section">
                    <h4><?php esc_html_e('Nearby Features', 'realestate-booking-suite'); ?></h4>
                    <div class="resbs-form-group" style="grid-column: 1 / -1;">
                        <label for="property_nearby_schools_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Nearby Schools', 'realestate-booking-suite'); ?>
                        </label>
                        <textarea name="property_nearby_schools" id="property_nearby_schools_<?php echo esc_attr($shortcode_id); ?>" 
                                  rows="3" placeholder="<?php esc_attr_e('List nearby schools and their distances', 'realestate-booking-suite'); ?>"></textarea>
                    </div>
                    <div class="resbs-form-group" style="grid-column: 1 / -1;">
                        <label for="property_nearby_shopping_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Nearby Shopping', 'realestate-booking-suite'); ?>
                        </label>
                        <textarea name="property_nearby_shopping" id="property_nearby_shopping_<?php echo esc_attr($shortcode_id); ?>" 
                                  rows="3" placeholder="<?php esc_attr_e('List nearby shopping centers and malls', 'realestate-booking-suite'); ?>"></textarea>
                    </div>
                    <div class="resbs-form-group" style="grid-column: 1 / -1;">
                        <label for="property_nearby_restaurants_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Nearby Restaurants', 'realestate-booking-suite'); ?>
                        </label>
                        <textarea name="property_nearby_restaurants" id="property_nearby_restaurants_<?php echo esc_attr($shortcode_id); ?>" 
                                  rows="3" placeholder="<?php esc_attr_e('List nearby restaurants and dining options', 'realestate-booking-suite'); ?>"></textarea>
                    </div>
                </div>

                <?php if ($sanitized_atts['show_video']): ?>
                <div class="resbs-form-section">
                    <h4><?php esc_html_e('Video & Virtual Tour', 'realestate-booking-suite'); ?></h4>
                    <div class="resbs-form-group" style="grid-column: 1 / -1;">
                        <label for="property_video_url_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Video URL', 'realestate-booking-suite'); ?>
                        </label>
                        <input type="url" name="property_video_url" id="property_video_url_<?php echo esc_attr($shortcode_id); ?>" 
                               placeholder="<?php esc_attr_e('e.g., https://www.youtube.com/watch?v=...', 'realestate-booking-suite'); ?>">
                    </div>
                    <div class="resbs-form-group" style="grid-column: 1 / -1;">
                        <label for="property_video_embed_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Video Embed Code', 'realestate-booking-suite'); ?>
                        </label>
                        <textarea name="property_video_embed" id="property_video_embed_<?php echo esc_attr($shortcode_id); ?>" 
                                  rows="4" placeholder="<?php esc_attr_e('Paste iframe embed code here', 'realestate-booking-suite'); ?>"></textarea>
                    </div>
                    <div class="resbs-form-group" style="grid-column: 1 / -1;">
                        <label for="property_virtual_tour_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Virtual Tour URL', 'realestate-booking-suite'); ?>
                        </label>
                        <input type="url" name="property_virtual_tour" id="property_virtual_tour_<?php echo esc_attr($shortcode_id); ?>" 
                               placeholder="<?php esc_attr_e('e.g., https://my360tour.com/...', 'realestate-booking-suite'); ?>">
                    </div>
                </div>
                <?php endif; ?>

                <div class="resbs-form-section">
                    <h4><?php esc_html_e('Agent Information', 'realestate-booking-suite'); ?></h4>
                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_agent_name_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Agent Name', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_agent_name" id="property_agent_name_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('Enter agent name', 'realestate-booking-suite'); ?>">
                        </div>
                        <div class="resbs-form-group">
                            <label for="property_agent_phone_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Agent Phone', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="tel" name="property_agent_phone" id="property_agent_phone_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., +1 234 567 8900', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>
                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_agent_email_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Agent Email', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="email" name="property_agent_email" id="property_agent_email_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., agent@example.com', 'realestate-booking-suite'); ?>">
                        </div>
                        <div class="resbs-form-group">
                            <label for="property_agent_experience_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Years of Experience', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="number" name="property_agent_experience" id="property_agent_experience_<?php echo esc_attr($shortcode_id); ?>" 
                                   min="0" placeholder="<?php esc_attr_e('e.g., 10', 'realestate-booking-suite'); ?>">
                        </div>
                    </div>
                    <div class="resbs-form-row">
                        <div class="resbs-form-group">
                            <label for="property_agent_response_time_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Response Time', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" name="property_agent_response_time" id="property_agent_response_time_<?php echo esc_attr($shortcode_id); ?>" 
                                   placeholder="<?php esc_attr_e('e.g., < 1 Hour', 'realestate-booking-suite'); ?>">
                        </div>
                        <div class="resbs-form-group">
                            <label for="property_agent_photo_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Agent Photo', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="file" name="property_agent_photo" id="property_agent_photo_<?php echo esc_attr($shortcode_id); ?>" 
                                   accept="image/*" class="resbs-file-input">
                            <p class="resbs-input-help"><?php esc_html_e('Upload agent profile photo', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>

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
     * Login Form Shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function login_form_shortcode($atts) {
        // Check if login form is enabled in settings
        $enable_login_form_option = get_option('resbs_enable_login_form', 0);
        
        // Check if the option is explicitly enabled (value should be '1' or 1)
        if (empty($enable_login_form_option) || $enable_login_form_option !== '1') {
            return '<div class="resbs-login-form-disabled">
                <p>' . esc_html__('Login form is currently disabled. Please contact the site administrator.', 'realestate-booking-suite') . '</p>
            </div>';
        }

        // If we reach here, login form is enabled
        $enable_login_form = true;

        // If user is already logged in, show logout option
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $logout_url = wp_logout_url(get_permalink());
            
            ob_start();
            ?>
            <div class="resbs-login-form-wrapper">
                <div class="resbs-login-form-logged-in">
                    <p><?php echo sprintf(esc_html__('You are already logged in as %s.', 'realestate-booking-suite'), esc_html($current_user->display_name)); ?></p>
                    <a href="<?php echo esc_url($logout_url); ?>" class="resbs-logout-btn">
                        <?php esc_html_e('Log Out', 'realestate-booking-suite'); ?>
                    </a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        // Get settings
        $page_title = get_option('resbs_signin_page_title', 'Sign in or register');
        $page_subtitle = get_option('resbs_signin_page_subtitle', 'to save your favourite homes and more');
        $enable_buyer_signup = get_option('resbs_enable_signup_buyers', 0) === '1';
        
        $login_url = wp_login_url(get_permalink());
        
        // Check if there's a custom buyer registration page
        $buyer_registration_page = get_page_by_path('register');
        if ($buyer_registration_page && $enable_buyer_signup) {
            $register_url = get_permalink($buyer_registration_page);
        } else {
            $register_url = wp_registration_url();
        }
        
        $lost_password_url = wp_lostpassword_url();
        
        $shortcode_id = 'resbs-login-form-' . uniqid();
        
        ob_start();
        ?>
        <div class="resbs-login-form-wrapper" id="<?php echo esc_attr($shortcode_id); ?>">
            <?php if ($page_title || $page_subtitle): ?>
                <div class="resbs-login-form-header">
                    <?php if ($page_title): ?>
                        <h2 class="resbs-login-form-title"><?php echo esc_html($page_title); ?></h2>
                    <?php endif; ?>
                    <?php if ($page_subtitle): ?>
                        <p class="resbs-login-form-subtitle"><?php echo esc_html($page_subtitle); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="resbs-login-form-content">
                <?php if ($enable_login_form): ?>
                    <form class="resbs-auth-form resbs-login-form" method="post" action="<?php echo esc_url($login_url); ?>">
                        <?php
                        // Show any login errors
                        if (isset($_GET['login']) && $_GET['login'] === 'failed') {
                            echo '<div class="resbs-login-error">';
                            echo '<p>' . esc_html__('Invalid username or password.', 'realestate-booking-suite') . '</p>';
                            echo '</div>';
                        }
                        ?>
                        
                        <div class="resbs-form-field">
                            <label for="login_username_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Username or Email', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" 
                                   name="log" 
                                   id="login_username_<?php echo esc_attr($shortcode_id); ?>" 
                                   required
                                   autocomplete="username">
                        </div>
                        
                        <div class="resbs-form-field">
                            <label for="login_password_<?php echo esc_attr($shortcode_id); ?>">
                                <?php esc_html_e('Password', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="password" 
                                   name="pwd" 
                                   id="login_password_<?php echo esc_attr($shortcode_id); ?>" 
                                   required
                                   autocomplete="current-password">
                        </div>
                        
                        <div class="resbs-form-field resbs-remember-field">
                            <label>
                                <input type="checkbox" name="rememberme" value="forever">
                                <?php esc_html_e('Remember Me', 'realestate-booking-suite'); ?>
                            </label>
                        </div>
                        
                        <input type="hidden" name="redirect_to" value="<?php echo esc_url(get_permalink()); ?>">
                        
                        <button type="submit" class="resbs-auth-btn resbs-login-submit-btn">
                            <?php esc_html_e('LOG IN', 'realestate-booking-suite'); ?>
                        </button>
                        
                        <div class="resbs-auth-links">
                            <a href="<?php echo esc_url($lost_password_url); ?>">
                                <?php esc_html_e('Forgot Password?', 'realestate-booking-suite'); ?>
                            </a>
                        </div>
                    </form>
                    
                    <?php if (get_option('users_can_register') || $enable_buyer_signup): ?>
                        <div class="resbs-register-section">
                            <div class="resbs-register-divider">
                                <span><?php esc_html_e('OR', 'realestate-booking-suite'); ?></span>
                            </div>
                            <p class="resbs-register-text">
                                <?php esc_html_e("Don't have an account?", 'realestate-booking-suite'); ?>
                            </p>
                            <a href="<?php echo esc_url($register_url); ?>" class="resbs-register-btn">
                                <?php esc_html_e('Create Account', 'realestate-booking-suite'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    /**
     * Buyer Registration Shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string Shortcode output
     */
    public function buyer_registration_shortcode($atts) {
        // Form submission is now handled by handle_buyer_registration_submit() at template_redirect hook
        // This prevents "headers already sent" errors
        
        // Check if buyer signup is enabled
        $enable_buyer_signup = get_option('resbs_enable_signup_buyers', 0) === '1';
        
        if (!$enable_buyer_signup) {
            return '<div class="resbs-registration-disabled">
                <p>' . esc_html__('Buyer registration is currently disabled. Please contact the site administrator.', 'realestate-booking-suite') . '</p>
            </div>';
        }

        // Check if user is already logged in
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            $logout_url = wp_logout_url(get_permalink());
            
            ob_start();
            ?>
            <div class="resbs-registration-wrapper">
                <div class="resbs-registration-logged-in">
                    <p><?php echo sprintf(esc_html__('You are already logged in as %s.', 'realestate-booking-suite'), esc_html($current_user->display_name)); ?></p>
                    <a href="<?php echo esc_url($logout_url); ?>" class="resbs-logout-btn">
                        <?php esc_html_e('Log Out', 'realestate-booking-suite'); ?>
                    </a>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        // Get settings
        $page_title = get_option('resbs_buyer_signup_title', 'Get started with your account');
        $page_subtitle = get_option('resbs_buyer_signup_subtitle', 'to save your favourite homes and more');
        
        $shortcode_id = 'resbs-buyer-registration-' . uniqid();
        
        // Get errors from transient if they exist
        $errors = new WP_Error();
        if (isset($_GET['reg_errors'])) {
            $transient_key = sanitize_text_field($_GET['reg_errors']);
            $error_messages = get_transient($transient_key);
            if ($error_messages) {
                foreach ($error_messages as $message) {
                    $errors->add('registration_error', $message);
                }
                delete_transient($transient_key);
            }
        }
        
        // Check for verification success/error messages
        $verification_message = '';
        $verification_type = '';
        
        if (isset($_GET['reg_success'])) {
            $success_key = sanitize_text_field($_GET['reg_success']);
            $success_data = get_transient($success_key);
            if ($success_data === 'email_verification') {
                $verification_message = esc_html__('Registration successful! Please check your email to verify your account. Click the verification link in the email to activate your account.', 'realestate-booking-suite');
                $verification_type = 'success';
                delete_transient($success_key);
            }
        }
        
        if (isset($_GET['verify_success'])) {
            $verify_key = sanitize_text_field($_GET['verify_success']);
            $verify_data = get_transient($verify_key);
            if ($verify_data === 'verified') {
                $verification_message = esc_html__('Email verified successfully! Your account has been activated. You are now logged in.', 'realestate-booking-suite');
                $verification_type = 'success';
                delete_transient($verify_key);
            } elseif ($verify_data === 'already_verified') {
                $verification_message = esc_html__('Your email is already verified. You can log in now.', 'realestate-booking-suite');
                $verification_type = 'info';
                delete_transient($verify_key);
            }
        }
        
        if (isset($_GET['verify_error'])) {
            $error_key = sanitize_text_field($_GET['verify_error']);
            $error_data = get_transient($error_key);
            if ($error_data === 'invalid_token') {
                $verification_message = esc_html__('Invalid verification link. Please check your email or request a new verification link.', 'realestate-booking-suite');
                $verification_type = 'error';
                delete_transient($error_key);
            } elseif ($error_data === 'expired_token') {
                $verification_message = esc_html__('Verification link has expired. Please register again or contact support.', 'realestate-booking-suite');
                $verification_type = 'error';
                delete_transient($error_key);
            }
        }
        
        ob_start();
        ?>
        <style>
        /* Professional Registration Form Styles - Inline to ensure they load */
        .resbs-registration-wrapper {
            max-width: 500px !important;
            margin: 60px auto !important;
            padding: 0 20px !important;
            box-sizing: border-box !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }
        .resbs-registration-header {
            text-align: center !important;
            margin-bottom: 40px !important;
        }
        .resbs-registration-title {
            font-size: 2.25rem !important;
            font-weight: 700 !important;
            color: #1a1a1a !important;
            margin: 0 0 12px 0 !important;
            letter-spacing: -0.5px !important;
            line-height: 1.2 !important;
        }
        .resbs-registration-subtitle {
            font-size: 16px !important;
            color: #6b7280 !important;
            margin: 0 !important;
            line-height: 1.6 !important;
            font-weight: 400 !important;
        }
        .resbs-registration-content {
            background: #ffffff !important;
            border-radius: 12px !important;
            padding: 40px !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            border: 1px solid #e5e7eb !important;
        }
        .resbs-registration-form .resbs-form-field {
            margin-bottom: 24px !important;
        }
        .resbs-registration-form .resbs-form-field label {
            display: block !important;
            margin-bottom: 8px !important;
            font-weight: 600 !important;
            color: #374151 !important;
            font-size: 14px !important;
            line-height: 1.5 !important;
        }
        .resbs-registration-form .resbs-form-field input[type="text"],
        .resbs-registration-form .resbs-form-field input[type="email"],
        .resbs-registration-form .resbs-form-field input[type="password"] {
            width: 100% !important;
            padding: 14px 16px !important;
            border: 1.5px solid #d1d5db !important;
            border-radius: 8px !important;
            font-size: 15px !important;
            color: #1f2937 !important;
            background: #ffffff !important;
            transition: all 0.2s ease !important;
            box-sizing: border-box !important;
            font-family: inherit !important;
            line-height: 1.5 !important;
        }
        .resbs-registration-form .resbs-form-field input:focus {
            outline: none !important;
            border-color: #0073aa !important;
            box-shadow: 0 0 0 3px rgba(0, 115, 170, 0.1) !important;
        }
        .resbs-registration-submit-btn {
            width: 100% !important;
            padding: 16px 24px !important;
            background: #0073aa !important;
            color: #ffffff !important;
            border: none !important;
            border-radius: 8px !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            cursor: pointer !important;
            transition: all 0.3s ease !important;
            margin-top: 12px !important;
            box-shadow: 0 2px 4px rgba(0, 115, 170, 0.2) !important;
        }
        .resbs-registration-submit-btn:hover {
            background: #005a87 !important;
            box-shadow: 0 4px 8px rgba(0, 115, 170, 0.3) !important;
            transform: translateY(-1px) !important;
        }
        .resbs-registration-footer {
            margin-top: 24px !important;
            text-align: center !important;
        }
        .resbs-registration-login-link {
            font-size: 14px !important;
            color: #6b7280 !important;
            margin: 0 !important;
        }
        .resbs-registration-login-link a {
            color: #0073aa !important;
            text-decoration: none !important;
            font-weight: 600 !important;
        }
        .resbs-registration-login-link a:hover {
            text-decoration: underline !important;
        }
        .resbs-registration-error {
            background: #fef2f2 !important;
            border: 1px solid #fecaca !important;
            border-radius: 8px !important;
            padding: 12px 16px !important;
            margin-bottom: 20px !important;
            color: #991b1b !important;
        }
        .resbs-registration-error p {
            margin: 4px 0 !important;
            font-size: 14px !important;
        }
        .resbs-registration-success {
            background: #f0fdf4 !important;
            border: 1px solid #bbf7d0 !important;
            border-radius: 8px !important;
            padding: 12px 16px !important;
            margin-bottom: 20px !important;
            color: #166534 !important;
        }
        .resbs-registration-success p {
            margin: 4px 0 !important;
            font-size: 14px !important;
        }
        .resbs-registration-info {
            background: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            border-radius: 8px !important;
            padding: 12px 16px !important;
            margin-bottom: 20px !important;
            color: #1e40af !important;
        }
        .resbs-registration-info p {
            margin: 4px 0 !important;
            font-size: 14px !important;
        }
        @media (max-width: 768px) {
            .resbs-registration-wrapper {
                margin: 20px auto !important;
                padding: 0 16px !important;
            }
            .resbs-registration-content {
                padding: 24px !important;
            }
            .resbs-registration-title {
                font-size: 1.75rem !important;
            }
        }
        </style>
        <div class="resbs-registration-wrapper" id="<?php echo esc_attr($shortcode_id); ?>">
            <?php if ($page_title || $page_subtitle): ?>
                <div class="resbs-registration-header">
                    <?php if ($page_title): ?>
                        <h2 class="resbs-registration-title"><?php echo esc_html($page_title); ?></h2>
                    <?php endif; ?>
                    <?php if ($page_subtitle): ?>
                        <p class="resbs-registration-subtitle"><?php echo esc_html($page_subtitle); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="resbs-registration-content">
                <?php if (is_wp_error($errors) && $errors->has_errors()): ?>
                    <div class="resbs-registration-error">
                        <?php foreach ($errors->get_error_messages() as $message): ?>
                            <p><?php echo esc_html($message); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($verification_message)): ?>
                    <div class="resbs-registration-<?php echo esc_attr($verification_type); ?>">
                        <p><?php echo esc_html($verification_message); ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (empty($verification_message) || $verification_type === 'error'): ?>
                <form class="resbs-registration-form" method="post" action="<?php echo esc_url(get_permalink()); ?>">
                    <?php wp_nonce_field('resbs_buyer_register', 'resbs_register_nonce'); ?>
                    
                    <div class="resbs-form-field">
                        <label for="user_login_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Username', 'realestate-booking-suite'); ?> <span class="required">*</span>
                        </label>
                        <input type="text" 
                               name="user_login" 
                               id="user_login_<?php echo esc_attr($shortcode_id); ?>" 
                               value="<?php echo isset($_POST['user_login']) ? esc_attr($_POST['user_login']) : ''; ?>"
                               required
                               autocomplete="username">
                    </div>
                    
                    <div class="resbs-form-field">
                        <label for="user_email_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Email Address', 'realestate-booking-suite'); ?> <span class="required">*</span>
                        </label>
                        <input type="email" 
                               name="user_email" 
                               id="user_email_<?php echo esc_attr($shortcode_id); ?>" 
                               value="<?php echo isset($_POST['user_email']) ? esc_attr($_POST['user_email']) : ''; ?>"
                               required
                               autocomplete="email">
                    </div>
                    
                    <div class="resbs-form-field">
                        <label for="user_pass_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Password', 'realestate-booking-suite'); ?> <span class="required">*</span>
                        </label>
                        <input type="password" 
                               name="user_pass" 
                               id="user_pass_<?php echo esc_attr($shortcode_id); ?>" 
                               required
                               autocomplete="new-password"
                               minlength="6">
                    </div>
                    
                    <div class="resbs-form-field">
                        <label for="user_pass_confirm_<?php echo esc_attr($shortcode_id); ?>">
                            <?php esc_html_e('Confirm Password', 'realestate-booking-suite'); ?> <span class="required">*</span>
                        </label>
                        <input type="password" 
                               name="user_pass_confirm" 
                               id="user_pass_confirm_<?php echo esc_attr($shortcode_id); ?>" 
                               required
                               autocomplete="new-password"
                               minlength="6">
                    </div>
                    
                    <button type="submit" class="resbs-registration-submit-btn">
                        <?php esc_html_e('Create Account', 'realestate-booking-suite'); ?>
                    </button>
                </form>
                <?php endif; ?>
                
                <?php if (empty($verification_message) || $verification_type === 'error'): ?>
                <div class="resbs-registration-footer">
                    <p class="resbs-registration-login-link">
                        <?php esc_html_e('Already have an account?', 'realestate-booking-suite'); ?>
                        <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>">
                            <?php esc_html_e('Log in', 'realestate-booking-suite'); ?>
                        </a>
                    </p>
                </div>
                <?php endif; ?>
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
    private function render_property_card($atts = array()) {
        // Set defaults
        $defaults = array(
            'layout' => 'grid',
            'show_price' => true,
            'show_meta' => true,
            'show_excerpt' => false,
            'show_badges' => true,
            'show_favorite_button' => false,
            'show_book_button' => true
        );
        $atts = wp_parse_args($atts, $defaults);
        
        $property_id = get_the_ID();
        $property_price = get_post_meta($property_id, '_property_price', true);
        $property_bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $property_bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        $property_size = get_post_meta($property_id, '_property_size', true);
        $property_area_sqft = get_post_meta($property_id, '_property_area_sqft', true);
        $property_featured = get_post_meta($property_id, '_property_featured', true);
        $property_status = get_the_terms($property_id, 'property_status');
        $property_type = get_the_terms($property_id, 'property_type');
        $property_location = get_the_terms($property_id, 'property_location');
        
        // Use area_sqft if size is not available
        if (empty($property_size) && !empty($property_area_sqft)) {
            $property_size = $property_area_sqft;
        }

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

                <?php if (!empty($atts['show_price']) && !empty($property_price)): ?>
                    <div class="resbs-property-price">
                        <?php 
                        // Format price with dynamic currency symbol
                        echo esc_html(resbs_format_price($property_price));
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($atts['show_meta'])): ?>
                    <div class="resbs-property-meta">
                        <?php if (!empty($property_bedrooms)): ?>
                            <div class="resbs-property-meta-item">
                                <i class="fas fa-bed"></i>
                                <span><?php echo esc_html($property_bedrooms); ?> <?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_bathrooms)): ?>
                            <div class="resbs-property-meta-item">
                                <i class="fas fa-bath"></i>
                                <span><?php echo esc_html($property_bathrooms); ?> <?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_size)): ?>
                            <div class="resbs-property-meta-item">
                                <i class="fas fa-ruler-combined"></i>
                                <span><?php echo esc_html(resbs_format_area($property_size)); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($property_location) && !is_wp_error($property_location)): ?>
                            <div class="resbs-property-meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo esc_html($property_location[0]->name); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($atts['show_excerpt'])): ?>
                    <div class="resbs-property-excerpt">
                        <?php echo wp_kses_post(wp_trim_words(get_the_excerpt(), 20)); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($atts['show_book_button'])): ?>
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
            'post_status' => array('publish', 'pending', 'draft'), // Show all statuses
            'author' => $user_id,
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        if (!empty($properties)) {
            foreach ($properties as $property) {
                $this->render_property_card_for_user($property);
            }
        } else {
            echo '<div class="resbs-no-properties">';
            echo '<i class="fas fa-home" style="font-size: 48px; color: #ccc; margin-bottom: 20px;"></i>';
            echo '<p>' . esc_html__('You haven\'t submitted any properties yet.', 'realestate-booking-suite') . '</p>';
            $submit_url = resbs_get_submit_property_page_url();
            if ($submit_url) {
                echo '<a href="' . esc_url($submit_url) . '" class="resbs-submit-first-btn">';
                echo esc_html__('Submit Your First Property', 'realestate-booking-suite');
                echo '</a>';
            }
            echo '</div>';
        }
    }
    
    /**
     * Render property card for user dashboard - using archive page design EXACTLY
     */
    private function render_property_card_for_user($property) {
        $property_id = $property->ID;
        
        // Get featured image - same as archive
        $featured_image = get_the_post_thumbnail_url($property_id, 'medium');
        if (!$featured_image) {
            $gallery = get_post_meta($property_id, '_property_gallery', true);
            if ($gallery) {
                $gallery_array = is_array($gallery) ? $gallery : explode(',', $gallery);
                if (!empty($gallery_array[0])) {
                    $featured_image = wp_get_attachment_image_url($gallery_array[0], 'medium');
                }
            }
        }
        
        // Get meta data - using EXACT same keys as archive
        $price = get_post_meta($property_id, '_property_price', true);
        $bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
        $bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
        $area_sqft = get_post_meta($property_id, '_property_area_sqft', true);
        if (!$area_sqft) {
            $area_sqft = get_post_meta($property_id, '_property_size', true);
        }
        
        // Get address and location - same as archive
        $address = get_post_meta($property_id, '_property_address', true);
        $city = get_post_meta($property_id, '_property_city', true);
        $state = get_post_meta($property_id, '_property_state', true);
        $zip = get_post_meta($property_id, '_property_zip', true);
        
        // Build location string like archive does
        $location_parts = array();
        if ($address) $location_parts[] = $address;
        if ($city) $location_parts[] = $city;
        if ($state) $location_parts[] = $state;
        if ($zip) $location_parts[] = $zip;
        $location = !empty($location_parts) ? implode(', ', $location_parts) : '';
        
        // If no location from meta, try location taxonomy
        if (empty($location)) {
            $location_terms = get_the_terms($property_id, 'property_location');
            if ($location_terms && !is_wp_error($location_terms)) {
                $location = $location_terms[0]->name;
            }
        }
        
        // Get property type and status from taxonomies - same as archive
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
        
        // If no status from taxonomy, use post status
        if (empty($property_status_name)) {
            $status = $property->post_status;
            $status_labels = array(
                'publish' => __('Published', 'realestate-booking-suite'),
                'pending' => __('Pending Review', 'realestate-booking-suite'),
                'draft' => __('Draft', 'realestate-booking-suite')
            );
            $property_status_name = isset($status_labels[$status]) ? $status_labels[$status] : ucfirst($status);
        }
        
        // Format price with dynamic currency
        $formatted_price = '';
        if ($price) {
            $formatted_price = resbs_format_price($price);
        }
        
        // Badge class based on post status
        $status = $property->post_status;
        $post_date = get_the_date('Y-m-d', $property_id);
        $days_old = (time() - strtotime($post_date)) / (60 * 60 * 24);
        
        if ($status === 'pending') {
            $badge_class = 'badge-new';
            $badge_text = 'Pending Review';
        } elseif ($status === 'draft') {
            $badge_class = 'badge-standard';
            $badge_text = 'Draft';
        } elseif ($days_old < 7) {
            $badge_class = 'badge-new';
            $badge_text = 'Just listed';
        } elseif ($days_old < 30) {
            $badge_class = 'badge-featured';
            $badge_text = 'Featured';
        } else {
            $badge_class = 'badge-standard';
            $badge_text = 'Available';
        }
        ?>
        <div class="property-card" data-property-id="<?php echo esc_attr($property_id); ?>">
            <div class="property-image">
                <?php if ($featured_image): ?>
                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr($property->post_title); ?>">
                <?php else: ?>
                    <div class="no-image-placeholder" style="background: #f3f4f6; display: flex; align-items: center; justify-content: center; min-height: 250px;">
                        <i class="fas fa-home" style="font-size: 48px; color: #9ca3af;"></i>
                    </div>
                <?php endif; ?>
                <div class="gradient-overlay"></div>
                <div class="property-badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($badge_text); ?></div>
                
                <?php if (resbs_is_wishlist_enabled()): 
                    $is_favorited = resbs_is_property_favorited($property_id);
                ?>
                <button class="favorite-btn resbs-favorite-btn <?php echo esc_attr($is_favorited ? 'favorited' : ''); ?>" data-property-id="<?php echo esc_attr($property_id); ?>">
                    <i class="<?php echo esc_attr($is_favorited ? 'fas' : 'far'); ?> fa-heart"></i>
                </button>
                <?php endif; ?>
                
                <div class="property-info-overlay">
                    <h3 class="property-title"><?php echo esc_html($property->post_title); ?></h3>
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
                    <?php if ($property_type_name): ?>
                        <span class="property-type"><?php echo esc_html($property_type_name); ?></span>
                    <?php endif; ?>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <?php if ($status === 'pending' && current_user_can('publish_posts')): ?>
                            <?php wp_nonce_field('resbs_publish_property', 'resbs_publish_property_nonce', false); ?>
                            <button class="publish-property-btn" data-property-id="<?php echo esc_attr($property_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('resbs_publish_property')); ?>" style="background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; transition: all 0.3s;">
                                <i class="fas fa-check"></i> Publish
                            </button>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(get_permalink($property_id)); ?>" class="view-details-btn">
                            View Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render user favorites
     * 
     * @param int $user_id User ID
     * @param array $atts Sanitized attributes
     */
    private function render_user_favorites($user_id, $atts = array()) {
        // Use global favorites manager if available
        $favorites = array();
        
        if (isset($GLOBALS['resbs_favorites_manager'])) {
            $favorites_manager = $GLOBALS['resbs_favorites_manager'];
            // Get favorites for the specific user
            if (is_user_logged_in() && get_current_user_id() == $user_id) {
                $favorites = $favorites_manager->get_user_favorites();
            } else {
                // For other users, use user meta (correct key is 'resbs_favorites')
                $favorites = get_user_meta($user_id, 'resbs_favorites', true);
                if (!is_array($favorites)) {
                    $favorites = array();
                }
            }
        } else {
            // Fallback to user meta (correct key is 'resbs_favorites')
            $favorites = get_user_meta($user_id, 'resbs_favorites', true);
            if (!is_array($favorites)) {
                $favorites = array();
            }
        }
        
        if (!empty($favorites) && is_array($favorites)) {
            // Filter out invalid IDs
            $favorites = array_filter(array_map('intval', $favorites));
            
            if (!empty($favorites)) {
                $properties = get_posts(array(
                    'post_type' => 'property',
                    'post_status' => 'publish',
                    'post__in' => $favorites,
                    'posts_per_page' => -1,
                    'orderby' => 'post__in'
                ));

                if (!empty($properties)) {
                    // Set default attributes for property cards if not provided
                    $default_atts = array(
                        'layout' => 'grid',
                        'show_price' => true,
                        'show_meta' => true,
                        'show_excerpt' => false,
                        'show_badges' => true,
                        'show_favorite_button' => false,
                        'show_book_button' => true
                    );
                    $card_atts = wp_parse_args($atts, $default_atts);
                    
                    echo '<div class="resbs-favorites-grid">';
                    foreach ($properties as $property) {
                        setup_postdata($property);
                        $this->render_property_card($card_atts);
                    }
                    wp_reset_postdata();
                    echo '</div>';
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
        // Check if WooCommerce bookings exist
        $bookings = array();
        
        // Try to get bookings from WooCommerce orders
        if (class_exists('WooCommerce')) {
            $customer_orders = wc_get_orders(array(
                'customer_id' => $user_id,
                'limit' => 20,
                'orderby' => 'date',
                'order' => 'DESC'
            ));
            
            if (!empty($customer_orders)) {
                echo '<div class="resbs-bookings-list">';
                foreach ($customer_orders as $order) {
                    $order_id = $order->get_id();
                    $order_date = $order->get_date_created()->date_i18n(get_option('date_format'));
                    $order_total = $order->get_formatted_order_total();
                    $order_status = $order->get_status();
                    
                    echo '<div class="resbs-booking-item">';
                    echo '<div class="resbs-booking-header">';
                    echo '<h5><a href="' . esc_url($order->get_view_order_url()) . '">' . sprintf(esc_html__('Order #%s', 'realestate-booking-suite'), esc_html($order_id)) . '</a></h5>';
                    echo '<span class="resbs-booking-status status-' . esc_attr($order_status) . '">' . esc_html(ucfirst($order_status)) . '</span>';
                    echo '</div>';
                    echo '<p class="resbs-booking-date">' . esc_html__('Date:', 'realestate-booking-suite') . ' ' . esc_html($order_date) . '</p>';
                    echo '<p class="resbs-booking-total">' . esc_html__('Total:', 'realestate-booking-suite') . ' ' . wp_kses_post($order_total) . '</p>';
                    echo '</div>';
                }
                echo '</div>';
                return;
            }
        }
        
        // No bookings found
        echo '<div class="resbs-bookings-placeholder">';
        echo '<p>' . esc_html__('No bookings found. Your booking history will appear here.', 'realestate-booking-suite') . '</p>';
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
