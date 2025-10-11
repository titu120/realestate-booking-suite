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

// Get all property meta fields with fallbacks
$price = get_post_meta($property_id, '_property_price', true) ?: get_post_meta($property_id, '_resbs_price', true);
$price_per_sqft = get_post_meta($property_id, '_property_price_per_sqft', true);
$price_note = get_post_meta($property_id, '_property_price_note', true);
$call_for_price = get_post_meta($property_id, '_property_call_for_price', true);
$bedrooms = get_post_meta($property_id, '_property_bedrooms', true) ?: get_post_meta($property_id, '_resbs_bedrooms', true);
$bathrooms = get_post_meta($property_id, '_property_bathrooms', true) ?: get_post_meta($property_id, '_resbs_bathrooms', true);
$half_baths = get_post_meta($property_id, '_property_half_baths', true);
$total_rooms = get_post_meta($property_id, '_property_total_rooms', true);
$floors = get_post_meta($property_id, '_property_floors', true);
$floor_level = get_post_meta($property_id, '_property_floor_level', true);
$area = get_post_meta($property_id, '_property_area_sqft', true) ?: get_post_meta($property_id, '_resbs_area', true);
$lot_size = get_post_meta($property_id, '_property_lot_size_sqft', true);
$year_built = get_post_meta($property_id, '_property_year_built', true);
$year_remodeled = get_post_meta($property_id, '_property_year_remodeled', true);
$latitude = get_post_meta($property_id, '_property_latitude', true) ?: get_post_meta($property_id, '_resbs_latitude', true);
$longitude = get_post_meta($property_id, '_property_longitude', true) ?: get_post_meta($property_id, '_resbs_longitude', true);
$gallery = get_post_meta($property_id, '_property_gallery', true) ?: get_post_meta($property_id, '_resbs_gallery', true);
$floor_plans = get_post_meta($property_id, '_property_floor_plans', true);
$virtual_tour = get_post_meta($property_id, '_property_virtual_tour', true);
$video_url = get_post_meta($property_id, '_property_video_url', true) ?: get_post_meta($property_id, '_resbs_video_url', true);
$video_embed = get_post_meta($property_id, '_property_video_embed', true);
$description = get_post_meta($property_id, '_property_description', true) ?: get_post_meta($property_id, '_resbs_description', true);
$features = get_post_meta($property_id, '_property_features', true) ?: get_post_meta($property_id, '_resbs_features', true);
$amenities = get_post_meta($property_id, '_property_amenities', true) ?: get_post_meta($property_id, '_resbs_amenities', true);
$parking = get_post_meta($property_id, '_property_parking', true);
$heating = get_post_meta($property_id, '_property_heating', true);
$cooling = get_post_meta($property_id, '_property_cooling', true);
$basement = get_post_meta($property_id, '_property_basement', true);
$roof = get_post_meta($property_id, '_property_roof', true);
$exterior_material = get_post_meta($property_id, '_property_exterior_material', true);
$floor_covering = get_post_meta($property_id, '_property_floor_covering', true);

// Get property status and type
$property_status = get_post_meta($property_id, '_property_status', true) ?: 'For Sale';
$property_type = get_post_meta($property_id, '_property_type', true) ?: 'Property';

// Get location data
$address = get_post_meta($property_id, '_property_address', true) ?: get_post_meta($property_id, '_resbs_address', true);
$city = get_post_meta($property_id, '_property_city', true) ?: get_post_meta($property_id, '_resbs_city', true);
$state = get_post_meta($property_id, '_property_state', true) ?: get_post_meta($property_id, '_resbs_state', true);
$zip = get_post_meta($property_id, '_property_zip', true) ?: get_post_meta($property_id, '_resbs_zip', true);

// Get agent data
$agent_id = get_post_meta($property_id, '_property_agent', true);
$agent_name = get_post_meta($property_id, '_property_agent_name', true);
$agent_phone = get_post_meta($property_id, '_property_agent_phone', true);
$agent_email = get_post_meta($property_id, '_property_agent_email', true);
$agent_photo = get_post_meta($property_id, '_property_agent_photo', true);

