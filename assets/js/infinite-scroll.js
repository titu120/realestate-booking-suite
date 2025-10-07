/**
 * Infinite Scroll JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Global variables
    let infiniteScrollInstances = {};
    let isLoading = false;

    /**
     * Initialize infinite scroll when document is ready
     */
    $(document).ready(function() {
        initializeInfiniteScroll();
    });

    /**
     * Initialize infinite scroll functionality
     */
    function initializeInfiniteScroll() {
        // Initialize all infinite scroll containers
        $('.resbs-infinite-properties-container').each(function() {
            const $container = $(this);
            const containerId = $container.attr('id');
            
            if (containerId && !infiniteScrollInstances[containerId]) {
                initializeInfiniteScrollInstance($container);
            }
        });

        // Handle load more button clicks
        $(document).on('click', '.resbs-load-more-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $container = $btn.closest('.resbs-infinite-properties-container');
            loadMoreProperties($container);
        });

        // Handle filter form submissions
        $(document).on('submit', '.resbs-infinite-filter-form', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $container = $form.closest('.resbs-infinite-properties-container');
            applyFilters($container, $form);
        });

        // Handle reset button clicks
        $(document).on('click', '.resbs-reset-btn', function(e) {
            e.preventDefault();
            const $btn = $(this);
            const $form = $btn.closest('.resbs-infinite-filter-form');
            const $container = $form.closest('.resbs-infinite-properties-container');
            resetFilters($container, $form);
        });

        // Handle pagination clicks
        $(document).on('click', '.resbs-pagination a', function(e) {
            e.preventDefault();
            const $link = $(this);
            const $container = $link.closest('.resbs-infinite-properties-container');
            const page = $link.data('page');
            loadPage($container, page);
        });
    }

    /**
     * Initialize infinite scroll instance
     */
    function initializeInfiniteScrollInstance($container) {
        const containerId = $container.attr('id');
        const settings = $container.find('.resbs-infinite-properties-grid').data('settings');
        
        if (!settings) {
            return;
        }

        // Store instance data
        infiniteScrollInstances[containerId] = {
            container: $container,
            settings: settings,
            currentPage: 1,
            totalPages: 0,
            totalProperties: 0,
            isLoading: false,
            hasMore: true,
            filters: {}
        };

        // Load initial properties
        loadInitialProperties($container);

        // Setup infinite scroll if enabled
        if (settings.infinite_scroll) {
            setupInfiniteScroll($container);
        }
    }

    /**
     * Load initial properties
     */
    function loadInitialProperties($container) {
        const containerId = $container.attr('id');
        const instance = infiniteScrollInstances[containerId];
        
        if (!instance) {
            return;
        }

        const $grid = $container.find('.resbs-infinite-properties-grid');
        const $loadMoreBtn = $container.find('.resbs-load-more-btn');
        const $pagination = $container.find('.resbs-pagination-fallback');
        const $count = $container.find('.resbs-count-number');

        // Show loading state
        $grid.html('<div class="resbs-loading-container"><div class="resbs-loading-spinner"><span class="dashicons dashicons-update"></span></div><p>' + resbs_infinite_scroll_ajax.messages.loading + '</p></div>');

        // Make AJAX request
        $.ajax({
            url: resbs_infinite_scroll_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_load_more_properties',
                page: 1,
                posts_per_page: instance.settings.posts_per_page,
                widget_id: containerId,
                nonce: resbs_infinite_scroll_ajax.nonce,
                ...instance.filters
            },
            success: function(response) {
                if (response.success) {
                    $grid.html(response.data.html);
                    updateInstanceData(containerId, response.data);
                    updateControls($container, response.data);
                    updateCount($container, response.data);
                } else {
                    showError($container, response.data.message);
                }
            },
            error: function() {
                showError($container, resbs_infinite_scroll_ajax.messages.error);
            }
        });
    }

    /**
     * Load more properties
     */
    function loadMoreProperties($container) {
        const containerId = $container.attr('id');
        const instance = infiniteScrollInstances[containerId];
        
        if (!instance || instance.isLoading || !instance.hasMore) {
            return;
        }

        instance.isLoading = true;
        const $loadMoreBtn = $container.find('.resbs-load-more-btn');
        const $grid = $container.find('.resbs-infinite-properties-grid');
        const nextPage = instance.currentPage + 1;

        // Show loading state
        $loadMoreBtn.addClass('loading');
        $loadMoreBtn.find('.resbs-load-more-text').hide();
        $loadMoreBtn.find('.resbs-loading-spinner').show();

        // Make AJAX request
        $.ajax({
            url: resbs_infinite_scroll_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_load_more_properties',
                page: nextPage,
                posts_per_page: instance.settings.posts_per_page,
                widget_id: containerId,
                nonce: resbs_infinite_scroll_ajax.nonce,
                ...instance.filters
            },
            success: function(response) {
                if (response.success) {
                    // Append new properties with animation
                    const $newProperties = $(response.data.html);
                    $newProperties.hide();
                    $grid.append($newProperties);
                    $newProperties.fadeIn(300);

                    updateInstanceData(containerId, response.data);
                    updateControls($container, response.data);
                    updateCount($container, response.data);
                } else {
                    showError($container, response.data.message);
                }
            },
            error: function() {
                showError($container, resbs_infinite_scroll_ajax.messages.error);
            },
            complete: function() {
                instance.isLoading = false;
                $loadMoreBtn.removeClass('loading');
                $loadMoreBtn.find('.resbs-load-more-text').show();
                $loadMoreBtn.find('.resbs-loading-spinner').hide();
            }
        });
    }

    /**
     * Load specific page
     */
    function loadPage($container, page) {
        const containerId = $container.attr('id');
        const instance = infiniteScrollInstances[containerId];
        
        if (!instance || instance.isLoading) {
            return;
        }

        instance.isLoading = true;
        const $grid = $container.find('.resbs-infinite-properties-grid');
        const $pagination = $container.find('.resbs-pagination-fallback');

        // Show loading state
        $grid.html('<div class="resbs-loading-container"><div class="resbs-loading-spinner"><span class="dashicons dashicons-update"></span></div><p>' + resbs_infinite_scroll_ajax.messages.loading + '</p></div>');

        // Make AJAX request
        $.ajax({
            url: resbs_infinite_scroll_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_load_more_properties',
                page: page,
                posts_per_page: instance.settings.posts_per_page,
                widget_id: containerId,
                nonce: resbs_infinite_scroll_ajax.nonce,
                ...instance.filters
            },
            success: function(response) {
                if (response.success) {
                    $grid.html(response.data.html);
                    updateInstanceData(containerId, response.data);
                    updateControls($container, response.data);
                    updateCount($container, response.data);
                    
                    // Scroll to top of grid
                    $('html, body').animate({
                        scrollTop: $grid.offset().top - 100
                    }, 500);
                } else {
                    showError($container, response.data.message);
                }
            },
            error: function() {
                showError($container, resbs_infinite_scroll_ajax.messages.error);
            },
            complete: function() {
                instance.isLoading = false;
            }
        });
    }

    /**
     * Apply filters
     */
    function applyFilters($container, $form) {
        const containerId = $container.attr('id');
        const instance = infiniteScrollInstances[containerId];
        
        if (!instance) {
            return;
        }

        // Get filter values
        const filters = {
            property_type: $form.find('[name="property_type"]').val(),
            property_status: $form.find('[name="property_status"]').val(),
            location: $form.find('[name="location"]').val(),
            price_min: $form.find('[name="price_min"]').val(),
            price_max: $form.find('[name="price_max"]').val(),
            bedrooms: $form.find('[name="bedrooms"]').val(),
            bathrooms: $form.find('[name="bathrooms"]').val(),
            featured_only: $form.find('[name="featured_only"]').is(':checked')
        };

        // Update instance filters
        instance.filters = filters;
        instance.currentPage = 1;
        instance.hasMore = true;

        // Reload properties
        loadInitialProperties($container);
    }

    /**
     * Reset filters
     */
    function resetFilters($container, $form) {
        const containerId = $container.attr('id');
        const instance = infiniteScrollInstances[containerId];
        
        if (!instance) {
            return;
        }

        // Reset form
        $form[0].reset();

        // Clear filters
        instance.filters = {};
        instance.currentPage = 1;
        instance.hasMore = true;

        // Reload properties
        loadInitialProperties($container);
    }

    /**
     * Setup infinite scroll
     */
    function setupInfiniteScroll($container) {
        const containerId = $container.attr('id');
        const instance = infiniteScrollInstances[containerId];
        
        if (!instance) {
            return;
        }

        // Throttle scroll event
        let scrollTimeout;
        $(window).on('scroll.infinite-scroll-' + containerId, function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(function() {
                checkInfiniteScroll($container);
            }, 100);
        });
    }

    /**
     * Check if should load more on scroll
     */
    function checkInfiniteScroll($container) {
        const containerId = $container.attr('id');
        const instance = infiniteScrollInstances[containerId];
        
        if (!instance || instance.isLoading || !instance.hasMore) {
            return;
        }

        const $loadMoreBtn = $container.find('.resbs-load-more-btn');
        const btnOffset = $loadMoreBtn.offset();
        const windowHeight = $(window).height();
        const scrollTop = $(window).scrollTop();

        // Check if load more button is in viewport
        if (btnOffset && (btnOffset.top - windowHeight - scrollTop) < 200) {
            loadMoreProperties($container);
        }
    }

    /**
     * Update instance data
     */
    function updateInstanceData(containerId, data) {
        const instance = infiniteScrollInstances[containerId];
        if (instance) {
            instance.currentPage = data.current_page;
            instance.totalPages = data.total_pages;
            instance.totalProperties = data.total_properties;
            instance.hasMore = data.has_more;
        }
    }

    /**
     * Update controls
     */
    function updateControls($container, data) {
        const $loadMoreBtn = $container.find('.resbs-load-more-btn');
        const $pagination = $container.find('.resbs-pagination-fallback');
        const settings = $container.find('.resbs-infinite-properties-grid').data('settings');

        // Update load more button
        if (data.has_more) {
            $loadMoreBtn.show();
        } else {
            $loadMoreBtn.hide();
        }

        // Update pagination
        if (settings.show_pagination && data.total_pages > 1) {
            const paginationHtml = generatePagination(data.current_page, data.total_pages);
            $pagination.html(paginationHtml).show();
        } else {
            $pagination.hide();
        }
    }

    /**
     * Update count display
     */
    function updateCount($container, data) {
        const $count = $container.find('.resbs-count-number');
        $count.text(data.total_properties);
    }

    /**
     * Generate pagination HTML
     */
    function generatePagination(currentPage, totalPages) {
        let html = '<div class="resbs-pagination">';
        html += '<div class="resbs-pagination-info">';
        html += '<span class="resbs-page-info">' + resbs_infinite_scroll_ajax.messages.page + ' ' + currentPage + ' ' + resbs_infinite_scroll_ajax.messages.of + ' ' + totalPages + '</span>';
        html += '</div>';
        html += '<div class="resbs-pagination-links">';

        // Previous button
        if (currentPage > 1) {
            html += '<a href="#" class="resbs-pagination-link resbs-pagination-prev" data-page="' + (currentPage - 1) + '">';
            html += '<span class="dashicons dashicons-arrow-left-alt2"></span>';
            html += resbs_infinite_scroll_ajax.messages.previous;
            html += '</a>';
        }

        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(totalPages, currentPage + 2);

        if (startPage > 1) {
            html += '<a href="#" class="resbs-pagination-link" data-page="1">1</a>';
            if (startPage > 2) {
                html += '<span class="resbs-pagination-dots">...</span>';
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            const activeClass = i === currentPage ? ' active' : '';
            html += '<a href="#" class="resbs-pagination-link' + activeClass + '" data-page="' + i + '">' + i + '</a>';
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += '<span class="resbs-pagination-dots">...</span>';
            }
            html += '<a href="#" class="resbs-pagination-link" data-page="' + totalPages + '">' + totalPages + '</a>';
        }

        // Next button
        if (currentPage < totalPages) {
            html += '<a href="#" class="resbs-pagination-link resbs-pagination-next" data-page="' + (currentPage + 1) + '">';
            html += resbs_infinite_scroll_ajax.messages.next;
            html += '<span class="dashicons dashicons-arrow-right-alt2"></span>';
            html += '</a>';
        }

        html += '</div>';
        html += '</div>';

        return html;
    }

    /**
     * Show error message
     */
    function showError($container, message) {
        const $grid = $container.find('.resbs-infinite-properties-grid');
        $grid.html('<div class="resbs-error-container"><div class="resbs-error-icon"><span class="dashicons dashicons-warning"></span></div><p>' + message + '</p><button type="button" class="resbs-retry-btn">' + resbs_infinite_scroll_ajax.messages.retry + '</button></div>');
    }

    /**
     * Handle retry button clicks
     */
    $(document).on('click', '.resbs-retry-btn', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const $container = $btn.closest('.resbs-infinite-properties-container');
        loadInitialProperties($container);
    });

    /**
     * Handle window resize
     */
    $(window).on('resize', function() {
        // Recalculate infinite scroll positions
        Object.keys(infiniteScrollInstances).forEach(function(containerId) {
            const instance = infiniteScrollInstances[containerId];
            if (instance && instance.settings.infinite_scroll) {
                checkInfiniteScroll(instance.container);
            }
        });
    });

    /**
     * Public API
     */
    window.RESBSInfiniteScroll = {
        loadMore: loadMoreProperties,
        loadPage: loadPage,
        applyFilters: applyFilters,
        resetFilters: resetFilters,
        getInstance: function(containerId) {
            return infiniteScrollInstances[containerId];
        }
    };

})(jQuery);
