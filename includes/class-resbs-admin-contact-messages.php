<?php
/**
 * Admin Contact Messages Management
 * 
 * @package RealEstateBookingSuite
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Admin_Contact_Messages {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_update_contact_message_status', array($this, 'update_contact_message_status'));
        add_action('wp_ajax_delete_contact_message', array($this, 'delete_contact_message'));
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook === 'property_page_contact-messages') {
            wp_enqueue_style(
                'resbs-admin-contact-messages',
                RESBS_URL . 'assets/css/admin-contact-messages.css',
                array(),
                '1.0.0'
            );
            
            wp_enqueue_script(
                'resbs-admin-contact-messages',
                RESBS_URL . 'assets/js/admin-contact-messages.js',
                array(),
                '1.0.0',
                true
            );
            
            // Localize script
            wp_localize_script('resbs-admin-contact-messages', 'resbs_contact_admin', array(
                'update_confirm' => esc_js(__('Are you sure you want to update the status?', 'realestate-booking-suite'))
            ));
        }
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=property',
            'Contact Messages',
            'Contact Messages',
            'manage_options',
            'contact-messages',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'realestate-booking-suite'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'resbs_contact_messages';
        
        // Handle actions with nonce verification
        if (isset($_GET['action']) && isset($_GET['id'])) {
            // Sanitize ID first
            $id = intval($_GET['id']);
            
            // CRITICAL: Do NOT sanitize nonce before verification
            $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : '';
            if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_contact_message_action_' . $id)) {
                wp_die(esc_html__('Security check failed. Please try again.', 'realestate-booking-suite'));
            }
            
            $action = sanitize_text_field($_GET['action']);
            
            switch ($action) {
                case 'update_status':
                    if (isset($_GET['status'])) {
                        $status = sanitize_text_field($_GET['status']);
                        $this->update_contact_message_status_direct($id, $status);
                    }
                    break;
                case 'delete':
                    $this->delete_contact_message_direct($id);
                    break;
            }
        }
        
        // Get contact messages
        // Table name is safe - constructed from $wpdb->prefix (no user input)
        // WordPress doesn't support table name placeholders in prepare(), so we use table names directly
        // Both $table_name and $wpdb->posts are safe (constructed from constants, not user input)
        $contact_messages = $wpdb->get_results("
            SELECT cm.*, p.post_title as property_title 
            FROM `{$table_name}` cm 
            LEFT JOIN `{$wpdb->posts}` p ON cm.property_id = p.ID 
            ORDER BY cm.created_at DESC
        ");
        
        // Create a global nonce for AJAX operations
        $ajax_nonce = wp_create_nonce('resbs_contact_message_admin_action');
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Contact Messages', 'realestate-booking-suite'); ?></h1>
            
            <?php if (empty($contact_messages)): ?>
                <div class="notice notice-info">
                    <p><?php echo esc_html__('No contact messages found.', 'realestate-booking-suite'); ?></p>
                </div>
            <?php else: ?>
                <div class="tablenav top">
                    <div class="alignleft actions">
                        <select id="status-filter">
                            <option value=""><?php echo esc_html__('All Status', 'realestate-booking-suite'); ?></option>
                            <option value="unread"><?php echo esc_html__('Unread', 'realestate-booking-suite'); ?></option>
                            <option value="read"><?php echo esc_html__('Read', 'realestate-booking-suite'); ?></option>
                            <option value="replied"><?php echo esc_html__('Replied', 'realestate-booking-suite'); ?></option>
                            <option value="archived"><?php echo esc_html__('Archived', 'realestate-booking-suite'); ?></option>
                        </select>
                        <button type="button" class="button" onclick="filterByStatus()"><?php echo esc_html__('Filter', 'realestate-booking-suite'); ?></button>
                    </div>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('ID', 'realestate-booking-suite'); ?></th>
                            <th><?php echo esc_html__('Property', 'realestate-booking-suite'); ?></th>
                            <th><?php echo esc_html__('Customer', 'realestate-booking-suite'); ?></th>
                            <th><?php echo esc_html__('Contact Info', 'realestate-booking-suite'); ?></th>
                            <th><?php echo esc_html__('Message', 'realestate-booking-suite'); ?></th>
                            <th><?php echo esc_html__('Status', 'realestate-booking-suite'); ?></th>
                            <th><?php echo esc_html__('Date', 'realestate-booking-suite'); ?></th>
                            <th><?php echo esc_html__('Actions', 'realestate-booking-suite'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contact_messages as $message): ?>
                            <tr>
                                <td>#<?php echo esc_html($message->id); ?></td>
                                <td>
                                    <strong><?php echo esc_html($message->property_title); ?></strong>
                                    <br>
                                    <small><?php echo esc_html__('ID:', 'realestate-booking-suite'); ?> <?php echo esc_html($message->property_id); ?></small>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($message->name); ?></strong>
                                </td>
                                <td>
                                    <strong><?php echo esc_html__('Email:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html($message->email); ?><br>
                                    <strong><?php echo esc_html__('Phone:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html($message->phone); ?>
                                </td>
                                <td>
                                    <div style="max-width: 300px; word-wrap: break-word;">
                                        <?php 
                                        // Use sanitize_textarea_field to preserve line breaks in messages
                                        $message_preview = sanitize_textarea_field($message->message);
                                        echo esc_html(wp_trim_words($message_preview, 20)); 
                                        ?>
                                        <?php if (strlen($message_preview) > 100): ?>
                                            <?php 
                                            // Sanitize message for JavaScript
                                            $message_safe = sanitize_textarea_field($message->message);
                                            // Use data attribute for safer JSON passing (escaped for HTML attribute)
                                            $message_json = wp_json_encode($message_safe);
                                            ?>
                                            <br><a href="#" 
                                                   class="show-full-message-link" 
                                                   data-message-id="<?php echo esc_attr($message->id); ?>" 
                                                   data-message="<?php echo esc_attr($message_json); ?>"><?php echo esc_html__('Read more...', 'realestate-booking-suite'); ?></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                    $status = sanitize_text_field($message->status);
                                    $status_display = ucfirst($status);
                                    ?>
                                    <span class="status-<?php echo esc_attr($status); ?>">
                                        <?php echo esc_html($status_display); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($message->created_at))); ?>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <select onchange="updateStatus(<?php echo esc_attr($message->id); ?>, this.value, '<?php echo esc_attr(wp_create_nonce('resbs_contact_message_action_' . $message->id)); ?>')">
                                            <option value=""><?php echo esc_html__('Change Status', 'realestate-booking-suite'); ?></option>
                                            <option value="unread" <?php selected($message->status, 'unread'); ?>><?php echo esc_html__('Unread', 'realestate-booking-suite'); ?></option>
                                            <option value="read" <?php selected($message->status, 'read'); ?>><?php echo esc_html__('Read', 'realestate-booking-suite'); ?></option>
                                            <option value="replied" <?php selected($message->status, 'replied'); ?>><?php echo esc_html__('Replied', 'realestate-booking-suite'); ?></option>
                                            <option value="archived" <?php selected($message->status, 'archived'); ?>><?php echo esc_html__('Archived', 'realestate-booking-suite'); ?></option>
                                        </select>
                                        <br><br>
                                        <?php 
                                        // Sanitize email and subject for mailto link
                                        $email = sanitize_email($message->email);
                                        // Get translated subject text and sanitize to prevent email header injection
                                        $subject_text = sprintf(__('Re: Contact Message #%d', 'realestate-booking-suite'), absint($message->id));
                                        $subject = wp_strip_all_tags($subject_text);
                                        $subject = str_replace(array("\r", "\n"), '', $subject);
                                        $subject = urlencode($subject);
                                        ?>
                                        <a href="mailto:<?php echo esc_attr($email); ?>?subject=<?php echo esc_attr($subject); ?>" class="reply-link"><?php echo esc_html__('Reply', 'realestate-booking-suite'); ?></a>
                                        <br>
                                        <a href="<?php echo esc_url(wp_nonce_url(add_query_arg(array('action' => 'delete', 'id' => $message->id), admin_url('edit.php?post_type=property&page=contact-messages')), 'resbs_contact_message_action_' . $message->id)); ?>" 
                                           onclick="return confirm('<?php echo esc_js(__('Are you sure you want to delete this contact message?', 'realestate-booking-suite')); ?>')" 
                                           class="delete-link" style="color: #a00;"><?php echo esc_html__('Delete', 'realestate-booking-suite'); ?></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Full Message Modal -->
        <div id="fullMessageModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
            <div class="bg-white rounded-xl max-w-2xl w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo esc_html__('Full Message', 'realestate-booking-suite'); ?></h3>
                    <button onclick="closeFullMessageModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="fullMessageContent" class="text-gray-700 whitespace-pre-wrap" style="word-wrap: break-word;"></div>
            </div>
        </div>
        
        <!-- Admin contact messages styles and scripts are now enqueued via wp_enqueue_style/wp_enqueue_script -->
        <?php
    }
    
    /**
     * Update contact message status (AJAX handler)
     */
    public function update_contact_message_status() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Unauthorized', 'realestate-booking-suite')));
            return;
        }
        
        // CRITICAL: Do NOT sanitize nonce before verification
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_contact_message_admin_action')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite')));
            return;
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        
        if (empty($id) || empty($status)) {
            wp_send_json_error(array('message' => esc_html__('Invalid parameters', 'realestate-booking-suite')));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'resbs_contact_messages';
        
        $allowed_statuses = array('unread', 'read', 'replied', 'archived');
        if (!in_array($status, $allowed_statuses, true)) {
            wp_send_json_error(array('message' => esc_html__('Invalid status', 'realestate-booking-suite')));
            return;
        }
        
        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => esc_html__('Status updated successfully', 'realestate-booking-suite')));
        } else {
            wp_send_json_error(array('message' => esc_html__('Failed to update status', 'realestate-booking-suite')));
        }
    }
    
    /**
     * Update contact message status (direct call from GET request)
     */
    private function update_contact_message_status_direct($id, $status) {
        // Check user permissions (defense in depth)
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'resbs_contact_messages';
        
        $allowed_statuses = array('unread', 'read', 'replied', 'archived');
        if (!in_array($status, $allowed_statuses, true)) {
            return false;
        }
        
        return $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
    }
    
    /**
     * Delete contact message (AJAX handler)
     */
    public function delete_contact_message() {
        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => esc_html__('Unauthorized', 'realestate-booking-suite')));
            return;
        }
        
        // CRITICAL: Do NOT sanitize nonce before verification
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_contact_message_admin_action')) {
            wp_send_json_error(array('message' => esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite')));
            return;
        }
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        
        if (empty($id)) {
            wp_send_json_error(array('message' => esc_html__('Invalid message ID', 'realestate-booking-suite')));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'resbs_contact_messages';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success(array('message' => esc_html__('Contact message deleted successfully', 'realestate-booking-suite')));
        } else {
            wp_send_json_error(array('message' => esc_html__('Failed to delete contact message', 'realestate-booking-suite')));
        }
    }
    
    /**
     * Delete contact message (direct call from GET request)
     */
    private function delete_contact_message_direct($id) {
        // Check user permissions (defense in depth)
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'resbs_contact_messages';
        
        return $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
    }
}

// Initialize the admin class
new RESBS_Admin_Contact_Messages();
