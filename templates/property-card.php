<?php
/**
 * Property Card Template
 * Displays individual property cards in archive views
 * 
 * Security Measures:
 * - Nonce verification for AJAX requests (resbs_archive_nonce)
 * - User permission checks handled server-side in AJAX handlers
 * - Input sanitization (property IDs validated and sanitized)
 * - Rate limiting implemented server-side for favorite toggles
 * - XSS protection via esc_attr(), esc_url(), esc_html() functions
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$property_id = get_the_ID();
$price = get_post_meta($property_id, 'resbs_property_price', true);
$status = get_post_meta($property_id, 'resbs_property_status', true);
$type = get_post_meta($property_id, 'resbs_property_type', true);
$bedrooms = get_post_meta($property_id, 'resbs_property_bedrooms', true);
$bathrooms = get_post_meta($property_id, 'resbs_property_bathrooms', true);
$area = get_post_meta($property_id, 'resbs_property_area', true);
$address = get_post_meta($property_id, 'resbs_property_address', true);
$featured = get_post_meta($property_id, 'resbs_property_featured', true);
$gallery = get_post_meta($property_id, 'resbs_property_gallery', true);
$layout = RESBS_Archive_Handler::get_archive_layout();

// Format price with dynamic currency
$formatted_price = '';
if ($price) {
    $formatted_price = resbs_format_price($price);
    if ($status === 'for-rent') {
        $formatted_price .= '/' . __('month', 'realestate-booking-suite');
    }
}

// Get featured image
$featured_image = get_the_post_thumbnail_url($property_id, 'medium');
if (!$featured_image && $gallery) {
    $gallery_array = is_array($gallery) ? $gallery : explode(',', $gallery);
    if (!empty($gallery_array[0])) {
        $featured_image = wp_get_attachment_image_url($gallery_array[0], 'medium');
    }
}

// Status badge
$status_badge = '';
switch ($status) {
    case 'for-sale':
        $status_badge = '<span class="resbs-status-badge resbs-status-sale">' . esc_html__('For Sale', 'realestate-booking-suite') . '</span>';
        break;
    case 'for-rent':
        $status_badge = '<span class="resbs-status-badge resbs-status-rent">' . esc_html__('For Rent', 'realestate-booking-suite') . '</span>';
        break;
    case 'sold':
        $status_badge = '<span class="resbs-status-badge resbs-status-sold">' . esc_html__('Sold', 'realestate-booking-suite') . '</span>';
        break;
    case 'rented':
        $status_badge = '<span class="resbs-status-badge resbs-status-rented">' . esc_html__('Rented', 'realestate-booking-suite') . '</span>';
        break;
}

// Featured badge
$featured_badge = '';
if ($featured) {
    $featured_badge = '<span class="resbs-featured-badge">' . esc_html__('Featured', 'realestate-booking-suite') . '</span>';
}
?>

<div class="resbs-property-card" data-property-id="<?php echo esc_attr($property_id); ?>">
    
    <!-- Property Image -->
    <div class="resbs-property-image">
        <?php if ($featured_image) : ?>
            <img src="<?php echo esc_url($featured_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
        <?php else : ?>
            <div class="resbs-no-image">
                <i class="fas fa-home"></i>
                <span><?php echo esc_html__('No Image', 'realestate-booking-suite'); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Overlay Badges -->
        <div class="resbs-property-badges">
            <?php echo wp_kses_post($featured_badge); ?>
            <?php echo wp_kses_post($status_badge); ?>
        </div>
        
        <!-- Favorite Button -->
        <button class="resbs-favorite-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
            <i class="far fa-heart"></i>
        </button>
        
        <!-- Quick View Button -->
        <button class="resbs-quick-view-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
            <i class="fas fa-eye"></i>
        </button>
    </div>
    
    <!-- Property Content -->
    <div class="resbs-property-content">
        
        <!-- Property Header -->
        <div class="resbs-property-header">
            <<?php echo esc_attr(resbs_get_title_heading_tag()); ?> class="resbs-property-title">
                <a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
            </<?php echo esc_attr(resbs_get_title_heading_tag()); ?>>
            
            <?php 
            // Show address only if setting allows (dynamically from Listings settings)
            if ($address && resbs_should_show_listing_address()) : ?>
                <p class="resbs-property-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?php echo esc_html($address); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <!-- Property Price -->
        <?php 
        // Show price only if setting allows (dynamically from Listings settings)
        if ($formatted_price && resbs_should_show_price()) : ?>
            <div class="resbs-property-price">
                <?php echo esc_html($formatted_price); ?>
            </div>
        <?php endif; ?>
        
        <!-- Property Details -->
        <div class="resbs-property-details">
            <?php if ($bedrooms) : ?>
                <div class="resbs-property-detail">
                    <i class="fas fa-bed"></i>
                    <span><?php echo esc_html($bedrooms); ?> <?php echo esc_html($bedrooms > 1 ? __('beds', 'realestate-booking-suite') : __('bed', 'realestate-booking-suite')); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($bathrooms) : ?>
                <div class="resbs-property-detail">
                    <i class="fas fa-bath"></i>
                    <span><?php echo esc_html($bathrooms); ?> <?php echo esc_html($bathrooms > 1 ? __('baths', 'realestate-booking-suite') : __('bath', 'realestate-booking-suite')); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($area) : ?>
                <div class="resbs-property-detail">
                    <i class="fas fa-ruler-combined"></i>
                    <span><?php echo esc_html(resbs_format_area($area)); ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Property Type -->
        <?php if ($type) : ?>
            <div class="resbs-property-type">
                <span class="resbs-type-badge"><?php echo esc_html(ucfirst($type)); ?></span>
            </div>
        <?php endif; ?>
        
        <!-- Property Actions -->
        <div class="resbs-property-actions">
            <a href="<?php the_permalink(); ?>" class="resbs-view-details-btn">
                <span><?php echo esc_html__('View Details', 'realestate-booking-suite'); ?></span>
                <i class="fas fa-arrow-right"></i>
            </a>
            
            <button class="resbs-contact-agent-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
                <i class="fas fa-envelope"></i>
                <?php echo esc_html__('Contact Agent', 'realestate-booking-suite'); ?>
            </button>
        </div>
        
        <!-- Property Meta (for list view) -->
        <?php if ($layout === 'list') : ?>
            <div class="resbs-property-meta">
                <div class="resbs-property-excerpt">
                    <?php echo esc_html(wp_trim_words(get_the_excerpt(), 20)); ?>
                </div>
                
                <div class="resbs-property-features">
                    <?php
                    $features = get_post_meta($property_id, 'resbs_property_features', true);
                    if ($features && is_array($features)) {
                        $feature_count = 0;
                        foreach ($features as $feature) {
                            if ($feature_count >= 3) break;
                            echo '<span class="resbs-feature-tag">' . esc_html($feature) . '</span>';
                            $feature_count++;
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
        
    </div>
</div>
<!-- Property card scripts are now enqueued via wp_enqueue_script -->
