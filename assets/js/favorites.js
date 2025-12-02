/**
 * Favorites JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Global variables
    let favoritesCount = 0;
    let isProcessing = false;

    /**
     * Initialize favorites when document is ready
     */
    $(document).ready(function() {
        initializeFavorites();
    });

    /**
     * Initialize favorites functionality
     */
    function initializeFavorites() {
        // Handle favorite button clicks
        $(document).on('click', '.resbs-favorite-btn', function(e) {
            e.preventDefault();
            handleFavoriteToggle($(this));
        });

        // Handle remove button clicks
        $(document).on('click', '.resbs-favorite-remove-btn', function(e) {
            e.preventDefault();
            handleFavoriteRemove($(this));
        });

        // Handle clear favorites button
        $(document).on('click', '.resbs-clear-favorites-btn', function(e) {
            e.preventDefault();
            handleClearFavorites($(this));
        });

        // Update favorites count on page load
        updateFavoritesCount();
    }

    /**
     * Handle favorite toggle
     */
    function handleFavoriteToggle($btn) {
        if (isProcessing) {
            return;
        }

        const propertyId = $btn.data('property-id');
        const context = $btn.data('context') || 'card';

        if (!propertyId) {
            showMessage('error', resbs_favorites_ajax.messages.error);
            return;
        }

        // Check if user is logged in
        if (!resbs_favorites_ajax.user_logged_in) {
            showLoginMessage();
            return;
        }

        // Add loading state
        $btn.addClass('loading');
        isProcessing = true;

        // Make AJAX request
        $.ajax({
            url: resbs_favorites_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_toggle_favorite',
                property_id: propertyId,
                nonce: resbs_favorites_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateFavoriteButton($btn, response.data.is_favorite);
                    updateFavoritesCount();
                    showMessage('success', response.data.message);
                    
                    // Update favorites page if it exists
                    if (context === 'favorites') {
                        removePropertyFromFavorites(propertyId);
                    }
                } else {
                    showMessage('error', response.data.message);
                }
            },
            error: function() {
                showMessage('error', resbs_favorites_ajax.messages.error);
            },
            complete: function() {
                $btn.removeClass('loading');
                isProcessing = false;
            }
        });
    }

    /**
     * Handle favorite remove
     */
    function handleFavoriteRemove($btn) {
        if (isProcessing) {
            return;
        }

        const propertyId = $btn.data('property-id');
        const $card = $btn.closest('.resbs-favorite-property-card');

        if (!propertyId) {
            showMessage('error', resbs_favorites_ajax.messages.error);
            return;
        }

        // Add loading state
        $btn.addClass('loading');
        isProcessing = true;

        // Make AJAX request
        $.ajax({
            url: resbs_favorites_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_toggle_favorite',
                property_id: propertyId,
                nonce: resbs_favorites_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Remove card with animation
                    $card.fadeOut(300, function() {
                        $(this).remove();
                        updateFavoritesCount();
                        
                        // Check if no more favorites
                        if ($('.resbs-favorite-property-card').length === 0) {
                            showNoFavoritesMessage();
                        }
                    });
                    
                    showMessage('success', response.data.message);
                } else {
                    showMessage('error', response.data.message);
                }
            },
            error: function() {
                showMessage('error', resbs_favorites_ajax.messages.error);
            },
            complete: function() {
                $btn.removeClass('loading');
                isProcessing = false;
            }
        });
    }

    /**
     * Handle clear favorites
     */
    function handleClearFavorites($btn) {
        if (isProcessing) {
            return;
        }

        // Confirm action
        if (!confirm(resbs_favorites_ajax.messages.clear_confirm)) {
            return;
        }

        // Add loading state
        $btn.addClass('loading');
        isProcessing = true;

        // Make AJAX request
        $.ajax({
            url: resbs_favorites_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_clear_favorites',
                nonce: resbs_favorites_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Clear all favorites with animation
                    $('.resbs-favorite-property-card').fadeOut(300, function() {
                        $(this).remove();
                    });
                    
                    // Show no favorites message
                    setTimeout(function() {
                        showNoFavoritesMessage();
                        updateFavoritesCount();
                    }, 300);
                    
                    showMessage('success', response.data.message);
                } else {
                    showMessage('error', response.data.message);
                }
            },
            error: function() {
                showMessage('error', resbs_favorites_ajax.messages.error);
            },
            complete: function() {
                $btn.removeClass('loading');
                isProcessing = false;
            }
        });
    }

    /**
     * Update favorite button state
     */
    function updateFavoriteButton($btn, isFavorite) {
        const $icon = $btn.find('.dashicons');
        const $text = $btn.find('.resbs-favorite-text');
        
        if (isFavorite) {
            $btn.addClass('resbs-favorite-btn-active');
            $icon.removeClass('dashicons-heart').addClass('dashicons-heart-filled');
            if ($text.length) {
                $text.text(resbs_favorites_ajax.messages.remove_from_favorites);
            }
        } else {
            $btn.removeClass('resbs-favorite-btn-active');
            $icon.removeClass('dashicons-heart-filled').addClass('dashicons-heart');
            if ($text.length) {
                $text.text(resbs_favorites_ajax.messages.add_to_favorites);
            }
        }
    }

    /**
     * Update favorites count
     */
    function updateFavoritesCount() {
        $.ajax({
            url: resbs_favorites_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_get_favorites',
                nonce: resbs_favorites_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    favoritesCount = response.data.count;
                    $('.resbs-favorites-count').text('(' + favoritesCount + ')');
                    
                    // Update widget count if exists
                    $('.resbs-favorites-widget .resbs-favorites-count').text('(' + favoritesCount + ')');
                }
            },
            error: function() {
                // Failed to update favorites count
            }
        });
    }

    /**
     * Remove property from favorites page
     */
    function removePropertyFromFavorites(propertyId) {
        const $card = $('.resbs-favorite-property-card[data-property-id="' + propertyId + '"]');
        if ($card.length) {
            $card.fadeOut(300, function() {
                $(this).remove();
                
                // Check if no more favorites
                if ($('.resbs-favorite-property-card').length === 0) {
                    showNoFavoritesMessage();
                }
            });
        }
    }

    /**
     * Show no favorites message
     */
    function showNoFavoritesMessage() {
        const $container = $('.resbs-favorites-container');
        if ($container.length) {
            $container.html(`
                <div class="resbs-favorites-empty">
                    <div class="resbs-favorites-empty-icon">
                        <span class="dashicons dashicons-heart"></span>
                    </div>
                    <h3>${resbs_favorites_ajax.messages.no_favorites}</h3>
                    <p>${resbs_favorites_ajax.messages.start_exploring || 'Start exploring properties and add them to your favorites to see them here.'}</p>
                    <a href="${resbs_favorites_ajax.browse_url || '/properties/'}" class="resbs-favorites-browse-btn">
                        ${resbs_favorites_ajax.messages.browse_properties || 'Browse Properties'}
                    </a>
                </div>
            `);
        }
    }

    /**
     * Show message
     */
    function showMessage(type, message) {
        // Use toast notification if available, otherwise show banner
        if (typeof showToastNotification === 'function') {
            // Use the toast notification instead of top banner
            showToastNotification(message, type);
            return;
        }
        
        // Fallback to old banner (only if toast notification not available)
        // Remove existing messages
        $('.resbs-favorites-message').remove();
        
        // Create new message
        const $message = $('<div class="resbs-favorites-message ' + type + ' show">' + message + '</div>');
        
        // Insert message
        const $container = $('.resbs-favorites-container');
        if ($container.length) {
            $container.prepend($message);
        } else {
            $('body').prepend($message);
        }
        
        // Auto-hide message after 3 seconds
        setTimeout(function() {
            $message.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    /**
     * Show login message
     */
    function showLoginMessage() {
        const message = resbs_favorites_ajax.messages.login_required;
        const loginUrl = resbs_favorites_ajax.login_url;
        
        // Create login modal or redirect
        if (confirm(message + ' Click OK to login.')) {
            window.location.href = loginUrl;
        }
    }

    /**
     * Load favorites for a specific container
     */
    function loadFavorites($container) {
        const $loading = $('<div class="resbs-favorites-loading">' + resbs_favorites_ajax.messages.loading + '</div>');
        $container.html($loading);
        
        $.ajax({
            url: resbs_favorites_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_get_favorites',
                nonce: resbs_favorites_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderFavorites($container, response.data.properties);
                } else {
                    $container.html('<div class="resbs-favorites-error">' + response.data.message + '</div>');
                }
            },
            error: function() {
                $container.html('<div class="resbs-favorites-error">' + resbs_favorites_ajax.messages.error + '</div>');
            }
        });
    }

    /**
     * Render favorites
     */
    function renderFavorites($container, properties) {
        if (properties.length === 0) {
            $container.html(`
                <div class="resbs-favorites-empty">
                    <div class="resbs-favorites-empty-icon">
                        <span class="dashicons dashicons-heart"></span>
                    </div>
                    <h3>${resbs_favorites_ajax.messages.no_favorites}</h3>
                    <p>${resbs_favorites_ajax.messages.start_exploring || 'Start exploring properties and add them to your favorites to see them here.'}</p>
                </div>
            `);
            return;
        }

        let html = '<div class="resbs-favorites-grid">';
        
        properties.forEach(function(property) {
            html += renderFavoriteCard(property);
        });
        
        html += '</div>';
        $container.html(html);
    }

    /**
     * Render favorite card
     */
    function renderFavoriteCard(property) {
        const image = property.featured_image || '';
        const price = property.price ? formatPrice(property.price) : '';
        const bedrooms = property.bedrooms || '';
        const bathrooms = property.bathrooms || '';
        const area = property.area || '';
        const location = property.location ? property.location.join(', ') : '';
        
        return `
            <div class="resbs-favorite-property-card" data-property-id="${property.id}">
                <div class="resbs-favorite-property-image">
                    <a href="${property.permalink}">
                        ${image ? `<img src="${image}" alt="${property.title}">` : '<div class="resbs-favorite-property-placeholder"><span class="dashicons dashicons-camera"></span></div>'}
                    </a>
                    <button type="button" class="resbs-favorite-btn resbs-favorite-btn-active" data-property-id="${property.id}">
                        <span class="dashicons dashicons-heart-filled"></span>
                    </button>
                </div>
                <div class="resbs-favorite-property-content">
                    <h4 class="resbs-favorite-property-title">
                        <a href="${property.permalink}">${property.title}</a>
                    </h4>
                    ${price ? `<div class="resbs-favorite-property-price">${price}</div>` : ''}
                    <div class="resbs-favorite-property-details">
                        ${bedrooms ? `<span class="resbs-favorite-property-detail"><span class="dashicons dashicons-bed-alt"></span>${bedrooms} Bed</span>` : ''}
                        ${bathrooms ? `<span class="resbs-favorite-property-detail"><span class="dashicons dashicons-bath"></span>${bathrooms} Bath</span>` : ''}
                        ${area ? `<span class="resbs-favorite-property-detail"><span class="dashicons dashicons-fullscreen-alt"></span>${area} sq ft</span>` : ''}
                    </div>
                    ${location ? `<div class="resbs-favorite-property-location"><span class="dashicons dashicons-location"></span>${location}</div>` : ''}
                    <div class="resbs-favorite-property-actions">
                        <a href="${property.permalink}" class="resbs-favorite-view-btn">${resbs_favorites_ajax.messages.view_property}</a>
                        <button type="button" class="resbs-favorite-remove-btn" data-property-id="${property.id}">
                            <span class="dashicons dashicons-heart-filled"></span>
                            ${resbs_favorites_ajax.messages.remove_property}
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    /**
     * Format price
     */
    function formatPrice(price) {
        if (!price) return '';
        
        const numPrice = parseInt(price);
        if (isNaN(numPrice)) return price;
        
        return '$' + numPrice.toLocaleString();
    }

    /**
     * Public API
     */
    window.RESBSFavorites = {
        toggleFavorite: handleFavoriteToggle,
        removeFavorite: handleFavoriteRemove,
        clearFavorites: handleClearFavorites,
        updateCount: updateFavoritesCount,
        loadFavorites: loadFavorites,
        getCount: function() {
            return favoritesCount;
        }
    };

})(jQuery);
