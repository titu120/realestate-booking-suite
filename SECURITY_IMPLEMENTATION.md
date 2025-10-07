# Security Implementation Guide

## Overview

This document outlines the comprehensive security measures implemented in the RealEstate Booking Suite plugin to protect against malicious attacks, unauthorized access, and data breaches.

## Security Features Implemented

### 1. **Nonce Verification**
All forms and AJAX requests are protected with WordPress nonces to prevent CSRF attacks.

#### Forms Protected:
- Property Grid Widget filters
- Contact Settings form
- Badge Settings form
- Map Settings form
- Infinite Scroll requests
- Favorites toggle requests
- Quick View requests

#### Implementation:
```php
// Form nonce field
RESBS_Security::nonce_field('action_name');

// AJAX nonce verification
RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'action_name');
```

### 2. **Input Sanitization**
All user inputs are sanitized using WordPress functions and custom security helpers.

#### Sanitization Methods:
- `sanitize_text_field()` - Text inputs
- `sanitize_textarea_field()` - Textarea inputs
- `sanitize_email()` - Email addresses
- `esc_url_raw()` - URLs
- `intval()` - Integer values
- `floatval()` - Float values
- `sanitize_hex_color()` - Color values

#### Implementation:
```php
// Using security helper
$sanitized_text = RESBS_Security::sanitize_text($input);
$sanitized_email = RESBS_Security::sanitize_email($input);
$sanitized_url = RESBS_Security::sanitize_url($input);
$sanitized_int = RESBS_Security::sanitize_int($input);
```

### 3. **Output Escaping**
All outputs are properly escaped to prevent XSS attacks.

#### Escaping Methods:
- `esc_html()` - HTML content
- `esc_attr()` - HTML attributes
- `esc_url()` - URLs
- `esc_js()` - JavaScript
- `wp_kses_post()` - Allowed HTML tags

#### Implementation:
```php
// Escaping outputs
echo RESBS_Security::escape_html($content);
echo RESBS_Security::escape_attr($attribute);
echo RESBS_Security::escape_url($url);
```

### 4. **Capability Checks**
All admin functions check user capabilities before execution.

#### Capabilities Checked:
- `manage_options` - Admin settings
- `edit_posts` - Content editing
- `publish_posts` - Content publishing

#### Implementation:
```php
// Check user capabilities
RESBS_Security::check_capability('manage_options');
```

### 5. **Rate Limiting**
AJAX requests are rate-limited to prevent abuse and DoS attacks.

#### Rate Limits:
- Filter requests: 20 per 5 minutes
- Favorite toggles: 30 per 5 minutes
- Quick view: 50 per 5 minutes
- Map requests: 30 per 5 minutes
- Clear favorites: 5 per 5 minutes

#### Implementation:
```php
// Rate limiting check
if (!RESBS_Security::check_rate_limit('action_name', 20, 300)) {
    wp_send_json_error(array(
        'message' => esc_html__('Too many requests. Please try again later.', 'realestate-booking-suite')
    ));
}
```

### 6. **SQL Injection Prevention**
All database queries use WordPress prepared statements and proper sanitization.

#### Protection Methods:
- WordPress `$wpdb->prepare()` for custom queries
- WordPress Query API for standard queries
- Input validation before database operations
- SQL injection pattern detection

#### Implementation:
```php
// Prepared statements
$wpdb->prepare("SELECT * FROM table WHERE id = %d", $id);

// SQL injection detection
if (RESBS_Security::detect_sql_injection($input)) {
    // Log and block
}
```

### 7. **XSS Prevention**
Cross-site scripting attacks are prevented through multiple layers.

#### Protection Methods:
- Input sanitization
- Output escaping
- XSS pattern detection
- Content Security Policy headers

#### Implementation:
```php
// XSS detection
if (RESBS_Security::detect_xss($input)) {
    // Log and sanitize
}
```

### 8. **File Upload Security**
File uploads are validated for type, size, and malicious content.

#### Validation Checks:
- File type validation
- File size limits
- Malicious content scanning
- MIME type verification

