<?php get_header(); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">

<?php
// Note: AJAX functionality disabled for now to prevent errors
// wp_enqueue_script('resbs-dynamic-archive');

// SIMPLE WORKING FILTER APPROACH
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : '';
$property_type_filter = isset($_GET['property_type']) ? sanitize_text_field($_GET['property_type']) : '';
$min_bedrooms = isset($_GET['min_bedrooms']) ? intval($_GET['min_bedrooms']) : '';
$max_bedrooms = isset($_GET['max_bedrooms']) ? intval($_GET['max_bedrooms']) : '';
$min_bathrooms = isset($_GET['min_bathrooms']) ? floatval($_GET['min_bathrooms']) : '';
$max_bathrooms = isset($_GET['max_bathrooms']) ? floatval($_GET['max_bathrooms']) : '';
$min_sqft = isset($_GET['min_sqft']) ? intval($_GET['min_sqft']) : '';
$max_sqft = isset($_GET['max_sqft']) ? intval($_GET['max_sqft']) : '';
$year_built = isset($_GET['year_built']) ? sanitize_text_field($_GET['year_built']) : '';
$property_status = isset($_GET['property_status']) ? sanitize_text_field($_GET['property_status']) : '';
$sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'date';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Build WP_Query arguments
$args = array(
    'post_type' => 'property',
    'post_status' => 'publish',
    'posts_per_page' => 12,
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
    case 'popular':
        // Sort by comment count (popularity indicator)
        $args['orderby'] = 'comment_count';
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
            <div class="sort-controls">
                <span class="sort-label">Sort by:</span>
                <select class="sort-select" name="sort_by" id="sortSelect" onchange="handleSortChange(this.value)">
                    <option value="newest" <?php selected($sort_by, 'newest'); ?>>Newest</option>
                    <option value="price_low" <?php selected($sort_by, 'price_low'); ?>>Price: Low to High</option>
                    <option value="price_high" <?php selected($sort_by, 'price_high'); ?>>Price: High to Low</option>
                    <option value="popular" <?php selected($sort_by, 'popular'); ?>>Most Popular</option>
                </select>

                <div class="layout-toggle">
                    <button onclick="showGridLayout()" class="layout-btn" id="gridBtn">
                        <i class="fas fa-th-large"></i>
                    </button>
                </div>

                <button class="filter-toggle" onclick="showMap()" id="mapToggleBtn">
                    <i class="fas fa-map-marked-alt"></i>
                </button>
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
                            <button class="favorite-btn">
                                <i class="far fa-heart"></i>
                            </button>
                            <div class="property-info-overlay">
                                        <h3 class="property-title"><?php echo esc_html(get_the_title()); ?></h3>
                                        <p class="property-location"><?php echo esc_html($location); ?></p>
                            </div>
                        </div>
                        <div class="property-details">
                            <div class="property-price-container">
                                        <span class="property-price"><?php echo esc_html($formatted_price); ?></span>
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
                                                <span><?php echo esc_html(number_format($area_sqft)); ?> sq ft</span>
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
                    <div id="googleMap"></div>
                    
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
                                <div class="legend-color" style="background-color: #0f766e;"></div>
                                <span class="legend-label">Standard</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php
            // Get Google Maps API key
            $google_maps_api_key = get_option('resbs_google_maps_api_key', '');
            $map_zoom = get_option('resbs_map_zoom_level', 10);
            $map_center_lat = get_option('resbs_map_center_lat', 40.7128);
            $map_center_lng = get_option('resbs_map_center_lng', -74.0060);
            
            // Debug: Show API key status (remove after testing)
            if (empty($google_maps_api_key)) {
                echo '<!-- DEBUG: Google Maps API key is NOT set in database. Please add it in WordPress Admin → RealEstate Booking Suite → Map Settings -->';
            } else {
                echo '<!-- DEBUG: Google Maps API key is SET (length: ' . strlen($google_maps_api_key) . ' characters) -->';
            }
            
            // Prepare properties data for JavaScript
            $properties_query->rewind_posts();
            $properties_data = array();
            while ($properties_query->have_posts()): $properties_query->the_post();
                $latitude = get_post_meta(get_the_ID(), '_property_latitude', true);
                $longitude = get_post_meta(get_the_ID(), '_property_longitude', true);
                
                if ($latitude && $longitude) {
                    $price = get_post_meta(get_the_ID(), '_property_price', true);
                    $bedrooms = get_post_meta(get_the_ID(), '_property_bedrooms', true);
                    $bathrooms = get_post_meta(get_the_ID(), '_property_bathrooms', true);
                    $area_sqft = get_post_meta(get_the_ID(), '_property_area_sqft', true);
                    $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'thumbnail');
                    
                    $post_date = get_the_date('Y-m-d');
                    $days_old = (time() - strtotime($post_date)) / (60 * 60 * 24);
                    
                    if ($days_old < 7) {
                        $marker_color = '#10b981'; // Green for new
                    } elseif ($days_old < 30) {
                        $marker_color = '#f97316'; // Orange for featured
                    } else {
                        $marker_color = '#0f766e'; // Teal for standard
                    }
                    
                    $properties_data[] = array(
                        'id' => get_the_ID(),
                        'title' => get_the_title(),
                        'lat' => floatval($latitude),
                        'lng' => floatval($longitude),
                        'price' => $price ? '$' . number_format($price) : 'Price on request',
                        'bedrooms' => $bedrooms,
                        'bathrooms' => $bathrooms,
                        'area_sqft' => $area_sqft,
                        'permalink' => get_permalink(),
                        'image' => $featured_image ? $featured_image : '',
                        'marker_color' => $marker_color,
                        'days_old' => $days_old
                    );
                }
            endwhile;
            wp_reset_postdata();
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
    padding: 0 20px;
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
    position: relative;
}

