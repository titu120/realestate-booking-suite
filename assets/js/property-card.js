/**
 * Property Card JavaScript
 * Extracted from inline script in templates/property-card.php
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Favorite button functionality
        $('.resbs-favorite-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var propertyId = $btn.data('property-id');
            var $icon = $btn.find('i');
            
            if (!propertyId || !resbs_archive || !resbs_archive.nonce) {
                return;
            }
            
            // Toggle visual state
            $icon.toggleClass('far fas');
            $btn.toggleClass('favorited');
            
            // AJAX call
            $.ajax({
                url: resbs_archive.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_toggle_favorite',
                    property_id: parseInt(propertyId),
                    nonce: resbs_archive.nonce
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.favorited) {
                            $btn.addClass('favorited');
                            $icon.removeClass('far').addClass('fas');
                        } else {
                            $btn.removeClass('favorited');
                            $icon.removeClass('fas').addClass('far');
                        }
                    } else {
                        // Revert visual state on error
                        $icon.toggleClass('far fas');
                        $btn.toggleClass('favorited');
                    }
                },
                error: function(xhr, status, error) {
                    // Revert visual state on error
                    $icon.toggleClass('far fas');
                    $btn.toggleClass('favorited');
                }
            });
        });
        
        // Quick view functionality
        $('.resbs-quick-view-btn').on('click', function(e) {
            e.preventDefault();
            var propertyId = $(this).data('property-id');
            
            if (!propertyId) {
                return;
            }
            
            resbsOpenQuickView(parseInt(propertyId));
        });
        
        // Contact agent functionality
        $('.resbs-contact-agent-btn').on('click', function(e) {
            e.preventDefault();
            var propertyId = $(this).data('property-id');
            
            if (!propertyId) {
                return;
            }
            
            resbsOpenContactForm(parseInt(propertyId));
        });
    });
    
    // Quick view function
    function resbsOpenQuickView(propertyId) {
        if (!propertyId || !Number.isInteger(propertyId) || propertyId <= 0) {
            return;
        }
    }
    
    // Contact form function
    function resbsOpenContactForm(propertyId) {
        if (!propertyId || !Number.isInteger(propertyId) || propertyId <= 0) {
            return;
        }
    }
    
    // Make functions globally available
    window.resbsOpenQuickView = resbsOpenQuickView;
    window.resbsOpenContactForm = resbsOpenContactForm;
    
})(jQuery);

