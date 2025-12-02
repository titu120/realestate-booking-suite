<?php
/**
 * Simple Archive Property Template - Content Only (for Block Themes)
 * Block Theme Compatible - NO header/footer calls
 * 
 * This template is used by block themes via the_content filter.
 * It contains the same content as simple-archive.php but WITHOUT get_header() and get_footer() calls.
 * 
 * @package RealEstate_Booking_Suite
 */

// For block themes: Include the full archive template content
// The archive handler will include this file, which contains all the archive logic and HTML
// but without the header/footer calls that would conflict with block themes

// Include the main archive template logic
$simple_archive_path = dirname(__FILE__) . '/simple-archive.php';

// Read the file and remove header/footer sections
if (file_exists($simple_archive_path)) {
    $content = file_get_contents($simple_archive_path);
    
    // Remove everything before the Font Awesome link (lines 1-31)
    $content = preg_replace('/^<\?php.*?@get_header\(\);.*?\?>/s', '', $content);
    
    // Remove the footer section (last 7 lines)
    $content = preg_replace('/<\?php\s+wp_reset_postdata\(\);.*?@get_footer\(\);.*?\?>\s*$/s', '<?php wp_reset_postdata(); ?>', $content);
    
    // Output the content
    echo $content;
} else {
    // Fallback error message
    echo '<div class="error">Archive template not found.</div>';
}
