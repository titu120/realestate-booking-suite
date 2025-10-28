<?php get_header(); ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">

<?php
// Note: AJAX functionality disabled for now to prevent errors
// wp_enqueue_script('resbs-dynamic-archive');

// Get query parameters for filtering
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : '';
$property_type = isset($_GET['property_type']) ? sanitize_text_field($_GET['property_type']) : '';
$bedrooms = isset($_GET['bedrooms']) ? intval($_GET['bedrooms']) : '';
$bathrooms = isset($_GET['bathrooms']) ? intval($_GET['bathrooms']) : '';
$min_sqft = isset($_GET['min_sqft']) ? intval($_GET['min_sqft']) : '';
$max_sqft = isset($_GET['max_sqft']) ? intval($_GET['max_sqft']) : '';
$year_built = isset($_GET['year_built']) ? sanitize_text_field($_GET['year_built']) : '';
$property_status = isset($_GET['property_status']) ? sanitize_text_field($_GET['property_status']) : '';
$sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'date';

// Build WP_Query arguments
$args = array(
    'post_type' => 'property',
    'post_status' => 'publish',
    'posts_per_page' => 12,
    'paged' => $paged,
    'meta_query' => array(),
    'tax_query' => array(),
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
    
    if (!empty($min_price)) {
        $price_query['value'] = $min_price;
        $price_query['compare'] = '>=';
    }
    
    if (!empty($max_price)) {
        if (!empty($min_price)) {
            $price_query['compare'] = 'BETWEEN';
            $price_query['value'] = array($min_price, $max_price);
        } else {
            $price_query['value'] = $max_price;
            $price_query['compare'] = '<=';
        }
    }
    
    $args['meta_query'][] = $price_query;
}

// Add bedrooms filter
if (!empty($bedrooms)) {
    $args['meta_query'][] = array(
        'key' => '_property_bedrooms',
        'value' => $bedrooms,
        'compare' => '>=',
        'type' => 'NUMERIC'
    );
}

// Add bathrooms filter
if (!empty($bathrooms)) {
    $args['meta_query'][] = array(
        'key' => '_property_bathrooms',
        'value' => $bathrooms,
        'compare' => '>=',
        'type' => 'NUMERIC'
    );
}

// Add square footage filter
if (!empty($min_sqft) || !empty($max_sqft)) {
    $sqft_query = array(
        'key' => '_property_area_sqft',
        'type' => 'NUMERIC',
    );
    
    if (!empty($min_sqft)) {
        $sqft_query['value'] = $min_sqft;
        $sqft_query['compare'] = '>=';
    }
    
    if (!empty($max_sqft)) {
        if (!empty($min_sqft)) {
            $sqft_query['compare'] = 'BETWEEN';
            $sqft_query['value'] = array($min_sqft, $max_sqft);
        } else {
            $sqft_query['value'] = $max_sqft;
            $sqft_query['compare'] = '<=';
        }
    }
    
    $args['meta_query'][] = $sqft_query;
}

// Add year built filter
if (!empty($year_built)) {
    $year_value = str_replace('+', '', $year_built);
    $args['meta_query'][] = array(
        'key' => '_property_year_built',
        'value' => $year_value,
        'compare' => '>=',
        'type' => 'NUMERIC'
    );
}

// Add property type filter
if (!empty($property_type)) {
    $args['tax_query'][] = array(
        'taxonomy' => 'property_type',
        'field' => 'slug',
        'terms' => $property_type,
    );
}

