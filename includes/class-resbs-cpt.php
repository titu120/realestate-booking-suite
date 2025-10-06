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
    }
    
    /**
     * Register Property Custom Post Type
     */
    public function register_property_cpt() {
        $labels = array(
            'name'                  => _x('Properties', 'Post type general name', 'realestate-booking-suite'),
            'singular_name'         => _x('Property', 'Post type singular name', 'realestate-booking-suite'),
            'menu_name'             => _x('Properties', 'Admin Menu text', 'realestate-booking-suite'),
            'name_admin_bar'        => _x('Property', 'Add New on Toolbar', 'realestate-booking-suite'),
            'add_new'               => __('Add New', 'realestate-booking-suite'),
            'add_new_item'          => __('Add New Property', 'realestate-booking-suite'),
            'new_item'              => __('New Property', 'realestate-booking-suite'),
            'edit_item'             => __('Edit Property', 'realestate-booking-suite'),
            'view_item'             => __('View Property', 'realestate-booking-suite'),
            'all_items'             => __('All Properties', 'realestate-booking-suite'),
            'search_items'          => __('Search Properties', 'realestate-booking-suite'),
            'parent_item_colon'     => __('Parent Properties:', 'realestate-booking-suite'),
            'not_found'             => __('No properties found.', 'realestate-booking-suite'),
            'not_found_in_trash'    => __('No properties found in Trash.', 'realestate-booking-suite'),
            'featured_image'        => _x('Property Image', 'Overrides the "Featured Image" phrase', 'realestate-booking-suite'),
            'set_featured_image'    => _x('Set property image', 'Overrides the "Set featured image" phrase', 'realestate-booking-suite'),
            'remove_featured_image' => _x('Remove property image', 'Overrides the "Remove featured image" phrase', 'realestate-booking-suite'),
            'use_featured_image'    => _x('Use as property image', 'Overrides the "Use as featured image" phrase', 'realestate-booking-suite'),
            'archives'              => _x('Property archives', 'The post type archive label', 'realestate-booking-suite'),
            'insert_into_item'      => _x('Insert into property', 'Overrides the "Insert into post" phrase', 'realestate-booking-suite'),
            'uploaded_to_this_item' => _x('Uploaded to this property', 'Overrides the "Uploaded to this post" phrase', 'realestate-booking-suite'),
            'filter_items_list'     => _x('Filter properties list', 'Screen reader text for the filter links', 'realestate-booking-suite'),
            'items_list_navigation' => _x('Properties list navigation', 'Screen reader text for the pagination', 'realestate-booking-suite'),
            'items_list'            => _x('Properties list', 'Screen reader text for the items list', 'realestate-booking-suite'),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array('slug' => 'property'),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-building',
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
            'name'              => _x('Property Types', 'taxonomy general name', 'realestate-booking-suite'),
            'singular_name'     => _x('Property Type', 'taxonomy singular name', 'realestate-booking-suite'),
            'search_items'      => __('Search Property Types', 'realestate-booking-suite'),
            'all_items'         => __('All Property Types', 'realestate-booking-suite'),
            'parent_item'       => __('Parent Property Type', 'realestate-booking-suite'),
            'parent_item_colon' => __('Parent Property Type:', 'realestate-booking-suite'),
            'edit_item'         => __('Edit Property Type', 'realestate-booking-suite'),
            'update_item'       => __('Update Property Type', 'realestate-booking-suite'),
            'add_new_item'      => __('Add New Property Type', 'realestate-booking-suite'),
            'new_item_name'     => __('New Property Type Name', 'realestate-booking-suite'),
            'menu_name'         => __('Property Types', 'realestate-booking-suite'),
        );

        register_taxonomy('property_type', array('property'), array(
            'hierarchical'      => true,
            'labels'            => $type_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'property-type'),
            'show_in_rest'      => true,
        ));

        // Property Status Taxonomy
        $status_labels = array(
            'name'              => _x('Property Status', 'taxonomy general name', 'realestate-booking-suite'),
            'singular_name'     => _x('Property Status', 'taxonomy singular name', 'realestate-booking-suite'),
            'search_items'      => __('Search Property Status', 'realestate-booking-suite'),
            'all_items'         => __('All Property Status', 'realestate-booking-suite'),
            'parent_item'       => __('Parent Property Status', 'realestate-booking-suite'),
            'parent_item_colon' => __('Parent Property Status:', 'realestate-booking-suite'),
            'edit_item'         => __('Edit Property Status', 'realestate-booking-suite'),
            'update_item'       => __('Update Property Status', 'realestate-booking-suite'),
            'add_new_item'      => __('Add New Property Status', 'realestate-booking-suite'),
            'new_item_name'     => __('New Property Status Name', 'realestate-booking-suite'),
            'menu_name'         => __('Property Status', 'realestate-booking-suite'),
        );

        register_taxonomy('property_status', array('property'), array(
            'hierarchical'      => true,
            'labels'            => $status_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'property-status'),
            'show_in_rest'      => true,
        ));

        // Property Location Taxonomy
        $location_labels = array(
            'name'              => _x('Property Locations', 'taxonomy general name', 'realestate-booking-suite'),
            'singular_name'     => _x('Property Location', 'taxonomy singular name', 'realestate-booking-suite'),
            'search_items'      => __('Search Property Locations', 'realestate-booking-suite'),
            'all_items'         => __('All Property Locations', 'realestate-booking-suite'),
            'parent_item'       => __('Parent Property Location', 'realestate-booking-suite'),
            'parent_item_colon' => __('Parent Property Location:', 'realestate-booking-suite'),
            'edit_item'         => __('Edit Property Location', 'realestate-booking-suite'),
            'update_item'       => __('Update Property Location', 'realestate-booking-suite'),
            'add_new_item'      => __('Add New Property Location', 'realestate-booking-suite'),
            'new_item_name'     => __('New Property Location Name', 'realestate-booking-suite'),
            'menu_name'         => __('Property Locations', 'realestate-booking-suite'),
        );

        register_taxonomy('property_location', array('property'), array(
            'hierarchical'      => true,
            'labels'            => $location_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => array('slug' => 'property-location'),
            'show_in_rest'      => true,
        ));
    }
}

// Initialize the class
new RESBS_CPT();
