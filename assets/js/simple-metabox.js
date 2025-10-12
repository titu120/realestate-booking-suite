/**
 * Simple Property Metabox JavaScript - NO CONFLICTS
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Simple initialization - NO form interference
    $(document).ready(function() {
        console.log('🔧 SIMPLE METABOX: Initializing without conflicts');
        
        // Only handle number inputs
        initNumberInputs();
        
        // Only handle media uploads
        initMediaUploads();
        
        // Simple tab switching
        initSimpleTabs();
        
        // DO NOT touch form submission
        console.log('🔧 SIMPLE METABOX: Ready - no form interference');
    });
    
    /**
     * Initialize number input controls
     */
    function initNumberInputs() {
        // Handle number input buttons
        $(document).on('click', '.resbs-number-btn', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var action = $btn.data('action');
            var target = $btn.data('target');
            var $input = $('#' + target);
            
            if ($input.length) {
                var currentValue = parseInt($input.val()) || 0;
                var newValue = currentValue;
                
                if (action === 'increase') {
                    newValue = currentValue + 1;
                } else if (action === 'decrease' && currentValue > 0) {
                    newValue = currentValue - 1;
                }
                
                $input.val(newValue);
            }
        });
    }
    
    /**
     * Initialize media uploads
     */
    function initMediaUploads() {
        // Handle upload area clicks
        $(document).on('click', '.resbs-upload-area', function(e) {
            e.preventDefault();
            var $input = $(this).find('input[type="file"]');
            if ($input.length) {
                $input.click();
            }
        });
        
        // Handle file input changes
        $(document).on('change', 'input[type="file"]', function() {
            var files = this.files;
            if (files.length > 0) {
                console.log('Files selected:', files.length);
                // Basic file handling - no complex upload logic
            }
        });
    }
    
    /**
     * Simple tab switching - NO form interference
     */
    function initSimpleTabs() {
        // Handle tab button clicks
        $(document).on('click', '.resbs-tab-nav-btn', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var targetTab = $btn.data('tab');
            
            if (targetTab) {
                // Hide all tab contents
                $('.resbs-tab-content').removeClass('active').hide();
                
                // Show target tab
                $('#' + targetTab).addClass('active').show();
                
                // Update button states
                $('.resbs-tab-nav-btn').removeClass('active');
                $btn.addClass('active');
                
                console.log('Tab switched to:', targetTab);
            }
        });
    }

})(jQuery);
