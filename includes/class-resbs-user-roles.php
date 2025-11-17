<?php
/**
 * User Role Management
 * Safely assigns user roles based on available plugins
 * 
 * SECURITY NOTES:
 * - Current hooks (register_new_user, user_register) are WordPress core internal hooks
 * - These hooks are NOT triggered by user-facing forms, so nonces are not required
 * - WordPress core handles security for user registration
 * - If AJAX handlers or admin forms are added in the future, they MUST include:
 *   1. Nonce verification using wp_verify_nonce()
 *   2. Capability checks using current_user_can()
 *   3. Input sanitization and validation
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
     * 
     * SECURITY: Hooks into WordPress core actions that are secure by default.
     * No nonces needed as these are internal WordPress hooks, not user-facing forms.
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
     * SECURITY: This method is called via WordPress core hooks (register_new_user, user_register).
     * These hooks are internal WordPress actions, not user-facing forms, so nonces are not required.
     * WordPress core handles the security for user registration.
     * 
     * @param int $user_id User ID (provided by WordPress core hook)
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
     * SECURITY: This is a read-only informational method. No nonces needed.
     * If this method is called from admin forms or AJAX handlers, those callers
     * must implement nonce verification and capability checks.
     * 
     * Example for AJAX handlers:
     *   // Verify nonce
     *   if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_action')) {
     *       wp_send_json_error('Security check failed');
     *   }
     *   // Check capability
     *   if (!current_user_can('manage_options')) {
     *       wp_send_json_error('Insufficient permissions');
     *   }
     *   // Then call this method
     *   $role = RESBS_User_Roles::get_recommended_role();
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

