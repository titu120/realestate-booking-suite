/**
 * Favorites Saved Properties Page JavaScript
 * Extracted from class-resbs-favorites.php
 * 
 * @package RealEstate_Booking_Suite
 */

// Favorite button functionality for saved properties page
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
        const formData = new FormData();
        formData.append('action', 'resbs_toggle_favorite');
        formData.append('property_id', propertyId);
        
        // Use localized nonce
        if (typeof resbs_favorites !== 'undefined' && resbs_favorites.nonce) {
            formData.append('nonce', resbs_favorites.nonce);
        } else {
            return;
        }
        
        fetch(resbs_favorites.ajax_url, {
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
                    // If removed from favorites, reload page to update list
                    if (window.location.href.indexOf('saved-properties') !== -1) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 500);
                    }
                }
                
                // Show success message as toast notification
                if (data.data && data.data.message) {
                    if (typeof showToastNotification === 'function') {
                        const safeMessage = String(data.data.message).replace(/[<>]/g, '');
                        showToastNotification(safeMessage, 'success');
                    }
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
                let errorMessage = resbs_favorites.messages.error || 'An error occurred. Please try again.';
                
                if (data && data.data) {
                    if (typeof data.data === 'string') {
                        errorMessage = String(data.data).replace(/[<>]/g, '');
                    } else if (data.data.message) {
                        errorMessage = String(data.data.message).replace(/[<>]/g, '');
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
            
            alert(resbs_favorites.messages.error || 'An error occurred. Please try again.');
        });
    });
});

