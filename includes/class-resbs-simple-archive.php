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
        add_filter('template_include', array($this, 'use_simple_template'), 10);
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
                esc_url(RESBS_URL . 'assets/js/simple-archive.js'),
                array('jquery'),
                '1.0.0',
                true
            );
            
            // Localize script with security nonces and AJAX URL
            // This provides nonces for any future AJAX functionality
            wp_localize_script('resbs-simple-archive', 'resbs_simple_archive', array(
                'ajax_url' => esc_url(admin_url('admin-ajax.php')),
                'nonce' => esc_js(wp_create_nonce('resbs_simple_archive_nonce')),
                'favorites_nonce' => esc_js(wp_create_nonce('resbs_favorites_nonce')),
            ));
        }
    }
    
    public function use_simple_template($template) {
        // ONLY handle archive pages, NOT single property pages
        if (is_post_type_archive('resbs_property') || 
            is_post_type_archive('property') || 
            is_post_type_archive('properties') ||
            is_tax('resbs_property_category') ||
            is_tax('property_category') ||
            is_tax('properties_category')) {
            
            // Sanitize and validate the template path
            $template_file = 'templates/simple-archive.php';
            $simple_template = trailingslashit(RESBS_PATH) . $template_file;
            
            // Ensure the file exists and is within the plugin directory (security check)
            if (file_exists($simple_template) && 
                strpos(realpath($simple_template), realpath(RESBS_PATH)) === 0) {
                return $simple_template;
            }
        }
        return $template;
    }
}

// Initialize
new RESBS_Simple_Archive();
