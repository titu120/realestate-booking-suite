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
        // For block themes: Filter the_content to show our archive content
        add_filter('the_content', array($this, 'archive_content_filter'), 10);
    }

    
    /**
     * Initialize archive handler
     */
    public function init() {
        // Add rewrite rules for custom archive pages
        add_rewrite_rule(
            '^properties/?$',
            'index.php?post_type=property&archive_view=grid',
            'top'
        );
        
        add_rewrite_rule(
            '^properties/list/?$',
            'index.php?post_type=property&archive_view=list',
            'top'
        );
        
        add_rewrite_rule(
            '^properties/map/?$',
            'index.php?post_type=property&archive_view=map',
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
            if (is_post_type_archive('property')) {
                // Set posts per page
                $posts_per_page = get_option('resbs_properties_per_page', 12);
                $query->set('posts_per_page', $posts_per_page);
                
                // Handle sorting - sanitize and validate
                $sort_raw = get_query_var('property_sort');
                $sort = sanitize_text_field($sort_raw);
                $allowed_sorts = array('price_low', 'price_high', 'newest', 'oldest', 'title', 'date');
                if (!in_array($sort, $allowed_sorts, true)) {
                    $sort = get_option('resbs_default_sort', 'date');
                    $sort = sanitize_text_field($sort);
                    if (!in_array($sort, $allowed_sorts, true)) {
                        $sort = 'date';
                    }
                }
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
        // Validate sort value against whitelist
        $allowed_sorts = array('price_low', 'price_high', 'newest', 'oldest', 'title', 'date');
        if (!in_array($sort, $allowed_sorts, true)) {
            $sort = 'date';
        }
        
        switch ($sort) {
            case 'price_low':
                $query->set('meta_key', '_property_price');
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'ASC');
                break;
            case 'price_high':
                $query->set('meta_key', '_property_price');
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
     * 
     * Note: This method handles public GET parameters for archive filtering.
     * No nonce verification needed as this is for public-facing archive pages.
     * All inputs are sanitized to prevent injection attacks.
     * 
     * @param string $source Source of filter data: 'GET' for archive pages, 'POST' for AJAX requests
     * @return array Meta query array for WP_Query
     */
    private function build_meta_query($source = 'GET') {
        $meta_query = array();
        
        // Determine which superglobal to use and sanitize
        if ($source === 'POST') {
            $input = isset($_POST) ? wp_unslash($_POST) : array();
        } else {
            $input = isset($_GET) ? wp_unslash($_GET) : array();
        }
        
        // Price range filter - sanitize and validate (prices are floats)
        if (isset($input['min_price']) && !empty($input['min_price'])) {
            $min_price = is_numeric($input['min_price']) ? floatval($input['min_price']) : 0;
            if ($min_price > 0) {
                $meta_query[] = array(
                    'key' => '_property_price',
                    'value' => $min_price,
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }
        }
        
        if (isset($input['max_price']) && !empty($input['max_price'])) {
            $max_price = is_numeric($input['max_price']) ? floatval($input['max_price']) : 0;
            if ($max_price > 0) {
                $meta_query[] = array(
                    'key' => '_property_price',
                    'value' => $max_price,
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                );
            }
        }
        
        // Property type filter - use taxonomy, not meta
        // Note: Property type is a taxonomy, not a meta field, so this filter should use tax_query instead
        // Keeping for backward compatibility but should be migrated to tax_query
        
        // Bedrooms filter - support both min/max and single value for backward compatibility
        if (isset($input['min_bedrooms']) && !empty($input['min_bedrooms'])) {
            $min_bedrooms = is_numeric($input['min_bedrooms']) ? absint($input['min_bedrooms']) : 0;
            if ($min_bedrooms > 0) {
                $meta_query[] = array(
                    'key' => '_property_bedrooms',
                    'value' => $min_bedrooms,
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }
        }
        
        if (isset($input['max_bedrooms']) && !empty($input['max_bedrooms'])) {
            $max_bedrooms = is_numeric($input['max_bedrooms']) ? absint($input['max_bedrooms']) : 0;
            if ($max_bedrooms > 0) {
                $meta_query[] = array(
                    'key' => '_property_bedrooms',
                    'value' => $max_bedrooms,
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                );
            }
        }
        
        // Backward compatibility: support single 'bedrooms' parameter
        if (!isset($input['min_bedrooms']) && !isset($input['max_bedrooms']) && isset($input['bedrooms']) && !empty($input['bedrooms'])) {
            $bedrooms = is_numeric($input['bedrooms']) ? absint($input['bedrooms']) : 0;
            if ($bedrooms > 0) {
                $meta_query[] = array(
                    'key' => '_property_bedrooms',
                    'value' => $bedrooms,
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }
        }
        
        // Bathrooms filter - support both min/max and single value for backward compatibility
        if (isset($input['min_bathrooms']) && !empty($input['min_bathrooms'])) {
            $min_bathrooms = is_numeric($input['min_bathrooms']) ? floatval($input['min_bathrooms']) : 0;
            if ($min_bathrooms > 0) {
                $meta_query[] = array(
                    'key' => '_property_bathrooms',
                    'value' => $min_bathrooms,
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }
        }
        
        if (isset($input['max_bathrooms']) && !empty($input['max_bathrooms'])) {
            $max_bathrooms = is_numeric($input['max_bathrooms']) ? floatval($input['max_bathrooms']) : 0;
            if ($max_bathrooms > 0) {
                $meta_query[] = array(
                    'key' => '_property_bathrooms',
                    'value' => $max_bathrooms,
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                );
            }
        }
        
        // Backward compatibility: support single 'bathrooms' parameter
        if (!isset($input['min_bathrooms']) && !isset($input['max_bathrooms']) && isset($input['bathrooms']) && !empty($input['bathrooms'])) {
            $bathrooms = is_numeric($input['bathrooms']) ? floatval($input['bathrooms']) : 0;
            if ($bathrooms > 0) {
                $meta_query[] = array(
                    'key' => '_property_bathrooms',
                    'value' => $bathrooms,
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }
        }
        
        // Status filter - use taxonomy, not meta
        // Note: Property status is a taxonomy, not a meta field, so this filter should use tax_query instead
        // Keeping for backward compatibility but should be migrated to tax_query
        
        return $meta_query;
    }
    
    /**
     * Enqueue archive-specific assets
     */
    public function enqueue_archive_assets() {
        if (is_post_type_archive('property') || is_tax('property_type') || is_tax('property_status') || is_tax('property_location')) {
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
                'ajax_url' => esc_url(admin_url('admin-ajax.php')),
                'nonce' => esc_js(wp_create_nonce('resbs_archive_nonce')),
                'loading_text' => esc_js(__('Loading...', 'realestate-booking-suite')),
                'no_results_text' => esc_js(__('No properties found.', 'realestate-booking-suite'))
            ));
        }
    }
    
    /**
     * Custom archive template
     */
    public function custom_archive_template($template) {
        // Use template_include for archive (like Estatik does)
        // The template uses get_header()/get_footer() which WordPress handles automatically
        if (is_post_type_archive('property') || is_tax('property_type') || is_tax('property_status') || is_tax('property_location')) {
            // For classic themes AND block themes: Use custom template
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
        if (is_post_type_archive('property')) {
            echo '<meta name="description" content="' . esc_attr(get_option('resbs_archive_meta_description', 'Browse our collection of premium real estate properties.')) . '">';
            echo '<meta name="keywords" content="' . esc_attr(get_option('resbs_archive_meta_keywords', 'real estate, properties, homes, apartments, condos')) . '">';
        }
    }
    
    /**
     * Filter the_content for property archives in block themes
     * This allows block themes to handle header/footer while we inject our content
     */
    // REMOVED: archive_content_filter - using template_include instead

    
    /**
     * Get archive layout
     */
    public static function get_archive_layout() {
        $layout_raw = get_query_var('archive_view');
        $layout = sanitize_text_field($layout_raw);
        $allowed_layouts = array('grid', 'list', 'map');
        if (!in_array($layout, $allowed_layouts, true)) {
            $layout = get_option('resbs_default_archive_layout', 'grid');
            $layout = sanitize_text_field($layout);
            if (!in_array($layout, $allowed_layouts, true)) {
                $layout = 'grid';
            }
        }
        return $layout;
    }
    
    /**
     * Get archive sort option
     */
    public static function get_archive_sort() {
        $sort_raw = get_query_var('property_sort');
        $sort = sanitize_text_field($sort_raw);
        $allowed_sorts = array('price_low', 'price_high', 'newest', 'oldest', 'title', 'date');
        if (!in_array($sort, $allowed_sorts, true)) {
            $sort = get_option('resbs_default_sort', 'date');
            $sort = sanitize_text_field($sort);
            if (!in_array($sort, $allowed_sorts, true)) {
                $sort = 'date';
            }
        }
        return $sort;
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
        $classes[] = 'resbs-columns-' . sanitize_html_class($columns);
        
        return implode(' ', $classes);
    }
    
    /**
     * AJAX handler for archive filters
     * 
     * Security: Uses nonce verification for CSRF protection.
     * No capability check needed as this is a public-facing archive filter.
     * All inputs are sanitized to prevent injection attacks.
     */
    public function ajax_filter_properties() {
        // Security check: Verify nonce (no capability check needed for public archive filters)
        // This allows both logged-in and non-logged-in users to filter properties
        RESBS_Security::ajax_security_check('resbs_archive_nonce', '', false);
        
        // Sanitize and validate input parameters
        $posts_per_page = isset($_POST['per_page']) ? RESBS_Security::sanitize_int(wp_unslash($_POST['per_page']), 12) : 12;
        $paged = isset($_POST['page']) ? RESBS_Security::sanitize_int(wp_unslash($_POST['page']), 1) : 1;
        
        // Ensure reasonable limits
        $posts_per_page = min(max($posts_per_page, 1), 100); // Between 1 and 100
        $paged = max($paged, 1); // At least 1
        
        $args = array(
            'post_type' => 'property',
            'post_status' => 'publish',
            'posts_per_page' => $posts_per_page,
            'paged' => $paged,
        );
        
        // Apply filters (build_meta_query handles sanitization)
        // Use POST source for AJAX requests
        $meta_query = $this->build_meta_query('POST');
        if (!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
        }
        
        // Apply sorting - sanitize sort parameter
        $sort = isset($_POST['sort']) ? sanitize_text_field(wp_unslash($_POST['sort'])) : 'date';
        // Whitelist allowed sort values
        $allowed_sorts = array('price_low', 'price_high', 'newest', 'oldest', 'title', 'date');
        if (!in_array($sort, $allowed_sorts, true)) {
            $sort = 'date';
        }
        
        // Apply sorting directly to args array
        switch ($sort) {
            case 'price_low':
                $args['meta_key'] = '_property_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'ASC';
                break;
            case 'price_high':
                $args['meta_key'] = '_property_price';
                $args['orderby'] = 'meta_value_num';
                $args['order'] = 'DESC';
                break;
            case 'newest':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
            case 'oldest':
                $args['orderby'] = 'date';
                $args['order'] = 'ASC';
                break;
            case 'title':
                $args['orderby'] = 'title';
                $args['order'] = 'ASC';
                break;
            default:
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
        }
        
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
                'message' => esc_html__('No properties found.', 'realestate-booking-suite')
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
                        <?php 
                        $price = get_post_meta(get_the_ID(), '_property_price', true);
                        if ($price && is_numeric($price)) {
                            $currency_symbol = sanitize_text_field(get_option('resbs_currency_symbol', '$'));
                            $currency_position = sanitize_text_field(get_option('resbs_currency_position', 'before'));
                            $formatted_price = number_format(floatval($price), 2);
                            if ($currency_position === 'before') {
                                echo esc_html($currency_symbol . $formatted_price);
                            } else {
                                echo esc_html($formatted_price . $currency_symbol);
                            }
                        }
                        ?>
                    </div>
                    <div class="resbs-property-meta">
                        <?php
                        $bedrooms = get_post_meta(get_the_ID(), '_property_bedrooms', true);
                        $bathrooms = get_post_meta(get_the_ID(), '_property_bathrooms', true);
                        // Try multiple possible area meta keys
                        $area_size = get_post_meta(get_the_ID(), '_property_size', true);
                        $area_sqft = get_post_meta(get_the_ID(), '_property_area_sqft', true);
                        $area = !empty($area_size) ? $area_size : (!empty($area_sqft) ? $area_sqft : '');
                        
                        if (!empty($bedrooms)) {
                            $bedrooms_text = $bedrooms == 1 ? esc_html__('bed', 'realestate-booking-suite') : esc_html__('beds', 'realestate-booking-suite');
                            echo '<span>' . esc_html($bedrooms) . ' ' . $bedrooms_text . '</span>';
                        }
                        if (!empty($bathrooms)) {
                            $bathrooms_text = $bathrooms == 1 ? esc_html__('bath', 'realestate-booking-suite') : esc_html__('baths', 'realestate-booking-suite');
                            echo '<span>' . esc_html($bathrooms) . ' ' . $bathrooms_text . '</span>';
                        }
                        if (!empty($area)) {
                            echo '<span>' . esc_html($area) . ' ' . esc_html__('sq ft', 'realestate-booking-suite') . '</span>';
                        }
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