<?php
    /**
     * Single Property Template - Dynamic Version
     *
     * @package RealEstate_Booking_Suite
     */

    get_header();

    // Prevent direct access
    if (! defined('ABSPATH')) {
        exit;
    }

    // Define plugin URL if not already defined
    if (! defined('RESBS_URL')) {
        define('RESBS_URL', plugin_dir_url(dirname(__FILE__)) . '/');
    }

    // Get the current post
    global $post;
    if (! $post) {
        return;
    }

    // Get all property data
    $price          = get_post_meta($post->ID, '_property_price', true);
    $price_per_sqft = get_post_meta($post->ID, '_property_price_per_sqft', true);
    $price_note     = get_post_meta($post->ID, '_property_price_note', true);
    $call_for_price = get_post_meta($post->ID, '_property_call_for_price', true);

    $bedrooms       = get_post_meta($post->ID, '_property_bedrooms', true);
    $bathrooms      = get_post_meta($post->ID, '_property_bathrooms', true);
    $half_baths     = get_post_meta($post->ID, '_property_half_baths', true);
    $total_rooms    = get_post_meta($post->ID, '_property_total_rooms', true);
    $floors         = get_post_meta($post->ID, '_property_floors', true);
    $floor_level    = get_post_meta($post->ID, '_property_floor_level', true);
    $area_sqft      = get_post_meta($post->ID, '_property_area_sqft', true);
    $lot_size_sqft  = get_post_meta($post->ID, '_property_lot_size_sqft', true);
    $year_built     = get_post_meta($post->ID, '_property_year_built', true);
    $year_remodeled = get_post_meta($post->ID, '_property_year_remodeled', true);

    $property_type      = get_post_meta($post->ID, '_property_type', true);
    $property_status    = get_post_meta($post->ID, '_property_status', true);
    $property_condition = get_post_meta($post->ID, '_property_condition', true);

    $address      = get_post_meta($post->ID, '_property_address', true);
    $city         = get_post_meta($post->ID, '_property_city', true);
    $state        = get_post_meta($post->ID, '_property_state', true);
    $zip          = get_post_meta($post->ID, '_property_zip', true);
    $country      = get_post_meta($post->ID, '_property_country', true);
    $latitude     = get_post_meta($post->ID, '_property_latitude', true);
    $longitude    = get_post_meta($post->ID, '_property_longitude', true);
    $map_iframe   = get_post_meta($post->ID, '_property_map_iframe', true);
    // Try alternative field names
    if (!$map_iframe) {
        $map_iframe = get_post_meta($post->ID, '_property_custom_map_iframe', true);
    }
    if (!$map_iframe) {
        $map_iframe = get_post_meta($post->ID, '_property_map_embed', true);
    }
    if (!$map_iframe) {
        $map_iframe = get_post_meta($post->ID, '_property_google_map_iframe', true);
    }
    $hide_address = get_post_meta($post->ID, '_property_hide_address', true);

    $features          = get_post_meta($post->ID, '_property_features', true);
    $amenities         = get_post_meta($post->ID, '_property_amenities', true);
    $parking           = get_post_meta($post->ID, '_property_parking', true);
    $heating           = get_post_meta($post->ID, '_property_heating', true);
    $cooling           = get_post_meta($post->ID, '_property_cooling', true);
    $basement          = get_post_meta($post->ID, '_property_basement', true);
    $roof              = get_post_meta($post->ID, '_property_roof', true);
    $exterior_material = get_post_meta($post->ID, '_property_exterior_material', true);
    $floor_covering    = get_post_meta($post->ID, '_property_floor_covering', true);

    // Nearby features
    $nearby_schools     = get_post_meta($post->ID, '_property_nearby_schools', true);
    $nearby_shopping    = get_post_meta($post->ID, '_property_nearby_shopping', true);
    $nearby_restaurants = get_post_meta($post->ID, '_property_nearby_restaurants', true);

    $gallery_images           = get_post_meta($post->ID, '_property_gallery', true);
    
    // Convert gallery attachment IDs to URLs
    $gallery_urls = [];
    if (!empty($gallery_images) && is_array($gallery_images)) {
        foreach ($gallery_images as $image_id) {
            $image_url = wp_get_attachment_image_url($image_id, 'full');
            if ($image_url) {
                $gallery_urls[] = $image_url;
            }
        }
    }
    $floor_plans              = get_post_meta($post->ID, '_property_floor_plans', true);
    $virtual_tour             = get_post_meta($post->ID, '_property_virtual_tour', true);
    $virtual_tour_title       = get_post_meta($post->ID, '_property_virtual_tour_title', true);
    $virtual_tour_description = get_post_meta($post->ID, '_property_virtual_tour_description', true);
    $virtual_tour_button_text = get_post_meta($post->ID, '_property_virtual_tour_button_text', true);
    $video_url                = get_post_meta($post->ID, '_property_video_url', true);
    $video_embed              = get_post_meta($post->ID, '_property_video_embed', true);

    // Agent data
    $agent_name              = get_post_meta($post->ID, '_property_agent_name', true);
    $agent_phone             = get_post_meta($post->ID, '_property_agent_phone', true);
    $agent_email             = get_post_meta($post->ID, '_property_agent_email', true);
    $agent_photo             = get_post_meta($post->ID, '_property_agent_photo', true);
    $agent_properties_sold   = get_post_meta($post->ID, '_property_agent_properties_sold', true);
    $agent_experience        = get_post_meta($post->ID, '_property_agent_experience', true);
    $agent_response_time     = get_post_meta($post->ID, '_property_agent_response_time', true);
    $agent_rating            = get_post_meta($post->ID, '_property_agent_rating', true);
    $agent_reviews           = get_post_meta($post->ID, '_property_agent_reviews', true);
    $agent_send_message_text = get_post_meta($post->ID, '_property_agent_send_message_text', true);

    // Contact Form Dynamic Fields
    $contact_form_title      = get_post_meta($post->ID, '_property_contact_form_title', true);
    $contact_name_label      = get_post_meta($post->ID, '_property_contact_name_label', true);
    $contact_email_label     = get_post_meta($post->ID, '_property_contact_email_label', true);
    $contact_phone_label     = get_post_meta($post->ID, '_property_contact_phone_label', true);
    $contact_message_label   = get_post_meta($post->ID, '_property_contact_message_label', true);
    $contact_success_message = get_post_meta($post->ID, '_property_contact_success_message', true);
    $contact_submit_text     = get_post_meta($post->ID, '_property_contact_submit_text', true);

    // Mortgage Calculator Dynamic Fields
    $mortgage_calculator_title      = get_post_meta($post->ID, '_property_mortgage_calculator_title', true);
    $mortgage_property_price_label  = get_post_meta($post->ID, '_property_mortgage_property_price_label', true);
    $mortgage_down_payment_label    = get_post_meta($post->ID, '_property_mortgage_down_payment_label', true);
    $mortgage_interest_rate_label   = get_post_meta($post->ID, '_property_mortgage_interest_rate_label', true);
    $mortgage_loan_term_label       = get_post_meta($post->ID, '_property_mortgage_loan_term_label', true);
    $mortgage_monthly_payment_label = get_post_meta($post->ID, '_property_mortgage_monthly_payment_label', true);
    $mortgage_default_down_payment  = get_post_meta($post->ID, '_property_mortgage_default_down_payment', true);
    $mortgage_default_interest_rate = get_post_meta($post->ID, '_property_mortgage_default_interest_rate', true);
    $mortgage_default_loan_term     = get_post_meta($post->ID, '_property_mortgage_default_loan_term', true);
    $mortgage_disclaimer_text       = get_post_meta($post->ID, '_property_mortgage_disclaimer_text', true);
    
    // Get global mortgage calculator settings
    $mortgage_loan_terms = get_option('resbs_mortgage_loan_terms', '');
    $mortgage_default_loan_term_global = get_option('resbs_mortgage_default_loan_term', '');
    $mortgage_default_down_payment_global = get_option('resbs_mortgage_default_down_payment', '');
    $mortgage_default_interest_rate_global = get_option('resbs_mortgage_default_interest_rate', '');

    // Tour Information Fields
    $tour_duration   = get_post_meta($post->ID, '_property_tour_duration', true);
    $tour_group_size = get_post_meta($post->ID, '_property_tour_group_size', true);
    $tour_safety     = get_post_meta($post->ID, '_property_tour_safety', true);

    // Get property badges
    $property_badges = get_post_meta($post->ID, '_property_badges', true);
    if (! is_array($property_badges)) {
        $property_badges = [];
    }

    // Get property taxonomies
    $property_types     = get_the_terms($post->ID, 'property_type');
    $property_statuses  = get_the_terms($post->ID, 'property_status');
    $property_locations = get_the_terms($post->ID, 'property_location');

    // Get featured image
    $featured_image = get_the_post_thumbnail_url($post->ID, 'large');

    // Format price
    $formatted_price = '';
    if ($price && ! $call_for_price) {
        $formatted_price = '$' . number_format($price);
    } elseif ($call_for_price) {
        $formatted_price = 'Call for Price';
    }

    // Format price per sqft
    $formatted_price_per_sqft = '';
    if ($price_per_sqft) {
        $formatted_price_per_sqft = '$' . number_format($price_per_sqft) . '/sq ft';
    }

    // Format area
    $formatted_area = '';
    if ($area_sqft) {
        $formatted_area = number_format($area_sqft) . ' sq ft';
    }

    // Format lot size
    $formatted_lot_size = '';
    if ($lot_size_sqft) {
        $formatted_lot_size = number_format($lot_size_sqft) . ' sq ft';
    }

    // Format full address
    $full_address = '';
    if ($address) {
        $full_address = $address;
        if ($city) {
            $full_address .= ', ' . $city;
        }

        if ($state) {
            $full_address .= ', ' . $state;
        }

        if ($zip) {
            $full_address .= ' ' . $zip;
        }

        if ($country) {
            $full_address .= ', ' . $country;
        }

    }
    
    // Ensure addresses are properly formatted with country if missing
    if ($country && strpos(strtolower($full_address), strtolower($country)) === false) {
        $full_address .= ', ' . $country;
    }

    // Parse features and amenities
    $features_array = [];
    if ($features && is_string($features)) {
        $features_array = explode(',', $features);
        $features_array = array_map('trim', $features_array);
        // Filter out empty items
        $features_array = array_filter($features_array, function($item) {
            return !empty(trim($item));
        });
    }

    $amenities_array = [];
    if ($amenities && is_string($amenities)) {
        $amenities_array = explode(',', $amenities);
        $amenities_array = array_map('trim', $amenities_array);
        // Filter out empty items
        $amenities_array = array_filter($amenities_array, function($item) {
            return !empty(trim($item));
        });
    }

    // Parse gallery images
    $gallery_array = [];
    if ($gallery_images && is_string($gallery_images)) {
        $gallery_array = explode(',', $gallery_images);
        $gallery_array = array_map('trim', $gallery_array);
    }

    // Default values for missing data
    $default_values = [
        'price'                 => '$0',
        'price_per_sqft'        => '$0/sq ft',
        'bedrooms'              => '0',
        'bathrooms'             => '0',
        'area_sqft'             => '0 sq ft',
        'property_type'         => 'Property',
        'property_status'       => 'Available',
        'property_condition'    => 'Good',
        'agent_name'            => 'Contact Agent',
        'agent_phone'           => 'N/A',
        'agent_email'           => 'N/A',
        'agent_rating'          => '5',
        'agent_reviews'         => '0',
        'agent_experience'      => 'N/A',
        'agent_response_time'   => 'N/A',
        'agent_properties_sold' => '0',
    ];

    // Apply defaults
    foreach ($default_values as $key => $default_value) {
        if (empty($$key)) {
            $$key = $default_value;
        }
    }
