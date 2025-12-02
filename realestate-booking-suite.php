<?php 
/**
 * Plugin Name: RealEstate Booking Suite
 * Description: Professional real estate booking plugin that allows users and agents to submit properties, manage bookings, and display properties in responsive layouts. Plugin includes Elementor and Appearance widgets, advanced AJAX search, map integration, property details page, frontend dashboard, favorites, and booking history. Fully multilingual-ready.
 * Author: Softivus
 * Author URI: https://softivus.com
 * Version: 1.0.0
 * Text Domain: realestate-booking-suite
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Tested up to: 6.4
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RESBS_PATH', plugin_dir_path(__FILE__));
define('RESBS_URL', plugin_dir_url(__FILE__));

// Force flush rewrite rules on activation
function resbs_flush_rewrite_rules() {
    flush_rewrite_rules();
}

/**
 * Create wishlist page on plugin activation
 * Uses slug "saved-properties" to avoid conflicts with other plugins
 */
function resbs_create_wishlist_page() {
    // Check if page already exists
    $page_slug = 'saved-properties';
    $existing_page = get_page_by_path($page_slug);
    
    // Also check if we stored the page ID in options
    $stored_page_id = get_option('resbs_wishlist_page_id');
    
    if ($stored_page_id) {
        $existing_page = get_post($stored_page_id);
        if ($existing_page && $existing_page->post_status !== 'trash') {
            // Page exists and is not trashed, don't create again
            return $stored_page_id;
        }
    }
    
    // If page exists but not in our option, use it
    if ($existing_page && $existing_page->post_status !== 'trash') {
        update_option('resbs_wishlist_page_id', $existing_page->ID);
        return $existing_page->ID;
    }
    
    // Create the page
    $page_data = array(
        'post_title'    => __('My Saved Properties', 'realestate-booking-suite'),
        'post_content'  => '[resbs_favorites]',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => $page_slug,
        'post_author'   => 1,
        'comment_status' => 'closed',
        'ping_status'   => 'closed'
    );
    
    $page_id = wp_insert_post($page_data);
    
    if ($page_id && !is_wp_error($page_id)) {
        // Store the page ID in options
        update_option('resbs_wishlist_page_id', $page_id);
        return $page_id;
    }
    
    return false;
}

/**
 * Create profile page on plugin activation
 * Uses slug "profile" to avoid conflicts with other plugins
 */
function resbs_create_profile_page() {
    // Check if page already exists
    $page_slug = 'profile';
    $existing_page = get_page_by_path($page_slug);
    
    // Also check if we stored the page ID in options
    $stored_page_id = get_option('resbs_profile_page_id');
    
    if ($stored_page_id) {
        $existing_page = get_post($stored_page_id);
        if ($existing_page && $existing_page->post_status !== 'trash') {
            // Page exists and is not trashed, don't create again
            return $stored_page_id;
        }
    }
    
    // If page exists but not in our option, use it
    if ($existing_page && $existing_page->post_status !== 'trash') {
        update_option('resbs_profile_page_id', $existing_page->ID);
        return $existing_page->ID;
    }
    
    // Get title and subtitle from settings or use defaults
    $page_title = get_option('resbs_profile_page_title', 'User Profile');
    $page_title = sanitize_text_field($page_title);
    
    // Create the page
    $page_data = array(
        'post_title'    => $page_title,
        'post_content'  => '[resbs_dashboard show_profile="yes"]',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => $page_slug,
        'post_author'   => 1,
        'comment_status' => 'closed',
        'ping_status'   => 'closed'
    );
    
    $page_id = wp_insert_post($page_data);
    
    if ($page_id && !is_wp_error($page_id)) {
        // Store the page ID in options
        update_option('resbs_profile_page_id', $page_id);
        return $page_id;
    }
    
    return false;
}

/**
 * Create submit property page on plugin activation
 * Uses slug "submit-property" to avoid conflicts with other plugins
 */
