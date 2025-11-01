<?php
/**
 * Elementor Property Request Form Widget
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Ensure Elementor Widget_Base class is available
if (!class_exists('\Elementor\Widget_Base')) {
    return;
}

// Prevent class redeclaration
if (class_exists('RESBS_Request_Form_Widget')) {
    return;
}

class RESBS_Request_Form_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'resbs-request-form';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return esc_html__('Property Request Form', 'realestate-booking-suite');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    /**
     * Get widget categories
     */
    public function get_categories() {
        return array('resbs-widgets');
    }

    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return array('property', 'form', 'request', 'contact', 'inquiry');
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => esc_html__('Content', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'title',
            array(
                'label' => esc_html__('Title', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__('Ask an Agent About This Home', 'realestate-booking-suite'),
            )
        );

        $this->add_control(
            'message',
            array(
                'label' => esc_html__('Default Message', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__("Hello, I'd like more information about this home. Thank you!", 'realestate-booking-suite'),
            )
        );

        $this->add_control(
            'disable_name',
            array(
                'label' => esc_html__('Disable Name', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->add_control(
            'disable_phone',
            array(
                'label' => esc_html__('Disable Phone', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->add_control(
            'disable_email',
            array(
                'label' => esc_html__('Disable Email', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
            )
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => esc_html__('Style', 'realestate-booking-suite'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'form_background',
            array(
                'label' => esc_html__('Form Background Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-request-form' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'title_typography',
                'label' => esc_html__('Title Typography', 'realestate-booking-suite'),
                'selector' => '{{WRAPPER}} .resbs-request-form-title',
            )
        );

        $this->add_control(
            'title_color',
            array(
                'label' => esc_html__('Title Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-request-form-title' => 'color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_color',
            array(
                'label' => esc_html__('Button Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-request-submit-btn' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $title = sanitize_text_field($settings['title']);
        $default_message = sanitize_textarea_field($settings['message']);
        $disable_name = $settings['disable_name'] === 'yes';
        $disable_phone = $settings['disable_phone'] === 'yes';
        $disable_email = $settings['disable_email'] === 'yes';
        
        $widget_id = 'resbs-request-form-' . $this->get_id();
        $current_user = wp_get_current_user();
        $current_property_id = get_the_ID();
        
        ?>
        <div class="resbs-request-form-widget" id="<?php echo esc_attr($widget_id); ?>">
            <form class="resbs-request-form" method="post" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>">
                <?php wp_nonce_field('resbs_request_form', 'resbs_request_nonce'); ?>
                <input type="hidden" name="action" value="resbs_submit_request_form">
                <?php if ($current_property_id): ?>
                    <input type="hidden" name="property_id" value="<?php echo esc_attr($current_property_id); ?>">
                <?php endif; ?>
                
                <?php if (!empty($title)): ?>
                    <h3 class="resbs-request-form-title"><?php echo esc_html($title); ?></h3>
                <?php endif; ?>
                
                <div class="resbs-request-form-fields">
                    <?php if (!$disable_name): ?>
                        <div class="resbs-form-field">
                            <label for="request_name_<?php echo esc_attr($widget_id); ?>">
                                <?php esc_html_e('Name', 'realestate-booking-suite'); ?> *
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="request_name_<?php echo esc_attr($widget_id); ?>" 
                                   value="<?php echo esc_attr($current_user->display_name); ?>"
                                   required>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$disable_email): ?>
                        <div class="resbs-form-field">
                            <label for="request_email_<?php echo esc_attr($widget_id); ?>">
                                <?php esc_html_e('Email', 'realestate-booking-suite'); ?> *
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="request_email_<?php echo esc_attr($widget_id); ?>" 
                                   value="<?php echo esc_attr($current_user->user_email); ?>"
                                   required>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$disable_phone): ?>
                        <div class="resbs-form-field">
                            <label for="request_phone_<?php echo esc_attr($widget_id); ?>">
                                <?php esc_html_e('Phone', 'realestate-booking-suite'); ?>
                            </label>
                            <select name="phone_code" class="resbs-phone-code">
                                <option value="other"><?php esc_html_e('Other', 'realestate-booking-suite'); ?></option>
                                <option value="us">+1 (US)</option>
                                <option value="uk">+44 (UK)</option>
                                <option value="ca">+1 (CA)</option>
                            </select>
                            <input type="tel" 
                                   name="phone" 
                                   id="request_phone_<?php echo esc_attr($widget_id); ?>" 
                                   class="resbs-phone-input">
                        </div>
                    <?php endif; ?>
                    
                    <div class="resbs-form-field">
                        <label for="request_message_<?php echo esc_attr($widget_id); ?>">
                            <?php esc_html_e('Message', 'realestate-booking-suite'); ?> *
                        </label>
                        <textarea name="message" 
                                  id="request_message_<?php echo esc_attr($widget_id); ?>" 
                                  rows="4"
                                  required><?php echo esc_textarea($default_message); ?></textarea>
                    </div>
                </div>
                
                <div class="resbs-request-form-footer">
                    <p class="resbs-request-disclaimer">
                        <?php esc_html_e('By clicking the «REQUEST INFO» button you agree to the Terms of Use and Privacy Policy', 'realestate-booking-suite'); ?>
                    </p>
                    
                    <button type="submit" class="resbs-request-submit-btn">
                        <?php esc_html_e('REQUEST INFO', 'realestate-booking-suite'); ?>
                    </button>
                </div>
                
                <div class="resbs-request-form-message" style="display: none;"></div>
            </form>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <div class="resbs-request-form-widget">
            <form class="resbs-request-form">
                <h3 class="resbs-request-form-title">{{{ settings.title }}}</h3>
                <div class="resbs-request-form-fields">
                    <div class="resbs-form-field">
                        <label><?php esc_html_e('Name', 'realestate-booking-suite'); ?> *</label>
                        <input type="text" value="admin">
                    </div>
                    <div class="resbs-form-field">
                        <label><?php esc_html_e('Email', 'realestate-booking-suite'); ?> *</label>
                        <input type="email" value="admin@example.com">
                    </div>
                    <div class="resbs-form-field">
                        <label><?php esc_html_e('Phone', 'realestate-booking-suite'); ?></label>
                        <select><option><?php esc_html_e('Other', 'realestate-booking-suite'); ?></option></select>
                        <input type="tel">
                    </div>
                    <div class="resbs-form-field">
                        <label><?php esc_html_e('Message', 'realestate-booking-suite'); ?> *</label>
                        <textarea rows="4">{{{ settings.message }}}</textarea>
                    </div>
                </div>
                <div class="resbs-request-form-footer">
                    <p class="resbs-request-disclaimer"><?php esc_html_e('By clicking the «REQUEST INFO» button you agree to the Terms of Use and Privacy Policy', 'realestate-booking-suite'); ?></p>
                    <button type="button" class="resbs-request-submit-btn"><?php esc_html_e('REQUEST INFO', 'realestate-booking-suite'); ?></button>
                </div>
            </form>
        </div>
        <?php
    }
}

