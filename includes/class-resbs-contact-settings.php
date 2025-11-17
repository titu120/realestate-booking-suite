<?php
/**
 * Contact Settings Manager Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Contact_Settings {

    /**
     * Constructor
     */
    public function __construct() {
        // add_action('admin_menu', array($this, 'add_admin_menu')); // Disabled - handled by main settings class
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Contact Settings', 'realestate-booking-suite'),
            esc_html__('Contact Settings', 'realestate-booking-suite'),
            'manage_options',
            'resbs-contact-settings',
            array($this, 'admin_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        // Contact Information
        register_setting('resbs_contact_settings', 'resbs_contact_phone');
        register_setting('resbs_contact_settings', 'resbs_contact_email');
        register_setting('resbs_contact_settings', 'resbs_contact_address');
        register_setting('resbs_contact_settings', 'resbs_contact_website');
        register_setting('resbs_contact_settings', 'resbs_contact_whatsapp');
        register_setting('resbs_contact_settings', 'resbs_contact_telegram');
        
        // Business Information
        register_setting('resbs_contact_settings', 'resbs_business_name');
        register_setting('resbs_contact_settings', 'resbs_business_hours');
        register_setting('resbs_contact_settings', 'resbs_business_description');
        
        // Social Media
        register_setting('resbs_contact_settings', 'resbs_social_facebook');
        register_setting('resbs_contact_settings', 'resbs_social_twitter');
        register_setting('resbs_contact_settings', 'resbs_social_instagram');
        register_setting('resbs_contact_settings', 'resbs_social_linkedin');
        register_setting('resbs_contact_settings', 'resbs_social_youtube');
        
        // Display Options
        register_setting('resbs_contact_settings', 'resbs_show_contact_widget');
        register_setting('resbs_contact_settings', 'resbs_contact_widget_title');
        register_setting('resbs_contact_settings', 'resbs_contact_widget_style');
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook === 'property_page_resbs-contact-settings') {
            wp_enqueue_style(
                'resbs-contact-admin',
                RESBS_URL . 'assets/css/contact-admin.css',
                array(),
                '1.0.0'
            );
        }
    }

    /**
     * Admin page
     */
    public function admin_page() {
        // Check user permissions
        RESBS_Security::check_capability('manage_options');
        
        if (isset($_POST['submit'])) {
            $this->save_contact_settings();
        }
        
        // Get current settings
        $settings = $this->get_contact_settings();
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Contact Settings', 'realestate-booking-suite'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('resbs_contact_settings_nonce', 'resbs_contact_settings_nonce'); ?>
                
                <div class="resbs-contact-settings">
                    <!-- Contact Information Section -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('Contact Information', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="resbs_contact_phone">
                                        <?php esc_html_e('Phone Number', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="tel" 
                                           id="resbs_contact_phone"
                                           name="resbs_contact_phone"
                                           value="<?php echo esc_attr($settings['phone']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('+1-234-567-8900', 'realestate-booking-suite'); ?>">
                                    <p class="description">
                                        <?php esc_html_e('Primary contact phone number.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_contact_email">
                                        <?php esc_html_e('Email Address', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="email" 
                                           id="resbs_contact_email"
                                           name="resbs_contact_email"
                                           value="<?php echo esc_attr($settings['email']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('info@yoursite.com', 'realestate-booking-suite'); ?>">
                                    <p class="description">
                                        <?php esc_html_e('Primary contact email address.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_contact_address">
                                        <?php esc_html_e('Business Address', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <textarea id="resbs_contact_address"
                                              name="resbs_contact_address"
                                              rows="3"
                                              class="large-text"
                                              placeholder="<?php esc_attr_e('123 Main Street, City, State 12345', 'realestate-booking-suite'); ?>"><?php echo esc_textarea($settings['address']); ?></textarea>
                                    <p class="description">
                                        <?php esc_html_e('Complete business address.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_contact_website">
                                        <?php esc_html_e('Website URL', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="url" 
                                           id="resbs_contact_website"
                                           name="resbs_contact_website"
                                           value="<?php echo esc_attr($settings['website']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('https://yoursite.com', 'realestate-booking-suite'); ?>">
                                    <p class="description">
                                        <?php esc_html_e('Official website URL.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_contact_whatsapp">
                                        <?php esc_html_e('WhatsApp Number', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="tel" 
                                           id="resbs_contact_whatsapp"
                                           name="resbs_contact_whatsapp"
                                           value="<?php echo esc_attr($settings['whatsapp']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('+1-234-567-8900', 'realestate-booking-suite'); ?>">
                                    <p class="description">
                                        <?php esc_html_e('WhatsApp contact number (optional).', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_contact_telegram">
                                        <?php esc_html_e('Telegram Username', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="resbs_contact_telegram"
                                           name="resbs_contact_telegram"
                                           value="<?php echo esc_attr($settings['telegram']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('@yourusername', 'realestate-booking-suite'); ?>">
                                    <p class="description">
                                        <?php esc_html_e('Telegram username (optional).', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Business Information Section -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('Business Information', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="resbs_business_name">
                                        <?php esc_html_e('Business Name', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="resbs_business_name"
                                           name="resbs_business_name"
                                           value="<?php echo esc_attr($settings['business_name']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('Your Business Name', 'realestate-booking-suite'); ?>">
                                    <p class="description">
                                        <?php esc_html_e('Official business name.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_business_hours">
                                        <?php esc_html_e('Business Hours', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <textarea id="resbs_business_hours"
                                              name="resbs_business_hours"
                                              rows="4"
                                              class="large-text"
                                              placeholder="<?php esc_attr_e('Monday - Friday: 9:00 AM - 6:00 PM\nSaturday: 10:00 AM - 4:00 PM\nSunday: Closed', 'realestate-booking-suite'); ?>"><?php echo esc_textarea($settings['business_hours']); ?></textarea>
                                    <p class="description">
                                        <?php esc_html_e('Business operating hours.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_business_description">
                                        <?php esc_html_e('Business Description', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <textarea id="resbs_business_description"
                                              name="resbs_business_description"
                                              rows="3"
                                              class="large-text"
                                              placeholder="<?php esc_attr_e('Brief description of your real estate business...', 'realestate-booking-suite'); ?>"><?php echo esc_textarea($settings['business_description']); ?></textarea>
                                    <p class="description">
                                        <?php esc_html_e('Brief description of your business (optional).', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Social Media Section -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('Social Media Links', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="resbs_social_facebook">
                                        <?php esc_html_e('Facebook URL', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="url" 
                                           id="resbs_social_facebook"
                                           name="resbs_social_facebook"
                                           value="<?php echo esc_attr($settings['facebook']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('https://facebook.com/yourpage', 'realestate-booking-suite'); ?>">
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_social_twitter">
                                        <?php esc_html_e('Twitter URL', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="url" 
                                           id="resbs_social_twitter"
                                           name="resbs_social_twitter"
                                           value="<?php echo esc_attr($settings['twitter']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('https://twitter.com/yourhandle', 'realestate-booking-suite'); ?>">
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_social_instagram">
                                        <?php esc_html_e('Instagram URL', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="url" 
                                           id="resbs_social_instagram"
                                           name="resbs_social_instagram"
                                           value="<?php echo esc_attr($settings['instagram']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('https://instagram.com/yourhandle', 'realestate-booking-suite'); ?>">
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_social_linkedin">
                                        <?php esc_html_e('LinkedIn URL', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="url" 
                                           id="resbs_social_linkedin"
                                           name="resbs_social_linkedin"
                                           value="<?php echo esc_attr($settings['linkedin']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('https://linkedin.com/company/yourcompany', 'realestate-booking-suite'); ?>">
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_social_youtube">
                                        <?php esc_html_e('YouTube URL', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="url" 
                                           id="resbs_social_youtube"
                                           name="resbs_social_youtube"
                                           value="<?php echo esc_attr($settings['youtube']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('https://youtube.com/yourchannel', 'realestate-booking-suite'); ?>">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Display Options Section -->
                    <div class="resbs-settings-section">
                        <h2><?php esc_html_e('Display Options', 'realestate-booking-suite'); ?></h2>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <?php esc_html_e('Contact Widget', 'realestate-booking-suite'); ?>
                                </th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input type="checkbox" 
                                                   name="resbs_show_contact_widget"
                                                   value="1" 
                                                   <?php checked($settings['show_contact_widget']); ?>>
                                            <?php esc_html_e('Show contact information widget', 'realestate-booking-suite'); ?>
                                        </label>
                                        <p class="description">
                                            <?php esc_html_e('Display contact information in property pages and widgets.', 'realestate-booking-suite'); ?>
                                        </p>
                                    </fieldset>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_contact_widget_title">
                                        <?php esc_html_e('Widget Title', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="resbs_contact_widget_title"
                                           name="resbs_contact_widget_title"
                                           value="<?php echo esc_attr($settings['widget_title']); ?>"
                                           class="regular-text"
                                           placeholder="<?php esc_attr_e('Contact Us', 'realestate-booking-suite'); ?>">
                                    <p class="description">
                                        <?php esc_html_e('Title for contact information widget.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="resbs_contact_widget_style">
                                        <?php esc_html_e('Widget Style', 'realestate-booking-suite'); ?>
                                    </label>
                                </th>
                                <td>
                                    <select id="resbs_contact_widget_style" name="resbs_contact_widget_style">
                                        <option value="default" <?php selected($settings['widget_style'], 'default'); ?>><?php esc_html_e('Default', 'realestate-booking-suite'); ?></option>
                                        <option value="minimal" <?php selected($settings['widget_style'], 'minimal'); ?>><?php esc_html_e('Minimal', 'realestate-booking-suite'); ?></option>
                                        <option value="detailed" <?php selected($settings['widget_style'], 'detailed'); ?>><?php esc_html_e('Detailed', 'realestate-booking-suite'); ?></option>
                                    </select>
                                    <p class="description">
                                        <?php esc_html_e('Choose the display style for contact information.', 'realestate-booking-suite'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <?php submit_button(esc_html__('Save Contact Settings', 'realestate-booking-suite')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Save contact settings
     */
    private function save_contact_settings() {
        // Check if nonce exists
        if (!isset($_POST['resbs_contact_settings_nonce'])) {
            wp_die(
                esc_html__('Security check failed. Please refresh the page and try again.', 'realestate-booking-suite'),
                esc_html__('Security Error', 'realestate-booking-suite'),
                array('response' => 403)
            );
        }
        
        // Verify nonce and check permissions using combined security helper
        RESBS_Security::verify_nonce_and_capability(
            $_POST['resbs_contact_settings_nonce'],
            'resbs_contact_settings_nonce',
            'manage_options'
        );

        // Sanitize and save settings using security helper
        // Use null coalescing operator to safely handle missing POST values
        $settings = array(
            'resbs_contact_phone' => RESBS_Security::sanitize_text($_POST['resbs_contact_phone'] ?? ''),
            'resbs_contact_email' => RESBS_Security::sanitize_email($_POST['resbs_contact_email'] ?? ''),
            'resbs_contact_address' => RESBS_Security::sanitize_textarea($_POST['resbs_contact_address'] ?? ''),
            'resbs_contact_website' => RESBS_Security::sanitize_url($_POST['resbs_contact_website'] ?? ''),
            'resbs_contact_whatsapp' => RESBS_Security::sanitize_text($_POST['resbs_contact_whatsapp'] ?? ''),
            'resbs_contact_telegram' => RESBS_Security::sanitize_text($_POST['resbs_contact_telegram'] ?? ''),
            'resbs_business_name' => RESBS_Security::sanitize_text($_POST['resbs_business_name'] ?? ''),
            'resbs_business_hours' => RESBS_Security::sanitize_textarea($_POST['resbs_business_hours'] ?? ''),
            'resbs_business_description' => RESBS_Security::sanitize_textarea($_POST['resbs_business_description'] ?? ''),
            'resbs_social_facebook' => RESBS_Security::sanitize_url($_POST['resbs_social_facebook'] ?? ''),
            'resbs_social_twitter' => RESBS_Security::sanitize_url($_POST['resbs_social_twitter'] ?? ''),
            'resbs_social_instagram' => RESBS_Security::sanitize_url($_POST['resbs_social_instagram'] ?? ''),
            'resbs_social_linkedin' => RESBS_Security::sanitize_url($_POST['resbs_social_linkedin'] ?? ''),
            'resbs_social_youtube' => RESBS_Security::sanitize_url($_POST['resbs_social_youtube'] ?? ''),
            'resbs_show_contact_widget' => RESBS_Security::sanitize_bool($_POST['resbs_show_contact_widget'] ?? false),
            'resbs_contact_widget_title' => RESBS_Security::sanitize_text($_POST['resbs_contact_widget_title'] ?? ''),
            'resbs_contact_widget_style' => RESBS_Security::sanitize_text($_POST['resbs_contact_widget_style'] ?? 'default')
        );

        foreach ($settings as $key => $value) {
            update_option($key, $value);
        }
        
        add_settings_error('resbs_contact_settings', 'settings_updated', 
                          esc_html__('Contact settings saved successfully.', 'realestate-booking-suite'), 'updated');
    }

    /**
     * Get contact settings
     */
    public function get_contact_settings() {
        return array(
            'phone' => get_option('resbs_contact_phone', ''),
            'email' => get_option('resbs_contact_email', ''),
            'address' => get_option('resbs_contact_address', ''),
            'website' => get_option('resbs_contact_website', ''),
            'whatsapp' => get_option('resbs_contact_whatsapp', ''),
            'telegram' => get_option('resbs_contact_telegram', ''),
            'business_name' => get_option('resbs_business_name', ''),
            'business_hours' => get_option('resbs_business_hours', ''),
            'business_description' => get_option('resbs_business_description', ''),
            'facebook' => get_option('resbs_social_facebook', ''),
            'twitter' => get_option('resbs_social_twitter', ''),
            'instagram' => get_option('resbs_social_instagram', ''),
            'linkedin' => get_option('resbs_social_linkedin', ''),
            'youtube' => get_option('resbs_social_youtube', ''),
            'show_contact_widget' => get_option('resbs_show_contact_widget', true),
            'widget_title' => get_option('resbs_contact_widget_title', __('Contact Us', 'realestate-booking-suite')),
            'widget_style' => get_option('resbs_contact_widget_style', 'default')
        );
    }

    /**
     * Get formatted contact information
     */
    public function get_formatted_contact_info() {
        $settings = $this->get_contact_settings();
        
        $contact_info = array();
        
        if (!empty($settings['phone'])) {
            $contact_info['phone'] = array(
                'label' => esc_html__('Phone:', 'realestate-booking-suite'),
                'value' => esc_html($settings['phone']),
                'link' => 'tel:' . esc_attr($settings['phone'])
            );
        }
        
        if (!empty($settings['email'])) {
            $contact_info['email'] = array(
                'label' => esc_html__('Email:', 'realestate-booking-suite'),
                'value' => esc_html($settings['email']),
                'link' => 'mailto:' . esc_attr($settings['email'])
            );
        }
        
        if (!empty($settings['whatsapp'])) {
            $contact_info['whatsapp'] = array(
                'label' => esc_html__('WhatsApp:', 'realestate-booking-suite'),
                'value' => esc_html($settings['whatsapp']),
                'link' => 'https://wa.me/' . esc_attr(preg_replace('/[^0-9]/', '', $settings['whatsapp']))
            );
        }
        
        if (!empty($settings['telegram'])) {
            $contact_info['telegram'] = array(
                'label' => esc_html__('Telegram:', 'realestate-booking-suite'),
                'value' => esc_html($settings['telegram']),
                'link' => 'https://t.me/' . esc_attr(ltrim($settings['telegram'], '@'))
            );
        }
        
        if (!empty($settings['website'])) {
            $contact_info['website'] = array(
                'label' => esc_html__('Website:', 'realestate-booking-suite'),
                'value' => esc_html($settings['website']),
                'link' => esc_url($settings['website'])
            );
        }
        
        return $contact_info;
    }

    /**
     * Get social media links
     */
    public function get_social_links() {
        $settings = $this->get_contact_settings();
        
        $social_links = array();
        
        if (!empty($settings['facebook'])) {
            $social_links['facebook'] = array(
                'name' => esc_html__('Facebook', 'realestate-booking-suite'),
                'url' => esc_url($settings['facebook']),
                'icon' => 'dashicons-facebook'
            );
        }
        
        if (!empty($settings['twitter'])) {
            $social_links['twitter'] = array(
                'name' => esc_html__('Twitter', 'realestate-booking-suite'),
                'url' => esc_url($settings['twitter']),
                'icon' => 'dashicons-twitter'
            );
        }
        
        if (!empty($settings['instagram'])) {
            $social_links['instagram'] = array(
                'name' => esc_html__('Instagram', 'realestate-booking-suite'),
                'url' => esc_url($settings['instagram']),
                'icon' => 'dashicons-instagram'
            );
        }
        
        if (!empty($settings['linkedin'])) {
            $social_links['linkedin'] = array(
                'name' => esc_html__('LinkedIn', 'realestate-booking-suite'),
                'url' => esc_url($settings['linkedin']),
                'icon' => 'dashicons-linkedin'
            );
        }
        
        if (!empty($settings['youtube'])) {
            $social_links['youtube'] = array(
                'name' => esc_html__('YouTube', 'realestate-booking-suite'),
                'url' => esc_url($settings['youtube']),
                'icon' => 'dashicons-video-alt3'
            );
        }
        
        return $social_links;
    }
}

// Initialize Contact Settings
new RESBS_Contact_Settings();
