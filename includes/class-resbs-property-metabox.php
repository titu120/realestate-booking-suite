<?php
/**
 * Professional Property Metabox Class
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Property_Metabox {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('add_meta_boxes', array($this, 'add_property_metaboxes'));
        add_action('save_post', array($this, 'save_property_metabox'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_resbs_upload_property_media', array($this, 'handle_media_upload'));
        add_action('wp_ajax_resbs_delete_property_media', array($this, 'handle_media_delete'));
    }

    /**
     * Add property metaboxes
     */
    public function add_property_metaboxes() {
        add_meta_box(
            'resbs-property-main',
            esc_html__('Property Information', 'realestate-booking-suite'),
            array($this, 'property_main_metabox'),
            'property',
            'normal',
            'high'
        );

        add_meta_box(
            'resbs-property-badges',
            esc_html__('Property Badges', 'realestate-booking-suite'),
            array($this, 'property_badges_metabox'),
            'property',
            'side',
            'default'
        );

        // Booking settings moved to main content area in stunning design
    }

    /**
     * Main Property Metabox - Stunning Design
     */
    public function property_main_metabox($post) {
        wp_nonce_field('resbs_property_metabox_nonce', 'resbs_property_metabox_nonce');
        
        // Get all property data
        $price = get_post_meta($post->ID, '_property_price', true);
        $price_per_sqft = get_post_meta($post->ID, '_property_price_per_sqft', true);
        $price_note = get_post_meta($post->ID, '_property_price_note', true);
        $call_for_price = get_post_meta($post->ID, '_property_call_for_price', true);
        
        $bedrooms = get_post_meta($post->ID, '_property_bedrooms', true);
        $bathrooms = get_post_meta($post->ID, '_property_bathrooms', true);
        $half_baths = get_post_meta($post->ID, '_property_half_baths', true);
        $total_rooms = get_post_meta($post->ID, '_property_total_rooms', true);
        $floors = get_post_meta($post->ID, '_property_floors', true);
        $floor_level = get_post_meta($post->ID, '_property_floor_level', true);
        $area_sqft = get_post_meta($post->ID, '_property_area_sqft', true);
        $lot_size_sqft = get_post_meta($post->ID, '_property_lot_size_sqft', true);
        $year_built = get_post_meta($post->ID, '_property_year_built', true);
        $year_remodeled = get_post_meta($post->ID, '_property_year_remodeled', true);
        
        $property_type = get_post_meta($post->ID, '_property_type', true);
        $property_status = get_post_meta($post->ID, '_property_status', true);
        $property_condition = get_post_meta($post->ID, '_property_condition', true);
        
        $address = get_post_meta($post->ID, '_property_address', true);
        $city = get_post_meta($post->ID, '_property_city', true);
        $state = get_post_meta($post->ID, '_property_state', true);
        $zip = get_post_meta($post->ID, '_property_zip', true);
        $country = get_post_meta($post->ID, '_property_country', true);
        $latitude = get_post_meta($post->ID, '_property_latitude', true);
        $longitude = get_post_meta($post->ID, '_property_longitude', true);
        $hide_address = get_post_meta($post->ID, '_property_hide_address', true);
        
        $features = get_post_meta($post->ID, '_property_features', true);
        $amenities = get_post_meta($post->ID, '_property_amenities', true);
        $parking = get_post_meta($post->ID, '_property_parking', true);
        $heating = get_post_meta($post->ID, '_property_heating', true);
        $cooling = get_post_meta($post->ID, '_property_cooling', true);
        $basement = get_post_meta($post->ID, '_property_basement', true);
        $roof = get_post_meta($post->ID, '_property_roof', true);
        $exterior_material = get_post_meta($post->ID, '_property_exterior_material', true);
        $floor_covering = get_post_meta($post->ID, '_property_floor_covering', true);
        
        $gallery_images = get_post_meta($post->ID, '_property_gallery', true);
        $floor_plans = get_post_meta($post->ID, '_property_floor_plans', true);
        $virtual_tour = get_post_meta($post->ID, '_property_virtual_tour', true);
        $video_url = get_post_meta($post->ID, '_property_video_url', true);
        $video_embed = get_post_meta($post->ID, '_property_video_embed', true);
        
        ?>
        <div class="resbs-stunning-metabox">
            <!-- Header Section -->
            <div class="resbs-metabox-header">
                <div class="resbs-header-content">
                    <div class="resbs-header-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 22V12H15V22" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="resbs-header-text">
                        <h2><?php esc_html_e('Property Information', 'realestate-booking-suite'); ?></h2>
                        <p><?php esc_html_e('Complete property details and specifications', 'realestate-booking-suite'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="resbs-stunning-tabs">
                <nav class="resbs-tab-navigation">
                    <button type="button" class="resbs-tab-nav-btn active" data-tab="overview">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Overview', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="pricing">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="12" y1="1" x2="12" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6312 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6312 13.6815 18 14.5717 18 15.5C18 16.4283 17.6312 17.3185 16.9749 17.9749C16.3185 18.6312 15.4283 19 14.5 19H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Pricing', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="specifications">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                <line x1="9" y1="9" x2="15" y2="9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="9" y1="15" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Specifications', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="location">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 10C21 17 12 23 12 23S3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.3639 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Location', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="features">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Features', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="media">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="2"/>
                                <polyline points="21,15 16,10 5,21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Media', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="booking">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.105 21 21 20.105 21 19V7C21 5.895 20.105 5 19 5H5C3.895 5 3 5.895 3 7V19C3 20.105 3.895 21 5 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Booking', 'realestate-booking-suite'); ?></span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="resbs-stunning-content">
                <!-- Overview Tab -->
                <div id="overview" class="resbs-tab-content active">
                    <div class="resbs-content-grid">
                        <!-- Property Classification Card -->
                        <div class="resbs-content-card resbs-card-primary">
                            <div class="resbs-card-header">
                                <div class="resbs-card-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="resbs-card-title">
                                    <h3><?php esc_html_e('Property Classification', 'realestate-booking-suite'); ?></h3>
                                    <p><?php esc_html_e('Basic property information and type', 'realestate-booking-suite'); ?></p>
                                </div>
                            </div>
                            <div class="resbs-card-body">
                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_type"><?php esc_html_e('Property Type', 'realestate-booking-suite'); ?></label>
                                        <select id="property_type" name="property_type" class="resbs-stunning-select">
                                            <option value=""><?php esc_html_e('Select Property Type', 'realestate-booking-suite'); ?></option>
                                            <option value="house" <?php selected($property_type, 'house'); ?>><?php esc_html_e('House', 'realestate-booking-suite'); ?></option>
                                            <option value="apartment" <?php selected($property_type, 'apartment'); ?>><?php esc_html_e('Apartment', 'realestate-booking-suite'); ?></option>
                                            <option value="condo" <?php selected($property_type, 'condo'); ?>><?php esc_html_e('Condo', 'realestate-booking-suite'); ?></option>
                                            <option value="townhouse" <?php selected($property_type, 'townhouse'); ?>><?php esc_html_e('Townhouse', 'realestate-booking-suite'); ?></option>
                                            <option value="villa" <?php selected($property_type, 'villa'); ?>><?php esc_html_e('Villa', 'realestate-booking-suite'); ?></option>
                                            <option value="commercial" <?php selected($property_type, 'commercial'); ?>><?php esc_html_e('Commercial', 'realestate-booking-suite'); ?></option>
                                            <option value="land" <?php selected($property_type, 'land'); ?>><?php esc_html_e('Land', 'realestate-booking-suite'); ?></option>
                                        </select>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_status"><?php esc_html_e('Property Status', 'realestate-booking-suite'); ?></label>
                                        <select id="property_status" name="property_status" class="resbs-stunning-select">
                                            <option value=""><?php esc_html_e('Select Status', 'realestate-booking-suite'); ?></option>
                                            <option value="for-sale" <?php selected($property_status, 'for-sale'); ?>><?php esc_html_e('For Sale', 'realestate-booking-suite'); ?></option>
                                            <option value="for-rent" <?php selected($property_status, 'for-rent'); ?>><?php esc_html_e('For Rent', 'realestate-booking-suite'); ?></option>
                                            <option value="sold" <?php selected($property_status, 'sold'); ?>><?php esc_html_e('Sold', 'realestate-booking-suite'); ?></option>
                                            <option value="rented" <?php selected($property_status, 'rented'); ?>><?php esc_html_e('Rented', 'realestate-booking-suite'); ?></option>
                                            <option value="off-market" <?php selected($property_status, 'off-market'); ?>><?php esc_html_e('Off Market', 'realestate-booking-suite'); ?></option>
                                        </select>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_condition"><?php esc_html_e('Property Condition', 'realestate-booking-suite'); ?></label>
                                        <select id="property_condition" name="property_condition" class="resbs-stunning-select">
                                            <option value=""><?php esc_html_e('Select Condition', 'realestate-booking-suite'); ?></option>
                                            <option value="excellent" <?php selected($property_condition, 'excellent'); ?>><?php esc_html_e('Excellent', 'realestate-booking-suite'); ?></option>
                                            <option value="very-good" <?php selected($property_condition, 'very-good'); ?>><?php esc_html_e('Very Good', 'realestate-booking-suite'); ?></option>
                                            <option value="good" <?php selected($property_condition, 'good'); ?>><?php esc_html_e('Good', 'realestate-booking-suite'); ?></option>
                                            <option value="fair" <?php selected($property_condition, 'fair'); ?>><?php esc_html_e('Fair', 'realestate-booking-suite'); ?></option>
                                            <option value="needs-work" <?php selected($property_condition, 'needs-work'); ?>><?php esc_html_e('Needs Work', 'realestate-booking-suite'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pricing Tab -->
                <div id="pricing" class="resbs-tab-content">
                    <div class="resbs-content-grid">
                        <div class="resbs-content-card resbs-card-success">
                            <div class="resbs-card-header">
                                <div class="resbs-card-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <line x1="12" y1="1" x2="12" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6312 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6312 13.6815 18 14.5717 18 15.5C18 16.4283 17.6312 17.3185 16.9749 17.9749C16.3185 18.6312 15.4283 19 14.5 19H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="resbs-card-title">
                                    <h3><?php esc_html_e('Property Pricing', 'realestate-booking-suite'); ?></h3>
                                    <p><?php esc_html_e('Set property price and pricing details', 'realestate-booking-suite'); ?></p>
                                </div>
                            </div>
                            <div class="resbs-card-body">
                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_price"><?php esc_html_e('Price', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-input-group">
                                            <span class="resbs-input-prefix">$</span>
                                            <input type="number" id="property_price" name="property_price" value="<?php echo esc_attr($price); ?>" class="resbs-stunning-input" placeholder="0">
                                        </div>
                                        <p class="resbs-input-help"><?php esc_html_e('Enter the property price', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_price_per_sqft"><?php esc_html_e('Price per Sq Ft', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-input-group">
                                            <span class="resbs-input-prefix">$</span>
                                            <input type="number" id="property_price_per_sqft" name="property_price_per_sqft" value="<?php echo esc_attr($price_per_sqft); ?>" class="resbs-stunning-input" placeholder="0">
                                        </div>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_price_note"><?php esc_html_e('Price Note', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_price_note" name="property_price_note" value="<?php echo esc_attr($price_note); ?>" class="resbs-stunning-input" placeholder="<?php esc_attr_e('e.g., Starting From', 'realestate-booking-suite'); ?>">
                                    </div>
                                </div>
                                <div class="resbs-form-group">
                                    <label class="resbs-stunning-checkbox">
                                        <input type="checkbox" id="property_call_for_price" name="property_call_for_price" value="1" <?php checked($call_for_price, '1'); ?>>
                                        <span class="resbs-checkbox-mark"></span>
                                        <span class="resbs-checkbox-text"><?php esc_html_e('Call for Price', 'realestate-booking-suite'); ?></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Continue with other tabs... -->
                <!-- Specifications Tab -->
                <div id="specifications" class="resbs-tab-content">
                    <div class="resbs-content-grid">
                        <div class="resbs-content-card resbs-card-info">
                            <div class="resbs-card-header">
                                <div class="resbs-card-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                        <line x1="9" y1="9" x2="15" y2="9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <line x1="9" y1="15" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="resbs-card-title">
                                    <h3><?php esc_html_e('Property Specifications', 'realestate-booking-suite'); ?></h3>
                                    <p><?php esc_html_e('Detailed property specifications and measurements', 'realestate-booking-suite'); ?></p>
                                </div>
                            </div>
                            <div class="resbs-card-body">
                                <div class="resbs-specs-grid">
                                    <div class="resbs-spec-item">
                                        <label for="property_bedrooms"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-number-control">
                                            <button type="button" class="resbs-number-btn" data-target="property_bedrooms" data-action="decrease">-</button>
                                            <input type="number" id="property_bedrooms" name="property_bedrooms" value="<?php echo esc_attr($bedrooms); ?>" min="0" class="resbs-stunning-input">
                                            <button type="button" class="resbs-number-btn" data-target="property_bedrooms" data-action="increase">+</button>
                                        </div>
                                    </div>
                                    <div class="resbs-spec-item">
                                        <label for="property_bathrooms"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-number-control">
                                            <button type="button" class="resbs-number-btn" data-target="property_bathrooms" data-action="decrease">-</button>
                                            <input type="number" id="property_bathrooms" name="property_bathrooms" value="<?php echo esc_attr($bathrooms); ?>" min="0" class="resbs-stunning-input">
                                            <button type="button" class="resbs-number-btn" data-target="property_bathrooms" data-action="increase">+</button>
                                        </div>
                                    </div>
                                    <div class="resbs-spec-item">
                                        <label for="property_half_baths"><?php esc_html_e('Half Baths', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-number-control">
                                            <button type="button" class="resbs-number-btn" data-target="property_half_baths" data-action="decrease">-</button>
                                            <input type="number" id="property_half_baths" name="property_half_baths" value="<?php echo esc_attr($half_baths); ?>" min="0" class="resbs-stunning-input">
                                            <button type="button" class="resbs-number-btn" data-target="property_half_baths" data-action="increase">+</button>
                                        </div>
                                    </div>
                                    <div class="resbs-spec-item">
                                        <label for="property_total_rooms"><?php esc_html_e('Total Rooms', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-number-control">
                                            <button type="button" class="resbs-number-btn" data-target="property_total_rooms" data-action="decrease">-</button>
                                            <input type="number" id="property_total_rooms" name="property_total_rooms" value="<?php echo esc_attr($total_rooms); ?>" min="0" class="resbs-stunning-input">
                                            <button type="button" class="resbs-number-btn" data-target="property_total_rooms" data-action="increase">+</button>
                                        </div>
                                    </div>
                                    <div class="resbs-spec-item">
                                        <label for="property_floors"><?php esc_html_e('Floors', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-number-control">
                                            <button type="button" class="resbs-number-btn" data-target="property_floors" data-action="decrease">-</button>
                                            <input type="number" id="property_floors" name="property_floors" value="<?php echo esc_attr($floors); ?>" min="0" class="resbs-stunning-input">
                                            <button type="button" class="resbs-number-btn" data-target="property_floors" data-action="increase">+</button>
                                        </div>
                                    </div>
                                    <div class="resbs-spec-item">
                                        <label for="property_floor_level"><?php esc_html_e('Floor Level', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-number-control">
                                            <button type="button" class="resbs-number-btn" data-target="property_floor_level" data-action="decrease">-</button>
                                            <input type="number" id="property_floor_level" name="property_floor_level" value="<?php echo esc_attr($floor_level); ?>" min="0" class="resbs-stunning-input">
                                            <button type="button" class="resbs-number-btn" data-target="property_floor_level" data-action="increase">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_area_sqft"><?php esc_html_e('Area (Sq Ft)', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-input-group">
                                            <input type="number" id="property_area_sqft" name="property_area_sqft" value="<?php echo esc_attr($area_sqft); ?>" class="resbs-stunning-input" placeholder="0">
                                            <span class="resbs-input-suffix">sq ft</span>
                                        </div>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_lot_size_sqft"><?php esc_html_e('Lot Size (Sq Ft)', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-input-group">
                                            <input type="number" id="property_lot_size_sqft" name="property_lot_size_sqft" value="<?php echo esc_attr($lot_size_sqft); ?>" class="resbs-stunning-input" placeholder="0">
                                            <span class="resbs-input-suffix">sq ft</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_year_built"><?php esc_html_e('Year Built', 'realestate-booking-suite'); ?></label>
                                        <input type="number" id="property_year_built" name="property_year_built" value="<?php echo esc_attr($year_built); ?>" class="resbs-stunning-input" placeholder="1990" min="1800" max="<?php echo date('Y'); ?>">
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_year_remodeled"><?php esc_html_e('Year Remodeled', 'realestate-booking-suite'); ?></label>
                                        <input type="number" id="property_year_remodeled" name="property_year_remodeled" value="<?php echo esc_attr($year_remodeled); ?>" class="resbs-stunning-input" placeholder="2000" min="1800" max="<?php echo date('Y'); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location Tab -->
                <div id="location" class="resbs-tab-content">
                    <div class="resbs-content-grid">
                        <div class="resbs-content-card resbs-card-warning">
                            <div class="resbs-card-header">
                                <div class="resbs-card-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M21 10C21 17 12 23 12 23S3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.3639 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="resbs-card-title">
                                    <h3><?php esc_html_e('Property Location', 'realestate-booking-suite'); ?></h3>
                                    <p><?php esc_html_e('Address and location details', 'realestate-booking-suite'); ?></p>
                                </div>
                            </div>
                            <div class="resbs-card-body">
                                <?php if (!get_option('resbs_map_api_key')): ?>
                                <div class="resbs-alert resbs-alert-warning">
                                    <div class="resbs-alert-icon">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M10.29 3.86L1.82 18C1.64573 18.3024 1.57299 18.6453 1.61211 18.9873C1.65124 19.3293 1.80026 19.6522 2.03588 19.9067C2.2715 20.1612 2.58172 20.3343 2.92278 20.4015C3.26384 20.4687 3.61736 20.4273 3.93 20.283L12 16.77L20.07 20.283C20.3826 20.4273 20.7362 20.4687 21.0772 20.4015C21.4183 20.3343 21.7285 20.1612 21.9641 19.9067C22.1997 19.6522 22.3488 19.3293 22.3879 18.9873C22.427 18.6453 22.3543 18.3024 22.18 18L13.71 3.86C13.5318 3.56631 13.2807 3.32312 12.9812 3.15447C12.6817 2.98582 12.3438 2.89725 12 2.89725C11.6562 2.89725 11.3183 2.98582 11.0188 3.15447C10.7193 3.32312 10.4682 3.56631 10.29 3.86Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </div>
                                    <div class="resbs-alert-content">
                                        <p><?php esc_html_e('You need to configure your Google Maps API key to use location features.', 'realestate-booking-suite'); ?></p>
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=resbs-general-settings')); ?>" class="resbs-btn resbs-btn-primary"><?php esc_html_e('Configure API Key', 'realestate-booking-suite'); ?></a>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="resbs-form-group">
                                    <label class="resbs-stunning-checkbox">
                                        <input type="checkbox" id="property_hide_address" name="property_hide_address" value="1" <?php checked($hide_address, '1'); ?>>
                                        <span class="resbs-checkbox-mark"></span>
                                        <span class="resbs-checkbox-text"><?php esc_html_e('Hide address from public', 'realestate-booking-suite'); ?></span>
                                    </label>
                                </div>

                                <div class="resbs-form-group">
                                    <label for="property_address"><?php esc_html_e('Address', 'realestate-booking-suite'); ?></label>
                                    <input type="text" id="property_address" name="property_address" value="<?php echo esc_attr($address); ?>" class="resbs-stunning-input" placeholder="<?php esc_attr_e('Enter full address', 'realestate-booking-suite'); ?>">
                                </div>

                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_city"><?php esc_html_e('City', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_city" name="property_city" value="<?php echo esc_attr($city); ?>" class="resbs-stunning-input" placeholder="<?php esc_attr_e('City', 'realestate-booking-suite'); ?>">
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_state"><?php esc_html_e('State/Province', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_state" name="property_state" value="<?php echo esc_attr($state); ?>" class="resbs-stunning-input" placeholder="<?php esc_attr_e('State/Province', 'realestate-booking-suite'); ?>">
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_zip"><?php esc_html_e('ZIP/Postal Code', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_zip" name="property_zip" value="<?php echo esc_attr($zip); ?>" class="resbs-stunning-input" placeholder="<?php esc_attr_e('ZIP/Postal Code', 'realestate-booking-suite'); ?>">
                                    </div>
                                </div>

                                <div class="resbs-form-group">
                                    <label for="property_country"><?php esc_html_e('Country', 'realestate-booking-suite'); ?></label>
                                    <select id="property_country" name="property_country" class="resbs-stunning-select">
                                        <option value=""><?php esc_html_e('Select Country', 'realestate-booking-suite'); ?></option>
                                        <option value="US" <?php selected($country, 'US'); ?>><?php esc_html_e('United States', 'realestate-booking-suite'); ?></option>
                                        <option value="CA" <?php selected($country, 'CA'); ?>><?php esc_html_e('Canada', 'realestate-booking-suite'); ?></option>
                                        <option value="GB" <?php selected($country, 'GB'); ?>><?php esc_html_e('United Kingdom', 'realestate-booking-suite'); ?></option>
                                        <option value="AU" <?php selected($country, 'AU'); ?>><?php esc_html_e('Australia', 'realestate-booking-suite'); ?></option>
                                        <option value="DE" <?php selected($country, 'DE'); ?>><?php esc_html_e('Germany', 'realestate-booking-suite'); ?></option>
                                        <option value="FR" <?php selected($country, 'FR'); ?>><?php esc_html_e('France', 'realestate-booking-suite'); ?></option>
                                        <option value="IT" <?php selected($country, 'IT'); ?>><?php esc_html_e('Italy', 'realestate-booking-suite'); ?></option>
                                        <option value="ES" <?php selected($country, 'ES'); ?>><?php esc_html_e('Spain', 'realestate-booking-suite'); ?></option>
                                        <option value="BD" <?php selected($country, 'BD'); ?>><?php esc_html_e('Bangladesh', 'realestate-booking-suite'); ?></option>
                                    </select>
                                </div>

                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_latitude"><?php esc_html_e('Latitude', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_latitude" name="property_latitude" value="<?php echo esc_attr($latitude); ?>" class="resbs-stunning-input" placeholder="40.7128">
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_longitude"><?php esc_html_e('Longitude', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_longitude" name="property_longitude" value="<?php echo esc_attr($longitude); ?>" class="resbs-stunning-input" placeholder="-74.0060">
                                    </div>
                                </div>

                                <?php if (get_option('resbs_map_api_key')): ?>
                                <div class="resbs-form-group">
                                    <button type="button" id="resbs-geocode-address" class="resbs-btn resbs-btn-secondary">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M21 10C21 17 12 23 12 23S3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.3639 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <?php esc_html_e('Get Coordinates from Address', 'realestate-booking-suite'); ?>
                                    </button>
                                    <div id="resbs-map-preview" style="height: 300px; margin-top: 15px; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Features Tab -->
                <div id="features" class="resbs-tab-content">
                    <div class="resbs-content-grid">
                        <div class="resbs-content-card resbs-card-secondary">
                            <div class="resbs-card-header">
                                <div class="resbs-card-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="resbs-card-title">
                                    <h3><?php esc_html_e('Property Features & Amenities', 'realestate-booking-suite'); ?></h3>
                                    <p><?php esc_html_e('Features, amenities, and property characteristics', 'realestate-booking-suite'); ?></p>
                                </div>
                            </div>
                            <div class="resbs-card-body">
                                <div class="resbs-form-group">
                                    <label for="property_features"><?php esc_html_e('Features', 'realestate-booking-suite'); ?></label>
                                    <input type="text" id="property_features" name="property_features" value="<?php echo esc_attr($features); ?>" class="resbs-stunning-input" placeholder="<?php esc_attr_e('e.g., Balcony, Fireplace, Hardwood Floors', 'realestate-booking-suite'); ?>">
                                    <p class="resbs-input-help"><?php esc_html_e('Separate multiple features with commas', 'realestate-booking-suite'); ?></p>
                                </div>

                                <div class="resbs-form-group">
                                    <label for="property_amenities"><?php esc_html_e('Amenities', 'realestate-booking-suite'); ?></label>
                                    <input type="text" id="property_amenities" name="property_amenities" value="<?php echo esc_attr($amenities); ?>" class="resbs-stunning-input" placeholder="<?php esc_attr_e('e.g., Pool, Gym, Security', 'realestate-booking-suite'); ?>">
                                    <p class="resbs-input-help"><?php esc_html_e('Separate multiple amenities with commas', 'realestate-booking-suite'); ?></p>
                                </div>

                                <div class="resbs-features-grid">
                                    <div class="resbs-form-group">
                                        <label for="property_parking"><?php esc_html_e('Parking', 'realestate-booking-suite'); ?></label>
                                        <select id="property_parking" name="property_parking" class="resbs-stunning-select">
                                            <option value=""><?php esc_html_e('Select Parking', 'realestate-booking-suite'); ?></option>
                                            <option value="garage" <?php selected($parking, 'garage'); ?>><?php esc_html_e('Garage', 'realestate-booking-suite'); ?></option>
                                            <option value="driveway" <?php selected($parking, 'driveway'); ?>><?php esc_html_e('Driveway', 'realestate-booking-suite'); ?></option>
                                            <option value="street" <?php selected($parking, 'street'); ?>><?php esc_html_e('Street Parking', 'realestate-booking-suite'); ?></option>
                                            <option value="none" <?php selected($parking, 'none'); ?>><?php esc_html_e('No Parking', 'realestate-booking-suite'); ?></option>
                                        </select>
                                    </div>

                                    <div class="resbs-form-group">
                                        <label for="property_heating"><?php esc_html_e('Heating', 'realestate-booking-suite'); ?></label>
                                        <select id="property_heating" name="property_heating" class="resbs-stunning-select">
                                            <option value=""><?php esc_html_e('Select Heating', 'realestate-booking-suite'); ?></option>
                                            <option value="central" <?php selected($heating, 'central'); ?>><?php esc_html_e('Central Heating', 'realestate-booking-suite'); ?></option>
                                            <option value="gas" <?php selected($heating, 'gas'); ?>><?php esc_html_e('Gas Heating', 'realestate-booking-suite'); ?></option>
                                            <option value="electric" <?php selected($heating, 'electric'); ?>><?php esc_html_e('Electric Heating', 'realestate-booking-suite'); ?></option>
                                            <option value="wood" <?php selected($heating, 'wood'); ?>><?php esc_html_e('Wood Heating', 'realestate-booking-suite'); ?></option>
                                            <option value="none" <?php selected($heating, 'none'); ?>><?php esc_html_e('No Heating', 'realestate-booking-suite'); ?></option>
                                        </select>
                                    </div>

                                    <div class="resbs-form-group">
                                        <label for="property_cooling"><?php esc_html_e('Cooling', 'realestate-booking-suite'); ?></label>
                                        <select id="property_cooling" name="property_cooling" class="resbs-stunning-select">
                                            <option value=""><?php esc_html_e('Select Cooling', 'realestate-booking-suite'); ?></option>
                                            <option value="central" <?php selected($cooling, 'central'); ?>><?php esc_html_e('Central Air', 'realestate-booking-suite'); ?></option>
                                            <option value="window" <?php selected($cooling, 'window'); ?>><?php esc_html_e('Window Units', 'realestate-booking-suite'); ?></option>
                                            <option value="none" <?php selected($cooling, 'none'); ?>><?php esc_html_e('No Cooling', 'realestate-booking-suite'); ?></option>
                                        </select>
                                    </div>

                                    <div class="resbs-form-group">
                                        <label for="property_basement"><?php esc_html_e('Basement', 'realestate-booking-suite'); ?></label>
                                        <select id="property_basement" name="property_basement" class="resbs-stunning-select">
                                            <option value=""><?php esc_html_e('Select Basement', 'realestate-booking-suite'); ?></option>
                                            <option value="finished" <?php selected($basement, 'finished'); ?>><?php esc_html_e('Finished Basement', 'realestate-booking-suite'); ?></option>
                                            <option value="unfinished" <?php selected($basement, 'unfinished'); ?>><?php esc_html_e('Unfinished Basement', 'realestate-booking-suite'); ?></option>
                                            <option value="crawl" <?php selected($basement, 'crawl'); ?>><?php esc_html_e('Crawl Space', 'realestate-booking-suite'); ?></option>
                                            <option value="none" <?php selected($basement, 'none'); ?>><?php esc_html_e('No Basement', 'realestate-booking-suite'); ?></option>
                                        </select>
                                    </div>

                                    <div class="resbs-form-group">
                                        <label for="property_roof"><?php esc_html_e('Roof', 'realestate-booking-suite'); ?></label>
                                        <select id="property_roof" name="property_roof" class="resbs-stunning-select">
                                            <option value=""><?php esc_html_e('Select Roof Type', 'realestate-booking-suite'); ?></option>
                                            <option value="asphalt" <?php selected($roof, 'asphalt'); ?>><?php esc_html_e('Asphalt Shingles', 'realestate-booking-suite'); ?></option>
                                            <option value="metal" <?php selected($roof, 'metal'); ?>><?php esc_html_e('Metal Roof', 'realestate-booking-suite'); ?></option>
                                            <option value="tile" <?php selected($roof, 'tile'); ?>><?php esc_html_e('Tile Roof', 'realestate-booking-suite'); ?></option>
                                            <option value="slate" <?php selected($roof, 'slate'); ?>><?php esc_html_e('Slate Roof', 'realestate-booking-suite'); ?></option>
                                            <option value="flat" <?php selected($roof, 'flat'); ?>><?php esc_html_e('Flat Roof', 'realestate-booking-suite'); ?></option>
                                        </select>
                                    </div>

                                    <div class="resbs-form-group">
                                        <label for="property_exterior_material"><?php esc_html_e('Exterior Material', 'realestate-booking-suite'); ?></label>
                                        <select id="property_exterior_material" name="property_exterior_material" class="resbs-stunning-select">
                                            <option value=""><?php esc_html_e('Select Exterior Material', 'realestate-booking-suite'); ?></option>
                                            <option value="brick" <?php selected($exterior_material, 'brick'); ?>><?php esc_html_e('Brick', 'realestate-booking-suite'); ?></option>
                                            <option value="wood" <?php selected($exterior_material, 'wood'); ?>><?php esc_html_e('Wood Siding', 'realestate-booking-suite'); ?></option>
                                            <option value="vinyl" <?php selected($exterior_material, 'vinyl'); ?>><?php esc_html_e('Vinyl Siding', 'realestate-booking-suite'); ?></option>
                                            <option value="stucco" <?php selected($exterior_material, 'stucco'); ?>><?php esc_html_e('Stucco', 'realestate-booking-suite'); ?></option>
                                            <option value="stone" <?php selected($exterior_material, 'stone'); ?>><?php esc_html_e('Stone', 'realestate-booking-suite'); ?></option>
                                            <option value="concrete" <?php selected($exterior_material, 'concrete'); ?>><?php esc_html_e('Concrete', 'realestate-booking-suite'); ?></option>
                                        </select>
                                    </div>

                                    <div class="resbs-form-group">
                                        <label for="property_floor_covering"><?php esc_html_e('Floor Covering', 'realestate-booking-suite'); ?></label>
                                        <select id="property_floor_covering" name="property_floor_covering" class="resbs-stunning-select">
                                            <option value=""><?php esc_html_e('Select Floor Covering', 'realestate-booking-suite'); ?></option>
                                            <option value="hardwood" <?php selected($floor_covering, 'hardwood'); ?>><?php esc_html_e('Hardwood', 'realestate-booking-suite'); ?></option>
                                            <option value="carpet" <?php selected($floor_covering, 'carpet'); ?>><?php esc_html_e('Carpet', 'realestate-booking-suite'); ?></option>
                                            <option value="tile" <?php selected($floor_covering, 'tile'); ?>><?php esc_html_e('Tile', 'realestate-booking-suite'); ?></option>
                                            <option value="laminate" <?php selected($floor_covering, 'laminate'); ?>><?php esc_html_e('Laminate', 'realestate-booking-suite'); ?></option>
                                            <option value="vinyl" <?php selected($floor_covering, 'vinyl'); ?>><?php esc_html_e('Vinyl', 'realestate-booking-suite'); ?></option>
                                            <option value="concrete" <?php selected($floor_covering, 'concrete'); ?>><?php esc_html_e('Concrete', 'realestate-booking-suite'); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Media Tab -->
                <div id="media" class="resbs-tab-content">
                    <div class="resbs-content-grid">
                        <div class="resbs-content-card resbs-card-dark">
                            <div class="resbs-card-header">
                                <div class="resbs-card-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="2"/>
                                        <polyline points="21,15 16,10 5,21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="resbs-card-title">
                                    <h3><?php esc_html_e('Property Media', 'realestate-booking-suite'); ?></h3>
                                    <p><?php esc_html_e('Photos, videos, and virtual tours', 'realestate-booking-suite'); ?></p>
                                </div>
                            </div>
                            <div class="resbs-card-body">
                                <!-- Photo Gallery -->
                                <div class="resbs-media-section">
                                    <h4><?php esc_html_e('Photo Gallery', 'realestate-booking-suite'); ?></h4>
                                    <div class="resbs-stunning-uploader">
                                        <div class="resbs-upload-area" id="gallery-upload-area">
                                            <div class="resbs-upload-content">
                                                <div class="resbs-upload-icon">
                                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                                        <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="2"/>
                                                        <polyline points="21,15 16,10 5,21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </div>
                                                <h5><?php esc_html_e('Upload Property Photos', 'realestate-booking-suite'); ?></h5>
                                                <p><?php esc_html_e('Drag and drop images here or click to browse', 'realestate-booking-suite'); ?></p>
                                                <p class="resbs-upload-info"><?php esc_html_e('JPG, PNG, GIF up to 10MB each', 'realestate-booking-suite'); ?></p>
                                            </div>
                                            <input type="file" id="gallery-upload" name="gallery_upload[]" multiple accept="image/*" style="display: none;">
                                        </div>
                                        <div class="resbs-gallery-grid" id="gallery-grid">
                                            <?php
                                            if (!empty($gallery_images)) {
                                                foreach ($gallery_images as $image_id) {
                                                    $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                                    if ($image_url) {
                                                        echo '<div class="resbs-gallery-item" data-id="' . esc_attr($image_id) . '">';
                                                        echo '<img src="' . esc_url($image_url) . '" alt="">';
                                                        echo '<button type="button" class="resbs-remove-image" data-id="' . esc_attr($image_id) . '">×</button>';
                                                        echo '</div>';
                                                    }
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Virtual Tour -->
                                <div class="resbs-media-section">
                                    <h4><?php esc_html_e('Virtual Tour', 'realestate-booking-suite'); ?></h4>
                                    <div class="resbs-form-group">
                                        <label for="property_virtual_tour"><?php esc_html_e('Virtual Tour URL', 'realestate-booking-suite'); ?></label>
                                        <input type="url" id="property_virtual_tour" name="property_virtual_tour" value="<?php echo esc_attr($virtual_tour); ?>" class="resbs-stunning-input" placeholder="https://example.com/virtual-tour">
                                        <p class="resbs-input-help"><?php esc_html_e('Enter the URL for your virtual tour (360° photos, Matterport, etc.)', 'realestate-booking-suite'); ?></p>
                                    </div>
                                </div>

                                <!-- Video -->
                                <div class="resbs-media-section">
                                    <h4><?php esc_html_e('Property Video', 'realestate-booking-suite'); ?></h4>
                                    <div class="resbs-form-group">
                                        <label for="property_video_url"><?php esc_html_e('Video URL', 'realestate-booking-suite'); ?></label>
                                        <input type="url" id="property_video_url" name="property_video_url" value="<?php echo esc_attr($video_url); ?>" class="resbs-stunning-input" placeholder="https://youtube.com/watch?v=...">
                                        <p class="resbs-input-help"><?php esc_html_e('YouTube, Vimeo, or other video platform URL', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_video_embed"><?php esc_html_e('Video Embed Code', 'realestate-booking-suite'); ?></label>
                                        <textarea id="property_video_embed" name="property_video_embed" class="resbs-stunning-textarea" rows="4" placeholder="<?php esc_attr_e('Paste your video embed code here...', 'realestate-booking-suite'); ?>"><?php echo esc_textarea($video_embed); ?></textarea>
                                        <p class="resbs-input-help"><?php esc_html_e('Alternative: Paste embed code directly', 'realestate-booking-suite'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Tab -->
                <div id="booking" class="resbs-tab-content">
                    <div class="resbs-content-grid">
                        <div class="resbs-content-card resbs-card-primary">
                            <div class="resbs-card-header">
                                <div class="resbs-card-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M8 7V3M16 7V3M7 11H17M5 21H19C20.105 21 21 20.105 21 19V7C21 5.895 20.105 5 19 5H5C3.895 5 3 5.895 3 7V19C3 20.105 3.895 21 5 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="resbs-card-title">
                                    <h3><?php esc_html_e('Booking Settings', 'realestate-booking-suite'); ?></h3>
                                    <p><?php esc_html_e('Configure booking availability and policies', 'realestate-booking-suite'); ?></p>
                                </div>
                            </div>
                            <div class="resbs-card-body">
                                <?php
                                $enable_booking = get_post_meta($post->ID, '_property_enable_booking', true);
                                $min_stay = get_post_meta($post->ID, '_property_min_stay', true);
                                $max_stay = get_post_meta($post->ID, '_property_max_stay', true);
                                $checkin_time = get_post_meta($post->ID, '_property_checkin_time', true);
                                $checkout_time = get_post_meta($post->ID, '_property_checkout_time', true);
                                $cancellation_policy = get_post_meta($post->ID, '_property_cancellation_policy', true);
                                ?>
                                
                                <div class="resbs-form-group">
                                    <label class="resbs-stunning-checkbox">
                                        <input type="checkbox" id="property_enable_booking" name="property_enable_booking" value="1" <?php checked($enable_booking, '1'); ?>>
                                        <span class="resbs-checkbox-mark"></span>
                                        <span class="resbs-checkbox-text"><?php esc_html_e('Enable Booking for this Property', 'realestate-booking-suite'); ?></span>
                                    </label>
                                    <p class="resbs-input-help"><?php esc_html_e('Allow visitors to book this property for short-term stays', 'realestate-booking-suite'); ?></p>
                                </div>

                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_min_stay"><?php esc_html_e('Minimum Stay (Days)', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-number-control">
                                            <button type="button" class="resbs-number-btn" data-target="property_min_stay" data-action="decrease">-</button>
                                            <input type="number" id="property_min_stay" name="property_min_stay" value="<?php echo esc_attr($min_stay ?: '1'); ?>" min="1" class="resbs-stunning-input">
                                            <button type="button" class="resbs-number-btn" data-target="property_min_stay" data-action="increase">+</button>
                                        </div>
                                        <p class="resbs-input-help"><?php esc_html_e('Minimum number of days for booking', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_max_stay"><?php esc_html_e('Maximum Stay (Days)', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-number-control">
                                            <button type="button" class="resbs-number-btn" data-target="property_max_stay" data-action="decrease">-</button>
                                            <input type="number" id="property_max_stay" name="property_max_stay" value="<?php echo esc_attr($max_stay ?: '30'); ?>" min="1" class="resbs-stunning-input">
                                            <button type="button" class="resbs-number-btn" data-target="property_max_stay" data-action="increase">+</button>
                                        </div>
                                        <p class="resbs-input-help"><?php esc_html_e('Maximum number of days for booking', 'realestate-booking-suite'); ?></p>
                                    </div>
                                </div>

                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_checkin_time"><?php esc_html_e('Check-in Time', 'realestate-booking-suite'); ?></label>
                                        <input type="time" id="property_checkin_time" name="property_checkin_time" value="<?php echo esc_attr($checkin_time); ?>" class="resbs-stunning-input">
                                        <p class="resbs-input-help"><?php esc_html_e('Default check-in time for guests', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_checkout_time"><?php esc_html_e('Check-out Time', 'realestate-booking-suite'); ?></label>
                                        <input type="time" id="property_checkout_time" name="property_checkout_time" value="<?php echo esc_attr($checkout_time); ?>" class="resbs-stunning-input">
                                        <p class="resbs-input-help"><?php esc_html_e('Default check-out time for guests', 'realestate-booking-suite'); ?></p>
                                    </div>
                                </div>

                                <div class="resbs-form-group">
                                    <label for="property_cancellation_policy"><?php esc_html_e('Cancellation Policy', 'realestate-booking-suite'); ?></label>
                                    <select id="property_cancellation_policy" name="property_cancellation_policy" class="resbs-stunning-select">
                                        <option value=""><?php esc_html_e('Select Cancellation Policy', 'realestate-booking-suite'); ?></option>
                                        <option value="flexible" <?php selected($cancellation_policy, 'flexible'); ?>><?php esc_html_e('Flexible - Free cancellation up to 24 hours before check-in', 'realestate-booking-suite'); ?></option>
                                        <option value="moderate" <?php selected($cancellation_policy, 'moderate'); ?>><?php esc_html_e('Moderate - Free cancellation up to 5 days before check-in', 'realestate-booking-suite'); ?></option>
                                        <option value="strict" <?php selected($cancellation_policy, 'strict'); ?>><?php esc_html_e('Strict - Free cancellation up to 7 days before check-in', 'realestate-booking-suite'); ?></option>
                                        <option value="super_strict" <?php selected($cancellation_policy, 'super_strict'); ?>><?php esc_html_e('Super Strict - No refunds for cancellations', 'realestate-booking-suite'); ?></option>
                                    </select>
                                    <p class="resbs-input-help"><?php esc_html_e('Choose the cancellation policy for this property', 'realestate-booking-suite'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Property Details Metabox (Legacy - keeping for compatibility)
     */
    public function property_details_metabox($post) {
        wp_nonce_field('resbs_property_metabox_nonce', 'resbs_property_metabox_nonce');
        
        $price = get_post_meta($post->ID, '_property_price', true);
        $price_per_sqft = get_post_meta($post->ID, '_property_price_per_sqft', true);
        $price_note = get_post_meta($post->ID, '_property_price_note', true);
        $call_for_price = get_post_meta($post->ID, '_property_call_for_price', true);
        
        $bedrooms = get_post_meta($post->ID, '_property_bedrooms', true);
        $bathrooms = get_post_meta($post->ID, '_property_bathrooms', true);
        $half_baths = get_post_meta($post->ID, '_property_half_baths', true);
        $total_rooms = get_post_meta($post->ID, '_property_total_rooms', true);
        $floors = get_post_meta($post->ID, '_property_floors', true);
        $floor_level = get_post_meta($post->ID, '_property_floor_level', true);
        $area_sqft = get_post_meta($post->ID, '_property_area_sqft', true);
        $lot_size_sqft = get_post_meta($post->ID, '_property_lot_size_sqft', true);
        $year_built = get_post_meta($post->ID, '_property_year_built', true);
        $year_remodeled = get_post_meta($post->ID, '_property_year_remodeled', true);
        
        $property_type = get_post_meta($post->ID, '_property_type', true);
        $property_status = get_post_meta($post->ID, '_property_status', true);
        $property_condition = get_post_meta($post->ID, '_property_condition', true);
        
        ?>
        <div class="resbs-metabox-container">
            <div class="resbs-tabs">
                <nav class="resbs-tab-nav">
                    <button type="button" class="resbs-tab-btn active" data-tab="basic"><?php esc_html_e('Basic Info', 'realestate-booking-suite'); ?></button>
                    <button type="button" class="resbs-tab-btn" data-tab="pricing"><?php esc_html_e('Pricing', 'realestate-booking-suite'); ?></button>
                    <button type="button" class="resbs-tab-btn" data-tab="specifications"><?php esc_html_e('Specifications', 'realestate-booking-suite'); ?></button>
                </nav>

                <div class="resbs-tab-content">
                    <!-- Basic Info Tab -->
                    <div id="basic" class="resbs-tab-panel active">
                        <div class="resbs-form-section">
                            <h3><?php esc_html_e('Property Classification', 'realestate-booking-suite'); ?></h3>
                            <div class="resbs-form-row">
                                <div class="resbs-form-group">
                                    <label for="property_type"><?php esc_html_e('Property Type', 'realestate-booking-suite'); ?></label>
                                    <select id="property_type" name="property_type" class="resbs-select">
                                        <option value=""><?php esc_html_e('Select Property Type', 'realestate-booking-suite'); ?></option>
                                        <option value="house" <?php selected($property_type, 'house'); ?>><?php esc_html_e('House', 'realestate-booking-suite'); ?></option>
                                        <option value="apartment" <?php selected($property_type, 'apartment'); ?>><?php esc_html_e('Apartment', 'realestate-booking-suite'); ?></option>
                                        <option value="condo" <?php selected($property_type, 'condo'); ?>><?php esc_html_e('Condo', 'realestate-booking-suite'); ?></option>
                                        <option value="townhouse" <?php selected($property_type, 'townhouse'); ?>><?php esc_html_e('Townhouse', 'realestate-booking-suite'); ?></option>
                                        <option value="villa" <?php selected($property_type, 'villa'); ?>><?php esc_html_e('Villa', 'realestate-booking-suite'); ?></option>
                                        <option value="commercial" <?php selected($property_type, 'commercial'); ?>><?php esc_html_e('Commercial', 'realestate-booking-suite'); ?></option>
                                        <option value="land" <?php selected($property_type, 'land'); ?>><?php esc_html_e('Land', 'realestate-booking-suite'); ?></option>
                                    </select>
                                </div>
                                <div class="resbs-form-group">
                                    <label for="property_status"><?php esc_html_e('Property Status', 'realestate-booking-suite'); ?></label>
                                    <select id="property_status" name="property_status" class="resbs-select">
                                        <option value=""><?php esc_html_e('Select Status', 'realestate-booking-suite'); ?></option>
                                        <option value="for-sale" <?php selected($property_status, 'for-sale'); ?>><?php esc_html_e('For Sale', 'realestate-booking-suite'); ?></option>
                                        <option value="for-rent" <?php selected($property_status, 'for-rent'); ?>><?php esc_html_e('For Rent', 'realestate-booking-suite'); ?></option>
                                        <option value="sold" <?php selected($property_status, 'sold'); ?>><?php esc_html_e('Sold', 'realestate-booking-suite'); ?></option>
                                        <option value="rented" <?php selected($property_status, 'rented'); ?>><?php esc_html_e('Rented', 'realestate-booking-suite'); ?></option>
                                        <option value="off-market" <?php selected($property_status, 'off-market'); ?>><?php esc_html_e('Off Market', 'realestate-booking-suite'); ?></option>
                                    </select>
                                </div>
                                <div class="resbs-form-group">
                                    <label for="property_condition"><?php esc_html_e('Property Condition', 'realestate-booking-suite'); ?></label>
                                    <select id="property_condition" name="property_condition" class="resbs-select">
                                        <option value=""><?php esc_html_e('Select Condition', 'realestate-booking-suite'); ?></option>
                                        <option value="excellent" <?php selected($property_condition, 'excellent'); ?>><?php esc_html_e('Excellent', 'realestate-booking-suite'); ?></option>
                                        <option value="very-good" <?php selected($property_condition, 'very-good'); ?>><?php esc_html_e('Very Good', 'realestate-booking-suite'); ?></option>
                                        <option value="good" <?php selected($property_condition, 'good'); ?>><?php esc_html_e('Good', 'realestate-booking-suite'); ?></option>
                                        <option value="fair" <?php selected($property_condition, 'fair'); ?>><?php esc_html_e('Fair', 'realestate-booking-suite'); ?></option>
                                        <option value="needs-work" <?php selected($property_condition, 'needs-work'); ?>><?php esc_html_e('Needs Work', 'realestate-booking-suite'); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Tab -->
                    <div id="pricing" class="resbs-tab-panel">
                        <div class="resbs-form-section">
                            <h3><?php esc_html_e('Property Pricing', 'realestate-booking-suite'); ?></h3>
                            <div class="resbs-form-row">
                                <div class="resbs-form-group">
                                    <label for="property_price"><?php esc_html_e('Price', 'realestate-booking-suite'); ?></label>
                                    <input type="number" id="property_price" name="property_price" value="<?php echo esc_attr($price); ?>" class="resbs-input" placeholder="0">
                                    <p class="description"><?php esc_html_e('Enter the property price', 'realestate-booking-suite'); ?></p>
                                </div>
                                <div class="resbs-form-group">
                                    <label for="property_price_per_sqft"><?php esc_html_e('Price per Sq Ft', 'realestate-booking-suite'); ?></label>
                                    <input type="number" id="property_price_per_sqft" name="property_price_per_sqft" value="<?php echo esc_attr($price_per_sqft); ?>" class="resbs-input" placeholder="0">
                                </div>
                                <div class="resbs-form-group">
                                    <label for="property_price_note"><?php esc_html_e('Price Note', 'realestate-booking-suite'); ?></label>
                                    <input type="text" id="property_price_note" name="property_price_note" value="<?php echo esc_attr($price_note); ?>" class="resbs-input" placeholder="<?php esc_attr_e('e.g., Starting From', 'realestate-booking-suite'); ?>">
                                </div>
                            </div>
                            <div class="resbs-form-group">
                                <label class="resbs-checkbox-label">
                                    <input type="checkbox" id="property_call_for_price" name="property_call_for_price" value="1" <?php checked($call_for_price, '1'); ?>>
                                    <span class="resbs-checkbox-text"><?php esc_html_e('Call for Price', 'realestate-booking-suite'); ?></span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Specifications Tab -->
                    <div id="specifications" class="resbs-tab-panel">
                        <div class="resbs-form-section">
                            <h3><?php esc_html_e('Property Specifications', 'realestate-booking-suite'); ?></h3>
                            <div class="resbs-form-row">
                                <div class="resbs-form-group">
                                    <label for="property_bedrooms"><?php esc_html_e('Bedrooms', 'realestate-booking-suite'); ?></label>
                                    <div class="resbs-number-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_bedrooms" data-action="decrease">-</button>
                                        <input type="number" id="property_bedrooms" name="property_bedrooms" value="<?php echo esc_attr($bedrooms); ?>" min="0" class="resbs-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_bedrooms" data-action="increase">+</button>
                                    </div>
                                </div>
                                <div class="resbs-form-group">
                                    <label for="property_bathrooms"><?php esc_html_e('Bathrooms', 'realestate-booking-suite'); ?></label>
                                    <div class="resbs-number-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_bathrooms" data-action="decrease">-</button>
                                        <input type="number" id="property_bathrooms" name="property_bathrooms" value="<?php echo esc_attr($bathrooms); ?>" min="0" class="resbs-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_bathrooms" data-action="increase">+</button>
                                    </div>
                                </div>
                                <div class="resbs-form-group">
                                    <label for="property_half_baths"><?php esc_html_e('Half Baths', 'realestate-booking-suite'); ?></label>
                                    <div class="resbs-number-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_half_baths" data-action="decrease">-</button>
                                        <input type="number" id="property_half_baths" name="property_half_baths" value="<?php echo esc_attr($half_baths); ?>" min="0" class="resbs-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_half_baths" data-action="increase">+</button>
                                    </div>
                                </div>
                            </div>
                            <div class="resbs-form-row">
                                <div class="resbs-form-group">
                                    <label for="property_total_rooms"><?php esc_html_e('Total Rooms', 'realestate-booking-suite'); ?></label>
                                    <div class="resbs-number-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_total_rooms" data-action="decrease">-</button>
                                        <input type="number" id="property_total_rooms" name="property_total_rooms" value="<?php echo esc_attr($total_rooms); ?>" min="0" class="resbs-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_total_rooms" data-action="increase">+</button>
                                    </div>
                                </div>
                                <div class="resbs-form-group">
                                    <label for="property_floors"><?php esc_html_e('Floors', 'realestate-booking-suite'); ?></label>
                                    <div class="resbs-number-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_floors" data-action="decrease">-</button>
                                        <input type="number" id="property_floors" name="property_floors" value="<?php echo esc_attr($floors); ?>" min="0" class="resbs-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_floors" data-action="increase">+</button>
                                    </div>
                                </div>
                                <div class="resbs-form-group">
                                    <label for="property_floor_level"><?php esc_html_e('Floor Level', 'realestate-booking-suite'); ?></label>
                                    <div class="resbs-number-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_floor_level" data-action="decrease">-</button>
                                        <input type="number" id="property_floor_level" name="property_floor_level" value="<?php echo esc_attr($floor_level); ?>" min="0" class="resbs-input">
                                        <button type="button" class="resbs-number-btn" data-target="property_floor_level" data-action="increase">+</button>
                                    </div>
                                </div>
                            </div>
                            <div class="resbs-form-row">
                                <div class="resbs-form-group">
                                    <label for="property_area_sqft"><?php esc_html_e('Area (Sq Ft)', 'realestate-booking-suite'); ?></label>
                                    <input type="number" id="property_area_sqft" name="property_area_sqft" value="<?php echo esc_attr($area_sqft); ?>" class="resbs-input" placeholder="0">
                                </div>
                                <div class="resbs-form-group">
                                    <label for="property_lot_size_sqft"><?php esc_html_e('Lot Size (Sq Ft)', 'realestate-booking-suite'); ?></label>
                                    <input type="number" id="property_lot_size_sqft" name="property_lot_size_sqft" value="<?php echo esc_attr($lot_size_sqft); ?>" class="resbs-input" placeholder="0">
                                </div>
                            </div>
                            <div class="resbs-form-row">
                                <div class="resbs-form-group">
                                    <label for="property_year_built"><?php esc_html_e('Year Built', 'realestate-booking-suite'); ?></label>
                                    <input type="number" id="property_year_built" name="property_year_built" value="<?php echo esc_attr($year_built); ?>" class="resbs-input" placeholder="1990" min="1800" max="<?php echo date('Y'); ?>">
                                </div>
                                <div class="resbs-form-group">
                                    <label for="property_year_remodeled"><?php esc_html_e('Year Remodeled', 'realestate-booking-suite'); ?></label>
                                    <input type="number" id="property_year_remodeled" name="property_year_remodeled" value="<?php echo esc_attr($year_remodeled); ?>" class="resbs-input" placeholder="2000" min="1800" max="<?php echo date('Y'); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Property Media Metabox
     */
    public function property_media_metabox($post) {
        $gallery_images = get_post_meta($post->ID, '_property_gallery', true);
        $floor_plans = get_post_meta($post->ID, '_property_floor_plans', true);
        $virtual_tour = get_post_meta($post->ID, '_property_virtual_tour', true);
        $video_url = get_post_meta($post->ID, '_property_video_url', true);
        $video_embed = get_post_meta($post->ID, '_property_video_embed', true);
        
        ?>
        <div class="resbs-metabox-container">
            <div class="resbs-tabs">
                <nav class="resbs-tab-nav">
                    <button type="button" class="resbs-tab-btn active" data-tab="gallery"><?php esc_html_e('Photo Gallery', 'realestate-booking-suite'); ?></button>
                    <button type="button" class="resbs-tab-btn" data-tab="floor-plans"><?php esc_html_e('Floor Plans', 'realestate-booking-suite'); ?></button>
                    <button type="button" class="resbs-tab-btn" data-tab="virtual-tour"><?php esc_html_e('Virtual Tour', 'realestate-booking-suite'); ?></button>
                    <button type="button" class="resbs-tab-btn" data-tab="video"><?php esc_html_e('Video', 'realestate-booking-suite'); ?></button>
                </nav>

                <div class="resbs-tab-content">
                    <!-- Photo Gallery Tab -->
                    <div id="gallery" class="resbs-tab-panel active">
                        <div class="resbs-form-section">
                            <h3><?php esc_html_e('Property Photo Gallery', 'realestate-booking-suite'); ?></h3>
                            <div class="resbs-media-uploader">
                                <div class="resbs-upload-area" id="gallery-upload-area">
                                    <div class="resbs-upload-content">
                                        <span class="dashicons dashicons-camera"></span>
                                        <p><?php esc_html_e('Click to upload photos or drag and drop', 'realestate-booking-suite'); ?></p>
                                        <p class="resbs-upload-info"><?php esc_html_e('JPG, PNG, GIF up to 10MB each', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <input type="file" id="gallery-upload" name="gallery_upload[]" multiple accept="image/*" style="display: none;">
                                </div>
                                <div class="resbs-gallery-grid" id="gallery-grid">
                                    <?php
                                    if (!empty($gallery_images)) {
                                        foreach ($gallery_images as $image_id) {
                                            $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                            if ($image_url) {
                                                echo '<div class="resbs-gallery-item" data-id="' . esc_attr($image_id) . '">';
                                                echo '<img src="' . esc_url($image_url) . '" alt="">';
                                                echo '<button type="button" class="resbs-remove-image" data-id="' . esc_attr($image_id) . '">×</button>';
                                                echo '</div>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floor Plans Tab -->
                    <div id="floor-plans" class="resbs-tab-panel">
                        <div class="resbs-form-section">
                            <h3><?php esc_html_e('Floor Plans', 'realestate-booking-suite'); ?></h3>
                            <div class="resbs-media-uploader">
                                <div class="resbs-upload-area" id="floor-plans-upload-area">
                                    <div class="resbs-upload-content">
                                        <span class="dashicons dashicons-format-image"></span>
                                        <p><?php esc_html_e('Click to upload floor plans or drag and drop', 'realestate-booking-suite'); ?></p>
                                        <p class="resbs-upload-info"><?php esc_html_e('JPG, PNG, GIF up to 10MB each', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <input type="file" id="floor-plans-upload" name="floor_plans_upload[]" multiple accept="image/*" style="display: none;">
                                </div>
                                <div class="resbs-gallery-grid" id="floor-plans-grid">
                                    <?php
                                    if (!empty($floor_plans)) {
                                        foreach ($floor_plans as $image_id) {
                                            $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                                            if ($image_url) {
                                                echo '<div class="resbs-gallery-item" data-id="' . esc_attr($image_id) . '">';
                                                echo '<img src="' . esc_url($image_url) . '" alt="">';
                                                echo '<button type="button" class="resbs-remove-image" data-id="' . esc_attr($image_id) . '">×</button>';
                                                echo '</div>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Virtual Tour Tab -->
                    <div id="virtual-tour" class="resbs-tab-panel">
                        <div class="resbs-form-section">
                            <h3><?php esc_html_e('Virtual Tour', 'realestate-booking-suite'); ?></h3>
                            <div class="resbs-form-group">
                                <label for="property_virtual_tour"><?php esc_html_e('Virtual Tour URL', 'realestate-booking-suite'); ?></label>
                                <input type="url" id="property_virtual_tour" name="property_virtual_tour" value="<?php echo esc_attr($virtual_tour); ?>" class="resbs-input" placeholder="https://example.com/virtual-tour">
                                <p class="description"><?php esc_html_e('Enter the URL for your virtual tour (360° photos, Matterport, etc.)', 'realestate-booking-suite'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Video Tab -->
                    <div id="video" class="resbs-tab-panel">
                        <div class="resbs-form-section">
                            <h3><?php esc_html_e('Property Video', 'realestate-booking-suite'); ?></h3>
                            <div class="resbs-form-group">
                                <label for="property_video_url"><?php esc_html_e('Video URL', 'realestate-booking-suite'); ?></label>
                                <input type="url" id="property_video_url" name="property_video_url" value="<?php echo esc_attr($video_url); ?>" class="resbs-input" placeholder="https://youtube.com/watch?v=...">
                                <p class="description"><?php esc_html_e('YouTube, Vimeo, or other video platform URL', 'realestate-booking-suite'); ?></p>
                            </div>
                            <div class="resbs-form-group">
                                <label for="property_video_embed"><?php esc_html_e('Video Embed Code', 'realestate-booking-suite'); ?></label>
                                <textarea id="property_video_embed" name="property_video_embed" class="resbs-textarea" rows="4" placeholder="<?php esc_attr_e('Paste your video embed code here...', 'realestate-booking-suite'); ?>"><?php echo esc_textarea($video_embed); ?></textarea>
                                <p class="description"><?php esc_html_e('Alternative: Paste embed code directly', 'realestate-booking-suite'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Property Location Metabox
     */
    public function property_location_metabox($post) {
        $address = get_post_meta($post->ID, '_property_address', true);
        $city = get_post_meta($post->ID, '_property_city', true);
        $state = get_post_meta($post->ID, '_property_state', true);
        $zip = get_post_meta($post->ID, '_property_zip', true);
        $country = get_post_meta($post->ID, '_property_country', true);
        $latitude = get_post_meta($post->ID, '_property_latitude', true);
        $longitude = get_post_meta($post->ID, '_property_longitude', true);
        $hide_address = get_post_meta($post->ID, '_property_hide_address', true);
        
        ?>
        <div class="resbs-metabox-container">
            <div class="resbs-form-section">
                <h3><?php esc_html_e('Property Location', 'realestate-booking-suite'); ?></h3>
                
                <?php if (!get_option('resbs_map_api_key')): ?>
                <div class="resbs-alert resbs-alert-warning">
                    <p><?php esc_html_e('You need to configure your Google Maps API key to use location features.', 'realestate-booking-suite'); ?></p>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=resbs-general-settings')); ?>" class="resbs-btn resbs-btn-primary"><?php esc_html_e('Configure API Key', 'realestate-booking-suite'); ?></a>
                </div>
                <?php endif; ?>

                <div class="resbs-form-group">
                    <label class="resbs-checkbox-label">
                        <input type="checkbox" id="property_hide_address" name="property_hide_address" value="1" <?php checked($hide_address, '1'); ?>>
                        <span class="resbs-checkbox-text"><?php esc_html_e('Hide address from public', 'realestate-booking-suite'); ?></span>
                    </label>
                </div>

                <div class="resbs-form-row">
                    <div class="resbs-form-group resbs-form-group-full">
                        <label for="property_address"><?php esc_html_e('Address', 'realestate-booking-suite'); ?></label>
                        <input type="text" id="property_address" name="property_address" value="<?php echo esc_attr($address); ?>" class="resbs-input" placeholder="<?php esc_attr_e('Enter full address', 'realestate-booking-suite'); ?>">
                    </div>
                </div>

                <div class="resbs-form-row">
                    <div class="resbs-form-group">
                        <label for="property_city"><?php esc_html_e('City', 'realestate-booking-suite'); ?></label>
                        <input type="text" id="property_city" name="property_city" value="<?php echo esc_attr($city); ?>" class="resbs-input" placeholder="<?php esc_attr_e('City', 'realestate-booking-suite'); ?>">
                    </div>
                    <div class="resbs-form-group">
                        <label for="property_state"><?php esc_html_e('State/Province', 'realestate-booking-suite'); ?></label>
                        <input type="text" id="property_state" name="property_state" value="<?php echo esc_attr($state); ?>" class="resbs-input" placeholder="<?php esc_attr_e('State/Province', 'realestate-booking-suite'); ?>">
                    </div>
                    <div class="resbs-form-group">
                        <label for="property_zip"><?php esc_html_e('ZIP/Postal Code', 'realestate-booking-suite'); ?></label>
                        <input type="text" id="property_zip" name="property_zip" value="<?php echo esc_attr($zip); ?>" class="resbs-input" placeholder="<?php esc_attr_e('ZIP/Postal Code', 'realestate-booking-suite'); ?>">
                    </div>
                </div>

                <div class="resbs-form-row">
                    <div class="resbs-form-group">
                        <label for="property_country"><?php esc_html_e('Country', 'realestate-booking-suite'); ?></label>
                        <select id="property_country" name="property_country" class="resbs-select">
                            <option value=""><?php esc_html_e('Select Country', 'realestate-booking-suite'); ?></option>
                            <option value="US" <?php selected($country, 'US'); ?>><?php esc_html_e('United States', 'realestate-booking-suite'); ?></option>
                            <option value="CA" <?php selected($country, 'CA'); ?>><?php esc_html_e('Canada', 'realestate-booking-suite'); ?></option>
                            <option value="GB" <?php selected($country, 'GB'); ?>><?php esc_html_e('United Kingdom', 'realestate-booking-suite'); ?></option>
                            <option value="AU" <?php selected($country, 'AU'); ?>><?php esc_html_e('Australia', 'realestate-booking-suite'); ?></option>
                            <option value="DE" <?php selected($country, 'DE'); ?>><?php esc_html_e('Germany', 'realestate-booking-suite'); ?></option>
                            <option value="FR" <?php selected($country, 'FR'); ?>><?php esc_html_e('France', 'realestate-booking-suite'); ?></option>
                            <option value="IT" <?php selected($country, 'IT'); ?>><?php esc_html_e('Italy', 'realestate-booking-suite'); ?></option>
                            <option value="ES" <?php selected($country, 'ES'); ?>><?php esc_html_e('Spain', 'realestate-booking-suite'); ?></option>
                            <option value="BD" <?php selected($country, 'BD'); ?>><?php esc_html_e('Bangladesh', 'realestate-booking-suite'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="resbs-form-row">
                    <div class="resbs-form-group">
                        <label for="property_latitude"><?php esc_html_e('Latitude', 'realestate-booking-suite'); ?></label>
                        <input type="text" id="property_latitude" name="property_latitude" value="<?php echo esc_attr($latitude); ?>" class="resbs-input" placeholder="40.7128">
                    </div>
                    <div class="resbs-form-group">
                        <label for="property_longitude"><?php esc_html_e('Longitude', 'realestate-booking-suite'); ?></label>
                        <input type="text" id="property_longitude" name="property_longitude" value="<?php echo esc_attr($longitude); ?>" class="resbs-input" placeholder="-74.0060">
                    </div>
                </div>

                <?php if (get_option('resbs_map_api_key')): ?>
                <div class="resbs-form-group">
                    <button type="button" id="resbs-geocode-address" class="resbs-btn resbs-btn-secondary">
                        <?php esc_html_e('Get Coordinates from Address', 'realestate-booking-suite'); ?>
                    </button>
                    <div id="resbs-map-preview" style="height: 300px; margin-top: 15px; border: 1px solid #ddd; border-radius: 4px;"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Property Features Metabox
     */
    public function property_features_metabox($post) {
        $features = get_post_meta($post->ID, '_property_features', true);
        $amenities = get_post_meta($post->ID, '_property_amenities', true);
        $parking = get_post_meta($post->ID, '_property_parking', true);
        $heating = get_post_meta($post->ID, '_property_heating', true);
        $cooling = get_post_meta($post->ID, '_property_cooling', true);
        $basement = get_post_meta($post->ID, '_property_basement', true);
        $roof = get_post_meta($post->ID, '_property_roof', true);
        $exterior_material = get_post_meta($post->ID, '_property_exterior_material', true);
        $floor_covering = get_post_meta($post->ID, '_property_floor_covering', true);
        
        ?>
        <div class="resbs-metabox-container">
            <div class="resbs-form-section">
                <h3><?php esc_html_e('Property Features', 'realestate-booking-suite'); ?></h3>
                
                <div class="resbs-form-group">
                    <label for="property_features"><?php esc_html_e('Features', 'realestate-booking-suite'); ?></label>
                    <input type="text" id="property_features" name="property_features" value="<?php echo esc_attr($features); ?>" class="resbs-input" placeholder="<?php esc_attr_e('e.g., Balcony, Fireplace, Hardwood Floors', 'realestate-booking-suite'); ?>">
                    <p class="description"><?php esc_html_e('Separate multiple features with commas', 'realestate-booking-suite'); ?></p>
                </div>

                <div class="resbs-form-group">
                    <label for="property_amenities"><?php esc_html_e('Amenities', 'realestate-booking-suite'); ?></label>
                    <input type="text" id="property_amenities" name="property_amenities" value="<?php echo esc_attr($amenities); ?>" class="resbs-input" placeholder="<?php esc_attr_e('e.g., Pool, Gym, Security', 'realestate-booking-suite'); ?>">
                    <p class="description"><?php esc_html_e('Separate multiple amenities with commas', 'realestate-booking-suite'); ?></p>
                </div>

                <div class="resbs-form-group">
                    <label for="property_parking"><?php esc_html_e('Parking', 'realestate-booking-suite'); ?></label>
                    <select id="property_parking" name="property_parking" class="resbs-select">
                        <option value=""><?php esc_html_e('Select Parking', 'realestate-booking-suite'); ?></option>
                        <option value="garage" <?php selected($parking, 'garage'); ?>><?php esc_html_e('Garage', 'realestate-booking-suite'); ?></option>
                        <option value="driveway" <?php selected($parking, 'driveway'); ?>><?php esc_html_e('Driveway', 'realestate-booking-suite'); ?></option>
                        <option value="street" <?php selected($parking, 'street'); ?>><?php esc_html_e('Street Parking', 'realestate-booking-suite'); ?></option>
                        <option value="none" <?php selected($parking, 'none'); ?>><?php esc_html_e('No Parking', 'realestate-booking-suite'); ?></option>
                    </select>
                </div>

                <div class="resbs-form-group">
                    <label for="property_heating"><?php esc_html_e('Heating', 'realestate-booking-suite'); ?></label>
                    <select id="property_heating" name="property_heating" class="resbs-select">
                        <option value=""><?php esc_html_e('Select Heating', 'realestate-booking-suite'); ?></option>
                        <option value="central" <?php selected($heating, 'central'); ?>><?php esc_html_e('Central Heating', 'realestate-booking-suite'); ?></option>
                        <option value="gas" <?php selected($heating, 'gas'); ?>><?php esc_html_e('Gas Heating', 'realestate-booking-suite'); ?></option>
                        <option value="electric" <?php selected($heating, 'electric'); ?>><?php esc_html_e('Electric Heating', 'realestate-booking-suite'); ?></option>
                        <option value="wood" <?php selected($heating, 'wood'); ?>><?php esc_html_e('Wood Heating', 'realestate-booking-suite'); ?></option>
                        <option value="none" <?php selected($heating, 'none'); ?>><?php esc_html_e('No Heating', 'realestate-booking-suite'); ?></option>
                    </select>
                </div>

                <div class="resbs-form-group">
                    <label for="property_cooling"><?php esc_html_e('Cooling', 'realestate-booking-suite'); ?></label>
                    <select id="property_cooling" name="property_cooling" class="resbs-select">
                        <option value=""><?php esc_html_e('Select Cooling', 'realestate-booking-suite'); ?></option>
                        <option value="central" <?php selected($cooling, 'central'); ?>><?php esc_html_e('Central Air', 'realestate-booking-suite'); ?></option>
                        <option value="window" <?php selected($cooling, 'window'); ?>><?php esc_html_e('Window Units', 'realestate-booking-suite'); ?></option>
                        <option value="none" <?php selected($cooling, 'none'); ?>><?php esc_html_e('No Cooling', 'realestate-booking-suite'); ?></option>
                    </select>
                </div>

                <div class="resbs-form-group">
                    <label for="property_basement"><?php esc_html_e('Basement', 'realestate-booking-suite'); ?></label>
                    <select id="property_basement" name="property_basement" class="resbs-select">
                        <option value=""><?php esc_html_e('Select Basement', 'realestate-booking-suite'); ?></option>
                        <option value="finished" <?php selected($basement, 'finished'); ?>><?php esc_html_e('Finished Basement', 'realestate-booking-suite'); ?></option>
                        <option value="unfinished" <?php selected($basement, 'unfinished'); ?>><?php esc_html_e('Unfinished Basement', 'realestate-booking-suite'); ?></option>
                        <option value="crawl" <?php selected($basement, 'crawl'); ?>><?php esc_html_e('Crawl Space', 'realestate-booking-suite'); ?></option>
                        <option value="none" <?php selected($basement, 'none'); ?>><?php esc_html_e('No Basement', 'realestate-booking-suite'); ?></option>
                    </select>
                </div>

                <div class="resbs-form-group">
                    <label for="property_roof"><?php esc_html_e('Roof', 'realestate-booking-suite'); ?></label>
                    <select id="property_roof" name="property_roof" class="resbs-select">
                        <option value=""><?php esc_html_e('Select Roof Type', 'realestate-booking-suite'); ?></option>
                        <option value="asphalt" <?php selected($roof, 'asphalt'); ?>><?php esc_html_e('Asphalt Shingles', 'realestate-booking-suite'); ?></option>
                        <option value="metal" <?php selected($roof, 'metal'); ?>><?php esc_html_e('Metal Roof', 'realestate-booking-suite'); ?></option>
                        <option value="tile" <?php selected($roof, 'tile'); ?>><?php esc_html_e('Tile Roof', 'realestate-booking-suite'); ?></option>
                        <option value="slate" <?php selected($roof, 'slate'); ?>><?php esc_html_e('Slate Roof', 'realestate-booking-suite'); ?></option>
                        <option value="flat" <?php selected($roof, 'flat'); ?>><?php esc_html_e('Flat Roof', 'realestate-booking-suite'); ?></option>
                    </select>
                </div>

                <div class="resbs-form-group">
                    <label for="property_exterior_material"><?php esc_html_e('Exterior Material', 'realestate-booking-suite'); ?></label>
                    <select id="property_exterior_material" name="property_exterior_material" class="resbs-select">
                        <option value=""><?php esc_html_e('Select Exterior Material', 'realestate-booking-suite'); ?></option>
                        <option value="brick" <?php selected($exterior_material, 'brick'); ?>><?php esc_html_e('Brick', 'realestate-booking-suite'); ?></option>
                        <option value="wood" <?php selected($exterior_material, 'wood'); ?>><?php esc_html_e('Wood Siding', 'realestate-booking-suite'); ?></option>
                        <option value="vinyl" <?php selected($exterior_material, 'vinyl'); ?>><?php esc_html_e('Vinyl Siding', 'realestate-booking-suite'); ?></option>
                        <option value="stucco" <?php selected($exterior_material, 'stucco'); ?>><?php esc_html_e('Stucco', 'realestate-booking-suite'); ?></option>
                        <option value="stone" <?php selected($exterior_material, 'stone'); ?>><?php esc_html_e('Stone', 'realestate-booking-suite'); ?></option>
                        <option value="concrete" <?php selected($exterior_material, 'concrete'); ?>><?php esc_html_e('Concrete', 'realestate-booking-suite'); ?></option>
                    </select>
                </div>

                <div class="resbs-form-group">
                    <label for="property_floor_covering"><?php esc_html_e('Floor Covering', 'realestate-booking-suite'); ?></label>
                    <select id="property_floor_covering" name="property_floor_covering" class="resbs-select">
                        <option value=""><?php esc_html_e('Select Floor Covering', 'realestate-booking-suite'); ?></option>
                        <option value="hardwood" <?php selected($floor_covering, 'hardwood'); ?>><?php esc_html_e('Hardwood', 'realestate-booking-suite'); ?></option>
                        <option value="carpet" <?php selected($floor_covering, 'carpet'); ?>><?php esc_html_e('Carpet', 'realestate-booking-suite'); ?></option>
                        <option value="tile" <?php selected($floor_covering, 'tile'); ?>><?php esc_html_e('Tile', 'realestate-booking-suite'); ?></option>
                        <option value="laminate" <?php selected($floor_covering, 'laminate'); ?>><?php esc_html_e('Laminate', 'realestate-booking-suite'); ?></option>
                        <option value="vinyl" <?php selected($floor_covering, 'vinyl'); ?>><?php esc_html_e('Vinyl', 'realestate-booking-suite'); ?></option>
                        <option value="concrete" <?php selected($floor_covering, 'concrete'); ?>><?php esc_html_e('Concrete', 'realestate-booking-suite'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Property Badges Metabox
     */
    public function property_badges_metabox($post) {
        $featured = get_post_meta($post->ID, '_property_featured', true);
        $new = get_post_meta($post->ID, '_property_new', true);
        $sold = get_post_meta($post->ID, '_property_sold', true);
        $foreclosure = get_post_meta($post->ID, '_property_foreclosure', true);
        $open_house = get_post_meta($post->ID, '_property_open_house', true);
        
        ?>
        <div class="resbs-metabox-container">
            <div class="resbs-form-section">
                <h3><?php esc_html_e('Property Badges', 'realestate-booking-suite'); ?></h3>
                
                <div class="resbs-form-group">
                    <label class="resbs-checkbox-label">
                        <input type="checkbox" id="property_featured" name="property_featured" value="1" <?php checked($featured, '1'); ?>>
                        <span class="resbs-checkbox-text"><?php esc_html_e('Featured Property', 'realestate-booking-suite'); ?></span>
                    </label>
                </div>

                <div class="resbs-form-group">
                    <label class="resbs-checkbox-label">
                        <input type="checkbox" id="property_new" name="property_new" value="1" <?php checked($new, '1'); ?>>
                        <span class="resbs-checkbox-text"><?php esc_html_e('New Property', 'realestate-booking-suite'); ?></span>
                    </label>
                </div>

                <div class="resbs-form-group">
                    <label class="resbs-checkbox-label">
                        <input type="checkbox" id="property_sold" name="property_sold" value="1" <?php checked($sold, '1'); ?>>
                        <span class="resbs-checkbox-text"><?php esc_html_e('Sold Property', 'realestate-booking-suite'); ?></span>
                    </label>
                </div>

                <div class="resbs-form-group">
                    <label class="resbs-checkbox-label">
                        <input type="checkbox" id="property_foreclosure" name="property_foreclosure" value="1" <?php checked($foreclosure, '1'); ?>>
                        <span class="resbs-checkbox-text"><?php esc_html_e('Foreclosure', 'realestate-booking-suite'); ?></span>
                    </label>
                </div>

                <div class="resbs-form-group">
                    <label class="resbs-checkbox-label">
                        <input type="checkbox" id="property_open_house" name="property_open_house" value="1" <?php checked($open_house, '1'); ?>>
                        <span class="resbs-checkbox-text"><?php esc_html_e('Open House', 'realestate-booking-suite'); ?></span>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Property Booking Metabox
     */
    public function property_booking_metabox($post) {
        $booking_enabled = get_post_meta($post->ID, '_property_booking_enabled', true);
        $min_stay = get_post_meta($post->ID, '_property_min_stay', true);
        $max_stay = get_post_meta($post->ID, '_property_max_stay', true);
        $check_in_time = get_post_meta($post->ID, '_property_check_in_time', true);
        $check_out_time = get_post_meta($post->ID, '_property_check_out_time', true);
        $cancellation_policy = get_post_meta($post->ID, '_property_cancellation_policy', true);
        
        ?>
        <div class="resbs-metabox-container">
            <div class="resbs-form-section">
                <h3><?php esc_html_e('Booking Settings', 'realestate-booking-suite'); ?></h3>
                
                <div class="resbs-form-group">
                    <label class="resbs-checkbox-label">
                        <input type="checkbox" id="property_booking_enabled" name="property_booking_enabled" value="1" <?php checked($booking_enabled, '1'); ?>>
                        <span class="resbs-checkbox-text"><?php esc_html_e('Enable Booking', 'realestate-booking-suite'); ?></span>
                    </label>
                </div>

                <div class="resbs-form-row">
                    <div class="resbs-form-group">
                        <label for="property_min_stay"><?php esc_html_e('Minimum Stay (Days)', 'realestate-booking-suite'); ?></label>
                        <input type="number" id="property_min_stay" name="property_min_stay" value="<?php echo esc_attr($min_stay); ?>" class="resbs-input" min="1" placeholder="1">
                    </div>
                    <div class="resbs-form-group">
                        <label for="property_max_stay"><?php esc_html_e('Maximum Stay (Days)', 'realestate-booking-suite'); ?></label>
                        <input type="number" id="property_max_stay" name="property_max_stay" value="<?php echo esc_attr($max_stay); ?>" class="resbs-input" min="1" placeholder="30">
                    </div>
                </div>

                <div class="resbs-form-row">
                    <div class="resbs-form-group">
                        <label for="property_check_in_time"><?php esc_html_e('Check-in Time', 'realestate-booking-suite'); ?></label>
                        <input type="time" id="property_check_in_time" name="property_check_in_time" value="<?php echo esc_attr($check_in_time); ?>" class="resbs-input">
                    </div>
                    <div class="resbs-form-group">
                        <label for="property_check_out_time"><?php esc_html_e('Check-out Time', 'realestate-booking-suite'); ?></label>
                        <input type="time" id="property_check_out_time" name="property_check_out_time" value="<?php echo esc_attr($check_out_time); ?>" class="resbs-input">
                    </div>
                </div>

                <div class="resbs-form-group">
                    <label for="property_cancellation_policy"><?php esc_html_e('Cancellation Policy', 'realestate-booking-suite'); ?></label>
                    <select id="property_cancellation_policy" name="property_cancellation_policy" class="resbs-select">
                        <option value=""><?php esc_html_e('Select Policy', 'realestate-booking-suite'); ?></option>
                        <option value="flexible" <?php selected($cancellation_policy, 'flexible'); ?>><?php esc_html_e('Flexible', 'realestate-booking-suite'); ?></option>
                        <option value="moderate" <?php selected($cancellation_policy, 'moderate'); ?>><?php esc_html_e('Moderate', 'realestate-booking-suite'); ?></option>
                        <option value="strict" <?php selected($cancellation_policy, 'strict'); ?>><?php esc_html_e('Strict', 'realestate-booking-suite'); ?></option>
                        <option value="super-strict" <?php selected($cancellation_policy, 'super-strict'); ?>><?php esc_html_e('Super Strict', 'realestate-booking-suite'); ?></option>
                    </select>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Save property metabox data
     */
    public function save_property_metabox($post_id) {
        // Check if our nonce is set and verify it
        if (!isset($_POST['resbs_property_metabox_nonce']) || !wp_verify_nonce($_POST['resbs_property_metabox_nonce'], 'resbs_property_metabox_nonce')) {
            return;
        }

        // Check if user has permissions to save data
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Check if not an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check if our post type
        if (get_post_type($post_id) !== 'property') {
            return;
        }

        // Save all property meta fields
        $fields = array(
            '_property_price', '_property_price_per_sqft', '_property_price_note', '_property_call_for_price',
            '_property_bedrooms', '_property_bathrooms', '_property_half_baths', '_property_total_rooms',
            '_property_floors', '_property_floor_level', '_property_area_sqft', '_property_lot_size_sqft',
            '_property_year_built', '_property_year_remodeled', '_property_type', '_property_status', '_property_condition',
            '_property_address', '_property_city', '_property_state', '_property_zip', '_property_country',
            '_property_latitude', '_property_longitude', '_property_hide_address',
            '_property_features', '_property_amenities', '_property_parking', '_property_heating', '_property_cooling',
            '_property_basement', '_property_roof', '_property_exterior_material', '_property_floor_covering',
            '_property_featured', '_property_new', '_property_sold', '_property_foreclosure', '_property_open_house',
            '_property_enable_booking', '_property_min_stay', '_property_max_stay', '_property_checkin_time',
            '_property_checkout_time', '_property_cancellation_policy', '_property_virtual_tour',
            '_property_video_url', '_property_video_embed'
        );

        foreach ($fields as $field) {
            if (isset($_POST[str_replace('_property_', 'property_', $field)])) {
                $value = sanitize_text_field($_POST[str_replace('_property_', 'property_', $field)]);
                update_post_meta($post_id, $field, $value);
            } else {
                delete_post_meta($post_id, $field);
            }
        }

        // Handle media uploads
        if (isset($_POST['property_gallery'])) {
            $gallery = array_map('intval', $_POST['property_gallery']);
            update_post_meta($post_id, '_property_gallery', $gallery);
        }

        if (isset($_POST['property_floor_plans'])) {
            $floor_plans = array_map('intval', $_POST['property_floor_plans']);
            update_post_meta($post_id, '_property_floor_plans', $floor_plans);
        }
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        global $post_type;
        
        if ($post_type === 'property' && ($hook === 'post.php' || $hook === 'post-new.php')) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_media();
            
            wp_enqueue_style(
                'resbs-property-metabox',
                RESBS_URL . 'assets/css/property-metabox.css',
                array(),
                '1.0.0'
            );
            
            wp_enqueue_style(
                'resbs-stunning-metabox',
                RESBS_URL . 'assets/css/stunning-metabox.css',
                array('resbs-property-metabox'),
                '1.0.0'
            );
            
            wp_enqueue_script(
                'resbs-property-metabox',
                RESBS_URL . 'assets/js/property-metabox.js',
                array('jquery', 'wp-color-picker', 'media-upload'),
                '1.0.0',
                true
            );
            
            wp_localize_script('resbs-property-metabox', 'resbs_metabox', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('resbs_metabox_nonce'),
                'map_api_key' => get_option('resbs_map_api_key'),
                'strings' => array(
                    'upload_error' => esc_html__('Upload failed. Please try again.', 'realestate-booking-suite'),
                    'delete_confirm' => esc_html__('Are you sure you want to delete this image?', 'realestate-booking-suite'),
                    'geocoding_error' => esc_html__('Could not find coordinates for this address.', 'realestate-booking-suite')
                )
            ));
            
            // Add inline script for tab functionality
            wp_add_inline_script('resbs-property-metabox', '
                jQuery(document).ready(function($) {
                    // Handle stunning metabox tabs
                    $(".resbs-tab-nav-btn").on("click", function() {
                        var $btn = $(this);
                        var tabId = $btn.data("tab");
                        
                        // Update active tab button
                        $(".resbs-tab-nav-btn").removeClass("active");
                        $btn.addClass("active");
                        
                        // Update active tab panel
                        $(".resbs-tab-content").removeClass("active");
                        $("#" + tabId).addClass("active");
                        
                        // Trigger resize for maps if present
                        if (tabId === "location" && window.google && window.google.maps) {
                            setTimeout(function() {
                                google.maps.event.trigger(window.resbsMap, "resize");
                            }, 100);
                        }
                    });
                    
                    // Handle number controls
                    $(".resbs-number-btn").on("click", function() {
                        var $btn = $(this);
                        var $input = $("#" + $btn.data("target"));
                        var action = $btn.data("action");
                        var currentValue = parseInt($input.val()) || 0;
                        var min = parseInt($input.attr("min")) || 0;
                        var max = parseInt($input.attr("max")) || 999;
                        
                        if (action === "increase") {
                            if (currentValue < max) {
                                $input.val(currentValue + 1).trigger("change");
                            }
                        } else if (action === "decrease") {
                            if (currentValue > min) {
                                $input.val(currentValue - 1).trigger("change");
                            }
                        }
                    });
                });
            ');
        }
    }

    /**
     * Handle media upload
     */
    public function handle_media_upload() {
        check_ajax_referer('resbs_metabox_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_die(esc_html__('You do not have permission to upload files.', 'realestate-booking-suite'));
        }
        
        $uploaded_files = array();
        
        if (!empty($_FILES['files'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            foreach ($_FILES['files']['name'] as $key => $value) {
                if ($_FILES['files']['name'][$key]) {
                    $file = array(
                        'name' => $_FILES['files']['name'][$key],
                        'type' => $_FILES['files']['type'][$key],
                        'tmp_name' => $_FILES['files']['tmp_name'][$key],
                        'error' => $_FILES['files']['error'][$key],
                        'size' => $_FILES['files']['size'][$key]
                    );
                    
                    $attachment_id = media_handle_sideload($file, 0);
                    
                    if (!is_wp_error($attachment_id)) {
                        $uploaded_files[] = $attachment_id;
                    }
                }
            }
        }
        
        wp_send_json_success($uploaded_files);
    }

    /**
     * Handle media delete
     */
    public function handle_media_delete() {
        check_ajax_referer('resbs_metabox_nonce', 'nonce');
        
        if (!current_user_can('delete_posts')) {
            wp_die(esc_html__('You do not have permission to delete files.', 'realestate-booking-suite'));
        }
        
        $attachment_id = intval($_POST['attachment_id']);
        
        if (wp_delete_attachment($attachment_id, true)) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }
}