function resbs_create_submit_property_page() {
    // Check if page already exists
    $page_slug = 'submit-property';
    $existing_page = get_page_by_path($page_slug);
    
    // Also check if we stored the page ID in options
    $stored_page_id = get_option('resbs_submit_property_page_id');
    
    if ($stored_page_id) {
        $existing_page = get_post($stored_page_id);
        if ($existing_page && $existing_page->post_status !== 'trash') {
            // Page exists and is not trashed, don't create again
            return $stored_page_id;
        }
    }
    
    // If page exists but not in our option, use it
    if ($existing_page && $existing_page->post_status !== 'trash') {
        update_option('resbs_submit_property_page_id', $existing_page->ID);
        return $existing_page->ID;
    }
    
    // Create the page
    $page_data = array(
        'post_title'    => __('Submit Property', 'realestate-booking-suite'),
        'post_content'  => '[resbs_submit_property]',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => $page_slug,
        'post_author'   => 1,
        'comment_status' => 'closed',
        'ping_status'   => 'closed'
    );
    
    $page_id = wp_insert_post($page_data);
    
    if ($page_id && !is_wp_error($page_id)) {
        // Store the page ID in options
        update_option('resbs_submit_property_page_id', $page_id);
        return $page_id;
    }
    
    return false;
}

/**
 * Create default property status terms if they don't exist
 */
function resbs_create_default_property_statuses() {
    // Check if terms already exist
    $existing_terms = get_terms(array(
        'taxonomy' => 'property_status',
        'hide_empty' => false,
    ));
    
    // If terms exist and not an error, return
    if (!empty($existing_terms) && !is_wp_error($existing_terms)) {
        return;
    }
    
    // Create default property status terms
    $default_statuses = array(
        'For Sale',
        'For Rent',
        'Sold',
        'Rented',
        'Available',
        'Pending',
        'Under Contract'
    );
    
    foreach ($default_statuses as $status_name) {
        // Check if term already exists
        $term_exists = term_exists($status_name, 'property_status');
        
        if (!$term_exists) {
            wp_insert_term($status_name, 'property_status');
        }
    }
}

/**
 * Plugin activation hook
 */
