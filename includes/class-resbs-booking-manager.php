<?php
/**
 * Booking Manager Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Booking_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_resbs_submit_booking', array($this, 'handle_booking_submission'));
        add_action('wp_ajax_nopriv_resbs_submit_booking', array($this, 'handle_booking_submission'));
        add_action('wp_ajax_resbs_update_booking_status', array($this, 'update_booking_status'));
        add_action('wp_ajax_resbs_delete_booking', array($this, 'delete_booking'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('init', array($this, 'create_booking_post_type'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only enqueue on bookings page
        if ($hook !== 'toplevel_page_resbs-bookings') {
            return;
        }
        
        wp_enqueue_script(
            'resbs-booking-manager',
            RESBS_URL . 'assets/js/booking-manager.js',
            array(),
            '1.0.0',
            true
        );
        
        // Get nonces
        $update_nonce = wp_create_nonce('resbs_update_booking_status');
        $delete_nonce = wp_create_nonce('resbs_delete_booking');
        
        wp_localize_script('resbs-booking-manager', 'resbs_booking_manager', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'update_nonce' => esc_js($update_nonce),
            'delete_nonce' => esc_js($delete_nonce),
            'messages' => array(
                'status_updated' => esc_js(__('Booking status updated!', 'realestate-booking-suite')),
                'error_updating' => esc_js(__('Error updating status', 'realestate-booking-suite')),
                'confirm_delete' => esc_js(__('Are you sure you want to delete this booking?', 'realestate-booking-suite')),
                'error_deleting' => esc_js(__('Error deleting booking', 'realestate-booking-suite'))
            )
        ));
    }

    /**
     * Create booking post type
     */
    public function create_booking_post_type() {
        $labels = array(
            'name' => 'Bookings',
            'singular_name' => 'Booking',
            'menu_name' => 'Bookings',
            'add_new' => 'Add New Booking',
            'add_new_item' => 'Add New Booking',
            'edit_item' => 'Edit Booking',
            'new_item' => 'New Booking',
            'view_item' => 'View Booking',
            'search_items' => 'Search Bookings',
            'not_found' => 'No bookings found',
            'not_found_in_trash' => 'No bookings found in trash'
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'custom-fields')
        );

        register_post_type('property_booking', $args);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=property',
            'Bookings',
            'Bookings',
            'manage_options',
            'property-bookings',
            array($this, 'bookings_admin_page')
        );
    }

    /**
     * Handle booking form submission
     */
    public function handle_booking_submission() {
        
        // Verify nonce - check both possible nonce field names for compatibility
        // CRITICAL: Do NOT sanitize nonce before verification - wp_verify_nonce expects raw nonce
        $nonce = isset($_POST['_wpnonce']) ? $_POST['_wpnonce'] : (isset($_POST['resbs_booking_form_nonce']) ? $_POST['resbs_booking_form_nonce'] : '');
        if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_booking_form')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite')));
            return;
        }

        // Sanitize and validate data
        $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
        $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
        $preferred_date = isset($_POST['preferred_date']) ? sanitize_text_field($_POST['preferred_date']) : '';
        $preferred_time = isset($_POST['preferred_time']) ? sanitize_text_field($_POST['preferred_time']) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
        $property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;

        // Validate required fields (only name, email, phone are required)
        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
            wp_send_json_error(array('message' => esc_html__('Please fill in all required fields (Name, Email, Phone).', 'realestate-booking-suite')));
            return;
        }

        // Validate email
        if (!is_email($email)) {
            wp_send_json_error(array('message' => esc_html__('Please enter a valid email address.', 'realestate-booking-suite')));
            return;
        }

        // Validate property exists
        if (!get_post($property_id) || get_post_type($property_id) !== 'property') {
            wp_send_json_error(array('message' => esc_html__('Invalid property selected.', 'realestate-booking-suite')));
            return;
        }

        // Create booking post
        $property_title = get_the_title($property_id);
        $booking_data = array(
            'post_title' => 'Booking for ' . $first_name . ' ' . $last_name . ' - ' . $property_title,
            'post_content' => $message,
            'post_status' => 'publish',
            'post_type' => 'property_booking',
            'meta_input' => array(
                '_booking_first_name' => $first_name,
                '_booking_last_name' => $last_name,
                '_booking_email' => $email,
                '_booking_phone' => $phone,
                '_booking_preferred_date' => $preferred_date,
                '_booking_preferred_time' => $preferred_time,
                '_booking_property_id' => $property_id,
                '_booking_status' => 'pending',
                '_booking_created_date' => current_time('mysql')
            )
        );

        $booking_id = wp_insert_post($booking_data);

        if (is_wp_error($booking_id)) {
            wp_send_json_error(array('message' => esc_html__('Failed to create booking. Please try again.', 'realestate-booking-suite')));
            return;
        }

        // Send notification emails
        $this->send_booking_notifications($booking_id, $property_id, $first_name, $last_name, $email, $phone, $preferred_date, $preferred_time, $message);
        wp_send_json_success(array('message' => esc_html__('Booking submitted successfully!', 'realestate-booking-suite')));
    }

    /**
     * Send booking notification emails
     */
    private function send_booking_notifications($booking_id, $property_id, $first_name, $last_name, $email, $phone, $preferred_date, $preferred_time, $message) {
        // Sanitize all data for email
        $property_title = sanitize_text_field(get_the_title($property_id));
        $property_url = esc_url_raw(get_permalink($property_id));
        $first_name_safe = sanitize_text_field($first_name);
        $last_name_safe = sanitize_text_field($last_name);
        $email_safe = sanitize_email($email);
        $phone_safe = sanitize_text_field($phone);
        $preferred_date_safe = sanitize_text_field($preferred_date);
        $preferred_time_safe = sanitize_text_field($preferred_time);
        $message_safe = sanitize_textarea_field($message);
        
        // Email to admin
        $admin_email = sanitize_email(get_option('admin_email'));
        
        // Sanitize email subject to prevent header injection
        $admin_subject_raw = 'New Property Tour Booking - ' . $property_title;
        $admin_subject = wp_strip_all_tags($admin_subject_raw);
        $admin_subject = str_replace(array("\r", "\n"), '', $admin_subject);
        
        // Build date/time info
        $date_time_info = "";
        if (!empty($preferred_date_safe) && !empty($preferred_time_safe)) {
            $date_time_info = "Preferred Date: {$preferred_date_safe}\nPreferred Time: {$preferred_time_safe}\n";
        } elseif (!empty($preferred_date_safe)) {
            $date_time_info = "Preferred Date: {$preferred_date_safe}\n";
        } elseif (!empty($preferred_time_safe)) {
            $date_time_info = "Preferred Time: {$preferred_time_safe}\n";
        } else {
            $date_time_info = "Date/Time: Not specified (customer will be contacted to schedule)\n";
        }
        
        $admin_message = "
        New tour booking has been submitted:
        
        Property: {$property_title}
        Property URL: {$property_url}
        
        Customer Details:
        Name: {$first_name_safe} {$last_name_safe}
        Email: {$email_safe}
        Phone: {$phone_safe}
        
        {$date_time_info}
        Message: {$message_safe}
        
        Booking ID: {$booking_id}
        ";
        
        // Sanitize site name for email headers to prevent header injection
        $site_name = sanitize_text_field(get_bloginfo('name'));
        $site_name = str_replace(array("\r", "\n"), '', $site_name);
        
        // Email headers
        $admin_headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $site_name . ' <' . $admin_email . '>'
        );
        
        wp_mail($admin_email, $admin_subject, $admin_message, $admin_headers);
        
        // Email to customer
        // Sanitize email subject to prevent header injection
        $customer_subject_raw = 'Tour Booking Confirmation - ' . $property_title;
        $customer_subject = wp_strip_all_tags($customer_subject_raw);
        $customer_subject = str_replace(array("\r", "\n"), '', $customer_subject);
        
        // Build customer date/time info
        $customer_date_time_info = "";
        if (!empty($preferred_date_safe) && !empty($preferred_time_safe)) {
            $customer_date_time_info = "We have received your tour booking request for:\nDate: {$preferred_date_safe}\nTime: {$preferred_time_safe}\n\nOur team will contact you within 24 hours to confirm your appointment.";
        } elseif (!empty($preferred_date_safe)) {
            $customer_date_time_info = "We have received your tour booking request for:\nDate: {$preferred_date_safe}\n\nOur team will contact you within 24 hours to confirm your appointment time.";
        } elseif (!empty($preferred_time_safe)) {
            $customer_date_time_info = "We have received your tour booking request for:\nTime: {$preferred_time_safe}\n\nOur team will contact you within 24 hours to confirm your appointment date.";
        } else {
            $customer_date_time_info = "We have received your tour booking request.\n\nOur team will contact you within 24 hours to schedule your appointment.";
        }
        
        $customer_message = "
        Dear {$first_name_safe},
        
        Thank you for your interest in {$property_title}.
        
        {$customer_date_time_info}
        
        Property Details:
        {$property_title}
        {$property_url}
        
        If you have any questions, please don't hesitate to contact us.
        
        Best regards,
        Real Estate Team
        ";
        
        // Email headers
        $customer_headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . $site_name . ' <' . $admin_email . '>'
        );
        
        wp_mail($email_safe, $customer_subject, $customer_message, $customer_headers);
    }

    /**
     * Admin bookings page
     */
    public function bookings_admin_page() {
        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'realestate-booking-suite'));
        }

        $bookings = get_posts(array(
            'post_type' => 'property_booking',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        // Generate nonces for AJAX requests
        $update_nonce = wp_create_nonce('resbs_update_booking_status');
        $delete_nonce = wp_create_nonce('resbs_delete_booking');
        ?>
        <div class="wrap">
            <h1>Property Bookings</h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Property</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): 
                        $property_id = get_post_meta($booking->ID, '_booking_property_id', true);
                        $status = get_post_meta($booking->ID, '_booking_status', true);
                        $first_name = get_post_meta($booking->ID, '_booking_first_name', true);
                        $last_name = get_post_meta($booking->ID, '_booking_last_name', true);
                        $email = get_post_meta($booking->ID, '_booking_email', true);
                        $phone = get_post_meta($booking->ID, '_booking_phone', true);
                        $preferred_date = get_post_meta($booking->ID, '_booking_preferred_date', true);
                        $preferred_time = get_post_meta($booking->ID, '_booking_preferred_time', true);
                    ?>
                    <tr>
                        <td><?php echo esc_html($first_name . ' ' . $last_name); ?></td>
                        <td>
                            <?php if ($property_id): ?>
                                <a href="<?php echo esc_url(get_edit_post_link($property_id)); ?>"><?php echo esc_html(get_the_title($property_id)); ?></a>
                            <?php else: ?>
                                Property not found
                            <?php endif; ?>
                        </td>
                        <td><?php 
                            $date_time_display = '';
                            if (!empty($preferred_date) && !empty($preferred_time)) {
                                $date_time_display = $preferred_date . ' at ' . $preferred_time;
                            } elseif (!empty($preferred_date)) {
                                $date_time_display = $preferred_date;
                            } elseif (!empty($preferred_time)) {
                                $date_time_display = $preferred_time;
                            } else {
                                $date_time_display = __('Not specified', 'realestate-booking-suite');
                            }
                            echo esc_html($date_time_display);
                        ?></td>
                        <td>
                            <select onchange="updateBookingStatus(<?php echo esc_js($booking->ID); ?>, this.value)">
                                <option value="pending" <?php selected($status, 'pending'); ?>>Pending</option>
                                <option value="confirmed" <?php selected($status, 'confirmed'); ?>>Confirmed</option>
                                <option value="completed" <?php selected($status, 'completed'); ?>>Completed</option>
                                <option value="cancelled" <?php selected($status, 'cancelled'); ?>>Cancelled</option>
                            </select>
                        </td>
                        <td>
                            <?php if (!empty($email) && is_email($email)): ?>
                                <a href="mailto:<?php echo esc_attr(sanitize_email($email)); ?>"><?php echo esc_html($email); ?></a><br>
                            <?php else: ?>
                                <?php echo esc_html($email); ?><br>
                            <?php endif; ?>
                            <?php if (!empty($phone)): ?>
                                <?php 
                                // Sanitize phone number for tel: protocol (remove non-phone characters except +, -, spaces)
                                $phone_clean = preg_replace('/[^0-9+\- ]/', '', $phone);
                                ?>
                                <a href="tel:<?php echo esc_attr($phone_clean); ?>"><?php echo esc_html($phone); ?></a>
                            <?php else: ?>
                                <?php echo esc_html($phone); ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(get_edit_post_link($booking->ID)); ?>" class="button">Edit</a>
                            <button onclick="deleteBooking(<?php echo esc_js($booking->ID); ?>)" class="button">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Booking manager scripts are now enqueued via wp_enqueue_script in booking-manager.js -->
        <?php
    }

    /**
     * Update booking status
     */
    public function update_booking_status() {
        // Verify nonce and capability
        // CRITICAL: Do NOT sanitize nonce before verification
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_update_booking_status')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite')));
            return;
        }

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('You do not have sufficient permissions.', 'realestate-booking-suite')));
            return;
        }

        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        // Validate booking exists
        if (!get_post($booking_id) || get_post_type($booking_id) !== 'property_booking') {
            wp_send_json_error(array('message' => esc_html__('Invalid booking.', 'realestate-booking-suite')));
            return;
        }

        // Validate status value
        $allowed_statuses = array('pending', 'confirmed', 'completed', 'cancelled');
        if (!in_array($status, $allowed_statuses, true)) {
            wp_send_json_error(array('message' => esc_html__('Invalid status.', 'realestate-booking-suite')));
            return;
        }

        update_post_meta($booking_id, '_booking_status', $status);
        wp_send_json_success();
    }

    /**
     * Delete booking
     */
    public function delete_booking() {
        // Verify nonce and capability
        // CRITICAL: Do NOT sanitize nonce before verification
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_delete_booking')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite')));
            return;
        }

        // Check user capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('You do not have sufficient permissions.', 'realestate-booking-suite')));
            return;
        }

        $booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;

        // Validate booking exists
        if (!get_post($booking_id) || get_post_type($booking_id) !== 'property_booking') {
            wp_send_json_error(array('message' => esc_html__('Invalid booking.', 'realestate-booking-suite')));
            return;
        }

        wp_delete_post($booking_id, true);
        wp_send_json_success();
    }
}

// Initialize the booking manager
new RESBS_Booking_Manager();
