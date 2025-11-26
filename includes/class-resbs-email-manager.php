<?php
/**
 * Email Manager Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Email_Manager {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        // add_action('admin_menu', array($this, 'add_admin_menu')); // Disabled - handled by main settings class
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Email hooks
        add_action('resbs_property_submitted', array($this, 'send_property_submission_email'), 10, 2);
        add_action('resbs_booking_confirmed', array($this, 'send_booking_confirmation_email'), 10, 2);
        add_action('resbs_booking_cancelled', array($this, 'send_booking_cancellation_email'), 10, 2);
        add_action('resbs_search_alert_triggered', array($this, 'send_search_alert_email'), 10, 3);
        
        // AJAX handlers
        add_action('wp_ajax_resbs_send_test_email', array($this, 'ajax_send_test_email'));
        add_action('wp_ajax_resbs_preview_email_template', array($this, 'ajax_preview_email_template'));
    }

    /**
     * Initialize
     */
    public function init() {
        // Set default email templates if not set
        $this->set_default_templates();
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Email Settings', 'realestate-booking-suite'),
            esc_html__('Email Settings', 'realestate-booking-suite'),
            'manage_options',
            'resbs-email-settings',
            array($this, 'email_settings_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting('resbs_email_settings', 'resbs_email_from_name');
        register_setting('resbs_email_settings', 'resbs_email_from_email');
        register_setting('resbs_email_settings', 'resbs_email_reply_to');
        register_setting('resbs_email_settings', 'resbs_email_enable_html');
        register_setting('resbs_email_settings', 'resbs_email_enable_property_submission');
        register_setting('resbs_email_settings', 'resbs_email_enable_booking_emails');
        register_setting('resbs_email_settings', 'resbs_email_enable_search_alerts');
        register_setting('resbs_email_settings', 'resbs_email_admin_notifications');
        register_setting('resbs_email_settings', 'resbs_email_smtp_host');
        register_setting('resbs_email_settings', 'resbs_email_smtp_port');
        register_setting('resbs_email_settings', 'resbs_email_smtp_username');
        register_setting('resbs_email_settings', 'resbs_email_smtp_password');
        register_setting('resbs_email_settings', 'resbs_email_smtp_encryption');
        register_setting('resbs_email_settings', 'resbs_email_smtp_enabled');
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook === 'property_page_resbs-email-settings') {
            wp_enqueue_style(
                'resbs-email-admin',
                RESBS_URL . 'assets/css/email-admin.css',
                array(),
                '1.0.0'
            );
            
            wp_enqueue_script(
                'resbs-email-admin',
                RESBS_URL . 'assets/js/email-admin.js',
                array('jquery'),
                '1.0.0',
                true
            );
            
            wp_localize_script('resbs-email-admin', 'resbs_email_admin_ajax', array(
                'ajax_url' => esc_url(admin_url('admin-ajax.php')),
                'nonce' => esc_js(wp_create_nonce('resbs_email_admin_nonce')),
                'messages' => array(
                    'test_email_sent' => esc_html__('Test email sent successfully!', 'realestate-booking-suite'),
                    'test_email_failed' => esc_html__('Failed to send test email.', 'realestate-booking-suite'),
                    'preview_loading' => esc_html__('Loading preview...', 'realestate-booking-suite'),
                    'preview_error' => esc_html__('Error loading preview.', 'realestate-booking-suite')
                )
            ));
        }
    }

    /**
     * Email settings page
     */
    public function email_settings_page() {
        // Check user permissions - must have manage_options capability
        RESBS_Security::check_capability('manage_options');
        
        // Handle form submission
        if (isset($_POST['submit']) && isset($_POST['resbs_email_settings_nonce'])) {
            // Verify nonce and capability for form submission
            RESBS_Security::verify_nonce_and_capability(
                $_POST['resbs_email_settings_nonce'],
                'resbs_email_settings_nonce',
                'manage_options'
            );
            $this->save_email_settings();
        }
        
        // Get current settings
        $from_name = get_option('resbs_email_from_name', get_bloginfo('name'));
        $from_email = get_option('resbs_email_from_email', get_option('admin_email'));
        $reply_to = get_option('resbs_email_reply_to', get_option('admin_email'));
        $enable_html = get_option('resbs_email_enable_html', true);
        $enable_property_submission = get_option('resbs_email_enable_property_submission', true);
        $enable_booking_emails = get_option('resbs_email_enable_booking_emails', true);
        $enable_search_alerts = get_option('resbs_email_enable_search_alerts', true);
        $admin_notifications = get_option('resbs_email_admin_notifications', true);
        $smtp_enabled = get_option('resbs_email_smtp_enabled', false);
        $smtp_host = get_option('resbs_email_smtp_host', '');
        $smtp_port = get_option('resbs_email_smtp_port', '587');
        $smtp_username = get_option('resbs_email_smtp_username', '');
        $smtp_password = get_option('resbs_email_smtp_password', '');
        $smtp_encryption = get_option('resbs_email_smtp_encryption', 'tls');
        ?>
        
        <div class="wrap">
            <h1><?php esc_html_e('Email Settings', 'realestate-booking-suite'); ?></h1>
            
            <?php settings_errors('resbs_email_settings'); ?>
            
            <form method="post" action="">
                <?php wp_nonce_field('resbs_email_settings_nonce', 'resbs_email_settings_nonce'); ?>
                
                <div class="resbs-email-settings-container">
                    <!-- General Settings -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('General Email Settings', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="resbs_email_from_name">
                                        <?php esc_html_e('From Name', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="resbs_email_from_name"
                                           name="resbs_email_from_name"
                                           value="<?php echo esc_attr($from_name); ?>"
                                           class="regular-text">
                                    <p class="description">
                                        <?php esc_html_e('The name that appears in the "From" field of emails.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_email_from_email">
                                        <?php esc_html_e('From Email', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="email" 
                                           id="resbs_email_from_email"
                                           name="resbs_email_from_email"
                                           value="<?php echo esc_attr($from_email); ?>"
                                           class="regular-text">
                                    <p class="description">
                                        <?php esc_html_e('The email address that appears in the "From" field of emails.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_email_reply_to">
                                        <?php esc_html_e('Reply-To Email', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="email" 
                                           id="resbs_email_reply_to"
                                           name="resbs_email_reply_to"
                                           value="<?php echo esc_attr($reply_to); ?>"
                                           class="regular-text">
                                    <p class="description">
                                        <?php esc_html_e('The email address that replies will be sent to.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Email Format', 'realestate-booking-suite'); ?>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="resbs_email_enable_html" 
                                               value="1" 
                                               <?php checked($enable_html); ?>>
                                        <?php esc_html_e('Enable HTML emails', 'realestate-booking-suite'); ?>
                                    </label>
                                    <p class="description">
                                        <?php esc_html_e('Send emails in HTML format with styling.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Email Types -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('Email Types', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Property Submission', 'realestate-booking-suite'); ?>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="resbs_email_enable_property_submission" 
                                               value="1" 
                                               <?php checked($enable_property_submission); ?>>
                                        <?php esc_html_e('Send emails for new property submissions', 'realestate-booking-suite'); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Booking Emails', 'realestate-booking-suite'); ?>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="resbs_email_enable_booking_emails" 
                                               value="1" 
                                               <?php checked($enable_booking_emails); ?>>
                                        <?php esc_html_e('Send booking confirmation and cancellation emails', 'realestate-booking-suite'); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Search Alerts', 'realestate-booking-suite'); ?>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="resbs_email_enable_search_alerts" 
                                               value="1" 
                                               <?php checked($enable_search_alerts); ?>>
                                        <?php esc_html_e('Send saved search alert emails', 'realestate-booking-suite'); ?>
                                    </label>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Admin Notifications', 'realestate-booking-suite'); ?>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="resbs_email_admin_notifications" 
                                               value="1" 
                                               <?php checked($admin_notifications); ?>>
                                        <?php esc_html_e('Send admin notifications for new submissions', 'realestate-booking-suite'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- SMTP Settings -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('SMTP Settings', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Enable SMTP', 'realestate-booking-suite'); ?>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" 
                                               name="resbs_email_smtp_enabled" 
                                               value="1" 
                                               <?php checked($smtp_enabled); ?>>
                                        <?php esc_html_e('Use SMTP for sending emails', 'realestate-booking-suite'); ?>
                                    </label>
                                    <p class="description">
                                        <?php esc_html_e('Enable this if you want to use an SMTP server instead of the default WordPress mail function.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr class="smtp-setting">
                                <th scope="row">
                                    <label for="resbs_email_smtp_host">
                                        <?php esc_html_e('SMTP Host', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="resbs_email_smtp_host"
                                           name="resbs_email_smtp_host"
                                           value="<?php echo esc_attr($smtp_host); ?>"
                                           class="regular-text">
                                    <p class="description">
                                        <?php esc_html_e('Your SMTP server hostname (e.g., smtp.gmail.com).', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr class="smtp-setting">
                                <th scope="row">
                                    <label for="resbs_email_smtp_port">
                                        <?php esc_html_e('SMTP Port', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="number" 
                                           id="resbs_email_smtp_port"
                                           name="resbs_email_smtp_port"
                                           value="<?php echo esc_attr($smtp_port); ?>"
                                           class="small-text">
                                    <p class="description">
                                        <?php esc_html_e('SMTP port (usually 587 for TLS or 465 for SSL).', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr class="smtp-setting">
                                <th scope="row">
                                    <label for="resbs_email_smtp_username">
                                        <?php esc_html_e('SMTP Username', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="resbs_email_smtp_username"
                                           name="resbs_email_smtp_username"
                                           value="<?php echo esc_attr($smtp_username); ?>"
                                           class="regular-text">
                                    <p class="description">
                                        <?php esc_html_e('Your SMTP username (usually your email address).', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr class="smtp-setting">
                                <th scope="row">
                                    <label for="resbs_email_smtp_password">
                                        <?php esc_html_e('SMTP Password', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="password" 
                                           id="resbs_email_smtp_password"
                                           name="resbs_email_smtp_password"
                                           value="<?php echo esc_attr($smtp_password); ?>"
                                           class="regular-text">
                                    <p class="description">
                                        <?php esc_html_e('Your SMTP password or app password.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr class="smtp-setting">
                                <th scope="row">
                                    <label for="resbs_email_smtp_encryption">
                                        <?php esc_html_e('Encryption', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="resbs_email_smtp_encryption" name="resbs_email_smtp_encryption">
                                        <option value="none" <?php selected($smtp_encryption, 'none'); ?>><?php esc_html_e('None', 'realestate-booking-suite'); ?></option>
                                        <option value="tls" <?php selected($smtp_encryption, 'tls'); ?>><?php esc_html_e('TLS', 'realestate-booking-suite'); ?></option>
                                        <option value="ssl" <?php selected($smtp_encryption, 'ssl'); ?>><?php esc_html_e('SSL', 'realestate-booking-suite'); ?></option>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e('Encryption method for SMTP connection.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <!-- Email Templates -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('Email Templates', 'realestate-booking-suite'); ?></h2>
                        
                        <div class="resbs-email-templates">
                            <div class="resbs-template-tabs">
                                <button type="button" class="resbs-tab-button active" data-template="property_submission">
                                    <?php esc_html_e('Property Submission', 'realestate-booking-suite'); ?>
                                </button>
                                <button type="button" class="resbs-tab-button" data-template="booking_confirmation">
                                    <?php esc_html_e('Booking Confirmation', 'realestate-booking-suite'); ?>
                                </button>
                                <button type="button" class="resbs-tab-button" data-template="booking_cancellation">
                                    <?php esc_html_e('Booking Cancellation', 'realestate-booking-suite'); ?>
                                </button>
                                <button type="button" class="resbs-tab-button" data-template="search_alert">
                                    <?php esc_html_e('Search Alert', 'realestate-booking-suite'); ?>
                                </button>
                            </div>
                            
                            <div class="resbs-template-content">
                                <?php $this->render_template_editor('property_submission'); ?>
                                <?php $this->render_template_editor('booking_confirmation'); ?>
                                <?php $this->render_template_editor('booking_cancellation'); ?>
                                <?php $this->render_template_editor('search_alert'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Test Email -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('Test Email', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="resbs_test_email">
                                        <?php esc_html_e('Test Email Address', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="email" 
                                           id="resbs_test_email"
                                           name="resbs_test_email"
                                           value="<?php echo esc_attr(get_option('admin_email')); ?>"
                                           class="regular-text">
                                    <button type="button" id="resbs-send-test-email" class="button">
                                        <?php esc_html_e('Send Test Email', 'realestate-booking-suite'); ?>
                                    </button>
                                    <p class="description">
                                        <?php esc_html_e('Send a test email to verify your email settings.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php submit_button(esc_html__('Save Email Settings', 'realestate-booking-suite')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Render template editor
     */
    private function render_template_editor($template_type) {
        $template = get_option('resbs_email_template_' . $template_type, $this->get_default_template($template_type));
        $subject = get_option('resbs_email_subject_' . $template_type, $this->get_default_subject($template_type));
        
        $is_active = $template_type === 'property_submission' ? 'active' : '';
        ?>
        <div class="resbs-template-panel <?php echo esc_attr($is_active); ?>" data-template="<?php echo esc_attr($template_type); ?>">
            <h3><?php echo esc_html($this->get_template_title($template_type)); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="resbs_email_subject_<?php echo esc_attr($template_type); ?>">
                            <?php esc_html_e('Email Subject', 'realestate-booking-suite'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" 
                               id="resbs_email_subject_<?php echo esc_attr($template_type); ?>"
                               name="resbs_email_subject_<?php echo esc_attr($template_type); ?>"
                               value="<?php echo esc_attr($subject); ?>"
                               class="large-text">
                        <p class="description">
                            <?php esc_html_e('Available placeholders:', 'realestate-booking-suite'); ?>
                            <?php echo esc_html($this->get_available_placeholders($template_type)); ?>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="resbs_email_template_<?php echo esc_attr($template_type); ?>">
                            <?php esc_html_e('Email Template', 'realestate-booking-suite'); ?>
                        </label>
                    </th>
                    <td>
                        <div class="resbs-template-editor">
                            <div class="resbs-template-toolbar">
                                <button type="button" class="button resbs-insert-placeholder" data-template="<?php echo esc_attr($template_type); ?>">
                                    <?php esc_html_e('Insert Placeholder', 'realestate-booking-suite'); ?>
                                </button>
                                <button type="button" class="button resbs-preview-template" data-template="<?php echo esc_attr($template_type); ?>">
                                    <?php esc_html_e('Preview', 'realestate-booking-suite'); ?>
                                </button>
                            </div>
                            
                            <textarea id="resbs_email_template_<?php echo esc_attr($template_type); ?>"
                                      name="resbs_email_template_<?php echo esc_attr($template_type); ?>"
                                      rows="20"
                                      class="large-text code"><?php echo esc_textarea($template); ?></textarea>
                            
                            <p class="description">
                                <?php esc_html_e('Available placeholders:', 'realestate-booking-suite'); ?>
                                <?php echo esc_html($this->get_available_placeholders($template_type)); ?>
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Save email settings
     */
    private function save_email_settings() {
        // Note: Nonce and capability are already verified in email_settings_page() before calling this method
        // This is a redundant check for extra security, but the main check happens in the page callback
        if (isset($_POST['resbs_email_settings_nonce'])) {
            RESBS_Security::verify_nonce_and_capability(
                $_POST['resbs_email_settings_nonce'],
                'resbs_email_settings_nonce',
                'manage_options'
            );
        }

        // Sanitize and save settings using security helper
        $settings = array(
            'resbs_email_from_name' => RESBS_Security::sanitize_text($_POST['resbs_email_from_name'] ?? ''),
            'resbs_email_from_email' => RESBS_Security::sanitize_email($_POST['resbs_email_from_email'] ?? ''),
            'resbs_email_reply_to' => RESBS_Security::sanitize_email($_POST['resbs_email_reply_to'] ?? ''),
            'resbs_email_enable_html' => RESBS_Security::sanitize_bool($_POST['resbs_email_enable_html'] ?? false),
            'resbs_email_enable_property_submission' => RESBS_Security::sanitize_bool($_POST['resbs_email_enable_property_submission'] ?? false),
            'resbs_email_enable_booking_emails' => RESBS_Security::sanitize_bool($_POST['resbs_email_enable_booking_emails'] ?? false),
            'resbs_email_enable_search_alerts' => RESBS_Security::sanitize_bool($_POST['resbs_email_enable_search_alerts'] ?? false),
            'resbs_email_admin_notifications' => RESBS_Security::sanitize_bool($_POST['resbs_email_admin_notifications'] ?? false),
            'resbs_email_smtp_enabled' => RESBS_Security::sanitize_bool($_POST['resbs_email_smtp_enabled'] ?? false),
            'resbs_email_smtp_host' => RESBS_Security::sanitize_text($_POST['resbs_email_smtp_host'] ?? ''),
            'resbs_email_smtp_port' => $this->sanitize_smtp_port($_POST['resbs_email_smtp_port'] ?? 587),
            'resbs_email_smtp_username' => RESBS_Security::sanitize_text($_POST['resbs_email_smtp_username'] ?? ''),
            'resbs_email_smtp_password' => RESBS_Security::sanitize_text($_POST['resbs_email_smtp_password'] ?? ''),
            'resbs_email_smtp_encryption' => $this->sanitize_smtp_encryption($_POST['resbs_email_smtp_encryption'] ?? 'tls')
        );

        // Save email templates
        $template_types = array('property_submission', 'booking_confirmation', 'booking_cancellation', 'search_alert');
        foreach ($template_types as $type) {
            if (isset($_POST['resbs_email_subject_' . $type])) {
                update_option('resbs_email_subject_' . $type, RESBS_Security::sanitize_text($_POST['resbs_email_subject_' . $type]));
            }
            if (isset($_POST['resbs_email_template_' . $type])) {
                update_option('resbs_email_template_' . $type, RESBS_Security::sanitize_textarea($_POST['resbs_email_template_' . $type]));
            }
        }

        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        add_settings_error('resbs_email_settings', 'settings_updated', 
                          esc_html__('Email settings saved successfully.', 'realestate-booking-suite'), 'updated');
    }

    /**
     * Send property submission email
     */
    public function send_property_submission_email($property_id, $submission_data) {
        if (!get_option('resbs_email_enable_property_submission', true)) {
            return;
        }

        $property = get_post($property_id);
        if (!$property) {
            return;
        }

        $subject = $this->replace_placeholders(
            get_option('resbs_email_subject_property_submission', $this->get_default_subject('property_submission')),
            'property_submission',
            $property_id,
            $submission_data
        );

        $message = $this->replace_placeholders(
            get_option('resbs_email_template_property_submission', $this->get_default_template('property_submission')),
            'property_submission',
            $property_id,
            $submission_data
        );

        $to = sanitize_email($submission_data['email'] ?? get_option('admin_email'));
        $this->send_email($to, $subject, $message);

        // Send admin notification if enabled
        if (get_option('resbs_email_admin_notifications', true)) {
            $admin_subject = sprintf(
                esc_html__('New Property Submission: %s', 'realestate-booking-suite'),
                esc_html($property->post_title)
            );
            
            $admin_message = sprintf(
                esc_html__('A new property has been submitted: %s', 'realestate-booking-suite'),
                esc_html($property->post_title)
            );
            
            $this->send_email(get_option('admin_email'), $admin_subject, $admin_message);
        }
    }

    /**
     * Send booking confirmation email
     */
    public function send_booking_confirmation_email($booking_id, $booking_data) {
        if (!get_option('resbs_email_enable_booking_emails', true)) {
            return;
        }

        $subject = $this->replace_placeholders(
            get_option('resbs_email_subject_booking_confirmation', $this->get_default_subject('booking_confirmation')),
            'booking_confirmation',
            $booking_id,
            $booking_data
        );

        $message = $this->replace_placeholders(
            get_option('resbs_email_template_booking_confirmation', $this->get_default_template('booking_confirmation')),
            'booking_confirmation',
            $booking_id,
            $booking_data
        );

        $to = sanitize_email($booking_data['email'] ?? get_option('admin_email'));
        $this->send_email($to, $subject, $message);
    }

    /**
     * Send booking cancellation email
     */
    public function send_booking_cancellation_email($booking_id, $booking_data) {
        if (!get_option('resbs_email_enable_booking_emails', true)) {
            return;
        }

        $subject = $this->replace_placeholders(
            get_option('resbs_email_subject_booking_cancellation', $this->get_default_subject('booking_cancellation')),
            'booking_cancellation',
            $booking_id,
            $booking_data
        );

        $message = $this->replace_placeholders(
            get_option('resbs_email_template_booking_cancellation', $this->get_default_template('booking_cancellation')),
            'booking_cancellation',
            $booking_id,
            $booking_data
        );

        $to = sanitize_email($booking_data['email'] ?? get_option('admin_email'));
        $this->send_email($to, $subject, $message);
    }

    /**
     * Send search alert email
     */
    public function send_search_alert_email($search_id, $search_data, $matching_properties) {
        if (!get_option('resbs_email_enable_search_alerts', true)) {
            return;
        }

        $subject = $this->replace_placeholders(
            get_option('resbs_email_subject_search_alert', $this->get_default_subject('search_alert')),
            'search_alert',
            $search_id,
            array_merge($search_data, array('properties' => $matching_properties))
        );

        $message = $this->replace_placeholders(
            get_option('resbs_email_template_search_alert', $this->get_default_template('search_alert')),
            'search_alert',
            $search_id,
            array_merge($search_data, array('properties' => $matching_properties))
        );

        $to = sanitize_email($search_data['email'] ?? get_option('admin_email'));
        $this->send_email($to, $subject, $message);
    }

    /**
     * Send email
     */
    private function send_email($to, $subject, $message) {
        $from_name = get_option('resbs_email_from_name', get_bloginfo('name'));
        $from_email = get_option('resbs_email_from_email', get_option('admin_email'));
        $reply_to = get_option('resbs_email_reply_to', get_option('admin_email'));
        $enable_html = get_option('resbs_email_enable_html', true);
        
        // Sanitize email addresses
        $to = sanitize_email($to);
        $from_email = sanitize_email($from_email);
        $reply_to = sanitize_email($reply_to);
        
        // Escape from name for email header (remove any potentially dangerous characters)
        // Remove newlines and carriage returns to prevent header injection
        $from_name = sanitize_text_field($from_name);
        $from_name = str_replace(array("\r", "\n", "\t"), '', $from_name);
        // Quote from name if it contains special characters to prevent header injection
        if (preg_match('/[<>@,;:"]/', $from_name)) {
            $from_name = '"' . str_replace('"', '\\"', $from_name) . '"';
        }

        // Set headers - use proper escaping for email headers
        $headers = array(
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $reply_to,
            'Content-Type: ' . ($enable_html ? 'text/html' : 'text/plain') . '; charset=UTF-8'
        );

        // Add HTML wrapper if HTML is enabled
        if ($enable_html) {
            $message = $this->wrap_html_email($message);
        }

        // Send email - sanitize subject to prevent header injection
        // Remove newlines and strip HTML tags
        $subject_safe = wp_strip_all_tags($subject);
        $subject_safe = str_replace(array("\r", "\n"), '', $subject_safe);
        $sent = wp_mail($to, $subject_safe, $message, $headers);

        return $sent;
    }

    /**
     * Wrap email in HTML template
     * WordPress.org compliant - no <style> tags in PHP files
     * All styles are applied inline for email client compatibility
     */
    private function wrap_html_email($content) {
        $site_name = get_bloginfo('name');
        $site_url = home_url();
        
        // Build HTML email template with inline styles (WordPress.org compliant)
        // Note: Email clients require inline styles, so we apply them directly to elements
        // CSS definitions are maintained in assets/css/email-styles.css for reference
        $html = '<!DOCTYPE html>' . "\n";
        $html .= '<html>' . "\n";
        $html .= '<head>' . "\n";
        $html .= '<meta charset="UTF-8">' . "\n";
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
        $html .= '<title>' . esc_html($site_name) . '</title>' . "\n";
        $html .= '</head>' . "\n";
        $html .= '<body>' . "\n";
        $html .= '<div class="resbs-email-container" style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #ffffff;">' . "\n";
        $html .= '<div class="resbs-email-header" style="background: #0073aa; color: #ffffff; padding: 20px; text-align: center; border-radius: 4px 4px 0 0;">' . "\n";
        $html .= '<h1 style="margin: 0; font-size: 24px; font-weight: 600;">' . esc_html($site_name) . '</h1>' . "\n";
        $html .= '</div>' . "\n";
        $html .= '<div class="resbs-email-content" style="padding: 30px 20px; background: #ffffff;">' . "\n";
        $html .= wp_kses_post($content) . "\n";
        $html .= '</div>' . "\n";
        $html .= '<div class="resbs-email-footer" style="background: #f8f9fa; border-top: 1px solid #e9ecef; padding: 20px; text-align: center; border-radius: 0 0 4px 4px;">' . "\n";
        $html .= '<p style="margin: 0; color: #666; font-size: 12px; line-height: 1.4;">' . esc_html__('This email was sent from', 'realestate-booking-suite') . ' <a href="' . esc_url($site_url) . '" style="color: #0073aa; text-decoration: none;">' . esc_html($site_name) . '</a></p>' . "\n";
        $html .= '</div>' . "\n";
        $html .= '</div>' . "\n";
        $html .= '</body>' . "\n";
        $html .= '</html>';
        
        return $html;
    }

    /**
     * Replace placeholders in email content
     */
    private function replace_placeholders($content, $template_type, $object_id, $data) {
        $placeholders = $this->get_placeholders($template_type, $object_id, $data);
        
        foreach ($placeholders as $placeholder => $value) {
            $content = str_replace('{' . $placeholder . '}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Get placeholders for template type
     */
    private function get_placeholders($template_type, $object_id, $data) {
        $placeholders = array(
            'site_name' => esc_html(get_bloginfo('name')),
            'site_url' => esc_url(home_url()),
            'admin_email' => sanitize_email(get_option('admin_email')),
            'current_date' => esc_html(current_time(get_option('date_format'))),
            'current_time' => esc_html(current_time(get_option('time_format')))
        );

        switch ($template_type) {
            case 'property_submission':
                $property = get_post($object_id);
                if ($property) {
                    $placeholders = array_merge($placeholders, array(
                        'property_title' => esc_html($property->post_title),
                        'property_url' => esc_url(get_permalink($object_id)),
                        'property_id' => absint($object_id),
                        'submission_date' => esc_html(get_the_date('', $object_id)),
                        'submission_time' => esc_html(get_the_time('', $object_id)),
                        'submitter_name' => esc_html($data['name'] ?? ''),
                        'submitter_email' => sanitize_email($data['email'] ?? ''),
                        'submitter_phone' => esc_html($data['phone'] ?? '')
                    ));
                }
                break;

            case 'booking_confirmation':
            case 'booking_cancellation':
                $placeholders = array_merge($placeholders, array(
                    'booking_id' => absint($object_id),
                    'booking_date' => esc_html($data['date'] ?? ''),
                    'booking_time' => esc_html($data['time'] ?? ''),
                    'property_title' => esc_html($data['property_title'] ?? ''),
                    'property_url' => esc_url($data['property_url'] ?? ''),
                    'customer_name' => esc_html($data['name'] ?? ''),
                    'customer_email' => sanitize_email($data['email'] ?? ''),
                    'customer_phone' => esc_html($data['phone'] ?? ''),
                    'booking_notes' => esc_html($data['notes'] ?? '')
                ));
                break;

            case 'search_alert':
                $placeholders = array_merge($placeholders, array(
                    'search_id' => absint($object_id),
                    'search_criteria' => esc_html($data['criteria'] ?? ''),
                    'properties_count' => absint(count($data['properties'] ?? array())),
                    'subscriber_name' => esc_html($data['name'] ?? ''),
                    'subscriber_email' => sanitize_email($data['email'] ?? ''),
                    'alert_date' => esc_html(current_time(get_option('date_format')))
                ));
                break;
        }

        return $placeholders;
    }

    /**
     * Get default template
     */
    private function get_default_template($template_type) {
        $templates = array(
            'property_submission' => esc_html__('Thank you for submitting your property!

Dear {submitter_name},

We have received your property submission for "{property_title}" and it is currently under review.

Property Details:
- Title: {property_title}
- Submission Date: {submission_date}
- Property ID: {property_id}

You can view your property here: {property_url}

We will review your submission and get back to you within 24-48 hours.

Thank you for choosing {site_name}!

Best regards,
The {site_name} Team', 'realestate-booking-suite'),

            'booking_confirmation' => esc_html__('Booking Confirmation

Dear {customer_name},

Your booking has been confirmed!

Booking Details:
- Property: {property_title}
- Date: {booking_date}
- Time: {booking_time}
- Booking ID: {booking_id}

Property Link: {property_url}

Notes: {booking_notes}

If you have any questions, please contact us at {admin_email}.

Thank you for choosing {site_name}!

Best regards,
The {site_name} Team', 'realestate-booking-suite'),

            'booking_cancellation' => esc_html__('Booking Cancellation

Dear {customer_name},

Your booking has been cancelled.

Booking Details:
- Property: {property_title}
- Date: {booking_date}
- Time: {booking_time}
- Booking ID: {booking_id}

If you have any questions or would like to reschedule, please contact us at {admin_email}.

Thank you for your interest in {site_name}!

Best regards,
The {site_name} Team', 'realestate-booking-suite'),

            'search_alert' => esc_html__('New Properties Match Your Search!

Dear {subscriber_name},

We found {properties_count} new properties that match your search criteria.

Search Criteria: {search_criteria}

Here are the matching properties:

{properties_list}

Visit our website to view more details: {site_url}

If you no longer wish to receive these alerts, you can unsubscribe from your account settings.

Thank you for using {site_name}!

Best regards,
The {site_name} Team', 'realestate-booking-suite')
        );

        return $templates[$template_type] ?? '';
    }

    /**
     * Get default subject
     */
    private function get_default_subject($template_type) {
        $subjects = array(
            'property_submission' => esc_html__('Property Submission Received - {site_name}', 'realestate-booking-suite'),
            'booking_confirmation' => esc_html__('Booking Confirmed - {property_title}', 'realestate-booking-suite'),
            'booking_cancellation' => esc_html__('Booking Cancelled - {property_title}', 'realestate-booking-suite'),
            'search_alert' => esc_html__('New Properties Found - {site_name}', 'realestate-booking-suite')
        );

        return $subjects[$template_type] ?? '';
    }

    /**
     * Get template title
     */
    private function get_template_title($template_type) {
        $titles = array(
            'property_submission' => esc_html__('Property Submission Email', 'realestate-booking-suite'),
            'booking_confirmation' => esc_html__('Booking Confirmation Email', 'realestate-booking-suite'),
            'booking_cancellation' => esc_html__('Booking Cancellation Email', 'realestate-booking-suite'),
            'search_alert' => esc_html__('Search Alert Email', 'realestate-booking-suite')
        );

        return $titles[$template_type] ?? '';
    }

    /**
     * Get available placeholders
     */
    private function get_available_placeholders($template_type) {
        $placeholders = array(
            'property_submission' => '{site_name}, {property_title}, {property_url}, {property_id}, {submission_date}, {submission_time}, {submitter_name}, {submitter_email}, {submitter_phone}',
            'booking_confirmation' => '{site_name}, {booking_id}, {booking_date}, {booking_time}, {property_title}, {property_url}, {customer_name}, {customer_email}, {customer_phone}, {booking_notes}',
            'booking_cancellation' => '{site_name}, {booking_id}, {booking_date}, {booking_time}, {property_title}, {property_url}, {customer_name}, {customer_email}, {customer_phone}',
            'search_alert' => '{site_name}, {search_id}, {search_criteria}, {properties_count}, {subscriber_name}, {subscriber_email}, {alert_date}'
        );

        return $placeholders[$template_type] ?? '';
    }

    /**
     * Set default templates
     */
    private function set_default_templates() {
        $template_types = array('property_submission', 'booking_confirmation', 'booking_cancellation', 'search_alert');
        
        foreach ($template_types as $type) {
            if (!get_option('resbs_email_template_' . $type)) {
                update_option('resbs_email_template_' . $type, $this->get_default_template($type));
            }
            if (!get_option('resbs_email_subject_' . $type)) {
                update_option('resbs_email_subject_' . $type, $this->get_default_subject($type));
            }
        }
    }

    /**
     * AJAX handler for sending test email
     */
    public function ajax_send_test_email() {
        // Verify nonce and check permissions using combined security helper
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        RESBS_Security::verify_ajax_nonce_and_capability($nonce, 'resbs_email_admin_nonce', 'manage_options');

        // Check if test_email is provided
        if (!isset($_POST['test_email'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Email address is required.', 'realestate-booking-suite')
            ));
        }
        
        $test_email = RESBS_Security::sanitize_email($_POST['test_email']);
        
        if (!is_email($test_email)) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid email address.', 'realestate-booking-suite')
            ));
        }

        $subject = esc_html__('Test Email from RealEstate Booking Suite', 'realestate-booking-suite');
        $message = esc_html__('This is a test email to verify your email settings are working correctly.', 'realestate-booking-suite');

        $sent = $this->send_email($test_email, $subject, $message);

        if ($sent) {
            wp_send_json_success(array(
                'message' => esc_html__('Test email sent successfully!', 'realestate-booking-suite')
            ));
        } else {
            wp_send_json_error(array(
                'message' => esc_html__('Failed to send test email.', 'realestate-booking-suite')
            ));
        }
    }

    /**
     * AJAX handler for previewing email template
     */
    public function ajax_preview_email_template() {
        // Verify nonce and check permissions using combined security helper
        $nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
        RESBS_Security::verify_ajax_nonce_and_capability($nonce, 'resbs_email_admin_nonce', 'manage_options');

        // Check if required parameters are provided
        if (!isset($_POST['template_type']) || !isset($_POST['template_content'])) {
            wp_send_json_error(array(
                'message' => esc_html__('Template type and content are required.', 'realestate-booking-suite')
            ));
        }
        
        $template_type = RESBS_Security::sanitize_text($_POST['template_type']);
        $template_content = RESBS_Security::sanitize_textarea($_POST['template_content']);

        // Validate template type against whitelist
        $allowed_template_types = array('property_submission', 'booking_confirmation', 'booking_cancellation', 'search_alert');
        if (!in_array($template_type, $allowed_template_types, true)) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid template type.', 'realestate-booking-suite')
            ));
            return;
        }

        // Generate preview with sample data
        $sample_data = $this->get_sample_data($template_type);
        $preview = $this->replace_placeholders($template_content, $template_type, 1, $sample_data);

        wp_send_json_success(array(
            'preview' => wp_kses_post($preview)
        ));
    }

    /**
     * Get sample data for preview
     */
    private function get_sample_data($template_type) {
        $sample_data = array(
            'name' => esc_html__('John Doe', 'realestate-booking-suite'),
            'email' => sanitize_email('john@example.com'),
            'phone' => esc_html('+1-555-123-4567'),
            'date' => esc_html(current_time(get_option('date_format'))),
            'time' => esc_html(current_time(get_option('time_format'))),
            'notes' => esc_html__('Sample booking notes', 'realestate-booking-suite'),
            'criteria' => esc_html__('3 bedrooms, 2 bathrooms, under $500,000', 'realestate-booking-suite'),
            'property_title' => esc_html__('Beautiful Family Home', 'realestate-booking-suite'),
            'property_url' => esc_url(home_url('/property/sample-property/')),
            'properties' => array(
                array(
                    'title' => esc_html__('Sample Property 1', 'realestate-booking-suite'),
                    'url' => esc_url(home_url('/property/sample-1/')),
                    'price' => esc_html('$450,000')
                ),
                array(
                    'title' => esc_html__('Sample Property 2', 'realestate-booking-suite'),
                    'url' => esc_url(home_url('/property/sample-2/')),
                    'price' => esc_html('$475,000')
                )
            )
        );

        return $sample_data;
    }

    /**
     * Sanitize SMTP encryption value
     */
    private function sanitize_smtp_encryption($value) {
        $allowed_values = array('none', 'tls', 'ssl');
        $sanitized = sanitize_text_field($value);
        if (!in_array($sanitized, $allowed_values, true)) {
            return 'tls'; // Default to TLS
        }
        return $sanitized;
    }

    /**
     * Sanitize SMTP port value
     */
    private function sanitize_smtp_port($value) {
        $port = RESBS_Security::sanitize_int($value, 587);
        // Validate port is in common SMTP port range
        if ($port < 1 || $port > 65535) {
            return 587; // Default to 587
        }
        return $port;
    }
}

// Initialize Email Manager
new RESBS_Email_Manager();

