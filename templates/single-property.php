<?php
/**
 * Single Property Template - Dynamic Version
 * Converts static HTML to dynamic WordPress content
 */





 

// Get the current property ID
$property_id = get_the_ID();

// Helper function to safely handle meta fields
function resbs_safe_meta($value) {
    if (is_array($value)) {
        return array_filter($value);
    } elseif (is_string($value)) {
        return array_filter(explode(',', $value));
    }
    return array();
}

// Helper function to get similar properties
function resbs_get_similar_properties($property_id, $limit = 4) {
    // Get current property data
    $property_type = get_post_meta($property_id, '_property_type', true);
    $property_status = get_post_meta($property_id, '_property_status', true);
    $city = get_post_meta($property_id, '_property_city', true);
    $price = get_post_meta($property_id, '_property_price', true);
    
    // Build meta query for similar properties
    $meta_query = array(
        'relation' => 'OR',
        // Same property type
        array(
            'key' => '_property_type',
            'value' => $property_type,
            'compare' => '='
        ),
        // Same city
        array(
            'key' => '_property_city',
            'value' => $city,
            'compare' => '='
        ),
        // Same status
        array(
            'key' => '_property_status',
            'value' => $property_status,
            'compare' => '='
        )
    );
    
    // Add price range if price is available and numeric
    if ($price && is_numeric($price) && $price > 0) {
        $meta_query[] = array(
            'key' => '_property_price',
            'value' => array(
                floatval($price) * 0.8, // 20% less
                floatval($price) * 1.2  // 20% more
            ),
            'compare' => 'BETWEEN',
            'type' => 'NUMERIC'
        );
    }
    
    // Get similar properties with better criteria
    $similar_properties = get_posts(array(
        'post_type' => 'property',
        'posts_per_page' => $limit,
        'post__not_in' => array($property_id),
        'meta_query' => $meta_query,
        'orderby' => 'rand' // Randomize to show different properties
    ));
    
    // If no similar properties found, get any other properties with better fallback
    if (empty($similar_properties)) {
        $similar_properties = get_posts(array(
            'post_type' => 'property',
            'posts_per_page' => $limit,
            'post__not_in' => array($property_id),
            'orderby' => 'rand'
        ));
    }
    
    // If still no properties, get any property including current one (as last resort)
    if (empty($similar_properties)) {
        $similar_properties = get_posts(array(
            'post_type' => 'property',
            'posts_per_page' => $limit,
            'orderby' => 'rand'
        ));
    }
    
    return $similar_properties;
}

// Get all property meta fields with fallbacks
$price = get_post_meta($property_id, '_property_price', true);
$price_per_sqft = get_post_meta($property_id, '_property_price_per_sqft', true);
$price_note = get_post_meta($property_id, '_property_price_note', true);
$call_for_price = get_post_meta($property_id, '_property_call_for_price', true);
$bedrooms = get_post_meta($property_id, '_property_bedrooms', true);
$bathrooms = get_post_meta($property_id, '_property_bathrooms', true);
$half_baths = get_post_meta($property_id, '_property_half_baths', true);
$total_rooms = get_post_meta($property_id, '_property_total_rooms', true);
$floors = get_post_meta($property_id, '_property_floors', true);
$floor_level = get_post_meta($property_id, '_property_floor_level', true);
$area = get_post_meta($property_id, '_property_area_sqft', true);
$lot_size = get_post_meta($property_id, '_property_lot_size_sqft', true);
$year_built = get_post_meta($property_id, '_property_year_built', true);
$year_remodeled = get_post_meta($property_id, '_property_year_remodeled', true);
$latitude = get_post_meta($property_id, '_property_latitude', true);
$longitude = get_post_meta($property_id, '_property_longitude', true);
$gallery = get_post_meta($property_id, '_property_gallery', true);
$floor_plans = get_post_meta($property_id, '_property_floor_plans', true);
$virtual_tour = get_post_meta($property_id, '_property_virtual_tour', true);
$virtual_tour_title = get_post_meta($property_id, '_property_virtual_tour_title', true);
$virtual_tour_description = get_post_meta($property_id, '_property_virtual_tour_description', true);
$virtual_tour_button_text = get_post_meta($property_id, '_property_virtual_tour_button_text', true);
$video_url = get_post_meta($property_id, '_property_video_url', true);
$video_embed = get_post_meta($property_id, '_property_video_embed', true);
$map_iframe = get_post_meta($property_id, '_property_map_iframe', true);

// Get nearby features
$nearby_schools = get_post_meta($property_id, '_property_nearby_schools', true);
$nearby_shopping = get_post_meta($property_id, '_property_nearby_shopping', true);
$nearby_restaurants = get_post_meta($property_id, '_property_nearby_restaurants', true);

// Debug: Log video and virtual tour data
if (current_user_can('manage_options')) {
    error_log('Map iframe data: ' . ($map_iframe ? substr($map_iframe, 0, 100) . '...' : 'EMPTY'));
    error_log('Video URL: ' . ($video_url ? $video_url : 'EMPTY'));
    error_log('Video embed: ' . ($video_embed ? substr($video_embed, 0, 100) . '...' : 'EMPTY'));
    error_log('Virtual tour: ' . ($virtual_tour ? $virtual_tour : 'EMPTY'));
}
$description = get_post_meta($property_id, '_property_description', true);
$features = get_post_meta($property_id, '_property_features', true);
$amenities = get_post_meta($property_id, '_property_amenities', true);
$parking = get_post_meta($property_id, '_property_parking', true);
$heating = get_post_meta($property_id, '_property_heating', true);
$cooling = get_post_meta($property_id, '_property_cooling', true);
$basement = get_post_meta($property_id, '_property_basement', true);
$roof = get_post_meta($property_id, '_property_roof', true);
$exterior_material = get_post_meta($property_id, '_property_exterior_material', true);
$floor_covering = get_post_meta($property_id, '_property_floor_covering', true);

// Get property status, type, and condition
$property_status = get_post_meta($property_id, '_property_status', true);
$property_type = get_post_meta($property_id, '_property_type', true);
$property_condition = get_post_meta($property_id, '_property_condition', true);

// Get location data
$address = get_post_meta($property_id, '_property_address', true);
$city = get_post_meta($property_id, '_property_city', true);
$state = get_post_meta($property_id, '_property_state', true);
$zip = get_post_meta($property_id, '_property_zip', true);

// Get agent data
$agent_id = get_post_meta($property_id, '_property_agent', true);
$agent_name = get_post_meta($property_id, '_property_agent_name', true);
$agent_phone = get_post_meta($property_id, '_property_agent_phone', true);
$agent_email = get_post_meta($property_id, '_property_agent_email', true);
$agent_photo = get_post_meta($property_id, '_property_agent_photo', true);
$agent_properties_sold = get_post_meta($property_id, '_property_agent_properties_sold', true);
$agent_experience = get_post_meta($property_id, '_property_agent_experience', true);
$agent_response_time = get_post_meta($property_id, '_property_agent_response_time', true);
$agent_rating = get_post_meta($property_id, '_property_agent_rating', true);
$agent_reviews = get_post_meta($property_id, '_property_agent_reviews', true);
$agent_send_message_text = get_post_meta($property_id, '_property_agent_send_message_text', true);

// Contact Form Dynamic Fields
$contact_form_title = get_post_meta($property_id, '_property_contact_form_title', true);
$contact_name_label = get_post_meta($property_id, '_property_contact_name_label', true);
$contact_email_label = get_post_meta($property_id, '_property_contact_email_label', true);
$contact_phone_label = get_post_meta($property_id, '_property_contact_phone_label', true);
$contact_message_label = get_post_meta($property_id, '_property_contact_message_label', true);
$contact_success_message = get_post_meta($property_id, '_property_contact_success_message', true);
$contact_submit_text = get_post_meta($property_id, '_property_contact_submit_text', true);

// Mortgage Calculator Dynamic Fields
$mortgage_calculator_title = get_post_meta($property_id, '_property_mortgage_calculator_title', true);
$mortgage_property_price_label = get_post_meta($property_id, '_property_mortgage_property_price_label', true);
$mortgage_down_payment_label = get_post_meta($property_id, '_property_mortgage_down_payment_label', true);
$mortgage_interest_rate_label = get_post_meta($property_id, '_property_mortgage_interest_rate_label', true);
$mortgage_loan_term_label = get_post_meta($property_id, '_property_mortgage_loan_term_label', true);
$mortgage_monthly_payment_label = get_post_meta($property_id, '_property_mortgage_monthly_payment_label', true);
$mortgage_default_down_payment = get_post_meta($property_id, '_property_mortgage_default_down_payment', true);
$mortgage_default_interest_rate = get_post_meta($property_id, '_property_mortgage_default_interest_rate', true);
$mortgage_default_loan_term = get_post_meta($property_id, '_property_mortgage_default_loan_term', true);
$mortgage_loan_terms = get_post_meta($property_id, '_property_mortgage_loan_terms', true);
$mortgage_disclaimer_text = get_post_meta($property_id, '_property_mortgage_disclaimer_text', true);

// Tour Information Fields
$tour_duration = get_post_meta($property_id, '_property_tour_duration', true);
$tour_group_size = get_post_meta($property_id, '_property_tour_group_size', true);
$tour_safety = get_post_meta($property_id, '_property_tour_safety', true);

// Get gallery images with proper URL conversion
$gallery_images = array();

if (is_array($gallery)) {
    $gallery_ids = array_filter($gallery);
} elseif (is_string($gallery) && !empty($gallery)) {
    $gallery_ids = array_filter(explode(',', $gallery));
} else {
    $gallery_ids = array();
}

// Convert attachment IDs to URLs
foreach ($gallery_ids as $image_id) {
    if (is_numeric($image_id)) {
        $image_url = wp_get_attachment_image_url(intval($image_id), 'large');
        if ($image_url) {
            $gallery_images[] = $image_url;
        }
    } elseif (filter_var($image_id, FILTER_VALIDATE_URL)) {
        $gallery_images[] = $image_id;
    }
}

