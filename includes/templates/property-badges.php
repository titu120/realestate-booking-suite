<?php
/**
 * Property Badges Template
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Display property badges
 * 
 * @param int $property_id Property ID
 * @param string $context Display context (card, single, carousel, map)
 */
function resbs_display_property_badges($property_id, $context = 'card') {
    // Validate and sanitize property ID
    $property_id = absint($property_id);
    if (!$property_id) {
        return;
    }
    
    // Validate and sanitize context
    $context = sanitize_key($context);
    
    // Get badge manager instance
    $badge_manager = new RESBS_Badge_Manager();
    
    // Display badges using the action hook
    do_action('resbs_property_badges', $property_id, $context);
}

/**
 * Get property badge HTML
 * 
 * @param int $property_id Property ID
 * @param string $context Display context
 * @return string Badge HTML
 */
function resbs_get_property_badges_html($property_id, $context = 'card') {
    // Validate and sanitize property ID
    $property_id = absint($property_id);
    if (!$property_id) {
        return '';
    }
    
    // Validate and sanitize context
    $context = sanitize_key($context);
    
    ob_start();
    resbs_display_property_badges($property_id, $context);
    return ob_get_clean();
}

/**
 * Check if property has specific badge
 * 
 * @param int $property_id Property ID
 * @param string $badge_type Badge type (featured, new, sold)
 * @return bool
 */
function resbs_property_has_badge($property_id, $badge_type) {
    // Validate and sanitize property ID
    $property_id = absint($property_id);
    if (!$property_id) {
        return false;
    }
    
    // Validate and sanitize badge type
    $badge_type = sanitize_key($badge_type);
    if (empty($badge_type)) {
        return false;
    }
    
    // Validate badge type against allowed values
    $allowed_badge_types = array('featured', 'new', 'sold');
    if (!in_array($badge_type, $allowed_badge_types, true)) {
        return false;
    }
    
    $badge_manager = new RESBS_Badge_Manager();
    $badges = $badge_manager->get_property_badges($property_id);
    
    if (!is_array($badges)) {
        return false;
    }
    
    foreach ($badges as $badge) {
        if (isset($badge['type']) && $badge['type'] === $badge_type) {
            return true;
        }
    }
    
    return false;
}

/**
 * Get property badge count
 * 
 * @param int $property_id Property ID
 * @return int Number of badges
 */
function resbs_get_property_badge_count($property_id) {
    // Validate and sanitize property ID
    $property_id = absint($property_id);
    if (!$property_id) {
        return 0;
    }
    
    $badge_manager = new RESBS_Badge_Manager();
    $badges = $badge_manager->get_property_badges($property_id);
    
    if (!is_array($badges)) {
        return 0;
    }
    
    return count($badges);
}

/**
 * Badge shortcode
 * 
 * @param array $atts Shortcode attributes
 * @return string Badge HTML
 */
function resbs_badge_shortcode($atts) {
    $atts = shortcode_atts(array(
        'property_id' => get_the_ID(),
        'type' => 'all', // all, featured, new, sold
        'context' => 'shortcode',
        'show' => 'true'
    ), $atts);
    
    if ($atts['show'] !== 'true') {
        return '';
    }
    
    // Validate and sanitize property ID
    $property_id = absint($atts['property_id']);
    if (!$property_id) {
        return '';
    }
    
    // Validate and sanitize context
    $context = sanitize_key($atts['context']);
    
    // Validate badge type
    $badge_type = sanitize_key($atts['type']);
    $allowed_badge_types = array('all', 'featured', 'new', 'sold');
    if (!in_array($badge_type, $allowed_badge_types, true)) {
        $badge_type = 'all';
    }
    
    $badge_manager = new RESBS_Badge_Manager();
    $badges = $badge_manager->get_property_badges($property_id, $context);
    
    if (!is_array($badges) || empty($badges)) {
        return '';
    }
    
    // Filter by type if specified
    if ($badge_type !== 'all') {
        $badges = array_filter($badges, function($badge) use ($badge_type) {
            return isset($badge['type']) && $badge['type'] === $badge_type;
        });
    }
    
    if (empty($badges)) {
        return '';
    }
    
    ob_start();
    echo '<div class="resbs-badges-shortcode">';
    
    foreach ($badges as $badge) {
        // Validate badge array structure
        if (!is_array($badge) || !isset($badge['type']) || !isset($badge['text'])) {
            continue;
        }
        
        // Sanitize badge data
        $badge_type_safe = sanitize_key($badge['type']);
        $badge_size = isset($badge['size']) ? sanitize_key($badge['size']) : 'medium';
        $badge_position = isset($badge['position']) ? sanitize_key($badge['position']) : 'top-left';
        $badge_style = isset($badge['style']) ? $badge['style'] : '';
        $badge_text = sanitize_text_field($badge['text']);
        
        $classes = array(
            'resbs-badge',
            'resbs-badge-' . $badge_type_safe,
            'resbs-badge-' . $badge_size,
            'resbs-badge-' . $badge_position
        );
        
        $class_string = implode(' ', $classes);
        
        echo '<span class="' . esc_attr($class_string) . '" style="' . esc_attr($badge_style) . '">';
        echo esc_html($badge_text);
        echo '</span>';
    }
    
    echo '</div>';
    
    return ob_get_clean();
}
add_shortcode('resbs_badges', 'resbs_badge_shortcode');