#### Implementation:
```php
// File upload validation
$validation = RESBS_Security::validate_file_upload($file, $allowed_types, $max_size);
if (is_wp_error($validation)) {
    // Handle error
}
```

### 9. **Data Validation**
All data is validated before processing and storage.

#### Validation Types:
- Required field validation
- Format validation (email, URL, etc.)
- Range validation (numbers, dates)
- Custom validation rules

#### Implementation:
```php
// Comprehensive input validation
$result = RESBS_Security::validate_input($input, 'email', true);
if (is_wp_error($result)) {
    // Handle validation error
}
```

### 10. **Security Logging**
Security events are logged for monitoring and analysis.

#### Logged Events:
- Failed login attempts
- SQL injection attempts
- XSS attempts
- Rate limit violations
- Permission violations

#### Implementation:
```php
// Log security events
RESBS_Security::log_security_event('event_name', $details);
```

## Security Helper Class

### RESBS_Security Class Methods

#### Nonce Methods:
- `verify_nonce($nonce, $action, $die = true)` - Verify form nonce
- `verify_ajax_nonce($nonce, $action)` - Verify AJAX nonce
- `nonce_field($action, $name, $referer, $echo)` - Generate nonce field
- `ajax_nonce($action)` - Generate AJAX nonce

#### Sanitization Methods:
- `sanitize_text($input)` - Sanitize text input
- `sanitize_textarea($input)` - Sanitize textarea input
- `sanitize_email($input)` - Sanitize email input
- `sanitize_url($input)` - Sanitize URL input
- `sanitize_int($input, $default)` - Sanitize integer input
- `sanitize_float($input, $default)` - Sanitize float input
- `sanitize_bool($input)` - Sanitize boolean input
- `sanitize_array($input, $callback)` - Sanitize array input
- `sanitize_property_id($input)` - Sanitize property ID
- `sanitize_post_id($input)` - Sanitize post ID
- `sanitize_term($input, $taxonomy)` - Sanitize taxonomy term

#### Escaping Methods:
- `escape_html($input)` - Escape HTML output
- `escape_attr($input)` - Escape HTML attributes
- `escape_url($input)` - Escape URL output
- `escape_js($input)` - Escape JavaScript output
- `escape_kses($input, $allowed_html)` - Escape with allowed HTML

#### Validation Methods:
- `check_capability($capability, $die)` - Check user capabilities
- `validate_input($input, $type, $required)` - Validate input
- `validate_file_upload($file, $allowed_types, $max_size)` - Validate file upload
- `detect_sql_injection($input)` - Detect SQL injection attempts
- `detect_xss($input)` - Detect XSS attempts

#### Security Methods:
- `check_rate_limit($action, $limit, $window)` - Check rate limits
- `log_security_event($event, $details)` - Log security events
- `get_client_ip()` - Get client IP address
- `sanitize_form_data($data, $fields)` - Sanitize form data

## AJAX Security Implementation

### All AJAX Handlers Include:

1. **Nonce Verification**
```php
RESBS_Security::verify_ajax_nonce($_POST['nonce'], 'action_name');
```

2. **Rate Limiting**
```php
if (!RESBS_Security::check_rate_limit('action_name', 20, 300)) {
    wp_send_json_error(array('message' => 'Too many requests'));
}
```

3. **Input Sanitization**
```php
$property_id = RESBS_Security::sanitize_property_id($_POST['property_id']);
$filters = RESBS_Security::sanitize_array($_POST['filters']);
```

4. **Output Escaping**
```php
wp_send_json_success(array(
    'message' => esc_html__('Success message', 'realestate-booking-suite')
));
```

## Form Security Implementation

### All Forms Include:

1. **Nonce Fields**
```php
RESBS_Security::nonce_field('form_action');
```

2. **Capability Checks**
```php
RESBS_Security::check_capability('manage_options');
```

3. **Input Sanitization**
```php
$settings = RESBS_Security::sanitize_form_data($_POST, $field_types);
```