// Add property status filter
if (!empty($property_status)) {
    $args['tax_query'][] = array(
        'taxonomy' => 'property_status',
        'field' => 'slug',
        'terms' => $property_status,
    );
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

// Set tax_query relation
if (count($args['tax_query']) > 1) {
    $args['tax_query']['relation'] = 'AND';
}

// Execute the query
$properties_query = new WP_Query($args);

// Get property types for filter dropdown
$property_types = get_terms(array(
    'taxonomy' => 'property_type',
    'hide_empty' => false,
));

// Get property statuses for filter dropdown
$property_statuses = get_terms(array(
    'taxonomy' => 'property_status',
    'hide_empty' => false,
));
?>

<div class="rbs-archive">

    <!-- Advanced Search Bar -->
    <div class="search-bar">
        <div class="container">
            <form method="GET" class="search-container">
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
            </form>

            <!-- Dropdown Panels Container -->
            <div class="dropdowns-container">
                <!-- Price Dropdown -->
                <div id="priceDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label">Min Price</label>
                            <input type="number" placeholder="$ Min Price" class="dropdown-input" name="min_price" value="<?php echo esc_attr($min_price); ?>" onchange="this.form.submit()">
                        </div>
                        <div>
                            <label class="dropdown-label">Max Price</label>
                            <input type="number" placeholder="$ Max Price" class="dropdown-input" name="max_price" value="<?php echo esc_attr($max_price); ?>" onchange="this.form.submit()">
                        </div>
                    </div>
                </div>

                <!-- Type Dropdown -->
                <div id="typeDropdown" class="dropdown-content">
                    <div class="checkbox-grid">
                        <?php if ($property_types && !is_wp_error($property_types)): ?>
                            <?php foreach ($property_types as $type): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="property_type" value="<?php echo esc_attr($type->slug); ?>" <?php checked($property_type, $type->slug); ?> onchange="this.form.submit()">
                                    <span><?php echo esc_html($type->name); ?></span>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <label class="checkbox-item">
                            <input type="checkbox">
                            <span>House</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox">
                            <span>Apartment</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox">
                            <span>Condo</span>
                        </label>
                        <label class="checkbox-item">
                            <input type="checkbox">
                            <span>Office</span>
                        </label>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bedrooms Dropdown -->
                <div id="bedroomsDropdown" class="dropdown-content">
                    <div class="filter-options">
                        <button type="button" class="filter-option" data-value="">Any</button>
                        <button type="button" class="filter-option" data-value="1">1+</button>
                        <button type="button" class="filter-option" data-value="2">2+</button>
                        <button type="button" class="filter-option" data-value="3">3+</button>
                        <button type="button" class="filter-option" data-value="4">4+</button>
                        <button type="button" class="filter-option" data-value="5">5+</button>
                    </div>
                    <input type="hidden" name="bedrooms" value="<?php echo esc_attr($bedrooms); ?>">
                </div>

                <!-- Bathrooms Dropdown -->
                <div id="bathroomsDropdown" class="dropdown-content">
                    <div class="filter-options">
                        <button type="button" class="filter-option" data-value="">Any</button>
                        <button type="button" class="filter-option" data-value="1">1+</button>
                        <button type="button" class="filter-option" data-value="2">2+</button>
                        <button type="button" class="filter-option" data-value="3">3+</button>
                        <button type="button" class="filter-option" data-value="4">4+</button>
                    </div>
                    <input type="hidden" name="bathrooms" value="<?php echo esc_attr($bathrooms); ?>">
                </div>

                <!-- More Filters Dropdown -->
                <div id="moreFiltersDropdown" class="dropdown-content">
                    <div class="dropdown-grid">
                        <div>
                            <label class="dropdown-label">Square Feet</label>
                            <div class="flex" style="gap: 8px;">
                                <input type="number" placeholder="Min" class="dropdown-input" name="min_sqft" value="<?php echo esc_attr($min_sqft); ?>" onchange="this.form.submit()">
                                <input type="number" placeholder="Max" class="dropdown-input" name="max_sqft" value="<?php echo esc_attr($max_sqft); ?>" onchange="this.form.submit()">
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
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container main-content">
        <!-- Control Bar -->
        <div class="control-bar">
            <!-- Left Side -->
            <div class="view-controls">
                <button onclick="toggleView('list')" class="view-btn">
                    <i class="fas fa-list"></i>
                    <span>List View</span>
                </button>
                <div class="view-divider">|</div>
                <button onclick="toggleView('map')" class="view-btn active">
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
                <select class="sort-select" name="sort_by" onchange="this.form.submit()">
                    <option value="newest" <?php selected($sort_by, 'newest'); ?>>Newest</option>
                    <option value="price_low" <?php selected($sort_by, 'price_low'); ?>>Price: Low to High</option>
                    <option value="price_high" <?php selected($sort_by, 'price_high'); ?>>Price: High to Low</option>
                    <option value="popular" <?php selected($sort_by, 'popular'); ?>>Most Popular</option>
                </select>

                <div class="layout-toggle">
                    <button onclick="showGridLayout()" class="layout-btn" id="gridBtn"></button>
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
                                        <a href="<?php echo get_permalink(); ?>" class="view-details-btn" target="_blank">
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

            <!-- Interactive Map -->
            <div class="map-section">
                <div class="map-container">
                    <div class="map-bg">
                        <!-- Map Controls -->
                        <div class="map-controls">
                            <button class="map-control">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button class="map-control">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button class="map-control">
                                <i class="fas fa-expand"></i>
                            </button>
                        </div>

                        <!-- Map Legend -->
                        <div class="map-legend">
                            <h4 class="legend-title">Legend</h4>
                            <div class="legend-items">
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #f97316;"></div>
                                    <span class="legend-label">Featured</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #10b981;"></div>
                                    <span class="legend-label">Just Listed</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #0f766e;"></div>
                                    <span class="legend-label">Standard</span>
                                </div>
                            </div>
                        </div>

                        <!-- Map Markers -->
                        <div id="mapMarkers" class="map-markers">
                            <?php 
                            // Reset query for map markers
                            $properties_query->rewind_posts();
                            $marker_count = 0;
                            ?>
                            <?php while ($properties_query->have_posts()): $properties_query->the_post(); ?>
                                <?php
                                $marker_count++;
                                $latitude = get_post_meta(get_the_ID(), '_property_latitude', true);
                                $longitude = get_post_meta(get_the_ID(), '_property_longitude', true);
                                $price = get_post_meta(get_the_ID(), '_property_price', true);
                                
                                // Skip if no coordinates
                                if (!$latitude || !$longitude) continue;
                                
                                // Determine marker color based on property age
                                $post_date = get_the_date('Y-m-d');
                                $days_old = (time() - strtotime($post_date)) / (60 * 60 * 24);
                                
                                if ($days_old < 7) {
                                    $marker_color = '#10b981'; // Green for new
                                    $marker_icon = 'fas fa-building';
                                } elseif ($days_old < 30) {
                                    $marker_color = '#f97316'; // Orange for featured
                                    $marker_icon = 'fas fa-home';
                                } else {
                                    $marker_color = '#0f766e'; // Teal for standard
                                    $marker_icon = 'fas fa-building';
                                }
                                
                                // Random positioning for demo (in real implementation, use actual coordinates)
                                $top = 25 + ($marker_count * 8) % 50;
                                $left = 20 + ($marker_count * 12) % 60;
                                
                                $formatted_price = $price ? '$' . number_format($price) : 'Price on request';
                                ?>
                                
                                <!-- Marker -->
                                <div class="map-marker" style="top: <?php echo $top; ?>%; left: <?php echo $left; ?>%;" data-property-id="<?php echo get_the_ID(); ?>" onclick="highlightProperty(<?php echo get_the_ID(); ?>)">
                                <div class="marker-tooltip">
                                        <div class="tooltip-title"><?php echo esc_html(get_the_title()); ?></div>
                                        <div class="tooltip-price"><?php echo esc_html($formatted_price); ?></div>
                                </div>
                                    <div class="marker-icon" style="background-color: <?php echo esc_attr($marker_color); ?>;">
                                        <i class="<?php echo esc_attr($marker_icon); ?>"></i>
                                </div>
                            </div>
                            <?php endwhile; ?>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
        
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

// Simple view toggle functionality
function toggleView(viewType) {
    const listBtn = document.querySelector('[onclick="toggleView(\'list\')"]');
    const mapBtn = document.querySelector('[onclick="toggleView(\'map\')"]');
    const mapSection = document.querySelector('.map-section');
    const listingsContainer = document.querySelector('.listings-container');
    const propertyGrid = document.getElementById('propertyGrid');
    
    // Remove active class from all buttons
    if (listBtn) listBtn.classList.remove('active');
    if (mapBtn) mapBtn.classList.remove('active');
    
    // Add active class to clicked button and change layout
    if (viewType === 'list') {
        if (listBtn) listBtn.classList.add('active');
        // Hide map for list view
        if (mapSection) {
        mapSection.classList.remove('map-visible');
        mapSection.classList.add('map-hidden');
        }
        if (listingsContainer) {
        listingsContainer.classList.remove('map-visible');
        }
        if (propertyGrid) {
        propertyGrid.style.setProperty('grid-template-columns', 'repeat(4, 1fr)', 'important');
        }
    } else {
        if (mapBtn) mapBtn.classList.add('active');
        // Show map for map view
        if (mapSection) {
        mapSection.classList.remove('map-hidden');
        mapSection.classList.add('map-visible');
        }
        if (listingsContainer) {
        listingsContainer.classList.add('map-visible');
        }
        if (propertyGrid) {
        propertyGrid.style.setProperty('grid-template-columns', 'repeat(2, 1fr)', 'important');
        }
    }
}

// Simple map show functionality
function showMap() {
    const mapSection = document.querySelector('.map-section');
    const listingsContainer = document.querySelector('.listings-container');
    const propertyGrid = document.getElementById('propertyGrid');
    
    if (mapSection) {
        mapSection.classList.remove('map-hidden');
        mapSection.classList.add('map-visible');
    }
    
    if (listingsContainer) {
        listingsContainer.classList.add('map-visible');
    }
    
    if (propertyGrid) {
        propertyGrid.style.setProperty('grid-template-columns', 'repeat(2, 1fr)', 'important');
    }
}

// Grid layout functionality
function showGridLayout() {
    const mapSection = document.querySelector('.map-section');
    const listingsContainer = document.querySelector('.listings-container');
    const propertyGrid = document.getElementById('propertyGrid');
    const gridBtn = document.getElementById('gridBtn');
    const mapToggleBtn = document.getElementById('mapToggleBtn');
    const listBtn = document.querySelector('[onclick="toggleView(\'list\')"]');
    const mapBtn = document.querySelector('[onclick="toggleView(\'map\')"]');
    
    // Remove active from all other buttons
    if (listBtn) listBtn.classList.remove('active');
    if (mapBtn) mapBtn.classList.remove('active');
    if (mapToggleBtn) mapToggleBtn.classList.remove('active');
    
    // Activate grid button
    if (gridBtn) gridBtn.classList.add('active');
    
    // Hide map
    if (mapSection) {
        mapSection.classList.remove('map-visible');
        mapSection.classList.add('map-hidden');
    }
    
    if (listingsContainer) {
        listingsContainer.classList.remove('map-visible');
    }
    
    // Set grid to 4 columns (full width)
    if (propertyGrid) {
        propertyGrid.style.setProperty('grid-template-columns', 'repeat(4, 1fr)', 'important');
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

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set initial map state to hidden
    const mapSection = document.querySelector('.map-section');
    if (mapSection) {
        mapSection.classList.add('map-hidden');
    }
    
    // Set initial grid layout (4 columns for full width by default)
    const propertyGrid = document.getElementById('propertyGrid');
    if (propertyGrid) {
        propertyGrid.style.setProperty('grid-template-columns', 'repeat(4, 1fr)', 'important');
    }
});
</script>

<?php wp_reset_postdata(); ?>

<?php get_footer(); ?>