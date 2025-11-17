<?php
/**
 * Simple Archive Property Template
 * Block Theme Compatible
 * 
 * @package RealEstate_Booking_Suite
 */

// IMPORTANT: Check for reset BEFORE any output (including get_header())
// If reset parameter is present, redirect to clean URL and set all values to defaults
if (isset($_GET['reset'])) {
    // Get the archive page URL without any query parameters
    $archive_url = get_post_type_archive_link('property');
    if (!$archive_url) {
        // Fallback to home URL if archive link doesn't exist
        $archive_url = home_url('/');
    }
    // Redirect to clean archive URL
    wp_redirect($archive_url);
    exit;
}

// Now safe to output headers and content
// Use helper function to safely get header (avoids deprecation warnings in block themes)
if (function_exists('resbs_get_header')) {
    resbs_get_header();
} else {
    get_header();
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">

<?php
// Note: AJAX functionality disabled for now to prevent errors
// wp_enqueue_script('resbs-dynamic-archive');

// SIMPLE WORKING FILTER APPROACH

$search_query = !isset($_GET['search']) ? '' : sanitize_text_field($_GET['search']);
$min_price = !isset($_GET['min_price']) ? '' : intval($_GET['min_price']);
$max_price = !isset($_GET['max_price']) ? '' : intval($_GET['max_price']);
$property_type_filter = !isset($_GET['property_type']) ? '' : sanitize_text_field($_GET['property_type']);
$min_bedrooms = !isset($_GET['min_bedrooms']) ? '' : intval($_GET['min_bedrooms']);
$max_bedrooms = !isset($_GET['max_bedrooms']) ? '' : intval($_GET['max_bedrooms']);
$min_bathrooms = !isset($_GET['min_bathrooms']) ? '' : floatval($_GET['min_bathrooms']);
$max_bathrooms = !isset($_GET['max_bathrooms']) ? '' : floatval($_GET['max_bathrooms']);
$min_sqft = !isset($_GET['min_sqft']) ? '' : intval($_GET['min_sqft']);
$max_sqft = !isset($_GET['max_sqft']) ? '' : intval($_GET['max_sqft']);
$year_built = !isset($_GET['year_built']) ? '' : sanitize_text_field($_GET['year_built']);
$property_status = !isset($_GET['property_status']) ? '' : sanitize_text_field($_GET['property_status']);
$sort_by = !isset($_GET['sort_by']) ? 'newest' : sanitize_text_field($_GET['sort_by']);
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

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

// Add square feet filter (min and max)
if (!empty($min_sqft) || !empty($max_sqft)) {
    $sqft_query = array(
        'key' => '_property_area_sqft',
        'type' => 'NUMERIC',
    );
    
    if (!empty($min_sqft) && !empty($max_sqft)) {
        $sqft_query['value'] = array($min_sqft, $max_sqft);
        $sqft_query['compare'] = 'BETWEEN';
    } elseif (!empty($min_sqft)) {
        $sqft_query['value'] = $min_sqft;
        $sqft_query['compare'] = '>=';
    } elseif (!empty($max_sqft)) {
        $sqft_query['value'] = $max_sqft;
        $sqft_query['compare'] = '<=';
    }
    
    $args['meta_query'][] = $sqft_query;
}

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
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'property_status',
            'field' => 'slug',
            'terms' => $property_status,
        )
    );
}

// Property type filter will be handled by database query below

// Add sorting (dynamically from Listings settings)
// Get default sort if not specified
if (empty($sort_by) || $sort_by === 'date') {
    $sort_by = resbs_get_default_sort_option();
}

// Only apply sorting if enabled in settings
if (resbs_is_sorting_enabled()) {
    switch ($sort_by) {
        case 'lowest_price':
        case 'price_low':
            $args['meta_key'] = '_property_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'highest_price':
        case 'price_high':
            $args['meta_key'] = '_property_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'largest_sqft':
            $args['meta_key'] = '_property_area_sqft';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'lowest_sqft':
            $args['meta_key'] = '_property_area_sqft';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'bedrooms':
            $args['meta_key'] = '_property_bedrooms';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'bathrooms':
            $args['meta_key'] = '_property_bathrooms';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'featured':
            $args['meta_key'] = '_property_featured';
            $args['orderby'] = 'meta_value';
            $args['order'] = 'DESC';
            break;
        case 'oldest':
            $args['orderby'] = 'date';
            $args['order'] = 'ASC';
            break;
        case 'newest':
        default:
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
    }
} else {
    // Default sorting when disabled
    $args['orderby'] = 'date';
    $args['order'] = 'DESC';
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
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'property_type',
            'field' => 'slug',
            'terms' => $property_type_filter,
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

<div class="rbs-archive">

    <!-- Advanced Search Bar -->
    <div class="search-bar">
        <div class="container">
            <form method="GET" class="search-container" id="searchForm">
                <!-- Search Input -->
                <div class="search-input-container">
                    <i class="fas fa-search search-icon"></i>
                    <input
                        type="text"
                        placeholder="Address, City, ZIP..."
                        class="search-input"
                        id="searchInput"
                        name="search"
                        value="<?php echo esc_attr($search_query); ?>"
                    >
                </div>

                <!-- Filter Buttons -->
                <div class="filter-buttons">
                    <button type="button" onclick="toggleDropdown('priceDropdown')" class="filter-chip">
                        <span>Price</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <button type="button" onclick="toggleDropdown('typeDropdown')" class="filter-chip">
                        <span>Type</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <button type="button" onclick="toggleDropdown('bedroomsDropdown')" class="filter-chip">
                        <span>Bedrooms</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>

                    <button type="button" onclick="toggleDropdown('bathroomsDropdown')" class="filter-chip">
                        <span>Bathrooms</span>
                        <i class="fas fa-chevron-down"></i>
                    </button>


                    <button type="button" onclick="toggleDropdown('moreFiltersDropdown')" class="filter-chip">
                        <span>More filters</span>
                        <i class="fas fa-sliders-h"></i>
                    </button>

                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
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
                        <i class="fas fa-redo"></i> Reset to Default
                    </a>
                </div>

                <!-- Dropdown Panels Container -->
                <div class="dropdowns-container">
                <!-- Price Dropdown -->
                <div id="priceDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label">Min Price</label>
                            <input type="number" placeholder="$ Min Price" class="dropdown-input" name="min_price" value="<?php echo esc_attr($min_price); ?>">
                        </div>
                        <div>
                            <label class="dropdown-label">Max Price</label>
                            <input type="number" placeholder="$ Max Price" class="dropdown-input" name="max_price" value="<?php echo esc_attr($max_price); ?>">
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="apply-filter-btn">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <button type="button" class="clear-filter-btn" onclick="clearPriceFilter()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Type Dropdown -->
                <div id="typeDropdown" class="dropdown-content">
                    <div class="checkbox-grid">
                        <!-- Add "Any" option first -->
                        <label class="checkbox-item">
                            <input type="radio" name="property_type" value="" <?php checked($property_type_filter, ''); ?>>
                            <span>Any Type</span>
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
                            <span>House</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="radio" name="property_type" value="apartment" <?php checked($property_type_filter, 'apartment'); ?>>
                            <span>Apartment</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="radio" name="property_type" value="condo" <?php checked($property_type_filter, 'condo'); ?>>
                            <span>Condo</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="radio" name="property_type" value="office" <?php checked($property_type_filter, 'office'); ?>>
                            <span>Office</span>
                        </label>
                        <?php endif; ?>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="apply-filter-btn">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <button type="button" class="clear-filter-btn" onclick="clearTypeFilter()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Bedrooms Dropdown -->
                <div id="bedroomsDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label">Min Bedrooms</label>
                            <input type="number" placeholder="Min Bedrooms" class="dropdown-input" name="min_bedrooms" value="<?php echo esc_attr($min_bedrooms); ?>" min="0">
                        </div>
                        <div>
                            <label class="dropdown-label">Max Bedrooms</label>
                            <input type="number" placeholder="Max Bedrooms" class="dropdown-input" name="max_bedrooms" value="<?php echo esc_attr($max_bedrooms); ?>" min="0">
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="apply-filter-btn">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <button type="button" class="clear-filter-btn" onclick="clearBedroomsFilter()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>

                <!-- Bathrooms Dropdown -->
                <div id="bathroomsDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label">Min Bathrooms</label>
                            <input type="number" placeholder="Min Bathrooms" class="dropdown-input" name="min_bathrooms" value="<?php echo esc_attr($min_bathrooms); ?>" min="0" step="0.5">
                        </div>
                        <div>
                            <label class="dropdown-label">Max Bathrooms</label>
                            <input type="number" placeholder="Max Bathrooms" class="dropdown-input" name="max_bathrooms" value="<?php echo esc_attr($max_bathrooms); ?>" min="0" step="0.5">
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="apply-filter-btn">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <button type="button" class="clear-filter-btn" onclick="clearBathroomsFilter()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>


                <!-- More Filters Dropdown -->
                <div id="moreFiltersDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label">Square Feet</label>
                            <div class="flex" style="gap: 8px;">
                                <input type="number" placeholder="Min" class="dropdown-input" name="min_sqft" value="<?php echo esc_attr($min_sqft); ?>">
                                <input type="number" placeholder="Max" class="dropdown-input" name="max_sqft" value="<?php echo esc_attr($max_sqft); ?>">
                            </div>
                        </div>
                        <div>
                            <label class="dropdown-label">Year Built</label>
                            <select class="dropdown-input" name="year_built">
                                <option value="">Any</option>
                                <option value="2020+" <?php selected($year_built, '2020+'); ?>>2020+</option>
                                <option value="2010+" <?php selected($year_built, '2010+'); ?>>2010+</option>
                                <option value="2000+" <?php selected($year_built, '2000+'); ?>>2000+</option>
                                <option value="1990+" <?php selected($year_built, '1990+'); ?>>1990+</option>
                            </select>
                        </div>
                        <div>
                            <label class="dropdown-label">Status</label>
                            <select class="dropdown-input" name="property_status">
                                <option value="">All</option>
                                <?php if ($property_statuses && !is_wp_error($property_statuses)): ?>
                                    <?php foreach ($property_statuses as $status): ?>
                                        <option value="<?php echo esc_attr($status->slug); ?>" <?php selected($property_status, $status->slug); ?>><?php echo esc_html($status->name); ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="for-sale" <?php selected($property_status, 'for-sale'); ?>>For Sale</option>
                                    <option value="for-rent" <?php selected($property_status, 'for-rent'); ?>>For Rent</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="submit" class="apply-filter-btn">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <button type="button" class="clear-filter-btn" onclick="clearMoreFilters()">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </div>
                </div>
            </div>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container main-content">
        <!-- Control Bar -->
        <div class="control-bar">
            <!-- Left Side -->
            <div class="view-controls">
                <button onclick="showListView()" class="view-btn active">
                    <i class="fas fa-list"></i>
                    <span>Grid View</span>
                </button>
                <div class="view-divider">|</div>
                <button onclick="showMapView()" class="view-btn">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Map View</span>
                </button>
                <div class="results-count">
                    <span id="resultsCount"><?php echo $properties_query->found_posts; ?></span> results
                </div>
            </div>

            <!-- Right Side -->
            <div class="right-controls">
                <?php 
                // Show sort controls only if sorting is enabled (dynamically from Listings settings)
                if (resbs_is_sorting_enabled()) : 
                    $enabled_sort_options = resbs_get_sort_options();
                ?>
                <div class="sort-controls">
                    <span class="sort-label">Sort by:</span>
                    <select class="sort-select" name="sort_by" id="sortSelect" onchange="handleSortChange(this.value)">
                        <?php if (in_array('newest', $enabled_sort_options)) : ?>
                            <option value="newest" <?php selected($sort_by, 'newest'); ?>>Newest</option>
                        <?php endif; ?>
                        <?php if (in_array('oldest', $enabled_sort_options)) : ?>
                            <option value="oldest" <?php selected($sort_by, 'oldest'); ?>>Oldest</option>
                        <?php endif; ?>
                        <?php if (in_array('lowest_price', $enabled_sort_options)) : ?>
                            <option value="lowest_price" <?php selected($sort_by, 'lowest_price'); ?>>Price: Low to High</option>
                        <?php endif; ?>
                        <?php if (in_array('highest_price', $enabled_sort_options)) : ?>
                            <option value="highest_price" <?php selected($sort_by, 'highest_price'); ?>>Price: High to Low</option>
                        <?php endif; ?>
                        <?php if (in_array('largest_sqft', $enabled_sort_options)) : ?>
                            <option value="largest_sqft" <?php selected($sort_by, 'largest_sqft'); ?>>Largest sq ft</option>
                        <?php endif; ?>
                        <?php if (in_array('lowest_sqft', $enabled_sort_options)) : ?>
                            <option value="lowest_sqft" <?php selected($sort_by, 'lowest_sqft'); ?>>Lowest sq ft</option>
                        <?php endif; ?>
                        <?php if (in_array('bedrooms', $enabled_sort_options)) : ?>
                            <option value="bedrooms" <?php selected($sort_by, 'bedrooms'); ?>>Bedrooms</option>
                        <?php endif; ?>
                        <?php if (in_array('bathrooms', $enabled_sort_options)) : ?>
                            <option value="bathrooms" <?php selected($sort_by, 'bathrooms'); ?>>Bathrooms</option>
                        <?php endif; ?>
                        <?php if (in_array('featured', $enabled_sort_options)) : ?>
                            <option value="featured" <?php selected($sort_by, 'featured'); ?>>Featured</option>
                        <?php endif; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="layout-toggle">
                    <button onclick="showGridLayout()" class="layout-btn" id="gridBtn">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button class="filter-toggle" onclick="showMap()" id="mapToggleBtn">
                        <i class="fas fa-map-marked-alt"></i>
                    </button>
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
                                $formatted_price = '$' . number_format($price);
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
                                $badge_text = 'Just listed';
                            } elseif ($days_old < 30) {
                                $badge_class = 'badge-featured';
                                $badge_text = 'Featured';
                            } else {
                                $badge_class = 'badge-standard';
                                $badge_text = 'Available';
                            }
                            ?>
                            
                            <!-- Property Card -->
                            <div class="property-card" data-property-id="<?php echo get_the_ID(); ?>">
                        <div class="property-image">
                                    <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                            <div class="gradient-overlay"></div>
                                    <div class="property-badge <?php echo esc_attr($badge_class); ?>"><?php echo esc_html($badge_text); ?></div>
                            <?php if (resbs_is_wishlist_enabled()): 
                                $property_id = get_the_ID();
                                $is_favorited = resbs_is_property_favorited($property_id);
                            ?>
                            <button class="favorite-btn resbs-favorite-btn <?php echo $is_favorited ? 'favorited' : ''; ?>" data-property-id="<?php echo esc_attr($property_id); ?>">
                                <i class="<?php echo $is_favorited ? 'fas' : 'far'; ?> fa-heart"></i>
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
                                                <span><?php echo esc_html($bedrooms); ?> beds</span>
                                </div>
                                        <?php endif; ?>
                                        <?php if ($bathrooms): ?>
                                <div class="property-feature">
                                    <i class="fas fa-bath"></i>
                                                <span><?php echo esc_html($bathrooms); ?> baths</span>
                                </div>
                                        <?php endif; ?>
                                        <?php if ($area_sqft): ?>
                                <div class="property-feature">
                                    <i class="fas fa-ruler-combined"></i>
                                                <span><?php echo resbs_format_area($area_sqft); ?></span>
                                </div>
                                        <?php endif; ?>
                            </div>
                            <div class="property-footer">
                                        <span class="property-type"><?php echo esc_html($property_type_name); ?></span>
                                        <a href="<?php echo get_permalink(); ?>" class="view-details-btn" target="_blank" onclick="console.log('Property ID: <?php echo get_the_ID(); ?>, Permalink: <?php echo get_permalink(); ?>')">
                                    View Details <i class="fas fa-arrow-right"></i>
                                        </a>
                            </div>
                        </div>
                    </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-properties-found">
                            <h3>No properties found</h3>
                            <p>Try adjusting your search criteria or browse all properties.</p>
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
                        <a href="<?php echo esc_url($reset_url); ?>" class="map-control" id="mapResetBtn" title="Reset to Default" style="cursor: pointer; z-index: 1000; text-decoration: none; color: inherit; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-redo"></i>
                        </a>
                    </div>
                    
                    <!-- Map Legend -->
                    <div class="map-legend" style="position: absolute; bottom: 20px; left: 20px; z-index: 10;">
                        <h4 class="legend-title">Legend</h4>
                        <div class="legend-items">
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #10b981;"></div>
                                <span class="legend-label">Just Listed</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #f97316;"></div>
                                <span class="legend-label">Featured</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background-color: #3b82f6;"></div>
                                <span class="legend-label">Standard</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php
            // Using OpenStreetMap with Leaflet.js - completely FREE, no API keys needed!
            // No tokens or API keys required - everything is free and unlimited
            $use_openstreetmap = true; // Always use free OpenStreetMap
            // Get map settings dynamically from General settings
            $map_zoom = resbs_get_default_zoom_level();
            $map_settings = resbs_get_map_settings('archive');
            
            // IMPORTANT: Map location is based on EACH PROPERTY's individual coordinates
            // Dashboard location settings are NOT used - each property has its own location
            // This fallback is ONLY used if NO properties have coordinates set
            $map_center_lat = 23.8103; // Fallback only - property locations take priority
            $map_center_lng = 90.4125; // Fallback only - property locations take priority
            
            // Debug: Using free OpenStreetMap
            echo '<!-- DEBUG: Using FREE OpenStreetMap with Leaflet.js - No API keys required, unlimited usage, completely free! -->';
            
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
                        echo '<!-- DEBUG: Property "' . esc_html($property_title) . '" (ID: ' . $property_id . ') has invalid coordinates, will geocode -->';
                    }
                } else {
                    // No coordinates - need geocoding
                    $needs_geocoding = true;
                    echo '<!-- DEBUG: Property "' . esc_html($property_title) . '" (ID: ' . $property_id . ') has NO coordinates, will geocode from address -->';
                }
                
                // Get location taxonomy term FIRST (most reliable for geocoding)
                $location_terms = get_the_terms($property_id, 'property_location');
                $location_name = '';
                if ($location_terms && !is_wp_error($location_terms) && !empty($location_terms)) {
                    $location_name = $location_terms[0]->name;
                }
                
                // Build address string for geocoding
                // Priority: Use location taxonomy if available, otherwise build from address fields
                $address_string = '';
                
                if (!empty($location_name)) {
                    // Use location taxonomy as primary - it's usually the most accurate (e.g., "Uttara Dhaka", "Jashore")
                    $address_string = $location_name . ', Bangladesh';
                    echo '<!-- DEBUG: Property "' . esc_html($property_title) . '" using location taxonomy: ' . esc_html($location_name) . ' -->';
                } else {
                    // Fallback to address fields
                    if ($address) $address_string .= $address;
                    if ($city) $address_string .= ($address_string ? ', ' : '') . $city;
                    if ($state) $address_string .= ($address_string ? ', ' : '') . $state;
                    if ($zip) $address_string .= ($address_string ? ' ' : '') . $zip;
                    if ($country) $address_string .= ($address_string ? ', ' : '') . $country;
                    
                    // If still empty, try city alone
                    if (empty($address_string) && $city) {
                        $address_string = $city . ', Bangladesh';
                    }
                }
                
                // ALWAYS add property to data array - even if it needs geocoding
                // Ensure needs_geocoding is TRUE if no coordinates exist
                $needs_geocoding = ($lat_float === null || $lng_float === null);
                
                $property_data = array(
                    'id' => $property_id,
                    'title' => $property_title,
                        'price' => $price ? '$' . number_format($price) : 'Price on request',
                        'bedrooms' => $bedrooms,
                        'bathrooms' => $bathrooms,
                        'area_sqft' => $area_sqft,
                        'permalink' => get_permalink(),
                        'image' => $featured_image ? $featured_image : '',
                        'marker_color' => $marker_color,
                    'days_old' => $days_old,
                    'city' => $city ? $city : '',
                    'address' => $address ? $address : '',
                    'location_name' => $location_name ? $location_name : '', // Location taxonomy term
                    'full_address' => $address_string,
                    'needs_geocoding' => $needs_geocoding // Explicitly set this
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
                    
                    // Debug: log if address string is empty
                    if (empty($address_string)) {
                        echo '<!-- WARNING: Property "' . esc_html($property_title) . '" has NO address string - will try location taxonomy or city -->';
                    }
                }
                
                // Add ALL properties to the array
                $properties_data[] = $property_data;
            endwhile;
            wp_reset_postdata();
            
            // Debug output - detailed information
            $properties_with_coords = array_filter($properties_data, function($p) { return !empty($p['lat']) && !empty($p['lng']); });
            $properties_needing_geocode = array_filter($properties_data, function($p) { return !empty($p['needs_geocoding']) && $p['needs_geocoding']; });
            
            echo '<!-- DEBUG: Total properties in query: ' . $properties_query->found_posts . ' -->';
            echo '<!-- DEBUG: Total properties being added to map: ' . count($properties_data) . ' -->';
            echo '<!-- DEBUG: Properties with valid coordinates: ' . count($properties_with_coords) . ' -->';
            echo '<!-- DEBUG: Properties needing geocoding: ' . count($properties_needing_geocode) . ' -->';
            
            if (count($properties_data) > 0) {
                echo '<!-- DEBUG: All properties list -->';
                foreach ($properties_data as $idx => $prop) {
                    if (isset($prop['lat']) && isset($prop['lng'])) {
                        echo '<!-- Property ' . ($idx + 1) . ': ' . esc_html($prop['title']) . ' - Lat: ' . $prop['lat'] . ', Lng: ' . $prop['lng'] . ' -->';
                    } else {
                        echo '<!-- Property ' . ($idx + 1) . ': ' . esc_html($prop['title']) . ' - Will geocode: ' . esc_html($prop['full_address']) . ' -->';
                    }
                }
            }
            ?>
        
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
                    'prev_text' => '<i class="fas fa-chevron-left"></i> Previous',
                    'next_text' => 'Next <i class="fas fa-chevron-right"></i>',
                ));
                ?>
                </div>
        <?php endif; ?>
        </div>
