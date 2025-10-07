/**
 * Search Alerts JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initializeSearchAlerts();
    });

    /**
     * Initialize search alerts functionality
     */
    function initializeSearchAlerts() {
        // Save search alert form
        $('#resbs-search-alert-form').on('submit', function(e) {
            e.preventDefault();
            saveSearchAlert();
        });

        // Delete search alert
        $(document).on('click', '.resbs-alert-btn.delete', function(e) {
            e.preventDefault();
            const alertId = $(this).data('alert-id');
            const email = $(this).data('email');
            deleteSearchAlert(alertId, email);
        });

        // Load alerts if email is provided
        const emailInput = $('#resbs_alert_email');
        if (emailInput.length && emailInput.val()) {
            loadSearchAlerts(emailInput.val());
        }

        // Load alerts when email changes
        emailInput.on('blur', function() {
            const email = $(this).val();
            if (email && isValidEmail(email)) {
                loadSearchAlerts(email);
            }
        });

        // Update search criteria from current filters
        updateSearchCriteria();
    }

    /**
     * Save search alert
     */
    function saveSearchAlert() {
        const form = $('#resbs-search-alert-form');
        const formData = new FormData(form[0]);
        
        // Validate form
        if (!validateSearchAlertForm(formData)) {
            return;
        }

        // Show loading state
        const submitBtn = form.find('.resbs-save-alert-btn');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text(resbs_search_alerts_ajax.messages.loading);

        // Send AJAX request
        $.ajax({
            url: resbs_search_alerts_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_save_search_alert',
                name: formData.get('name'),
                email: formData.get('email'),
                frequency: formData.get('frequency'),
                search_criteria: formData.get('search_criteria'),
                nonce: resbs_search_alerts_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    form[0].reset();
                    updateSearchCriteria();
                    
                    // Load updated alerts
                    const email = formData.get('email');
                    if (email) {
                        loadSearchAlerts(email);
                    }
                } else {
                    showMessage(response.data.message, 'error');
                }
            },
            error: function() {
                showMessage(resbs_search_alerts_ajax.messages.error, 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    }

    /**
     * Delete search alert
     */
    function deleteSearchAlert(alertId, email) {
        if (!confirm('Are you sure you want to delete this search alert?')) {
            return;
        }

        // Show loading state
        const deleteBtn = $('.resbs-alert-btn.delete[data-alert-id="' + alertId + '"]');
        const originalText = deleteBtn.text();
        deleteBtn.prop('disabled', true).text(resbs_search_alerts_ajax.messages.loading);

        // Send AJAX request
        $.ajax({
            url: resbs_search_alerts_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_delete_search_alert',
                alert_id: alertId,
                email: email,
                nonce: resbs_search_alerts_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage(response.data.message, 'success');
                    loadSearchAlerts(email);
                } else {
                    showMessage(response.data.message, 'error');
                }
            },
            error: function() {
                showMessage(resbs_search_alerts_ajax.messages.error, 'error');
            },
            complete: function() {
                deleteBtn.prop('disabled', false).text(originalText);
            }
        });
    }

    /**
     * Load search alerts
     */
    function loadSearchAlerts(email) {
        const alertsList = $('#resbs-alerts-list');
        
        // Show loading state
        alertsList.html('<div class="resbs-loading"><div class="resbs-loading-spinner"></div>' + resbs_search_alerts_ajax.messages.loading + '</div>');

        // Send AJAX request
        $.ajax({
            url: resbs_search_alerts_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_get_search_alerts',
                email: email,
                nonce: resbs_search_alerts_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    renderSearchAlerts(response.data.alerts);
                } else {
                    showMessage(response.data.message, 'error');
                    alertsList.html('<div class="resbs-alerts-empty"><div class="resbs-alerts-empty-icon">📧</div><h4>No Alerts Found</h4><p>No search alerts found for this email address.</p></div>');
                }
            },
            error: function() {
                showMessage(resbs_search_alerts_ajax.messages.error, 'error');
                alertsList.html('<div class="resbs-alerts-empty"><div class="resbs-alerts-empty-icon">❌</div><h4>Error Loading Alerts</h4><p>Failed to load search alerts. Please try again.</p></div>');
            }
        });
    }

    /**
     * Render search alerts
     */
    function renderSearchAlerts(alerts) {
        const alertsList = $('#resbs-alerts-list');
        
        if (!alerts || alerts.length === 0) {
            alertsList.html('<div class="resbs-alerts-empty"><div class="resbs-alerts-empty-icon">📧</div><h4>No Search Alerts</h4><p>You don\'t have any saved search alerts yet.</p></div>');
            return;
        }

        let html = '';
        alerts.forEach(function(alert) {
            html += renderSearchAlert(alert);
        });
        
        alertsList.html(html);
    }

    /**
     * Render individual search alert
     */
    function renderSearchAlert(alert) {
        const criteria = JSON.parse(alert.search_criteria);
        const criteriaText = formatSearchCriteria(criteria);
        const createdDate = new Date(alert.created_at).toLocaleDateString();
        const lastSent = alert.last_sent ? new Date(alert.last_sent).toLocaleDateString() : 'Never';
        
        return `
            <div class="resbs-alert-item">
                <div class="resbs-alert-header">
                    <h4 class="resbs-alert-title">${escapeHtml(alert.name)}'s Alert</h4>
                    <div class="resbs-alert-actions">
                        <button type="button" class="resbs-alert-btn delete" data-alert-id="${alert.id}" data-email="${escapeHtml(alert.email)}">
                            Delete
                        </button>
                    </div>
                </div>
                
                <div class="resbs-alert-details">
                    <div class="resbs-alert-detail">
                        <span class="resbs-alert-detail-label">Email</span>
                        <span class="resbs-alert-detail-value">${escapeHtml(alert.email)}</span>
                    </div>
                    <div class="resbs-alert-detail">
                        <span class="resbs-alert-detail-label">Frequency</span>
                        <span class="resbs-alert-detail-value">${escapeHtml(alert.frequency)}</span>
                    </div>
                    <div class="resbs-alert-detail">
                        <span class="resbs-alert-detail-label">Created</span>
                        <span class="resbs-alert-detail-value">${createdDate}</span>
                    </div>
                    <div class="resbs-alert-detail">
                        <span class="resbs-alert-detail-label">Last Sent</span>
                        <span class="resbs-alert-detail-value">${lastSent}</span>
                    </div>
                </div>
                
                <div class="resbs-alert-criteria">
                    <h4>Search Criteria</h4>
                    <p>${escapeHtml(criteriaText)}</p>
                </div>
            </div>
        `;
    }

    /**
     * Format search criteria for display
     */
    function formatSearchCriteria(criteria) {
        const parts = [];
        
        if (criteria.price_min || criteria.price_max) {
            let priceRange = '';
            if (criteria.price_min) {
                priceRange += '$' + numberFormat(criteria.price_min);
            }
            priceRange += ' - ';
            if (criteria.price_max) {
                priceRange += '$' + numberFormat(criteria.price_max);
            }
            parts.push('Price: ' + priceRange);
        }
        
        if (criteria.bedrooms) {
            parts.push('Bedrooms: ' + criteria.bedrooms + '+');
        }
        
        if (criteria.bathrooms) {
            parts.push('Bathrooms: ' + criteria.bathrooms + '+');
        }
        
        if (criteria.property_type) {
            parts.push('Type: ' + criteria.property_type);
        }
        
        if (criteria.location) {
            parts.push('Location: ' + criteria.location);
        }
        
        return parts.length > 0 ? parts.join(', ') : 'No specific criteria';
    }

    /**
     * Update search criteria from current filters
     */
    function updateSearchCriteria() {
        // Get current search filters from the page
        const criteria = {};
        
        // Price range
        const priceMin = $('input[name="price_min"]').val();
        const priceMax = $('input[name="price_max"]').val();
        if (priceMin) criteria.price_min = priceMin;
        if (priceMax) criteria.price_max = priceMax;
        
        // Bedrooms
        const bedrooms = $('select[name="bedrooms"]').val();
        if (bedrooms) criteria.bedrooms = bedrooms;
        
        // Bathrooms
        const bathrooms = $('select[name="bathrooms"]').val();
        if (bathrooms) criteria.bathrooms = bathrooms;
        
        // Property type
        const propertyType = $('select[name="property_type"]').val();
        if (propertyType) criteria.property_type = propertyType;
        
        // Property status
        const propertyStatus = $('select[name="property_status"]').val();
        if (propertyStatus) criteria.property_status = propertyStatus;
        
        // Location
        const location = $('select[name="location"]').val();
        if (location) criteria.location = location;
        
        // Update hidden field
        $('#resbs_search_criteria').val(JSON.stringify(criteria));
        
        // Update criteria display
        const criteriaText = formatSearchCriteria(criteria);
        $('.resbs-search-criteria p').text(criteriaText || 'No search criteria selected. Please use the filters above to set your criteria.');
    }

    /**
     * Validate search alert form
     */
    function validateSearchAlertForm(formData) {
        const name = formData.get('name');
        const email = formData.get('email');
        const searchCriteria = formData.get('search_criteria');
        
        // Clear previous errors
        $('.resbs-form-group').removeClass('error');
        $('.resbs-form-error').remove();
        
        let hasErrors = false;
        
        // Validate name
        if (!name || name.trim() === '') {
            showFieldError('resbs_alert_name', resbs_search_alerts_ajax.messages.name_required);
            hasErrors = true;
        }
        
        // Validate email
        if (!email || email.trim() === '') {
            showFieldError('resbs_alert_email', resbs_search_alerts_ajax.messages.email_required);
            hasErrors = true;
        } else if (!isValidEmail(email)) {
            showFieldError('resbs_alert_email', resbs_search_alerts_ajax.messages.invalid_email);
            hasErrors = true;
        }
        
        // Validate search criteria
        if (!searchCriteria || searchCriteria.trim() === '') {
            showFieldError('resbs_search_criteria', resbs_search_alerts_ajax.messages.search_criteria_required);
            hasErrors = true;
        }
        
        return !hasErrors;
    }

    /**
     * Show field error
     */
    function showFieldError(fieldId, message) {
        const field = $('#' + fieldId);
        const formGroup = field.closest('.resbs-form-group');
        
        formGroup.addClass('error');
        
        if (formGroup.find('.resbs-form-error').length === 0) {
            formGroup.append('<div class="resbs-form-error">' + escapeHtml(message) + '</div>');
        }
    }

    /**
     * Show message
     */
    function showMessage(message, type) {
        // Remove existing messages
        $('.resbs-message').remove();
        
        // Create new message
        const messageElement = $('<div class="resbs-message ' + type + '">' + escapeHtml(message) + '</div>');
        
        // Insert at top of container
        $('.resbs-search-alerts-container').prepend(messageElement);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            messageElement.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }

    /**
     * Validate email address
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Format number with commas
     */
    function numberFormat(number) {
        return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    /**
     * Auto-update search criteria when filters change
     */
    $('input[name="price_min"], input[name="price_max"], select[name="bedrooms"], select[name="bathrooms"], select[name="property_type"], select[name="property_status"], select[name="location"]').on('change', function() {
        updateSearchCriteria();
    });

    /**
     * Initialize search criteria on page load
     */
    $(window).on('load', function() {
        setTimeout(updateSearchCriteria, 1000);
    });

})(jQuery);

