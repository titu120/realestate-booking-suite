/**
 * Property Metabox Tab Switching
 * Extracted from class-resbs-property-metabox.php
 * 
 * @package RealEstate_Booking_Suite
 */

// Tab switching is now handled by admin-tabs.js
// This function is kept for backward compatibility with onclick handlers
function switchTab(tabId) {
    // Try to use the main tab handler first
    if (window.switchToTab) {
        var result = window.switchToTab(tabId);
        if (result) {
            return;
        }
    }
    
    // Fallback: Direct tab switching
    
    // Remove active from all buttons
    document.querySelectorAll('.resbs-tab-nav-btn').forEach(function(btn) {
        btn.classList.remove('active');
    });
    
    // Hide all tab content
    document.querySelectorAll('.resbs-tab-content').forEach(function(content) {
        content.classList.remove('active');
        content.style.display = 'none';
    });
    
    // Show selected tab
    var targetTab = document.getElementById(tabId);
    var targetBtn = document.querySelector('[data-tab="' + tabId + '"]');
    
    if (targetTab && targetBtn) {
        targetTab.classList.add('active');
        // Force visibility with multiple methods
        targetTab.style.setProperty('display', 'block', 'important');
        targetTab.style.setProperty('visibility', 'visible', 'important');
        targetTab.style.setProperty('opacity', '1', 'important');
        targetTab.style.setProperty('height', 'auto', 'important');
        targetTab.style.setProperty('min-height', '200px', 'important');
        targetBtn.classList.add('active');
        
        // Update hidden input field for form submission
        var hiddenInput = document.getElementById('resbs_active_tab');
        if (hiddenInput) {
            hiddenInput.value = tabId;
        }
        
        // Update localStorage
        if (typeof localStorage !== 'undefined') {
            localStorage.setItem('resbs_active_tab', tabId);
        }
        
        // Double-check visibility after a short delay
        setTimeout(function() {
            var computedDisplay = window.getComputedStyle(targetTab).display;
            var computedVisibility = window.getComputedStyle(targetTab).visibility;
            if (computedDisplay === 'none' || computedVisibility === 'hidden') {
                targetTab.style.setProperty('display', 'block', 'important');
                targetTab.style.setProperty('visibility', 'visible', 'important');
            }
        }, 100);
    }
}

