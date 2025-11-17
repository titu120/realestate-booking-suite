<?php
/**
 * Archive Property Clean Template
 * Block Theme Compatible
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Use helper function to safely get header (avoids deprecation warnings in block themes)
if (function_exists('resbs_get_header')) {
    resbs_get_header();
} else {
    get_header();
} ?>

<!-- THIS IS THE CLEAN TEMPLATE - PASTE YOUR HTML HERE -->
<div style="background: yellow; padding: 20px; border: 3px solid red; margin: 20px;">
    <h1 style="color: red;">CLEAN TEMPLATE IS WORKING!</h1>
    <p>If you see this, paste your HTML below to replace this message.</p>
</div>

<!-- PASTE YOUR HTML HERE -->
<!-- Replace everything below with your HTML format -->

<div class="my-archive">
    <h1>My Properties</h1>
    <div class="properties">
        <!-- Your HTML structure goes here -->
    </div>
</div>

<!-- END OF YOUR HTML -->

<?php
// Use helper function to safely get footer (avoids deprecation warnings in block themes)
if (function_exists('resbs_get_footer')) {
    resbs_get_footer();
} else {
    get_footer();
}
?>
