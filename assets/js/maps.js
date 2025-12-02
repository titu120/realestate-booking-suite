/**
 * Google Maps Integration JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Global variables
    let maps = {};
    let markers = {};
    let markerClusters = {};
    let infoWindows = {};
    let currentFilters = {};

    /**
     * Initialize maps when document is ready
     */
    $(document).ready(function() {
        initializeMaps();
    });

    /**
     * Initialize all maps on the page
     */
    function initializeMaps() {
        $('.resbs-property-map').each(function() {
            const $mapContainer = $(this);
            const mapId = $mapContainer.attr('id');
            
            if (mapId && !maps[mapId]) {
                initializeMap($mapContainer);
            }
        });
    }

    /**
     * Initialize a single map
     */
    function initializeMap($mapContainer) {
        const mapId = $mapContainer.attr('id');
        const lat = parseFloat($mapContainer.data('lat')) || resbs_maps_ajax.default_lat;
        const lng = parseFloat($mapContainer.data('lng')) || resbs_maps_ajax.default_lng;
        const zoom = parseInt($mapContainer.data('zoom')) || resbs_maps_ajax.default_zoom;
        const clusterMarkers = $mapContainer.data('cluster') === 'true';
        const isSingle = $mapContainer.data('single') === 'true';

        // Show loading state
        showMapLoading($mapContainer);

        // Initialize map
        const mapOptions = {
            center: { lat: lat, lng: lng },
            zoom: zoom,
            mapTypeId: getMapTypeId(),
            styles: getMapStyles(),
            mapTypeControl: true,
            streetViewControl: true,
            fullscreenControl: true,
            zoomControl: true,
            gestureHandling: 'greedy'
        };

        const map = new google.maps.Map($mapContainer[0], mapOptions);
        maps[mapId] = map;
        markers[mapId] = [];
        infoWindows[mapId] = [];

        // Initialize marker clustering if enabled
        if (clusterMarkers && !isSingle) {
            markerClusters[mapId] = new MarkerClusterer(map, [], {
                imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
                maxZoom: 15,
                gridSize: 50,
                styles: getClusterStyles()
            });
        }

        // Initialize map controls
        initializeMapControls($mapContainer, mapId);

        // Load properties
        if (isSingle) {
            loadSingleProperty($mapContainer, mapId);
        } else {
            loadMapProperties($mapContainer, mapId);
        }

        // Hide loading state
        hideMapLoading($mapContainer);

        // Add map event listeners
        addMapEventListeners(map, mapId);
    }

    /**
     * Initialize map controls
     */
    function initializeMapControls($mapContainer, mapId) {
        const $controls = $mapContainer.siblings('.resbs-map-controls');
        
        if ($controls.length === 0) {
            return;
        }

        // Initialize search functionality
        const $searchInput = $controls.find('.resbs-map-search-input');
        const $searchBtn = $controls.find('.resbs-map-search-btn');
        
        if ($searchInput.length && $searchBtn.length) {
            initializeSearch($searchInput, $searchBtn, mapId);
        }

        // Initialize filters
        const $filterForm = $controls.find('.resbs-map-filter-form');
        const $resetBtn = $controls.find('.resbs-map-reset-btn');
        
        if ($filterForm.length) {
            initializeFilters($filterForm, $resetBtn, mapId);
        }
    }

    /**
     * Initialize search functionality
     */
    function initializeSearch($searchInput, $searchBtn, mapId) {
        // Initialize Google Places Autocomplete
        const autocomplete = new google.maps.places.Autocomplete($searchInput[0], {
            types: ['geocode', 'establishment'],
            componentRestrictions: { country: 'us' } // Adjust as needed
        });

        // Handle place selection
        autocomplete.addListener('place_changed', function() {
            const place = autocomplete.getPlace();
            
            if (place.geometry) {
                const map = maps[mapId];
                map.setCenter(place.geometry.location);
                map.setZoom(15);
                
                // Load properties for new location
                loadMapProperties($('#' + mapId), mapId);
            }
        });

        // Handle search button click
        $searchBtn.on('click', function(e) {
            e.preventDefault();
            performLocationSearch($searchInput.val(), mapId);
        });

        // Handle Enter key
        $searchInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                performLocationSearch($(this).val(), mapId);
            }
        });
    }

    /**
     * Perform location search
     */
    function performLocationSearch(query, mapId) {
        if (!query.trim()) {
            return;
        }

        showMapLoading($('#' + mapId));

        $.ajax({
            url: resbs_maps_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_search_map_area',
                search_query: query,
                nonce: resbs_maps_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const map = maps[mapId];
                    const location = new google.maps.LatLng(response.data.lat, response.data.lng);
                    
                    map.setCenter(location);
                    map.setZoom(15);
                    
                    // Load properties for new location
                    loadMapProperties($('#' + mapId), mapId);
                } else {
                    showMapError($('#' + mapId), response.data.message);
                }
            },
            error: function() {
                showMapError($('#' + mapId), resbs_maps_ajax.messages.error);
            },
            complete: function() {
                hideMapLoading($('#' + mapId));
            }
        });
    }

    /**
     * Initialize filters
     */
    function initializeFilters($filterForm, $resetBtn, mapId) {
        // Handle form submission
        $filterForm.on('submit', function(e) {
            e.preventDefault();
            applyFilters($(this), mapId);
        });

        // Handle reset button
        if ($resetBtn.length) {
            $resetBtn.on('click', function(e) {
                e.preventDefault();
                resetFilters($filterForm, mapId);
            });
        }

        // Handle filter changes
        $filterForm.find('.resbs-map-filter').on('change', function() {
            applyFilters($filterForm, mapId);
        });
    }

    /**
     * Apply filters
     */
    function applyFilters($form, mapId) {
        const filters = {
            property_type: $form.find('[name="property_type"]').val(),
            property_status: $form.find('[name="property_status"]').val(),
            price_min: $form.find('[name="price_min"]').val(),
            price_max: $form.find('[name="price_max"]').val(),
            bedrooms: $form.find('[name="bedrooms"]').val(),
            bathrooms: $form.find('[name="bathrooms"]').val()
        };

        currentFilters[mapId] = filters;
        loadMapProperties($('#' + mapId), mapId);
    }

    /**
     * Reset filters
     */
    function resetFilters($form, mapId) {
        $form[0].reset();
        currentFilters[mapId] = {};
        loadMapProperties($('#' + mapId), mapId);
    }

    /**
     * Load properties for map
     */
    function loadMapProperties($mapContainer, mapId) {
        const map = maps[mapId];
        const bounds = map.getBounds();
        
        if (!bounds) {
            return;
        }

        const ne = bounds.getNorthEast();
        const sw = bounds.getSouthWest();

        const data = {
            action: 'resbs_get_map_properties',
            north: ne.lat(),
            south: sw.lat(),
            east: ne.lng(),
            west: sw.lng(),
            nonce: resbs_maps_ajax.nonce
        };

        // Add current filters
        if (currentFilters[mapId]) {
            Object.assign(data, currentFilters[mapId]);
        }

        $.ajax({
            url: resbs_maps_ajax.ajax_url,
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    displayMapProperties(response.data.properties, mapId);
                } else {
                    showMapError($mapContainer, response.data.message);
                }
            },
            error: function() {
                showMapError($mapContainer, resbs_maps_ajax.messages.error);
            }
        });
    }

    /**
     * Load single property
     */
    function loadSingleProperty($mapContainer, mapId) {
        const lat = parseFloat($mapContainer.data('lat'));
        const lng = parseFloat($mapContainer.data('lng'));
        
        if (lat && lng) {
            const map = maps[mapId];
            const position = new google.maps.LatLng(lat, lng);
            
            // Create marker
            const marker = new google.maps.Marker({
                position: position,
                map: map,
                title: resbs_maps_ajax.messages.view_property,
                icon: getMarkerIcon()
            });

            markers[mapId].push(marker);
            
            // Center map on property
            map.setCenter(position);
        }
    }

    /**
     * Display properties on map
     */
    function displayMapProperties(properties, mapId) {
        const map = maps[mapId];
        
        // Clear existing markers
        clearMapMarkers(mapId);

        if (properties.length === 0) {
            showNoPropertiesMessage(mapId);
            return;
        }

        // Create markers for each property
        const newMarkers = [];
        
        properties.forEach(function(property) {
            const marker = createPropertyMarker(property, mapId);
            if (marker) {
                newMarkers.push(marker);
            }
        });

        // Add markers to map or cluster
        if (markerClusters[mapId]) {
            markerClusters[mapId].addMarkers(newMarkers);
        } else {
            newMarkers.forEach(function(marker) {
                marker.setMap(map);
            });
        }

        markers[mapId] = newMarkers;
    }

    /**
     * Create property marker
     */
    function createPropertyMarker(property, mapId) {
        const position = new google.maps.LatLng(property.latitude, property.longitude);
        
        const marker = new google.maps.Marker({
            position: position,
            title: property.title,
            icon: getMarkerIcon(property),
            animation: google.maps.Animation.DROP
        });

        // Create info window
        const infoWindow = new google.maps.InfoWindow({
            content: createInfoWindowContent(property)
        });

        infoWindows[mapId].push(infoWindow);

        // Add click listener
        marker.addListener('click', function() {
            // Close other info windows
            infoWindows[mapId].forEach(function(iw) {
                iw.close();
            });
            
            // Open this info window
            infoWindow.open(maps[mapId], marker);
        });

        return marker;
    }

    /**
     * Create info window content
     */
    function createInfoWindowContent(property) {
        const badges = getPropertyBadges(property);
        const price = formatPrice(property.price);
        const meta = getPropertyMeta(property);
        
        return `
            <div class="resbs-info-window">
                <div class="resbs-info-window-content">
                    ${property.featured_image ? `
                        <img src="${property.featured_image}" 
                             alt="${property.title}" 
                             class="resbs-info-window-image">
                    ` : ''}
                    
                    <div class="resbs-info-window-body">
                        <h4 class="resbs-info-window-title">
                            <a href="${property.permalink}">${property.title}</a>
                        </h4>
                        
                        <div class="resbs-info-window-price">${price}</div>
                        
                        <div class="resbs-info-window-meta">
                            ${meta}
                        </div>
                        
                        ${badges ? `<div class="resbs-info-window-badges">${badges}</div>` : ''}
                        
                        <div class="resbs-info-window-actions">
                            <a href="${property.permalink}" 
                               class="resbs-info-window-btn resbs-info-window-btn-primary">
                                <span class="dashicons dashicons-visibility"></span>
                                ${resbs_maps_ajax.messages.view_property}
                            </a>
                            
                            <button type="button" 
                                    class="resbs-info-window-btn resbs-info-window-btn-outline resbs-favorite-btn"
                                    data-property-id="${property.id}">
                                <span class="dashicons dashicons-heart"></span>
                                ${resbs_maps_ajax.messages.add_to_favorites}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Get property badges
     */
    function getPropertyBadges(property) {
        const badges = [];
        
        if (property.featured) {
            badges.push('<span class="resbs-info-window-badge resbs-info-window-badge-featured">' + 
                       resbs_maps_ajax.messages.featured + '</span>');
        }
        
        if (property.new) {
            badges.push('<span class="resbs-info-window-badge resbs-info-window-badge-new">' + 
                       resbs_maps_ajax.messages.new + '</span>');
        }
        
        if (property.sold) {
            badges.push('<span class="resbs-info-window-badge resbs-info-window-badge-sold">' + 
                       resbs_maps_ajax.messages.sold + '</span>');
        }
        
        return badges.join('');
    }

    /**
     * Get property meta information
     */
    function getPropertyMeta(property) {
        const meta = [];
        
        if (property.bedrooms) {
            meta.push(`
                <div class="resbs-info-window-meta-item">
                    <span class="dashicons dashicons-bed-alt"></span>
                    ${property.bedrooms} ${resbs_maps_ajax.messages.bedrooms}
                </div>
            `);
        }
        
        if (property.bathrooms) {
            meta.push(`
                <div class="resbs-info-window-meta-item">
                    <span class="dashicons dashicons-bath"></span>
                    ${property.bathrooms} ${resbs_maps_ajax.messages.bathrooms}
                </div>
            `);
        }
        
        if (property.area) {
            meta.push(`
                <div class="resbs-info-window-meta-item">
                    <span class="dashicons dashicons-fullscreen-alt"></span>
                    ${property.area} ${resbs_maps_ajax.messages.sqft}
                </div>
            `);
        }
        
        return meta.join('');
    }

    /**
     * Format price with dynamic currency
     */
    function formatPrice(price) {
        if (!price) return '';
        
        const numPrice = parseInt(price);
        if (isNaN(numPrice)) return price;
        
        // Get currency from localized script (if available)
        const currencySymbol = (typeof resbs_maps_ajax !== 'undefined' && resbs_maps_ajax.currency_symbol) 
            ? resbs_maps_ajax.currency_symbol 
            : '$';
        const currencyCode = (typeof resbs_maps_ajax !== 'undefined' && resbs_maps_ajax.currency_code) 
            ? resbs_maps_ajax.currency_code 
            : 'USD';
        
        // Format number with thousand separators
        const formattedNumber = numPrice.toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        });
        
        // Return with currency symbol on the left (default)
        return currencySymbol + formattedNumber;
    }

    /**
     * Get marker icon
     */
    function getMarkerIcon(property) {
        if (resbs_maps_ajax.marker_icon) {
            return {
                url: resbs_maps_ajax.marker_icon,
                scaledSize: new google.maps.Size(32, 32),
                anchor: new google.maps.Point(16, 32)
            };
        }
        
        // Default marker with property status color
        if (property && property.property_status && property.property_status.length > 0) {
            const status = property.property_status[0].toLowerCase();
            const colors = {
                'for-sale': '#28a745',
                'for-rent': '#007cba',
                'sold': '#dc3545',
                'rented': '#6c757d'
            };
            
            return {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: colors[status] || '#007cba',
                fillOpacity: 1,
                strokeColor: '#fff',
                strokeWeight: 2,
                scale: 8
            };
        }
        
        return null; // Use default marker
    }

    /**
     * Get map type ID
     */
    function getMapTypeId() {
        const style = resbs_maps_ajax.map_style || 'default';
        const mapTypes = {
            'default': google.maps.MapTypeId.ROADMAP,
            'satellite': google.maps.MapTypeId.SATELLITE,
            'hybrid': google.maps.MapTypeId.HYBRID,
            'terrain': google.maps.MapTypeId.TERRAIN
        };
        
        return mapTypes[style] || google.maps.MapTypeId.ROADMAP;
    }

    /**
     * Get map styles
     */
    function getMapStyles() {
        // Add custom map styles here if needed
        return [];
    }

    /**
     * Get cluster styles
     */
    function getClusterStyles() {
        return [
            {
                textColor: 'white',
                url: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m1.png',
                height: 53,
                width: 53
            },
            {
                textColor: 'white',
                url: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m2.png',
                height: 56,
                width: 56
            },
            {
                textColor: 'white',
                url: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m3.png',
                height: 66,
                width: 66
            }
        ];
    }

    /**
     * Add map event listeners
     */
    function addMapEventListeners(map, mapId) {
        // Listen for bounds changes
        map.addListener('bounds_changed', function() {
            clearTimeout(map.boundsTimeout);
            map.boundsTimeout = setTimeout(function() {
                loadMapProperties($('#' + mapId), mapId);
            }, 500);
        });

        // Listen for zoom changes
        map.addListener('zoom_changed', function() {
            clearTimeout(map.zoomTimeout);
            map.zoomTimeout = setTimeout(function() {
                loadMapProperties($('#' + mapId), mapId);
            }, 500);
        });
    }

    /**
     * Clear map markers
     */
    function clearMapMarkers(mapId) {
        if (markerClusters[mapId]) {
            markerClusters[mapId].clearMarkers();
        } else {
            markers[mapId].forEach(function(marker) {
                marker.setMap(null);
            });
        }
        
        markers[mapId] = [];
        infoWindows[mapId] = [];
    }

    /**
     * Show map loading state
     */
    function showMapLoading($mapContainer) {
        $mapContainer.addClass('resbs-map-loading-overlay');
    }

    /**
     * Hide map loading state
     */
    function hideMapLoading($mapContainer) {
        $mapContainer.removeClass('resbs-map-loading-overlay');
    }

    /**
     * Show map error
     */
    function showMapError($mapContainer, message) {
        $mapContainer.addClass('resbs-map-error-overlay');
        $mapContainer.html(`
            <div class="resbs-map-error">
                <div class="resbs-map-error-icon">⚠️</div>
                <div class="resbs-map-error-message">${message}</div>
                <button type="button" class="resbs-map-error-btn" onclick="location.reload()">
                    ${resbs_maps_ajax.messages.retry || 'Retry'}
                </button>
            </div>
        `);
    }

    /**
     * Show no properties message
     */
    function showNoPropertiesMessage(mapId) {
        const map = maps[mapId];
        const center = map.getCenter();
        
        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div class="resbs-info-window">
                    <div class="resbs-info-window-content">
                        <div class="resbs-info-window-body">
                            <div class="resbs-info-window-message">
                                ${resbs_maps_ajax.messages.no_properties}
                            </div>
                        </div>
                    </div>
                </div>
            `,
            position: center
        });
        
        infoWindow.open(map);
        
        // Close after 3 seconds
        setTimeout(function() {
            infoWindow.close();
        }, 3000);
    }

    /**
     * Handle favorite button clicks
     */
    $(document).on('click', '.resbs-favorite-btn', function(e) {
        e.preventDefault();
        
        const $btn = $(this);
        const propertyId = $btn.data('property-id');
        
        if (!propertyId) {
            return;
        }
        
        // Toggle favorite
        $.ajax({
            url: resbs_maps_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_toggle_favorite',
                property_id: propertyId,
                nonce: resbs_maps_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const isFavorite = response.data.is_favorite;
                    const $icon = $btn.find('.dashicons');
                    const $text = $btn.contents().filter(function() {
                        return this.nodeType === 3;
                    });
                    
                    if (isFavorite) {
                        $icon.removeClass('dashicons-heart').addClass('dashicons-heart-filled');
                        $text[0].textContent = ' ' + resbs_maps_ajax.messages.remove_from_favorites;
                        $btn.addClass('favorited');
                    } else {
                        $icon.removeClass('dashicons-heart-filled').addClass('dashicons-heart');
                        $text[0].textContent = ' ' + resbs_maps_ajax.messages.add_to_favorites;
                        $btn.removeClass('favorited');
                    }
                }
            },
            error: function() {
                // Failed to toggle favorite
            }
        });
    });

    /**
     * Handle window resize
     */
    $(window).on('resize', function() {
        Object.keys(maps).forEach(function(mapId) {
            const map = maps[mapId];
            if (map) {
                google.maps.event.trigger(map, 'resize');
            }
        });
    });

    /**
     * Public API
     */
    window.RESBSMaps = {
        initializeMap: initializeMap,
        loadMapProperties: loadMapProperties,
        clearMapMarkers: clearMapMarkers,
        getMap: function(mapId) {
            return maps[mapId];
        },
        getMarkers: function(mapId) {
            return markers[mapId] || [];
        }
    };

})(jQuery);