function resbs_plugin_activation() {
    // Define path constant if not already defined
    if (!defined('RESBS_PATH')) {
        define('RESBS_PATH', plugin_dir_path(__FILE__));
    }
    
    // Load the CPT class file first
    if (!class_exists('RESBS_CPT')) {
        $cpt_file = RESBS_PATH . 'includes/class-resbs-cpt.php';
        if (file_exists($cpt_file)) {
            require_once $cpt_file;
        }
    }
    
    // Register post types first
    if (class_exists('RESBS_CPT')) {
        $cpt = new RESBS_CPT();
        $cpt->register_property_cpt();
        $cpt->register_property_taxonomies();
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Create default property status terms
    resbs_create_default_property_statuses();
    
    // Create wishlist page
    resbs_create_wishlist_page();
    
    // Create profile page
    resbs_create_profile_page();
    
    // Create submit property page
    resbs_create_submit_property_page();
    
    // Set flag to ensure rewrite rules are flushed on next init
    delete_option('resbs_flush_rewrite_rules');
}
register_activation_hook(__FILE__, 'resbs_plugin_activation');

/**
 * Plugin deactivation hook
 * 
 * Cleans up scheduled cron jobs when plugin is deactivated
 */
function resbs_plugin_deactivation() {
    // Clear scheduled cron jobs
    wp_clear_scheduled_hook('resbs_send_search_alerts');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'resbs_plugin_deactivation');

/**
 * Plugin uninstall hook
 * 
 * Removes all plugin data when plugin is uninstalled
 * This includes: custom tables, options, user meta, post meta, taxonomies
 * Note: Created pages are preserved to avoid accidental deletion of user content
 */
function resbs_plugin_uninstall() {
    // Security check - only run if uninstalling via WordPress
    if (!defined('WP_UNINSTALL_PLUGIN')) {
        return;
    }
    
    global $wpdb;
    
    // Only run if user has proper permissions
    if (!current_user_can('activate_plugins')) {
        return;
    }
    
    // Clear scheduled cron jobs
    wp_clear_scheduled_hook('resbs_send_search_alerts');
    
    // Delete all custom post type posts (property and property_booking)
    $post_types = array('property', 'property_booking');
    foreach ($post_types as $post_type) {
        $posts = get_posts(array(
            'post_type' => $post_type,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'fields' => 'ids'
        ));
        
        foreach ($posts as $post_id) {
            wp_delete_post($post_id, true); // Force delete (bypass trash)
        }
    }
    
    // Delete created pages
    $page_ids = array(
        get_option('resbs_wishlist_page_id'),
        get_option('resbs_profile_page_id'),
        get_option('resbs_submit_property_page_id')
    );
    
    foreach ($page_ids as $page_id) {
        if ($page_id) {
            wp_delete_post($page_id, true); // Force delete
        }
    }
    
    // Remove custom database tables
    // Table name is safe - constructed from $wpdb->prefix (no user input)
    $table_name = $wpdb->prefix . 'resbs_contact_messages';
    // Use backticks for table name - safe as it's constructed from $wpdb->prefix
    $wpdb->query("DROP TABLE IF EXISTS `" . str_replace('`', '``', $table_name) . "`");
    
    // Remove search alerts table
    $search_alerts_table = $wpdb->prefix . 'resbs_search_alerts';
    $wpdb->query("DROP TABLE IF EXISTS `" . str_replace('`', '``', $search_alerts_table) . "`");
    
    // Remove all plugin options
    // Using LIKE with wildcard - safe as it's a literal string pattern
    $like_pattern = $wpdb->esc_like('resbs_') . '%';
    $options = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
            $like_pattern
        ),
        ARRAY_A
    );
    
    foreach ($options as $option) {
        delete_option($option['option_name']);
    }
    
    // Remove user meta
    // Using LIKE with wildcard - safe as it's a literal string pattern
    $like_pattern = $wpdb->esc_like('resbs_') . '%';
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
            $like_pattern
        )
    );
    
    // Remove post meta (for any remaining orphaned meta)
    // Using LIKE with wildcard - safe as it's a literal string pattern
    $like_pattern1 = $wpdb->esc_like('_property_') . '%';
    $like_pattern2 = $wpdb->esc_like('_resbs_') . '%';
    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE %s OR meta_key LIKE %s",
            $like_pattern1,
            $like_pattern2
        )
    );
    
    // Remove taxonomies and terms
    $taxonomies = array('property_type', 'property_status', 'property_location', 'property_tag');
    foreach ($taxonomies as $taxonomy) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));
        
        if (!is_wp_error($terms) && !empty($terms)) {
            foreach ($terms as $term) {
                wp_delete_term($term->term_id, $taxonomy);
            }
        }
        
        // Delete term meta for this taxonomy
        if (!empty($wpdb->termmeta)) {
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE tm FROM {$wpdb->termmeta} tm 
                    INNER JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id 
                    WHERE tt.taxonomy = %s",
                    $taxonomy
                )
            );
        }
    }
    
    // Delete orphaned term relationships
    $wpdb->query(
        "DELETE tr FROM {$wpdb->term_relationships} tr 
        LEFT JOIN {$wpdb->posts} posts ON posts.ID = tr.object_id 
        WHERE posts.ID IS NULL"
    );
    
    // Delete orphaned terms (terms without taxonomies)
    $wpdb->query(
        "DELETE t FROM {$wpdb->terms} t 
        LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id 
        WHERE tt.term_id IS NULL"
    );
    
    // Delete orphaned term meta
    if (!empty($wpdb->termmeta)) {
        $wpdb->query(
            "DELETE tm FROM {$wpdb->termmeta} tm 
            LEFT JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id 
            WHERE tt.term_id IS NULL"
        );
    }
    
    // Delete orphaned post meta (meta without posts)
    $wpdb->query(
        "DELETE pm FROM {$wpdb->postmeta} pm 
        LEFT JOIN {$wpdb->posts} posts ON posts.ID = pm.post_id 
        WHERE posts.ID IS NULL"
    );
    
    // Delete orphaned comments (comments for deleted posts)
    $wpdb->query(
        "DELETE c FROM {$wpdb->comments} c 
        LEFT JOIN {$wpdb->posts} posts ON posts.ID = c.comment_post_ID 
        WHERE posts.ID IS NULL AND c.comment_post_ID > 0"
    );
    
    // Delete orphaned comment meta
    $wpdb->query(
        "DELETE cm FROM {$wpdb->commentmeta} cm 
        LEFT JOIN {$wpdb->comments} c ON cm.comment_id = c.comment_ID 
        WHERE c.comment_ID IS NULL"
    );
    
    // Flush rewrite rules
    flush_rewrite_rules();
    
    // Clear any cached data
    wp_cache_flush();
}
register_uninstall_hook(__FILE__, 'resbs_plugin_uninstall');

