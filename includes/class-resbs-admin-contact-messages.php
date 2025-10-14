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
        global $wpdb;
        $table_name = $wpdb->prefix . 'resbs_contact_messages';
        
        // Handle actions
        if (isset($_GET['action']) && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            
            switch ($_GET['action']) {
                case 'update_status':
                    if (isset($_GET['status'])) {
                        $status = sanitize_text_field($_GET['status']);
                        $this->update_contact_message_status($id, $status);
                    }
                    break;
                case 'delete':
                    $this->delete_contact_message($id);
                    break;
            }
        }
        
        // Get contact messages
        $contact_messages = $wpdb->get_results("
            SELECT cm.*, p.post_title as property_title 
            FROM {$table_name} cm 
            LEFT JOIN {$wpdb->posts} p ON cm.property_id = p.ID 
            ORDER BY cm.created_at DESC
        ");
        
        ?>
        <div class="wrap">
            <h1>Contact Messages</h1>
            
            <?php if (empty($contact_messages)): ?>
                <div class="notice notice-info">
                    <p>No contact messages found.</p>
                </div>
            <?php else: ?>
                <div class="tablenav top">
                    <div class="alignleft actions">
                        <select id="status-filter">
                            <option value="">All Status</option>
                            <option value="unread">Unread</option>
                            <option value="read">Read</option>
                            <option value="replied">Replied</option>
                            <option value="archived">Archived</option>
                        </select>
                        <button type="button" class="button" onclick="filterByStatus()">Filter</button>
                    </div>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Property</th>
                            <th>Customer</th>
                            <th>Contact Info</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contact_messages as $message): ?>
                            <tr>
                                <td>#<?php echo $message->id; ?></td>
                                <td>
                                    <strong><?php echo esc_html($message->property_title); ?></strong>
                                    <br>
                                    <small>ID: <?php echo $message->property_id; ?></small>
                                </td>
                                <td>
                                    <strong><?php echo esc_html($message->name); ?></strong>
                                </td>
                                <td>
                                    <strong>Email:</strong> <?php echo esc_html($message->email); ?><br>
                                    <strong>Phone:</strong> <?php echo esc_html($message->phone); ?>
                                </td>
                                <td>
                                    <div style="max-width: 300px; word-wrap: break-word;">
                                        <?php echo esc_html(wp_trim_words($message->message, 20)); ?>
                                        <?php if (strlen($message->message) > 100): ?>
                                            <br><a href="#" onclick="showFullMessage(<?php echo $message->id; ?>, '<?php echo esc_js($message->message); ?>')">Read more...</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-<?php echo $message->status; ?>">
                                        <?php echo ucfirst($message->status); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('M j, Y g:i A', strtotime($message->created_at)); ?>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <select onchange="updateStatus(<?php echo $message->id; ?>, this.value)">
                                            <option value="">Change Status</option>
                                            <option value="unread" <?php selected($message->status, 'unread'); ?>>Unread</option>
                                            <option value="read" <?php selected($message->status, 'read'); ?>>Read</option>
                                            <option value="replied" <?php selected($message->status, 'replied'); ?>>Replied</option>
                                            <option value="archived" <?php selected($message->status, 'archived'); ?>>Archived</option>
                                        </select>
                                        <br><br>
                                        <a href="mailto:<?php echo esc_attr($message->email); ?>?subject=Re: Contact Message #<?php echo $message->id; ?>" class="reply-link">Reply</a>
                                        <br>
                                        <a href="?page=contact-messages&action=delete&id=<?php echo $message->id; ?>" 
                                           onclick="return confirm('Are you sure you want to delete this contact message?')" 
                                           class="delete-link" style="color: #a00;">Delete</a>
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
                    <h3 class="text-2xl font-bold text-gray-800">Full Message</h3>
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
            function updateStatus(id, status) {
                if (status && confirm('Are you sure you want to update the status?')) {
                    window.location.href = '?page=contact-messages&action=update_status&id=' + id + '&status=' + status;
                }
            }
            
            function filterByStatus() {
                const status = document.getElementById('status-filter').value;
                if (status) {
                    window.location.href = '?page=contact-messages&status=' + status;
                } else {
                    window.location.href = '?page=contact-messages';
                }
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
     * Update contact message status
     */
    public function update_contact_message_status() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $id = intval($_POST['id']);
        $status = sanitize_text_field($_POST['status']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'resbs_contact_messages';
        
        $result = $wpdb->update(
            $table_name,
            array('status' => $status),
            array('id' => $id),
            array('%s'),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success('Status updated successfully');
        } else {
            wp_send_json_error('Failed to update status');
        }
    }
    
    /**
     * Delete contact message
     */
    public function delete_contact_message() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $id = intval($_POST['id']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'resbs_contact_messages';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $id),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success('Contact message deleted successfully');
        } else {
            wp_send_json_error('Failed to delete contact message');
        }
    }
}

// Initialize the admin class
new RESBS_Admin_Contact_Messages();
