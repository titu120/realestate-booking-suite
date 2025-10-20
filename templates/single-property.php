<?php
    /**
     * Single Property Template - Dynamic Version
     * Converts static HTML to dynamic WordPress content
     */

     get_header();

    // Get the current property ID
    $property_id = get_the_ID();

    // Helper function to safely handle meta fields
    function resbs_safe_meta($value)
    {
        if (is_array($value)) {
            return array_filter($value);
        } elseif (is_string($value)) {
            return array_filter(explode(',', $value));
        }
        return [];
    }

    // Helper function to get similar properties
    function resbs_get_similar_properties($property_id, $limit = 4)
    {
        // Get current property data
        $property_type   = get_post_meta($property_id, '_property_type', true);
        $property_status = get_post_meta($property_id, '_property_status', true);
        $city            = get_post_meta($property_id, '_property_city', true);
        $price           = get_post_meta($property_id, '_property_price', true);

        // Build meta query for similar properties
        $meta_query = [
            'relation' => 'OR',
            // Same property type
            [
                'key'     => '_property_type',
                'value'   => $property_type,
                'compare' => '=',
            ],
            // Same city
            [
                'key'     => '_property_city',
                'value'   => $city,
                'compare' => '=',
            ],
            // Same status
            [
                'key'     => '_property_status',
                'value'   => $property_status,
                'compare' => '=',
            ],
        ];

        // Add price range if price is available and numeric
        if ($price && is_numeric($price) && $price > 0) {
            $meta_query[] = [
                'key'     => '_property_price',
                'value'   => [
                    floatval($price) * 0.8, // 20% less
                    floatval($price) * 1.2, // 20% more
                ],
                'compare' => 'BETWEEN',
                'type'    => 'NUMERIC',
            ];
        }

        // Get similar properties with better criteria
        $similar_properties = get_posts([
            'post_type'      => 'property',
            'posts_per_page' => $limit,
            'post__not_in'   => [$property_id],
            'meta_query'     => $meta_query,
            'orderby'        => 'rand', // Randomize to show different properties
        ]);

        // If no similar properties found, get any other properties with better fallback
        if (empty($similar_properties)) {
            $similar_properties = get_posts([
                'post_type'      => 'property',
                'posts_per_page' => $limit,
                'post__not_in'   => [$property_id],
                'orderby'        => 'rand',
            ]);
        }

        // If still no properties, get any property including current one (as last resort)
        if (empty($similar_properties)) {
            $similar_properties = get_posts([
                'post_type'      => 'property',
                'posts_per_page' => $limit,
                'orderby'        => 'rand',
            ]);
        }

        return $similar_properties;
    }

    // Get all property meta fields with fallbacks
    $price                    = get_post_meta($property_id, '_property_price', true);
    $price_per_sqft           = get_post_meta($property_id, '_property_price_per_sqft', true);
    $price_note               = get_post_meta($property_id, '_property_price_note', true);
    $call_for_price           = get_post_meta($property_id, '_property_call_for_price', true);
    $bedrooms                 = get_post_meta($property_id, '_property_bedrooms', true);
    $bathrooms                = get_post_meta($property_id, '_property_bathrooms', true);
    $half_baths               = get_post_meta($property_id, '_property_half_baths', true);
    $total_rooms              = get_post_meta($property_id, '_property_total_rooms', true);
    $floors                   = get_post_meta($property_id, '_property_floors', true);
    $floor_level              = get_post_meta($property_id, '_property_floor_level', true);
    $area                     = get_post_meta($property_id, '_property_area_sqft', true);
    $lot_size                 = get_post_meta($property_id, '_property_lot_size_sqft', true);
    $year_built               = get_post_meta($property_id, '_property_year_built', true);
    $year_remodeled           = get_post_meta($property_id, '_property_year_remodeled', true);
    $latitude                 = get_post_meta($property_id, '_property_latitude', true);
    $longitude                = get_post_meta($property_id, '_property_longitude', true);
    $gallery                  = get_post_meta($property_id, '_property_gallery', true);
    $floor_plans              = get_post_meta($property_id, '_property_floor_plans', true);
    $virtual_tour             = get_post_meta($property_id, '_property_virtual_tour', true);
    $virtual_tour_title       = get_post_meta($property_id, '_property_virtual_tour_title', true);
    $virtual_tour_description = get_post_meta($property_id, '_property_virtual_tour_description', true);
    $virtual_tour_button_text = get_post_meta($property_id, '_property_virtual_tour_button_text', true);
    $video_url                = get_post_meta($property_id, '_property_video_url', true);
    $video_embed              = get_post_meta($property_id, '_property_video_embed', true);
    $map_iframe               = get_post_meta($property_id, '_property_map_iframe', true);

    // Get nearby features
    $nearby_schools     = get_post_meta($property_id, '_property_nearby_schools', true);
    $nearby_shopping    = get_post_meta($property_id, '_property_nearby_shopping', true);
    $nearby_restaurants = get_post_meta($property_id, '_property_nearby_restaurants', true);

    // Debug: Log video and virtual tour data
    if (current_user_can('manage_options')) {
        error_log('Map iframe data: ' . ($map_iframe ? substr($map_iframe, 0, 100) . '...' : 'EMPTY'));
        error_log('Video URL: ' . ($video_url ? $video_url : 'EMPTY'));
        error_log('Video embed: ' . ($video_embed ? substr($video_embed, 0, 100) . '...' : 'EMPTY'));
        error_log('Virtual tour: ' . ($virtual_tour ? $virtual_tour : 'EMPTY'));
    }
    $description       = get_post_meta($property_id, '_property_description', true);
    $features          = get_post_meta($property_id, '_property_features', true);
    $amenities         = get_post_meta($property_id, '_property_amenities', true);
    $parking           = get_post_meta($property_id, '_property_parking', true);
    $heating           = get_post_meta($property_id, '_property_heating', true);
    $cooling           = get_post_meta($property_id, '_property_cooling', true);
    $basement          = get_post_meta($property_id, '_property_basement', true);
    $roof              = get_post_meta($property_id, '_property_roof', true);
    $exterior_material = get_post_meta($property_id, '_property_exterior_material', true);
    $floor_covering    = get_post_meta($property_id, '_property_floor_covering', true);

    // Get property status, type, and condition
    $property_status    = get_post_meta($property_id, '_property_status', true);
    $property_type      = get_post_meta($property_id, '_property_type', true);
    $property_condition = get_post_meta($property_id, '_property_condition', true);

    // Get location data
    $address = get_post_meta($property_id, '_property_address', true);
    $city    = get_post_meta($property_id, '_property_city', true);
    $state   = get_post_meta($property_id, '_property_state', true);
    $zip     = get_post_meta($property_id, '_property_zip', true);

    // Get agent data
    $agent_id              = get_post_meta($property_id, '_property_agent', true);
    $agent_name            = get_post_meta($property_id, '_property_agent_name', true);
    $agent_phone           = get_post_meta($property_id, '_property_agent_phone', true);
    $agent_email           = get_post_meta($property_id, '_property_agent_email', true);
    $agent_photo           = get_post_meta($property_id, '_property_agent_photo', true);
    $agent_properties_sold = get_post_meta($property_id, '_property_agent_properties_sold', true);
    $agent_experience      = get_post_meta($property_id, '_property_agent_experience', true);

    // Get tour/booking data
    $tour_duration           = get_post_meta($property_id, '_property_tour_duration', true);
    $tour_group_size         = get_post_meta($property_id, '_property_tour_group_size', true);
    $tour_safety             = get_post_meta($property_id, '_property_tour_safety', true);
    $available_times         = get_post_meta($property_id, '_property_available_times', true);
    $agent_response_time     = get_post_meta($property_id, '_property_agent_response_time', true);
    $agent_rating            = get_post_meta($property_id, '_property_agent_rating', true);
    $agent_reviews           = get_post_meta($property_id, '_property_agent_reviews', true);
    $agent_send_message_text = get_post_meta($property_id, '_property_agent_send_message_text', true);

    // Contact Form Dynamic Fields
    $contact_form_title      = get_post_meta($property_id, '_property_contact_form_title', true);
    $contact_name_label      = get_post_meta($property_id, '_property_contact_name_label', true);
    $contact_email_label     = get_post_meta($property_id, '_property_contact_email_label', true);
    $contact_phone_label     = get_post_meta($property_id, '_property_contact_phone_label', true);
    $contact_message_label   = get_post_meta($property_id, '_property_contact_message_label', true);
    $contact_success_message = get_post_meta($property_id, '_property_contact_success_message', true);
    $contact_submit_text     = get_post_meta($property_id, '_property_contact_submit_text', true);

    // Mortgage Calculator Dynamic Fields
    $mortgage_calculator_title      = get_post_meta($property_id, '_property_mortgage_calculator_title', true);
    $mortgage_property_price_label  = get_post_meta($property_id, '_property_mortgage_property_price_label', true);
    $mortgage_down_payment_label    = get_post_meta($property_id, '_property_mortgage_down_payment_label', true);
    $mortgage_interest_rate_label   = get_post_meta($property_id, '_property_mortgage_interest_rate_label', true);
    $mortgage_loan_term_label       = get_post_meta($property_id, '_property_mortgage_loan_term_label', true);
    $mortgage_monthly_payment_label = get_post_meta($property_id, '_property_mortgage_monthly_payment_label', true);
    $mortgage_default_down_payment  = get_post_meta($property_id, '_property_mortgage_default_down_payment', true);
    $mortgage_default_interest_rate = get_post_meta($property_id, '_property_mortgage_default_interest_rate', true);
    $mortgage_default_loan_term     = get_post_meta($property_id, '_property_mortgage_default_loan_term', true);
    $mortgage_loan_terms            = get_post_meta($property_id, '_property_mortgage_loan_terms', true);
    $mortgage_disclaimer_text       = get_post_meta($property_id, '_property_mortgage_disclaimer_text', true);

    // Tour Information Fields
    $tour_duration   = get_post_meta($property_id, '_property_tour_duration', true);
    $tour_group_size = get_post_meta($property_id, '_property_tour_group_size', true);
    $tour_safety     = get_post_meta($property_id, '_property_tour_safety', true);

    // Get gallery images with proper URL conversion
    $gallery_images = [];

    if (is_array($gallery)) {
        $gallery_ids = array_filter($gallery);
    } elseif (is_string($gallery) && ! empty($gallery)) {
        $gallery_ids = array_filter(explode(',', $gallery));
    } else {
        $gallery_ids = [];
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
    $formatted_price          = $price ? '$' . number_format($price) : __('Price on Request', 'realestate-booking-suite');
    $price_per_sqft_formatted = $price_per_sqft ? '$' . number_format($price_per_sqft) . ' ' . __('/sq ft', 'realestate-booking-suite') : '';

    // Format location
    $full_address = trim($address . ', ' . $city . ', ' . $state . ' ' . $zip, ', ');

    // Get property title
    $property_title = get_the_title();
?>






<div class="single-property-container tw:bg-gray-50">
    <div class="tw:container tw:mx-auto tw:px-4 tw:py-8">

        <div class="tw:grid tw:grid-cols-1 tw:lg:grid-cols-3 tw:gap-8">
            <!-- Main Content -->
            <div class="tw:lg:col-span-2">
                <!-- Property Header -->
                <div class="tw:bg-white tw:rounded-xl tw:shadow-sm tw:p-6 tw:mb-6">
                    <div class="tw:flex tw:flex-col tw:md:flex-row tw:md:items-center tw:justify-between tw:mb-4">
                        <div>
                            <div class="tw:flex tw:items-center tw:space-x-2 tw:mb-2">
                                <span class="tw:bg-emerald-500 tw:text-white tw:px-3 tw:py-1 tw:rounded-full tw:text-sm tw:font-semibold badge"><?php echo esc_html($property_status); ?></span>
                                <?php if (get_post_meta($property_id, '_property_featured', true)): ?>
                                <span class="tw:bg-blue-500 tw:text-white tw:px-3 tw:py-1 tw:rounded-full tw:text-sm tw:font-semibold"><?php esc_html_e('Featured', 'realestate-booking-suite'); ?></span>
                                <?php endif; ?>
                                <?php if (get_post_meta($property_id, '_property_hot_deal', true)): ?>
                                <span class="tw:bg-orange-500 tw:text-white tw:px-3 tw:py-1 tw:rounded-full tw:text-sm tw:font-semibold"><?php esc_html_e('Hot Deal', 'realestate-booking-suite'); ?></span>
                                <?php endif; ?>
                            </div>
                            <h1 class="tw:text-3xl tw:md:text-4xl tw:font-bold tw:text-gray-800 tw:mb-2"><?php echo esc_html($property_title); ?></h1>
                            <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-4 tw:mb-2">
                                <p class="tw:text-gray-600 tw:flex tw:items-center">
                                    <i class="fas fa-map-marker-alt tw:text-emerald-500 tw:mr-2"></i>
                                    <?php echo esc_html($full_address ?: __('Location not specified', 'realestate-booking-suite')); ?>
                                </p>
                                <span class="tw:bg-blue-100 tw:text-blue-800 tw:px-3 tw:py-1 tw:rounded-full tw:text-sm tw:font-medium">
                                    <i class="fas fa-home tw:mr-1"></i><?php echo esc_html($property_type); ?>
                                </span>
                                <span class="tw:bg-green-100 tw:text-green-800 tw:px-3 tw:py-1 tw:rounded-full tw:text-sm tw:font-medium">
                                    <i class="fas fa-star tw:mr-1"></i><?php echo esc_html($property_condition); ?>
                                </span>
                            </div>
                        </div>
                        <div class="tw:mt-4 tw:md:mt-0">
                            <p class="tw:text-4xl tw:font-bold tw:text-emerald-500"><?php echo esc_html($formatted_price); ?></p>
                            <?php if ($price_per_sqft_formatted): ?>
                            <p class="tw:text-gray-500 tw:text-sm"><?php echo esc_html($price_per_sqft_formatted); ?></p>
                            <?php endif; ?>

                            <!-- Price Note and Call for Price at bottom -->
                            <?php if ($price_note): ?>
                            <div class="tw:bg-blue-50 tw:border-l-4  tw:border-gray-200lue-500 tw:p-3 tw:rounded tw:mt-3">
                                <p class="tw:text-blue-800 tw:text-sm"><i class="fas fa-info-circle tw:mr-2"></i><?php echo esc_html($price_note); ?></p>
                            </div>
                            <?php endif; ?>

                            <?php if ($call_for_price): ?>
                            <div class="tw:bg-orange-50 tw:border-l-4 tw:border-orange-500 tw:p-3 tw:rounded tw:mt-3">
                                <p class="tw:text-orange-800 tw:text-sm"><i class="fas fa-phone tw:mr-2"></i><?php esc_html_e('Call for pricing information', 'realestate-booking-suite'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tw:flex tw:flex-wrap tw:gap-3 no-print">
                        <button onclick="shareProperty()" class=" tw:flex tw:items-center tw:space-x-2 tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg hover:tw:bg-gray-50 tw:transition" data-tooltip="<?php esc_attr_e('Share Property', 'realestate-booking-suite'); ?>">
                            <i class="fas fa-share-alt tw:text-gray-600"></i>
                            <span class="tw:text-gray-700"><?php esc_html_e('Share', 'realestate-booking-suite'); ?></span>
                        </button>
                        <button onclick="printPage()" class=" tw:flex tw:items-center tw:space-x-2 tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg hover:tw:bg-gray-50 tw:transition" data-tooltip="<?php esc_attr_e('Print Details', 'realestate-booking-suite'); ?>">
                            <i class="fas fa-print tw:text-gray-600"></i>
                            <span class="tw:text-gray-700"><?php esc_html_e('Print', 'realestate-booking-suite'); ?></span>
                        </button>
                    </div>
                </div>

                <!-- Image Gallery -->
                <div class="tw:bg-white tw:rounded-xl tw:shadow-sm tw:p-4 tw:mb-6">
                    <?php if (! empty($gallery_images)): ?>
                    <div class="tw:grid tw:grid-cols-4 tw:gap-3">
                        <?php foreach ($gallery_images as $index => $image_url): ?>
                            <?php if ($index === 0): ?>
                            <div class="tw:col-span-4 tw:md:col-span-2 tw:md:row-span-2">
                                <img src="<?php echo esc_url($image_url); ?>" alt="Property Main" class="tw:w-full tw:h-full tw:object-cover tw:rounded-lg tw:cursor-pointer gallery-img" onclick="openImageViewer(<?php echo $index; ?>)">
                            </div>
                            <?php elseif ($index < 5): ?>
                            <div class="tw:col-span-2 tw:md:col-span-1">
                                <img src="<?php echo esc_url($image_url); ?>" alt="Property" class="tw:w-full tw:h-full tw:object-cover tw:rounded-lg tw:cursor-pointer gallery-img" onclick="openImageViewer(<?php echo $index; ?>)">
                            </div>
                            <?php elseif ($index === 5): ?>
                            <div class="tw:col-span-2 tw:md:col-span-1 tw:relative">
                                <img src="<?php echo esc_url($image_url); ?>" alt="Property" class="tw:w-full tw:h-full tw:object-cover tw:rounded-lg tw:cursor-pointer gallery-img" onclick="openImageViewer(<?php echo $index; ?>)">
                                <div class="tw:absolute tw:inset-0 tw:bg-black tw:bg-opacity-50 tw:rounded-lg tw:flex tw:items-center tw:justify-center tw:cursor-pointer" onclick="openImageViewer(<?php echo $index; ?>)">
                                    <span class="tw:text-white tw:text-xl tw:font-semibold">+<?php echo count($gallery_images) - 5; ?> More</span>
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="tw:text-center tw:py-12">
                        <i class="fas fa-image tw:text-gray-300 tw:text-6xl tw:mb-4"></i>
                        <p class="tw:text-gray-500"><?php esc_html_e('No images available for this property', 'realestate-booking-suite'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Key Features -->
                <div class="tw:bg-white tw:rounded-xl tw:shadow-sm tw:p-6 tw:mb-6">
                    <h2 class="tw:text-2xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Key Features', 'realestate-booking-suite'); ?></h2>
                    <div class="tw:grid tw:grid-cols-2 tw:md:grid-cols-4 tw:gap-4">
                        <div class="tw:text-center tw:p-4 tw:bg-gray-50 tw:rounded-lg">
                            <i class="fas fa-bed tw:text-3xl tw:text-emerald-500 tw:mb-2"></i>
                            <p class="tw:text-2xl tw:font-bold tw:text-gray-800"><?php echo esc_html($bedrooms ?: '0'); ?></p>
                            <p class="tw:text-gray-600 tw:text-sm"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></p>
                        </div>
                        <div class="tw:text-center tw:p-4 tw:bg-gray-50 tw:rounded-lg">
                            <i class="fas fa-bath tw:text-3xl tw:text-emerald-500 tw:mb-2"></i>
                            <p class="tw:text-2xl tw:font-bold tw:text-gray-800"><?php echo esc_html($bathrooms ?: '0'); ?></p>
                            <p class="tw:text-gray-600 tw:text-sm"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></p>
                        </div>
                        <div class="tw:text-center tw:p-4 tw:bg-gray-50 tw:rounded-lg">
                            <i class="fas fa-ruler-combined tw:text-3xl tw:text-emerald-500 tw:mb-2"></i>
                            <p class="tw:text-2xl tw:font-bold tw:text-gray-800"><?php echo esc_html($area ?: '0'); ?></p>
                            <p class="tw:text-gray-600 tw:text-sm"><?php esc_html_e('Sq Ft', 'realestate-booking-suite'); ?></p>
                        </div>
                        <div class="tw:text-center tw:p-4 tw:bg-gray-50 tw:rounded-lg">
                            <i class="fas fa-car tw:text-3xl tw:text-emerald-500 tw:mb-2"></i>
                            <p class="tw:text-2xl tw:font-bold tw:text-gray-800"><?php echo esc_html($parking ?: '0'); ?></p>
                            <p class="tw:text-gray-600 tw:text-sm"><?php esc_html_e('Parking', 'realestate-booking-suite'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="tw:bg-white tw:rounded-xl tw:shadow-sm tw:mb-6 no-print">
                    <div class="tw:flex tw:overflow-x-auto tw:border-b tw:border-gray-200">
                        <button onclick="switchTab('overview')" class="tw-tab-button tw-tab-active tw:px-6 tw:py-4 tw:font-semibold tw:whitespace-nowrap" data-tab="overview">
                            <i class="fas fa-home tw:mr-2"></i><?php esc_html_e('Overview', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('pricing')" class="tw-tab-button tw:px-6 tw:py-4 tw:font-semibold tw:text-gray-600 hover:tw:text-emerald-500 tw:whitespace-nowrap" data-tab="pricing">
                            <i class="fas fa-dollar-sign tw:mr-2"></i><?php esc_html_e('Pricing', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('specifications')" class="tw-tab-button tw:px-6 tw:py-4 tw:font-semibold tw:text-gray-600 hover:tw:text-emerald-500 tw:whitespace-nowrap" data-tab="specifications">
                            <i class="fas fa-list tw:mr-2"></i><?php esc_html_e('Specifications', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('location')" class="tw-tab-button tw:px-6 tw:py-4 tw:font-semibold tw:text-gray-600 hover:tw:text-emerald-500 tw:whitespace-nowrap" data-tab="location">
                            <i class="fas fa-map-marker-alt tw:mr-2"></i><?php esc_html_e('Location', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('features')" class="tw-tab-button tw:px-6 tw:py-4 tw:font-semibold tw:text-gray-600 hover:tw:text-emerald-500 tw:whitespace-nowrap" data-tab="features">
                            <i class="fas fa-check-circle tw:mr-2"></i><?php esc_html_e('Features', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('media')" class="tw-tab-button tw:px-6 tw:py-4 tw:font-semibold tw:text-gray-600 hover:tw:text-emerald-500 tw:whitespace-nowrap" data-tab="media">
                            <i class="fas fa-image tw:mr-2"></i><?php esc_html_e('Media', 'realestate-booking-suite'); ?>
                        </button>
                        <?php if (! empty($floor_plans)): ?>
                        <button onclick="switchTab('floorplan')" class="tw-tab-button tw:px-6 tw:py-4 tw:font-semibold tw:text-gray-600 hover:tw:text-emerald-500 tw:whitespace-nowrap" data-tab="floorplan">
                            <i class="fas fa-vector-square tw:mr-2"></i><?php esc_html_e('Floor Plan', 'realestate-booking-suite'); ?>
                        </button>
                        <?php endif; ?>
                        <button onclick="switchTab('booking')" class="tw-tab-button tw:px-6 tw:py-4 tw:font-semibold tw:text-gray-600 hover:tw:text-emerald-500 tw:whitespace-nowrap" data-tab="booking">
                            <i class="fas fa-calendar tw:mr-2"></i><?php esc_html_e('Booking', 'realestate-booking-suite'); ?>
                        </button>
                    </div>

                    <!-- Tab Contents -->
                    <div class="tw:p-6">
                        <!-- Overview Tab -->
                        <div id="overview-tab" class="tw-tab-content">
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Property Description', 'realestate-booking-suite'); ?></h3>
                            <div class="tw:text-gray-600 tw:space-y-4 tw:leading-relaxed">
                                <?php
                                    // Get the post content (main description from editor)
                                    $post_content = get_post_field('post_content', $property_id);
                                if ($post_content): ?>
                                    <?php echo wp_kses_post(wpautop($post_content)); ?>
                                <?php elseif ($description): ?>
                                    <?php echo wp_kses_post(wpautop($description)); ?>
                                <?php else: ?>
                                    <p><?php esc_html_e('Welcome to this stunning property. This exceptional property offers a perfect blend of luxury, comfort, and modern design.', 'realestate-booking-suite'); ?></p>
                                <?php endif; ?>
                            </div>

                            <?php if ($virtual_tour): ?>
                            <div class="tw:mt-6 tw:p-4 tw:bg-emerald-50 tw:border-l-4 tw:border-emerald-500 tw:rounded">
                                <p class="tw:text-emerald-800"><i class="fas fa-info-circle tw:mr-2"></i><strong><?php esc_html_e('Virtual Tour Available:', 'realestate-booking-suite'); ?></strong> <a href="<?php echo esc_url($virtual_tour); ?>" target="_blank" class="tw:underline"><?php esc_html_e('Schedule a 3D virtual walkthrough', 'realestate-booking-suite'); ?></a><?php esc_html_e('of this property at your convenience.', 'realestate-booking-suite'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pricing Tab -->
                        <div id="pricing-tab" class="tw-tab-content hidden">
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Pricing Information', 'realestate-booking-suite'); ?></h3>
                            <div class="tw:grid tw:md:grid-cols-2 tw:gap-6">
                                <div class="tw:space-y-4">
                                    <div class="tw:bg-emerald-50 tw:rounded-lg tw:p-6">
                                        <h4 class="tw:text-2xl tw:font-bold tw:text-emerald-600 tw:mb-2"><?php echo esc_html($formatted_price); ?></h4>
                                        <p class="tw:text-gray-600"><?php echo esc_html($property_status); ?></p>
                                        <?php if ($price_per_sqft_formatted): ?>
                                        <p class="tw:text-sm tw:text-gray-500 tw:mt-2"><?php echo esc_html($price_per_sqft_formatted); ?></p>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($price_note): ?>
                                    <div class="tw:bg-blue-50 tw:border-l-4 tw:border-b tw:border-gray-200lue-500 tw:p-4 tw:rounded">
                                        <p class="tw:text-blue-800"><i class="fas fa-info-circle tw:mr-2"></i><?php echo esc_html($price_note); ?></p>
                                    </div>
                                    <?php endif; ?>

                                    <?php if ($call_for_price): ?>
                                    <div class="tw:bg-orange-50 tw:border-l-4 tw:border-orange-500 tw:p-4 tw:rounded">
                                        <p class="tw:text-orange-800"><i class="fas fa-phone tw:mr-2"></i><?php esc_html_e('Call for pricing information', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="tw:space-y-4">
                                    <div class="tw:bg-gray-50 tw:rounded-lg tw:p-4">
                                        <h5 class="tw:font-semibold tw:text-gray-800 tw:mb-3"><?php esc_html_e('Price Breakdown', 'realestate-booking-suite'); ?></h5>
                                        <div class="tw:space-y-2 tw:text-sm">
                                            <div class="tw:flex tw:justify-between">
                                                <span class="tw:text-gray-600"><?php esc_html_e('List Price:', 'realestate-booking-suite'); ?></span>
                                                <span class="tw:font-semibold"><?php echo esc_html($formatted_price); ?></span>
                                            </div>
                                            <?php if ($price_per_sqft_formatted): ?>
                                            <div class="tw:flex tw:justify-between">
                                                <span class="tw:text-gray-600"><?php esc_html_e('Price per sq ft:', 'realestate-booking-suite'); ?></span>
                                                <span class="tw:font-semibold"><?php echo esc_html($price_per_sqft_formatted); ?></span>
                                            </div>
                                            <?php endif; ?>
                                            <div class="tw:flex tw:justify-between">
                                                <span class="tw:text-gray-600"><?php esc_html_e('Property Type:', 'realestate-booking-suite'); ?></span>
                                                <span class="tw:font-semibold"><?php echo esc_html($property_type); ?></span>
                                            </div>
                                            <div class="tw:flex tw:justify-between">
                                                <span class="tw:text-gray-600"><?php esc_html_e('Status:', 'realestate-booking-suite'); ?></span>
                                                <span class="tw:font-semibold tw:text-emerald-600"><?php echo esc_html($property_status); ?></span>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>

                        <!-- Specifications Tab -->
                        <div id="specifications-tab" class="tw-tab-content hidden">
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Property Specifications', 'realestate-booking-suite'); ?></h3>
                            <div class="tw:grid tw:md:grid-cols-2 tw:gap-6">
                                <div class="tw:space-y-3">
                                    <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-3"><?php esc_html_e('Basic Information', 'realestate-booking-suite'); ?></h4>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200 ">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Property ID:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($property_id); ?></span>
                                    </div>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Property Type:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($property_type); ?></span>
                                    </div>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Property Condition:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($property_condition); ?></span>
                                    </div>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Status:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-emerald-600"><?php echo esc_html($property_status); ?></span>
                                    </div>
                                    <?php if ($year_built): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Year Built:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($year_built); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($year_remodeled): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Year Remodeled:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($year_remodeled); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="tw:space-y-3">
                                    <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-3"><?php esc_html_e('Room Details', 'realestate-booking-suite'); ?></h4>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Bedrooms:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($bedrooms ?: '0'); ?></span>
                                    </div>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Bathrooms:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($bathrooms ?: '0'); ?></span>
                                    </div>
                                    <?php if ($half_baths): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Half Baths:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($half_baths); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($total_rooms): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Total Rooms:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($total_rooms); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floors): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Floors:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($floors); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floor_level): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Floor Level:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($floor_level); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="tw:mt-6 tw:grid tw:md:grid-cols-2 tw:gap-6">
                                <div class="tw:space-y-3">
                                    <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-3"><?php esc_html_e('Size & Area', 'realestate-booking-suite'); ?></h4>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Area:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($area ?: '0'); ?> sq ft</span>
                                    </div>
                                    <?php if ($lot_size): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Lot Size:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($lot_size); ?> sq ft</span>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="tw:space-y-3">
                                    <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-3"><?php esc_html_e('Building Features', 'realestate-booking-suite'); ?></h4>
                                    <?php if ($heating): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Heating:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($heating); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($cooling): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Cooling:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($cooling); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($roof): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Roof:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($roof); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($exterior_material): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Exterior:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($exterior_material); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floor_covering): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Flooring:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($floor_covering); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($basement): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Basement:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($basement); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($parking): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Parking:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($parking); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Details Tab -->
                        <div id="details-tab" class="tw-tab-content hidden">
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Property Details', 'realestate-booking-suite'); ?></h3>
                            <div class="tw:grid tw:md:grid-cols-2 tw:gap-6">
                                <div class="tw:space-y-3">
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Property ID:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($property_id); ?></span>
                                    </div>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Property Type:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($property_type); ?></span>
                                    </div>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Property Condition:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($property_condition); ?></span>
                                    </div>
                                    <?php if ($half_baths): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Half Baths:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($half_baths); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($total_rooms): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Total Rooms:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($total_rooms); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($year_built): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Year Built:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($year_built); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($lot_size): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Lot Size:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($lot_size); ?> sq ft</span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floors): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Stories:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($floors); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floor_level): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Floor Level:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($floor_level); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="tw:space-y-3">
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Status:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-emerald-600"><?php echo esc_html($property_status); ?></span>
                                    </div>
                                    <?php if ($heating): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Heating:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($heating); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($cooling): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Cooling:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($cooling); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($roof): ?>
                                    <div class="tw:flex tw:justify-between tw:py-2 tw:border-b tw:border-gray-200">
                                        <span class="tw:text-gray-600"><?php esc_html_e('Roof:', 'realestate-booking-suite'); ?></span>
                                        <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($roof); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Features Tab -->
                        <div id="features-tab" class="tw-tab-content hidden">
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Property Features & Amenities', 'realestate-booking-suite'); ?></h3>

                            <!-- Filter Buttons -->
                            <div class="tw:flex tw:flex-wrap tw:gap-2 tw:mb-6 no-print">
                                <button onclick="filterAmenities('all')" class="filter-btn filter-active tw:px-4 tw:py-2 tw:rounded-lg tw:font-semibold tw:text-sm tw:transition" data-filter="all"><?php esc_html_e('All', 'realestate-booking-suite'); ?></button>
                                <button onclick="filterAmenities('interior')" class="filter-btn tw:px-4 tw:py-2 tw:bg-gray-100 tw:text-gray-700 tw:rounded-lg tw:font-semibold tw:text-sm hover:tw:bg-gray-200 tw:transition" data-filter="interior"><?php esc_html_e('Interior', 'realestate-booking-suite'); ?></button>
                                <button onclick="filterAmenities('amenities')" class="filter-btn tw:px-4 tw:py-2 tw:bg-gray-100 tw:text-gray-700 tw:rounded-lg tw:font-semibold tw:text-sm hover:tw:bg-gray-200 tw:transition" data-filter="amenities"><?php esc_html_e('Amenities', 'realestate-booking-suite'); ?></button>
                            </div>

                  
                            <div class="tw:mb-8" data-category="interior">
                                <h4 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mb-4 tw:flex tw:items-center">
                                    <i class="fas fa-home tw:text-emerald-500 tw:mr-2"></i><?php esc_html_e('Property Features', 'realestate-booking-suite'); ?>
                                </h4>

                                <div class="tw:grid tw:md:grid-cols-2 tw:lg:grid-cols-3 tw:gap-4" id="featuresContainer">
                                  


                                    <!-- Custom Features from Database -->
                                    <?php if (! empty($features_list)): ?>
                                        <?php foreach ($features_list as $feature): ?>
                                        <div class="amenity-item tw:p-4 tw:bg-emerald-50 tw:rounded-lg tw:border-l-4 tw:border-emerald-500" data-category="interior">
                                            <i class="fas fa-star tw:text-emerald-500 tw:mr-2"></i>
                                            <span class="tw:text-gray-700 tw:font-medium"><?php echo esc_html($feature); ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!--                                 <?php esc_html_e('Property Amenities', 'realestate-booking-suite'); ?> Section -->
                            <div class="tw:mb-8" data-category="amenities">
                                <h4 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mb-4 tw:flex tw:items-center">
                                    <i class="fas fa-concierge-bell tw:text-emerald-500 tw:mr-2"></i><?php esc_html_e('Property Amenities', 'realestate-booking-suite'); ?>
                                </h4>

                                <div class="tw:grid tw:md:grid-cols-2 tw:lg:grid-cols-3 tw:gap-4" id="amenitiesContainer">
                                    <!-- Common Amenities -->


                                    <!-- Custom Amenities from Database -->
                                    <?php if (! empty($amenities_list)): ?>
                                        <?php foreach ($amenities_list as $amenity): ?>
                                        <div class="amenity-item tw:p-4 tw:bg-orange-50 tw:rounded-lg tw:border-l-4 tw:border-orange-500" data-category="amenities">
                                            <i class="fas fa-star tw:text-orange-500 tw:mr-2"></i>
                                            <span class="tw:text-gray-700 tw:font-medium"><?php echo esc_html($amenity); ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Features & Amenities Tab -->
                        <div id="features-tab" class="tab-content hidden">
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-6"><?php esc_html_e('Property Features', 'realestate-booking-suite'); ?> & Amenities</h3>

                            <!-- Features Section -->
                            <?php if (! empty($features_list)): ?>
                            <div class="tw:mb-8">
                                <h4 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mb-4 tw:flex tw:items-center">
                                    <i class="fas fa-home tw:text-emerald-500 tw:mr-2"></i><?php esc_html_e('Property Features', 'realestate-booking-suite'); ?>
                                </h4>

                                <div class="tw:grid tw:md:grid-cols-2 tw:lg:grid-cols-3 tw:gap-4">
                                    <?php foreach ($features_list as $feature): ?>
                                    <div class="amenity-item tw:p-4 tw:bg-emerald-50 tw:rounded-lg tw:border-l-4 tw:border-emerald-500 hover:tw:bg-emerald-100 tw:transition-colors">
                                        <i class="fas fa-check-circle tw:text-emerald-500 tw:mr-2"></i>
                                        <span class="tw:text-gray-700 tw:font-medium"><?php echo esc_html(trim($feature)); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Amenities Section -->
                            <?php if (! empty($amenities_list)): ?>
                            <div class="tw:mb-8">
                                <h4 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mb-4 tw:flex tw:items-center">
                                    <i class="fas fa-concierge-bell tw:text-orange-500 tw:mr-2"></i><?php esc_html_e('Property Amenities', 'realestate-booking-suite'); ?>
                                </h4>

                                <div class="tw:grid tw:md:grid-cols-2 tw:lg:grid-cols-3 tw:gap-4">
                                    <?php foreach ($amenities_list as $amenity): ?>
                                    <div class="amenity-item tw:p-4 tw:bg-orange-50 tw:rounded-lg tw:border-l-4 tw:border-orange-500 hover:tw:bg-orange-100 tw:transition-colors">
                                        <i class="fas fa-check-circle tw:text-orange-500 tw:mr-2"></i>
                                        <span class="tw:text-gray-700 tw:font-medium"><?php echo esc_html(trim($amenity)); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- No Features/Amenities Message -->
                            <?php if (empty($features_list) && empty($amenities_list)): ?>
                            <div class="tw:text-center tw:py-12">
                                <i class="fas fa-info-circle tw:text-gray-400 tw:text-4xl tw:mb-4"></i>
                                <p class="tw:text-gray-500 tw:text-lg"><?php esc_html_e('No features or amenities have been added to this property yet.', 'realestate-booking-suite'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Media Tab -->
                        <div id="media-tab" class="tw-tab-content hidden">
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Property Media', 'realestate-booking-suite'); ?></h3>

                            <!-- Image Gallery -->
                            <?php if (! empty($gallery_images)): ?>
                            <div class="tw:mb-8">
                                <h4 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Photo Gallery', 'realestate-booking-suite'); ?></h4>
                                <div class="tw:grid tw:grid-cols-2 tw:md:grid-cols-3 tw:lg:grid-cols-4 tw:gap-4">
                                    <?php foreach ($gallery_images as $index => $image_url): ?>
                                    <div class="tw:relative tw:group tw:cursor-pointer" onclick="openImageViewer(<?php echo $index; ?>)">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="Property Image<?php echo $index + 1; ?>" class="tw:w-full tw:h-48 tw:object-cover tw:rounded-lg gallery-img">
                                        <div class="tw:absolute tw:inset-0 tw:bg-black/0 tw:group-hover:bg-black/30 tw:rounded-lg tw:transition-all tw:duration-300 tw:flex tw:items-center tw:justify-center">
                                            <i class="fas fa-search-plus tw:text-white tw:text-2xl tw:opacity-0 group-hover:tw:opacity-100 tw:transition-opacity"></i>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="tw:text-center tw:py-12 tw:bg-gray-50 tw:rounded-lg">
                                <i class="fas fa-image tw:text-gray-300 tw:text-6xl tw:mb-4"></i>
                                <p class="tw:text-gray-500 tw:text-lg"><?php esc_html_e('No images available for this property', 'realestate-booking-suite'); ?></p>
                            </div>
                            <?php endif; ?>

                            <!-- Video Section -->
                            <?php if ($video_url || $video_embed): ?>
                            <div class="tw:mb-8">
                                <h4 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Property Video', 'realestate-booking-suite'); ?></h4>
                                <div class="tw:bg-gray-100 tw:rounded-lg tw:p-6">
                                    <?php if ($video_embed): ?>
                                        <div class="tw:aspect-video">
                                            <?php
                                                // Allow iframe tags with specific attributes for video embeds
                                                $allowed_html = [
                                                    'iframe' => [
                                                        'src'             => [],
                                                        'width'           => [],
                                                        'height'          => [],
                                                        'style'           => [],
                                                        'allowfullscreen' => [],
                                                        'loading'         => [],
                                                        'referrerpolicy'  => [],
                                                        'frameborder'     => [],
                                                        'scrolling'       => [],
                                                    ],
                                                ];
                                                echo wp_kses($video_embed, $allowed_html);
                                            ?>
                                        </div>
                                    <?php elseif ($video_url): ?>
                                        <div class="tw:aspect-video">
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
                                                        echo '<div class="tw:flex tw:items-center tw:justify-center tw:h-full tw:bg-gray-200 tw:rounded-lg"><p class="tw:text-gray-600">' . esc_html__('Invalid YouTube URL', 'realestate-booking-suite') . '</p></div>';
                                                    }
                                                } elseif (strpos($video_url, 'vimeo.com') !== false) {
                                                    $video_id = substr($video_url, strrpos($video_url, '/') + 1);
                                                    // Remove any query parameters
                                                    $video_id = strtok($video_id, '?');
                                                    if ($video_id) {
                                                        echo '<iframe width="100%" height="100%" src="https://player.vimeo.com/video/' . esc_attr($video_id) . '?title=0&byline=0&portrait=0" frameborder="0" allowfullscreen allow="autoplay; fullscreen; picture-in-picture"></iframe>';
                                                    } else {
                                                        echo '<div class="tw:flex tw:items-center tw:justify-center tw:h-full tw:bg-gray-200 tw:rounded-lg"><p class="tw:text-gray-600">' . esc_html__('Invalid Vimeo URL', 'realestate-booking-suite') . '</p></div>';
                                                    }
                                                } else {
                                                    echo '<video controls class="tw:w-full tw:h-full tw:rounded-lg"><source src="' . esc_url($video_url) . '" type="video/mp4">' . esc_html__('Your browser does not support the video tag.', 'realestate-booking-suite') . '</video>';
                                                }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Virtual Tour -->
                            <?php if ($virtual_tour): ?>
                            <div class="tw:mb-8">
                                <h4 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Virtual Tour', 'realestate-booking-suite'); ?></h4>
                                <div class="tw:bg-gradient-to-r tw:from-emerald-500 tw:to-teal-500 tw:text-white tw:rounded-lg tw:p-6">
                                    <div class="tw:flex tw:items-center tw:justify-between">
                                        <div>
                                            <h5 class="tw:text-xl tw:font-bold tw:mb-2"><?php echo esc_html($virtual_tour_title ?: __('3D Virtual Walkthrough', 'realestate-booking-suite')); ?></h5>
                                            <p class="tw:text-emerald-100"><?php echo esc_html($virtual_tour_description ?: __('Experience this property from anywhere with our interactive 3D tour.', 'realestate-booking-suite')); ?></p>
                                        </div>
                                        <a href="<?php echo esc_url($virtual_tour); ?>" target="_blank" class="tw:bg-white tw:text-emerald-600 tw:px-6 tw:py-3 tw:rounded-lg tw:font-semibold hover:tw:bg-gray-100 tw:transition">
                                            <i class="fas fa-play tw:mr-2"></i><?php echo esc_html($virtual_tour_button_text ?: __('Start Tour', 'realestate-booking-suite')); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Floor Plans -->
                            <?php if (! empty($floor_plans)): ?>
                            <div class="tw:mb-8">
                                <h4 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Floor Plans', 'realestate-booking-suite'); ?></h4>
                                <div class="tw:bg-gray-100 tw:rounded-lg tw:p-6">
                                    <img src="<?php echo esc_url($floor_plans); ?>" alt="Floor Plan" class="tw:w-full tw:max-w-2xl tw:mx-auto tw:rounded-lg tw:shadow-lg">
                                    <div class="tw:mt-4 tw:flex tw:justify-center tw:gap-4">
                                        <button onclick="downloadFloorPlan()" class="tw:px-6 tw:py-3 tw:bg-emerald-500 tw:text-white tw:rounded-lg hover:tw:bg-emerald-600 tw:transition">
                                            <i class="fas fa-download tw:mr-2"></i><?php esc_html_e('Download Floor Plan', 'realestate-booking-suite'); ?>
                                        </button>
                                        <button onclick="requestCustomPlan()" class="tw:px-6 tw:py-3 tw:bg-gray-700 tw:text-white tw:rounded-lg hover:tw:bg-gray-800 tw:transition">
                                            <i class="fas fa-envelope tw:mr-2"></i><?php esc_html_e('Request Custom Plan', 'realestate-booking-suite'); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        </div>

                        <!-- Floor Plan Tab -->
                        <?php if (! empty($floor_plans)): ?>
                        <div id="floorplan-tab" class="tab-content hidden">
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Floor Plan', 'realestate-booking-suite'); ?></h3>
                            <div class="tw:bg-gray-100 tw:rounded-lg tw:p-8 tw:text-center">
                                <img src="<?php echo esc_url($floor_plans); ?>" alt="<?php esc_attr_e('Floor Plan', 'realestate-booking-suite'); ?>" class="tw:w-full tw:max-w-3xl tw:mx-auto tw:rounded-lg tw:shadow-lg">
                                <div class="tw:mt-4 tw:flex tw:justify-center tw:gap-4 no-print">
                                    <button onclick="downloadFloorPlan()" class="tw:px-6 tw:py-3 tw:bg-emerald-500 tw:text-white tw:rounded-lg hover:tw:bg-emerald-600 tw:transition">
                                        <i class="fas fa-download tw:mr-2"></i><?php esc_html_e('Download Floor Plan', 'realestate-booking-suite'); ?>
                                    </button>
                                    <button onclick="requestCustomPlan()" class="tw:px-6 tw:py-3 tw:bg-gray-700 tw:text-white tw:rounded-lg hover:tw:bg-gray-800 tw:transition">
                                        <i class="fas fa-envelope tw:mr-2"></i><?php esc_html_e('Request Custom Plan', 'realestate-booking-suite'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Location Tab -->
                        <div id="location-tab" class="tw-tab-content hidden">
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Location & Nearby', 'realestate-booking-suite'); ?></h3>



                            <?php if ($map_iframe): ?>
                            <!-- Custom Map Iframe -->
                            <div class="tw:rounded-lg tw:mb-6 tw:overflow-hidden" style="height: 400px; width: 100%;">
                                <?php
                                    // Allow iframe tags with specific attributes for maps
                                    $allowed_html = [
                                        'iframe' => [
                                            'src'             => [],
                                            'width'           => [],
                                            'height'          => [],
                                            'style'           => [],
                                            'allowfullscreen' => [],
                                            'loading'         => [],
                                            'referrerpolicy'  => [],
                                            'frameborder'     => [],
                                            'scrolling'       => [],
                                        ],
                                    ];
                                    echo wp_kses($map_iframe, $allowed_html);
                                ?>
                            </div>
                            <?php elseif ($full_address): ?>
                            <!-- Fallback: Show address and Google Maps link -->
                            <div class="tw:bg-gray-100 tw:rounded-lg tw:p-6 tw:mb-6">
                                <div class="tw:flex tw:items-center tw:justify-between">
                                    <div>
                                        <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-2"><?php esc_html_e('Property Location', 'realestate-booking-suite'); ?></h4>
                                        <p class="tw:text-gray-600 tw:mb-3"><?php echo esc_html($full_address); ?></p>
                                        <a href="https://www.google.com/maps/search/<?php echo urlencode($full_address); ?>"
                                           target="_blank"
                                           class="tw:inline-flex tw:items-center tw:px-4 tw:py-2 tw:bg-emerald-500 tw:text-white tw:rounded-lg hover:tw:bg-emerald-600 tw:transition">
                                            <i class="fas fa-map-marker-alt tw:mr-2"></i>
                                            <?php esc_html_e('View on Google Maps', 'realestate-booking-suite'); ?>
                                        </a>
                                    </div>
                                    <i class="fas fa-map-marker-alt tw:text-emerald-500 tw:text-4xl"></i>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="tw:bg-gray-100 tw:rounded-lg tw:p-8 tw:text-center tw:mb-6">
                                <i class="fas fa-map-marker-alt tw:text-gray-400 tw:text-4xl tw:mb-4"></i>
                                <p class="tw:text-gray-600"><?php esc_html_e('Location information not available', 'realestate-booking-suite'); ?></p>
                                <p class="tw:text-sm tw:text-gray-500 tw:mt-2"><?php esc_html_e('Please add a map iframe or address to display location', 'realestate-booking-suite'); ?></p>
                            </div>
                            <?php endif; ?>

                            <div class="tw:grid tw:md:grid-cols-3 tw:gap-4">
                                <?php if ($nearby_schools): ?>
                                <div class="tw:p-4 tw:bg-blue-50 tw:rounded-lg">
                                    <i class="fas fa-graduation-cap tw:text-blue-500 tw:text-2xl tw:mb-2"></i>
                                    <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-1"><?php esc_html_e('Schools', 'realestate-booking-suite'); ?></h4>
                                    <p class="tw:text-sm tw:text-gray-600"><?php echo esc_html($nearby_schools); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if ($nearby_shopping): ?>
                                <div class="tw:p-4 tw:bg-green-50 tw:rounded-lg">
                                    <i class="fas fa-shopping-cart tw:text-green-500 tw:text-2xl tw:mb-2"></i>
                                    <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-1"><?php esc_html_e('Shopping', 'realestate-booking-suite'); ?></h4>
                                    <p class="tw:text-sm tw:text-gray-600"><?php echo esc_html($nearby_shopping); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if ($nearby_restaurants): ?>
                                <div class="tw:p-4 tw:bg-purple-50 tw:rounded-lg">
                                    <i class="fas fa-utensils tw:text-purple-500 tw:text-2xl tw:mb-2"></i>
                                    <h4 class="tw:font-semibold tw:text-gray-800 tw:mb-1"><?php esc_html_e('Restaurants', 'realestate-booking-suite'); ?></h4>
                                    <p class="tw:text-sm tw:text-gray-600"><?php echo esc_html($nearby_restaurants); ?></p>
                                </div>
                                <?php endif; ?>

                                <?php if (! $nearby_schools && ! $nearby_shopping && ! $nearby_restaurants): ?>
                                <div class="tw:col-span-3 tw:p-8 tw:text-center tw:bg-gray-50 tw:rounded-lg">
                                    <i class="fas fa-map-marker-alt tw:text-gray-400 tw:text-4xl tw:mb-4"></i>
                                    <p class="tw:text-gray-600"><?php esc_html_e('No nearby features configured', 'realestate-booking-suite'); ?></p>
                                    <p class="tw:text-sm tw:text-gray-500 tw:mt-2"><?php esc_html_e('Add nearby schools, shopping, and restaurants in the Location section', 'realestate-booking-suite'); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Reviews Tab -->
                        <div id="reviews-tab" class="tw-tab-content hidden">
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Property Reviews & Ratings', 'realestate-booking-suite'); ?></h3>

                            <!-- Overall Rating -->
                            <div class="tw:bg-gradient-to-r tw:from-emerald-50 tw:to-teal-50 tw:rounded-lg tw:p-6 tw:mb-6">
                                <div class="tw:flex tw:flex-col tw:md:flex-row tw:items-center tw:justify-between">
                                    <div class="tw:text-center tw:md:text-left tw:mb-4 tw:md:mb-0">
                                        <div class="tw:text-5xl tw:font-bold tw:text-gray-800 tw:mb-2">4.8</div>
                                        <div class="tw:flex tw:items-center tw:justify-center tw:md:justify-start tw:mb-2">
                                            <i class="fas fa-star rating-star"></i>
                                            <i class="fas fa-star rating-star"></i>
                                            <i class="fas fa-star rating-star"></i>
                                            <i class="fas fa-star rating-star"></i>
                                            <i class="fas fa-star-half-alt rating-star"></i>
                                        </div>
                                        <p class="tw:text-gray-600"><?php esc_html_e('Based on reviews', 'realestate-booking-suite'); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="tw:text-center tw:py-8">
                                <i class="fas fa-comments tw:text-gray-300 tw:text-4xl tw:mb-4"></i>
                                <p class="tw:text-gray-500"><?php esc_html_e('No reviews yet. Be the first to review this property!', 'realestate-booking-suite'); ?></p>
                            </div>
                        </div>

                        <!-- Booking Tab -->
                        <div id="booking-tab" class="tw-tab-content hidden">
                            <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Schedule a Viewing', 'realestate-booking-suite'); ?></h3>

                            <div class="tw:grid tw:md:grid-cols-2 tw:gap-8">
                                <!-- Booking Form -->
                                <div class="tw:bg-white tw:border tw:rounded-lg tw:p-6">
                                    <h4 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Book a Property Tour', 'realestate-booking-suite'); ?></h4>
                                    <form id="booking-form" class="tw:space-y-4">
                                        <div class="tw:grid tw:md:grid-cols-2 tw:gap-4">
                                            <div>
                                                <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php esc_html_e('First Name', 'realestate-booking-suite'); ?></label>
                                                <input type="text" name="first_name" id="booking_first_name" required class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" placeholder="Enter your first name">
                                            </div>
                                            <div>
                                                <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php esc_html_e('Last Name', 'realestate-booking-suite'); ?></label>
                                                <input type="text" name="last_name" id="booking_last_name" required class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" placeholder="Enter your last name">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php esc_html_e('Email', 'realestate-booking-suite'); ?></label>
                                            <input type="email" name="email" id="booking_email" required class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" placeholder="your.email@example.com">
                                        </div>
                                        <div>
                                            <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php esc_html_e('Phone', 'realestate-booking-suite'); ?></label>
                                            <input type="tel" name="phone" id="booking_phone" required class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" placeholder="(555) 123-4567">
                                        </div>
                                        <div class="tw:grid tw:md:grid-cols-2 tw:gap-4">
                                            <div>
                                                <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php esc_html_e('Preferred Date', 'realestate-booking-suite'); ?> <span class="tw:text-gray-400">(<?php esc_html_e('Optional', 'realestate-booking-suite'); ?>)</span></label>
                                                <input type="date" name="preferred_date" id="booking_date" class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500">
                                            </div>
                                            <div>
                                                <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php esc_html_e('Preferred Time', 'realestate-booking-suite'); ?> <span class="tw:text-gray-400">(<?php esc_html_e('Optional', 'realestate-booking-suite'); ?>)</span></label>
                                                <select name="preferred_time" id="booking_time" class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500">
                                                    <option value=""><?php esc_html_e('Select Time', 'realestate-booking-suite'); ?></option>
                                                    <?php
                                                        // Default 24-hour time slots
                                                        $default_times = [
                                                            '00:00' => '12:00 AM',
                                                            '01:00' => '1:00 AM',
                                                            '02:00' => '2:00 AM',
                                                            '03:00' => '3:00 AM',
                                                            '04:00' => '4:00 AM',
                                                            '05:00' => '5:00 AM',
                                                            '06:00' => '6:00 AM',
                                                            '07:00' => '7:00 AM',
                                                            '08:00' => '8:00 AM',
                                                            '09:00' => '9:00 AM',
                                                            '10:00' => '10:00 AM',
                                                            '11:00' => '11:00 AM',
                                                            '12:00' => '12:00 PM',
                                                            '13:00' => '1:00 PM',
                                                            '14:00' => '2:00 PM',
                                                            '15:00' => '3:00 PM',
                                                            '16:00' => '4:00 PM',
                                                            '17:00' => '5:00 PM',
                                                            '18:00' => '6:00 PM',
                                                            '19:00' => '7:00 PM',
                                                            '20:00' => '8:00 PM',
                                                            '21:00' => '9:00 PM',
                                                            '22:00' => '10:00 PM',
                                                            '23:00' => '11:00 PM',
                                                        ];

                                                        // Use configured times or defaults
                                                        $times_to_use = $available_times ?: $default_times;

                                                        foreach ($times_to_use as $time_value => $time_label) {
                                                            echo '<option value="' . esc_attr($time_value) . '">' . esc_html($time_label) . '</option>';
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php esc_html_e('Message', 'realestate-booking-suite'); ?> (<?php esc_html_e('Optional', 'realestate-booking-suite'); ?>)</label>
                                            <textarea name="message" id="booking_message" rows="3" class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" placeholder="Any specific questions or requirements..."></textarea>
                                        </div>
                                        <input type="hidden" name="property_id" value="<?php echo esc_attr($property_id); ?>">
                                        <input type="hidden" name="action" value="resbs_submit_booking">
                                        <?php wp_nonce_field('resbs_booking_nonce', '_wpnonce'); ?>
                                        <button type="button" id="booking-submit-btn" onclick="handleScheduleTourClick(event);" class="tw:w-full tw:bg-emerald-500 tw:text-white tw:py-3 tw:rounded-lg hover:tw:bg-emerald-600 tw:transition tw:font-semibold">
                                            <i class="fas fa-calendar-check tw:mr-2"></i><?php esc_html_e('Schedule Tour', 'realestate-booking-suite'); ?>
                                        </button>
                                    </form>
                                </div>

                                <!-- Booking Info -->
                                <div class="tw:space-y-6">
                                    <!-- Agent Contact -->
                                    <div class="tw:bg-emerald-50 tw:rounded-lg tw:p-6">
                                        <h4 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Contact Agent', 'realestate-booking-suite'); ?></h4>
                                        <div class="tw:space-y-3">
                                            <div class="tw:flex tw:items-center">
                                                <i class="fas fa-user tw:text-emerald-500 tw:mr-3"></i>
                                                <span class="tw:text-gray-700"><?php echo esc_html($agent_name ?: __('Property Agent', 'realestate-booking-suite')); ?></span>
                                            </div>
                                            <?php if ($agent_phone): ?>
                                            <div class="tw:flex tw:items-center">
                                                <i class="fas fa-phone tw:text-emerald-500 tw:mr-3"></i>
                                                <a href="tel:<?php echo esc_attr($agent_phone); ?>" class="tw:text-emerald-600 hover:tw:text-emerald-700"><?php echo esc_html($agent_phone); ?></a>
                                            </div>
                                            <?php endif; ?>
                                            <?php if ($agent_email): ?>
                                            <div class="tw:flex tw:items-center">
                                                <i class="fas fa-envelope tw:text-emerald-500 tw:mr-3"></i>
                                                <a href="mailto:<?php echo esc_attr($agent_email); ?>" class="tw:text-emerald-600 hover:tw:text-emerald-700"><?php echo esc_html($agent_email); ?></a>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <!-- Tour Information -->
                                    <div class="tw:bg-blue-50 tw:rounded-lg tw:p-6">
                                        <h4 class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:mb-4"><?php esc_html_e('Tour Information', 'realestate-booking-suite'); ?></h4>
                                        <div class="tw:space-y-3 tw:text-sm">
                                            <div class="tw:flex tw:items-start">
                                                <i class="fas fa-clock tw:text-blue-500 tw:mr-3 tw:mt-1"></i>
                                                <div>
                                                    <p class="tw:font-semibold tw:text-gray-800"><?php esc_html_e('Duration', 'realestate-booking-suite'); ?></p>
                                                    <p class="tw:text-gray-600"><?php echo esc_html($tour_duration ?: __('Approximately 30-45 minutes', 'realestate-booking-suite')); ?></p>
                                                </div>
                                            </div>
                                            <div class="tw:flex tw:items-start">
                                                <i class="fas fa-users tw:text-blue-500 tw:mr-3 tw:mt-1"></i>
                                                <div>
                                                    <p class="tw:font-semibold tw:text-gray-800"><?php esc_html_e('Group Size', 'realestate-booking-suite'); ?></p>
                                                    <p class="tw:text-gray-600"><?php echo esc_html($tour_group_size ?: __('Maximum 4 people per tour', 'realestate-booking-suite')); ?></p>
                                                </div>
                                            </div>
                                            <div class="tw:flex tw:items-start">
                                                <i class="fas fa-shield-alt tw:text-blue-500 tw:mr-3 tw:mt-1"></i>
                                                <div>
                                                    <p class="tw:font-semibold tw:text-gray-800"><?php esc_html_e('Safety', 'realestate-booking-suite'); ?></p>
                                                    <p class="tw:text-gray-600"><?php echo esc_html($tour_safety ?: __('All safety protocols followed', 'realestate-booking-suite')); ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Similar Properties -->
                <div class="tw:bg-white tw:rounded-xl tw:shadow-sm tw:p-6">
                    <h3 class="tw:text-2xl tw:font-bold tw:text-gray-800 tw:mb-6">Similar Properties</h3>
                    <div class="tw:grid tw:md:grid-cols-2 tw:gap-6">
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
                                    $debug_gallery     = get_post_meta($debug_prop->ID, '_property_gallery', true);
                                    $debug_gallery_str = is_array($debug_gallery) ? implode(',', $debug_gallery) : $debug_gallery;
                                    echo '<!-- Debug - Gallery: ' . esc_html($debug_gallery_str) . ' -->';
                                }
                            }

                            if ($similar_properties):
                                foreach ($similar_properties as $similar):
                                    $similar_price     = get_post_meta($similar->ID, '_property_price', true);
                                    $similar_bedrooms  = get_post_meta($similar->ID, '_property_bedrooms', true);
                                    $similar_bathrooms = get_post_meta($similar->ID, '_property_bathrooms', true);
                                    $similar_area      = get_post_meta($similar->ID, '_property_area_sqft', true);
                                    $similar_gallery   = get_post_meta($similar->ID, '_property_gallery', true);

                                    // Safely handle gallery data - could be array, string, or empty
                                    $similar_images = [];
                                    if (is_array($similar_gallery)) {
                                        $similar_images = array_filter($similar_gallery);
                                    } elseif (is_string($similar_gallery) && ! empty($similar_gallery)) {
                                    $similar_images = array_filter(explode(',', $similar_gallery));
                                }

                                                                                                                       // Get the first image URL properly
                                $similar_image = 'https://images.unsplash.com/photo-1600607687644-c7171b42498f?w=600'; // Default fallback

                                // Try gallery images first
                                if (! empty($similar_images) && is_array($similar_images)) {
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
                            if (! empty($similar_price) && is_numeric($similar_price)) {
                                $similar_price_formatted = '$' . number_format(floatval($similar_price));
                            }
                        ?>
                        <a href="<?php echo get_permalink($similar->ID); ?>" class="property-card tw:border tw:border-gray-200 tw:rounded-lg tw:overflow-hidden hover:tw:shadow-lg tw:transition-shadow">
                            <div class="tw:relative">
                                <img src="<?php echo esc_url($similar_image); ?>" alt="Property" class="tw:w-full tw:h-48 tw:object-cover">
                                <span class="tw:absolute tw:top-3 tw:left-3 tw:bg-emerald-500 tw:text-white tw:px-3 tw:py-1 tw:rounded-full tw:text-sm tw:font-semibold"><?php echo esc_html(get_post_meta($similar->ID, '_property_status', true) ?: 'For Sale'); ?></span>
                            </div>
                            <div class="tw:p-4">
                                <h4 class="tw:font-bold tw:text-lg tw:text-gray-800 tw:mb-2 hover:tw:text-emerald-600 tw:transition-colors"><?php echo esc_html($similar->post_title); ?></h4>
                                <p class="tw:text-gray-600 tw:text-sm tw:mb-3 tw:flex tw:items-center">
                                    <i class="fas fa-map-marker-alt tw:text-emerald-500 tw:mr-2"></i>
                                    <?php echo esc_html(get_post_meta($similar->ID, '_property_address', true)); ?>
                                </p>
                                <div class="tw:flex tw:items-center tw:justify-between tw:mb-3">
                                    <span class="tw:text-2xl tw:font-bold tw:text-emerald-500"><?php echo esc_html($similar_price_formatted); ?></span>
                                </div>
                                <div class="tw:flex tw:items-center tw:space-x-4 tw:text-sm tw:text-gray-600 tw:border-t tw:border-gray-200 tw:pt-3">
                                    <span class="tw:flex tw:items-center"><i class="fas fa-bed tw:mr-1 tw:text-emerald-500"></i><?php echo esc_html($similar_bedrooms ?: '0'); ?> Beds</span>
                                    <span class="tw:flex tw:items-center"><i class="fas fa-bath tw:mr-1 tw:text-emerald-500"></i><?php echo esc_html($similar_bathrooms ?: '0'); ?> Bath</span>
                                    <span class="tw:flex tw:items-center"><i class="fas fa-ruler-combined tw:mr-1 tw:text-emerald-500"></i><?php echo esc_html($similar_area ?: '0'); ?> sqft</span>
                                </div>
                            </div>
                        </a>
                        <?php
                            endforeach;
                            else:
                        ?>
                        <div class="tw:col-span-4 tw:text-center tw:py-8">
                            <i class="fas fa-home tw:text-gray-300 tw:text-4xl tw:mb-4"></i>
                            <p class="tw:text-gray-500">No similar properties found</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="tw:lg:col-span-1">
                <!-- Agent Card -->
                <div class="tw:bg-white tw:rounded-xl tw:shadow-sm tw:p-6 tw:mb-6  top-24">
                    <div class="tw:text-center tw:mb-6">
                        <?php if ($agent_photo): ?>
                        <img src="<?php echo esc_url($agent_photo); ?>" alt="Agent" class="tw:w-24 tw:h-24 tw:rounded-full tw:mx-auto tw:mb-3 tw:border-4 tw:border-emerald-100">
                        <?php else: ?>
                        <div class="tw:w-24 tw:h-24 tw:rounded-full tw:mx-auto tw:mb-3 tw:border-4 tw:border-emerald-100 tw:bg-gray-200 tw:flex tw:items-center tw:justify-center">
                            <i class="fas fa-user tw:text-gray-400 tw:text-2xl"></i>
                        </div>
                        <?php endif; ?>
                        <h3 class="tw:text-xl tw:font-bold tw:text-gray-800"><?php echo esc_html($agent_name ?: 'Property Agent'); ?></h3>
                        <p class="tw:text-gray-600 tw:text-sm">Real Estate Agent</p>
                        <div class="tw:flex tw:items-center tw:justify-center tw:mt-2">
                            <?php
                                $rating = intval($agent_rating ?: 5);
                                for ($i = 1; $i <= 5; $i++):
                            ?>
                                <i class="fas fa-star rating-star tw:text-sm<?php echo $i <= $rating ? 'tw:text-yellow-400' : 'tw:text-gray-300'; ?>"></i>
                            <?php endfor; ?>
                            <span class="tw:text-sm tw:text-gray-600 tw:ml-2">(<?php echo esc_html($agent_reviews ?: 'reviews'); ?>)</span>
                        </div>
                    </div>

                    <div class="tw:space-y-3 tw:mb-6">
                        <?php if ($agent_phone): ?>
                        <a href="tel:<?php echo esc_attr($agent_phone); ?>" class="tw:flex tw:items-center tw:justify-center tw:px-4 tw:py-3 tw:bg-emerald-500 tw:text-white tw:rounded-lg hover:tw:bg-emerald-600 tw:transition">
                            <i class="fas fa-phone tw:mr-2"></i>
                            <span>Call Agent</span>
                        </a>
                        <?php endif; ?>
                        <button onclick="openContactModal()" class="tw:w-full tw:flex tw:items-center tw:justify-center tw:px-4 tw:py-3 tw:bg-gray-700 tw:text-white tw:rounded-lg hover:tw:bg-gray-800 tw:transition">
                            <i class="fas fa-envelope tw:mr-2"></i>
                            <span><?php echo esc_html($agent_send_message_text ?: 'Send Message'); ?></span>
                        </button>
                    </div>

                    <div class="tw:border-t tw:border-gray-200 tw:pt-4">
                        <div class="tw:flex tw:items-center tw:justify-between tw:text-sm tw:mb-2">
                            <span class="tw:text-gray-600">Properties Sold:</span>
                            <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($agent_properties_sold ?: '100+'); ?></span>
                        </div>
                        <div class="tw:flex tw:items-center tw:justify-between tw:text-sm tw:mb-2">
                            <span class="tw:text-gray-600">Experience:</span>
                            <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($agent_experience ?: '5+ Years'); ?></span>
                        </div>
                        <div class="tw:flex tw:items-center tw:justify-between tw:text-sm">
                            <span class="tw:text-gray-600">Response Time:</span>
                            <span class="tw:font-semibold tw:text-gray-800"><?php echo esc_html($agent_response_time ?: '< 1 Hour'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Mortgage Calculator -->
                <div class="tw:bg-white tw:rounded-xl tw:shadow-sm tw:p-6 tw:mb-6">
                    <h3 class="tw:text-xl tw:font-bold tw:text-gray-800 tw:mb-4"><?php echo esc_html($mortgage_calculator_title ?: 'Mortgage Calculator'); ?></h3>
                    <div class="tw:space-y-4">
                        <div>
                            <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html($mortgage_property_price_label ?: 'Property Price'); ?></label>
                            <div class="tw:relative">
                                <div class="tw:absolute tw:inset-y-0 tw:left-0 tw:pl-3 tw:flex tw:items-center tw:pointer-events-none">
                                    <span class="tw:text-gray-500 tw:sm:text-sm">$</span>
                                </div>
                                <input type="number" id="propertyPrice" value="<?php echo esc_attr($price ?: '500000'); ?>" class="tw:w-full tw:pl-8 tw:pr-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" placeholder="<?php echo esc_attr('500000'); ?>" onkeyup="
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
                            <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html($mortgage_down_payment_label ?: 'Down Payment (%)'); ?></label>
                            <input type="range" id="downPayment" min="0" max="100" value="<?php echo esc_attr($mortgage_default_down_payment ?: '20'); ?>" class="tw:w-full" oninput="document.getElementById('downPaymentValue').textContent = this.value + '%'; calculateMortgageNow();">
                            <div class="tw:flex tw:justify-between tw:text-sm tw:text-gray-600">
                                <span>0%</span>
                                <span id="downPaymentValue" class="tw:font-semibold tw:text-emerald-600"><?php echo esc_html($mortgage_default_down_payment ?: '20'); ?>%</span>
                                <span>100%</span>
                            </div>
                        </div>
                        <div>
                            <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html($mortgage_interest_rate_label ?: 'Interest Rate (%)'); ?></label>
                            <input type="number" id="interestRate" value="<?php echo esc_attr($mortgage_default_interest_rate ?: '6.5'); ?>" step="0.1" class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" placeholder="<?php echo esc_attr('6.5'); ?>" onkeyup="calculateMortgageNow()" onchange="calculateMortgageNow()">
                        </div>
                        <div>
                            <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html($mortgage_loan_term_label ?: 'Loan Term (Years)'); ?></label>
                            <select id="loanTerm" class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" onchange="calculateMortgageNow()">
                                <?php
                                    $loan_terms_array = explode("\n", $mortgage_loan_terms ?: "15\n20\n30");
                                    foreach ($loan_terms_array as $term) {
                                        $term = trim($term);
                                        if (! empty($term)) {
                                            $selected = ($term == ($mortgage_default_loan_term ?: '30')) ? 'selected' : '';
                                            echo '<option value="' . esc_attr($term) . '" ' . $selected . '>' . esc_html($term) . ' Years</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </div>
                        <div class="tw:bg-emerald-50 tw:rounded-lg tw:p-4 tw:mt-4">
                            <p class="tw:text-sm tw:text-gray-600 tw:mb-1"><?php echo esc_html($mortgage_monthly_payment_label ?: 'Estimated Monthly Payment'); ?></p>
                            <p class="tw:text-3xl tw:font-bold tw:text-emerald-600" id="monthlyPayment">$0</p>
                            <p class="tw:text-xs tw:text-gray-500 tw:mt-2"><?php echo esc_html($mortgage_disclaimer_text ?: '*Principal & Interest only'); ?></p>
                        </div>

                        <!-- Calculate Button -->
                        <div class="tw:mt-4">
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
                            " class="tw:w-full tw:bg-emerald-500 tw:text-white tw:py-3 tw:px-4 tw:rounded-lg hover:tw:bg-emerald-600 tw:transition tw:font-semibold">
                                <i class="fas fa-calculator tw:mr-2"></i>Calculate Mortgage
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Image Viewer Modal -->
    <div id="imageViewer" class="image-viewer">
        <button onclick="closeImageViewer()" class="tw:absolute tw:top-4 tw:right-4 tw:text-white tw:text-3xl tw:z-10 hover:tw:text-emerald-400">
            <i class="fas fa-times"></i>
        </button>
        <button onclick="prevImage()" class="tw:absolute tw:left-4 tw:text-white tw:text-3xl tw:z-10 hover:tw:text-emerald-400">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button onclick="nextImage()" class="tw:absolute tw:right-4 tw:text-white tw:text-3xl tw:z-10 hover:tw:text-emerald-400">
            <i class="fas fa-chevron-right"></i>
        </button>
        <img id="viewerImage" src="" alt="<?php esc_attr_e('Property', 'realestate-booking-suite'); ?>" class="tw:max-w-full tw:max-h-full">
    </div>

    <!-- Contact Modal -->
    <div id="contactModal" class="tw:fixed tw:inset-0 tw:bg-black tw:bg-opacity-50 tw:z-50 tw:hidden tw:items-center tw:justify-center tw:p-4">
        <div class="tw:bg-white tw:rounded-xl tw:max-w-md tw:w-full tw:p-6">
            <div class="tw:flex tw:items-center tw:justify-between tw:mb-4">
                <h3 class="tw:text-2xl tw:font-bold tw:text-gray-800"><?php echo esc_html($contact_form_title ?: 'Contact Agent'); ?></h3>
                <button onclick="closeContactModal()" class="tw:text-gray-500 hover:tw:text-gray-700">
                    <i class="fas fa-times tw:text-xl"></i>
                </button>
            </div>
            <form onsubmit="submitContactForm(event)" class="tw:space-y-4">
                <div>
                    <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html($contact_name_label ?: 'Your Name'); ?></label>
                    <input type="text" name="contact_name" required class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" placeholder="<?php esc_attr_e('Enter your full name', 'realestate-booking-suite'); ?>">
                </div>
                <div>
                    <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html($contact_email_label ?: 'Email'); ?></label>
                    <input type="email" name="contact_email" required class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" placeholder="<?php esc_attr_e('your.email@example.com', 'realestate-booking-suite'); ?>">
                </div>
                <div>
                    <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html($contact_phone_label ?: 'Phone'); ?></label>
                    <input type="tel" name="contact_phone" required class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" placeholder="<?php esc_attr_e('(555) 123-4567', 'realestate-booking-suite'); ?>">
                </div>
                <div>
                    <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:mb-2"><?php echo esc_html($contact_message_label ?: 'Message'); ?></label>
                    <textarea name="contact_message" rows="4" required class="tw:w-full tw:px-4 tw:py-2 tw:border tw:border-gray-300 tw:rounded-lg focus:tw:outline-none focus:tw:ring-2 focus:tw:ring-emerald-500" placeholder="<?php esc_attr_e('Tell us about your interest in this property...', 'realestate-booking-suite'); ?>"></textarea>
                </div>
                <button type="submit" class="tw:w-full tw:bg-emerald-500 tw:text-white tw:py-3 tw:rounded-lg hover:tw:bg-emerald-600 tw:transition tw:font-semibold">
                    <?php echo esc_html($contact_submit_text ?: 'Send Message'); ?>
                </button>
            </form>
        </div>
    </div>

</div>


<?php get_footer(); ?>