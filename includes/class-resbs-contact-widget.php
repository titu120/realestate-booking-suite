<?php
/**
 * Contact Widget Class
 * 
 * SECURITY NOTES:
 * - Direct access prevention: ABSPATH check at top of file
 * - Nonce handling: WordPress core automatically handles nonce creation and verification for widget forms
 *   - Widget form() method: WordPress core adds nonce automatically when rendering widget forms
 *   - Widget update() method: WordPress core verifies nonce via check_ajax_referer('update-widget', 'nonce')
 * - User permissions: WordPress core automatically checks current_user_can('edit_theme_options') for widget updates
 *   - Only users with 'edit_theme_options' capability can save/update widgets
 *   - No additional capability checks needed in this class
 * - Data sanitization: All input sanitized in update() method using sanitize_text_field()
 * - Output escaping: All output uses esc_* functions (esc_attr, esc_html, esc_url, wp_kses_post)
 * 
 * WIDGET SECURITY (Handled by WordPress Core):
 * - Nonce verification: check_ajax_referer('update-widget', 'nonce') in wp_ajax_update_widget()
 * - Capability check: current_user_can('edit_theme_options') in wp_ajax_update_widget()
 * - User authentication: is_user_logged_in() check in wp_ajax_update_widget()
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Contact_Widget extends WP_Widget {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'resbs_contact_widget',
            esc_html__('Contact Information', 'realestate-booking-suite'),
            array(
                'description' => esc_html__('Display contact information and social media links.', 'realestate-booking-suite'),
                'classname' => 'resbs-contact-widget'
            )
        );
    }

    /**
     * Widget form
     */
    public function form($instance) {
        $defaults = array(
            'title' => esc_html__('Contact Us', 'realestate-booking-suite'),
            'show_phone' => true,
            'show_email' => true,
            'show_address' => true,
            'show_website' => true,
            'show_whatsapp' => false,
            'show_telegram' => false,
            'show_business_hours' => true,
            'show_social_links' => true,
            'widget_style' => 'default'
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        
        $title = sanitize_text_field($instance['title']);
        $show_phone = (bool) $instance['show_phone'];
        $show_email = (bool) $instance['show_email'];
        $show_address = (bool) $instance['show_address'];
        $show_website = (bool) $instance['show_website'];
        $show_whatsapp = (bool) $instance['show_whatsapp'];
        $show_telegram = (bool) $instance['show_telegram'];
        $show_business_hours = (bool) $instance['show_business_hours'];
        $show_social_links = (bool) $instance['show_social_links'];
        $widget_style = sanitize_text_field($instance['widget_style']);
        ?>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
                <?php esc_html_e('Title:', 'realestate-booking-suite'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" value="<?php echo esc_attr($title); ?>" />
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('widget_style')); ?>">
                <?php esc_html_e('Style:', 'realestate-booking-suite'); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('widget_style')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('widget_style')); ?>">
                <option value="default" <?php selected($widget_style, 'default'); ?>><?php esc_html_e('Default', 'realestate-booking-suite'); ?></option>
                <option value="minimal" <?php selected($widget_style, 'minimal'); ?>><?php esc_html_e('Minimal', 'realestate-booking-suite'); ?></option>
                <option value="detailed" <?php selected($widget_style, 'detailed'); ?>><?php esc_html_e('Detailed', 'realestate-booking-suite'); ?></option>
            </select>
        </p>
        
        <h4><?php esc_html_e('Display Options:', 'realestate-booking-suite'); ?></h4>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_phone); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_phone')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_phone')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_phone')); ?>">
                <?php esc_html_e('Show Phone', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_email); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_email')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_email')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_email')); ?>">
                <?php esc_html_e('Show Email', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_address); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_address')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_address')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_address')); ?>">
                <?php esc_html_e('Show Address', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_website); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_website')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_website')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_website')); ?>">
                <?php esc_html_e('Show Website', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_whatsapp); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_whatsapp')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_whatsapp')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_whatsapp')); ?>">
                <?php esc_html_e('Show WhatsApp', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_telegram); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_telegram')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_telegram')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_telegram')); ?>">
                <?php esc_html_e('Show Telegram', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_business_hours); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_business_hours')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_business_hours')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_business_hours')); ?>">
                <?php esc_html_e('Show Business Hours', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_social_links); ?> 
                   id="<?php echo esc_attr($this->get_field_id('show_social_links')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('show_social_links')); ?>" />
            <label for="<?php echo esc_attr($this->get_field_id('show_social_links')); ?>">
                <?php esc_html_e('Show Social Links', 'realestate-booking-suite'); ?>
            </label>
        </p>
        
        <?php
    }

    /**
     * Update widget
     * 
     * SECURITY: WordPress core handles nonce verification and capability checks
     * (current_user_can('edit_theme_options')) before this method is called.
     * This method only needs to sanitize the input data.
     * 
     * @param array $new_instance New settings for this instance
     * @param array $old_instance Old settings for this instance
     * @return array Sanitized instance settings
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['show_phone'] = isset($new_instance['show_phone']);
        $instance['show_email'] = isset($new_instance['show_email']);
        $instance['show_address'] = isset($new_instance['show_address']);
        $instance['show_website'] = isset($new_instance['show_website']);
        $instance['show_whatsapp'] = isset($new_instance['show_whatsapp']);
        $instance['show_telegram'] = isset($new_instance['show_telegram']);
        $instance['show_business_hours'] = isset($new_instance['show_business_hours']);
        $instance['show_social_links'] = isset($new_instance['show_social_links']);
        $instance['widget_style'] = sanitize_text_field($new_instance['widget_style']);
        
        return $instance;
    }

    /**
     * Display widget
     */
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', sanitize_text_field($instance['title']));
        $show_phone = (bool) $instance['show_phone'];
        $show_email = (bool) $instance['show_email'];
        $show_address = (bool) $instance['show_address'];
        $show_website = (bool) $instance['show_website'];
        $show_whatsapp = (bool) $instance['show_whatsapp'];
        $show_telegram = (bool) $instance['show_telegram'];
        $show_business_hours = (bool) $instance['show_business_hours'];
        $show_social_links = (bool) $instance['show_social_links'];
        $widget_style = sanitize_text_field($instance['widget_style']);
        
        // Get contact settings
        $contact_settings = new RESBS_Contact_Settings();
        $contact_info = $contact_settings->get_formatted_contact_info();
        $social_links = $contact_settings->get_social_links();
        $settings = $contact_settings->get_contact_settings();
        
        echo wp_kses_post($args['before_widget']);
        
        if (!empty($title)) {
            echo wp_kses_post($args['before_title']) . esc_html($title) . wp_kses_post($args['after_title']);
        }
        
        ?>
        <div class="resbs-contact-widget-content resbs-contact-style-<?php echo esc_attr($widget_style); ?>">
            <?php if ($show_phone && !empty($contact_info['phone'])): ?>
                <div class="resbs-contact-item resbs-contact-phone">
                    <span class="resbs-contact-icon dashicons dashicons-phone"></span>
                    <div class="resbs-contact-details">
                        <span class="resbs-contact-label"><?php echo esc_html($contact_info['phone']['label']); ?></span>
                        <a href="<?php echo esc_url($contact_info['phone']['link']); ?>" class="resbs-contact-value">
                            <?php echo esc_html($contact_info['phone']['value']); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($show_email && !empty($contact_info['email'])): ?>
                <div class="resbs-contact-item resbs-contact-email">
                    <span class="resbs-contact-icon dashicons dashicons-email"></span>
                    <div class="resbs-contact-details">
                        <span class="resbs-contact-label"><?php echo esc_html($contact_info['email']['label']); ?></span>
                        <a href="<?php echo esc_url($contact_info['email']['link']); ?>" class="resbs-contact-value">
                            <?php echo esc_html($contact_info['email']['value']); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($show_address && !empty($settings['address'])): ?>
                <div class="resbs-contact-item resbs-contact-address">
                    <span class="resbs-contact-icon dashicons dashicons-location"></span>
                    <div class="resbs-contact-details">
                        <span class="resbs-contact-label"><?php esc_html_e('Address:', 'realestate-booking-suite'); ?></span>
                        <span class="resbs-contact-value"><?php echo esc_html($settings['address']); ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($show_website && !empty($contact_info['website'])): ?>
                <div class="resbs-contact-item resbs-contact-website">
                    <span class="resbs-contact-icon dashicons dashicons-admin-site"></span>
                    <div class="resbs-contact-details">
                        <span class="resbs-contact-label"><?php echo esc_html($contact_info['website']['label']); ?></span>
                        <a href="<?php echo esc_url($contact_info['website']['link']); ?>" target="_blank" rel="noopener" class="resbs-contact-value">
                            <?php echo esc_html($contact_info['website']['value']); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($show_whatsapp && !empty($contact_info['whatsapp'])): ?>
                <div class="resbs-contact-item resbs-contact-whatsapp">
                    <span class="resbs-contact-icon dashicons dashicons-whatsapp"></span>
                    <div class="resbs-contact-details">
                        <span class="resbs-contact-label"><?php echo esc_html($contact_info['whatsapp']['label']); ?></span>
                        <a href="<?php echo esc_url($contact_info['whatsapp']['link']); ?>" target="_blank" rel="noopener" class="resbs-contact-value">
                            <?php echo esc_html($contact_info['whatsapp']['value']); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($show_telegram && !empty($contact_info['telegram'])): ?>
                <div class="resbs-contact-item resbs-contact-telegram">
                    <span class="resbs-contact-icon dashicons dashicons-telegram"></span>
                    <div class="resbs-contact-details">
                        <span class="resbs-contact-label"><?php echo esc_html($contact_info['telegram']['label']); ?></span>
                        <a href="<?php echo esc_url($contact_info['telegram']['link']); ?>" target="_blank" rel="noopener" class="resbs-contact-value">
                            <?php echo esc_html($contact_info['telegram']['value']); ?>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($show_business_hours && !empty($settings['business_hours'])): ?>
                <div class="resbs-contact-item resbs-contact-hours">
                    <span class="resbs-contact-icon dashicons dashicons-clock"></span>
                    <div class="resbs-contact-details">
                        <span class="resbs-contact-label"><?php esc_html_e('Business Hours:', 'realestate-booking-suite'); ?></span>
                        <div class="resbs-contact-value resbs-business-hours">
                            <?php echo wp_kses_post(nl2br($settings['business_hours'])); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($show_social_links && !empty($social_links)): ?>
                <div class="resbs-contact-item resbs-contact-social">
                    <span class="resbs-contact-icon dashicons dashicons-share"></span>
                    <div class="resbs-contact-details">
                        <span class="resbs-contact-label"><?php esc_html_e('Follow Us:', 'realestate-booking-suite'); ?></span>
                        <div class="resbs-social-links">
                            <?php foreach ($social_links as $platform => $social): ?>
                                <a href="<?php echo esc_url($social['url']); ?>" 
                                   target="_blank" 
                                   rel="noopener" 
                                   class="resbs-social-link resbs-social-<?php echo esc_attr($platform); ?>"
                                   title="<?php echo esc_attr($social['name']); ?>">
                                    <span class="dashicons <?php echo esc_attr($social['icon']); ?>"></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
        
        echo wp_kses_post($args['after_widget']);
    }
}

// Register the widget
add_action('widgets_init', function() {
    register_widget('RESBS_Contact_Widget');
});
