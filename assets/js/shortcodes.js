/**
 * Shortcodes JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Initialize shortcodes when document is ready
    $(document).ready(function() {
        RESBS_Shortcodes.init();
    });

    // Shortcodes object
    window.RESBS_Shortcodes = {
        
        /**
         * Initialize all shortcode functionality
         */
        init: function() {
            this.initPropertyGrid();
            this.initSearch();
            this.initDashboard();
            this.initSubmitProperty();
            this.initFavorites();
            this.initTabs();
            this.initPublishButtons();
        },

        /**
         * Initialize property grid shortcode
         */
        initPropertyGrid: function() {
            $('.resbs-property-grid-widget.resbs-shortcode').each(function() {
                var $widget = $(this);
                var settings = $widget.data('settings') || {};
                
                // Initialize filters
                $widget.find('.resbs-filter-form').on('submit', function(e) {
                    e.preventDefault();
                    RESBS_Shortcodes.filterProperties($widget, $(this));
                });

                // Initialize infinite scroll
                if (settings.enable_infinite_scroll) {
                    RESBS_Shortcodes.initInfiniteScroll($widget);
                }

                // Initialize carousel if layout is carousel
                if (settings.layout === 'carousel') {
                    RESBS_Shortcodes.initCarousel($widget);
                }
            });
        },

        /**
         * Initialize search shortcode
         */
        initSearch: function() {
            $('.resbs-search-widget').each(function() {
                var $widget = $(this);
                
                // Initialize search form
                $widget.find('.resbs-search-form').on('submit', function(e) {
                    e.preventDefault();
                    RESBS_Shortcodes.performSearch($widget, $(this));
                });

                // Initialize map if present
                var $mapContainer = $widget.find('.resbs-map-container');
                if ($mapContainer.length) {
                    RESBS_Shortcodes.initSearchMap($mapContainer);
                }
            });
        },

        /**
         * Initialize dashboard shortcode
         */
        initDashboard: function() {
            $('.resbs-dashboard-widget').each(function() {
                var $widget = $(this);
                
                // Initialize profile form
                $widget.find('.resbs-profile-form').on('submit', function(e) {
                    e.preventDefault();
                    RESBS_Shortcodes.updateProfile($widget, $(this));
                });
            });
        },

        /**
         * Initialize submit property shortcode
         */
        initSubmitProperty: function() {
            $('.resbs-submit-widget').each(function() {
                var $widget = $(this);
                
                // Initialize form submission
                $widget.find('.resbs-submit-form').on('submit', function(e) {
                    e.preventDefault();
                    RESBS_Shortcodes.submitProperty($widget, $(this));
                });

                // Initialize gallery upload
                $widget.find('.resbs-gallery-input').on('change', function() {
                    RESBS_Shortcodes.handleGalleryUpload($widget, this);
                });
            });
        },

        /**
         * Initialize favorites shortcode
         */
        initFavorites: function() {
            $('.resbs-favorites-widget').each(function() {
                var $widget = $(this);
                
                // Initialize clear favorites
                $widget.find('.resbs-clear-favorites-btn').on('click', function() {
                    RESBS_Shortcodes.clearFavorites($widget, $(this));
                });
            });
        },

        /**
         * Initialize tabs functionality
         */
        initPublishButtons: function() {
            var self = this;
            $(document).on('click', '.publish-property-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var propertyId = $btn.data('property-id');
                
                if (!propertyId) {
                    return;
                }
                
                // Disable button
                $btn.prop('disabled', true);
                var originalHtml = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Publishing...');
                
                // Get nonce
                var nonce = '';
                if (typeof resbsShortcodes !== 'undefined' && resbsShortcodes.publish_nonce) {
                    nonce = resbsShortcodes.publish_nonce;
                } else {
                    console.error('Nonce not found for publish action');
                    $btn.prop('disabled', false);
                    $btn.html(originalHtml);
                    return;
                }
                
                // AJAX request
                $.ajax({
                    url: resbsShortcodes.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'resbs_publish_property',
                        property_id: propertyId,
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            if (typeof showToastNotification === 'function') {
                                showToastNotification(response.data.message, 'success');
                            } else {
                                alert(response.data.message);
                            }
                            
                            // Reload page after short delay
                            setTimeout(function() {
                                window.location.reload();
                            }, 1000);
                        } else {
                            // Show error message
                            var errorMsg = response.data && response.data.message ? response.data.message : 'Failed to publish property.';
                            if (typeof showToastNotification === 'function') {
                                showToastNotification(errorMsg, 'error');
                            } else {
                                alert(errorMsg);
                            }
                            
                            // Re-enable button
                            $btn.prop('disabled', false);
                            $btn.html(originalHtml);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        if (typeof showToastNotification === 'function') {
                            showToastNotification('An error occurred. Please try again.', 'error');
                        } else {
                            alert('An error occurred. Please try again.');
                        }
                        
                        // Re-enable button
                        $btn.prop('disabled', false);
                        $btn.html(originalHtml);
                    }
                });
            });
        },

        initTabs: function() {
            // Use event delegation to handle dynamically loaded content
            $(document).on('click', '.resbs-tab-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $widget = $btn.closest('.resbs-dashboard-widget');
                var tabId = $btn.data('tab');
                
                if (!tabId) {
                    console.error('Tab ID not found');
                    return;
                }
                
                // Update active tab button
                $widget.find('.resbs-tab-btn').removeClass('active');
                $btn.addClass('active');
                
                // Update active tab panel
                $widget.find('.resbs-tab-panel').removeClass('active');
                var $targetPanel = $widget.find('#' + tabId);
                
                if ($targetPanel.length) {
                    $targetPanel.addClass('active');
                } else {
                    console.error('Tab panel not found:', tabId);
                }
            });
        },

        /**
         * Filter properties
         */
        filterProperties: function($widget, $form) {
            var formData = $form.serialize();
            var $loading = $widget.find('.resbs-widget-loading');
            var $results = $widget.find('.resbs-property-grid, .resbs-property-carousel .resbs-carousel-track');
            var $noResults = $widget.find('.resbs-widget-no-properties');
            
            // Show loading
            $loading.show();
            $noResults.hide();
            
            // Make AJAX request
            $.ajax({
                url: resbs_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_filter_properties',
                    nonce: resbs_ajax.nonce,
                    form_data: formData,
                    widget_settings: $widget.data('settings')
                },
                success: function(response) {
                    $loading.hide();
                    
                    if (response.success && response.data.html) {
                        $results.html(response.data.html);
                        
                        // Reinitialize carousel if needed
                        var settings = $widget.data('settings');
                        if (settings && settings.layout === 'carousel') {
                            RESBS_Shortcodes.initCarousel($widget);
                        }
                    } else {
                        $noResults.show();
                    }
                },
                error: function() {
                    $loading.hide();
                    $noResults.show();
                }
            });
        },

        /**
         * Perform search
         */
        performSearch: function($widget, $form) {
            var formData = $form.serialize();
            var $loading = $widget.find('.resbs-results-loading');
            var $results = $widget.find('.resbs-results-grid');
            var $count = $widget.find('.resbs-count-text');
            
            // Show loading
            $loading.show();
            
            // Make AJAX request
            $.ajax({
                url: resbs_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_search_properties',
                    nonce: resbs_ajax.nonce,
                    form_data: formData
                },
                success: function(response) {
                    $loading.hide();
                    
                    if (response.success && response.data) {
                        $results.html(response.data.html);
                        $count.text(response.data.count + ' ' + (response.data.count === 1 ? 'property' : 'properties') + ' found');
                        
                        // Update map markers if present
                        if (response.data.markers && window.resbsMap) {
                            RESBS_Shortcodes.updateMapMarkers(response.data.markers);
                        }
                    }
                },
                error: function() {
                    $loading.hide();
                }
            });
        },

        /**
         * Update user profile
         */
        updateProfile: function($widget, $form) {
            var formData = $form.serialize();
            
            $.ajax({
                url: resbs_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_update_profile',
                    nonce: resbs_ajax.nonce,
                    form_data: formData
                },
                success: function(response) {
                    if (response.success) {
                        RESBS_Shortcodes.showMessage($widget, response.data.message, 'success');
                    } else {
                        RESBS_Shortcodes.showMessage($widget, response.data.message, 'error');
                    }
                },
                error: function() {
                    RESBS_Shortcodes.showMessage($widget, 'An error occurred. Please try again.', 'error');
                }
            });
        },

        /**
         * Submit property
         */
        submitProperty: function($widget, $form) {
            var formData = new FormData($form[0]);
            var $loading = $widget.find('.resbs-submit-loading');
            var $submitBtn = $form.find('button[type="submit"]');
            
            // Get nonce from form
            var nonce = $form.find('input[name="resbs_submit_nonce"]').val();
            if (!nonce) {
                RESBS_Shortcodes.showMessage($widget, 'Security check failed. Please refresh the page.', 'error');
                return;
            }
            
            // Add action and nonce to FormData
            formData.append('action', 'resbs_submit_property');
            formData.append('nonce', nonce);
            
            // Show loading
            $loading.show();
            $submitBtn.prop('disabled', true).text('Submitting...');
            
            $.ajax({
                url: (typeof resbs_ajax !== 'undefined' && resbs_ajax.ajax_url) ? resbs_ajax.ajax_url : ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $loading.hide();
                    $submitBtn.prop('disabled', false).text('Submit Property');
                    
                    if (response.success) {
                        var message = response.data.message || 'Property submitted successfully!';
                        // If message contains HTML (like links), allow it
                        var $messageDiv = $('<div class="resbs-message resbs-message-success">' + message + '</div>');
                        $widget.prepend($messageDiv);
                        
                        // Auto-hide after 8 seconds
                        setTimeout(function() {
                            $messageDiv.fadeOut(function() {
                                $messageDiv.remove();
                            });
                        }, 8000);
                        
                        $form[0].reset();
                        $widget.find('.resbs-gallery-preview').empty();
                    } else {
                        RESBS_Shortcodes.showMessage($widget, response.data && response.data.message ? response.data.message : 'An error occurred. Please try again.', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $loading.hide();
                    $submitBtn.prop('disabled', false).text('Submit Property');
                    
                    var errorMessage = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMessage = xhr.responseJSON.data.message;
                    }
                    RESBS_Shortcodes.showMessage($widget, errorMessage, 'error');
                    console.error('Submit property error:', status, error, xhr);
                }
            });
        },

        /**
         * Clear favorites
         */
        clearFavorites: function($widget, $btn) {
            if (!confirm('Are you sure you want to clear all your favorite properties?')) {
                return;
            }
            
            var userId = $btn.data('user-id');
            
            $.ajax({
                url: resbs_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_clear_favorites',
                    nonce: resbs_ajax.nonce,
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        $widget.find('.resbs-favorites-grid').empty();
                        $widget.find('.resbs-favorites-empty').show();
                        RESBS_Shortcodes.showMessage($widget, 'All favorites cleared successfully.', 'success');
                    } else {
                        RESBS_Shortcodes.showMessage($widget, 'An error occurred. Please try again.', 'error');
                    }
                },
                error: function() {
                    RESBS_Shortcodes.showMessage($widget, 'An error occurred. Please try again.', 'error');
                }
            });
        },

        /**
         * Handle gallery upload
         */
        handleGalleryUpload: function($widget, input) {
            var files = input.files;
            var $preview = $widget.find('.resbs-gallery-preview');
            
            $preview.empty();
            
            for (var i = 0; i < files.length; i++) {
                var file = files[i];
                var reader = new FileReader();
                
                reader.onload = function(e) {
                    var $img = $('<img>').attr('src', e.target.result);
                    $preview.append($img);
                };
                
                reader.readAsDataURL(file);
            }
        },

        /**
         * Initialize infinite scroll
         */
        initInfiniteScroll: function($widget) {
            var $loadMoreBtn = $widget.find('.resbs-load-more-btn');
            var page = 2;
            var loading = false;
            
            $loadMoreBtn.on('click', function() {
                if (loading) return;
                
                loading = true;
                $loadMoreBtn.text('Loading...');
                
                var settings = $widget.data('settings');
                
                $.ajax({
                    url: resbs_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'resbs_load_more_properties',
                        nonce: resbs_ajax.nonce,
                        page: page,
                        settings: settings
                    },
                    success: function(response) {
                        if (response.success && response.data.html) {
                            $widget.find('.resbs-property-grid').append(response.data.html);
                            page++;
                            
                            if (!response.data.has_more) {
                                $loadMoreBtn.hide();
                            }
                        } else {
                            $loadMoreBtn.hide();
                        }
                    },
                    error: function() {
                        $loadMoreBtn.text('Load More Properties');
                    },
                    complete: function() {
                        loading = false;
                        $loadMoreBtn.text('Load More Properties');
                    }
                });
            });
        },

        /**
         * Initialize carousel
         */
        initCarousel: function($widget) {
            // This would integrate with the existing carousel functionality
            if (typeof RESBS_Carousel !== 'undefined') {
                RESBS_Carousel.init($widget.find('.resbs-property-carousel'));
            }
        },

        /**
         * Initialize search map
         */
        initSearchMap: function($container) {
            // This would integrate with the existing maps functionality
            if (typeof RESBS_Maps !== 'undefined') {
                RESBS_Maps.initSearchMap($container);
            }
        },


        /**
         * Update map markers
         */
        updateMapMarkers: function(markers) {
            if (typeof RESBS_Maps !== 'undefined') {
                RESBS_Maps.updateMarkers(markers);
            }
        },

        /**
         * Show message
         */
        showMessage: function($widget, message, type) {
            var $message = $('<div class="resbs-message resbs-message-' + type + '">' + message + '</div>');
            
            $widget.prepend($message);
            
            setTimeout(function() {
                $message.fadeOut(function() {
                    $message.remove();
                });
            }, 5000);
        }
    };

})(jQuery);
