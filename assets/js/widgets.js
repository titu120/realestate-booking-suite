/**
 * WordPress Widget JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Initialize widgets when document is ready
    $(document).ready(function() {
        initPropertyGridWidgets();
    });

    /**
     * Initialize Property Grid Widgets
     */
    function initPropertyGridWidgets() {
        $('.resbs-property-grid-widget').each(function() {
            var $widget = $(this);
            var widgetId = $widget.attr('id');
            var settings = $widget.data('settings');

            // Initialize filter form
            initFilterForm($widget, widgetId, settings);
            
            // Initialize favorite buttons
            initFavoriteButtons($widget);
            
            // Initialize book buttons
            initBookButtons($widget);
        });
    }

    /**
     * Initialize Filter Form
     */
    function initFilterForm($widget, widgetId, settings) {
        var $form = $widget.find('.resbs-filter-form');
        var $grid = $widget.find('.resbs-property-grid');
        var $loading = $widget.find('.resbs-widget-loading');
        var $noProperties = $widget.find('.resbs-widget-no-properties');

        if ($form.length === 0) {
            return;
        }

        // Handle form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            filterProperties($widget, widgetId, settings);
        });

        // Handle reset button
        $widget.find('.resbs-reset-btn').on('click', function() {
            $form[0].reset();
            filterProperties($widget, widgetId, settings);
        });

        // Handle real-time filtering on select change
        $form.find('select').on('change', function() {
            if (settings.real_time_filter) {
                filterProperties($widget, widgetId, settings);
            }
        });
    }

    /**
     * Filter Properties via AJAX
     */
    function filterProperties($widget, widgetId, settings) {
        var $form = $widget.find('.resbs-filter-form');
        var $grid = $widget.find('.resbs-property-grid');
        var $loading = $widget.find('.resbs-widget-loading');
        var $noProperties = $widget.find('.resbs-widget-no-properties');

        // Show loading
        $loading.show();
        $grid.hide();
        $noProperties.hide();

        // Get form data
        var formData = {
            action: 'resbs_filter_widget_properties',
            nonce: resbs_widget_ajax.nonce,
            widget_id: widgetId,
            settings: settings,
            filters: $form.serialize()
        };

        // Make AJAX request
        $.ajax({
            url: resbs_widget_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                $loading.hide();
                
                if (response.success && response.data.html) {
                    $grid.html(response.data.html).show();
                    $noProperties.hide();
                    
                    // Re-initialize buttons for new content
                    initFavoriteButtons($widget);
                    initBookButtons($widget);
                } else {
                    $grid.hide();
                    $noProperties.show();
                }
            },
            error: function() {
                $loading.hide();
                $grid.hide();
                $noProperties.show();
                
                // Show error message
                $noProperties.html('<p>' + resbs_widget_ajax.messages.error + '</p>');
            }
        });
    }

    /**
     * Initialize Favorite Buttons
     */
    function initFavoriteButtons($widget) {
        $widget.find('.resbs-favorite-btn').off('click').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var propertyId = $btn.data('property-id');
            
            if (!propertyId) {
                return;
            }

            // Toggle button state
            $btn.toggleClass('active');
            
            // Make AJAX request
            $.ajax({
                url: resbs_widget_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'resbs_toggle_favorite',
                    nonce: resbs_widget_ajax.nonce,
                    property_id: propertyId
                },
                success: function(response) {
                    if (response.success) {
                        // Update button state based on response
                        if (response.data.is_favorite) {
                            $btn.addClass('active');
                        } else {
                            $btn.removeClass('active');
                        }
                        
                        // Show notification if available
                        if (response.data.message) {
                            showNotification(response.data.message, 'success');
                        }
                    }
                },
                error: function() {
                    // Revert button state on error
                    $btn.toggleClass('active');
                    showNotification(resbs_widget_ajax.messages.error, 'error');
                }
            });
        });
    }

    /**
     * Initialize Book Buttons
     */
    function initBookButtons($widget) {
        $widget.find('.resbs-book-btn').off('click').on('click', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var propertyId = $btn.data('property-id');
            
            if (!propertyId) {
                return;
            }

            // Redirect to booking page or show booking modal
            var bookingUrl = $btn.closest('.resbs-property-card').find('.resbs-property-title a').attr('href');
            if (bookingUrl) {
                window.location.href = bookingUrl + '#booking';
            }
        });
    }

    /**
     * Show Notification
     */
    function showNotification(message, type) {
        // Create notification element
        var $notification = $('<div class="resbs-notification resbs-notification-' + type + '">' + message + '</div>');
        
        // Add to body
        $('body').append($notification);
        
        // Show notification
        setTimeout(function() {
            $notification.addClass('show');
        }, 100);
        
        // Hide notification after 3 seconds
        setTimeout(function() {
            $notification.removeClass('show');
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Handle Widget Form Toggle
     */
    $(document).on('change', 'input[id*="show_filters"]', function() {
        var $checkbox = $(this);
        var $filterOptions = $checkbox.closest('.resbs-widget-form').find('.resbs-filter-options');
        
        if ($checkbox.is(':checked')) {
            $filterOptions.show();
        } else {
            $filterOptions.hide();
        }
    });

    /**
     * Lazy Loading for Images
     */
    function initLazyLoading() {
        if ('IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            $('.resbs-property-image img[data-src]').each(function() {
                imageObserver.observe(this);
            });
        }
    }

    // Initialize lazy loading
    initLazyLoading();

    /**
     * Responsive Grid Adjustment
     */
    function adjustGridColumns() {
        $('.resbs-property-grid-widget').each(function() {
            var $widget = $(this);
            var $grid = $widget.find('.resbs-property-grid');
            var columns = $grid.data('columns');
            var windowWidth = $(window).width();
            
            // Adjust columns based on screen size
            if (windowWidth < 480) {
                $grid.removeClass('resbs-grid-2-cols resbs-grid-3-cols resbs-grid-4-cols').addClass('resbs-grid-1-cols');
            } else if (windowWidth < 768) {
                if (columns > 2) {
                    $grid.removeClass('resbs-grid-3-cols resbs-grid-4-cols').addClass('resbs-grid-2-cols');
                }
            } else if (windowWidth < 1024) {
                if (columns > 3) {
                    $grid.removeClass('resbs-grid-4-cols').addClass('resbs-grid-3-cols');
                }
            }
        });
    }

    // Adjust grid on window resize
    $(window).on('resize', debounce(adjustGridColumns, 250));
    
    // Initial adjustment
    adjustGridColumns();

    /**
     * Debounce function
     */
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

    /**
     * Smooth Scroll for Anchor Links
     */
    $('.resbs-property-title a[href*="#"]').on('click', function(e) {
        var target = $(this.getAttribute('href'));
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });

    /**
     * Keyboard Navigation Support
     */
    $('.resbs-property-card').on('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            $(this).find('.resbs-property-title a')[0].click();
        }
    });

    // Make property cards focusable
    $('.resbs-property-card').attr('tabindex', '0');

})(jQuery);

/**
 * CSS for Notifications
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
    z-index: 9999;
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

// Add notification CSS to head
if (!document.getElementById('resbs-notification-css')) {
    var style = document.createElement('style');
    style.id = 'resbs-notification-css';
    style.innerHTML = notificationCSS;
    document.head.appendChild(style);
}
