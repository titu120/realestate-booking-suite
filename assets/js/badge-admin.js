/**
 * Badge Admin JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initColorPickers();
        
        initBadgePreview();
        initFormValidation();
    });

    /**
     * Initialize color pickers
     */
    function initColorPickers() {
        $('.color-picker').wpColorPicker({
            change: function(event, ui) {
                updateBadgePreview();
            },
            clear: function() {
                updateBadgePreview();
            }
        });
    }

    /**
     * Initialize badge preview
     */
    function initBadgePreview() {
        // Create preview container if it doesn't exist
        if ($('.resbs-badge-preview-container').length === 0) {
            $('.resbs-badge-settings').prepend('<div class="resbs-badge-preview-container"><h3>Preview</h3><div class="resbs-badge-preview-demo"></div></div>');
        }

        updateBadgePreview();
    }

    /**
     * Update badge preview
     */
    function updateBadgePreview() {
        var $previewContainer = $('.resbs-badge-preview-demo');
        $previewContainer.empty();

        // Badge types
        var badgeTypes = ['featured', 'new', 'sold'];

        badgeTypes.forEach(function(type) {
            var enabled = $('#resbs_badge_' + type + '_enabled').is(':checked');
            
            if (enabled) {
                var text = $('#resbs_badge_' + type + '_text').val() || getDefaultText(type);
                var bgColor = $('#resbs_badge_' + type + '_bg_color').val() || getDefaultBgColor(type);
                var textColor = $('#resbs_badge_' + type + '_text_color').val() || getDefaultTextColor(type);
                var size = $('#resbs_badge_' + type + '_size').val() || 'medium';
                var borderRadius = $('#resbs_badge_' + type + '_border_radius').val() || '4';

                var $badge = $('<span class="resbs-badge resbs-badge-' + type + ' resbs-badge-' + size + '">' + text + '</span>');
                
                $badge.css({
                    'background-color': bgColor,
                    'color': textColor,
                    'border-radius': borderRadius + 'px'
                });

                $previewContainer.append($badge);
            }
        });
    }

    /**
     * Get default text for badge type
     */
    function getDefaultText(type) {
        var defaults = {
            'featured': 'Featured',
            'new': 'New',
            'sold': 'Sold'
        };
        return defaults[type] || type;
    }

    /**
     * Get default background color for badge type
     */
    function getDefaultBgColor(type) {
        var defaults = {
            'featured': '#ff6b35',
            'new': '#28a745',
            'sold': '#dc3545'
        };
        return defaults[type] || '#333333';
    }

    /**
     * Get default text color for badge type
     */
    function getDefaultTextColor(type) {
        return '#ffffff';
    }

    /**
     * Initialize form validation
     */
    function initFormValidation() {
        // CRITICAL FIX: Exclude WordPress post form (#post) - only validate badge settings forms
        // CRITICAL FIX: Don't block form submission - just show warnings
        $('form:not(#post)').on('submit', function(e) {
            var isValid = true;
            var errors = [];

            // Validate color fields (non-blocking)
            $('.color-picker').each(function() {
                var value = $(this).val();
                if (value && !isValidHexColor(value)) {
                    isValid = false;
                    errors.push('Invalid color format for ' + $(this).attr('name'));
                }
            });

            // Validate border radius (non-blocking)
            $('input[name*="border_radius"]').each(function() {
                var value = parseInt($(this).val());
                if (isNaN(value) || value < 0 || value > 50) {
                    isValid = false;
                    errors.push('Border radius must be between 0 and 50');
                }
            });

            // CRITICAL FIX: Don't prevent form submission - just show warning
            // WordPress handles validation server-side
            if (!isValid) {
                alert('Warning: Please fix the following errors:\n' + errors.join('\n') + '\n\nForm will still submit, but errors may occur.');
                // Don't prevent default - let WordPress handle it
            }
        });
    }

    /**
     * Validate hex color
     */
    function isValidHexColor(color) {
        return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(color);
    }

    /**
     * Watch for changes in form fields
     */
    $('input, select').on('change input', function() {
        updateBadgePreview();
    });

    /**
     * Reset to defaults
     */
    $('.resbs-reset-defaults').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to reset all badge settings to defaults?')) {
            resetToDefaults();
        }
    });

    /**
     * Reset badge settings to defaults
     */
    function resetToDefaults() {
        var defaults = {
            'featured': {
                'text': 'Featured',
                'bg_color': '#ff6b35',
                'text_color': '#ffffff',
                'position': 'top-left',
                'size': 'medium',
                'border_radius': '4'
            },
            'new': {
                'text': 'New',
                'bg_color': '#28a745',
                'text_color': '#ffffff',
                'position': 'top-left',
                'size': 'medium',
                'border_radius': '4'
            },
            'sold': {
                'text': 'Sold',
                'bg_color': '#dc3545',
                'text_color': '#ffffff',
                'position': 'top-left',
                'size': 'medium',
                'border_radius': '4'
            }
        };

        Object.keys(defaults).forEach(function(type) {
            Object.keys(defaults[type]).forEach(function(field) {
                var $field = $('#resbs_badge_' + type + '_' + field);
                if ($field.length) {
                    $field.val(defaults[type][field]);
                    
                    // Trigger color picker update if it's a color field
                    if (field.includes('color')) {
                        $field.trigger('change');
                    }
                }
            });
        });

        updateBadgePreview();
    }

    /**
     * Export settings
     */
    $('.resbs-export-settings').on('click', function(e) {
        e.preventDefault();
        exportSettings();
    });

    /**
     * Export badge settings
     */
    function exportSettings() {
        var settings = {};
        
        $('input, select').each(function() {
            var name = $(this).attr('name');
            if (name && name.startsWith('resbs_badge_')) {
                var value = $(this).val();
                if ($(this).is(':checkbox')) {
                    value = $(this).is(':checked') ? '1' : '0';
                }
                settings[name] = value;
            }
        });

        var dataStr = JSON.stringify(settings, null, 2);
        var dataBlob = new Blob([dataStr], {type: 'application/json'});
        
        var link = document.createElement('a');
        link.href = URL.createObjectURL(dataBlob);
        link.download = 'resbs-badge-settings.json';
        link.click();
    }

    /**
     * Import settings
     */
    $('.resbs-import-settings').on('click', function(e) {
        e.preventDefault();
        $('#resbs-import-file').click();
    });

    /**
     * Handle file import
     */
    $('#resbs-import-file').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var settings = JSON.parse(e.target.result);
                    importSettings(settings);
                } catch (error) {
                    alert('Invalid file format. Please select a valid JSON file.');
                }
            };
            reader.readAsText(file);
        }
    });

    /**
     * Import badge settings
     */
    function importSettings(settings) {
        if (confirm('This will overwrite your current badge settings. Continue?')) {
            Object.keys(settings).forEach(function(name) {
                var $field = $('[name="' + name + '"]');
                if ($field.length) {
                    if ($field.is(':checkbox')) {
                        $field.prop('checked', settings[name] === '1');
                    } else {
                        $field.val(settings[name]);
                    }
                }
            });
            
            updateBadgePreview();
            alert('Settings imported successfully!');
        }
    }

    /**
     * Add import/export buttons to form
     */
    if ($('.resbs-import-export-buttons').length === 0) {
        $('.submit').before('<div class="resbs-import-export-buttons"><button type="button" class="button resbs-export-settings">Export Settings</button> <button type="button" class="button resbs-import-settings">Import Settings</button> <button type="button" class="button resbs-reset-defaults">Reset to Defaults</button></div><input type="file" id="resbs-import-file" accept=".json" style="display: none;">');
    }

})(jQuery);