// Manual flush rewrite rules function (for debugging)
function resbs_manual_flush_rewrite_rules() {
    // Only allow admins
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Check if parameter is set
    if (!isset($_GET['resbs_flush'])) {
        return;
    }
    
    $flush_param = sanitize_text_field($_GET['resbs_flush']);
    if ($flush_param !== '1') {
        return;
    }
    
    // Verify nonce for security
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_flush_rewrite_rules')) {
        wp_die(
            esc_html__('Security check failed. Please try again.', 'realestate-booking-suite'),
            esc_html__('Security Check Failed', 'realestate-booking-suite'),
            array('response' => 403)
        );
    }
    
    flush_rewrite_rules();
    
    // Use admin notice instead of direct echo
    add_action('admin_notices', function() {
        echo '<div class="notice notice-success is-dismissible"><p>';
        echo esc_html__('Rewrite rules flushed successfully!', 'realestate-booking-suite');
        echo '</p></div>';
    });
}
add_action('init', 'resbs_manual_flush_rewrite_rules');

/**
 * Check if current theme is a block theme
 * 
 * This plugin is compatible with both classic and block themes (including Twenty Twenty-Five).
 * 
 * @return bool True if block theme, false otherwise
 */
function resbs_is_block_theme() {
    if (function_exists('wp_is_block_theme')) {
        return wp_is_block_theme();
    }
    return false;
}

/**
 * Get header - provides full HTML structure for block themes when using template_include
 */
function resbs_get_header() {
    global $resbs_html_started;
    
    if (resbs_is_block_theme()) {
        // When using template_include, we bypass WordPress template system
        // So we MUST provide full HTML structure for block themes
        if (!isset($resbs_html_started) || !$resbs_html_started) {
            $resbs_html_started = true;
            ?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
<?php
        }
        // Render block theme header
        // Use do_blocks to render the standard header template part
        echo do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' );
    } else {
        // For classic themes, use standard get_header()
        get_header();
    }
}

/**
 * Get footer - closes HTML structure for block themes when using template_include
 */
function resbs_get_footer() {
    global $resbs_html_started;
    
    if (resbs_is_block_theme()) {
        // Render block theme footer
        // Use do_blocks to render the standard footer template part
        echo do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' );
        
        // Close HTML structure for block themes
        if (isset($resbs_html_started) && $resbs_html_started) {
            ?>
</div><!-- .wp-site-blocks -->
<?php
            if (!did_action('wp_footer')) {
                wp_footer();
            }
            ?>
</body>
</html>
<?php
        }
    } else {
        // For classic themes, use standard get_footer()
        get_footer();
    }
}

// SINGLE PROPERTY TEMPLATE LOADER
// For block themes: Use the_content filter (like Estatik) - NO template_include
// For classic themes: Use template_include with custom template
function resbs_single_property_template_loader($template) {
    if (is_singular('property')) {
        // For classic themes AND block themes (now that get_header is fixed): Use custom template
        $single_template = RESBS_PATH . 'templates/single-property.php';
        if (file_exists($single_template)) {
            return $single_template;
        }
    }
    return $template;
}
add_filter('template_include', 'resbs_single_property_template_loader', 5);

// REMOVED: resbs_single_property_content_filter - using template_include instead

