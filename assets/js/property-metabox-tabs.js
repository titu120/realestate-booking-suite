/**
 * Property Metabox Tab Switching
 * Extracted from class-resbs-property-metabox.php
 * 
 * @package RealEstate_Booking_Suite
 */

// Tab switching is now handled by admin-tabs.js
// This function is kept for backward compatibility with onclick handlers
function switchTab(tabId) {
    console.log("Inline switchTab called for:", tabId);
    
    // Try to use the main tab handler first
    if (window.switchToTab) {
        var result = window.switchToTab(tabId);
        if (result) {
            console.log("Tab switched successfully via main handler");
            return;
        }
    }
    
    // Fallback: Direct tab switching
    console.log("Using fallback tab switching for:", tabId);
    
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
        targetTab.style.display = 'block';
        targetBtn.classList.add('active');
        console.log("Fallback tab switched to:", tabId);
        
        // Debug content
        var content = targetTab.innerHTML.trim();
        console.log("Tab content length:", content.length);
        if (content.length < 100) {
            console.warn("Tab appears to have minimal content:", tabId);
        }
    } else {
        console.error("Fallback: Tab not found:", tabId);
        console.error("Available tab IDs:", Array.from(document.querySelectorAll('.resbs-tab-content')).map(function(el) { return el.id; }));
    }
}

