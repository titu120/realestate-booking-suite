/**
 * Email Admin JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Initialize when document is ready
    $(document).ready(function() {
        initializeEmailAdmin();
    });

    /**
     * Initialize email admin functionality
     */
    function initializeEmailAdmin() {
        // Template tabs
        $('.resbs-tab-button').on('click', function() {
            const templateType = $(this).data('template');
            switchTemplate(templateType);
        });

        // SMTP settings toggle
        $('input[name="resbs_email_smtp_enabled"]').on('change', function() {
            toggleSmtpSettings($(this).is(':checked'));
        });

        // Initialize SMTP settings visibility
        toggleSmtpSettings($('input[name="resbs_email_smtp_enabled"]').is(':checked'));

        // Insert placeholder buttons
        $('.resbs-insert-placeholder').on('click', function() {
            const templateType = $(this).data('template');
            showPlaceholderSelector($(this), templateType);
        });

        // Preview template buttons
        $('.resbs-preview-template').on('click', function() {
            const templateType = $(this).data('template');
            previewTemplate(templateType);
        });

        // Send test email
        $('#resbs-send-test-email').on('click', function() {
            sendTestEmail();
        });

        // Close preview modal
        $(document).on('click', '.resbs-email-preview-close', function() {
            closePreview();
        });

        // Close preview on escape key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape') {
                closePreview();
            }
        });

        // Close preview on background click
        $(document).on('click', '.resbs-email-preview', function(e) {
            if (e.target === this) {
                closePreview();
            }
        });

        // Close placeholder selector on outside click
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.resbs-placeholder-selector, .resbs-insert-placeholder').length) {
                $('.resbs-placeholder-selector').removeClass('show');
            }
        });
    }

    /**
     * Switch template tab
     */
    function switchTemplate(templateType) {
        // Update tab buttons
        $('.resbs-tab-button').removeClass('active');
        $('.resbs-tab-button[data-template="' + templateType + '"]').addClass('active');

        // Update template panels
        $('.resbs-template-panel').removeClass('active');
        $('.resbs-template-panel[data-template="' + templateType + '"]').addClass('active');
    }

    /**
     * Toggle SMTP settings visibility
     */
    function toggleSmtpSettings(enabled) {
        if (enabled) {
            $('.smtp-setting').addClass('show');
        } else {
            $('.smtp-setting').removeClass('show');
        }
    }

    /**
     * Show placeholder selector
     */
    function showPlaceholderSelector(button, templateType) {
        const selector = $('.resbs-placeholder-selector');
        
        // Hide any existing selectors
        selector.removeClass('show');
        
        // Create or update placeholder list
        const placeholders = getPlaceholdersForTemplate(templateType);
        let placeholderHtml = '';
        
        placeholders.forEach(function(placeholder) {
            placeholderHtml += '<div class="resbs-placeholder-item" data-placeholder="{' + placeholder + '}">' + placeholder + '</div>';
        });
        
        // Create selector if it doesn't exist
        if (selector.length === 0) {
            $('body').append('<div class="resbs-placeholder-selector">' + placeholderHtml + '</div>');
        } else {
            selector.html(placeholderHtml);
        }
        
        // Position selector
        const buttonOffset = button.offset();
        const selectorElement = $('.resbs-placeholder-selector');
        selectorElement.css({
            top: buttonOffset.top + button.outerHeight() + 5,
            left: buttonOffset.left
        });
        
        // Show selector
        selectorElement.addClass('show');
        
        // Handle placeholder selection
        selectorElement.off('click.placeholder').on('click.placeholder', '.resbs-placeholder-item', function() {
            const placeholder = $(this).data('placeholder');
            const textarea = $('textarea[name="resbs_email_template_' + templateType + '"]');
            const currentValue = textarea.val();
            const cursorPos = textarea.prop('selectionStart');
            
            // Insert placeholder at cursor position
            const newValue = currentValue.substring(0, cursorPos) + placeholder + currentValue.substring(cursorPos);
            textarea.val(newValue);
            
            // Set cursor position after inserted placeholder
            const newCursorPos = cursorPos + placeholder.length;
            textarea.prop('selectionStart', newCursorPos);
            textarea.prop('selectionEnd', newCursorPos);
            textarea.focus();
            
            // Hide selector
            selectorElement.removeClass('show');
        });
    }

    /**
     * Get placeholders for template type
     */
    function getPlaceholdersForTemplate(templateType) {
        const placeholders = {
            'property_submission': [
                'site_name',
                'property_title',
                'property_url',
                'property_id',
                'submission_date',
                'submission_time',
                'submitter_name',
                'submitter_email',
                'submitter_phone'
            ],
            'booking_confirmation': [
                'site_name',
                'booking_id',
                'booking_date',
                'booking_time',
                'property_title',
                'property_url',
                'customer_name',
                'customer_email',
                'customer_phone',
                'booking_notes'
            ],
            'booking_cancellation': [
                'site_name',
                'booking_id',
                'booking_date',
                'booking_time',
                'property_title',
                'property_url',
                'customer_name',
                'customer_email',
                'customer_phone'
            ],
            'search_alert': [
                'site_name',
                'search_id',
                'search_criteria',
                'properties_count',
                'subscriber_name',
                'subscriber_email',
                'alert_date'
            ]
        };
        
        return placeholders[templateType] || [];
    }

    /**
     * Preview email template
     */
    function previewTemplate(templateType) {
        const templateContent = $('textarea[name="resbs_email_template_' + templateType + '"]').val();
        const subject = $('input[name="resbs_email_subject_' + templateType + '"]').val();
        
        if (!templateContent.trim()) {
            alert(resbs_email_admin_ajax.messages.preview_error);
            return;
        }
        
        // Show loading state
        const previewButton = $('.resbs-preview-template[data-template="' + templateType + '"]');
        previewButton.prop('disabled', true).text(resbs_email_admin_ajax.messages.preview_loading);
        
        // Send AJAX request
        $.ajax({
            url: resbs_email_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_preview_email_template',
                template_type: templateType,
                template_content: templateContent,
                nonce: resbs_email_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showPreview(subject, response.data.preview);
                } else {
                    alert(resbs_email_admin_ajax.messages.preview_error);
                }
            },
            error: function() {
                alert(resbs_email_admin_ajax.messages.preview_error);
            },
            complete: function() {
                previewButton.prop('disabled', false).text('Preview');
            }
        });
    }

    /**
     * Show email preview
     */
    function showPreview(subject, content) {
        // Create preview modal if it doesn't exist
        let previewModal = $('.resbs-email-preview');
        if (previewModal.length === 0) {
            previewModal = $('<div class="resbs-email-preview">' +
                '<div class="resbs-email-preview-header">' +
                    '<h3>Email Preview</h3>' +
                    '<button class="resbs-email-preview-close">&times;</button>' +
                '</div>' +
                '<div class="resbs-email-preview-content"></div>' +
            '</div>');
            $('body').append(previewModal);
        }
        
        // Update content
        previewModal.find('.resbs-email-preview-content').html(
            '<h4>Subject: ' + escapeHtml(subject) + '</h4>' +
            '<div class="resbs-email-preview-body">' + content + '</div>'
        );
        
        // Show modal
        previewModal.addClass('show');
        
        // Add overlay
        if ($('.resbs-email-preview-overlay').length === 0) {
            $('body').append('<div class="resbs-email-preview-overlay"></div>');
        }
    }

    /**
     * Close email preview
     */
    function closePreview() {
        $('.resbs-email-preview').removeClass('show');
        $('.resbs-email-preview-overlay').remove();
    }

    /**
     * Send test email
     */
    function sendTestEmail() {
        const testEmail = $('#resbs_test_email').val();
        
        if (!testEmail || !isValidEmail(testEmail)) {
            alert('Please enter a valid email address.');
            return;
        }
        
        // Show loading state
        const sendButton = $('#resbs-send-test-email');
        const originalText = sendButton.text();
        sendButton.prop('disabled', true).text('Sending...');
        
        // Send AJAX request
        $.ajax({
            url: resbs_email_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'resbs_send_test_email',
                test_email: testEmail,
                nonce: resbs_email_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    showMessage(response.data.message, 'success');
                } else {
                    showMessage(response.data.message, 'error');
                }
            },
            error: function() {
                showMessage(resbs_email_admin_ajax.messages.test_email_failed, 'error');
            },
            complete: function() {
                sendButton.prop('disabled', false).text(originalText);
            }
        });
    }

    /**
     * Show message
     */
    function showMessage(message, type) {
        // Remove existing messages
        $('.resbs-message').remove();
        
        // Create new message
        const messageElement = $('<div class="resbs-message ' + type + '">' + escapeHtml(message) + '</div>');
        
        // Insert after page title
        $('.wrap h1').after(messageElement);
        
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
     * Auto-save functionality
     */
    let autoSaveTimeout;
    $('textarea[name^="resbs_email_template_"], input[name^="resbs_email_subject_"]').on('input', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            // Auto-save could be implemented here
        }, 2000);
    });

    /**
     * Character count for textareas
     */
    $('textarea[name^="resbs_email_template_"]').each(function() {
        const textarea = $(this);
        const maxLength = 10000; // Set maximum length
        const counter = $('<div class="resbs-char-counter">0 / ' + maxLength + '</div>');
        
        textarea.after(counter);
        
        textarea.on('input', function() {
            const length = $(this).val().length;
            counter.text(length + ' / ' + maxLength);
            
            if (length > maxLength * 0.9) {
                counter.addClass('warning');
            } else {
                counter.removeClass('warning');
            }
            
            if (length > maxLength) {
                counter.addClass('error');
            } else {
                counter.removeClass('error');
            }
        });
        
        // Initial count
        textarea.trigger('input');
    });

    /**
     * Template validation
     */
    $('form').on('submit', function(e) {
        let hasErrors = false;
        
        // Check required fields
        $('input[required], textarea[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('error');
                hasErrors = true;
            } else {
                $(this).removeClass('error');
            }
        });
        
        // Check email fields
        $('input[type="email"]').each(function() {
            if ($(this).val() && !isValidEmail($(this).val())) {
                $(this).addClass('error');
                hasErrors = true;
            } else {
                $(this).removeClass('error');
            }
        });
        
        if (hasErrors) {
            e.preventDefault();
            showMessage('Please fix the errors before saving.', 'error');
        }
    });

    /**
     * Remove error class on input
     */
    $('input, textarea, select').on('input change', function() {
        $(this).removeClass('error');
    });

})(jQuery);

