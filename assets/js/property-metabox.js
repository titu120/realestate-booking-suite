/**
 * Professional Property Metabox JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        RESBS_Property_Metabox.init();
    });

    // Property Metabox object
    window.RESBS_Property_Metabox = {
        
        /**
         * Initialize metabox functionality
         */
        init: function() {
            this.initTabs();
            this.initNumberInputs();
            this.initMediaUploader();
            this.initMapIntegration();
            this.initFormValidation();
            this.initAutoSave();
        },

    /**
     * Initialize tabs with enhanced animations
     */
    initTabs: function() {
        // Handle both old and new tab systems
        $('.resbs-tab-btn, .resbs-tab-nav-btn').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var $container = $btn.closest('.resbs-tabs, .resbs-stunning-tabs');
            var tabId = $btn.data('tab');
            
            // Don't switch if already active
            if ($btn.hasClass('active')) {
                return;
            }
            
            // Add loading state
            $btn.addClass('resbs-loading');
            
            // Update active tab button with animation
            $container.find('.resbs-tab-btn, .resbs-tab-nav-btn').removeClass('active');
            $btn.addClass('active');
            
            // Fade out current content
            var $currentContent = $container.find('.resbs-tab-panel.active, .resbs-tab-content.active');
            var $newContent = $container.find('#' + tabId);
            
            if ($currentContent.length) {
                $currentContent.fadeOut(200, function() {
                    $currentContent.removeClass('active');
                    $newContent.addClass('active').hide().fadeIn(300);
                    $btn.removeClass('resbs-loading');
                    
                    // Trigger resize for maps if present
                    if (tabId === 'location' && window.google && window.google.maps) {
                        setTimeout(function() {
                            google.maps.event.trigger(window.resbsMap, 'resize');
                        }, 100);
                    }
                });
            } else {
                $newContent.addClass('active').hide().fadeIn(300);
                $btn.removeClass('resbs-loading');
            }
            
            // Add ripple effect
            RESBS_Property_Metabox.addRippleEffect($btn);
        });
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
                                alert('Error deleting image. Please try again.');
                            }
                        },
                        error: function() {
                            $item.removeClass('resbs-loading');
                            alert('Error deleting image. Please try again.');
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
            
            // Click to upload with ripple effect
            $area.on('click', function(e) {
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
                formData.append('files[]', files[i]);
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
                error: function() {
                    $loadingItem.fadeOut(300, function() {
                        $(this).remove();
                    });
                    RESBS_Property_Metabox.showNotification(resbs_metabox.strings.upload_error, 'error');
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
                return;
            }
            
            // Geocode address button
            $('#resbs-geocode-address').on('click', function() {
                var address = $('#property_address').val();
                var city = $('#property_city').val();
                var state = $('#property_state').val();
                var zip = $('#property_zip').val();
                var country = $('#property_country').val();
                
                var fullAddress = [address, city, state, zip, country].filter(function(part) {
                    return part && part.trim() !== '';
                }).join(', ');
                
                if (fullAddress) {
                    RESBS_Property_Metabox.geocodeAddress(fullAddress);
                }
            });
            
            // Initialize map if coordinates exist
            var lat = $('#property_latitude').val();
            var lng = $('#property_longitude').val();
            
            if (lat && lng) {
                RESBS_Property_Metabox.initMap(parseFloat(lat), parseFloat(lng));
            }
        },

        /**
         * Geocode address
         */
        geocodeAddress: function(address) {
            if (!window.google || !window.google.maps) {
                return;
            }
            
            var geocoder = new google.maps.Geocoder();
            
            geocoder.geocode({ address: address }, function(results, status) {
                if (status === 'OK' && results[0]) {
                    var location = results[0].geometry.location;
                    var lat = location.lat();
                    var lng = location.lng();
                    
                    $('#property_latitude').val(lat);
                    $('#property_longitude').val(lng);
                    
                    RESBS_Property_Metabox.initMap(lat, lng);
                } else {
                    alert(resbs_metabox.strings.geocoding_error);
                }
            });
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
            
            var mapOptions = {
                center: { lat: lat, lng: lng },
                zoom: 15,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };
            
            window.resbsMap = new google.maps.Map(mapElement, mapOptions);
            
            var marker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: window.resbsMap,
                draggable: true,
                title: 'Property Location'
            });
            
            // Update coordinates when marker is dragged
            marker.addListener('dragend', function() {
                var position = marker.getPosition();
                $('#property_latitude').val(position.lat());
                $('#property_longitude').val(position.lng());
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
                    alert('Please fill in all required fields.');
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