// Get gallery images
if (is_array($gallery)) {
    $gallery_images = array_filter($gallery);
} else {
    $gallery_images = array_filter(explode(',', $gallery));
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
$property_title = get_the_title() ?: 'Property Details';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($property_title); ?> - Property Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }

        .gallery-img {
            transition: transform 0.3s ease;
        }

        .gallery-img:hover {
            transform: scale(1.05);
        }

        .tab-active {
            border-bottom: 3px solid #10b981;
            color: #10b981;
        }

        .smooth-scroll {
            scroll-behavior: smooth;
        }

        #map {
            height: 400px;
            width: 100%;
        }

        .amenity-item {
            transition: all 0.3s ease;
        }

        .amenity-item:hover {
            background-color: #f0fdf4;
            transform: translateY(-2px);
        }

        .property-card {
            transition: all 0.3s ease;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .rating-star {
            color: #fbbf24;
        }

        .filter-active {
            background-color: #10b981;
            color: white;
        }

        .image-viewer {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .image-viewer.active {
            display: flex;
        }

        @media print {
            .no-print {
                display: none;
            }
        }

        .badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .8;
            }
        }

        .skeleton {
            animation: skeleton-loading 1s linear infinite alternate;
        }

        @keyframes skeleton-loading {
            0% {
                background-color: hsl(200, 20%, 80%);
            }
            100% {
                background-color: hsl(200, 20%, 95%);
            }
        }

        .tooltip {
            position: relative;
        }

        .tooltip:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            white-space: nowrap;
            font-size: 12px;
            z-index: 1000;
        }
    </style>