?>
<!-- Single Property Template Styles -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<link rel="stylesheet" href="<?php echo RESBS_URL; ?>assets/css/single-property.css" />

<!-- Single Property Template Scripts -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="<?php echo RESBS_URL; ?>assets/js/single-property-template.js"></script>

<div class="single-property">




    <div class="main-single-container container main-content ">
        <div class="grid grid-cols-1 lg-grid-cols-3">
            <!-- Main Content -->
            <div class="lg-col-span-2 test-class" > 
                <!-- Property Header -->
                <div class="card">
                    <div class="property-header">
                        <div>
                            <div class="property-badges">
                                <?php if ($property_status): ?>
                                    <span class="badge badge-primary badge"><?php echo esc_html($property_status); ?></span>
                                <?php endif; ?>
                                <?php if (in_array('Featured', $property_badges)): ?>
                                    <span class="badge badge-blue">Featured</span>
                                <?php endif; ?>
                                <?php if (in_array('New', $property_badges)): ?>
                                    <span class="badge badge-orange">New</span>
                                <?php endif; ?>
                                <?php if (in_array('Sold', $property_badges)): ?>
                                    <span class="badge badge-red">Sold</span>
                                <?php endif; ?>
                            </div>
                            <h1 class="property-title"><?php echo esc_html(get_the_title()); ?></h1>
                            <p class="property-location">
                                <i class="fas fa-map-marker-alt text-emerald-500"></i>
                                <?php echo esc_html($full_address ? $full_address : 'Location not specified'); ?>
                            </p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <p class="property-price"><?php echo esc_html($formatted_price); ?></p>
                            <?php if ($formatted_price_per_sqft): ?>
                                <p class="property-price-per-unit"><?php echo esc_html($formatted_price_per_sqft); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="property-actions no-print">
                        <button onclick="shareProperty()" class="tooltip action-btn" data-tooltip="Share Property">
                            <i class="fas fa-share-alt text-gray-600"></i>
                            <span class="text-gray-700">Share</span>
                        </button>
                        <button onclick="saveFavorite()" class="tooltip action-btn" data-tooltip="Save to Favorites">
                            <i class="far fa-heart text-gray-600"></i>
                            <span class="text-gray-700">Save</span>
                        </button>
                        <button onclick="printPage()" class="tooltip action-btn" data-tooltip="Print Details">
                            <i class="fas fa-print text-gray-600"></i>
                            <span class="text-gray-700">Print</span>
                        </button>
                        <button onclick="exportPDF()" class="tooltip action-btn action-btn-primary" data-tooltip="Export as PDF">
                            <i class="fas fa-download"></i>
                            <span>Export PDF</span>
                        </button>
                    </div>
                </div>

                <!-- Image Gallery -->
                <div class="card">
                    <div class="gallery">
                        <?php 
                            // Show ONLY gallery images, not post thumbnail
                            $all_images = [];
                            if (!empty($gallery_urls)) {
                                $all_images = $gallery_urls;
                            }
                            $total_images = count($all_images);
                        ?>

                        <?php if ($total_images > 0): ?>
                            <div class="gallery-item gallery-main">
                                <img src="<?php echo esc_url($all_images[0]); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="gallery-img" onclick="openImageViewer(0)">
                            </div>

                            <?php for ($i = 1; $i < 5; $i++): ?>
                                <?php if ($i < $total_images): ?>
                                    <div class="gallery-item <?php echo ($i == 4 && $total_images > 5) ? 'gallery-more' : ''; ?>">
                                        <img src="<?php echo esc_url($all_images[$i]); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="gallery-img" onclick="openImageViewer(<?php echo $i; ?>)">
                                        <?php if ($i == 4 && $total_images > 5): ?>
                                            <div class="gallery-overlay" onclick="openImageViewer(4)">
                                                <span>+<?php echo($total_images - 5); ?> More</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endfor; ?>
                            <?php else: ?>
                                <div class="gallery-item gallery-main">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder-property.jpg'); ?>" alt="No image available" class="gallery-img">
                                </div>
                            <?php endif; ?>
                    </div>
                </div>

                <!-- Key Features -->
                <div class="card">
                    <h2 class="section-title">Key Features</h2>
                    <div class="key-features-grid">
                        <?php if ($bedrooms): ?>
                        <div class="feature-item">
                            <i class="fas fa-bed feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($bedrooms); ?></p>
                            <p class="feature-label">Bedrooms</p>
                        </div>
                        <?php endif; ?>

                        <?php if ($bathrooms): ?>
                        <div class="feature-item">
                            <i class="fas fa-bath feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($bathrooms); ?></p>
                            <p class="feature-label">Bathrooms</p>
                        </div>
                        <?php endif; ?>

                        <?php if ($formatted_area): ?>
                        <div class="feature-item">
                            <i class="fas fa-ruler-combined feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($area_sqft); ?></p>
                            <p class="feature-label">Sq Ft</p>
                        </div>
                        <?php endif; ?>

                        <?php if ($parking): ?>
                        <div class="feature-item">
                            <i class="fas fa-car feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($parking); ?></p>
                            <p class="feature-label">Parking</p>
                        </div>
                        <?php endif; ?>

                        <?php if ($year_built): ?>
                        <div class="feature-item">
                            <i class="fas fa-calendar feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($year_built); ?></p>
                            <p class="feature-label">Year Built</p>
                        </div>
                        <?php endif; ?>

                        <?php if ($property_type): ?>
                        <div class="feature-item">
                            <i class="fas fa-home feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($property_type); ?></p>
                            <p class="feature-label">Type</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="tabs no-print">
                    <div class="tabs-header">
                        <button onclick="switchTab('overview')" class="tab-button active" data-tab="overview">
                            Overview
                        </button>
                        <button onclick="switchTab('pricing')" class="tab-button" data-tab="pricing">
                            Pricing
                        </button>
                        <button onclick="switchTab('specifications')" class="tab-button" data-tab="specifications">
                            Specifications
                        </button>
                        <button onclick="switchTab('location')" class="tab-button" data-tab="location">
                            Location
                        </button>
                        <button onclick="switchTab('features')" class="tab-button" data-tab="features">
                            Features
                        </button>
                        <button onclick="switchTab('media')" class="tab-button" data-tab="media">
                            Media
                        </button>
                        <button onclick="switchTab('agent')" class="tab-button" data-tab="agent">
                            Agent
                        </button>
                        <button onclick="switchTab('booking')" class="tab-button" data-tab="booking">
                            Booking
                        </button>
                    </div>

 
                    <!-- Tab Contents -->
                    <div class="tabs-content">
                        <!-- Overview Tab -->
                        <div id="overview-tab" class="tab-content active">
                            <!-- Property Classification -->
                            <div class="mb-6">
                                <h3 class="section-title">Property Classification</h3>
                                <p class="text-gray-600 mb-4">Basic property information and type</p>
                                <div class="classification-container">
                                    <?php if ($property_type): ?>
                                    <div class="classification-item">
                                        <label class="classification-label">Property Type</label>
                                        <p class="classification-value"><?php echo esc_html($property_type); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($property_status): ?>
                                    <div class="classification-item">
                                        <label class="classification-label">Property Status</label>
                                        <p class="classification-value"><?php echo esc_html($property_status); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($property_condition): ?>
                                    <div class="classification-item">
                                        <label class="classification-label">Property Condition</label>
                                        <p class="classification-value"><?php echo esc_html($property_condition); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Property Description -->
                            <div class="mb-6">
                                <h3 class="section-title">Property Description</h3>
                                <div class="text-gray-600 space-y-4 leading-relaxed">
                                    <?php if (get_the_content()): ?>
                                        <?php echo wp_kses_post(get_the_content()); ?>
                                    <?php else: ?>
                                        <p>No description available for this property.</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($virtual_tour): ?>
                            <div class="info-box">
                                <p><i class="fas fa-info-circle mr-2"></i><strong>Virtual Tour Available:</strong> <?php echo esc_html($virtual_tour_description ? $virtual_tour_description : 'Schedule a 3D virtual walkthrough of this property at your convenience.'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pricing Tab -->
                        <div id="pricing-tab" class="tab-content">
                            <h3 class="section-title">Property Pricing</h3>
                            <p class="text-gray-600 mb-6">Set property price and pricing details</p>
                            
                            <div class="pricing-container">
                                <!-- Main Price -->
                                <div class="pricing-card main-price">
                                    <label class="pricing-label">Price</label>
                                    <p class="pricing-value"><?php echo esc_html($formatted_price); ?></p>
                                </div>
                                
                                <!-- Price per Sq Ft -->
                                <?php if ($formatted_price_per_sqft): ?>
                                <div class="pricing-card">
                                    <label class="pricing-label">Price per Sq Ft</label>
                                    <p class="pricing-value-small"><?php echo esc_html($formatted_price_per_sqft); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Price Note -->
                                <?php if ($price_note): ?>
                                <div class="pricing-card">
                                    <label class="pricing-label">Price Note</label>
                                    <p class="pricing-value-small"><?php echo esc_html($price_note); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($call_for_price): ?>
                            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-blue-800 font-medium"><i class="fas fa-phone mr-2"></i>Call for Price</p>
                                <p class="text-blue-600 text-sm mt-1">Contact us for pricing information</p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Specifications Tab -->
                        <div id="specifications-tab" class="tab-content">
                            <h3 class="section-title">Property Specifications</h3>
                            <p class="text-gray-600 mb-6">Detailed property specifications and measurements</p>
                            
                            <div class="specifications-grid">
                                <?php if ($bedrooms): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bedrooms</label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($bedrooms); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($bathrooms): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Bathrooms</label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($bathrooms); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($half_baths): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Half Baths</label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($half_baths); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($total_rooms): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Rooms</label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($total_rooms); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($floors): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Floors</label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($floors); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($floor_level): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Floor Level</label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($floor_level); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($formatted_area): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Area (Sq Ft)</label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($area_sqft); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($lot_size_sqft): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Lot Size (Sq Ft)</label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($lot_size_sqft); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($year_built): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Year Built</label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($year_built); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($year_remodeled): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Year Remodeled</label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($year_remodeled); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Details Tab -->
                        <div id="details-tab" class="tab-content">
                            <h3 class="section-title">Property Details</h3>
                            <div class="details-grid">
                                <div class="space-y-3">
                                    <div class="detail-item">
                                        <span class="detail-label">Property ID:</span>
                                        <span class="detail-value"><?php echo esc_html($post->ID); ?></span>
                                    </div>
                                    <?php if ($property_type): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Property Type:</span>
                                        <span class="detail-value"><?php echo esc_html($property_type); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($year_built): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Year Built:</span>
                                        <span class="detail-value"><?php echo esc_html($year_built); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($formatted_lot_size): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Lot Size:</span>
                                        <span class="detail-value"><?php echo esc_html($formatted_lot_size); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floors): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Floors:</span>
                                        <span class="detail-value"><?php echo esc_html($floors); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="space-y-3">
                                    <?php if ($property_status): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Status:</span>
                                        <span class="detail-value text-emerald-600"><?php echo esc_html($property_status); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floor_level): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Floor Level:</span>
                                        <span class="detail-value"><?php echo esc_html($floor_level); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($heating): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Heating:</span>
                                        <span class="detail-value"><?php echo esc_html($heating); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($cooling): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Cooling:</span>
                                        <span class="detail-value"><?php echo esc_html($cooling); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($property_condition): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Condition:</span>
                                        <span class="detail-value"><?php echo esc_html($property_condition); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Features Tab -->
                        <div id="features-tab" class="tab-content">
                            <h3 class="section-title">Amenities & Features</h3>

                            <!-- Filter Buttons -->
                            <div class="amenities-filter no-print">
                                <button onclick="filterAmenities('all')" class="filter-btn active" data-filter="all">All</button>
                                <button onclick="filterAmenities('interior')" class="filter-btn" data-filter="interior">Interior</button>
                                <button onclick="filterAmenities('exterior')" class="filter-btn" data-filter="exterior">Exterior</button>
                            </div>

                            <div class="amenities-grid" id="amenitiesContainer">
                                <?php 
                                
                                // Simple categorization: Features = Interior, Amenities = Exterior
                                function categorizeItem($item, $type) {
                                    // Features are always interior, Amenities are always exterior
                                    return ($type === 'feature') ? 'interior' : 'exterior';
                                }
                                
                                // Display features and amenities from dashboard settings
                                if (!empty($features_array)): ?>
                                    <?php foreach ($features_array as $index => $feature): ?>
                                        <?php 
                                        $feature_trimmed = trim($feature);
                                        // Skip empty or whitespace-only items
                                        if (empty($feature_trimmed)) {
                                            continue;
                                        }
                                        $category = categorizeItem($feature_trimmed, 'feature');
                                        ?>
                                        <div class="amenity-item" data-category="<?php echo esc_attr($category); ?>">
                                            <i class="fas fa-check-circle text-emerald-500"></i>
                                            <span class="text-gray-700"><?php echo esc_html($feature_trimmed); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (!empty($amenities_array)): ?>
                                    <?php foreach ($amenities_array as $index => $amenity): ?>
                                        <?php 
                                        $amenity_trimmed = trim($amenity);
                                        // Skip empty or whitespace-only items
                                        if (empty($amenity_trimmed)) {
                                            continue;
                                        }
                                        $category = categorizeItem($amenity_trimmed, 'amenity');
                                        ?>
                                        <div class="amenity-item" data-category="<?php echo esc_attr($category); ?>">
                                            <i class="fas fa-check-circle text-emerald-500"></i>
                                            <span class="text-gray-700"><?php echo esc_html($amenity_trimmed); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (empty($features_array) && empty($amenities_array)): ?>
                                    <div class="amenity-item">
                                        <i class="fas fa-info-circle text-gray-400"></i>
                                        <span class="text-gray-500">No features or amenities listed for this property.</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Floor Plan Tab -->
                        <div id="floorplan-tab" class="tab-content">
                            <h3 class="section-title">Floor Plan</h3>
                            <div class="floor-plan-container">
                                <?php if ($floor_plans): ?>
                                    <img src="<?php echo esc_url($floor_plans); ?>" alt="Floor Plan" class="floor-plan-image">
                                    <div class="floor-plan-actions no-print">
                                        <button onclick="downloadFloorPlan()" class="btn btn-primary">
                                            <i class="fas fa-download mr-2"></i>Download Floor Plan
                                        </button>
                                        <button onclick="requestCustomPlan()" class="btn" style="background-color: var(--secondary-color); color: var(--white);">
                                            <i class="fas fa-envelope mr-2"></i>Request Custom Plan
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-8">
                                        <i class="fas fa-home text-gray-400 text-6xl mb-4"></i>
                                        <p class="text-gray-500">No floor plan available for this property.</p>
                                        <button onclick="requestCustomPlan()" class="btn btn-primary mt-4">
                                            <i class="fas fa-envelope mr-2"></i>Request Floor Plan
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Location Tab -->
                        <div id="location-tab" class="tab-content">
                            <h3 class="section-title">Property Location</h3>
                            <p class="text-gray-600 mb-6">Property address and location details</p>
                            
                            <!-- Address Information -->
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold mb-4">Property Address</h4>
                                <div class="address-card">
                                    <div class="address-header">
                                        <i class="fas fa-map-marker-alt address-icon"></i>
                                        <h5 class="address-title">Full Address</h5>
                                    </div>
                                    <div class="address-content">
                                        <?php if ($full_address): ?>
                                            <p class="address-main"><?php echo esc_html($full_address); ?></p>
                                        <?php else: ?>
                                            <p class="address-placeholder">Address not specified</p>
                                        <?php endif; ?>
                                        
                                        <?php if ($city || $state || $zip): ?>
                                            <div class="address-details">
                                                <?php if ($city): ?>
                                                    <div class="address-detail-item">
                                                        <i class="fas fa-city"></i>
                                                        <span><?php echo esc_html($city); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($state): ?>
                                                    <div class="address-detail-item">
                                                        <i class="fas fa-flag"></i>
                                                        <span><?php echo esc_html($state); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($zip): ?>
                                                    <div class="address-detail-item">
                                                        <i class="fas fa-mail-bulk"></i>
                                                        <span><?php echo esc_html($zip); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($country): ?>
                                            <div class="address-country">
                                                <i class="fas fa-globe"></i>
                                                <span><?php echo esc_html($country); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Map -->
                            <?php if ($map_iframe): ?>
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold mb-4">Map Location</h4>
                                <div class="map-container" style="width: 100%; height: 400px; border-radius: 8px; overflow: hidden;">
                                    <?php 
                                    // Allow iframe tags for maps
                                    $allowed_html = array(
                                        'iframe' => array(
                                            'src' => array(),
                                            'width' => array(),
                                            'height' => array(),
                                            'frameborder' => array(),
                                            'allowfullscreen' => array(),
                                            'loading' => array(),
                                            'referrerpolicy' => array(),
                                            'style' => array(),
                                            'class' => array(),
                                            'id' => array()
                                        )
                                    );
                                    
                                    // Ensure iframe has proper styling
                                    $styled_iframe = $map_iframe;
                                    if (strpos($styled_iframe, 'style=') === false) {
                                        $styled_iframe = str_replace('<iframe', '<iframe style="width: 100%; height: 100%; border: none;"', $styled_iframe);
                                    }
                                    
                                    echo wp_kses($styled_iframe, $allowed_html);
                                    ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Nearby Features -->
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold mb-4">Nearby Features</h4>
                                <div class="nearby-features-grid">
                                    <?php if ($nearby_schools): ?>
                                    <div class="location-feature location-feature-blue">
                                        <i class="fas fa-graduation-cap"></i>
                                        <h4>Schools</h4>
                                        <p><?php echo esc_html($nearby_schools); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($nearby_shopping): ?>
                                    <div class="location-feature location-feature-green">
                                        <i class="fas fa-shopping-cart"></i>
                                        <h4>Shopping</h4>
                                        <p><?php echo esc_html($nearby_shopping); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($nearby_restaurants): ?>
                                    <div class="location-feature location-feature-purple">
                                        <i class="fas fa-utensils"></i>
                                        <h4>Restaurants</h4>
                                        <p><?php echo esc_html($nearby_restaurants); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Reviews Tab -->
                        <div id="reviews-tab" class="tab-content">
                            <h3 class="section-title">Property Reviews & Ratings</h3>
                            <p class="text-gray-600 mb-6">Customer reviews and ratings for this property</p>
                            
                            <?php
                            // Get property reviews from comments
                            $reviews = get_comments(array(
                                'post_id' => $post->ID,
                                'status' => 'approve',
                                'type' => 'comment'
                            ));
                            
                            if (!empty($reviews)):
                                $total_reviews = count($reviews);
                                $average_rating = 0;
                                $rating_counts = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);
                                
                                // Calculate ratings
                                foreach ($reviews as $review) {
                                    $rating = get_comment_meta($review->comment_ID, 'rating', true);
                                    if ($rating) {
                                        $average_rating += $rating;
                                        if (isset($rating_counts[$rating])) {
                                            $rating_counts[$rating]++;
                                        }
                                    }
                                }
                                
                                if ($total_reviews > 0) {
                                    $average_rating = round($average_rating / $total_reviews, 1);
                                }
                            ?>
                            
                            <!-- Overall Rating -->
                            <div class="rating-overview mb-8">
                                <div class="rating-content">
                                    <div class="rating-score">
                                        <div class="rating-value"><?php echo esc_html($average_rating); ?></div>
                                        <div class="rating-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <?php if ($i <= floor($average_rating)): ?>
                                                    <i class="fas fa-star rating-star"></i>
                                                <?php elseif ($i == ceil($average_rating) && $average_rating != floor($average_rating)): ?>
                                                    <i class="fas fa-star-half-alt rating-star"></i>
                                                <?php else: ?>
                                                    <i class="far fa-star rating-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="rating-count">Based on <?php echo esc_html($total_reviews); ?> review<?php echo $total_reviews != 1 ? 's' : ''; ?></p>
                                    </div>
                                    
                                    <?php if ($total_reviews > 0): ?>
                                    <div class="rating-bars">
                                        <?php for ($star = 5; $star >= 1; $star--): ?>
                                            <?php $percentage = $total_reviews > 0 ? ($rating_counts[$star] / $total_reviews) * 100 : 0; ?>
                                            <div class="rating-bar">
                                                <span class="rating-label"><?php echo $star; ?>★</span>
                                                <div class="rating-bar-track">
                                                    <div class="rating-bar-fill" style="width: <?php echo esc_attr($percentage); ?>%"></div>
                                                </div>
                                                <span class="rating-bar-count"><?php echo esc_html($rating_counts[$star]); ?></span>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Reviews List -->
                            <div class="space-y-4">
                                <?php foreach ($reviews as $review): ?>
                                    <?php $rating = get_comment_meta($review->comment_ID, 'rating', true); ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="review-user">
                                                <?php echo get_avatar($review->comment_author_email, 48, '', '', array('class' => 'review-avatar')); ?>
                                                <div>
                                                    <h4 class="review-name"><?php echo esc_html($review->comment_author); ?></h4>
                                                    <div class="review-meta">
                                                        <?php if ($rating): ?>
                                                        <div class="rating-stars">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <?php if ($i <= $rating): ?>
                                                                    <i class="fas fa-star rating-star text-sm"></i>
                                                                <?php else: ?>
                                                                    <i class="far fa-star text-gray-300 text-sm"></i>
                                                                <?php endif; ?>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <?php endif; ?>
                                                        <span class="review-date"><?php echo esc_html(human_time_diff(strtotime($review->comment_date), current_time('timestamp')) . ' ago'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p class="review-text"><?php echo esc_html($review->comment_content); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php else: ?>
                                <div class="text-center py-8">
                                    <i class="fas fa-comments text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500">No reviews yet for this property.</p>
                                    <p class="text-gray-400 text-sm mt-2">Be the first to leave a review!</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Media Tab -->
                        <div id="media-tab" class="tab-content">
                            <h3 class="section-title">Property Media</h3>
                            <p class="text-gray-600 mb-6">Photos, videos, and virtual tours</p>
                            
                            <!-- Photo Gallery -->
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold mb-4">Photo Gallery</h4>
                                <?php if (!empty($gallery_urls)): ?>
                                    <div class="media-gallery-row">
                                        <?php foreach ($gallery_urls as $index => $image_url): ?>
                                            <div class="media-gallery-item cursor-pointer" onclick="openImageViewer(<?php echo $index; ?>)">
                                                <img src="<?php echo esc_url($image_url); ?>" alt="Property Image" class="media-gallery-image">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                                        <i class="fas fa-images text-gray-400 text-4xl mb-4"></i>
                                        <p class="text-gray-500">No photos available for this property</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Property Video -->
                            <?php if ($video_url || $video_embed): ?>
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold mb-4">Property Video</h4>
                                <div class="property-video-container">
                                    <?php if ($video_embed): ?>
                                        <div class="video-embed">
                                            <?php echo wp_kses_post($video_embed); ?>
                                        </div>
                                    <?php elseif ($video_url): ?>
                                        <?php
                                        // Detect video platform and extract video ID
                                        $video_platform = '';
                                        $video_id = '';
                                        $embed_url = '';
                                        
                                        // YouTube detection
                                        if (strpos($video_url, 'youtube.com') !== false || strpos($video_url, 'youtu.be') !== false) {
                                            $video_platform = 'youtube';
                                            if (strpos($video_url, 'youtube.com/watch?v=') !== false) {
                                                $video_id = substr($video_url, strpos($video_url, 'v=') + 2);
                                                $video_id = strtok($video_id, '&');
                                            } elseif (strpos($video_url, 'youtu.be/') !== false) {
                                                $video_id = substr($video_url, strpos($video_url, 'youtu.be/') + 9);
                                                $video_id = strtok($video_id, '?');
                                            }
                                            if ($video_id) {
                                                $embed_url = 'https://www.youtube.com/embed/' . $video_id . '?rel=0&modestbranding=1';
                                            }
                                        }
                                        // Vimeo detection
                                        elseif (strpos($video_url, 'vimeo.com') !== false) {
                                            $video_platform = 'vimeo';
                                            if (strpos($video_url, 'vimeo.com/') !== false) {
                                                $video_id = substr($video_url, strpos($video_url, 'vimeo.com/') + 9);
                                                $video_id = strtok($video_id, '?');
                                            }
                                            if ($video_id) {
                                                $embed_url = 'https://player.vimeo.com/video/' . $video_id . '?title=0&byline=0&portrait=0';
                                            }
                                        }
                                        // Dailymotion detection
                                        elseif (strpos($video_url, 'dailymotion.com') !== false) {
                                            $video_platform = 'dailymotion';
                                            if (strpos($video_url, 'dailymotion.com/video/') !== false) {
                                                $video_id = substr($video_url, strpos($video_url, 'dailymotion.com/video/') + 22);
                                                $video_id = strtok($video_id, '?');
                                            }
                                            if ($video_id) {
                                                $embed_url = 'https://www.dailymotion.com/embed/video/' . $video_id;
                                            }
                                        }
                                        // Wistia detection
                                        elseif (strpos($video_url, 'wistia.com') !== false || strpos($video_url, 'wistia.net') !== false) {
                                            $video_platform = 'wistia';
                                            if (preg_match('/wistia\.(com|net)\/medias\/([a-zA-Z0-9]+)/', $video_url, $matches)) {
                                                $video_id = $matches[2];
                                                $embed_url = 'https://fast.wistia.net/embed/iframe/' . $video_id;
                                            }
                                        }
                                        // Generic iframe detection (for other platforms)
                                        elseif (strpos($video_url, 'embed') !== false || strpos($video_url, 'iframe') !== false) {
                                            $video_platform = 'iframe';
                                            $embed_url = $video_url;
                                        }
                                        ?>
                                        
                                        <?php if ($video_platform && $embed_url): ?>
                                            <div class="video-embed-container">
                                                <iframe 
                                                    width="100%" 
                                                    height="400" 
                                                    src="<?php echo esc_url($embed_url); ?>" 
                                                    frameborder="0" 
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                    allowfullscreen
                                                    style="border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                                                </iframe>
                                            </div>
                                        <?php else: ?>
                                            <div class="video-player">
                                                <video controls width="100%" height="400" style="border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);">
                                                    <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                                                    <source src="<?php echo esc_url($video_url); ?>" type="video/webm">
                                                    <source src="<?php echo esc_url($video_url); ?>" type="video/ogg">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Virtual Tour -->
                            <?php if ($virtual_tour): ?>
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold mb-4">Virtual Tour</h4>
                                <div class="virtual-tour-container">
                                    <h5 class="virtual-tour-title"><?php echo esc_html($virtual_tour_title ? $virtual_tour_title : 'Virtual Tour'); ?></h5>
                                    <p class="virtual-tour-description"><?php echo esc_html($virtual_tour_description ? $virtual_tour_description : 'Experience this property from anywhere with our interactive 3D tour.'); ?></p>
                                    <a href="<?php echo esc_url($virtual_tour); ?>" target="_blank" class="virtual-tour-button">
                                        <i class="fas fa-play mr-2"></i>
                                        <?php echo esc_html($virtual_tour_button_text ? $virtual_tour_button_text : 'Start Virtual Tour'); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Agent Tab -->
                        <div id="agent-tab" class="tab-content">
                            <h3 class="section-title">Property Agent</h3>
                            <p class="text-gray-600 mb-6">Contact information and agent details</p>
                            
                            <!-- Professional Agent Card -->
                            <div class="agent-profile-card">
                                <div class="agent-profile-header">
                                    <div class="agent-avatar-section">
                                        <?php if ($agent_photo): ?>
                                            <img src="<?php echo esc_url($agent_photo); ?>" alt="<?php echo esc_attr($agent_name); ?>" class="agent-profile-avatar">
                                        <?php else: ?>
                                            <div class="agent-profile-avatar agent-placeholder">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="agent-profile-info">
                                        <h4 class="agent-profile-name"><?php echo esc_html($agent_name ? $agent_name : 'Not specified'); ?></h4>
                                        <p class="agent-profile-title">Real Estate Agent</p>
                                        <?php if ($agent_rating): ?>
                                        <div class="agent-rating-section">
                                            <div class="agent-stars">
                                                <?php
                                                $rating = intval($agent_rating);
                                                for ($i = 1; $i <= 5; $i++):
                                                ?>
                                                    <i class="fas fa-star"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="agent-reviews">(<?php echo esc_html($agent_reviews ? $agent_reviews : '0'); ?> reviews)</span>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="agent-details-grid">
                                    <?php if ($agent_phone): ?>
                                    <div class="agent-detail-item">
                                        <i class="fas fa-phone agent-detail-icon"></i>
                                        <div class="agent-detail-content">
                                            <span class="agent-detail-label">Phone</span>
                                            <span class="agent-detail-value"><?php echo esc_html($agent_phone); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($agent_email): ?>
                                    <div class="agent-detail-item">
                                        <i class="fas fa-envelope agent-detail-icon"></i>
                                        <div class="agent-detail-content">
                                            <span class="agent-detail-label">Email</span>
                                            <span class="agent-detail-value"><?php echo esc_html($agent_email); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($agent_properties_sold): ?>
                                    <div class="agent-detail-item">
                                        <i class="fas fa-home agent-detail-icon"></i>
                                        <div class="agent-detail-content">
                                            <span class="agent-detail-label">Properties Sold</span>
                                            <span class="agent-detail-value"><?php echo esc_html($agent_properties_sold); ?>+</span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($agent_experience): ?>
                                    <div class="agent-detail-item">
                                        <i class="fas fa-calendar agent-detail-icon"></i>
                                        <div class="agent-detail-content">
                                            <span class="agent-detail-label">Experience</span>
                                            <span class="agent-detail-value"><?php echo esc_html($agent_experience); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($agent_response_time): ?>
                                    <div class="agent-detail-item">
                                        <i class="fas fa-clock agent-detail-icon"></i>
                                        <div class="agent-detail-content">
                                            <span class="agent-detail-label">Response Time</span>
                                            <span class="agent-detail-value"><?php echo esc_html($agent_response_time); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Contact Form -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html($contact_form_title ? $contact_form_title : 'Contact Agent'); ?></h4>
                                <form class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_name_label ? $contact_name_label : 'Your Name'); ?></label>
                                            <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_email_label ? $contact_email_label : 'Email'); ?></label>
                                            <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_phone_label ? $contact_phone_label : 'Phone'); ?></label>
                                        <input type="tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_message_label ? $contact_message_label : 'Message'); ?></label>
                                        <textarea rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-full">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        <?php echo esc_html($contact_submit_text ? $contact_submit_text : 'Send Message'); ?>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Booking Tab -->
                        <div id="booking-tab" class="tab-content">
                            <h3 class="section-title">Property Booking</h3>
                            <p class="text-gray-600 mb-6">Schedule a viewing or book this property</p>
                            
                            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                                <h4 class="text-xl font-bold text-gray-800 mb-2">Book Property Viewing</h4>
                                <p class="text-gray-600 mb-8">Fill out the form below to schedule your property viewing</p>
                                
                                <form id="directBookingForm" onsubmit="handleDirectBooking(event)">
                                    <div class="space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <label for="bookingName" class="block text-sm font-semibold text-gray-700">Your Name *</label>
                                                <input type="text" id="bookingName" name="bookingName" required 
                                                       placeholder="Enter your full name"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200">
                                            </div>
                                            <div class="space-y-2">
                                                <label for="bookingEmail" class="block text-sm font-semibold text-gray-700">Email *</label>
                                                <input type="email" id="bookingEmail" name="bookingEmail" required 
                                                       placeholder="Enter your email address"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200">
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <label for="bookingPhone" class="block text-sm font-semibold text-gray-700">Phone</label>
                                                <input type="tel" id="bookingPhone" name="bookingPhone" 
                                                       placeholder="Enter your phone number"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200">
                                            </div>
                                            <div class="space-y-2">
                                                <label for="bookingDate" class="block text-sm font-semibold text-gray-700">Preferred Date *</label>
                                                <input type="date" id="bookingDate" name="bookingDate" required 
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200">
                                            </div>
                                        </div>
                                        

                                        
                                        <div class="space-y-2">
                                            <label for="bookingMessage" class="block text-sm font-semibold text-gray-700">Additional Message</label>
                                            <textarea id="bookingMessage" name="bookingMessage" rows="4" 
                                                      placeholder="Any specific requirements or questions about the property..."
                                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200 resize-none"></textarea>
                                        </div>
                                        
                                        <div class="pt-4">
                                            <button type="submit" class="w-full bg-white hover:bg-green-700  font-bold py-4 px-6 rounded-lg transition duration-200 flex items-center justify-center  transform ">
                                                <i class="fas fa-calendar-check mr-3 text-lg"></i>Schedule Property Viewing
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Similar Properties -->
                <div class="card">
                    <h3 class="section-title">Similar Properties</h3>
                    <p class="text-gray-600 mb-6">Other properties you might be interested in</p>
                    
                    <?php
                    // Get similar properties based on property type and status
                    $similar_properties = get_posts(array(
                        'post_type' => 'property',
                        'posts_per_page' => 2,
                        'post__not_in' => array($post->ID),
                        'meta_query' => array(
                            'relation' => 'AND',
                            array(
                                'key' => '_property_type',
                                'value' => $property_type,
                                'compare' => '='
                            ),
                            array(
                                'key' => '_property_status',
                                'value' => $property_status,
                                'compare' => '='
                            )
                        )
                    ));
                    
                    // If no similar properties found with exact match, try with just property type
                    if (empty($similar_properties)) {
                        $similar_properties = get_posts(array(
                            'post_type' => 'property',
                            'posts_per_page' => 2,
                            'post__not_in' => array($post->ID),
                            'meta_query' => array(
                                array(
                                    'key' => '_property_type',
                                    'value' => $property_type,
                                    'compare' => '='
                                )
                            )
                        ));
                    }
                    
                    // If still no results, get any other properties
                    if (empty($similar_properties)) {
                        $similar_properties = get_posts(array(
                            'post_type' => 'property',
                            'posts_per_page' => 2,
                            'post__not_in' => array($post->ID),
                            'post_status' => 'publish'
                        ));
                    }
                    
                    // Final fallback - get all properties except current one
                    if (empty($similar_properties)) {
                        $all_properties = get_posts(array(
                            'post_type' => 'property',
                            'posts_per_page' => -1,
                            'post_status' => 'publish'
                        ));
                        
                        // Remove current property from results
                        $similar_properties = array();
                        foreach ($all_properties as $prop) {
                            if ($prop->ID != $post->ID) {
                                $similar_properties[] = $prop;
                                if (count($similar_properties) >= 2) {
                                    break;
                                }
                            }
                        }
                    }
                    ?>
                    
                    
                    <?php if (!empty($similar_properties)): ?>
                        <div class="similar-properties-grid">
                            <?php foreach ($similar_properties as $similar_property): ?>
                                <?php
                                $similar_price = get_post_meta($similar_property->ID, '_property_price', true);
                                $similar_bedrooms = get_post_meta($similar_property->ID, '_property_bedrooms', true);
                                $similar_bathrooms = get_post_meta($similar_property->ID, '_property_bathrooms', true);
                                $similar_area = get_post_meta($similar_property->ID, '_property_area_sqft', true);
                                $similar_city = get_post_meta($similar_property->ID, '_property_city', true);
                                $similar_state = get_post_meta($similar_property->ID, '_property_state', true);
                                $similar_featured_image = get_the_post_thumbnail_url($similar_property->ID, 'medium');
                                $similar_status = get_post_meta($similar_property->ID, '_property_status', true);
                                
                                $formatted_similar_price = $similar_price ? '$' . number_format($similar_price) : 'Price on request';
                                $similar_location = trim($similar_city . ', ' . $similar_state, ', ');
                                ?>
                                
                                <div class="property-card">
                                    <div class="property-image">
                                        <?php if ($similar_featured_image): ?>
                                            <img src="<?php echo esc_url($similar_featured_image); ?>" alt="<?php echo esc_attr($similar_property->post_title); ?>">
                                        <?php else: ?>
                                            <div class="bg-gray-200 h-48 flex items-center justify-center">
                                                <i class="fas fa-home text-gray-400 text-4xl"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($similar_status): ?>
                                            <span class="property-badge"><?php echo esc_html($similar_status); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="property-info">
                                        <h4 class="property-card-title">
                                            <a href="<?php echo esc_url(get_permalink($similar_property->ID)); ?>" class="hover:text-emerald-600 transition-colors">
                                                <?php echo esc_html($similar_property->post_title); ?>
                                            </a>
                                        </h4>
                                        <p class="property-card-location">
                                            <i class="fas fa-map-marker-alt text-emerald-500"></i>
                                            <?php echo esc_html($similar_location ? $similar_location : 'Location not specified'); ?>
                                        </p>
                                        <div class="property-card-price"><?php echo esc_html($formatted_similar_price); ?></div>
                                        <div class="property-card-features">
                                            <?php if ($similar_bedrooms): ?>
                                                <span><i class="fas fa-bed mr-1"></i><?php echo esc_html($similar_bedrooms); ?> Bed<?php echo $similar_bedrooms != 1 ? 's' : ''; ?></span>
                                            <?php endif; ?>
                                            <?php if ($similar_bathrooms): ?>
                                                <span><i class="fas fa-bath mr-1"></i><?php echo esc_html($similar_bathrooms); ?> Bath<?php echo $similar_bathrooms != 1 ? 's' : ''; ?></span>
                                            <?php endif; ?>
                                            <?php if ($similar_area): ?>
                                                <span><i class="fas fa-ruler-combined mr-1"></i><?php echo esc_html($similar_area); ?> sqft</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-home text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">No similar properties found.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Agent Card -->
                <div class="card agent-card">
                    <div class="text-center mb-6">
                        <?php if ($agent_photo): ?>
                            <img src="<?php echo esc_url($agent_photo); ?>" alt="<?php echo esc_attr($agent_name); ?>" class="agent-avatar">
                        <?php else: ?>
                            <div class="agent-avatar" style="background-color: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <h3 class="agent-name"><?php echo esc_html($agent_name); ?></h3>
                        <p class="agent-title">Real Estate Agent</p>
                        <div class="agent-rating">
                            <?php
                                $rating = intval($agent_rating);
                                for ($i = 1; $i <= 5; $i++):
                            ?>
                                <i class="fas fa-star rating-star text-sm<?php echo $i <= $rating ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                            <?php endfor; ?>
                            <span class="text-sm text-gray-600 ml-2">(<?php echo esc_html($agent_reviews); ?> reviews)</span>
                        </div>
                    </div>

                    <div class="agent-actions">
                        <?php if ($agent_phone): ?>
                        <a href="tel:<?php echo esc_attr($agent_phone); ?>" class="agent-action agent-action-primary">
                            <i class="fas fa-phone mr-2"></i>
                            <span>Call Agent</span>
                        </a>
                        <?php endif; ?>
                        <button onclick="openContactModal()" class="agent-action agent-action-secondary">
                            <i class="fas fa-envelope mr-2"></i>
                            <span><?php echo esc_html($agent_send_message_text ? $agent_send_message_text : 'Send Message'); ?></span>
                        </button>
                        <button onclick="scheduleTour()" class="agent-action agent-action-outline">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            <span>Schedule Tour</span>
                        </button>
                    </div>

                    <div class="agent-stats">
                        <div class="agent-stat">
                            <span class="agent-stat-label">Properties Sold:</span>
                            <span class="agent-stat-value"><?php echo esc_html($agent_properties_sold); ?>+</span>
                        </div>
                        <div class="agent-stat">
                            <span class="agent-stat-label">Experience:</span>
                            <span class="agent-stat-value"><?php echo esc_html($agent_experience); ?></span>
                        </div>
                        <div class="agent-stat">
                            <span class="agent-stat-label">Response Time:</span>
                            <span class="agent-stat-value"><?php echo esc_html($agent_response_time); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Mortgage Calculator -->
                <div class="card">
                    <h3 class="section-title"><?php echo esc_html($mortgage_calculator_title ? $mortgage_calculator_title : 'Mortgage Calculator'); ?></h3>
                    <div class="space-y-4">
                        <div class="calculator-input">
                            <label class="calculator-label"><?php echo esc_html($mortgage_property_price_label ? $mortgage_property_price_label : 'Property Price'); ?></label>
                            <input type="text" id="propertyPrice" value="<?php echo esc_attr($formatted_price); ?>" class="calculator-field" onkeyup="calculateMortgage()">
                        </div>
                        <div class="calculator-input">
                            <label class="calculator-label"><?php echo esc_html($mortgage_down_payment_label ? $mortgage_down_payment_label : 'Down Payment (%)'); ?></label>
                            <input type="range" id="downPayment" min="0" max="100" value="<?php echo esc_attr($mortgage_default_down_payment ? $mortgage_default_down_payment : ($mortgage_default_down_payment_global ? $mortgage_default_down_payment_global : '20')); ?>" class="calculator-slider" oninput="updateDownPayment(this.value); calculateMortgage()">
                            <div class="calculator-slider-labels">
                                <span>0%</span>
                                <span id="downPaymentValue" class="calculator-slider-value"><?php echo esc_html($mortgage_default_down_payment ? $mortgage_default_down_payment : ($mortgage_default_down_payment_global ? $mortgage_default_down_payment_global : '20')); ?>%</span>
                                <span>100%</span>
                            </div>
                        </div>
                        <div class="calculator-input">
                            <label class="calculator-label"><?php echo esc_html($mortgage_interest_rate_label ? $mortgage_interest_rate_label : 'Interest Rate (%)'); ?></label>
                            <input type="number" id="interestRate" value="<?php echo esc_attr($mortgage_default_interest_rate ? $mortgage_default_interest_rate : ($mortgage_default_interest_rate_global ? $mortgage_default_interest_rate_global : '6.5')); ?>" step="0.1" class="calculator-field" onkeyup="calculateMortgage()">
                        </div>
                        <div class="calculator-input">
                            <label class="calculator-label"><?php echo esc_html($mortgage_loan_term_label ? $mortgage_loan_term_label : 'Loan Term (Years)'); ?></label>
                            <select id="loanTerm" class="calculator-field" onchange="calculateMortgage()">
                                <?php
                                    // Use ONLY dashboard settings - no hardcoded fallbacks
                                    if (!empty($mortgage_loan_terms)) {
                                        $loan_terms_raw = $mortgage_loan_terms;
                                        // Handle both comma-separated and newline-separated values
                                        $loan_terms = array_map('trim', preg_split('/[,\n\r]+/', $loan_terms_raw));
                                        
                                        // Ensure we have valid numeric terms
                                        $loan_terms = array_filter($loan_terms, function($term) {
                                            return is_numeric($term) && $term > 0;
                                        });
                                        
                                        // Get default term from dashboard or property settings
                                        $default_term = $mortgage_default_loan_term ? $mortgage_default_loan_term : $mortgage_default_loan_term_global;
                                        
                                        // Debug output (remove this after testing)
                                        if (current_user_can('manage_options')) {
                                            echo '<!-- DEBUG: Dashboard loan terms: ' . esc_html($loan_terms_raw) . ' -->';
                                            echo '<!-- DEBUG: Parsed terms: ' . print_r($loan_terms, true) . ' -->';
                                            echo '<!-- DEBUG: Default term: ' . esc_html($default_term) . ' -->';
                                        }
                                        
                                        // Display options from dashboard settings
                                        foreach ($loan_terms as $term):
                                            $is_selected = ($term == $default_term) ? ' selected' : '';
                                    ?>
                                        <option value="<?php echo esc_attr($term); ?>"<?php echo $is_selected; ?>><?php echo esc_html($term); ?> Years</option>
                                    <?php 
                                        endforeach;
                                    } else {
                                        // Only show this if no dashboard settings are configured
                                        echo '<option value="">Please configure loan terms in dashboard</option>';
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="calculator-result">
                            <p class="calculator-result-label"><?php echo esc_html($mortgage_monthly_payment_label ? $mortgage_monthly_payment_label : 'Estimated Monthly Payment'); ?></p>
                            <p class="calculator-result-value" id="monthlyPayment">$0</p>
                            <p class="calculator-result-note"><?php echo esc_html($mortgage_disclaimer_text ? $mortgage_disclaimer_text : '*Principal & Interest only'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="stats-card">
                    <h3 class="stats-title">Property Insights</h3>
                    <div class="stats-list">
                        <div class="stat-item">
                            <span class="stat-label">
                                <i class="fas fa-eye mr-2"></i>Views
                            </span>
                            <span class="stat-value">1,234</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">
                                <i class="fas fa-heart mr-2"></i>Favorites
                            </span>
                            <span class="stat-value">89</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">
                                <i class="fas fa-share-alt mr-2"></i>Shares
                            </span>
                            <span class="stat-value">34</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">
                                <i class="fas fa-clock mr-2"></i>Listed
                            </span>
                            <span class="stat-value">5 days ago</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Image Viewer Modal -->
    <div id="imageViewer" class="image-viewer">
        <button onclick="closeImageViewer()" class="image-viewer-close">
            <i class="fas fa-times"></i>
        </button>
        <button onclick="prevImage()" class="image-viewer-prev">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button onclick="nextImage()" class="image-viewer-next">
            <i class="fas fa-chevron-right"></i>
        </button>
        <img id="viewerImage" src="" alt="Property">
    </div>

    <!-- Contact Modal -->
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo esc_html($contact_form_title ? $contact_form_title : 'Contact Agent'); ?></h3>
                <button onclick="closeContactModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form onsubmit="submitContactForm(event)" class="space-y-4">
                <div class="form-group">
                    <label class="form-label"><?php echo esc_html($contact_name_label ? $contact_name_label : 'Your Name'); ?></label>
                    <input type="text" required class="form-input" name="contact_name">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo esc_html($contact_email_label ? $contact_email_label : 'Email'); ?></label>
                    <input type="email" required class="form-input" name="contact_email">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo esc_html($contact_phone_label ? $contact_phone_label : 'Phone'); ?></label>
                    <input type="tel" required class="form-input" name="contact_phone">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo esc_html($contact_message_label ? $contact_message_label : 'Message'); ?></label>
                    <textarea rows="4" required class="form-input form-textarea" name="contact_message"></textarea>
                </div>
                <button type="submit" class="form-submit">
                    <?php echo esc_html($contact_submit_text ? $contact_submit_text : 'Send Message'); ?>
                </button>
            </form>
        </div>
    </div>

    </div>

    <!-- Pass PHP data to JavaScript -->
    <script>
        // Pass gallery images from PHP to JavaScript
        window.galleryImagesFromPHP = [
            <?php
                if (! empty($all_images)) {
                    foreach ($all_images as $index => $image) {
                        echo "'" . esc_js($image) . "'";
                        if ($index < count($all_images) - 1) {
                            echo ',';
                        }
                    }
                } else {
                    echo "'" . esc_js(get_template_directory_uri() . '/assets/images/placeholder-property.jpg') . "'";
                }
            ?>
        ];
    </script>

    <!-- Enqueue Single Property JavaScript -->
    <script src="<?php echo esc_url(RESBS_URL . 'assets/js/single-property.js'); ?>"></script>
    
    <!-- Additional CSS for better filtering -->
    <style>
        .amenity-item[style*="display: none"] {
            display: none !important;
            height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden !important;
        }
        .amenities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
    </style>
    
    <!-- Backup inline filter function -->
    <script>
        // Backup filter function in case external JS doesn't load
        function filterAmenities(category) {
            console.log('Filtering amenities by category:', category);
            
            const items = document.querySelectorAll('.amenity-item');
            const buttons = document.querySelectorAll('.filter-btn');
            
            console.log('Found items:', items.length);
            console.log('Found buttons:', buttons.length);
            
            // Update button styles
            buttons.forEach(btn => {
                btn.classList.remove('active', 'filter-active');
                btn.classList.add('bg-gray-100', 'text-gray-700');
            });
            
            // Find the clicked button and update its style
            const clickedButton = document.querySelector(`[data-filter="${category}"]`);
            if (clickedButton) {
                clickedButton.classList.add('active', 'filter-active');
                clickedButton.classList.remove('bg-gray-100', 'text-gray-700');
                console.log('Updated button style for:', category);
            }
            
            // Filter items
            let visibleCount = 0;
            items.forEach(item => {
                const itemCategory = item.dataset.category;
                console.log('Item:', item.textContent.trim(), 'Category:', itemCategory);
                
                if (category === 'all' || itemCategory === category) {
                    item.style.display = 'block';
                    item.style.visibility = 'visible';
                    visibleCount++;
                } else {
                    item.style.display = 'none';
                    item.style.visibility = 'hidden';
                }
            });
            
            console.log('Visible items after filtering:', visibleCount);
        }
        
        // Test function to verify JavaScript is working
        function testFilter() {
            console.log('JavaScript is working!');
        }
        
        // Make sure function is available globally
        window.filterAmenities = filterAmenities;
        window.testFilter = testFilter;
    </script>

<?php get_footer(); ?>