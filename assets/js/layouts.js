/**
 * Layout JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initializeLayouts();
    });

    /**
     * Initialize layout functionality
     */
    function initializeLayouts() {
        // Initialize carousels
        $('.resbs-property-carousel').each(function() {
            initializeCarousel($(this));
        });

        // Handle layout switching
        $('.resbs-layout-switcher').on('click', function(e) {
            e.preventDefault();
            const layout = $(this).data('layout');
            switchLayout(layout);
        });

        // Handle window resize for responsive layouts
        $(window).on('resize', debounce(function() {
            updateResponsiveLayouts();
        }, 250));
    }

    /**
     * Initialize carousel
     */
    function initializeCarousel($carousel) {
        const $track = $carousel.find('.resbs-carousel-track');
        const $cards = $track.find('.resbs-property-card');
        const $prevBtn = $carousel.find('.resbs-carousel-prev');
        const $nextBtn = $carousel.find('.resbs-carousel-next');
        const $dots = $carousel.find('.resbs-carousel-dots');
        
        const autoplay = $carousel.data('autoplay') === 'true';
        const autoplaySpeed = parseInt($carousel.data('autoplay-speed')) || 3000;
        const showDots = $carousel.data('show-dots') === 'true';
        const showArrows = $carousel.data('show-arrows') === 'true';
        
        let currentIndex = 0;
        let autoplayInterval;
        let isTransitioning = false;
        
        // Calculate items per view based on container width
        function getItemsPerView() {
            const containerWidth = $carousel.width();
            const cardWidth = 320; // Base card width + gap
            return Math.max(1, Math.floor(containerWidth / cardWidth));
        }
        
        // Update carousel position
        function updateCarousel() {
            if (isTransitioning) return;
            
            const itemsPerView = getItemsPerView();
            const maxIndex = Math.max(0, $cards.length - itemsPerView);
            currentIndex = Math.min(currentIndex, maxIndex);
            
            const translateX = -currentIndex * (100 / itemsPerView);
            $track.css('transform', `translateX(${translateX}%)`);
            
            // Update dots
            if (showDots) {
                updateDots();
            }
            
            // Update arrow states
            if (showArrows) {
                $prevBtn.prop('disabled', currentIndex === 0);
                $nextBtn.prop('disabled', currentIndex >= maxIndex);
            }
        }
        
        // Update dots
        function updateDots() {
            $dots.find('.resbs-carousel-dot').removeClass('active');
            $dots.find('.resbs-carousel-dot').eq(currentIndex).addClass('active');
        }
        
        // Create dots
        function createDots() {
            if (!showDots) return;
            
            $dots.empty();
            const itemsPerView = getItemsPerView();
            const totalSlides = Math.ceil($cards.length / itemsPerView);
            
            for (let i = 0; i < totalSlides; i++) {
                const $dot = $('<button class="resbs-carousel-dot" data-slide="' + i + '"></button>');
                $dots.append($dot);
            }
            
            $dots.find('.resbs-carousel-dot').on('click', function() {
                const slideIndex = parseInt($(this).data('slide'));
                currentIndex = slideIndex * itemsPerView;
                updateCarousel();
            });
        }
        
        // Next slide
        function nextSlide() {
            if (isTransitioning) return;
            
            const itemsPerView = getItemsPerView();
            const maxIndex = Math.max(0, $cards.length - itemsPerView);
            
            if (currentIndex < maxIndex) {
                currentIndex++;
                updateCarousel();
            } else if (autoplay) {
                // Loop to beginning
                currentIndex = 0;
                updateCarousel();
            }
        }
        
        // Previous slide
        function prevSlide() {
            if (isTransitioning) return;
            
            if (currentIndex > 0) {
                currentIndex--;
                updateCarousel();
            } else if (autoplay) {
                // Loop to end
                const itemsPerView = getItemsPerView();
                currentIndex = Math.max(0, $cards.length - itemsPerView);
                updateCarousel();
            }
        }
        
        // Start autoplay
        function startAutoplay() {
            if (!autoplay) return;
            
            autoplayInterval = setInterval(nextSlide, autoplaySpeed);
        }
        
        // Stop autoplay
        function stopAutoplay() {
            if (autoplayInterval) {
                clearInterval(autoplayInterval);
                autoplayInterval = null;
            }
        }
        
        // Event handlers
        if (showArrows) {
            $nextBtn.on('click', function() {
                stopAutoplay();
                nextSlide();
                if (autoplay) {
                    setTimeout(startAutoplay, 2000);
                }
            });
            
            $prevBtn.on('click', function() {
                stopAutoplay();
                prevSlide();
                if (autoplay) {
                    setTimeout(startAutoplay, 2000);
                }
            });
        }
        
        // Pause autoplay on hover
        $carousel.on('mouseenter', stopAutoplay);
        $carousel.on('mouseleave', function() {
            if (autoplay) {
                startAutoplay();
            }
        });
        
        // Enhanced touch/swipe events with better mobile support
        let startX = 0;
        let startY = 0;
        let isDragging = false;
        let dragStartTime = 0;
        let velocity = 0;
        let lastMoveTime = 0;
        let lastMoveX = 0;
        
        // Touch start
        $track.on('touchstart', function(e) {
            const touch = e.originalEvent.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
            lastMoveX = touch.clientX;
            isDragging = true;
            dragStartTime = Date.now();
            lastMoveTime = dragStartTime;
            velocity = 0;
            stopAutoplay();
            
            // Add active class for visual feedback
            $carousel.addClass('resbs-carousel-dragging');
        });
        
        // Touch move
        $track.on('touchmove', function(e) {
            if (!isDragging) return;
            
            const touch = e.originalEvent.touches[0];
            const currentTime = Date.now();
            const deltaX = touch.clientX - lastMoveX;
            const deltaTime = currentTime - lastMoveTime;
            
            if (deltaTime > 0) {
                velocity = deltaX / deltaTime;
            }
            
            lastMoveX = touch.clientX;
            lastMoveTime = currentTime;
            
            // Prevent default to avoid scrolling
            e.preventDefault();
        });
        
        // Touch end
        $track.on('touchend', function(e) {
            if (!isDragging) return;
            
            const touch = e.originalEvent.changedTouches[0];
            const endX = touch.clientX;
            const endY = touch.clientY;
            const diffX = startX - endX;
            const diffY = startY - endY;
            const dragDuration = Date.now() - dragStartTime;
            
            // Remove dragging class
            $carousel.removeClass('resbs-carousel-dragging');
            
            // Calculate swipe threshold based on velocity and distance
            const minSwipeDistance = 50;
            const maxSwipeTime = 500;
            const minVelocity = 0.3;
            
            // Check if it's a valid horizontal swipe
            const isHorizontalSwipe = Math.abs(diffX) > Math.abs(diffY);
            const isLongEnough = Math.abs(diffX) > minSwipeDistance;
            const isFastEnough = Math.abs(velocity) > minVelocity;
            const isQuickEnough = dragDuration < maxSwipeTime;
            
            if (isHorizontalSwipe && (isLongEnough || (isFastEnough && isQuickEnough))) {
                if (diffX > 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }
            
            isDragging = false;
            velocity = 0;
            
            if (autoplay) {
                setTimeout(startAutoplay, 2000);
            }
        });
        
        // Mouse events for desktop
        $track.on('mousedown', function(e) {
            startX = e.clientX;
            startY = e.clientY;
            isDragging = true;
            dragStartTime = Date.now();
            stopAutoplay();
            e.preventDefault();
        });
        
        $track.on('mousemove', function(e) {
            if (!isDragging) return;
            e.preventDefault();
        });
        
        $track.on('mouseup', function(e) {
            if (!isDragging) return;
            
            const endX = e.clientX;
            const endY = e.clientY;
            const diffX = startX - endX;
            const diffY = startY - endY;
            
            if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 50) {
                if (diffX > 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }
            
            isDragging = false;
            
            if (autoplay) {
                setTimeout(startAutoplay, 2000);
            }
        });
        
        // Prevent context menu on long press
        $track.on('contextmenu', function(e) {
            e.preventDefault();
        });
        
        // Keyboard navigation
        $carousel.on('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                prevSlide();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                nextSlide();
            }
        });
        
        // Make carousel focusable
        $carousel.attr('tabindex', '0');
        
        // Initialize
        createDots();
        updateCarousel();
        startAutoplay();
        
        // Handle window resize
        $(window).on('resize', debounce(function() {
            createDots();
            updateCarousel();
        }, 250));
    }

    /**
     * Switch layout
     */
    function switchLayout(layout) {
        const $widget = $('.resbs-property-grid-widget');
        const $grid = $widget.find('.resbs-property-grid, .resbs-property-carousel');
        
        // Remove existing layout classes
        $widget.removeClass('resbs-layout-grid resbs-layout-list resbs-layout-carousel');
        $grid.removeClass('resbs-layout-grid resbs-layout-list resbs-layout-carousel');
        
        // Add new layout class
        $widget.addClass('resbs-layout-' + layout);
        $grid.addClass('resbs-layout-' + layout);
        
        // Update layout switcher active state
        $('.resbs-layout-switcher').removeClass('active');
        $('.resbs-layout-switcher[data-layout="' + layout + '"]').addClass('active');
        
        // Reinitialize carousel if needed
        if (layout === 'carousel') {
            $widget.find('.resbs-property-carousel').each(function() {
                initializeCarousel($(this));
            });
        }
        
        // Trigger layout change event
        $widget.trigger('layoutChanged', [layout]);
    }

    /**
     * Update responsive layouts
     */
    function updateResponsiveLayouts() {
        $('.resbs-property-carousel').each(function() {
            const $carousel = $(this);
            const $track = $carousel.find('.resbs-carousel-track');
            const $cards = $track.find('.resbs-property-card');
            
            if ($cards.length === 0) return;
            
            // Recalculate items per view
            const containerWidth = $carousel.width();
            const cardWidth = 320;
            const itemsPerView = Math.max(1, Math.floor(containerWidth / cardWidth));
            
            // Update card widths
            $cards.css('flex', '0 0 ' + (100 / itemsPerView) + '%');
            
            // Update carousel position
            const currentIndex = $carousel.data('current-index') || 0;
            const maxIndex = Math.max(0, $cards.length - itemsPerView);
            const newIndex = Math.min(currentIndex, maxIndex);
            
            if (newIndex !== currentIndex) {
                $carousel.data('current-index', newIndex);
                const translateX = -newIndex * (100 / itemsPerView);
                $track.css('transform', `translateX(${translateX}%)`);
            }
        });
    }

    /**
     * Debounce function
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = function() {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Layout switcher component
     */
    function createLayoutSwitcher() {
        const $switcher = $('<div class="resbs-layout-switcher-container"></div>');
        const $buttons = $('<div class="resbs-layout-switcher-buttons"></div>');
        
        const layouts = [
            { key: 'grid', label: 'Grid', icon: '⊞' },
            { key: 'list', label: 'List', icon: '☰' },
            { key: 'carousel', label: 'Carousel', icon: '▶' }
        ];
        
        layouts.forEach(function(layout) {
            const $button = $('<button class="resbs-layout-switcher" data-layout="' + layout.key + '" title="' + layout.label + '">' + layout.icon + '</button>');
            $buttons.append($button);
        });
        
        $switcher.append($buttons);
        return $switcher;
    }

    /**
     * Add layout switcher to widgets
     */
    function addLayoutSwitchers() {
        $('.resbs-property-grid-widget').each(function() {
            const $widget = $(this);
            
            if ($widget.find('.resbs-layout-switcher-container').length === 0) {
                const $switcher = createLayoutSwitcher();
                $widget.prepend($switcher);
            }
        });
    }

    /**
     * Initialize layout switchers
     */
    function initializeLayoutSwitchers() {
        addLayoutSwitchers();
        
        // Handle switcher clicks
        $(document).on('click', '.resbs-layout-switcher', function(e) {
            e.preventDefault();
            const layout = $(this).data('layout');
            const $widget = $(this).closest('.resbs-property-grid-widget');
            
            // Update widget layout
            $widget.removeClass('resbs-layout-grid resbs-layout-list resbs-layout-carousel');
            $widget.addClass('resbs-layout-' + layout);
            
            // Update switcher active state
            $widget.find('.resbs-layout-switcher').removeClass('active');
            $(this).addClass('active');
            
            // Reinitialize carousel if needed
            if (layout === 'carousel') {
                $widget.find('.resbs-property-carousel').each(function() {
                    initializeCarousel($(this));
                });
            }
        });
    }

    /**
     * Auto-detect and apply layout based on container width
     */
    function autoDetectLayout() {
        $('.resbs-property-grid-widget').each(function() {
            const $widget = $(this);
            const containerWidth = $widget.width();
            
            if (containerWidth < 600) {
                // Mobile: use list layout
                $widget.addClass('resbs-layout-list');
            } else if (containerWidth < 900) {
                // Tablet: use grid layout
                $widget.addClass('resbs-layout-grid');
            } else {
                // Desktop: use grid layout
                $widget.addClass('resbs-layout-grid');
            }
        });
    }

    // Initialize layout switchers
    initializeLayoutSwitchers();
    
    // Auto-detect layout on load
    autoDetectLayout();
    
    // Re-detect layout on resize
    $(window).on('resize', debounce(autoDetectLayout, 250));

})(jQuery);
