<?php
/**
 * Demo Content Importer Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Demo_Importer {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_demo_import_page'));
        add_action('admin_init', array($this, 'process_demo_import'));
    }

    /**
     * Add Demo Import page to admin menu
     */
    public function add_demo_import_page() {
        add_submenu_page(
            'resbs-main-menu',
            esc_html__('Import Demo Content', 'realestate-booking-suite'),
            esc_html__('Demo Import', 'realestate-booking-suite'),
            'manage_options',
            'resbs-demo-import',
            array($this, 'render_demo_import_page')
        );
    }

    /**
     * Render Demo Import page
     */
    public function render_demo_import_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Import Demo Content', 'realestate-booking-suite'); ?></h1>
            
            <div class="card" style="max-width: 600px; margin-top: 20px; padding: 20px;">
                <h2><?php esc_html_e('One Click Demo Import', 'realestate-booking-suite'); ?></h2>
                <p><?php esc_html_e('Click the button below to import sample properties, agents, and settings. This will help you understand how the plugin works.', 'realestate-booking-suite'); ?></p>
                
                <div class="notice notice-warning inline">
                    <p><?php esc_html_e('Note: This will create dummy content on your site. You can delete it later.', 'realestate-booking-suite'); ?></p>
                </div>
                
                <form method="post" action="">
                    <?php wp_nonce_field('resbs_demo_import_nonce', 'resbs_demo_import_nonce'); ?>
                    <p class="submit">
                        <input type="submit" name="resbs_import_demo" id="resbs_import_demo" class="button button-primary" value="<?php esc_attr_e('Import Demo Content', 'realestate-booking-suite'); ?>">
                    </p>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Process demo import
     */
    public function process_demo_import() {
        if (isset($_POST['resbs_import_demo'])) {
            // Verify nonce
            $nonce = isset($_POST['resbs_demo_import_nonce']) ? sanitize_text_field(wp_unslash($_POST['resbs_demo_import_nonce'])) : '';
            if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_demo_import_nonce')) {
                wp_die(esc_html__('Security check failed.', 'realestate-booking-suite'));
            }
            
            // Check permissions
            if (!current_user_can('manage_options')) {
                wp_die(esc_html__('You do not have permission to perform this action.', 'realestate-booking-suite'));
            }
            
            $this->import_demo_content();
            
            add_action('admin_notices', function() {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php esc_html_e('Demo content imported successfully!', 'realestate-booking-suite'); ?></p>
                </div>
                <?php
            });
        }
    }

    /**
     * Import demo content
     */
    private function import_demo_content() {
        // 1. Create Property Statuses
        $statuses = array('For Sale', 'For Rent', 'Sold', 'New Construction');
        $status_ids = array();
        foreach ($statuses as $status) {
            $sanitized_status = sanitize_text_field($status);
            if (!term_exists($sanitized_status, 'property_status')) {
                $term = wp_insert_term($sanitized_status, 'property_status');
                if (!is_wp_error($term) && isset($term['term_id'])) {
                    $status_ids[$status] = absint($term['term_id']);
                }
            } else {
                $term = get_term_by('name', $sanitized_status, 'property_status');
                if ($term && !is_wp_error($term) && isset($term->term_id)) {
                    $status_ids[$status] = absint($term->term_id);
                }
            }
        }

        // 2. Create Property Types
        $types = array('Apartment', 'Villa', 'Office', 'Condo', 'Townhouse');
        $type_ids = array();
        foreach ($types as $type) {
            $sanitized_type = sanitize_text_field($type);
            if (!term_exists($sanitized_type, 'property_type')) {
                $term = wp_insert_term($sanitized_type, 'property_type');
                if (!is_wp_error($term) && isset($term['term_id'])) {
                    $type_ids[$type] = absint($term['term_id']);
                }
            } else {
                $term = get_term_by('name', $sanitized_type, 'property_type');
                if ($term && !is_wp_error($term) && isset($term->term_id)) {
                    $type_ids[$type] = absint($term->term_id);
                }
            }
        }

        // 3. Create Property Locations
        $locations = array('New York', 'Los Angeles', 'London', 'Paris', 'Dubai');
        $location_ids = array();
        foreach ($locations as $location) {
            $sanitized_location = sanitize_text_field($location);
            if (!term_exists($sanitized_location, 'property_location')) {
                $term = wp_insert_term($sanitized_location, 'property_location');
                if (!is_wp_error($term) && isset($term['term_id'])) {
                    $location_ids[$location] = absint($term['term_id']);
                }
            } else {
                $term = get_term_by('name', $sanitized_location, 'property_location');
                if ($term && !is_wp_error($term) && isset($term->term_id)) {
                    $location_ids[$location] = absint($term->term_id);
                }
            }
        }

        // 4. Create Sample Properties
        $properties = array(
            array(
                'title' => 'Modern Luxury Villa',
                'price' => '1500000',
                'type' => 'Villa',
                'status' => 'For Sale',
                'location' => 'Los Angeles',
                'bed' => 5,
                'bath' => 4,
                'sqft' => 4500,
                'desc' => 'Beautiful luxury villa with swimming pool and garden. Features state-of-the-art amenities and breathtaking views.',
                'features' => 'Swimming Pool, Garden, Garage, Smart Home',
                'agent' => array(
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'phone' => '+1 234 567 8900',
                    'title' => 'Senior Agent'
                )
            ),
            array(
                'title' => 'Downtown Apartment',
                'price' => '3500',
                'type' => 'Apartment',
                'status' => 'For Rent',
                'location' => 'New York',
                'bed' => 2,
                'bath' => 2,
                'sqft' => 1200,
                'desc' => 'Cozy apartment in the heart of the city. Walking distance to all major attractions and subway stations.',
                'features' => 'Gym, Concierge, Rooftop Access',
                'agent' => array(
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com',
                    'phone' => '+1 987 654 3210',
                    'title' => 'Rental Specialist'
                )
            ),
            array(
                'title' => 'Corporate Office Space',
                'price' => '5000000',
                'type' => 'Office',
                'status' => 'For Sale',
                'location' => 'London',
                'bed' => 0,
                'bath' => 2,
                'sqft' => 3000,
                'desc' => 'Premium office space suitable for large companies. Located in the financial district with excellent connectivity.',
                'features' => 'Conference Room, High-Speed Internet, Security',
                'agent' => array(
                    'name' => 'Robert Brown',
                    'email' => 'robert@example.com',
                    'phone' => '+44 20 1234 5678',
                    'title' => 'Commercial Broker'
                )
            ),
            array(
                'title' => 'Seaside Condo',
                'price' => '750000',
                'type' => 'Condo',
                'status' => 'New Construction',
                'location' => 'Dubai',
                'bed' => 3,
                'bath' => 2,
                'sqft' => 1800,
                'desc' => 'Brand new condo with sea views. Access to private beach and luxury amenities.',
                'features' => 'Beach Access, Pool, Spa, Parking',
                'agent' => array(
                    'name' => 'Sarah Wilson',
                    'email' => 'sarah@example.com',
                    'phone' => '+971 50 123 4567',
                    'title' => 'Luxury Consultant'
                )
            )
        );

        foreach ($properties as $prop) {
            // Sanitize property data
            $title = isset($prop['title']) ? sanitize_text_field($prop['title']) : '';
            $desc = isset($prop['desc']) ? wp_kses_post($prop['desc']) : '';
            $price = isset($prop['price']) ? sanitize_text_field($prop['price']) : '';
            $bed = isset($prop['bed']) ? absint($prop['bed']) : 0;
            $bath = isset($prop['bath']) ? absint($prop['bath']) : 0;
            $sqft = isset($prop['sqft']) ? absint($prop['sqft']) : 0;
            $features = isset($prop['features']) ? sanitize_text_field($prop['features']) : '';
            
            if (empty($title)) {
                continue;
            }
            
            $post_data = array(
                'post_title'    => $title,
                'post_content'  => $desc,
                'post_status'   => 'publish',
                'post_type'     => 'property',
                'post_author'   => absint(get_current_user_id())
            );
            
            $post_id = wp_insert_post($post_data, true);
            
            if ($post_id && !is_wp_error($post_id)) {
                $post_id = absint($post_id);
                
                // Set Meta - sanitize all values
                update_post_meta($post_id, '_property_price', $price);
                update_post_meta($post_id, '_property_bedrooms', $bed);
                update_post_meta($post_id, '_property_bathrooms', $bath);
                update_post_meta($post_id, '_property_area_sqft', $sqft);
                update_post_meta($post_id, '_property_featured', '1');
                update_post_meta($post_id, '_property_features', $features);
                
                // Set Agent Meta - sanitize all values
                if (isset($prop['agent']) && is_array($prop['agent'])) {
                    $agent_name = isset($prop['agent']['name']) ? sanitize_text_field($prop['agent']['name']) : '';
                    $agent_email = isset($prop['agent']['email']) ? sanitize_email($prop['agent']['email']) : '';
                    $agent_phone = isset($prop['agent']['phone']) ? sanitize_text_field($prop['agent']['phone']) : '';
                    $agent_title = isset($prop['agent']['title']) ? sanitize_text_field($prop['agent']['title']) : '';
                    
                    update_post_meta($post_id, '_property_agent_name', $agent_name);
                    update_post_meta($post_id, '_property_agent_email', $agent_email);
                    update_post_meta($post_id, '_property_agent_phone', $agent_phone);
                    update_post_meta($post_id, '_property_agent_title', $agent_title);
                }
                
                // Set Terms - validate term IDs exist before setting
                if (isset($prop['type']) && isset($type_ids[$prop['type']]) && $type_ids[$prop['type']] > 0) {
                    wp_set_post_terms($post_id, array(absint($type_ids[$prop['type']])), 'property_type');
                }
                if (isset($prop['status']) && isset($status_ids[$prop['status']]) && $status_ids[$prop['status']] > 0) {
                    wp_set_post_terms($post_id, array(absint($status_ids[$prop['status']])), 'property_status');
                }
                if (isset($prop['location']) && isset($location_ids[$prop['location']]) && $location_ids[$prop['location']] > 0) {
                    wp_set_post_terms($post_id, array(absint($location_ids[$prop['location']])), 'property_location');
                }
            }
        }
    }
}
