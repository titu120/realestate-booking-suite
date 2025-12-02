<?php
    /**
     * Single Property Template - Dynamic Version
     * Block Theme Compatible
     *
     * @package RealEstate_Booking_Suite
     */

    // Prevent direct access
    if (! defined('ABSPATH')) {
        exit;
    }

    // Define plugin URL if not already defined
    if (! defined('RESBS_URL')) {
        define('RESBS_URL', plugin_dir_url(dirname(__FILE__)) . '/');
    }

    // Use helper function to safely get header (avoids deprecation warnings in block themes)
    resbs_get_header();
?>

<?php if (have_posts()) : ?>
    <?php while (have_posts()) : the_post(); ?>
        <?php
        // Get the current post (now available after the_post())
        global $post;
        
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
        // Fallback: try without underscore prefix (same as archive page)
        if (empty($latitude)) {
            $latitude = get_post_meta($post->ID, 'property_latitude', true);
        }
        if (empty($longitude)) {
            $longitude = get_post_meta($post->ID, 'property_longitude', true);
        }
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

        // Get features and amenities - COMPLETE FIX: Always return strings, never arrays
        $features_raw  = get_post_meta($post->ID, '_property_features', true);
        $amenities_raw = get_post_meta($post->ID, '_property_amenities', true);
        
        // RECURSIVE FUNCTION TO FLATTEN ANY ARRAY STRUCTURE
        function resbs_flatten_to_string($data) {
            if (is_array($data)) {
                $result = array();
                foreach ($data as $item) {
                    if (is_array($item)) {
                        $flattened = resbs_flatten_to_string($item);
                        if (!empty($flattened)) {
                            $result[] = $flattened;
                        }
                    } elseif (is_string($item) || is_numeric($item)) {
                        $trimmed = trim((string)$item);
                        if (!empty($trimmed)) {
                            $result[] = $trimmed;
                        }
                    }
                }
                return implode(', ', $result);
            } elseif (is_string($data) || is_numeric($data)) {
                return trim((string)$data);
            }
            return '';
        }
        
        // FORCE TO STRING - NO EXCEPTIONS
        $features = resbs_flatten_to_string($features_raw);
        $amenities = resbs_flatten_to_string($amenities_raw);
        
        // FINAL GUARANTEE - MUST BE STRING
        $features = is_string($features) ? $features : '';
        $amenities = is_string($amenities) ? $amenities : '';
        // Get property details - ensure we get the values correctly
        $parking           = get_post_meta($post->ID, '_property_parking', true);
        $heating           = get_post_meta($post->ID, '_property_heating', true);
        $cooling           = get_post_meta($post->ID, '_property_cooling', true);
        $basement          = get_post_meta($post->ID, '_property_basement', true);
        $roof              = get_post_meta($post->ID, '_property_roof', true);
        $exterior_material = get_post_meta($post->ID, '_property_exterior_material', true);
        $floor_covering    = get_post_meta($post->ID, '_property_floor_covering', true);
        
        // Ensure values are strings and trimmed (handle any edge cases)
        $parking = is_string($parking) ? trim($parking) : (is_numeric($parking) ? (string)$parking : '');
        $heating = is_string($heating) ? trim($heating) : (is_numeric($heating) ? (string)$heating : '');
        $cooling = is_string($cooling) ? trim($cooling) : (is_numeric($cooling) ? (string)$cooling : '');
        $basement = is_string($basement) ? trim($basement) : (is_numeric($basement) ? (string)$basement : '');
        $roof = is_string($roof) ? trim($roof) : (is_numeric($roof) ? (string)$roof : '');
        $exterior_material = is_string($exterior_material) ? trim($exterior_material) : (is_numeric($exterior_material) ? (string)$exterior_material : '');
        $floor_covering = is_string($floor_covering) ? trim($floor_covering) : (is_numeric($floor_covering) ? (string)$floor_covering : '');

        // Nearby features
        $nearby_schools     = get_post_meta($post->ID, '_property_nearby_schools', true);
        $nearby_shopping    = get_post_meta($post->ID, '_property_nearby_shopping', true);
        $nearby_restaurants = get_post_meta($post->ID, '_property_nearby_restaurants', true);

        $gallery_images           = get_post_meta($post->ID, '_property_gallery', true);
        
        // Convert gallery attachment IDs to URLs (handle both IDs and URLs)
        $gallery_urls = [];
        if (!empty($gallery_images)) {
            if (is_array($gallery_images)) {
                foreach ($gallery_images as $image_item) {
                    // Check if it's an attachment ID (numeric) or URL (string)
                    if (is_numeric($image_item)) {
                        $image_url = wp_get_attachment_image_url($image_item, 'full');
                        if ($image_url) {
                            $gallery_urls[] = $image_url;
                        }
                    } elseif (is_string($image_item) && filter_var($image_item, FILTER_VALIDATE_URL)) {
                        // It's already a URL
                        $gallery_urls[] = $image_item;
                    }
                }
            } elseif (is_string($gallery_images)) {
                // Handle comma-separated string
                $gallery_array = explode(',', $gallery_images);
                foreach ($gallery_array as $image_item) {
                    $image_item = trim($image_item);
                    if (is_numeric($image_item)) {
                        $image_url = wp_get_attachment_image_url($image_item, 'full');
                        if ($image_url) {
                            $gallery_urls[] = $image_url;
                        }
                    } elseif (filter_var($image_item, FILTER_VALIDATE_URL)) {
                        $gallery_urls[] = $image_item;
                    }
                }
            }
        }
        $floor_plans              = get_post_meta($post->ID, '_property_floor_plans', true);
        // Helper function to get field value with default (only for new properties)
        $get_field_with_default = function($meta_key, $default) use ($post) {
            $exists = metadata_exists('post', $post->ID, $meta_key);
            $value = get_post_meta($post->ID, $meta_key, true);
            return $exists ? $value : $default;
        };
        
        $virtual_tour             = get_post_meta($post->ID, '_property_virtual_tour', true);
        $virtual_tour_title       = $get_field_with_default('_property_virtual_tour_title', '3D Virtual Walkthrough');
        $virtual_tour_description = $get_field_with_default('_property_virtual_tour_description', 'Experience this property from anywhere with our interactive 3D tour.');
        $virtual_tour_button_text = $get_field_with_default('_property_virtual_tour_button_text', 'Start Tour');
        $video_url                = get_post_meta($post->ID, '_property_video_url', true);
        $video_embed              = get_post_meta($post->ID, '_property_video_embed', true);

        // Agent data
        $agent_name              = get_post_meta($post->ID, '_property_agent_name', true);
        $agent_phone             = get_post_meta($post->ID, '_property_agent_phone', true);
        $agent_email             = get_post_meta($post->ID, '_property_agent_email', true);
        $agent_photo             = get_post_meta($post->ID, '_property_agent_photo', true);
        $agent_properties_sold   = $get_field_with_default('_property_agent_properties_sold', '100+');
        $agent_experience        = $get_field_with_default('_property_agent_experience', '5+ Years');
        $agent_response_time     = $get_field_with_default('_property_agent_response_time', '< 1 Hour');
        $agent_rating            = $get_field_with_default('_property_agent_rating', '5');
        $agent_send_message_text = $get_field_with_default('_property_agent_send_message_text', 'Send Message');

        // Contact Form Dynamic Fields
        $contact_form_title      = $get_field_with_default('_property_contact_form_title', 'Contact Agent');
        $contact_name_label      = $get_field_with_default('_property_contact_name_label', 'Your Name');
        $contact_email_label     = $get_field_with_default('_property_contact_email_label', 'Email');
        $contact_phone_label     = $get_field_with_default('_property_contact_phone_label', 'Phone');
        $contact_message_label   = $get_field_with_default('_property_contact_message_label', 'Message');
        $contact_success_message = $get_field_with_default('_property_contact_success_message', 'Thank you! Your message has been sent to the agent.');
        $contact_submit_text     = $get_field_with_default('_property_contact_submit_text', 'Send Message');

        // Booking Form Dynamic Fields
        $booking_form_title      = $get_field_with_default('_property_booking_form_title', 'Property Booking');
        $booking_form_subtitle   = $get_field_with_default('_property_booking_form_subtitle', 'Schedule a viewing or book this property');
        $booking_name_label      = $get_field_with_default('_property_booking_name_label', 'Your Name');
        $booking_email_label     = $get_field_with_default('_property_booking_email_label', 'Email');
        $booking_phone_label     = $get_field_with_default('_property_booking_phone_label', 'Phone');
        $booking_date_label      = $get_field_with_default('_property_booking_date_label', 'Preferred Date');
        $booking_time_label      = $get_field_with_default('_property_booking_time_label', 'Preferred Time');
        $booking_message_label   = $get_field_with_default('_property_booking_message_label', 'Additional Message');
        $booking_submit_text     = $get_field_with_default('_property_booking_submit_text', 'Schedule Property Viewing');

        // Mortgage Calculator Dynamic Fields
        $mortgage_calculator_title      = $get_field_with_default('_property_mortgage_calculator_title', 'Mortgage Calculator');
        $mortgage_property_price_label  = $get_field_with_default('_property_mortgage_property_price_label', 'Property Price');
        $mortgage_down_payment_label    = $get_field_with_default('_property_mortgage_down_payment_label', 'Down Payment (%)');
        $mortgage_interest_rate_label   = $get_field_with_default('_property_mortgage_interest_rate_label', 'Interest Rate (%)');
        $mortgage_loan_term_label       = $get_field_with_default('_property_mortgage_loan_term_label', 'Loan Term (Years)');
        $mortgage_monthly_payment_label = $get_field_with_default('_property_mortgage_monthly_payment_label', 'Estimated Monthly Payment');
        $mortgage_default_down_payment  = $get_field_with_default('_property_mortgage_default_down_payment', '20');
        $mortgage_default_interest_rate = $get_field_with_default('_property_mortgage_default_interest_rate', '6.5');
        $mortgage_disclaimer_text       = $get_field_with_default('_property_mortgage_disclaimer_text', '*Principal & Interest only');
        
        // Get global mortgage calculator settings
        $mortgage_loan_terms = get_option('resbs_mortgage_loan_terms', '');
        $mortgage_default_loan_term_global = get_option('resbs_mortgage_default_loan_term', '');
        $mortgage_default_down_payment_global = get_option('resbs_mortgage_default_down_payment', '');
        $mortgage_default_interest_rate_global = get_option('resbs_mortgage_default_interest_rate', '');

        // Tour Information Fields (removed - not used on frontend)

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

        // Format price with dynamic currency
        $formatted_price = '';
        if ($price && ! $call_for_price) {
            $formatted_price = resbs_format_price($price);
        } elseif ($call_for_price) {
            $formatted_price = __('Call for Price', 'realestate-booking-suite');
        }

        // Format price per unit (uses General settings) with dynamic currency
        $formatted_price_per_sqft = '';
        if ($price_per_sqft) {
            $area_unit = resbs_get_area_unit();
            $unit_label = ($area_unit === 'sqm') ? 'sq m' : 'sq ft';
            $formatted_price_per_sqft = resbs_format_price($price_per_sqft) . '/' . $unit_label;
        }

        // Format area (uses General settings)
        $formatted_area = '';
        if ($area_sqft) {
            $formatted_area = resbs_format_area($area_sqft);
        }

        // Format lot size (uses General settings)
        $formatted_lot_size = '';
        if ($lot_size_sqft) {
            $formatted_lot_size = resbs_format_lot_size($lot_size_sqft);
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

        // Parse features and amenities - ensure $features and $amenities are strings at this point
        $features_array = [];
        if (!empty($features)) {
            if (is_string($features)) {
                // String, explode by comma
            $features_array = explode(',', $features);
            } elseif (is_array($features)) {
                // Already an array, flatten it
                $features_array = $features;
            }
            
            // Deep clean: ensure all values are strings, flatten any nested arrays
            $cleaned_features = [];
            foreach ($features_array as $item) {
                if (is_array($item)) {
                    // If it's an array, implode it
                    $flattened = implode(', ', array_filter(array_map('trim', $item)));
                    if (!empty($flattened)) {
                        $cleaned_features[] = $flattened;
                    }
                } elseif (is_string($item) || is_numeric($item)) {
                    $trimmed = trim((string)$item);
                    if (!empty($trimmed)) {
                        $cleaned_features[] = $trimmed;
                    }
                }
            }
            $features_array = array_values($cleaned_features);
        }

        $amenities_array = [];
        if (!empty($amenities)) {
            if (is_string($amenities)) {
                // String, explode by comma
            $amenities_array = explode(',', $amenities);
            } elseif (is_array($amenities)) {
                // Already an array, flatten it
                $amenities_array = $amenities;
            }
            
            // Deep clean: ensure all values are strings, flatten any nested arrays
            $cleaned_amenities = [];
            foreach ($amenities_array as $item) {
                if (is_array($item)) {
                    // If it's an array, implode it
                    $flattened = implode(', ', array_filter(array_map('trim', $item)));
                    if (!empty($flattened)) {
                        $cleaned_amenities[] = $flattened;
                    }
                } elseif (is_string($item) || is_numeric($item)) {
                    $trimmed = trim((string)$item);
                    if (!empty($trimmed)) {
                        $cleaned_amenities[] = $trimmed;
                    }
                }
            }
            $amenities_array = array_values($cleaned_amenities);
        }

        // Parse gallery images
        $gallery_array = [];
        if ($gallery_images && is_string($gallery_images)) {
            $gallery_array = explode(',', $gallery_images);
            $gallery_array = array_map('trim', $gallery_array);
        }

        // Default values for missing data
        // Get currency symbol for defaults
        $currency_symbol = resbs_get_currency_symbol();
        $default_values = [
            'price'                 => $currency_symbol . '0',
            'price_per_sqft'        => $currency_symbol . '0/sq ft',
            'bedrooms'              => '0',
            'bathrooms'             => '0',
            'area_sqft'             => '0 sq ft',
            'property_type'         => __('Property', 'realestate-booking-suite'),
            'property_status'       => __('Available', 'realestate-booking-suite'),
            'property_condition'    => __('Good', 'realestate-booking-suite'),
            'agent_name'            => __('Contact Agent', 'realestate-booking-suite'),
            'agent_phone'           => __('N/A', 'realestate-booking-suite'),
            'agent_email'           => __('N/A', 'realestate-booking-suite'),
            'agent_rating'          => '5',
            'agent_reviews'         => '0',
            'agent_experience'      => __('N/A', 'realestate-booking-suite'),
            'agent_response_time'   => __('N/A', 'realestate-booking-suite'),
            'agent_properties_sold' => '0',
        ];

        // Apply defaults
        foreach ($default_values as $key => $default_value) {
            if (empty($$key)) {
                $$key = $default_value;
            }
        }
        ?>
<!-- Single Property Template - Styles and scripts are now enqueued via wp_enqueue_style/wp_enqueue_script -->

<div class="single-property resbs-single-property-wrapper" id="resbs-single-property-page">




    <div class="main-single-container container main-content " style="width: 100% !important; max-width: 1536px !important; min-width: 0 !important; margin: 0 auto !important; margin-left: auto !important; margin-right: auto !important; padding: 4rem 1rem !important; padding-top: 4rem !important; padding-bottom: 4rem !important; padding-left: 1rem !important; padding-right: 1rem !important; box-sizing: border-box !important; position: relative !important; display: block !important;">
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
                                    <span class="badge badge-blue"><?php echo esc_html__('Featured', 'realestate-booking-suite'); ?></span>
                                <?php endif; ?>
                                <?php if (in_array('New', $property_badges)): ?>
                                    <span class="badge badge-orange"><?php echo esc_html__('New', 'realestate-booking-suite'); ?></span>
                                <?php endif; ?>
                                <?php if (in_array('Sold', $property_badges)): ?>
                                    <span class="badge badge-red"><?php echo esc_html__('Sold', 'realestate-booking-suite'); ?></span>
                                <?php endif; ?>
                            </div>
                            <<?php echo esc_attr(resbs_get_title_heading_tag()); ?> class="property-title"><?php echo esc_html(get_the_title()); ?></<?php echo esc_attr(resbs_get_title_heading_tag()); ?>>
                            <p class="property-location">
                                <i class="fas fa-map-marker-alt text-emerald-500"></i>
                                <?php echo esc_html($full_address ? $full_address : __('Location not specified', 'realestate-booking-suite')); ?>
                            </p>
                            <?php if (resbs_should_show_date_added()): ?>
                            <p class="property-date" style="color: #6b7280; font-size: 14px; margin-top: 8px;">
                                <i class="fas fa-calendar-alt"></i>
                                <?php echo esc_html__('Listed on', 'realestate-booking-suite'); ?> <?php echo esc_html(resbs_format_date(get_the_date('Y-m-d'))); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <p class="property-price"><?php echo esc_html($formatted_price); ?></p>
                            <?php if ($formatted_price_per_sqft): ?>
                                <p class="property-price-per-unit"><?php echo esc_html($formatted_price_per_sqft); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="property-actions no-print">
                        <?php 
                        // Show sharing button only if enabled (dynamically from Listings settings)
                        if (resbs_is_sharing_enabled()) : ?>
                        <button onclick="shareProperty()" class="tooltip action-btn" data-tooltip="<?php echo esc_attr__('Share Property', 'realestate-booking-suite'); ?>">
                            <i class="fas fa-share-alt text-gray-600"></i>
                            <span class="text-gray-700"><?php echo esc_html__('Share', 'realestate-booking-suite'); ?></span>
                        </button>
                        <?php endif; ?>
                        <button onclick="printPage()" class="tooltip action-btn" data-tooltip="<?php echo esc_attr__('Print Details', 'realestate-booking-suite'); ?>">
                            <i class="fas fa-print text-gray-600"></i>
                            <span class="text-gray-700"><?php echo esc_html__('Print', 'realestate-booking-suite'); ?></span>
                        </button>
                    </div>
                </div>

                <!-- Image Gallery -->
                <?php 
                    // Show ONLY gallery images, not post thumbnail
                    $all_images = [];
                    if (!empty($gallery_urls)) {
                        $all_images = $gallery_urls;
                    }
                    $total_images = count($all_images);
                ?>
                <?php if ($total_images > 0): ?>
                <div class="card">
                    <div class="gallery">
                        <?php 
                        // Check if lightbox is disabled
                        $lightbox_disabled = resbs_is_lightbox_disabled_single_page();
                        ?>
                        <div class="gallery-item gallery-main">
                            <img src="<?php echo esc_url($all_images[0]); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="gallery-img"<?php if (!$lightbox_disabled): ?> onclick="<?php echo esc_js('openImageViewer(' . absint(0) . ')'); ?>"<?php endif; ?>>
                        </div>

                        <?php for ($i = 1; $i < 5; $i++): ?>
                            <?php if ($i < $total_images): ?>
                                <div class="gallery-item <?php echo esc_attr(($i == 4 && $total_images > 5) ? 'gallery-more' : ''); ?>">
                                    <img src="<?php echo esc_url($all_images[$i]); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="gallery-img"<?php if (!$lightbox_disabled): ?> onclick="<?php echo esc_js('openImageViewer(' . absint($i) . ')'); ?>"<?php endif; ?>>
                                    <?php if ($i == 4 && $total_images > 5): ?>
                                        <div class="gallery-overlay"<?php if (!$lightbox_disabled): ?> onclick="<?php echo esc_js('openImageViewer(' . absint(4) . ')'); ?>"<?php endif; ?>>
                                            <span>+<?php echo esc_html($total_images - 5); ?> <?php echo esc_html__('More', 'realestate-booking-suite'); ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Key Features -->
                <div class="card">
                    <h2 class="section-title"><?php echo esc_html__('Key Features', 'realestate-booking-suite'); ?></h2>
                    <div class="key-features-grid">
                        <?php if ($bedrooms): ?>
                        <div class="feature-item">
                            <i class="fas fa-bed feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($bedrooms); ?></p>
                            <p class="feature-label"><?php echo esc_html__('Bedrooms', 'realestate-booking-suite'); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($bathrooms): ?>
                        <div class="feature-item">
                            <i class="fas fa-bath feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($bathrooms); ?></p>
                            <p class="feature-label"><?php echo esc_html__('Bathrooms', 'realestate-booking-suite'); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($formatted_area): ?>
                        <div class="feature-item">
                            <i class="fas fa-ruler-combined feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($formatted_area); ?></p>
                            <p class="feature-label"><?php echo esc_html(resbs_get_area_unit() === 'sqm' ? __('Sq M', 'realestate-booking-suite') : __('Sq Ft', 'realestate-booking-suite')); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($parking): ?>
                        <div class="feature-item">
                            <i class="fas fa-car feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($parking); ?></p>
                            <p class="feature-label"><?php echo esc_html__('Parking', 'realestate-booking-suite'); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($year_built): ?>
                        <div class="feature-item">
                            <i class="fas fa-calendar feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($year_built); ?></p>
                            <p class="feature-label"><?php echo esc_html__('Year Built', 'realestate-booking-suite'); ?></p>
                        </div>
                        <?php endif; ?>

                        <?php if ($property_type): ?>
                        <div class="feature-item">
                            <i class="fas fa-home feature-icon"></i>
                            <p class="feature-value"><?php echo esc_html($property_type); ?></p>
                            <p class="feature-label"><?php echo esc_html__('Type', 'realestate-booking-suite'); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="tabs no-print">
                    <div class="tabs-header">
                        <button onclick="switchTab('overview')" class="tab-button active" data-tab="overview">
                            <?php echo esc_html__('Overview', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('pricing')" class="tab-button" data-tab="pricing">
                            <?php echo esc_html__('Pricing', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('specifications')" class="tab-button" data-tab="specifications">
                            <?php echo esc_html__('Specifications', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('location')" class="tab-button" data-tab="location">
                            <?php echo esc_html__('Location', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('features')" class="tab-button" data-tab="features">
                            <?php echo esc_html__('Features', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('media')" class="tab-button" data-tab="media">
                            <?php echo esc_html__('Media', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('agent')" class="tab-button" data-tab="agent">
                            <?php echo esc_html__('Agent', 'realestate-booking-suite'); ?>
                        </button>
                        <button onclick="switchTab('booking')" class="tab-button" data-tab="booking">
                            <?php echo esc_html__('Booking', 'realestate-booking-suite'); ?>
                        </button>
                    </div>

 
                    <!-- Tab Contents -->
                    <div class="tabs-content">
                        <!-- Overview Tab -->
                        <div id="overview-tab" class="tab-content active">
                            <!-- Property Classification -->
                            <div class="mb-6">
                                <h3 class="section-title"><?php echo esc_html__('Property Classification', 'realestate-booking-suite'); ?></h3>
                                <p class="text-gray-600 mb-4"><?php echo esc_html__('Basic property information and type', 'realestate-booking-suite'); ?></p>
                                <div class="classification-container">
                                    <?php if ($property_type): ?>
                                    <div class="classification-item">
                                        <label class="classification-label"><?php echo esc_html__('Property Type', 'realestate-booking-suite'); ?></label>
                                        <p class="classification-value"><?php echo esc_html($property_type); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($property_status): ?>
                                    <div class="classification-item">
                                        <label class="classification-label"><?php echo esc_html__('Property Status', 'realestate-booking-suite'); ?></label>
                                        <p class="classification-value"><?php echo esc_html($property_status); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($property_condition): ?>
                                    <div class="classification-item">
                                        <label class="classification-label"><?php echo esc_html__('Property Condition', 'realestate-booking-suite'); ?></label>
                                        <p class="classification-value"><?php echo esc_html($property_condition); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Property Description -->
                            <div class="mb-6">
                                <h3 class="section-title"><?php echo esc_html__('Property Description', 'realestate-booking-suite'); ?></h3>
                                <div class="text-gray-600 space-y-4 leading-relaxed">
                                    <?php if (get_the_content()): ?>
                                        <?php echo wp_kses_post(get_the_content()); ?>
                                    <?php else: ?>
                                        <p><?php echo esc_html__('No description available for this property.', 'realestate-booking-suite'); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if ($virtual_tour): ?>
                            <div class="info-box">
                                <p><i class="fas fa-info-circle mr-2"></i><strong><?php echo esc_html__('Virtual Tour Available:', 'realestate-booking-suite'); ?></strong> <?php echo esc_html($virtual_tour_description); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pricing Tab -->
                        <div id="pricing-tab" class="tab-content">
                            <h3 class="section-title"><?php echo esc_html__('Property Pricing', 'realestate-booking-suite'); ?></h3>
                            <p class="text-gray-600 mb-6"><?php echo esc_html__('Set property price and pricing details', 'realestate-booking-suite'); ?></p>
                            
                            <div class="pricing-container">
                                <!-- Main Price -->
                                <div class="pricing-card main-price">
                                    <label class="pricing-label"><?php echo esc_html__('Price', 'realestate-booking-suite'); ?></label>
                                    <p class="pricing-value"><?php echo esc_html($formatted_price); ?></p>
                                </div>
                                
                                <!-- Price per Unit -->
                                <?php if ($formatted_price_per_sqft): ?>
                                <div class="pricing-card">
                                    <label class="pricing-label"><?php echo esc_html(sprintf(__('Price per %s', 'realestate-booking-suite'), resbs_get_area_unit() === 'sqm' ? __('Sq M', 'realestate-booking-suite') : __('Sq Ft', 'realestate-booking-suite'))); ?></label>
                                    <p class="pricing-value-small"><?php echo esc_html($formatted_price_per_sqft); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <!-- Price Note -->
                                <?php if ($price_note): ?>
                                <div class="pricing-card">
                                    <label class="pricing-label"><?php echo esc_html__('Price Note', 'realestate-booking-suite'); ?></label>
                                    <p class="pricing-value-small"><?php echo esc_html($price_note); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($call_for_price): ?>
                            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-blue-800 font-medium"><i class="fas fa-phone mr-2"></i><?php echo esc_html__('Call for Price', 'realestate-booking-suite'); ?></p>
                                <p class="text-blue-600 text-sm mt-1"><?php echo esc_html__('Contact us for pricing information', 'realestate-booking-suite'); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Specifications Tab -->
                        <div id="specifications-tab" class="tab-content">
                            <h3 class="section-title"><?php echo esc_html__('Property Specifications', 'realestate-booking-suite'); ?></h3>
                            <p class="text-gray-600 mb-6"><?php echo esc_html__('Detailed property specifications and measurements', 'realestate-booking-suite'); ?></p>
                            
                            <div class="specifications-grid">
                                <?php if ($bedrooms): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html__('Bedrooms', 'realestate-booking-suite'); ?></label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($bedrooms); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($bathrooms): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html__('Bathrooms', 'realestate-booking-suite'); ?></label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($bathrooms); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($half_baths): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html__('Half Baths', 'realestate-booking-suite'); ?></label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($half_baths); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($total_rooms): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html__('Total Rooms', 'realestate-booking-suite'); ?></label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($total_rooms); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($floors): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html__('Floors', 'realestate-booking-suite'); ?></label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($floors); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($floor_level): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html__('Floor Level', 'realestate-booking-suite'); ?></label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($floor_level); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($formatted_area): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html(sprintf(__('Area (%s)', 'realestate-booking-suite'), resbs_get_area_unit() === 'sqm' ? __('Sq M', 'realestate-booking-suite') : __('Sq Ft', 'realestate-booking-suite'))); ?></label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($formatted_area); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($formatted_lot_size): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html(sprintf(__('Lot Size (%s)', 'realestate-booking-suite'), resbs_get_lot_size_unit() === 'sqm' ? __('Sq M', 'realestate-booking-suite') : __('Sq Ft', 'realestate-booking-suite'))); ?></label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($formatted_lot_size); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($year_built): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html__('Year Built', 'realestate-booking-suite'); ?></label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($year_built); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($year_remodeled): ?>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html__('Year Remodeled', 'realestate-booking-suite'); ?></label>
                                    <p class="text-2xl font-bold text-gray-900"><?php echo esc_html($year_remodeled); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Details Tab -->
                        <div id="details-tab" class="tab-content">
                            <h3 class="section-title"><?php echo esc_html__('Property Details', 'realestate-booking-suite'); ?></h3>
                            <div class="details-grid">
                                <div class="space-y-3">
                                    <div class="detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Property ID:', 'realestate-booking-suite'); ?></span>
                                        <span class="detail-value"><?php echo esc_html($post->ID); ?></span>
                                    </div>
                                    <?php if ($property_type): ?>
                                    <div class="detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Property Type:', 'realestate-booking-suite'); ?></span>
                                        <span class="detail-value"><?php echo esc_html($property_type); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($year_built): ?>
                                    <div class="detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Year Built:', 'realestate-booking-suite'); ?></span>
                                        <span class="detail-value"><?php echo esc_html($year_built); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($formatted_lot_size): ?>
                                    <div class="detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Lot Size:', 'realestate-booking-suite'); ?></span>
                                        <span class="detail-value"><?php echo esc_html($formatted_lot_size); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floors): ?>
                                    <div class="detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Floors:', 'realestate-booking-suite'); ?></span>
                                        <span class="detail-value"><?php echo esc_html($floors); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="space-y-3">
                                    <?php if ($property_status): ?>
                                    <div class="detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Status:', 'realestate-booking-suite'); ?></span>
                                        <span class="detail-value text-emerald-600"><?php echo esc_html($property_status); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($floor_level): ?>
                                    <div class="detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Floor Level:', 'realestate-booking-suite'); ?></span>
                                        <span class="detail-value"><?php echo esc_html($floor_level); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($heating): ?>
                                    <div class="detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Heating:', 'realestate-booking-suite'); ?></span>
                                        <span class="detail-value"><?php echo esc_html($heating); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($cooling): ?>
                                    <div class="detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Cooling:', 'realestate-booking-suite'); ?></span>
                                        <span class="detail-value"><?php echo esc_html($cooling); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($property_condition): ?>
                                    <div class="detail-item">
                                        <span class="detail-label"><?php echo esc_html__('Condition:', 'realestate-booking-suite'); ?></span>
                                        <span class="detail-value"><?php echo esc_html($property_condition); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Features Tab -->
                        <div id="features-tab" class="tab-content">
                            <h3 class="section-title"><?php echo esc_html__('Property Features', 'realestate-booking-suite'); ?></h3>
                            
                            <!-- Features List Section -->
                            <?php if (!empty($features_array) && is_array($features_array) && count($features_array) > 0): ?>
                            <div class="property-features-section mb-8">
                                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html__('Features', 'realestate-booking-suite'); ?></h4>
                                <div class="property-features-list" style="display: flex; flex-wrap: wrap; gap: 12px;">
                                    <?php foreach ($features_array as $feature): 
                                        $feature_trimmed = trim($feature);
                                        if (empty($feature_trimmed)) continue;
                                    ?>
                                    <div class="feature-badge" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; color: #0369a1; font-size: 14px; font-weight: 500;">
                                        <i class="fas fa-check-circle" style="color: #0ea5e9;"></i>
                                        <span><?php echo esc_html($feature_trimmed); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Amenities List Section -->
                            <?php if (!empty($amenities_array) && is_array($amenities_array) && count($amenities_array) > 0): ?>
                            <div class="property-features-section mb-8">
                                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html__('Amenities', 'realestate-booking-suite'); ?></h4>
                                <div class="property-amenities-list" style="display: flex; flex-wrap: wrap; gap: 12px;">
                                    <?php foreach ($amenities_array as $amenity): 
                                        $amenity_trimmed = trim($amenity);
                                        if (empty($amenity_trimmed)) continue;
                                    ?>
                                    <div class="amenity-badge" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; color: #166534; font-size: 14px; font-weight: 500;">
                                        <i class="fas fa-star" style="color: #22c55e;"></i>
                                        <span><?php echo esc_html($amenity_trimmed); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Property Features Section -->
                            <?php
                            // Check if any property details exist
                            $has_property_details = !empty($parking) || !empty($heating) || !empty($cooling) || 
                                                   !empty($basement) || !empty($roof) || !empty($exterior_material) || 
                                                   !empty($floor_covering);
                            ?>
                            <?php if ($has_property_details): ?>
                            <div class="property-features-section mb-8">
                                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html__('Property Details', 'realestate-booking-suite'); ?></h4>
                                <div class="property-features-grid">
                                    <?php 
                                    // Helper function to get display labels - improved to handle free text
                                    function resbs_get_parking_label($value) {
                                        if (empty($value)) return '';
                                        $value_lower = strtolower(trim($value));
                                        $labels = array(
                                            'garage' => __('Garage', 'realestate-booking-suite'),
                                            'driveway' => __('Driveway', 'realestate-booking-suite'),
                                            'street' => __('Street Parking', 'realestate-booking-suite'),
                                            'none' => __('No Parking', 'realestate-booking-suite')
                                        );
                                        // Check for exact match first
                                        if (isset($labels[$value_lower])) {
                                            return $labels[$value_lower];
                                        }
                                        // Check for partial matches
                                        foreach ($labels as $key => $label) {
                                            if (strpos($value_lower, $key) !== false) {
                                                return $label;
                                            }
                                        }
                                        // Return original value if no match
                                        return trim($value);
                                    }
                                    
                                    function resbs_get_heating_label($value) {
                                        if (empty($value)) return '';
                                        $value_lower = strtolower(trim($value));
                                        $labels = array(
                                            'central' => __('Central Heating', 'realestate-booking-suite'),
                                            'gas' => __('Gas Heating', 'realestate-booking-suite'),
                                            'electric' => __('Electric Heating', 'realestate-booking-suite'),
                                            'wood' => __('Wood Heating', 'realestate-booking-suite'),
                                            'none' => __('No Heating', 'realestate-booking-suite')
                                        );
                                        // Check for exact match first
                                        if (isset($labels[$value_lower])) {
                                            return $labels[$value_lower];
                                        }
                                        // Check for partial matches
                                        foreach ($labels as $key => $label) {
                                            if (strpos($value_lower, $key) !== false) {
                                                return $label;
                                            }
                                        }
                                        // Return original value if no match
                                        return trim($value);
                                    }
                                    
                                    function resbs_get_cooling_label($value) {
                                        if (empty($value)) return '';
                                        $value_lower = strtolower(trim($value));
                                        $labels = array(
                                            'central' => __('Central Air', 'realestate-booking-suite'),
                                            'window' => __('Window Units', 'realestate-booking-suite'),
                                            'none' => __('No Cooling', 'realestate-booking-suite')
                                        );
                                        // Check for exact match first
                                        if (isset($labels[$value_lower])) {
                                            return $labels[$value_lower];
                                        }
                                        // Check for partial matches
                                        foreach ($labels as $key => $label) {
                                            if (strpos($value_lower, $key) !== false) {
                                                return $label;
                                            }
                                        }
                                        // Return original value if no match
                                        return trim($value);
                                    }
                                    
                                    function resbs_get_basement_label($value) {
                                        if (empty($value)) return '';
                                        $value_lower = strtolower(trim($value));
                                        $labels = array(
                                            'finished' => __('Finished Basement', 'realestate-booking-suite'),
                                            'unfinished' => __('Unfinished Basement', 'realestate-booking-suite'),
                                            'crawl' => __('Crawl Space', 'realestate-booking-suite'),
                                            'none' => __('No Basement', 'realestate-booking-suite')
                                        );
                                        // Check for exact match first
                                        if (isset($labels[$value_lower])) {
                                            return $labels[$value_lower];
                                        }
                                        // Check for partial matches
                                        foreach ($labels as $key => $label) {
                                            if (strpos($value_lower, $key) !== false) {
                                                return $label;
                                            }
                                        }
                                        // Return original value if no match
                                        return trim($value);
                                    }
                                    
                                    function resbs_get_roof_label($value) {
                                        if (empty($value)) return '';
                                        $value_lower = strtolower(trim($value));
                                        $labels = array(
                                            'asphalt' => __('Asphalt Shingles', 'realestate-booking-suite'),
                                            'metal' => __('Metal Roof', 'realestate-booking-suite'),
                                            'tile' => __('Tile Roof', 'realestate-booking-suite'),
                                            'slate' => __('Slate Roof', 'realestate-booking-suite'),
                                            'wood' => __('Wood Shingles', 'realestate-booking-suite'),
                                            'shingle' => __('Shingle', 'realestate-booking-suite')
                                        );
                                        // Check for exact match first
                                        if (isset($labels[$value_lower])) {
                                            return $labels[$value_lower];
                                        }
                                        // Check for partial matches
                                        foreach ($labels as $key => $label) {
                                            if (strpos($value_lower, $key) !== false) {
                                                return $label;
                                            }
                                        }
                                        // Return original value if no match
                                        return trim($value);
                                    }
                                    
                                    // Ensure values are strings and trimmed
                                    $parking = !empty($parking) ? trim((string)$parking) : '';
                                    $heating = !empty($heating) ? trim((string)$heating) : '';
                                    $cooling = !empty($cooling) ? trim((string)$cooling) : '';
                                    $basement = !empty($basement) ? trim((string)$basement) : '';
                                    $roof = !empty($roof) ? trim((string)$roof) : '';
                                    $exterior_material = !empty($exterior_material) ? trim((string)$exterior_material) : '';
                                    $floor_covering = !empty($floor_covering) ? trim((string)$floor_covering) : '';
                                    ?>
                                    <?php if (!empty($parking)): ?>
                                    <div class="feature-detail-item">
                                        <div class="feature-detail-icon">
                                            <i class="fas fa-car"></i>
                                        </div>
                                        <div class="feature-detail-content">
                                            <span class="feature-detail-label"><?php echo esc_html__('Parking', 'realestate-booking-suite'); ?></span>
                                            <span class="feature-detail-value"><?php echo esc_html(resbs_get_parking_label($parking)); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($heating)): ?>
                                    <div class="feature-detail-item">
                                        <div class="feature-detail-icon">
                                            <i class="fas fa-thermometer-half"></i>
                                        </div>
                                        <div class="feature-detail-content">
                                            <span class="feature-detail-label"><?php echo esc_html__('Heating', 'realestate-booking-suite'); ?></span>
                                            <span class="feature-detail-value"><?php echo esc_html(resbs_get_heating_label($heating)); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($cooling)): ?>
                                    <div class="feature-detail-item">
                                        <div class="feature-detail-icon">
                                            <i class="fas fa-snowflake"></i>
                                        </div>
                                        <div class="feature-detail-content">
                                            <span class="feature-detail-label"><?php echo esc_html__('Cooling', 'realestate-booking-suite'); ?></span>
                                            <span class="feature-detail-value"><?php echo esc_html(resbs_get_cooling_label($cooling)); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($basement)): ?>
                                    <div class="feature-detail-item">
                                        <div class="feature-detail-icon">
                                            <i class="fas fa-layer-group"></i>
                                        </div>
                                        <div class="feature-detail-content">
                                            <span class="feature-detail-label"><?php echo esc_html__('Basement', 'realestate-booking-suite'); ?></span>
                                            <span class="feature-detail-value"><?php echo esc_html(resbs_get_basement_label($basement)); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($roof)): ?>
                                    <div class="feature-detail-item">
                                        <div class="feature-detail-icon">
                                            <i class="fas fa-home"></i>
                                        </div>
                                        <div class="feature-detail-content">
                                            <span class="feature-detail-label"><?php echo esc_html__('Roof', 'realestate-booking-suite'); ?></span>
                                            <span class="feature-detail-value"><?php echo esc_html(resbs_get_roof_label($roof)); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($exterior_material)): ?>
                                    <div class="feature-detail-item">
                                        <div class="feature-detail-icon">
                                            <i class="fas fa-cube"></i>
                                        </div>
                                        <div class="feature-detail-content">
                                            <span class="feature-detail-label"><?php echo esc_html__('Exterior Material', 'realestate-booking-suite'); ?></span>
                                            <span class="feature-detail-value"><?php echo esc_html(trim($exterior_material)); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($floor_covering)): ?>
                                    <div class="feature-detail-item">
                                        <div class="feature-detail-icon">
                                            <i class="fas fa-th-large"></i>
                                        </div>
                                        <div class="feature-detail-content">
                                            <span class="feature-detail-label"><?php echo esc_html__('Floor Covering', 'realestate-booking-suite'); ?></span>
                                            <span class="feature-detail-value"><?php echo esc_html(trim($floor_covering)); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Custom Fields Section -->
                            <?php
                            $custom_fields = get_option('resbs_custom_fields', array());
                            if (!empty($custom_fields) && is_array($custom_fields)) {
                                $has_custom_fields = false;
                                $custom_fields_data = array();
                                
                                foreach ($custom_fields as $field_id => $field) {
                                    if (!is_array($field) || empty($field['label'])) {
                                        continue;
                                    }
                                    
                                    $label = $field['label'];
                                    $meta_key = isset($field['meta_key']) ? trim($field['meta_key']) : '';
                                    
                                    if (empty($meta_key)) {
                                        $meta_key = '_property_' . sanitize_key(str_replace(' ', '_', strtolower($label)));
                                    } else {
                                        $meta_key = preg_replace('/^_property_+/', '', $meta_key);
                                        $meta_key = '_property_' . ltrim($meta_key, '_');
                                    }
                                    
                                    $value = get_post_meta($post->ID, $meta_key, true);
                                    
                                    if (!empty($value)) {
                                        $has_custom_fields = true;
                                        $custom_fields_data[] = array(
                                            'label' => $label,
                                            'value' => $value,
                                            'type' => isset($field['type']) ? $field['type'] : 'text'
                                        );
                                    }
                                }
                                
                                if ($has_custom_fields) {
                                    ?>
                                    <div class="property-features-section mb-8" style="margin-top: 40px;">
                                        <h4 class="text-lg font-semibold mb-4"><?php echo esc_html__('Additional Information', 'realestate-booking-suite'); ?></h4>
                                        <div class="property-features-grid">
                                            <?php foreach ($custom_fields_data as $custom_field): ?>
                                                <div class="feature-detail-item">
                                                    <div class="feature-detail-icon">
                                                        <i class="fas fa-info-circle"></i>
                                                    </div>
                                                    <div class="feature-detail-content">
                                                        <span class="feature-detail-label"><?php echo esc_html($custom_field['label']); ?></span>
                                                        <span class="feature-detail-value"><?php echo esc_html($custom_field['value']); ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>

                        <!-- Floor Plan Tab -->
                        <div id="floorplan-tab" class="tab-content">
                            <h3 class="section-title"><?php echo esc_html__('Floor Plan', 'realestate-booking-suite'); ?></h3>
                            <div class="floor-plan-container">
                                <?php if ($floor_plans): ?>
                                    <img src="<?php echo esc_url($floor_plans); ?>" alt="<?php echo esc_attr__('Floor Plan', 'realestate-booking-suite'); ?>" class="floor-plan-image">
                                    <div class="floor-plan-actions no-print">
                                        <button onclick="downloadFloorPlan()" class="btn btn-primary">
                                            <i class="fas fa-download mr-2"></i><?php echo esc_html__('Download Floor Plan', 'realestate-booking-suite'); ?>
                                        </button>
                                        <button onclick="requestCustomPlan()" class="btn" style="background-color: <?php echo esc_attr(resbs_get_secondary_color()); ?>; color: white;">
                                            <i class="fas fa-envelope mr-2"></i><?php echo esc_html__('Request Custom Plan', 'realestate-booking-suite'); ?>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-8">
                                        <i class="fas fa-home text-gray-400 text-6xl mb-4"></i>
                                        <p class="text-gray-500"><?php echo esc_html__('No floor plan available for this property.', 'realestate-booking-suite'); ?></p>
                                        <button onclick="requestCustomPlan()" class="btn btn-primary mt-4">
                                            <i class="fas fa-envelope mr-2"></i><?php echo esc_html__('Request Floor Plan', 'realestate-booking-suite'); ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Location Tab -->
                        <div id="location-tab" class="tab-content">
                            <h3 class="section-title"><?php echo esc_html__('Property Location', 'realestate-booking-suite'); ?></h3>
                            <p class="text-gray-600 mb-6"><?php echo esc_html__('Property address and location details', 'realestate-booking-suite'); ?></p>
                            
                            <!-- Address Information -->
                            <div class="mb-6">
                                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html__('Property Address', 'realestate-booking-suite'); ?></h4>
                                <div class="address-card">
                                    <div class="address-header">
                                        <i class="fas fa-map-marker-alt address-icon"></i>
                                        <h5 class="address-title"><?php echo esc_html__('Full Address', 'realestate-booking-suite'); ?></h5>
                                    </div>
                                    <div class="address-content">
                                        <?php if ($full_address): ?>
                                            <p class="address-main"><?php echo esc_html($full_address); ?></p>
                                        <?php else: ?>
                                            <p class="address-placeholder"><?php echo esc_html__('Address not specified', 'realestate-booking-suite'); ?></p>
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
                            <?php 
                            // Show map if enabled in settings and either map iframe OR coordinates exist
                            $has_map_data = ($map_iframe || (!empty($latitude) && !empty($longitude)));
                            // Initialize map_id with default value
                            $map_id = 'resbs-property-map-' . $post->ID;
                            
                            if ($has_map_data && resbs_should_show_map_single_listing()): 
                                // Use coordinates if available and no iframe (Leaflet doesn't need API key)
                                $use_coordinates = (!empty($latitude) && !empty($longitude) && !$map_iframe);
                            ?>
                            <div style="margin: 0 !important; padding: 0 !important; margin-bottom: 0 !important; padding-bottom: 0 !important; display: block;">
                                <h4 class="text-lg font-semibold" style="margin-bottom: 8px; margin-top: 0;"><?php echo esc_html__('Map Location', 'realestate-booking-suite'); ?></h4>
                                <div class="map-container" style="width: 100% !important; height: 300px !important; min-height: 300px !important; max-height: 300px !important; border-radius: 8px; overflow: hidden; position: relative; margin: 0 !important; padding: 0 !important; margin-bottom: 0 !important; padding-bottom: 0 !important; z-index: 1; display: block !important;">
                                    <?php if ($map_iframe): ?>
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
                                    <?php elseif ($use_coordinates): ?>
                                        <?php
                                        // Use the map ID already defined above
                                        $lat_val = floatval($latitude);
                                        $lng_val = floatval($longitude);
                                        ?>
                                        <!-- Map container - OpenStreetMap with Leaflet (NO API KEY NEEDED) -->
                                        <div id="<?php echo esc_attr($map_id); ?>" style="width: 100%; height: 300px; min-height: 300px; max-height: 300px; margin: 0 !important; padding: 0 !important; margin-bottom: 0 !important; padding-bottom: 0 !important; display: block !important; visibility: visible !important; position: relative; z-index: 1;"></div>
                                        <script type="text/javascript">
                                        (function() {
                                            var mapId = '<?php echo esc_js($map_id); ?>';
                                            var lat = <?php echo wp_json_encode($lat_val); ?>;
                                            var lng = <?php echo wp_json_encode($lng_val); ?>;
                                            var mapInstance = null;
                                            var mapInitialized = false;
                                            
                                            function initLeafletMap() {
                                                if (mapInitialized && mapInstance) return;
                                                
                                                var mapElement = document.getElementById(mapId);
                                                if (!mapElement) {
                                                    setTimeout(initLeafletMap, 50);
                                                    return;
                                                }
                                                
                                                // Ensure element is visible and has dimensions
                                                var rect = mapElement.getBoundingClientRect();
                                                if (rect.width === 0 || rect.height === 0) {
                                                    setTimeout(initLeafletMap, 100);
                                                    return;
                                                }
                                                
                                                // Force visible
                                                mapElement.style.display = 'block';
                                                mapElement.style.visibility = 'visible';
                                                mapElement.style.width = '100%';
                                                mapElement.style.height = '300px';
                                                
                                                // Check if Leaflet is loaded
                                                if (typeof L === 'undefined' || typeof L.map !== 'function') {
                                                    setTimeout(initLeafletMap, 100);
                                                    return;
                                                }
                                                
                                                try {
                                                    // Fix Leaflet icon paths
                                                    delete L.Icon.Default.prototype._getIconUrl;
                                                    L.Icon.Default.mergeOptions({
                                                        iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                                                        shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                                                        iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png'
                                                    });
                                                    
                                                    // Initialize map
                                                    mapInstance = L.map(mapId, {
                                                        center: [lat, lng],
                                                        zoom: 15,
                                                        zoomControl: true
                                                    });
                                                    
                                                    // Add OpenStreetMap tiles
                                                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                                                        maxZoom: 19
                                                    }).addTo(mapInstance);
                                                    
                                                    // Add marker
                                                    L.marker([lat, lng]).addTo(mapInstance);
                                                    
                                                    mapInitialized = true;
                                                    
                                                    // CRITICAL: Call invalidateSize multiple times to fix rendering
                                                    setTimeout(function() {
                                                        if (mapInstance) {
                                                            mapInstance.invalidateSize();
                                                        }
                                                    }, 100);
                                                    
                                                    setTimeout(function() {
                                                        if (mapInstance) {
                                                            mapInstance.invalidateSize();
                                                        }
                                                    }, 300);
                                                    
                                                    setTimeout(function() {
                                                        if (mapInstance) {
                                                            mapInstance.invalidateSize();
                                                        }
                                                    }, 600);
                                                    
                                                    setTimeout(function() {
                                                        if (mapInstance) {
                                                            mapInstance.invalidateSize();
                                                        }
                                                    }, 1000);
                                                    
                                                } catch(e) {
                                                    // Map initialization error - silently retry
                                                    mapInitialized = false;
                                                    setTimeout(initLeafletMap, 200);
                                                }
                                            }
                                            
                                            // Initialize when DOM is ready
                                            if (document.readyState === 'loading') {
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    setTimeout(initLeafletMap, 100);
                                                });
                                            } else {
                                                setTimeout(initLeafletMap, 100);
                                            }
                                            
                                            // Also try on window load
                                            window.addEventListener('load', function() {
                                                setTimeout(function() {
                                                    initLeafletMap();
                                                    if (mapInstance) {
                                                        mapInstance.invalidateSize();
                                                    }
                                                }, 200);
                                            });
                                            
                                            // Force retry after delays
                                            setTimeout(initLeafletMap, 500);
                                            setTimeout(initLeafletMap, 1000);
                                            setTimeout(initLeafletMap, 2000);
                                        })();
                                        </script>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <style type="text/css">
                            .single-property .map-container {
                                height: 300px !important;
                                min-height: 300px !important;
                                max-height: 300px !important;
                                margin-bottom: 0 !important;
                                padding-bottom: 0 !important;
                                margin: 0 !important;
                            }
                            #<?php echo esc_attr($map_id); ?>,
                            .map-container > div,
                            div[id^="resbs-property-map-"] {
                                margin-bottom: 0 !important;
                                padding-bottom: 0 !important;
                                margin: 0 !important;
                                height: 300px !important;
                            }
                            </style>
                            
                            <!-- Nearby Features -->
                            <div style="margin-bottom: 0; margin-top: 10px; padding-bottom: 0;">
                                <h4 class="text-lg font-semibold" style="margin-bottom: 8px; margin-top: 0;"><?php echo esc_html__('Nearby Features', 'realestate-booking-suite'); ?></h4>
                                <div class="nearby-features-grid" style="margin: 0; padding: 0; margin-top: 0;">
                                    <?php if ($nearby_schools): ?>
                                    <div class="location-feature location-feature-blue">
                                        <i class="fas fa-graduation-cap"></i>
                                        <h4><?php echo esc_html__('Schools', 'realestate-booking-suite'); ?></h4>
                                        <p><?php echo esc_html($nearby_schools); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($nearby_shopping): ?>
                                    <div class="location-feature location-feature-green">
                                        <i class="fas fa-shopping-cart"></i>
                                        <h4><?php echo esc_html__('Shopping', 'realestate-booking-suite'); ?></h4>
                                        <p><?php echo esc_html($nearby_shopping); ?></p>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($nearby_restaurants): ?>
                                    <div class="location-feature location-feature-purple">
                                        <i class="fas fa-utensils"></i>
                                        <h4><?php echo esc_html__('Restaurants', 'realestate-booking-suite'); ?></h4>
                                        <p><?php echo esc_html($nearby_restaurants); ?></p>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Reviews Tab -->
                        <div id="reviews-tab" class="tab-content">
                            <h3 class="section-title"><?php echo esc_html__('Property Reviews & Ratings', 'realestate-booking-suite'); ?></h3>
                            <p class="text-gray-600 mb-6"><?php echo esc_html__('Customer reviews and ratings for this property', 'realestate-booking-suite'); ?></p>
                            
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
                                        <p class="rating-count"><?php echo esc_html(sprintf(__('Based on %d %s', 'realestate-booking-suite'), $total_reviews, $total_reviews != 1 ? __('reviews', 'realestate-booking-suite') : __('review', 'realestate-booking-suite'))); ?></p>
                                    </div>
                                    
                                    <?php if ($total_reviews > 0): ?>
                                    <div class="rating-bars">
                                        <?php for ($star = 5; $star >= 1; $star--): ?>
                                            <?php $percentage = $total_reviews > 0 ? ($rating_counts[$star] / $total_reviews) * 100 : 0; ?>
                                            <div class="rating-bar">
                                                <span class="rating-label"><?php echo esc_html($star); ?>★</span>
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
                                                        <span class="review-date"><?php echo esc_html(sprintf(__('%s ago', 'realestate-booking-suite'), human_time_diff(strtotime($review->comment_date), current_time('timestamp')))); ?></span>
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
                                    <p class="text-gray-500"><?php echo esc_html__('No reviews yet for this property.', 'realestate-booking-suite'); ?></p>
                                    <p class="text-gray-400 text-sm mt-2"><?php echo esc_html__('Be the first to leave a review!', 'realestate-booking-suite'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Media Tab -->
                        <div id="media-tab" class="tab-content">
                            <h3 class="section-title"><?php echo esc_html__('Property Media', 'realestate-booking-suite'); ?></h3>
                            <p class="text-gray-600 mb-6"><?php echo esc_html__('Photos, videos, and virtual tours', 'realestate-booking-suite'); ?></p>
                            
                            <!-- Photo Gallery -->
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html__('Photo Gallery', 'realestate-booking-suite'); ?></h4>
                                <?php if (!empty($gallery_urls)): ?>
                                    <div class="media-gallery-row">
                                        <?php foreach ($gallery_urls as $index => $image_url): ?>
                                            <div class="media-gallery-item cursor-pointer"<?php if (!$lightbox_disabled): ?> onclick="<?php echo esc_js('openImageViewer(' . absint($index) . ')'); ?>"<?php endif; ?>>
                                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr__('Property Image', 'realestate-booking-suite'); ?>" class="media-gallery-image">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                                        <i class="fas fa-images text-gray-400 text-4xl mb-4"></i>
                                        <p class="text-gray-500"><?php echo esc_html__('No photos available for this property', 'realestate-booking-suite'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Property Video -->
                            <?php if ($video_url || $video_embed): ?>
                            <div class="mb-8">
                                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html__('Property Video', 'realestate-booking-suite'); ?></h4>
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
                                                    <?php echo esc_html__('Your browser does not support the video tag.', 'realestate-booking-suite'); ?>
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
                                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html__('Virtual Tour', 'realestate-booking-suite'); ?></h4>
                                <div class="virtual-tour-container">
                                    <?php if (!empty($virtual_tour_title)): ?>
                                    <h5 class="virtual-tour-title"><?php echo esc_html($virtual_tour_title); ?></h5>
                                    <?php endif; ?>
                                    <?php if (!empty($virtual_tour_description)): ?>
                                    <p class="virtual-tour-description"><?php echo esc_html($virtual_tour_description); ?></p>
                                    <?php endif; ?>
                                    <a href="<?php echo esc_url($virtual_tour); ?>" target="_blank" class="virtual-tour-button">
                                        <i class="fas fa-play mr-2"></i>
                                        <?php echo esc_html($virtual_tour_button_text); ?>
                                    </a>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Agent Tab -->
                        <div id="agent-tab" class="tab-content">
                            <h3 class="section-title"><?php echo esc_html__('Property Agent', 'realestate-booking-suite'); ?></h3>
                            <p class="text-gray-600 mb-6"><?php echo esc_html__('Contact information and agent details', 'realestate-booking-suite'); ?></p>
                            
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
                                        <h4 class="agent-profile-name"><?php echo esc_html($agent_name ? $agent_name : __('Not specified', 'realestate-booking-suite')); ?></h4>
                                        <p class="agent-profile-title"><?php echo esc_html__('Real Estate Agent', 'realestate-booking-suite'); ?></p>
                                        <div class="agent-rating-section">
                                            <div class="agent-stars">
                                                <?php
                                                $rating = intval($agent_rating ?: 5); // Default to 5 if no rating
                                                for ($i = 1; $i <= 5; $i++):
                                                    if ($i <= $rating) {
                                                        echo '<i class="fas fa-star"></i>';
                                                    } else {
                                                        echo '<i class="far fa-star"></i>';
                                                    }
                                                endfor;
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="agent-details-grid">
                                    <?php if ($agent_phone): ?>
                                    <div class="agent-detail-item">
                                        <i class="fas fa-phone agent-detail-icon"></i>
                                        <div class="agent-detail-content">
                                            <span class="agent-detail-label"><?php echo esc_html__('Phone', 'realestate-booking-suite'); ?></span>
                                            <span class="agent-detail-value"><?php echo esc_html($agent_phone); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($agent_email): ?>
                                    <div class="agent-detail-item">
                                        <i class="fas fa-envelope agent-detail-icon"></i>
                                        <div class="agent-detail-content">
                                            <span class="agent-detail-label"><?php echo esc_html__('Email', 'realestate-booking-suite'); ?></span>
                                            <span class="agent-detail-value"><?php echo esc_html($agent_email); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($agent_properties_sold): ?>
                                    <div class="agent-detail-item">
                                        <i class="fas fa-home agent-detail-icon"></i>
                                        <div class="agent-detail-content">
                                            <span class="agent-detail-label"><?php echo esc_html__('Properties Sold', 'realestate-booking-suite'); ?></span>
                                            <span class="agent-detail-value"><?php echo esc_html($agent_properties_sold); ?>+</span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($agent_experience): ?>
                                    <div class="agent-detail-item">
                                        <i class="fas fa-calendar agent-detail-icon"></i>
                                        <div class="agent-detail-content">
                                            <span class="agent-detail-label"><?php echo esc_html__('Experience', 'realestate-booking-suite'); ?></span>
                                            <span class="agent-detail-value"><?php echo esc_html($agent_experience); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($agent_response_time): ?>
                                    <div class="agent-detail-item">
                                        <i class="fas fa-clock agent-detail-icon"></i>
                                        <div class="agent-detail-content">
                                            <span class="agent-detail-label"><?php echo esc_html__('Response Time', 'realestate-booking-suite'); ?></span>
                                            <span class="agent-detail-value"><?php echo esc_html($agent_response_time); ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Contact Form -->
                            <div class="bg-gray-50 p-6 rounded-lg">
                                <?php if (!empty($contact_form_title)): ?>
                                <h4 class="text-lg font-semibold mb-4"><?php echo esc_html($contact_form_title); ?></h4>
                                <?php endif; ?>
                                <form class="space-y-4" onsubmit="submitContactForm(event)">
                                    <?php wp_nonce_field('resbs_contact_form', 'resbs_contact_form_nonce'); ?>
                                    <input type="hidden" name="property_id" value="<?php echo esc_attr($post->ID); ?>">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_name_label ? $contact_name_label : __('Your Name', 'realestate-booking-suite')); ?></label>
                                            <input type="text" name="contact_name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_email_label ? $contact_email_label : __('Email', 'realestate-booking-suite')); ?></label>
                                            <input type="email" name="contact_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_phone_label ? $contact_phone_label : __('Phone', 'realestate-booking-suite')); ?></label>
                                        <input type="tel" name="contact_phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2"><?php echo esc_html($contact_message_label ? $contact_message_label : __('Message', 'realestate-booking-suite')); ?></label>
                                        <textarea rows="4" name="contact_message" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-full">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        <?php echo esc_html($contact_submit_text ? $contact_submit_text : __('Send Message', 'realestate-booking-suite')); ?>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Booking Tab -->
                        <div id="booking-tab" class="tab-content">
                            <div class="bg-white p-8 rounded-xl shadow-lg border border-gray-100">
                                <?php if (!empty($booking_form_title)): ?>
                                <h4 class="text-xl font-bold text-gray-800 mb-2"><?php echo esc_html($booking_form_title); ?></h4>
                                <?php endif; ?>
                                <?php if (!empty($booking_form_subtitle)): ?>
                                <p class="text-gray-600 mb-8"><?php echo esc_html($booking_form_subtitle); ?></p>
                                <?php endif; ?>
                                
                                <form id="directBookingForm" method="post" action="#">
                                    <?php wp_nonce_field('resbs_booking_form', 'resbs_booking_form_nonce'); ?>
                                    <input type="hidden" name="property_id" value="<?php echo esc_attr($post->ID); ?>">
                                    <div class="space-y-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <label for="bookingName" class="block text-sm font-semibold text-gray-700"><?php echo esc_html($booking_name_label ? $booking_name_label : __('Your Name', 'realestate-booking-suite')); ?> *</label>
                                                <input type="text" id="bookingName" name="bookingName" required 
                                                       placeholder="<?php echo esc_attr($booking_name_label ? $booking_name_label : __('Enter your full name', 'realestate-booking-suite')); ?>"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200">
                                            </div>
                                            <div class="space-y-2">
                                                <label for="bookingEmail" class="block text-sm font-semibold text-gray-700"><?php echo esc_html($booking_email_label ? $booking_email_label : __('Email', 'realestate-booking-suite')); ?> *</label>
                                                <input type="email" id="bookingEmail" name="bookingEmail" required 
                                                       placeholder="<?php echo esc_attr($booking_email_label ? $booking_email_label : __('Enter your email address', 'realestate-booking-suite')); ?>"
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200">
                                            </div>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="space-y-2">
                                                <label for="bookingPhone" class="block text-sm font-semibold text-gray-700"><?php echo esc_html($booking_phone_label ? $booking_phone_label : __('Phone', 'realestate-booking-suite')); ?></label>
                                                    <input type="tel" id="bookingPhone" name="bookingPhone" 
                                                           placeholder="<?php echo esc_attr($booking_phone_label ? $booking_phone_label : __('Enter your phone number', 'realestate-booking-suite')); ?>"
                                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200">
                                            </div>
                                            <div class="space-y-2">
                                                <label for="bookingDate" class="block text-sm font-semibold text-gray-700"><?php echo esc_html($booking_date_label ? $booking_date_label : __('Preferred Date', 'realestate-booking-suite')); ?> *</label>
                                                <input type="date" id="bookingDate" name="bookingDate" required 
                                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200">
                                            </div>
                                        </div>

                                        
                                        <div class="space-y-2">
                                            <label for="bookingMessage" class="block text-sm font-semibold text-gray-700"><?php echo esc_html($booking_message_label ? $booking_message_label : __('Additional Message', 'realestate-booking-suite')); ?></label>
                                            <textarea id="bookingMessage" name="bookingMessage" rows="4" 
                                                      placeholder="<?php echo esc_attr($booking_message_label ? $booking_message_label : __('Any specific requirements or questions about the property...', 'realestate-booking-suite')); ?>"
                                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition duration-200 resize-none"></textarea>
                                        </div>
                                        
                                        <div class="pt-4">
                                            <button type="submit" id="resbs-booking-submit-btn" class="w-full bg-white hover:bg-green-700  font-bold py-4 px-6 rounded-lg transition duration-200 flex items-center justify-center  transform ">
                                                <i class="fas fa-calendar-check mr-3 text-lg"></i><?php echo esc_html($booking_submit_text ? $booking_submit_text : __('Schedule Property Viewing', 'realestate-booking-suite')); ?>
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
                    <h3 class="section-title"><?php echo esc_html__('Similar Properties', 'realestate-booking-suite'); ?></h3>
                    <p class="text-gray-600 mb-6"><?php echo esc_html__('Other properties you might be interested in', 'realestate-booking-suite'); ?></p>
                    
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
                                
                                $formatted_similar_price = $similar_price ? resbs_format_price($similar_price) : __('Price on request', 'realestate-booking-suite');
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
                                            <?php echo esc_html($similar_location ? $similar_location : __('Location not specified', 'realestate-booking-suite')); ?>
                                        </p>
                                        <div class="property-card-price"><?php echo esc_html($formatted_similar_price); ?></div>
                                        <div class="property-card-features">
                                            <?php if ($similar_bedrooms): ?>
                                                <span><i class="fas fa-bed mr-1"></i><?php echo esc_html($similar_bedrooms); ?> <?php echo esc_html($similar_bedrooms != 1 ? __('Beds', 'realestate-booking-suite') : __('Bed', 'realestate-booking-suite')); ?></span>
                                            <?php endif; ?>
                                            <?php if ($similar_bathrooms): ?>
                                                <span><i class="fas fa-bath mr-1"></i><?php echo esc_html($similar_bathrooms); ?> <?php echo esc_html($similar_bathrooms != 1 ? __('Baths', 'realestate-booking-suite') : __('Bath', 'realestate-booking-suite')); ?></span>
                                            <?php endif; ?>
                                            <?php if ($similar_area): ?>
                                                <span><i class="fas fa-ruler-combined mr-1"></i><?php echo esc_html(resbs_format_area($similar_area)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-home text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500"><?php echo esc_html__('No similar properties found.', 'realestate-booking-suite'); ?></p>
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
                            <div class="agent-avatar" style="background-color: <?php echo esc_attr(resbs_get_main_color()); ?>; color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                        <h3 class="agent-name"><?php echo esc_html($agent_name); ?></h3>
                        <p class="agent-title"><?php echo esc_html__('Real Estate Agent', 'realestate-booking-suite'); ?></p>
                        <div class="agent-rating">
                            <?php
                                $rating = intval($agent_rating ?: 5); // Default to 5 if no rating
                                for ($i = 1; $i <= 5; $i++):
                                    if ($i <= $rating) {
                                        echo '<i class="fas fa-star rating-star text-sm text-yellow-400"></i>';
                                    } else {
                                        echo '<i class="far fa-star rating-star text-sm text-gray-300"></i>';
                                    }
                                endfor;
                            ?>
                        </div>
                    </div>

                    <div class="agent-actions">
                        <?php if ($agent_phone): ?>
                        <a href="tel:<?php echo esc_attr($agent_phone); ?>" class="agent-action agent-action-primary">
                            <i class="fas fa-phone mr-2"></i>
                            <span><?php echo esc_html__('Call Agent', 'realestate-booking-suite'); ?></span>
                        </a>
                        <?php endif; ?>
                        <button onclick="openContactModal()" class="agent-action agent-action-secondary">
                            <i class="fas fa-envelope mr-2"></i>
                            <span><?php echo esc_html($agent_send_message_text ? $agent_send_message_text : __('Send Message', 'realestate-booking-suite')); ?></span>
                        </button>
                    </div>

                    <div class="agent-stats">
                        <div class="agent-stat">
                            <span class="agent-stat-label"><?php echo esc_html__('Properties Sold:', 'realestate-booking-suite'); ?></span>
                            <span class="agent-stat-value"><?php echo esc_html($agent_properties_sold); ?>+</span>
                        </div>
                        <div class="agent-stat">
                            <span class="agent-stat-label"><?php echo esc_html__('Experience:', 'realestate-booking-suite'); ?></span>
                            <span class="agent-stat-value"><?php echo esc_html($agent_experience); ?></span>
                        </div>
                        <div class="agent-stat">
                            <span class="agent-stat-label"><?php echo esc_html__('Response Time:', 'realestate-booking-suite'); ?></span>
                            <span class="agent-stat-value"><?php echo esc_html($agent_response_time); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Mortgage Calculator -->
                <div class="card">
                    <?php if (!empty($mortgage_calculator_title)): ?>
                    <h3 class="section-title"><?php echo esc_html($mortgage_calculator_title); ?></h3>
                    <?php endif; ?>
                    <div class="space-y-4">
                        <div class="calculator-input">
                            <label class="calculator-label"><?php echo esc_html(!empty($mortgage_property_price_label) ? $mortgage_property_price_label : __('Property Price', 'realestate-booking-suite')); ?></label>
                            <input type="text" id="propertyPrice" value="<?php echo esc_attr($price && !$call_for_price ? number_format($price) : ''); ?>" class="calculator-field" onkeyup="calculateMortgage()" placeholder="<?php echo esc_attr($call_for_price ? __('Enter price', 'realestate-booking-suite') : ''); ?>">
                        </div>
                        <div class="calculator-input">
                            <label class="calculator-label"><?php echo esc_html(!empty($mortgage_down_payment_label) ? $mortgage_down_payment_label : __('Down Payment (%)', 'realestate-booking-suite')); ?></label>
                            <input type="range" id="downPayment" min="0" max="100" value="<?php echo esc_attr($mortgage_default_down_payment ? $mortgage_default_down_payment : ($mortgage_default_down_payment_global ? $mortgage_default_down_payment_global : '20')); ?>" class="calculator-slider" oninput="updateDownPayment(this.value); calculateMortgage()">
                            <div class="calculator-slider-labels">
                                <span>0%</span>
                                <span id="downPaymentValue" class="calculator-slider-value"><?php echo esc_html($mortgage_default_down_payment ? $mortgage_default_down_payment : ($mortgage_default_down_payment_global ? $mortgage_default_down_payment_global : '20')); ?>%</span>
                                <span>100%</span>
                            </div>
                        </div>
                        <div class="calculator-input">
                            <label class="calculator-label"><?php echo esc_html(!empty($mortgage_interest_rate_label) ? $mortgage_interest_rate_label : __('Interest Rate (%)', 'realestate-booking-suite')); ?></label>
                            <input type="number" id="interestRate" value="<?php echo esc_attr($mortgage_default_interest_rate ? $mortgage_default_interest_rate : ($mortgage_default_interest_rate_global ? $mortgage_default_interest_rate_global : '6.5')); ?>" step="0.1" class="calculator-field" onkeyup="calculateMortgage()">
                        </div>
                        <div class="calculator-input">
                            <label class="calculator-label"><?php echo esc_html(!empty($mortgage_loan_term_label) ? $mortgage_loan_term_label : __('Loan Term (Years)', 'realestate-booking-suite')); ?></label>
                            <select id="loanTerm" class="calculator-field" onchange="calculateMortgage()">
                                <?php
                                    // Use global default loan term
                                    $default_term = $mortgage_default_loan_term_global;
                                    // If no default term is set, use 30 years as a user-friendly default
                                    if (empty($default_term) || !is_numeric($default_term)) {
                                        $default_term = '30';
                                    }
                                    
                                    // Always generate 1-50 years range for user-friendly experience
                                    for ($year = 1; $year <= 50; $year++):
                                ?>
                                    <option value="<?php echo esc_attr($year); ?>"<?php selected($year, $default_term); ?>><?php echo esc_html($year); ?> <?php echo esc_html__('Years', 'realestate-booking-suite'); ?></option>
                                <?php 
                                    endfor;
                                ?>
                            </select>
                        </div>
                        <div class="calculator-result">
                            <p class="calculator-result-label"><?php echo esc_html(!empty($mortgage_monthly_payment_label) ? $mortgage_monthly_payment_label : __('Estimated Monthly Payment', 'realestate-booking-suite')); ?></p>
                            <p class="calculator-result-value" id="monthlyPayment"><?php echo esc_html(resbs_get_currency_symbol()); ?>0</p>
                            <?php if (!empty($mortgage_disclaimer_text)): ?>
                            <p class="calculator-result-note"><?php echo esc_html($mortgage_disclaimer_text); ?></p>
                            <?php endif; ?>
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
        <img id="viewerImage" src="" alt="<?php echo esc_attr__('Property', 'realestate-booking-suite'); ?>">
    </div>

    <!-- Contact Modal -->
    <div id="contactModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title"><?php echo esc_html(!empty($contact_form_title) ? $contact_form_title : __('Contact Agent', 'realestate-booking-suite')); ?></h3>
                <button onclick="closeContactModal()" class="modal-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form onsubmit="return submitContactForm(event); return false;" class="space-y-4">
                <?php wp_nonce_field('resbs_contact_form', 'resbs_contact_form_nonce'); ?>
                <input type="hidden" name="property_id" value="<?php echo esc_attr($post->ID); ?>">
                <div class="form-group">
                    <label class="form-label"><?php echo esc_html($contact_name_label ? $contact_name_label : __('Your Name', 'realestate-booking-suite')); ?></label>
                    <input type="text" required class="form-input" name="contact_name">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo esc_html($contact_email_label ? $contact_email_label : __('Email', 'realestate-booking-suite')); ?></label>
                    <input type="email" required class="form-input" name="contact_email">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo esc_html($contact_phone_label ? $contact_phone_label : __('Phone', 'realestate-booking-suite')); ?></label>
                    <input type="tel" required class="form-input" name="contact_phone">
                </div>
                <div class="form-group">
                    <label class="form-label"><?php echo esc_html($contact_message_label ? $contact_message_label : __('Message', 'realestate-booking-suite')); ?></label>
                    <textarea rows="4" required class="form-input form-textarea" name="contact_message"></textarea>
                </div>
                <button type="submit" class="form-submit">
                    <?php echo esc_html($contact_submit_text ? $contact_submit_text : __('Send Message', 'realestate-booking-suite')); ?>
                </button>
            </form>
        </div>
    </div>

    </div>

    <!-- PHP data is now passed via wp_localize_script in template assets class -->
    <!-- Also pass gallery images directly from template as backup -->
    <script type="text/javascript">
        // Ensure gallery images are available to JavaScript
        (function() {
            var templateGalleryImages = <?php echo wp_json_encode($gallery_urls); ?>;
            if (templateGalleryImages && templateGalleryImages.length > 0) {
                // Set on window object for global access
                window.galleryImagesFromTemplate = templateGalleryImages;
                // Also update resbs_ajax if it exists
                if (typeof resbs_ajax !== 'undefined') {
                    resbs_ajax.gallery_images = templateGalleryImages;
                }
                // Also set on window.galleryImages
                window.galleryImages = templateGalleryImages;
            }
        })();
    </script>
    
    <!-- Amenities and phone code styles are now enqueued via wp_enqueue_style -->

    <?php endwhile; ?>
<?php else : ?>
    <div class="container" style="padding: 20px;">
        <h1><?php echo esc_html__('Property Not Found', 'realestate-booking-suite'); ?></h1>
        <p><?php echo esc_html__('Sorry, the property you\'re looking for doesn\'t exist or has been removed.', 'realestate-booking-suite'); ?></p>
        <a href="<?php echo esc_url(home_url('/property/')); ?>" style="background: <?php echo esc_attr(resbs_get_main_color()); ?>; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;"><?php echo esc_html__('Back to Properties', 'realestate-booking-suite'); ?></a>
    </div>
<?php endif; ?>

<?php
// Use helper function to safely get footer (avoids deprecation warnings in block themes)
resbs_get_footer();
?>