4. **Output Escaping**
```php
echo RESBS_Security::escape_html($content);
echo RESBS_Security::escape_attr($attribute);
```

## Database Security

### All Database Operations:

1. **Use WordPress APIs**
```php
// Use WordPress Query API
$query = new WP_Query($args);

// Use WordPress meta functions
update_post_meta($post_id, $meta_key, $meta_value);
```

2. **Prepared Statements**
```php
// For custom queries
$wpdb->prepare("SELECT * FROM table WHERE id = %d", $id);
```

3. **Input Validation**
```php
// Validate before database operations
$property_id = RESBS_Security::sanitize_property_id($input);
```

## File Security

### File Operations:

1. **Path Validation**
```php
// Validate file paths
$file_path = realpath($input_path);
if (strpos($file_path, WP_CONTENT_DIR) !== 0) {
    // Invalid path
}
```

2. **File Type Validation**
```php
// Validate file types
$file_type = wp_check_filetype($file['name']);
if (!in_array($file_type['ext'], $allowed_types)) {
    // Invalid file type
}
```

3. **Content Scanning**
```php
// Scan for malicious content
$content = file_get_contents($file['tmp_name']);
if (strpos($content, '<?php') !== false) {
    // Malicious content detected
}
```

## Security Best Practices

### 1. **Always Sanitize Input**
```php
// Good
$input = RESBS_Security::sanitize_text($_POST['input']);

// Bad
$input = $_POST['input'];
```

### 2. **Always Escape Output**
```php
// Good
echo RESBS_Security::escape_html($content);

// Bad
echo $content;
```

### 3. **Verify Nonces**
```php
// Good
RESBS_Security::verify_nonce($_POST['nonce'], 'action');

// Bad
// No nonce verification
```

### 4. **Check Capabilities**
```php
// Good
RESBS_Security::check_capability('manage_options');

// Bad
// No capability check
```

### 5. **Use Rate Limiting**
```php
// Good
if (!RESBS_Security::check_rate_limit('action', 20, 300)) {
    // Handle rate limit
}

// Bad
// No rate limiting
```

## Security Monitoring

### Logged Events:
- Failed authentication attempts
- SQL injection attempts
- XSS attempts
- Rate limit violations
- Permission violations
- File upload violations

### Log Format:
```json
{
    "timestamp": "2024-01-01 12:00:00",
    "event": "sql_injection_attempt",
    "user_id": 1,
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "details": {
        "input": "malicious_input",
        "pattern": "sql_pattern"
    }
}
```

## Security Testing

### Test Cases:
1. **CSRF Protection**
   - Test forms without nonces
   - Test AJAX requests without nonces

2. **XSS Protection**
   - Test script injection in inputs
   - Test script injection in outputs

3. **SQL Injection Protection**
   - Test SQL injection in search fields
   - Test SQL injection in filters

4. **Rate Limiting**
   - Test rapid AJAX requests
   - Test form submissions

5. **File Upload Security**
   - Test malicious file uploads
   - Test oversized files
   - Test invalid file types

## Security Updates

### Regular Security Tasks:
1. **Update WordPress Core**
2. **Update Plugin Dependencies**
3. **Review Security Logs**
4. **Test Security Measures**
5. **Update Security Policies**

### Security Checklist:
- [ ] All forms have nonces
- [ ] All inputs are sanitized
- [ ] All outputs are escaped
- [ ] All AJAX requests are rate-limited
- [ ] All admin functions check capabilities
- [ ] All file uploads are validated
- [ ] All database queries use prepared statements
- [ ] Security events are logged
- [ ] XSS and SQL injection detection is active
- [ ] Rate limiting is configured

## Conclusion

The RealEstate Booking Suite plugin implements comprehensive security measures to protect against common web vulnerabilities including CSRF, XSS, SQL injection, and unauthorized access. All user inputs are sanitized, outputs are escaped, and security events are logged for monitoring.

The security implementation follows WordPress best practices and provides multiple layers of protection to ensure the plugin is secure and reliable for production use.
