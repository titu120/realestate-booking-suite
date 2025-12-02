<?php
/**
 * Simple Archive Property Template
 * Block Theme Compatible
 * 
 * @package RealEstate_Booking_Suite
 */

// IMPORTANT: Check for reset BEFORE any output (including get_header())
// If reset parameter is present, redirect to clean URL and set all values to defaults
if (isset($_GET['reset']) && !empty($_GET['reset'])) {
    // Sanitize reset parameter for security
    $reset_param = sanitize_text_field($_GET['reset']);
    // Verify nonce for security (optional but recommended for state-changing operations)
    // For public archive pages, we'll use a simple validation
    // Get the archive page URL without any query parameters
    $archive_url = get_post_type_archive_link('property');
    if (!$archive_url) {
        // Fallback to home URL if archive link doesn't exist
        $archive_url = home_url('/');
    }
    // Use wp_safe_redirect to prevent open redirect vulnerabilities
    wp_safe_redirect($archive_url);
    exit;
}

// Now safe to output headers and content
// Use resbs_get_header() to ensure compatibility with both block and classic themes
resbs_get_header();
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">

<?php
// Note: AJAX functionality disabled for now to prevent errors
// wp_enqueue_script('resbs-dynamic-archive');

// SIMPLE WORKING FILTER APPROACH

// Sanitize all user input from $_GET
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$min_price = isset($_GET['min_price']) ? absint($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? absint($_GET['max_price']) : '';
$property_type_filter = isset($_GET['property_type']) ? sanitize_text_field($_GET['property_type']) : '';
$min_bedrooms = isset($_GET['min_bedrooms']) ? absint($_GET['min_bedrooms']) : '';
$max_bedrooms = isset($_GET['max_bedrooms']) ? absint($_GET['max_bedrooms']) : '';
$min_bathrooms = isset($_GET['min_bathrooms']) ? absint($_GET['min_bathrooms']) : '';
$max_bathrooms = isset($_GET['max_bathrooms']) ? absint($_GET['max_bathrooms']) : '';
$min_sqft = isset($_GET['min_sqft']) ? absint($_GET['min_sqft']) : '';
$max_sqft = isset($_GET['max_sqft']) ? absint($_GET['max_sqft']) : '';
$year_built = isset($_GET['year_built']) ? sanitize_text_field($_GET['year_built']) : '';
$property_status = isset($_GET['property_status']) ? sanitize_text_field($_GET['property_status']) : '';
$sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'date';
$paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;

// Build WP_Query arguments
// Get properties per page from Listings settings (dynamically applied)
$properties_per_page = resbs_get_properties_per_page();
$args = array(
    'post_type' => 'property',
    'post_status' => 'publish',
    'posts_per_page' => $properties_per_page,
    'paged' => $paged,
    'meta_query' => array(),
);

// Add search query
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

// Add price range filter
if (!empty($min_price) || !empty($max_price)) {
    $price_query = array(
        'key' => '_property_price',
        'type' => 'NUMERIC',
    );
    
    if (!empty($min_price) && !empty($max_price)) {
        $price_query['value'] = array($min_price, $max_price);
        $price_query['compare'] = 'BETWEEN';
    } elseif (!empty($min_price)) {
        $price_query['value'] = $min_price;
        $price_query['compare'] = '>=';
    } elseif (!empty($max_price)) {
        $price_query['value'] = $max_price;
        $price_query['compare'] = '<=';
    }
    
    $args['meta_query'][] = $price_query;
}

// Add bedrooms filter (min and max)
if (!empty($min_bedrooms) || !empty($max_bedrooms)) {
    $bedrooms_query = array(
        'key' => '_property_bedrooms',
        'type' => 'NUMERIC',
    );
    
    if (!empty($min_bedrooms) && !empty($max_bedrooms)) {
        $bedrooms_query['value'] = array($min_bedrooms, $max_bedrooms);
        $bedrooms_query['compare'] = 'BETWEEN';
    } elseif (!empty($min_bedrooms)) {
        $bedrooms_query['value'] = $min_bedrooms;
        $bedrooms_query['compare'] = '>=';
    } elseif (!empty($max_bedrooms)) {
        $bedrooms_query['value'] = $max_bedrooms;
        $bedrooms_query['compare'] = '<=';
    }
    
    $args['meta_query'][] = $bedrooms_query;
}

// Add bathrooms filter (min and max)
if (!empty($min_bathrooms) || !empty($max_bathrooms)) {
    $bathrooms_query = array(
        'key' => '_property_bathrooms',
        'type' => 'NUMERIC',
    );
    
    if (!empty($min_bathrooms) && !empty($max_bathrooms)) {
        $bathrooms_query['value'] = array($min_bathrooms, $max_bathrooms);
        $bathrooms_query['compare'] = 'BETWEEN';
    } elseif (!empty($min_bathrooms)) {
        $bathrooms_query['value'] = $min_bathrooms;
        $bathrooms_query['compare'] = '>=';
    } elseif (!empty($max_bathrooms)) {
        $bathrooms_query['value'] = $max_bathrooms;
        $bathrooms_query['compare'] = '<=';
    }
    
    $args['meta_query'][] = $bathrooms_query;
}

// Property type filter will be handled by database query below

// Add year built filter
if (!empty($year_built)) {
    $year_query = array(
        'key' => '_property_year_built',
        'type' => 'NUMERIC',
    );
    
    if (strpos($year_built, '+') !== false) {
        $year_value = intval(str_replace('+', '', $year_built));
        $year_query['value'] = $year_value;
        $year_query['compare'] = '>=';
    } else {
        $year_query['value'] = intval($year_built);
        $year_query['compare'] = '=';
    }
    
    $args['meta_query'][] = $year_query;
}

// Add property status filter
if (!empty($property_status)) {
    // Sanitize property status term slug
    $property_status_slug = sanitize_text_field($property_status);
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'property_status',
            'field' => 'slug',
            'terms' => $property_status_slug,
        )
    );
}

