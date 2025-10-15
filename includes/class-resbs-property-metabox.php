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
        add_action('save_post', array($this, 'save_property_metabox'), 10, 1);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_resbs_upload_property_media', array($this, 'handle_media_upload'));
        add_action('wp_ajax_resbs_delete_property_media', array($this, 'handle_media_delete'));
        add_action('wp_ajax_resbs_get_gallery', array($this, 'get_gallery_data'));
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
        $map_iframe = get_post_meta($post->ID, '_property_map_iframe', true);
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
        
        // Nearby features
        $nearby_schools = get_post_meta($post->ID, '_property_nearby_schools', true);
        $nearby_shopping = get_post_meta($post->ID, '_property_nearby_shopping', true);
        $nearby_restaurants = get_post_meta($post->ID, '_property_nearby_restaurants', true);
        
        $gallery_images = get_post_meta($post->ID, '_property_gallery', true);
        $floor_plans = get_post_meta($post->ID, '_property_floor_plans', true);
        $virtual_tour = get_post_meta($post->ID, '_property_virtual_tour', true);
        $virtual_tour_title = get_post_meta($post->ID, '_property_virtual_tour_title', true);
        $virtual_tour_description = get_post_meta($post->ID, '_property_virtual_tour_description', true);
        $virtual_tour_button_text = get_post_meta($post->ID, '_property_virtual_tour_button_text', true);
        $video_url = get_post_meta($post->ID, '_property_video_url', true);
        $video_embed = get_post_meta($post->ID, '_property_video_embed', true);
        
        // Agent data
        $agent_name = get_post_meta($post->ID, '_property_agent_name', true);
        $agent_phone = get_post_meta($post->ID, '_property_agent_phone', true);
        $agent_email = get_post_meta($post->ID, '_property_agent_email', true);
        $agent_photo = get_post_meta($post->ID, '_property_agent_photo', true);
        $agent_properties_sold = get_post_meta($post->ID, '_property_agent_properties_sold', true);
        $agent_experience = get_post_meta($post->ID, '_property_agent_experience', true);
        $agent_response_time = get_post_meta($post->ID, '_property_agent_response_time', true);
        $agent_rating = get_post_meta($post->ID, '_property_agent_rating', true);
        $agent_reviews = get_post_meta($post->ID, '_property_agent_reviews', true);
        $agent_send_message_text = get_post_meta($post->ID, '_property_agent_send_message_text', true);
        
        // Contact Form Dynamic Fields
        $contact_form_title = get_post_meta($post->ID, '_property_contact_form_title', true);
        $contact_name_label = get_post_meta($post->ID, '_property_contact_name_label', true);
        $contact_email_label = get_post_meta($post->ID, '_property_contact_email_label', true);
        $contact_phone_label = get_post_meta($post->ID, '_property_contact_phone_label', true);
        $contact_message_label = get_post_meta($post->ID, '_property_contact_message_label', true);
        $contact_success_message = get_post_meta($post->ID, '_property_contact_success_message', true);
        $contact_submit_text = get_post_meta($post->ID, '_property_contact_submit_text', true);
        
        // Mortgage Calculator Dynamic Fields
        $mortgage_calculator_title = get_post_meta($post->ID, '_property_mortgage_calculator_title', true);
        $mortgage_property_price_label = get_post_meta($post->ID, '_property_mortgage_property_price_label', true);
        $mortgage_down_payment_label = get_post_meta($post->ID, '_property_mortgage_down_payment_label', true);
        $mortgage_interest_rate_label = get_post_meta($post->ID, '_property_mortgage_interest_rate_label', true);
        $mortgage_loan_term_label = get_post_meta($post->ID, '_property_mortgage_loan_term_label', true);
        $mortgage_monthly_payment_label = get_post_meta($post->ID, '_property_mortgage_monthly_payment_label', true);
        $mortgage_default_down_payment = get_post_meta($post->ID, '_property_mortgage_default_down_payment', true);
        $mortgage_default_interest_rate = get_post_meta($post->ID, '_property_mortgage_default_interest_rate', true);
        $mortgage_default_loan_term = get_post_meta($post->ID, '_property_mortgage_default_loan_term', true);
        $mortgage_loan_terms = get_post_meta($post->ID, '_property_mortgage_loan_terms', true);
        $mortgage_disclaimer_text = get_post_meta($post->ID, '_property_mortgage_disclaimer_text', true);
        
        // Tour Information Fields
        $tour_duration = get_post_meta($post->ID, '_property_tour_duration', true);
        $tour_group_size = get_post_meta($post->ID, '_property_tour_group_size', true);
        $tour_safety = get_post_meta($post->ID, '_property_tour_safety', true);
        
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
            <script>
            // Tab switching is now handled by admin-tabs.js
            // This function is kept for backward compatibility with onclick handlers
            function switchTab(tabId) {
                console.log("Inline switchTab called for:", tabId);
                
                // Try to use the main tab handler first
                if (window.switchToTab) {
                    var result = window.switchToTab(tabId);
                    if (result) {
                        console.log("Tab switched successfully via main handler");
                        return;
                    }
                }
                
                // Fallback: Direct tab switching
                console.log("Using fallback tab switching for:", tabId);
                
                // Remove active from all buttons
                document.querySelectorAll('.resbs-tab-nav-btn').forEach(function(btn) {
                    btn.classList.remove('active');
                });
                
                // Hide all tab content
                document.querySelectorAll('.resbs-tab-content').forEach(function(content) {
                    content.classList.remove('active');
                    content.style.display = 'none';
                });
                
                // Show selected tab
                var targetTab = document.getElementById(tabId);
                var targetBtn = document.querySelector('[data-tab="' + tabId + '"]');
                
                if (targetTab && targetBtn) {
                    targetTab.classList.add('active');
                    targetTab.style.display = 'block';
                    targetBtn.classList.add('active');
                    console.log("Fallback tab switched to:", tabId);
                    
                    // Debug content
                    var content = targetTab.innerHTML.trim();
                    console.log("Tab content length:", content.length);
                    if (content.length < 100) {
                        console.warn("Tab appears to have minimal content:", tabId);
                    }
                } else {
                    console.error("Fallback: Tab not found:", tabId);
                    console.error("Available tab IDs:", Array.from(document.querySelectorAll('.resbs-tab-content')).map(function(el) { return el.id; }));
                }
            }
            </script>
            <style>
            .resbs-tab-content {
                display: none !important;
            }
            .resbs-tab-content.active {
                display: block !important;
            }
            </style>
            <div class="resbs-stunning-tabs">
                <nav class="resbs-tab-navigation">
                    <button type="button" class="resbs-tab-nav-btn active" data-tab="overview" onclick="switchTab('overview')">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Overview', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="pricing" onclick="switchTab('pricing')">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <line x1="12" y1="1" x2="12" y2="23" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M17 5H9.5C8.57174 5 7.6815 5.36875 7.02513 6.02513C6.36875 6.6815 6 7.57174 6 8.5C6 9.42826 6.36875 10.3185 7.02513 10.9749C7.6815 11.6312 8.57174 12 9.5 12H14.5C15.4283 12 16.3185 12.3687 16.9749 13.0251C17.6312 13.6815 18 14.5717 18 15.5C18 16.4283 17.6312 17.3185 16.9749 17.9749C16.3185 18.6312 15.4283 19 14.5 19H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Pricing', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="specifications" onclick="switchTab('specifications')">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                <line x1="9" y1="9" x2="15" y2="9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <line x1="9" y1="15" x2="15" y2="15" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Specifications', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="location" onclick="switchTab('location')">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M21 10C21 17 12 23 12 23S3 17 3 10C3 7.61305 3.94821 5.32387 5.63604 3.63604C7.32387 1.94821 9.61305 1 12 1C14.3869 1 16.6761 1.94821 18.3639 3.63604C20.0518 5.32387 21 7.61305 21 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Location', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="features" onclick="switchTab('features')">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 12L11 14L15 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Features', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="media" onclick="switchTab('media')">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5" stroke="currentColor" stroke-width="2"/>
                                <polyline points="21,15 16,10 5,21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Media', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="agent" onclick="switchTab('agent')">
                        <span class="resbs-tab-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        <span class="resbs-tab-text"><?php esc_html_e('Agent', 'realestate-booking-suite'); ?></span>
                    </button>
                    <button type="button" class="resbs-tab-nav-btn" data-tab="booking" onclick="switchTab('booking')">
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
                                            <input type="number" id="property_price" name="property_price" value="<?php echo esc_attr($price); ?>" class="resbs-stunning-input" placeholder="0" step="0.01">
                                        </div>
                                        <p class="resbs-input-help"><?php esc_html_e('Enter the property price', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_price_per_sqft"><?php esc_html_e('Price per Sq Ft', 'realestate-booking-suite'); ?></label>
                                        <div class="resbs-input-group">
                                            <span class="resbs-input-prefix">$</span>
                                            <input type="number" id="property_price_per_sqft" name="property_price_per_sqft" value="<?php echo esc_attr($price_per_sqft); ?>" class="resbs-stunning-input" placeholder="0" step="0.01">
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
                                    <input type="text" id="property_country" name="property_country" value="<?php echo esc_attr($country); ?>" class="resbs-stunning-input" placeholder="<?php esc_attr_e('Enter country name', 'realestate-booking-suite'); ?>">
                                </div>


                                <div class="resbs-form-group">
                                    <label for="property_map_iframe"><?php esc_html_e('Custom Map Iframe', 'realestate-booking-suite'); ?></label>
                                    <textarea id="property_map_iframe" name="property_map_iframe" class="resbs-stunning-input" rows="4" placeholder="<iframe src=&quot;https://www.google.com/maps/embed?pb=...&quot; width=&quot;100%&quot; height=&quot;400&quot; style=&quot;border:0;&quot; allowfullscreen=&quot;&quot; loading=&quot;lazy&quot; referrerpolicy=&quot;no-referrer-when-downgrade&quot;></iframe>"><?php echo esc_textarea($map_iframe); ?></textarea>
                                    <p class="resbs-help-text"><?php esc_html_e('Paste your Google Maps embed iframe code here. This will display a custom map on the property page.', 'realestate-booking-suite'); ?></p>
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

                                <!-- Nearby Features Section -->
                                <div class="resbs-nearby-features">
                                    <h4><?php esc_html_e('Nearby Features', 'realestate-booking-suite'); ?></h4>
                                  
                                    
                                    <div class="resbs-form-row">
                                        <div class="resbs-form-group">
                                            <label for="property_nearby_schools"><?php esc_html_e('Nearby Schools', 'realestate-booking-suite'); ?></label>
                                            <textarea id="property_nearby_schools" name="property_nearby_schools" class="resbs-stunning-input" rows="3" placeholder="<?php esc_attr_e('e.g., Dhaka University, North South University, American International School'); ?>"><?php echo esc_textarea($nearby_schools); ?></textarea>
                                        </div>
                                        
                                        <div class="resbs-form-group">
                                            <label for="property_nearby_shopping"><?php esc_html_e('Nearby Shopping', 'realestate-booking-suite'); ?></label>
                                            <textarea id="property_nearby_shopping" name="property_nearby_shopping" class="resbs-stunning-input" rows="3" placeholder="<?php esc_attr_e('e.g., Bashundhara City, Jamuna Future Park, New Market'); ?>"><?php echo esc_textarea($nearby_shopping); ?></textarea>
                                        </div>
                                        
                                        <div class="resbs-form-group">
                                            <label for="property_nearby_restaurants"><?php esc_html_e('Nearby Restaurants', 'realestate-booking-suite'); ?></label>
                                            <textarea id="property_nearby_restaurants" name="property_nearby_restaurants" class="resbs-stunning-input" rows="3" placeholder="<?php esc_attr_e('e.g., Pizza Hut, KFC, Local Bangladeshi restaurants'); ?>"><?php echo esc_textarea($nearby_restaurants); ?></textarea>
                                        </div>
                                    </div>
                                </div>
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
                                    
                                    <!-- Feature Suggestions -->
                                    <div class="resbs-feature-suggestions">
                                        <h4><?php esc_html_e('Common Features', 'realestate-booking-suite'); ?></h4>
                                        <div class="resbs-suggestion-tags">
                                            <?php
                                            $suggested_features = array(
                                                'Balcony',
                                                'Fireplace', 
                                                'Hardwood Floors',
                                                'Granite Countertops',
                                                'Stainless Steel Appliances',
                                                'Walk-in Closet',
                                                'Crown Molding',
                                                'High Ceilings',
                                                'Bay Windows',
                                                'Skylights',
                                                'Built-in Shelves',
                                                'Marble Bathroom'
                                            );
                                            
                                            foreach ($suggested_features as $feature) {
                                                echo '<span class="resbs-suggestion-tag" data-feature="' . esc_attr($feature) . '">' . esc_html($feature) . '</span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Current Features Display -->
                                    <div class="resbs-current-features">
                                        <h4><?php esc_html_e('Current Features', 'realestate-booking-suite'); ?></h4>
                                        <div class="resbs-feature-tags" id="feature-tags-container">
                                            <!-- Features will be populated here by JavaScript -->
                                        </div>
                                    </div>
                                    
                                    <!-- Manual Input -->
                                    <div class="resbs-manual-feature-input">
                                        <input type="text" id="property_features_input" class="resbs-stunning-input" placeholder="<?php esc_attr_e('Type a custom feature and press Enter', 'realestate-booking-suite'); ?>">
                                        <button type="button" id="add-custom-feature" class="resbs-btn-secondary"><?php esc_html_e('Add Feature', 'realestate-booking-suite'); ?></button>
                                    </div>
                                    
                                    <!-- Hidden field for form submission -->
                                    <input type="hidden" id="property_features" name="property_features" value="<?php echo esc_attr($features); ?>">
                                    
                                    <p class="resbs-input-help"><?php esc_html_e('Click on suggested features to add them, or type custom features manually', 'realestate-booking-suite'); ?></p>
                                </div>

                                <div class="resbs-form-group">
                                    <label for="property_amenities"><?php esc_html_e('Amenities', 'realestate-booking-suite'); ?></label>
                                    
                                    <!-- Amenity Suggestions -->
                                    <div class="resbs-feature-suggestions">
                                        <h4><?php esc_html_e('Common Amenities', 'realestate-booking-suite'); ?></h4>
                                        <div class="resbs-suggestion-tags">
                                            <?php
                                            $suggested_amenities = array(
                                                'Pool',
                                                'Gym',
                                                'Security',
                                                'Parking',
                                                'Elevator',
                                                'Concierge',
                                                'Rooftop',
                                                'Garden',
                                                'Balcony',
                                                'Terrace',
                                                'Storage',
                                                'Laundry'
                                            );
                                            
                                            foreach ($suggested_amenities as $amenity) {
                                                echo '<span class="resbs-suggestion-tag" data-amenity="' . esc_attr($amenity) . '">' . esc_html($amenity) . '</span>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Current Amenities Display -->
                                    <div class="resbs-current-features">
                                        <h4><?php esc_html_e('Current Amenities', 'realestate-booking-suite'); ?></h4>
                                        <div class="resbs-feature-tags" id="amenity-tags-container">
                                            <!-- Amenities will be populated here by JavaScript -->
                                        </div>
                                    </div>
                                    
                                    <!-- Manual Input -->
                                    <div class="resbs-manual-feature-input">
                                        <input type="text" id="property_amenities_input" class="resbs-stunning-input" placeholder="<?php esc_attr_e('Type a custom amenity and press Enter', 'realestate-booking-suite'); ?>">
                                        <button type="button" id="add-custom-amenity" class="resbs-btn-secondary"><?php esc_html_e('Add Amenity', 'realestate-booking-suite'); ?></button>
                                    </div>
                                    
                                    <!-- Hidden field for form submission -->
                                    <input type="hidden" id="property_amenities" name="property_amenities" value="<?php echo esc_attr($amenities); ?>">
                                    
                                    <p class="resbs-input-help"><?php esc_html_e('Click on suggested amenities to add them, or type custom amenities manually', 'realestate-booking-suite'); ?></p>
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
                                        <div>
                                            <h3 style="color: #0073aa; margin: 0 0 20px 0; font-size: 18px;">📸 Upload Property Photos</h3>
                                            
                                            <!-- WordPress Media Uploader Button -->
                                            <div style="margin: 20px 0;">
                                                <button type="button" id="upload-gallery-button" class="button button-primary" style="font-size: 16px;">
                                                    📁 Select Images
                                                </button>
                                                <p style="margin: 10px 0; color: #666;">Click to open WordPress Media Library</p>
                                                </div>
                                            
                                            <!-- Hidden input for selected images -->
                                            <input type="hidden" id="gallery-images" name="gallery_images" value="">
                                            
                                            <!-- Display selected images -->
                                            <div id="gallery-preview" style="margin-top: 20px;">
                                                <h4>Selected Images:</h4>
                                                <div id="gallery-list" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                                                    <!-- Images will be displayed here -->
                                            </div>
                                            </div>
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
                                    
                                    <!-- Virtual Tour Customization -->
                                    <div class="resbs-form-group">
                                        <label for="property_virtual_tour_title"><?php esc_html_e('Virtual Tour Title', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_virtual_tour_title" name="property_virtual_tour_title" value="<?php echo esc_attr($virtual_tour_title ?: '3D Virtual Walkthrough'); ?>" class="resbs-stunning-input" placeholder="3D Virtual Walkthrough">
                                        <p class="resbs-input-help"><?php esc_html_e('Customize the title displayed in the virtual tour section', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    
                                    <div class="resbs-form-group">
                                        <label for="property_virtual_tour_description"><?php esc_html_e('Virtual Tour Description', 'realestate-booking-suite'); ?></label>
                                        <textarea id="property_virtual_tour_description" name="property_virtual_tour_description" class="resbs-stunning-input" rows="3" placeholder="Experience this property from anywhere with our interactive 3D tour."><?php echo esc_textarea($virtual_tour_description ?: 'Experience this property from anywhere with our interactive 3D tour.'); ?></textarea>
                                        <p class="resbs-input-help"><?php esc_html_e('Customize the description text for the virtual tour', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    
                                    <div class="resbs-form-group">
                                        <label for="property_virtual_tour_button_text"><?php esc_html_e('Button Text', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_virtual_tour_button_text" name="property_virtual_tour_button_text" value="<?php echo esc_attr($virtual_tour_button_text ?: 'Start Tour'); ?>" class="resbs-stunning-input" placeholder="Start Tour">
                                        <p class="resbs-input-help"><?php esc_html_e('Customize the button text for the virtual tour', 'realestate-booking-suite'); ?></p>
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

                <!-- Agent Tab -->
                <div id="agent" class="resbs-tab-content">
                    <div class="resbs-content-grid">
                        <div class="resbs-content-card resbs-card-primary">
                            <div class="resbs-card-header">
                                <div class="resbs-card-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <div class="resbs-card-title">
                                    <h3><?php esc_html_e('Property Agent', 'realestate-booking-suite'); ?></h3>
                                    <p><?php esc_html_e('Agent information and contact details', 'realestate-booking-suite'); ?></p>
                                </div>
                            </div>
                            <div class="resbs-card-body">
                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_agent_name"><?php esc_html_e('Agent Name', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_agent_name" name="property_agent_name" value="<?php echo esc_attr($agent_name); ?>" class="resbs-stunning-input" placeholder="John Smith">
                                        <p class="resbs-input-help"><?php esc_html_e('Full name of the property agent', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_agent_phone"><?php esc_html_e('Phone Number', 'realestate-booking-suite'); ?></label>
                                        <input type="tel" id="property_agent_phone" name="property_agent_phone" value="<?php echo esc_attr($agent_phone); ?>" class="resbs-stunning-input" placeholder="+1 (555) 123-4567">
                                        <p class="resbs-input-help"><?php esc_html_e('Agent contact phone number', 'realestate-booking-suite'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="resbs-form-group">
                                    <label for="property_agent_email"><?php esc_html_e('Email Address', 'realestate-booking-suite'); ?></label>
                                    <input type="email" id="property_agent_email" name="property_agent_email" value="<?php echo esc_attr($agent_email); ?>" class="resbs-stunning-input" placeholder="agent@example.com">
                                    <p class="resbs-input-help"><?php esc_html_e('Agent email address for inquiries', 'realestate-booking-suite'); ?></p>
                                </div>
                                
                                <div class="resbs-form-group">
                                    <label for="property_agent_photo"><?php esc_html_e('Agent Photo', 'realestate-booking-suite'); ?></label>
                                    
                                    <!-- Compact Agent Photo Uploader -->
                                    <div class="resbs-agent-photo-uploader" style="display: flex; align-items: center; gap: 15px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9;">
                                        <!-- Photo Preview -->
                                        <div id="agent-photo-preview" style="flex-shrink: 0;">
                                            <?php if ($agent_photo): ?>
                                                <img src="<?php echo esc_url($agent_photo); ?>" alt="Agent Photo" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #0073aa;">
                                            <?php else: ?>
                                                <div style="width: 60px; height: 60px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center; color: #666; font-size: 24px;">👤</div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Upload Controls -->
                                        <div style="flex: 1;">
                                            <button type="button" id="upload-agent-photo" class="button button-primary" style="margin-right: 10px;">
                                                📷 <?php esc_html_e('Upload Photo', 'realestate-booking-suite'); ?>
                                            </button>
                                            <?php if ($agent_photo): ?>
                                                <button type="button" id="remove-agent-photo" class="button button-secondary" style="background: #dc3232; color: white; border: none;">
                                                    Remove
                                                </button>
                                            <?php endif; ?>
                                            <p style="margin: 5px 0 0 0; color: #666; font-size: 12px;">
                                                <?php esc_html_e('Select from media library', 'realestate-booking-suite'); ?>
                                            </p>
                                        </div>
                                        
                                        <!-- Hidden input for selected image -->
                                        <input type="hidden" id="property_agent_photo" name="property_agent_photo" value="<?php echo esc_attr($agent_photo); ?>">
                                    </div>
                                    
                                    <p class="resbs-input-help"><?php esc_html_e('Upload a professional photo of the agent', 'realestate-booking-suite'); ?></p>
                                </div>
                                
                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_agent_properties_sold"><?php esc_html_e('Properties Sold', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_agent_properties_sold" name="property_agent_properties_sold" value="<?php echo esc_attr($agent_properties_sold ?: '100+'); ?>" class="resbs-stunning-input" placeholder="100+">
                                        <p class="resbs-input-help"><?php esc_html_e('Number of properties sold (e.g., 100+)', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_agent_experience"><?php esc_html_e('Experience', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_agent_experience" name="property_agent_experience" value="<?php echo esc_attr($agent_experience ?: '5+ Years'); ?>" class="resbs-stunning-input" placeholder="5+ Years">
                                        <p class="resbs-input-help"><?php esc_html_e('Years of experience (e.g., 5+ Years)', 'realestate-booking-suite'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_agent_response_time"><?php esc_html_e('Response Time', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_agent_response_time" name="property_agent_response_time" value="<?php echo esc_attr($agent_response_time ?: '< 1 Hour'); ?>" class="resbs-stunning-input" placeholder="< 1 Hour">
                                        <p class="resbs-input-help"><?php esc_html_e('Average response time (e.g., < 1 Hour)', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_agent_rating"><?php esc_html_e('Rating', 'realestate-booking-suite'); ?></label>
                                        <select id="property_agent_rating" name="property_agent_rating" class="resbs-stunning-select">
                                            <option value="1" <?php selected($agent_rating ?: '5', '1'); ?>>1 Star</option>
                                            <option value="2" <?php selected($agent_rating ?: '5', '2'); ?>>2 Stars</option>
                                            <option value="3" <?php selected($agent_rating ?: '5', '3'); ?>>3 Stars</option>
                                            <option value="4" <?php selected($agent_rating ?: '5', '4'); ?>>4 Stars</option>
                                            <option value="5" <?php selected($agent_rating ?: '5', '5'); ?>>5 Stars</option>
                                        </select>
                                        <p class="resbs-input-help"><?php esc_html_e('Agent rating out of 5 stars', 'realestate-booking-suite'); ?></p>
                                    </div>
                                </div>
                                
                                <div class="resbs-form-group">
                                    <label for="property_agent_reviews"><?php esc_html_e('Reviews Text', 'realestate-booking-suite'); ?></label>
                                    <input type="text" id="property_agent_reviews" name="property_agent_reviews" value="<?php echo esc_attr($agent_reviews ?: 'reviews'); ?>" class="resbs-stunning-input" placeholder="reviews">
                                    <p class="resbs-input-help"><?php esc_html_e('Text to display after rating (e.g., reviews, testimonials)', 'realestate-booking-suite'); ?></p>
                                </div>
                                
                                <div class="resbs-form-group">
                                    <label for="property_agent_send_message_text"><?php esc_html_e('Send Message Button Text', 'realestate-booking-suite'); ?></label>
                                    <input type="text" id="property_agent_send_message_text" name="property_agent_send_message_text" value="<?php echo esc_attr($agent_send_message_text ?: 'Send Message'); ?>" class="resbs-stunning-input" placeholder="Send Message">
                                    <p class="resbs-input-help"><?php esc_html_e('Customize the send message button text', 'realestate-booking-suite'); ?></p>
                                </div>
                                
                                <!-- Contact Form Settings -->
                                <div class="resbs-form-group">
                                    <label for="property_contact_form_title"><?php esc_html_e('Contact Form Title', 'realestate-booking-suite'); ?></label>
                                    <input type="text" id="property_contact_form_title" name="property_contact_form_title" value="<?php echo esc_attr($contact_form_title ?: 'Contact Agent'); ?>" class="resbs-stunning-input" placeholder="Contact Agent">
                                    <p class="resbs-input-help"><?php esc_html_e('Title displayed at the top of the contact form', 'realestate-booking-suite'); ?></p>
                                </div>

                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_contact_name_label"><?php esc_html_e('Name Field Label', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_contact_name_label" name="property_contact_name_label" value="<?php echo esc_attr($contact_name_label ?: 'Your Name'); ?>" class="resbs-stunning-input" placeholder="Your Name">
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_contact_email_label"><?php esc_html_e('Email Field Label', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_contact_email_label" name="property_contact_email_label" value="<?php echo esc_attr($contact_email_label ?: 'Email'); ?>" class="resbs-stunning-input" placeholder="Email">
                                    </div>
                                </div>

                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_contact_phone_label"><?php esc_html_e('Phone Field Label', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_contact_phone_label" name="property_contact_phone_label" value="<?php echo esc_attr($contact_phone_label ?: 'Phone'); ?>" class="resbs-stunning-input" placeholder="Phone">
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_contact_message_label"><?php esc_html_e('Message Field Label', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_contact_message_label" name="property_contact_message_label" value="<?php echo esc_attr($contact_message_label ?: 'Message'); ?>" class="resbs-stunning-input" placeholder="Message">
                                    </div>
                                </div>

                                <div class="resbs-form-group">
                                    <label for="property_contact_success_message"><?php esc_html_e('Success Message', 'realestate-booking-suite'); ?></label>
                                    <textarea id="property_contact_success_message" name="property_contact_success_message" class="resbs-stunning-textarea" rows="3" placeholder="Thank you! Your message has been sent to the agent."><?php echo esc_textarea($contact_success_message ?: 'Thank you! Your message has been sent to the agent.'); ?></textarea>
                                    <p class="resbs-input-help"><?php esc_html_e('Message shown after successful contact form submission', 'realestate-booking-suite'); ?></p>
                                </div>

                                <div class="resbs-form-group">
                                    <label for="property_contact_submit_text"><?php esc_html_e('Submit Button Text', 'realestate-booking-suite'); ?></label>
                                    <input type="text" id="property_contact_submit_text" name="property_contact_submit_text" value="<?php echo esc_attr($contact_submit_text ?: 'Send Message'); ?>" class="resbs-stunning-input" placeholder="Send Message">
                                </div>
                                
                                <!-- Mortgage Calculator Settings -->
                                <div class="resbs-form-group">
                                    <label for="property_mortgage_calculator_title"><?php esc_html_e('Mortgage Calculator Title', 'realestate-booking-suite'); ?></label>
                                    <input type="text" id="property_mortgage_calculator_title" name="property_mortgage_calculator_title" value="<?php echo esc_attr($mortgage_calculator_title ?: 'Mortgage Calculator'); ?>" class="resbs-stunning-input" placeholder="Mortgage Calculator">
                                    <p class="resbs-input-help"><?php esc_html_e('Title displayed at the top of the mortgage calculator', 'realestate-booking-suite'); ?></p>
                                </div>

                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_mortgage_property_price_label"><?php esc_html_e('Property Price Label', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_mortgage_property_price_label" name="property_mortgage_property_price_label" value="<?php echo esc_attr($mortgage_property_price_label ?: 'Property Price'); ?>" class="resbs-stunning-input" placeholder="Property Price">
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_mortgage_down_payment_label"><?php esc_html_e('Down Payment Label', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_mortgage_down_payment_label" name="property_mortgage_down_payment_label" value="<?php echo esc_attr($mortgage_down_payment_label ?: 'Down Payment (%)'); ?>" class="resbs-stunning-input" placeholder="Down Payment (%)">
                                    </div>
                                </div>

                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_mortgage_interest_rate_label"><?php esc_html_e('Interest Rate Label', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_mortgage_interest_rate_label" name="property_mortgage_interest_rate_label" value="<?php echo esc_attr($mortgage_interest_rate_label ?: 'Interest Rate (%)'); ?>" class="resbs-stunning-input" placeholder="Interest Rate (%)">
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_mortgage_loan_term_label"><?php esc_html_e('Loan Term Label', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_mortgage_loan_term_label" name="property_mortgage_loan_term_label" value="<?php echo esc_attr($mortgage_loan_term_label ?: 'Loan Term (Years)'); ?>" class="resbs-stunning-input" placeholder="Loan Term (Years)">
                                    </div>
                                </div>

                                <div class="resbs-form-group">
                                    <label for="property_mortgage_monthly_payment_label"><?php esc_html_e('Monthly Payment Label', 'realestate-booking-suite'); ?></label>
                                    <input type="text" id="property_mortgage_monthly_payment_label" name="property_mortgage_monthly_payment_label" value="<?php echo esc_attr($mortgage_monthly_payment_label ?: 'Estimated Monthly Payment'); ?>" class="resbs-stunning-input" placeholder="Estimated Monthly Payment">
                                </div>

                                <div class="resbs-form-row">
                                    <div class="resbs-form-group">
                                        <label for="property_mortgage_default_down_payment"><?php esc_html_e('Default Down Payment (%)', 'realestate-booking-suite'); ?></label>
                                        <input type="number" id="property_mortgage_default_down_payment" name="property_mortgage_default_down_payment" value="<?php echo esc_attr($mortgage_default_down_payment ?: '20'); ?>" class="resbs-stunning-input" placeholder="20" min="0" max="100">
                                    </div>
                                    <div class="resbs-form-group">
                                        <label for="property_mortgage_default_interest_rate"><?php esc_html_e('Default Interest Rate (%)', 'realestate-booking-suite'); ?></label>
                                        <input type="number" id="property_mortgage_default_interest_rate" name="property_mortgage_default_interest_rate" value="<?php echo esc_attr($mortgage_default_interest_rate ?: '6.5'); ?>" class="resbs-stunning-input" placeholder="6.5" step="0.1" min="0" max="50">
                                    </div>
                                </div>

                                <div class="resbs-form-group">
                                    <label for="property_mortgage_default_loan_term"><?php esc_html_e('Default Loan Term (Years)', 'realestate-booking-suite'); ?></label>
                                    <input type="number" id="property_mortgage_default_loan_term" name="property_mortgage_default_loan_term" value="<?php echo esc_attr($mortgage_default_loan_term ?: '30'); ?>" class="resbs-stunning-input" placeholder="30" min="1" max="50">
                                </div>

                                <div class="resbs-form-group">
                                    <label for="property_mortgage_loan_terms"><?php esc_html_e('Available Loan Terms', 'realestate-booking-suite'); ?></label>
                                    <textarea id="property_mortgage_loan_terms" name="property_mortgage_loan_terms" class="resbs-stunning-textarea" rows="3" placeholder="15&#10;20&#10;30"><?php echo esc_textarea($mortgage_loan_terms ?: "15\n20\n30"); ?></textarea>
                                    <p class="resbs-input-help"><?php esc_html_e('Enter each loan term on a new line (e.g., 15, 20, 30)', 'realestate-booking-suite'); ?></p>
                                </div>

                                <div class="resbs-form-group">
                                    <label for="property_mortgage_disclaimer_text"><?php esc_html_e('Disclaimer Text', 'realestate-booking-suite'); ?></label>
                                    <textarea id="property_mortgage_disclaimer_text" name="property_mortgage_disclaimer_text" class="resbs-stunning-textarea" rows="2" placeholder="*Principal & Interest only"><?php echo esc_textarea($mortgage_disclaimer_text ?: '*Principal & Interest only'); ?></textarea>
                                    <p class="resbs-input-help"><?php esc_html_e('Disclaimer text shown below the monthly payment', 'realestate-booking-suite'); ?></p>
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

                                <!-- Tour Information Section -->
                                <div class="resbs-form-section">
                                    <h4><?php esc_html_e('Tour Information', 'realestate-booking-suite'); ?></h4>
                                    <p class="resbs-input-help"><?php esc_html_e('Customize the tour information displayed to visitors', 'realestate-booking-suite'); ?></p>
                                    
                                    <div class="resbs-form-group">
                                        <label for="property_tour_duration"><?php esc_html_e('Tour Duration', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_tour_duration" name="property_tour_duration" value="<?php echo esc_attr($tour_duration ?: 'Approximately 30-45 minutes'); ?>" class="resbs-stunning-input" placeholder="Approximately 30-45 minutes">
                                        <p class="resbs-input-help"><?php esc_html_e('Duration of the property tour (e.g., 30-45 minutes)', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    
                                    <div class="resbs-form-group">
                                        <label for="property_tour_group_size"><?php esc_html_e('Group Size', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_tour_group_size" name="property_tour_group_size" value="<?php echo esc_attr($tour_group_size ?: 'Maximum 4 people per tour'); ?>" class="resbs-stunning-input" placeholder="Maximum 4 people per tour">
                                        <p class="resbs-input-help"><?php esc_html_e('Maximum number of people allowed per tour', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    
                                    <div class="resbs-form-group">
                                        <label for="property_tour_safety"><?php esc_html_e('Safety Information', 'realestate-booking-suite'); ?></label>
                                        <input type="text" id="property_tour_safety" name="property_tour_safety" value="<?php echo esc_attr($tour_safety ?: 'All safety protocols followed'); ?>" class="resbs-stunning-input" placeholder="All safety protocols followed">
                                        <p class="resbs-input-help"><?php esc_html_e('Safety information for the tour', 'realestate-booking-suite'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        // WordPress Media Uploader - Simple and Reliable
        jQuery(document).ready(function($) {
            console.log('📸 WordPress Media Uploader Starting...');
            
            var galleryImages = [];
            
            // Agent Photo Uploader
            $('#upload-agent-photo').click(function(e) {
                e.preventDefault();
                console.log('👤 Opening WordPress Media Library for Agent Photo...');
                
                var agentPhotoUploader = wp.media({
                    title: 'Select Agent Photo',
                    button: {
                        text: 'Use as Agent Photo'
                    },
                    multiple: false,
                    library: {
                        type: 'image'
                    }
                });
                
                agentPhotoUploader.on('select', function() {
                    var attachment = agentPhotoUploader.state().get('selection').first().toJSON();
                    console.log('👤 Agent photo selected:', attachment.url);
                    
                    // Update the hidden input
                    $('#property_agent_photo').val(attachment.url);
                    
                    // Update the preview
                    $('#agent-photo-preview').html(
                        '<img src="' + attachment.url + '" alt="Agent Photo" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 2px solid #0073aa;">'
                    );
                    
                    // Show remove button
                    if ($('#remove-agent-photo').length === 0) {
                        $('#upload-agent-photo').after('<button type="button" id="remove-agent-photo" class="button button-secondary" style="background: #dc3232; color: white; border: none;">Remove</button>');
                    }
                });
                
                agentPhotoUploader.open();
            });
            
            // Remove Agent Photo
            $(document).on('click', '#remove-agent-photo', function(e) {
                e.preventDefault();
                console.log('🗑️ Removing agent photo...');
                
                // Clear the hidden input
                $('#property_agent_photo').val('');
                
                // Reset the preview
                $('#agent-photo-preview').html(
                    '<div style="width: 60px; height: 60px; border-radius: 50%; background: #ddd; display: flex; align-items: center; justify-content: center; color: #666; font-size: 24px;">👤</div>'
                );
                
                // Hide remove button
                $('#remove-agent-photo').remove();
            });
            
            // WordPress Media Uploader
            $('#upload-gallery-button').click(function(e) {
                e.preventDefault();
                console.log('📁 Opening WordPress Media Library...');
                
                var mediaUploader = wp.media({
                    title: 'Select Property Images',
                    button: {
                        text: 'Add to Gallery'
                    },
                    multiple: true,
                    library: {
                        type: 'image'
                    }
                });
                
                mediaUploader.on('select', function() {
                    var selection = mediaUploader.state().get('selection');
                    console.log('📸 Selected images:', selection.length);
                    
                    selection.map(function(attachment) {
                        var attachmentData = attachment.toJSON();
                        galleryImages.push(attachmentData.id);
                        console.log('➕ Added image:', attachmentData.id, attachmentData.filename);
                    });
                    
                    // Update hidden input
                    $('#gallery-images').val(galleryImages.join(','));
                    
                    // Display selected images
                    displayGalleryImages();
                    
                    // Silent success - no alert to prevent interruption
                    console.log('✅ Success! Added ' + selection.length + ' image(s) to gallery. Click "Update" to save.');
                });
                
                mediaUploader.open();
            });
            
            // Display selected images
            function displayGalleryImages() {
                var galleryList = $('#gallery-list');
                galleryList.empty();
                
                if (galleryImages.length === 0) {
                    galleryList.html('<p style="color: #666; font-style: italic;">No images selected</p>');
                    return;
                }
                
                galleryImages.forEach(function(imageId) {
                    var imageUrl = wp.media.attachment(imageId).get('url');
                    if (imageUrl) {
                        var imageHtml = '<div style="position: relative; display: inline-block; margin: 5px;">' +
                            '<img src="' + imageUrl + '" style="width: 100px; height: 100px; object-fit: cover; border-radius: 5px; border: 2px solid #0073aa;">' +
                            '<button type="button" class="remove-image" data-id="' + imageId + '" style="position: absolute; top: -5px; right: -5px; background: #ff0000; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer;">×</button>' +
                            '</div>';
                        galleryList.append(imageHtml);
                    }
                });
            }
            
            // Remove image
            $(document).on('click', '.remove-image', function() {
                var imageId = $(this).data('id');
                galleryImages = galleryImages.filter(function(id) {
                    return id != imageId;
                });
                $('#gallery-images').val(galleryImages.join(','));
                displayGalleryImages();
                console.log('➖ Removed image:', imageId);
            });
            
            console.log('📸 WordPress Media Uploader Ready!');
        });
        
        // Disabled notification system to prevent alert issues
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== UPLOAD SYSTEM INITIALIZED (Notifications Disabled) ===');
            
            // Notification system disabled to prevent alert conflicts
            
            // Function to refresh galleries
            function refreshGalleries() {
                console.log('Refreshing galleries...');
                
                // Get current post ID from URL
                var postId = window.location.search.match(/post=(\d+)/);
                if (!postId) return;
                postId = postId[1];
                
                // Make AJAX call to get updated gallery
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=resbs_get_gallery&post_id=' + postId + '&nonce=<?php echo wp_create_nonce('resbs_metabox_nonce'); ?>'
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Gallery data received:', data);
                    if (data.success) {
                        // Update gallery grid
                        var galleryGrid = document.getElementById('gallery-grid');
                        if (galleryGrid && data.data.gallery) {
                            console.log('Updating gallery with HTML:', data.data.gallery);
                            galleryGrid.innerHTML = data.data.gallery;
                            
                            // Check if images are now visible
                            var galleryItems = galleryGrid.querySelectorAll('.resbs-gallery-item');
                            console.log('Gallery now has', galleryItems.length, 'items');
                        }
                        
                        // Update floor plans grid
                        var floorPlansGrid = document.getElementById('floor-plans-grid');
                        if (floorPlansGrid && data.data.floor_plans) {
                            console.log('Updating floor plans with HTML:', data.data.floor_plans);
                            floorPlansGrid.innerHTML = data.data.floor_plans;
                        }
                        
                        console.log('Galleries refreshed successfully');
                    } else {
                        console.error('Gallery refresh failed:', data);
                    }
                })
                .catch(error => {
                    console.error('Error refreshing galleries:', error);
                    // Fallback to page reload
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                });
            }
            
            // Function to show image previews
            function showImagePreviews(files, inputName) {
                console.log('Showing previews for:', files.length, 'files');
                
                // Determine which gallery to use
                var galleryId = inputName.includes('gallery') ? 'gallery-grid' : 'floor-plans-grid';
                var gallery = document.getElementById(galleryId);
                
                if (!gallery) {
                    console.error('Gallery not found:', galleryId);
                    return;
                }
                
                // Clear existing previews
                var existingPreviews = gallery.querySelectorAll('.preview-item');
                existingPreviews.forEach(function(preview) {
                    preview.remove();
                });
                
                // Add preview for each file
                Array.from(files).forEach(function(file, index) {
                    if (file.type.startsWith('image/')) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var previewItem = document.createElement('div');
                            previewItem.className = 'preview-item';
                            previewItem.style.cssText = `
                                display: inline-block;
                                margin: 10px;
                                border: 2px solid #0073aa;
                                border-radius: 8px;
                                overflow: hidden;
                                background: white;
                                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                                position: relative;
                                animation: fadeIn 0.3s ease-in;
                            `;
                            
                            previewItem.innerHTML = `
                                <img src="${e.target.result}" style="
                                    width: 150px;
                                    height: 150px;
                                    object-fit: cover;
                                    display: block;
                                ">
                                <div style="
                                    position: absolute;
                                    top: 5px;
                                    right: 5px;
                                    background: rgba(0,115,170,0.9);
                                    color: white;
                                    padding: 2px 6px;
                                    border-radius: 4px;
                                    font-size: 12px;
                                    font-weight: bold;
                                ">${index + 1}</div>
                                <div style="
                                    position: absolute;
                                    top: 5px;
                                    left: 5px;
                                    background: rgba(255,193,7,0.9);
                                    color: #000;
                                    padding: 2px 6px;
                                    border-radius: 4px;
                                    font-size: 10px;
                                    font-weight: bold;
                                ">PREVIEW</div>
                                <div style="
                                    padding: 8px;
                                    background: #f8f9fa;
                                    font-size: 12px;
                                    color: #666;
                                    text-align: center;
                                    max-width: 150px;
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                    white-space: nowrap;
                                " title="${file.name}">${file.name}</div>
                            `;
                            
                            gallery.appendChild(previewItem);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            
            // Notification system disabled to prevent alert issues
            function showNotification(message, type, duration) {
                // Silent - no notifications shown to prevent alert conflicts
                console.log('Notification disabled:', message);
            }
            
            // Add CSS animations
            var style = document.createElement('style');
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOut {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
                @keyframes fadeIn {
                    from { opacity: 0; transform: scale(0.8); }
                    to { opacity: 1; transform: scale(1); }
                }
                .preview-item {
                    transition: all 0.3s ease;
                }
                .preview-item:hover {
                    transform: scale(1.05);
                    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
                }
            `;
            document.head.appendChild(style);
            
            // Handle file inputs
            var fileInputs = document.querySelectorAll('input[type="file"]');
            console.log('Found file inputs:', fileInputs.length);
            
            fileInputs.forEach(function(input, index) {
                console.log('Setting up input ' + index + ':', input.name);
                
                input.addEventListener('change', function(e) {
                    var files = e.target.files;
                    console.log('File input changed:', e.target.name, files.length);
                    console.log('Selected files:', Array.from(files).map(f => f.name));
                    
                    if (files.length > 0) {
                        // Notification disabled to prevent alert issues
                        var fileNames = Array.from(files).map(f => f.name).join(', ');
                        var message = `Selected ${files.length} file(s): ${fileNames}`;
                        console.log('Files selected:', message);
                        
                        // Update the input area with visual feedback
                        var container = input.closest('div');
                        if (container) {
                            container.style.borderColor = '#28a745';
                            container.style.backgroundColor = '#f0fff4';
                            
                            // Add file count display
                            var existingCount = container.querySelector('.file-count');
                            if (existingCount) {
                                existingCount.remove();
                            }
                            
                            var countDiv = document.createElement('div');
                            countDiv.className = 'file-count';
                            countDiv.style.cssText = 'color: #28a745; font-weight: bold; margin-top: 10px; font-size: 14px;';
                            countDiv.textContent = `📁 ${files.length} file(s) selected`;
                            container.appendChild(countDiv);
                        }
                        
                        // Show preview images immediately
                        showImagePreviews(files, input.name);
                        
                        // Upload ready - no notification to prevent alert issues
                        setTimeout(function() {
                            console.log('Images ready for upload! Click "Update" to save them.');
                        }, 1000);
                    }
                });
            });
            
            // Upload system ready
            
            // Check for upload success
            var successNotice = document.querySelector('.notice-success');
            if (successNotice && successNotice.textContent.includes('Images have been uploaded')) {
                // Clear preview images
                document.querySelectorAll('.preview-item').forEach(function(preview) {
                    preview.remove();
                });
                
                // Success - no notification to prevent alert issues
                console.log('Images uploaded successfully! Check the gallery below.');
            }
        });
        
        // Tab persistence is now handled by admin-tabs.js
        
        // Simple plus/minus button functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Handle plus/minus button clicks
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('resbs-number-btn')) {
                    e.preventDefault();
                    
                    var button = e.target;
                    var action = button.getAttribute('data-action');
                    var targetId = button.getAttribute('data-target');
                    var input = document.getElementById(targetId);
                    
                    if (input) {
                        var currentValue = parseInt(input.value) || 0;
                        var min = parseInt(input.getAttribute('min')) || 0;
                        var max = parseInt(input.getAttribute('max')) || 999;
                        
                        if (action === 'increase' && currentValue < max) {
                            input.value = currentValue + 1;
                        } else if (action === 'decrease' && currentValue > min) {
                            input.value = currentValue - 1;
                        }
                        
                        // Trigger change event
                        input.dispatchEvent(new Event('change'));
                    }
                }
            });
            
            // FEATURES & AMENITIES FUNCTIONALITY
            // Initialize features and amenities
            initFeaturesAndAmenities();
            
            function initFeaturesAndAmenities() {
                // Load existing features and amenities
                loadExistingFeatures();
                loadExistingAmenities();
                
                // Setup feature suggestion clicks
                document.addEventListener('click', function(e) {
                    if (e.target.classList.contains('resbs-suggestion-tag') && e.target.hasAttribute('data-feature')) {
                        addFeature(e.target.getAttribute('data-feature'));
                    }
                    if (e.target.classList.contains('resbs-suggestion-tag') && e.target.hasAttribute('data-amenity')) {
                        addAmenity(e.target.getAttribute('data-amenity'));
                    }
                });
                
                // Setup custom feature input
                var featureInput = document.getElementById('property_features_input');
                var addFeatureBtn = document.getElementById('add-custom-feature');
                if (featureInput && addFeatureBtn) {
                    addFeatureBtn.addEventListener('click', function() {
                        var customFeature = featureInput.value.trim();
                        if (customFeature) {
                            addFeature(customFeature);
                            featureInput.value = '';
                        }
                    });
                    
                    featureInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            var customFeature = featureInput.value.trim();
                            if (customFeature) {
                                addFeature(customFeature);
                                featureInput.value = '';
                            }
                        }
                    });
                }
                
                // Setup custom amenity input
                var amenityInput = document.getElementById('property_amenities_input');
                var addAmenityBtn = document.getElementById('add-custom-amenity');
                if (amenityInput && addAmenityBtn) {
                    addAmenityBtn.addEventListener('click', function() {
                        var customAmenity = amenityInput.value.trim();
                        if (customAmenity) {
                            addAmenity(customAmenity);
                            amenityInput.value = '';
                        }
                    });
                    
                    amenityInput.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            var customAmenity = amenityInput.value.trim();
                            if (customAmenity) {
                                addAmenity(customAmenity);
                                amenityInput.value = '';
                            }
                        }
                    });
                }
            }
            
            function loadExistingFeatures() {
                var featuresField = document.getElementById('property_features');
                var container = document.getElementById('feature-tags-container');
                if (featuresField && container) {
                    var features = featuresField.value ? featuresField.value.split(',') : [];
                    container.innerHTML = '';
                    features.forEach(function(feature) {
                        if (feature.trim()) {
                            addFeatureTag(feature.trim());
                        }
                    });
                }
            }
            
            function loadExistingAmenities() {
                var amenitiesField = document.getElementById('property_amenities');
                var container = document.getElementById('amenity-tags-container');
                if (amenitiesField && container) {
                    var amenities = amenitiesField.value ? amenitiesField.value.split(',') : [];
                    container.innerHTML = '';
                    amenities.forEach(function(amenity) {
                        if (amenity.trim()) {
                            addAmenityTag(amenity.trim());
                        }
                    });
                }
            }
            
            function addFeature(feature) {
                var featuresField = document.getElementById('property_features');
                var container = document.getElementById('feature-tags-container');
                
                if (featuresField && container) {
                    var currentFeatures = featuresField.value ? featuresField.value.split(',') : [];
                    if (!currentFeatures.includes(feature)) {
                        currentFeatures.push(feature);
                        featuresField.value = currentFeatures.join(',');
                        addFeatureTag(feature);
                    }
                }
            }
            
            function addAmenity(amenity) {
                var amenitiesField = document.getElementById('property_amenities');
                var container = document.getElementById('amenity-tags-container');
                
                if (amenitiesField && container) {
                    var currentAmenities = amenitiesField.value ? amenitiesField.value.split(',') : [];
                    if (!currentAmenities.includes(amenity)) {
                        currentAmenities.push(amenity);
                        amenitiesField.value = currentAmenities.join(',');
                        addAmenityTag(amenity);
                    }
                }
            }
            
            function addFeatureTag(feature) {
                var container = document.getElementById('feature-tags-container');
                if (container) {
                    var tag = document.createElement('span');
                    tag.className = 'resbs-feature-tag';
                    tag.innerHTML = feature + ' <button type="button" class="remove-tag" data-feature="' + feature + '">×</button>';
                    container.appendChild(tag);
                    
                    // Add remove functionality
                    tag.querySelector('.remove-tag').addEventListener('click', function() {
                        removeFeature(feature);
                        tag.remove();
                    });
                }
            }
            
            function addAmenityTag(amenity) {
                var container = document.getElementById('amenity-tags-container');
                if (container) {
                    var tag = document.createElement('span');
                    tag.className = 'resbs-feature-tag';
                    tag.innerHTML = amenity + ' <button type="button" class="remove-tag" data-amenity="' + amenity + '">×</button>';
                    container.appendChild(tag);
                    
                    // Add remove functionality
                    tag.querySelector('.remove-tag').addEventListener('click', function() {
                        removeAmenity(amenity);
                        tag.remove();
                    });
                }
            }
            
            function removeFeature(feature) {
                var featuresField = document.getElementById('property_features');
                if (featuresField) {
                    var currentFeatures = featuresField.value ? featuresField.value.split(',') : [];
                    var index = currentFeatures.indexOf(feature);
                    if (index > -1) {
                        currentFeatures.splice(index, 1);
                        featuresField.value = currentFeatures.join(',');
                    }
                }
            }
            
            function removeAmenity(amenity) {
                var amenitiesField = document.getElementById('property_amenities');
                if (amenitiesField) {
                    var currentAmenities = amenitiesField.value ? amenitiesField.value.split(',') : [];
                    var index = currentAmenities.indexOf(amenity);
                    if (index > -1) {
                        currentAmenities.splice(index, 1);
                        amenitiesField.value = currentAmenities.join(',');
                    }
                }
            }
        });
        </script>
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
        <!-- OLD TAB SYSTEM COMPLETELY REMOVED - Using new stunning tabs above -->
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
                                    <input type="number" id="property_area_sqft" name="property_area_sqft" value="<?php echo esc_attr($area_sqft); ?>" class="resbs-input" placeholder="0" step="0.01">
                                </div>
                                <div class="resbs-form-group">
                                    <label for="property_lot_size_sqft"><?php esc_html_e('Lot Size (Sq Ft)', 'realestate-booking-suite'); ?></label>
                                    <input type="number" id="property_lot_size_sqft" name="property_lot_size_sqft" value="<?php echo esc_attr($lot_size_sqft); ?>" class="resbs-input" placeholder="0" step="0.01">
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
                                <div class="resbs-upload-area" id="gallery-upload-area" onclick="document.getElementById('gallery-upload').click();">
                                    <div class="resbs-upload-content">
                                        <span class="dashicons dashicons-camera"></span>
                                        <p><?php esc_html_e('Click to upload photos or drag and drop', 'realestate-booking-suite'); ?></p>
                                        <p class="resbs-upload-info"><?php esc_html_e('JPG, PNG, GIF up to 10MB each', 'realestate-booking-suite'); ?></p>
                                    </div>
                                    <input type="file" id="gallery-upload" name="gallery_upload[]" multiple accept="image/*" style="position: absolute; top: -9999px; left: -9999px; opacity: 0; pointer-events: none;">
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
                                <div style="border: 2px dashed #28a745; padding: 25px; margin: 15px 0; background: linear-gradient(135deg, #f0fff4 0%, #e6f7e6 100%); border-radius: 12px; transition: all 0.3s ease;">
                                    <div style="text-align: center;">
                                        <div style="font-size: 48px; margin-bottom: 15px;">🏠</div>
                                        <h3 style="color: #28a745; margin: 0 0 10px 0; font-size: 20px;">Upload Floor Plans</h3>
                                        <p style="color: #555; margin: 0 0 20px 0; font-size: 16px;">Select floor plan images to help buyers visualize the layout</p>
                                        
                                        <div style="position: relative; display: inline-block; width: 100%; max-width: 400px;">
                                            <input type="file" name="floor_plans_upload[]" multiple accept="image/*" style="
                                                font-size: 16px; 
                                                padding: 15px 20px; 
                                                border: 2px solid #28a745; 
                                                background: white; 
                                                cursor: pointer; 
                                                width: 100%; 
                                                border-radius: 8px;
                                                transition: all 0.3s ease;
                                                box-shadow: 0 2px 8px rgba(40,167,69,0.1);
                                            " onmouseover="this.style.borderColor='#1e7e34'; this.style.boxShadow='0 4px 12px rgba(40,167,69,0.2)'" onmouseout="this.style.borderColor='#28a745'; this.style.boxShadow='0 2px 8px rgba(40,167,69,0.1)'">
                                    </div>
                                        
                                        <div style="margin-top: 15px; color: #666; font-size: 14px;">
                                            <p style="margin: 5px 0;">✅ Supported: JPG, PNG, GIF</p>
                                            <p style="margin: 5px 0;">📏 Maximum: 10MB per file</p>
                                            <p style="margin: 5px 0;">💾 Save page to upload files</p>
                                        </div>
                                    </div>
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
                        <input type="text" id="property_country" name="property_country" value="<?php echo esc_attr($country); ?>" class="resbs-input" placeholder="<?php esc_attr_e('Enter country name', 'realestate-booking-suite'); ?>">
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
        // Basic WordPress checks
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if (get_post_type($post_id) !== 'property') return;
        
        // Verify nonce
        if (!isset($_POST['resbs_property_metabox_nonce']) || !wp_verify_nonce($_POST['resbs_property_metabox_nonce'], 'resbs_property_metabox_nonce')) {
            return;
        }
        
        // Save property metabox data
        // Define fields to save
        $fields_to_save = array(
            'property_price' => '_property_price',
            'property_price_per_sqft' => '_property_price_per_sqft', 
            'property_price_note' => '_property_price_note',
            'property_bedrooms' => '_property_bedrooms',
            'property_bathrooms' => '_property_bathrooms',
            'property_half_baths' => '_property_half_baths',
            'property_total_rooms' => '_property_total_rooms',
            'property_floors' => '_property_floors',
            'property_floor_level' => '_property_floor_level',
            'property_area_sqft' => '_property_area_sqft',
            'property_lot_size_sqft' => '_property_lot_size_sqft',
            'property_year_built' => '_property_year_built',
            'property_year_remodeled' => '_property_year_remodeled',
            'property_type' => '_property_type',
            'property_status' => '_property_status',
            'property_condition' => '_property_condition',
            'property_address' => '_property_address',
            'property_city' => '_property_city',
            'property_state' => '_property_state',
            'property_zip' => '_property_zip',
            'property_country' => '_property_country',
            'property_map_iframe' => '_property_map_iframe',
            'property_features' => '_property_features',
            'property_amenities' => '_property_amenities',
            'property_nearby_schools' => '_property_nearby_schools',
            'property_nearby_shopping' => '_property_nearby_shopping',
            'property_nearby_restaurants' => '_property_nearby_restaurants',
            'property_virtual_tour' => '_property_virtual_tour',
            'property_virtual_tour_title' => '_property_virtual_tour_title',
            'property_virtual_tour_description' => '_property_virtual_tour_description',
            'property_virtual_tour_button_text' => '_property_virtual_tour_button_text',
            'property_agent_name' => '_property_agent_name',
            'property_agent_phone' => '_property_agent_phone',
            'property_agent_email' => '_property_agent_email',
            'property_agent_photo' => '_property_agent_photo',
            'property_agent_properties_sold' => '_property_agent_properties_sold',
            'property_agent_experience' => '_property_agent_experience',
            'property_agent_response_time' => '_property_agent_response_time',
            'property_agent_rating' => '_property_agent_rating',
            'property_agent_reviews' => '_property_agent_reviews',
            'property_agent_send_message_text' => '_property_agent_send_message_text',
            'property_contact_form_title' => '_property_contact_form_title',
            'property_contact_name_label' => '_property_contact_name_label',
            'property_contact_email_label' => '_property_contact_email_label',
            'property_contact_phone_label' => '_property_contact_phone_label',
            'property_contact_message_label' => '_property_contact_message_label',
            'property_contact_success_message' => '_property_contact_success_message',
            'property_contact_submit_text' => '_property_contact_submit_text',
            'property_mortgage_calculator_title' => '_property_mortgage_calculator_title',
            'property_mortgage_property_price_label' => '_property_mortgage_property_price_label',
            'property_mortgage_down_payment_label' => '_property_mortgage_down_payment_label',
            'property_mortgage_interest_rate_label' => '_property_mortgage_interest_rate_label',
            'property_mortgage_loan_term_label' => '_property_mortgage_loan_term_label',
            'property_mortgage_monthly_payment_label' => '_property_mortgage_monthly_payment_label',
            'property_mortgage_default_down_payment' => '_property_mortgage_default_down_payment',
            'property_mortgage_default_interest_rate' => '_property_mortgage_default_interest_rate',
            'property_mortgage_default_loan_term' => '_property_mortgage_default_loan_term',
            'property_mortgage_loan_terms' => '_property_mortgage_loan_terms',
            'property_mortgage_disclaimer_text' => '_property_mortgage_disclaimer_text',
            'property_video_url' => '_property_video_url',
            'property_video_embed' => '_property_video_embed',
            'property_tour_duration' => '_property_tour_duration',
            'property_tour_group_size' => '_property_tour_group_size',
            'property_tour_safety' => '_property_tour_safety'
        );
        
        $saved_count = 0;
        
        // Save all fields with better error handling
        foreach ($fields_to_save as $form_field => $meta_key) {
            // Special handling for HTML fields (iframe and video embed)
            if ($form_field === 'property_map_iframe') {
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
                $value = isset($_POST[$form_field]) ? wp_kses($_POST[$form_field], $allowed_html) : '';
                // Debug: Log iframe field saving
                error_log('Saving iframe field: ' . $form_field . ' = ' . substr($value, 0, 100) . '...');
            } elseif ($form_field === 'property_video_embed') {
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
                $value = isset($_POST[$form_field]) ? wp_kses($_POST[$form_field], $allowed_html) : '';
                // Debug: Log video embed field saving
                error_log('Saving video embed field: ' . $form_field . ' = ' . substr($value, 0, 100) . '...');
            } elseif ($form_field === 'property_virtual_tour_description' || 
                      $form_field === 'property_contact_success_message' ||
                      $form_field === 'property_mortgage_loan_terms' ||
                      $form_field === 'property_mortgage_disclaimer_text') {
                // Special handling for textarea fields
                $value = isset($_POST[$form_field]) ? sanitize_textarea_field($_POST[$form_field]) : '';
            } else {
                $value = isset($_POST[$form_field]) ? sanitize_text_field($_POST[$form_field]) : '';
            }
            
            // Only update if value is different or if it's a new field
            $existing_value = get_post_meta($post_id, $meta_key, true);
            if ($value !== $existing_value) {
                $result = update_post_meta($post_id, $meta_key, $value);
                if ($result !== false) {
                    $saved_count++;
                    // Debug: Log successful save
                    if ($form_field === 'property_map_iframe') {
                        error_log('Iframe field saved successfully');
                    }
                } else {
                    // Debug: Log save failure
                    if ($form_field === 'property_map_iframe') {
                        error_log('Failed to save iframe field');
                    }
                }
            }
        }
        
        // Direct save for iframe field to ensure it's saved
        if (isset($_POST['property_map_iframe'])) {
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
            $iframe_value = wp_kses($_POST['property_map_iframe'], $allowed_html);
            $result = update_post_meta($post_id, '_property_map_iframe', $iframe_value);
            if ($result !== false) {
                error_log('Iframe field saved directly: ' . substr($iframe_value, 0, 100) . '...');
            } else {
                error_log('Failed to save iframe field directly');
            }
        }
        
        // Handle checkboxes (they might not be in POST if unchecked)
        $checkbox_fields = array(
            'property_featured' => '_property_featured',
            'property_new' => '_property_new', 
            'property_sold' => '_property_sold',
            'property_foreclosure' => '_property_foreclosure',
            'property_open_house' => '_property_open_house',
            'property_call_for_price' => '_property_call_for_price'
        );
        
        foreach ($checkbox_fields as $form_field => $meta_key) {
            if (isset($_POST[$form_field])) {
                $result = update_post_meta($post_id, $meta_key, '1');
                if ($result !== false) {
                    $saved_count++;
                }
            } else {
                $result = delete_post_meta($post_id, $meta_key);
                if ($result !== false) {
                    $saved_count++;
                }
            }
        }
        
        // Silent update - no success messages
        // Removed success message to prevent alert issues

        // Handle gallery images from WordPress Media Library with better error handling
        if (isset($_POST['gallery_images']) && !empty($_POST['gallery_images'])) {
            $gallery_image_ids = explode(',', sanitize_text_field($_POST['gallery_images']));
            $gallery_image_ids = array_map('intval', $gallery_image_ids);
            $gallery_image_ids = array_filter($gallery_image_ids); // Remove empty values
            
            if (!empty($gallery_image_ids)) {
                $result = update_post_meta($post_id, '_property_gallery', $gallery_image_ids);
                if ($result !== false) {
                    $saved_count++;
                }
                error_log('RESBS: Gallery updated with ' . count($gallery_image_ids) . ' images: ' . implode(',', $gallery_image_ids));
            }
        }

        if (!empty($_FILES['floor_plans_upload']['name'][0])) {
            error_log('RESBS: Processing floor plans upload - ' . count($_FILES['floor_plans_upload']['name']) . ' files');
            $uploaded_ids = $this->handle_file_uploads($post_id, 'floor_plans_upload', '_property_floor_plans');
            error_log('RESBS: Floor plans upload result - ' . print_r($uploaded_ids, true));
        }

        // Handle media uploads with better error handling
        if (isset($_POST['property_gallery'])) {
            $gallery = array_map('intval', $_POST['property_gallery']);
            $result = update_post_meta($post_id, '_property_gallery', $gallery);
            if ($result !== false) {
                $saved_count++;
            }
        }

        if (isset($_POST['property_floor_plans'])) {
            $floor_plans = array_map('intval', $_POST['property_floor_plans']);
            $result = update_post_meta($post_id, '_property_floor_plans', $floor_plans);
            if ($result !== false) {
                $saved_count++;
            }
        }
        
        // Handle tab persistence redirect
        if (isset($_POST['resbs_active_tab'])) {
            $active_tab = sanitize_text_field($_POST['resbs_active_tab']);
            $redirect_url = add_query_arg('tab', $active_tab, get_edit_post_link($post_id, 'raw'));
            wp_redirect($redirect_url);
            exit;
        }
        
        // Silent file uploads - no success messages
        // Removed upload success messages to prevent alert issues
        
        
    }

    /**
     * Handle file uploads
     */
    private function handle_file_uploads($post_id, $field_name, $meta_key) {
        error_log('RESBS: Starting file upload for ' . $field_name);
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        
        $uploaded_ids = array();
        
        if (!empty($_FILES[$field_name]['name'])) {
            $files = $_FILES[$field_name];
            
            // Handle multiple files
            if (is_array($files['name'])) {
                for ($i = 0; $i < count($files['name']); $i++) {
                    if (!empty($files['name'][$i])) {
                        $file = array(
                            'name' => $files['name'][$i],
                            'type' => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'error' => $files['error'][$i],
                            'size' => $files['size'][$i]
                        );
                        
                        $attachment_id = media_handle_sideload($file, $post_id);
                        
                        if (!is_wp_error($attachment_id)) {
                            $uploaded_ids[] = $attachment_id;
                        }
                    }
                }
            } else {
                // Handle single file
                $attachment_id = media_handle_sideload($files, $post_id);
                
                if (!is_wp_error($attachment_id)) {
                    $uploaded_ids[] = $attachment_id;
                }
            }
        }
        
        if (!empty($uploaded_ids)) {
            // Get existing attachments
            $existing = get_post_meta($post_id, $meta_key, true);
            if (!is_array($existing)) {
                $existing = array();
            }
            
            // Merge with new uploads
            $all_attachments = array_merge($existing, $uploaded_ids);
            update_post_meta($post_id, $meta_key, $all_attachments);
            
            error_log('RESBS: Updated ' . $meta_key . ' with ' . count($all_attachments) . ' total attachments');
        }
        
        return $uploaded_ids;
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
            
            // Enqueue property metabox JavaScript for plus/minus buttons
            wp_enqueue_script(
                'resbs-property-metabox',
                RESBS_URL . 'assets/js/property-metabox.js',
                array('jquery'),
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
            
            // Tab switching is now inline in the metabox HTML
        }
    }

    /**
     * Handle media upload
     */
    public function handle_media_upload() {
        check_ajax_referer('resbs_metabox_nonce', 'nonce');
        
        if (!current_user_can('upload_files')) {
            wp_send_json_error(esc_html__('You do not have permission to upload files.', 'realestate-booking-suite'));
        }
        
        $uploaded_files = array();
        $errors = array();
        
        if (!empty($_FILES['files'])) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            
            // Handle multiple files
            if (is_array($_FILES['files']['name'])) {
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
                        } else {
                            $errors[] = sprintf(
                                esc_html__('Failed to upload %s: %s', 'realestate-booking-suite'),
                                $file['name'],
                                $attachment_id->get_error_message()
                            );
                        }
                    }
                }
            } else {
                // Handle single file
                if ($_FILES['files']['name']) {
                    $attachment_id = media_handle_sideload($_FILES['files'], 0);
                    
                    if (!is_wp_error($attachment_id)) {
                        $uploaded_files[] = $attachment_id;
                    } else {
                        $errors[] = sprintf(
                            esc_html__('Failed to upload %s: %s', 'realestate-booking-suite'),
                            $_FILES['files']['name'],
                            $attachment_id->get_error_message()
                        );
                    }
                }
            }
        }
        
        if (!empty($errors)) {
            wp_send_json_error(array(
                'message' => esc_html__('Some files failed to upload.', 'realestate-booking-suite'),
                'errors' => $errors,
                'uploaded_files' => $uploaded_files
            ));
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

    /**
     * Get gallery data via AJAX
     */
    public function get_gallery_data() {
        check_ajax_referer('resbs_metabox_nonce', 'nonce');
        
        $post_id = intval($_POST['post_id']);
        
        if (!$post_id) {
            wp_send_json_error('Invalid post ID');
        }
        
        // Get gallery images
        $gallery_images = get_post_meta($post_id, '_property_gallery', true);
        $gallery_html = '';
        
        error_log('RESBS: Gallery images for post ' . $post_id . ': ' . print_r($gallery_images, true));
        
        if (!empty($gallery_images) && is_array($gallery_images)) {
            foreach ($gallery_images as $image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                error_log('RESBS: Image ID ' . $image_id . ' URL: ' . $image_url);
                if ($image_url) {
                    $gallery_html .= '<div class="resbs-gallery-item" data-id="' . esc_attr($image_id) . '">';
                    $gallery_html .= '<img src="' . esc_url($image_url) . '" alt="">';
                    $gallery_html .= '<button type="button" class="resbs-remove-image" data-id="' . esc_attr($image_id) . '">×</button>';
                    $gallery_html .= '</div>';
                }
            }
        }
        
        error_log('RESBS: Gallery HTML: ' . $gallery_html);
        
        // Get floor plans
        $floor_plans = get_post_meta($post_id, '_property_floor_plans', true);
        $floor_plans_html = '';
        
        if (!empty($floor_plans) && is_array($floor_plans)) {
            foreach ($floor_plans as $image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                if ($image_url) {
                    $floor_plans_html .= '<div class="resbs-gallery-item" data-id="' . esc_attr($image_id) . '">';
                    $floor_plans_html .= '<img src="' . esc_url($image_url) . '" alt="">';
                    $floor_plans_html .= '<button type="button" class="resbs-remove-image" data-id="' . esc_attr($image_id) . '">×</button>';
                    $floor_plans_html .= '</div>';
                }
            }
        }
        
        wp_send_json_success(array(
            'gallery' => $gallery_html,
            'floor_plans' => $floor_plans_html
        ));
    }
    
    /**
     * Alternative save function with higher priority
     */
    public function save_property_metabox_alt($post_id) {
        // Only run for property post type
        if (get_post_type($post_id) !== 'property') {
            return;
        }
        
        // Simple backup save for critical fields
        $critical_fields = array(
            'property_bedrooms' => '_property_bedrooms',
            'property_bathrooms' => '_property_bathrooms',
            'property_area_sqft' => '_property_area_sqft',
            'property_lot_size_sqft' => '_property_lot_size_sqft'
        );
        
        foreach ($critical_fields as $form_field => $meta_field) {
            if (isset($_POST[$form_field])) {
                $value = sanitize_text_field($_POST[$form_field]);
                update_post_meta($post_id, $meta_field, $value);
            }
        }
    }
}
