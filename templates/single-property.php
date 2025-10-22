<?php
    /**
     * Single Property Template - Dynamic Version
     *
     * @package RealEstate_Booking_Suite
     */

    // Prevent direct access
    if (! defined('ABSPATH')) {
        exit;
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
    $mortgage_loan_terms            = get_post_meta($post->ID, '_property_mortgage_loan_terms', true);
    $mortgage_disclaimer_text       = get_post_meta($post->ID, '_property_mortgage_disclaimer_text', true);

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
    }

    $amenities_array = [];
    if ($amenities && is_string($amenities)) {
        $amenities_array = explode(',', $amenities);
        $amenities_array = array_map('trim', $amenities_array);
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(get_the_title()); ?> - Property Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        :root {
    --rebs-primary-color: #10b981;
    --rebs-primary-dark: #059669;
    --rebs-primary-light: #d1fae5;
    --rebs-secondary-color: #1f2937;
    --rebs-light-gray: #f9fafb;
    --rebs-medium-gray: #e5e7eb;
    --rebs-dark-gray: #6b7280;
    --rebs-text-dark: #1f2937;
    --rebs-text-light: #6b7280;
    --rebs-white: #ffffff;
    --rebs-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    --rebs-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --rebs-radius: 0.5rem;
    --rebs-radius-lg: 0.75rem;
}

*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;

}

/* Reset and Base Styles */
.single-property{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Inter', sans-serif;
    background: #f9fafb !important
}

/* Ensure Font Awesome icons display properly */
.single-property .fas,
.single-property .far,
.single-property .fab,
.single-property .fal,
.single-property .fa {
    font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 6 Brands" !important;
    font-weight: 900 !important;
    font-style: normal !important;
    font-variant: normal !important;
    text-rendering: auto !important;
    line-height: 1 !important;
    -webkit-font-smoothing: antialiased !important;
    -moz-osx-font-smoothing: grayscale !important;
}

.single-property .far {
    font-weight: 400 !important;
}

.single-property .fab {
    font-family: "Font Awesome 6 Brands" !important;
    font-weight: 400 !important;
}

.single-property body {
    background-color: var(--rebs-light-gray);
    color: var(--rebs-text-dark);
    line-height: 1.6;
}

/* Layout - Simple Background */
.single-property .main-single-container {
    max-width: 1536px;
    margin: 0 auto;
    padding: 0 1rem;
    min-height: 100vh;
}

.single-property .grid {
    display: grid;
    gap: 2rem;
}

.single-property .grid-cols-1 {
    grid-template-columns: 1fr;
}

