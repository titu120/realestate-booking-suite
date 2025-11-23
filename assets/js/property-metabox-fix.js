/**
 * CRITICAL FIX: Ensure WordPress Update button ALWAYS works
 * This file ensures NO JavaScript interferes with WordPress form submission
 * 
 * @package RealEstate_Booking_Suite
 */

// CRITICAL FIX - Remove all handlers and ensure form can submit
(function() {
    'use strict';
    
    function removeHandlers() {
        if (typeof jQuery === 'undefined' || !jQuery.fn) {
            return;
        }
        
        var $ = jQuery;
        
        // Remove ALL our submit handlers
        $('#post').off('submit.resbs');
        $('#post').off('submit.resbs-validation');
        $('#post').off('submit.resbs-block');
        
        // Remove ALL our click handlers
        $('#publish, #save-post, input[name="save"], input[name="publish"]').off('click.resbs');
        $('#publish, #save-post, input[name="save"], input[name="publish"]').off('click.resbs-block');
        
        // CRITICAL: Remove HTML5 validation that might block submission
        // WordPress handles validation - we don't need HTML5 required fields blocking
        var $form = $('#post');
        if ($form.length) {
            // Don't add novalidate - let WordPress handle it naturally
            // But ensure form can submit
            $form.off('submit.resbs-block');
        }
    }
    
    // Run immediately
    removeHandlers();
    
    // Run when DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', removeHandlers);
    } else {
        removeHandlers();
    }
    
    // Run when jQuery ready
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function() {
            removeHandlers();
            
            // CRITICAL: Ensure Update button is never disabled
            jQuery('#publish, #save-post').prop('disabled', false);
            jQuery('#publish, #save-post').removeClass('disabled');
        });
        
        jQuery(window).on('load', removeHandlers);
        
        // Run periodically to catch handlers added later
        setInterval(removeHandlers, 200);
    }
    
})();
