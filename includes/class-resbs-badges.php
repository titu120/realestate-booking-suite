<?php
/**
 * Badge Manager Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Badge_Manager {

    /**
     * Badge types
     */
    private $badge_types = array(
        'featured' => array(
            'label' => 'Featured',
            'default_text' => 'Featured',
            'default_color' => '#ff6b35',
            'default_bg_color' => '#ff6b35',
            'default_text_color' => '#ffffff',
            'meta_key' => '_property_featured',
            'meta_value' => 'yes'
        ),
        'new' => array(
            'label' => 'New',
            'default_text' => 'New',
            'default_color' => '#28a745',
            'default_bg_color' => '#28a745',
            'default_text_color' => '#ffffff',
            'meta_key' => '_property_new',
            'meta_value' => 'yes'
        ),
        'sold' => array(
            'label' => 'Sold',
            'default_text' => 'Sold',
            'default_color' => '#dc3545',
            'default_bg_color' => '#dc3545',
            'default_text_color' => '#ffffff',
            'meta_key' => '_property_sold',
            'meta_value' => 'yes'
        )
    );

    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Hook into property display
        add_action('resbs_property_badges', array($this, 'display_property_badges'), 10, 2);
        add_filter('resbs_property_card_badges', array($this, 'get_property_badges'), 10, 2);
    }

    /**
     * Initialize
     */
    public function init() {
        // Add meta boxes for property badges
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_property_badges'));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'edit.php?post_type=property',
            esc_html__('Badge Settings', 'realestate-booking-suite'),
            esc_html__('Badge Settings', 'realestate-booking-suite'),
            'manage_options',
            'resbs-badge-settings',
            array($this, 'admin_page')
        );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        foreach ($this->badge_types as $type => $config) {
            register_setting('resbs_badge_settings', 'resbs_badge_' . $type . '_enabled');
            register_setting('resbs_badge_settings', 'resbs_badge_' . $type . '_text');
            register_setting('resbs_badge_settings', 'resbs_badge_' . $type . '_color');
            register_setting('resbs_badge_settings', 'resbs_badge_' . $type . '_bg_color');
            register_setting('resbs_badge_settings', 'resbs_badge_' . $type . '_text_color');
            register_setting('resbs_badge_settings', 'resbs_badge_' . $type . '_position');
            register_setting('resbs_badge_settings', 'resbs_badge_' . $type . '_size');
            register_setting('resbs_badge_settings', 'resbs_badge_' . $type . '_border_radius');
        }
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_assets() {
        wp_enqueue_style(
            'resbs-badges',
            RESBS_URL . 'assets/css/badges.css',
            array(),
            '1.0.0'
        );
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        if ($hook === 'property_page_resbs-badge-settings') {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_script(
                'resbs-badge-admin',
                RESBS_URL . 'assets/js/badge-admin.js',
                array('jquery', 'wp-color-picker'),
                '1.0.0',
                true
            );
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'resbs-property-badges',
            esc_html__('Property Badges', 'realestate-booking-suite'),
            array($this, 'property_badges_meta_box'),
            'property',
            'side',
            'default'
        );
    }

    /**
     * Property badges meta box
     */
    public function property_badges_meta_box($post) {
        wp_nonce_field('resbs_property_badges_nonce', 'resbs_property_badges_nonce');
        
        echo '<div class="resbs-badge-meta-box">';
        
        foreach ($this->badge_types as $type => $config) {
            $enabled = get_option('resbs_badge_' . $type . '_enabled', true);
            
            if (!$enabled) {
                continue;
            }
            
            $meta_key = $config['meta_key'];
            $current_value = get_post_meta($post->ID, $meta_key, true);
            $checked = ($current_value === $config['meta_value']) ? 'checked' : '';
            
            echo '<p>';
            echo '<label>';
            echo '<input type="checkbox" name="' . esc_attr($meta_key) . '" value="' . esc_attr($config['meta_value']) . '" ' . $checked . '>';
            echo ' ' . esc_html($this->get_badge_text($type)) . '</label>';
            echo '</p>';
        }
        
        echo '</div>';
    }

    /**
     * Save property badges
     */
    public function save_property_badges($post_id) {
        // Check nonce
        if (!isset($_POST['resbs_property_badges_nonce']) || 
            !wp_verify_nonce($_POST['resbs_property_badges_nonce'], 'resbs_property_badges_nonce')) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check post type
        if (get_post_type($post_id) !== 'property') {
            return;
        }

        // Save badge meta
        foreach ($this->badge_types as $type => $config) {
            $meta_key = $config['meta_key'];
            $meta_value = $config['meta_value'];
            
            if (isset($_POST[$meta_key]) && $_POST[$meta_key] === $meta_value) {
                update_post_meta($post_id, $meta_key, $meta_value);
            } else {
                delete_post_meta($post_id, $meta_key);
            }
        }
    }

    /**
     * Admin page
     */
    public function admin_page() {
        if (isset($_POST['submit'])) {
            $this->save_badge_settings();
        }
        
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Badge Settings', 'realestate-booking-suite'); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('resbs_badge_settings_nonce', 'resbs_badge_settings_nonce'); ?>
                
                <div class="resbs-badge-settings">
                    <?php foreach ($this->badge_types as $type => $config): ?>
                        <div class="resbs-badge-type-settings">
                            <h2><?php echo esc_html($config['label']); ?> <?php esc_html_e('Badge', 'realestate-booking-suite'); ?></h2>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="resbs_badge_<?php echo esc_attr($type); ?>_enabled">
                                            <?php esc_html_e('Enable Badge', 'realestate-booking-suite'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="checkbox" 
                                               id="resbs_badge_<?php echo esc_attr($type); ?>_enabled"
                                               name="resbs_badge_<?php echo esc_attr($type); ?>_enabled"
                                               value="1" 
                                               <?php checked(get_option('resbs_badge_' . $type . '_enabled', true)); ?>>
                                        <p class="description">
                                            <?php esc_html_e('Enable or disable this badge type.', 'realestate-booking-suite'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="resbs_badge_<?php echo esc_attr($type); ?>_text">
                                            <?php esc_html_e('Badge Text', 'realestate-booking-suite'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="text" 
                                               id="resbs_badge_<?php echo esc_attr($type); ?>_text"
                                               name="resbs_badge_<?php echo esc_attr($type); ?>_text"
                                               value="<?php echo esc_attr(get_option('resbs_badge_' . $type . '_text', $config['default_text'])); ?>"
                                               class="regular-text">
                                        <p class="description">
                                            <?php esc_html_e('Text to display on the badge.', 'realestate-booking-suite'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="resbs_badge_<?php echo esc_attr($type); ?>_bg_color">
                                            <?php esc_html_e('Background Color', 'realestate-booking-suite'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="text" 
                                               id="resbs_badge_<?php echo esc_attr($type); ?>_bg_color"
                                               name="resbs_badge_<?php echo esc_attr($type); ?>_bg_color"
                                               value="<?php echo esc_attr(get_option('resbs_badge_' . $type . '_bg_color', $config['default_bg_color'])); ?>"
                                               class="color-picker">
                                        <p class="description">
                                            <?php esc_html_e('Background color of the badge.', 'realestate-booking-suite'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="resbs_badge_<?php echo esc_attr($type); ?>_text_color">
                                            <?php esc_html_e('Text Color', 'realestate-booking-suite'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="text" 
                                               id="resbs_badge_<?php echo esc_attr($type); ?>_text_color"
                                               name="resbs_badge_<?php echo esc_attr($type); ?>_text_color"
                                               value="<?php echo esc_attr(get_option('resbs_badge_' . $type . '_text_color', $config['default_text_color'])); ?>"
                                               class="color-picker">
                                        <p class="description">
                                            <?php esc_html_e('Text color of the badge.', 'realestate-booking-suite'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="resbs_badge_<?php echo esc_attr($type); ?>_position">
                                            <?php esc_html_e('Position', 'realestate-booking-suite'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select id="resbs_badge_<?php echo esc_attr($type); ?>_position"
                                                name="resbs_badge_<?php echo esc_attr($type); ?>_position">
                                            <option value="top-left" <?php selected(get_option('resbs_badge_' . $type . '_position', 'top-left'), 'top-left'); ?>>
                                                <?php esc_html_e('Top Left', 'realestate-booking-suite'); ?>
                                            </option>
                                            <option value="top-right" <?php selected(get_option('resbs_badge_' . $type . '_position', 'top-left'), 'top-right'); ?>>
                                                <?php esc_html_e('Top Right', 'realestate-booking-suite'); ?>
                                            </option>
                                            <option value="bottom-left" <?php selected(get_option('resbs_badge_' . $type . '_position', 'top-left'), 'bottom-left'); ?>>
                                                <?php esc_html_e('Bottom Left', 'realestate-booking-suite'); ?>
                                            </option>
                                            <option value="bottom-right" <?php selected(get_option('resbs_badge_' . $type . '_position', 'top-left'), 'bottom-right'); ?>>
                                                <?php esc_html_e('Bottom Right', 'realestate-booking-suite'); ?>
                                            </option>
                                        </select>
                                        <p class="description">
                                            <?php esc_html_e('Position of the badge on the property card.', 'realestate-booking-suite'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="resbs_badge_<?php echo esc_attr($type); ?>_size">
                                            <?php esc_html_e('Size', 'realestate-booking-suite'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <select id="resbs_badge_<?php echo esc_attr($type); ?>_size"
                                                name="resbs_badge_<?php echo esc_attr($type); ?>_size">
                                            <option value="small" <?php selected(get_option('resbs_badge_' . $type . '_size', 'medium'), 'small'); ?>>
                                                <?php esc_html_e('Small', 'realestate-booking-suite'); ?>
                                            </option>
                                            <option value="medium" <?php selected(get_option('resbs_badge_' . $type . '_size', 'medium'), 'medium'); ?>>
                                                <?php esc_html_e('Medium', 'realestate-booking-suite'); ?>
                                            </option>
                                            <option value="large" <?php selected(get_option('resbs_badge_' . $type . '_size', 'medium'), 'large'); ?>>
                                                <?php esc_html_e('Large', 'realestate-booking-suite'); ?>
                                            </option>
                                        </select>
                                        <p class="description">
                                            <?php esc_html_e('Size of the badge.', 'realestate-booking-suite'); ?>
                                        </p>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="resbs_badge_<?php echo esc_attr($type); ?>_border_radius">
                                            <?php esc_html_e('Border Radius', 'realestate-booking-suite'); ?>
                                        </label>
                                    </th>
                                    <td>
                                        <input type="number" 
                                               id="resbs_badge_<?php echo esc_attr($type); ?>_border_radius"
                                               name="resbs_badge_<?php echo esc_attr($type); ?>_border_radius"
                                               value="<?php echo esc_attr(get_option('resbs_badge_' . $type . '_border_radius', '4')); ?>"
                                               min="0" max="50" step="1">
                                        <p class="description">
                                            <?php esc_html_e('Border radius in pixels (0 for square, higher for more rounded).', 'realestate-booking-suite'); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php submit_button(esc_html__('Save Badge Settings', 'realestate-booking-suite')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * Save badge settings
     */
    private function save_badge_settings() {
        // Check nonce using security helper
        RESBS_Security::verify_nonce($_POST['resbs_badge_settings_nonce'], 'resbs_badge_settings_nonce');

        // Check permissions using security helper
        RESBS_Security::check_capability('manage_options');

        // Save settings
        foreach ($this->badge_types as $type => $config) {
            $fields = array('enabled', 'text', 'bg_color', 'text_color', 'position', 'size', 'border_radius');
            
            foreach ($fields as $field) {
                $option_name = 'resbs_badge_' . $type . '_' . $field;
                $value = isset($_POST[$option_name]) ? $_POST[$option_name] : '';
                
                // Sanitize based on field type using security helper
                switch ($field) {
                    case 'enabled':
                        $value = RESBS_Security::sanitize_bool($value) ? 1 : 0;
                        break;
                    case 'text':
                        $value = RESBS_Security::sanitize_text($value);
                        break;
                    case 'bg_color':
                    case 'text_color':
                        $value = sanitize_hex_color($value);
                        break;
                    case 'position':
                        $allowed_positions = array('top-left', 'top-right', 'bottom-left', 'bottom-right');
                        $value = in_array($value, $allowed_positions) ? $value : 'top-left';
                        break;
                    case 'size':
                        $allowed_sizes = array('small', 'medium', 'large');
                        $value = in_array($value, $allowed_sizes) ? $value : 'medium';
                        break;
                    case 'border_radius':
                        $value = RESBS_Security::sanitize_int($value, 4);
                        $value = max(0, min(50, $value));
                        break;
                }
                
                update_option($option_name, $value);
            }
        }
        
        // Generate dynamic CSS
        $this->generate_dynamic_css();
        
        add_settings_error('resbs_badge_settings', 'settings_updated', 
                          esc_html__('Badge settings saved successfully.', 'realestate-booking-suite'), 'updated');
    }

    /**
     * Get property badges
     */
    public function get_property_badges($property_id, $context = 'card') {
        $badges = array();
        
        foreach ($this->badge_types as $type => $config) {
            $enabled = get_option('resbs_badge_' . $type . '_enabled', true);
            
            if (!$enabled) {
                continue;
            }
            
            $meta_key = $config['meta_key'];
            $current_value = get_post_meta($property_id, $meta_key, true);
            
            if ($current_value === $config['meta_value']) {
                $badges[] = array(
                    'type' => $type,
                    'text' => $this->get_badge_text($type),
                    'class' => 'resbs-badge resbs-badge-' . $type,
                    'style' => $this->get_badge_style($type),
                    'position' => get_option('resbs_badge_' . $type . '_position', 'top-left'),
                    'size' => get_option('resbs_badge_' . $type . '_size', 'medium')
                );
            }
        }
        
        return $badges;
    }

    /**
     * Display property badges
     */
    public function display_property_badges($property_id, $context = 'card') {
        $badges = $this->get_property_badges($property_id, $context);
        
        if (empty($badges)) {
            return;
        }
        
        echo '<div class="resbs-property-badges resbs-badges-' . esc_attr($context) . '">';
        
        foreach ($badges as $badge) {
            $this->render_badge($badge);
        }
        
        echo '</div>';
    }

    /**
     * Render individual badge
     */
    private function render_badge($badge) {
        $classes = array(
            'resbs-badge',
            'resbs-badge-' . $badge['type'],
            'resbs-badge-' . $badge['size'],
            'resbs-badge-' . $badge['position']
        );
        
        $class_string = implode(' ', $classes);
        
        echo '<span class="' . esc_attr($class_string) . '" style="' . esc_attr($badge['style']) . '">';
        echo esc_html($badge['text']);
        echo '</span>';
    }

    /**
     * Get badge text
     */
    private function get_badge_text($type) {
        $text = get_option('resbs_badge_' . $type . '_text', $this->badge_types[$type]['default_text']);
        return apply_filters('resbs_badge_text_' . $type, $text);
    }

    /**
     * Get badge style
     */
    private function get_badge_style($type) {
        $bg_color = get_option('resbs_badge_' . $type . '_bg_color', $this->badge_types[$type]['default_bg_color']);
        $text_color = get_option('resbs_badge_' . $type . '_text_color', $this->badge_types[$type]['default_text_color']);
        $border_radius = get_option('resbs_badge_' . $type . '_border_radius', '4');
        
        $style = 'background-color: ' . esc_attr($bg_color) . '; ';
        $style .= 'color: ' . esc_attr($text_color) . '; ';
        $style .= 'border-radius: ' . esc_attr($border_radius) . 'px;';
        
        return $style;
    }

    /**
     * Generate dynamic CSS
     */
    private function generate_dynamic_css() {
        $css = '';
        
        foreach ($this->badge_types as $type => $config) {
            $enabled = get_option('resbs_badge_' . $type . '_enabled', true);
            
            if (!$enabled) {
                continue;
            }
            
            $bg_color = get_option('resbs_badge_' . $type . '_bg_color', $config['default_bg_color']);
            $text_color = get_option('resbs_badge_' . $type . '_text_color', $config['default_text_color']);
            $border_radius = get_option('resbs_badge_' . $type . '_border_radius', '4');
            $size = get_option('resbs_badge_' . $type . '_size', 'medium');
            
            // Size styles
            $size_styles = array(
                'small' => 'font-size: 10px; padding: 2px 6px;',
                'medium' => 'font-size: 12px; padding: 4px 8px;',
                'large' => 'font-size: 14px; padding: 6px 12px;'
            );
            
            $css .= '.resbs-badge-' . $type . ' { ';
            $css .= 'background-color: ' . esc_attr($bg_color) . '; ';
            $css .= 'color: ' . esc_attr($text_color) . '; ';
            $css .= 'border-radius: ' . esc_attr($border_radius) . 'px; ';
            $css .= $size_styles[$size];
            $css .= ' }' . "\n";
        }
        
        // Save CSS to file
        $upload_dir = wp_upload_dir();
        $css_file = $upload_dir['basedir'] . '/resbs-badges.css';
        
        if (file_put_contents($css_file, $css)) {
            update_option('resbs_badges_css_version', time());
        }
    }

    /**
     * Get dynamic CSS URL
     */
    public function get_dynamic_css_url() {
        $upload_dir = wp_upload_dir();
        $css_file = $upload_dir['basedir'] . '/resbs-badges.css';
        
        if (file_exists($css_file)) {
            return $upload_dir['baseurl'] . '/resbs-badges.css?ver=' . get_option('resbs_badges_css_version', '1.0.0');
        }
        
        return false;
    }
}

// Initialize badge manager
new RESBS_Badge_Manager();
