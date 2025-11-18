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
        add_action('wp_ajax_update_contact_message_status', array($this, 'update_contact_message_status'));
        add_action('wp_ajax_delete_contact_message', array($this, 'delete_contact_message'));
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
            // Verify nonce for security
            if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'resbs_contact_message_action_' . intval($_GET['id']))) {
                wp_die(esc_html__('Security check failed. Please try again.', 'realestate-booking-suite'));
            }
            
            $id = intval($_GET['id']);
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
        // Table names are safe as they're constructed from $wpdb->prefix
        $contact_messages = $wpdb->get_results("
            SELECT cm.*, p.post_title as property_title 
            FROM {$table_name} cm 
            LEFT JOIN {$wpdb->posts} p ON cm.property_id = p.ID 
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
                                        <?php echo esc_html(wp_trim_words($message->message, 20)); ?>
                                        <?php if (strlen($message->message) > 100): ?>
                                            <br><a href="#" onclick="showFullMessage(<?php echo esc_js($message->id); ?>, <?php echo esc_js(wp_json_encode($message->message)); ?>)"><?php echo esc_html__('Read more...', 'realestate-booking-suite'); ?></a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-<?php echo esc_attr($message->status); ?>">
                                        <?php echo esc_html(ucfirst($message->status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo esc_html(date('M j, Y g:i A', strtotime($message->created_at))); ?>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <select onchange="updateStatus(<?php echo esc_js($message->id); ?>, this.value, '<?php echo esc_js(wp_create_nonce('resbs_contact_message_action_' . $message->id)); ?>')">
                                            <option value=""><?php echo esc_html__('Change Status', 'realestate-booking-suite'); ?></option>
                                            <option value="unread" <?php selected($message->status, 'unread'); ?>><?php echo esc_html__('Unread', 'realestate-booking-suite'); ?></option>
                                            <option value="read" <?php selected($message->status, 'read'); ?>><?php echo esc_html__('Read', 'realestate-booking-suite'); ?></option>
                                            <option value="replied" <?php selected($message->status, 'replied'); ?>><?php echo esc_html__('Replied', 'realestate-booking-suite'); ?></option>
                                            <option value="archived" <?php selected($message->status, 'archived'); ?>><?php echo esc_html__('Archived', 'realestate-booking-suite'); ?></option>
                                        </select>
                                        <br><br>
                                        <a href="mailto:<?php echo esc_attr($message->email); ?>?subject=<?php echo esc_attr(sprintf(__('Re: Contact Message #%d', 'realestate-booking-suite'), $message->id)); ?>" class="reply-link"><?php echo esc_html__('Reply', 'realestate-booking-suite'); ?></a>
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
                <div id="fullMessageContent" class="text-gray-700 whitespace-pre-wrap"></div>
            </div>
        </div>
        
        <style>
            .status-unread { color: #d63638; font-weight: bold; }
            .status-read { color: #0073aa; font-weight: bold; }
            .status-replied { color: #00a32a; font-weight: bold; }
            .status-archived { color: #646970; font-weight: bold; }
            .row-actions select { margin-bottom: 5px; }
            .delete-link, .reply-link { text-decoration: none; }
            .delete-link:hover, .reply-link:hover { text-decoration: underline; }
        </style>
        
        <script>
            // Global nonce for AJAX operations
            const resbsContactAdminNonce = '<?php echo esc_js($ajax_nonce); ?>';
            
            function updateStatus(id, status, nonce) {
                if (status && confirm('<?php echo esc_js(__('Are you sure you want to update the status?', 'realestate-booking-suite')); ?>')) {
                    const url = new URL(window.location.href);
                    url.searchParams.set('action', 'update_status');
                    url.searchParams.set('id', id);
                    url.searchParams.set('status', status);
                    url.searchParams.set('_wpnonce', nonce);
                    window.location.href = url.toString();
                }
            }
            
            function filterByStatus() {
                const status = document.getElementById('status-filter').value;
                const url = new URL(window.location.href);
                if (status) {
                    url.searchParams.set('status', status);
                } else {
                    url.searchParams.delete('status');
                }
                window.location.href = url.toString();
            }
            
            function showFullMessage(id, message) {
                document.getElementById('fullMessageContent').textContent = message;
                document.getElementById('fullMessageModal').classList.remove('hidden');
                document.getElementById('fullMessageModal').classList.add('flex');
            }
            
            function closeFullMessageModal() {
                document.getElementById('fullMessageModal').classList.add('hidden');
                document.getElementById('fullMessageModal').classList.remove('flex');
            }
        </script>
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
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_contact_message_admin_action')) {
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
        if (!in_array($status, $allowed_statuses)) {
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
        if (!in_array($status, $allowed_statuses)) {
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
        
        // Verify nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'resbs_contact_message_admin_action')) {
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
