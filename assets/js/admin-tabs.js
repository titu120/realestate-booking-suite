/**
 * Admin Tab Switching JavaScript - COMPREHENSIVE FIX
 * 
 * @package RealEstate_Booking_Suite
 */

(function($) {
    'use strict';

    // Global tab manager
    window.RESBS_TabManager = {
        initialized: false,
        tabButtons: null,
        tabContents: null,
        
        /**
         * Initialize the tab manager
         */
        init: function() {
            console.log('🔧 TAB MANAGER: Initializing comprehensive tab system');
            
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', this.setupTabs.bind(this));
            } else {
                this.setupTabs();
            }
        },
        
        /**
         * Setup tabs with comprehensive error handling
         */
        setupTabs: function() {
            console.log('🔧 TAB MANAGER: Setting up tabs');
            
            // Find all tab elements
            this.tabButtons = $('.resbs-tab-nav-btn');
            this.tabContents = $('.resbs-tab-content');
            
            console.log('🔧 TAB MANAGER: Found', this.tabButtons.length, 'buttons and', this.tabContents.length, 'content divs');
            
            if (this.tabButtons.length === 0) {
                console.log('🔧 TAB MANAGER: No tab buttons found, retrying in 500ms');
                setTimeout(this.setupTabs.bind(this), 500);
                return;
            }
            
            // Add comprehensive CSS
            this.addTabCSS();
            
            // Remove conflicting attributes
            this.cleanupTabButtons();
            
            // Attach event handlers
            this.attachEventHandlers();
            
            // Debug all tab content
            this.debugTabContent();
            
            // Ensure overview tab is active
            this.ensureDefaultTab();
            
            this.initialized = true;
            console.log('🔧 TAB MANAGER: Tab system initialized successfully');
        },
        
        /**
         * Add comprehensive CSS for tab display
         */
        addTabCSS: function() {
            $('<style>')
                .prop('type', 'text/css')
                .html(`
                    /* Tab Content Styling */
                    .resbs-tab-content { 
                        display: none !important; 
                        min-height: 300px;
                        background: #ffffff;
                        border: 1px solid #e1e5e9;
                        border-radius: 12px;
                        padding: 30px;
                        margin: 20px 0;
                        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
                        position: relative;
                    }
                    .resbs-tab-content.active { 
                        display: block !important; 
                        animation: fadeIn 0.3s ease-in-out;
                    }
                    
                    /* Tab Button Styling */
                    .resbs-tab-nav-btn { 
                        cursor: pointer !important; 
                        transition: all 0.3s ease;
                        position: relative;
                    }
                    .resbs-tab-nav-btn:hover { 
                        opacity: 0.8 !important; 
                        transform: translateY(-2px);
                    }
                    .resbs-tab-nav-btn.active {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
                        color: white !important;
                        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
                    }
                    
                    /* Empty tab detection */
                    .resbs-tab-content:empty::after {
                        content: "⚠️ This tab appears to be empty. Please check the content.";
                        color: #ff6b6b;
                        font-style: italic;
                        display: block;
                        text-align: center;
                        padding: 40px 20px;
                        background: #fff5f5;
                        border: 2px dashed #ff6b6b;
                        border-radius: 8px;
                        margin: 20px 0;
                    }
                    
                    /* Animation */
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    
                    /* Debug styling for empty tabs */
                    .resbs-tab-content.debug-empty {
                        background: #fff5f5 !important;
                        border: 2px dashed #ff6b6b !important;
                    }
                `)
                .appendTo('head');
            
            console.log('🔧 TAB MANAGER: CSS added to head');
        },
        
        /**
         * Clean up tab buttons to prevent conflicts
         */
        cleanupTabButtons: function() {
            this.tabButtons.each(function() {
                var $btn = $(this);
                var tabId = $btn.data('tab');
                
                // Remove onclick attributes to prevent conflicts
                if ($btn.attr('onclick')) {
                    console.log('🔧 TAB MANAGER: Removing onclick from button:', tabId);
                    $btn.removeAttr('onclick');
                }
                
                // Ensure proper data attributes
                if (!tabId) {
                    console.warn('🔧 TAB MANAGER: Button missing data-tab attribute:', $btn[0]);
                }
            });
        },
        
        /**
         * Attach comprehensive event handlers
         */
        attachEventHandlers: function() {
            var self = this;
            
            // Remove any existing handlers
            this.tabButtons.off('click.tabManager');
            
            // Add new click handlers
            this.tabButtons.on('click.tabManager', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $btn = $(this);
                var tabId = $btn.data('tab');
                
                console.log('🔧 TAB MANAGER: Tab clicked:', tabId);
                
                if (tabId) {
                    self.switchToTab(tabId);
                } else {
                    console.error('🔧 TAB MANAGER: No tab ID found for button:', $btn[0]);
                }
            });
            
            console.log('🔧 TAB MANAGER: Event handlers attached to', this.tabButtons.length, 'buttons');
        },
        
        /**
         * Debug all tab content
         */
        debugTabContent: function() {
            console.log('🔧 TAB MANAGER: Debugging all tab content...');
            
            this.tabContents.each(function() {
                var $tab = $(this);
                var tabId = $tab.attr('id');
                var content = $tab.html().trim();
                var contentLength = content.length;
                
                console.log('🔧 TAB MANAGER: Tab', tabId, '- Content length:', contentLength);
                
                if (contentLength < 100) {
                    console.warn('🔧 TAB MANAGER: Tab', tabId, 'has minimal content!');
                    $tab.addClass('debug-empty');
                } else {
                    console.log('🔧 TAB MANAGER: Tab', tabId, 'has substantial content');
                }
            });
        },
        
        /**
         * Switch to a specific tab with comprehensive error handling
         */
        switchToTab: function(tabId) {
            console.log('🔧 TAB MANAGER: Switching to tab:', tabId);
            
            if (!tabId) {
                console.error('🔧 TAB MANAGER: No tab ID provided');
                return false;
            }
            
            // Find target elements
            var $targetTab = $('#' + tabId);
            var $targetBtn = $('[data-tab="' + tabId + '"]');
            
            console.log('🔧 TAB MANAGER: Target tab found:', $targetTab.length);
            console.log('🔧 TAB MANAGER: Target button found:', $targetBtn.length);
            
            if ($targetTab.length === 0) {
                console.error('🔧 TAB MANAGER: Tab content not found:', tabId);
                console.error('🔧 TAB MANAGER: Available tab IDs:', this.tabContents.map(function() { return this.id; }).get());
                return false;
            }
            
            if ($targetBtn.length === 0) {
                console.error('🔧 TAB MANAGER: Tab button not found:', tabId);
                return false;
            }
            
            // Remove active from all elements
            this.tabButtons.removeClass('active');
            this.tabContents.removeClass('active').hide();
            
            // Show target tab
            $targetTab.addClass('active').show();
            $targetBtn.addClass('active');
            
            // Debug the switched tab
            var tabContent = $targetTab.html().trim();
            console.log('🔧 TAB MANAGER: Tab switched successfully to:', tabId);
            console.log('🔧 TAB MANAGER: Tab is now visible:', $targetTab.is(':visible'));
            console.log('🔧 TAB MANAGER: Tab content length:', tabContent.length);
            
            if (tabContent.length < 100) {
                console.warn('🔧 TAB MANAGER: Tab has minimal content:', tabId);
                $targetTab.addClass('debug-empty');
            } else {
                $targetTab.removeClass('debug-empty');
            }
            
            // Trigger custom event
            $(document).trigger('resbs:tab:switched', [tabId, $targetTab]);
            
            return true;
        },
        
        /**
         * Ensure default tab is active
         */
        ensureDefaultTab: function() {
            setTimeout(function() {
                if (!$('.resbs-tab-content.active').length) {
                    console.log('🔧 TAB MANAGER: No active tab found, showing overview');
                    window.RESBS_TabManager.switchToTab('overview');
                }
            }, 100);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        console.log('🔧 ADMIN TABS: Initializing tab functionality');
        window.RESBS_TabManager.init();
    });
    
    // Make switchToTab available globally for backward compatibility
    window.switchToTab = function(tabId) {
        if (window.RESBS_TabManager && window.RESBS_TabManager.initialized) {
            return window.RESBS_TabManager.switchToTab(tabId);
        } else {
            console.warn('🔧 FALLBACK: Tab manager not initialized, using fallback');
            return fallbackSwitchToTab(tabId);
        }
    };
    
    // Fallback function for when tab manager is not available
    function fallbackSwitchToTab(tabId) {
        console.log('🔧 FALLBACK: Using fallback tab switching for:', tabId);
        
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
            console.log('🔧 FALLBACK: Tab switched to:', tabId);
            return true;
        } else {
            console.error('🔧 FALLBACK: Tab not found:', tabId);
            return false;
        }
    }

})(jQuery);

// Additional fallback for when jQuery is not available
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 FALLBACK: Setting up vanilla JavaScript tab handlers');
    
    // Wait a bit for the main tab manager to initialize
    setTimeout(function() {
        // Check if main tab manager is working
        if (!window.RESBS_TabManager || !window.RESBS_TabManager.initialized) {
            console.log('🔧 FALLBACK: Main tab manager not working, setting up vanilla handlers');
            
            // Add vanilla JavaScript click handlers
            document.querySelectorAll('.resbs-tab-nav-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var tabId = this.getAttribute('data-tab');
                    console.log('🔧 FALLBACK: Tab clicked:', tabId);
                    
                    if (tabId) {
                        fallbackSwitchToTab(tabId);
                    }
                });
            });
        }
    }, 1000);
});

// Global fallback function
function fallbackSwitchToTab(tabId) {
    console.log('🔧 FALLBACK: Using fallback tab switching for:', tabId);
    
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
        console.log('🔧 FALLBACK: Tab switched to:', tabId);
        return true;
    } else {
        console.error('🔧 FALLBACK: Tab not found:', tabId);
        return false;
    }
}