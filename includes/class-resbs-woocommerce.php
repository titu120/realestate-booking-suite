<?php
/**
 * WooCommerce Integration Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_WooCommerce {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init_woocommerce_integration'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_resbs_add_to_cart', array($this, 'handle_add_to_cart'));
        // Note: Removed nopriv action since booking requires user login
        add_action('woocommerce_add_to_cart', array($this, 'on_add_to_cart'), 10, 6);
        add_action('woocommerce_checkout_process', array($this, 'validate_booking_checkout'));
        add_action('woocommerce_checkout_order_processed', array($this, 'process_booking_order'), 10, 1);
        add_action('woocommerce_order_status_completed', array($this, 'complete_booking'), 10, 1);
        add_action('woocommerce_order_status_cancelled', array($this, 'cancel_booking'), 10, 1);
        add_action('woocommerce_order_status_refunded', array($this, 'refund_booking'), 10, 1);
    }
    
    /**
     * Initialize WooCommerce integration
     */
    public function init_woocommerce_integration() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Create booking product category
        $this->create_booking_category();
        
        // Add booking product type
        add_filter('product_type_selector', array($this, 'add_booking_product_type'));
        add_action('woocommerce_product_options_general_product_data', array($this, 'booking_product_options'));
        add_action('woocommerce_process_product_meta', array($this, 'save_booking_product_options'));
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Only enqueue if WooCommerce is available
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Localize script for WooCommerce integration
        wp_localize_script('resbs-main', 'resbs_wc_ajax', array(
            'ajax_url' => esc_url(admin_url('admin-ajax.php')),
            'nonce' => esc_js(wp_create_nonce('resbs_wc_nonce')),
            'cart_url' => esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : '/cart/'),
            'checkout_url' => esc_url(function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '/checkout/'),
            'messages' => array(
                'adding_to_cart' => esc_html__('Adding to cart...', 'realestate-booking-suite'),
                'added_to_cart' => esc_html__('Property added to cart!', 'realestate-booking-suite'),
                'error' => esc_html__('Error adding property to cart.', 'realestate-booking-suite'),
                'login_required' => esc_html__('Please login to book properties.', 'realestate-booking-suite'),
                'select_dates' => esc_html__('Please select check-in and check-out dates.', 'realestate-booking-suite'),
                'invalid_dates' => esc_html__('Check-out date must be after check-in date.', 'realestate-booking-suite')
            )
        ));
    }
    
    /**
     * Create booking product category
     */
    private function create_booking_category() {
        $category_name = esc_html__('Property Bookings', 'realestate-booking-suite');
        $category_slug = 'property-bookings';
        
        if (!term_exists($category_slug, 'product_cat')) {
            wp_insert_term(
                $category_name,
                'product_cat',
                array(
                    'description' => esc_html__('Property booking products', 'realestate-booking-suite'),
                    'slug' => $category_slug
                )
            );
        }
    }
    
    /**
     * Add booking product type
     */
    public function add_booking_product_type($types) {
        $types['property_booking'] = esc_html__('Property Booking', 'realestate-booking-suite');
        return $types;
    }
    
    /**
     * Add booking product options
     */
    public function booking_product_options() {
        global $post;
        
        // Check user permissions
        if (!current_user_can('edit_post', $post->ID)) {
            return;
        }
        
        echo '<div class="options_group show_if_property_booking">';
        
        woocommerce_wp_text_input(array(
            'id' => '_property_id',
            'label' => esc_html__('Property ID', 'realestate-booking-suite'),
            'placeholder' => esc_html__('Enter property ID', 'realestate-booking-suite'),
            'desc_tip' => true,
            'description' => esc_html__('The ID of the property this booking is for.', 'realestate-booking-suite')
        ));
        
        woocommerce_wp_text_input(array(
            'id' => '_booking_price_per_night',
            'label' => esc_html__('Price per Night', 'realestate-booking-suite'),
            'placeholder' => esc_html__('0.00', 'realestate-booking-suite'),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => '0.01',
                'min' => '0'
            ),
            'desc_tip' => true,
            'description' => esc_html__('Price per night for this property.', 'realestate-booking-suite')
        ));
        
        woocommerce_wp_text_input(array(
            'id' => '_max_guests',
            'label' => esc_html__('Maximum Guests', 'realestate-booking-suite'),
            'placeholder' => esc_html__('4', 'realestate-booking-suite'),
            'type' => 'number',
            'custom_attributes' => array(
                'min' => '1'
            ),
            'desc_tip' => true,
            'description' => esc_html__('Maximum number of guests allowed.', 'realestate-booking-suite')
        ));
        
        woocommerce_wp_text_input(array(
            'id' => '_min_nights',
            'label' => esc_html__('Minimum Nights', 'realestate-booking-suite'),
            'placeholder' => esc_html__('1', 'realestate-booking-suite'),
            'type' => 'number',
            'custom_attributes' => array(
                'min' => '1'
            ),
            'desc_tip' => true,
            'description' => esc_html__('Minimum number of nights required.', 'realestate-booking-suite')
        ));
        
        echo '</div>';
    }
    
    /**
     * Save booking product options
     */
    public function save_booking_product_options($post_id) {
        // Verify nonce
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'update-post_' . $post_id)) {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Verify this is a product post type
        if (get_post_type($post_id) !== 'product') {
            return;
        }
        
        $property_id = isset($_POST['_property_id']) ? sanitize_text_field($_POST['_property_id']) : '';
        $price_per_night = isset($_POST['_booking_price_per_night']) ? floatval($_POST['_booking_price_per_night']) : 0;
        $max_guests = isset($_POST['_max_guests']) ? intval($_POST['_max_guests']) : 1;
        $min_nights = isset($_POST['_min_nights']) ? intval($_POST['_min_nights']) : 1;
        
        update_post_meta($post_id, '_property_id', $property_id);
        update_post_meta($post_id, '_booking_price_per_night', $price_per_night);
        update_post_meta($post_id, '_max_guests', $max_guests);
        update_post_meta($post_id, '_min_nights', $min_nights);
    }
    
    /**
     * Handle AJAX add to cart
     */
    public function handle_add_to_cart() {
        try {
            // Check if WooCommerce is available
            if (!class_exists('WooCommerce') || !function_exists('WC')) {
                wp_send_json_error(esc_html__('WooCommerce is not available.', 'realestate-booking-suite'));
                return;
            }
            
            // Verify nonce
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_wc_nonce')) {
                wp_send_json_error(esc_html__('Security check failed.', 'realestate-booking-suite'));
                return;
            }
            
            if (!is_user_logged_in()) {
                wp_send_json_error(esc_html__('Please login to book properties.', 'realestate-booking-suite'));
                return;
            }
            
            $property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;
            $checkin_date = isset($_POST['checkin_date']) ? sanitize_text_field($_POST['checkin_date']) : '';
            $checkout_date = isset($_POST['checkout_date']) ? sanitize_text_field($_POST['checkout_date']) : '';
            $guests = isset($_POST['guests']) ? intval($_POST['guests']) : 1;
            
            // Validate property ID
            if (!$property_id || !get_post($property_id)) {
                wp_send_json_error(esc_html__('Invalid property ID.', 'realestate-booking-suite'));
                return;
            }
            
            // Use default dates if not provided
            if (empty($checkin_date)) {
                $checkin_date = date('Y-m-d', strtotime('+1 day'));
            }
            if (empty($checkout_date)) {
                $checkout_date = date('Y-m-d', strtotime('+2 days'));
            }
            
            // Validate dates
            if (strtotime($checkout_date) <= strtotime($checkin_date)) {
                wp_send_json_error(esc_html__('Check-out date must be after check-in date.', 'realestate-booking-suite'));
                return;
            }
            
            // Get or create a simple product for this property
            $product_id = $this->get_or_create_simple_product($property_id);
            
            if (!$product_id) {
                wp_send_json_error(esc_html__('Error creating booking product.', 'realestate-booking-suite'));
                return;
            }
            
            // Add to cart with booking data
            $cart_item_data = array(
                'property_id' => $property_id,
                'checkin_date' => $checkin_date,
                'checkout_date' => $checkout_date,
                'guests' => $guests
            );
            
            $cart_item_key = WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);
            
            if ($cart_item_key) {
                wp_send_json_success(array(
                    'message' => esc_html__('Property added to cart successfully!', 'realestate-booking-suite'),
                    'cart_url' => wc_get_cart_url(),
                    'checkout_url' => wc_get_checkout_url()
                ));
            } else {
                wp_send_json_error(esc_html__('Failed to add property to cart.', 'realestate-booking-suite'));
            }
            
        } catch (Exception $e) {
            wp_send_json_error(esc_html__('An error occurred: ', 'realestate-booking-suite') . esc_html($e->getMessage()));
        }
    }
    
    /**
     * Get or create a simple product for property booking
     */
    private function get_or_create_simple_product($property_id) {
        // Verify property exists and user has permission
        $property = get_post($property_id);
        if (!$property) {
            return false;
        }
        
        // Check if product already exists for this property
        $existing_products = get_posts(array(
            'post_type' => 'product',
            'meta_query' => array(
                array(
                    'key' => '_property_id',
                    'value' => $property_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1
        ));
        
        if (!empty($existing_products)) {
            return $existing_products[0]->ID;
        }
        
        // Verify user has permission to create products
        if (!current_user_can('publish_products') && !current_user_can('edit_posts')) {
            return false;
        }
        
        // Create new product
        $property_price = get_post_meta($property_id, '_resbs_price', true);
        
        $product_data = array(
            'post_title' => sprintf(esc_html__('Booking: %s', 'realestate-booking-suite'), esc_html($property->post_title)),
            'post_content' => sprintf(esc_html__('Property booking for: %s', 'realestate-booking-suite'), esc_html($property->post_title)),
            'post_status' => 'publish',
            'post_type' => 'product',
            'post_author' => get_current_user_id()
        );
        
        $product_id = wp_insert_post($product_data);
        
        if ($product_id) {
            // Set product meta
            update_post_meta($product_id, '_property_id', $property_id);
            update_post_meta($product_id, '_price', $property_price ? floatval($property_price) : 100);
            update_post_meta($product_id, '_regular_price', $property_price ? floatval($property_price) : 100);
            update_post_meta($product_id, '_manage_stock', 'no');
            update_post_meta($product_id, '_stock_status', 'instock');
            update_post_meta($product_id, '_virtual', 'yes');
            update_post_meta($product_id, '_downloadable', 'no');
            
            // Set product type
            wp_set_object_terms($product_id, 'simple', 'product_type');
        }
        
        return $product_id;
    }
    
    /**
     * Get or create booking product for property
     */
    private function get_or_create_booking_product($property_id) {
        // Verify property exists
        $property = get_post($property_id);
        if (!$property) {
            return false;
        }
        
        // Check if product already exists for this property
        $existing_products = get_posts(array(
            'post_type' => 'product',
            'meta_query' => array(
                array(
                    'key' => '_property_id',
                    'value' => $property_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1
        ));
        
        if (!empty($existing_products)) {
            return $existing_products[0]->ID;
        }
        
        // Verify user has permission to create products
        if (!current_user_can('publish_products') && !current_user_can('edit_posts')) {
            return false;
        }
        
        // Create new booking product
        $property_price = get_post_meta($property_id, '_resbs_price', true);
        
        $product_data = array(
            'post_title' => esc_html__('Booking for', 'realestate-booking-suite') . ' ' . esc_html($property->post_title),
            'post_content' => esc_html__('Property booking for', 'realestate-booking-suite') . ' ' . esc_html($property->post_title),
            'post_status' => 'publish',
            'post_type' => 'product',
            'post_author' => get_current_user_id(),
            'meta_input' => array(
                '_property_id' => intval($property_id),
                '_booking_price_per_night' => floatval($property_price),
                '_max_guests' => 10,
                '_min_nights' => 1,
                '_virtual' => 'yes',
                '_downloadable' => 'no',
                '_manage_stock' => 'no',
                '_stock_status' => 'instock',
                '_visibility' => 'hidden'
            )
        );
        
        $product_id = wp_insert_post($product_data);
        
        if ($product_id) {
            // Set product type
            wp_set_object_terms($product_id, 'property_booking', 'product_type');
            
            // Set product category
            $category = get_term_by('slug', 'property-bookings', 'product_cat');
            if ($category) {
                wp_set_object_terms($product_id, $category->term_id, 'product_cat');
            }
        }
        
        return $product_id;
    }
    
    /**
     * Calculate number of nights
     */
    private function calculate_nights($checkin_date, $checkout_date) {
        $checkin = new DateTime($checkin_date);
        $checkout = new DateTime($checkout_date);
        $interval = $checkin->diff($checkout);
        return $interval->days;
    }
    
    /**
     * On add to cart action
     */
    public function on_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        if (isset($cart_item_data['property_id'])) {
            // Store booking data in session
            WC()->session->set('booking_data_' . $cart_item_key, $cart_item_data);
        }
    }
    
    /**
     * Validate booking checkout
     */
    public function validate_booking_checkout() {
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['property_id'])) {
                $booking_data = WC()->session->get('booking_data_' . $cart_item_key);
                
                if ($booking_data) {
                    // Validate booking dates
                    $checkin_date = $booking_data['checkin_date'];
                    $checkout_date = $booking_data['checkout_date'];
                    
                    if (strtotime($checkout_date) <= strtotime($checkin_date)) {
                        wc_add_notice(esc_html__('Check-out date must be after check-in date.', 'realestate-booking-suite'), 'error');
                    }
                    
                    // Check availability (you can add more complex availability logic here)
                    if (!$this->is_property_available($booking_data['property_id'], $checkin_date, $checkout_date)) {
                        wc_add_notice(esc_html__('Property is not available for the selected dates.', 'realestate-booking-suite'), 'error');
                    }
                }
            }
        }
    }
    
    /**
     * Process booking order
     */
    public function process_booking_order($order_id) {
        $order = wc_get_order($order_id);
        
        if (!$order) {
            return;
        }
        
        // Verify order belongs to current user or user has permission
        $current_user_id = get_current_user_id();
        $order_user_id = $order->get_user_id();
        
        // This is called via WooCommerce hook, so it's a system action
        // But we should still verify the order is valid
        if (!$order_user_id && !current_user_can('manage_options')) {
            return;
        }
        
        foreach ($order->get_items() as $item_id => $item) {
            // Verify item is a product item
            if (!is_a($item, 'WC_Order_Item_Product')) {
                continue;
            }
            
            // Get product ID from order item
            // @var WC_Order_Item_Product $item
            $item_data = $item->get_data();
            $product_id = isset($item_data['product_id']) ? intval($item_data['product_id']) : 0;
            
            if (!$product_id) {
                continue;
            }
            
            $property_id = get_post_meta($product_id, '_property_id', true);
            
            if ($property_id) {
                // Get booking data from cart
                $booking_data = WC()->session->get('booking_data_' . $item_id);
                
                if ($booking_data) {
                    // Create booking record
                    $this->create_booking_record($order_id, $property_id, $booking_data);
                }
            }
        }
    }
    
    /**
     * Create booking record
     */
    private function create_booking_record($order_id, $property_id, $booking_data) {
        // Sanitize and validate input data
        $order_id = intval($order_id);
        $property_id = intval($property_id);
        $user_id = get_current_user_id();
        
        // Verify property exists
        if (!get_post($property_id)) {
            return false;
        }
        
        // Calculate nights if not provided
        $nights = isset($booking_data['nights']) ? intval($booking_data['nights']) : 1;
        if (!isset($booking_data['nights']) && isset($booking_data['checkin_date']) && isset($booking_data['checkout_date'])) {
            $nights = $this->calculate_nights($booking_data['checkin_date'], $booking_data['checkout_date']);
        }
        
        $price_per_night = isset($booking_data['price_per_night']) ? floatval($booking_data['price_per_night']) : 0;
        $total_price = $nights * $price_per_night;
        
        $booking_meta = array(
            'order_id' => $order_id,
            'property_id' => $property_id,
            'user_id' => $user_id,
            'checkin_date' => sanitize_text_field($booking_data['checkin_date']),
            'checkout_date' => sanitize_text_field($booking_data['checkout_date']),
            'guests' => isset($booking_data['guests']) ? intval($booking_data['guests']) : 1,
            'nights' => $nights,
            'price_per_night' => $price_per_night,
            'total_price' => $total_price,
            'status' => 'pending',
            'created_at' => current_time('mysql')
        );
        
        // Store in custom table or post meta
        $booking_id = wp_insert_post(array(
            'post_type' => 'property_booking',
            'post_title' => esc_html__('Booking', 'realestate-booking-suite') . ' #' . esc_html($order_id),
            'post_status' => 'publish',
            'meta_input' => $booking_meta
        ));
        
        return $booking_id;
    }
    
    /**
     * Complete booking
     */
    public function complete_booking($order_id) {
        $this->update_booking_status($order_id, 'confirmed');
        
        // Send confirmation email
        $this->send_booking_confirmation($order_id);
    }
    
    /**
     * Cancel booking
     */
    public function cancel_booking($order_id) {
        $this->update_booking_status($order_id, 'cancelled');
    }
    
    /**
     * Refund booking
     */
    public function refund_booking($order_id) {
        $this->update_booking_status($order_id, 'refunded');
    }
    
    /**
     * Update booking status
     */
    private function update_booking_status($order_id, $status) {
        // Verify order exists and user has permission
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Only allow status updates by authorized users (order owner or admin)
        $current_user_id = get_current_user_id();
        $order_user_id = $order->get_user_id();
        
        // Allow if user owns the order, is admin, or this is called via WooCommerce hook (system action)
        if ($current_user_id && $order_user_id != $current_user_id && !current_user_can('manage_options')) {
            // If called directly (not via hook), verify permissions
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            $is_hook_call = false;
            foreach ($backtrace as $trace) {
                if (isset($trace['function']) && in_array($trace['function'], array('do_action', 'apply_filters'))) {
                    $is_hook_call = true;
                    break;
                }
            }
            
            if (!$is_hook_call) {
                return;
            }
        }
        
        $bookings = get_posts(array(
            'post_type' => 'property_booking',
            'meta_query' => array(
                array(
                    'key' => 'order_id',
                    'value' => $order_id,
                    'compare' => '='
                )
            )
        ));
        
        foreach ($bookings as $booking) {
            update_post_meta($booking->ID, 'status', sanitize_text_field($status));
        }
    }
    
    /**
     * Check if property is available
     */
    private function is_property_available($property_id, $checkin_date, $checkout_date) {
        // Check for existing bookings
        $existing_bookings = get_posts(array(
            'post_type' => 'property_booking',
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'property_id',
                    'value' => $property_id,
                    'compare' => '='
                ),
                array(
                    'key' => 'status',
                    'value' => array('confirmed', 'pending'),
                    'compare' => 'IN'
                )
            )
        ));
        
        foreach ($existing_bookings as $booking) {
            $existing_checkin = get_post_meta($booking->ID, 'checkin_date', true);
            $existing_checkout = get_post_meta($booking->ID, 'checkout_date', true);
            
            // Check for date conflicts
            if (($checkin_date < $existing_checkout) && ($checkout_date > $existing_checkin)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Send booking confirmation email
     */
    private function send_booking_confirmation($order_id) {
        $order = wc_get_order($order_id);
        $user_email = $order->get_billing_email();
        
        $subject = esc_html__('Booking Confirmation', 'realestate-booking-suite');
        $message = esc_html__('Your property booking has been confirmed. Order ID:', 'realestate-booking-suite') . ' ' . esc_html($order_id);
        
        wp_mail($user_email, $subject, $message);
    }
    
    /**
     * Get user booking history
     */
    public function get_user_booking_history($user_id = null) {
        $current_user_id = get_current_user_id();
        
        if (!$user_id) {
            $user_id = $current_user_id;
        }
        
        // Security: Users can only view their own bookings unless they're admins
        if ($user_id != $current_user_id && !current_user_can('manage_options')) {
            return array();
        }
        
        $bookings = get_posts(array(
            'post_type' => 'property_booking',
            'meta_query' => array(
                array(
                    'key' => 'user_id',
                    'value' => $user_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        return $bookings;
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php esc_html_e('RealEstate Booking Suite requires WooCommerce to be installed and activated.', 'realestate-booking-suite'); ?></p>
        </div>
        <?php
    }
    
    /**
     * Render booking history shortcode
     */
    public function booking_history_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Please login to view your booking history.', 'realestate-booking-suite') . '</p>';
        }
        
        // Parse shortcode attributes
        $atts = shortcode_atts(array(
            'user_id' => null
        ), $atts, 'resbs_booking_history');
        
        // Security: Only allow user_id parameter for admins
        $user_id = null;
        if (!empty($atts['user_id']) && current_user_can('manage_options')) {
            $user_id = intval($atts['user_id']);
        }
        
        $bookings = $this->get_user_booking_history($user_id);
        
        if (empty($bookings)) {
            return '<p>' . esc_html__('No bookings found.', 'realestate-booking-suite') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="resbs-booking-history">
            <h3><?php esc_html_e('Booking History', 'realestate-booking-suite'); ?></h3>
            <div class="resbs-bookings-list">
                <?php foreach ($bookings as $booking): ?>
                    <?php
                    $property_id = get_post_meta($booking->ID, 'property_id', true);
                    $checkin_date = get_post_meta($booking->ID, 'checkin_date', true);
                    $checkout_date = get_post_meta($booking->ID, 'checkout_date', true);
                    $guests = get_post_meta($booking->ID, 'guests', true);
                    $status = get_post_meta($booking->ID, 'status', true);
                    $total_price = get_post_meta($booking->ID, 'total_price', true);
                    $order_id = get_post_meta($booking->ID, 'order_id', true);
                    
                    $property = get_post($property_id);
                    ?>
                    <div class="resbs-booking-item">
                        <div class="resbs-booking-property">
                            <h4><?php echo esc_html($property ? $property->post_title : esc_html__('Property not found', 'realestate-booking-suite')); ?></h4>
                            <p><?php echo esc_html__('Order ID:', 'realestate-booking-suite'); ?> #<?php echo esc_html($order_id); ?></p>
                        </div>
                        <div class="resbs-booking-dates">
                            <p><strong><?php esc_html_e('Check-in:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html(date_i18n('M j, Y', strtotime($checkin_date))); ?></p>
                            <p><strong><?php esc_html_e('Check-out:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html(date_i18n('M j, Y', strtotime($checkout_date))); ?></p>
                            <p><strong><?php esc_html_e('Guests:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html($guests); ?></p>
                        </div>
                        <div class="resbs-booking-status">
                            <span class="resbs-status resbs-status-<?php echo esc_attr($status); ?>">
                                <?php echo esc_html(ucfirst($status)); ?>
                            </span>
                            <p><strong><?php esc_html_e('Total:', 'realestate-booking-suite'); ?></strong> <?php echo wp_kses_post(wc_price($total_price)); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Initialize the class
new RESBS_WooCommerce();

// Register shortcode
add_shortcode('resbs_booking_history', array('RESBS_WooCommerce', 'booking_history_shortcode'));