</div>

<style>
<?php
// Get color settings from General Settings
$main_color = resbs_get_main_color();
$secondary_color = resbs_get_secondary_color();

// Helper function to darken color for hover states
function darken_color($color, $percent = 10) {
    $color = ltrim($color, '#');
    $r = hexdec(substr($color, 0, 2));
    $g = hexdec(substr($color, 2, 2));
    $b = hexdec(substr($color, 4, 2));
    $r = max(0, min(255, $r - ($r * $percent / 100)));
    $g = max(0, min(255, $g - ($g * $percent / 100)));
    $b = max(0, min(255, $b - ($b * $percent / 100)));
    return '#' . str_pad(dechex($r), 2, '0', STR_PAD_LEFT) . str_pad(dechex($g), 2, '0', STR_PAD_LEFT) . str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
}

// Helper function to convert hex to rgba for box-shadow
function hex_to_rgba($hex, $alpha = 0.3) {
    $hex = ltrim($hex, '#');
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    return "rgba($r, $g, $b, $alpha)";
}

$main_color_dark = darken_color($main_color, 10);
$secondary_color_dark = darken_color($secondary_color, 10);
$main_color_rgba = hex_to_rgba($main_color, 0.3);
?>
/* Main Layout Fixes */
.rbs-archive {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.main-content {
    flex: 1;
    padding-bottom: 60px;
}

.listings-container {
    margin-bottom: 40px;
}

.pagination-container {
    margin-top: 40px;
    margin-bottom: 40px;
}

/* Property Grid Layout - Conditional columns */
.property-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* Default: 4 columns when map is hidden */
    gap: 20px;
    margin-bottom: 40px;
}

/* When map is visible, show only 2 columns */
.listings-container.map-visible .property-grid {
    grid-template-columns: repeat(2, 1fr) !important;
}

/* Additional specific rule to ensure 2 columns when map is visible */
.rbs-archive .listings-container.map-visible .properties-list .property-grid {
    grid-template-columns: repeat(2, 1fr) !important;
}

/* Property Card Styling */
.property-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.property-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.property-card.highlighted {
    border: 2px solid #10b981;
    transform: translateY(-2px);
}

.no-properties-found {
    padding: 60px 20px;
    text-align: center;
    background-color: #f9fafb;
    border-radius: 12px;
    margin: 40px 0;
}

.no-properties-found h3 {
    color: #374151;
    margin-bottom: 12px;
    font-size: 24px;
    font-weight: 600;
}

.no-properties-found p {
    color: #6b7280;
    font-size: 16px;
    margin: 0;
}

/* Additional spacing improvements */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px !important; /* Ensure padding is always applied */
}

/* Ensure main-content container maintains padding */
.container.main-content {
    padding-left: 20px !important;
    padding-right: 20px !important;
}

/* Ensure property grid respects container padding */
.listings-container {
    width: 100%;
    margin-bottom: 40px;
}

.properties-list {
    width: 100%;
}

.property-grid {
    width: 100%;
    box-sizing: border-box;
}

/* Ensure proper footer spacing */
body {
    margin: 0;
    padding: 0;
}

/* Map Visibility Controls */
.map-section {
    display: none; /* Hidden by default */
    transition: all 0.3s ease;
}

.map-section.map-visible {
    display: block;
}

.map-section.map-hidden {
    display: none;
}

/* Layout adjustments when map is visible */
.listings-container.map-visible {
    display: flex;
    gap: 20px;
    height: 85vh; /* Full height for map view */
    min-height: 85vh;
}

.listings-container.map-visible .properties-list {
    flex: 1;
    overflow-y: auto; /* Make it scrollable */
    height: 100%; /* Full height */
    padding-right: 10px; /* Add some space for scrollbar */
}

/* Custom scrollbar styling */
.listings-container.map-visible .properties-list::-webkit-scrollbar {
    width: 8px;
}

.listings-container.map-visible .properties-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.listings-container.map-visible .properties-list::-webkit-scrollbar-thumb {
    background: #10b981;
    border-radius: 10px;
}

.listings-container.map-visible .properties-list::-webkit-scrollbar-thumb:hover {
    background: #059669;
}

.listings-container.map-visible .map-section {
    flex: 1;
    display: block;
    height: 100%; /* Full height to match properties list */
}

.listings-container.map-visible .map-section .map-container {
    height: 100%;
    min-height: 500px;
    position: relative;
    width: 100%;
}

