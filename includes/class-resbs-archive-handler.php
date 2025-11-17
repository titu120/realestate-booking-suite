<?php
/**
 * Archive Handler for Real Estate Properties
 * 
 * Handles property archive pages with full customization control
 * 
 * @package RealEstateBookingSuite
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Archive_Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_archive_assets'));
        add_filter('template_include', array($this, 'custom_archive_template'), 99);
        add_action('wp_head', array($this, 'add_archive_meta'));
    }
    
    /**
     * Initialize archive handler
     */
    public function init() {
        // Add rewrite rules for custom archive pages
        add_rewrite_rule(
            '^properties/?$',
            'index.php?post_type=resbs_property&archive_view=grid',
            'top'
        );
        
        add_rewrite_rule(
            '^properties/list/?$',
            'index.php?post_type=resbs_property&archive_view=list',
            'top'
        );
        
        add_rewrite_rule(
            '^properties/map/?$',
            'index.php?post_type=resbs_property&archive_view=map',
            'top'
        );
        
        // Add query vars
        add_filter('query_vars', array($this, 'add_query_vars'));
        
        // Handle archive display
        add_action('pre_get_posts', array($this, 'modify_archive_query'));
    }
    
    /**
     * Add custom query variables
     */
    public function add_query_vars($vars) {
        $vars[] = 'archive_view';
        $vars[] = 'property_layout';
        $vars[] = 'property_sort';
        return $vars;
    }
    
    /**
     * Modify archive query
     */
    public function modify_archive_query($query) {
        if (!is_admin() && $query->is_main_query()) {
            if (is_post_type_archive('resbs_property')) {
                // Set posts per page
                $posts_per_page = get_option('resbs_properties_per_page', 12);
                $query->set('posts_per_page', $posts_per_page);
                
                // Handle sorting
                $sort = get_query_var('property_sort') ?: get_option('resbs_default_sort', 'date');
                $this->apply_sorting($query, $sort);
                
                // Handle meta query for filters
                $meta_query = $this->build_meta_query();
                if (!empty($meta_query)) {
                    $query->set('meta_query', $meta_query);
                }
            }
        }
    }
    
    /**
     * Apply sorting to query
     */
    private function apply_sorting($query, $sort) {
        switch ($sort) {
            case 'price_low':
                $query->set('meta_key', 'resbs_property_price');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'ASC');
                break;
            case 'price_high':
                $query->set('meta_key', 'resbs_property_price');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;
            case 'newest':
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
                break;
            case 'oldest':
                $query->set('orderby', 'date');
                $query->set('order', 'ASC');
                break;
            case 'title':
                $query->set('orderby', 'title');
                $query->set('order', 'ASC');
                break;
            default:
                $query->set('orderby', 'date');
                $query->set('order', 'DESC');
        }
    }
    
    /**
     * Build meta query for filters
     */
    private function build_meta_query() {
        $meta_query = array();
        
        // Price range filter
        if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
            $meta_query[] = array(
                'key' => 'resbs_property_price',
                'value' => intval($_GET['min_price']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
            $meta_query[] = array(
                'key' => 'resbs_property_price',
                'value' => intval($_GET['max_price']),
                'compare' => '<=',
                'type' => 'NUMERIC'
            );
        }
        
        // Property type filter
        if (isset($_GET['property_type']) && !empty($_GET['property_type'])) {
            $meta_query[] = array(
                'key' => 'resbs_property_type',
                'value' => sanitize_text_field($_GET['property_type']),
                'compare' => '='
            );
        }
        
        // Bedrooms filter
        if (isset($_GET['bedrooms']) && !empty($_GET['bedrooms'])) {
            $meta_query[] = array(
                'key' => 'resbs_property_bedrooms',
                'value' => intval($_GET['bedrooms']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        // Bathrooms filter
        if (isset($_GET['bathrooms']) && !empty($_GET['bathrooms'])) {
            $meta_query[] = array(
                'key' => 'resbs_property_bathrooms',
                'value' => intval($_GET['bathrooms']),
                'compare' => '>=',
                'type' => 'NUMERIC'
            );
        }
        
        // Status filter
        if (isset($_GET['status']) && !empty($_GET['status'])) {
            $meta_query[] = array(
                'key' => 'resbs_property_status',
                'value' => sanitize_text_field($_GET['status']),
                'compare' => '='
            );
        }
        
        return $meta_query;
    }
    
    /**
     * Enqueue archive-specific assets
     */
    public function enqueue_archive_assets() {
        if (is_post_type_archive('resbs_property') || is_tax('resbs_property_category')) {
            wp_enqueue_style(
                'resbs-archive',
                RESBS_URL . 'assets/css/archive.css',
                array(),
                '1.0.0'
            );
            
            wp_enqueue_script(
                'resbs-archive',
                RESBS_URL . 'assets/js/archive.js',
                array('jquery'),
                '1.0.0',
                true
            );
            
            // Localize script for AJAX
            wp_localize_script('resbs-archive', 'resbs_archive', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('resbs_archive_nonce'),
                'loading_text' => __('Loading...', 'realestate-booking-suite'),
                'no_results_text' => __('No properties found.', 'realestate-booking-suite')
            ));
        }
    }
    
    /**
     * Custom archive template
     */
    public function custom_archive_template($template) {
        if (is_post_type_archive('resbs_property') || is_tax('resbs_property_category')) {
            // Use simple archive template
            $simple_template = RESBS_PATH . 'templates/simple-archive.php';
            if (file_exists($simple_template)) {
                return $simple_template;
            }
        }
        
        return $template;
    }
    
    /**
     * Add archive meta tags
     */
    public function add_archive_meta() {
        if (is_post_type_archive('resbs_property')) {
            echo '<meta name="description" content="' . esc_attr(get_option('resbs_archive_meta_description', 'Browse our collection of premium real estate properties.')) . '">';
            echo '<meta name="keywords" content="' . esc_attr(get_option('resbs_archive_meta_keywords', 'real estate, properties, homes, apartments, condos')) . '">';
        }
    }
    
    /**
     * Get archive layout
     */
    public static function get_archive_layout() {
        $layout = get_query_var('archive_view') ?: get_option('resbs_default_archive_layout', 'grid');
        return sanitize_text_field($layout);
    }
    
    /**
     * Get archive sort option
     */
    public static function get_archive_sort() {
        $sort = get_query_var('property_sort') ?: get_option('resbs_default_sort', 'date');
        return sanitize_text_field($sort);
    }
    
    /**
     * Render archive filters
     */
    public static function render_archive_filters() {
        $template_path = RESBS_PATH . 'templates/archive-filters.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
    }
    
    /**
     * Render archive pagination
     */
    public static function render_archive_pagination() {
        $template_path = RESBS_PATH . 'templates/archive-pagination.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback to WordPress pagination
            the_posts_pagination(array(
                'mid_size' => 2,
                'prev_text' => __('Previous', 'realestate-booking-suite'),
                'next_text' => __('Next', 'realestate-booking-suite'),
            ));
        }
    }
    
    /**
     * Get property grid classes
     */
    public static function get_property_grid_classes() {
        $layout = self::get_archive_layout();
        $classes = array('resbs-property-grid');
        
        switch ($layout) {
            case 'list':
                $classes[] = 'resbs-layout-list';
                break;
            case 'map':
                $classes[] = 'resbs-layout-map';
                break;
            default:
                $classes[] = 'resbs-layout-grid';
                break;
        }
        
        // Add responsive classes
        $columns = get_option('resbs_grid_columns', 3);
        $classes[] = 'resbs-columns-' . $columns;
        
        return implode(' ', $classes);
    }
    
    /**
     * AJAX handler for archive filters
     */
    public function ajax_filter_properties() {
        check_ajax_referer('resbs_archive_nonce', 'nonce');
        
        $args = array(
            'post_type' => 'resbs_property',
            'post_status' => 'publish',
            'posts_per_page' => intval($_POST['per_page']) ?: 12,
            'paged' => intval($_POST['page']) ?: 1,
        );
        
        // Apply filters
        $meta_query = $this->build_meta_query();
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        // Apply sorting
        $sort = sanitize_text_field($_POST['sort']) ?: 'date';
        $this->apply_sorting((object)$args, $sort);
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            ob_start();
            while ($query->have_posts()) {
                $query->the_post();
                $this->render_property_card();
            }
            wp_reset_postdata();
            
            $html = ob_get_clean();
            
            wp_send_json_success(array(
                'html' => $html,
                'found_posts' => $query->found_posts,
                'max_pages' => $query->max_num_pages,
                'current_page' => $query->get('paged')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('No properties found.', 'realestate-booking-suite')
            ));
        }
    }
    
    /**
     * Render individual property card
     */
    private function render_property_card() {
        $template_path = RESBS_PATH . 'templates/property-card.php';
        if (file_exists($template_path)) {
            include $template_path;
        } else {
            // Fallback card template
            ?>
            <div class="resbs-property-card">
                <div class="resbs-property-image">
                    <?php if (has_post_thumbnail()): ?>
                        <?php the_post_thumbnail('medium'); ?>
                    <?php else: ?>
                        <div class="resbs-no-image">No Image</div>
                    <?php endif; ?>
                </div>
                <div class="resbs-property-content">
                    <h3 class="resbs-property-title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    <div class="resbs-property-price">
                        <?php echo esc_html(get_post_meta(get_the_ID(), 'resbs_property_price', true)); ?>
                    </div>
                    <div class="resbs-property-meta">
                        <?php
                        $bedrooms = get_post_meta(get_the_ID(), 'resbs_property_bedrooms', true);
                        $bathrooms = get_post_meta(get_the_ID(), 'resbs_property_bathrooms', true);
                        $area = get_post_meta(get_the_ID(), 'resbs_property_area', true);
                        
                        if ($bedrooms) echo '<span>' . $bedrooms . ' beds</span>';
                        if ($bathrooms) echo '<span>' . $bathrooms . ' baths</span>';
                        if ($area) echo '<span>' . $area . ' sq ft</span>';
                        ?>
                    </div>
                </div>
            </div>
            <?php
        }
    }
}

// Initialize the archive handler
new RESBS_Archive_Handler();

// AJAX handlers
add_action('wp_ajax_resbs_filter_properties', array(new RESBS_Archive_Handler(), 'ajax_filter_properties'));
add_action('wp_ajax_nopriv_resbs_filter_properties', array(new RESBS_Archive_Handler(), 'ajax_filter_properties'));