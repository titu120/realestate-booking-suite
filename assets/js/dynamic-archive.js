/**
 * Dynamic Archive AJAX JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // AJAX Search Functionality
    var searchForm = $('.search-container');
    var propertyGrid = $('#propertyGrid');
    var resultsCount = $('#resultsCount');
    var mapMarkers = $('#mapMarkers');
    var isLoading = false;
    
    // Handle form submission with AJAX - DISABLED to allow price filter to work
    // searchForm.on('submit', function(e) {
    //     // Check if this is a price filter submission
    //     var isPriceFilter = $(e.target).find('input[name="min_price"], input[name="max_price"]').length > 0;
    //     
    //     if (isPriceFilter) {
    //         // Allow normal form submission for price filters
    //         return true;
    //     }
    //     
    //     // Prevent default for other filters and use AJAX
    //     e.preventDefault();
    //     
    //     if (isLoading) return;
    //     
    //     performSearch();
    // });
    
    // Handle filter changes
    $('.filter-option').on('click', function() {
        if (isLoading) return;
        
        performSearch();
    });
    
    // Handle sort changes
    $('.sort-select').on('change', function() {
        if (isLoading) return;
        
        performSearch();
    });
    
    // Handle radio button changes for property type
    $('input[name="property_type"]').on('change', function() {
        if (isLoading) return;
        
        performSearch();
    });
    
    // Perform AJAX search
    function performSearch() {
        isLoading = true;
        
        // Show loading state
        showLoadingState();
        
        // Collect form data
        var formData = {
            action: 'resbs_search_properties',
            nonce: resbs_ajax.nonce,
            search: $('#searchInput').val(),
            min_price: $('input[name="min_price"]').val(),
            max_price: $('input[name="max_price"]').val(),
            property_type: $('input[name="property_type"]:checked').val(),
            bedrooms: $('input[name="bedrooms"]').val(),
            bathrooms: $('input[name="bathrooms"]').val(),
            min_sqft: $('input[name="min_sqft"]').val(),
            max_sqft: $('input[name="max_sqft"]').val(),
            year_built: $('select[name="year_built"]').val(),
            property_status: $('select[name="property_status"]').val(),
            sort_by: $('.sort-select').val(),
            paged: 1
        };
        
        // Perform AJAX request
        $.ajax({
            url: resbs_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    updateResults(response);
                } else {
                    // Silent failure - no alert
                    console.log('Search failed silently');
                }
            },
            error: function() {
                // Silent error - no alert
                console.log('AJAX error occurred silently');
            },
            complete: function() {
                isLoading = false;
                hideLoadingState();
            }
        });
    }
    
    // Update search results
    function updateResults(response) {
        // Update results count
        resultsCount.text(response.found_posts);
        
        // Clear existing properties
        propertyGrid.empty();
        
        // Add new properties
        if (response.properties && response.properties.length > 0) {
            response.properties.forEach(function(property) {
                var propertyCard = createPropertyCard(property);
                propertyGrid.append(propertyCard);
            });
        } else {
            propertyGrid.append('<div class="no-properties-found"><h3>No properties found</h3><p>Try adjusting your search criteria or browse all properties.</p></div>');
        }
        
        // Update map markers
        updateMapMarkers(response.map_markers);
        
        // Update pagination if needed
        updatePagination(response);
    }
    
    // Create property card HTML
    function createPropertyCard(property) {
        var features = '';
        if (property.bedrooms) {
            features += '<div class="property-feature"><i class="fas fa-bed"></i><span>' + property.bedrooms + ' beds</span></div>';
        }
        if (property.bathrooms) {
            features += '<div class="property-feature"><i class="fas fa-bath"></i><span>' + property.bathrooms + ' baths</span></div>';
        }
        if (property.area_sqft) {
            features += '<div class="property-feature"><i class="fas fa-ruler-combined"></i><span>' + number_format(property.area_sqft) + ' sq ft</span></div>';
        }
        
        return $('<div class="property-card" data-property-id="' + property.id + '">' +
            '<div class="property-image">' +
                '<img src="' + property.featured_image + '" alt="' + property.title + '">' +
                '<div class="gradient-overlay"></div>' +
                '<div class="property-badge ' + property.badge_class + '">' + property.badge_text + '</div>' +
                '<button class="favorite-btn"><i class="far fa-heart"></i></button>' +
                '<div class="property-info-overlay">' +
                    '<h3 class="property-title">' + property.title + '</h3>' +
                    '<p class="property-location">' + property.location + '</p>' +
                '</div>' +
            '</div>' +
            '<div class="property-details">' +
                '<div class="property-price-container">' +
                    '<span class="property-price">' + property.price + '</span>' +
                    '<span class="property-status">' + property.property_status + '</span>' +
                '</div>' +
                '<div class="property-features">' + features + '</div>' +
                '<div class="property-footer">' +
                    '<span class="property-type">' + property.property_type + '</span>' +
                    '<a href="' + property.permalink + '" class="view-details-btn">View Details <i class="fas fa-arrow-right"></i></a>' +
                '</div>' +
            '</div>' +
        '</div>');
    }
    
    // Update map markers
    function updateMapMarkers(markers) {
        mapMarkers.empty();
        
        if (markers && markers.length > 0) {
            markers.forEach(function(marker, index) {
                var top = 25 + (index * 8) % 50;
                var left = 20 + (index * 12) % 60;
                
                var markerHtml = '<div class="map-marker" style="top: ' + top + '%; left: ' + left + '%;" data-property-id="' + marker.id + '" onclick="highlightProperty(' + marker.id + ')">' +
                    '<div class="marker-tooltip">' +
                        '<div class="tooltip-title">' + marker.title + '</div>' +
                        '<div class="tooltip-price">' + marker.price + '</div>' +
                    '</div>' +
                    '<div class="marker-icon" style="background-color: ' + getMarkerColor(marker.badge_class) + ';">' +
                        '<i class="' + getMarkerIcon(marker.badge_class) + '"></i>' +
                    '</div>' +
                '</div>';
                
                mapMarkers.append(markerHtml);
            });
        }
    }
    
    // Get marker color based on badge class
    function getMarkerColor(badgeClass) {
        switch (badgeClass) {
            case 'badge-featured':
                return '#f97316';
            case 'badge-new':
                return '#10b981';
            default:
                return '#0f766e';
        }
    }
    
    // Get marker icon based on badge class
    function getMarkerIcon(badgeClass) {
        switch (badgeClass) {
            case 'badge-featured':
                return 'fas fa-home';
            case 'badge-new':
                return 'fas fa-building';
            default:
                return 'fas fa-building';
        }
    }
    
    // Update pagination
    function updatePagination(response) {
        // This would be implemented based on your pagination needs
        // For now, we'll just log the response
        console.log('Pagination data:', response);
    }
    
    // Show loading state
    function showLoadingState() {
        propertyGrid.addClass('loading');
        $('.search-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Searching...');
    }
    
    // Hide loading state
    function hideLoadingState() {
        propertyGrid.removeClass('loading');
        $('.search-btn').prop('disabled', false).html('<i class="fas fa-search"></i> Search');
    }
    
    // Show error message - DISABLED to prevent alerts
    function showError(message) {
        // Silent error - no alert
        console.log('Error (silent):', message);
    }
    
    // Number formatting helper
    function number_format(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // Handle filter option clicks
    $('.filter-option').on('click', function() {
        var value = $(this).data('value');
        var dropdown = $(this).closest('.dropdown-content');
        var hiddenInput = dropdown.find('input[type="hidden"]');
        
        if (hiddenInput.length) {
            hiddenInput.val(value);
        }
        
        // Update button appearance
        dropdown.find('.filter-option').removeClass('active');
        $(this).addClass('active');
        
        // Close dropdown
        dropdown.hide();
        
        // Trigger search
        performSearch();
    });
    
    // Handle property type checkboxes
    $('input[name="property_type"]').on('change', function() {
        performSearch();
    });
    
    // Handle input changes with debounce
    var searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            performSearch();
        }, 500); // Wait 500ms after user stops typing
    });
    
    // Handle price input changes
    $('input[name="min_price"], input[name="max_price"]').on('change', function() {
        performSearch();
    });
    
    // Handle square footage input changes
    $('input[name="min_sqft"], input[name="max_sqft"]').on('change', function() {
        performSearch();
    });
    
    // Handle select changes
    $('select[name="year_built"], select[name="property_status"]').on('change', function() {
        performSearch();
    });
});