// Fallback to featured image if no gallery images
if (empty($gallery_images)) {
    $featured_image_id = get_post_thumbnail_id($property_id);
    if ($featured_image_id) {
        $featured_image_url = wp_get_attachment_image_url($featured_image_id, 'large');
        if ($featured_image_url) {
            $gallery_images[] = $featured_image_url;
        }
    }
}

// Get features and amenities
if (is_array($features)) {
    $features_list = array_filter(array_map('trim', $features));
} else {
    $features_list = array_filter(array_map('trim', explode(',', $features)));
}

if (is_array($amenities)) {
    $amenities_list = array_filter(array_map('trim', $amenities));
} else {
    $amenities_list = array_filter(array_map('trim', explode(',', $amenities)));
}

// Format price
$formatted_price = $price ? '$' . number_format($price) : 'Price on Request';
$price_per_sqft_formatted = $price_per_sqft ? '$' . number_format($price_per_sqft) . '/sq ft' : '';

// Format location
$full_address = trim($address . ', ' . $city . ', ' . $state . ' ' . $zip, ', ');

// Get property title
$property_title = get_the_title();
?>

<!-- External CSS and JS -->
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__) . '../assets/css/single-property.css'; ?>">
<script src="<?php echo plugin_dir_url(__FILE__) . '../assets/js/single-property.js'; ?>"></script>