/**
 * Badge widget
 */
class RESBS_Badge_Widget extends WP_Widget {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            'resbs_badge_widget',
            esc_html__('Property Badges', 'realestate-booking-suite'),
            array(
                'description' => esc_html__('Display property badges for a specific property.', 'realestate-booking-suite'),
                'classname' => 'resbs-badge-widget'
            )
        );
    }
    
    /**
     * Widget form
     */
    public function form($instance) {
        $defaults = array(
            'title' => esc_html__('Property Badges', 'realestate-booking-suite'),
            'property_id' => '',
            'badge_type' => 'all',
            'context' => 'widget'
        );
        
        $instance = wp_parse_args((array) $instance, $defaults);
        
        $title = sanitize_text_field($instance['title']);
        $property_id = intval($instance['property_id']);
        $badge_type = sanitize_text_field($instance['badge_type']);
        $context = sanitize_text_field($instance['context']);
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
            <label for="<?php echo esc_attr($this->get_field_id('property_id')); ?>">
                <?php esc_html_e('Property ID:', 'realestate-booking-suite'); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('property_id')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('property_id')); ?>" 
                   type="number" value="<?php echo esc_attr($property_id); ?>" />
            <small><?php esc_html_e('Leave empty to show badges for current property.', 'realestate-booking-suite'); ?></small>
        </p>
        
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('badge_type')); ?>">
                <?php esc_html_e('Badge Type:', 'realestate-booking-suite'); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr($this->get_field_id('badge_type')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('badge_type')); ?>">
                <option value="all" <?php selected($badge_type, 'all'); ?>><?php esc_html_e('All Badges', 'realestate-booking-suite'); ?></option>
                <option value="featured" <?php selected($badge_type, 'featured'); ?>><?php esc_html_e('Featured Only', 'realestate-booking-suite'); ?></option>
                <option value="new" <?php selected($badge_type, 'new'); ?>><?php esc_html_e('New Only', 'realestate-booking-suite'); ?></option>
                <option value="sold" <?php selected($badge_type, 'sold'); ?>><?php esc_html_e('Sold Only', 'realestate-booking-suite'); ?></option>
            </select>
        </p>
        
        <?php
    }
    
    /**
     * Update widget
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = isset($new_instance['title']) ? sanitize_text_field($new_instance['title']) : '';
        $instance['property_id'] = isset($new_instance['property_id']) ? absint($new_instance['property_id']) : 0;
        
        // Validate badge_type against whitelist
        $badge_type = isset($new_instance['badge_type']) ? sanitize_key($new_instance['badge_type']) : 'all';
        $allowed_badge_types = array('all', 'featured', 'new', 'sold');
        if (!in_array($badge_type, $allowed_badge_types, true)) {
            $badge_type = 'all';
        }
        $instance['badge_type'] = $badge_type;
        
        $instance['context'] = isset($new_instance['context']) ? sanitize_key($new_instance['context']) : 'widget';
        
        return $instance;
    }
    
    /**
     * Display widget
     */
    public function widget($args, $instance) {
        $title = isset($instance['title']) ? apply_filters('widget_title', sanitize_text_field($instance['title'])) : '';
        $property_id = isset($instance['property_id']) ? absint($instance['property_id']) : 0;
        
        // Validate badge_type against whitelist
        $badge_type = isset($instance['badge_type']) ? sanitize_key($instance['badge_type']) : 'all';
        $allowed_badge_types = array('all', 'featured', 'new', 'sold');
        if (!in_array($badge_type, $allowed_badge_types, true)) {
            $badge_type = 'all';
        }
        
        $context = isset($instance['context']) ? sanitize_key($instance['context']) : 'widget';
        
        // Use current property if no ID specified
        if (!$property_id) {
            $property_id = get_the_ID();
        }
        
        $property_id = absint($property_id);
        if (!$property_id) {
            return;
        }
        
        echo wp_kses_post($args['before_widget']);
        
        if (!empty($title)) {
            echo wp_kses_post($args['before_title']) . esc_html($title) . wp_kses_post($args['after_title']);
        }
        
        // Display badges using shortcode
        echo do_shortcode('[resbs_badges property_id="' . esc_attr($property_id) . '" type="' . esc_attr($badge_type) . '" context="' . esc_attr($context) . '"]');
        
        echo wp_kses_post($args['after_widget']);
    }
}

// Register badge widget
function resbs_register_badge_widget() {
    register_widget('RESBS_Badge_Widget');
}
add_action('widgets_init', 'resbs_register_badge_widget');
