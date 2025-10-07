/**
 * Admin JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Initialize admin functionality when document is ready
    $(document).ready(function() {
        RESBS_Admin.init();
    });

    // Admin object
    window.RESBS_Admin = {
        
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.initTabs();
            this.initForms();
            this.initTables();
            this.initModals();
            this.initTooltips();
        },

        /**
         * Initialize tabs
         */
        initTabs: function() {
            $('.resbs-tab-btn').on('click', function() {
                var $btn = $(this);
                var $container = $btn.closest('.resbs-admin-tabs');
                var tabId = $btn.data('tab');
                
                // Update active tab button
                $container.find('.resbs-tab-btn').removeClass('active');
                $btn.addClass('active');
                
                // Update active tab panel
                $container.find('.resbs-tab-panel').removeClass('active');
                $container.find('#' + tabId).addClass('active');
            });
        },

        /**
         * Initialize forms
         */
        initForms: function() {
            // Form validation
            $('.resbs-form').on('submit', function(e) {
                if (!RESBS_Admin.validateForm($(this))) {
                    e.preventDefault();
                    return false;
                }
            });

            // Auto-save functionality
            $('.resbs-auto-save input, .resbs-auto-save select, .resbs-auto-save textarea').on('change', function() {
                var $form = $(this).closest('form');
                RESBS_Admin.autoSave($form);
            });
        },

        /**
         * Initialize tables
         */
        initTables: function() {
            // Sortable tables
            $('.resbs-table-sortable th[data-sort]').on('click', function() {
                var $th = $(this);
                var $table = $th.closest('table');
                var column = $th.data('sort');
                var direction = $th.hasClass('sort-asc') ? 'desc' : 'asc';
                
                // Update sort indicators
                $table.find('th').removeClass('sort-asc sort-desc');
                $th.addClass('sort-' + direction);
                
                // Sort table
                RESBS_Admin.sortTable($table, column, direction);
            });

            // Bulk actions
            $('.resbs-bulk-action').on('change', function() {
                var action = $(this).val();
                if (action) {
                    RESBS_Admin.showBulkActionForm(action);
                }
            });
        },

        /**
         * Initialize modals
         */
        initModals: function() {
            // Modal triggers
            $('[data-modal]').on('click', function(e) {
                e.preventDefault();
                var modalId = $(this).data('modal');
                RESBS_Admin.openModal(modalId);
            });

            // Modal close
            $('.resbs-modal-close, .resbs-modal-overlay').on('click', function() {
                RESBS_Admin.closeModal();
            });

            // Escape key to close modal
            $(document).on('keydown', function(e) {
                if (e.keyCode === 27) { // Escape key
                    RESBS_Admin.closeModal();
                }
            });
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            $('[data-tooltip]').each(function() {
                var $element = $(this);
                var tooltip = $element.data('tooltip');
                
                $element.on('mouseenter', function() {
                    RESBS_Admin.showTooltip($element, tooltip);
                });
                
                $element.on('mouseleave', function() {
                    RESBS_Admin.hideTooltip();
                });
            });
        },

        /**
         * Validate form
         */
        validateForm: function($form) {
            var isValid = true;
            var $requiredFields = $form.find('[required]');
            
            $requiredFields.each(function() {
                var $field = $(this);
                var value = $field.val().trim();
                
                if (!value) {
                    $field.addClass('error');
                    isValid = false;
                } else {
                    $field.removeClass('error');
                }
            });
            
            // Email validation
            $form.find('input[type="email"]').each(function() {
                var $field = $(this);
                var email = $field.val();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (email && !emailRegex.test(email)) {
                    $field.addClass('error');
                    isValid = false;
                }
            });
            
            // URL validation
            $form.find('input[type="url"]').each(function() {
                var $field = $(this);
                var url = $field.val();
                
                if (url) {
                    try {
                        new URL(url);
                    } catch (e) {
                        $field.addClass('error');
                        isValid = false;
                    }
                }
            });
            
            return isValid;
        },

        /**
         * Auto-save form
         */
        autoSave: function($form) {
            var formData = $form.serialize();
            var formId = $form.attr('id') || 'resbs-form';
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'resbs_auto_save',
                    form_id: formId,
                    form_data: formData,
                    nonce: $('#resbs_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        RESBS_Admin.showMessage('Form auto-saved', 'success');
                    }
                }
            });
        },

        /**
         * Sort table
         */
        sortTable: function($table, column, direction) {
            var $tbody = $table.find('tbody');
            var $rows = $tbody.find('tr').toArray();
            
            $rows.sort(function(a, b) {
                var aVal = $(a).find('td[data-column="' + column + '"]').text().trim();
                var bVal = $(b).find('td[data-column="' + column + '"]').text().trim();
                
                // Try to parse as numbers
                var aNum = parseFloat(aVal);
                var bNum = parseFloat(bVal);
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return direction === 'asc' ? aNum - bNum : bNum - aNum;
                } else {
                    return direction === 'asc' ? 
                        aVal.localeCompare(bVal) : 
                        bVal.localeCompare(aVal);
                }
            });
            
            $tbody.empty().append($rows);
        },

        /**
         * Show bulk action form
         */
        showBulkActionForm: function(action) {
            var $selectedItems = $('.resbs-table input[type="checkbox"]:checked');
            
            if ($selectedItems.length === 0) {
                RESBS_Admin.showMessage('Please select items to perform bulk action', 'warning');
                return;
            }
            
            var itemIds = [];
            $selectedItems.each(function() {
                itemIds.push($(this).val());
            });
            
            // Show confirmation modal
            var message = 'Are you sure you want to ' + action + ' ' + itemIds.length + ' item(s)?';
            
            if (confirm(message)) {
                RESBS_Admin.performBulkAction(action, itemIds);
            }
        },

        /**
         * Perform bulk action
         */
        performBulkAction: function(action, itemIds) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'resbs_bulk_action',
                    bulk_action: action,
                    item_ids: itemIds,
                    nonce: $('#resbs_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        RESBS_Admin.showMessage(response.data.message, 'success');
                        location.reload();
                    } else {
                        RESBS_Admin.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    RESBS_Admin.showMessage('An error occurred', 'error');
                }
            });
        },

        /**
         * Open modal
         */
        openModal: function(modalId) {
            var $modal = $('#' + modalId);
            if ($modal.length) {
                $modal.addClass('active');
                $('body').addClass('modal-open');
            }
        },

        /**
         * Close modal
         */
        closeModal: function() {
            $('.resbs-modal.active').removeClass('active');
            $('body').removeClass('modal-open');
        },

        /**
         * Show tooltip
         */
        showTooltip: function($element, text) {
            var $tooltip = $('<div class="resbs-tooltip">' + text + '</div>');
            $('body').append($tooltip);
            
            var offset = $element.offset();
            var elementWidth = $element.outerWidth();
            var elementHeight = $element.outerHeight();
            var tooltipWidth = $tooltip.outerWidth();
            var tooltipHeight = $tooltip.outerHeight();
            
            var left = offset.left + (elementWidth / 2) - (tooltipWidth / 2);
            var top = offset.top - tooltipHeight - 10;
            
            $tooltip.css({
                position: 'absolute',
                left: left + 'px',
                top: top + 'px',
                zIndex: 9999
            });
        },

        /**
         * Hide tooltip
         */
        hideTooltip: function() {
            $('.resbs-tooltip').remove();
        },

        /**
         * Show message
         */
        showMessage: function(message, type) {
            var $message = $('<div class="resbs-alert resbs-alert-' + type + '">' + message + '</div>');
            
            $('.resbs-admin-wrap').prepend($message);
            
            setTimeout(function() {
                $message.fadeOut(function() {
                    $message.remove();
                });
            }, 5000);
        },

        /**
         * Confirm action
         */
        confirm: function(message, callback) {
            if (confirm(message)) {
                callback();
            }
        },

        /**
         * Loading state
         */
        setLoading: function($element, loading) {
            if (loading) {
                $element.addClass('loading').prop('disabled', true);
                $element.data('original-text', $element.text());
                $element.html('<span class="resbs-spinner"></span> Loading...');
            } else {
                $element.removeClass('loading').prop('disabled', false);
                $element.text($element.data('original-text'));
            }
        }
    };

    // Utility functions
    window.RESBS_Utils = {
        
        /**
         * Format number
         */
        formatNumber: function(num) {
            return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        },

        /**
         * Format currency
         */
        formatCurrency: function(amount, currency) {
            currency = currency || '$';
            return currency + this.formatNumber(amount);
        },

        /**
         * Format date
         */
        formatDate: function(date) {
            return new Date(date).toLocaleDateString();
        },

        /**
         * Debounce function
         */
        debounce: function(func, wait) {
            var timeout;
            return function executedFunction() {
                var later = function() {
                    clearTimeout(timeout);
                    func();
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        /**
         * Throttle function
         */
        throttle: function(func, limit) {
            var inThrottle;
            return function() {
                var args = arguments;
                var context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(function() {
                        inThrottle = false;
                    }, limit);
                }
            };
        }
    };

})(jQuery);
