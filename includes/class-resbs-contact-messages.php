<?php
/**
 * Contact Messages Handler
 * 
 * @package RealEstateBookingSuite
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Contact_Messages {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'resbs_contact_messages';
        
        // Create table on activation (use main plugin file path)
        $main_plugin_file = RESBS_PATH . 'realestate-booking-suite.php';
        if (file_exists($main_plugin_file)) {
            register_activation_hook($main_plugin_file, array($this, 'create_contact_messages_table'));
        }
        
        // Also create table immediately if it doesn't exist (for existing installations)
        $this->maybe_create_table();
        
        // Add AJAX handlers
        add_action('wp_ajax_submit_contact_message', array($this, 'handle_contact_message_submission'));
        add_action('wp_ajax_nopriv_submit_contact_message', array($this, 'handle_contact_message_submission'));
    }
    
    /**
     * Check if table exists and create if needed
     */
    private function maybe_create_table() {
        global $wpdb;
        // Use esc_like() to escape underscores in table name (underscores are wildcards in SQL LIKE)
        $table_exists = $wpdb->get_var($wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $wpdb->esc_like($this->table_name)
        ));
        
        if ($table_exists !== $this->table_name) {
            $this->create_contact_messages_table();
        }
    }
    
    /**
     * Create contact messages table
     */
    public function create_contact_messages_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE {$this->table_name} (
            id int(11) NOT NULL AUTO_INCREMENT,
            property_id int(11) NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            message text NOT NULL,
            status varchar(50) DEFAULT 'unread',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY property_id (property_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Handle contact message submission via AJAX
     */
    public function handle_contact_message_submission() {
        // Verify nonce - check both possible nonce field names for compatibility
        // CRITICAL: Do NOT sanitize nonce before verification
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : (isset($_POST['resbs_contact_form_nonce']) ? $_POST['resbs_contact_form_nonce'] : '');
        if (!wp_verify_nonce($nonce, 'resbs_contact_form')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite')));
            return;
        }
        
        // Sanitize input data
        $property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;
        $name = isset($_POST['contact_name']) ? sanitize_text_field($_POST['contact_name']) : '';
        $email = isset($_POST['contact_email']) ? sanitize_email($_POST['contact_email']) : '';
        $phone = isset($_POST['contact_phone']) ? sanitize_text_field($_POST['contact_phone']) : '';
        $message = isset($_POST['contact_message']) ? sanitize_textarea_field($_POST['contact_message']) : '';
        
        // Validate property ID
        if (empty($property_id) || $property_id <= 0) {
            wp_send_json_error(array('message' => esc_html__('Invalid property ID', 'realestate-booking-suite')));
            return;
        }
        
        // Verify property exists and is published
        $property = get_post($property_id);
        if (!$property || $property->post_status !== 'publish' || $property->post_type !== 'property') {
            wp_send_json_error(array('message' => esc_html__('Invalid property', 'realestate-booking-suite')));
            return;
        }
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($phone) || empty($message)) {
            wp_send_json_error(array('message' => esc_html__('All required fields must be filled', 'realestate-booking-suite')));
            return;
        }
        
        // Validate email
        if (!is_email($email)) {
            wp_send_json_error(array('message' => esc_html__('Invalid email address', 'realestate-booking-suite')));
            return;
        }
        
        // Insert into database
        global $wpdb;
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'property_id' => $property_id,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'message' => $message,
                'status' => 'unread'
            ),
            array(
                '%d', '%s', '%s', '%s', '%s', '%s'
            )
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => esc_html__('Failed to save contact message', 'realestate-booking-suite')));
            return;
        }
        
        $contact_message_id = $wpdb->insert_id;
        
        // Send email notifications
        $this->send_contact_message_notifications($contact_message_id, $property_id, $name, $email, $phone, $message);
        
        // Get success message from property meta (with default for new properties only)
        $exists = metadata_exists('post', $property_id, '_property_contact_success_message');
        $success_message = get_post_meta($property_id, '_property_contact_success_message', true);
        // Only show default if meta doesn't exist (new property), not if it's empty (user cleared it)
        if (!$exists) {
            $success_message = esc_html__('Thank you! Your message has been sent to the agent.', 'realestate-booking-suite');
        } else {
            $success_message = sanitize_text_field($success_message);
        }
        
        wp_send_json_success(array(
            'message' => esc_html($success_message),
            'contact_message_id' => absint($contact_message_id)
        ));
    }
    
    /**
     * Send email notifications for contact messages
     */
    private function send_contact_message_notifications($contact_message_id, $property_id, $name, $email, $phone, $message) {
        // Get property details
        $property_title = get_the_title($property_id);
        $property_url = get_permalink($property_id);
        
        // Get agent details
        $agent_name = get_post_meta($property_id, '_property_agent_name', true);
        $agent_email = get_post_meta($property_id, '_property_agent_email', true);
        
        // Sanitize and escape data for email
        $property_title_escaped = sanitize_text_field($property_title);
        $property_url_escaped = esc_url_raw($property_url);
        $name_escaped = sanitize_text_field($name);
        $email_escaped = sanitize_email($email);
        $phone_escaped = sanitize_text_field($phone);
        $message_escaped = sanitize_textarea_field($message);
        $agent_name_escaped = !empty($agent_name) ? sanitize_text_field($agent_name) : __('The Property Team', 'realestate-booking-suite');
        $contact_message_id_escaped = absint($contact_message_id);
        
        // Email to agent
        if (!empty($agent_email) && is_email($agent_email)) {
            // Sanitize email subject to prevent header injection
            $agent_subject_raw = sprintf(__('New Contact Message for %s', 'realestate-booking-suite'), $property_title_escaped);
            $agent_subject = wp_strip_all_tags($agent_subject_raw);
            $agent_subject = str_replace(array("\r", "\n"), '', $agent_subject);
            
            $agent_message = sprintf(
                __("New contact message received:\n\n", 'realestate-booking-suite') .
                __("Property: %s\n", 'realestate-booking-suite') .
                __("Property URL: %s\n\n", 'realestate-booking-suite') .
                __("Customer Details:\n", 'realestate-booking-suite') .
                __("Name: %s\n", 'realestate-booking-suite') .
                __("Email: %s\n", 'realestate-booking-suite') .
                __("Phone: %s\n\n", 'realestate-booking-suite') .
                __("Message:\n", 'realestate-booking-suite') .
                "%s\n\n" .
                __("Contact Message ID: #%d", 'realestate-booking-suite'),
                $property_title_escaped,
                $property_url_escaped,
                $name_escaped,
                $email_escaped,
                $phone_escaped,
                $message_escaped,
                $contact_message_id_escaped
            );
            
            // Sanitize agent name for email headers to prevent header injection
            $agent_name_for_headers = sanitize_text_field($agent_name_escaped);
            $agent_name_for_headers = str_replace(array("\r", "\n"), '', $agent_name_for_headers);
            
            // Email headers
            $agent_headers = array(
                'Content-Type: text/plain; charset=UTF-8',
                'From: ' . $agent_name_for_headers . ' <' . sanitize_email($agent_email) . '>'
            );
            
            wp_mail(sanitize_email($agent_email), $agent_subject, $agent_message, $agent_headers);
        }
        
        // Email to customer
        // Sanitize email subject to prevent header injection
        $customer_subject_raw = sprintf(__('Message Confirmation - %s', 'realestate-booking-suite'), $property_title_escaped);
        $customer_subject = wp_strip_all_tags($customer_subject_raw);
        $customer_subject = str_replace(array("\r", "\n"), '', $customer_subject);
        $customer_message = sprintf(
            __("Thank you for your message!\n\n", 'realestate-booking-suite') .
            __("Property: %s\n", 'realestate-booking-suite') .
            __("Property URL: %s\n\n", 'realestate-booking-suite') .
            __("Your Message:\n", 'realestate-booking-suite') .
            "%s\n\n" .
            __("We'll get back to you soon.\n\n", 'realestate-booking-suite') .
            __("Best regards,\n", 'realestate-booking-suite') .
            "%s",
            $property_title_escaped,
            $property_url_escaped,
            $message_escaped,
            $agent_name_escaped
        );
        
        // Sanitize agent name for email headers to prevent header injection
        $agent_name_for_headers = sanitize_text_field($agent_name_escaped);
        $agent_name_for_headers = str_replace(array("\r", "\n"), '', $agent_name_for_headers);
        
        // Use agent email if available, otherwise use admin email
        $from_email = !empty($agent_email) && is_email($agent_email) ? sanitize_email($agent_email) : sanitize_email(get_option('admin_email'));
        
        // Email headers
        $customer_headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $agent_name_for_headers . ' <' . $from_email . '>'
        );
        
        wp_mail($email_escaped, $customer_subject, $customer_message, $customer_headers);
    }
    
    /**
     * Get contact messages for a property
     * 
     * @param int|null $property_id Property ID (optional)
     * @param string|null $status Message status (optional)
     * @return array|object|null Database query results
     */
    public function get_contact_messages($property_id = null, $status = null) {
        // Check user permissions - only allow admins or users with manage_options capability
        if (!current_user_can('manage_options')) {
            return array();
        }
        
        global $wpdb;
        
        $where_conditions = array();  
        $where_values = array();
        
        if ($property_id) {
            $where_conditions[] = 'property_id = %d';
            $where_values[] = intval($property_id);
        }
        
        if ($status) {
            $allowed_statuses = array('unread', 'read', 'replied', 'archived');
            if (in_array($status, $allowed_statuses)) {
                $where_conditions[] = 'status = %s';
                $where_values[] = sanitize_text_field($status);
            }
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        // Escape table name for consistency (table name is safe - constructed from $wpdb->prefix)
        $table_name_escaped = esc_sql($this->table_name);
        $sql = "SELECT * FROM `{$table_name_escaped}` {$where_clause} ORDER BY created_at DESC";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Update contact message status
     * 
     * @param int $contact_message_id Contact message ID
     * @param string $status New status
     * @return bool|int False on failure, number of rows updated on success
     */
    public function update_contact_message_status($contact_message_id, $status) {
        // Check user permissions - only allow admins or users with manage_options capability
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        global $wpdb;
        
        $allowed_statuses = array('unread', 'read', 'replied', 'archived');
        if (!in_array($status, $allowed_statuses)) {
            return false;
        }
        
        $contact_message_id = intval($contact_message_id);
        $status = sanitize_text_field($status);
        
        return $wpdb->update(
            $this->table_name,
            array('status' => $status),
            array('id' => $contact_message_id),
            array('%s'),
            array('%d')
        );
    }
    
    /**
     * Delete contact message
     * 
     * @param int $contact_message_id Contact message ID
     * @return bool|int False on failure, number of rows deleted on success
     */
    public function delete_contact_message($contact_message_id) {
        // Check user permissions - only allow admins or users with manage_options capability
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        global $wpdb;
        
        $contact_message_id = intval($contact_message_id);
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $contact_message_id),
            array('%d')
        );
    }
}

// Initialize the class
new RESBS_Contact_Messages();
