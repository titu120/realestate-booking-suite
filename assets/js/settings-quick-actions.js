/**
 * Settings Quick Actions JavaScript
 * Extracted from class-resbs-settings.php
 * 
 * @package RealEstate_Booking_Suite
 */

jQuery(document).ready(function($) {
    // Get localized data
    var actionIndex = parseInt(resbs_quick_actions.actionIndex || 0);
    var labels = resbs_quick_actions.labels || {};
    
    $('#add-quick-action').click(function() {
        var newAction = `
            <div class="quick-action-item" data-index="${actionIndex}">
                <div class="resbs-form-group">
                    <label>${labels.action_title || 'Action Title'}</label>
                    <input type="text" name="resbs_quick_actions[${actionIndex}][title]" placeholder="${labels.action_title_placeholder || 'e.g., Send Message'}" />
                </div>
                
                <div class="resbs-form-group">
                    <label>${labels.icon_class || 'Icon Class'}</label>
                    <input type="text" name="resbs_quick_actions[${actionIndex}][icon]" placeholder="${labels.icon_class_placeholder || 'e.g., fas fa-envelope'}" />
                    <p class="description">${labels.icon_class_description || 'FontAwesome icon class (e.g., fas fa-envelope, fas fa-share-alt)'}</p>
                </div>
                
                <div class="resbs-form-group">
                    <label>${labels.js_action || 'JavaScript Action'}</label>
                    <input type="text" name="resbs_quick_actions[${actionIndex}][action]" placeholder="${labels.js_action_placeholder || 'e.g., openContactModal()'}" />
                    <p class="description">${labels.js_action_description || 'JavaScript function to call when clicked'}</p>
                </div>
                
                <div class="resbs-form-group">
                    <label>${labels.button_style || 'Button Style Classes'}</label>
                    <input type="text" name="resbs_quick_actions[${actionIndex}][style]" placeholder="${labels.button_style_placeholder || 'e.g., bg-gray-700 text-white hover:bg-gray-800'}" />
                    <p class="description">${labels.button_style_description || 'Tailwind CSS classes for button styling'}</p>
                </div>
                
                <div class="resbs-form-group">
                    <label>
                        <input type="checkbox" name="resbs_quick_actions[${actionIndex}][enabled]" value="1" checked />
                        ${labels.enable_action || 'Enable this action'}
                    </label>
                </div>
                
                <button type="button" class="button remove-action">${labels.remove_action || 'Remove Action'}</button>
            </div>
        `;
        $('#quick-actions-container').append(newAction);
        actionIndex++;
    });
    
    $(document).on('click', '.remove-action', function() {
        $(this).closest('.quick-action-item').remove();
    });
});

