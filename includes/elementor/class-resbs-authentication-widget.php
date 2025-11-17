<?php
/**
 * Elementor Property Authentication Widget
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
if (class_exists('RESBS_Authentication_Widget')) {
    return;
}

class RESBS_Authentication_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'resbs-authentication';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return esc_html__('Property Authentication', 'realestate-booking-suite');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-lock-user';
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
        return array('property', 'authentication', 'login', 'logout', 'user');
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
            'form_type',
            array(
                'label' => esc_html__('Form', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'login_buttons',
                'options' => array(
                    'login_buttons' => esc_html__('Login Buttons', 'realestate-booking-suite'),
                    'login_form' => esc_html__('Login Form', 'realestate-booking-suite'),
                    'register_form' => esc_html__('Register Form', 'realestate-booking-suite'),
                ),
            )
        );

        $this->add_control(
            'enable_facebook_auth',
            array(
                'label' => esc_html__('Enable Facebook Auth', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => array(
                    'form_type' => 'login_buttons',
                ),
            )
        );

        $this->add_control(
            'enable_google_auth',
            array(
                'label' => esc_html__('Enable Google Auth', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'no',
                'condition' => array(
                    'form_type' => 'login_buttons',
                ),
            )
        );

        $this->add_control(
            'enable_login_form',
            array(
                'label' => esc_html__('Enable Login Form', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Yes', 'realestate-booking-suite'),
                'label_off' => esc_html__('No', 'realestate-booking-suite'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => array(
                    'form_type' => 'login_buttons',
                ),
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
                    '{{WRAPPER}} .resbs-auth-form' => 'background-color: {{VALUE}};',
                ),
            )
        );

        $this->add_control(
            'button_color',
            array(
                'label' => esc_html__('Button Color', 'realestate-booking-suite'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .resbs-auth-btn' => 'background-color: {{VALUE}};',
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
        
        $form_type = isset($settings['form_type']) ? sanitize_text_field($settings['form_type']) : 'login_buttons';
        // Validate form_type against allowed values
        $allowed_form_types = array('login_buttons', 'login_form', 'register_form');
        if (!in_array($form_type, $allowed_form_types, true)) {
            $form_type = 'login_buttons';
        }
        $enable_facebook_auth = isset($settings['enable_facebook_auth']) && $settings['enable_facebook_auth'] === 'yes';
        $enable_google_auth = isset($settings['enable_google_auth']) && $settings['enable_google_auth'] === 'yes';
        $enable_login_form = isset($settings['enable_login_form']) && $settings['enable_login_form'] === 'yes';
        
        $widget_id = 'resbs-authentication-' . sanitize_html_class($this->get_id());
        $is_logged_in = is_user_logged_in();
        $current_user = wp_get_current_user();
        $login_url = wp_login_url(get_permalink());
        $logout_url = wp_logout_url(get_permalink());
        $register_url = wp_registration_url();
        
        ?>
        <div class="resbs-authentication-widget" id="<?php echo esc_attr($widget_id); ?>">
            <?php if ($is_logged_in): ?>
                <div class="resbs-auth-logged-in">
                    <div class="resbs-auth-message">
                        <?php esc_html_e("You're already logged in.", 'realestate-booking-suite'); ?>
                    </div>
                    <a href="<?php echo esc_url($logout_url); ?>" class="resbs-auth-btn resbs-logout-btn">
                        <?php esc_html_e('LOG OUT', 'realestate-booking-suite'); ?>
                    </a>
                </div>
            <?php else: ?>
                <?php if ($form_type === 'login_buttons'): ?>
                    <div class="resbs-auth-buttons">
                        <?php if ($enable_facebook_auth): ?>
                            <button type="button" class="resbs-auth-btn resbs-facebook-btn">
                                <span class="dashicons dashicons-facebook-alt"></span>
                                <?php esc_html_e('Login with Facebook', 'realestate-booking-suite'); ?>
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($enable_google_auth): ?>
                            <button type="button" class="resbs-auth-btn resbs-google-btn">
                                <span class="dashicons dashicons-google"></span>
                                <?php esc_html_e('Login with Google', 'realestate-booking-suite'); ?>
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($enable_login_form): ?>
                            <div class="resbs-auth-divider">
                                <span><?php esc_html_e('OR', 'realestate-booking-suite'); ?></span>
                            </div>
                            
                            <form class="resbs-auth-form resbs-login-form" method="post" action="<?php echo esc_url($login_url); ?>">
                                <div class="resbs-form-field">
                                    <label for="login_username_<?php echo esc_attr($widget_id); ?>">
                                        <?php esc_html_e('Username or Email', 'realestate-booking-suite'); ?>
                                    </label>
                                    <input type="text" 
                                           name="log" 
                                           id="login_username_<?php echo esc_attr($widget_id); ?>" 
                                           required>
                                </div>
                                
                                <div class="resbs-form-field">
                                    <label for="login_password_<?php echo esc_attr($widget_id); ?>">
                                        <?php esc_html_e('Password', 'realestate-booking-suite'); ?>
                                    </label>
                                    <input type="password" 
                                           name="pwd" 
                                           id="login_password_<?php echo esc_attr($widget_id); ?>" 
                                           required>
                                </div>
                                
                                <div class="resbs-form-field resbs-remember-field">
                                    <label>
                                        <input type="checkbox" name="rememberme" value="forever">
                                        <?php esc_html_e('Remember Me', 'realestate-booking-suite'); ?>
                                    </label>
                                </div>
                                
                                <button type="submit" class="resbs-auth-btn resbs-login-submit-btn">
                                    <?php esc_html_e('LOG IN', 'realestate-booking-suite'); ?>
                                </button>
                                
                                <div class="resbs-auth-links">
                                    <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">
                                        <?php esc_html_e('Forgot Password?', 'realestate-booking-suite'); ?>
                                    </a>
                                    <span>|</span>
                                    <a href="<?php echo esc_url($register_url); ?>">
                                        <?php esc_html_e('Register', 'realestate-booking-suite'); ?>
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php elseif ($form_type === 'login_form'): ?>
                    <form class="resbs-auth-form resbs-login-form" method="post" action="<?php echo esc_url($login_url); ?>">
                        <div class="resbs-form-field">
                            <label for="login_username_<?php echo esc_attr($widget_id); ?>">
                                <?php esc_html_e('Username or Email', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="text" 
                                   name="log" 
                                   id="login_username_<?php echo esc_attr($widget_id); ?>" 
                                   required>
                        </div>
                        
                        <div class="resbs-form-field">
                            <label for="login_password_<?php echo esc_attr($widget_id); ?>">
                                <?php esc_html_e('Password', 'realestate-booking-suite'); ?>
                            </label>
                            <input type="password" 
                                   name="pwd" 
                                   id="login_password_<?php echo esc_attr($widget_id); ?>" 
                                   required>
                        </div>
                        
                        <div class="resbs-form-field resbs-remember-field">
                            <label>
                                <input type="checkbox" name="rememberme" value="forever">
                                <?php esc_html_e('Remember Me', 'realestate-booking-suite'); ?>
                            </label>
                        </div>
                        
                        <button type="submit" class="resbs-auth-btn resbs-login-submit-btn">
                            <?php esc_html_e('LOG IN', 'realestate-booking-suite'); ?>
                        </button>
                        
                        <div class="resbs-auth-links">
                            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">
                                <?php esc_html_e('Forgot Password?', 'realestate-booking-suite'); ?>
                            </a>
                            <span>|</span>
                            <a href="<?php echo esc_url($register_url); ?>">
                                <?php esc_html_e('Register', 'realestate-booking-suite'); ?>
                            </a>
                        </div>
                    </form>
                <?php elseif ($form_type === 'register_form'): ?>
                    <form class="resbs-auth-form resbs-register-form" method="post" action="<?php echo esc_url($register_url); ?>">
                        <div class="resbs-form-field">
                            <label for="register_username_<?php echo esc_attr($widget_id); ?>">
                                <?php esc_html_e('Username', 'realestate-booking-suite'); ?> *
                            </label>
                            <input type="text" 
                                   name="user_login" 
                                   id="register_username_<?php echo esc_attr($widget_id); ?>" 
                                   required>
                        </div>
                        
                        <div class="resbs-form-field">
                            <label for="register_email_<?php echo esc_attr($widget_id); ?>">
                                <?php esc_html_e('Email', 'realestate-booking-suite'); ?> *
                            </label>
                            <input type="email" 
                                   name="user_email" 
                                   id="register_email_<?php echo esc_attr($widget_id); ?>" 
                                   required>
                        </div>
                        
                        <div class="resbs-form-field">
                            <label for="register_password_<?php echo esc_attr($widget_id); ?>">
                                <?php esc_html_e('Password', 'realestate-booking-suite'); ?> *
                            </label>
                            <input type="password" 
                                   name="user_pass" 
                                   id="register_password_<?php echo esc_attr($widget_id); ?>" 
                                   required>
                        </div>
                        
                        <button type="submit" class="resbs-auth-btn resbs-register-submit-btn">
                            <?php esc_html_e('REGISTER', 'realestate-booking-suite'); ?>
                        </button>
                        
                        <div class="resbs-auth-links">
                            <a href="<?php echo esc_url($login_url); ?>">
                                <?php esc_html_e('Already have an account? Login', 'realestate-booking-suite'); ?>
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            <?php endif; ?>
            
            <div class="resbs-auth-footer">
                <p><?php esc_html_e('Powered by RealEstate Booking Suite', 'realestate-booking-suite'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <div class="resbs-authentication-widget">
            <div class="resbs-auth-logged-in">
                <div class="resbs-auth-message"><?php esc_html_e("You're already logged in.", 'realestate-booking-suite'); ?></div>
                <button type="button" class="resbs-auth-btn resbs-logout-btn"><?php esc_html_e('LOG OUT', 'realestate-booking-suite'); ?></button>
            </div>
            <div class="resbs-auth-footer">
                <p><?php esc_html_e('Powered by RealEstate Booking Suite', 'realestate-booking-suite'); ?></p>
            </div>
        </div>
        <?php
    }
}