</head>
<body class="bg-gray-50">
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
                            <p class="text-gray-600 flex items-center">
                                <i class="fas fa-map-marker-alt text-emerald-500 mr-2"></i>
                                <?php echo esc_html($full_address ?: 'Location not specified'); ?>
                            </p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <p class="text-4xl font-bold text-emerald-500"><?php echo esc_html($formatted_price); ?></p>
                            <?php if ($price_per_sqft_formatted): ?>
                            <p class="text-gray-500 text-sm"><?php echo esc_html($price_per_sqft_formatted); ?></p>
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
                            Overview
                        </button>
                        <button onclick="switchTab('details')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="details">
                            Details
                        </button>
                        <button onclick="switchTab('features')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="features">
                            Features
                        </button>
                        <?php if (!empty($floor_plans)): ?>
                        <button onclick="switchTab('floorplan')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="floorplan">
                            Floor Plan
                        </button>
                        <?php endif; ?>
                        <button onclick="switchTab('location')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="location">
                            Location
                        </button>
                        <button onclick="switchTab('reviews')" class="tab-button px-6 py-4 font-semibold text-gray-600 hover:text-emerald-500 whitespace-nowrap" data-tab="reviews">
                            Reviews
                        </button>
                    </div>

                    <!-- Tab Contents -->
                    <div class="p-6">
                        <!-- Overview Tab -->
                        <div id="overview-tab" class="tab-content">
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Property Description</h3>
                            <div class="text-gray-600 space-y-4 leading-relaxed">
                                <?php if ($description): ?>
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
                            <h3 class="text-xl font-bold text-gray-800 mb-4">Amenities & Features</h3>
                            
                            <!-- Filter Buttons -->
                            <div class="flex flex-wrap gap-2 mb-6 no-print">
                                <button onclick="filterAmenities('all')" class="filter-btn filter-active px-4 py-2 rounded-lg font-semibold text-sm transition" data-filter="all">All</button>
                                <button onclick="filterAmenities('interior')" class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-200 transition" data-filter="interior">Interior</button>
                                <button onclick="filterAmenities('exterior')" class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-200 transition" data-filter="exterior">Exterior</button>
                                <button onclick="filterAmenities('building')" class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-lg font-semibold text-sm hover:bg-gray-200 transition" data-filter="building">Building</button>
                            </div>

                            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4" id="amenitiesContainer">
                                <?php if (!empty($features_list)): ?>
                                    <?php foreach ($features_list as $feature): ?>
                                    <div class="amenity-item p-4 bg-gray-50 rounded-lg" data-category="interior">
                                        <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                                        <span class="text-gray-700"><?php echo esc_html($feature); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($amenities_list)): ?>
                                    <?php foreach ($amenities_list as $amenity): ?>
                                    <div class="amenity-item p-4 bg-gray-50 rounded-lg" data-category="building">
                                        <i class="fas fa-check-circle text-emerald-500 mr-2"></i>
                                        <span class="text-gray-700"><?php echo esc_html($amenity); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
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
                            <?php if ($latitude && $longitude): ?>
                            <div id="map" class="rounded-lg mb-6"></div>
                            <?php endif; ?>
                            
                            <div class="grid md:grid-cols-3 gap-4">
                                <div class="p-4 bg-blue-50 rounded-lg">
                                    <i class="fas fa-graduation-cap text-blue-500 text-2xl mb-2"></i>
                                    <h4 class="font-semibold text-gray-800 mb-1">Schools</h4>
                                    <p class="text-sm text-gray-600">Nearby schools</p>
                                </div>
                                <div class="p-4 bg-green-50 rounded-lg">
                                    <i class="fas fa-shopping-cart text-green-500 text-2xl mb-2"></i>
                                    <h4 class="font-semibold text-gray-800 mb-1">Shopping</h4>
                                    <p class="text-sm text-gray-600">Shopping centers nearby</p>
                                </div>
                                <div class="p-4 bg-purple-50 rounded-lg">
                                    <i class="fas fa-utensils text-purple-500 text-2xl mb-2"></i>
                                    <h4 class="font-semibold text-gray-800 mb-1">Restaurants</h4>
                                    <p class="text-sm text-gray-600">Dining options nearby</p>
                                </div>
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
                    </div>
                </div>

                <!-- Similar Properties -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">Similar Properties</h3>
                    <div class="grid md:grid-cols-2 gap-6">
                        <?php
                        // Get similar properties
                        $similar_properties = get_posts(array(
                            'post_type' => 'property',
                            'posts_per_page' => 2,
                            'post__not_in' => array($property_id),
                            'meta_query' => array(
                                array(
                                    'key' => '_property_status',
                                    'value' => $property_status,
                                    'compare' => '='
                                )
                            )
                        ));
                        
                        if ($similar_properties):
                            foreach ($similar_properties as $similar):
                                $similar_price = get_post_meta($similar->ID, '_property_price', true);
                                $similar_bedrooms = get_post_meta($similar->ID, '_property_bedrooms', true);
                                $similar_bathrooms = get_post_meta($similar->ID, '_property_bathrooms', true);
                                $similar_area = get_post_meta($similar->ID, '_property_area_sqft', true);
                                $similar_gallery = get_post_meta($similar->ID, '_property_gallery', true);
                                $similar_images = is_array($similar_gallery) ? $similar_gallery : explode(',', $similar_gallery);
                                $similar_image = !empty($similar_images) ? $similar_images[0] : 'https://images.unsplash.com/photo-1600607687644-c7171b42498f?w=600';
                        ?>
                        <div class="property-card border rounded-lg overflow-hidden">
                            <div class="relative">
                                <img src="<?php echo esc_url($similar_image); ?>" alt="Property" class="w-full h-48 object-cover">
                                <span class="absolute top-3 left-3 bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-semibold"><?php echo esc_html($property_status); ?></span>
                            </div>
                            <div class="p-4">
                                <h4 class="font-bold text-lg text-gray-800 mb-2"><?php echo esc_html($similar->post_title); ?></h4>
                                <p class="text-gray-600 text-sm mb-3 flex items-center">
                                    <i class="fas fa-map-marker-alt text-emerald-500 mr-2"></i>
                                    <?php echo esc_html(get_post_meta($similar->ID, '_property_address', true)); ?>
                                </p>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-2xl font-bold text-emerald-500">$<?php echo esc_html(number_format($similar_price)); ?></span>
                                </div>
                                <div class="flex items-center space-x-4 text-sm text-gray-600 border-t pt-3">
                                    <span><i class="fas fa-bed mr-1"></i><?php echo esc_html($similar_bedrooms ?: '0'); ?> Beds</span>
                                    <span><i class="fas fa-bath mr-1"></i><?php echo esc_html($similar_bathrooms ?: '0'); ?> Bath</span>
                                    <span><i class="fas fa-ruler-combined mr-1"></i><?php echo esc_html($similar_area ?: '0'); ?> sqft</span>
                                </div>
                            </div>
                        </div>
                        <?php
                            endforeach;
                        else:
                        ?>
                        <div class="col-span-2 text-center py-8">
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
                            <i class="fas fa-star rating-star text-sm"></i>
                            <i class="fas fa-star rating-star text-sm"></i>
                            <i class="fas fa-star rating-star text-sm"></i>
                            <i class="fas fa-star rating-star text-sm"></i>
                            <i class="fas fa-star rating-star text-sm"></i>
                            <span class="text-sm text-gray-600 ml-2">(reviews)</span>
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
                            <span>Send Message</span>
                        </button>
                        <button onclick="scheduleTour()" class="w-full flex items-center justify-center px-4 py-3 border-2 border-emerald-500 text-emerald-500 rounded-lg hover:bg-emerald-50 transition">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <span>Schedule Tour</span>
                        </button>
                    </div>

                    <div class="border-t pt-4">
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-gray-600">Properties Sold:</span>
                            <span class="font-semibold text-gray-800">100+</span>
                        </div>
                        <div class="flex items-center justify-between text-sm mb-2">
                            <span class="text-gray-600">Experience:</span>
                            <span class="font-semibold text-gray-800">5+ Years</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Response Time:</span>
                            <span class="font-semibold text-gray-800">< 1 Hour</span>
                        </div>
                    </div>
                </div>

                <!-- Mortgage Calculator -->
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Mortgage Calculator</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Property Price</label>
                            <input type="text" id="propertyPrice" value="<?php echo esc_attr($formatted_price); ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" onkeyup="calculateMortgage()">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Down Payment (%)</label>
                            <input type="range" id="downPayment" min="0" max="100" value="20" class="w-full" oninput="updateDownPayment(this.value); calculateMortgage()">
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>0%</span>
                                <span id="downPaymentValue" class="font-semibold">20%</span>
                                <span>100%</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Interest Rate (%)</label>
                            <input type="number" id="interestRate" value="6.5" step="0.1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" onkeyup="calculateMortgage()">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Loan Term (Years)</label>
                            <select id="loanTerm" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500" onchange="calculateMortgage()">
                                <option value="15">15 Years</option>
                                <option value="20">20 Years</option>
                                <option value="30" selected>30 Years</option>
                            </select>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-4 mt-4">
                            <p class="text-sm text-gray-600 mb-1">Estimated Monthly Payment</p>
                            <p class="text-3xl font-bold text-emerald-600" id="monthlyPayment">$0</p>
                            <p class="text-xs text-gray-500 mt-2">*Principal & Interest only</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-gradient-to-br from-emerald-500 to-teal-500 rounded-xl shadow-sm p-6 text-white">
                    <h3 class="text-xl font-bold mb-4">Property Insights</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center">
                                <i class="fas fa-eye mr-2"></i>Views
                            </span>
                            <span class="font-bold"><?php echo get_post_meta($property_id, '_property_views', true) ?: '0'; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center">
                                <i class="fas fa-heart mr-2"></i>Favorites
                            </span>
                            <span class="font-bold"><?php echo get_post_meta($property_id, '_property_favorites', true) ?: '0'; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center">
                                <i class="fas fa-share-alt mr-2"></i>Shares
                            </span>
                            <span class="font-bold"><?php echo get_post_meta($property_id, '_property_shares', true) ?: '0'; ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center">
                                <i class="fas fa-clock mr-2"></i>Listed
                            </span>
                            <span class="font-bold"><?php echo human_time_diff(get_the_time('U'), current_time('timestamp')); ?> ago</span>
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
                <h3 class="text-2xl font-bold text-gray-800">Contact Agent</h3>
                <button onclick="closeContactModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form onsubmit="submitContactForm(event)" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                    <input type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                    <input type="tel" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                    <textarea rows="4" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
                <button type="submit" class="w-full bg-emerald-500 text-white py-3 rounded-lg hover:bg-emerald-600 transition font-semibold">
                    Send Message
                </button>
            </form>
        </div>
    </div>

    <script>
        // Gallery images from PHP
        const galleryImages = <?php echo json_encode($gallery_images); ?>;
        let currentImageIndex = 0;

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Tab Switching
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            
            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('tab-active');
                button.classList.add('text-gray-600');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.remove('hidden');
            
            // Add active class to clicked button
            const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
            activeButton.classList.add('tab-active');
            activeButton.classList.remove('text-gray-600');

            // Initialize map if location tab is opened
            if (tabName === 'location' && !window.mapInitialized) {
                initMap();
            }
        }

        // Filter Amenities
        function filterAmenities(category) {
            const items = document.querySelectorAll('.amenity-item');
            const buttons = document.querySelectorAll('.filter-btn');
            
            // Update button styles
            buttons.forEach(btn => {
                btn.classList.remove('filter-active');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });
            
            event.target.classList.add('filter-active');
            event.target.classList.remove('bg-gray-100', 'text-gray-700');
            
            // Filter items
            items.forEach(item => {
                if (category === 'all' || item.dataset.category === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Image Viewer
        function openImageViewer(index) {
            currentImageIndex = index;
            document.getElementById('viewerImage').src = galleryImages[index];
            document.getElementById('imageViewer').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeImageViewer() {
            document.getElementById('imageViewer').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
            document.getElementById('viewerImage').src = galleryImages[currentImageIndex];
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
            document.getElementById('viewerImage').src = galleryImages[currentImageIndex];
        }

        // Keyboard navigation for image viewer
        document.addEventListener('keydown', function(e) {
            if (document.getElementById('imageViewer').classList.contains('active')) {
                if (e.key === 'ArrowRight') nextImage();
                if (e.key === 'ArrowLeft') prevImage();
                if (e.key === 'Escape') closeImageViewer();
            }
        });

        // Contact Modal
        function openContactModal() {
            document.getElementById('contactModal').classList.remove('hidden');
            document.getElementById('contactModal').classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeContactModal() {
            document.getElementById('contactModal').classList.add('hidden');
            document.getElementById('contactModal').classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        function submitContactForm(e) {
            e.preventDefault();
            alert('Thank you for your message! The agent will contact you shortly.');
            closeContactModal();
        }

        // Mortgage Calculator
        function updateDownPayment(value) {
            document.getElementById('downPaymentValue').textContent = value + '%';
        }

        function calculateMortgage() {
            const priceStr = document.getElementById('propertyPrice').value.replace(/[$,]/g, '');
            const price = parseFloat(priceStr);
            const downPaymentPercent = parseFloat(document.getElementById('downPayment').value);
            const interestRate = parseFloat(document.getElementById('interestRate').value);
            const loanTerm = parseFloat(document.getElementById('loanTerm').value);
            
            const downPayment = price * (downPaymentPercent / 100);
            const loanAmount = price - downPayment;
            const monthlyRate = (interestRate / 100) / 12;
            const numberOfPayments = loanTerm * 12;
            
            const monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / 
                                  (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
            
            document.getElementById('monthlyPayment').textContent = 
                '$' + monthlyPayment.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }

        // Initialize map
        let map;
        function initMap() {
            if (window.mapInitialized) return;
            
            <?php if ($latitude && $longitude): ?>
            map = L.map('map').setView([<?php echo esc_js($latitude); ?>, <?php echo esc_js($longitude); ?>], 14);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
            
            L.marker([<?php echo esc_js($latitude); ?>, <?php echo esc_js($longitude); ?>]).addTo(map)
                .bindPopup('<b><?php echo esc_js($property_title); ?></b><br><?php echo esc_js($full_address); ?>')
                .openPopup();
            <?php endif; ?>
            
            window.mapInitialized = true;
        }

        // Utility Functions
        function shareProperty() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo esc_js($property_title); ?>',
                    text: 'Check out this amazing property!',
                    url: window.location.href
                });
            } else {
                alert('Share link copied to clipboard!');
                navigator.clipboard.writeText(window.location.href);
            }
        }

        function saveFavorite() {
            alert('Property saved to your favorites!');
        }

        function printPage() {
            window.print();
        }

        function exportPDF() {
            alert('PDF export will be downloaded shortly...');
        }

        function downloadFloorPlan() {
            alert('Floor plan downloading...');
        }

        function requestCustomPlan() {
            alert('Request sent! Our team will contact you shortly.');
        }

        function scheduleTour() {
            alert('Tour scheduling form will open...');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateMortgage();
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            const contactModal = document.getElementById('contactModal');
            if (event.target === contactModal) {
                closeContactModal();
            }
        }
    </script>
</body>
</html>