<?php
/**
 * Simple Archive Handler
 * Just handles the archive template - nothing else
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Simple_Archive {
    
    public function __construct() {
        add_filter('template_include', array($this, 'use_simple_template'), 99);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_simple_archive_assets'));
    }
    
    public function enqueue_simple_archive_assets() {
        // Only enqueue on archive pages
        if (is_post_type_archive('resbs_property') || 
            is_post_type_archive('property') || 
            is_post_type_archive('properties') ||
            is_tax('resbs_property_category') ||
            is_tax('property_category') ||
            is_tax('properties_category')) {
            
            // Enqueue the simple archive JavaScript
            wp_enqueue_script(
                'resbs-simple-archive',
                RESBS_URL . 'assets/js/simple-archive.js',
                array('jquery'),
                '1.0.0',
                true
            );
        }
    }
    
    public function use_simple_template($template) {
        // Debug: Check what post type we're dealing with
        global $wp_query;
        $post_type = get_query_var('post_type');
        
        // Check for different possible post types
        if (is_post_type_archive('resbs_property') || 
            is_post_type_archive('property') || 
            is_post_type_archive('properties') ||
            is_tax('resbs_property_category') ||
            is_tax('property_category') ||
            is_tax('properties_category') ||
            $post_type === 'property' ||
            $post_type === 'properties' ||
            $post_type === 'resbs_property') {
            
            $simple_template = RESBS_PATH . 'templates/simple-archive.php';
            if (file_exists($simple_template)) {
                return $simple_template;
            }
        }
        return $template;
    }
}

// Initialize
new RESBS_Simple_Archive();