@media (min-width: 768px) {
    .single-property .grid-cols-2 {
        grid-template-columns: repeat(2, 1fr);
    }

    .single-property .grid-cols-3 {
        grid-template-columns: repeat(3, 1fr);
    }

    .single-property .grid-cols-4 {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (min-width: 1024px) {
    .single-property .lg-grid-cols-3 {
        grid-template-columns: 1fr 1fr 1fr;
    }
}

/* Header */
.single-property header {
    background-color: var(--rebs-white);
    box-shadow: var(--rebs-shadow);
    position: sticky;
    top: 0;
    z-index: 50;
}

.single-property .header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 0;
}

.single-property .header-left {
    display: flex;
    align-items: center;
    gap: 2rem;
}

.single-property .logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.single-property .logo-icon {
    width: 2.5rem;
    height: 2.5rem;
    background: linear-gradient(to right, var(--rebs-primary-color), #0d9488);
    border-radius: var(--rebs-radius);
    display: flex;
    align-items: center;
    justify-content: center;
}

.single-property .logo-icon i {
    color: var(--rebs-white);
    font-size: 1.25rem;
}

.single-property .logo-text {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--rebs-text-dark);
}

.single-property .nav-desktop {
    display: none;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .single-property .nav-desktop {
        display: flex;
    }
}

.single-property .nav-link {
    color: var(--rebs-dark-gray);
    text-decoration: none;
    transition: color 0.3s;
}

.single-property .nav-link:hover {
    color: var(--rebs-primary-color);
}

.single-property .header-right {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.single-property .header-icon {
    color: var(--rebs-dark-gray);
    background: none;
    border: none;
    cursor: pointer;
    transition: color 0.3s;
}

.single-property .header-icon:hover {
    color: var(--rebs-primary-color);
}

.single-property .btn {
    display: inline-block;
    padding: 0.5rem 1.5rem;
    border-radius: var(--rebs-radius);
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
}

.single-property .btn-primary {
    background-color: var(--rebs-primary-color);
    color: var(--rebs-white);
}

.single-property .btn-primary:hover {
    background-color: var(--rebs-primary-dark);
}

.single-property .menu-toggle {
    display: block;
    color: var(--rebs-dark-gray);
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
}

@media (min-width: 768px) {
    .single-property .menu-toggle {
        display: none;
    }
}

/* Mobile Menu */
.single-property .mobile-menu {
    display: none;
    background-color: var(--rebs-white);
    box-shadow: var(--rebs-shadow);
}

.single-property .mobile-menu.active {
    display: block;
}

.single-property .mobile-nav {
    padding: 1rem 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.single-property .mobile-nav a {
    color: var(--rebs-dark-gray);
    text-decoration: none;
    transition: color 0.3s;
}

.single-property .mobile-nav a:hover {
    color: var(--rebs-primary-color);
}

/* Breadcrumb */
.single-property .breadcrumb {
    background-color: var(--rebs-white);
    border-bottom: 1px solid var(--rebs-medium-gray);
    padding: 0.75rem 0;
}

.single-property .breadcrumb-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.single-property .breadcrumb-link {
    color: var(--rebs-dark-gray);
    text-decoration: none;
    transition: color 0.3s;
}

.single-property .breadcrumb-link:hover {
    color: var(--rebs-primary-color);
}

.single-property .breadcrumb-separator {
    color: var(--rebs-dark-gray);
    font-size: 0.75rem;
}

.single-property .breadcrumb-current {
    color: var(--rebs-text-dark);
    font-weight: 500;
}

/* Main Content */
.single-property .main-content {
    padding: 2rem 0;
}

/* Cards - Simple Background */
.single-property .card {
    background: #ffffff;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    border: 1px solid #e5e7eb;
}

/* Property Header */
.single-property .property-header {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

@media (min-width: 768px) {
    .single-property .property-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
    }
}

.single-property .property-badges {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.single-property .badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--rebs-white);
}

.single-property .badge-primary {
    background-color: var(--rebs-primary-color);
}

.single-property .badge-blue {
    background-color: #3b82f6;
}

.single-property .badge-orange {
    background-color: #f97316;
}

.single-property .property-title {
    font-size: 1.875rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

@media (min-width: 768px) {
    .single-property .property-title {
        font-size: 2.25rem;
    }
}

.single-property .property-location {
    color: var(--rebs-dark-gray);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.single-property .property-price {
    font-size: 2.25rem;
    font-weight: 700;
    color: var(--rebs-primary-color);
}

.single-property .property-price-per-unit {
    color: var(--rebs-dark-gray);
    font-size: 0.875rem;
}

.single-property .property-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 20px !important;
}

.single-property .action-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: 1px solid var(--rebs-medium-gray);
    border-radius: var(--rebs-radius);
    background-color: var(--rebs-white);
    cursor: pointer;
    transition: all 0.3s;
}

.single-property .action-btn:hover {
    background-color: var(--rebs-light-gray);
}

.single-property .action-btn-primary {
    background-color: var(--rebs-primary-color);
    color: var(--rebs-white);
    border-color: var(--rebs-primary-color);
}

.single-property .action-btn-primary:hover {
    background-color: var(--rebs-primary-dark);
}

/* Gallery */
.single-property .gallery {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}

.single-property .gallery-item {
    border-radius: var(--rebs-radius);
    overflow: hidden;
    cursor: pointer;
}

.single-property .gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.single-property .gallery-item:hover img {
    transform: scale(1.05);
}

.single-property .gallery-main {
    grid-column: span 4;
    grid-row: span 2;
}

@media (min-width: 768px) {
    .single-property .gallery-main {
        grid-column: span 2;
    }
}

.single-property .gallery-more {
    position: relative;
}

.single-property .gallery-overlay {
    position: absolute;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: var(--rebs-radius);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}

.single-property .gallery-overlay span {
    color: var(--rebs-white);
    font-size: 1.25rem;
    font-weight: 600;
}

/* Key Features */
.single-property .key-features-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

@media (min-width: 768px) {
    .single-property .key-features-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.single-property .feature-item {
    text-align: center;
    padding: 1rem;
    background-color: var(--rebs-light-gray);
    border-radius: var(--rebs-radius);
}

.single-property .feature-icon {
    font-size: 1.875rem;
    color: var(--rebs-primary-color);
    margin-bottom: 0.5rem;
    display: inline-block;
    width: auto;
    height: auto;
}

.single-property .feature-value {
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.single-property .feature-label {
    color: var(--rebs-dark-gray);
    font-size: 0.875rem;
}

/* Tabs - Unified White Box Design */
.single-property .tabs {
    background: #ffffff;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    margin-bottom: 2rem;
    overflow: hidden;
}

.single-property .tabs-header {
    display: flex;
    overflow-x: auto;
    background: transparent;
    border-bottom: 1px solid #e5e7eb;
    padding: 0;
    margin-bottom: 0;
}

.single-property .tab-button {
    background: transparent;
    border: none;
    padding: 1rem 1.5rem;
    cursor: pointer;
    font-weight: 600;
    color: #4b5563;
    transition: color 0.2s ease;
    border-bottom: 2px solid transparent;
    white-space: nowrap;
    position: relative;
    font-size: 0.875rem;
    text-transform: none;
    letter-spacing: 0;
    margin-right: 0;
    font-size: 16px !important;
}

.single-property .tab-button:hover {
    color: #10b981;
    background: transparent;
    transform: none;
}

.single-property .tab-button.active {
    color: #10b981;
    border-bottom-color: #10b981;
    background: transparent;
    box-shadow: none;
    font-weight: 600;
}

.single-property .tabs-content {
    padding: 2rem;
    background: transparent;
    border-radius: 0;
    border: none;
}

.single-property .tab-content {
    display: none;
}

.single-property .tab-content.active {
    display: block;
}

.single-property .section-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

/* Info Box */
.single-property .info-box {
    padding: 1rem;
    background-color: var(--rebs-primary-light);
    border-left: 4px solid var(--rebs-primary-color);
    border-radius: var(--rebs-radius);
    margin-top: 1.5rem;
}

.single-property .info-box p {
    color: #065f46;
}

/* Property Details */
.single-property .details-grid {
    display: grid;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .single-property .details-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.single-property .detail-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--rebs-medium-gray);
}

.single-property .detail-label {
    color: var(--rebs-dark-gray);
}

.single-property .detail-value {
    font-weight: 600;
}

/* Amenities */
.single-property .amenities-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1.5rem;
}

.single-property .filter-btn {
    padding: 0.5rem 1rem;
    border-radius: var(--rebs-radius);
    font-weight: 600;
    font-size: 0.875rem;
    background-color: var(--rebs-light-gray);
    color: var(--rebs-dark-gray);
    border: none;
    cursor: pointer;
    transition: all 0.3s;
}

.single-property .filter-btn:hover {
    background-color: var(--rebs-medium-gray);
}

.single-property .filter-btn.active {
    background-color: var(--rebs-primary-color);
    color: var(--rebs-white);
}

.single-property .amenities-grid {
    display: grid;
    gap: 1rem;
}

@media (min-width: 768px) {
    .single-property .amenities-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .single-property .amenities-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.single-property .amenity-item {
    padding: 1rem;
    background-color: var(--rebs-light-gray);
    border-radius: var(--rebs-radius);
    transition: all 0.3s;
}

.single-property .amenity-item:hover {
    background-color: #f0fdf4;
    transform: translateY(-2px);
}

.single-property .amenity-item i {
    color: var(--rebs-primary-color);
    margin-right: 0.5rem;
}

/* Floor Plan */
.single-property .floor-plan-container {
    background-color: var(--rebs-light-gray);
    border-radius: var(--rebs-radius);
    padding: 2rem;
    text-align: center;
}

.single-property .floor-plan-image {
    max-width: 48rem;
    margin: 0 auto;
    border-radius: var(--rebs-radius);
    box-shadow: var(--rebs-shadow-lg);
}

.single-property .floor-plan-actions {
    margin-top: 1rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
}

/* Map */
.single-property #map {
    height: 400px;
    width: 100%;
    border-radius: var(--rebs-radius);
    margin-bottom: 1.5rem;
}

.single-property .location-features {
    display: grid;
    gap: 1rem;
}

@media (min-width: 768px) {
    .single-property .location-features {
        grid-template-columns: repeat(3, 1fr);
    }
}

.single-property .location-feature {
    padding: 1rem;
    border-radius: var(--rebs-radius);
}

.single-property .location-feature i {
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.single-property .location-feature h4 {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.single-property .location-feature p {
    font-size: 0.875rem;
    color: var(--rebs-dark-gray);
}

.single-property .location-feature-blue {
    background-color: #dbeafe;
}

.single-property .location-feature-blue i {
    color: #3b82f6;
}

.single-property .location-feature-green {
    background-color: #dcfce7;
}

.single-property .location-feature-green i {
    color: #22c55e;
}

.single-property .location-feature-purple {
    background-color: #f3e8ff;
}

.single-property .location-feature-purple i {
    color: #a855f7;
}

.single-property .location-feature-red {
    background-color: #fee2e2;
}

.single-property .location-feature-red i {
    color: #ef4444;
}

.single-property .location-feature-yellow {
    background-color: #fef3c7;
}

.single-property .location-feature-yellow i {
    color: #f59e0b;
}

.single-property .location-feature-teal {
    background-color: #ccfbf1;
}

.single-property .location-feature-teal i {
    color: #14b8a6;
}

/* Reviews */
.single-property .rating-overview {
    background: linear-gradient(to right, var(--rebs-primary-light), #ccfbf1);
    border-radius: var(--rebs-radius);
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.single-property .rating-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
}

@media (min-width: 768px) {
    .single-property .rating-content {
        flex-direction: row;
    }
}

.single-property .rating-score {
    text-align: center;
    margin-bottom: 1rem;
}

@media (min-width: 768px) {
    .single-property .rating-score {
        text-align: left;
        margin-bottom: 0;
    }
}

.single-property .rating-value {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.single-property .rating-stars {
    display: flex;
    margin-bottom: 0.5rem;
}

.single-property .rating-star {
    color: #fbbf24;
}

.single-property .rating-count {
    color: var(--rebs-dark-gray);
}

.single-property .rating-bars {
    width: 100%;
}

@media (min-width: 768px) {
    .single-property .rating-bars {
        width: 50%;
    }
}

.single-property .rating-bar {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.single-property .rating-label {
    width: 2rem;
    font-size: 0.875rem;
    color: var(--rebs-dark-gray);
}

.single-property .rating-bar-track {
    flex: 1;
    background-color: var(--rebs-medium-gray);
    border-radius: 9999px;
    height: 0.5rem;
    margin: 0 0.5rem;
}

.single-property .rating-bar-fill {
    background-color: var(--rebs-primary-color);
    height: 100%;
    border-radius: 9999px;
}

.single-property .rating-bar-count {
    width: 2rem;
    font-size: 0.875rem;
    color: var(--rebs-dark-gray);
    text-align: right;
}

.single-property .reviews-controls {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

@media (min-width: 768px) {
    .single-property .reviews-controls {
        flex-direction: row;
    }
}

.single-property .review-search {
    flex: 1;
    padding: 0.5rem 1rem;
    border: 1px solid var(--rebs-medium-gray);
    border-radius: var(--rebs-radius);
    outline: none;
}

.single-property .review-search:focus {
    border-color: var(--rebs-primary-color);
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.single-property .review-sort {
    padding: 0.5rem 1rem;
    border: 1px solid var(--rebs-medium-gray);
    border-radius: var(--rebs-radius);
    outline: none;
}

.single-property .review-sort:focus {
    border-color: var(--rebs-primary-color);
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.single-property .review-item {
    border-bottom: 1px solid var(--rebs-medium-gray);
    padding-bottom: 1rem;
    margin-bottom: 1rem;
}

.single-property .review-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.single-property .review-user {
    display: flex;
    align-items: center;
}

.single-property .review-avatar {
    width: 3rem;
    height: 3rem;
    border-radius: 9999px;
    margin-right: 0.75rem;
}

.single-property .review-name {
    font-weight: 600;
}

.single-property .review-meta {
    display: flex;
    align-items: center;
}

.single-property .review-date {
    font-size: 0.875rem;
    color: var(--rebs-dark-gray);
    margin-left: 0.5rem;
}

.single-property .review-text {
    color: var(--rebs-dark-gray);
    margin-bottom: 0.5rem;
}

.single-property .review-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.875rem;
}

.single-property .review-action {
    color: var(--rebs-dark-gray);
    background: none;
    border: none;
    cursor: pointer;
    transition: color 0.3s;
}

.single-property .review-action:hover {
    color: var(--rebs-primary-color);
}

.single-property .load-more {
    width: 100%;
    padding: 0.75rem 1.5rem;
    background-color: var(--rebs-light-gray);
    color: var(--rebs-dark-gray);
    border: none;
    border-radius: var(--rebs-radius);
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: 1.5rem;
}

.single-property .load-more:hover {
    background-color: var(--rebs-medium-gray);
}

/* Similar Properties */
.single-property .similar-properties-grid {
    display: grid;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .single-property .similar-properties-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.single-property .property-card {
    border: 1px solid var(--rebs-medium-gray);
    border-radius: var(--rebs-radius);
    overflow: hidden;
    transition: all 0.3s;
}

.single-property .property-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.single-property .property-image {
    position: relative;
}

.single-property .property-image img {
    width: 100%;
    height: 12rem;
    object-fit: cover;
}

.single-property .property-badge {
    position: absolute;
    top: 0.75rem;
    left: 0.75rem;
    background-color: var(--rebs-primary-color);
    color: var(--rebs-white);
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.single-property .property-info {
    padding: 1rem;
}

.single-property .property-card-title {
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.single-property .property-card-location {
    color: var(--rebs-dark-gray);
    font-size: 0.875rem;
    margin-bottom: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.single-property .property-card-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--rebs-primary-color);
    margin-bottom: 0.75rem;
}

.single-property .property-card-features {
    display: flex;
    align-items: center;
    gap: 1rem;
    font-size: 0.875rem;
    color: var(--rebs-dark-gray);
    border-top: 1px solid var(--rebs-medium-gray);
    padding-top: 0.75rem;
}

/* Sidebar */
.single-property .sidebar {
    position: sticky;
    top: 6rem;
}

/* Agent Card */
.single-property .agent-card {
    text-align: center;
    margin-bottom: 1.5rem;
}

.single-property .agent-avatar {
    width: 6rem;
    height: 6rem;
    border-radius: 9999px;
    margin: 0 auto 0.75rem;
    border: 4px solid var(--rebs-primary-light);
}

.single-property .agent-name {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 0.25rem;
}

.single-property .agent-title {
    color: var(--rebs-dark-gray);
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.single-property .agent-rating {
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1.5rem;
}

.single-property .agent-stats {
    border-top: 1px solid var(--rebs-medium-gray);
    padding-top: 1rem;
}

.single-property .agent-stat {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.single-property .agent-stat-label {
    color: var(--rebs-dark-gray);
}

.single-property .agent-stat-value {
    font-weight: 600;
}

.single-property .agent-actions {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.single-property .agent-action {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-radius: var(--rebs-radius);
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
}

.single-property .agent-action-primary {
    background-color: var(--rebs-primary-color);
    color: var(--rebs-white);
}

.single-property .agent-action-primary:hover {
    background-color: var(--rebs-primary-dark);
}

.single-property .agent-action-secondary {
    background-color: var(--rebs-secondary-color);
    color: var(--rebs-white);
}

.single-property .agent-action-secondary:hover {
    background-color: #374151;
}

.single-property .agent-action-outline {
    border: 2px solid var(--rebs-primary-color);
    color: var(--rebs-primary-color);
    background-color: transparent;
}

.single-property .agent-action-outline:hover {
    background-color: var(--rebs-primary-light);
}

/* Mortgage Calculator */
.single-property .calculator-input {
    margin-bottom: 1rem;
}

.single-property .calculator-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--rebs-dark-gray);
    margin-bottom: 0.5rem;
}

.single-property .calculator-field {
    width: 100%;
    padding: 0.5rem 1rem;
    border: 1px solid var(--rebs-medium-gray);
    border-radius: var(--rebs-radius);
    outline: none;
}

.single-property .calculator-field:focus {
    border-color: var(--rebs-primary-color);
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.single-property .calculator-slider {
    width: 100%;
    margin-bottom: 0.5rem;
}

.single-property .calculator-slider-labels {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
    color: var(--rebs-dark-gray);
}

.single-property .calculator-slider-value {
    font-weight: 600;
}

.single-property .calculator-result {
    background-color: var(--rebs-primary-light);
    border-radius: var(--rebs-radius);
    padding: 1rem;
    margin-top: 1rem;
}

.single-property .calculator-result-label {
    font-size: 0.875rem;
    color: var(--rebs-dark-gray);
    margin-bottom: 0.25rem;
}

.single-property .calculator-result-value {
    font-size: 1.875rem;
    font-weight: 700;
    color: var(--rebs-primary-color);
}

.single-property .calculator-result-note {
    font-size: 0.75rem;
    color: var(--rebs-dark-gray);
    margin-top: 0.5rem;
}

/* Stats Card */
.single-property .stats-card {
    background: linear-gradient(to bottom right, var(--rebs-primary-color), #0d9488);
    border-radius: var(--rebs-radius-lg);
    box-shadow: var(--rebs-shadow);
    padding: 1.5rem;
    color: var(--rebs-white);
}

.single-property .stats-title {
    font-size: 1.25rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.single-property .stats-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.single-property .stat-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.single-property .stat-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.single-property .stat-value {
    font-weight: 700;
}

/* Footer */
.single-property footer {
    background-color: var(--rebs-secondary-color);
    color: var(--rebs-white);
    margin-top: 4rem;
}

.single-property .footer-content {
    padding: 3rem 0;
}

.single-property .footer-grid {
    display: grid;
    gap: 2rem;
}

@media (min-width: 768px) {
    .single-property .footer-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

.single-property .footer-logo {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.single-property .footer-logo-text {
    font-size: 1.5rem;
    font-weight: 700;
}

.single-property .footer-description {
    color: #9ca3af;
    font-size: 0.875rem;
    line-height: 1.5;
}

.single-property .footer-heading {
    font-weight: 700;
    margin-bottom: 1rem;
}

.single-property .footer-links {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.single-property .footer-link {
    color: #9ca3af;
    font-size: 0.875rem;
    text-decoration: none;
    transition: color 0.3s;
}

.single-property .footer-link:hover {
    color: var(--rebs-primary-color);
}

.single-property .footer-contact {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.single-property .footer-contact-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #9ca3af;
    font-size: 0.875rem;
}

.single-property .footer-contact-item i {
    color: var(--rebs-primary-color);
}

.single-property .footer-bottom {
    border-top: 1px solid #374151;
    padding-top: 2rem;
    text-align: center;
    color: #9ca3af;
    font-size: 0.875rem;
}

/* Modals */
.single-property .modal {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 50;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.single-property .modal.active {
    display: flex;
}

.single-property .modal-content {
    background-color: var(--rebs-white);
    border-radius: var(--rebs-radius-lg);
    max-width: 28rem;
    width: 100%;
    padding: 1.5rem;
}

.single-property .modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
}

.single-property .modal-title {
    font-size: 1.5rem;
    font-weight: 700;
}

.single-property .modal-close {
    color: var(--rebs-dark-gray);
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    transition: color 0.3s;
}

.single-property .modal-close:hover {
    color: var(--rebs-text-dark);
}

.single-property .form-group {
    margin-bottom: 1rem;
}

.single-property .form-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--rebs-dark-gray);
    margin-bottom: 0.5rem;
}

.single-property .form-input {
    width: 100%;
    padding: 0.5rem 1rem;
    border: 1px solid var(--rebs-medium-gray);
    border-radius: var(--rebs-radius);
    outline: none;
}

.single-property .form-input:focus {
    border-color: var(--rebs-primary-color);
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
}

.single-property .form-textarea {
    resize: vertical;
    min-height: 6rem;
}

.single-property .form-submit {
    width: 100%;
    padding: 0.75rem 1rem;
    background-color: var(--rebs-primary-color);
    color: var(--rebs-white);
    border: none;
    border-radius: var(--rebs-radius);
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s;
}

.single-property .form-submit:hover {
    background-color: var(--rebs-primary-dark);
}

/* Image Viewer */
.single-property .image-viewer {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 9999;
    display: none;
    justify-content: center;
    align-items: center;
}

.single-property .image-viewer.active {
    display: flex;
}

.single-property .image-viewer img {
    max-width: 90%;
    max-height: 90%;
}

.single-property .image-viewer-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    color: var(--rebs-white);
    font-size: 1.875rem;
    background: none;
    border: none;
    cursor: pointer;
    z-index: 10;
    transition: color 0.3s;
}

.single-property .image-viewer-close:hover {
    color: var(--rebs-primary-color);
}

.single-property .image-viewer-prev,
.single-property .image-viewer-next {
    position: absolute;
    color: var(--rebs-white);
    font-size: 1.875rem;
    background: none;
    border: none;
    cursor: pointer;
    z-index: 10;
    transition: color 0.3s;
}

.single-property .image-viewer-prev {
    left: 1rem;
}

.single-property .image-viewer-next {
    right: 1rem;
}

.single-property .image-viewer-prev:hover,
.single-property .image-viewer-next:hover {
    color: var(--rebs-primary-color);
}

/* Utility Classes */
.single-property .text-center {
    text-align: center;
}

.single-property .text-left {
    text-align: left;
}

.single-property .text-right {
    text-align: right;
}

.single-property .hidden {
    display: none;
}

.single-property .flex {
    display: flex;
}

.single-property .items-center {
    align-items: center;
}

.single-property .justify-center {
    justify-content: center;
}

.single-property .justify-between {
    justify-content: space-between;
}

.single-property .flex-col {
    flex-direction: column;
}

.single-property .flex-wrap {
    flex-wrap: wrap;
}

.single-property .gap-2 {
    gap: 0.5rem;
}

.single-property .gap-3 {
    gap: 0.75rem;
}

.single-property .gap-4 {
    gap: 1rem;
}

.single-property .gap-6 {
    gap: 1.5rem;
}

.single-property .mb-2 {
    margin-bottom: 0.5rem;
}

.single-property .mb-3 {
    margin-bottom: 0.75rem;
}

.single-property .mb-4 {
    margin-bottom: 1rem;
}

.single-property .mb-6 {
    margin-bottom: 1.5rem;
}

.single-property .mt-2 {
    margin-top: 0.5rem;
}

.single-property .mt-4 {
    margin-top: 1rem;
}

.single-property .mt-6 {
    margin-top: 1.5rem;
}

.single-property .mt-16 {
    margin-top: 4rem;
}

.single-property .mr-1 {
    margin-right: 0.25rem;
}

.single-property .mr-2 {
    margin-right: 0.5rem;
}

.single-property .mr-3 {
    margin-right: 0.75rem;
}

.single-property .ml-2 {
    margin-left: 0.5rem;
}

.single-property .p-4 {
    padding: 1rem;
}

.single-property .p-6 {
    padding: 1.5rem;
}

.single-property .px-4 {
    padding-left: 1rem;
    padding-right: 1rem;
}

.single-property .px-6 {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.single-property .py-2 {
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
}

.single-property .py-3 {
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
}

.single-property .py-4 {
    padding-top: 1rem;
    padding-bottom: 1rem;
}

.single-property .py-8 {
    padding-top: 2rem;
    padding-bottom: 2rem;
}

.single-property .pt-3 {
    padding-top: 0.75rem;
}

.single-property .pt-4 {
    padding-top: 1rem;
}

.single-property .pb-4 {
    padding-bottom: 1rem;
}

.single-property .space-y-2 > * + * {
    margin-top: 0.5rem;
}

.single-property .space-y-3 > * + * {
    margin-top: 0.75rem;
}

.single-property .space-y-4 > * + * {
    margin-top: 1rem;
}

.single-property .space-y-6 > * + * {
    margin-top: 1.5rem;
}

.single-property .leading-relaxed {
    line-height: 1.625;
}

.single-property .whitespace-nowrap {
    white-space: nowrap;
}

.single-property .overflow-x-auto {
    overflow-x: auto;
}

.single-property .sticky {
    position: sticky;
}

.single-property .top-0 {
    top: 0;
}

.single-property .top-24 {
    top: 6rem;
}

.single-property .absolute {
    position: absolute;
}

.single-property .relative {
    position: relative;
}

.single-property .inset-0 {
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}

.single-property .z-10 {
    z-index: 10;
}

.single-property .z-50 {
    z-index: 50;
}

.single-property .w-full {
    width: 100%;
}

.single-property .w-12 {
    width: 3rem;
}

.single-property .h-12 {
    height: 3rem;
}

.single-property .max-w-full {
    max-width: 100%;
}

.single-property .max-h-full {
    max-height: 100%;
}

.single-property .max-w-md {
    max-width: 28rem;
}

.single-property .max-w-3xl {
    max-width: 48rem;
}

.single-property .object-cover {
    object-fit: cover;
}

.single-property .rounded {
    border-radius: var(--rebs-radius);
}

.single-property .rounded-lg {
    border-radius: var(--rebs-radius-lg);
}

.single-property .rounded-full {
    border-radius: 9999px;
}

.single-property .border {
    border-width: 1px;
    border-style: solid;
    border-color: var(--rebs-medium-gray);
}

.single-property .border-b {
    border-bottom-width: 1px;
    border-bottom-style: solid;
    border-bottom-color: var(--rebs-medium-gray);
}

.single-property .border-t {
    border-top-width: 1px;
    border-top-style: solid;
    border-top-color: var(--rebs-medium-gray);
}

.single-property .border-l-4 {
    border-left-width: 4px;
    border-left-style: solid;
    border-left-color: var(--rebs-primary-color);
}

.single-property .border-2 {
    border-width: 2px;
    border-style: solid;
    border-color: var(--rebs-primary-color);
}

.single-property .border-gray-300 {
    border-color: var(--rebs-medium-gray);
}

.single-property .bg-white {
    background-color: var(--rebs-white);
}

.single-property .bg-gray-50 {
    background-color: var(--rebs-light-gray);
}

.single-property .bg-gray-100 {
    background-color: #f3f4f6;
}

.single-property .bg-gray-700 {
    background-color: var(--rebs-secondary-color);
}

.single-property .bg-emerald-500 {
    background-color: var(--rebs-primary-color);
}

.single-property .bg-emerald-50 {
    background-color: var(--rebs-primary-light);
}

.single-property .bg-blue-500 {
    background-color: #3b82f6;
}

.single-property .bg-orange-500 {
    background-color: #f97316;
}

.single-property .bg-blue-50 {
    background-color: #dbeafe;
}

.single-property .bg-green-50 {
    background-color: #dcfce7;
}

.single-property .bg-purple-50 {
    background-color: #f3e8ff;
}

.single-property .bg-red-50 {
    background-color: #fee2e2;
}

.single-property .bg-yellow-50 {
    background-color: #fef3c7;
}

.single-property .bg-teal-50 {
    background-color: #ccfbf1;
}

.single-property .text-white {
    color: var(--rebs-white);
}

.single-property .text-gray-600 {
    color: var(--rebs-dark-gray);
}

.single-property .text-gray-700 {
    color: #374151;
}

.single-property .text-gray-800 {
    color: var(--rebs-text-dark);
}

.single-property .text-emerald-500 {
    color: var(--rebs-primary-color);
}

.single-property .text-emerald-600 {
    color: var(--rebs-primary-dark);
}

.single-property .text-emerald-800 {
    color: #065f46;
}

.single-property .text-blue-500 {
    color: #3b82f6;
}

.single-property .text-green-500 {
    color: #22c55e;
}

.single-property .text-purple-500 {
    color: #a855f7;
}

.single-property .text-red-500 {
    color: #ef4444;
}

.single-property .text-yellow-500 {
    color: #f59e0b;
}

.single-property .text-teal-500 {
    color: #14b8a6;
}

.single-property .text-sm {
    font-size: 0.875rem;
}

.single-property .text-xs {
    font-size: 0.75rem;
}

.single-property .text-lg {
    font-size: 1.125rem;
}

.single-property .text-xl {
    font-size: 1.25rem;
}

.single-property .text-2xl {
    font-size: 1.5rem;
}

.single-property .text-3xl {
    font-size: 1.875rem;
}

.single-property .text-4xl {
    font-size: 2.25rem;
}

.single-property .text-5xl {
    font-size: 3rem;
}

.single-property .font-semibold {
    font-weight: 600;
}

.single-property .font-bold {
    font-weight: 700;
}

.single-property .cursor-pointer {
    cursor: pointer;
}

.single-property .transition {
    transition: all 0.3s;
}


@media print {
    .single-property .no-print {
        display: none !important;
    }
}

/* Animation */
.single-property .badge {
    animation: rebs-pulse 2s infinite;
}

@keyframes rebs-pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .8;
    }
}

.single-property .skeleton {
    animation: rebs-skeleton-loading 1s linear infinite alternate;
}

@keyframes rebs-skeleton-loading {
    0% {
        background-color: hsl(200, 20%, 80%);
    }
    100% {
        background-color: hsl(200, 20%, 95%);
    }
}

.single-property .tooltip {
    position: relative;
}

.single-property .tooltip:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: var(--rebs-secondary-color);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    white-space: nowrap;
    font-size: 12px;
    z-index: 1000;
}

.single-property .smooth-scroll {
    scroll-behavior: smooth;
}

/* Additional fixes for dynamic content */
.single-property .tab-content {
    min-height: 200px;
}

.single-property .section-title {
    color: #1e293b;
    font-weight: 800;
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    position: relative;
    padding-bottom: 0.75rem;
}

.single-property .section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    border-radius: 2px;
}

.single-property .text-gray-600 {
    color: var(--rebs-text-light);
}

.single-property .bg-gray-50 {
    background-color: var(--rebs-light-gray);
}

.single-property .text-gray-900 {
    color: var(--rebs-text-dark);
}

.single-property .text-gray-500 {
    color: var(--rebs-text-light);
}

/* Ensure proper spacing for dynamic content */
.single-property .mb-6 {
    margin-bottom: 1.5rem;
}

.single-property .mb-8 {
    margin-bottom: 2rem;
}

.single-property .space-y-4 > * + * {
    margin-top: 1rem;
}

.single-property .space-y-3 > * + * {
    margin-top: 0.75rem;
}

/* Specifications Grid */
.single-property .specifications-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (min-width: 768px) {
    .single-property .specifications-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (min-width: 1024px) {
    .single-property .specifications-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (min-width: 1280px) {
    .single-property .specifications-grid {
        grid-template-columns: repeat(5, 1fr);
    }
}

.single-property .specifications-grid > div {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    padding: 1.5rem 1rem;
    border-radius: 12px;
    text-align: center;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    position: relative;
    overflow: hidden;
}

.single-property .specifications-grid > div::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.single-property .specifications-grid > div:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    border-color: #10b981;
}

.single-property .specifications-grid > div:hover::before {
    transform: scaleX(1);
}

.single-property .specifications-grid > div label {
    display: block;
    font-size: 0.75rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.single-property .specifications-grid > div p {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    line-height: 1.2;
}

/* Pricing Container - Compact Design */
.single-property .pricing-container {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    max-width: 500px;
}

@media (min-width: 768px) {
    .single-property .pricing-container {
        flex-direction: row;
        max-width: 600px;
    }
}

.single-property .pricing-card {
    background: #ffffff;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    text-align: center;
    flex: 1;
}

.single-property .pricing-card.main-price {
    background: #f9fafb;
    border-color: #d1d5db;
}

.single-property .pricing-label {
    display: block;
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    margin-bottom: 0.25rem;
}

.single-property .pricing-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.single-property .pricing-value-small {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.single-property .pricing-note {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

/* Classification Container - Compact Design */
.single-property .classification-container {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.75rem;
    max-width: 600px;
}

.single-property .classification-item {
    background: #ffffff;
    padding: 30px;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
    text-align: center;
}

.single-property .classification-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
    white-space: nowrap;
}

.single-property .classification-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    margin-top: 10px;
}

/* Nearby Features Grid */
.single-property .nearby-features-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

@media (min-width: 768px) {
    .single-property .nearby-features-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.single-property .location-feature {
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.single-property .location-feature:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.single-property .location-feature i {
    font-size: 2rem;
    margin-bottom: 1rem;
    display: block;
}

.single-property .location-feature h4 {
    font-size: 1.125rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    color: #1e293b;
}

.single-property .location-feature p {
    font-size: 0.875rem;
    color: #64748b;
    margin: 0;
}

.single-property .location-feature-blue {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-color: #3b82f6;
}

.single-property .location-feature-blue i {
    color: #3b82f6;
}

.single-property .location-feature-green {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border-color: #10b981;
}

.single-property .location-feature-green i {
    color: #10b981;
}

.single-property .location-feature-purple {
    background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
    border-color: #8b5cf6;
}

.single-property .location-feature-purple i {
    color: #8b5cf6;
}

/* Address Card - Minimal Design */
.single-property .address-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;

}

.single-property .address-header {
    background: transparent;
    padding: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    border: none;
}

.single-property .address-icon {
    font-size: 1rem;
    color: #6b7280;
}

.single-property .address-title {
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.single-property .address-content {
    padding: 0;
}

.single-property .address-main {
    font-size: 1rem;
    font-weight: 400;
    color: #111827;
    margin-bottom: 0.75rem;
    line-height: 1.5;
}

.single-property .address-placeholder {
    font-size: 1rem;
    color: #9ca3af;
    font-style: italic;
    margin-bottom: 0.75rem;
}

.single-property .address-details {
    display: flex;
    flex-wrap: nowrap;
    gap: 1rem;
    margin-bottom: 0.75rem;
    align-items: center;
}

.single-property .address-detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0;
    background: transparent;
    border-radius: 0;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    border: none;
    white-space: nowrap;
}

.single-property .address-detail-item i {
    color: #6b7280;
    font-size: 0.875rem;
}

.single-property .address-country {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0;
    background: transparent;
    border-radius: 0;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    border: none;
    white-space: nowrap;
}

.single-property .address-country i {
    color: #6b7280;
    font-size: 0.875rem;
}

/* Media Gallery Row Layout (for Media Tab only) */
.single-property .media-gallery-row {
    display: flex;
    gap: 0.75rem;
    overflow-x: auto;
    padding: 0.5rem 0;
    scrollbar-width: thin;
    scrollbar-color: #d1d5db #f3f4f6;
}

.single-property .media-gallery-row::-webkit-scrollbar {
    height: 6px;
}

.single-property .media-gallery-row::-webkit-scrollbar-track {
    background: #f3f4f6;
    border-radius: 3px;
}

.single-property .media-gallery-row::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 3px;
}

.single-property .media-gallery-row::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

.single-property .media-gallery-item {
    flex-shrink: 0;
    width: 200px;
    height: 150px;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    transition: transform 0.2s ease;
}

.single-property .media-gallery-item:hover {
    transform: scale(1.02);
}

.single-property .media-gallery-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.2s ease;
}

.single-property .media-gallery-item:hover .media-gallery-image {
    opacity: 0.9;
}

/* Virtual Tour & Property Video - Clean Design */
.single-property .virtual-tour-container,
.single-property .property-video-container {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
}

.single-property .virtual-tour-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.75rem;
}

.single-property .virtual-tour-description {
    color: #6b7280;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

.single-property .virtual-tour-button,
.single-property .property-video-button {
    display: inline-flex;
    align-items: center;
    padding: 0.75rem 1.5rem;
    background-color: #10b981;
    color: #ffffff;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background-color 0.2s ease;
}

.single-property .virtual-tour-button:hover,
.single-property .property-video-button:hover {
    background-color: #059669;
    color: #ffffff;
    text-decoration: none;
}

/* Professional Agent Profile Design */
.single-property .agent-profile-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
}

.single-property .agent-profile-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.single-property .agent-profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #10b981;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

.single-property .agent-placeholder {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
}

.single-property .agent-profile-name {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 0.5rem;
}

.single-property .agent-profile-title {
    color: #6b7280;
    font-size: 1rem;
    margin-bottom: 1rem;
}

.single-property .agent-rating-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.single-property .agent-stars {
    display: flex;
    gap: 0.25rem;
}

.single-property .agent-stars i {
    color: #fbbf24;
    font-size: 1rem;
}

.single-property .agent-reviews {
    color: #6b7280;
    font-size: 0.875rem;
}

.single-property .agent-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.single-property .agent-detail-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.single-property .agent-detail-item:hover {
    background: #f3f4f6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.single-property .agent-detail-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}

.single-property .agent-detail-content {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.single-property .agent-detail-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.single-property .agent-detail-value {
    font-size: 1rem;
    font-weight: 600;
    color: #111827;
}

/* Map Container */
.single-property .map-container {
    width: 100%;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.single-property .map-container iframe {
    width: 100%;
    height: 400px;
    border: none;
    border-radius: 8px;
}

/* Map Placeholder */
.single-property .map-placeholder {
    width: 100%;
    height: 300px;
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.single-property .map-placeholder-content {
    text-align: center;
    padding: 2rem;
}

.test-class {
grid-column: 1 / 3 !important;
}
    </style>
</head>
<body class="smooth-scroll">

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
                                    <?php if ($price_note): ?>
                                        <p class="pricing-note"><?php echo esc_html($price_note); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Price per Sq Ft -->
                                <?php if ($formatted_price_per_sqft): ?>
                                <div class="pricing-card">
                                    <label class="pricing-label">Price per Sq Ft</label>
                                    <p class="pricing-value-small"><?php echo esc_html($formatted_price_per_sqft); ?></p>
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
                                <?php if (! empty($features_array)): ?>
                                    <?php foreach ($features_array as $feature): ?>
                                        <div class="amenity-item" data-category="interior">
                                            <i class="fas fa-check-circle text-emerald-500"></i>
                                            <span class="text-gray-700"><?php echo esc_html(trim($feature)); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <?php if (! empty($amenities_array)): ?>
                                    <?php foreach ($amenities_array as $amenity): ?>
                                        <div class="amenity-item" data-category="exterior">
                                            <i class="fas fa-check-circle text-emerald-500"></i>
                                            <span class="text-gray-700"><?php echo esc_html(trim($amenity)); ?></span>
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
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="bg-gray-50 p-6 rounded-lg">
                                    <h4 class="text-lg font-semibold mb-4">Schedule Viewing</h4>
                                    <p class="text-gray-600 mb-4">Book a time to visit this property in person</p>
                                    <button onclick="scheduleViewing()" class="btn btn-primary w-full">
                                        <i class="fas fa-calendar-alt mr-2"></i>Schedule Viewing
                                    </button>
                                </div>
                                
                                <div class="bg-gray-50 p-6 rounded-lg">
                                    <h4 class="text-lg font-semibold mb-4">Virtual Tour</h4>
                                    <p class="text-gray-600 mb-4">Take a virtual tour of this property</p>
                                    <?php if ($virtual_tour): ?>
                                        <a href="<?php echo esc_url($virtual_tour); ?>" target="_blank" class="btn btn-secondary w-full">
                                            <i class="fas fa-play mr-2"></i>Start Virtual Tour
                                        </a>
                                    <?php else: ?>
                                        <button onclick="requestVirtualTour()" class="btn btn-secondary w-full">
                                            <i class="fas fa-video mr-2"></i>Request Virtual Tour
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Similar Properties -->
                <div class="card">
                    <h3 class="section-title">Similar Properties</h3>
                    <p class="text-gray-600 mb-6">Other properties you might be interested in</p>
                    
                    <?php
                    // Get similar properties based on property type and location
                    $similar_properties = get_posts(array(
                        'post_type' => 'property',
                        'posts_per_page' => 4,
                        'post__not_in' => array($post->ID),
                        'meta_query' => array(
                            'relation' => 'OR',
                            array(
                                'key' => '_property_type',
                                'value' => $property_type,
                                'compare' => '='
                            ),
                            array(
                                'key' => '_property_city',
                                'value' => $city,
                                'compare' => '='
                            )
                        )
                    ));
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
                            <input type="range" id="downPayment" min="0" max="100" value="<?php echo esc_attr($mortgage_default_down_payment ? $mortgage_default_down_payment : '20'); ?>" class="calculator-slider" oninput="updateDownPayment(this.value); calculateMortgage()">
                            <div class="calculator-slider-labels">
                                <span>0%</span>
                                <span id="downPaymentValue" class="calculator-slider-value"><?php echo esc_html($mortgage_default_down_payment ? $mortgage_default_down_payment : '20'); ?>%</span>
                                <span>100%</span>
                            </div>
                        </div>
                        <div class="calculator-input">
                            <label class="calculator-label"><?php echo esc_html($mortgage_interest_rate_label ? $mortgage_interest_rate_label : 'Interest Rate (%)'); ?></label>
                            <input type="number" id="interestRate" value="<?php echo esc_attr($mortgage_default_interest_rate ? $mortgage_default_interest_rate : '6.5'); ?>" step="0.1" class="calculator-field" onkeyup="calculateMortgage()">
                        </div>
                        <div class="calculator-input">
                            <label class="calculator-label"><?php echo esc_html($mortgage_loan_term_label ? $mortgage_loan_term_label : 'Loan Term (Years)'); ?></label>
                            <select id="loanTerm" class="calculator-field" onchange="calculateMortgage()">
                                <?php
                                    $loan_terms   = $mortgage_loan_terms ? explode(',', $mortgage_loan_terms) : ['15', '20', '30'];
                                    $default_term = $mortgage_default_loan_term ? $mortgage_default_loan_term : '30';
                                    foreach ($loan_terms as $term):
                                        $term = trim($term);
                                    ?>
	                                    <option value="<?php echo esc_attr($term); ?>"<?php echo $term == $default_term ? 'selected' : ''; ?>><?php echo esc_html($term); ?> Years</option>
	                                <?php endforeach; ?>
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

    <script>
        // Gallery images - Dynamic from PHP
        const galleryImages = [
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
        let currentImageIndex = 0;

        // Mobile Menu Toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('active');
        }

        // Tab Switching
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });

            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');

            // Add active class to clicked button
            const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
            activeButton.classList.add('active');

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
                btn.classList.remove('active');
            });

            event.target.classList.add('active');

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
            document.getElementById('contactModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeContactModal() {
            document.getElementById('contactModal').classList.remove('active');
            document.body.style.overflow = 'auto';
        }

        function submitContactForm(e) {
            e.preventDefault();
            alert('<?php echo esc_js($contact_success_message ? $contact_success_message : 'Thank you for your message! Our agent will contact you shortly.'); ?>');
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

        // Map initialization removed - only using iframes now

        // Search Reviews
        function searchReviews() {
            const searchTerm = document.getElementById('reviewSearch').value.toLowerCase();
            const reviews = document.querySelectorAll('.review-item');

            reviews.forEach(review => {
                const text = review.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    review.style.display = 'block';
                } else {
                    review.style.display = 'none';
                }
            });
        }

        // Sort Reviews
        function sortReviews() {
            const sortValue = document.getElementById('reviewSort').value;
            alert('Sorting reviews by: ' + sortValue);
            // Implementation would depend on your data structure
        }

        // Like Review
        function likeReview(button) {
            const span = button.querySelector('span');
            const currentCount = parseInt(span.textContent.match(/\d+/)[0]);
            span.textContent = `Helpful (${currentCount + 1})`;
            button.classList.add('text-emerald-500');
        }

        // Utility Functions
        function shareProperty() {
            if (navigator.share) {
                navigator.share({
                    title: '<?php echo esc_js(get_the_title()); ?>',
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
            // In production, you'd implement actual PDF generation
        }

        function downloadFloorPlan() {
            alert('Floor plan downloading...');
        }

        function requestCustomPlan() {
            alert('Request sent! Our team will contact you shortly.');
        }

        function scheduleTour() {
            // Create a modal for tour scheduling
            const modal = document.createElement('div');
            modal.id = 'tourModal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;
            
            modal.innerHTML = `
                <div style="
                    background: white;
                    padding: 2rem;
                    border-radius: 12px;
                    max-width: 500px;
                    width: 90%;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                ">
                    <h3 style="margin-bottom: 1.5rem; color: #1e293b; font-size: 1.5rem;">Schedule Property Tour</h3>
                    <form id="tourForm">
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Your Name</label>
                            <input type="text" required style="
                                width: 100%;
                                padding: 0.75rem;
                                border: 1px solid #d1d5db;
                                border-radius: 8px;
                                font-size: 1rem;
                            ">
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Email</label>
                            <input type="email" required style="
                                width: 100%;
                                padding: 0.75rem;
                                border: 1px solid #d1d5db;
                                border-radius: 8px;
                                font-size: 1rem;
                            ">
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Phone</label>
                            <input type="tel" style="
                                width: 100%;
                                padding: 0.75rem;
                                border: 1px solid #d1d5db;
                                border-radius: 8px;
                                font-size: 1rem;
                            ">
                        </div>
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Preferred Date</label>
                            <input type="date" required style="
                                width: 100%;
                                padding: 0.75rem;
                                border: 1px solid #d1d5db;
                                border-radius: 8px;
                                font-size: 1rem;
                            ">
                        </div>
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Preferred Time</label>
                            <select required style="
                                width: 100%;
                                padding: 0.75rem;
                                border: 1px solid #d1d5db;
                                border-radius: 8px;
                                font-size: 1rem;
                            ">
                                <option value="">Select Time</option>
                                <option value="9:00 AM">9:00 AM</option>
                                <option value="10:00 AM">10:00 AM</option>
                                <option value="11:00 AM">11:00 AM</option>
                                <option value="12:00 PM">12:00 PM</option>
                                <option value="1:00 PM">1:00 PM</option>
                                <option value="2:00 PM">2:00 PM</option>
                                <option value="3:00 PM">3:00 PM</option>
                                <option value="4:00 PM">4:00 PM</option>
                                <option value="5:00 PM">5:00 PM</option>
                            </select>
                        </div>
                        <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                            <button type="button" onclick="closeTourModal()" style="
                                padding: 0.75rem 1.5rem;
                                border: 1px solid #d1d5db;
                                background: white;
                                color: #374151;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 600;
                            ">Cancel</button>
                            <button type="submit" style="
                                padding: 0.75rem 1.5rem;
                                background: #10b981;
                                color: white;
                                border: none;
                                border-radius: 8px;
                                cursor: pointer;
                                font-weight: 600;
                            ">Schedule Tour</button>
                        </div>
                    </form>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Handle form submission
            document.getElementById('tourForm').addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Tour scheduled successfully! We will contact you to confirm the appointment.');
                closeTourModal();
            });
        }
        
        function closeTourModal() {
            const modal = document.getElementById('tourModal');
            if (modal) {
                modal.remove();
            }
        }
        
        function scheduleViewing() {
            scheduleTour(); // Use the same function
        }
        
        function requestVirtualTour() {
            alert('Virtual tour request sent! We will contact you shortly to arrange your virtual tour.');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            calculateMortgage();
        });

        // Close modals when clicking outside
        window.onclick = function(event) {
            const contactModal = document.getElementById('contactModal');
            const tourModal = document.getElementById('tourModal');
            if (event.target === contactModal) {
                closeContactModal();
            }
            if (event.target === tourModal) {
                closeTourModal();
            }
        }
    </script>
</body>
</html>