.listings-container.map-visible .map-section {
    min-height: 500px;
    height: 100%;
}

#googleMap {
    width: 100% !important;
    height: 100% !important;
    min-height: 500px !important;
    position: relative !important;
    background: transparent !important;
    position: relative;
    z-index: 1;
    display: block;
}

/* Ensure map is visible when section is visible */
.map-section.map-visible #googleMap {
    display: block !important;
    visibility: visible !important;
}

/* Map Legend positioning */
.map-legend {
    position: absolute;
    bottom: 20px;
    left: 20px;
    background: white;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    z-index: 100;
}

/* Responsive Grid Layout */
/* Large screens (1200px+) */
@media (min-width: 1200px) {
    .property-grid {
        grid-template-columns: repeat(4, 1fr);
    }
    
    .listings-container.map-visible .property-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

/* Medium screens (768px - 1199px) */
@media (min-width: 768px) and (max-width: 1199px) {
    .property-grid {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .listings-container.map-visible .property-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

/* Small screens (below 768px) */
@media (max-width: 767px) {
    .main-content {
        padding-bottom: 40px;
    }
    
    .listings-container {
        margin-bottom: 30px;
    }
    
    .pagination-container {
        margin-top: 30px;
        margin-bottom: 30px;
    }
    
    .property-grid {
        margin-bottom: 30px;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    /* Mobile button design */
    .control-bar {
        flex-direction: column;
        gap: 20px;
        align-items: stretch;
    }
    
    .view-controls {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .view-btn {
        padding: 10px 16px;
        font-size: 13px;
    }
    
    .sort-controls {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    /* Mobile map layout */
    .listings-container.map-visible {
        flex-direction: column;
        min-height: auto;
    }
    
    .listings-container.map-visible .properties-list {
        max-height: 50vh;
        overflow-y: auto;
    }
    
    .listings-container.map-visible .map-section {
        order: 2;
        margin-top: 20px;
        min-height: 50vh;
    }
    
    .listings-container.map-visible .property-grid {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

/* Extra small screens (below 480px) */
@media (max-width: 479px) {
    .property-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .listings-container.map-visible .property-grid {
        grid-template-columns: 1fr;
    }
}

/* View Control Buttons Design */
.view-controls {
    display: flex;
    align-items: center;
    gap: 15px;
}

.view-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    outline: none;
}

.view-btn:hover {
    border-color: <?php echo esc_attr($main_color); ?>;
    color: <?php echo esc_attr($main_color); ?>;
    background: #f0fdf4;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px <?php echo esc_attr($main_color_rgba); ?>;
}

.view-btn.active {
    background: <?php echo esc_attr($main_color); ?>;
    border-color: <?php echo esc_attr($main_color); ?>;
    color: white;
    box-shadow: 0 4px 12px <?php echo esc_attr($main_color_rgba); ?>;
}

.view-btn.active:hover {
    background: <?php echo esc_attr($main_color_dark); ?>;
    border-color: <?php echo esc_attr($main_color_dark); ?>;
    color: white;
}

.view-btn i {
    font-size: 16px;
}

.view-divider {
    color: #d1d5db;
    font-weight: 300;
    font-size: 18px;
}

.results-count {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}

/* Control Bar Layout */
.control-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 30px 0;
    padding: 20px 0;
    border-bottom: 1px solid #e5e7eb;
    position: relative;
    overflow: visible;
    z-index: 10;
}

.right-controls {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}

.sort-controls {
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
    z-index: 100;
}

.sort-label {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
}

.sort-select {
    padding: 8px 35px 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
    color: #374151;
    font-size: 14px;
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s ease;
    min-width: 150px;
    width: auto;
    position: relative;
    z-index: 101;
    appearance: none;
    background-image: url('data:image/svg+xml;charset=UTF-8,%3csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'%23374151\' stroke-width=\'2\' stroke-linecap=\'round\' stroke-linejoin=\'round\'%3e%3cpolyline points=\'6 9 12 15 18 9\'%3e%3c/polyline%3e%3c/svg%3e');
    background-repeat: no-repeat;
    background-position: right 10px center;
    background-size: 16px;
}

.sort-select:hover,
.sort-select:focus {
    border-color: <?php echo esc_attr($main_color); ?>;
    z-index: 102;
}

.sort-select option {
    padding: 8px 12px;
    background: white;
    color: #374151;
}

.layout-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-direction: row;
}

.layout-btn,
.filter-toggle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    padding: 0;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: white;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.3s ease;
    outline: none;
    font-size: 16px;
}

.layout-btn:hover,
.filter-toggle:hover {
    border-color: <?php echo esc_attr($main_color); ?>;
    color: <?php echo esc_attr($main_color); ?>;
    background: #f0fdf4;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px <?php echo esc_attr($main_color_rgba); ?>;
}

.layout-btn.active,
.filter-toggle.active {
    background: <?php echo esc_attr($main_color); ?>;
    border-color: <?php echo esc_attr($main_color); ?>;
    color: white;
    box-shadow: 0 4px 12px <?php echo esc_attr($main_color_rgba); ?>;
}

.layout-btn.active:hover,
.filter-toggle.active:hover {
    background: <?php echo esc_attr($main_color_dark); ?>;
    border-color: <?php echo esc_attr($main_color_dark); ?>;
    color: white;
}

.layout-btn i,
.filter-toggle i {
    font-size: 16px;
}

/* Filter Action Buttons */
.filter-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e5e7eb;
}

.apply-filter-btn, .clear-filter-btn {
    flex: 1;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.apply-filter-btn {
    background-color: <?php echo esc_attr($main_color); ?>;
    color: white;
    font-weight: 600;
}

.apply-filter-btn:hover {
    background-color: <?php echo esc_attr($main_color_dark); ?>;
    color: white;
}

.clear-filter-btn {
    background-color: #ef4444;
    color: white;
    font-weight: 600;
}

.clear-filter-btn:hover {
    background-color: #dc2626;
    color: white;
}

.apply-filter-btn i, .clear-filter-btn i {
    font-size: 12px;
}

/* Property Footer Meta Styling */
.property-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-top: 1px solid #e5e7eb;
}

.property-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.property-type {
    color: #6b7280;
    font-size: 13px;
    font-weight: 500;
}

.property-date {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #6b7280;
    font-size: 12px;
}

.property-date i {
    font-size: 11px;
    color: #9ca3af;
}

.view-details-btn {
    padding: 6px 12px;
    background: <?php echo esc_attr($main_color); ?>;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.view-details-btn:hover {
    background: <?php echo esc_attr($main_color_dark); ?>;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px <?php echo esc_attr($main_color_rgba); ?>;
}

.view-details-btn i {
    font-size: 11px;
}

/* Search Button */
.search-btn {
    padding: 12px 24px;
    background: <?php echo esc_attr($main_color); ?>;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.search-btn:hover {
    background: <?php echo esc_attr($main_color_dark); ?>;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px <?php echo esc_attr($main_color_rgba); ?>;
}

.search-btn i {
    font-size: 14px;
}
</style>

<script>
// Simple dropdown toggle functionality
function toggleDropdown(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const allDropdowns = document.querySelectorAll('.dropdown-content');
    
    // Close all other dropdowns
    allDropdowns.forEach(dd => {
        if (dd.id !== dropdownId) {
            dd.style.display = 'none';
        }
    });
    
    // Toggle current dropdown
    if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    } else {
        dropdown.style.display = 'block';
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.dropdown-content');
    const filterButtons = document.querySelectorAll('.filter-chip');
    
    let clickedInsideDropdown = false;
    let clickedInsideFilterButton = false;
    
    // Check if click was inside a dropdown
    dropdowns.forEach(dropdown => {
        if (dropdown.contains(event.target)) {
            clickedInsideDropdown = true;
        }
    });
    
    // Check if click was inside a filter button
    filterButtons.forEach(button => {
        if (button.contains(event.target)) {
            clickedInsideFilterButton = true;
        }
    });
    
    // Close all dropdowns if click was outside
    if (!clickedInsideDropdown && !clickedInsideFilterButton) {
        dropdowns.forEach(dropdown => {
            dropdown.style.display = 'none';
        });
    }
});

// Clear price filter function
function clearPriceFilter() {
    document.querySelector('input[name="min_price"]').value = '';
    document.querySelector('input[name="max_price"]').value = '';
    document.getElementById('searchForm').submit();
}

// Clear type filter function
function clearTypeFilter() {
    // Select the "Any Type" radio button
    document.querySelector('input[name="property_type"][value=""]').checked = true;
    document.getElementById('searchForm').submit();
}

// Clear bedrooms filter function
function clearBedroomsFilter() {
    document.querySelector('input[name="min_bedrooms"]').value = '';
    document.querySelector('input[name="max_bedrooms"]').value = '';
    document.getElementById('searchForm').submit();
}

// Clear bathrooms filter function
function clearBathroomsFilter() {
    document.querySelector('input[name="min_bathrooms"]').value = '';
    document.querySelector('input[name="max_bathrooms"]').value = '';
    document.getElementById('searchForm').submit();
}

// Clear more filters function
function clearMoreFilters() {
    document.querySelector('input[name="min_sqft"]').value = '';
    document.querySelector('input[name="max_sqft"]').value = '';
    document.querySelector('select[name="year_built"]').value = '';
    document.querySelector('select[name="property_status"]').value = '';
    document.getElementById('searchForm').submit();
}

// Reset all filters to default (already defined above as window.resetAllFilters)

// Handle sort change dynamically
function handleSortChange(sortValue) {
    // Get current URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    
    // Update sort_by parameter
    urlParams.set('sort_by', sortValue);
    
    // Remove paged parameter to go back to page 1
    urlParams.delete('paged');
    
    // Build new URL with updated parameters
    const newUrl = window.location.pathname + '?' + urlParams.toString();
    
    // Navigate to new URL
    window.location.href = newUrl;
}

// Show Map View - Always show map when clicked
function showMapView() {
    const mapSection = document.querySelector('.map-section');
    const listingsContainer = document.querySelector('.listings-container');
    const viewButtons = document.querySelectorAll('.view-btn');
    const mapToggleBtn = document.getElementById('mapToggleBtn');
    
    // Remove active class from all view buttons
    viewButtons.forEach(btn => btn.classList.remove('active'));
    
    // Always show map view
    mapSection.classList.remove('map-hidden');
    mapSection.classList.add('map-visible');
    listingsContainer.classList.add('map-visible');
    
    // Update button states
    document.querySelector('.view-btn[onclick="showMapView()"]').classList.add('active');
    if (mapToggleBtn) mapToggleBtn.classList.add('active');
    
    console.log('Map view activated. Listings container classes:', listingsContainer.className);
}

// Show List View - Always hide map when clicked
function showListView() {
    const mapSection = document.querySelector('.map-section');
    const listingsContainer = document.querySelector('.listings-container');
    const viewButtons = document.querySelectorAll('.view-btn');
    const mapToggleBtn = document.getElementById('mapToggleBtn');
    
    // Remove active class from all view buttons
    viewButtons.forEach(btn => btn.classList.remove('active'));
    
    // Always hide map view
    mapSection.classList.remove('map-visible');
    mapSection.classList.add('map-hidden');
    listingsContainer.classList.remove('map-visible');
    
    // Update button states
    document.querySelector('.view-btn[onclick="showListView()"]').classList.add('active');
    if (mapToggleBtn) mapToggleBtn.classList.remove('active');
    
    console.log('List view activated. Map hidden.');
}

// Show map function - Always show map when clicked
function showMap() {
    const mapSection = document.querySelector('.map-section');
    const listingsContainer = document.querySelector('.listings-container');
    const mapToggleBtn = document.getElementById('mapToggleBtn');
    const gridBtn = document.getElementById('gridBtn');
    
    // Always show map
    mapSection.classList.remove('map-hidden');
    mapSection.classList.add('map-visible');
    listingsContainer.classList.add('map-visible');
    
    // Update button states
    if (mapToggleBtn) mapToggleBtn.classList.add('active');
    if (gridBtn) gridBtn.classList.remove('active');
    
    // Update view buttons
    document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector('.view-btn[onclick="showMapView()"]').classList.add('active');
    
    console.log('Map button clicked - Map shown');
}

// Show grid layout function - Always hide map when clicked
function showGridLayout() {
    const mapSection = document.querySelector('.map-section');
    const listingsContainer = document.querySelector('.listings-container');
    const propertyGrid = document.getElementById('propertyGrid');
    const gridBtn = document.getElementById('gridBtn');
    const mapToggleBtn = document.getElementById('mapToggleBtn');
    
    // Always hide map
    mapSection.classList.remove('map-visible');
    mapSection.classList.add('map-hidden');
    listingsContainer.classList.remove('map-visible');
    
    if (propertyGrid) {
        propertyGrid.classList.remove('list-view');
    }
    
    // Update button states
    if (gridBtn) gridBtn.classList.add('active');
    if (mapToggleBtn) mapToggleBtn.classList.remove('active');
    
    // Update view buttons
    document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelector('.view-btn[onclick="showListView()"]').classList.add('active');
    
    console.log('Grid button clicked - Map hidden');
}

// Highlight property function (for map markers)
function highlightProperty(propertyId) {
    // Remove active class from all property cards
    document.querySelectorAll('.property-card').forEach(card => {
        card.classList.remove('highlighted');
    });
    
    // Add active class to selected property card
    const selectedCard = document.querySelector(`[data-property-id="${propertyId}"]`);
    if (selectedCard) {
        selectedCard.classList.add('highlighted');
        selectedCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    // Update map marker states
    document.querySelectorAll('.map-marker').forEach(marker => {
        marker.classList.remove('active');
    });
    
    const selectedMarker = document.querySelector(`.map-marker[data-property-id="${propertyId}"]`);
    if (selectedMarker) {
        selectedMarker.classList.add('active');
    }
}

// Toast notification function
function showToastNotification(message, type) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.resbs-toast-notification');
    existingToasts.forEach(toast => toast.remove());
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'resbs-toast-notification resbs-toast-' + (type || 'success');
    toast.innerHTML = '<span class="resbs-toast-message">' + message + '</span><button class="resbs-toast-close">&times;</button>';
    
    // Add to body
    document.body.appendChild(toast);
    
    // Show toast with animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Auto-hide after 3 seconds
    const autoHide = setTimeout(() => {
        hideToast(toast);
    }, 3000);
    
    // Close button click
    toast.querySelector('.resbs-toast-close').addEventListener('click', () => {
        clearTimeout(autoHide);
        hideToast(toast);
    });
}

function hideToast(toast) {
    toast.classList.remove('show');
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 300);
}

// Add toast notification styles
const toastStyle = document.createElement('style');
toastStyle.textContent = `
    .resbs-toast-notification {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #10b981;
        color: white;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        gap: 12px;
        z-index: 10000;
        min-width: 250px;
        max-width: 400px;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
    }
    .resbs-toast-notification.show {
        opacity: 1;
        transform: translateY(0);
    }
    .resbs-toast-notification.resbs-toast-success {
        background: #10b981;
    }
    .resbs-toast-notification.resbs-toast-error {
        background: #ef4444;
    }
    .resbs-toast-message {
        flex: 1;
    }
    .resbs-toast-close {
        background: transparent;
        border: none;
        color: white;
        font-size: 20px;
        line-height: 1;
        cursor: pointer;
        padding: 0;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    .resbs-toast-close:hover {
        opacity: 1;
    }
`;
document.head.appendChild(toastStyle);

// Reset all filters to default - Make it global
window.resetAllFilters = function() {
    try {
        console.log('resetAllFilters called - redirecting to base URL');
        
        // Get the base URL without any query parameters
        const baseUrl = window.location.pathname;
        
        // Add cache-busting parameter to force fresh load, then redirect to clean URL
        // This ensures browser doesn't use cached version with old filter values
        const timestamp = Date.now();
        window.location.href = baseUrl + '?reset=' + timestamp;
        
        // Note: After page loads with reset parameter, PHP will see it's not a filter
        // and will show default/empty values since other GET params are missing
        
    } catch (error) {
        console.error('Error resetting filters:', error);
        // Fallback: reload page without query params
        window.location.href = window.location.pathname;
    }
};

// Favorite button functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize favorite button states on page load
    function initializeFavoriteButtons() {
        const favoriteButtons = document.querySelectorAll('.favorite-btn, .resbs-favorite-btn');
        favoriteButtons.forEach(function(btn) {
            const propertyId = btn.getAttribute('data-property-id');
            if (!propertyId) return;
            
            const icon = btn.querySelector('i');
            if (!icon) return;
            
            // Check if button already has favorited class (set by PHP)
            if (btn.classList.contains('favorited')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
        });
    }
    
    // Initialize buttons on page load
    initializeFavoriteButtons();
    
    // Map reset button is now a direct link, no JavaScript needed
    // But keep the function for any other uses
    
    // Handle favorite button clicks
    document.addEventListener('click', function(e) {
        const favoriteBtn = e.target.closest('.favorite-btn, .resbs-favorite-btn');
        if (!favoriteBtn) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const propertyId = favoriteBtn.getAttribute('data-property-id');
        if (!propertyId) {
            console.error('Property ID not found');
            return;
        }
        
        const icon = favoriteBtn.querySelector('i');
        if (!icon) return;
        
        // Toggle visual state immediately for better UX
        const isFavorited = favoriteBtn.classList.contains('favorited');
        if (isFavorited) {
            favoriteBtn.classList.remove('favorited');
            icon.classList.remove('fas');
            icon.classList.add('far');
        } else {
            favoriteBtn.classList.add('favorited');
            icon.classList.remove('far');
            icon.classList.add('fas');
        }
        
        // Make AJAX request
        const formData = new FormData();
        formData.append('action', 'resbs_toggle_favorite');
        formData.append('property_id', propertyId);
        
        // Generate nonce - ensure it's fresh
        const nonce = '<?php echo esc_js(wp_create_nonce('resbs_favorites_nonce')); ?>';
        if (!nonce) {
            alert('<?php echo esc_js(__('Unable to generate security token. Please refresh the page.', 'realestate-booking-suite')); ?>');
            return;
        }
        formData.append('nonce', nonce);
        
        fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (!data) {
                throw new Error('Invalid response from server');
            }
            
            if (data.success) {
                // Success - update button state
                if (data.data && data.data.is_favorite) {
                    favoriteBtn.classList.add('favorited');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    favoriteBtn.classList.remove('favorited');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
                
                // Show success message as toast notification
                if (data.data && data.data.message) {
                    showToastNotification(data.data.message, 'success');
                }
            } else {
                // Error - revert visual state
                if (isFavorited) {
                    favoriteBtn.classList.add('favorited');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    favoriteBtn.classList.remove('favorited');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
                
                // Show error message
                let errorMessage = '<?php echo esc_js(__('An error occurred. Please try again.', 'realestate-booking-suite')); ?>';
                
                if (data && data.data) {
                    // data.data can be a string or an object
                    if (typeof data.data === 'string') {
                        errorMessage = data.data;
                    } else if (data.data.message) {
                        errorMessage = data.data.message;
                    }
                }
                
                alert(errorMessage);
            }
        })
        .catch(error => {
            console.error('Favorite button error:', error);
            
            // Revert visual state on error
            if (isFavorited) {
                favoriteBtn.classList.add('favorited');
                icon.classList.remove('far');
                icon.classList.add('fas');
            } else {
                favoriteBtn.classList.remove('favorited');
                icon.classList.remove('fas');
                icon.classList.add('far');
            }
            
            alert('<?php echo esc_js(__('An error occurred. Please try again.', 'realestate-booking-suite')); ?>');
        });
    });
    
    const mapSection = document.querySelector('.map-section');
    const listingsContainer = document.querySelector('.listings-container');
    
    // Ensure map starts hidden
    if (mapSection) {
        mapSection.classList.add('map-hidden');
        mapSection.classList.remove('map-visible');
    }
    
    // Ensure listings container starts in list view
    if (listingsContainer) {
        listingsContainer.classList.remove('map-visible');
    }
    
    // Set initial button states
    const listViewBtn = document.querySelector('.view-btn[onclick="showListView()"]');
    const mapViewBtn = document.querySelector('.view-btn[onclick="showMapView()"]');
    const mapToggleBtn = document.getElementById('mapToggleBtn');
    const gridBtn = document.getElementById('gridBtn');
    
    if (listViewBtn) listViewBtn.classList.add('active');
    if (mapViewBtn) mapViewBtn.classList.remove('active');
    if (mapToggleBtn) mapToggleBtn.classList.remove('active');
    if (gridBtn) gridBtn.classList.add('active');
    
    // Initialize OpenStreetMap (free, no setup required)
    <?php if ($use_openstreetmap): ?>
    initializeOpenStreetMap();
    <?php endif; ?>
});
</script>

<?php if ($use_openstreetmap): ?>
<!-- Leaflet.js CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="anonymous" onerror="console.warn('⚠️ Leaflet CSS failed to load, trying alternate CDN'); this.onerror=null; this.href='https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css';"/>
<!-- Leaflet.markercluster CSS (for marker clustering) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" crossorigin="anonymous" />
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" crossorigin="anonymous" />
<!-- Leaflet.js JavaScript -->
<script>
// Load Leaflet.js and initialize when ready
(function() {
    var leafletScript = document.createElement('script');
    leafletScript.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
    leafletScript.crossOrigin = 'anonymous';
    leafletScript.async = true;
    leafletScript.defer = true;
    
    var loadAttempts = 0;
    var maxAttempts = 3;
    
    function loadLeaflet() {
        loadAttempts++;
        console.log('📦 Loading Leaflet.js (attempt ' + loadAttempts + ')...');
        
        var newScript = document.createElement('script');
        newScript.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        newScript.crossOrigin = 'anonymous';
        newScript.async = true;
        
        newScript.onload = function() {
            console.log('✅ Leaflet.js script loaded successfully');
            // Load Leaflet.markercluster after Leaflet is loaded
            loadMarkerCluster();
        };
        
        function loadMarkerCluster() {
            // Load Leaflet.markercluster CSS (already in head)
            // Load Leaflet.markercluster JS
            var clusterScript = document.createElement('script');
            clusterScript.src = 'https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js';
            clusterScript.crossOrigin = 'anonymous';
            clusterScript.async = true;
            clusterScript.onload = function() {
                console.log('✅ Leaflet.markercluster loaded successfully');
                // Both libraries loaded, initialization will happen below
                if (typeof window.initLeafletWhenReady === 'function') {
                    window.initLeafletWhenReady();
                }
            };
            clusterScript.onerror = function() {
                console.warn('⚠️ Failed to load Leaflet.markercluster, trying alternate CDN...');
                var altClusterScript = document.createElement('script');
                altClusterScript.src = 'https://cdn.jsdelivr.net/npm/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js';
                altClusterScript.crossOrigin = 'anonymous';
                altClusterScript.async = true;
                altClusterScript.onload = function() {
                    console.log('✅ Leaflet.markercluster loaded from alternate CDN');
                    if (typeof window.initLeafletWhenReady === 'function') {
                        window.initLeafletWhenReady();
                    }
                };
                altClusterScript.onerror = function() {
                    console.warn('⚠️ Failed to load Leaflet.markercluster, clustering will be disabled');
                    // Still initialize map without clustering
                    if (typeof window.initLeafletWhenReady === 'function') {
                        window.initLeafletWhenReady();
                    }
                };
                document.head.appendChild(altClusterScript);
            };
            document.head.appendChild(clusterScript);
        }
        
        newScript.onerror = function() {
            console.warn('⚠️ Failed to load Leaflet.js (attempt ' + loadAttempts + ')');
            if (loadAttempts < maxAttempts) {
                // Try alternate CDN
                console.log('🔄 Trying alternate CDN...');
                var altScript = document.createElement('script');
                altScript.src = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js';
                altScript.crossOrigin = 'anonymous';
                altScript.async = true;
                altScript.onload = function() {
                    console.log('✅ Leaflet.js loaded from alternate CDN');
                    // Load marker cluster after Leaflet loads
                    loadMarkerCluster();
                };
                altScript.onerror = function() {
                    if (loadAttempts >= maxAttempts) {
                        console.error('❌ Failed to load Leaflet.js from all sources after ' + maxAttempts + ' attempts');
                        // Don't show error immediately - let initLeafletWhenReady handle it with delay
                    }
                };
                document.head.appendChild(altScript);
            } else {
                console.error('❌ Failed to load Leaflet.js from all sources');
                // Error will be handled by initLeafletWhenReady with proper delay
            }
        };
        
        document.head.appendChild(newScript);
    }
    
    // Start loading
    loadLeaflet();
})();
</script>

<script>
console.log('=== OpenStreetMap (Leaflet) Integration Started ===');
console.log('Using FREE OpenStreetMap - No API keys, no billing, unlimited usage!');
console.log('Total properties in query:', <?php echo $properties_query->found_posts; ?>);
console.log('Total properties being added to map:', <?php echo count($properties_data); ?>);

// Leaflet Variables - Must be global
window.map = null;
window.markers = [];
window.popups = [];
window.markerClusterGroup = null; // For marker clustering
window.propertiesData = <?php echo json_encode($properties_data); ?>;
window.mapInitialized = false;
// Map settings from General settings - dynamically applied
window.resbsMapSettings = <?php echo json_encode($map_settings); ?>;

console.log('=== PROPERTIES DATA DEBUG ===');
console.log('All properties data:', window.propertiesData);
if (window.propertiesData && window.propertiesData.length > 0) {
    console.log('Property details:');
    window.propertiesData.forEach(function(prop, idx) {
        if (prop.lat && prop.lng) {
            console.log('  [' + (idx + 1) + ']', prop.title, '- Has coordinates - Lat:', prop.lat, ', Lng:', prop.lng);
        } else {
            console.log('  [' + (idx + 1) + ']', prop.title, '- Needs geocoding:', prop.full_address || 'No address');
        }
    });
} else {
    console.warn('⚠️ WARNING: No properties found!');
}

// Initialize OpenStreetMap with Leaflet
function initializeOpenStreetMap() {
    console.log('=== initializeOpenStreetMap called ===');
    console.log('Leaflet.js loaded:', typeof L !== 'undefined');
    
    if (typeof L === 'undefined') {
        console.warn('⚠️ Leaflet.js is not loaded yet, will wait...');
        // Don't show error immediately - wait a bit more for it to load
        setTimeout(function() {
            if (typeof L !== 'undefined') {
                console.log('✅ Leaflet.js loaded, initializing map...');
                initializeOpenStreetMap();
            } else {
                console.error('❌ Leaflet.js failed to load after additional wait');
                // Only show error if map container is visible
                const mapContainer = document.getElementById('googleMap');
                if (mapContainer && mapContainer.offsetParent !== null) {
                    showMapError('Map library failed to load. Please refresh the page.');
                }
            }
        }, 1000);
        return;
    }
    
    console.log('Properties data:', window.propertiesData ? window.propertiesData.length + ' properties' : 'none');
    
    const mapContainer = document.getElementById('googleMap'); // Keep same container ID
    if (!mapContainer) {
        console.error('❌ Map container (#googleMap) not found in DOM');
        return;
    }
    
    // Remove any existing error messages first
    const existingError = mapContainer.querySelector('.resbs-map-error');
    if (existingError) {
        existingError.remove();
        console.log('✅ Removed existing error message');
    }
    
    // Clear any error HTML that might have been set directly
    if (mapContainer.innerHTML.includes('Map Library Failed to Load') || 
        mapContainer.innerHTML.includes('⚠️') ||
        mapContainer.textContent.includes('Map Error')) {
        mapContainer.innerHTML = ''; // Clear error content
        console.log('✅ Cleared error HTML from map container');
    }
    
    // Check if map already exists
    if (window.map) {
        console.log('✅ Map already initialized, skipping...');
        // Still resize it in case container became visible
        setTimeout(function() {
            if (window.map) {
                window.map.invalidateSize();
            }
        }, 100);
        return;
    }
    
    console.log('Map container found:', mapContainer);
    console.log('Map container visible:', mapContainer.offsetParent !== null);
    console.log('Map container dimensions:', mapContainer.offsetWidth + 'x' + mapContainer.offsetHeight);
    
    // Initialize even if hidden - Leaflet can initialize on hidden containers
    // We'll call invalidateSize() when it becomes visible
    if (mapContainer.offsetParent === null) {
        console.log('⏳ Map container is hidden, but will initialize anyway (will resize when shown)');
    }
    
    // IMPORTANT: Show ALL properties on map - geocode those without coordinates
    let centerLat = <?php echo esc_js($map_center_lat); ?>; // Fallback only
    let centerLng = <?php echo esc_js($map_center_lng); ?>; // Fallback only
    
    // Separate properties with and without coordinates
    const propertiesWithCoords = [];
    const propertiesNeedingGeocode = [];
    
    if (window.propertiesData && window.propertiesData.length > 0) {
        window.propertiesData.forEach(function(property) {
            const lat = parseFloat(property.lat);
            const lng = parseFloat(property.lng);
            
            // Check if property has valid coordinates
            const hasValidCoords = !isNaN(lat) && !isNaN(lng) && 
                lat >= -90 && lat <= 90 && 
                lng >= -180 && lng <= 180 &&
                lat !== 0 && lng !== 0;
            
            if (hasValidCoords) {
                // Has valid coordinates - add directly to map
                propertiesWithCoords.push(property);
                console.log('✅ Property with coordinates:', property.title, 'at', lat, ',', lng);
            } else {
                // No valid coordinates - needs geocoding
                // Ensure we have an address for geocoding
                if (!property.full_address || property.full_address.trim() === '') {
                    // Priority 1: Use location_name (location taxonomy term) if available - most reliable
                    if (property.location_name && property.location_name.trim() !== '') {
                        property.full_address = property.location_name + ', Bangladesh';
                        console.log('📍 Property needs geocoding (using location taxonomy):', property.title, '- Location:', property.full_address);
                    }
                    // Priority 2: Try city
                    else if (property.city && property.city.trim() !== '') {
                        property.full_address = property.city + ', Bangladesh';
                        console.log('📍 Property needs geocoding (using city):', property.title, '- Address:', property.full_address);
                    }
                    // Priority 3: Try address field
                    else if (property.address && property.address.trim() !== '') {
                        property.full_address = property.address + ', Bangladesh';
                        console.log('📍 Property needs geocoding (using address):', property.title, '- Address:', property.full_address);
                    }
                }
                
                // If still no address, try property title as last resort
                if (!property.full_address || property.full_address.trim() === '') {
                    // Try using location name from property if available (e.g., "Uttara Dhaka", "Jashore")
                    const locationMatch = property.title.match(/(?:real estate|property|wali|Copy)/i);
                    if (!locationMatch && property.title.trim() !== '') {
                        property.full_address = property.title + ', Bangladesh';
                        console.log('📍 Property needs geocoding (using title):', property.title, '- Address:', property.full_address);
                    } else {
                        // Try to extract location from title or use a default
                        property.full_address = property.title.replace(/Copy/gi, '').replace(/\s+/g, ' ').trim() + ', Bangladesh';
                        if (property.full_address === ', Bangladesh') {
                            property.full_address = 'Dhaka, Bangladesh'; // Default fallback
                        }
                        console.log('📍 Property needs geocoding (using modified title):', property.title, '- Address:', property.full_address);
                    }
                }
                
                // Always add to geocoding queue if we have any address text
                if (property.full_address && property.full_address.trim() !== '') {
                    property.needs_geocoding = true; // Ensure this is set
                    propertiesNeedingGeocode.push(property);
                    console.log('✅ Property added to geocoding queue:', property.title, '- Will geocode:', property.full_address);
                } else {
                    console.error('❌ Property cannot be geocoded - no address available:', property.title);
                }
            }
        });
    }
    
    console.log('📊 Summary:');
    console.log('  - Total properties:', window.propertiesData ? window.propertiesData.length : 0);
    console.log('  - Properties with coordinates:', propertiesWithCoords.length);
    console.log('  - Properties needing geocoding:', propertiesNeedingGeocode.length);
    console.log('  - Total properties that will appear on map:', propertiesWithCoords.length + propertiesNeedingGeocode.length);
    
    // Log all properties for debugging
    if (window.propertiesData && window.propertiesData.length > 0) {
        console.log('📋 DETAILED PROPERTIES LIST:');
        window.propertiesData.forEach(function(prop, idx) {
            const lat = parseFloat(prop.lat);
            const lng = parseFloat(prop.lng);
            if (!isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0) {
                console.log('  [' + (idx + 1) + '] ✅', prop.title, '- HAS COORDINATES:', lat, ',', lng);
            } else if (prop.needs_geocoding) {
                console.log('  [' + (idx + 1) + '] 📍', prop.title, '- WILL GEOCODE:', prop.full_address || prop.city || prop.title || 'NO ADDRESS');
            } else {
                console.log('  [' + (idx + 1) + '] ⚠️', prop.title, '- SKIPPED (needs_geocoding=false, no coords)');
            }
        });
    }
    
    // Calculate map center from properties with coordinates
    if (propertiesWithCoords.length > 0) {
        let minLat = Infinity, maxLat = -Infinity;
        let minLng = Infinity, maxLng = -Infinity;
        
        propertiesWithCoords.forEach(function(property) {
            const lat = parseFloat(property.lat);
            const lng = parseFloat(property.lng);
            minLat = Math.min(minLat, lat);
            maxLat = Math.max(maxLat, lat);
            minLng = Math.min(minLng, lng);
            maxLng = Math.max(maxLng, lng);
        });
        
        centerLat = (minLat + maxLat) / 2;
        centerLng = (minLng + maxLng) / 2;
        console.log('✅ Map centered on properties with coordinates:', centerLat, centerLng);
    }
    
    try {
        console.log('🗺️ Creating OpenStreetMap with Leaflet...');
        // Get zoom from map settings (dynamically from General settings)
        const mapZoom = window.resbsMapSettings ? window.resbsMapSettings.zoom : <?php echo esc_js($map_zoom); ?>;
        console.log('📍 Using zoom level:', mapZoom, '(from General settings)');
        
        // Create Leaflet map with OpenStreetMap tiles
        window.map = L.map('googleMap', {
            center: [centerLat, centerLng],
            zoom: mapZoom,
            zoomControl: true
        });
        
        // Add OpenStreetMap tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(window.map);
        
        console.log('✅ OpenStreetMap loaded successfully');
        
        // Add markers for properties that already have coordinates
        if (propertiesWithCoords.length > 0) {
            console.log('📍 Adding', propertiesWithCoords.length, 'properties with coordinates to map');
            addLeafletMarkers(propertiesWithCoords);
            
            // Fit bounds to show all properties with coordinates
            if (propertiesWithCoords.length > 1) {
                const bounds = L.latLngBounds([]);
                propertiesWithCoords.forEach(function(property) {
                    bounds.extend([parseFloat(property.lat), parseFloat(property.lng)]);
                });
                window.map.fitBounds(bounds, {padding: [50, 50]});
                console.log('📍 Map bounds adjusted to fit', propertiesWithCoords.length, 'properties with coordinates');
            }
        }
        
        // Then geocode properties that need it - IMPORTANT: This will add ALL properties to map
        if (propertiesNeedingGeocode.length > 0) {
            console.log('📍 Starting geocoding for', propertiesNeedingGeocode.length, 'properties without coordinates...');
            console.log('📍 This will ensure ALL properties appear on the map!');
            geocodePropertiesNominatim(propertiesNeedingGeocode);
        } else {
            console.log('✅ All properties already have coordinates - no geocoding needed');
        }
        
        // Log final status
        console.log('🗺️ Map initialization complete!');
        console.log('📍 Properties on map:', propertiesWithCoords.length, '(with coords) +', propertiesNeedingGeocode.length, '(geocoding) =', propertiesWithCoords.length + propertiesNeedingGeocode.length, 'total');
        
        window.mapInitialized = true;
        console.log('✅ OpenStreetMap fully initialized with markers');
        
        // If container was hidden, resize when it becomes visible
        if (mapContainer.offsetParent === null) {
            const resizeObserver = new MutationObserver(function() {
                if (mapContainer.offsetParent !== null && window.map) {
                    console.log('📍 Map container became visible, resizing...');
                    setTimeout(function() {
                        window.map.invalidateSize();
                    }, 200);
                    resizeObserver.disconnect();
                }
            });
            
            resizeObserver.observe(mapContainer.parentElement || mapContainer, {
                attributes: true,
                attributeFilter: ['class', 'style'],
                childList: false,
                subtree: true
            });
        }
        
    } catch (error) {
        console.error('❌ Error initializing OpenStreetMap:', error);
        console.error('Error details:', error.message, error.stack);
        showMapError('Error initializing map: ' + error.message + '. Please check your browser console for details.');
    }
}

// Add Leaflet markers
function addLeafletMarkers(propertiesArray) {
    const propertiesToUse = propertiesArray || window.propertiesData || [];
    
    console.log('=== addLeafletMarkers called ===');
    console.log('Map available:', !!window.map);
    console.log('Properties to add:', propertiesToUse.length);
    
    if (!window.map) {
        console.error('❌ Map not available');
        return;
    }
    
    if (!propertiesToUse || propertiesToUse.length === 0) {
        console.error('❌ No properties available to add markers');
        return;
    }
    
    // Get map settings
    const mapSettings = window.resbsMapSettings || {};
    const enableCluster = mapSettings.enableCluster || false;
    
    console.log('✅ Adding markers for', propertiesToUse.length, 'properties');
    console.log('📍 Clustering enabled:', enableCluster);
    
    // Clear existing markers and cluster group
    if (window.markerClusterGroup) {
        window.map.removeLayer(window.markerClusterGroup);
        window.markerClusterGroup = null;
    }
    window.markers.forEach(function(marker) {
        window.map.removeLayer(marker);
    });
    window.markers = [];
    window.popups = [];
    
    // Initialize marker cluster group if clustering is enabled
    if (enableCluster && typeof L.markerClusterGroup !== 'undefined') {
        // Get cluster settings
        const clusterIcon = mapSettings.clusterIcon || 'circle';
        const clusterIconColor = mapSettings.clusterIconColor || '#333333';
        
        // Create custom cluster icon function
        const createClusterIcon = function(cluster) {
            const count = cluster.getChildCount();
            let iconHtml = '';
            
            switch(clusterIcon) {
                case 'bubble':
                    iconHtml = `<div style="background-color: ${clusterIconColor}; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${count}</div>`;
                    break;
                case 'outline':
                    iconHtml = `<div style="background-color: rgba(255,255,255,0.9); color: ${clusterIconColor}; width: 40px; height: 40px; border-radius: 50%; border: 3px solid ${clusterIconColor}; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${count}</div>`;
                    break;
                case 'circle':
                default:
                    iconHtml = `<div style="background-color: ${clusterIconColor}; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${count}</div>`;
                    break;
            }
            
            return L.divIcon({
                className: 'marker-cluster-custom',
                html: iconHtml,
                iconSize: L.point(40, 40),
                iconAnchor: L.point(20, 20)
            });
        };
        
        window.markerClusterGroup = L.markerClusterGroup({
            iconCreateFunction: createClusterIcon,
            maxClusterRadius: 50,
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true
        });
        
        window.markerClusterGroup.addTo(window.map);
        console.log('✅ Marker cluster group initialized with icon type:', clusterIcon, 'and color:', clusterIconColor);
    }
    
    // Add markers for all properties
    propertiesToUse.forEach(function(property, index) {
        if (property.lat && property.lng) {
            const marker = createLeafletMarker(property);
            if (marker) {
                if (enableCluster && window.markerClusterGroup) {
                    // Add to cluster group
                    window.markerClusterGroup.addLayer(marker);
                } else {
                    // Add directly to map
                    marker.addTo(window.map);
                }
                window.markers.push(marker);
                console.log('✅ Marker added for property', (index + 1) + ':', property.title, 'at', property.lat, ',', property.lng);
            }
        } else {
            console.warn('⚠️ Skipping property without coordinates:', property.title);
        }
    });
    
    console.log('✅ Successfully added', window.markers.length, 'markers to map');
    if (enableCluster && window.markerClusterGroup) {
        console.log('📍 Markers are clustered');
    }
}

// Create a Leaflet marker (returns marker object, doesn't add to map)
function createLeafletMarker(property) {
    if (!window.map) return null;
    if (!property.lat || !property.lng) return null;
    
    // Get map settings from General settings
    const mapSettings = window.resbsMapSettings || {};
    const markerType = mapSettings.markerType || 'icon';
    const useSingleMarker = mapSettings.useSingleMarker || false;
    const singleMarkerIcon = mapSettings.singleMarkerIcon || 'pin';
    const singleMarkerColor = mapSettings.singleMarkerColor || '#333333';
    
    // Determine marker color based on settings
    let markerColor = property.marker_color || '#10b981';
    if (useSingleMarker) {
        markerColor = singleMarkerColor;
    }
    
    // Create marker icon based on settings
    let markerIcon;
    
    if (markerType === 'price' && property.price) {
        // Show price as marker
        const priceText = property.price.replace(/[$,]/g, '').replace(/Price on request/i, 'P.O.R');
        markerIcon = L.divIcon({
            className: 'leaflet-marker-price',
            html: `<div style="background-color: ${markerColor}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; white-space: nowrap; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${priceText}</div>`,
            iconSize: [60, 20],
            iconAnchor: [30, 10]
        });
    } else {
        // Use icon marker
        let iconHtml = '';
        if (useSingleMarker) {
            // Use single marker icon type
            switch(singleMarkerIcon) {
                case 'outline':
                    iconHtml = `<div style="width: 24px; height: 24px; border: 3px solid ${markerColor}; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); background-color: rgba(255,255,255,0.8); box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`;
                    break;
                case 'person':
                    iconHtml = `<div style="width: 24px; height: 24px; border-radius: 50%; background-color: ${markerColor}; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-size: 12px;">👤</div>`;
                    break;
                case 'pin':
                default:
                    iconHtml = `<div style="width: 20px; height: 20px; border-radius: 50%; background-color: ${markerColor}; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`;
                    break;
            }
        } else {
            // Use property-specific color
            iconHtml = `<div style="width: 20px; height: 20px; border-radius: 50%; background-color: ${markerColor}; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`;
        }
        
        markerIcon = L.divIcon({
            className: 'leaflet-marker-custom',
            html: iconHtml,
            iconSize: [20, 20],
            iconAnchor: [10, 10]
        });
    }
    
    // Create marker (don't add to map yet - will be added by caller)
    const marker = L.marker([parseFloat(property.lat), parseFloat(property.lng)], {
        icon: markerIcon
    });
    
    // Create popup content
    const popupContent = `
        <div class="property-info-window" style="min-width: 250px; padding: 10px;">
            ${property.image ? `<img src="${property.image}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;" alt="${property.title}">` : ''}
            <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">${property.title}</h3>
            <p style="margin: 0 0 8px 0; font-size: 18px; font-weight: bold; color: #10b981;">${property.price}</p>
            <div style="display: flex; gap: 15px; margin-bottom: 10px; font-size: 14px; color: #666;">
                ${property.bedrooms ? `<span>🛏️ ${property.bedrooms} beds</span>` : ''}
                ${property.bathrooms ? `<span>🚿 ${property.bathrooms} baths</span>` : ''}
                ${property.area_sqft ? `<span>📏 ${property.area_sqft.toLocaleString()} sq ft</span>` : ''}
            </div>
            <a href="${property.permalink}" target="_blank" style="display: inline-block; padding: 8px 16px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; margin-top: 8px;">
                View Details →
            </a>
        </div>
    `;
    
    // Create popup
    marker.bindPopup(popupContent);
    
    return marker;
}

// Legacy function for backwards compatibility (now uses createLeafletMarker)
function addLeafletMarker(property) {
    const marker = createLeafletMarker(property);
    if (marker) {
        // Get map settings to check if clustering is enabled
        const mapSettings = window.resbsMapSettings || {};
        const enableCluster = mapSettings.enableCluster || false;
        
        if (enableCluster && window.markerClusterGroup) {
            // Add to cluster group
            window.markerClusterGroup.addLayer(marker);
        } else {
            // Add directly to map
            marker.addTo(window.map);
        }
        window.markers.push(marker);
    }
}

// Geocode properties using Nominatim (free OpenStreetMap geocoding)
function geocodePropertiesNominatim(propertiesArray) {
    if (!propertiesArray || propertiesArray.length === 0) return;
    if (!window.map) return;
    
    console.log('🗺️ Starting Nominatim geocoding for', propertiesArray.length, 'properties');
    console.log('📝 Using FREE Nominatim geocoding service - no API keys needed!');
    
    let geocodeIndex = 0;
    const geocodeDelay = 1000; // Delay between geocoding requests (ms) - Nominatim requires slower requests
    
    function geocodeNext() {
        if (geocodeIndex >= propertiesArray.length) {
            console.log('✅ Finished geocoding all properties');
            // Update bounds to include newly geocoded properties
            updateLeafletBounds();
            return;
        }
        
        const property = propertiesArray[geocodeIndex];
        if (!property.full_address) {
            console.warn('⚠️ Skipping', property.title, '- no address to geocode');
            geocodeIndex++;
            setTimeout(geocodeNext, geocodeDelay);
            return;
        }
        
        console.log('📍 Geocoding:', property.title, '- Address:', property.full_address);
        
        // Use Nominatim (free OpenStreetMap geocoding service)
        const geocodeUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(property.full_address)}&limit=1`;
        
        fetch(geocodeUrl, {
            headers: {
                'User-Agent': 'RealEstate-Booking-Suite-WordPress-Plugin/1.0' // Required by Nominatim
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    const result = data[0];
                    const lat = parseFloat(result.lat);
                    const lng = parseFloat(result.lon);
                    
                    // Add coordinates to property
                    property.lat = lat;
                    property.lng = lng;
                    property.needs_geocoding = false;
                    
                    console.log('✅ Geocoded:', property.title, '- Coordinates:', lat, ',', lng);
                    
                    // Add marker for this property
                    addLeafletMarker(property);
                } else {
                    console.warn('⚠️ Geocoding failed for:', property.title, '- No results found');
                }
                
                // Process next property
                geocodeIndex++;
                setTimeout(geocodeNext, geocodeDelay);
            })
            .catch(error => {
                console.warn('⚠️ Geocoding error for:', property.title, '- Error:', error);
                geocodeIndex++;
                setTimeout(geocodeNext, geocodeDelay);
            });
    }
    
    // Start geocoding
    geocodeNext();
}

// Update map bounds to include all markers
function updateLeafletBounds() {
    if (!window.map || !window.markers || window.markers.length === 0) return;
    
    const bounds = L.latLngBounds([]);
    window.markers.forEach(function(marker) {
        bounds.extend(marker.getLatLng());
    });
    
    if (window.markers.length > 0) {
        window.map.fitBounds(bounds, {padding: [50, 50]});
        console.log('📍 Map bounds updated to show all', window.markers.length, 'properties');
    }
}

// Check and initialize map
function checkAndInitLeafletMap() {
    const mapContainer = document.getElementById('googleMap');
    if (!mapContainer) {
        console.error('❌ Map container (#googleMap) not found');
        return;
    }
    
    console.log('📋 Map container check:', {
        'exists': !!mapContainer,
        'visible': mapContainer.offsetParent !== null,
        'width': mapContainer.offsetWidth,
        'height': mapContainer.offsetHeight,
        'mapExists': !!window.map
    });
    
    // Initialize map if not already initialized
    if (!window.map && typeof L !== 'undefined') {
        if (mapContainer.offsetParent !== null) {
            console.log('📍 Map container is visible, initializing now...');
            initializeOpenStreetMap();
        } else {
            console.log('📍 Map container is hidden, but initializing anyway...');
            // Initialize even if hidden - Leaflet can handle this
            initializeOpenStreetMap();
            
            // Set up observer to resize when shown
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mapContainer.offsetParent !== null && window.map) {
                        console.log('📍 Map container became visible, resizing map...');
                        setTimeout(function() {
                            if (window.map) {
                                window.map.invalidateSize();
                            }
                        }, 100);
                        observer.disconnect();
                    }
                });
            });
            
            observer.observe(mapContainer.parentElement || mapContainer, {
                attributes: true,
                attributeFilter: ['class', 'style'],
                childList: false,
                subtree: true
            });
            
            // Also watch for class changes on map section
            const mapSection = document.querySelector('.map-section');
            if (mapSection) {
                observer.observe(mapSection, {
                    attributes: true,
                    attributeFilter: ['class'],
                    childList: false,
                    subtree: false
                });
            }
        }
    } else if (window.map) {
        console.log('📍 Map already initialized');
    } else {
        console.warn('⚠️ Leaflet.js not loaded yet');
    }
}

// Function to show user-friendly map error
function showMapError(message) {
    const mapContainer = document.getElementById('googleMap');
    if (!mapContainer) return;
    
    // Remove any existing error message
    const existingError = mapContainer.querySelector('.resbs-map-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Get admin URL
    let adminUrl = '';
    if (typeof window.location !== 'undefined') {
        const currentUrl = window.location.href;
        const urlParts = currentUrl.split('/');
        if (urlParts.length > 3) {
            adminUrl = urlParts[0] + '//' + urlParts[2] + '/wp-admin';
        }
    }
    
    // Create error message overlay
    const errorDiv = document.createElement('div');
    errorDiv.className = 'resbs-map-error';
    errorDiv.style.cssText = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 1000; max-width: 450px; text-align: center; border: 2px solid #ef4444;';
    errorDiv.innerHTML = `
        <div style="color: #ef4444; font-size: 48px; margin-bottom: 15px;">⚠️</div>
        <h3 style="color: #1f2937; margin: 0 0 10px 0; font-size: 18px; font-weight: 600;">Map Error</h3>
        <p style="color: #6b7280; margin: 0 0 20px 0; font-size: 14px; line-height: 1.5;">${message}</p>
        ${adminUrl ? '<a href="' + adminUrl + '/admin.php?page=resbs-general-settings" target="_blank" style="display: inline-block; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; margin-top: 10px;">⚙️ Configure Map Token in Settings</a>' : ''}
        <p style="margin-top: 15px; font-size: 11px; color: #9ca3af;">Go to WordPress Admin → RealEstate Booking Suite → Settings → Map Settings</p>
    `;
    
    mapContainer.style.position = 'relative';
    mapContainer.appendChild(errorDiv);
}

// Initialize Leaflet map when library loads
window.initLeafletWhenReady = function() {
    if (typeof L !== 'undefined') {
        console.log('✅ Leaflet.js loaded, checking map initialization...');
        // Remove any error messages if Leaflet is now loaded
        const mapContainer = document.getElementById('googleMap');
        if (mapContainer) {
            const existingError = mapContainer.querySelector('.resbs-map-error');
            if (existingError) {
                existingError.remove();
                console.log('✅ Removed error message - Leaflet loaded successfully');
            }
            // Also clear innerHTML if it was set to error message
            if (mapContainer.innerHTML.includes('Map Library Failed to Load')) {
                mapContainer.innerHTML = ''; // Clear error, map will be initialized below
            }
        }
        checkAndInitLeafletMap();
    } else {
        console.log('⏳ Waiting for Leaflet.js to load...');
        setTimeout(function() {
            if (typeof L !== 'undefined') {
                window.initLeafletWhenReady();
            } else {
                console.log('⏳ Still waiting for Leaflet.js...');
                // Wait longer before showing error
                setTimeout(function() {
                    if (typeof L !== 'undefined') {
                        window.initLeafletWhenReady();
                    } else {
                        console.log('⏳ Waiting a bit more...');
                        // Final attempt after longer delay
                        setTimeout(function() {
                            if (typeof L !== 'undefined') {
                                window.initLeafletWhenReady();
                            } else {
                                console.error('❌ Leaflet.js failed to load after extended wait');
                                const mapContainer = document.getElementById('googleMap');
                                if (mapContainer) {
                                    // Only show error if map container is visible
                                    if (mapContainer.offsetParent !== null) {
                                        showMapError('Failed to load map library. Please check your internet connection and refresh the page.');
                                    }
                                }
                            }
                        }, 3000);
                    }
                }, 2000);
            }
        }, 500);
    }
};

// Start initialization
window.initLeafletWhenReady();

// Update showMapView function for Leaflet
const originalShowMapView = window.showMapView;
window.showMapView = function() {
    console.log('🗺️ showMapView called');
    if (typeof originalShowMapView === 'function') {
        originalShowMapView();
    }
    
    // Wait a bit longer for container to become visible
    setTimeout(function() {
        const mapContainer = document.getElementById('googleMap');
        console.log('📋 showMapView - Map container check:', {
            'exists': !!mapContainer,
            'visible': mapContainer ? mapContainer.offsetParent !== null : false,
            'LeafletLoaded': typeof L !== 'undefined',
            'mapExists': !!window.map
        });
        
        if (typeof L !== 'undefined') {
            if (!window.map && mapContainer && mapContainer.offsetParent !== null) {
                console.log('📍 Initializing Leaflet map in showMapView');
                initializeOpenStreetMap();
            } else if (window.map) {
                // Resize map
                console.log('📍 Map exists, resizing...');
                setTimeout(function() {
                    window.map.invalidateSize();
                    // Fit bounds
                    if (window.markers && window.markers.length > 0) {
                        updateLeafletBounds();
                    }
                }, 100);
            } else {
                console.log('📍 Map container not visible yet, will initialize when visible');
                // Try again after a short delay
                setTimeout(function() {
                    if (!window.map && mapContainer && mapContainer.offsetParent !== null) {
                        console.log('📍 Container now visible, initializing...');
                        initializeOpenStreetMap();
                    }
                }, 500);
            }
        } else {
            console.warn('⚠️ Leaflet.js not loaded yet in showMapView');
            // Wait for Leaflet to load
            setTimeout(function() {
                if (typeof L !== 'undefined' && mapContainer && mapContainer.offsetParent !== null && !window.map) {
                    console.log('📍 Leaflet loaded, initializing map now...');
                    initializeOpenStreetMap();
                }
            }, 1000);
        }
    }, 300);
};

</script>

<?php elseif (!empty($google_maps_api_key)): ?>
<script>
console.log('=== Google Maps Integration Started ===');
console.log('API Key from PHP:', '<?php echo substr(esc_js($google_maps_api_key), 0, 10); ?>...');
console.log('Total properties in query:', <?php echo $properties_query->found_posts; ?>);
console.log('Total properties being added to map:', <?php echo count($properties_data); ?>);

// Google Maps Variables - Must be global
window.map = null;
window.markers = [];
window.infoWindows = [];
window.propertiesData = <?php echo json_encode($properties_data); ?>;
window.googleMapsApiKey = '<?php echo esc_js($google_maps_api_key); ?>';
window.mapInitialized = false;
window.geocoder = null;

// Debug: Verify API key was loaded correctly
console.log('API Key Status:', {
    'exists': typeof window.googleMapsApiKey !== 'undefined',
    'length': window.googleMapsApiKey ? window.googleMapsApiKey.length : 0,
    'startsWith_Aiza': window.googleMapsApiKey ? window.googleMapsApiKey.startsWith('AIza') : false,
    'first_10_chars': window.googleMapsApiKey ? window.googleMapsApiKey.substring(0, 10) + '...' : 'NOT SET'
});

console.log('=== PROPERTIES DATA DEBUG ===');
console.log('All properties data:', window.propertiesData);
if (window.propertiesData && window.propertiesData.length > 0) {
    console.log('Property details:');
    window.propertiesData.forEach(function(prop, idx) {
        if (prop.lat && prop.lng) {
            console.log('  [' + (idx + 1) + ']', prop.title, '- Has coordinates - Lat:', prop.lat, ', Lng:', prop.lng);
        } else {
            console.log('  [' + (idx + 1) + ']', prop.title, '- Needs geocoding:', prop.full_address || 'No address');
        }
    });
} else {
    console.warn('⚠️ WARNING: No properties found!');
}
console.log('Window variables set. API Key length:', window.googleMapsApiKey ? window.googleMapsApiKey.length : 0);

// Initialize map function (called by Google Maps API callback)
window.initMap = function() {
    console.log('=== initMap called ===');
    console.log('Google Maps API loaded:', typeof google !== 'undefined' && typeof google.maps !== 'undefined');
    console.log('Properties data:', window.propertiesData ? window.propertiesData.length + ' properties' : 'none');
    
    const mapContainer = document.getElementById('googleMap');
    if (!mapContainer) {
        console.error('❌ Map container (#googleMap) not found in DOM');
        return;
    }
    
    console.log('Map container found:', mapContainer);
    console.log('Map container visible:', mapContainer.offsetParent !== null);
    console.log('Map container dimensions:', mapContainer.offsetWidth + 'x' + mapContainer.offsetHeight);
    
    // Wait a bit if container is not visible
    if (mapContainer.offsetParent === null) {
        console.log('⏳ Map container is hidden, will initialize when shown');
        return;
    }
    
    // IMPORTANT: Show ALL properties on map - geocode those without coordinates
    let centerLat = <?php echo esc_js($map_center_lat); ?>; // Fallback only
    let centerLng = <?php echo esc_js($map_center_lng); ?>; // Fallback only
    
    // Initialize geocoder for properties without coordinates
    window.geocoder = new google.maps.Geocoder();
    
    // Separate properties with and without coordinates
    const propertiesWithCoords = [];
    const propertiesNeedingGeocode = [];
    
    if (window.propertiesData && window.propertiesData.length > 0) {
        window.propertiesData.forEach(function(property) {
            const lat = parseFloat(property.lat);
            const lng = parseFloat(property.lng);
            
            // Check if property has valid coordinates
            const hasValidCoords = !isNaN(lat) && !isNaN(lng) && 
                lat >= -90 && lat <= 90 && 
                lng >= -180 && lng <= 180 &&
                lat !== 0 && lng !== 0;
            
            if (hasValidCoords) {
                // Has valid coordinates - add directly to map
                propertiesWithCoords.push(property);
                console.log('✅ Property with coordinates:', property.title, 'at', lat, ',', lng);
            } else {
                // No valid coordinates - needs geocoding
                // Ensure we have an address for geocoding
                if (!property.full_address || property.full_address.trim() === '') {
                    // Priority 1: Use location_name (location taxonomy term) if available - most reliable
                    if (property.location_name && property.location_name.trim() !== '') {
                        property.full_address = property.location_name + ', Bangladesh';
                        console.log('📍 Property needs geocoding (using location taxonomy):', property.title, '- Location:', property.full_address);
                    }
                    // Priority 2: Try city
                    else if (property.city && property.city.trim() !== '') {
                        property.full_address = property.city + ', Bangladesh';
                        console.log('📍 Property needs geocoding (using city):', property.title, '- Address:', property.full_address);
                    }
                    // Priority 3: Try address field
                    else if (property.address && property.address.trim() !== '') {
                        property.full_address = property.address + ', Bangladesh';
                        console.log('📍 Property needs geocoding (using address):', property.title, '- Address:', property.full_address);
                    }
                }
                
                // If still no address, try property title as last resort
                if (!property.full_address || property.full_address.trim() === '') {
                    // Try to extract location from title or use a default
                    property.full_address = property.title.replace(/Copy/gi, '').replace(/\s+/g, ' ').trim() + ', Bangladesh';
                    if (property.full_address === ', Bangladesh') {
                        property.full_address = 'Dhaka, Bangladesh'; // Default fallback
                    }
                    console.log('📍 Property needs geocoding (using title):', property.title, '- Address:', property.full_address);
                }
                
                // Always add to geocoding queue if we have any address text
                if (property.full_address && property.full_address.trim() !== '') {
                    property.needs_geocoding = true; // Ensure this is set
                    propertiesNeedingGeocode.push(property);
                    console.log('✅ Property added to geocoding queue:', property.title, '- Will geocode:', property.full_address);
                } else {
                    console.error('❌ Property cannot be geocoded - no address available:', property.title);
                }
            }
        });
    }
    
    console.log('📊 Summary: ' + propertiesWithCoords.length + ' with coordinates, ' + propertiesNeedingGeocode.length + ' need geocoding');
    
    // Calculate map center from properties with coordinates
    if (propertiesWithCoords.length > 0) {
        let bounds = new google.maps.LatLngBounds();
        propertiesWithCoords.forEach(function(property) {
            bounds.extend(new google.maps.LatLng(parseFloat(property.lat), parseFloat(property.lng)));
        });
        centerLat = bounds.getCenter().lat();
        centerLng = bounds.getCenter().lng();
        console.log('✅ Map centered on properties with coordinates:', centerLat, centerLng);
    }
    
    try {
        console.log('🗺️ Creating Google Map...');
        // Create map
        window.map = new google.maps.Map(mapContainer, {
            center: { lat: centerLat, lng: centerLng },
            zoom: <?php echo esc_js($map_zoom); ?>,
            mapTypeControl: true,
            streetViewControl: true,
            fullscreenControl: true,
            zoomControl: true,
            styles: [
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }]
                }
            ]
        });
        
        console.log('✅ Google Map created successfully');
        
        // First, add markers for properties that already have coordinates
        if (propertiesWithCoords.length > 0) {
            console.log('📍 Adding', propertiesWithCoords.length, 'properties with coordinates to map');
            addPropertyMarkers(propertiesWithCoords);
            
            // Fit bounds to show all properties with coordinates
            let bounds = new google.maps.LatLngBounds();
            propertiesWithCoords.forEach(function(property) {
                bounds.extend(new google.maps.LatLng(parseFloat(property.lat), parseFloat(property.lng)));
            });
            window.map.fitBounds(bounds, {padding: 50});
            console.log('📍 Map bounds adjusted to fit', propertiesWithCoords.length, 'properties with coordinates');
        }
        
        // Then geocode properties that need it
        if (propertiesNeedingGeocode.length > 0) {
            console.log('📍 Geocoding', propertiesNeedingGeocode.length, 'properties without coordinates...');
            geocodeProperties(propertiesNeedingGeocode);
        }
        
        window.mapInitialized = true;
        console.log('✅ Google Map fully initialized with markers');
    } catch (error) {
        console.error('❌ Error initializing Google Map:', error);
        console.error('Error details:', error.message, error.stack);
    }
};

// Initialize Google Map
function initializeGoogleMap() {
    // Check if Google Maps API is loaded
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        console.log('Waiting for Google Maps API to load...');
        // Wait for API to load
        setTimeout(function() {
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                checkAndInitMap();
            } else {
                console.error('Google Maps API failed to load');
            }
        }, 1000);
        return;
    }
    
    checkAndInitMap();
}

// Check and initialize map
function checkAndInitMap() {
    const mapContainer = document.getElementById('googleMap');
    if (!mapContainer) {
        console.error('Map container not found');
        return;
    }
    
    // If map is visible, initialize it
    if (mapContainer.offsetParent !== null && !window.map) {
        window.initMap();
    } else if (mapContainer.offsetParent === null) {
        // Map is hidden, set up observer to initialize when shown
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mapContainer.offsetParent !== null && !window.map && typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                    window.initMap();
                    observer.disconnect();
                }
            });
        });
        
        observer.observe(mapContainer, {
            attributes: true,
            attributeFilter: ['class'],
            childList: false,
            subtree: false
        });
    }
}

// Geocode properties that don't have coordinates
function geocodeProperties(propertiesArray) {
    if (!propertiesArray || propertiesArray.length === 0) return;
    if (!window.geocoder || !window.map) return;
    
    console.log('🗺️ Starting geocoding for', propertiesArray.length, 'properties');
    
    let geocodeIndex = 0;
    const geocodeDelay = 200; // Delay between geocoding requests (ms) to avoid rate limiting
    
    function geocodeNext() {
        if (geocodeIndex >= propertiesArray.length) {
            console.log('✅ Finished geocoding all properties');
            // Update bounds to include newly geocoded properties
            updateMapBounds();
        return;
    }
    
        const property = propertiesArray[geocodeIndex];
        if (!property.full_address) {
            console.warn('⚠️ Skipping', property.title, '- no address to geocode');
            geocodeIndex++;
            setTimeout(geocodeNext, geocodeDelay);
            return;
        }
        
        console.log('📍 Geocoding:', property.title, '- Address:', property.full_address);
        
        window.geocoder.geocode(
            { address: property.full_address },
            function(results, status) {
                if (status === 'OK' && results && results[0]) {
                    const location = results[0].geometry.location;
                    const lat = location.lat();
                    const lng = location.lng();
                    
                    // Add coordinates to property
                    property.lat = lat;
                    property.lng = lng;
                    property.needs_geocoding = false;
                    
                    console.log('✅ Geocoded:', property.title, '- Coordinates:', lat, ',', lng);
                    
                    // Add marker for this property
                    addPropertyMarker(property);
                } else {
                    console.warn('⚠️ Geocoding failed for:', property.title, '- Status:', status);
                }
                
                // Process next property
                geocodeIndex++;
                setTimeout(geocodeNext, geocodeDelay);
            }
        );
    }
    
    // Start geocoding
    geocodeNext();
}

// Update map bounds to include all markers
function updateMapBounds() {
    if (!window.map || !window.markers || window.markers.length === 0) return;
    
    const bounds = new google.maps.LatLngBounds();
    window.markers.forEach(function(marker) {
        bounds.extend(marker.getPosition());
    });
    
    if (window.markers.length > 0) {
        window.map.fitBounds(bounds, {padding: 50});
        console.log('📍 Map bounds updated to show all', window.markers.length, 'properties');
    }
}

// Add a single property marker to the map
function addPropertyMarker(property) {
    if (!window.map) return;
    if (!property.lat || !property.lng) return;
    
        // Create custom marker icon based on property status
        const markerIcon = {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
        fillColor: property.marker_color || '#10b981',
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2
        };
        
        // Create marker
        const marker = new google.maps.Marker({
        position: { lat: parseFloat(property.lat), lng: parseFloat(property.lng) },
            map: window.map,
            title: property.title,
            icon: markerIcon,
            animation: google.maps.Animation.DROP
        });
        
        // Create info window content
        const infoContent = `
            <div class="property-info-window" style="min-width: 250px; padding: 10px;">
                ${property.image ? `<img src="${property.image}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;" alt="${property.title}">` : ''}
                <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">${property.title}</h3>
                <p style="margin: 0 0 8px 0; font-size: 18px; font-weight: bold; color: #10b981;">${property.price}</p>
                <div style="display: flex; gap: 15px; margin-bottom: 10px; font-size: 14px; color: #666;">
                    ${property.bedrooms ? `<span>🛏️ ${property.bedrooms} beds</span>` : ''}
                    ${property.bathrooms ? `<span>🚿 ${property.bathrooms} baths</span>` : ''}
                    ${property.area_sqft ? `<span>📏 ${property.area_sqft.toLocaleString()} sq ft</span>` : ''}
                </div>
                <a href="${property.permalink}" target="_blank" style="display: inline-block; padding: 8px 16px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; margin-top: 8px;">
                    View Details →
                </a>
            </div>
        `;
        
        // Create info window
        const infoWindow = new google.maps.InfoWindow({
            content: infoContent
        });
        
        // Add click event to marker
        marker.addListener('click', function() {
            // Close all other info windows
            window.infoWindows.forEach(function(iw) {
                iw.close();
            });
            infoWindow.open(window.map, marker);
    });
    
    // Store marker and info window
    window.markers.push(marker);
    window.infoWindows.push(infoWindow);
}

// Add property markers to map
function addPropertyMarkers(propertiesArray) {
    // Use provided array or fall back to all properties with coordinates
    const propertiesToUse = propertiesArray || window.propertiesData || [];
    
    console.log('=== addPropertyMarkers called ===');
    console.log('Map available:', !!window.map);
    console.log('Properties to add:', propertiesToUse.length);
    
    if (!window.map) {
        console.error('❌ Map not available');
        return;
    }
    
    if (!propertiesToUse || propertiesToUse.length === 0) {
        console.error('❌ No properties available to add markers');
        return;
    }
    
    console.log('✅ Adding markers for', propertiesToUse.length, 'properties');
    
    // Log all properties before adding markers
    propertiesToUse.forEach(function(property, index) {
        console.log('  Property ' + (index + 1) + ':', property.title, '- Coordinates:', property.lat, ',', property.lng);
    });
    
    // Add markers for all properties using the helper function
    propertiesToUse.forEach(function(property, index) {
        if (property.lat && property.lng) {
            addPropertyMarker(property);
        } else {
            console.warn('⚠️ Skipping property without coordinates:', property.title);
        }
        
        console.log('✅ Marker added for property', (index + 1) + ':', property.title, 'at', property.lat, ',', property.lng);
    });
    
    console.log('✅ Successfully added', window.markers.length, 'markers to map');
    console.log('Marker positions:');
    window.markers.forEach(function(marker, index) {
        const pos = marker.getPosition();
        console.log('  Marker', (index + 1) + ':', pos.lat(), ',', pos.lng());
    });
}

// Re-initialize map when shown
function showMapView() {
    const mapSection = document.querySelector('.map-section');
    const listingsContainer = document.querySelector('.listings-container');
    const viewButtons = document.querySelectorAll('.view-btn');
    const mapToggleBtn = document.getElementById('mapToggleBtn');
    
    // Remove active class from all view buttons
    viewButtons.forEach(btn => btn.classList.remove('active'));
    
    // Always show map view
    mapSection.classList.remove('map-hidden');
    mapSection.classList.add('map-visible');
    listingsContainer.classList.add('map-visible');
    
    // Update button states
    document.querySelector('.view-btn[onclick="showMapView()"]').classList.add('active');
    if (mapToggleBtn) mapToggleBtn.classList.add('active');
    
    // Initialize map after a short delay to ensure container is visible
    setTimeout(function() {
        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
            if (!window.map) {
                console.log('Initializing map in showMapView');
                window.initMap();
            } else {
                // Resize map to fix any display issues
                google.maps.event.trigger(window.map, 'resize');
                // Use all properties that have coordinates (including geocoded ones)
                const allProperties = window.propertiesData || [];
                const propertiesWithCoords = allProperties.filter(function(p) {
                    return p.lat && p.lng;
                });
                
                if (propertiesWithCoords.length > 0) {
                    let bounds = new google.maps.LatLngBounds();
                    propertiesWithCoords.forEach(function(property) {
                        bounds.extend(new google.maps.LatLng(parseFloat(property.lat), parseFloat(property.lng)));
                    });
                    window.map.fitBounds(bounds, {padding: 50});
                    console.log('Map bounds updated to fit', propertiesWithCoords.length, 'properties');
                }
            }
        } else {
            console.log('Google Maps API not loaded yet');
        }
    }, 200);
    
    console.log('Map view activated. Listings container classes:', listingsContainer.className);
}

// Update showMap function to also initialize map
const originalShowMap = window.showMap;
if (typeof originalShowMap === 'function') {
    window.showMap = function() {
        originalShowMap();
        setTimeout(function() {
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                if (!window.map) {
                    window.initMap();
                } else {
                    google.maps.event.trigger(window.map, 'resize');
                }
            }
        }, 200);
    };
}
</script>

<!-- Google Maps JavaScript API - Load after page is ready -->
<script>
(function() {
    console.log('=== Google Maps API Loader ===');
    console.log('API Key exists:', typeof window.googleMapsApiKey !== 'undefined');
    console.log('API Key value:', window.googleMapsApiKey ? (window.googleMapsApiKey.substring(0, 10) + '...') : 'NOT SET');
    
    if (typeof window.googleMapsApiKey === 'undefined' || !window.googleMapsApiKey) {
        console.error('❌ Google Maps API key not set in window.googleMapsApiKey');
        console.error('Please save your API key in WordPress Admin → RealEstate Booking Suite → Map Settings');
        return;
    }
    
    // Check if already loaded
    if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
        console.log('✅ Google Maps API already loaded');
        if (window.initMap && typeof window.initMap === 'function') {
            window.initMap();
        }
        return;
    }
    
    // Load Google Maps API
    console.log('📡 Loading Google Maps API...');
    console.log('API Key being used:', window.googleMapsApiKey ? (window.googleMapsApiKey.substring(0, 15) + '...') : 'MISSING');
    
    // Validate API key format before loading
    if (!window.googleMapsApiKey || window.googleMapsApiKey.length < 20) {
        console.error('❌ Invalid API key format or key is too short');
        showMapError('Google Maps API key is invalid or missing. Please check your API key in Settings.');
        return;
    }
    
    if (!window.googleMapsApiKey.startsWith('AIza')) {
        console.warn('⚠️ API key format warning: Google Maps API keys typically start with "AIza"');
    }
    
    const script = document.createElement('script');
    const apiUrl = 'https://maps.googleapis.com/maps/api/js?key=' + window.googleMapsApiKey + '&callback=initMap&libraries=places';
    console.log('API URL (key hidden):', apiUrl.replace(window.googleMapsApiKey, 'KEY_HIDDEN'));
    script.src = apiUrl;
    script.async = true;
    script.defer = true;
    script.onload = function() {
        console.log('✅ Google Maps API script loaded successfully');
        // Check for API errors after a short delay
        setTimeout(function() {
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                // Check if there's an error in the map container
                const mapContainer = document.getElementById('googleMap');
                if (mapContainer) {
                    // Listen for API errors
                    google.maps.event.addListenerOnce(window.map || {}, 'error', function(error) {
                        console.error('❌ Google Maps API Error:', error);
                        showMapError('Google Maps API Error: ' + (error.message || 'Unknown error'));
                    });
                }
            }
        }, 1000);
    };
    script.onerror = function() {
        console.error('❌ Failed to load Google Maps API script');
        showMapError('Failed to load Google Maps API. Please check your API key configuration.');
    };
    
    // Add global error handler for Google Maps API errors
    window.gm_authFailure = function() {
        console.error('❌ Google Maps Authentication Failed');
        showMapError('Google Maps API authentication failed. Please check your API key in Settings.');
    };
    
    // Listen for specific Google Maps API errors
    window.addEventListener('error', function(event) {
        if (event.message && typeof event.message === 'string') {
            if (event.message.includes('ApiNotActivatedMapError')) {
                console.error('❌ Maps JavaScript API is not enabled');
                showMapError('Maps JavaScript API is not enabled. Please enable it in Google Cloud Console → APIs & Services → Library → Maps JavaScript API.');
                event.preventDefault();
            } else if (event.message.includes('BillingNotEnabledMapError')) {
                console.error('❌ Billing is not enabled');
                showMapError('Billing is not enabled for your Google Cloud project. Please enable billing in Google Cloud Console to use Google Maps API.');
                event.preventDefault();
            }
        }
    }, true);
    
    // Catch Google Maps API errors from console errors
    const originalError = console.error;
    console.error = function(...args) {
        originalError.apply(console, args);
        const errorText = args.join(' ');
        if (errorText.includes('ApiNotActivatedMapError')) {
            if (!document.querySelector('.resbs-map-error')) {
                showMapError('Maps JavaScript API is not enabled. Please enable it in Google Cloud Console → APIs & Services → Library → Maps JavaScript API.');
            }
        } else if (errorText.includes('BillingNotEnabledMapError')) {
            if (!document.querySelector('.resbs-map-error')) {
                showMapError('Billing is not enabled for your Google Cloud project. Please enable billing in Google Cloud Console.');
            }
        }
    };
    document.head.appendChild(script);
    console.log('📝 Script tag added to document head');
})();

// Function to show user-friendly map error
function showMapError(message) {
    const mapContainer = document.getElementById('googleMap');
    if (!mapContainer) return;
    
    // Remove any existing error message
    const existingError = mapContainer.querySelector('.resbs-map-error');
    if (existingError) {
        existingError.remove();
    }
    
    // Get admin URL (try to detect it)
    let adminUrl = '';
    if (typeof window.location !== 'undefined') {
        const currentUrl = window.location.href;
        const urlParts = currentUrl.split('/');
        if (urlParts.length > 3) {
            adminUrl = urlParts[0] + '//' + urlParts[2] + '/wp-admin';
        }
    }
    
    // Create error message overlay
    const errorDiv = document.createElement('div');
    errorDiv.className = 'resbs-map-error';
    errorDiv.style.cssText = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 1000; max-width: 450px; text-align: center; border: 2px solid #ef4444;';
    errorDiv.innerHTML = `
        <div style="color: #ef4444; font-size: 48px; margin-bottom: 15px;">⚠️</div>
        <h3 style="color: #1f2937; margin: 0 0 10px 0; font-size: 18px; font-weight: 600;">Google Maps Error</h3>
        <p style="color: #6b7280; margin: 0 0 20px 0; font-size: 14px; line-height: 1.5;">${message}</p>
        <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; border-radius: 6px; margin-bottom: 15px; text-align: left;">
            <p style="margin: 0 0 10px 0; font-size: 13px; color: #991b1b; font-weight: 600;">🔧 To fix this, please check:</p>
            <ul style="margin: 0; padding-left: 20px; font-size: 12px; color: #991b1b; line-height: 1.8;">
                <li>Your API key is correct and active</li>
                <li><strong>Maps JavaScript API</strong> is enabled in Google Cloud Console</li>
                <li><strong>Billing is enabled</strong> in your Google Cloud project</li>
                <li>If using API key restrictions, add <code>localhost</code> to allowed domains</li>
            </ul>
        </div>
        ${adminUrl ? '<a href="' + adminUrl + '/admin.php?page=resbs-general-settings" target="_blank" style="display: inline-block; padding: 10px 20px; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px; font-weight: 500; margin-top: 10px;">⚙️ Configure API Key in Settings</a>' : ''}
        <p style="margin-top: 15px; font-size: 11px; color: #9ca3af;">Go to WordPress Admin → RealEstate Booking Suite → Settings → Map Settings</p>
    `;
    
    mapContainer.style.position = 'relative';
    mapContainer.appendChild(errorDiv);
}

// Check for Google Maps API errors on page load
document.addEventListener('DOMContentLoaded', function() {
    // Monitor for common Google Maps errors
    const checkMapErrors = setInterval(function() {
        const mapContainer = document.getElementById('googleMap');
        if (mapContainer) {
            // Check if Google Maps shows an error overlay
            // Use textContent to check for error messages (querySelector doesn't support :contains)
            const textContent = mapContainer.textContent || '';
            const hasGoogleError = textContent.includes("can't load Google Maps") || 
                                 textContent.includes("can't load") ||
                                 textContent.includes("Google Maps");
            
            if (hasGoogleError && !mapContainer.querySelector('.resbs-map-error')) {
                clearInterval(checkMapErrors);
                // Only show custom error if Google's error message is present
                // Check for common Google Maps error indicators
                const hasErrorIndicators = mapContainer.textContent.match(/error|failed|can't|unable/i);
                if (hasErrorIndicators) {
                    showMapError('Google Maps failed to load. Please check your API key and billing configuration.');
                }
            }
        }
    }, 2000);
    
    // Stop checking after 10 seconds
    setTimeout(function() {
        clearInterval(checkMapErrors);
    }, 10000);
});
</script>
<?php endif; ?>

<?php wp_reset_postdata(); ?>

<?php
// Use helper function to safely get footer (avoids deprecation warnings in block themes)
if (function_exists('resbs_get_footer')) {
    resbs_get_footer();
} else {
    get_footer();
}
?>