// Property type filter will be handled by database query below

// Add sorting (dynamically from Listings settings)
// Get default sort if not specified
if (empty($sort_by) || $sort_by === 'date') {
    $sort_by = resbs_get_default_sort_option();
}

// Add sorting
switch ($sort_by) {
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
    default:
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        break;
}

// Set meta_query relation
if (count($args['meta_query']) > 1) {
    $args['meta_query']['relation'] = 'AND';
}

// Execute the query
// Clear any potential caching issues
wp_cache_flush();

// SIMPLE PROPERTY TYPE FILTER
if (!empty($property_type_filter)) {
    // Sanitize property type term slug
    $property_type_slug = sanitize_text_field($property_type_filter);
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'property_type',
            'field' => 'slug',
            'terms' => $property_type_slug,
        )
    );
}

$properties_query = new WP_Query($args);

// Get ONLY existing property types - no creating new ones
$property_types = get_terms(array(
    'taxonomy' => 'property_type',
    'hide_empty' => false,
));

// Get property statuses
$property_statuses = get_terms(array(
    'taxonomy' => 'property_status',
    'hide_empty' => false,
));
?>

<div class="rbs-archive resbs-archive-wrapper" id="resbs-property-archive">

    <!-- Advanced Search Bar -->
    <div class="search-bar">
        <div class="container" style="width: 100% !important; max-width: 1540px !important; min-width: 0 !important; margin: 0 auto !important; margin-left: auto !important; margin-right: auto !important; padding: 4rem 16px !important; padding-top: 1rem !important; padding-bottom: 1rem !important; padding-left: 16px !important; padding-right: 16px !important; box-sizing: border-box !important; position: relative !important; display: block !important;">
            <?php 
            // Get the archive page URL for form action
            $archive_url = get_post_type_archive_link('property');
            if (!$archive_url) {
                $archive_url = home_url('/');
            }
            ?>
            <form method="GET" class="search-container" id="searchForm">
                <!-- Search Input -->
                <div class="search-input-container">
                    <i class="fas fa-search search-icon"></i>
                    <input
                        type="text"
                        placeholder="<?php echo esc_attr__('Address, City, ZIP...', 'realestate-booking-suite'); ?>"
                        class="search-input"
                        id="searchInput"
                        name="search"
                        value="<?php echo esc_attr($search_query); ?>"
                    >
                </div>

                <!-- Filter Buttons -->
                <div class="filter-buttons">
                    <button type="button" onclick="toggleDropdown('priceDropdown', event)" class="filter-chip">
                        <span><?php echo esc_html__('Price', 'realestate-booking-suite'); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <button type="button" onclick="toggleDropdown('typeDropdown', event)" class="filter-chip">
                        <span><?php echo esc_html__('Type', 'realestate-booking-suite'); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <button type="button" onclick="toggleDropdown('bedroomsDropdown', event)" class="filter-chip">
                        <span><?php echo esc_html__('Bedrooms', 'realestate-booking-suite'); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <button type="button" onclick="toggleDropdown('bathroomsDropdown', event)" class="filter-chip">
                        <span><?php echo esc_html__('Bathrooms', 'realestate-booking-suite'); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>


                    <button type="button" onclick="toggleDropdown('moreFiltersDropdown', event)" class="filter-chip">
                        <span><?php echo esc_html__('More filters', 'realestate-booking-suite'); ?></span>
                        <i class="fas fa-sliders-h"></i>
                    </button>

                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> <?php echo esc_html__('Search', 'realestate-booking-suite'); ?>
                    </button>
                    <?php 
                    // Get the archive page URL and add reset parameter
                    $archive_url = get_post_type_archive_link('property');
                    if (!$archive_url) {
                        $archive_url = home_url('/');
                    }
                    // Add reset parameter to trigger PHP reset logic
                    $reset_url = add_query_arg('reset', '1', $archive_url);
                    ?>
                    <a href="<?php echo esc_url($reset_url); ?>" class="search-btn" style="background-color: #6b7280; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-redo"></i> <?php echo esc_html__('Reset to Default', 'realestate-booking-suite'); ?>
                    </a>
                </div>

                <!-- Dropdown Panels Container -->
                <div class="dropdowns-container">
                <!-- Price Dropdown -->
                <div id="priceDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label"><?php echo esc_html__('Min Price', 'realestate-booking-suite'); ?></label>
                            <input type="number" placeholder="<?php echo esc_attr(sprintf(__('%s Min Price', 'realestate-booking-suite'), resbs_get_currency_symbol())); ?>" class="dropdown-input" name="min_price" value="<?php echo !empty($min_price) ? esc_attr($min_price) : ''; ?>">
                        </div>
                        <div>
                            <label class="dropdown-label"><?php echo esc_html__('Max Price', 'realestate-booking-suite'); ?></label>
                            <input type="number" placeholder="<?php echo esc_attr(sprintf(__('%s Max Price', 'realestate-booking-suite'), resbs_get_currency_symbol())); ?>" class="dropdown-input" name="max_price" value="<?php echo !empty($max_price) ? esc_attr($max_price) : ''; ?>">
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="apply-filter-btn">
                            <i class="fas fa-filter"></i> <?php echo esc_html__('Apply Filter', 'realestate-booking-suite'); ?>
                        </button>
                        <button type="button" class="clear-filter-btn" onclick="clearPriceFilter()">
                            <i class="fas fa-times"></i> <?php echo esc_html__('Clear', 'realestate-booking-suite'); ?>
                        </button>
                    </div>
                </div>

                <!-- Type Dropdown -->
                <div id="typeDropdown" class="dropdown-content">
                    <div class="checkbox-grid">
                        <!-- Add "Any" option first -->
                        <label class="checkbox-item">
                            <input type="radio" name="property_type" value="" <?php checked($property_type_filter, ''); ?>>
                            <span><?php echo esc_html__('Any Type', 'realestate-booking-suite'); ?></span>
                        </label>
                        <?php if ($property_types && !is_wp_error($property_types)): ?>
                            <?php foreach ($property_types as $type): ?>
                                <label class="checkbox-item">
                                    <input type="radio" name="property_type" value="<?php echo esc_attr($type->slug); ?>" <?php checked($property_type_filter, $type->slug); ?>>
                                    <span><?php echo esc_html($type->name); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <label class="checkbox-item">
                            <input type="radio" name="property_type" value="house" <?php checked($property_type_filter, 'house'); ?>>
                            <span><?php echo esc_html__('House', 'realestate-booking-suite'); ?></span>
                        </label>
                        <label class="checkbox-item">
                            <input type="radio" name="property_type" value="apartment" <?php checked($property_type_filter, 'apartment'); ?>>
                            <span><?php echo esc_html__('Apartment', 'realestate-booking-suite'); ?></span>
                        </label>
                        <label class="checkbox-item">
                            <input type="radio" name="property_type" value="condo" <?php checked($property_type_filter, 'condo'); ?>>
                            <span><?php echo esc_html__('Condo', 'realestate-booking-suite'); ?></span>
                        </label>
                        <label class="checkbox-item">
                            <input type="radio" name="property_type" value="office" <?php checked($property_type_filter, 'office'); ?>>
                            <span><?php echo esc_html__('Office', 'realestate-booking-suite'); ?></span>
                        </label>
                        <?php endif; ?>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="apply-filter-btn">
                            <i class="fas fa-filter"></i> <?php echo esc_html__('Apply Filter', 'realestate-booking-suite'); ?>
                        </button>
                        <button type="button" class="clear-filter-btn" onclick="clearTypeFilter()">
                            <i class="fas fa-times"></i> <?php echo esc_html__('Clear', 'realestate-booking-suite'); ?>
                        </button>
                    </div>
                </div>

                <!-- Bedrooms Dropdown -->
                <div id="bedroomsDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label"><?php echo esc_html__('Min Bedrooms', 'realestate-booking-suite'); ?></label>
                            <input type="number" placeholder="<?php echo esc_attr__('Min Bedrooms', 'realestate-booking-suite'); ?>" class="dropdown-input" name="min_bedrooms" value="<?php echo !empty($min_bedrooms) ? esc_attr($min_bedrooms) : ''; ?>" min="0">
                        </div>
                        <div>
                            <label class="dropdown-label"><?php echo esc_html__('Max Bedrooms', 'realestate-booking-suite'); ?></label>
                            <input type="number" placeholder="<?php echo esc_attr__('Max Bedrooms', 'realestate-booking-suite'); ?>" class="dropdown-input" name="max_bedrooms" value="<?php echo !empty($max_bedrooms) ? esc_attr($max_bedrooms) : ''; ?>" min="0">
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="apply-filter-btn">
                            <i class="fas fa-filter"></i> <?php echo esc_html__('Apply Filter', 'realestate-booking-suite'); ?>
                        </button>
                        <button type="button" class="clear-filter-btn" onclick="clearBedroomsFilter()">
                            <i class="fas fa-times"></i> <?php echo esc_html__('Clear', 'realestate-booking-suite'); ?>
                        </button>
                    </div>
                </div>

                <!-- Bathrooms Dropdown -->
                <div id="bathroomsDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label"><?php echo esc_html__('Min Bathrooms', 'realestate-booking-suite'); ?></label>
                            <input type="number" placeholder="<?php echo esc_attr__('Min Bathrooms', 'realestate-booking-suite'); ?>" class="dropdown-input" name="min_bathrooms" value="<?php echo !empty($min_bathrooms) ? esc_attr($min_bathrooms) : ''; ?>" min="0" step="0.5">
                        </div>
                        <div>
                            <label class="dropdown-label"><?php echo esc_html__('Max Bathrooms', 'realestate-booking-suite'); ?></label>
                            <input type="number" placeholder="<?php echo esc_attr__('Max Bathrooms', 'realestate-booking-suite'); ?>" class="dropdown-input" name="max_bathrooms" value="<?php echo !empty($max_bathrooms) ? esc_attr($max_bathrooms) : ''; ?>" min="0" step="0.5">
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="apply-filter-btn">
                            <i class="fas fa-filter"></i> <?php echo esc_html__('Apply Filter', 'realestate-booking-suite'); ?>
                        </button>
                        <button type="button" class="clear-filter-btn" onclick="clearBathroomsFilter()">
                            <i class="fas fa-times"></i> <?php echo esc_html__('Clear', 'realestate-booking-suite'); ?>
                        </button>
                    </div>
                </div>


                <!-- More Filters Dropdown -->
                <div id="moreFiltersDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label"><?php echo esc_html__('Square Feet', 'realestate-booking-suite'); ?></label>
                            <div class="flex" style="gap: 8px;">
                                <input type="number" placeholder="<?php echo esc_attr__('Min', 'realestate-booking-suite'); ?>" class="dropdown-input" name="min_sqft" value="<?php echo !empty($min_sqft) ? esc_attr($min_sqft) : ''; ?>">
                                <input type="number" placeholder="<?php echo esc_attr__('Max', 'realestate-booking-suite'); ?>" class="dropdown-input" name="max_sqft" value="<?php echo !empty($max_sqft) ? esc_attr($max_sqft) : ''; ?>">
                            </div>
                        </div>
                        <div>
                            <label class="dropdown-label"><?php echo esc_html__('Year Built', 'realestate-booking-suite'); ?></label>
                            <select class="dropdown-input" name="year_built">
                                <option value=""><?php echo esc_html__('Any', 'realestate-booking-suite'); ?></option>
                                <option value="2020+" <?php selected($year_built, '2020+'); ?>>2020+</option>
                                <option value="2010+" <?php selected($year_built, '2010+'); ?>>2010+</option>
                                <option value="2000+" <?php selected($year_built, '2000+'); ?>>2000+</option>
                                <option value="1990+" <?php selected($year_built, '1990+'); ?>>1990+</option>
                            </select>
                        </div>
                        <div>
                            <label class="dropdown-label"><?php echo esc_html__('Status', 'realestate-booking-suite'); ?></label>
                            <select class="dropdown-input" name="property_status">
                                <option value=""><?php echo esc_html__('All', 'realestate-booking-suite'); ?></option>
                                <?php if ($property_statuses && !is_wp_error($property_statuses)): ?>
                                    <?php foreach ($property_statuses as $status): ?>
                                        <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($property_status, $status->slug); ?>><?php echo esc_html($status->name); ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="for-sale" <?php selected($property_status, 'for-sale'); ?>><?php echo esc_html__('For Sale', 'realestate-booking-suite'); ?></option>
                                    <option value="for-rent" <?php selected($property_status, 'for-rent'); ?>><?php echo esc_html__('For Rent', 'realestate-booking-suite'); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="apply-filter-btn">
                            <i class="fas fa-filter"></i> <?php echo esc_html__('Apply Filter', 'realestate-booking-suite'); ?>
                        </button>
                        <button type="button" class="clear-filter-btn" onclick="clearMoreFilters()">
                            <i class="fas fa-times"></i> <?php echo esc_html__('Clear', 'realestate-booking-suite'); ?>
                        </button>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container main-content" style="width: 100% !important; max-width: 1540px !important; min-width: 0 !important; margin: 0 auto !important; margin-left: auto !important; margin-right: auto !important; padding: 4rem 16px !important; padding-top: 1rem !important; padding-bottom: 4rem !important; padding-left: 16px !important; padding-right: 16px !important; box-sizing: border-box !important; position: relative !important; display: block !important;">
        <!-- Control Bar -->
        <div class="control-bar">
            <!-- Left Side -->
            <div class="view-controls">
                <button onclick="showListView()" class="view-btn active">
                    <i class="fas fa-list"></i>
                    <span><?php echo esc_html__('Grid View', 'realestate-booking-suite'); ?></span>
                </button>
                <div class="view-divider">|</div>
                <button onclick="showMapView()" class="view-btn">
                    <i class="fas fa-map-marked-alt"></i>
                    <span><?php echo esc_html__('Map View', 'realestate-booking-suite'); ?></span>
                </button>
                <div class="results-count">
                    <span id="resultsCount"><?php echo esc_html($properties_query->found_posts); ?></span> <?php echo esc_html__('results', 'realestate-booking-suite'); ?>
                </div>
            </div>

            <!-- Right Side -->
            <div class="right-controls">
                <div class="sort-controls">
                    <span class="sort-label"><?php echo esc_html__('Sort by:', 'realestate-booking-suite'); ?></span>
                    <select class="sort-select" name="sort_by" onchange="this.form.submit()">
                        <option value="newest" <?php selected($sort_by, 'newest'); ?><?php selected($sort_by, 'date'); ?>><?php echo esc_html__('Newest', 'realestate-booking-suite'); ?></option>
                        <option value="price_low" <?php selected($sort_by, 'price_low'); ?>><?php echo esc_html__('Price: Low to High', 'realestate-booking-suite'); ?></option>
                        <option value="price_high" <?php selected($sort_by, 'price_high'); ?>><?php echo esc_html__('Price: High to Low', 'realestate-booking-suite'); ?></option>
                    </select>
                </div>


            </div>
        </div>

        <!-- Map and Listings Layout -->
        <div class="listings-container">
            <!-- Property Listings -->
            <div class="properties-list" id="propertiesContainer">
                <div id="propertyGrid" class="property-grid">
                    <?php if ($properties_query->have_posts()): ?>
                        <?php while ($properties_query->have_posts()): $properties_query->the_post(); ?>
                            <?php
                            // Get property meta data
                            $price = get_post_meta(get_the_ID(), '_property_price', true);
                            $bedrooms = get_post_meta(get_the_ID(), '_property_bedrooms', true);
                            $bathrooms = get_post_meta(get_the_ID(), '_property_bathrooms', true);
                            $area_sqft = get_post_meta(get_the_ID(), '_property_area_sqft', true);
                            $address = get_post_meta(get_the_ID(), '_property_address', true);
                            $city = get_post_meta(get_the_ID(), '_property_city', true);
                            $state = get_post_meta(get_the_ID(), '_property_state', true);
                            $zip = get_post_meta(get_the_ID(), '_property_zip', true);
                            $latitude = get_post_meta(get_the_ID(), '_property_latitude', true);
                            $longitude = get_post_meta(get_the_ID(), '_property_longitude', true);
                            
                            // Get property type and status
                            $property_types = get_the_terms(get_the_ID(), 'property_type');
                            $property_statuses = get_the_terms(get_the_ID(), 'property_status');
                            
                            $property_type_name = '';
                            if ($property_types && !is_wp_error($property_types)) {
                                $property_type_name = $property_types[0]->name;
                            }
                            
                            $property_status_name = '';
                            if ($property_statuses && !is_wp_error($property_statuses)) {
                                $property_status_name = $property_statuses[0]->name;
                            }
                            
                            // Get featured image
                            $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'large');
                            if (!$featured_image) {
                                $featured_image = 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800';
                            }
                            
                            // Format price
                            $formatted_price = '';
                            if ($price) {
                                $formatted_price = resbs_format_price($price);
                            }
                            
                            // Format location
                            $location = '';
                            if ($address) $location .= $address;
                            if ($city) $location .= ($location ? ', ' : '') . $city;
                            if ($state) $location .= ($location ? ', ' : '') . $state;
                            if ($zip) $location .= ($location ? ' ' : '') . $zip;
                            
                            // Determine badge
                            $badge_class = 'badge-new';
                            $badge_text = 'Just listed';
                            $post_date = get_the_date('Y-m-d');
                            $days_old = (time() - strtotime($post_date)) / (60 * 60 * 24);
                            
                            if ($days_old < 7) {
                                $badge_class = 'badge-new';
                                $badge_text = __('Just listed', 'realestate-booking-suite');
                            } elseif ($days_old < 30) {
                                $badge_class = 'badge-featured';
                                $badge_text = __('Featured', 'realestate-booking-suite');
                            } else {
                                $badge_class = 'badge-standard';
                                $badge_text = __('Available', 'realestate-booking-suite');
                            }
                            ?>
                            
                            <!-- Property Card -->
                            <div class="property-card" data-property-id="<?php echo esc_attr(get_the_ID()); ?>">
                        <div class="property-image">
                                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                            <div class="gradient-overlay"></div>
                                    <div class="property-badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($badge_text); ?></div>
                            <?php if (resbs_is_wishlist_enabled()): 
                                $property_id = get_the_ID();
                                $is_favorited = resbs_is_property_favorited($property_id);
                            ?>
                            <button class="favorite-btn resbs-favorite-btn <?php echo esc_attr($is_favorited ? 'favorited' : ''); ?>" data-property-id="<?php echo esc_attr($property_id); ?>">
                                <i class="<?php echo esc_attr($is_favorited ? 'fas' : 'far'); ?> fa-heart"></i>
                            </button>
                            <?php endif; ?>
                            <div class="property-info-overlay">
                                        <h3 class="property-title"><?php echo esc_html(get_the_title()); ?></h3>
                                        <?php if (resbs_should_show_listing_address() && $location): ?>
                                            <p class="property-location"><?php echo esc_html($location); ?></p>
                                        <?php endif; ?>
                            </div>
                        </div>
                        <div class="property-details">
                            <div class="property-price-container">
                                        <?php if (resbs_should_show_price() && $formatted_price): ?>
                                            <span class="property-price"><?php echo esc_html($formatted_price); ?></span>
                                        <?php endif; ?>
                                        <span class="property-status"><?php echo esc_html($property_status_name); ?></span>
                            </div>
                            <div class="property-features">
                                        <?php if ($bedrooms): ?>
                                <div class="property-feature">
                                    <i class="fas fa-bed"></i>
                                                <span><?php echo esc_html($bedrooms); ?> <?php echo esc_html($bedrooms != 1 ? esc_html__('beds', 'realestate-booking-suite') : esc_html__('bed', 'realestate-booking-suite')); ?></span>
                                </div>
                                        <?php endif; ?>
                                        <?php if ($bathrooms): ?>
                                <div class="property-feature">
                                    <i class="fas fa-bath"></i>
                                                <span><?php echo esc_html($bathrooms); ?> <?php echo esc_html($bathrooms != 1 ? esc_html__('baths', 'realestate-booking-suite') : esc_html__('bath', 'realestate-booking-suite')); ?></span>
                                </div>
                                        <?php endif; ?>
                                        <?php if ($area_sqft): ?>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i>
                                                <span><?php echo esc_html(resbs_format_area($area_sqft)); ?></span>
                                </div>
                                        <?php endif; ?>
                            </div>
                            <div class="property-footer">
                                        <span class="property-type"><?php echo esc_html($property_type_name); ?></span>
                                        <a href="<?php echo esc_url(get_permalink()); ?>" class="view-details-btn" target="_blank">
                                    <?php echo esc_html__('View Details', 'realestate-booking-suite'); ?> <i class="fas fa-arrow-right"></i>
                                        </a>
                            </div>
                        </div>
                    </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-properties-found">
                            <h3><?php echo esc_html__('No properties found', 'realestate-booking-suite'); ?></h3>
                            <p><?php echo esc_html__('Try adjusting your search criteria or browse all properties.', 'realestate-booking-suite'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Interactive Google Map -->
            <div class="map-section map-hidden">
                <div class="map-container" style="position: relative;">
                    <div id="googleMap" style="width: 100%; height: 100%; min-height: 500px;"></div>
                    
                    <!-- Map Controls -->
                    <div class="map-controls">
                        <?php 
                        // Get the archive page URL and add reset parameter
                        $archive_url = get_post_type_archive_link('property');
                        if (!$archive_url) {
                            $archive_url = home_url('/');
                        }
                        // Add reset parameter to trigger PHP reset logic
                        $reset_url = add_query_arg('reset', '1', $archive_url);
                        ?>
                        <a href="<?php echo esc_url($reset_url); ?>" class="map-control" id="mapResetBtn" title="<?php echo esc_attr__('Reset to Default', 'realestate-booking-suite'); ?>" style="cursor: pointer; z-index: 1000; text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                    
                    <!-- Map Legend -->
                    <div class="map-legend" style="position: absolute; bottom: 20px; left: 20px; z-index: 10;">
                        <h4 class="legend-title"><?php echo esc_html__('Legend', 'realestate-booking-suite'); ?></h4>
                        <div class="legend-items">
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #10b981;"></div>
                                <span class="legend-label"><?php echo esc_html__('Just Listed', 'realestate-booking-suite'); ?></span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #f97316;"></div>
                                <span class="legend-label"><?php echo esc_html__('Featured', 'realestate-booking-suite'); ?></span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #3b82f6;"></div>
                                <span class="legend-label"><?php echo esc_html__('Standard', 'realestate-booking-suite'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php
            // Using OpenStreetMap with Leaflet.js - completely FREE, no API keys needed!
            // No tokens or API keys required - everything is free and unlimited
            $use_openstreetmap = true; // Always use free OpenStreetMap
            $google_maps_api_key = ''; // Not used when OpenStreetMap is enabled, but defined for compatibility
            // Get map settings dynamically from General settings
            $map_zoom = resbs_get_default_zoom_level();
            $map_settings = resbs_get_map_settings('archive');
            
            // IMPORTANT: Map location is based on EACH PROPERTY's individual coordinates
            // Dashboard location settings are NOT used - each property has its own location
            // This fallback is ONLY used if NO properties have coordinates set
            $map_center_lat = 23.8103; // Fallback only - property locations take priority
            $map_center_lng = 90.4125; // Fallback only - property locations take priority
            
            // Using free OpenStreetMap with Leaflet.js
            
            // Prepare properties data for JavaScript
            // IMPORTANT: Add ALL properties to map - use geocoding for those without coordinates
            $properties_query->rewind_posts();
            $properties_data = array();
            $properties_without_coords = array();
            
            while ($properties_query->have_posts()): $properties_query->the_post();
                $property_id = get_the_ID();
                $property_title = get_the_title();
                
                // Get coordinates - try multiple meta key formats for compatibility
                $latitude = get_post_meta($property_id, '_property_latitude', true);
                $longitude = get_post_meta($property_id, '_property_longitude', true);
                
                // Fallback: try without underscore prefix
                if (empty($latitude)) {
                    $latitude = get_post_meta($property_id, 'property_latitude', true);
                }
                if (empty($longitude)) {
                    $longitude = get_post_meta($property_id, 'property_longitude', true);
                }
                
                $city = get_post_meta($property_id, '_property_city', true);
                $address = get_post_meta($property_id, '_property_address', true);
                $state = get_post_meta($property_id, '_property_state', true);
                $zip = get_post_meta($property_id, '_property_zip', true);
                $country = get_post_meta($property_id, '_property_country', true);
                
                // Get property data regardless of coordinates
                $price = get_post_meta($property_id, '_property_price', true);
                $bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
                $bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
                $area_sqft = get_post_meta($property_id, '_property_area_sqft', true);
                $featured_image = get_the_post_thumbnail_url($property_id, 'thumbnail');
                    
                    $post_date = get_the_date('Y-m-d');
                    $days_old = (time() - strtotime($post_date)) / (60 * 60 * 24);
                    
                    if ($days_old < 7) {
                        $marker_color = '#10b981'; // Green for new
                    } elseif ($days_old < 30) {
                        $marker_color = '#f97316'; // Orange for featured
                    } else {
                        $marker_color = '#3b82f6'; // Blue for standard
                    }
                    
                // Validate and set coordinates
                $lat_float = null;
                $lng_float = null;
                $needs_geocoding = false;
                
                if (!empty($latitude) && !empty($longitude)) {
                    $lat_float = floatval($latitude);
                    $lng_float = floatval($longitude);
                    
                    // Check if coordinates are valid
                    if (!is_numeric($latitude) || !is_numeric($longitude) || 
                        $lat_float < -90 || $lat_float > 90 ||
                        $lng_float < -180 || $lng_float > 180 ||
                        ($lat_float == 0 && $lng_float == 0)) {
                        // Invalid coordinates - need geocoding
                        $lat_float = null;
                        $lng_float = null;
                        $needs_geocoding = true;
                    }
                } else {
                    // No coordinates - need geocoding
                    $needs_geocoding = true;
                }
                
                // Get location taxonomy term FIRST (most reliable for geocoding)
                $location_terms = get_the_terms($property_id, 'property_location');
                $location_name = '';
                if ($location_terms && !is_wp_error($location_terms) && !empty($location_terms)) {
                    $location_name = sanitize_text_field($location_terms[0]->name);
                }
                
                // Build address string for geocoding
                // Priority: Use location taxonomy if available, otherwise build from address fields
                $address_string = '';
                
                if (!empty($location_name)) {
                    // Use location taxonomy as primary - it's usually the most accurate (e.g., "Uttara Dhaka", "Jashore")
                    $address_string = $location_name . ', Bangladesh';
                } else {
                    // Fallback to address fields - sanitize before concatenation
                    if ($address) $address_string .= sanitize_text_field($address);
                    if ($city) $address_string .= ($address_string ? ', ' : '') . sanitize_text_field($city);
                    if ($state) $address_string .= ($address_string ? ', ' : '') . sanitize_text_field($state);
                    if ($zip) $address_string .= ($address_string ? ' ' : '') . sanitize_text_field($zip);
                    if ($country) $address_string .= ($address_string ? ', ' : '') . sanitize_text_field($country);
                    
                    // If still empty, try city alone
                    if (empty($address_string) && $city) {
                        $address_string = sanitize_text_field($city) . ', Bangladesh';
                    }
                    
                    // If still empty, try property title (might contain location info)
                    if (empty($address_string) && !empty($property_title)) {
                        // Check if title contains location keywords
                        $title_lower = strtolower($property_title);
                        $bangladesh_cities = array('dhaka', 'chittagong', 'sylhet', 'comilla', 'feni', 'coxbazar', 'rajshahi', 'khulna', 'barisal', 'rangpur', 'mymensingh');
                        foreach ($bangladesh_cities as $city_name) {
                            if (strpos($title_lower, $city_name) !== false) {
                                $address_string = ucfirst($city_name) . ', Bangladesh';
                                break;
                            }
                        }
                    }
                }
                
                // ALWAYS add property to data array - even if it needs geocoding
                // Ensure needs_geocoding is TRUE if no coordinates exist
                $needs_geocoding = ($lat_float === null || $lng_float === null);
                
                // If no address string was built, try to use property title as fallback
                if (empty($address_string) && !empty($property_title)) {
                    // Use property title + Bangladesh as last resort
                    $address_string = sanitize_text_field($property_title) . ', Bangladesh';
                }
                
                // Format price using plugin function for consistency
                $formatted_price_string = '';
                if ($price) {
                    $formatted_price_string = resbs_format_price($price);
                } else {
                    $formatted_price_string = __('Price on request', 'realestate-booking-suite');
                }
                
                $property_data = array(
                    'id' => absint($property_id),
                    'title' => sanitize_text_field($property_title),
                    'price' => sanitize_text_field($formatted_price_string),
                    'bedrooms' => $bedrooms ? absint($bedrooms) : '',
                    'bathrooms' => $bathrooms ? floatval($bathrooms) : '',
                    'area_sqft' => $area_sqft ? absint($area_sqft) : '',
                    'permalink' => esc_url_raw(get_permalink()),
                    'image' => $featured_image ? esc_url_raw($featured_image) : '',
                    'marker_color' => sanitize_hex_color($marker_color),
                    'days_old' => floatval($days_old),
                    'city' => $city ? sanitize_text_field($city) : '',
                    'address' => $address ? sanitize_text_field($address) : '',
                    'location_name' => $location_name ? sanitize_text_field($location_name) : '', // Location taxonomy term
                    'full_address' => sanitize_text_field($address_string),
                    'needs_geocoding' => (bool) $needs_geocoding // Explicitly set this
                );
                
                if ($lat_float !== null && $lng_float !== null) {
                    // Has valid coordinates
                    $property_data['lat'] = $lat_float;
                    $property_data['lng'] = $lng_float;
                    $property_data['needs_geocoding'] = false;
                } else {
                    // Needs geocoding - will be done in JavaScript
                    $property_data['needs_geocoding'] = true;
                    $properties_without_coords[] = $property_data;
                }
                
                
                // Add ALL properties to the array - NO EXCEPTIONS
                $properties_data[] = $property_data;
            endwhile;
            wp_reset_postdata();
            
            // Pass data to JavaScript via filter (for wp_localize_script) - for backwards compatibility
            add_filter('resbs_archive_js_data', function($data) use ($use_openstreetmap, $properties_data, $map_settings, $map_center_lat, $map_center_lng, $map_zoom) {
                return array(
                    'use_openstreetmap' => $use_openstreetmap,
                    'properties_data' => $properties_data,
                    'map_settings' => $map_settings,
                    'map_center_lat' => $map_center_lat,
                    'map_center_lng' => $map_center_lng,
                    'map_zoom' => $map_zoom
                );
            }, 10, 1);
            ?>
            
            <!-- Pass properties data directly to JavaScript - Store in data attribute that CANNOT be overwritten -->
            <script type="text/javascript">
            // CRITICAL: Store data in a way that CANNOT be overwritten
            // Use a hidden div with data attribute as backup, AND set window variables
            (function() {
                'use strict';
                
                // Get properties data from PHP
                var propertiesData = <?php echo wp_json_encode($properties_data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                
                if (propertiesData.length === 0) {
                    return;
                }
                
                // CRITICAL: Store in MULTIPLE places to prevent loss
                // 1. Store in a hidden div data attribute (cannot be overwritten by JS)
                var dataDiv = document.createElement('div');
                dataDiv.id = 'resbs-properties-data-storage';
                dataDiv.style.display = 'none';
                dataDiv.setAttribute('data-properties', JSON.stringify(propertiesData));
                document.body.appendChild(dataDiv);
                
                // 2. Store in window with a unique key that won't conflict
                window.RESBS_PROPERTIES_DATA = propertiesData;
                
                // 3. Store in window.resbs_archive
                if (typeof window.resbs_archive === 'undefined') {
                    window.resbs_archive = {};
                }
                // Preserve existing data
                var existingData = {
                    ajax_url: window.resbs_archive.ajax_url || (typeof resbs_archive !== 'undefined' ? resbs_archive.ajax_url : ''),
                    favorites_nonce: window.resbs_archive.favorites_nonce || (typeof resbs_archive !== 'undefined' ? resbs_archive.favorites_nonce : ''),
                    translations: window.resbs_archive.translations || (typeof resbs_archive !== 'undefined' ? resbs_archive.translations : {})
                };
                
                // Set properties_data - make it non-writable to prevent overwriting
                Object.defineProperty(window.resbs_archive, 'properties_data', {
                    value: propertiesData,
                    writable: false,
                    configurable: false,
                    enumerable: true
                });
                
                window.resbs_archive.use_openstreetmap = <?php echo wp_json_encode((bool) $use_openstreetmap); ?>;
                window.resbs_archive.map_settings = <?php echo wp_json_encode($map_settings, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
                window.resbs_archive.map_center_lat = <?php echo floatval($map_center_lat); ?>;
                window.resbs_archive.map_center_lng = <?php echo floatval($map_center_lng); ?>;
                window.resbs_archive.map_zoom = <?php echo intval($map_zoom); ?>;
                window.resbs_archive.ajax_url = existingData.ajax_url;
                window.resbs_archive.favorites_nonce = existingData.favorites_nonce;
                window.resbs_archive.translations = existingData.translations;
                
                // 4. Also update global resbs_archive if it exists
                if (typeof resbs_archive !== 'undefined') {
                    resbs_archive.properties_data = propertiesData;
                    resbs_archive.use_openstreetmap = window.resbs_archive.use_openstreetmap;
                    resbs_archive.map_settings = window.resbs_archive.map_settings;
                    resbs_archive.map_center_lat = window.resbs_archive.map_center_lat;
                    resbs_archive.map_center_lng = window.resbs_archive.map_center_lng;
                    resbs_archive.map_zoom = window.resbs_archive.map_zoom;
                }
                
                // 5. Set window.propertiesData
                window.propertiesData = propertiesData;
            })();
            </script>
        
        <!-- Pagination -->
        <?php if ($properties_query->max_num_pages > 1): ?>
            <div class="pagination-container">
                <?php
                echo paginate_links(array(
                    'total' => $properties_query->max_num_pages,
                    'current' => $paged,
                    'format' => '?paged=%#%',
                    'show_all' => false,
                    'type' => 'list',
                    'end_size' => 2,
                    'mid_size' => 1,
                    'prev_text' => '<i class="fas fa-chevron-left"></i> ' . esc_html__('Previous', 'realestate-booking-suite'),
                    'next_text' => esc_html__('Next', 'realestate-booking-suite') . ' <i class="fas fa-chevron-right"></i>',
                ));
                ?>
                </div>
        <?php endif; ?>
        </div>
</div>

<?php
// Inline styles have been moved to assets/css/simple-archive-layout.css
// Dynamic colors are handled via wp_add_inline_style in class-resbs-template-assets.php
// All CSS is now enqueued via wp_enqueue_style in class-resbs-template-assets.php
?>

<!-- Inline styles have been moved to assets/css/simple-archive-layout.css -->
<!-- Dynamic colors are handled via wp_add_inline_style in class-resbs-template-assets.php -->
<!-- All CSS is now enqueued via wp_enqueue_style in class-resbs-template-assets.php -->

<!-- Archive dropdown and other scripts are now enqueued via wp_enqueue_script -->
<!-- All JavaScript has been moved to assets/js/simple-archive.js for WordPress.org compliance -->
<?php if ($use_openstreetmap): ?>
<!-- Leaflet.js CSS and JS will be loaded via wp_enqueue_script in class-resbs-template-assets.php -->
<?php endif; ?>

<?php if ($use_openstreetmap): ?>
<!-- OpenStreetMap initialization is now handled in assets/js/simple-archive.js -->
<?php elseif (!empty($google_maps_api_key)): ?>
<!-- Google Maps initialization is now handled in assets/js/simple-archive.js -->
<?php endif; ?>

<script>
// Clean form before submission - remove empty/zero values
(function() {
    'use strict';
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            // Remove empty/zero number inputs before submission
            const numberInputs = this.querySelectorAll('input[type="number"]');
            numberInputs.forEach(function(input) {
                const value = input.value.trim();
                // If empty or zero, remove the name attribute so it won't be submitted
                if (value === '' || value === '0' || value === null || value === undefined) {
                    input.removeAttribute('name');
                }
            });
            
            // Remove empty hidden inputs
            const hiddenInputs = this.querySelectorAll('input[type="hidden"]');
            hiddenInputs.forEach(function(input) {
                const value = input.value.trim();
                if (value === '' || value === '0' || value === null || value === undefined) {
                    input.removeAttribute('name');
                }
            });
            
            // Remove empty select values
            const selects = this.querySelectorAll('select');
            selects.forEach(function(select) {
                const value = select.value.trim();
                if (value === '' || value === null || value === undefined) {
                    select.removeAttribute('name');
                }
            });
            
            // Remove empty text inputs (except search)
            const textInputs = this.querySelectorAll('input[type="text"]:not([name="search"])');
            textInputs.forEach(function(input) {
                const value = input.value.trim();
                if (value === '' || value === null || value === undefined) {
                    input.removeAttribute('name');
                }
            });
        });
    }
})();

// Submit form function
function submitForm() {
    const searchForm = document.getElementById('searchForm');
    if (searchForm) {
        searchForm.submit();
    }
}

// Clear price filter function
function clearPriceFilter() {
    const minPriceInput = document.querySelector('input[name="min_price"]');
    const maxPriceInput = document.querySelector('input[name="max_price"]');
    const searchForm = document.getElementById('searchForm');
    
    if (minPriceInput) minPriceInput.value = '';
    if (maxPriceInput) maxPriceInput.value = '';
    if (searchForm) {
        searchForm.submit();
    }
}

// Clear type filter function
function clearTypeFilter() {
    // Select the "Any Type" radio button
    const anyTypeRadio = document.querySelector('input[name="property_type"][value=""]');
    if (anyTypeRadio) {
        anyTypeRadio.checked = true;
        const searchForm = document.getElementById('searchForm');
        if (searchForm) {
            searchForm.submit();
        }
    }
}

// Clear bedrooms filter function
function clearBedroomsFilter() {
    const minBedroomsInput = document.querySelector('input[name="min_bedrooms"]');
    const maxBedroomsInput = document.querySelector('input[name="max_bedrooms"]');
    const searchForm = document.getElementById('searchForm');
    
    if (minBedroomsInput) minBedroomsInput.value = '';
    if (maxBedroomsInput) maxBedroomsInput.value = '';
    if (searchForm) {
        searchForm.submit();
    }
}

// Clear bathrooms filter function
function clearBathroomsFilter() {
    const minBathroomsInput = document.querySelector('input[name="min_bathrooms"]');
    const maxBathroomsInput = document.querySelector('input[name="max_bathrooms"]');
    const searchForm = document.getElementById('searchForm');
    
    if (minBathroomsInput) minBathroomsInput.value = '';
    if (maxBathroomsInput) maxBathroomsInput.value = '';
    if (searchForm) {
        searchForm.submit();
    }
}

// Clear more filters function
function clearMoreFilters() {
    const minSqftInput = document.querySelector('input[name="min_sqft"]');
    const maxSqftInput = document.querySelector('input[name="max_sqft"]');
    const yearBuiltSelect = document.querySelector('select[name="year_built"]');
    const propertyStatusSelect = document.querySelector('select[name="property_status"]');
    const searchForm = document.getElementById('searchForm');
    
    if (minSqftInput) minSqftInput.value = '';
    if (maxSqftInput) maxSqftInput.value = '';
    if (yearBuiltSelect) yearBuiltSelect.value = '';
    if (propertyStatusSelect) propertyStatusSelect.value = '';
    
    if (searchForm) {
        searchForm.submit();
    }
}

// Auto-submit search on input (with debounce)
(function() {
    'use strict';
    let searchTimeout = null;
    
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const searchForm = document.getElementById('searchForm');
        
        if (searchInput && searchForm) {
            // Auto-submit when user types in search input
            searchInput.addEventListener('input', function() {
                // Clear previous timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }
                
                // Set new timeout to submit after user stops typing (500ms delay)
                searchTimeout = setTimeout(function() {
                    if (searchForm) {
                        searchForm.submit();
                    }
                }, 500);
            });
            
            // Also submit on Enter key (immediate)
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    // Clear timeout since we're submitting immediately
                    if (searchTimeout) {
                        clearTimeout(searchTimeout);
                    }
                    if (searchForm) {
                        searchForm.submit();
                    }
                }
            });
        }
    });
})();
</script>

<?php
wp_reset_postdata();

// Use resbs_get_footer() to ensure compatibility with both block and classic themes
resbs_get_footer();
?>
