/* Elementor Listings Widget JavaScript */

jQuery(document).ready(function($) {
    'use strict';

    // Initialize all listings widgets
    $('.resbs-listings-widget').each(function() {
        initListingsWidget($(this));
    });

    /**
     * Initialize listings widget
     */
    function initListingsWidget($widget) {
        var widgetId = $widget.attr('id');
        var settings = $widget.data('settings') || {};
        var layout = settings.layout || 'grid';
        var columns = $widget.data('columns') || 3;
        var gridGap = $widget.data('grid-gap') || '1.5rem';
        
        var listingsMap = null;
        var mapInitialized = false;
        var mapMarkers = [];
        
        // Load Leaflet.js if not already loaded
        function loadLeaflet() {
            if (typeof L !== 'undefined') {
                return Promise.resolve();
            }
            
            return new Promise(function(resolve, reject) {
                // Check if Leaflet CSS is loaded
                if (!$('link[href*="leaflet.css"]').length) {
                    var cssLink = document.createElement('link');
                    cssLink.rel = 'stylesheet';
                    cssLink.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                    cssLink.crossOrigin = 'anonymous';
                    document.head.appendChild(cssLink);
                }
                
                // Check if Leaflet JS is loaded
                if ($('script[src*="leaflet.js"]').length === 0) {
                    var script = document.createElement('script');
                    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                    script.crossOrigin = 'anonymous';
                    script.async = true;
                    script.onload = function() {
                        // Fix Leaflet icon paths
                        delete L.Icon.Default.prototype._getIconUrl;
                        L.Icon.Default.mergeOptions({
                            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png'
                        });
                        resolve();
                    };
                    script.onerror = function() {
                        // Try alternate CDN
                        var altScript = document.createElement('script');
                        altScript.src = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js';
                        altScript.crossOrigin = 'anonymous';
                        altScript.async = true;
                        altScript.onload = function() {
                            delete L.Icon.Default.prototype._getIconUrl;
                            L.Icon.Default.mergeOptions({
                                iconUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-icon.png',
                                shadowUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-shadow.png',
                                iconRetinaUrl: 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/images/marker-icon-2x.png'
                            });
                            resolve();
                        };
                        altScript.onerror = reject;
                        document.head.appendChild(altScript);
                    };
                    document.head.appendChild(script);
                } else {
                    // Already loading, wait for it
                    var checkLeaflet = setInterval(function() {
                        if (typeof L !== 'undefined') {
                            clearInterval(checkLeaflet);
                            resolve();
                        }
                    }, 100);
                    setTimeout(function() {
                        clearInterval(checkLeaflet);
                        if (typeof L === 'undefined') {
                            reject(new Error('Leaflet.js failed to load'));
                        }
                    }, 10000);
                }
            });
        }
        
        // Initialize map when needed
        function initMapIfNeeded() {
            if (mapInitialized || !$('#' + widgetId + ' .resbs-listings-map-view').is(':visible')) {
                return;
            }
            
            var mapId = 'resbs-map-canvas-' + widgetId;
            var mapContainer = document.getElementById(mapId);
            var $mapData = $('#' + widgetId + ' .resbs-map-data');
            var mapData = $mapData.length ? $mapData.text() : '[]';
            
            if (!mapContainer) {
                return;
            }
            
            try {
                var properties = JSON.parse(mapData);
                
                loadLeaflet().then(function() {
                    initializeListingsMap(mapContainer, properties, widgetId);
                }).catch(function(error) {
                    mapContainer.innerHTML = '<div style="padding: 2rem; text-align: center; color: #6b7280;"><p>Map library failed to load. Please refresh the page.</p></div>';
                });
            } catch (e) {
                // Error parsing map data
            }
        }
        
        function initializeListingsMap(container, properties, widgetId) {
            if (!properties || properties.length === 0) {
                container.innerHTML = '<div style="padding: 2rem; text-align: center; color: #6b7280;"><p>No properties with location data found.</p></div>';
                return;
            }
            
            // Calculate center
            var centerLat = 0;
            var centerLng = 0;
            properties.forEach(function(prop) {
                centerLat += prop.lat;
                centerLng += prop.lng;
            });
            centerLat = centerLat / properties.length;
            centerLng = centerLng / properties.length;
            
            // Clear existing map if any
            if (listingsMap) {
                listingsMap.remove();
                mapMarkers = [];
            }
            
            // Create Leaflet map with OpenStreetMap tiles
            listingsMap = L.map(container, {
                center: [centerLat, centerLng],
                zoom: properties.length === 1 ? 15 : 10,
                zoomControl: true
            });
            
            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 19
            }).addTo(listingsMap);
            
            // Create bounds for fitting
            var bounds = [];
            
            // Create custom marker icon
            var markerIcon = L.divIcon({
                className: 'leaflet-marker-custom',
                html: '<div style="width: 20px; height: 20px; border-radius: 50%; background-color: #ef4444; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
            
            // Helper function to escape HTML entities in JavaScript
            function escapeHtml(text) {
                var map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
            }
            
            // Add markers for each property
            properties.forEach(function(property) {
                var position = [property.lat, property.lng];
                bounds.push(position);
                
                var price = property.price ? '$' + parseFloat(property.price).toLocaleString() : 'Price on request';
                var popupContent = '<div style="min-width: 200px; padding: 0.75rem;">' +
                    '<h4 style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 700;"><a href="' + escapeHtml(property.permalink) + '" style="color: #111827; text-decoration: none;">' + escapeHtml(property.title) + '</a></h4>' +
                    '<p style="margin: 0 0 0.5rem 0; color: #10b981; font-weight: 700; font-size: 1.125rem;">' + escapeHtml(price) + '</p>';
                
                if (property.bedrooms || property.bathrooms) {
                    popupContent += '<p style="margin: 0; color: #6b7280; font-size: 0.875rem;">';
                    if (property.bedrooms) popupContent += escapeHtml(String(property.bedrooms)) + ' Bed' + (property.bedrooms != 1 ? 's' : '') + ' ';
                    if (property.bathrooms) popupContent += escapeHtml(String(property.bathrooms)) + ' Bath' + (property.bathrooms != 1 ? 's' : '');
                    popupContent += '</p>';
                }
                
                popupContent += '</div>';
                
                var marker = L.marker(position, {
                    icon: markerIcon
                }).addTo(listingsMap);
                
                marker.bindPopup(popupContent, {
                    maxWidth: 300,
                    className: 'resbs-listings-popup'
                });
                
                mapMarkers.push(marker);
            });
            
            // Fit bounds if multiple properties
            if (properties.length > 1 && bounds.length > 0) {
                listingsMap.fitBounds(bounds, {
                    padding: [20, 20],
                    maxZoom: 16
                });
            }
            
            mapInitialized = true;
        }
        
        // Handle view toggle
        $widget.find('.resbs-view-btn').on('click', function() {
            var view = $(this).data('view');
            var $content = $widget.find('.resbs-listings-content');
            
            // Update active button
            $widget.find('.resbs-view-btn').removeClass('active');
            $(this).addClass('active');
            
            // Show/hide views
            if (view === 'map') {
                $widget.find('.resbs-listings-grid-view').hide();
                $widget.find('.resbs-listings-map-view').show();
                $widget.find('.resbs-listings-pagination').hide();
                setTimeout(function() {
                    initMapIfNeeded();
                    if (listingsMap) {
                        listingsMap.invalidateSize();
                    }
                }, 100);
            } else {
                $widget.find('.resbs-listings-map-view').hide();
                $widget.find('.resbs-listings-grid-view').show();
                
                // Show/hide pagination
                $widget.find('.resbs-listings-pagination').show();
                
                // Update container layout class
                var $container = $widget.find('.resbs-properties-container');
                if (view === 'grid') {
                    $container.attr('style', 'grid-template-columns: repeat(' + columns + ', 1fr); gap: ' + gridGap + ';');
                } else {
                    $container.attr('style', '');
                }
            }
        });
        
        // Initialize map if default view is map
        if (layout === 'map') {
            initMapIfNeeded();
        }
        
        // Prevent Quick View and Favorite buttons from being added
        $widget.find('.resbs-property-card').off('mouseenter').on('mouseenter', function(e) {
            e.stopPropagation();
        });
        
        // Remove any existing Quick View and Favorite buttons
        $widget.find('.resbs-quickview-btn, .resbs-quickview-trigger, .resbs-quick-view-btn, .resbs-favorite-btn').remove();
        
        // Monitor for dynamically added Quick View buttons
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        if ($(node).hasClass('resbs-quickview-btn') || 
                            $(node).hasClass('resbs-quickview-trigger') || 
                            $(node).hasClass('resbs-quick-view-btn') ||
                            $(node).hasClass('resbs-favorite-btn') ||
                            $(node).find('.resbs-quickview-btn, .resbs-quickview-trigger, .resbs-quick-view-btn, .resbs-favorite-btn').length > 0) {
                            $(node).remove();
                        }
                    }
                });
            });
        });
        
        observer.observe($widget[0], {
            childList: true,
            subtree: true
        });
    }
});

