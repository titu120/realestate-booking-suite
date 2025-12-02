<?php
/**
 * Debug Block Theme Detection
 * 
 * Temporary file to check if block theme is being detected
 */

// Check what theme is active
$current_theme = wp_get_theme();
echo "Current Theme: " . $current_theme->get('Name') . "<br>";
echo "Theme Template: " . $current_theme->get_template() . "<br>";

// Check if wp_is_block_theme exists
echo "wp_is_block_theme function exists: " . (function_exists('wp_is_block_theme') ? 'YES' : 'NO') . "<br>";

// Check if it's a block theme
if (function_exists('wp_is_block_theme')) {
    echo "Is Block Theme: " . (wp_is_block_theme() ? 'YES' : 'NO') . "<br>";
}

// Check resbs_is_block_theme
if (function_exists('resbs_is_block_theme')) {
    echo "resbs_is_block_theme: " . (resbs_is_block_theme() ? 'YES' : 'NO') . "<br>";
}

// Check if we're on a property page
echo "is_singular('property'): " . (is_singular('property') ? 'YES' : 'NO') . "<br>";
echo "is_post_type_archive('property'): " . (is_post_type_archive('property') ? 'YES' : 'NO') . "<br>";