#googleMap {
    width: 100%;
    height: 100%;
    min-height: 400px;
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
    border-color: #10b981;
    color: #10b981;
    background: #f0fdf4;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}

.view-btn.active {
    background: #10b981;
    border-color: #10b981;
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.view-btn.active:hover {
    background: #059669;
    border-color: #059669;
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
}

.sort-controls {
    display: flex;
    align-items: center;
    gap: 15px;
}

.sort-label {
    color: #6b7280;
    font-size: 14px;
    font-weight: 500;
}

.sort-select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background: white;
    color: #374151;
    font-size: 14px;
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s ease;
}

.sort-select:hover,
.sort-select:focus {
    border-color: #10b981;
}

.layout-toggle {
    display: flex;
    align-items: center;
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
    border-color: #10b981;
    color: #10b981;
    background: #f0fdf4;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
}

.layout-btn.active,
.filter-toggle.active {
    background: #10b981;
    border-color: #10b981;
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.layout-btn.active:hover,
.filter-toggle.active:hover {
    background: #059669;
    border-color: #059669;
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
    background-color: #10b981;
    color: white;
    font-weight: 600;
}

.apply-filter-btn:hover {
    background-color: #059669;
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

// Initialize map state on page load
document.addEventListener('DOMContentLoaded', function() {
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
    
    // Initialize Google Map if API key is available
    <?php if (!empty($google_maps_api_key)): ?>
    initializeGoogleMap();
    <?php else: ?>
    console.warn('Google Maps API key is not set. Please add your API key in Map Settings.');
    <?php endif; ?>
});
</script>

<?php if (!empty($google_maps_api_key)): ?>
<script>
console.log('=== Google Maps Integration Started ===');
console.log('API Key from PHP:', '<?php echo substr(esc_js($google_maps_api_key), 0, 10); ?>...');
console.log('Properties with coordinates:', <?php echo count($properties_data); ?>);

// Google Maps Variables - Must be global
window.map = null;
window.markers = [];
window.infoWindows = [];
window.propertiesData = <?php echo json_encode($properties_data); ?>;
window.googleMapsApiKey = '<?php echo esc_js($google_maps_api_key); ?>';
window.mapInitialized = false;

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
    
    // Get map center from properties or use default
    let centerLat = <?php echo esc_js($map_center_lat); ?>;
    let centerLng = <?php echo esc_js($map_center_lng); ?>;
    
    // If properties exist, center map on them
    if (window.propertiesData && window.propertiesData.length > 0) {
        console.log('📍 Centering map on properties');
        let bounds = new google.maps.LatLngBounds();
        window.propertiesData.forEach(function(property) {
            bounds.extend(new google.maps.LatLng(property.lat, property.lng));
        });
        centerLat = bounds.getCenter().lat();
        centerLng = bounds.getCenter().lng();
        console.log('Map center:', centerLat, centerLng);
    } else {
        console.log('📌 No properties with coordinates, using default center');
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
        
        // Fit bounds if properties exist
        if (window.propertiesData && window.propertiesData.length > 0) {
            let bounds = new google.maps.LatLngBounds();
            window.propertiesData.forEach(function(property) {
                bounds.extend(new google.maps.LatLng(property.lat, property.lng));
            });
            window.map.fitBounds(bounds);
            console.log('📍 Map bounds adjusted to fit all properties');
        }
        
        // Add markers for each property
        addPropertyMarkers();
        
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

// Add property markers to map
function addPropertyMarkers() {
    if (!window.map || !window.propertiesData || window.propertiesData.length === 0) {
        console.log('Cannot add markers: map or properties not available');
        return;
    }
    
    // Clear existing markers
    window.markers.forEach(function(marker) {
        marker.setMap(null);
    });
    window.markers = [];
    window.infoWindows = [];
    
    window.propertiesData.forEach(function(property) {
        // Create custom marker icon based on property status
        const markerIcon = {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 10,
            fillColor: property.marker_color,
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2
        };
        
        // Create marker
        const marker = new google.maps.Marker({
            position: { lat: property.lat, lng: property.lng },
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
            
            // Open this info window
            infoWindow.open(window.map, marker);
            
            // Highlight property card in list
            highlightProperty(property.id);
            
            // Pan to marker
            window.map.panTo(marker.getPosition());
            
            console.log('Property clicked:', property.title, 'ID:', property.id);
        });
        
        window.markers.push(marker);
        window.infoWindows.push(infoWindow);
    });
    
    console.log('Added', window.markers.length, 'markers to map');
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
                if (window.propertiesData && window.propertiesData.length > 0) {
                    let bounds = new google.maps.LatLngBounds();
                    window.propertiesData.forEach(function(property) {
                        bounds.extend(new google.maps.LatLng(property.lat, property.lng));
                    });
                    window.map.fitBounds(bounds);
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
    const script = document.createElement('script');
    const apiUrl = 'https://maps.googleapis.com/maps/api/js?key=' + window.googleMapsApiKey + '&callback=initMap&libraries=places';
    console.log('API URL:', apiUrl.replace(window.googleMapsApiKey, 'KEY_HIDDEN'));
    script.src = apiUrl;
    script.async = true;
    script.defer = true;
    script.onload = function() {
        console.log('✅ Google Maps API script loaded successfully');
    };
    script.onerror = function() {
        console.error('❌ Failed to load Google Maps API');
        console.error('Please check:');
        console.error('1. Your API key is correct');
        console.error('2. Maps JavaScript API is enabled in Google Cloud Console');
        console.error('3. Your domain is whitelisted (if restricted)');
        console.error('4. You have billing enabled in Google Cloud');
    };
    document.head.appendChild(script);
    console.log('📝 Script tag added to document head');
})();
</script>
<?php endif; ?>

<?php wp_reset_postdata(); ?>

<?php get_footer(); ?>