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
    if (!$property_id) {
        return;
    }
    
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
    if (!$property_id) {
        return '';
    }
    
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
    if (!$property_id || !$badge_type) {
        return false;
    }
    
    $badge_manager = new RESBS_Badge_Manager();
    $badges = $badge_manager->get_property_badges($property_id);
    
    foreach ($badges as $badge) {
        if ($badge['type'] === $badge_type) {
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
    if (!$property_id) {
        return 0;
    }
    
    $badge_manager = new RESBS_Badge_Manager();
    $badges = $badge_manager->get_property_badges($property_id);
    
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
    
    $property_id = intval($atts['property_id']);
    $context = sanitize_text_field($atts['context']);
    
    if (!$property_id) {
        return '';
    }
    
    $badge_manager = new RESBS_Badge_Manager();
    $badges = $badge_manager->get_property_badges($property_id, $context);
    
    if (empty($badges)) {
        return '';
    }
    
    // Filter by type if specified
    if ($atts['type'] !== 'all') {
        $badges = array_filter($badges, function($badge) use ($atts) {
            return $badge['type'] === $atts['type'];
        });
    }
    
    if (empty($badges)) {
        return '';
    }
    
    ob_start();
    echo '<div class="resbs-badges-shortcode">';
    
    foreach ($badges as $badge) {
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
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['property_id'] = intval($new_instance['property_id']);
        $instance['badge_type'] = sanitize_text_field($new_instance['badge_type']);
        $instance['context'] = sanitize_text_field($new_instance['context']);
        
        return $instance;
    }
    
    /**
     * Display widget
     */
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', sanitize_text_field($instance['title']));
        $property_id = intval($instance['property_id']);
        $badge_type = sanitize_text_field($instance['badge_type']);
        $context = sanitize_text_field($instance['context']);
        
        // Use current property if no ID specified
        if (!$property_id) {
            $property_id = get_the_ID();
        }
        
        if (!$property_id) {
            return;
        }
        
        echo $args['before_widget'];
        
        if (!empty($title)) {
            echo $args['before_title'] . esc_html($title) . $args['after_title'];
        }
        
        // Display badges using shortcode
        echo do_shortcode('[resbs_badges property_id="' . esc_attr($property_id) . '" type="' . esc_attr($badge_type) . '" context="' . esc_attr($context) . '"]');
        
        echo $args['after_widget'];
    }
}

// Register badge widget
function resbs_register_badge_widget() {
    register_widget('RESBS_Badge_Widget');
}
add_action('widgets_init', 'resbs_register_badge_widget');
