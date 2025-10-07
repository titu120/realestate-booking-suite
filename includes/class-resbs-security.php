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

class RESBS_Security {

    /**
     * Verify nonce for forms
     */
    public static function verify_nonce($nonce, $action, $die = true) {
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
     */
    public static function verify_ajax_nonce($nonce, $action) {
        if (!wp_verify_nonce($nonce, $action)) {
            wp_send_json_error(array(
                'message' => esc_html__('Security check failed.', 'realestate-booking-suite')
            ));
        }
        return true;
    }

    /**
     * Check user capabilities
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
     * Sanitize text input
     */
    public static function sanitize_text($input) {
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
        return intval($input) ?: $default;
    }

    /**
     * Sanitize float input
     */
    public static function sanitize_float($input, $default = 0.0) {
        return floatval($input) ?: $default;
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
        return wp_nonce_field($action, $name, $referer, $echo);
    }

    /**
     * Generate nonce for AJAX
     */
    public static function ajax_nonce($action) {
        return wp_create_nonce($action);
    }

    /**
     * Validate file upload
     */
    public static function validate_file_upload($file, $allowed_types = array('jpg', 'jpeg', 'png', 'gif'), $max_size = 2097152) {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return new WP_Error('no_file', esc_html__('No file uploaded.', 'realestate-booking-suite'));
        }
        
        // Check file size
        if ($file['size'] > $max_size) {
            return new WP_Error('file_too_large', esc_html__('File is too large.', 'realestate-booking-suite'));
        }
        
        // Check file type
        $file_type = wp_check_filetype($file['name']);
        if (!in_array($file_type['ext'], $allowed_types)) {
            return new WP_Error('invalid_file_type', esc_html__('Invalid file type.', 'realestate-booking-suite'));
        }
        
        // Check for malicious content
        $file_content = file_get_contents($file['tmp_name']);
        if (strpos($file_content, '<?php') !== false || strpos($file_content, '<script') !== false) {
            return new WP_Error('malicious_content', esc_html__('File contains malicious content.', 'realestate-booking-suite'));
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
        
        $log_entry = array(
            'timestamp' => current_time('mysql'),
            'event' => $event,
            'user_id' => get_current_user_id(),
            'ip_address' => self::get_client_ip(),
            'user_agent' => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
            'details' => $details
        );
        
        error_log('RESBS Security Event: ' . wp_json_encode($log_entry));
    }

    /**
     * Get client IP address
     */
    public static function get_client_ip() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    }

    /**
     * Rate limiting check
     */
    public static function check_rate_limit($action, $limit = 10, $window = 300) {
        $ip = self::get_client_ip();
        $key = 'resbs_rate_limit_' . md5($ip . $action);
        $count = get_transient($key);
        
        if ($count === false) {
            set_transient($key, 1, $window);
            return true;
        }
        
        if ($count >= $limit) {
            self::log_security_event('rate_limit_exceeded', array(
                'action' => $action,
                'ip' => $ip,
                'count' => $count
            ));
            return false;
        }
        
        set_transient($key, $count + 1, $window);
        return true;
    }

    /**
     * Validate CSRF token
     */
    public static function validate_csrf_token($token, $action) {
        return wp_verify_nonce($token, $action);
    }

    /**
     * Sanitize SQL query parameters
     */
    public static function sanitize_sql_param($param) {
        global $wpdb;
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
                    'input' => $input,
                    'pattern' => $pattern
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
                    'input' => $input,
                    'pattern' => $pattern
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
