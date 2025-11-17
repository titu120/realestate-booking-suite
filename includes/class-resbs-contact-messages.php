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
        
        // Create table on activation
        register_activation_hook(__FILE__, array($this, 'create_contact_messages_table'));
        
        // Add AJAX handlers
        add_action('wp_ajax_submit_contact_message', array($this, 'handle_contact_message_submission'));
        add_action('wp_ajax_nopriv_submit_contact_message', array($this, 'handle_contact_message_submission'));
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
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'contact_message_nonce')) {
            wp_die(esc_html__('Security check failed', 'realestate-booking-suite'));
        }
        
        // Sanitize input data
        $property_id = intval($_POST['property_id']);
        $name = sanitize_text_field($_POST['contact_name']);
        $email = sanitize_email($_POST['contact_email']);
        $phone = sanitize_text_field($_POST['contact_phone']);
        $message = sanitize_textarea_field($_POST['contact_message']);
        
        // Validate required fields
        if (empty($name) || empty($email) || empty($phone) || empty($message)) {
            wp_send_json_error(esc_html__('All required fields must be filled', 'realestate-booking-suite'));
            return;
        }
        
        // Validate email
        if (!is_email($email)) {
            wp_send_json_error(esc_html__('Invalid email address', 'realestate-booking-suite'));
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
            wp_send_json_error(esc_html__('Failed to save contact message', 'realestate-booking-suite'));
            return;
        }
        
        $contact_message_id = $wpdb->insert_id;
        
        // Send email notifications
        $this->send_contact_message_notifications($contact_message_id, $property_id, $name, $email, $phone, $message);
        
        // Get success message from property meta
        $success_message = get_post_meta($property_id, '_property_contact_success_message', true);
        if (empty($success_message)) {
            $success_message = __('Thank you! Your message has been sent to the agent.', 'realestate-booking-suite');
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
        if (!empty($agent_email)) {
            $agent_subject = sprintf(__('New Contact Message for %s', 'realestate-booking-suite'), $property_title_escaped);
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
            
            wp_mail(sanitize_email($agent_email), $agent_subject, $agent_message);
        }
        
        // Email to customer
        $customer_subject = sprintf(__('Message Confirmation - %s', 'realestate-booking-suite'), $property_title_escaped);
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
        
        wp_mail($email_escaped, $customer_subject, $customer_message);
    }
    
    /**
     * Get contact messages for a property
     */
    public function get_contact_messages($property_id = null, $status = null) {
        global $wpdb;
        
        $where_conditions = array();
        $where_values = array();
        
        if ($property_id) {
            $where_conditions[] = 'property_id = %d';
            $where_values[] = $property_id;
        }
        
        if ($status) {
            $where_conditions[] = 'status = %s';
            $where_values[] = $status;
        }
        
        $where_clause = '';
        if (!empty($where_conditions)) {
            $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
        }
        
        $sql = "SELECT * FROM {$this->table_name} {$where_clause} ORDER BY created_at DESC";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Update contact message status
     */
    public function update_contact_message_status($contact_message_id, $status) {
        global $wpdb;
        
        $allowed_statuses = array('unread', 'read', 'replied', 'archived');
        if (!in_array($status, $allowed_statuses)) {
            return false;
        }
        
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
     */
    public function delete_contact_message($contact_message_id) {
        global $wpdb;
        
        return $wpdb->delete(
            $this->table_name,
            array('id' => $contact_message_id),
            array('%d')
        );
    }
}

// Initialize the class
new RESBS_Contact_Messages();
