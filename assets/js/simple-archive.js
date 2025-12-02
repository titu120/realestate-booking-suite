// Simple Archive JavaScript functionality
// Extracted from simple-archive.php template
// All PHP variables are passed via wp_localize_script

(function() {
    'use strict';

    // Get localized data from WordPress (may be empty initially, will be populated by inline script)
    const resbsData = typeof resbs_archive !== 'undefined' ? resbs_archive : {};
    const useOpenStreetMap = resbsData.use_openstreetmap || false;
    // Don't set propertiesData here - it will be set by inline script or when map initializes
    const mapSettings = resbsData.map_settings || {};
    const mapCenterLat = parseFloat(resbsData.map_center_lat) || 23.8103;
    const mapCenterLng = parseFloat(resbsData.map_center_lng) || 90.4125;
    const mapZoom = parseInt(resbsData.map_zoom) || 10;
    const ajaxUrl = resbsData.ajax_url || '';
    const favoritesNonce = resbsData.favorites_nonce || '';
    const translations = resbsData.translations || {};

    // SIMPLE FORM SUBMISSION - Let form submit naturally without interference
    // No JavaScript needed - form uses standard GET submission

    // Simple dropdown toggle functionality
 window.toggleDropdown = function(dropdownId, event) {
     if (event) {
         event.stopPropagation();
         event.preventDefault();
     }
     
     const dropdown = document.getElementById(dropdownId);
     if (!dropdown) {
         return;
     }
     
     const allDropdowns = document.querySelectorAll('.dropdown-content');

     // Close all other dropdowns
     allDropdowns.forEach(dd => {
         if (dd.id !== dropdownId) {
             dd.style.display = 'none';
             dd.classList.remove('active');
         }
     });

     // Toggle current dropdown
     const isActive = dropdown.style.display === 'block' || dropdown.classList.contains('active');
     
     if (isActive) {
         dropdown.style.display = 'none';
         dropdown.classList.remove('active');
     } else {
         // Find the button that triggered this
         let button = null;
         if (event && event.currentTarget) {
             button = event.currentTarget;
         } else if (event && event.target) {
             button = event.target.closest('.filter-chip');
         }
         
         // Fallback: find button by onclick attribute
         if (!button) {
             const buttons = document.querySelectorAll('.filter-chip');
             buttons.forEach(btn => {
                 const onclick = btn.getAttribute('onclick');
                 if (onclick && onclick.includes("'" + dropdownId + "'")) {
                     button = btn;
                 }
             });
         }
         
        if (button) {
            const buttonRect = button.getBoundingClientRect();
            const container = document.querySelector('.dropdowns-container');
            const dropdownWidth = 400; // Approximate dropdown width
            const viewportWidth = window.innerWidth;
            
            if (container) {
                const containerRect = container.getBoundingClientRect();
                
                // Calculate position relative to dropdowns-container
                let relativeLeft = buttonRect.left - containerRect.left;
                const relativeTop = buttonRect.bottom - containerRect.top + 8;
                
                // Check if dropdown would go off-screen on the right
                const dropdownRight = relativeLeft + dropdownWidth;
                if (dropdownRight > containerRect.width) {
                    // Align to the right edge of the button instead
                    relativeLeft = buttonRect.right - containerRect.left - dropdownWidth;
                    // If still off-screen on the left, align to container left
                    if (relativeLeft < 0) {
                        relativeLeft = 0;
                    }
                }
                
                dropdown.style.position = 'absolute';
                dropdown.style.top = relativeTop + 'px';
                dropdown.style.left = relativeLeft + 'px';
                dropdown.style.right = 'auto';
                dropdown.style.display = 'block';
                dropdown.classList.add('active');
            } else {
                // Fallback: use fixed positioning
                let fixedLeft = buttonRect.left;
                const fixedTop = buttonRect.bottom + 8;
                
                // Check if dropdown would go off-screen
                if (fixedLeft + dropdownWidth > viewportWidth) {
                    fixedLeft = viewportWidth - dropdownWidth - 16; // 16px margin
                    if (fixedLeft < 16) {
                        fixedLeft = 16;
                    }
                }
                
                dropdown.style.position = 'fixed';
                dropdown.style.top = fixedTop + 'px';
                dropdown.style.left = fixedLeft + 'px';
                dropdown.style.display = 'block';
                dropdown.classList.add('active');
            }
        } else {
            // No button found, just show dropdown at default position
            dropdown.style.display = 'block';
            dropdown.classList.add('active');
        }
     }
 };

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('.dropdown-content');
        const filterButtons = document.querySelectorAll('.filter-chip');
        
        let clickedInsideDropdown = false;
        let clickedInsideFilterButton = false;
        
        // Check if click was inside a dropdown
        dropdowns.forEach(dropdown => {
            if (dropdown.contains(event.target)) {
                clickedInsideDropdown = true;
            }
        });
        
        // Check if click was inside a filter button
        filterButtons.forEach(button => {
            if (button.contains(event.target)) {
                clickedInsideFilterButton = true;
            }
        });
        
        // Close all dropdowns if click was outside
        if (!clickedInsideDropdown && !clickedInsideFilterButton) {
            dropdowns.forEach(dropdown => {
                dropdown.style.display = 'none';
            });
        }
    });

    // Form submission is handled naturally by browser - no JavaScript needed

    // Clear price filter function
    window.clearPriceFilter = function() {
        document.querySelector('input[name="min_price"]').value = '';
        document.querySelector('input[name="max_price"]').value = '';
        document.getElementById('searchForm').submit();
    };

    // Clear type filter function
    window.clearTypeFilter = function() {
        // Select the "Any Type" radio button
        document.querySelector('input[name="property_type"][value=""]').checked = true;
        document.getElementById('searchForm').submit();
    };

    // Clear bedrooms filter function
    window.clearBedroomsFilter = function() {
        document.querySelector('input[name="min_bedrooms"]').value = '';
        document.querySelector('input[name="max_bedrooms"]').value = '';
        document.getElementById('searchForm').submit();
    };

    // Clear bathrooms filter function
    window.clearBathroomsFilter = function() {
        document.querySelector('input[name="min_bathrooms"]').value = '';
        document.querySelector('input[name="max_bathrooms"]').value = '';
        document.getElementById('searchForm').submit();
    };

    // Clear more filters function
    window.clearMoreFilters = function() {
        document.querySelector('input[name="min_sqft"]').value = '';
        document.querySelector('input[name="max_sqft"]').value = '';
        document.querySelector('select[name="year_built"]').value = '';
        document.querySelector('select[name="property_status"]').value = '';
        document.getElementById('searchForm').submit();
    };

    // Handle sort change dynamically
    window.handleSortChange = function(sortValue) {
        // Get the search form
        const searchForm = document.getElementById('searchForm');
        
        if (searchForm) {
            // Get all current form values to preserve filters
            const formData = new FormData(searchForm);
            const urlParams = new URLSearchParams();
            
            // Add all form fields to preserve filters
            for (const [key, value] of formData.entries()) {
                // Skip sort_by (we'll set it below) and empty values
                if (key !== 'sort_by' && value) {
                    urlParams.append(key, value);
                }
            }
            
            // Add/update sort_by parameter
            if (sortValue) {
                urlParams.set('sort_by', sortValue);
            }
            
            // Remove paged parameter to go back to page 1
            urlParams.delete('paged');
            
            // Build new URL with all parameters
            const archiveUrl = searchForm.action || window.location.pathname;
            // Clean the archive URL (remove existing query string if any)
            const cleanUrl = archiveUrl.split('?')[0];
            const newUrl = cleanUrl + '?' + urlParams.toString();
            
            // Navigate to new URL
            window.location.href = newUrl;
        } else {
            // Fallback: use current URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            if (sortValue) {
                urlParams.set('sort_by', sortValue);
            }
            urlParams.delete('paged');
            const newUrl = window.location.pathname + '?' + urlParams.toString();
            window.location.href = newUrl;
        }
    };

    // Show Map View - Always show map when clicked
    // Store scroll prevention handlers
    let scrollPreventionHandlers = {
        wheel: null,
        touchmove: null,
        scroll: null,
        windowScroll: null
    };
    
    // Function to prevent body scrolling
    function enableScrollPrevention() {
        const mainContent = document.querySelector('.main-content');
        const rbsArchive = document.querySelector('.rbs-archive');
        
        // Prevent body scrolling when map view is active
        document.body.classList.add('map-view-active');
        document.documentElement.classList.add('map-view-active');
        if (mainContent) mainContent.classList.add('map-view-active');
        if (rbsArchive) rbsArchive.classList.add('map-view-active');
        
        // Store current scroll position
        const scrollY = window.scrollY;
        
        // Prevent all scroll events on body/html when map is active
        scrollPreventionHandlers.wheel = function(e) {
            const propertiesList = document.querySelector('.listings-container.map-visible .properties-list');
            const target = e.target;
            
            // Check if the event is within the properties list or its children
            if (propertiesList && (propertiesList.contains(target) || propertiesList === target)) {
                // Check if we're at the boundaries
                const isAtTop = propertiesList.scrollTop <= 0;
                const isAtBottom = propertiesList.scrollHeight - propertiesList.scrollTop <= propertiesList.clientHeight + 1;
                
                // Prevent scroll propagation if at boundaries
                if ((isAtTop && e.deltaY < 0) || (isAtBottom && e.deltaY > 0)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                return; // Allow scrolling within properties list
            }
            
            // Prevent all other scrolling
            e.preventDefault();
            e.stopPropagation();
            return false;
        };
        
        scrollPreventionHandlers.touchmove = function(e) {
            const propertiesList = document.querySelector('.listings-container.map-visible .properties-list');
            const target = e.target;
            
            // Check if the event is within the properties list or its children
            if (propertiesList && (propertiesList.contains(target) || propertiesList === target)) {
                // Check if we're at the boundaries
                const isAtTop = propertiesList.scrollTop <= 0;
                const isAtBottom = propertiesList.scrollHeight - propertiesList.scrollTop <= propertiesList.clientHeight + 1;
                
                // Prevent scroll propagation if at boundaries
                if ((isAtTop && e.touches[0].clientY > e.touches[0].clientY) || 
                    (isAtBottom && e.touches[0].clientY < e.touches[0].clientY)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
                return; // Allow scrolling within properties list
            }
            
            // Prevent all other scrolling
            e.preventDefault();
            e.stopPropagation();
            return false;
        };
        
        scrollPreventionHandlers.scroll = function(e) {
            // Always prevent body/html scrolling
            if (e.target === document.body || e.target === document.documentElement) {
                window.scrollTo(0, scrollY); // Maintain scroll position
                e.preventDefault();
                e.stopPropagation();
                return false;
            }
        };
        
        // Prevent scroll on window
        const preventWindowScroll = function(e) {
            window.scrollTo(0, scrollY);
            e.preventDefault();
            return false;
        };
        
        document.body.addEventListener('wheel', scrollPreventionHandlers.wheel, { passive: false });
        document.body.addEventListener('touchmove', scrollPreventionHandlers.touchmove, { passive: false });
        document.body.addEventListener('scroll', scrollPreventionHandlers.scroll, { passive: false });
        window.addEventListener('scroll', preventWindowScroll, { passive: false });
        
        // Store window scroll handler
        scrollPreventionHandlers.windowScroll = preventWindowScroll;
    }
    
    // Function to re-enable body scrolling
    function disableScrollPrevention() {
        const mainContent = document.querySelector('.main-content');
        const rbsArchive = document.querySelector('.rbs-archive');
        
        // Re-enable body scrolling
        document.body.classList.remove('map-view-active');
        document.documentElement.classList.remove('map-view-active');
        if (mainContent) mainContent.classList.remove('map-view-active');
        if (rbsArchive) rbsArchive.classList.remove('map-view-active');
        
        // Remove event listeners
        if (scrollPreventionHandlers.wheel) {
            document.body.removeEventListener('wheel', scrollPreventionHandlers.wheel);
            scrollPreventionHandlers.wheel = null;
        }
        if (scrollPreventionHandlers.touchmove) {
            document.body.removeEventListener('touchmove', scrollPreventionHandlers.touchmove);
            scrollPreventionHandlers.touchmove = null;
        }
        if (scrollPreventionHandlers.scroll) {
            document.body.removeEventListener('scroll', scrollPreventionHandlers.scroll);
            scrollPreventionHandlers.scroll = null;
        }
        if (scrollPreventionHandlers.windowScroll) {
            window.removeEventListener('scroll', scrollPreventionHandlers.windowScroll);
            scrollPreventionHandlers.windowScroll = null;
        }
    }
    
    window.showMapView = function() {
        const mapSection = document.querySelector('.map-section');
        const listingsContainer = document.querySelector('.listings-container');
        const viewButtons = document.querySelectorAll('.view-btn');
        const mapToggleBtn = document.getElementById('mapToggleBtn');
        
        // Remove active class from all view buttons
        viewButtons.forEach(btn => btn.classList.remove('active'));
        
        // Always show map view - FORCE visibility with inline styles
        if (mapSection) {
            mapSection.classList.remove('map-hidden');
            mapSection.classList.add('map-visible');
            // Force display with inline style to override any CSS
            mapSection.style.display = 'block';
            mapSection.style.visibility = 'visible';
            mapSection.style.opacity = '1';
            mapSection.style.width = 'auto';
            mapSection.style.height = '500px';
        }
        
        if (listingsContainer) {
            listingsContainer.classList.add('map-visible');
        }
        
        // Update button states
        const mapViewBtn = document.querySelector('.view-btn[onclick*="showMapView"]');
        if (mapViewBtn) mapViewBtn.classList.add('active');
        if (mapToggleBtn) mapToggleBtn.classList.add('active');
        
        // Ensure map container is visible
        const mapContainer = document.getElementById('googleMap');
        if (mapContainer) {
            mapContainer.style.display = 'block';
            mapContainer.style.visibility = 'visible';
            mapContainer.style.width = '100%';
            mapContainer.style.height = '100%';
            mapContainer.style.minHeight = '500px';
        }
        
        // Initialize map if using OpenStreetMap
        // CRITICAL: Get fresh data from window.resbs_archive (set by inline script)
        function refreshPropertiesData() {
            // Priority 1: window.resbs_archive (set by inline script - most reliable)
            if (typeof window.resbs_archive !== 'undefined' && window.resbs_archive.properties_data && Array.isArray(window.resbs_archive.properties_data)) {
                window.propertiesData = window.resbs_archive.properties_data;
                return window.propertiesData;
            }
            // Priority 2: window.propertiesData (also set by inline script)
            if (window.propertiesData && Array.isArray(window.propertiesData) && window.propertiesData.length > 0) {
                return window.propertiesData;
            }
            // Priority 3: resbs_archive (from wp_localize_script - might be empty)
            const resbsData = typeof resbs_archive !== 'undefined' ? resbs_archive : {};
            if (resbsData.properties_data && Array.isArray(resbsData.properties_data) && resbsData.properties_data.length > 0) {
                window.propertiesData = resbsData.properties_data;
                return window.propertiesData;
            }
            return [];
        }
        
        const useOpenStreetMap = (typeof window.resbs_archive !== 'undefined' && window.resbs_archive.use_openstreetmap) || 
                                 (typeof resbs_archive !== 'undefined' && resbs_archive.use_openstreetmap) || 
                                 false;
        
        if (useOpenStreetMap && typeof window.initializeOpenStreetMap === 'function') {
            // Refresh data immediately before initializing
            refreshPropertiesData();
            
            // Wait a tiny bit to ensure data is set
            setTimeout(function() {
                // Refresh again to be sure
                refreshPropertiesData();
                window.initializeOpenStreetMap();
            }, 100);
        }
    };

    // Show List View - Always hide map when clicked
    window.showListView = function() {
        const mapSection = document.querySelector('.map-section');
        const listingsContainer = document.querySelector('.listings-container');
        const viewButtons = document.querySelectorAll('.view-btn');
        const mapToggleBtn = document.getElementById('mapToggleBtn');
        
        // Remove active class from all view buttons
        viewButtons.forEach(btn => btn.classList.remove('active'));
        
        // Always hide map view
        if (mapSection) {
            mapSection.classList.remove('map-visible');
            mapSection.classList.add('map-hidden');
        }
        if (listingsContainer) {
            listingsContainer.classList.remove('map-visible');
        }
        
        // Update button states
        const listViewBtn = document.querySelector('.view-btn[onclick*="showListView"]');
        if (listViewBtn) listViewBtn.classList.add('active');
        if (mapToggleBtn) mapToggleBtn.classList.remove('active');
        
    };

    // Show map function - Always show map when clicked
    window.showMap = function() {
        const mapSection = document.querySelector('.map-section');
        const listingsContainer = document.querySelector('.listings-container');
        const mapToggleBtn = document.getElementById('mapToggleBtn');
        const gridBtn = document.getElementById('gridBtn');
        
        // Always show map
        if (mapSection) {
            mapSection.classList.remove('map-hidden');
            mapSection.classList.add('map-visible');
        }
        if (listingsContainer) {
            listingsContainer.classList.add('map-visible');
        }
        
        // Update button states
        if (mapToggleBtn) mapToggleBtn.classList.add('active');
        if (gridBtn) gridBtn.classList.remove('active');
        
        // Update view buttons
        document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
        const mapViewBtn = document.querySelector('.view-btn[onclick*="showMapView"]');
        if (mapViewBtn) mapViewBtn.classList.add('active');
        
        
        // Initialize map if using OpenStreetMap
        // CRITICAL: Get fresh data from window.resbs_archive (set by inline script)
        function refreshPropertiesData() {
            // Priority 1: window.resbs_archive (set by inline script - most reliable)
            if (typeof window.resbs_archive !== 'undefined' && window.resbs_archive.properties_data) {
                if (Array.isArray(window.resbs_archive.properties_data) && window.resbs_archive.properties_data.length > 0) {
                    window.propertiesData = window.resbs_archive.properties_data;
                    return window.propertiesData;
                }
            }
            // Priority 2: window.propertiesData (also set by inline script)
            if (window.propertiesData && Array.isArray(window.propertiesData) && window.propertiesData.length > 0) {
                return window.propertiesData;
            }
            // Priority 3: resbs_archive (from wp_localize_script - might be empty)
            const resbsData = typeof resbs_archive !== 'undefined' ? resbs_archive : {};
            if (resbsData.properties_data && Array.isArray(resbsData.properties_data) && resbsData.properties_data.length > 0) {
                window.propertiesData = resbsData.properties_data;
                return window.propertiesData;
            }
            return [];
        }
        
        const useOpenStreetMap = (typeof window.resbs_archive !== 'undefined' && window.resbs_archive.use_openstreetmap) || 
                                 (typeof resbs_archive !== 'undefined' && resbs_archive.use_openstreetmap) || 
                                 false;
        
        if (useOpenStreetMap && typeof window.initializeOpenStreetMap === 'function') {
            // Refresh data immediately before initializing
            refreshPropertiesData();
            
            // Wait a tiny bit to ensure data is set
            setTimeout(function() {
                // Refresh again to be sure
                refreshPropertiesData();
                window.initializeOpenStreetMap();
            }, 100);
        }
    };

    // Show grid layout function - Always hide map when clicked
    window.showGridLayout = function() {
        const mapSection = document.querySelector('.map-section');
        const listingsContainer = document.querySelector('.listings-container');
        const propertyGrid = document.getElementById('propertyGrid');
        const gridBtn = document.getElementById('gridBtn');
        const mapToggleBtn = document.getElementById('mapToggleBtn');
        
        // Always hide map
        if (mapSection) {
            mapSection.classList.remove('map-visible');
            mapSection.classList.add('map-hidden');
        }
        if (listingsContainer) {
            listingsContainer.classList.remove('map-visible');
        }
        
        if (propertyGrid) {
            propertyGrid.classList.remove('list-view');
        }
        
        // Update button states
        if (gridBtn) gridBtn.classList.add('active');
        if (mapToggleBtn) mapToggleBtn.classList.remove('active');
        
        // Update view buttons
        document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
        const listViewBtn = document.querySelector('.view-btn[onclick*="showListView"]');
        if (listViewBtn) listViewBtn.classList.add('active');
        
    };

    // Highlight property function (for map markers)
window.highlightProperty = function(propertyId) {
        // Remove active class from all property cards
    document.querySelectorAll('.property-card').forEach(card => {
            card.classList.remove('highlighted');
        });
        
        // Add active class to selected property card
        const selectedCard = document.querySelector(`[data-property-id="${propertyId}"]`);
        if (selectedCard) {
            selectedCard.classList.add('highlighted');
            selectedCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        // Update map marker states
    document.querySelectorAll('.map-marker').forEach(marker => {
        marker.classList.remove('active');
    });

        const selectedMarker = document.querySelector(`.map-marker[data-property-id="${propertyId}"]`);
        if (selectedMarker) {
            selectedMarker.classList.add('active');
        }
    };

    // Toast notification function
    function showToastNotification(message, type) {
        // Remove existing toasts
        const existingToasts = document.querySelectorAll('.resbs-toast-notification');
        existingToasts.forEach(toast => toast.remove());
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'resbs-toast-notification resbs-toast-' + (type || 'success');
        toast.innerHTML = '<span class="resbs-toast-message">' + message + '</span><button class="resbs-toast-close">&times;</button>';
        
        // Add to body
        document.body.appendChild(toast);
        
        // Show toast with animation
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Auto-hide after 3 seconds
        const autoHide = setTimeout(() => {
            hideToast(toast);
        }, 3000);
        
        // Close button click
        const closeBtn = toast.querySelector('.resbs-toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                clearTimeout(autoHide);
                hideToast(toast);
            });
        }
    }

    function hideToast(toast) {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }

    // Reset all filters to default - Make it global
    window.resetAllFilters = function() {
        try {
            
            // Get the base URL without any query parameters
            const baseUrl = window.location.pathname;
            
            // Add cache-busting parameter to force fresh load, then redirect to clean URL
            // This ensures browser doesn't use cached version with old filter values
            const timestamp = Date.now();
            window.location.href = baseUrl + '?reset=' + timestamp;
            
        } catch (error) {
            // Fallback: reload page without query params
            window.location.href = window.location.pathname;
        }
    };

    // Favorite button functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize favorite button states on page load
        function initializeFavoriteButtons() {
            const favoriteButtons = document.querySelectorAll('.favorite-btn, .resbs-favorite-btn');
            favoriteButtons.forEach(function(btn) {
                const propertyId = btn.getAttribute('data-property-id');
                if (!propertyId) return;
                
                const icon = btn.querySelector('i');
                if (!icon) return;
                
                // Check if button already has favorited class (set by PHP)
                if (btn.classList.contains('favorited')) {
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
            });
        }
        
        // Initialize buttons on page load
        initializeFavoriteButtons();
        
        // Handle favorite button clicks
        document.addEventListener('click', function(e) {
            const favoriteBtn = e.target.closest('.favorite-btn, .resbs-favorite-btn');
            if (!favoriteBtn) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            const propertyId = favoriteBtn.getAttribute('data-property-id');
            if (!propertyId) {
                return;
            }
            
            const icon = favoriteBtn.querySelector('i');
            if (!icon) return;
            
            // Toggle visual state immediately for better UX
            const isFavorited = favoriteBtn.classList.contains('favorited');
            if (isFavorited) {
                favoriteBtn.classList.remove('favorited');
                icon.classList.remove('fas');
                icon.classList.add('far');
            } else {
                favoriteBtn.classList.add('favorited');
                icon.classList.remove('far');
                icon.classList.add('fas');
            }
            
            // Make AJAX request
            if (!ajaxUrl || !favoritesNonce) {
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'resbs_toggle_favorite');
            formData.append('property_id', propertyId);
            formData.append('nonce', favoritesNonce);
            
            fetch(ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (!data) {
                    throw new Error('Invalid response from server');
                }
                
                if (data.success) {
                    // Success - update button state
                    if (data.data && data.data.is_favorite) {
                        favoriteBtn.classList.add('favorited');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        favoriteBtn.classList.remove('favorited');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                    
                    // Show success message as toast notification
                    if (data.data && data.data.message) {
                        showToastNotification(data.data.message, 'success');
                    }
                } else {
                    // Error - revert visual state
                    if (isFavorited) {
                        favoriteBtn.classList.add('favorited');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        favoriteBtn.classList.remove('favorited');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                    
                    // Show error message
                    let errorMessage = translations.error_occurred || 'An error occurred. Please try again.';
                    
                    if (data && data.data) {
                        // data.data can be a string or an object
                        if (typeof data.data === 'string') {
                            errorMessage = data.data;
                        } else if (data.data.message) {
                            errorMessage = data.data.message;
                        }
                    }
                    
                    alert(errorMessage);
                }
            })
            .catch(error => {
                // Revert visual state on error
                if (isFavorited) {
                    favoriteBtn.classList.add('favorited');
                    icon.classList.remove('far');
                    icon.classList.add('fas');
                } else {
                    favoriteBtn.classList.remove('favorited');
                    icon.classList.remove('fas');
                    icon.classList.add('far');
                }
                
                alert(translations.error_occurred || 'An error occurred. Please try again.');
            });
        });
        
        const mapSection = document.querySelector('.map-section');
        const listingsContainer = document.querySelector('.listings-container');
        
        // Ensure map starts hidden
        if (mapSection) {
            mapSection.classList.add('map-hidden');
            mapSection.classList.remove('map-visible');
        }
        
        // Ensure listings container starts in list view
        if (listingsContainer) {
            listingsContainer.classList.remove('map-visible');
        }
        
        // Set initial button states
        const listViewBtn = document.querySelector('.view-btn[onclick*="showListView"]');
        const mapViewBtn = document.querySelector('.view-btn[onclick*="showMapView"]');
        const mapToggleBtn = document.getElementById('mapToggleBtn');
        const gridBtn = document.getElementById('gridBtn');
        
        if (listViewBtn) listViewBtn.classList.add('active');
        if (mapViewBtn) mapViewBtn.classList.remove('active');
        if (mapToggleBtn) mapToggleBtn.classList.remove('active');
        if (gridBtn) gridBtn.classList.add('active');
        
        // Initialize OpenStreetMap (free, no setup required)
        if (useOpenStreetMap) {
            // Get properties data from resbs_archive (set by inline script)
            // IMPORTANT: Check multiple sources and wait a bit for inline script to run
            function getPropertiesData() {
                // Check window.resbs_archive first (set by inline script)
                if (typeof window.resbs_archive !== 'undefined' && window.resbs_archive.properties_data && window.resbs_archive.properties_data.length > 0) {
                    return window.resbs_archive.properties_data;
                }
                // Check window.propertiesData (also set by inline script)
                if (window.propertiesData && Array.isArray(window.propertiesData) && window.propertiesData.length > 0) {
                    return window.propertiesData;
                }
                // Check resbs_archive (from wp_localize_script - might be empty)
                const currentResbsData = typeof resbs_archive !== 'undefined' ? resbs_archive : {};
                if (currentResbsData.properties_data && currentResbsData.properties_data.length > 0) {
                    return currentResbsData.properties_data;
                }
                return [];
            }
            
            const currentPropertiesData = getPropertiesData();
            
            // Set global properties data
            window.propertiesData = currentPropertiesData;
            window.mapInitialized = false;
            const currentResbsData = typeof window.resbs_archive !== 'undefined' ? window.resbs_archive : (typeof resbs_archive !== 'undefined' ? resbs_archive : {});
            window.resbsMapSettings = currentResbsData.map_settings || mapSettings;
            
            // Don't initialize map on page load - wait for user to click map button
            // Map will be initialized when showMapView() or showMap() is called
        }
    });

    // OpenStreetMap initialization functions (only if using OpenStreetMap)
    // Note: propertiesData will be loaded from resbs_archive when map initializes
    if (useOpenStreetMap) {
        // Leaflet Variables - Must be global
        window.map = null;
        window.markers = [];
        window.popups = [];
        window.markerClusterGroup = null;
        // Don't set propertiesData here - it will be loaded when map initializes
        window.mapInitialized = false;
        window.resbsMapSettings = mapSettings;

        // Initialize OpenStreetMap with Leaflet
        window.initializeOpenStreetMap = function() {
            // Wait for Leaflet with retries
            let attempts = 0;
            const maxAttempts = 25; // 25 attempts = 5 seconds total
            
            function checkLeaflet() {
                if (typeof L !== 'undefined' && typeof L.map === 'function') {
                    // Leaflet is loaded, proceed with initialization
                    proceedWithMapInit();
                } else {
                    attempts++;
                    if (attempts < maxAttempts) {
                        setTimeout(checkLeaflet, 200);
                    } else {
                        // Only show error if map section is actually visible
                        const mapSection = document.querySelector('.map-section');
                        const mapContainer = document.getElementById('googleMap');
                        if (mapSection && mapContainer) {
                            const computedStyle = window.getComputedStyle(mapSection);
                            const isVisible = !mapSection.classList.contains('map-hidden') && 
                                            computedStyle.display !== 'none' && 
                                            computedStyle.visibility !== 'hidden';
                            if (isVisible) {
                                showMapError('Map library failed to load. Please refresh the page.');
                            }
                        }
                    }
                }
            }
            
            function proceedWithMapInit() {
                const mapContainer = document.getElementById('googleMap');
                if (!mapContainer) {
                    return;
                }
                
                // Check if map already exists
                if (window.map) {
                    setTimeout(function() {
                        if (window.map) {
                            window.map.invalidateSize();
                        }
                    }, 100);
                    return;
                }
                
                // CRITICAL: Read from MULTIPLE sources in order of priority
                let propsData = [];
                
                // Priority 1: Read from hidden div data attribute (MOST RELIABLE - cannot be overwritten)
                const dataDiv = document.getElementById('resbs-properties-data-storage');
                if (dataDiv) {
                    const storedData = dataDiv.getAttribute('data-properties');
                    if (storedData) {
                        try {
                            propsData = JSON.parse(storedData);
                        } catch(e) {
                            // Failed to parse data from div
                        }
                    }
                }
                
                // Priority 2: window.RESBS_PROPERTIES_DATA (unique key, less likely to conflict)
                if ((!propsData || propsData.length === 0) && typeof window.RESBS_PROPERTIES_DATA !== 'undefined' && Array.isArray(window.RESBS_PROPERTIES_DATA)) {
                    propsData = window.RESBS_PROPERTIES_DATA;
                }
                
                // Priority 3: window.resbs_archive.properties_data
                if ((!propsData || propsData.length === 0) && typeof window.resbs_archive !== 'undefined' && window.resbs_archive.properties_data) {
                    if (Array.isArray(window.resbs_archive.properties_data)) {
                        propsData = window.resbs_archive.properties_data;
                    }
                }
                
                // Priority 4: window.propertiesData
                if ((!propsData || propsData.length === 0) && window.propertiesData && Array.isArray(window.propertiesData)) {
                    propsData = window.propertiesData;
                }
                
                // Priority 5: global resbs_archive
                if ((!propsData || propsData.length === 0) && typeof resbs_archive !== 'undefined' && resbs_archive.properties_data && Array.isArray(resbs_archive.properties_data)) {
                    propsData = resbs_archive.properties_data;
                }
                
                // Ensure it's an array
                if (!Array.isArray(propsData)) {
                    propsData = [];
                }
                
                // Set global for other functions
                window.propertiesData = propsData;
                
                if (propsData.length === 0) {
                    const mapContainer = document.getElementById('googleMap');
                    if (mapContainer) {
                        const noPropsDiv = document.createElement('div');
                        noPropsDiv.className = 'resbs-map-no-properties';
                        noPropsDiv.style.cssText = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1000; text-align: center;';
                        noPropsDiv.innerHTML = '<p style="margin: 0; color: #666;">No properties with location data found.</p>';
                        mapContainer.appendChild(noPropsDiv);
                    }
                    return;
                }
                
                // Separate properties with and without coordinates
                const propertiesWithCoords = [];
                const propertiesNeedingGeocode = [];
                
                if (propsData && propsData.length > 0) {
                    propsData.forEach(function(property, index) {
                        const lat = property.lat ? parseFloat(property.lat) : NaN;
                        const lng = property.lng ? parseFloat(property.lng) : NaN;
                        
                        const hasValidCoords = !isNaN(lat) && !isNaN(lng) && 
                            lat >= -90 && lat <= 90 && 
                            lng >= -180 && lng <= 180 &&
                            lat !== 0 && lng !== 0;
                        
                        if (hasValidCoords) {
                            propertiesWithCoords.push(property);
                        } else {
                            // Try to build address from available data - be very permissive
                            let addressToUse = property.full_address || '';
                            
                            if (!addressToUse || addressToUse.trim() === '') {
                                if (property.location_name && property.location_name.trim() !== '') {
                                    addressToUse = property.location_name + ', Bangladesh';
                                } else if (property.city && property.city.trim() !== '') {
                                    addressToUse = property.city + ', Bangladesh';
                                } else if (property.address && property.address.trim() !== '') {
                                    addressToUse = property.address + ', Bangladesh';
                                } else if (property.title && property.title.trim() !== '') {
                                    // Last resort: use property title - extract city name if possible
                                    const title = property.title.trim();
                                    const bangladeshCities = ['Dhaka', 'Chittagong', 'Sylhet', 'Comilla', 'Feni', 'Coxbazar', 'Cox\'s Bazar', 'Rajshahi', 'Khulna', 'Barisal', 'Rangpur', 'Mymensingh'];
                                    let foundCity = '';
                                    for (let i = 0; i < bangladeshCities.length; i++) {
                                        if (title.toLowerCase().includes(bangladeshCities[i].toLowerCase())) {
                                            foundCity = bangladeshCities[i];
                                            break;
                                        }
                                    }
                                    if (foundCity) {
                                        addressToUse = foundCity + ', Bangladesh';
                                    } else {
                                        addressToUse = title + ', Bangladesh';
                                    }
                                }
                            }
                            
                            // ALWAYS add to geocoding if we have ANY address data - be very permissive
                            if (addressToUse && addressToUse.trim() !== '') {
                                property.full_address = addressToUse.trim();
                                property.needs_geocoding = true;
                                propertiesNeedingGeocode.push(property);
                            } else {
                                // Even if no address, try to use title as last resort
                                if (property.title && property.title.trim() !== '') {
                                    property.full_address = property.title.trim() + ', Bangladesh';
                                    property.needs_geocoding = true;
                                    propertiesNeedingGeocode.push(property);
                                }
                            }
                        }
                    });
                }
                
                // Calculate map center
                let centerLat = mapCenterLat;
                let centerLng = mapCenterLng;
                
                if (propertiesWithCoords.length > 0) {
                    let minLat = Infinity, maxLat = -Infinity;
                    let minLng = Infinity, maxLng = -Infinity;
                    
                    propertiesWithCoords.forEach(function(property) {
                        const lat = parseFloat(property.lat);
                        const lng = parseFloat(property.lng);
                        minLat = Math.min(minLat, lat);
                        maxLat = Math.max(maxLat, lat);
                        minLng = Math.min(minLng, lng);
                        maxLng = Math.max(maxLng, lng);
                    });
                    
                    centerLat = (minLat + maxLat) / 2;
                    centerLng = (minLng + maxLng) / 2;
                }
                
                try {
                    const mapZoomLevel = window.resbsMapSettings ? window.resbsMapSettings.zoom : mapZoom;
                    
                    window.map = L.map('googleMap', {
                        center: [centerLat, centerLng],
                        zoom: mapZoomLevel,
                        zoomControl: true
                    });
                    
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(window.map);
                    
                    // Add markers for properties with coordinates
                    if (propertiesWithCoords.length > 0) {
                        addLeafletMarkers(propertiesWithCoords);
                        
                        if (propertiesWithCoords.length > 1) {
                            const bounds = L.latLngBounds([]);
                            propertiesWithCoords.forEach(function(property) {
                                bounds.extend([parseFloat(property.lat), parseFloat(property.lng)]);
                            });
                            window.map.fitBounds(bounds, {padding: [50, 50]});
                        } else if (propertiesWithCoords.length === 1) {
                            // Single property - center on it
                            window.map.setView([parseFloat(propertiesWithCoords[0].lat), parseFloat(propertiesWithCoords[0].lng)], 13);
                        }
                    }
                    
                    // Geocode properties that need it
                    if (propertiesNeedingGeocode.length > 0) {
                        geocodePropertiesNominatim(propertiesNeedingGeocode);
                    }
                    
                    // Remove any existing "no properties" message first
                    const mapContainer = document.getElementById('googleMap');
                    if (mapContainer) {
                        const existingMsg = mapContainer.querySelector('.resbs-map-no-properties');
                        if (existingMsg) existingMsg.remove();
                    }
                    
                    // Only show "no properties" message if we truly have NO properties data at all
                    if ((!propsData || propsData.length === 0) && propertiesWithCoords.length === 0 && propertiesNeedingGeocode.length === 0) {
                        if (mapContainer) {
                            const noPropsDiv = document.createElement('div');
                            noPropsDiv.className = 'resbs-map-no-properties';
                            noPropsDiv.style.cssText = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); z-index: 1000; text-align: center;';
                            noPropsDiv.innerHTML = '<p style="margin: 0; color: #666;">No properties with location data found.</p>';
                            mapContainer.appendChild(noPropsDiv);
                        }
                    } else if (propertiesWithCoords.length === 0 && propertiesNeedingGeocode.length === 0 && propsData && propsData.length > 0) {
                        // Properties exist but have no location data
                    }
                    
                    window.mapInitialized = true;
                    
                } catch (error) {
                    // Only show error if map section is visible
                    const mapSection = document.querySelector('.map-section');
                    if (mapSection && !mapSection.classList.contains('map-hidden')) {
                        const computedStyle = window.getComputedStyle(mapSection);
                        if (computedStyle.display !== 'none' && computedStyle.visibility !== 'hidden') {
                            showMapError('Error initializing map: ' + error.message);
                        }
                    }
                }
            }
            
            // Start checking for Leaflet
            checkLeaflet();
        };

        // Add Leaflet markers
        function addLeafletMarkers(propertiesArray) {
            if (!window.map || !propertiesArray || propertiesArray.length === 0) {
                return;
            }
            
            const mapSettings = window.resbsMapSettings || {};
            const enableCluster = mapSettings.enableCluster || false;
            
            // Clear existing markers
            if (window.markerClusterGroup) {
                window.map.removeLayer(window.markerClusterGroup);
                window.markerClusterGroup = null;
            }
            window.markers.forEach(function(marker) {
                window.map.removeLayer(marker);
            });
            window.markers = [];
            window.popups = [];
            
            // Initialize cluster group if enabled
            if (enableCluster && typeof L.markerClusterGroup !== 'undefined') {
                window.markerClusterGroup = L.markerClusterGroup({
                    maxClusterRadius: 50,
                    spiderfyOnMaxZoom: true,
                    showCoverageOnHover: false,
                    zoomToBoundsOnClick: true
                });
                window.markerClusterGroup.addTo(window.map);
            }
            
            // Add markers
            let markersAdded = 0;
            propertiesArray.forEach(function(property) {
                if (property.lat && property.lng) {
                    const marker = createLeafletMarker(property);
                    if (marker) {
                        if (enableCluster && window.markerClusterGroup) {
                            window.markerClusterGroup.addLayer(marker);
                        } else {
                            marker.addTo(window.map);
                        }
                        window.markers.push(marker);
                        markersAdded++;
                    }
                }
            });
        }

        // Create a Leaflet marker
        function createLeafletMarker(property) {
            if (!window.map || !property.lat || !property.lng) return null;
            
            const mapSettings = window.resbsMapSettings || {};
            const markerType = mapSettings.markerType || 'icon';
            const useSingleMarker = mapSettings.useSingleMarker || false;
            const singleMarkerColor = mapSettings.singleMarkerColor || '#333333';
            let markerColor = property.marker_color || '#10b981';
            
            if (useSingleMarker) {
                markerColor = singleMarkerColor;
            }
            
            let markerIcon;
            if (markerType === 'price' && property.price) {
                const priceText = property.price.replace(/[$,]/g, '').replace(/Price on request/i, 'P.O.R');
                markerIcon = L.divIcon({
                    className: 'leaflet-marker-price',
                    html: `<div style="background-color: ${markerColor}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; white-space: nowrap; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${priceText}</div>`,
                    iconSize: [60, 20],
                    iconAnchor: [30, 10]
                });
            } else {
                const iconHtml = `<div style="width: 20px; height: 20px; border-radius: 50%; background-color: ${markerColor}; border: 2px solid #ffffff; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`;
                markerIcon = L.divIcon({
                    className: 'leaflet-marker-custom',
                    html: iconHtml,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
            }
            
            const marker = L.marker([parseFloat(property.lat), parseFloat(property.lng)], {
                icon: markerIcon
            });
            
            const popupContent = `
                <div class="property-info-window" style="min-width: 250px; padding: 10px;">
                    ${property.image ? `<img src="${property.image}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; margin-bottom: 10px;" alt="${property.title}">` : ''}
                    <h3 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600;">${property.title}</h3>
                    <p style="margin: 0 0 8px 0; font-size: 18px; font-weight: bold; color: #10b981;">${property.price}</p>
                    <div style="display: flex; gap: 15px; margin-bottom: 10px; font-size: 14px; color: #666;">
                        ${property.bedrooms ? `<span>🛏️ ${property.bedrooms} beds</span>` : ''}
                        ${property.bathrooms ? `<span>🚿 ${property.bathrooms} baths</span>` : ''}
                        ${property.area_sqft ? `<span>📏 ${property.area_sqft.toLocaleString()} sq ft</span>` : ''}
                    </div>
                    <a href="${property.permalink}" target="_blank" style="display: inline-block; padding: 8px 16px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; font-weight: 500; margin-top: 8px;">
                        View Details →
                    </a>
                </div>
            `;
            
            marker.bindPopup(popupContent);
            return marker;
        }

        // Geocode properties using Nominatim
        function geocodePropertiesNominatim(propertiesArray) {
            if (!propertiesArray || propertiesArray.length === 0 || !window.map) {
                return;
            }
            
            let geocodeIndex = 0;
            const geocodeDelay = 1000; // 1 second delay to respect API limits
            
            function geocodeNext() {
                if (geocodeIndex >= propertiesArray.length) {
                    updateLeafletBounds();
                    return;
                }
                
                const property = propertiesArray[geocodeIndex];
                
                if (!property.full_address || property.full_address.trim() === '') {
                    geocodeIndex++;
                    setTimeout(geocodeNext, geocodeDelay);
                    return;
                }
                
                const geocodeUrl = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(property.full_address)}&limit=1`;
                
                fetch(geocodeUrl, {
                    headers: {
                        'User-Agent': 'RealEstate-Booking-Suite-WordPress-Plugin/1.0'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const result = data[0];
                        property.lat = parseFloat(result.lat);
                        property.lng = parseFloat(result.lon);
                        property.needs_geocoding = false;
                        
                        const marker = createLeafletMarker(property);
                        if (marker) {
                            const mapSettings = window.resbsMapSettings || {};
                            const enableCluster = mapSettings.enableCluster || false;
                            
                            if (enableCluster && window.markerClusterGroup) {
                                window.markerClusterGroup.addLayer(marker);
                            } else {
                                marker.addTo(window.map);
                            }
                            window.markers.push(marker);
                            
                            // Update bounds after adding marker
                            if (window.markers.length > 0) {
                                const bounds = L.latLngBounds([]);
                                window.markers.forEach(function(m) {
                                    bounds.extend(m.getLatLng());
                                });
                                window.map.fitBounds(bounds, {padding: [50, 50]});
                            }
                        }
                    }
                    geocodeIndex++;
                    setTimeout(geocodeNext, geocodeDelay);
                })
                .catch(error => {
                    geocodeIndex++;
                    setTimeout(geocodeNext, geocodeDelay);
                });
            }
            
            geocodeNext();
        }

        // Update map bounds
        function updateLeafletBounds() {
            if (!window.map || !window.markers || window.markers.length === 0) return;
            
            const bounds = L.latLngBounds([]);
            window.markers.forEach(function(marker) {
                bounds.extend(marker.getLatLng());
            });
            
            if (window.markers.length > 0) {
                window.map.fitBounds(bounds, {padding: [50, 50]});
            }
        }

        // Show map error - only if map section is visible
        function showMapError(message) {
            const mapContainer = document.getElementById('googleMap');
            if (!mapContainer) return;
            
            // Only show error if map section is actually visible
            const mapSection = document.querySelector('.map-section');
            if (!mapSection || mapSection.classList.contains('map-hidden')) {
                return;
            }
            
            const computedStyle = window.getComputedStyle(mapSection);
            if (computedStyle.display === 'none' || computedStyle.visibility === 'hidden') {
                return;
            }
            
            const existingError = mapContainer.querySelector('.resbs-map-error');
            if (existingError) existingError.remove();
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'resbs-map-error';
            errorDiv.style.cssText = 'position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); z-index: 1000; max-width: 450px; text-align: center; border: 2px solid #ef4444;';
            errorDiv.innerHTML = `
                <div style="color: #ef4444; font-size: 48px; margin-bottom: 15px;">⚠️</div>
                <h3 style="color: #1f2937; margin: 0 0 10px 0; font-size: 18px; font-weight: 600;">Map Error</h3>
                <p style="color: #6b7280; margin: 0 0 20px 0; font-size: 14px; line-height: 1.5;">${message}</p>
            `;
            
            mapContainer.style.position = 'relative';
            mapContainer.appendChild(errorDiv);
        }
    }

})();
