/**
 * Quick View JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Quick View Manager
    var QuickViewManager = {
        modal: null,
        isOpen: false,
        currentPropertyId: null,
        isLoading: false,

        /**
         * Initialize
         */
        init: function() {
            this.modal = $('#resbs-quickview-modal');
            this.bindEvents();
            this.initKeyboardNavigation();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;

            // Quick view button clicks
            $(document).on('click', '.resbs-quickview-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var propertyId = $(this).data('property-id');
                if (propertyId) {
                    self.openQuickView(propertyId);
                }
            });

            // Close modal events
            $(document).on('click', '.resbs-quickview-close, .resbs-quickview-overlay', function(e) {
                e.preventDefault();
                self.closeQuickView();
            });

            // Prevent modal content clicks from closing modal
            $(document).on('click', '.resbs-quickview-container', function(e) {
                e.stopPropagation();
            });

            // Gallery thumbnail clicks
            $(document).on('click', '.resbs-quickview-thumb', function(e) {
                e.preventDefault();
                self.switchMainImage($(this));
            });

            // Book now button
            $(document).on('click', '.resbs-book-now', function(e) {
                e.preventDefault();
                var propertyId = $(this).data('property-id');
                self.handleBookNow(propertyId);
            });

            // Favorite button
            $(document).on('click', '.resbs-favorite-btn', function(e) {
                e.preventDefault();
                var propertyId = $(this).data('property-id');
                self.handleFavorite(propertyId, $(this));
            });

            // Map preview click
            $(document).on('click', '.resbs-map-preview', function(e) {
                e.preventDefault();
                var $this = $(this);
                var lat = $this.data('lat');
                var lng = $this.data('lng');
                if (lat && lng) {
                    self.openMap(lat, lng);
                }
            });

            // View details button
            $(document).on('click', '.resbs-view-details', function(e) {
                // Let the link work normally
                self.closeQuickView();
            });
        },

        /**
         * Initialize keyboard navigation
         */
        initKeyboardNavigation: function() {
            var self = this;

            $(document).on('keydown', function(e) {
                if (!self.isOpen) return;

                switch(e.key) {
                    case 'Escape':
                        e.preventDefault();
                        self.closeQuickView();
                        break;
                    case 'ArrowLeft':
                        e.preventDefault();
                        self.navigateGallery('prev');
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        self.navigateGallery('next');
                        break;
                }
            });
        },

        /**
         * Open quick view modal
         */
        openQuickView: function(propertyId) {
            if (this.isLoading || this.isOpen) return;

            this.currentPropertyId = propertyId;
            this.isLoading = true;
            this.isOpen = true;

            // Show modal
            this.modal.addClass('active').attr('aria-hidden', 'false');
            $('body').addClass('resbs-quickview-open');

            // Show loading state
            this.showLoading();

            // Load content via AJAX
            this.loadQuickViewContent(propertyId);
        },

        /**
         * Close quick view modal
         */
        closeQuickView: function() {
            if (!this.isOpen) return;

            this.isOpen = false;
            this.currentPropertyId = null;

            // Hide modal
            this.modal.removeClass('active').attr('aria-hidden', 'true');
            $('body').removeClass('resbs-quickview-open');

            // Clear content
            this.modal.find('.resbs-quickview-wrapper').empty();

            // Reset focus
            $('.resbs-quickview-btn[data-property-id="' + this.currentPropertyId + '"]').focus();
        },

        /**
         * Show loading state
         */
        showLoading: function() {
            var loadingHtml = '<div class="resbs-quickview-loading">' +
                '<div class="resbs-quickview-spinner"></div>' +
                '<span>' + resbs_quickview_ajax.messages.loading + '</span>' +
                '</div>';
            
            this.modal.find('.resbs-quickview-wrapper').html(loadingHtml);
        },

        /**
         * Load quick view content via AJAX
         */
        loadQuickViewContent: function(propertyId) {
            var self = this;

            $.ajax({
                url: resbs_quickview_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_get_quickview',
                    property_id: propertyId,
                    nonce: resbs_quickview_ajax.nonce
                },
                success: function(response) {
                    self.isLoading = false;
                    
                    if (response.success && response.data.content) {
                        self.modal.find('.resbs-quickview-wrapper').html(response.data.content);
                        self.initializeQuickViewFeatures();
                        self.focusModal();
                    } else {
                        self.showError(response.data.message || resbs_quickview_ajax.messages.error);
                    }
                },
                error: function() {
                    self.isLoading = false;
                    self.showError(resbs_quickview_ajax.messages.error);
                }
            });
        },

        /**
         * Show error message
         */
        showError: function(message) {
            var errorHtml = '<div class="resbs-quickview-error">' +
                '<div class="resbs-error-icon">⚠️</div>' +
                '<div class="resbs-error-message">' + message + '</div>' +
                '<button type="button" class="resbs-btn resbs-btn-primary resbs-quickview-close">' +
                resbs_quickview_ajax.messages.close || 'Close' +
                '</button>' +
                '</div>';
            
            this.modal.find('.resbs-quickview-wrapper').html(errorHtml);
        },

        /**
         * Initialize quick view features
         */
        initializeQuickViewFeatures: function() {
            this.initializeGallery();
            this.initializeMap();
            this.initializeAnimations();
        },

        /**
         * Initialize gallery functionality
         */
        initializeGallery: function() {
            var $thumbs = this.modal.find('.resbs-quickview-thumb');
            if ($thumbs.length > 0) {
                $thumbs.first().addClass('active');
            }
        },

        /**
         * Switch main image
         */
        switchMainImage: function($thumb) {
            var $mainImage = this.modal.find('.resbs-quickview-image');
            var newSrc = $thumb.find('img').data('full');
            var newAlt = $thumb.find('img').attr('alt');

            if (newSrc && newSrc !== $mainImage.attr('src')) {
                // Update active thumbnail
                this.modal.find('.resbs-quickview-thumb').removeClass('active');
                $thumb.addClass('active');

                // Fade out current image
                $mainImage.fadeOut(200, function() {
                    $mainImage.attr('src', newSrc).attr('alt', newAlt).fadeIn(200);
                });
            }
        },

        /**
         * Navigate gallery with keyboard
         */
        navigateGallery: function(direction) {
            var $thumbs = this.modal.find('.resbs-quickview-thumb');
            var $active = $thumbs.filter('.active');
            var index = $thumbs.index($active);

            if (direction === 'prev') {
                index = index > 0 ? index - 1 : $thumbs.length - 1;
            } else {
                index = index < $thumbs.length - 1 ? index + 1 : 0;
            }

            this.switchMainImage($thumbs.eq(index));
        },

        /**
         * Initialize map functionality
         */
        initializeMap: function() {
            // Map functionality can be extended here
            // For now, clicking the map preview will open in new tab
        },

        /**
         * Open map in new tab
         */
        openMap: function(lat, lng) {
            var mapUrl = 'https://www.google.com/maps?q=' + lat + ',' + lng;
            window.open(mapUrl, '_blank');
        },

        /**
         * Initialize animations
         */
        initializeAnimations: function() {
            var $wrapper = this.modal.find('.resbs-quickview-wrapper');
            $wrapper.addClass('resbs-quickview-fade-in');
        },

        /**
         * Focus modal for accessibility
         */
        focusModal: function() {
            var $closeBtn = this.modal.find('.resbs-quickview-close');
            if ($closeBtn.length) {
                $closeBtn.focus();
            }
        },

        /**
         * Handle book now action
         */
        handleBookNow: function(propertyId) {
            // Get property URL and redirect to booking
            var propertyUrl = this.modal.find('.resbs-view-details').attr('href');
            if (propertyUrl) {
                window.location.href = propertyUrl + '#booking';
            }
        },

        /**
         * Handle favorite action
         */
        handleFavorite: function(propertyId, $button) {
            if (!propertyId) return;

            var $icon = $button.find('.dashicons');
            var $text = $button.find('.resbs-favorite-text');
            var isActive = $button.hasClass('active');

            // Toggle visual state immediately
            $button.toggleClass('active');
            $icon.toggleClass('dashicons-heart dashicons-heart-filled');
            $text.text(isActive ? 
                resbs_quickview_ajax.messages.add_to_favorites || 'Add to Favorites' : 
                resbs_quickview_ajax.messages.remove_from_favorites || 'Remove from Favorites'
            );

            // Make AJAX request
            $.ajax({
                url: resbs_quickview_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_toggle_favorite',
                    property_id: propertyId,
                    nonce: resbs_quickview_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update button state based on response
                        if (response.data.is_favorite) {
                            $button.addClass('active');
                            $icon.removeClass('dashicons-heart').addClass('dashicons-heart-filled');
                            $text.text(resbs_quickview_ajax.messages.remove_from_favorites || 'Remove from Favorites');
                        } else {
                            $button.removeClass('active');
                            $icon.removeClass('dashicons-heart-filled').addClass('dashicons-heart');
                            $text.text(resbs_quickview_ajax.messages.add_to_favorites || 'Add to Favorites');
                        }

                        // Show notification
                        if (response.data.message) {
                            QuickViewManager.showNotification(response.data.message, 'success');
                        }
                    }
                },
                error: function() {
                    // Revert button state on error
                    $button.toggleClass('active');
                    $icon.toggleClass('dashicons-heart dashicons-heart-filled');
                    $text.text(isActive ? 
                        resbs_quickview_ajax.messages.remove_from_favorites || 'Remove from Favorites' : 
                        resbs_quickview_ajax.messages.add_to_favorites || 'Add to Favorites'
                    );
                    
                    QuickViewManager.showNotification(resbs_quickview_ajax.messages.error, 'error');
                }
            });
        },

        /**
         * Show notification
         */
        showNotification: function(message, type) {
            var $notification = $('<div class="resbs-notification resbs-notification-' + type + '">' + message + '</div>');
            
            $('body').append($notification);
            
            setTimeout(function() {
                $notification.addClass('show');
            }, 100);
            
            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 3000);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        QuickViewManager.init();
    });

    // Add quick view button to property cards
    $(document).on('mouseenter', '.resbs-property-card', function() {
        var $card = $(this);
        if ($card.find('.resbs-quickview-trigger').length === 0) {
            var propertyId = $card.find('[data-property-id]').first().data('property-id');
            if (propertyId) {
                var $trigger = $('<div class="resbs-quickview-trigger">' +
                    '<button type="button" class="resbs-quickview-btn" data-property-id="' + propertyId + '">' +
                    '<span class="dashicons dashicons-visibility"></span>' +
                    '<span class="resbs-quickview-text">' + (resbs_quickview_ajax.messages.quick_view || 'Quick View') + '</span>' +
                    '</button>' +
                    '</div>');
                $card.append($trigger);
            }
        }
    });

    // Smooth scroll for anchor links
    $(document).on('click', 'a[href*="#"]', function(e) {
        var target = $(this.getAttribute('href'));
        if (target.length && target.offset().top) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });

    // Lazy loading for images
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            imageObserver.unobserve(img);
                        }
                    }
                });
            });

            $('.resbs-quickview-image[data-src]').each(function() {
                imageObserver.observe(this);
            });
        }
    }

    // Initialize lazy loading
    initLazyLoading();

    // Expose QuickViewManager globally for external use
    window.RESBSQuickView = QuickViewManager;

})(jQuery);

/**
 * CSS for Notifications (if not already included)
 */
var notificationCSS = `
<style>
.resbs-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 15px 20px;
    border-radius: 4px;
    color: #fff;
    font-weight: 600;
    z-index: 999999;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    max-width: 300px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.resbs-notification.show {
    transform: translateX(0);
}

.resbs-notification-success {
    background: #28a745;
}

.resbs-notification-error {
    background: #dc3545;
}

.resbs-notification-info {
    background: #17a2b8;
}

@media (max-width: 480px) {
    .resbs-notification {
        right: 10px;
        left: 10px;
        max-width: none;
        transform: translateY(-100%);
    }
    
    .resbs-notification.show {
        transform: translateY(0);
    }
}
</style>
`;

// Add notification CSS to head if not already present
if (!document.getElementById('resbs-notification-css')) {
    var style = document.createElement('style');
    style.id = 'resbs-notification-css';
    style.innerHTML = notificationCSS;
    document.head.appendChild(style);
}
