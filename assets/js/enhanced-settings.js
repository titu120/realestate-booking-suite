// MINIMAL WORKING VERSION
jQuery(document).ready(function($) {
    // Tab switching
    $('.resbs-nav-link').on('click', function(e) {
        e.preventDefault();
        
        // Get tab data
        var tab = $(this).data('tab');
        
        // Don't reload if same tab
        if ($(this).hasClass('active')) {
            return;
        }
        
        // Update active state
        $('.resbs-nav-link').removeClass('active');
        $(this).addClass('active');
        
        // Show loading spinner with minimum time
        var loadingStartTime = Date.now();
        $('.resbs-settings-content').html('<div style="text-align: center; padding: 30px;"><div style="display: inline-block; width: 30px; height: 30px; border: 3px solid #f3f3f3; border-top: 3px solid #00a0d2; border-radius: 50%; animation: spin 1s linear infinite;"></div><p style="margin-top: 15px; font-size: 14px;">Loading...</p></div>');
        
        // Load tab content via AJAX
        $.post(ajaxurl, {
            action: 'resbs_load_tab_content',
            tab: tab,
            nonce: resbsEnhancedSettings.nonceLoadTab
        })
        .done(function(response) {
            var loadingTime = Date.now() - loadingStartTime;
            var minLoadingTime = 500; // Minimum 500ms loading time
            
            if (response.success) {
                // Ensure minimum loading time for smooth UX
                setTimeout(function() {
                    $('.resbs-settings-content').html(response.data);
                }, Math.max(0, minLoadingTime - loadingTime));
            } else {
                setTimeout(function() {
                    $('.resbs-settings-content').html('<div class="notice notice-error"><p>Error loading tab content.</p></div>');
                }, Math.max(0, minLoadingTime - loadingTime));
            }
        })
        .fail(function(xhr, status, error) {
            var loadingTime = Date.now() - loadingStartTime;
            var minLoadingTime = 500;
            
            setTimeout(function() {
                $('.resbs-settings-content').html('<div class="notice notice-error"><p>AJAX Error: ' + error + '</p></div>');
            }, Math.max(0, minLoadingTime - loadingTime));
        });
    });
    
    // Enhanced Color Picker Functionality
    function updateColorHex(colorInput, hexInput) {
        var color = colorInput.val();
        hexInput.val(color.toUpperCase());
    }
    
    // Initialize color pickers
    $('input[type="color"]').each(function() {
        var $colorInput = $(this);
        var $hexInput = $colorInput.siblings('.resbs-color-hex');
        
        // Update on color input change
        $colorInput.on('input change', function() {
            updateColorHex($colorInput, $hexInput);
        });
        
        // Update on hex input change
        $hexInput.on('input', function() {
            var hex = $(this).val();
            if (/^#[0-9A-F]{6}$/i.test(hex)) {
                $colorInput.val(hex);
            }
        });
        
        // Reset button
        $colorInput.siblings('.resbs-color-reset').on('click', function() {
            var defaultColor = $(this).data('default');
            $colorInput.val(defaultColor);
            updateColorHex($colorInput, $hexInput);
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

