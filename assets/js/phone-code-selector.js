/**
 * Phone Code Selector JavaScript
 * Extracted from inline script in single-property.php
 * 
 * @package RealEstate_Booking_Suite
 */

(function() {
    'use strict';
    
    const select = document.getElementById('bookingPhoneCode');
    const searchInput = document.getElementById('bookingPhoneCodeSearch');
    const wrapper = document.querySelector('.phone-code-wrapper');
    
    if (!select || !searchInput || !wrapper) return;
    
    // Store all original options with their data
    const allOptions = Array.from(select.options).map(opt => ({
        value: opt.value,
        text: opt.text,
        element: opt.cloneNode(true)
    }));
    
    let isSearchVisible = false;
    
    // Show search input when dropdown is clicked/focused
    function showSearch() {
        if (isSearchVisible) return;
        isSearchVisible = true;
        searchInput.style.display = 'block';
        searchInput.value = '';
        searchInput.focus();
        // Expand select to show filtered results
        select.size = Math.min(allOptions.length, 8);
        filterOptions('');
    }
    
    // Hide search input
    function hideSearch() {
        if (!isSearchVisible) return;
        isSearchVisible = false;
        searchInput.style.display = 'none';
        searchInput.value = '';
        select.size = 1;
        // Restore all options
        restoreAllOptions();
    }
    
    // Restore all original options
    function restoreAllOptions() {
        select.innerHTML = '';
        allOptions.forEach(opt => {
            select.appendChild(opt.element.cloneNode(true));
        });
    }
    
    // Filter options based on search term
    function filterOptions(searchTerm) {
        const term = searchTerm.toLowerCase().trim();
        
        select.innerHTML = '';
        
        if (term === '') {
            // Show all options
            allOptions.forEach(opt => {
                select.appendChild(opt.element.cloneNode(true));
            });
            select.size = Math.min(allOptions.length, 8);
        } else {
            // Filter options
            const filtered = allOptions.filter(opt => {
                return opt.text.toLowerCase().includes(term) || 
                       opt.value.toLowerCase().includes(term);
            });
            
            if (filtered.length === 0) {
                const noResults = document.createElement('option');
                noResults.value = '';
                noResults.textContent = resbs_phone_code.no_country_found || 'No country found';
                noResults.disabled = true;
                select.appendChild(noResults);
                select.size = 1;
            } else {
                filtered.forEach(opt => {
                    select.appendChild(opt.element.cloneNode(true));
                });
                select.size = Math.min(filtered.length, 8);
            }
        }
    }
    
    // Show search on click/focus
    select.addEventListener('mousedown', function(e) {
        e.preventDefault();
        showSearch();
    });
    
    select.addEventListener('focus', showSearch);
    
    // Filter as user types
    searchInput.addEventListener('input', function(e) {
        filterOptions(e.target.value);
    });
    
    // Handle option selection
    select.addEventListener('change', function() {
        hideSearch();
    });
    
    // Hide search when clicking outside
    let blurTimeout;
    function handleBlur() {
        blurTimeout = setTimeout(function() {
            hideSearch();
        }, 300);
    }
    
    select.addEventListener('blur', handleBlur);
    searchInput.addEventListener('blur', handleBlur);
    
    // Cancel blur if focusing back
    select.addEventListener('focus', function() {
        clearTimeout(blurTimeout);
    });
    
    searchInput.addEventListener('focus', function() {
        clearTimeout(blurTimeout);
    });
    
    // Keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            select.focus();
            if (select.options.length > 0) {
                select.selectedIndex = 0;
            }
        } else if (e.key === 'Escape') {
            hideSearch();
            select.blur();
        }
    });
    
    // Prevent select from opening natively when search is visible
    select.addEventListener('mousedown', function(e) {
        if (isSearchVisible) {
            e.preventDefault();
        }
    });
})();