// Enqueue assets
function resbs_enqueue_assets() {
    // Only load plugin CSS on property-related pages to prevent conflicts with theme
    $post = get_post();
    $has_shortcode = $post && has_shortcode($post->post_content ?? '', 'resbs_');
    
    $is_property_page = is_singular('property') || 
                        is_post_type_archive('property') || 
                        is_tax('property_type') || 
                        is_tax('property_status') || 
                        is_tax('property_location') || 
                        is_tax('property_tag') ||
                        $has_shortcode;
    
    // Enqueue Font Awesome only on property pages
    if ($is_property_page) {
        wp_enqueue_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            array(),
            '6.4.0',
            'all'
        );
        
        // Enqueue CSS only on property pages
        wp_enqueue_style(
            'resbs-style',
            RESBS_URL . 'assets/css/style.css',
            array('font-awesome'),
            '1.0.0'
        );
        
        // Enqueue Contact Widget CSS
        wp_enqueue_style(
            'resbs-contact-widget',
            RESBS_URL . 'assets/css/contact-widget.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue Layout CSS
        wp_enqueue_style(
            'resbs-layouts',
            RESBS_URL . 'assets/css/layouts.css',
            array(),
            '1.0.0'
        );
        
        // Enqueue Single Property Responsive CSS only on single property pages
        if (is_singular('property')) {
            // Load with HIGHEST priority - after all theme styles
            wp_enqueue_style(
                'resbs-single-property-responsive',
                RESBS_URL . 'assets/css/single-property-responsive.css',
                array(), // No dependencies
                '2.0.0' // Version bump
            );
            // Force load after all other styles with maximum priority
            add_action('wp_head', function() {
                echo '<style id="resbs-single-property-override">body.single.single-property .single-property.resbs-single-property-wrapper#resbs-single-property-page { position: relative !important; isolation: isolate !important; }</style>' . "\n";
            }, 99999);
        }
        
        // Enqueue Shortcodes CSS
        wp_enqueue_style(
            'resbs-shortcodes',
            RESBS_URL . 'assets/css/shortcodes.css',
            array(),
            '1.0.0'
        );
    }
        
        // Enqueue Modern Dashboard CSS (Admin Only)
        if (is_admin()) {
            wp_enqueue_style(
                'resbs-modern-dashboard',
                RESBS_URL . 'assets/css/modern-dashboard.css',
                array(),
                '1.0.0'
            );
        }
        
        // Only load archive CSS on property archive pages, not on all pages
        if (is_post_type_archive('property') || is_tax('property_type') || is_tax('property_status') || is_tax('property_location') || is_tax('property_tag')) {
            // Load fallback CSS first (simple selectors)
            wp_enqueue_style(
                'resbs-archive-fallback',
                RESBS_URL . 'assets/css/rbs-archive-fallback.css',
                array(), // No dependencies
                '2.0.0'
            );
            // Load with HIGHEST priority - after all theme styles
            wp_enqueue_style(
                'resbs-archive',
                RESBS_URL . 'assets/css/rbs-archive.css',
                array('resbs-archive-fallback'), // Load after fallback
                '2.0.0' // Version bump
            );
            // Force load after all other styles with maximum priority - CRITICAL CONTAINER WIDTH OVERRIDE
            add_action('wp_head', function() {
                echo '<style id="resbs-archive-override">
                /* MAXIMUM PRIORITY CONTAINER WIDTH - OVERRIDE ALL THEME STYLES */
                body.archive.post-type-archive-property .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container,
                body.archive.tax-property_type .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container,
                body.archive.tax-property_status .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container,
                body.archive.tax-property_location .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container,
                body.page .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container,
                body .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container,
                .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container,
                #resbs-property-archive .container,
                .rbs-archive .container,
                body.archive.post-type-archive-property .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container.main-content,
                body.archive.tax-property_type .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container.main-content,
                body.archive.tax-property_status .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container.main-content,
                body.archive.tax-property_location .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container.main-content,
                body.page .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container.main-content,
                body .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container.main-content,
                .rbs-archive.resbs-archive-wrapper#resbs-property-archive .container.main-content,
                #resbs-property-archive .container.main-content,
                .rbs-archive .container.main-content {
                    width: 100% !important;
                    max-width: 1540px !important;
                    min-width: 0 !important;
                    margin: 0 auto !important;
                    margin-left: auto !important;
                    margin-right: auto !important;
                    padding: 4rem 16px !important;
                    padding-top: 4rem !important;
                    padding-bottom: 4rem !important;
                    padding-left: 16px !important;
                    padding-right: 16px !important;
                    box-sizing: border-box !important;
                    position: relative !important;
                    display: block !important;
                }
                body.archive.post-type-archive-property .rbs-archive.resbs-archive-wrapper#resbs-property-archive {
                    position: relative !important;
                    isolation: isolate !important;
                    contain: layout style paint !important;
                }
                </style>' . "\n";
            }, 99999);
        }
        
        // Re-enable Dynamic Archive JS but with modifications
        // Only load on frontend, NOT in admin (to avoid interfering with property edit page)
        if (!is_admin()) {
            wp_enqueue_script(
                'resbs-dynamic-archive',
                RESBS_URL . 'assets/js/dynamic-archive.js',
                array('jquery'),
                '1.0.0',
                true
            );
        }
        

        
    
}
add_action('wp_enqueue_scripts', 'resbs_enqueue_assets', 5);
add_action('admin_enqueue_scripts', 'resbs_enqueue_assets', 5);

// Fallback: Add Font Awesome directly to head if not already loaded
function resbs_add_fontawesome_fallback() {
    if (!wp_style_is('font-awesome', 'enqueued') && !wp_style_is('font-awesome', 'done')) {
        $fontawesome_url = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
        $fontawesome_integrity = 'sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==';
        echo '<link rel="stylesheet" href="' . esc_url($fontawesome_url) . '" integrity="' . esc_attr($fontawesome_integrity) . '" crossorigin="anonymous" referrerpolicy="no-referrer" />' . "\n";
    }
}
add_action('wp_head', 'resbs_add_fontawesome_fallback', 1);

