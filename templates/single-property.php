<?php
/**
 * Single Property Template
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Suppress deprecated warnings
error_reporting(E_ERROR | E_PARSE);

// Start output buffering to catch any warnings
ob_start();

// Get property data
$property_id = get_the_ID();
$price = get_post_meta($property_id, '_resbs_price', true);
$bedrooms = get_post_meta($property_id, '_resbs_bedrooms', true);
$bathrooms = get_post_meta($property_id, '_resbs_bathrooms', true);
$area = get_post_meta($property_id, '_resbs_area', true);
$latitude = get_post_meta($property_id, '_resbs_latitude', true);
$longitude = get_post_meta($property_id, '_resbs_longitude', true);
$gallery = get_post_meta($property_id, '_resbs_gallery', true);
$video_url = get_post_meta($property_id, '_resbs_video_url', true);
$description = get_post_meta($property_id, '_resbs_description', true);
$amenities = get_post_meta($property_id, '_resbs_amenities', true);

$property_status = wp_get_post_terms($property_id, 'property_status', array('fields' => 'names'));
$property_type = wp_get_post_terms($property_id, 'property_type', array('fields' => 'names'));
$location = wp_get_post_terms($property_id, 'property_location', array('fields' => 'names'));

// Clear any warnings from output buffer
ob_clean();

// Try to get header, but don't fail if it doesn't exist
if (function_exists('get_header')) {
    get_header();
} else {
    // Fallback HTML structure
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php wp_title('|', true, 'right'); ?></title>
        <?php wp_head(); ?>
    </head>
    <body <?php body_class(); ?>>
    <?php
}
?>

<div class="resbs-single-property" data-property-id="<?php echo esc_attr($property_id); ?>">
    <!-- Property Header -->
    <div class="resbs-property-header">
        <div class="resbs-property-title-section">
            <h1 class="resbs-property-title"><?php the_title(); ?></h1>
            
            <?php if (!empty($location)): ?>
                <div class="resbs-property-address">
                    <span class="dashicons dashicons-location"></span>
                    <?php echo esc_html(implode(', ', $location)); ?>
                </div>
            <?php endif; ?>
            
            <!-- Property Badges -->
            <div class="resbs-badges-single">
                <?php do_action('resbs_property_badges', $property_id, 'single'); ?>
            </div>
            
            <?php if (!empty($property_status)): ?>
                <div class="resbs-property-status">
                    <?php foreach ($property_status as $status): ?>
                        <span class="resbs-status-badge resbs-status-<?php echo esc_attr(strtolower(str_replace(' ', '-', $status))); ?>">
                            <?php echo esc_html($status); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="resbs-property-price-section">
            <?php if ($price): ?>
                <div class="resbs-property-price">
                    <span class="resbs-price-amount">$<?php echo esc_html(number_format(floatval($price))); ?></span>
                    <?php if (!empty($property_status) && in_array('Rent', $property_status)): ?>
                        <span class="resbs-price-period"><?php esc_html_e('/month', 'realestate-booking-suite'); ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="resbs-property-actions">
                <button type="button" class="resbs-favorite-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
                    <span class="dashicons dashicons-heart"></span>
                    <span class="resbs-favorite-text"><?php esc_html_e('Add to Favorites', 'realestate-booking-suite'); ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Property Gallery -->
    <?php if (!empty($gallery) || has_post_thumbnail()): ?>
        <div class="resbs-property-gallery">
            <div class="resbs-gallery-main">
                <?php if (has_post_thumbnail()): ?>
                    <div class="resbs-gallery-item resbs-gallery-featured">
                        <a href="<?php echo esc_url(get_the_post_thumbnail_url($property_id, 'large')); ?>" data-lightbox="property-gallery" data-title="<?php echo esc_attr(get_the_title()); ?>">
                            <?php the_post_thumbnail('large'); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($gallery)): ?>
                    <?php 
                    $gallery_images = explode(',', $gallery);
                    foreach ($gallery_images as $image_id): 
                        $image_url = wp_get_attachment_image_url($image_id, 'large');
                        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                        if ($image_url):
                    ?>
                        <div class="resbs-gallery-item">
                            <a href="<?php echo esc_url($image_url); ?>" data-lightbox="property-gallery" data-title="<?php echo esc_attr($image_alt ?: get_the_title()); ?>">
                                <img src="<?php echo esc_url(wp_get_attachment_image_url($image_id, 'medium')); ?>" alt="<?php echo esc_attr($image_alt ?: get_the_title()); ?>">
                            </a>
                        </div>
                    <?php 
                        endif;
                    endforeach; 
                    ?>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($gallery)): ?>
                <div class="resbs-gallery-thumbnails">
                    <?php foreach ($gallery_images as $image_id): ?>
                        <?php
                        $thumb_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                        if ($thumb_url):
                        ?>
                            <div class="resbs-gallery-thumb">
                                <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php echo esc_attr(get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: get_the_title()); ?>">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Property Content -->
    <div class="resbs-property-content"></div>
        <div class="resbs-property-main-content">
            <!-- Description -->
            <div class="resbs-property-description">
                <h2><?php esc_html_e('Description', 'realestate-booking-suite'); ?></h2>
                <div class="resbs-description-content">
                    <?php if ($description && trim($description) !== ''): ?>
                        <?php echo wp_kses_post(wpautop($description)); ?>
                    <?php elseif (get_the_content() && trim(get_the_content()) !== ''): ?>
                        <?php the_content(); ?>
                    <?php else: ?>
                        <p><?php esc_html_e('No description available for this property.', 'realestate-booking-suite'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Amenities -->
            <?php if ($amenities): ?>
                <div class="resbs-property-amenities">
                    <h2><?php esc_html_e('Amenities', 'realestate-booking-suite'); ?></h2>
                    <div class="resbs-amenities-list">
                        <?php
                        $amenities_list = explode(',', $amenities);
                        foreach ($amenities_list as $amenity):
                            $amenity = trim($amenity);
                            if ($amenity):
                        ?>
                            <div class="resbs-amenity-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php echo esc_html($amenity); ?>
                            </div>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Video -->
            <?php if ($video_url): ?>
                <div class="resbs-property-video">
                    <h2><?php esc_html_e('Video Tour', 'realestate-booking-suite'); ?></h2>
                    <div class="resbs-video-container">
                        <?php echo wp_oembed_get(esc_url($video_url)); ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Map -->
            <?php if ($latitude && $longitude): ?>
                <div class="resbs-property-map">
                    <h2><?php esc_html_e('Location', 'realestate-booking-suite'); ?></h2>
                    <div id="resbs-property-map" class="resbs-map-container" data-lat="<?php echo esc_attr($latitude); ?>" data-lng="<?php echo esc_attr($longitude); ?>"></div>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Property Sidebar -->
        <div class="resbs-property-sidebar">
            <!-- Property Details -->
            <div class="resbs-property-details">
                <h3><?php esc_html_e('Property Details', 'realestate-booking-suite'); ?></h3>
                <div class="resbs-details-list">
                    <?php if ($price && floatval($price) > 0): ?>
                        <div class="resbs-detail-item">
                            <span class="resbs-detail-label"><?php esc_html_e('Price', 'realestate-booking-suite'); ?>:</span>
                            <span class="resbs-detail-value">$<?php echo esc_html(number_format(floatval($price))); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($bedrooms && intval($bedrooms) > 0): ?>
                        <div class="resbs-detail-item">
                            <span class="resbs-detail-label"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?>:</span>
                            <span class="resbs-detail-value"><?php echo esc_html($bedrooms); ?> <?php echo esc_html($bedrooms == 1 ? __('Bedroom', 'realestate-booking-suite') : __('Bedrooms', 'realestate-booking-suite')); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($bathrooms && floatval($bathrooms) > 0): ?>
                        <div class="resbs-detail-item">
                            <span class="resbs-detail-label"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?>:</span>
                            <span class="resbs-detail-value"><?php echo esc_html($bathrooms); ?> <?php echo esc_html($bathrooms == 1 ? __('Bathroom', 'realestate-booking-suite') : __('Bathrooms', 'realestate-booking-suite')); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($area && floatval($area) > 0): ?>
                        <div class="resbs-detail-item">
                            <span class="resbs-detail-label"><?php esc_html_e('Area', 'realestate-booking-suite'); ?>:</span>
                            <span class="resbs-detail-value"><?php echo esc_html(number_format(floatval($area))); ?> <?php esc_html_e('sq ft', 'realestate-booking-suite'); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($property_type)): ?>
                        <div class="resbs-detail-item">
                            <span class="resbs-detail-label"><?php esc_html_e('Property Type', 'realestate-booking-suite'); ?>:</span>
                            <span class="resbs-detail-value"><?php echo esc_html(implode(', ', $property_type)); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="resbs-detail-item">
                        <span class="resbs-detail-label"><?php esc_html_e('Property ID', 'realestate-booking-suite'); ?>:</span>
                        <span class="resbs-detail-value">#<?php echo esc_html($property_id); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Booking Widget -->
            <div class="resbs-booking-widget">
                <h3><?php esc_html_e('Book This Property', 'realestate-booking-suite'); ?></h3>
                <div class="resbs-booking-form">
                    <div class="resbs-booking-field">
                        <label for="checkin_date"><?php esc_html_e('Check-in Date', 'realestate-booking-suite'); ?></label>
                        <input type="date" id="checkin_date" name="checkin_date" required>
                    </div>
                    <div class="resbs-booking-field">
                        <label for="checkout_date"><?php esc_html_e('Check-out Date', 'realestate-booking-suite'); ?></label>
                        <input type="date" id="checkout_date" name="checkout_date" required>
                    </div>
                    <div class="resbs-booking-field">
                        <label for="guests"><?php esc_html_e('Number of Guests', 'realestate-booking-suite'); ?></label>
                        <select id="guests" name="guests" required>
                            <option value="1">1 <?php esc_html_e('Guest', 'realestate-booking-suite'); ?></option>
                            <option value="2">2 <?php esc_html_e('Guests', 'realestate-booking-suite'); ?></option>
                            <option value="3">3 <?php esc_html_e('Guests', 'realestate-booking-suite'); ?></option>
                            <option value="4">4 <?php esc_html_e('Guests', 'realestate-booking-suite'); ?></option>
                            <option value="5">5+ <?php esc_html_e('Guests', 'realestate-booking-suite'); ?></option>
                        </select>
                    </div>
                    <div class="resbs-booking-buttons">
                        <button type="button" class="resbs-booking-submit-btn">
                            <?php esc_html_e('Validate Dates', 'realestate-booking-suite'); ?>
                        </button>
                        <button type="button" class="resbs-book-now-btn" data-property-id="<?php echo esc_attr($property_id); ?>">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php esc_html_e('Book Now', 'realestate-booking-suite'); ?>
                        </button>
                    </div>
                </div>
                
                <!-- Contact Info (shown when WooCommerce not available) -->
                <div class="resbs-contact-info" style="display: none;">
                    <?php
                    // Get contact settings
                    $contact_settings = new RESBS_Contact_Settings();
                    $contact_info = $contact_settings->get_formatted_contact_info();
                    $widget_title = get_option('resbs_contact_widget_title', esc_html__('Contact Us to Book', 'realestate-booking-suite'));
                    ?>
                    
                    <h4><?php echo esc_html($widget_title); ?></h4>
                    <p><?php esc_html_e('For booking inquiries, please contact us:', 'realestate-booking-suite'); ?></p>
                    
                    <?php if (!empty($contact_info)): ?>
                        <?php foreach ($contact_info as $type => $info): ?>
                            <p>
                                <strong><?php echo esc_html($info['label']); ?></strong> 
                                <a href="<?php echo esc_url($info['link']); ?>" target="_blank" rel="noopener">
                                    <?php echo esc_html($info['value']); ?>
                                </a>
                            </p>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p><?php esc_html_e('Contact information not configured. Please contact the administrator.', 'realestate-booking-suite'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Property Favorites Section -->
<?php do_action('resbs_single_property_actions', $property_id); ?>

<!-- Property Map Section -->
<?php do_action('resbs_property_map_display', $property_id, 'single'); ?>

<?php
// Try to get footer, but don't fail if it doesn't exist
if (function_exists('get_footer')) {
    get_footer();
} else {
    // Fallback footer
    ?>
    <?php wp_footer(); ?>
    </body>
    </html>
    <?php
}
?>
