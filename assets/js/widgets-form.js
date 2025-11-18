/**
 * Widgets Form JavaScript
 * Extracted from class-resbs-widgets.php
 * 
 * @package RealEstate_Booking_Suite
 */

jQuery(document).ready(function($) {
    // Show/hide filter options based on checkbox
    $('.resbs-widget-form').on('change', '.resbs-show-filters-checkbox', function() {
        if ($(this).is(':checked')) {
            $('.resbs-filter-options').show();
        } else {
            $('.resbs-filter-options').hide();
        }
    });
    
    // Show/hide carousel options based on layout selection
    $('.resbs-widget-form').on('change', '.resbs-layout-select', function() {
        if ($(this).val() === 'carousel') {
            $('.resbs-carousel-options').show();
        } else {
            $('.resbs-carousel-options').hide();
        }
    });
});