// Load main functionality
// Load main functions file
$functions_file = RESBS_PATH . 'includes/functions.php';
if (file_exists($functions_file)) {
    require_once $functions_file;
} else {
    wp_die(esc_html__('RealEstate Booking Suite: Required file missing. Please reinstall the plugin.', 'realestate-booking-suite'));
}

// Load contact messages functionality
$contact_messages_file = RESBS_PATH . 'includes/class-resbs-contact-messages.php';
if (file_exists($contact_messages_file)) {
    require_once $contact_messages_file;
}

// Load admin contact messages functionality
$admin_contact_file = RESBS_PATH . 'includes/class-resbs-admin-contact-messages.php';
if (file_exists($admin_contact_file)) {
    require_once $admin_contact_file;
}

// Load email handler functionality
$email_handler_file = RESBS_PATH . 'includes/class-resbs-email-handler.php';
if (file_exists($email_handler_file)) {
    require_once $email_handler_file;
}

// Load enhanced settings functionality (NEW ESTATIK-STYLE)
$enhanced_settings_file = RESBS_PATH . 'includes/class-resbs-enhanced-settings.php';
if (file_exists($enhanced_settings_file)) {
    require_once $enhanced_settings_file;
    new RESBS_Enhanced_Settings();
}

// Load Demo Importer
$demo_importer_file = RESBS_PATH . 'includes/class-resbs-demo-importer.php';
if (file_exists($demo_importer_file)) {
    require_once $demo_importer_file;
    new RESBS_Demo_Importer();
}

// Load simple archive handler
$simple_archive_file = RESBS_PATH . 'includes/class-resbs-simple-archive.php';
if (file_exists($simple_archive_file)) {
    require_once $simple_archive_file;
}

// Load user role management (safe role assignment)
$user_roles_file = RESBS_PATH . 'includes/class-resbs-user-roles.php';
if (file_exists($user_roles_file)) {
    require_once $user_roles_file;
}

// Prevent unverified users from logging in (if email verification is enabled)
// This hook catches ALL authentication attempts (wp_signon, wp_authenticate, etc.)
function resbs_check_email_verification($user, $username = null, $password = null) {
    // Only check if email verification is enabled
    $enable_email_verification = get_option('resbs_enable_email_verification', 1);
    
    if (!$enable_email_verification) {
        return $user; // Email verification disabled, allow login
    }
    
    // If user is a WP_Error, return it (authentication already failed)
    if (is_wp_error($user)) {
        return $user;
    }
    
    // If user is null or not a WP_User object, return as-is
    if (!$user || !is_a($user, 'WP_User')) {
        return $user;
    }
    
    // Skip check for administrators (they can always log in)
    if (user_can($user, 'manage_options')) {
        return $user;
    }
    
    // Check if user's email is verified
    $email_verified = get_user_meta($user->ID, 'resbs_email_verified', true);
    
    // Check if user has a verification token (created through plugin registration)
    $verification_token = get_user_meta($user->ID, 'resbs_verification_token', true);
    
    // If meta doesn't exist (old user created before email verification), treat as verified
    if ($email_verified === '' || $email_verified === false) {
        // Grandfather in existing users - mark them as verified
        update_user_meta($user->ID, 'resbs_email_verified', '1');
        return $user;
    }
    
    // If user was created from WordPress admin (no verification token), auto-verify them
    // Also verify if email_verified is '0' (unverified) but no token exists
    if (empty($verification_token)) {
        // User created from admin panel or has no token - auto-verify
        update_user_meta($user->ID, 'resbs_email_verified', '1');
        return $user;
    }
    
    // If not verified, prevent login
    if ($email_verified !== '1') {
        // Remove auth cookie if set
        wp_clear_auth_cookie();
        
        // Return error
        return new WP_Error(
            'email_not_verified',
            esc_html__('Error: Your email address has not been verified. Please check your email and click the verification link to activate your account.', 'realestate-booking-suite')
        );
    }
    
    return $user;
}
// Hook into multiple authentication points to catch all login methods
add_filter('wp_authenticate_user', 'resbs_check_email_verification', 99, 2);
add_filter('authenticate', 'resbs_check_email_verification', 99, 3);

/**
 * Auto-verify users created from WordPress admin panel
 * This ensures users created manually don't get blocked by email verification
 */
