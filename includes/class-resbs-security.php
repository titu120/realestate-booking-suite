<?php
/**
 * Security Helper Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Security Helper Class
 * 
 * This class provides comprehensive security functions for the RealEstate Booking Suite plugin.
 * 
 * SECURITY BEST PRACTICES:
 * 
 * 1. NONCES (CSRF Protection):
 *    - ALWAYS use nonces for forms that modify data (POST requests)
 *    - ALWAYS use nonces for AJAX requests that modify data
 *    - Use verify_nonce() for regular form submissions
 *    - Use verify_ajax_nonce() for AJAX handlers
 *    - Use verify_rest_nonce() for REST API endpoints
 * 
 * 2. CAPABILITIES (User Permissions):
 *    - ALWAYS check capabilities for admin pages (use 'manage_options' for settings)
 *    - Check capabilities for actions that modify data
 *    - Use check_capability() for admin pages
 *    - Use verify_nonce_and_capability() for admin form submissions
 *    - Use verify_ajax_nonce_and_capability() for admin AJAX handlers
 * 
 * 3. OWNERSHIP VERIFICATION:
 *    - ALWAYS verify users can only modify their own content
 *    - Use verify_post_ownership() to check if user owns a post
 *    - Combine with nonce checks for complete security
 * 
 * 4. QUICK SECURITY WRAPPERS:
 *    - Use admin_page_security_check() at start of admin page callbacks
 *    - Use ajax_security_check() at start of AJAX handlers
 * 
 * EXAMPLE USAGE:
 * 
 * // Admin page callback
 * public function settings_page() {
 *     RESBS_Security::admin_page_security_check('manage_options', 'resbs_save_settings');
 *     // ... rest of code
 * }
 * 
 * // AJAX handler
 * public function handle_ajax() {
 *     RESBS_Security::ajax_security_check('resbs_ajax_action', 'edit_posts', true);
 *     // ... rest of code
 * }
 * 
 * // Form submission
 * public function handle_form() {
 *     $nonce = $_POST['_wpnonce'];
 *     RESBS_Security::verify_nonce_and_capability($nonce, 'form_action', 'manage_options');
 *     // ... rest of code
 * }
 * 
 * // User content modification
 * public function edit_property($property_id) {
 *     $nonce = $_POST['nonce'];
 *     RESBS_Security::verify_ajax_nonce($nonce, 'edit_property');
 *     if (!RESBS_Security::verify_post_ownership($property_id)) {
 *         wp_send_json_error(array('message' => 'Permission denied'));
 *     }
 *     // ... rest of code
 * }
 */
class RESBS_Security {

    /**
     * Verify nonce for forms
     * 
     * Use this for regular form submissions (POST requests)
     * Always use nonces for forms that modify data
     * 
     * @param string $nonce The nonce value to verify (do NOT sanitize before passing)
     * @param string $action The action name used when creating the nonce
     * @param bool $die Whether to die on failure (default: true)
     * @return bool True if nonce is valid, false otherwise
     */
    public static function verify_nonce($nonce, $action, $die = true) {
        // Note: nonce should not be sanitized before verification
        $action = sanitize_text_field($action);
        if (!wp_verify_nonce($nonce, $action)) {
            if ($die) {
                wp_die(
                    esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite'),
                    esc_html__('Security Error', 'realestate-booking-suite'),
                    array('response' => 403)
                );
            }
            return false;
        }
        return true;
    }

