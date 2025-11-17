<?php
/**
 * Dashboard AJAX Handlers
 * 
 * @package RealEstate_Booking_Suite
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class RESBS_Dashboard_AJAX {

    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_resbs_get_dashboard_stats', array($this, 'get_dashboard_stats'));
        add_action('wp_ajax_resbs_get_property_chart_data', array($this, 'get_property_chart_data'));
        add_action('wp_ajax_resbs_get_recent_activities', array($this, 'get_recent_activities'));
        add_action('wp_ajax_resbs_export_property_data', array($this, 'export_property_data'));
    }

    /**
     * Get dashboard statistics
     * 
     * Security measures:
     * - Nonce verification (CSRF protection)
     * - Capability check (manage_options required)
     */
    public function get_dashboard_stats() {
        // Verify nonce and check capability using security helper
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        RESBS_Security::verify_ajax_nonce_and_capability($nonce, 'resbs_dashboard_nonce', 'manage_options');
        
        $stats = array();
        
        // Get property counts
        $property_counts = wp_count_posts('property');
        $stats['published_properties'] = $property_counts->publish;
        $stats['draft_properties'] = $property_counts->draft;
        $stats['pending_properties'] = $property_counts->pending;
        
        // Get booking counts (placeholder - integrate with your booking system)
        $stats['total_bookings'] = $this->get_total_bookings();
        $stats['pending_bookings'] = $this->get_pending_bookings();
        $stats['completed_bookings'] = $this->get_completed_bookings();
        
        // Get revenue data (placeholder - integrate with your payment system)
        $stats['total_revenue'] = $this->get_total_revenue();
        $stats['monthly_revenue'] = $this->get_monthly_revenue();
        
        // Get user counts
        $stats['total_users'] = count_users()['total_users'];
        $stats['new_users_this_month'] = $this->get_new_users_this_month();
        
        // Get property type counts
        $stats['property_types'] = $this->get_property_type_counts();
        
        // Get location counts
        $stats['locations'] = $this->get_location_counts();
        
        wp_send_json_success($stats);
    }

    /**
     * Get property chart data
     * 
     * Security measures:
     * - Nonce verification (CSRF protection)
     * - Capability check (manage_options required)
     * - Input sanitization
     */
    public function get_property_chart_data() {
        // Verify nonce and check capability using security helper
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        RESBS_Security::verify_ajax_nonce_and_capability($nonce, 'resbs_dashboard_nonce', 'manage_options');
        
        // Sanitize input using security helper
        $period = isset($_POST['period']) ? RESBS_Security::sanitize_text($_POST['period']) : '6months';
        
        // Validate period value to prevent invalid input
        $allowed_periods = array('7days', '30days', '6months', '1year');
        if (!in_array($period, $allowed_periods, true)) {
            $period = '6months';
        }
        $data = array();
        
        switch ($period) {
            case '7days':
                $data = $this->get_weekly_data();
                break;
            case '30days':
                $data = $this->get_monthly_data();
                break;
            case '6months':
                $data = $this->get_six_month_data();
                break;
            case '1year':
                $data = $this->get_yearly_data();
                break;
            default:
                $data = $this->get_six_month_data();
        }
        
        wp_send_json_success($data);
    }

    /**
     * Get recent activities
     * 
     * Security measures:
     * - Nonce verification (CSRF protection)
     * - Capability check (manage_options required)
     */
    public function get_recent_activities() {
        // Verify nonce and check capability using security helper
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        RESBS_Security::verify_ajax_nonce_and_capability($nonce, 'resbs_dashboard_nonce', 'manage_options');
        
        $activities = array();
        
        // Get recent properties
        $recent_properties = get_posts(array(
            'post_type' => 'property',
            'posts_per_page' => 5,
            'post_status' => array('publish', 'draft', 'pending')
        ));
        
        foreach ($recent_properties as $property) {
            $activities[] = array(
                'type' => 'property',
                'action' => 'created',
                'title' => esc_html($property->post_title),
                'date' => esc_html($property->post_date),
                'status' => esc_html($property->post_status),
                'url' => esc_url(get_edit_post_link($property->ID))
            );
        }
        
        // Get recent bookings (placeholder)
        $recent_bookings = $this->get_recent_bookings();
        foreach ($recent_bookings as $booking) {
            $activities[] = array(
                'type' => 'booking',
                'action' => 'created',
                'title' => isset($booking['title']) ? esc_html($booking['title']) : '',
                'date' => isset($booking['date']) ? esc_html($booking['date']) : '',
                'status' => isset($booking['status']) ? esc_html($booking['status']) : '',
                'url' => isset($booking['url']) ? esc_url($booking['url']) : ''
            );
        }
        
        // Sort by date
        usort($activities, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        wp_send_json_success(array_slice($activities, 0, 10));
    }

    /**
     * Export property data
     * 
     * Security measures:
     * - Nonce verification (CSRF protection)
     * - Capability check (manage_options required)
     * - Input sanitization and validation
     * - Rate limiting consideration (export operations)
     */
    public function export_property_data() {
        // Verify nonce and check capability using security helper
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        RESBS_Security::verify_ajax_nonce_and_capability($nonce, 'resbs_dashboard_nonce', 'manage_options');
        
        // Rate limiting check for export operations (prevent abuse)
        if (!RESBS_Security::check_rate_limit('resbs_export_properties', 5, 3600)) {
            wp_send_json_error(array(
                'message' => esc_html__('Too many export requests. Please try again later.', 'realestate-booking-suite')
            ));
        }
        
        // Sanitize and validate format using security helper
        $format = isset($_POST['format']) ? RESBS_Security::sanitize_text($_POST['format']) : 'csv';
        
        // Validate format to prevent invalid input
        $allowed_formats = array('csv', 'json');
        if (!in_array($format, $allowed_formats, true)) {
            wp_send_json_error(array(
                'message' => esc_html__('Invalid export format.', 'realestate-booking-suite')
            ));
        }
        $properties = get_posts(array(
            'post_type' => 'property',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        // Export based on validated format
        if ($format === 'csv') {
            $this->export_csv($properties);
        } else {
            // Format is already validated, so this must be 'json'
            $this->export_json($properties);
        }
    }

    /**
     * Get weekly data
     */
    private function get_weekly_data() {
        $labels = array();
        $values = array();
        
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('M j', strtotime($date));
            
            $count = $this->get_properties_count_by_date($date);
            $values[] = $count;
        }
        
        return array(
            'labels' => $labels,
            'values' => $values
        );
    }

    /**
     * Get monthly data
     */
    private function get_monthly_data() {
        $labels = array();
        $values = array();
        
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $labels[] = date('M j', strtotime($date));
            
            $count = $this->get_properties_count_by_date($date);
            $values[] = $count;
        }
        
        return array(
            'labels' => $labels,
            'values' => $values
        );
    }

    /**
     * Get six month data
     */
    private function get_six_month_data() {
        $labels = array();
        $values = array();
        
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-{$i} months"));
            $labels[] = date('M Y', strtotime($date));
            
            $count = $this->get_properties_count_by_month($date);
            $values[] = $count;
        }
        
        return array(
            'labels' => $labels,
            'values' => $values
        );
    }

    /**
     * Get yearly data
     */
    private function get_yearly_data() {
        $labels = array();
        $values = array();
        
        for ($i = 11; $i >= 0; $i--) {
            $date = date('Y-m-01', strtotime("-{$i} months"));
            $labels[] = date('M Y', strtotime($date));
            
            $count = $this->get_properties_count_by_month($date);
            $values[] = $count;
        }
        
        return array(
            'labels' => $labels,
            'values' => $values
        );
    }

    /**
     * Get properties count by date
     */
    private function get_properties_count_by_date($date) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_type = 'property' 
             AND post_status = 'publish' 
             AND DATE(post_date) = %s",
            $date
        ));
        
        return intval($count);
    }

    /**
     * Get properties count by month
     */
    private function get_properties_count_by_month($date) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} 
             WHERE post_type = 'property' 
             AND post_status = 'publish' 
             AND YEAR(post_date) = YEAR(%s) 
             AND MONTH(post_date) = MONTH(%s)",
            $date,
            $date
        ));
        
        return intval($count);
    }

    /**
     * Get total bookings (placeholder)
     */
    private function get_total_bookings() {
        // Integrate with your booking system
        return 0;
    }

    /**
     * Get pending bookings (placeholder)
     */
    private function get_pending_bookings() {
        // Integrate with your booking system
        return 0;
    }

    /**
     * Get completed bookings (placeholder)
     */
    private function get_completed_bookings() {
        // Integrate with your booking system
        return 0;
    }

    /**
     * Get total revenue (placeholder)
     */
    private function get_total_revenue() {
        // Integrate with your payment system
        return 0;
    }

    /**
     * Get monthly revenue (placeholder)
     */
    private function get_monthly_revenue() {
        // Integrate with your payment system
        return 0;
    }

    /**
     * Get new users this month
     */
    private function get_new_users_this_month() {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->users} 
             WHERE YEAR(user_registered) = YEAR(%s) 
             AND MONTH(user_registered) = MONTH(%s)",
            current_time('mysql'),
            current_time('mysql')
        ));
        
        return intval($count);
    }

    /**
     * Get property type counts
     */
    private function get_property_type_counts() {
        $types = get_terms(array(
            'taxonomy' => 'property_type',
            'hide_empty' => false
        ));
        
        $counts = array();
        foreach ($types as $type) {
            $counts[esc_attr($type->slug)] = intval($type->count);
        }
        
        return $counts;
    }

    /**
     * Get location counts
     */
    private function get_location_counts() {
        $locations = get_terms(array(
            'taxonomy' => 'property_location',
            'hide_empty' => false
        ));
        
        $counts = array();
        foreach ($locations as $location) {
            $counts[esc_attr($location->slug)] = intval($location->count);
        }
        
        return $counts;
    }

    /**
     * Get recent bookings (placeholder)
     */
    private function get_recent_bookings() {
        // Integrate with your booking system
        return array();
    }

    /**
     * Export CSV
     */
    private function export_csv($properties) {
        $filename = 'properties-export-' . date('Y-m-d-H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . esc_attr($filename) . '"');
        
        $output = fopen('php://output', 'w');
        
        // CSV headers
        fputcsv($output, array(
            'ID',
            'Title',
            'Price',
            'Bedrooms',
            'Bathrooms',
            'Area (Sq Ft)',
            'Address',
            'City',
            'State',
            'ZIP',
            'Property Type',
            'Property Status',
            'Date Created',
            'Status'
        ));
        
        // CSV data
        foreach ($properties as $property) {
            $meta = get_post_meta($property->ID);
            
            fputcsv($output, array(
                intval($property->ID),
                esc_html($property->post_title),
                isset($meta['_property_price'][0]) ? esc_html($meta['_property_price'][0]) : '',
                isset($meta['_property_bedrooms'][0]) ? esc_html($meta['_property_bedrooms'][0]) : '',
                isset($meta['_property_bathrooms'][0]) ? esc_html($meta['_property_bathrooms'][0]) : '',
                isset($meta['_property_area_sqft'][0]) ? esc_html($meta['_property_area_sqft'][0]) : '',
                isset($meta['_property_address'][0]) ? esc_html($meta['_property_address'][0]) : '',
                isset($meta['_property_city'][0]) ? esc_html($meta['_property_city'][0]) : '',
                isset($meta['_property_state'][0]) ? esc_html($meta['_property_state'][0]) : '',
                isset($meta['_property_zip'][0]) ? esc_html($meta['_property_zip'][0]) : '',
                isset($meta['_property_type'][0]) ? esc_html($meta['_property_type'][0]) : '',
                isset($meta['_property_status'][0]) ? esc_html($meta['_property_status'][0]) : '',
                esc_html($property->post_date),
                esc_html($property->post_status)
            ));
        }
        
        fclose($output);
        exit;
    }

    /**
     * Export JSON
     */
    private function export_json($properties) {
        $filename = 'properties-export-' . date('Y-m-d-H-i-s') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . esc_attr($filename) . '"');
        
        $data = array();
        
        foreach ($properties as $property) {
            $meta = get_post_meta($property->ID);
            
            $data[] = array(
                'id' => intval($property->ID),
                'title' => esc_html($property->post_title),
                'content' => wp_kses_post($property->post_content),
                'excerpt' => esc_html($property->post_excerpt),
                'price' => isset($meta['_property_price'][0]) ? esc_html($meta['_property_price'][0]) : '',
                'bedrooms' => isset($meta['_property_bedrooms'][0]) ? esc_html($meta['_property_bedrooms'][0]) : '',
                'bathrooms' => isset($meta['_property_bathrooms'][0]) ? esc_html($meta['_property_bathrooms'][0]) : '',
                'area_sqft' => isset($meta['_property_area_sqft'][0]) ? esc_html($meta['_property_area_sqft'][0]) : '',
                'address' => isset($meta['_property_address'][0]) ? esc_html($meta['_property_address'][0]) : '',
                'city' => isset($meta['_property_city'][0]) ? esc_html($meta['_property_city'][0]) : '',
                'state' => isset($meta['_property_state'][0]) ? esc_html($meta['_property_state'][0]) : '',
                'zip' => isset($meta['_property_zip'][0]) ? esc_html($meta['_property_zip'][0]) : '',
                'property_type' => isset($meta['_property_type'][0]) ? esc_html($meta['_property_type'][0]) : '',
                'property_status' => isset($meta['_property_status'][0]) ? esc_html($meta['_property_status'][0]) : '',
                'date_created' => esc_html($property->post_date),
                'status' => esc_html($property->post_status)
            );
        }
        
        echo wp_json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }
}