function resbs_auto_verify_admin_created_users($user_id) {
    // Check if email verification is enabled
    $enable_email_verification = get_option('resbs_enable_email_verification', 1);
    
    if (!$enable_email_verification) {
        return; // Email verification disabled, no need to verify
    }
    
    // Check if user already has verification status
    $email_verified = get_user_meta($user_id, 'resbs_email_verified', true);
    $verification_token = get_user_meta($user_id, 'resbs_verification_token', true);
    
    // If user was created from admin (no token) and not already verified, auto-verify
    if (empty($verification_token) && $email_verified !== '1') {
        update_user_meta($user_id, 'resbs_email_verified', '1');
    }
}
add_action('user_register', 'resbs_auto_verify_admin_created_users', 10, 1);
add_action('edit_user_profile_update', 'resbs_auto_verify_admin_created_users', 10, 1);

/**
 * Quick fix: Verify all existing users created from admin panel
 * Access via: ?resbs_verify_all_users=1&_wpnonce=xxx (admin only)
 * Or verify specific user: ?resbs_verify_user=username&_wpnonce=xxx (admin only)
 */
function resbs_verify_all_admin_users() {
    // Only allow admins
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $verified_count = 0;
    $message = '';
    $action_taken = false;
    
    // Verify specific user
    if (isset($_GET['resbs_verify_user']) && !empty($_GET['resbs_verify_user'])) {
        // Verify nonce for security
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_verify_user')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>';
                echo esc_html__('Security check failed. Please try again.', 'realestate-booking-suite');
                echo '</p></div>';
            });
            return;
        }
        
        $username = sanitize_user($_GET['resbs_verify_user']);
        $user = get_user_by('login', $username);
        
        if (!$user) {
            $user = get_user_by('email', $username);
        }
        
        if ($user) {
            update_user_meta($user->ID, 'resbs_email_verified', '1');
            delete_user_meta($user->ID, 'resbs_verification_token');
            delete_user_meta($user->ID, 'resbs_verification_expires');
            $verified_count = 1;
            $message = sprintf(__('User "%s" has been verified successfully!', 'realestate-booking-suite'), esc_html($user->user_login));
            $action_taken = true;
        } else {
            $message = sprintf(__('User "%s" not found.', 'realestate-booking-suite'), esc_html($username));
            $action_taken = true;
        }
    }
    // Verify all users
    if (isset($_GET['resbs_verify_all_users'])) {
        $verify_all_param = sanitize_text_field($_GET['resbs_verify_all_users']);
        if ($verify_all_param !== '1') {
            return;
        }
        // Verify nonce for security
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
        if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_verify_all_users')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>';
                echo esc_html__('Security check failed. Please try again.', 'realestate-booking-suite');
                echo '</p></div>';
            });
            return;
        }
        
        // Get all users
        $users = get_users();
        
        foreach ($users as $user) {
            // Skip admins (they can always login)
            if (user_can($user->ID, 'manage_options')) {
                continue;
            }
            
            $email_verified = get_user_meta($user->ID, 'resbs_email_verified', true);
            $verification_token = get_user_meta($user->ID, 'resbs_verification_token', true);
            
            // If user was created from admin (no token) and not verified, verify them
            if (empty($verification_token) && $email_verified !== '1') {
                update_user_meta($user->ID, 'resbs_email_verified', '1');
                $verified_count++;
            } elseif ($email_verified === '' || $email_verified === false) {
                // Also verify users with no meta at all
                update_user_meta($user->ID, 'resbs_email_verified', '1');
                $verified_count++;
            } elseif ($email_verified === '0') {
                // Force verify users marked as unverified
                update_user_meta($user->ID, 'resbs_email_verified', '1');
                delete_user_meta($user->ID, 'resbs_verification_token');
                delete_user_meta($user->ID, 'resbs_verification_expires');
                $verified_count++;
            }
        }
        
        $message = sprintf(__('Successfully verified %d user(s) created from admin panel.', 'realestate-booking-suite'), $verified_count);
        $action_taken = true;
    }
    
    // Show success message
    if ($action_taken && !empty($message)) {
        add_action('admin_notices', function() use ($message, $verified_count) {
            $notice_class = $verified_count > 0 ? 'notice-success' : 'notice-warning';
            echo '<div class="notice ' . esc_attr($notice_class) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
        });
    }
}
add_action('admin_init', 'resbs_verify_all_admin_users');

/**
 * Quick fix: Create default property status terms
 * Access via: ?resbs_create_status_terms=1&_wpnonce=xxx (admin only)
 */
