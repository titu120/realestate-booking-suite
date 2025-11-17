<?php
/**
 * User Role Management
 * Safely assigns user roles based on available plugins
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_User_Roles {

    /**
     * Constructor
     */
    public function __construct() {
        // Hook into user registration to assign appropriate role
        add_action('register_new_user', array($this, 'assign_safe_user_role'), 10, 1);
        
        // Also hook into user creation for admin-created users
        add_action('user_register', array($this, 'assign_safe_user_role'), 10, 1);
    }

    /**
     * Assign safe user role based on available plugins
     * 
     * @param int $user_id User ID
     */
    public function assign_safe_user_role($user_id) {
        // Sanitize and validate user ID
        $user_id = absint($user_id);
        
        if (!$user_id) {
            return;
        }
        
        $user = get_userdata($user_id);
        
        if (!$user) {
            return;
        }

        // Don't change role if user already has a role assigned
        if (!empty($user->roles)) {
            return;
        }

        // Determine the best role based on available plugins
        $role = $this->get_best_role_for_user();
        
        // Sanitize role name before assignment
        $role = sanitize_key($role);

        // Validate role exists before assigning
        if (!get_role($role)) {
            return;
        }

        // Assign the role
        $user->set_role($role);
    }

    /**
     * Get the best role for a new user
     * 
     * @return string Role name (sanitized)
     */
    private function get_best_role_for_user() {
        // Check if WooCommerce is active and Customer role exists
        if (class_exists('WooCommerce')) {
            // Verify Customer role exists
            $customer_role = get_role('customer');
            if ($customer_role) {
                return sanitize_key('customer');
            }
        }

        // Fallback to Subscriber (WordPress core role - always safe)
        return sanitize_key('subscriber');
    }

    /**
     * Get recommended role for settings
     * 
     * @return string Recommended role name (sanitized)
     */
    public static function get_recommended_role() {
        if (class_exists('WooCommerce')) {
            $customer_role = get_role('customer');
            if ($customer_role) {
                return sanitize_key('customer');
            }
        }
        return sanitize_key('subscriber');
    }
}

// Initialize
new RESBS_User_Roles();