    /**
     * Verify AJAX nonce
     * 
     * Use this for AJAX requests (wp_ajax_* actions)
     * Always use nonces for AJAX handlers that modify data
     * 
     * @param string $nonce The nonce value to verify (do NOT sanitize before passing)
     * @param string $action The action name used when creating the nonce
     * @return bool True if nonce is valid, false otherwise (sends JSON error on failure)
     */
    public static function verify_ajax_nonce($nonce, $action) {
        // Note: nonce should not be sanitized before verification
        $action = sanitize_text_field($action);
        if (!wp_verify_nonce($nonce, $action)) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed.', 'realestate-booking-suite')
            ));
        }
        return true;
    }

    /**
     * Check user capabilities
     * 
     * Use this for admin pages and actions that require specific permissions
     * Common capabilities: 'manage_options', 'edit_posts', 'edit_pages', 'publish_posts'
     * 
     * @param string $capability The capability to check (default: 'manage_options')
     * @param bool $die Whether to die on failure (default: true)
     * @return bool True if user has capability, false otherwise
     */
    public static function check_capability($capability = 'manage_options', $die = true) {
        if (!current_user_can($capability)) {
            if ($die) {
                wp_die(
                    esc_html__('You do not have sufficient permissions to access this page.', 'realestate-booking-suite'),
                    esc_html__('Permission Denied', 'realestate-booking-suite'),
                    array('response' => 403)
                );
            }
            return false;
        }
        return true;
    }

    /**
     * Verify nonce AND check capability (combined security check)
     * 
     * Use this for admin form submissions that require both nonce and capability
     * Example: Settings pages, admin actions
     * 
     * @param string $nonce The nonce value to verify
     * @param string $action The action name used when creating the nonce
     * @param string $capability The capability to check (default: 'manage_options')
     * @param bool $die Whether to die on failure (default: true)
     * @return bool True if both checks pass, false otherwise
     */
    public static function verify_nonce_and_capability($nonce, $action, $capability = 'manage_options', $die = true) {
        // Verify nonce first
        if (!self::verify_nonce($nonce, $action, false)) {
            if ($die) {
                wp_die(
                    esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite'),
                    esc_html__('Security Error', 'realestate-booking-suite'),
                    array('response' => 403)
                );
            }
            return false;
        }
        
        // Then check capability
        return self::check_capability($capability, $die);
    }

    /**
     * Verify AJAX nonce AND check capability (combined security check)
     * 
     * Use this for AJAX handlers that require both nonce and capability
     * Example: Admin AJAX actions, user-specific data modifications
     * 
     * @param string $nonce The nonce value to verify
     * @param string $action The action name used when creating the nonce
     * @param string $capability The capability to check (default: 'manage_options')
     * @return bool True if both checks pass, false otherwise (sends JSON error on failure)
     */
    public static function verify_ajax_nonce_and_capability($nonce, $action, $capability = 'manage_options') {
        // Verify nonce first
        if (!isset($nonce) || !wp_verify_nonce($nonce, $action)) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed.', 'realestate-booking-suite')
            ));
        }
        
        // Then check capability
        if (!current_user_can($capability)) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have sufficient permissions.', 'realestate-booking-suite')
            ));
        }
        
        return true;
    }

    /**
     * Verify user owns a post/resource
     * 
     * Use this to ensure users can only modify their own content
     * Example: Users editing their own properties, bookings, etc.
     * 
     * @param int $post_id The post ID to check
     * @param int $user_id The user ID to check (default: current user)
     * @param bool $allow_admins Whether admins can bypass ownership check (default: true)
     * @return bool True if user owns the post or is admin, false otherwise
     */
    public static function verify_post_ownership($post_id, $user_id = null, $allow_admins = true) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }
        
        $post_id = absint($post_id);
        $user_id = absint($user_id);
        
        if ($post_id <= 0 || $user_id <= 0) {
            return false;
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return false;
        }
        
        // Admins can access any post if allowed
        if ($allow_admins && current_user_can('manage_options')) {
            return true;
        }
        
        // Check if user is the author
        return (int) $post->post_author === $user_id;
    }

    /**
     * Verify REST API nonce
     * 
     * Use this for WordPress REST API endpoints
     * 
     * @param string $nonce The nonce value to verify
     * @return bool|WP_Error True if valid, WP_Error on failure
     */
    public static function verify_rest_nonce($nonce) {
        if (!wp_verify_nonce($nonce, 'wp_rest')) {
            return new WP_Error(
                'rest_cookie_invalid_nonce',
                esc_html__('Cookie check failed', 'realestate-booking-suite'),
                array('status' => 403)
            );
        }
        return true;
    }

    /**
     * Verify HTTP referrer (additional security layer)
     * 
     * Use this as an additional check for form submissions
     * Note: Referrer can be spoofed, so always use with nonces
     * 
     * @param bool $die Whether to die on failure (default: false)
     * @return bool True if referrer is valid, false otherwise
     */
    public static function verify_referer($die = false) {
        $referer = wp_get_referer();
        if (!$referer) {
            if ($die) {
                wp_die(
                    esc_html__('Invalid referrer. Please try again.', 'realestate-booking-suite'),
                    esc_html__('Security Error', 'realestate-booking-suite'),
                    array('response' => 403)
                );
            }
            return false;
        }
        
        // Check if referrer is from same site
        $site_url = home_url();
        if (strpos($referer, $site_url) !== 0) {
            if ($die) {
                wp_die(
                    esc_html__('Invalid referrer. Please try again.', 'realestate-booking-suite'),
                    esc_html__('Security Error', 'realestate-booking-suite'),
                    array('response' => 403)
                );
            }
            return false;
        }
        
        return true;
    }

    /**
     * Security wrapper for admin page callbacks
     * 
     * Use this at the start of admin page callback functions
     * Automatically checks capability and verifies nonce if POST request
     * 
     * @param string $capability The capability required (default: 'manage_options')
     * @param string $nonce_action The nonce action name (optional, only needed for POST)
     * @return void|bool Dies on failure, returns true on success
     */
    public static function admin_page_security_check($capability = 'manage_options', $nonce_action = '') {
        // Check capability
        self::check_capability($capability, true);
        
        // If POST request and nonce action provided, verify nonce
        $request_method = isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field($_SERVER['REQUEST_METHOD']) : 'GET';
        if ($request_method === 'POST' && !empty($nonce_action)) {
            $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
            if (empty($nonce)) {
                $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
            }
            self::verify_nonce($nonce, $nonce_action, true);
        }
        
        return true;
    }

    /**
     * Security wrapper for AJAX handlers
     * 
     * Use this at the start of AJAX handler functions
     * Automatically checks nonce and optionally capability
     * 
     * @param string $nonce_action The nonce action name
     * @param string $capability Optional capability to check (empty = no capability check)
     * @param bool $require_login Whether login is required (default: false for nopriv actions)
     * @return void|bool Sends JSON error on failure, returns true on success
     */
    public static function ajax_security_check($nonce_action, $capability = '', $require_login = false) {
        // Check if login is required
        if ($require_login && !is_user_logged_in()) {
            wp_send_json_error(array(
                'message' => esc_html__('You must be logged in to perform this action.', 'realestate-booking-suite')
            ));
        }
        
        // Get nonce from POST
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (empty($nonce)) {
            $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : '';
        }
        
        // Verify nonce
        if (empty($nonce) || !wp_verify_nonce($nonce, $nonce_action)) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed.', 'realestate-booking-suite')
            ));
        }
        
        // Check capability if provided
        if (!empty($capability) && !current_user_can($capability)) {
            wp_send_json_error(array(
                'message' => esc_html__('You do not have sufficient permissions.', 'realestate-booking-suite')
            ));
        }
        
        return true;
    }

    /**
     * Sanitize text input - handles arrays to prevent errors
     */
    public static function sanitize_text($input) {
        // If array, convert to comma-separated string first
        if (is_array($input)) {
            $input = implode(', ', array_filter(array_map('trim', $input)));
        }
        // Ensure it's a string
        $input = (string)$input;
        // Now sanitize
        return sanitize_text_field($input);
    }

    /**
     * Sanitize textarea input
     */
    public static function sanitize_textarea($input) {
        return sanitize_textarea_field($input);
    }

    /**
     * Sanitize email input
     */
    public static function sanitize_email($input) {
        return sanitize_email($input);
    }

    /**
     * Sanitize URL input
     */
    public static function sanitize_url($input) {
        return esc_url_raw($input);
    }

    /**
     * Sanitize integer input
     */
    public static function sanitize_int($input, $default = 0) {
        // Check if input is numeric or can be converted to integer
        if (is_numeric($input)) {
            $value = intval($input);
            // Return the value even if it's 0 (0 is a valid integer)
            return $value;
        }
        // Return default only if input is not numeric
        return $default;
    }

    /**
     * Sanitize float input
     */
    public static function sanitize_float($input, $default = 0.0) {
        // Check if input is numeric or can be converted to float
        if (is_numeric($input)) {
            $value = floatval($input);
            // Return the value even if it's 0.0 (0.0 is a valid float)
            return $value;
        }
        // Return default only if input is not numeric
        return $default;
    }

    /**
     * Sanitize boolean input
     */
    public static function sanitize_bool($input) {
        return (bool) $input;
    }

    /**
     * Sanitize array input
     */
    public static function sanitize_array($input, $callback = 'sanitize_text_field') {
        if (!is_array($input)) {
            return array();
        }
        return array_map($callback, $input);
    }

    /**
     * Sanitize post ID
     */
    public static function sanitize_post_id($input) {
        $id = intval($input);
        if ($id <= 0) {
            return 0;
        }
        
        // Verify post exists and is published
        $post = get_post($id);
        if (!$post || $post->post_status !== 'publish') {
            return 0;
        }
        
        return $id;
    }

    /**
     * Sanitize property ID
     */
    public static function sanitize_property_id($input) {
        $id = self::sanitize_post_id($input);
        if ($id && get_post_type($id) !== 'property') {
            return 0;
        }
        return $id;
    }

    /**
     * Sanitize taxonomy term
     */
    public static function sanitize_term($input, $taxonomy) {
        $term = sanitize_text_field($input);
        if (empty($term)) {
            return '';
        }
        
        // Verify term exists
        $term_obj = get_term_by('slug', $term, $taxonomy);
        if (!$term_obj || is_wp_error($term_obj)) {
            return '';
        }
        
        return $term;
    }

    /**
     * Escape HTML output
     */
    public static function escape_html($input) {
        return esc_html($input);
    }

    /**
     * Escape HTML attributes
     */
    public static function escape_attr($input) {
        return esc_attr($input);
    }

    /**
     * Escape URL output
     */
    public static function escape_url($input) {
        return esc_url($input);
    }

    /**
     * Escape JavaScript output
     */
    public static function escape_js($input) {
        return esc_js($input);
    }

    /**
     * Escape and allow specific HTML tags
     */
    public static function escape_kses($input, $allowed_html = null) {
        if ($allowed_html === null) {
            $allowed_html = array(
                'a' => array(
                    'href' => array(),
                    'title' => array(),
                    'target' => array(),
                    'rel' => array()
                ),
                'br' => array(),
                'em' => array(),
                'strong' => array(),
                'p' => array(),
                'ul' => array(),
                'ol' => array(),
                'li' => array(),
                'h1' => array(),
                'h2' => array(),
                'h3' => array(),
                'h4' => array(),
                'h5' => array(),
                'h6' => array()
            );
        }
        return wp_kses($input, $allowed_html);
    }

    /**
     * Validate and sanitize form data
     */
    public static function sanitize_form_data($data, $fields) {
        $sanitized = array();
        
        foreach ($fields as $field => $type) {
            if (!isset($data[$field])) {
                $sanitized[$field] = '';
                continue;
            }
            
            switch ($type) {
                case 'text':
                    $sanitized[$field] = self::sanitize_text($data[$field]);
                    break;
                case 'textarea':
                    $sanitized[$field] = self::sanitize_textarea($data[$field]);
                    break;
                case 'email':
                    $sanitized[$field] = self::sanitize_email($data[$field]);
                    break;
                case 'url':
                    $sanitized[$field] = self::sanitize_url($data[$field]);
                    break;
                case 'int':
                    $sanitized[$field] = self::sanitize_int($data[$field]);
                    break;
                case 'float':
                    $sanitized[$field] = self::sanitize_float($data[$field]);
                    break;
                case 'bool':
                    $sanitized[$field] = self::sanitize_bool($data[$field]);
                    break;
                case 'array':
                    $sanitized[$field] = self::sanitize_array($data[$field]);
                    break;
                case 'property_id':
                    $sanitized[$field] = self::sanitize_property_id($data[$field]);
                    break;
                case 'post_id':
                    $sanitized[$field] = self::sanitize_post_id($data[$field]);
                    break;
                default:
                    $sanitized[$field] = self::sanitize_text($data[$field]);
                    break;
            }
        }
        
        return $sanitized;
    }

    /**
     * Generate nonce field for forms
     */
    public static function nonce_field($action, $name = '_wpnonce', $referer = true, $echo = true) {
        $action = sanitize_text_field($action);
        $name = sanitize_text_field($name);
        return wp_nonce_field($action, $name, $referer, $echo);
    }

    /**
     * Generate nonce for AJAX
     */
    public static function ajax_nonce($action) {
        $action = sanitize_text_field($action);
        return wp_create_nonce($action);
    }

    /**
     * Validate file upload
     */
    public static function validate_file_upload($file, $allowed_types = array('jpg', 'jpeg', 'png', 'gif'), $max_size = 2097152) {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return new WP_Error('no_file', esc_html__('No file uploaded.', 'realestate-booking-suite'));
        }
        
        // Sanitize file name
        if (isset($file['name'])) {
            $file['name'] = sanitize_file_name($file['name']);
        }
        
        // Check file size
        $max_size = absint($max_size);
        if (isset($file['size']) && absint($file['size']) > $max_size) {
            return new WP_Error('file_too_large', esc_html__('File is too large.', 'realestate-booking-suite'));
        }
        
        // Check file type
        $file_name = isset($file['name']) ? sanitize_file_name($file['name']) : '';
        $file_type = wp_check_filetype($file_name);
        $allowed_types = array_map('sanitize_text_field', $allowed_types);
        if (!in_array(strtolower($file_type['ext']), array_map('strtolower', $allowed_types), true)) {
            return new WP_Error('invalid_file_type', esc_html__('Invalid file type.', 'realestate-booking-suite'));
        }
        
        // Check for malicious content
        if (isset($file['tmp_name']) && is_readable($file['tmp_name'])) {
            $file_content = file_get_contents($file['tmp_name']);
            if ($file_content !== false && (strpos($file_content, '<?php') !== false || strpos($file_content, '<script') !== false)) {
                return new WP_Error('malicious_content', esc_html__('File contains malicious content.', 'realestate-booking-suite'));
            }
        }
        
        return true;
    }

    /**
     * Log security events
     */
    public static function log_security_event($event, $details = array()) {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        // Sanitize event name
        $event = sanitize_text_field($event);
        
        // Sanitize details array
        $sanitized_details = array();
        foreach ($details as $key => $value) {
            $sanitized_key = sanitize_key($key);
            if (is_string($value)) {
                $sanitized_details[$sanitized_key] = sanitize_text_field($value);
            } elseif (is_array($value)) {
                $sanitized_details[$sanitized_key] = array_map('sanitize_text_field', $value);
            } else {
                $sanitized_details[$sanitized_key] = $value;
            }
        }
        
        $log_entry = array(
            'timestamp' => esc_html(current_time('mysql')),
            'event' => esc_html($event),
            'user_id' => absint(get_current_user_id()),
            'ip_address' => esc_html(self::get_client_ip()),
            'user_agent' => esc_html(sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '')),
            'details' => $sanitized_details
        );
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('RESBS Security Event: ' . wp_json_encode($log_entry, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT));
        }
    }

    /**
     * Get client IP address
     */
    public static function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $server_value = sanitize_text_field($_SERVER[$key]);
                foreach (explode(',', $server_value) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return esc_html($ip);
                    }
                }
            }
        }
        
        return esc_html(sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''));
    }

    /**
     * Rate limiting check
     */
    public static function check_rate_limit($action, $limit = 10, $window = 300) {
        $ip = self::get_client_ip();
        $action = sanitize_text_field($action);
        $key = 'resbs_rate_limit_' . md5($ip . $action);
        $count = get_transient($key);
        
        if ($count === false) {
            set_transient($key, 1, absint($window));
            return true;
        }
        
        if ($count >= absint($limit)) {
            self::log_security_event('rate_limit_exceeded', array(
                'action' => esc_html($action),
                'ip' => esc_html($ip),
                'count' => absint($count)
            ));
            return false;
        }
        
        set_transient($key, absint($count) + 1, absint($window));
        return true;
    }

    /**
     * Validate CSRF token
     */
    public static function validate_csrf_token($token, $action) {
        // Note: token should not be sanitized before verification
        $action = sanitize_text_field($action);
        return wp_verify_nonce($token, $action);
    }

    /**
     * Sanitize SQL query parameters
     */
    public static function sanitize_sql_param($param) {
        global $wpdb;
        // Ensure param is sanitized before preparing
        if (is_string($param)) {
            $param = sanitize_text_field($param);
        }
        return $wpdb->prepare('%s', $param);
    }

    /**
     * Validate and sanitize meta data
     */
    public static function sanitize_meta_data($meta_key, $meta_value) {
        // Remove any potential malicious characters
        $meta_key = sanitize_key($meta_key);
        
        // Sanitize based on value type
        if (is_string($meta_value)) {
            $meta_value = sanitize_text_field($meta_value);
        } elseif (is_numeric($meta_value)) {
            $meta_value = is_float($meta_value) ? floatval($meta_value) : intval($meta_value);
        } elseif (is_bool($meta_value)) {
            $meta_value = (bool) $meta_value;
        } elseif (is_array($meta_value)) {
            $meta_value = self::sanitize_array($meta_value);
        }
        
        return array($meta_key, $meta_value);
    }

    /**
     * Check for SQL injection attempts
     */
    public static function detect_sql_injection($input) {
        $sql_patterns = array(
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
            '/(\b(OR|AND)\s+\d+\s*=\s*\d+)/i',
            '/(\b(OR|AND)\s+\w+\s*=\s*\w+)/i',
            '/(\b(OR|AND)\s+[\'"]\s*=\s*[\'"])/i',
            '/(\b(OR|AND)\s+[\'"]\s*LIKE\s*[\'"])/i',
            '/(\b(OR|AND)\s+[\'"]\s*IN\s*[\'"])/i'
        );
        
        foreach ($sql_patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                self::log_security_event('sql_injection_attempt', array(
                    'input' => esc_html(sanitize_text_field($input)),
                    'pattern' => esc_html($pattern)
                ));
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check for XSS attempts
     */
    public static function detect_xss($input) {
        $xss_patterns = array(
            '/<script[^>]*>.*?<\/script>/is',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/<object[^>]*>.*?<\/object>/is',
            '/<embed[^>]*>.*?<\/embed>/is',
            '/<applet[^>]*>.*?<\/applet>/is',
            '/<meta[^>]*>.*?<\/meta>/is',
            '/<link[^>]*>.*?<\/link>/is',
            '/<style[^>]*>.*?<\/style>/is',
            '/javascript:/i',
            '/vbscript:/i',
            '/onload\s*=/i',
            '/onerror\s*=/i',
            '/onclick\s*=/i',
            '/onmouseover\s*=/i'
        );
        
        foreach ($xss_patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                self::log_security_event('xss_attempt', array(
                    'input' => esc_html(sanitize_text_field($input)),
                    'pattern' => esc_html($pattern)
                ));
                return true;
            }
        }
        
        return false;
    }

    /**
     * Comprehensive input validation
     */
    public static function validate_input($input, $type = 'text', $required = false) {
        // Check if required field is empty
        if ($required && empty($input)) {
            return new WP_Error('required_field', esc_html__('This field is required.', 'realestate-booking-suite'));
        }
        
        // Check for SQL injection
        if (self::detect_sql_injection($input)) {
            return new WP_Error('sql_injection', esc_html__('Invalid input detected.', 'realestate-booking-suite'));
        }
        
        // Check for XSS
        if (self::detect_xss($input)) {
            return new WP_Error('xss_attempt', esc_html__('Invalid input detected.', 'realestate-booking-suite'));
        }
        
        // Sanitize based on type
        switch ($type) {
            case 'email':
                if (!is_email($input)) {
                    return new WP_Error('invalid_email', esc_html__('Invalid email address.', 'realestate-booking-suite'));
                }
                return sanitize_email($input);
                
            case 'url':
                if (!filter_var($input, FILTER_VALIDATE_URL)) {
                    return new WP_Error('invalid_url', esc_html__('Invalid URL.', 'realestate-booking-suite'));
                }
                return esc_url_raw($input);
                
            case 'int':
                if (!is_numeric($input)) {
                    return new WP_Error('invalid_number', esc_html__('Invalid number.', 'realestate-booking-suite'));
                }
                return intval($input);
                
            case 'float':
                if (!is_numeric($input)) {
                    return new WP_Error('invalid_number', esc_html__('Invalid number.', 'realestate-booking-suite'));
                }
                return floatval($input);
                
            default:
                return sanitize_text_field($input);
        }
    }
}
