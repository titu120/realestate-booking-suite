<?php
/**
 * Custom Post Type Registration
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_CPT {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_property_cpt'));
        add_action('init', array($this, 'register_property_taxonomies'));
        add_action('init', array($this, 'flush_rewrite_rules_if_needed'));
        // REMOVED: add_filter('template_include', array($this, 'property_template_include'));
        
        // SEO hooks
        add_action('save_post_property', array($this, 'handle_seo_on_save'), 10, 1);
        add_action('wp_head', array($this, 'add_seo_meta_description'));
    }
    
    /**
     * Handle SEO features on property save
     */
    public function handle_seo_on_save($post_id) {
        // Security checks
        // Skip autosaves and revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) {
            return;
        }
        
        // Verify post type
        if (get_post_type($post_id) !== 'property') {
            return;
        }
        
        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Auto-generate tags
        if (function_exists('resbs_auto_generate_tags')) {
            resbs_auto_generate_tags($post_id);
        }
    }
    
    /**
     * Add dynamic meta description to head
     */
    public function add_seo_meta_description() {
        if (!is_singular('property')) {
            return;
        }
        
        if (function_exists('resbs_generate_meta_description')) {
            $description = resbs_generate_meta_description();
            if ($description) {
                echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
            }
        }
    }
    
    /**
     * Register Property Custom Post Type
     */
    public function register_property_cpt() {
        $labels = array(
            'name'                  => esc_html_x('Properties', 'Post type general name', 'realestate-booking-suite'),
            'singular_name'         => esc_html_x('Property', 'Post type singular name', 'realestate-booking-suite'),
            'menu_name'             => esc_html_x('RealEstate Booking Suite', 'Admin Menu text', 'realestate-booking-suite'),
            'name_admin_bar'        => esc_html_x('Property', 'Add New on Toolbar', 'realestate-booking-suite'),
            'add_new'               => esc_html__('Add New', 'realestate-booking-suite'),
            'add_new_item'          => esc_html__('Add New Property', 'realestate-booking-suite'),
            'new_item'              => esc_html__('New Property', 'realestate-booking-suite'),
            'edit_item'             => esc_html__('Edit Property', 'realestate-booking-suite'),
            'view_item'             => esc_html__('View Property', 'realestate-booking-suite'),
            'all_items'             => esc_html__('All Properties', 'realestate-booking-suite'),
            'search_items'          => esc_html__('Search Properties', 'realestate-booking-suite'),
            'parent_item_colon'     => esc_html__('Parent Properties:', 'realestate-booking-suite'),
            'not_found'             => esc_html__('No properties found.', 'realestate-booking-suite'),
            'not_found_in_trash'    => esc_html__('No properties found in Trash.', 'realestate-booking-suite'),
            'featured_image'        => esc_html_x('Property Image', 'Overrides the "Featured Image" phrase', 'realestate-booking-suite'),
            'set_featured_image'    => esc_html_x('Set property image', 'Overrides the "Set featured image" phrase', 'realestate-booking-suite'),
            'remove_featured_image' => esc_html_x('Remove property image', 'Overrides the "Remove featured image" phrase', 'realestate-booking-suite'),
            'use_featured_image'    => esc_html_x('Use as property image', 'Overrides the "Use as featured image" phrase', 'realestate-booking-suite'),
            'archives'              => esc_html_x('Property archives', 'The post type archive label', 'realestate-booking-suite'),
            'insert_into_item'      => esc_html_x('Insert into property', 'Overrides the "Insert into post" phrase', 'realestate-booking-suite'),
            'uploaded_to_this_item' => esc_html_x('Uploaded to this property', 'Overrides the "Uploaded to this post" phrase', 'realestate-booking-suite'),
            'filter_items_list'     => esc_html_x('Filter properties list', 'Screen reader text for the filter links', 'realestate-booking-suite'),
            'items_list_navigation' => esc_html_x('Properties list navigation', 'Screen reader text for the pagination', 'realestate-booking-suite'),
            'items_list'            => esc_html_x('Properties list', 'Screen reader text for the items list', 'realestate-booking-suite'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => 'resbs-main-menu',
            'query_var'          => true,
            'rewrite'            => array('slug' => 'property'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'menu_icon'          => null,
            'supports'           => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest'       => true,
        );

        register_post_type('property', $args);
    }
    
    /**
     * Register Property Taxonomies
     */
    public function register_property_taxonomies() {
        // Property Type Taxonomy
        $type_labels = array(
            'name'              => esc_html_x('Property Types', 'taxonomy general name', 'realestate-booking-suite'),
            'singular_name'     => esc_html_x('Property Type', 'taxonomy singular name', 'realestate-booking-suite'),
            'search_items'      => esc_html__('Search Property Types', 'realestate-booking-suite'),
            'all_items'         => esc_html__('All Property Types', 'realestate-booking-suite'),
            'parent_item'       => esc_html__('Parent Property Type', 'realestate-booking-suite'),
            'parent_item_colon' => esc_html__('Parent Property Type:', 'realestate-booking-suite'),
            'edit_item'         => esc_html__('Edit Property Type', 'realestate-booking-suite'),
            'update_item'       => esc_html__('Update Property Type', 'realestate-booking-suite'),
            'add_new_item'      => esc_html__('Add New Property Type', 'realestate-booking-suite'),
            'new_item_name'     => esc_html__('New Property Type Name', 'realestate-booking-suite'),
            'menu_name'         => esc_html__('Property Types', 'realestate-booking-suite'),
        );

        register_taxonomy('property_type', array('property'), array(
            'hierarchical'      => true,
            'labels'            => $type_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'property-type'),
            'show_in_rest'      => true,
            'show_in_menu'      => 'resbs-main-menu',
        ));

        // Property Status Taxonomy
        $status_labels = array(
            'name'              => esc_html_x('Property Status', 'taxonomy general name', 'realestate-booking-suite'),
            'singular_name'     => esc_html_x('Property Status', 'taxonomy singular name', 'realestate-booking-suite'),
            'search_items'      => esc_html__('Search Property Status', 'realestate-booking-suite'),
            'all_items'         => esc_html__('All Property Status', 'realestate-booking-suite'),
            'parent_item'       => esc_html__('Parent Property Status', 'realestate-booking-suite'),
            'parent_item_colon' => esc_html__('Parent Property Status:', 'realestate-booking-suite'),
            'edit_item'         => esc_html__('Edit Property Status', 'realestate-booking-suite'),
            'update_item'       => esc_html__('Update Property Status', 'realestate-booking-suite'),
            'add_new_item'      => esc_html__('Add New Property Status', 'realestate-booking-suite'),
            'new_item_name'     => esc_html__('New Property Status Name', 'realestate-booking-suite'),
            'menu_name'         => esc_html__('Property Status', 'realestate-booking-suite'),
        );

        register_taxonomy('property_status', array('property'), array(
            'hierarchical'      => true,
            'labels'            => $status_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'property-status'),
            'show_in_rest'      => true,
            'show_in_menu'      => 'resbs-main-menu',
        ));

        // Property Location Taxonomy
        $location_labels = array(
            'name'              => esc_html_x('Property Locations', 'taxonomy general name', 'realestate-booking-suite'),
            'singular_name'     => esc_html_x('Property Location', 'taxonomy singular name', 'realestate-booking-suite'),
            'search_items'      => esc_html__('Search Property Locations', 'realestate-booking-suite'),
            'all_items'         => esc_html__('All Property Locations', 'realestate-booking-suite'),
            'parent_item'       => esc_html__('Parent Property Location', 'realestate-booking-suite'),
            'parent_item_colon' => esc_html__('Parent Property Location:', 'realestate-booking-suite'),
            'edit_item'         => esc_html__('Edit Property Location', 'realestate-booking-suite'),
            'update_item'       => esc_html__('Update Property Location', 'realestate-booking-suite'),
            'add_new_item'      => esc_html__('Add New Property Location', 'realestate-booking-suite'),
            'new_item_name'     => esc_html__('New Property Location Name', 'realestate-booking-suite'),
            'menu_name'         => esc_html__('Property Locations', 'realestate-booking-suite'),
        );

        register_taxonomy('property_location', array('property'), array(
            'hierarchical'      => true,
            'labels'            => $location_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'property-location'),
            'show_in_rest'      => true,
            'show_in_menu'      => 'resbs-main-menu',
        ));
    }
    
    /**
     * Include custom template for single property and archive
     */
    public function property_template_include($template) {
        global $post;
        
        // Security: Sanitize REQUEST_URI input
        // SIMPLE CHECK: If URL contains /property/ and has a slug, it's a single property
        // Note: sanitize_text_field() is more appropriate for REQUEST_URI (path string) than esc_url_raw()
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field($_SERVER['REQUEST_URI']) : '';
        
        // Check if it's a single property page (URL like /property/some-slug/)
        // Note: Single property template is handled by resbs_single_property_template_loader in main plugin file
        // This handler is disabled to avoid conflicts
        
        // Check if it's property archive (URL like /property/)
        // Note: Archive template is handled by RESBS_Simple_Archive and RESBS_Archive_Handler classes
        // This handler is disabled to avoid conflicts
        
        return $template;
    }
    
    /**
     * Flush rewrite rules if needed
     */
    public function flush_rewrite_rules_if_needed() {
        // Security: Only allow administrators to flush rewrite rules
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $option_name = 'resbs_flush_rewrite_rules';
        if (get_option($option_name) !== '1') {
            flush_rewrite_rules();
            update_option($option_name, '1');
        }
    }
}

// Initialize the class
new RESBS_CPT();
