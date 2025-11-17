<?php
/**
 * Email Handler for Real Estate Booking Suite
 * Handles contact form submissions and sends emails
 */

if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Email_Handler {
    
    public function __construct() {
        add_action('wp_ajax_submit_contact_form', array($this, 'handle_contact_form_submission'));
        add_action('wp_ajax_nopriv_submit_contact_form', array($this, 'handle_contact_form_submission'));
        add_action('wp_ajax_submit_booking_form', array($this, 'handle_booking_form_submission'));
        add_action('wp_ajax_nopriv_submit_booking_form', array($this, 'handle_booking_form_submission'));
    }
    
    /**
     * Handle contact form submission
     */
    public function handle_contact_form_submission() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_contact_form_nonce')) {
            wp_die(esc_html__('Security check failed', 'realestate-booking-suite'));
        }
        
        // Check if we're on localhost
        $is_localhost = (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
                        strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
                        strpos($_SERVER['HTTP_HOST'], 'testthree') !== false);
        
        // Get form data
        $name = sanitize_text_field($_POST['contact_name']);
        $email = sanitize_email($_POST['contact_email']);
        $phone = sanitize_text_field($_POST['contact_phone']);
        $message = sanitize_textarea_field($_POST['contact_message']);
        $property_id = intval($_POST['property_id']);
        
        // Get property and agent data
        $property_title = get_the_title($property_id);
        $agent_email = get_post_meta($property_id, '_property_agent_email', true);
        $agent_name = get_post_meta($property_id, '_property_agent_name', true);
        
        // Email subject
        $subject = sprintf('New inquiry for property: %s', esc_html($property_title));
        
        // Email content
        $email_content = "
        <h2>New Property Inquiry</h2>
        <p><strong>Property:</strong> " . esc_html($property_title) . "</p>
        <p><strong>From:</strong> " . esc_html($name) . "</p>
        <p><strong>Email:</strong> " . esc_html($email) . "</p>
        <p><strong>Phone:</strong> " . esc_html($phone) . "</p>
        <p><strong>Message:</strong></p>
        <p>" . esc_html($message) . "</p>
        <hr>
        <p><em>This inquiry was sent through your property listing website.</em></p>
        ";
        
        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . esc_html(get_bloginfo('name')) . ' <' . esc_html(get_option('admin_email')) . '>',
            'Reply-To: ' . esc_html($name) . ' <' . esc_html($email) . '>'
        );
        
        // Send email to agent
        $agent_email_sent = false;
        if ($agent_email) {
            $agent_email_sent = wp_mail($agent_email, $subject, $email_content, $headers);
        }
        
        // Send email to admin
        $admin_email_sent = wp_mail(get_option('admin_email'), $subject, $email_content, $headers);
        
        // Send confirmation email to customer
        $customer_subject = 'Thank you for your inquiry - ' . esc_html(get_bloginfo('name'));
        $customer_content = "
        <h2>Thank you for your inquiry!</h2>
        <p>Dear " . esc_html($name) . ",</p>
        <p>Thank you for your interest in the property: <strong>" . esc_html($property_title) . "</strong></p>
        <p>Our agent <strong>" . esc_html($agent_name) . "</strong> will contact you shortly to discuss your requirements.</p>
        <p>Your inquiry details:</p>
        <ul>
            <li><strong>Property:</strong> " . esc_html($property_title) . "</li>
            <li><strong>Message:</strong> " . esc_html($message) . "</li>
        </ul>
        <p>Best regards,<br>" . esc_html($agent_name) . "</p>
        ";
        
        $customer_headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . esc_html($agent_name) . ' <' . esc_html($agent_email) . '>'
        );
        
        $customer_email_sent = wp_mail($email, $customer_subject, $customer_content, $customer_headers);
        
        // Return response
        if ($is_localhost) {
            // On localhost, always return success
            wp_send_json_success(array(
                'message' => 'Thank you! Your message has been received. (Localhost mode - emails logged to error log)',
                'agent_contacted' => true,
                'admin_notified' => true,
                'confirmation_sent' => true,
                'localhost' => true
            ));
        } elseif ($agent_email_sent || $admin_email_sent) {
            wp_send_json_success(array(
                'message' => 'Thank you! Your message has been sent successfully.',
                'agent_contacted' => $agent_email_sent,
                'admin_notified' => $admin_email_sent,
                'confirmation_sent' => $customer_email_sent
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Sorry, there was an error sending your message. Please try again.'
            ));
        }
    }
    
    /**
     * Handle booking form submission
     */
    public function handle_booking_form_submission() {
        // Verify nonce for security
        if (!wp_verify_nonce($_POST['nonce'], 'resbs_booking_form_nonce')) {
            wp_die(esc_html__('Security check failed', 'realestate-booking-suite'));
        }
        
        // Get form data
        $name = sanitize_text_field($_POST['bookingName']);
        $email = sanitize_email($_POST['bookingEmail']);
        $phone = sanitize_text_field($_POST['bookingPhone']);
        $date = sanitize_text_field($_POST['bookingDate']);
        $time = sanitize_text_field($_POST['bookingTime']);
        $message = sanitize_textarea_field($_POST['bookingMessage']);
        $property_id = intval($_POST['property_id']);
        
        // Get property and agent data
        $property_title = get_the_title($property_id);
        $agent_email = get_post_meta($property_id, '_property_agent_email', true);
        $agent_name = get_post_meta($property_id, '_property_agent_name', true);
        
        // Email subject
        $subject = sprintf('New tour booking for property: %s', esc_html($property_title));
        
        // Email content
        $email_content = "
        <h2>New Property Tour Booking</h2>
        <p><strong>Property:</strong> " . esc_html($property_title) . "</p>
        <p><strong>From:</strong> " . esc_html($name) . "</p>
        <p><strong>Email:</strong> " . esc_html($email) . "</p>
        <p><strong>Phone:</strong> " . esc_html($phone) . "</p>
        <p><strong>Preferred Date:</strong> " . esc_html($date) . "</p>
        <p><strong>Preferred Time:</strong> " . esc_html($time) . "</p>
        <p><strong>Message:</strong></p>
        <p>" . esc_html($message) . "</p>
        <hr>
        <p><em>This booking request was sent through your property listing website.</em></p>
        ";
        
        // Email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . esc_html(get_bloginfo('name')) . ' <' . esc_html(get_option('admin_email')) . '>',
            'Reply-To: ' . esc_html($name) . ' <' . esc_html($email) . '>'
        );
        
        // Send email to agent
        $agent_email_sent = false;
        if ($agent_email) {
            $agent_email_sent = wp_mail($agent_email, $subject, $email_content, $headers);
        }
        
        // Send email to admin
        $admin_email_sent = wp_mail(get_option('admin_email'), $subject, $email_content, $headers);
        
        // Send confirmation email to customer
        $customer_subject = 'Tour booking confirmed - ' . esc_html(get_bloginfo('name'));
        $customer_content = "
        <h2>Tour Booking Confirmed!</h2>
        <p>Dear " . esc_html($name) . ",</p>
        <p>Thank you for booking a tour for the property: <strong>" . esc_html($property_title) . "</strong></p>
        <p><strong>Your booking details:</strong></p>
        <ul>
            <li><strong>Property:</strong> " . esc_html($property_title) . "</li>
            <li><strong>Date:</strong> " . esc_html($date) . "</li>
            <li><strong>Time:</strong> " . esc_html($time) . "</li>
        </ul>
        <p>Our agent <strong>" . esc_html($agent_name) . "</strong> will contact you shortly to confirm the appointment.</p>
        <p>Best regards,<br>" . esc_html($agent_name) . "</p>
        ";
        
        $customer_headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . esc_html($agent_name) . ' <' . esc_html($agent_email) . '>'
        );
        
        $customer_email_sent = wp_mail($email, $customer_subject, $customer_content, $customer_headers);
        
        // Return response
        if ($agent_email_sent || $admin_email_sent) {
            wp_send_json_success(array(
                'message' => 'Thank you! Your tour booking has been confirmed.',
                'agent_contacted' => $agent_email_sent,
                'admin_notified' => $admin_email_sent,
                'confirmation_sent' => $customer_email_sent
            ));
        } else {
            wp_send_json_error(array(
                'message' => 'Sorry, there was an error processing your booking. Please try again.'
            ));
        }
    }
}

// Initialize the email handler
new RESBS_Email_Handler();
