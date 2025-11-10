/**
 * Archive JavaScript for Real Estate Properties
 * Handles filtering, sorting, and interactive features
 * 
 * @package RealEstate_Booking_Suite
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize archive functionality
    initArchiveFeatures();
    
    function initArchiveFeatures() {
        // Initialize filters
        initFilters();
        
        // Initialize sorting
        initSorting();
        
        // Initialize view toggles
        initViewToggles();
        
        // Initialize favorites
        initFavorites();
        
        // Initialize quick view
        initQuickView();
        
        // Initialize contact forms
        initContactForms();
        
        // Initialize map (if needed)
        initMap();
    }
    
    /**
     * Initialize filter functionality
     */
    function initFilters() {
        const $filterForm = $('#resbs-filter-form');
        const $clearBtn = $('#resbs-clear-filters');
        
        // Form submission
        $filterForm.on('submit', function(e) {
            e.preventDefault();
            applyFilters();
        });
        
        // Real-time filtering on input change
        $filterForm.find('input, select').on('change', function() {
            // Debounce the filter application
            clearTimeout(window.filterTimeout);
            window.filterTimeout = setTimeout(function() {
                applyFilters();
            }, 500);
        });
        
        // Clear filters
        $clearBtn.on('click', function() {
            clearFilters();
        });
    }
    
    /**
     * Apply filters via AJAX
     */
    function applyFilters() {
        const formData = $('#resbs-filter-form').serialize();
        const sort = $('#resbs-sort-select').val();
        
        showLoading();
        
        $.ajax({
            url: resbs_archive.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_filter_properties',
                nonce: resbs_archive.nonce,
                sort: sort,
                page: 1,
                per_page: getPerPage(),
                form_data: formData
            },
            success: function(response) {
                if (response.success) {
                    updatePropertiesGrid(response.data);
                    updateResultsInfo(response.data);
                    updateURL(formData, sort);
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError('An error occurred while filtering properties.');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    /**
     * Clear all filters
     */
    function clearFilters() {
        $('#resbs-filter-form')[0].reset();
        $('#resbs-sort-select').val('date');
        
        // Redirect to clean archive URL
        window.location.href = getArchiveBaseURL();
    }
    
    /**
     * Update properties grid with new data
     */
    function updatePropertiesGrid(data) {
        const $container = $('#resbs-properties-container');
        
        if (data.html) {
            $container.html(data.html);
            
            // Re-initialize interactive elements
            initFavorites();
            initQuickView();
            initContactForms();
            
            // Scroll to top of results
            $container[0].scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    
    /**
     * Update results information
     */
    function updateResultsInfo(data) {
        const $count = $('#resbs-results-count');
        const count = data.found_posts || 0;
        const text = count === 1 ? 'property found' : 'properties found';
        
        $count.text(count + ' ' + text);
    }
    
    /**
     * Update URL without page reload
     */
    function updateURL(formData, sort) {
        let newURL = getArchiveBaseURL();
        
        if (formData) {
            newURL += '?' + formData;
        }
        
        if (sort && sort !== 'date') {
            newURL += (formData ? '&' : '?') + 'property_sort=' + sort;
        }
        
        history.pushState(null, null, newURL);
    }
    
    /**
     * Initialize sorting functionality
     */
    function initSorting() {
        $('#resbs-sort-select').on('change', function() {
            applyFilters();
        });
    }
    
    /**
     * Initialize view toggles
     */
    function initViewToggles() {
        $('.resbs-view-btn').on('click', function(e) {
            e.preventDefault();
            
            // Update active state
            $('.resbs-view-btn').removeClass('active');
            $(this).addClass('active');
            
            // Apply view change
            const view = $(this).data('view') || $(this).text().toLowerCase().trim();
            changeView(view);
        });
    }
    
    /**
     * Change archive view
     */
    function changeView(view) {
        const $container = $('#resbs-properties-container');
        const $mapContainer = $('#resbs-map-container');
        
        // Update container classes
        $container.removeClass('resbs-layout-grid resbs-layout-list resbs-layout-map');
        $container.addClass('resbs-layout-' + view);
        
        // Show/hide map container
        if (view === 'map') {
            $mapContainer.show();
            initMap();
        } else {
            $mapContainer.hide();
        }
        
        // Update URL
        const newURL = getArchiveBaseURL() + '?archive_view=' + view;
        history.pushState(null, null, newURL);
    }
    
    /**
     * Initialize favorites functionality
     */
    function initFavorites() {
        $('.resbs-favorite-btn').off('click').on('click', function(e) {
            e.preventDefault();
            
            const $btn = $(this);
            const propertyId = $btn.data('property-id');
            const $icon = $btn.find('i');
            
            // Toggle visual state immediately
            $icon.toggleClass('far fas');
            $btn.toggleClass('favorited');
            
            // AJAX call to save favorite
            $.ajax({
                url: resbs_archive.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_toggle_favorite',
                    property_id: propertyId,
                    nonce: resbs_archive.nonce
                },
                success: function(response) {
                    if (response.success) {
                        if (response.data.favorited) {
                            $btn.addClass('favorited');
                            $icon.removeClass('far').addClass('fas');
                            showNotification('Property added to favorites', 'success');
                        } else {
                            $btn.removeClass('favorited');
                            $icon.removeClass('fas').addClass('far');
                            showNotification('Property removed from favorites', 'info');
                        }
                    }
                },
                error: function() {
                    // Revert visual state on error
                    $icon.toggleClass('far fas');
                    $btn.toggleClass('favorited');
                    showError('Failed to update favorites');
                }
            });
        });
    }
    
    /**
     * Initialize quick view functionality
     */
    function initQuickView() {
        $('.resbs-quick-view-btn').off('click').on('click', function(e) {
            e.preventDefault();
            
            const propertyId = $(this).data('property-id');
            openQuickView(propertyId);
        });
    }
    
    /**
     * Open quick view modal
     */
    function openQuickView(propertyId) {
        showLoading();
        
        $.ajax({
            url: resbs_archive.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_get_quick_view',
                property_id: propertyId,
                nonce: resbs_archive.nonce
            },
            success: function(response) {
                if (response.success) {
                    showQuickViewModal(response.data);
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError('Failed to load property details');
            },
            complete: function() {
                hideLoading();
            }
        });
    }
    
    /**
     * Show quick view modal
     */
    function showQuickViewModal(data) {
        const modal = `
            <div id="resbs-quick-view-modal" class="resbs-modal">
                <div class="resbs-modal-content">
                    <div class="resbs-modal-header">
                        <h3>${data.title}</h3>
                        <button class="resbs-modal-close">&times;</button>
                    </div>
                    <div class="resbs-modal-body">
                        ${data.content}
                    </div>
                    <div class="resbs-modal-footer">
                        <a href="${data.permalink}" class="resbs-btn-primary">View Full Details</a>
                        <button class="resbs-btn-secondary resbs-modal-close">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modal);
        
        // Handle modal close
        $('.resbs-modal-close').on('click', function() {
            $('#resbs-quick-view-modal').remove();
        });
        
        // Close on backdrop click
        $('#resbs-quick-view-modal').on('click', function(e) {
            if (e.target === this) {
                $(this).remove();
            }
        });
    }
    
    /**
     * Initialize contact forms
     */
    function initContactForms() {
        $('.resbs-contact-agent-btn').off('click').on('click', function(e) {
            e.preventDefault();
            
            const propertyId = $(this).data('property-id');
            openContactForm(propertyId);
        });
    }
    
    /**
     * Open contact form modal
     */
    function openContactForm(propertyId) {
        const modal = `
            <div id="resbs-contact-modal" class="resbs-modal">
                <div class="resbs-modal-content">
                    <div class="resbs-modal-header">
                        <h3>Contact Agent</h3>
                        <button class="resbs-modal-close">&times;</button>
                    </div>
                    <div class="resbs-modal-body">
                        <form id="resbs-contact-form">
                            <input type="hidden" name="property_id" value="${propertyId}">
                            <div class="resbs-form-group">
                                <label for="contact_name">Your Name *</label>
                                <input type="text" id="contact_name" name="name" required>
                            </div>
                            <div class="resbs-form-group">
                                <label for="contact_email">Email Address *</label>
                                <input type="email" id="contact_email" name="email" required>
                            </div>
                            <div class="resbs-form-group">
                                <label for="contact_phone">Phone Number</label>
                                <input type="tel" id="contact_phone" name="phone">
                            </div>
                            <div class="resbs-form-group">
                                <label for="contact_message">Message *</label>
                                <textarea id="contact_message" name="message" rows="4" required></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="resbs-modal-footer">
                        <button type="submit" form="resbs-contact-form" class="resbs-btn-primary">Send Message</button>
                        <button class="resbs-btn-secondary resbs-modal-close">Cancel</button>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modal);
        
        // Handle form submission
        $('#resbs-contact-form').on('submit', function(e) {
            e.preventDefault();
            submitContactForm();
        });
        
        // Handle modal close
        $('.resbs-modal-close').on('click', function() {
            $('#resbs-contact-modal').remove();
        });
        
        // Close on backdrop click
        $('#resbs-contact-modal').on('click', function(e) {
            if (e.target === this) {
                $(this).remove();
            }
        });
    }
    
    /**
     * Submit contact form
     */
    function submitContactForm() {
        const formData = $('#resbs-contact-form').serialize();
        
        $.ajax({
            url: resbs_archive.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_send_contact_message',
                nonce: resbs_archive.nonce,
                form_data: formData
            },
            success: function(response) {
                if (response.success) {
                    showNotification('Message sent successfully!', 'success');
                    $('#resbs-contact-modal').remove();
                } else {
                    showError(response.data.message);
                }
            },
            error: function() {
                showError('Failed to send message');
            }
        });
    }
    
    /**
     * Initialize map functionality
     */
    function initMap() {
        const $mapContainer = $('#resbs-property-map');
        
        if ($mapContainer.length && typeof google !== 'undefined') {
            // Initialize Google Maps
            const map = new google.maps.Map($mapContainer[0], {
                zoom: 12,
                center: { lat: 25.7617, lng: -80.1918 }, // Miami coordinates
                styles: getMapStyles()
            });
            
            // Add property markers
            addPropertyMarkers(map);
        }
    }
    
    /**
     * Add property markers to map
     */
    function addPropertyMarkers(map) {
        $('.resbs-property-card').each(function() {
            const $card = $(this);
            const propertyId = $card.data('property-id');
            const title = $card.find('.resbs-property-title a').text();
            const price = $card.find('.resbs-property-price').text();
            
            // Get coordinates (you'll need to implement this)
            const coordinates = getPropertyCoordinates(propertyId);
            
            if (coordinates) {
                const marker = new google.maps.Marker({
                    position: coordinates,
                    map: map,
                    title: title,
                    icon: getMarkerIcon($card)
                });
                
                const infoWindow = new google.maps.InfoWindow({
                    content: `
                        <div class="resbs-map-info">
                            <h4>${title}</h4>
                            <p>${price}</p>
                            <a href="${getPropertyURL(propertyId)}">View Details</a>
                        </div>
                    `
                });
                
                marker.addListener('click', function() {
                    infoWindow.open(map, marker);
                });
            }
        });
    }
    
    /**
     * Get marker icon based on property type
     */
    function getMarkerIcon($card) {
        // Return different icons based on property status or type
        return {
            url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                <svg width="40" height="40" viewBox="0 0 40 40">
                    <circle cx="20" cy="20" r="18" fill="#007bff" stroke="white" stroke-width="2"/>
                    <text x="20" y="25" text-anchor="middle" fill="white" font-size="16">🏠</text>
                </svg>
            `),
            scaledSize: new google.maps.Size(40, 40)
        };
    }
    
    /**
     * Get map styles
     */
    function getMapStyles() {
        return [
            {
                featureType: 'all',
                elementType: 'geometry.fill',
                stylers: [{ weight: '2.00' }]
            },
            {
                featureType: 'all',
                elementType: 'geometry.stroke',
                stylers: [{ color: '#9c9c9c' }]
            },
            {
                featureType: 'all',
                elementType: 'labels.text',
                stylers: [{ visibility: 'on' }]
            },
            {
                featureType: 'landscape',
                elementType: 'all',
                stylers: [{ color: '#f2f2f2' }]
            },
            {
                featureType: 'landscape',
                elementType: 'geometry.fill',
                stylers: [{ color: '#ffffff' }]
            },
            {
                featureType: 'landscape.man_made',
                elementType: 'geometry.fill',
                stylers: [{ color: '#ffffff' }]
            },
            {
                featureType: 'poi',
                elementType: 'all',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'road',
                elementType: 'all',
                stylers: [{ saturation: -100 }, { lightness: 45 }]
            },
            {
                featureType: 'road',
                elementType: 'geometry.fill',
                stylers: [{ color: '#eeeeee' }]
            },
            {
                featureType: 'road',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#7b7b7b' }]
            },
            {
                featureType: 'road',
                elementType: 'labels.text.stroke',
                stylers: [{ color: '#ffffff' }]
            },
            {
                featureType: 'road.highway',
                elementType: 'all',
                stylers: [{ visibility: 'simplified' }]
            },
            {
                featureType: 'road.arterial',
                elementType: 'labels.icon',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'transit',
                elementType: 'all',
                stylers: [{ visibility: 'off' }]
            },
            {
                featureType: 'water',
                elementType: 'all',
                stylers: [{ color: '#46bcec' }, { visibility: 'on' }]
            },
            {
                featureType: 'water',
                elementType: 'geometry.fill',
                stylers: [{ color: '#c8d7d4' }]
            },
            {
                featureType: 'water',
                elementType: 'labels.text.fill',
                stylers: [{ color: '#070707' }]
            },
            {
                featureType: 'water',
                elementType: 'labels.text.stroke',
                stylers: [{ color: '#ffffff' }]
            }
        ];
    }
    
    /**
     * Utility functions
     */
    function showLoading() {
        $('#resbs-loading-overlay').show();
    }
    
    function hideLoading() {
        $('#resbs-loading-overlay').hide();
    }
    
    function showError(message) {
        showNotification(message, 'error');
    }
    
    function showNotification(message, type = 'info') {
        // Use toast notification if available, otherwise use old method
        if (typeof showToastNotification === 'function') {
            showToastNotification(message, type);
            return;
        }
        const notification = `
            <div class="resbs-notification resbs-notification-${type}">
                <span>${message}</span>
                <button class="resbs-notification-close">&times;</button>
            </div>
        `;
        
        $('body').append(notification);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.resbs-notification').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
        
        // Manual close
        $('.resbs-notification-close').on('click', function() {
            $(this).parent().fadeOut(function() {
                $(this).remove();
            });
        });
    }
    
    function getPerPage() {
        return parseInt($('#resbs-properties-per-page').val()) || 12;
    }
    
    function getArchiveBaseURL() {
        return window.location.origin + window.location.pathname;
    }
    
    function getPropertyCoordinates(propertyId) {
        // Implement this to get property coordinates
        // For now, return null
        return null;
    }
    
    function getPropertyURL(propertyId) {
        // Implement this to get property URL
        return '#';
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(e) {
        // Reload the page to reflect URL changes
        window.location.reload();
    });
    
    // Initialize on page load
    $(window).on('load', function() {
        // Any additional initialization after page load
        console.log('Archive page loaded');
    });
});