<div class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-md sticky top-0 z-50 no-print">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <a href="<?php echo home_url(); ?>" class="flex items-center space-x-2">
                        <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-home text-white text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold text-gray-800"><?php echo get_bloginfo('name'); ?></span>
                    </a>
                    <nav class="hidden md:flex space-x-6">
                        <a href="<?php echo home_url('/properties'); ?>" class="text-gray-600 hover:text-emerald-500 transition">Properties</a>
                        <a href="<?php echo home_url('/agents'); ?>" class="text-gray-600 hover:text-emerald-500 transition">Agents</a>
                        <a href="<?php echo home_url('/about'); ?>" class="text-gray-600 hover:text-emerald-500 transition">About</a>
                        <a href="<?php echo home_url('/contact'); ?>" class="text-gray-600 hover:text-emerald-500 transition">Contact</a>
                    </nav>
                </div>
                <div class="flex items-center space-x-4">
                    <button class="hidden md:block text-gray-600 hover:text-emerald-500">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="hidden md:block text-gray-600 hover:text-emerald-500">
                        <i class="fas fa-heart"></i>
                    </button>
                    <a href="<?php echo wp_login_url(); ?>" class="bg-emerald-500 text-white px-6 py-2 rounded-lg hover:bg-emerald-600 transition">
                        Sign In
                    </a>
                    <button class="md:hidden text-gray-600" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="hidden md:hidden bg-white shadow-lg no-print">
        <nav class="container mx-auto px-4 py-4 space-y-3">
            <a href="<?php echo home_url('/properties'); ?>" class="block text-gray-600 hover:text-emerald-500">Properties</a>
            <a href="<?php echo home_url('/agents'); ?>" class="block text-gray-600 hover:text-emerald-500">Agents</a>
            <a href="<?php echo home_url('/about'); ?>" class="block text-gray-600 hover:text-emerald-500">About</a>
            <a href="<?php echo home_url('/contact'); ?>" class="block text-gray-600 hover:text-emerald-500">Contact</a>
        </nav>
    </div>

    <!-- Breadcrumb -->
    <div class="bg-white border-b no-print">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center space-x-2 text-sm">
                <a href="<?php echo home_url(); ?>" class="text-gray-500 hover:text-emerald-500">Home</a>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                <a href="<?php echo home_url('/properties'); ?>" class="text-gray-500 hover:text-emerald-500">Properties</a>
                <i class="fas fa-chevron-right text-gray-400 text-xs"></i>
                <span class="text-gray-800 font-medium"><?php echo esc_html($property_title); ?></span>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Property Header -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-4">
                        <div>
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-semibold badge"><?php echo esc_html($property_status); ?></span>
                                <?php if (get_post_meta($property_id, '_property_featured', true)): ?>
                                <span class="bg-blue-500 text-white px-3 py-1 rounded-full text-sm font-semibold">Featured</span>
                                <?php endif; ?>
                                <?php if (get_post_meta($property_id, '_property_hot_deal', true)): ?>
                                <span class="bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-semibold">Hot Deal</span>
                                <?php endif; ?>
                            </div>
                            <h1 class="text-3xl md:text-4xl font-bold text-gray-800 mb-2"><?php echo esc_html($property_title); ?></h1>
                            <div class="flex flex-wrap items-center gap-4 mb-2">
                                <p class="text-gray-600 flex items-center">
                                    <i class="fas fa-map-marker-alt text-emerald-500 mr-2"></i>
                                    <?php echo esc_html($full_address ?: 'Location not specified'); ?>
                                </p>
                                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                                    <i class="fas fa-home mr-1"></i><?php echo esc_html($property_type); ?>
                                </span>
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                    <i class="fas fa-star mr-1"></i><?php echo esc_html($property_condition); ?>
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <p class="text-4xl font-bold text-emerald-500"><?php echo esc_html($formatted_price); ?></p>
                            <?php if ($price_per_sqft_formatted): ?>
                            <p class="text-gray-500 text-sm"><?php echo esc_html($price_per_sqft_formatted); ?></p>
                            <?php endif; ?>
                            
                            <!-- Price Note and Call for Price at bottom -->
                            <?php if ($price_note): ?>
                            <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded mt-3">
                                <p class="text-blue-800 text-sm"><i class="fas fa-info-circle mr-2"></i><?php echo esc_html($price_note); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($call_for_price): ?>
                            <div class="bg-orange-50 border-l-4 border-orange-500 p-3 rounded mt-3">
                                <p class="text-orange-800 text-sm"><i class="fas fa-phone mr-2"></i>Call for pricing information</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex flex-wrap gap-3 no-print">
                        <button onclick="shareProperty()" class="tooltip flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition" data-tooltip="Share Property">
                            <i class="fas fa-share-alt text-gray-600"></i>
                            <span class="text-gray-700">Share</span>
                        </button>
                        <button onclick="saveFavorite()" class="tooltip flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition" data-tooltip="Save to Favorites">
                            <i class="far fa-heart text-gray-600"></i>
                            <span class="text-gray-700">Save</span>
                        </button>
                        <button onclick="printPage()" class="tooltip flex items-center space-x-2 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition" data-tooltip="Print Details">
                            <i class="fas fa-print text-gray-600"></i>
                            <span class="text-gray-700">Print</span>
                        </button>
                        <button onclick="exportPDF()" class="tooltip flex items-center space-x-2 px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition" data-tooltip="Export as PDF">
                            <i class="fas fa-download"></i>
                            <span>Export PDF</span>
                        </button>
                    </div>
                </div>

                <!-- Image Gallery -->
                <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
                    <?php if (!empty($gallery_images)): ?>
                    <div class="grid grid-cols-4 gap-3">
                        <?php foreach ($gallery_images as $index => $image_url): ?>
                            <?php if ($index === 0): ?>
                            <div class="col-span-4 md:col-span-2 md:row-span-2">
                                <img src="<?php echo esc_url($image_url); ?>" alt="Property Main" class="w-full h-full object-cover rounded-lg cursor-pointer gallery-img" onclick="openImageViewer(<?php echo $index; ?>)">
                            </div>
                            <?php elseif ($index < 5): ?>
                            <div class="col-span-2 md:col-span-1">
                                <img src="<?php echo esc_url($image_url); ?>" alt="Property" class="w-full h-full object-cover rounded-lg cursor-pointer gallery-img" onclick="openImageViewer(<?php echo $index; ?>)">
                            </div>
                            <?php elseif ($index === 5): ?>
                            <div class="col-span-2 md:col-span-1 relative">
                                <img src="<?php echo esc_url($image_url); ?>" alt="Property" class="w-full h-full object-cover rounded-lg cursor-pointer gallery-img" onclick="openImageViewer(<?php echo $index; ?>)">
                                <div class="absolute inset-0 bg-black bg-opacity-50 rounded-lg flex items-center justify-center cursor-pointer" onclick="openImageViewer(<?php echo $index; ?>)">
                                    <span class="text-white text-xl font-semibold">+<?php echo count($gallery_images) - 5; ?> More</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-image text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500">No images available for this property</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Key Features -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Key Features</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-bed text-3xl text-emerald-500 mb-2"></i>
                            <p class="text-2xl font-bold text-gray-800"><?php echo esc_html($bedrooms ?: '0'); ?></p>
                            <p class="text-gray-600 text-sm">Bedrooms</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-bath text-3xl text-emerald-500 mb-2"></i>
                            <p class="text-2xl font-bold text-gray-800"><?php echo esc_html($bathrooms ?: '0'); ?></p>
                            <p class="text-gray-600 text-sm">Bathrooms</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-ruler-combined text-3xl text-emerald-500 mb-2"></i>
                            <p class="text-2xl font-bold text-gray-800"><?php echo esc_html($area ?: '0'); ?></p>
                            <p class="text-gray-600 text-sm">Sq Ft</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <i class="fas fa-car text-3xl text-emerald-500 mb-2"></i>
                            <p class="text-2xl font-bold text-gray-800"><?php echo esc_html($parking ?: '0'); ?></p>
                            <p class="text-gray-600 text-sm">Parking</p>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="bg-white rounded-xl shadow-sm mb-6 no-print">
                    <div class="flex overflow-x-auto border-b">
                        <button onclick="switchTab('overview')" class="tab-button tab-active px-6 py-4 font-semibold whitespace-nowrap" data-tab="overview">
                            <i class="fas fa-home mr-2"></i>Overview
                        </button>
                        <button onclick="switchTab('pricing')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="pricing">
                            <i class="fas fa-dollar-sign mr-2"></i>Pricing
                        </button>
                        <button onclick="switchTab('specifications')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="specifications">
                            <i class="fas fa-list mr-2"></i>Specifications
                        </button>
                        <button onclick="switchTab('location')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="location">
                            <i class="fas fa-map-marker-alt mr-2"></i>Location
                        </button>
                        <button onclick="switchTab('features')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="features">
                            <i class="fas fa-check-circle mr-2"></i>Features
                        </button>
                        <button onclick="switchTab('media')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="media">
                            <i class="fas fa-image mr-2"></i>Media
                        </button>
                        <?php if (!empty($floor_plans)): ?>
                        <button onclick="switchTab('floorplan')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="floorplan">
                            <i class="fas fa-vector-square mr-2"></i>Floor Plan
                        </button>
                        <?php endif; ?>
                        <button onclick="switchTab('booking')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="booking">
                            <i class="fas fa-calendar mr-2"></i>Booking
                        </button>
                    </div>

                    <!-- Tab Contents -->
                    <div class="p-6">
                        <!-- Overview Tab -->
                        <div id="overview-tab" class="tab-content">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Property Description</h3>
                            <div class="text-gray-600 space-y-4 leading-relaxed">
                                <?php 
                                // Get the post content (main description from editor)
                                $post_content = get_post_field('post_content', $property_id);
                                if ($post_content): ?>
                                    <?php echo wp_kses_post(wpautop($post_content)); ?>
                                <?php elseif ($description): ?>
                                    <?php echo wp_kses_post(wpautop($description)); ?>
                                <?php else: ?>
                                    <p>Welcome to this stunning property. This exceptional property offers a perfect blend of luxury, comfort, and modern design.</p>
                                <?php endif; ?>
                            </div>

                            <?php if ($virtual_tour): ?>
                            <div class="mt-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded">
                                <p class="text-emerald-800"><i class="fas fa-info-circle mr-2"></i><strong>Virtual Tour Available:</strong> <a href="<?php echo esc_url($virtual_tour); ?>" target="_blank" class="underline">Schedule a 3D virtual walkthrough</a> of this property at your convenience.</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pricing Tab -->
                        <div id="pricing-tab" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Pricing Information</h3>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-4">
                                    <div class="bg-emerald-50 rounded-lg p-6">
                                        <h4 class="text-2xl font-bold text-emerald-600 mb-2"><?php echo esc_html($formatted_price); ?></h4>
                                        <p class="text-gray-600"><?php echo esc_html($property_status); ?></p>
                                        <?php if ($price_per_sqft_formatted): ?>
                                        <p class="text-sm text-gray-500 mt-2"><?php echo esc_html($price_per_sqft_formatted); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($price_note): ?>
                                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                                        <p class="text-blue-800"><i class="fas fa-info-circle mr-2"></i><?php echo esc_html($price_note); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($call_for_price): ?>
                                    <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
                                        <p class="text-orange-800"><i class="fas fa-phone mr-2"></i>Call for pricing information</p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <h5 class="font-semibold text-gray-800 mb-3">Price Breakdown</h5>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">List Price:</span>
                                                <span class="font-semibold"><?php echo esc_html($formatted_price); ?></span>
                                            </div>
                                            <?php if ($price_per_sqft_formatted): ?>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Price per sq ft:</span>
                                                <span class="font-semibold"><?php echo esc_html($price_per_sqft_formatted); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Property Type:</span>
                                                <span class="font-semibold"><?php echo esc_html($property_type); ?></span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Status:</span>
                                                <span class="font-semibold text-emerald-600"><?php echo esc_html($property_status); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    

                                </div>
                            </div>
                        </div>

                        <!-- Specifications Tab -->
                        <div id="specifications-tab" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Property Specifications</h3>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <h4 class="font-semibold text-gray-800 mb-3">Basic Information</h4>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Property ID:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($property_id); ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Property Type:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($property_type); ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Property Condition:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($property_condition); ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Status:</span>
                                        <span class="font-semibold text-emerald-600"><?php echo esc_html($property_status); ?></span>
                                    </div>
                                    <?php if ($year_built): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Year Built:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($year_built); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($year_remodeled): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Year Remodeled:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($year_remodeled); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="space-y-3">
                                    <h4 class="font-semibold text-gray-800 mb-3">Room Details</h4>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Bedrooms:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($bedrooms ?: '0'); ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Bathrooms:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($bathrooms ?: '0'); ?></span>
                                    </div>
                                    <?php if ($half_baths): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Half Baths:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($half_baths); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($total_rooms): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Total Rooms:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($total_rooms); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floors): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Floors:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($floors); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floor_level): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Floor Level:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($floor_level); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mt-6 grid md:grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <h4 class="font-semibold text-gray-800 mb-3">Size & Area</h4>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Area:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($area ?: '0'); ?> sq ft</span>
                                    </div>
                                    <?php if ($lot_size): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Lot Size:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($lot_size); ?> sq ft</span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="space-y-3">
                                    <h4 class="font-semibold text-gray-800 mb-3">Building Features</h4>
                                    <?php if ($heating): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Heating:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($heating); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($cooling): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Cooling:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($cooling); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($roof): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Roof:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($roof); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($exterior_material): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Exterior:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($exterior_material); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floor_covering): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Flooring:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($floor_covering); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($basement): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Basement:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($basement); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($parking): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Parking:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($parking); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Details Tab -->
                        <div id="details-tab" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Property Details</h3>
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Property ID:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($property_id); ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Property Type:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($property_type); ?></span>
                                    </div>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Property Condition:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($property_condition); ?></span>
                                    </div>
                                    <?php if ($half_baths): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Half Baths:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($half_baths); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($total_rooms): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Total Rooms:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($total_rooms); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($year_built): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Year Built:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($year_built); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($lot_size): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Lot Size:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($lot_size); ?> sq ft</span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floors): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Stories:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($floors); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floor_level): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Floor Level:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($floor_level); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="space-y-3">
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Status:</span>
                                        <span class="font-semibold text-emerald-600"><?php echo esc_html($property_status); ?></span>
                                    </div>
                                    <?php if ($heating): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Heating:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($heating); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($cooling): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Cooling:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($cooling); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($roof): ?>
                                    <div class="flex justify-between py-2 border-b">
                                        <span class="text-gray-600">Roof:</span>
                                        <span class="font-semibold text-gray-800"><?php echo esc_html($roof); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Features Tab -->
                        <div id="features-tab" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Property Features & Amenities</h3>
                            
                            <!-- Filter Buttons -->
                            <div class="flex flex-wrap gap-2 mb-6 no-print">
                                <button onclick="filterAmenities('all')" class="filter-btn filter-active px-4 py-2 rounded-lg font-semibold text-sm transition" data-filter="all">All</button>
                                <button onclick="filterAmenities('interior')" class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-200 transition" data-filter="interior">Interior</button>
                                <button onclick="filterAmenities('amenities')" class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-200 transition" data-filter="amenities">Amenities</button>
                            </div>

                            <!-- Property Features Section -->
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-home text-emerald-500 mr-2"></i>Property Features
                                </h4>
                                
                                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4" id="featuresContainer">
                                    <!-- Common Interior Features -->

                                    
                                    <!-- Custom Features from Database -->
                                    <?php if (!empty($features_list)): ?>
                                        <?php foreach ($features_list as $feature): ?>
                                        <div class="amenity-item p-4 bg-emerald-50 rounded-lg border-l-4 border-emerald-500" data-category="interior">
                                            <i class="fas fa-star text-emerald-500 mr-2"></i>
                                            <span class="text-gray-700 font-medium"><?php echo esc_html($feature); ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Property Amenities Section -->
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-concierge-bell text-emerald-500 mr-2"></i>Property Amenities
                                </h4>
                                
                                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4" id="amenitiesContainer">
                                    <!-- Common Amenities -->
                                   
                                    
                                    <!-- Custom Amenities from Database -->
                                    <?php if (!empty($amenities_list)): ?>
                                        <?php foreach ($amenities_list as $amenity): ?>
                                        <div class="amenity-item p-4 bg-orange-50 rounded-lg border-l-4 border-orange-500" data-category="amenities">
                                            <i class="fas fa-star text-orange-500 mr-2"></i>
                                            <span class="text-gray-700 font-medium"><?php echo esc_html($amenity); ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Features & Amenities Tab -->
                        <div id="features-tab" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-6">Property Features & Amenities</h3>
                            
                            <!-- Features Section -->
                            <?php if (!empty($features_list)): ?>
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-home text-emerald-500 mr-2"></i>Property Features
                                </h4>
                                
                                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach ($features_list as $feature): ?>
                                    <div class="amenity-item p-4 bg-emerald-50 rounded-lg border-l-4 border-emerald-500 hover:bg-emerald-100 transition-colors">
                                        <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                                        <span class="text-gray-700 font-medium"><?php echo esc_html(trim($feature)); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Amenities Section -->
                            <?php if (!empty($amenities_list)): ?>
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-concierge-bell text-orange-500 mr-2"></i>Property Amenities
                                </h4>
                                
                                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <?php foreach ($amenities_list as $amenity): ?>
                                    <div class="amenity-item p-4 bg-orange-50 rounded-lg border-l-4 border-orange-500 hover:bg-orange-100 transition-colors">
                                        <i class="fas fa-check-circle text-orange-500 mr-2"></i>
                                        <span class="text-gray-700 font-medium"><?php echo esc_html(trim($amenity)); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- No Features/Amenities Message -->
                            <?php if (empty($features_list) && empty($amenities_list)): ?>
                            <div class="text-center py-12">
                                <i class="fas fa-info-circle text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No features or amenities have been added to this property yet.</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Media Tab -->
                        <div id="media-tab" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Property Media</h3>
                            
                            <!-- Image Gallery -->
                            <?php if (!empty($gallery_images)): ?>
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Photo Gallery</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    <?php foreach ($gallery_images as $index => $image_url): ?>
                                    <div class="relative group cursor-pointer" onclick="openImageViewer(<?php echo $index; ?>)">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="Property Image <?php echo $index + 1; ?>" class="w-full h-48 object-cover rounded-lg gallery-img">
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 rounded-lg transition-all duration-300 flex items-center justify-center">
                                            <i class="fas fa-search-plus text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="text-center py-12 bg-gray-50 rounded-lg">
                                <i class="fas fa-image text-gray-300 text-6xl mb-4"></i>
                                <p class="text-gray-500 text-lg">No images available for this property</p>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Video Section -->
                            <?php if ($video_url || $video_embed): ?>
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Property Video</h4>
                                <div class="bg-gray-100 rounded-lg p-6">
                                    <?php if ($video_embed): ?>
                                        <div class="aspect-video">
                                            <?php 
                                            // Allow iframe tags with specific attributes for video embeds
                                            $allowed_html = array(
                                                'iframe' => array(
                                                    'src' => array(),
                                                    'width' => array(),
                                                    'height' => array(),
                                                    'style' => array(),
                                                    'allowfullscreen' => array(),
                                                    'loading' => array(),
                                                    'referrerpolicy' => array(),
                                                    'frameborder' => array(),
                                                    'scrolling' => array()
                                                )
                                            );
                                            echo wp_kses($video_embed, $allowed_html); 
                                            ?>
                                        </div>
                                    <?php elseif ($video_url): ?>
                                        <div class="aspect-video">
                                            <?php
                                            // Convert YouTube/Vimeo URLs to embed format
                                            if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                                                $video_id = '';
                                                if (strpos($video_url, 'youtu.be') !== false) {
                                                    $video_id = substr($video_url, strpos($video_url, 'youtu.be/') + 9);
                                                    // Remove any query parameters
                                                    $video_id = strtok($video_id, '?');
                                                } elseif (strpos($video_url, 'youtube.com') !== false) {
                                                    parse_str(parse_url($video_url, PHP_URL_QUERY), $query);
                                                    $video_id = $query['v'] ?? '';
                                                }
                                                if ($video_id) {
                                                    echo '<iframe width="100%" height="100%" src="https://www.youtube.com/embed/' . esc_attr($video_id) . '?rel=0&modestbranding=1" frameborder="0" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>';
                                                } else {
                                                    echo '<div class="flex items-center justify-center h-full bg-gray-200 rounded-lg"><p class="text-gray-600">Invalid YouTube URL</p></div>';
                                                }
                                            } elseif (strpos($video_url, 'vimeo.com') !== false) {
                                                $video_id = substr($video_url, strrpos($video_url, '/') + 1);
                                                // Remove any query parameters
                                                $video_id = strtok($video_id, '?');
                                                if ($video_id) {
                                                    echo '<iframe width="100%" height="100%" src="https://player.vimeo.com/video/' . esc_attr($video_id) . '?title=0&byline=0&portrait=0" frameborder="0" allowfullscreen allow="autoplay; fullscreen; picture-in-picture"></iframe>';
                                                } else {
                                                    echo '<div class="flex items-center justify-center h-full bg-gray-200 rounded-lg"><p class="text-gray-600">Invalid Vimeo URL</p></div>';
                                                }
                                            } else {
                                                echo '<video controls class="w-full h-full rounded-lg"><source src="' . esc_url($video_url) . '" type="video/mp4">Your browser does not support the video tag.</video>';
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Virtual Tour -->
                            <?php if ($virtual_tour): ?>
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Virtual Tour</h4>
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-lg p-6">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h5 class="text-xl font-bold mb-2"><?php echo esc_html($virtual_tour_title ?: '3D Virtual Walkthrough'); ?></h5>
                                            <p class="text-emerald-100"><?php echo esc_html($virtual_tour_description ?: 'Experience this property from anywhere with our interactive 3D tour.'); ?></p>
                                        </div>
                                        <a href="<?php echo esc_url($virtual_tour); ?>" target="_blank" class="bg-white text-emerald-600 px-6 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                                            <i class="fas fa-play mr-2"></i><?php echo esc_html($virtual_tour_button_text ?: 'Start Tour'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Floor Plans -->
                            <?php if (!empty($floor_plans)): ?>
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold text-gray-800 mb-4">Floor Plans</h4>
                                <div class="bg-gray-100 rounded-lg p-6">
                                    <img src="<?php echo esc_url($floor_plans); ?>" alt="Floor Plan" class="w-full max-w-2xl mx-auto rounded-lg shadow-lg">
                                    <div class="mt-4 flex justify-center gap-4">
                                        <button onclick="downloadFloorPlan()" class="px-6 py-3 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition">
                                            <i class="fas fa-download mr-2"></i>Download Floor Plan
                                        </button>
                                        <button onclick="requestCustomPlan()" class="px-6 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition">
                                            <i class="fas fa-envelope mr-2"></i>Request Custom Plan
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                        </div>

                        <!-- Floor Plan Tab -->
                        <?php if (!empty($floor_plans)): ?>
                        <div id="floorplan-tab" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Floor Plan</h3>
                            <div class="bg-gray-100 rounded-lg p-8 text-center">
                                <img src="<?php echo esc_url($floor_plans); ?>" alt="Floor Plan" class="w-full max-w-3xl mx-auto rounded-lg shadow-lg">
                                <div class="mt-4 flex justify-center gap-4 no-print">
                                    <button onclick="downloadFloorPlan()" class="px-6 py-3 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition">
                                        <i class="fas fa-download mr-2"></i>Download Floor Plan
                                    </button>
                                    <button onclick="requestCustomPlan()" class="px-6 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition">
                                        <i class="fas fa-envelope mr-2"></i>Request Custom Plan
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Location Tab -->
                        <div id="location-tab" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Location & Nearby</h3>
                            

                            
                            <?php if ($map_iframe): ?>
                            <!-- Custom Map Iframe -->
                            <div class="rounded-lg mb-6 overflow-hidden" style="height: 400px; width: 100%;">
                                <?php 
                                // Allow iframe tags with specific attributes for maps
                                $allowed_html = array(
                                    'iframe' => array(
                                        'src' => array(),
                                        'width' => array(),
                                        'height' => array(),
                                        'style' => array(),
                                        'allowfullscreen' => array(),
                                        'loading' => array(),
                                        'referrerpolicy' => array(),
                                        'frameborder' => array(),
                                        'scrolling' => array()
                                    )
                                );
                                echo wp_kses($map_iframe, $allowed_html); 
                                ?>
                            </div>
                            <?php elseif ($full_address): ?>
                            <!-- Fallback: Show address and Google Maps link -->
                            <div class="bg-gray-100 rounded-lg p-6 mb-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-semibold text-gray-800 mb-2">Property Location</h4>
                                        <p class="text-gray-600 mb-3"><?php echo esc_html($full_address); ?></p>
                                        <a href="https://www.google.com/maps/search/<?php echo urlencode($full_address); ?>" 
                                           target="_blank" 
                                           class="inline-flex items-center px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition">
                                            <i class="fas fa-map-marker-alt mr-2"></i>
                                            View on Google Maps
                                        </a>
                                    </div>
                                    <i class="fas fa-map-marker-alt text-emerald-500 text-4xl"></i>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="bg-gray-100 rounded-lg p-8 text-center mb-6">
                                <i class="fas fa-map-marker-alt text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-600">Location information not available</p>
                                <p class="text-sm text-gray-500 mt-2">Please add a map iframe or address to display location</p>
                            </div>
                            <?php endif; ?>
                            
                            <div class="grid md:grid-cols-3 gap-4">
                                <?php if ($nearby_schools): ?>
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <i class="fas fa-graduation-cap text-blue-500 text-2xl mb-2"></i>
                                    <h4 class="font-semibold text-gray-800 mb-1">Schools</h4>
                                    <p class="text-sm text-gray-600"><?php echo esc_html($nearby_schools); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($nearby_shopping): ?>
                                <div class="p-4 bg-green-50 rounded-lg">
                                    <i class="fas fa-shopping-cart text-green-500 text-2xl mb-2"></i>
                                    <h4 class="font-semibold text-gray-800 mb-1">Shopping</h4>
                                    <p class="text-sm text-gray-600"><?php echo esc_html($nearby_shopping); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($nearby_restaurants): ?>
                                <div class="p-4 bg-purple-50 rounded-lg">
                                    <i class="fas fa-utensils text-purple-500 text-2xl mb-2"></i>
                                    <h4 class="font-semibold text-gray-800 mb-1">Restaurants</h4>
                                    <p class="text-sm text-gray-600"><?php echo esc_html($nearby_restaurants); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!$nearby_schools && !$nearby_shopping && !$nearby_restaurants): ?>
                                <div class="col-span-3 p-8 text-center bg-gray-50 rounded-lg">
                                    <i class="fas fa-map-marker-alt text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-600">No nearby features configured</p>
                                    <p class="text-sm text-gray-500 mt-2">Add nearby schools, shopping, and restaurants in the Location section</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Reviews Tab -->
                        <div id="reviews-tab" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Property Reviews & Ratings</h3>
                            
                            <!-- Overall Rating -->
                            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 rounded-lg p-6 mb-6">
                                <div class="flex flex-col md:flex-row items-center justify-between">
                                    <div class="text-center md:text-left mb-4 md:mb-0">
                                        <div class="text-5xl font-bold text-gray-800 mb-2">4.8</div>
                                        <div class="flex items-center justify-center md:justify-start mb-2">
                                            <i class="fas fa-star rating-star"></i>
                                            <i class="fas fa-star rating-star"></i>
                                            <i class="fas fa-star rating-star"></i>
                                            <i class="fas fa-star rating-star"></i>
                                            <i class="fas fa-star-half-alt rating-star"></i>
                                        </div>
                                        <p class="text-gray-600">Based on reviews</p>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center py-8">
                                <i class="fas fa-comments text-gray-300 text-4xl mb-4"></i>
                                <p class="text-gray-500">No reviews yet. Be the first to review this property!</p>
                            </div>
                        </div>

                        <!-- Booking Tab -->
                        <div id="booking-tab" class="tab-content hidden">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Schedule a Viewing</h3>
                            
                            <div class="grid md:grid-cols-2 gap-8">
                                <!-- Booking Form -->
                                <div class="bg-white border rounded-lg p-6">
                                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Book a Property Tour</h4>
                                    <form onsubmit="submitBookingForm(event)" class="space-y-4">
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                                <input type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Enter your first name">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                                <input type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Enter your last name">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                            <input type="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="your.email@example.com">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                            <input type="tel" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="(555) 123-4567">
                                        </div>
                                        <div class="grid md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Date</label>
                                                <input type="date" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-2">Preferred Time</label>
                                                <select required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                                    <option value="">Select Time</option>
                                                    <option value="09:00">9:00 AM</option>
                                                    <option value="10:00">10:00 AM</option>
                                                    <option value="11:00">11:00 AM</option>
                                                    <option value="12:00">12:00 PM</option>
                                                    <option value="13:00">1:00 PM</option>
                                                    <option value="14:00">2:00 PM</option>
                                                    <option value="15:00">3:00 PM</option>
                                                    <option value="16:00">4:00 PM</option>
                                                    <option value="17:00">5:00 PM</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Message (Optional)</label>
                                            <textarea rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Any specific questions or requirements..."></textarea>
                                        </div>
                                        <button type="submit" class="w-full bg-emerald-500 text-white py-3 rounded-lg hover:bg-emerald-600 transition font-semibold">
                                            <i class="fas fa-calendar-check mr-2"></i>Schedule Tour
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Booking Info -->
                                <div class="space-y-6">
                                    <!-- Agent Contact -->
                                    <div class="bg-emerald-50 rounded-lg p-6">
                                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Contact Agent</h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <i class="fas fa-user text-emerald-500 mr-3"></i>
                                                <span class="text-gray-700"><?php echo esc_html($agent_name ?: 'Property Agent'); ?></span>
                                            </div>
                                            <?php if ($agent_phone): ?>
                                            <div class="flex items-center">
                                                <i class="fas fa-phone text-emerald-500 mr-3"></i>
                                                <a href="tel:<?php echo esc_attr($agent_phone); ?>" class="text-emerald-600 hover:text-emerald-700"><?php echo esc_html($agent_phone); ?></a>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($agent_email): ?>
                                            <div class="flex items-center">
                                                <i class="fas fa-envelope text-emerald-500 mr-3"></i>
                                                <a href="mailto:<?php echo esc_attr($agent_email); ?>" class="text-emerald-600 hover:text-emerald-700"><?php echo esc_html($agent_email); ?></a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Tour Information -->
                                    <div class="bg-blue-50 rounded-lg p-6">
                                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Tour Information</h4>
                                        <div class="space-y-3 text-sm">
                                            <div class="flex items-start">
                                                <i class="fas fa-clock text-blue-500 mr-3 mt-1"></i>
                                                <div>
                                                    <p class="font-semibold text-gray-800">Duration</p>
                                                    <p class="text-gray-600"><?php echo esc_html($tour_duration ?: 'Approximately 30-45 minutes'); ?></p>
                                                </div>
                                            </div>
                                            <div class="flex items-start">
                                                <i class="fas fa-users text-blue-500 mr-3 mt-1"></i>
                                                <div>
                                                    <p class="font-semibold text-gray-800">Group Size</p>
                                                    <p class="text-gray-600"><?php echo esc_html($tour_group_size ?: 'Maximum 4 people per tour'); ?></p>
                                                </div>
                                            </div>
                                            <div class="flex items-start">
                                                <i class="fas fa-shield-alt text-blue-500 mr-3 mt-1"></i>
                                                <div>
                                                    <p class="font-semibold text-gray-800">Safety</p>
                                                    <p class="text-gray-600"><?php echo esc_html($tour_safety ?: 'All safety protocols followed'); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Quick Actions -->
                                    <div class="bg-gray-50 rounded-lg p-6">
                                        <h4 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h4>
                                        <div class="space-y-3">
                                            <?php
                                            // Get dynamic quick actions from settings
                                            $quick_actions = get_option('resbs_quick_actions', array());
                                            
                                            // Default actions if none configured
                                            if (empty($quick_actions)) {
                                                $quick_actions = array(
                                                    array(
                                                        'title' => 'Send Message',
                                                        'icon' => 'fas fa-envelope',
                                                        'action' => 'openContactModal()',
                                                        'style' => 'bg-gray-700 text-white hover:bg-gray-800',
                                                        'enabled' => true
                                                    ),
                                                    array(
                                                        'title' => 'Share Property',
                                                        'icon' => 'fas fa-share-alt',
                                                        'action' => 'shareProperty()',
                                                        'style' => 'border-2 border-emerald-500 text-emerald-500 hover:bg-emerald-50',
                                                        'enabled' => true
                                                    )
                                                );
                                            }
                                            
                                            // Display configured actions
                                            foreach ($quick_actions as $action) {
                                                if (isset($action['enabled']) && $action['enabled']) {
                                                    $icon = isset($action['icon']) ? $action['icon'] : 'fas fa-circle';
                                                    $title = isset($action['title']) ? $action['title'] : 'Action';
                                                    $onclick = isset($action['action']) ? $action['action'] : '';
                                                    $style = isset($action['style']) ? $action['style'] : 'bg-gray-700 text-white hover:bg-gray-800';
                                                    
                                                    echo '<button onclick="' . esc_attr($onclick) . '" class="w-full flex items-center justify-center px-4 py-3 ' . esc_attr($style) . ' rounded-lg transition">';
                                                    echo '<i class="' . esc_attr($icon) . ' mr-2"></i>' . esc_html($title);
                                                    echo '</button>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Similar Properties -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Similar Properties</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <?php
                        // Get similar properties using the helper function
                        $similar_properties = resbs_get_similar_properties($property_id, 4);
                        
                        // Debug information (remove in production)
                        if (current_user_can('manage_options')) {
                            echo '<!-- Debug: Found ' . count($similar_properties) . ' similar properties -->';
                            echo '<!-- Debug: Current property status: ' . $property_status . ' -->';
                            echo '<!-- Debug: Current property type: ' . $property_type . ' -->';
                            echo '<!-- Debug: Current property city: ' . $city . ' -->';
                            echo '<!-- Debug: Current property price: ' . $price . ' -->';
                            foreach ($similar_properties as $debug_prop) {
                                echo '<!-- Debug Property: ' . $debug_prop->post_title . ' (ID: ' . $debug_prop->ID . ') -->';
                                echo '<!-- Debug - Beds: ' . esc_html(get_post_meta($debug_prop->ID, '_property_bedrooms', true)) . ' -->';
                                echo '<!-- Debug - Baths: ' . esc_html(get_post_meta($debug_prop->ID, '_property_bathrooms', true)) . ' -->';
                                echo '<!-- Debug - Area: ' . esc_html(get_post_meta($debug_prop->ID, '_property_area_sqft', true)) . ' -->';
                                $debug_gallery = get_post_meta($debug_prop->ID, '_property_gallery', true);
                                $debug_gallery_str = is_array($debug_gallery) ? implode(',', $debug_gallery) : $debug_gallery;
                                echo '<!-- Debug - Gallery: ' . esc_html($debug_gallery_str) . ' -->';
                            }
                        }
                        
                        if ($similar_properties):
                            foreach ($similar_properties as $similar):
                                $similar_price = get_post_meta($similar->ID, '_property_price', true);
                                $similar_bedrooms = get_post_meta($similar->ID, '_property_bedrooms', true);
                                $similar_bathrooms = get_post_meta($similar->ID, '_property_bathrooms', true);
                                $similar_area = get_post_meta($similar->ID, '_property_area_sqft', true);
                                $similar_gallery = get_post_meta($similar->ID, '_property_gallery', true);
                                
                                // Safely handle gallery data - could be array, string, or empty
                                $similar_images = array();
                                if (is_array($similar_gallery)) {
                                    $similar_images = array_filter($similar_gallery);
                                } elseif (is_string($similar_gallery) && !empty($similar_gallery)) {
                                    $similar_images = array_filter(explode(',', $similar_gallery));
                                }
                                
                                // Get the first image URL properly
                                $similar_image = 'https://images.unsplash.com/photo-1600607687644-c7171b42498f?w=600'; // Default fallback
                                
                                // Try gallery images first
                                if (!empty($similar_images) && is_array($similar_images)) {
                                    $first_image = $similar_images[0];
                                    if (is_numeric($first_image)) {
                                        $first_image_id = intval($first_image);
                                        if ($first_image_id > 0) {
                                            $image_url = wp_get_attachment_image_url($first_image_id, 'medium');
                                            if ($image_url) {
                                                $similar_image = $image_url;
                                            }
                                        }
                                    } elseif (is_string($first_image) && filter_var($first_image, FILTER_VALIDATE_URL)) {
                                        $similar_image = $first_image;
                                    }
                                }
                                
                                // Fallback to featured image if no gallery
                                if ($similar_image === 'https://images.unsplash.com/photo-1600607687644-c7171b42498f?w=600') {
                                    $featured_image_id = get_post_thumbnail_id($similar->ID);
                                    if ($featured_image_id) {
                                        $featured_image_url = wp_get_attachment_image_url($featured_image_id, 'medium');
                                        if ($featured_image_url) {
                                            $similar_image = $featured_image_url;
                                        }
                                    }
                                }
                                
                                // Safely format price with fallback
                                $similar_price_formatted = 'Price on Request';
                                if (!empty($similar_price) && is_numeric($similar_price)) {
                                    $similar_price_formatted = '$' . number_format(floatval($similar_price));
                                }
                        ?>
                        <a href="<?php echo get_permalink($similar->ID); ?>" class="property-card border rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                            <div class="relative">
                                <img src="<?php echo esc_url($similar_image); ?>" alt="Property" class="w-full h-48 object-cover">
                                <span class="absolute top-3 left-3 bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-semibold"><?php echo esc_html(get_post_meta($similar->ID, '_property_status', true) ?: 'For Sale'); ?></span>
                            </div>
                            <div class="p-4">
                                <h4 class="font-bold text-lg text-gray-800 mb-2 hover:text-emerald-600 transition-colors"><?php echo esc_html($similar->post_title); ?></h4>
                                <p class="text-gray-600 text-sm mb-3 flex items-center">
                                    <i class="fas fa-map-marker-alt text-emerald-500 mr-2"></i>
                                    <?php echo esc_html(get_post_meta($similar->ID, '_property_address', true)); ?>
                                </p>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl font-bold text-emerald-500"><?php echo esc_html($similar_price_formatted); ?></span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600 border-t pt-3">
                                    <span class="flex items-center"><i class="fas fa-bed mr-1 text-emerald-500"></i><?php echo esc_html($similar_bedrooms ?: '0'); ?> Beds</span>
                                    <span class="flex items-center"><i class="fas fa-bath mr-1 text-emerald-500"></i><?php echo esc_html($similar_bathrooms ?: '0'); ?> Bath</span>
                                    <span class="flex items-center"><i class="fas fa-ruler-combined mr-1 text-emerald-500"></i><?php echo esc_html($similar_area ?: '0'); ?> sqft</span>
                                </div>
                            </div>
                        </a>
                        <?php
                            endforeach;
                        else:
                        ?>
                        <div class="col-span-4 text-center py-8">
                            <i class="fas fa-home text-gray-300 text-4xl mb-4"></i>
                            <p class="text-gray-500">No similar properties found</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Agent Card -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6  top-24">
                    <div class="text-center mb-6">
                        <?php if ($agent_photo): ?>
                        <img src="<?php echo esc_url($agent_photo); ?>" alt="Agent" class="w-24 h-24 rounded-full mx-auto mb-3 border-4 border-emerald-100">
                        <?php else: ?>
                        <div class="w-24 h-24 rounded-full mx-auto mb-3 border-4 border-emerald-100 bg-gray-200 flex items-center justify-center">
                            <i class="fas fa-user text-gray-400 text-2xl"></i>
                        </div>
                        <?php endif; ?>
                        <h3 class="text-xl font-bold text-gray-800"><?php echo esc_html($agent_name ?: 'Property Agent'); ?></h3>
                        <p class="text-gray-600 text-sm">Real Estate Agent</p>
                        <div class="flex items-center justify-center mt-2">
                            <?php 
                            $rating = intval($agent_rating ?: 5);
                            for ($i = 1; $i <= 5; $i++): 
                            ?>
                                <i class="fas fa-star rating-star text-sm <?php echo $i <= $rating ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                            <?php endfor; ?>
                            <span class="text-sm text-gray-600 ml-2">(<?php echo esc_html($agent_reviews ?: 'reviews'); ?>)</span>
                        </div>
                    </div>

                    <div class="space-y-3 mb-6">
                        <?php if ($agent_phone): ?>
                        <a href="tel:<?php echo esc_attr($agent_phone); ?>" class="flex items-center justify-center px-4 py-3 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition">
                            <i class="fas fa-phone mr-2"></i>
                            <span>Call Agent</span>
                        </a>
                        <?php endif; ?>
                        <button onclick="openContactModal()" class="w-full flex items-center justify-center px-4 py-3 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition">
                            <i class="fas fa-envelope mr-2"></i>
                            <span><?php echo esc_html($agent_send_message_text ?: 'Send Message'); ?></span>
                        </button>
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-gray-600">Properties Sold:</span>
                            <span class="font-semibold text-gray-800"><?php echo esc_html($agent_properties_sold ?: '100+'); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-gray-600">Experience:</span>
                            <span class="font-semibold text-gray-800"><?php echo esc_html($agent_experience ?: '5+ Years'); ?></span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Response Time:</span>
                            <span class="font-semibold text-gray-800"><?php echo esc_html($agent_response_time ?: '< 1 Hour'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Mortgage Calculator -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4"><?php echo esc_html($mortgage_calculator_title ?: 'Mortgage Calculator'); ?></h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($mortgage_property_price_label ?: 'Property Price'); ?></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input type="number" id="propertyPrice" value="<?php echo esc_attr($price ?: '500000'); ?>" class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="500000" onkeyup="
                                    console.log('Property price changed to:', this.value);
                                    
                                    // Get all values
                                    var price = this.value;
                                    var downPayment = document.getElementById('downPayment').value;
                                    var interestRate = document.getElementById('interestRate').value;
                                    var loanTerm = document.getElementById('loanTerm').value;
                                    
                                    // Convert to numbers
                                    price = parseFloat(price) || 0;
                                    downPayment = parseFloat(downPayment) || 0;
                                    interestRate = parseFloat(interestRate) || 0;
                                    loanTerm = parseFloat(loanTerm) || 30;
                                    
                                    // Handle very low property prices
                                    if (price < 1000) {
                                        console.log('Property price too low for realistic calculation:', price);
                                        document.getElementById('monthlyPayment').textContent = '$0';
                                        return;
                                    }
                                    
                                    // Calculate
                                    var downPaymentAmount = (price * downPayment) / 100;
                                    var loanAmount = price - downPaymentAmount;
                                    var monthlyRate = interestRate / 100 / 12;
                                    var numberOfPayments = loanTerm * 12;
                                    
                                    var monthlyPayment = 0;
                                    if (monthlyRate > 0 && loanAmount > 0) {
                                        monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
                                    } else if (loanAmount > 0) {
                                        monthlyPayment = loanAmount / numberOfPayments;
                                    }
                                    
                                    console.log('Auto calculation result:', monthlyPayment);
                                    
                                    // Display result
                                    document.getElementById('monthlyPayment').textContent = '$' + Math.round(monthlyPayment).toLocaleString();
                                " onchange="
                                    console.log('Property price changed to:', this.value);
                                    
                                    // Get all values
                                    var price = this.value;
                                    var downPayment = document.getElementById('downPayment').value;
                                    var interestRate = document.getElementById('interestRate').value;
                                    var loanTerm = document.getElementById('loanTerm').value;
                                    
                                    // Convert to numbers
                                    price = parseFloat(price) || 0;
                                    downPayment = parseFloat(downPayment) || 0;
                                    interestRate = parseFloat(interestRate) || 0;
                                    loanTerm = parseFloat(loanTerm) || 30;
                                    
                                    // Handle very low property prices
                                    if (price < 1000) {
                                        console.log('Property price too low for realistic calculation:', price);
                                        document.getElementById('monthlyPayment').textContent = '$0';
                                        return;
                                    }
                                    
                                    // Calculate
                                    var downPaymentAmount = (price * downPayment) / 100;
                                    var loanAmount = price - downPaymentAmount;
                                    var monthlyRate = interestRate / 100 / 12;
                                    var numberOfPayments = loanTerm * 12;
                                    
                                    var monthlyPayment = 0;
                                    if (monthlyRate > 0 && loanAmount > 0) {
                                        monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
                                    } else if (loanAmount > 0) {
                                        monthlyPayment = loanAmount / numberOfPayments;
                                    }
                                    
                                    console.log('Auto calculation result:', monthlyPayment);
                                    
                                    // Display result
                                    document.getElementById('monthlyPayment').textContent = '$' + Math.round(monthlyPayment).toLocaleString();
                                " placeholder="500000" min="0" step="1000">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($mortgage_down_payment_label ?: 'Down Payment (%)'); ?></label>
                            <input type="range" id="downPayment" min="0" max="100" value="<?php echo esc_attr($mortgage_default_down_payment ?: '20'); ?>" class="w-full" oninput="document.getElementById('downPaymentValue').textContent = this.value + '%'; calculateMortgageNow();">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>0%</span>
                                <span id="downPaymentValue" class="font-semibold text-emerald-600"><?php echo esc_html($mortgage_default_down_payment ?: '20'); ?>%</span>
                                <span>100%</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($mortgage_interest_rate_label ?: 'Interest Rate (%)'); ?></label>
                            <input type="number" id="interestRate" value="<?php echo esc_attr($mortgage_default_interest_rate ?: '6.5'); ?>" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="6.5" onkeyup="calculateMortgageNow()" onchange="calculateMortgageNow()">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($mortgage_loan_term_label ?: 'Loan Term (Years)'); ?></label>
                            <select id="loanTerm" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" onchange="calculateMortgageNow()">
                                <?php 
                                $loan_terms_array = explode("\n", $mortgage_loan_terms ?: "15\n20\n30");
                                foreach ($loan_terms_array as $term) {
                                    $term = trim($term);
                                    if (!empty($term)) {
                                        $selected = ($term == ($mortgage_default_loan_term ?: '30')) ? 'selected' : '';
                                        echo '<option value="' . esc_attr($term) . '" ' . $selected . '>' . esc_html($term) . ' Years</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-4 mt-4">
                            <p class="text-sm text-gray-600 mb-1"><?php echo esc_html($mortgage_monthly_payment_label ?: 'Estimated Monthly Payment'); ?></p>
                            <p class="text-3xl font-bold text-emerald-600" id="monthlyPayment">$0</p>
                            <p class="text-xs text-gray-500 mt-2"><?php echo esc_html($mortgage_disclaimer_text ?: '*Principal & Interest only'); ?></p>
                        </div>
                        
                        <!-- Calculate Button -->
                        <div class="mt-4">
                            <button type="button" onclick="
                                console.log('Button clicked!');
                                
                                // Get values
                                var price = document.getElementById('propertyPrice').value;
                                var downPayment = document.getElementById('downPayment').value;
                                var interestRate = document.getElementById('interestRate').value;
                                var loanTerm = document.getElementById('loanTerm').value;
                                
                                console.log('Input values:', {price: price, downPayment: downPayment, interestRate: interestRate, loanTerm: loanTerm});
                                
                                // Convert to numbers
                                price = parseFloat(price) || 0;
                                downPayment = parseFloat(downPayment) || 0;
                                interestRate = parseFloat(interestRate) || 0;
                                loanTerm = parseFloat(loanTerm) || 30;
                                
                                // Handle very low property prices
                                if (price < 1000) {
                                    console.log('Property price too low for realistic calculation:', price);
                                    document.getElementById('monthlyPayment').textContent = '$0';
                                    return;
                                }
                                
                                // Calculate
                                var downPaymentAmount = (price * downPayment) / 100;
                                var loanAmount = price - downPaymentAmount;
                                var monthlyRate = interestRate / 100 / 12;
                                var numberOfPayments = loanTerm * 12;
                                
                                var monthlyPayment = 0;
                                if (monthlyRate > 0 && loanAmount > 0) {
                                    monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
                                } else if (loanAmount > 0) {
                                    monthlyPayment = loanAmount / numberOfPayments;
                                }
                                
                                console.log('Calculation result:', monthlyPayment);
                                
                                // Display result
                                document.getElementById('monthlyPayment').textContent = '$' + Math.round(monthlyPayment).toLocaleString();
                                console.log('Display updated to:', document.getElementById('monthlyPayment').textContent);
                            " class="w-full bg-emerald-500 text-white py-3 px-4 rounded-lg hover:bg-emerald-600 transition font-semibold">
                                <i class="fas fa-calculator mr-2"></i>Calculate Mortgage
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white mt-16 no-print">
        <div class="container mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-home text-white text-xl"></i>
                        </div>
                        <span class="text-2xl font-bold"><?php echo get_bloginfo('name'); ?></span>
                    </div>
                    <p class="text-gray-400 text-sm"><?php echo get_bloginfo('description'); ?></p>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="<?php echo home_url('/properties'); ?>" class="hover:text-emerald-400">Properties</a></li>
                        <li><a href="<?php echo home_url('/agents'); ?>" class="hover:text-emerald-400">Agents</a></li>
                        <li><a href="<?php echo home_url('/about'); ?>" class="hover:text-emerald-400">About Us</a></li>
                        <li><a href="<?php echo home_url('/contact'); ?>" class="hover:text-emerald-400">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Services</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="<?php echo home_url('/buy'); ?>" class="hover:text-emerald-400">Buy Property</a></li>
                        <li><a href="<?php echo home_url('/sell'); ?>" class="hover:text-emerald-400">Sell Property</a></li>
                        <li><a href="<?php echo home_url('/rent'); ?>" class="hover:text-emerald-400">Rent Property</a></li>
                        <li><a href="<?php echo home_url('/management'); ?>" class="hover:text-emerald-400">Property Management</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold mb-4">Contact Info</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center"><i class="fas fa-phone mr-2 text-emerald-400"></i><?php echo get_option('phone', '+1 (555) 123-4567'); ?></li>
                        <li class="flex items-center"><i class="fas fa-envelope mr-2 text-emerald-400"></i><?php echo get_option('admin_email'); ?></li>
                        <li class="flex items-center"><i class="fas fa-map-marker-alt mr-2 text-emerald-400"></i><?php echo get_option('address', '123 Main St, LA, CA'); ?></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> <?php echo get_bloginfo('name'); ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Image Viewer Modal -->
    <div id="imageViewer" class="image-viewer">
        <button onclick="closeImageViewer()" class="absolute top-4 right-4 text-white text-3xl z-10 hover:text-emerald-400">
            <i class="fas fa-times"></i>
        </button>
        <button onclick="prevImage()" class="absolute left-4 text-white text-3xl z-10 hover:text-emerald-400">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button onclick="nextImage()" class="absolute right-4 text-white text-3xl z-10 hover:text-emerald-400">
            <i class="fas fa-chevron-right"></i>
        </button>
        <img id="viewerImage" src="" alt="Property" class="max-w-full max-h-full">
    </div>

    <!-- Contact Modal -->
    <div id="contactModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-2xl font-bold text-gray-800"><?php echo esc_html($contact_form_title ?: 'Contact Agent'); ?></h3>
                <button onclick="closeContactModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form onsubmit="submitContactForm(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_name_label ?: 'Your Name'); ?></label>
                    <input type="text" name="contact_name" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Enter your full name">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_email_label ?: 'Email'); ?></label>
                    <input type="email" name="contact_email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="your.email@example.com">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_phone_label ?: 'Phone'); ?></label>
                    <input type="tel" name="contact_phone" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="(555) 123-4567">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_message_label ?: 'Message'); ?></label>
                    <textarea name="contact_message" rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" placeholder="Tell us about your interest in this property..."></textarea>
                </div>
                <button type="submit" class="w-full bg-emerald-500 text-white py-3 rounded-lg hover:bg-emerald-600 transition font-semibold">
                    <?php echo esc_html($contact_submit_text ?: 'Send Message'); ?>
                </button>
            </form>
        </div>
    </div>

    <script>
        // Pass PHP variables to JavaScript
        window.galleryImages = <?php echo json_encode($gallery_images); ?>;
        window.propertyTitle = <?php echo esc_js($property_title); ?>;
        window.propertyAddress = <?php echo esc_js($full_address); ?>;
        window.videoUrl = <?php echo esc_js($video_url ?: ''); ?>;
        window.virtualTour = <?php echo esc_js($virtual_tour ?: ''); ?>;
        window.floorPlans = <?php echo esc_js($floor_plans ?: ''); ?>;
        window.contactSuccessMessage = <?php echo esc_js($contact_success_message); ?>;
        window.mortgageDefaultDownPayment = <?php echo esc_js($mortgage_default_down_payment); ?>;
        window.mortgageDefaultInterestRate = <?php echo esc_js($mortgage_default_interest_rate); ?>;
        window.mortgageDefaultLoanTerm = <?php echo esc_js($mortgage_default_loan_term); ?>;
        
        // Debug mortgage calculator variables
        console.log('PHP Mortgage Variables:', {
            title: <?php echo esc_js($mortgage_calculator_title); ?>,
            propertyPriceLabel: <?php echo esc_js($mortgage_property_price_label); ?>,
            downPaymentLabel: <?php echo esc_js($mortgage_down_payment_label); ?>,
            interestRateLabel: <?php echo esc_js($mortgage_interest_rate_label); ?>,
            loanTermLabel: <?php echo esc_js($mortgage_loan_term_label); ?>,
            monthlyPaymentLabel: <?php echo esc_js($mortgage_monthly_payment_label); ?>,
            defaultDownPayment: <?php echo esc_js($mortgage_default_down_payment); ?>,
            defaultInterestRate: <?php echo esc_js($mortgage_default_interest_rate); ?>,
            defaultLoanTerm: <?php echo esc_js($mortgage_default_loan_term); ?>,
            loanTerms: <?php echo esc_js($mortgage_loan_terms); ?>,
            disclaimerText: <?php echo esc_js($mortgage_disclaimer_text); ?>
        });
        
        // Enhanced Media Actions Functions
        function downloadAllImages() {
            if (window.galleryImages && window.galleryImages.length > 0) {
                // Create a zip file with all images
                const zip = new JSZip();
                let loadedImages = 0;
                
                window.galleryImages.forEach((imageUrl, index) => {
                    fetch(imageUrl)
                        .then(response => response.blob())
                        .then(blob => {
                            const fileName = `property_image_${index + 1}.jpg`;
                            zip.file(fileName, blob);
                            loadedImages++;
                            
                            if (loadedImages === window.galleryImages.length) {
                                zip.generateAsync({type: "blob"}).then(function(content) {
                                    const link = document.createElement('a');
                                    link.href = URL.createObjectURL(content);
                                    link.download = `${window.propertyTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_images.zip`;
                                    link.click();
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error downloading image:', error);
                            // Fallback: open image in new tab
                            window.open(imageUrl, '_blank');
                        });
                });
            } else {
                alert('No images available for download.');
            }
        }
        
        function downloadVideo() {
            if (window.videoUrl) {
                const link = document.createElement('a');
                link.href = window.videoUrl;
                link.download = `${window.propertyTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_video.mp4`;
                link.click();
            } else {
                alert('No video available for download.');
            }
        }
        
        function downloadFloorPlan() {
            if (window.floorPlans) {
                const link = document.createElement('a');
                link.href = window.floorPlans;
                link.download = `${window.propertyTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_floor_plan.jpg`;
                link.click();
            } else {
                alert('No floor plan available for download.');
            }
        }
        
        function shareMedia() {
            const shareData = {
                title: window.propertyTitle,
                text: `Check out this property: ${window.propertyTitle}`,
                url: window.location.href
            };
            
            if (navigator.share) {
                navigator.share(shareData);
            } else {
                // Fallback: copy to clipboard
                navigator.clipboard.writeText(shareData.url).then(() => {
                    alert('Property link copied to clipboard!');
                });
            }
        }
        
        function shareVirtualTour() {
            if (window.virtualTour) {
                const shareData = {
                    title: `${window.propertyTitle} - Virtual Tour`,
                    text: `Take a virtual tour of this property: ${window.propertyTitle}`,
                    url: window.virtualTour
                };
                
                if (navigator.share) {
                    navigator.share(shareData);
                } else {
                    navigator.clipboard.writeText(window.virtualTour).then(() => {
                        alert('Virtual tour link copied to clipboard!');
                    });
                }
            } else {
                alert('No virtual tour available.');
            }
        }
        
        function printMedia() {
            // Create a print-friendly version of the media
            const printWindow = window.open('', '_blank');
            const mediaContent = `
                <html>
                <head>
                    <title>${window.propertyTitle} - Media</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .media-section { margin-bottom: 20px; }
                        .image-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
                        .image-item { text-align: center; }
                        .image-item img { max-width: 100%; height: auto; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>${window.propertyTitle}</h1>
                        <p>${window.propertyAddress}</p>
                    </div>
                    ${window.galleryImages.map((img, index) => 
                        `<div class="image-item">
                            <img src="${img}" alt="Property Image ${index + 1}">
                            <p>Image ${index + 1}</p>
                        </div>`
                    ).join('')}
                </body>
                </html>
            `;
            
            printWindow.document.write(mediaContent);
            printWindow.document.close();
            printWindow.print();
        }
        
        // Existing functions (keeping for compatibility)
        function shareProperty() {
            shareMedia();
        }
        
        function saveFavorite() {
            // Add to favorites functionality
            const favorites = JSON.parse(localStorage.getItem('property_favorites') || '[]');
            const propertyId = <?php echo $property_id; ?>;
            
            if (!favorites.includes(propertyId)) {
                favorites.push(propertyId);
                localStorage.setItem('property_favorites', JSON.stringify(favorites));
                alert('Property added to favorites!');
            } else {
                alert('Property is already in your favorites!');
            }
        }
        
        function printPage() {
            window.print();
        }
        
        function exportPDF() {
            // Enhanced PDF export with media
            const printContent = document.querySelector('.property-details');
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>${window.propertyTitle}</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .no-print { display: none; }
                    </style>
                </head>
                <body>
                    ${printContent.outerHTML}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
        
        // Modal Functions
        function openContactModal() {
            document.getElementById('contactModal').classList.remove('hidden');
            document.getElementById('contactModal').classList.add('flex');
        }
        
        function closeContactModal() {
            document.getElementById('contactModal').classList.add('hidden');
            document.getElementById('contactModal').classList.remove('flex');
        }
        
        // Form Submission Functions
        function submitContactForm(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            // Add property ID and nonce
            formData.append('property_id', <?php echo $property_id; ?>);
            formData.append('action', 'submit_contact_message');
            formData.append('nonce', '<?php echo wp_create_nonce('contact_message_nonce'); ?>');
            
            // Show loading state
            const submitButton = event.target.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
            submitButton.disabled = true;
            
            // Submit via AJAX
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Use dynamic success message
                    let successMessage = data.data.message || window.contactSuccessMessage || 'Thank you! Your message has been sent to the agent.';
                    alert(successMessage);
                    closeContactModal();
                    event.target.reset(); // Reset form
                } else {
                    alert('Error: ' + (data.data || 'Failed to send message'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: Failed to send message. Please try again.');
            })
            .finally(() => {
                // Reset button state
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            });
        }
        
        // Main mortgage calculation function
        function calculateMortgageNow() {
            console.log('calculateMortgageNow() called');
            try {
                // Get all input values
                var price = document.getElementById('propertyPrice').value;
                var downPayment = document.getElementById('downPayment').value;
                var interestRate = document.getElementById('interestRate').value;
                var loanTerm = document.getElementById('loanTerm').value;
                
                console.log('Raw values:', {
                    price: document.getElementById('propertyPrice').value,
                    downPayment: downPayment,
                    interestRate: interestRate,
                    loanTerm: loanTerm
                });
                
                // Convert to numbers
                price = parseFloat(price) || 0;
                downPayment = parseFloat(downPayment) || 0;
                interestRate = parseFloat(interestRate) || 0;
                loanTerm = parseFloat(loanTerm) || 30;
                
                console.log('Parsed values:', {
                    price: price,
                    downPayment: downPayment,
                    interestRate: interestRate,
                    loanTerm: loanTerm
                });
                
                // If property price is too low, use default
                if (price < 1000) {
                    price = 500000;
                    document.getElementById('propertyPrice').value = '$500,000';
                    console.log('Property price too low, using default: $500,000');
                }
                
                console.log('Using these values for calculation:', {
                    price: price,
                    downPayment: downPayment,
                    interestRate: interestRate,
                    loanTerm: loanTerm
                });
                
                // Calculate mortgage
                var downPaymentAmount = (price * downPayment) / 100;
                var loanAmount = price - downPaymentAmount;
                var monthlyRate = interestRate / 100 / 12;
                var numberOfPayments = loanTerm * 12;
                
                console.log('Calculation values:', {
                    downPaymentAmount: downPaymentAmount,
                    loanAmount: loanAmount,
                    monthlyRate: monthlyRate,
                    numberOfPayments: numberOfPayments
                });
                
                var monthlyPayment = 0;
                
                // Check if we have valid values
                if (loanAmount <= 0) {
                    console.log('Invalid loan amount:', loanAmount);
                    monthlyPayment = 0;
                } else if (monthlyRate > 0 && loanAmount > 0) {
                    monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
                    console.log('Using interest rate formula');
                } else if (loanAmount > 0) {
                    monthlyPayment = loanAmount / numberOfPayments;
                    console.log('Using simple division formula');
                }
                
                // Ensure minimum payment display
                if (monthlyPayment < 1 && loanAmount > 0) {
                    monthlyPayment = loanAmount / numberOfPayments;
                    console.log('Payment too small, using simple division');
                }
                
                console.log('Final monthly payment:', monthlyPayment);
                
                // Display result
                var formattedPayment = '$' + Math.round(monthlyPayment).toLocaleString();
                document.getElementById('monthlyPayment').textContent = formattedPayment;
                console.log('Display updated to:', formattedPayment);
                
                // Force update to make sure it changes
                setTimeout(function() {
                    document.getElementById('monthlyPayment').textContent = formattedPayment;
                    console.log('Forced update to:', formattedPayment);
                }, 50);
                
                console.log('Mortgage calculated:', {
                    price: price,
                    downPayment: downPayment,
                    interestRate: interestRate,
                    loanTerm: loanTerm,
                    monthlyPayment: monthlyPayment
                });
                
            } catch (error) {
                console.error('Error calculating mortgage:', error);
                document.getElementById('monthlyPayment').textContent = 'ERROR';
            }
        }
        
        // Mortgage Calculator Functions
        function calculateMortgage() {
            console.log('calculateMortgage() called');
            
            try {
                // Get elements
                const propertyPriceElement = document.getElementById('propertyPrice');
                const downPaymentElement = document.getElementById('downPayment');
                const interestRateElement = document.getElementById('interestRate');
                const loanTermElement = document.getElementById('loanTerm');
                const monthlyPaymentElement = document.getElementById('monthlyPayment');
                
                if (!monthlyPaymentElement) {
                    console.error('Monthly payment element not found');
                    return;
                }
                
                // Simple calculation for testing
                const propertyPrice = 500000; // Default price
                const downPaymentPercent = 20; // Default 20%
                const interestRate = 6.5; // Default 6.5%
                const loanTerm = 30; // Default 30 years
                
                // Calculate monthly payment
                const downPaymentAmount = (propertyPrice * downPaymentPercent) / 100;
                const loanAmount = propertyPrice - downPaymentAmount;
                const monthlyRate = interestRate / 100 / 12;
                const numberOfPayments = loanTerm * 12;
                
                const monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
                
                // Display result
                monthlyPaymentElement.textContent = '$' + Math.round(monthlyPayment).toLocaleString();
                console.log('Monthly payment calculated:', monthlyPayment);
                
            } catch (error) {
                console.error('Error in calculateMortgage:', error);
                document.getElementById('monthlyPayment').textContent = 'ERROR';
            }
        }
        
        function updateDownPayment(value) {
            document.getElementById('downPaymentValue').textContent = value + '%';
        }
        
        // Initialize mortgage calculator with dynamic values
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Mortgage Calculator Initializing...');
            console.log('Default Down Payment:', window.mortgageDefaultDownPayment);
            console.log('Default Interest Rate:', window.mortgageDefaultInterestRate);
            console.log('Default Loan Term:', window.mortgageDefaultLoanTerm);
            
            // Set initial down payment value
            const downPaymentSlider = document.getElementById('downPayment');
            if (downPaymentSlider) {
                const downPaymentValue = window.mortgageDefaultDownPayment || 20;
                downPaymentSlider.value = downPaymentValue;
                updateDownPayment(downPaymentValue);
                console.log('Set down payment to:', downPaymentValue);
            }
            
            // Set initial interest rate
            const interestRateInput = document.getElementById('interestRate');
            if (interestRateInput) {
                const interestRateValue = window.mortgageDefaultInterestRate || 6.5;
                interestRateInput.value = interestRateValue;
                console.log('Set interest rate to:', interestRateValue);
            }
            
            // Set initial loan term
            const loanTermSelect = document.getElementById('loanTerm');
            if (loanTermSelect) {
                const loanTermValue = window.mortgageDefaultLoanTerm || 30;
                loanTermSelect.value = loanTermValue;
                console.log('Set loan term to:', loanTermValue);
            }
            
            // Calculate initial mortgage
            setTimeout(function() {
                console.log('Triggering initial calculation...');
                calculateMortgageNow();
                console.log('Initial mortgage calculation completed');
            }, 100);
            
            // Also trigger calculation on window load as backup
            window.addEventListener('load', function() {
                console.log('Window loaded, triggering calculation...');
                setTimeout(calculateMortgageNow, 200);
            });
            
            // Force calculation after a longer delay as final backup
            setTimeout(function() {
                console.log('Final backup calculation...');
                calculateMortgageNow();
            }, 1000);
        });
        
        // Load JSZip library for zip functionality
        if (!window.JSZip) {
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
            document.head.appendChild(script);
        }
    </script>
</div>