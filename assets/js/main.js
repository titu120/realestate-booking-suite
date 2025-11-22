/* RealEstate Booking Suite - JavaScript */

jQuery(document).ready(function($) {
    // Admin Metabox Gallery Functionality
    if ($('#resbs_add_gallery').length) {
        var galleryFrame;
        
        $('#resbs_add_gallery').on('click', function(e) {
            e.preventDefault();
            
            if (galleryFrame) {
                galleryFrame.open();
                return;
            }
            
            galleryFrame = wp.media({
                title: resbs_ajax.messages.select_gallery || 'Select Gallery Images',
                button: {
                    text: resbs_ajax.messages.add_to_gallery || 'Add to Gallery'
                },
                multiple: true
            });
            
            galleryFrame.on('select', function() {
                var selection = galleryFrame.state().get('selection');
                var galleryIds = $('#resbs_gallery').val().split(',').filter(function(id) { return id !== ''; });
                
                selection.map(function(attachment) {
                    var attachmentData = attachment.toJSON();
                    galleryIds.push(attachmentData.id);
                    
                    var item = $('<li class="resbs-gallery-item" data-id="' + attachmentData.id + '">' +
                        '<img src="' + attachmentData.sizes.thumbnail.url + '" alt="" />' +
                        '<a href="#" class="resbs-remove-image">' + (resbs_ajax.messages.remove || 'Remove') + '</a>' +
                        '</li>');
                    $('#resbs_gallery_list').append(item);
                });
                
                $('#resbs_gallery').val(galleryIds.join(','));
            });
            
            galleryFrame.open();
        });
        
        $(document).on('click', '.resbs-remove-image', function(e) {
            e.preventDefault();
            var item = $(this).closest('.resbs-gallery-item');
            var imageId = item.data('id');
            var galleryIds = $('#resbs_gallery').val().split(',').filter(function(id) { return id !== imageId && id !== ''; });
            
            item.remove();
            $('#resbs_gallery').val(galleryIds.join(','));
        });
    }
    
    // Frontend Property Form Submission
    if ($('#resbs-property-form').length) {
        $('#resbs-property-form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var formData = new FormData(this);
            var submitBtn = form.find('.resbs-submit-btn');
            var messageDiv = $('#resbs-message');
            
            // Disable submit button
            submitBtn.prop('disabled', true).text(resbs_ajax.messages.submitting || 'Submitting...');
            
            $.ajax({
                url: resbs_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        messageDiv.removeClass('resbs-error').addClass('resbs-success').html(response.data).show();
                        form[0].reset();
                    } else {
                        messageDiv.removeClass('resbs-success').addClass('resbs-error').html(response.data).show();
                    }
                },
                error: function() {
                    messageDiv.removeClass('resbs-success').addClass('resbs-error').html(resbs_ajax.messages.error).show();
                },
                complete: function() {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false);
                    if (form.find('input[name="edit_id"]').val() > 0) {
                        submitBtn.text(resbs_ajax.messages.update_property || 'Update Property');
                    } else {
                        submitBtn.text(resbs_ajax.messages.submit_property || 'Submit Property');
                    }
                }
            });
        });
    }
    
    // Search Functionality
    if ($('#resbs-search-form').length) {
        var map = null;
        var markers = [];
        
        $('#resbs-search-form').on('submit', function(e) {
            e.preventDefault();
            performSearch(1);
        });
        
        // Reset form
        $('.resbs-reset-btn').on('click', function() {
            $('#resbs-search-form')[0].reset();
            $('#resbs-results-container').empty();
            $('#resbs-total-results').text('0');
            clearMapMarkers();
        });
        
        // View type toggle
        $('.resbs-view-btn').on('click', function() {
            var viewType = $(this).data('view');
            $('.resbs-view-btn').removeClass('active');
            $(this).addClass('active');
            
            $('#resbs-results-container').removeClass('resbs-grid-view resbs-list-view resbs-map-view');
            $('#resbs-results-container').addClass('resbs-' + viewType + '-view');
            
            if (viewType === 'map') {
                $('#resbs-map-container').show();
                $('#resbs-results-container').hide();
                if (map) {
                    setTimeout(function() {
                        google.maps.event.trigger(map, 'resize');
                    }, 100);
                }
            } else {
                $('#resbs-map-container').hide();
                $('#resbs-results-container').show();
            }
        });
        
        // Pagination
        $(document).on('click', '.resbs-pagination button', function() {
            var page = $(this).data('page');
            if (page) {
                performSearch(page);
            }
        });
        
        function performSearch(page) {
            var form = $('#resbs-search-form');
            var formData = form.serialize();
            formData += '&page=' + page;
            
            // Add current view type
            var currentView = $('.resbs-view-btn.active').data('view');
            formData += '&view_type=' + currentView;
            
            var loadingDiv = $('#resbs-loading');
            var resultsContainer = $('#resbs-results-container');
            
            loadingDiv.show();
            resultsContainer.empty();
            clearMapMarkers();
            
            $.ajax({
                url: resbs_search_ajax.ajax_url,
                type: 'POST',
                data: formData,
                success: function(response) {
                    loadingDiv.hide();
                    
                    if (response.success) {
                        displayResults(response.data);
                        $('#resbs-total-results').text(response.data.total);
                    } else {
                        resultsContainer.html('<div class="resbs-no-results">' + response.data + '</div>');
                        $('#resbs-total-results').text('0');
                    }
                },
                error: function() {
                    loadingDiv.hide();
                    resultsContainer.html('<div class="resbs-no-results">' + resbs_search_ajax.messages.error + '</div>');
                    $('#resbs-total-results').text('0');
                }
            });
        }
        
        function displayResults(data) {
            var resultsContainer = $('#resbs-results-container');
            var currentView = $('.resbs-view-btn.active').data('view');
            var html = '';
            
            if (data.results && data.results.length > 0) {
                if (currentView === 'list') {
                    html = '<div class="resbs-property-list">';
                } else {
                    html = '<div class="resbs-property-grid">';
                }
                
                data.results.forEach(function(property) {
                    html += '<div class="resbs-property-card">';
                    
                    if (property.thumbnail) {
                        html += '<img src="' + property.thumbnail + '" alt="' + property.title + '" class="resbs-property-image">';
                    }
                    
                    html += '<div class="resbs-property-content">';
                    html += '<h3 class="resbs-property-title"><a href="' + property.permalink + '">' + property.title + '</a></h3>';
                    
                    if (property.price) {
                        html += '<div class="resbs-property-price">$' + parseFloat(property.price).toLocaleString() + '</div>';
                    }
                    
                    html += '<div class="resbs-property-details">';
                    if (property.bedrooms) {
                        html += '<span>' + property.bedrooms + ' ' + (property.bedrooms == 1 ? 'Bed' : 'Beds') + '</span>';
                    }
                    if (property.bathrooms) {
                        html += '<span>' + property.bathrooms + ' ' + (property.bathrooms == 1 ? 'Bath' : 'Baths') + '</span>';
                    }
                    if (property.area) {
                        html += '<span>' + property.area + ' sq ft</span>';
                    }
                    html += '</div>';
                    
                    if (property.location && property.location.length > 0) {
                        html += '<div class="resbs-property-location">' + property.location.join(', ') + '</div>';
                    }
                    
                    if (property.amenities) {
                        html += '<div class="resbs-property-amenities">';
                        var amenities = property.amenities.split(',');
                        amenities.forEach(function(amenity) {
                            if (amenity.trim()) {
                                html += '<span class="resbs-amenity-tag">' + amenity.trim() + '</span>';
                            }
                        });
                        html += '</div>';
                    }
                    
                    if (property.excerpt) {
                        html += '<div class="resbs-property-excerpt">' + property.excerpt + '</div>';
                    }
                    
                    html += '</div>';
                    html += '</div>';
                });
                
                html += '</div>';
                
                // Add pagination
                if (data.pages > 1) {
                    html += '<div class="resbs-pagination">';
                    
                    // Previous button
                    if (data.current_page > 1) {
                        html += '<button data-page="' + (data.current_page - 1) + '">Previous</button>';
                    }
                    
                    // Page numbers
                    for (var i = 1; i <= data.pages; i++) {
                        if (i === data.current_page) {
                            html += '<button class="active" disabled>' + i + '</button>';
                        } else {
                            html += '<button data-page="' + i + '">' + i + '</button>';
                        }
                    }
                    
                    // Next button
                    if (data.current_page < data.pages) {
                        html += '<button data-page="' + (data.current_page + 1) + '">Next</button>';
                    }
                    
                    html += '</div>';
                }
                
                // Update map if in map view
                if (currentView === 'map') {
                    updateMap(data.results);
                }
            } else {
                html = '<div class="resbs-no-results">' + resbs_search_ajax.messages.no_results + '</div>';
            }
            
            resultsContainer.html(html);
        }
        
        function updateMap(properties) {
            if (typeof google === 'undefined' || !google.maps) {
                return;
            }
            
            if (!map) {
                map = new google.maps.Map(document.getElementById('resbs-map'), {
                    zoom: 10,
                    center: { lat: 40.7128, lng: -74.0060 } // Default to NYC
                });
            }
            
            clearMapMarkers();
            
            if (properties.length > 0) {
                var bounds = new google.maps.LatLngBounds();
                
                properties.forEach(function(property) {
                    if (property.latitude && property.longitude) {
                        var position = {
                            lat: parseFloat(property.latitude),
                            lng: parseFloat(property.longitude)
                        };
                        
                        var marker = new google.maps.Marker({
                            position: position,
                            map: map,
                            title: property.title
                        });
                        
                        var infoWindow = new google.maps.InfoWindow({
                            content: '<div class="resbs-map-info">' +
                                '<h4><a href="' + property.permalink + '">' + property.title + '</a></h4>' +
                                '<p>$' + parseFloat(property.price).toLocaleString() + '</p>' +
                                '<p>' + property.bedrooms + ' bed, ' + property.bathrooms + ' bath</p>' +
                                '</div>'
                        });
                        
                        marker.addListener('click', function() {
                            infoWindow.open(map, marker);
                        });
                        
                        markers.push(marker);
                        bounds.extend(position);
                    }
                });
                
                if (markers.length > 0) {
                    map.fitBounds(bounds);
                }
            }
        }
        
        function clearMapMarkers() {
            markers.forEach(function(marker) {
                marker.setMap(null);
            });
            markers = [];
        }
    }
    
    // Property Grid Infinite Scroll
    if ($('.resbs-property-grid-container').length) {
        $('.resbs-load-more-btn').on('click', function() {
            var button = $(this);
            var container = button.closest('.resbs-property-grid-container');
            var grid = container.find('.resbs-property-grid');
            var loading = container.find('.resbs-loading-more');
            var page = parseInt(button.data('page'));
            var maxPages = parseInt(button.data('max-pages'));
            
            if (page > maxPages) {
                button.prop('disabled', true).text(resbs_grid_ajax.messages.no_more);
                return;
            }
            
            button.prop('disabled', true);
            loading.show();
            
            var data = {
                action: 'resbs_load_more_properties',
                nonce: resbs_grid_ajax.nonce,
                page: page,
                limit: container.data('limit'),
                columns: container.data('columns'),
                show_badges: 'true',
                show_price: 'true',
                show_meta: 'true',
                show_excerpt: 'false',
                image_size: 'medium'
            };
            
            $.ajax({
                url: resbs_grid_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        grid.append(response.data.html);
                        button.data('page', page + 1);
                        
                        if (page + 1 > maxPages) {
                            button.prop('disabled', true).text(resbs_grid_ajax.messages.no_more);
                        } else {
                            button.prop('disabled', false);
                        }
                    } else {
                        button.prop('disabled', true).text(resbs_grid_ajax.messages.error);
                    }
                },
                error: function() {
                    button.prop('disabled', true).text(resbs_grid_ajax.messages.error);
                },
                complete: function() {
                    loading.hide();
                }
            });
        });
    }
    
    // Single Property Functionality
    if ($('.resbs-single-property').length) {
        // Favorite toggle
        $('.resbs-favorite-btn').on('click', function() {
            var button = $(this);
            var propertyId = button.data('property-id');
            
            $.ajax({
                url: resbs_single_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_toggle_favorite',
                    nonce: resbs_single_ajax.nonce,
                    property_id: propertyId
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.action === 'added') {
                            button.addClass('active');
                            button.find('.resbs-favorite-text').text(resbs_single_ajax.messages.favorite_removed);
                        } else {
                            button.removeClass('active');
                            button.find('.resbs-favorite-text').text(resbs_single_ajax.messages.favorite_added);
                        }
                        
                        // Show success message
                        showMessage(response.data.message, 'success');
                    } else {
                        showMessage(response.data, 'error');
                    }
                },
                error: function() {
                    showMessage(resbs_single_ajax.messages.error, 'error');
                }
            });
        });
        
        // Gallery thumbnail navigation
        $('.resbs-gallery-thumb').on('click', function() {
            var thumb = $(this);
            var thumbIndex = thumb.index();
            var mainImages = $('.resbs-gallery-main .resbs-gallery-item');
            
            // Update active thumbnail
            $('.resbs-gallery-thumb').removeClass('active');
            thumb.addClass('active');
            
            // Scroll to corresponding main image
            if (mainImages.length > thumbIndex) {
                mainImages.eq(thumbIndex)[0].scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }
        });
        
        // Booking form submission
        $('.resbs-booking-submit-btn').on('click', function() {
            var form = $(this).closest('.resbs-booking-form');
            var checkinDate = form.find('#checkin_date').val();
            var checkoutDate = form.find('#checkout_date').val();
            var guests = form.find('#guests').val();
            
            if (!checkinDate || !checkoutDate || !guests) {
                showMessage('Please fill in all required fields.', 'error');
                return;
            }
            
            if (new Date(checkinDate) >= new Date(checkoutDate)) {
                showMessage('Check-out date must be after check-in date.', 'error');
                return;
            }
            
            // Here you would integrate with WooCommerce or booking system
            showMessage('Checking availability...', 'info');
            
            // Simulate booking process
            setTimeout(function() {
                showMessage('Property is available for the selected dates! Redirecting to booking...', 'success');
                // Redirect to WooCommerce product page or booking form
                // window.location.href = '/book-property/?property_id=' + propertyId + '&checkin=' + checkinDate + '&checkout=' + checkoutDate + '&guests=' + guests;
            }, 2000);
        });
        
        // Initialize map if present
        if ($('#resbs-property-map').length && typeof google !== 'undefined' && google.maps) {
            var mapElement = $('#resbs-property-map')[0];
            var lat = parseFloat(mapElement.dataset.lat);
            var lng = parseFloat(mapElement.dataset.lng);
            
            if (lat && lng) {
                var map = new google.maps.Map(mapElement, {
                    zoom: 15,
                    center: { lat: lat, lng: lng }
                });
                
                var marker = new google.maps.Marker({
                    position: { lat: lat, lng: lng },
                    map: map,
                    title: $('.resbs-property-title').text()
                });
            }
        }
        
        function showMessage(message, type) {
            var messageClass = 'resbs-message-' + type;
            var messageHtml = '<div class="resbs-message ' + messageClass + '">' + message + '</div>';
            
            // Remove existing messages
            $('.resbs-message').remove();
            
            // Add new message
            $('.resbs-single-property').prepend(messageHtml);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $('.resbs-message').fadeOut();
            }, 5000);
        }
    }
    
    // WooCommerce Integration - Book Now Button
    $(document).on('click', '.resbs-book-now-btn', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var propertyId = $('.resbs-single-property').data('property-id');
        
        if (!propertyId) {
            showMessage('Property ID not found.', 'error');
            return;
        }
        
        // Check if WooCommerce is available
        if (typeof resbs_wc_ajax === 'undefined') {
            // Simple fallback - show contact info
            showMessage('Booking system not available. Please contact us directly.', 'info');
            setTimeout(function() {
                alert('Please contact us at:\nEmail: info@yoursite.com\nPhone: +1-234-567-8900\n\nProperty: ' + propertyId);
            }, 1000);
            return;
        }
        
        // Simple fallback - redirect to contact page if WooCommerce not working
        if (!resbs_wc_ajax.ajax_url) {
            showMessage('Redirecting to contact page...', 'info');
            setTimeout(function() {
                window.location.href = '/contact/';
            }, 1000);
            return;
        }
        
        // Check if user is logged in
        if (!resbs_wc_ajax.nonce) {
            showMessage('Please login to book properties.', 'error');
            return;
        }
        
        // Get booking form data (use form data if available, otherwise defaults)
        var checkinDate = $('#checkin_date').val() || '';
        var checkoutDate = $('#checkout_date').val() || '';
        var guests = $('#guests').val() || '1';
        
        // If no dates from form, use default dates
        if (!checkinDate) {
            var tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            checkinDate = tomorrow.toISOString().split('T')[0];
        }
        
        if (!checkoutDate) {
            var dayAfter = new Date();
            dayAfter.setDate(dayAfter.getDate() + 2);
            dayAfter.setHours(0, 0, 0, 0);
            checkinDate = new Date(checkinDate);
            if (dayAfter <= checkinDate) {
                dayAfter = new Date(checkinDate);
                dayAfter.setDate(dayAfter.getDate() + 1);
            }
            checkoutDate = dayAfter.toISOString().split('T')[0];
        }
        
        button.prop('disabled', true).text('Adding to Cart...');
        
        $.ajax({
            url: resbs_wc_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_add_to_cart',
                nonce: resbs_wc_ajax.nonce,
                property_id: propertyId,
                checkin_date: checkinDate,
                checkout_date: checkoutDate,
                guests: guests
            },
            success: function(response) {
                if (response.success) {
                    showMessage('Property added to cart! Redirecting to checkout...', 'success');
                    
                    // Redirect to checkout after a short delay
                    setTimeout(function() {
                        window.location.href = resbs_wc_ajax.checkout_url;
                    }, 1500);
                } else {
                    showMessage(response.data || 'Error adding property to cart.', 'error');
                }
            },
            error: function(xhr, status, error) {
                showMessage('Error adding property to cart. Please try again.', 'error');
            },
            complete: function() {
                button.prop('disabled', false).text('Book Now');
            }
        });
    });
    
    // Check Availability Button (in booking form) - Just validates dates
    $(document).on('click', '.resbs-booking-submit-btn', function() {
        var button = $(this);
        var form = button.closest('.resbs-booking-form');
        
        var checkinDate = form.find('#checkin_date').val();
        var checkoutDate = form.find('#checkout_date').val();
        var guests = form.find('#guests').val();
        
        if (!checkinDate || !checkoutDate || !guests) {
            showMessage('Please select check-in and check-out dates.', 'error');
            return;
        }
        
        if (new Date(checkoutDate) <= new Date(checkinDate)) {
            showMessage('Check-out date must be after check-in date.', 'error');
            return;
        }
        
        // Just show that dates are valid - no real availability check needed
        showMessage('Dates are valid! You can now click "Book Now" to proceed with booking.', 'success');
    });
    
    // Property Grid Book Now buttons
    $('.resbs-property-card .resbs-book-now-btn').on('click', function() {
        var propertyCard = $(this).closest('.resbs-property-card');
        var propertyId = propertyCard.data('property-id');
        
        // Create a simple booking form or redirect to property page
        if (propertyId) {
            window.location.href = '/property/' + propertyId + '/?action=book';
        }
    });
    
    function showMessage(message, type) {
        var messageClass = 'resbs-message-' + type;
        var messageHtml = '<div class="resbs-message ' + messageClass + '">' + message + '</div>';
        
        // Remove existing messages
        $('.resbs-message').remove();
        
        // Add new message
        $('.resbs-single-property, .resbs-property-grid-container').prepend(messageHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.resbs-message').fadeOut();
        }, 5000);
    }
});
