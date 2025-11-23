// ENHANCED SETTINGS - SIMPLE URL-BASED NAVIGATION
jQuery(document).ready(function($) {
    // Tabs now use direct links, no AJAX needed
    // Just ensure active state is correct on page load
    var currentTab = window.location.search.match(/[?&]tab=([^&]+)/);
    if (currentTab) {
        $('.resbs-nav-link').removeClass('active');
        $('.resbs-nav-link[href*="tab=' + currentTab[1] + '"]').addClass('active');
    }
    
    // Enhanced Color Picker Functionality
    function updateColorHex(colorInput, hexInput) {
        var color = colorInput.val();
        hexInput.val(color.toUpperCase());
    }
    
    // Initialize color pickers - Use event delegation for dynamically loaded content
    $(document).on('input change', 'input[type="color"]', function() {
        var $colorInput = $(this);
        var $hexInput = $colorInput.closest('td').find('.resbs-color-hex');
        if ($hexInput.length) {
            updateColorHex($colorInput, $hexInput);
        }
    });
    
    $(document).on('input', '.resbs-color-hex', function() {
        var $hexInput = $(this);
        var hex = $hexInput.val();
        if (/^#[0-9A-F]{6}$/i.test(hex)) {
            var $colorInput = $hexInput.closest('td').find('input[type="color"]');
            if ($colorInput.length) {
                $colorInput.val(hex);
            }
        }
    });
    
    $(document).on('click', '.resbs-color-reset', function() {
        var $button = $(this);
        var defaultColor = $button.data('default');
        var $colorInput = $button.closest('td').find('input[type="color"]');
        var $hexInput = $button.closest('td').find('.resbs-color-hex');
        
        if ($colorInput.length && defaultColor) {
            $colorInput.val(defaultColor);
            if ($hexInput.length) {
                updateColorHex($colorInput, $hexInput);
            }
        }
    });
    
    // Sync hex input to color input before form submit
    // IMPORTANT: Only attach to settings forms, NOT WordPress post forms
    $(document).on('submit', 'form:not(#post)', function() {
        $(this).find('.resbs-color-hex').each(function() {
            var $hexInput = $(this);
            var hex = $hexInput.val();
            if (/^#[0-9A-F]{6}$/i.test(hex)) {
                var $colorInput = $hexInput.closest('td').find('input[type="color"]');
                if ($colorInput.length) {
                    $colorInput.val(hex);
                }
            }
        });
    });
    
    // Handle Create Page button
    $(document).on('click', '.resbs-create-page-btn', function(e) {
        e.preventDefault();
        var $button = $(this);
        var pageType = $button.data('page-type');
        var originalText = $button.text();
        
        if (!pageType) {
            alert('Error: Page type not specified');
            return;
        }
        
        // Disable button and show loading
        $button.prop('disabled', true).text('Creating...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'resbs_create_page',
                page_type: pageType,
                nonce: resbsEnhancedSettings.nonceCreatePage
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    var $message = $('<div>').addClass('notice notice-success inline');
                    $message.append($('<p>').text(response.data.message));
                    if (response.data.view_url && response.data.edit_url) {
                        var $links = $('<p>');
                        $links.append($('<a>').attr('href', response.data.view_url).attr('target', '_blank').text('View Page'));
                        $links.append(' | ');
                        $links.append($('<a>').attr('href', response.data.edit_url).text('Edit Page'));
                        $message.append($links);
                    }
                    
                    $button.closest('td').append($message);
                    $button.hide();
                    
                    // Reload page after 2 seconds to show updated info
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    alert('Error: ' + (response.data.message || 'Failed to create page'));
                    $button.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('Error: Failed to create page. Please try again.');
                $button.prop('disabled', false).text(originalText);
            }
        });
    });
});

