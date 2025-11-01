/**
 * Professional Property Metabox JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

        // Initialize when document is ready
        $(document).ready(function() {
            console.log('🔧 DEBUG: Property metabox JS loaded');
            
            // Initialize only essential features
            RESBS_Property_Metabox.initNumberInputs();
            RESBS_Property_Metabox.initMediaUploader();
            
            // DO NOT interfere with form submission
            console.log('🔧 DEBUG: Form submission handlers disabled to prevent conflicts');
        });
    
    // Additional initialization for upload areas
    $(document).ready(function() {
        // Force click handler for upload areas
        $('.resbs-upload-area').each(function() {
            var $area = $(this);
            var $input = $area.find('input[type="file"]');
            
            console.log('Setting up upload area:', {
                area: $area.length,
                input: $input.length,
                areaId: $area.attr('id'),
                inputId: $input.attr('id')
            });
            
            // Remove any existing handlers
            $area.off('click.upload');
            
            // Add new click handler
            $area.on('click.upload', function(e) {
                console.log('Upload area clicked directly!');
                e.preventDefault();
                e.stopPropagation();
                
                if ($input.length > 0) {
                    $input.click();
                } else {
                    console.error('File input not found!');
                }
            });
        });
        
        // Handle file input changes
        $(document).on('change', 'input[type="file"][id*="upload"]', function() {
            var files = this.files;
            var inputId = $(this).attr('id');
            var gridId = '';
            
            console.log('File input changed:', inputId, files.length);
            
            // Determine which grid to use based on input ID
            if (inputId === 'gallery-upload') {
                gridId = '#gallery-grid';
            } else if (inputId === 'floor-plans-upload') {
                gridId = '#floor-plans-grid';
            }
            
            if (files.length > 0 && gridId) {
                var $grid = $(gridId);
                if (typeof RESBS_Property_Metabox !== 'undefined' && RESBS_Property_Metabox.uploadFiles) {
                    RESBS_Property_Metabox.uploadFiles(files, $grid, 'gallery');
                } else {
                    console.error('RESBS_Property_Metabox.uploadFiles not available');
                }
            }
        });
    });

    // Property Metabox object
    window.RESBS_Property_Metabox = {
        
        /**
         * Initialize metabox functionality
         */
        init: function() {
            // Tab initialization is handled by admin-tabs.js to prevent conflicts
            this.initNumberInputs();
            this.initMediaUploader();
            this.initMapIntegration();
            this.initFormValidation();
            this.initAutoSave();
            this.initEnhancedFeatures();
        },

    /**
     * Initialize tabs with enhanced animations
     */
    initTabs: function() {
        console.log('🔧 DEBUG: Initializing tabs');
        
        // Find all tab buttons
        var $tabButtons = $('.resbs-tab-btn, .resbs-tab-nav-btn');
        console.log('🔧 DEBUG: Found tab buttons:', $tabButtons.length);
        
        if ($tabButtons.length === 0) {
            console.log('🔧 DEBUG: No tab buttons found, trying again in 500ms');
            setTimeout(function() {
                RESBS_Property_Metabox.initTabs();
            }, 500);
            return;
        }
        
        // Handle both old and new tab systems
        $tabButtons.off('click.tabSwitch').on('click.tabSwitch', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $btn = $(this);
            var $container = $btn.closest('.resbs-tabs, .resbs-stunning-tabs');
            var tabId = $btn.data('tab');
            
            console.log('🔧 DEBUG: Tab clicked:', tabId);
            console.log('🔧 DEBUG: Button:', $btn);
            console.log('🔧 DEBUG: Container:', $container);
            
            // Don't switch if already active
            if ($btn.hasClass('active')) {
                console.log('🔧 DEBUG: Tab already active, skipping');
                return;
            }
            
            // Save tab immediately when clicked
            localStorage.setItem('resbs_active_tab', tabId);
            console.log('🔧 DEBUG: Tab saved immediately:', tabId);
            
            // SIMPLE APPROACH: Just hide all tabs and show the selected one
            $container.find('.resbs-tab-content').removeClass('active').hide();
            $container.find('.resbs-tab-btn, .resbs-tab-nav-btn').removeClass('active');
            
            // Show the selected tab
            var $newContent = $container.find('#' + tabId);
            if ($newContent.length) {
                $newContent.addClass('active').show();
                $btn.addClass('active');
                console.log('🔧 DEBUG: Tab content shown:', tabId);
            } else {
                console.error('🔧 DEBUG: Tab content not found for:', tabId);
            }
        });
    },

    /**
     * Fix tab display on page load
     */
    fixTabDisplay: function() {
        console.log('🔧 DEBUG: Fixing tab display');
        
        // Find all tab containers
        $('.resbs-tabs, .resbs-stunning-tabs').each(function() {
            var $container = $(this);
            
            // Check if there's already an active tab
            var $activeTab = $container.find('.resbs-tab-content.active');
            if ($activeTab.length > 0) {
                console.log('🔧 DEBUG: Active tab already exists, keeping it');
                return;
            }
            
            // Try to use saved tab from localStorage
            var savedTab = localStorage.getItem('resbs_active_tab');
            console.log('🔧 DEBUG: Saved tab from localStorage:', savedTab);
            
            if (savedTab) {
                console.log('🔧 DEBUG: Using saved tab:', savedTab);
                
                // Hide all tabs first
                $container.find('.resbs-tab-content').removeClass('active').hide();
                $container.find('.resbs-tab-btn, .resbs-tab-nav-btn').removeClass('active');
                
                // Show the saved tab
                var $activeContent = $container.find('#' + savedTab);
                var $activeBtn = $container.find('[data-tab="' + savedTab + '"]');
                
                if ($activeContent.length) {
                    $activeContent.addClass('active').show();
                    $activeBtn.addClass('active');
                    console.log('🔧 DEBUG: Saved tab displayed:', savedTab);
                } else {
                    console.error('🔧 DEBUG: Saved tab content not found:', savedTab);
                    // Fallback to overview tab
                    this.showOverviewTab($container);
                }
            } else {
                console.log('🔧 DEBUG: No saved tab, keeping current state');
            }
        });
    },

    /**
     * Show overview tab as fallback
     */
    showOverviewTab: function($container) {
        console.log('🔧 DEBUG: Showing overview tab as fallback');
        
        // Hide all tabs first
        $container.find('.resbs-tab-content').removeClass('active').hide();
        $container.find('.resbs-tab-btn, .resbs-tab-nav-btn').removeClass('active');
        
        // Show overview tab
        var $overviewContent = $container.find('#overview');
        var $overviewBtn = $container.find('[data-tab="overview"]');
        
        if ($overviewContent.length) {
            $overviewContent.addClass('active').show();
            $overviewBtn.addClass('active');
            console.log('🔧 DEBUG: Overview tab displayed');
        } else {
            console.error('🔧 DEBUG: Overview tab not found');
        }
    },

        /**
         * Add ripple effect to buttons
         */
        addRippleEffect: function($element) {
            var $ripple = $('<span class="resbs-ripple"></span>');
            $element.append($ripple);
            
            setTimeout(function() {
                $ripple.remove();
            }, 600);
        },

        /**
         * Initialize number inputs with enhanced +/- buttons
         */
        initNumberInputs: function() {
            $(document).on('click', '.resbs-number-btn', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $input = $('#' + $btn.data('target'));
                var action = $btn.data('action');
                var currentValue = parseInt($input.val()) || 0;
                var min = parseInt($input.attr('min')) || 0;
                var max = parseInt($input.attr('max')) || 999;
                
                // Add visual feedback
                $btn.addClass('resbs-loading');
                
                setTimeout(function() {
                    if (action === 'increase') {
                        if (currentValue < max) {
                            $input.val(currentValue + 1).trigger('change');
                            RESBS_Property_Metabox.animateValueChange($input, currentValue, currentValue + 1);
                        }
                    } else if (action === 'decrease') {
                        if (currentValue > min) {
                            $input.val(currentValue - 1).trigger('change');
                            RESBS_Property_Metabox.animateValueChange($input, currentValue, currentValue - 1);
                        }
                    }
                    $btn.removeClass('resbs-loading');
                }, 150);
                
                // Add ripple effect
                RESBS_Property_Metabox.addRippleEffect($btn);
            });
        },

        /**
         * Animate value changes in number inputs
         */
        animateValueChange: function($input, from, to) {
            $input.addClass('resbs-value-changing');
            setTimeout(function() {
                $input.removeClass('resbs-value-changing');
            }, 300);
        },

        /**
         * Initialize media uploader with enhanced animations
         */
        initMediaUploader: function() {
            // Gallery upload
            this.initMediaUpload('#gallery-upload-area', '#gallery-upload', '#gallery-grid', 'gallery');
            
            // Floor plans upload
            this.initMediaUpload('#floor-plans-upload-area', '#floor-plans-upload', '#floor-plans-grid', 'floor_plans');
            
            // Fallback click handler using event delegation
            $(document).on('click', '.resbs-upload-area', function(e) {
                console.log('Fallback upload area clicked!', e);
                e.preventDefault();
                var $this = $(this);
                var $input = $this.find('input[type="file"]');
                if ($input.length > 0) {
                    $input.click();
                }
            });
            
            // Remove image functionality with enhanced animations
            $(document).on('click', '.resbs-remove-image', function(e) {
                e.preventDefault();
                var $item = $(this).closest('.resbs-gallery-item');
                var imageId = $(this).data('id');
                
                // Add confirmation with better UX
                if (confirm(resbs_metabox.strings.delete_confirm)) {
                    $item.addClass('resbs-loading');
                    
                    $.ajax({
                        url: resbs_metabox.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'resbs_delete_property_media',
                            attachment_id: imageId,
                            nonce: resbs_metabox.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                $item.animate({
                                    opacity: 0,
                                    transform: 'scale(0.8)'
                                }, 300, function() {
                                    $(this).remove();
                                });
                            } else {
                                $item.removeClass('resbs-loading');
                                console.log('Error deleting image. Please try again.');
                            }
                        },
                        error: function() {
                            $item.removeClass('resbs-loading');
                            console.log('Error deleting image. Please try again.');
                        }
                    });
                }
            });
        },

        /**
         * Initialize media upload for specific area with enhanced UX
         */
        initMediaUpload: function(areaSelector, inputSelector, gridSelector, type) {
            var $area = $(areaSelector);
            var $input = $(inputSelector);
            var $grid = $(gridSelector);
            
            // Debug: Check if elements exist
            console.log('Initializing media upload:', {
                areaSelector: areaSelector,
                inputSelector: inputSelector,
                gridSelector: gridSelector,
                areaExists: $area.length > 0,
                inputExists: $input.length > 0,
                gridExists: $grid.length > 0
            });
            
            // Click to upload with ripple effect
            $area.on('click', function(e) {
                console.log('Upload area clicked!', e);
                e.preventDefault();
                RESBS_Property_Metabox.addRippleEffect($(this));
                $input.click();
            });
            
            // File input change
            $input.on('change', function() {
                var files = this.files;
                if (files.length > 0) {
                    RESBS_Property_Metabox.uploadFiles(files, $grid, type);
                }
            });
            
            // Enhanced drag and drop with animations
            $area.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });
            
            $area.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });
            
            $area.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
                
                var files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    RESBS_Property_Metabox.uploadFiles(files, $grid, type);
                }
            });
            
            // Add hover effects
            $area.on('mouseenter', function() {
                $(this).addClass('resbs-hover');
            }).on('mouseleave', function() {
                $(this).removeClass('resbs-hover');
            });
        },

        /**
         * Upload files via AJAX with enhanced feedback
         */
        uploadFiles: function(files, $grid, type) {
            var formData = new FormData();
            
            for (var i = 0; i < files.length; i++) {
                formData.append('files[' + i + ']', files[i]);
            }
            
            formData.append('action', 'resbs_upload_property_media');
            formData.append('nonce', resbs_metabox.nonce);
            
            // Show enhanced loading state
            var $loadingItem = $('<div class="resbs-loading-item">' +
                '<div class="resbs-spinner"></div>' +
                '<p>Uploading ' + files.length + ' file(s)...</p>' +
                '</div>');
            $grid.append($loadingItem);
            
            $.ajax({
                url: resbs_metabox.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var percentComplete = evt.loaded / evt.total * 100;
                            $loadingItem.find('p').text('Uploading... ' + Math.round(percentComplete) + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $loadingItem.fadeOut(300, function() {
                        $(this).remove();
                    });
                    
                    if (response.success && response.data.length > 0) {
                        response.data.forEach(function(attachmentId, index) {
                            setTimeout(function() {
                                RESBS_Property_Metabox.addImageToGrid(attachmentId, $grid);
                            }, index * 100); // Stagger animations
                        });
                        
                        // Show success message
                        RESBS_Property_Metabox.showNotification('Files uploaded successfully!', 'success');
                    } else {
                        RESBS_Property_Metabox.showNotification(resbs_metabox.strings.upload_error, 'error');
                    }
                },
                error: function(xhr, status, error) {
                    $loadingItem.fadeOut(300, function() {
                        $(this).remove();
                    });
                    
                    var errorMessage = resbs_metabox.strings.upload_error;
                    if (xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message) {
                        errorMessage = xhr.responseJSON.data.message;
                    }
                    
                    RESBS_Property_Metabox.showNotification(errorMessage, 'error');
                }
            });
        },

        /**
         * Add image to grid with enhanced animations
         */
        addImageToGrid: function(attachmentId, $grid) {
            $.ajax({
                url: resbs_metabox.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_get_attachment_data',
                    attachment_id: attachmentId,
                    nonce: resbs_metabox.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var $item = $('<div class="resbs-gallery-item" data-id="' + attachmentId + '">' +
                            '<img src="' + response.data.thumbnail + '" alt="">' +
                            '<button type="button" class="resbs-remove-image" data-id="' + attachmentId + '">×</button>' +
                            '</div>');
                        
                        $grid.append($item);
                        
                        // Enhanced entrance animation
                        $item.css({
                            opacity: 0,
                            transform: 'scale(0.8) translateY(20px)'
                        }).animate({
                            opacity: 1
                        }, 300).css('transform', 'scale(1) translateY(0)');
                    }
                }
            });
        },

        /**
         * Show notification
         */
        showNotification: function(message, type) {
            type = type || 'info';
            var $notification = $('<div class="resbs-notification resbs-notification-' + type + '">' +
                '<div class="resbs-notification-content">' +
                '<span class="resbs-notification-message">' + message + '</span>' +
                '<button type="button" class="resbs-notification-close">×</button>' +
                '</div>' +
                '</div>');
            
            $('body').append($notification);
            
            // Animate in
            $notification.css({
                opacity: 0,
                transform: 'translateX(100%)'
            }).animate({
                opacity: 1
            }, 300).css('transform', 'translateX(0)');
            
            // Auto remove after 5 seconds
            setTimeout(function() {
                RESBS_Property_Metabox.hideNotification($notification);
            }, 5000);
            
            // Close button
            $notification.find('.resbs-notification-close').on('click', function() {
                RESBS_Property_Metabox.hideNotification($notification);
            });
        },

        /**
         * Hide notification
         */
        hideNotification: function($notification) {
            $notification.animate({
                opacity: 0,
                transform: 'translateX(100%)'
            }, 300, function() {
                $(this).remove();
            });
        },

        /**
         * Initialize map integration
         */
        initMapIntegration: function() {
            if (!resbs_metabox.map_api_key) {
                console.warn('⚠️ Google Maps API key not configured. Auto-geocoding disabled.');
                return;
            }
            
            console.log('🗺️ Initializing map integration with API key');
            
            // Load Google Maps API if not already loaded
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                console.log('📡 Loading Google Maps API...');
                RESBS_Property_Metabox.loadGoogleMapsAPI();
            } else {
                console.log('✅ Google Maps API already loaded');
                window.resbsMapsLoaded = true;
            }
            
            // Geocode address button (manual trigger)
            $('#resbs-geocode-address').on('click', function() {
                RESBS_Property_Metabox.geocodeFromFields();
            });
            
            // Auto-geocode when address fields change (dynamic location update)
            var geocodeTimeout;
            var addressFields = ['#property_address', '#property_city', '#property_state', '#property_zip', '#property_country'];
            
            addressFields.forEach(function(fieldSelector) {
                $(fieldSelector).on('input blur', function() {
                    // Clear previous timeout
                    clearTimeout(geocodeTimeout);
                    
                    // Debounce: Wait 1 second after user stops typing before geocoding
                    geocodeTimeout = setTimeout(function() {
                        var address = $('#property_address').val();
                        var city = $('#property_city').val();
                        var state = $('#property_state').val();
                        var zip = $('#property_zip').val();
                        var country = $('#property_country').val();
                        
                        // Only auto-geocode if at least city or address is provided
                        if (address || city) {
                            // Wait for Google Maps API to load if needed
                            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                                console.log('🔄 Auto-geocoding address fields...');
                                RESBS_Property_Metabox.geocodeFromFields(true);
                            } else {
                                // Wait for API to load, then geocode
                                var checkApi = setInterval(function() {
                                    if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                                        clearInterval(checkApi);
                                        console.log('🔄 Auto-geocoding address fields (after API load)...');
                                        RESBS_Property_Metabox.geocodeFromFields(true);
                                    }
                                }, 500);
                                
                                // Timeout after 10 seconds
                                setTimeout(function() {
                                    clearInterval(checkApi);
                                }, 10000);
                            }
                        }
                    }, 1000);
                });
            });
            
            // Initialize map if coordinates exist or wait for auto-geocode
            var lat = $('#property_latitude').val();
            var lng = $('#property_longitude').val();
            
            if (lat && lng && !isNaN(parseFloat(lat)) && !isNaN(parseFloat(lng))) {
                // Wait for Google Maps API to load, then initialize map
                var waitForApiToInitMap = setInterval(function() {
                    if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                        clearInterval(waitForApiToInitMap);
                        setTimeout(function() {
                            RESBS_Property_Metabox.initMap(parseFloat(lat), parseFloat(lng));
                        }, 300);
                    }
                }, 500);
                
                // Timeout after 10 seconds
                setTimeout(function() {
                    clearInterval(waitForApiToInitMap);
                }, 10000);
            } else {
                // If no coordinates but address fields exist, try to geocode on page load
                var address = $('#property_address').val();
                var city = $('#property_city').val();
                if ((address || city) && resbs_metabox.map_api_key) {
                    // Wait for Google Maps API to load
                    var waitForApi = setInterval(function() {
                        if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                            clearInterval(waitForApi);
                            setTimeout(function() {
                                console.log('🔄 Auto-geocoding on page load...');
                                RESBS_Property_Metabox.geocodeFromFields(true);
                            }, 500);
                        }
                    }, 500);
                    
                    // Timeout after 10 seconds
                    setTimeout(function() {
                        clearInterval(waitForApi);
                    }, 10000);
                }
            }
        },

        /**
         * Geocode from address fields (extracts all fields and geocodes)
         */
        geocodeFromFields: function(silent) {
            var address = $('#property_address').val();
            var city = $('#property_city').val();
            var state = $('#property_state').val();
            var zip = $('#property_zip').val();
            var country = $('#property_country').val();
            
            var fullAddress = [address, city, state, zip, country].filter(function(part) {
                return part && part.trim() !== '';
            }).join(', ');
            
            if (fullAddress) {
                if (!silent) {
                    console.log('📍 Geocoding:', fullAddress);
                }
                RESBS_Property_Metabox.geocodeAddress(fullAddress, silent);
            } else {
                if (!silent) {
                    console.warn('⚠️ No address fields filled to geocode');
                }
            }
        },

        /**
         * Geocode address
         */
        geocodeAddress: function(address, silent) {
            if (!window.google || !window.google.maps) {
                // Try to load Google Maps API if not loaded
                if (resbs_metabox.map_api_key && typeof window.resbsMapsLoaded === 'undefined') {
                    RESBS_Property_Metabox.loadGoogleMapsAPI();
                    // Retry after API loads
                    setTimeout(function() {
                        RESBS_Property_Metabox.geocodeAddress(address, silent);
                    }, 2000);
                } else if (!silent) {
                    console.warn('⚠️ Google Maps API not loaded');
                }
                return;
            }
            
            var geocoder = new google.maps.Geocoder();
            
            // Show loading indicator
            if (!silent) {
                $('#resbs-geocode-address').prop('disabled', true).text('Geocoding...');
            }
            
            geocoder.geocode({ address: address }, function(results, status) {
                // Re-enable button
                if (!silent) {
                    $('#resbs-geocode-address').prop('disabled', false).text('Get Coordinates from Address');
                }
                
                if (status === 'OK' && results[0]) {
                    var location = results[0].geometry.location;
                    var lat = location.lat();
                    var lng = location.lng();
                    var formattedAddress = results[0].formatted_address;
                    
                    // Update coordinate fields
                    $('#property_latitude').val(lat);
                    $('#property_longitude').val(lng);
                    
                    // Update map preview
                    RESBS_Property_Metabox.initMap(lat, lng);
                    
                    // Log success
                    if (!silent) {
                        console.log('✅ Geocoding successful:', formattedAddress);
                        console.log('📍 Coordinates:', lat, lng);
                    } else {
                        console.log('✅ Auto-geocoded:', lat, lng);
                    }
                    
                    // Show success message briefly
                    if (!silent) {
                        var $button = $('#resbs-geocode-address');
                        var originalText = $button.text();
                        $button.text('✓ Coordinates Updated!').addClass('resbs-btn-success');
                        setTimeout(function() {
                            $button.text(originalText).removeClass('resbs-btn-success');
                        }, 2000);
                    }
                } else {
                    if (!silent) {
                        console.error('❌ Geocoding failed:', status);
                        alert('Could not find coordinates for this address. Please check the address and try again.');
                    } else {
                        console.warn('⚠️ Auto-geocoding failed for:', address);
                    }
                }
            });
        },
        
        /**
         * Load Google Maps API dynamically
         */
        loadGoogleMapsAPI: function() {
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                window.resbsMapsLoaded = true;
                return;
            }
            
            if (window.resbsMapsLoading) {
                return; // Already loading
            }
            
            window.resbsMapsLoading = true;
            var script = document.createElement('script');
            var apiKey = resbs_metabox.map_api_key;
            script.src = 'https://maps.googleapis.com/maps/api/js?key=' + apiKey + '&callback=resbsMapsCallback&libraries=places';
            script.async = true;
            script.defer = true;
            
            // Create callback function
            window.resbsMapsCallback = function() {
                window.resbsMapsLoaded = true;
                window.resbsMapsLoading = false;
                console.log('✅ Google Maps API loaded');
            };
            
            script.onerror = function() {
                window.resbsMapsLoading = false;
                console.error('❌ Failed to load Google Maps API');
            };
            
            document.head.appendChild(script);
        },

        /**
         * Initialize map
         */
        initMap: function(lat, lng) {
            if (!window.google || !window.google.maps) {
                return;
            }
            
            var mapElement = document.getElementById('resbs-map-preview');
            if (!mapElement) {
                return;
            }
            
            // Use existing coordinates or default
            var currentLat = lat || parseFloat($('#property_latitude').val()) || 23.8103;
            var currentLng = lng || parseFloat($('#property_longitude').val()) || 90.4125;
            
            var mapOptions = {
                center: { lat: currentLat, lng: currentLng },
                zoom: 15,
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                mapTypeControl: true,
                streetViewControl: true,
                fullscreenControl: true,
                zoomControl: true
            };
            
            // Clear existing map if any
            if (window.resbsMap) {
                window.resbsMapMarker.setMap(null);
            }
            
            window.resbsMap = new google.maps.Map(mapElement, mapOptions);
            
            // Create or update marker
            if (!window.resbsMapMarker) {
                window.resbsMapMarker = new google.maps.Marker({
                    position: { lat: currentLat, lng: currentLng },
                    map: window.resbsMap,
                    draggable: true,
                    title: 'Property Location',
                    animation: google.maps.Animation.DROP
                });
            } else {
                window.resbsMapMarker.setPosition({ lat: currentLat, lng: currentLng });
                window.resbsMapMarker.setMap(window.resbsMap);
            }
            
            // Update coordinates when marker is dragged
            window.resbsMapMarker.addListener('dragend', function() {
                var position = window.resbsMapMarker.getPosition();
                $('#property_latitude').val(position.lat().toFixed(6));
                $('#property_longitude').val(position.lng().toFixed(6));
                console.log('📍 Marker dragged to:', position.lat(), position.lng());
            });
            
            // Update coordinates when map is clicked
            window.resbsMap.addListener('click', function(event) {
                var clickLat = event.latLng.lat();
                var clickLng = event.latLng.lng();
                
                // Move marker to clicked location
                window.resbsMapMarker.setPosition(event.latLng);
                
                // Update input fields
                $('#property_latitude').val(clickLat.toFixed(6));
                $('#property_longitude').val(clickLng.toFixed(6));
                
                console.log('📍 Map clicked at:', clickLat, clickLng);
            });
            
            // Update map when coordinates are manually changed
            $('#property_latitude, #property_longitude').on('change', function() {
                var newLat = parseFloat($('#property_latitude').val());
                var newLng = parseFloat($('#property_longitude').val());
                
                if (!isNaN(newLat) && !isNaN(newLng) && 
                    newLat >= -90 && newLat <= 90 && 
                    newLng >= -180 && newLng <= 180) {
                    var newPosition = { lat: newLat, lng: newLng };
                    window.resbsMap.setCenter(newPosition);
                    if (window.resbsMapMarker) {
                        window.resbsMapMarker.setPosition(newPosition);
                    }
                }
            });
        },

        /**
         * Initialize form validation
         */
        initFormValidation: function() {
            // Real-time validation
            $('.resbs-input[required], .resbs-select[required]').on('blur', function() {
                RESBS_Property_Metabox.validateField($(this));
            });
            
            // Form submission validation
            $('#post').on('submit', function(e) {
                var isValid = true;
                
                $('.resbs-input[required], .resbs-select[required]').each(function() {
                    if (!RESBS_Property_Metabox.validateField($(this))) {
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    console.log('Please fill in all required fields.');
                }
            });
        },

        /**
         * Validate individual field
         */
        validateField: function($field) {
            var value = $field.val().trim();
            var isValid = true;
            
            if ($field.attr('required') && !value) {
                $field.addClass('error');
                isValid = false;
            } else if ($field.attr('type') === 'email' && value) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    $field.addClass('error');
                    isValid = false;
                } else {
                    $field.removeClass('error');
                }
            } else if ($field.attr('type') === 'url' && value) {
                try {
                    new URL(value);
                    $field.removeClass('error');
                } catch (e) {
                    $field.addClass('error');
                    isValid = false;
                }
            } else {
                $field.removeClass('error');
            }
            
            return isValid;
        },

        /**
         * Initialize auto-save functionality
         */
        initAutoSave: function() {
            var autoSaveTimeout;
            
            $('.resbs-input, .resbs-select, .resbs-textarea').on('input change', function() {
                clearTimeout(autoSaveTimeout);
                
                autoSaveTimeout = setTimeout(function() {
                    RESBS_Property_Metabox.autoSave();
                }, 2000); // Auto-save after 2 seconds of inactivity
            });
        },

        /**
         * Auto-save form data
         */
        autoSave: function() {
            var formData = $('#post').serialize();
            
            $.ajax({
                url: resbs_metabox.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_auto_save_property',
                    form_data: formData,
                    post_id: $('#post_ID').val(),
                    nonce: resbs_metabox.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Show auto-save indicator
                        RESBS_Property_Metabox.showAutoSaveIndicator();
                    }
                }
            });
        },

        /**
         * Show auto-save indicator
         */
        showAutoSaveIndicator: function() {
            var $indicator = $('.resbs-auto-save-indicator');
            
            if ($indicator.length === 0) {
                $indicator = $('<div class="resbs-auto-save-indicator">Auto-saved</div>');
                $('.resbs-metabox-container').prepend($indicator);
            }
            
            $indicator.fadeIn().delay(2000).fadeOut();
        },

        /**
         * Utility function to format currency
         */
        formatCurrency: function(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        },

        /**
         * Utility function to calculate price per sqft
         */
        calculatePricePerSqft: function() {
            var price = parseFloat($('#property_price').val()) || 0;
            var area = parseFloat($('#property_area_sqft').val()) || 0;
            
            if (price > 0 && area > 0) {
                var pricePerSqft = price / area;
                $('#property_price_per_sqft').val(pricePerSqft.toFixed(2));
            }
        },

        /**
         * Initialize enhanced features functionality
         */
        initEnhancedFeatures: function() {
            this.loadExistingFeatures();
            this.loadExistingAmenities();
            this.bindFeatureEvents();
            this.bindAmenityEvents();
        },

        /**
         * Load existing features from the hidden field
         */
        loadExistingFeatures: function() {
            var featuresValue = $('#property_features').val();
            if (featuresValue) {
                var features = featuresValue.split(',').map(function(feature) {
                    return feature.trim();
                }).filter(function(feature) {
                    return feature.length > 0;
                });
                
                features.forEach(function(feature) {
                    this.addFeatureTag(feature);
                }.bind(this));
            }
        },

        /**
         * Load existing amenities from the hidden field
         */
        loadExistingAmenities: function() {
            var amenitiesValue = $('#property_amenities').val();
            if (amenitiesValue) {
                var amenities = amenitiesValue.split(',').map(function(amenity) {
                    return amenity.trim();
                }).filter(function(amenity) {
                    return amenity.length > 0;
                });
                
                amenities.forEach(function(amenity) {
                    this.addAmenityTag(amenity);
                }.bind(this));
            }
        },

        /**
         * Bind events for feature management
         */
        bindFeatureEvents: function() {
            var self = this;
            
            // Handle suggestion tag clicks
            $(document).on('click', '.resbs-suggestion-tag', function() {
                var feature = $(this).data('feature');
                if (feature && !self.featureExists(feature)) {
                    self.addFeatureTag(feature);
                    self.updateHiddenField();
                }
            });
            
            // Handle manual feature input
            $('#add-custom-feature').on('click', function() {
                self.addCustomFeature();
            });
            
            // Handle Enter key in manual input
            $('#property_features_input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.addCustomFeature();
                }
            });
            
            // Handle feature tag removal
            $(document).on('click', '.remove-feature', function(e) {
                e.preventDefault();
                $(this).closest('.resbs-feature-tag').remove();
                self.updateHiddenField();
            });
        },

        /**
         * Bind events for amenity management
         */
        bindAmenityEvents: function() {
            var self = this;
            
            // Handle amenity suggestion tag clicks
            $(document).on('click', '.resbs-suggestion-tag[data-amenity]', function() {
                var amenity = $(this).data('amenity');
                if (amenity && !self.amenityExists(amenity)) {
                    self.addAmenityTag(amenity);
                    self.updateAmenityHiddenField();
                }
            });
            
            // Handle manual amenity input
            $('#add-custom-amenity').on('click', function() {
                self.addCustomAmenity();
            });
            
            // Handle Enter key in manual amenity input
            $('#property_amenities_input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    self.addCustomAmenity();
                }
            });
            
            // Handle amenity tag removal
            $(document).on('click', '.remove-amenity', function(e) {
                e.preventDefault();
                $(this).closest('.resbs-feature-tag').remove();
                self.updateAmenityHiddenField();
            });
        },

        /**
         * Add a feature tag to the container
         */
        addFeatureTag: function(feature) {
            if (!feature || this.featureExists(feature)) {
                return;
            }
            
            var tagHtml = '<div class="resbs-feature-tag">' +
                '<span>' + this.escapeHtml(feature) + '</span>' +
                '<button type="button" class="remove-feature" title="Remove feature">×</button>' +
                '</div>';
            
            $('#feature-tags-container').append(tagHtml);
        },

        /**
         * Add custom feature from manual input
         */
        addCustomFeature: function() {
            var input = $('#property_features_input');
            var feature = this.sanitizeInput(input.val().trim());
            
            if (feature && !this.featureExists(feature)) {
                this.addFeatureTag(feature);
                this.updateHiddenField();
                input.val('');
            }
        },

        /**
         * Add an amenity tag to the container
         */
        addAmenityTag: function(amenity) {
            if (!amenity || this.amenityExists(amenity)) {
                return;
            }
            
            var tagHtml = '<div class="resbs-feature-tag">' +
                '<span>' + this.escapeHtml(amenity) + '</span>' +
                '<button type="button" class="remove-amenity" title="Remove amenity">×</button>' +
                '</div>';
            
            $('#amenity-tags-container').append(tagHtml);
        },

        /**
         * Add custom amenity from manual input
         */
        addCustomAmenity: function() {
            var input = $('#property_amenities_input');
            var amenity = this.sanitizeInput(input.val().trim());
            
            if (amenity && !this.amenityExists(amenity)) {
                this.addAmenityTag(amenity);
                this.updateAmenityHiddenField();
                input.val('');
            }
        },

        /**
         * Check if amenity already exists
         */
        amenityExists: function(amenity) {
            var exists = false;
            $('#amenity-tags-container .resbs-feature-tag').each(function() {
                var existingAmenity = $(this).find('span').text().trim();
                if (existingAmenity.toLowerCase() === amenity.toLowerCase()) {
                    exists = true;
                    return false; // break the loop
                }
            });
            return exists;
        },

        /**
         * Update the hidden amenity field with current amenities
         */
        updateAmenityHiddenField: function() {
            var amenities = [];
            $('#amenity-tags-container .resbs-feature-tag').each(function() {
                var amenity = $(this).find('span').text().trim();
                if (amenity) {
                    amenities.push(amenity);
                }
            });
            $('#property_amenities').val(amenities.join(', '));
        },

        /**
         * Check if feature already exists
         */
        featureExists: function(feature) {
            var exists = false;
            $('#feature-tags-container .resbs-feature-tag').each(function() {
                var existingFeature = $(this).find('span').text().trim();
                if (existingFeature.toLowerCase() === feature.toLowerCase()) {
                    exists = true;
                    return false; // break the loop
                }
            });
            return exists;
        },

        /**
         * Update the hidden field with current features
         */
        updateHiddenField: function() {
            var features = [];
            $('#feature-tags-container .resbs-feature-tag').each(function() {
                var feature = $(this).find('span').text().trim();
                if (feature) {
                    features.push(feature);
                }
            });
            $('#property_features').val(features.join(', '));
        },

        /**
         * Sanitize user input to prevent XSS and unwanted characters
         */
        sanitizeInput: function(input) {
            if (!input) return '';
            
            // Remove HTML tags
            input = input.replace(/<[^>]*>/g, '');
            
            // Remove script tags and javascript: protocols
            input = input.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '');
            input = input.replace(/javascript:/gi, '');
            
            // Remove potentially dangerous characters
            input = input.replace(/[<>'"&]/g, '');
            
            // Limit length to prevent abuse
            if (input.length > 100) {
                input = input.substring(0, 100);
            }
            
            return input.trim();
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml: function(text) {
            if (!text) return '';
            
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };

    // Auto-calculate price per sqft when price or area changes
    $(document).on('input', '#property_price, #property_area_sqft', function() {
        RESBS_Property_Metabox.calculatePricePerSqft();
    });

    // Load Google Maps API if needed
    if (resbs_metabox.map_api_key && !window.google) {
        var script = document.createElement('script');
        script.src = 'https://maps.googleapis.com/maps/api/js?key=' + resbs_metabox.map_api_key + '&libraries=places';
        script.async = true;
        script.defer = true;
        script.onload = function() {
            // Re-initialize map integration after Google Maps loads
            RESBS_Property_Metabox.initMapIntegration();
        };
        document.head.appendChild(script);
    }

})(jQuery);