function resbs_create_status_terms_on_demand() {
    // Only allow admins
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Only run if parameter is set
    if (!isset($_GET['resbs_create_status_terms'])) {
        return;
    }
    
    $create_terms_param = sanitize_text_field($_GET['resbs_create_status_terms']);
    if ($create_terms_param !== '1') {
        return;
    }
    
    // Verify nonce for security
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_create_status_terms')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible"><p>';
            echo esc_html__('Security check failed. Please try again.', 'realestate-booking-suite');
            echo '</p></div>';
        });
        return;
    }
    
    // Create default terms
    resbs_create_default_property_statuses();
    
    // Show success message
    add_action('admin_notices', function() {
        echo '<div class="notice notice-success is-dismissible"><p>';
        echo esc_html__('Default property status terms have been created successfully!', 'realestate-booking-suite');
        echo '</p></div>';
    });
}
add_action('admin_init', 'resbs_create_status_terms_on_demand');

/**
 * Fix existing properties: Convert array features/amenities to strings
 * Access via: ?resbs_fix_property_arrays=1&_wpnonce=xxx (admin only)
 */
function resbs_fix_property_arrays() {
    // Only allow admins
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Only run if parameter is set
    if (!isset($_GET['resbs_fix_property_arrays'])) {
        return;
    }
    
    $fix_arrays_param = sanitize_text_field($_GET['resbs_fix_property_arrays']);
    if ($fix_arrays_param !== '1') {
        return;
    }
    
    // Verify nonce for security
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    if (empty($nonce) || !wp_verify_nonce($nonce, 'resbs_fix_property_arrays')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible"><p>';
            echo esc_html__('Security check failed. Please try again.', 'realestate-booking-suite');
            echo '</p></div>';
        });
        return;
    }
    
    $fixed_count = 0;
    
    // Get all properties
    $properties = get_posts(array(
        'post_type' => 'property',
        'posts_per_page' => -1,
        'post_status' => 'any'
    ));
    
    foreach ($properties as $property) {
        $fixed = false;
        
        // Fix features
        $features = get_post_meta($property->ID, '_property_features', true);
        if (is_array($features)) {
            $features_string = implode(', ', array_filter(array_map(function($item) {
                if (is_array($item)) {
                    return implode(', ', array_filter(array_map('trim', $item)));
                }
                return trim((string)$item);
            }, $features)));
            update_post_meta($property->ID, '_property_features', $features_string);
            $fixed = true;
        }
        
        // Fix amenities
        $amenities = get_post_meta($property->ID, '_property_amenities', true);
        if (is_array($amenities)) {
            $amenities_string = implode(', ', array_filter(array_map(function($item) {
                if (is_array($item)) {
                    return implode(', ', array_filter(array_map('trim', $item)));
                }
                return trim((string)$item);
            }, $amenities)));
            update_post_meta($property->ID, '_property_amenities', $amenities_string);
            $fixed = true;
        }
        
        if ($fixed) {
            $fixed_count++;
        }
    }
    
    // Show success message
    add_action('admin_notices', function() use ($fixed_count) {
        echo '<div class="notice notice-success is-dismissible"><p>';
        echo esc_html(sprintf(__('Fixed %d property(ies) - converted array data to strings.', 'realestate-booking-suite'), $fixed_count));
        echo '</p></div>';
    });
}
add_action('admin_init', 'resbs_fix_property_arrays');

/**
 * Get currency symbol
 * @return string Currency symbol
 */
function resbs_get_currency_symbol() {
    // Get currency setting from WordPress options
    $currency = sanitize_text_field(get_option('resbs_currency_symbol', '$'));
    return $currency;
}

/**
 * Format price with currency symbol
 * @param float|string $price The price to format
 * @param int $decimals Number of decimal places (default 0)
 * @return string Formatted price with currency symbol
 */
function resbs_format_price($price, $decimals = 0) {
    if (empty($price) || $price === 0) {
        return '';
    }
    
    $currency_symbol = resbs_get_currency_symbol();
    $formatted_number = number_format(floatval($price), $decimals, '.', ',');
    
    // Get currency position from plugin settings
    $currency_position = get_option('resbs_currency_position', 'left');
    
    switch ($currency_position) {
        case 'left':
            return $currency_symbol . $formatted_number;
        case 'right':
            return $formatted_number . $currency_symbol;
        case 'left_space':
            return $currency_symbol . ' ' . $formatted_number;
        case 'right_space':
            return $formatted_number . ' ' . $currency_symbol;
        default:
            return $currency_symbol . $formatted_number;
    }
}

