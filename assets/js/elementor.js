/* Elementor Widget JavaScript */

jQuery(document).ready(function($) {
    'use strict';

    // Initialize all property grid widgets
    $('.resbs-elementor-property-grid').each(function() {
        initPropertyGrid($(this));
    });

    // Initialize all property carousel widgets with Swiper
    function initializeCarousels() {
        $('.resbs-property-carousel.swiper').each(function() {
            var $widget = $(this);
            if (!$widget.hasClass('resbs-swiper-initialized')) {
                initPropertyCarouselSwiper($widget);
            }
        });
    }
    
    // Initialize immediately if DOM ready
    initializeCarousels();
    
    // Also initialize when Elementor frontend is ready
    $(window).on('elementor/frontend/init', function() {
        setTimeout(initializeCarousels, 100);
    });
    
    // Fallback: Initialize after a short delay
    setTimeout(initializeCarousels, 500);

    /**
     * Initialize property grid widget
     */
    function initPropertyGrid($widget) {
        var settings = $widget.data('settings');
        var widgetId = $widget.attr('id');
        
        // Load initial properties
        loadProperties($widget, 1, {});
        
        // Handle filter form submission
        $widget.find('.resbs-filter-form').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serializeArray();
            var filters = {};
            
            $.each(formData, function(i, field) {
                if (field.value) {
                    filters[field.name] = field.value;
                }
            });
            
            loadProperties($widget, 1, filters);
        });
        
        // Handle reset button
        $widget.find('.resbs-reset-btn').on('click', function() {
            $widget.find('.resbs-filter-form')[0].reset();
            loadProperties($widget, 1, {});
        });
        
        // Handle pagination
        $(document).on('click', '#' + widgetId + ' .resbs-pagination-btn:not(.disabled)', function(e) {
            e.preventDefault();
            var page = $(this).data('page');
            if (page) {
                var filters = getCurrentFilters($widget);
                loadProperties($widget, page, filters);
            }
        });
        
        // Handle load more button
        $widget.find('.resbs-load-more-btn').on('click', function() {
            var $btn = $(this);
            var currentPage = parseInt($btn.data('page') || 1);
            var nextPage = currentPage + 1;
            var filters = getCurrentFilters($widget);
            
            $btn.prop('disabled', true).text(resbs_elementor_ajax.messages.loading);
            
            loadMoreProperties($widget, nextPage, filters, function() {
                $btn.data('page', nextPage).prop('disabled', false).text(resbs_elementor_ajax.messages.load_more);
            });
        });
        
        // Handle favorite button
        $(document).on('click', '#' + widgetId + ' .resbs-favorite-btn', function() {
            var $btn = $(this);
            var propertyId = $btn.data('property-id');
            
            if (!propertyId) return;
            
            toggleFavorite($btn, propertyId);
        });
        
        // Handle book button
        $(document).on('click', '#' + widgetId + ' .resbs-book-btn', function() {
            var $btn = $(this);
            var propertyId = $btn.data('property-id');
            
            if (!propertyId) return;
            
            // Redirect to property page or trigger booking
            window.location.href = '/property/' + propertyId + '/?action=book';
        });
    }

    /**
     * Load properties via AJAX
     */
    function loadProperties($widget, page, filters) {
        var settings = $widget.data('settings');
        var widgetId = $widget.attr('id');
        
        showLoading($widget);
        
        $.ajax({
            url: resbs_elementor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_elementor_load_properties',
                nonce: resbs_elementor_ajax.nonce,
                widget_id: widgetId,
                page: page,
                settings: settings,
                filters: filters
            },
            success: function(response) {
                if (response.success) {
                    displayProperties($widget, response.data);
                } else {
                    showError($widget, response.data || resbs_elementor_ajax.messages.error);
                }
            },
            error: function() {
                showError($widget, resbs_elementor_ajax.messages.error);
            },
            complete: function() {
                hideLoading($widget);
            }
        });
    }

    /**
     * Load more properties for infinite scroll
     */
    function loadMoreProperties($widget, page, filters, callback) {
        var settings = $widget.data('settings');
        var widgetId = $widget.attr('id');
        
        $.ajax({
            url: resbs_elementor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_elementor_load_properties',
                nonce: resbs_elementor_ajax.nonce,
                widget_id: widgetId,
                page: page,
                settings: settings,
                filters: filters,
                append: true
            },
            success: function(response) {
                if (response.success) {
                    appendProperties($widget, response.data);
                    if (callback) callback();
                } else {
                    showError($widget, response.data || resbs_elementor_ajax.messages.error);
                    if (callback) callback();
                }
            },
            error: function() {
                showError($widget, resbs_elementor_ajax.messages.error);
                if (callback) callback();
            }
        });
    }

    /**
     * Display properties in the grid
     */
    function displayProperties($widget, data) {
        var $grid = $widget.find('.resbs-property-grid');
        var $pagination = $widget.find('.resbs-pagination-wrapper');
        var $loadMore = $widget.find('.resbs-infinite-scroll-wrapper');
        var $noProperties = $widget.find('.resbs-no-properties');
        
        // Clear existing content
        $grid.empty();
        $pagination.empty();
        $noProperties.hide();
        
        if (data.properties && data.properties.length > 0) {
            // Add properties to grid
            $.each(data.properties, function(index, property) {
                var $propertyCard = createPropertyCard(property, $widget.data('settings'));
                $grid.append($propertyCard);
            });
            
            // Add pagination if enabled
            if (data.pagination && data.pagination.total_pages > 1) {
                $pagination.html(createPagination(data.pagination));
            }
            
            // Update load more button
            if (data.pagination && data.pagination.has_more) {
                $loadMore.find('.resbs-load-more-btn').data('page', data.pagination.current_page);
            } else {
                $loadMore.hide();
            }
            
            // Add animation
            $grid.find('.resbs-property-card').addClass('resbs-fade-in');
        } else {
            $noProperties.show();
        }
    }

    /**
     * Append properties for infinite scroll
     */
    function appendProperties($widget, data) {
        var $grid = $widget.find('.resbs-property-grid');
        var $loadMore = $widget.find('.resbs-infinite-scroll-wrapper');
        
        if (data.properties && data.properties.length > 0) {
            $.each(data.properties, function(index, property) {
                var $propertyCard = createPropertyCard(property, $widget.data('settings'));
                $propertyCard.addClass('resbs-slide-up');
                $grid.append($propertyCard);
            });
            
            // Update load more button
            if (data.pagination && data.pagination.has_more) {
                $loadMore.find('.resbs-load-more-btn').data('page', data.pagination.current_page);
            } else {
                $loadMore.hide();
            }
        } else {
            $loadMore.hide();
        }
    }

    /**
     * Create property card HTML
     */
    function createPropertyCard(property, settings) {
        var $card = $('<div class="resbs-property-card" data-property-id="' + property.id + '"></div>');
        
        // Property image
        var imageHtml = '<div class="resbs-property-image">';
        if (property.featured_image) {
            imageHtml += '<img src="' + property.featured_image + '" alt="' + property.title + '">';
        } else {
            imageHtml += '<div class="resbs-no-image"><span class="dashicons dashicons-camera"></span></div>';
        }
        imageHtml += '</div>';
        
        // Property badges
        if (settings.show_badges && property.badges && property.badges.length > 0) {
            imageHtml += '<div class="resbs-property-badges">';
            $.each(property.badges, function(i, badge) {
                imageHtml += '<span class="resbs-badge resbs-badge-' + badge.type + '">' + badge.text + '</span>';
            });
            imageHtml += '</div>';
        }
        
        // Property actions overlay
        if (settings.show_favorite_button || settings.show_book_button) {
            imageHtml += '<div class="resbs-property-actions-overlay">';
            if (settings.show_favorite_button) {
                imageHtml += '<button type="button" class="resbs-favorite-btn" data-property-id="' + property.id + '">';
                imageHtml += '<span class="dashicons dashicons-heart"></span>';
                imageHtml += '</button>';
            }
            if (settings.show_book_button) {
                imageHtml += '<button type="button" class="resbs-book-btn" data-property-id="' + property.id + '">';
                imageHtml += '<span class="dashicons dashicons-calendar-alt"></span>';
                imageHtml += '</button>';
            }
            imageHtml += '</div>';
        }
        
        $card.append(imageHtml);
        
        // Property content
        var contentHtml = '<div class="resbs-property-content">';
        contentHtml += '<h3 class="resbs-property-title"><a href="' + property.url + '">' + property.title + '</a></h3>';
        
        if (settings.show_price && property.price) {
            contentHtml += '<div class="resbs-property-price">$' + property.price_formatted + '</div>';
        }
        
        if (settings.show_meta && property.meta) {
            contentHtml += '<div class="resbs-property-meta">';
            if (property.meta.bedrooms) {
                contentHtml += '<span><span class="dashicons dashicons-bed"></span>' + property.meta.bedrooms + ' ' + (property.meta.bedrooms == 1 ? 'Bedroom' : 'Bedrooms') + '</span>';
            }
            if (property.meta.bathrooms) {
                contentHtml += '<span><span class="dashicons dashicons-shower"></span>' + property.meta.bathrooms + ' ' + (property.meta.bathrooms == 1 ? 'Bathroom' : 'Bathrooms') + '</span>';
            }
            if (property.meta.area) {
                contentHtml += '<span><span class="dashicons dashicons-fullscreen-alt"></span>' + property.meta.area + ' sq ft</span>';
            }
            contentHtml += '</div>';
        }
        
        if (settings.show_excerpt && property.excerpt) {
            contentHtml += '<div class="resbs-property-excerpt">' + property.excerpt + '</div>';
        }
        
        if (settings.show_favorite_button || settings.show_book_button) {
            contentHtml += '<div class="resbs-property-actions">';
            if (settings.show_favorite_button) {
                contentHtml += '<button type="button" class="resbs-btn resbs-btn-secondary resbs-favorite-btn" data-property-id="' + property.id + '">';
                contentHtml += '<span class="dashicons dashicons-heart"></span>Add to Favorites';
                contentHtml += '</button>';
            }
            if (settings.show_book_button) {
                contentHtml += '<a href="' + property.url + '?action=book" class="resbs-btn resbs-btn-primary">';
                contentHtml += '<span class="dashicons dashicons-calendar-alt"></span>Book Now';
                contentHtml += '</a>';
            }
            contentHtml += '</div>';
        }
        
        contentHtml += '</div>';
        $card.append(contentHtml);
        
        return $card;
    }

    /**
     * Create pagination HTML
     */
    function createPagination(pagination) {
        var html = '<div class="resbs-pagination">';
        
        // Previous button
        if (pagination.current_page > 1) {
            html += '<a href="#" class="resbs-pagination-btn" data-page="' + (pagination.current_page - 1) + '">';
            html += '<span class="dashicons dashicons-arrow-left-alt2"></span>Previous';
            html += '</a>';
        } else {
            html += '<span class="resbs-pagination-btn disabled">';
            html += '<span class="dashicons dashicons-arrow-left-alt2"></span>Previous';
            html += '</span>';
        }
        
        // Page numbers
        var startPage = Math.max(1, pagination.current_page - 2);
        var endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
        
        for (var i = startPage; i <= endPage; i++) {
            if (i === pagination.current_page) {
                html += '<span class="resbs-pagination-btn active">' + i + '</span>';
            } else {
                html += '<a href="#" class="resbs-pagination-btn" data-page="' + i + '">' + i + '</a>';
            }
        }
        
        // Next button
        if (pagination.current_page < pagination.total_pages) {
            html += '<a href="#" class="resbs-pagination-btn" data-page="' + (pagination.current_page + 1) + '">';
            html += 'Next<span class="dashicons dashicons-arrow-right-alt2"></span>';
            html += '</a>';
        } else {
            html += '<span class="resbs-pagination-btn disabled">';
            html += 'Next<span class="dashicons dashicons-arrow-right-alt2"></span>';
            html += '</span>';
        }
        
        html += '</div>';
        return html;
    }

    /**
     * Get current filter values
     */
    function getCurrentFilters($widget) {
        var filters = {};
        $widget.find('.resbs-filter-form input, .resbs-filter-form select').each(function() {
            var $field = $(this);
            var name = $field.attr('name');
            var value = $field.val();
            if (name && value) {
                filters[name] = value;
            }
        });
        return filters;
    }

    /**
     * Toggle favorite status
     */
    function toggleFavorite($btn, propertyId) {
        $.ajax({
            url: resbs_elementor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_toggle_favorite',
                nonce: resbs_elementor_ajax.nonce,
                property_id: propertyId
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.is_favorite) {
                        $btn.addClass('active');
                    } else {
                        $btn.removeClass('active');
                    }
                }
            }
        });
    }

    /**
     * Show loading state
     */
    function showLoading($widget) {
        $widget.find('.resbs-loading').show();
        $widget.find('.resbs-property-grid, .resbs-pagination-wrapper, .resbs-infinite-scroll-wrapper, .resbs-no-properties').hide();
    }

    /**
     * Hide loading state
     */
    function hideLoading($widget) {
        $widget.find('.resbs-loading').hide();
        $widget.find('.resbs-property-grid, .resbs-pagination-wrapper, .resbs-infinite-scroll-wrapper').show();
    }

    /**
     * Show error message
     */
    function showError($widget, message) {
        $widget.find('.resbs-no-properties p').text(message);
        $widget.find('.resbs-no-properties').show();
        $widget.find('.resbs-property-grid, .resbs-pagination-wrapper, .resbs-infinite-scroll-wrapper').hide();
    }

    // Handle window resize for responsive grid
    $(window).on('resize', function() {
        $('.resbs-elementor-property-grid').each(function() {
            var $widget = $(this);
            var settings = $widget.data('settings');
            var columns = settings.columns;
            
            // Adjust grid columns based on screen size
            var $grid = $widget.find('.resbs-property-grid');
            $grid.removeClass('resbs-grid-2-cols resbs-grid-3-cols resbs-grid-4-cols');
            
            if ($(window).width() <= 480) {
                $grid.addClass('resbs-grid-1-cols');
            } else if ($(window).width() <= 768) {
                $grid.addClass('resbs-grid-1-cols');
            } else if ($(window).width() <= 992) {
                if (columns >= 3) {
                    $grid.addClass('resbs-grid-2-cols');
                } else {
                    $grid.addClass('resbs-grid-' + columns + '-cols');
                }
            } else {
                $grid.addClass('resbs-grid-' + columns + '-cols');
            }
        });
    });

    // Trigger initial resize
    $(window).trigger('resize');

    /**
     * Initialize property carousel widget with Swiper
     */
    function initPropertyCarouselSwiper($widget) {
        // Wait for Swiper to load
        if (typeof Swiper === 'undefined') {
            setTimeout(function() {
                initPropertyCarouselSwiper($widget);
            }, 100);
            return;
        }
        
        var widgetId = $widget.attr('id');
        if (!$widgetId) {
            widgetId = 'resbs-carousel-' + Math.random().toString(36).substr(2, 9);
            $widget.attr('id', widgetId);
        }
        
        var autoplay = $widget.data('autoplay') === 'true' || $widget.data('autoplay') === true;
        var autoplaySpeed = parseInt($widget.data('autoplay-speed')) || 3000;
        var showDots = $widget.data('show-dots') === 'true' || $widget.data('show-dots') === true;
        var showArrows = $widget.data('show-arrows') === 'true' || $widget.data('show-arrows') === true;
        var infiniteLoop = $widget.data('infinite-loop') === 'true' || $widget.data('infinite-loop') === true;
        var pauseOnHover = $widget.data('pause-on-hover') === 'true' || $widget.data('pause-on-hover') === true;
        var itemsPerView = parseInt($widget.data('items-per-view')) || 3;
        
        // Check if already initialized
        if ($widget.hasClass('resbs-swiper-initialized')) {
            return;
        }
        
        // Initialize Swiper with proper selectors for this specific widget
        var swiperConfig = {
            slidesPerView: itemsPerView,
            spaceBetween: 25,
            loop: infiniteLoop && $widget.find('.swiper-slide').length > itemsPerView,
            autoplay: autoplay ? {
                delay: autoplaySpeed,
                disableOnInteraction: false,
                pauseOnMouseEnter: pauseOnHover
            } : false,
            pagination: showDots ? {
                el: $widget.find('.swiper-pagination')[0],
                clickable: true,
                dynamicBullets: true
            } : false,
            navigation: showArrows ? {
                nextEl: $widget.find('.swiper-button-next')[0],
                prevEl: $widget.find('.swiper-button-prev')[0],
            } : false,
            breakpoints: {
                320: {
                    slidesPerView: 1,
                    spaceBetween: 15
                },
                640: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 20
                },
                1024: {
                    slidesPerView: itemsPerView >= 3 ? 3 : itemsPerView,
                    spaceBetween: 25
                },
                1280: {
                    slidesPerView: itemsPerView,
                    spaceBetween: 25
                }
            },
            on: {
                init: function() {
                    $widget.addClass('resbs-swiper-initialized');
                }
            }
        };
        
        try {
            var swiper = new Swiper('#' + widgetId, swiperConfig);
            
            // Handle favorite buttons
            $widget.on('click', '.resbs-favorite-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $btn = $(this);
                var propertyId = $btn.data('property-id');
                if (propertyId) {
                    toggleFavorite($btn, propertyId);
                }
            });
        } catch (e) {
            console.error('Swiper initialization error:', e);
        }
    }
    
    // Old carousel code - keeping for backward compatibility but using Swiper now
    var carouselStates = {};
    
    function initPropertyCarousel($widget) {
        var settings = $widget.data('settings');
        var widgetId = $widget.attr('id');
        var slidesToShow = parseInt(settings.items_per_view || settings.slides_to_show || 3);
        
        // Initialize state for this widget
        carouselStates[widgetId] = {
            currentSlide: 0,
            totalSlides: 0,
            isAnimating: false,
            autoplayInterval: null
        };
        
        // Check if slides are already rendered in HTML
        var $existingSlides = $widget.find('.resbs-carousel-slide');
        if ($existingSlides.length > 0) {
            // Initialize with existing HTML
            carouselStates[widgetId].totalSlides = $existingSlides.length;
            setupCarousel($widget, null);
            if (settings.autoplay) {
                startAutoplay($widget);
            }
        } else {
            // Load via AJAX
            loadCarouselProperties($widget, function(properties) {
                carouselStates[widgetId].totalSlides = Math.ceil(properties.length / slidesToShow);
                setupCarousel($widget, properties);
                if (settings.autoplay) {
                    startAutoplay($widget);
                }
            });
        }
        
        // Handle navigation arrows
        $widget.find('.resbs-carousel-prev').on('click', function() {
            if (!carouselStates[widgetId].isAnimating) {
                prevSlide($widget);
            }
        });
        
        $widget.find('.resbs-carousel-next').on('click', function() {
            if (!carouselStates[widgetId].isAnimating) {
                nextSlide($widget);
            }
        });
        
        // Handle dot navigation
        $(document).on('click', '#' + widgetId + ' .resbs-carousel-dot', function() {
            if (!carouselStates[widgetId].isAnimating) {
                var slideIndex = $(this).data('slide');
                goToSlide($widget, slideIndex);
            }
        });
        
        // Handle favorite button
        $(document).on('click', '#' + widgetId + ' .resbs-favorite-btn', function() {
            var $btn = $(this);
            var propertyId = $btn.data('property-id');
            
            if (!propertyId) return;
            
            toggleFavorite($btn, propertyId);
        });
        
        // Handle book button
        $(document).on('click', '#' + widgetId + ' .resbs-book-btn', function() {
            var $btn = $(this);
            var propertyId = $btn.data('property-id');
            
            if (!propertyId) return;
            
            // Redirect to property page or trigger booking
            window.location.href = '/property/' + propertyId + '/?action=book';
        });
        
        // Pause autoplay on hover
        $widget.on('mouseenter', function() {
            stopAutoplay($widget);
        });
        
        $widget.on('mouseleave', function() {
            if (settings.autoplay) {
                startAutoplay($widget);
            }
        });
        
        // Touch/swipe support
        var startX = 0;
        var startY = 0;
        var distX = 0;
        var distY = 0;
        
        $widget.find('.resbs-carousel-track').on('touchstart', function(e) {
            var touch = e.originalEvent.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
        });
        
        $widget.find('.resbs-carousel-track').on('touchmove', function(e) {
            e.preventDefault();
        });
        
        $widget.find('.resbs-carousel-track').on('touchend', function(e) {
            var touch = e.originalEvent.changedTouches[0];
            distX = touch.clientX - startX;
            distY = touch.clientY - startY;
            
            // Check if horizontal swipe is greater than vertical
            if (Math.abs(distX) > Math.abs(distY) && Math.abs(distX) > 50) {
                if (distX > 0) {
                    // Swipe right - previous slide
                    if (!isAnimating) {
                        prevSlide($widget);
                    }
                } else {
                    // Swipe left - next slide
                    if (!isAnimating) {
                        nextSlide($widget);
                    }
                }
            }
        });
        
        // Keyboard navigation
        $widget.on('keydown', function(e) {
            if (e.key === 'ArrowLeft' && !isAnimating) {
                prevSlide($widget);
            } else if (e.key === 'ArrowRight' && !isAnimating) {
                nextSlide($widget);
            }
        });
        
        // Make widget focusable for keyboard navigation
        $widget.attr('tabindex', '0');
    }

    /**
     * Load carousel properties via AJAX
     */
    function loadCarouselProperties($widget, callback) {
        var settings = $widget.data('settings');
        var widgetId = $widget.attr('id');
        
        showCarouselLoading($widget);
        
        $.ajax({
            url: resbs_elementor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_elementor_load_carousel_properties',
                nonce: resbs_elementor_ajax.nonce,
                widget_id: widgetId,
                settings: settings
            },
            success: function(response) {
                if (response.success) {
                    callback(response.data.properties);
                } else {
                    showCarouselError($widget, response.data || resbs_elementor_ajax.messages.error);
                }
            },
            error: function() {
                showCarouselError($widget, resbs_elementor_ajax.messages.error);
            },
            complete: function() {
                hideCarouselLoading($widget);
            }
        });
    }

    /**
     * Setup carousel with properties
     */
    function setupCarousel($widget, properties) {
        var settings = $widget.data('settings');
        var $track = $widget.find('.resbs-carousel-track');
        var $dots = $widget.find('.resbs-carousel-dots');
        var slidesToShow = parseInt(settings.items_per_view || settings.slides_to_show || 3);
        
        // If slides already exist in HTML (server-rendered), use them
        var $existingSlides = $track.find('.resbs-carousel-slide');
        if ($existingSlides.length > 0) {
            // Just create dots and initialize
            var totalSlides = $existingSlides.length;
            $dots.empty();
            for (var k = 0; k < totalSlides; k++) {
                var $dot = $('<span class="resbs-carousel-dot" data-slide="' + k + '"></span>');
                if (k === 0) {
                    $dot.addClass('active');
                }
                $dots.append($dot);
            }
            $track.data('current-slide', 0);
            updateArrowStates($widget, 0, totalSlides);
            return;
        }
        
        // Otherwise create slides from AJAX data
        $track.empty();
        $dots.empty();
        
        if (properties && properties.length > 0) {
            // Create slides
            for (var i = 0; i < properties.length; i += slidesToShow) {
                var $slide = $('<div class="resbs-carousel-slide"></div>');
                
                for (var j = 0; j < slidesToShow && (i + j) < properties.length; j++) {
                    var property = properties[i + j];
                    var $propertyCard = createCarouselPropertyCard(property, settings);
                    $slide.append($propertyCard);
                }
                
                $track.append($slide);
            }
            
            // Create dots
            var totalSlides = Math.ceil(properties.length / slidesToShow);
            for (var k = 0; k < totalSlides; k++) {
                var $dot = $('<span class="resbs-carousel-dot" data-slide="' + k + '"></span>');
                if (k === 0) {
                    $dot.addClass('active');
                }
                $dots.append($dot);
            }
            
            // Set initial slide
            $track.data('current-slide', 0);
            updateArrowStates($widget, 0, totalSlides);
            
            // Add animation
            $track.find('.resbs-property-card').addClass('resbs-carousel-slide-in');
        } else {
            showCarouselError($widget, resbs_elementor_ajax.messages.no_properties);
        }
    }

    /**
     * Create carousel property card HTML
     */
    function createCarouselPropertyCard(property, settings) {
        var $card = $('<div class="resbs-property-card" data-property-id="' + property.id + '"></div>');
        
        // Property image
        var imageHtml = '<div class="resbs-property-image">';
        if (property.featured_image) {
            imageHtml += '<img src="' + property.featured_image + '" alt="' + property.title + '">';
        } else {
            imageHtml += '<div class="resbs-no-image"><span class="dashicons dashicons-camera"></span></div>';
        }
        imageHtml += '</div>';
        
        // Property badges
        if (settings.show_badges && property.badges && property.badges.length > 0) {
            imageHtml += '<div class="resbs-property-badges">';
            for (var i = 0; i < property.badges.length; i++) {
                var badge = property.badges[i];
                imageHtml += '<span class="resbs-badge resbs-badge-' + badge.type + '">' + badge.text + '</span>';
            }
            imageHtml += '</div>';
        }
        
        // Property actions overlay
        if (settings.show_favorite_button || settings.show_book_button) {
            imageHtml += '<div class="resbs-property-actions-overlay">';
            if (settings.show_favorite_button) {
                imageHtml += '<button type="button" class="resbs-favorite-btn" data-property-id="' + property.id + '">';
                imageHtml += '<span class="dashicons dashicons-heart"></span>';
                imageHtml += '</button>';
            }
            if (settings.show_book_button) {
                imageHtml += '<button type="button" class="resbs-book-btn" data-property-id="' + property.id + '">';
                imageHtml += '<span class="dashicons dashicons-calendar-alt"></span>';
                imageHtml += '</button>';
            }
            imageHtml += '</div>';
        }
        
        $card.append(imageHtml);
        
        // Property content
        var contentHtml = '<div class="resbs-property-content">';
        contentHtml += '<h3 class="resbs-property-title"><a href="' + property.url + '">' + property.title + '</a></h3>';
        
        if (settings.show_price && property.price) {
            contentHtml += '<div class="resbs-property-price">$' + property.price_formatted + '</div>';
        }
        
        if (settings.show_meta && property.meta) {
            contentHtml += '<div class="resbs-property-meta">';
            if (property.meta.bedrooms) {
                contentHtml += '<span><span class="dashicons dashicons-bed"></span>' + property.meta.bedrooms + ' ' + (property.meta.bedrooms == 1 ? 'Bedroom' : 'Bedrooms') + '</span>';
            }
            if (property.meta.bathrooms) {
                contentHtml += '<span><span class="dashicons dashicons-shower"></span>' + property.meta.bathrooms + ' ' + (property.meta.bathrooms == 1 ? 'Bathroom' : 'Bathrooms') + '</span>';
            }
            if (property.meta.area) {
                contentHtml += '<span><span class="dashicons dashicons-fullscreen-alt"></span>' + property.meta.area + ' sq ft</span>';
            }
            contentHtml += '</div>';
        }
        
        if (settings.show_excerpt && property.excerpt) {
            contentHtml += '<div class="resbs-property-excerpt">' + property.excerpt + '</div>';
        }
        
        if (settings.show_favorite_button || settings.show_book_button) {
            contentHtml += '<div class="resbs-property-actions">';
            if (settings.show_favorite_button) {
                contentHtml += '<button type="button" class="resbs-btn resbs-btn-secondary resbs-favorite-btn" data-property-id="' + property.id + '">';
                contentHtml += '<span class="dashicons dashicons-heart"></span>Add to Favorites';
                contentHtml += '</button>';
            }
            if (settings.show_book_button) {
                contentHtml += '<a href="' + property.url + '?action=book" class="resbs-btn resbs-btn-primary">';
                contentHtml += '<span class="dashicons dashicons-calendar-alt"></span>Book Now';
                contentHtml += '</a>';
            }
            contentHtml += '</div>';
        }
        
        contentHtml += '</div>';
        $card.append(contentHtml);
        
        return $card;
    }

    /**
     * Navigate to previous slide
     */
    function prevSlide($widget) {
        var widgetId = $widget.attr('id');
        var state = carouselStates[widgetId];
        if (!state) return;
        
        var settings = $widget.data('settings');
        var $track = $widget.find('.resbs-carousel-track');
        var currentSlide = parseInt($track.data('current-slide') || 0);
        var totalSlides = $widget.find('.resbs-carousel-slide').length;
        
        if (settings.infinite_loop || currentSlide > 0) {
            var newSlide = currentSlide > 0 ? currentSlide - 1 : (settings.infinite_loop ? totalSlides - 1 : 0);
            goToSlide($widget, newSlide);
        }
    }

    /**
     * Navigate to next slide
     */
    function nextSlide($widget) {
        var widgetId = $widget.attr('id');
        var state = carouselStates[widgetId];
        if (!state) return;
        
        var settings = $widget.data('settings');
        var $track = $widget.find('.resbs-carousel-track');
        var currentSlide = parseInt($track.data('current-slide') || 0);
        var totalSlides = $widget.find('.resbs-carousel-slide').length;
        
        if (settings.infinite_loop || currentSlide < totalSlides - 1) {
            var newSlide = currentSlide < totalSlides - 1 ? currentSlide + 1 : (settings.infinite_loop ? 0 : totalSlides - 1);
            goToSlide($widget, newSlide);
        }
    }

    /**
     * Go to specific slide
     */
    function goToSlide($widget, slideIndex) {
        var widgetId = $widget.attr('id');
        var state = carouselStates[widgetId];
        if (!state) return;
        
        var $track = $widget.find('.resbs-carousel-track');
        var $dots = $widget.find('.resbs-carousel-dots');
        var $slides = $widget.find('.resbs-carousel-slide');
        var totalSlides = $slides.length;
        
        if (slideIndex >= 0 && slideIndex < totalSlides && !state.isAnimating) {
            state.isAnimating = true;
            
            // Update track position - calculate based on slide width
            var slidesToShow = parseInt($track.data('slides-to-show') || 3);
            var slideWidth = 100 / slidesToShow;
            var translateX = -(slideIndex * slideWidth);
            $track.css({
                'transform': 'translateX(' + translateX + '%)',
                'transition': 'transform 0.5s ease'
            });
            $track.data('current-slide', slideIndex);
            state.currentSlide = slideIndex;
            
            // Update dots
            $dots.find('.resbs-carousel-dot').removeClass('active');
            $dots.find('.resbs-carousel-dot[data-slide="' + slideIndex + '"]').addClass('active');
            
            // Update arrow states
            updateArrowStates($widget, slideIndex, totalSlides);
            
            // Reset animation flag
            setTimeout(function() {
                state.isAnimating = false;
            }, 500);
        }
    }

    /**
     * Update arrow states
     */
    function updateArrowStates($widget, currentSlide, totalSlides) {
        var settings = $widget.data('settings');
        var $prevBtn = $widget.find('.resbs-carousel-prev');
        var $nextBtn = $widget.find('.resbs-carousel-next');
        
        if (!settings.infinite_loop) {
            $prevBtn.prop('disabled', currentSlide === 0);
            $nextBtn.prop('disabled', currentSlide === totalSlides - 1);
        } else {
            $prevBtn.prop('disabled', false);
            $nextBtn.prop('disabled', false);
        }
    }

    /**
     * Start autoplay
     */
    function startAutoplay($widget) {
        var widgetId = $widget.attr('id');
        var state = carouselStates[widgetId];
        if (!state) return;
        
        var settings = $widget.data('settings');
        var speed = parseInt(settings.autoplay_speed) || 3000;
        
        stopAutoplay($widget);
        
        state.autoplayInterval = setInterval(function() {
            nextSlide($widget);
        }, speed);
    }

    /**
     * Stop autoplay
     */
    function stopAutoplay($widget) {
        var widgetId = $widget.attr('id');
        var state = carouselStates[widgetId];
        if (!state) return;
        
        if (state.autoplayInterval) {
            clearInterval(state.autoplayInterval);
            state.autoplayInterval = null;
        }
    }

    /**
     * Update slide width based on slides to show
     */
    function updateSlideWidth($widget) {
        var $track = $widget.find('.resbs-carousel-track');
        var $slides = $widget.find('.resbs-carousel-slide');
        var slidesToShow = parseInt($track.data('slides-to-show') || 1);
        var totalSlides = $slides.length;
        
        if (totalSlides > 0) {
            var slideWidth = 100 / totalSlides;
            $slides.css('width', slideWidth + '%');
        }
    }

    /**
     * Show carousel loading state
     */
    function showCarouselLoading($widget) {
        $widget.find('.resbs-loading').show();
        $widget.find('.resbs-carousel-container, .resbs-no-properties').hide();
    }

    /**
     * Hide carousel loading state
     */
    function hideCarouselLoading($widget) {
        $widget.find('.resbs-loading').hide();
        $widget.find('.resbs-carousel-container').show();
    }

    /**
     * Show carousel error message
     */
    function showCarouselError($widget, message) {
        $widget.find('.resbs-no-properties p').text(message);
        $widget.find('.resbs-no-properties').show();
        $widget.find('.resbs-carousel-container').hide();
    }

    // Handle window resize for carousel
    $(window).on('resize', function() {
        $('.resbs-elementor-property-carousel').each(function() {
            var $widget = $(this);
            updateSlideWidth($widget);
        });
    });

    // ============================================
    // SEARCH WIDGET FUNCTIONALITY
    // ============================================
    $('.resbs-search-widget').each(function() {
        initSearchWidget($(this));
    });

    function initSearchWidget($widget) {
        var widgetId = $widget.attr('id');
        var $form = $widget.find('.resbs-search-form-inner');
        var $resultsUrl = $form.attr('action') || window.location.href;

        // Handle form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            performSearch($widget);
        });

        // Handle reset button
        $widget.find('.resbs-search-reset-btn').on('click', function() {
            $form[0].reset();
            performSearch($widget);
        });

        // Handle location button
        $widget.find('.resbs-search-location-btn').on('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    // You can implement reverse geocoding here
                    alert('Location feature requires Google Maps API integration');
                });
            } else {
                alert('Geolocation is not supported by your browser');
            }
        });
    }

    function performSearch($widget) {
        var $form = $widget.find('.resbs-search-form-inner');
        var formData = $form.serialize();
        var resultsUrl = $form.attr('action') || window.location.href;

        // Redirect to results page with search parameters
        window.location.href = resultsUrl + (resultsUrl.indexOf('?') > -1 ? '&' : '?') + formData;
    }

    // ============================================
    // LISTINGS WIDGET FUNCTIONALITY
    // ============================================
    $('.resbs-listings-widget').each(function() {
        initListingsWidget($(this));
    });

    function initListingsWidget($widget) {
        var widgetId = $widget.attr('id');
        var settings = $widget.data('settings') || {};

        // Handle view toggle
        $widget.find('.resbs-view-btn').on('click', function() {
            var view = $(this).data('view');
            switchView($widget, view);
        });

        // Handle sort change
        $widget.find('.resbs-sort-select').on('change', function() {
            var sortValue = $(this).val();
            if (sortValue) {
                reloadListings($widget, { sort: sortValue });
            }
        });

        // Handle favorite button
        $(document).on('click', '#' + widgetId + ' .resbs-favorite-btn', function() {
            var $btn = $(this);
            var propertyId = $btn.data('property-id');
            if (propertyId) {
                toggleFavorite($btn, propertyId);
            }
        });
    }

    function switchView($widget, view) {
        var $content = $widget.find('.resbs-listings-content');
        $content.removeClass('resbs-layout-grid resbs-layout-list resbs-layout-map');
        $content.addClass('resbs-layout-' + view);

        // Update active button
        $widget.find('.resbs-view-btn').removeClass('active');
        $widget.find('.resbs-view-btn[data-view="' + view + '"]').addClass('active');

        if (view === 'map') {
            initListingsMap($widget);
        }
    }

    function reloadListings($widget, filters) {
        // AJAX call to reload listings
        $.ajax({
            url: resbs_elementor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_elementor_load_listings',
                nonce: resbs_elementor_ajax.nonce,
                widget_id: $widget.attr('id'),
                filters: filters
            },
            success: function(response) {
                if (response.success) {
                    $widget.find('.resbs-properties-container').html(response.data.html);
                }
            }
        });
    }

    function initListingsMap($widget) {
        // Initialize Google Maps for listings
        if (typeof google !== 'undefined' && google.maps) {
            // Map implementation here
        }
    }

    // ============================================
    // REQUEST FORM WIDGET FUNCTIONALITY
    // ============================================
    $('.resbs-request-form-widget').each(function() {
        initRequestForm($(this));
    });

    function initRequestForm($widget) {
        var $form = $widget.find('.resbs-request-form');
        var $submitBtn = $widget.find('.resbs-request-submit-btn');

        $submitBtn.on('click', function(e) {
            e.preventDefault();
            submitRequestForm($widget);
        });
    }

    function submitRequestForm($widget) {
        var $form = $widget.find('.resbs-request-form');
        var $submitBtn = $widget.find('.resbs-request-submit-btn');
        var formData = $form.serialize();

        $submitBtn.prop('disabled', true).text('Submitting...');

        $.ajax({
            url: resbs_elementor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_elementor_submit_request',
                nonce: resbs_elementor_ajax.nonce,
                form_data: formData
            },
            success: function(response) {
                if (response.success) {
                    $form.html('<div style="padding: 30px; text-align: center;"><h3>Thank You!</h3><p>' + (response.data.message || 'Your request has been submitted successfully.') + '</p></div>');
                } else {
                    alert(response.data.message || 'An error occurred. Please try again.');
                    $submitBtn.prop('disabled', false).text('REQUEST INFO');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $submitBtn.prop('disabled', false).text('REQUEST INFO');
            }
        });
    }

    // ============================================
    // AUTHENTICATION WIDGET FUNCTIONALITY
    // ============================================
    $('.resbs-auth-widget').each(function() {
        initAuthWidget($(this));
    });

    function initAuthWidget($widget) {
        var $loginForm = $widget.find('.resbs-login-form');
        var $loginBtn = $widget.find('.resbs-login-btn');
        var $logoutBtn = $widget.find('.resbs-logout-btn');

        if ($loginForm.length) {
            $loginBtn.on('click', function(e) {
                e.preventDefault();
                performLogin($widget);
            });
        }

        if ($logoutBtn.length) {
            $logoutBtn.on('click', function(e) {
                e.preventDefault();
                performLogout($widget);
            });
        }

        // Social login buttons
        $widget.find('.resbs-social-btn').on('click', function() {
            var provider = $(this).data('provider');
            if (provider) {
                // Redirect to social login
                window.location.href = '/wp-login.php?social=' + provider;
            }
        });
    }

    function performLogin($widget) {
        var $form = $widget.find('.resbs-login-form');
        var username = $form.find('input[name="username"]').val();
        var password = $form.find('input[name="password"]').val();
        var $loginBtn = $widget.find('.resbs-login-btn');

        if (!username || !password) {
            alert('Please enter both username and password');
            return;
        }

        $loginBtn.prop('disabled', true).text('Logging in...');

        $.ajax({
            url: resbs_elementor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_elementor_login',
                nonce: resbs_elementor_ajax.nonce,
                username: username,
                password: password
            },
            success: function(response) {
                if (response.success) {
                    // Reload page to show logged in state
                    window.location.reload();
                } else {
                    alert(response.data.message || 'Login failed. Please check your credentials.');
                    $loginBtn.prop('disabled', false).text('LOG IN');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                $loginBtn.prop('disabled', false).text('LOG IN');
            }
        });
    }

    function performLogout($widget) {
        $.ajax({
            url: resbs_elementor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_elementor_logout',
                nonce: resbs_elementor_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload();
                }
            }
        });
    }

    // ============================================
    // HALF MAP WIDGET FUNCTIONALITY
    // ============================================
    $('.resbs-half-map-widget').each(function() {
        initHalfMapWidget($(this));
    });

    function initHalfMapWidget($widget) {
        var widgetId = $widget.attr('id');
        var mapId = 'resbs-half-map-' + widgetId;
        var $mapContainer = $widget.find('.resbs-half-map-map');
        var map = null;
        var markers = [];

        // Initialize Google Maps
        if (typeof google !== 'undefined' && google.maps) {
            $mapContainer.attr('id', mapId);

            // Create map
            map = new google.maps.Map(document.getElementById(mapId), {
                zoom: 10,
                center: { lat: 40.7128, lng: -74.0060 }, // Default to NYC
                mapTypeId: 'roadmap'
            });

            // Load property markers
            loadMapProperties($widget, map);
        } else {
            $mapContainer.html('<div class="resbs-map-error"><p>Google Maps is not available. Please check your API key.</p></div>');
        }

        // Handle property card click
        $widget.find('.resbs-half-map-property-card').on('click', function() {
            var propertyId = $(this).data('property-id');
            if (propertyId && map) {
                centerMapOnProperty(map, propertyId);
            }
        });
    }

    function loadMapProperties($widget, map) {
        var widgetId = $widget.attr('id');
        var markers = [];

        $.ajax({
            url: resbs_elementor_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_elementor_load_map_properties',
                nonce: resbs_elementor_ajax.nonce,
                widget_id: widgetId
            },
            success: function(response) {
                if (response.success && response.data.properties) {
                    var bounds = new google.maps.LatLngBounds();
                    
                    response.data.properties.forEach(function(property) {
                        if (property.latitude && property.longitude) {
                            var marker = new google.maps.Marker({
                                position: { lat: parseFloat(property.latitude), lng: parseFloat(property.longitude) },
                                map: map,
                                title: property.title
                            });
                            
                            markers.push(marker);
                            bounds.extend(marker.getPosition());

                            var infoWindow = new google.maps.InfoWindow({
                                content: '<div style="padding: 10px;"><strong>' + property.title + '</strong><br>' + property.price_formatted + '<br><a href="' + property.url + '" target="_blank">View Property</a></div>'
                            });

                            marker.addListener('click', function() {
                                infoWindow.open(map, marker);
                                $widget.find('.resbs-half-map-property-card[data-property-id="' + property.id + '"]').addClass('active').siblings().removeClass('active');
                            });
                        }
                    });

                    // Fit map to show all markers
                    if (markers.length > 0) {
                        map.fitBounds(bounds);
                    } else {
                        // Default center if no markers
                        map.setCenter({ lat: 40.7128, lng: -74.0060 });
                        map.setZoom(10);
                    }
                }
            }
        });
    }

    function centerMapOnProperty(map, propertyId) {
        // Find property marker and center map on it
        // Implementation depends on how markers are stored
    }
});
