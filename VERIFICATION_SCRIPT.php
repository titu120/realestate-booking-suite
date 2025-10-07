<?php
/**
 * Plugin Verification Script
 * 
 * This script verifies that all required files exist and basic functionality works
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Verification {

    private $errors = array();
    private $warnings = array();
    private $success = array();

    public function __construct() {
        $this->run_verification();
    }

    public function run_verification() {
        echo "<h1>RealEstate Booking Suite - Verification Report</h1>";
        echo "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .success { color: green; }
            .warning { color: orange; }
            .error { color: red; }
            .section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; }
        </style>";

        $this->check_required_files();
        $this->check_php_syntax();
        $this->check_wordpress_compatibility();
        $this->check_dependencies();
        $this->check_database_structure();
        $this->check_security_measures();
        $this->check_translation_readiness();
        $this->check_performance_optimizations();

        $this->display_results();
    }

    private function check_required_files() {
        echo "<div class='section'>";
        echo "<h2>1. Required Files Check</h2>";

        $required_files = array(
            'realestate-booking-suite.php' => 'Main plugin file',
            'includes/functions.php' => 'Functions file',
            'includes/class-resbs-cpt.php' => 'Custom Post Type class',
            'includes/class-resbs-metabox.php' => 'Metabox class',
            'includes/class-resbs-settings.php' => 'Settings class',
            'includes/class-resbs-frontend.php' => 'Frontend class',
            'includes/class-resbs-search.php' => 'Search class',
            'includes/class-resbs-woocommerce.php' => 'WooCommerce integration',
            'includes/class-resbs-elementor.php' => 'Elementor integration',
            'includes/class-resbs-widgets.php' => 'Widgets class',
            'includes/class-resbs-badges.php' => 'Badges class',
            'includes/class-resbs-maps.php' => 'Maps class',
            'includes/class-resbs-favorites.php' => 'Favorites class',
            'includes/class-resbs-contact-settings.php' => 'Contact settings',
            'includes/class-resbs-contact-widget.php' => 'Contact widget',
            'includes/class-resbs-infinite-scroll.php' => 'Infinite scroll',
            'includes/class-resbs-security.php' => 'Security helper',
            'includes/class-resbs-email-manager.php' => 'Email manager',
            'includes/class-resbs-search-alerts.php' => 'Search alerts',
            'includes/class-resbs-quickview.php' => 'Quick view',
            'includes/class-resbs-shortcodes.php' => 'Shortcodes',
            'includes/class-resbs-shortcode-ajax.php' => 'Shortcode AJAX',
            'assets/css/style.css' => 'Main CSS',
            'assets/css/layouts.css' => 'Layouts CSS',
            'assets/css/shortcodes.css' => 'Shortcodes CSS',
            'assets/css/quickview.css' => 'Quick view CSS',
            'assets/css/maps.css' => 'Maps CSS',
            'assets/css/favorites.css' => 'Favorites CSS',
            'assets/css/badges.css' => 'Badges CSS',
            'assets/css/infinite-scroll.css' => 'Infinite scroll CSS',
            'assets/css/contact-widget.css' => 'Contact widget CSS',
            'assets/css/single-property-responsive.css' => 'Single property CSS',
            'assets/css/admin.css' => 'Admin CSS',
            'assets/js/main.js' => 'Main JavaScript',
            'assets/js/layouts.js' => 'Layouts JavaScript',
            'assets/js/shortcodes.js' => 'Shortcodes JavaScript',
            'assets/js/quickview.js' => 'Quick view JavaScript',
            'assets/js/maps.js' => 'Maps JavaScript',
            'assets/js/favorites.js' => 'Favorites JavaScript',
            'assets/js/badges.js' => 'Badges JavaScript',
            'assets/js/infinite-scroll.js' => 'Infinite scroll JavaScript',
            'assets/js/admin.js' => 'Admin JavaScript',
            'includes/elementor/class-resbs-property-grid-widget.php' => 'Elementor Grid Widget',
            'includes/elementor/class-resbs-property-carousel-widget.php' => 'Elementor Carousel Widget',
            'templates/single-property.php' => 'Single property template',
            'templates/property-grid.php' => 'Property grid template',
            'templates/property-badges.php' => 'Property badges template'
        );

        foreach ($required_files as $file => $description) {
            $file_path = RESBS_PATH . $file;
            if (file_exists($file_path)) {
                $this->success[] = "✓ {$description}: {$file}";
            } else {
                $this->errors[] = "✗ Missing: {$description} ({$file})";
            }
        }

        echo "</div>";
    }

    private function check_php_syntax() {
        echo "<div class='section'>";
        echo "<h2>2. PHP Syntax Check</h2>";

        $php_files = glob(RESBS_PATH . '**/*.php', GLOB_BRACE);
        
        foreach ($php_files as $file) {
            $output = array();
            $return_var = 0;
            exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $return_var);
            
            if ($return_var === 0) {
                $this->success[] = "✓ PHP syntax OK: " . basename($file);
            } else {
                $this->errors[] = "✗ PHP syntax error in: " . basename($file) . " - " . implode(' ', $output);
            }
        }

        echo "</div>";
    }

    private function check_wordpress_compatibility() {
        echo "<div class='section'>";
        echo "<h2>3. WordPress Compatibility Check</h2>";

        // Check WordPress version
        global $wp_version;
        if (version_compare($wp_version, '5.2', '>=')) {
            $this->success[] = "✓ WordPress version compatible: {$wp_version}";
        } else {
            $this->errors[] = "✗ WordPress version too old: {$wp_version} (requires 5.2+)";
        }

        // Check PHP version
        if (version_compare(PHP_VERSION, '7.1', '>=')) {
            $this->success[] = "✓ PHP version compatible: " . PHP_VERSION;
        } else {
            $this->errors[] = "✗ PHP version too old: " . PHP_VERSION . " (requires 7.1+)";
        }

        // Check required WordPress functions
        $required_functions = array(
            'add_action',
            'add_filter',
            'wp_enqueue_script',
            'wp_enqueue_style',
            'register_post_type',
            'register_taxonomy',
            'add_shortcode',
            'wp_ajax',
            'wp_create_nonce',
            'wp_verify_nonce'
        );

        foreach ($required_functions as $function) {
            if (function_exists($function)) {
                $this->success[] = "✓ WordPress function available: {$function}";
            } else {
                $this->errors[] = "✗ WordPress function missing: {$function}";
            }
        }

        echo "</div>";
    }

    private function check_dependencies() {
        echo "<div class='section'>";
        echo "<h2>4. Dependencies Check</h2>";

        // Check WooCommerce
        if (class_exists('WooCommerce')) {
            $this->success[] = "✓ WooCommerce is active";
        } else {
            $this->warnings[] = "⚠ WooCommerce not active (optional dependency)";
        }

        // Check Elementor
        if (did_action('elementor/loaded')) {
            $this->success[] = "✓ Elementor is active";
        } else {
            $this->warnings[] = "⚠ Elementor not active (optional dependency)";
        }

        // Check required PHP extensions
        $required_extensions = array('json', 'mbstring', 'curl');
        foreach ($required_extensions as $extension) {
            if (extension_loaded($extension)) {
                $this->success[] = "✓ PHP extension available: {$extension}";
            } else {
                $this->errors[] = "✗ PHP extension missing: {$extension}";
            }
        }

        echo "</div>";
    }

    private function check_database_structure() {
        echo "<div class='section'>";
        echo "<h2>5. Database Structure Check</h2>";

        global $wpdb;

        // Check if custom post type exists
        $post_type_exists = post_type_exists('property');
        if ($post_type_exists) {
            $this->success[] = "✓ Custom post type 'property' registered";
        } else {
            $this->errors[] = "✗ Custom post type 'property' not registered";
        }

        // Check if taxonomies exist
        $taxonomies = array('property_type', 'property_status', 'property_location');
        foreach ($taxonomies as $taxonomy) {
            if (taxonomy_exists($taxonomy)) {
                $this->success[] = "✓ Taxonomy '{$taxonomy}' registered";
            } else {
                $this->errors[] = "✗ Taxonomy '{$taxonomy}' not registered";
            }
        }

        echo "</div>";
    }

    private function check_security_measures() {
        echo "<div class='section'>";
        echo "<h2>6. Security Measures Check</h2>";

        // Check for direct access prevention
        $files_to_check = array(
            'includes/class-resbs-cpt.php',
            'includes/class-resbs-metabox.php',
            'includes/class-resbs-settings.php'
        );

        foreach ($files_to_check as $file) {
            $file_path = RESBS_PATH . $file;
            if (file_exists($file_path)) {
                $content = file_get_contents($file_path);
                if (strpos($content, "if (!defined('ABSPATH'))") !== false) {
                    $this->success[] = "✓ Direct access prevention in: " . basename($file);
                } else {
                    $this->warnings[] = "⚠ No direct access prevention in: " . basename($file);
                }
            }
        }

        // Check for nonce usage
        $js_files = array(
            'assets/js/main.js',
            'assets/js/shortcodes.js',
            'assets/js/quickview.js'
        );

        foreach ($js_files as $file) {
            $file_path = RESBS_PATH . $file;
            if (file_exists($file_path)) {
                $content = file_get_contents($file_path);
                if (strpos($content, 'nonce') !== false) {
                    $this->success[] = "✓ Nonce usage found in: " . basename($file);
                } else {
                    $this->warnings[] = "⚠ No nonce usage found in: " . basename($file);
                }
            }
        }

        echo "</div>";
    }

    private function check_translation_readiness() {
        echo "<div class='section'>";
        echo "<h2>7. Translation Readiness Check</h2>";

        // Check for translation functions
        $php_files = glob(RESBS_PATH . 'includes/*.php');
        $translation_functions = array('__', '_e', 'esc_html__', 'esc_attr__', 'esc_html_e', 'esc_attr_e');
        
        foreach ($php_files as $file) {
            $content = file_get_contents($file);
            $has_translations = false;
            
            foreach ($translation_functions as $func) {
                if (strpos($content, $func) !== false) {
                    $has_translations = true;
                    break;
                }
            }
            
            if ($has_translations) {
                $this->success[] = "✓ Translation functions found in: " . basename($file);
            } else {
                $this->warnings[] = "⚠ No translation functions in: " . basename($file);
            }
        }

        // Check text domain consistency
        $main_file = RESBS_PATH . 'realestate-booking-suite.php';
        if (file_exists($main_file)) {
            $content = file_get_contents($main_file);
            if (strpos($content, 'realestate-booking-suite') !== false) {
                $this->success[] = "✓ Text domain defined in main file";
            } else {
                $this->errors[] = "✗ Text domain not defined in main file";
            }
        }

        echo "</div>";
    }

    private function check_performance_optimizations() {
        echo "<div class='section'>";
        echo "<h2>8. Performance Optimizations Check</h2>";

        // Check for minified assets
        $css_files = glob(RESBS_PATH . 'assets/css/*.css');
        foreach ($css_files as $file) {
            $content = file_get_contents($file);
            $size = filesize($file);
            
            if ($size < 100000) { // Less than 100KB
                $this->success[] = "✓ CSS file size reasonable: " . basename($file) . " ({$size} bytes)";
            } else {
                $this->warnings[] = "⚠ CSS file size large: " . basename($file) . " ({$size} bytes)";
            }
        }

        $js_files = glob(RESBS_PATH . 'assets/js/*.js');
        foreach ($js_files as $file) {
            $content = file_get_contents($file);
            $size = filesize($file);
            
            if ($size < 100000) { // Less than 100KB
                $this->success[] = "✓ JavaScript file size reasonable: " . basename($file) . " ({$size} bytes)";
            } else {
                $this->warnings[] = "⚠ JavaScript file size large: " . basename($file) . " ({$size} bytes)";
            }
        }

        echo "</div>";
    }

    private function display_results() {
        echo "<div class='section'>";
        echo "<h2>Verification Results</h2>";

        echo "<h3 class='success'>Successes (" . count($this->success) . ")</h3>";
        foreach ($this->success as $success) {
            echo "<p class='success'>{$success}</p>";
        }

        if (!empty($this->warnings)) {
            echo "<h3 class='warning'>Warnings (" . count($this->warnings) . ")</h3>";
            foreach ($this->warnings as $warning) {
                echo "<p class='warning'>{$warning}</p>";
            }
        }

        if (!empty($this->errors)) {
            echo "<h3 class='error'>Errors (" . count($this->errors) . ")</h3>";
            foreach ($this->errors as $error) {
                echo "<p class='error'>{$error}</p>";
            }
        }

        echo "<h3>Summary</h3>";
        echo "<p>Total Checks: " . (count($this->success) + count($this->warnings) + count($this->errors)) . "</p>";
        echo "<p class='success'>Passed: " . count($this->success) . "</p>";
        echo "<p class='warning'>Warnings: " . count($this->warnings) . "</p>";
        echo "<p class='error'>Errors: " . count($this->errors) . "</p>";

        if (empty($this->errors)) {
            echo "<h2 class='success'>✅ Plugin Verification PASSED</h2>";
            echo "<p>The plugin is ready for CodeCanyon submission!</p>";
        } else {
            echo "<h2 class='error'>❌ Plugin Verification FAILED</h2>";
            echo "<p>Please fix the errors before submission.</p>";
        }

        echo "</div>";
    }
}

// Run verification if accessed directly
if (isset($_GET['run_verification']) && current_user_can('manage_options')) {
    new RESBS_Verification();
} else {
    echo "<h1>RealEstate Booking Suite - Verification</h1>";
    echo "<p>To run the verification script, add ?run_verification=1 to the URL (admin access required).</p>";
}
?>
