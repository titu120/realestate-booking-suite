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
        // Debug logging
        error_log('RESBS: Booking submission received');
        error_log('RESBS: POST data: ' . print_r($_POST, true));
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['_wpnonce'], 'resbs_booking_nonce')) {
            error_log('RESBS: Nonce verification failed');
            wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'));
            return;
        }

        // Sanitize and validate data
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $preferred_date = sanitize_text_field($_POST['preferred_date']);
        $preferred_time = sanitize_text_field($_POST['preferred_time']);
        $message = sanitize_textarea_field($_POST['message']);
        $property_id = intval($_POST['property_id']);

        // Validate required fields (only name, email, phone are required)
        if (empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
            wp_send_json_error(array('message' => 'Please fill in all required fields (Name, Email, Phone).'));
            return;
        }

        // Validate email
        if (!is_email($email)) {
            wp_send_json_error(array('message' => 'Please enter a valid email address.'));
            return;
        }

        // Validate property exists
        if (!get_post($property_id) || get_post_type($property_id) !== 'property') {
            wp_send_json_error(array('message' => 'Invalid property selected.'));
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
            error_log('RESBS: Failed to create booking post: ' . $booking_id->get_error_message());
            wp_send_json_error(array('message' => 'Failed to create booking. Please try again.'));
            return;
        }

        error_log('RESBS: Booking created successfully with ID: ' . $booking_id);

        // Send notification emails
        $this->send_booking_notifications($booking_id, $property_id, $first_name, $last_name, $email, $phone, $preferred_date, $preferred_time, $message);

        error_log('RESBS: Booking submission completed successfully');
        wp_send_json_success(array('message' => 'Booking submitted successfully!'));
    }

    /**
     * Send booking notification emails
     */
    private function send_booking_notifications($booking_id, $property_id, $first_name, $last_name, $email, $phone, $preferred_date, $preferred_time, $message) {
        $property_title = get_the_title($property_id);
        $property_url = get_permalink($property_id);
        
        // Email to admin
        $admin_email = get_option('admin_email');
        $admin_subject = 'New Property Tour Booking - ' . $property_title;
        
        // Build date/time info
        $date_time_info = "";
        if (!empty($preferred_date) && !empty($preferred_time)) {
            $date_time_info = "Preferred Date: {$preferred_date}\nPreferred Time: {$preferred_time}\n";
        } elseif (!empty($preferred_date)) {
            $date_time_info = "Preferred Date: {$preferred_date}\n";
        } elseif (!empty($preferred_time)) {
            $date_time_info = "Preferred Time: {$preferred_time}\n";
        } else {
            $date_time_info = "Date/Time: Not specified (customer will be contacted to schedule)\n";
        }
        
        $admin_message = "
        New tour booking has been submitted:
        
        Property: {$property_title}
        Property URL: {$property_url}
        
        Customer Details:
        Name: {$first_name} {$last_name}
        Email: {$email}
        Phone: {$phone}
        
        {$date_time_info}
        Message: {$message}
        
        Booking ID: {$booking_id}
        ";
        
        wp_mail($admin_email, $admin_subject, $admin_message);
        
        // Email to customer
        $customer_subject = 'Tour Booking Confirmation - ' . $property_title;
        
        // Build customer date/time info
        $customer_date_time_info = "";
        if (!empty($preferred_date) && !empty($preferred_time)) {
            $customer_date_time_info = "We have received your tour booking request for:\nDate: {$preferred_date}\nTime: {$preferred_time}\n\nOur team will contact you within 24 hours to confirm your appointment.";
        } elseif (!empty($preferred_date)) {
            $customer_date_time_info = "We have received your tour booking request for:\nDate: {$preferred_date}\n\nOur team will contact you within 24 hours to confirm your appointment time.";
        } elseif (!empty($preferred_time)) {
            $customer_date_time_info = "We have received your tour booking request for:\nTime: {$preferred_time}\n\nOur team will contact you within 24 hours to confirm your appointment date.";
        } else {
            $customer_date_time_info = "We have received your tour booking request.\n\nOur team will contact you within 24 hours to schedule your appointment.";
        }
        
        $customer_message = "
        Dear {$first_name},
        
        Thank you for your interest in {$property_title}.
        
        {$customer_date_time_info}
        
        Property Details:
        {$property_title}
        {$property_url}
        
        If you have any questions, please don't hesitate to contact us.
        
        Best regards,
        Real Estate Team
        ";
        
        wp_mail($email, $customer_subject, $customer_message);
    }

    /**
     * Admin bookings page
     */
    public function bookings_admin_page() {
        $bookings = get_posts(array(
            'post_type' => 'property_booking',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
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
                                $date_time_display = esc_html($preferred_date . ' at ' . $preferred_time);
                            } elseif (!empty($preferred_date)) {
                                $date_time_display = esc_html($preferred_date);
                            } elseif (!empty($preferred_time)) {
                                $date_time_display = esc_html($preferred_time);
                            } else {
                                $date_time_display = 'Not specified';
                            }
                            echo $date_time_display;
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
                            <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a><br>
                            <a href="tel:<?php echo esc_attr($phone); ?>"><?php echo esc_html($phone); ?></a>
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
        
        <script>
        function updateBookingStatus(bookingId, status) {
            fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=resbs_update_booking_status&booking_id=' + encodeURIComponent(bookingId) + '&status=' + encodeURIComponent(status)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('<?php echo esc_js(__('Booking status updated!', 'realestate-booking-suite')); ?>');
                } else {
                    alert('<?php echo esc_js(__('Error updating status', 'realestate-booking-suite')); ?>');
                }
            });
        }
        
        function deleteBooking(bookingId) {
            if (confirm('<?php echo esc_js(__('Are you sure you want to delete this booking?', 'realestate-booking-suite')); ?>')) {
                fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=resbs_delete_booking&booking_id=' + encodeURIComponent(bookingId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('<?php echo esc_js(__('Error deleting booking', 'realestate-booking-suite')); ?>');
                    }
                });
            }
        }
        </script>
        <?php
    }

    /**
     * Update booking status
     */
    public function update_booking_status() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $booking_id = intval($_POST['booking_id']);
        $status = sanitize_text_field($_POST['status']);

        update_post_meta($booking_id, '_booking_status', $status);
        wp_send_json_success();
    }

    /**
     * Delete booking
     */
    public function delete_booking() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized');
            return;
        }

        $booking_id = intval($_POST['booking_id']);
        wp_delete_post($booking_id, true);
        wp_send_json_success();
    }
}

// Initialize the booking manager
new RESBS_Booking_Manager();
