<?php
/**
 * Test Archive Functionality
 * This file helps test the archive handler and display control
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Test if archive handler is working
function resbs_test_archive_functionality() {
    echo '<div style="background: #f0f8ff; border: 1px solid #007cba; padding: 15px; margin: 20px 0; border-radius: 5px;">';
    echo '<h3>Archive Functionality Test</h3>';
    
    // Test 1: Check if archive handler class exists
    if (class_exists('RESBS_Simple_Archive')) {
        echo '<p style="color: green;">✓ Simple Archive Handler class loaded successfully</p>';
        
        // Test 2: Check archive settings
        $archive_title = get_option('resbs_archive_title', 'Properties');
        $archive_layout = get_option('resbs_default_archive_layout', 'grid');
        $properties_per_page = get_option('resbs_properties_per_page', 12);
        
        echo '<p style="color: green;">✓ Archive settings loaded:</p>';
        echo '<ul>';
        echo '<li>Archive Title: ' . esc_html($archive_title) . '</li>';
        echo '<li>Default Layout: ' . esc_html($archive_layout) . '</li>';
        echo '<li>Properties Per Page: ' . esc_html($properties_per_page) . '</li>';
        echo '</ul>';
        
        // Test 3: Check if templates exist
        $archive_template = RESBS_PATH . 'templates/archive-property.php';
        $card_template = RESBS_PATH . 'templates/property-card.php';
        
        if (file_exists($archive_template)) {
            echo '<p style="color: green;">✓ Archive template exists</p>';
        } else {
            echo '<p style="color: red;">✗ Archive template missing</p>';
        }
        
        if (file_exists($card_template)) {
            echo '<p style="color: green;">✓ Property card template exists</p>';
        } else {
            echo '<p style="color: red;">✗ Property card template missing</p>';
        }
        
        // Test 4: Check CSS and JS files
        $css_file = RESBS_PATH . 'assets/css/archive.css';
        $js_file = RESBS_PATH . 'assets/js/archive.js';
        
        if (file_exists($css_file)) {
            echo '<p style="color: green;">✓ Archive CSS file exists</p>';
        } else {
            echo '<p style="color: red;">✗ Archive CSS file missing</p>';
        }
        
        if (file_exists($js_file)) {
            echo '<p style="color: green;">✓ Archive JS file exists</p>';
        } else {
            echo '<p style="color: red;">✗ Archive JS file missing</p>';
        }
        
        // Test 5: Check rewrite rules
        global $wp_rewrite;
        $rules = $wp_rewrite->wp_rewrite_rules();
        $has_property_rules = false;
        
        foreach ($rules as $pattern => $replacement) {
            if (strpos($pattern, 'properties') !== false) {
                $has_property_rules = true;
                break;
            }
        }
        
        if ($has_property_rules) {
            echo '<p style="color: green;">✓ Property archive rewrite rules found</p>';
        } else {
            echo '<p style="color: orange;">⚠ Property archive rewrite rules not found (may need to flush rewrite rules)</p>';
        }
        
    } else {
        echo '<p style="color: red;">✗ Archive Handler class not found</p>';
    }
    
    echo '<h4>Quick Actions:</h4>';
    echo '<p><a href="' . admin_url('admin.php?page=resbs-settings&tab=archive') . '" class="button">Go to Archive Settings</a></p>';
    echo '<p><a href="' . home_url('/properties/') . '" class="button" target="_blank">View Properties Archive</a></p>';
    echo '<p><a href="' . admin_url('options-permalink.php') . '" class="button">Flush Rewrite Rules</a></p>';
    
    echo '</div>';
}

// Add test to admin dashboard
add_action('admin_notices', 'resbs_test_archive_functionality');

// Add test to frontend (only for admins)
add_action('wp_footer', function() {
    if (current_user_can('manage_options')) {
        resbs_test_archive_functionality();
    }
});
