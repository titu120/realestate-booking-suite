/**
 * Archive Dropdown Toggle Functionality
 * Extracted from simple-archive.php
 * 
 * @package RealEstate_Booking_Suite
 */

// Simple dropdown toggle functionality - only define if not already defined
if (typeof window.toggleDropdown === 'undefined') {
    window.toggleDropdown = function(dropdownId, event) {
        const dropdown = document.getElementById(dropdownId);
        if (!dropdown) return;
        
        const allDropdowns = document.querySelectorAll('.dropdown-content');
        
        // Close all other dropdowns
        allDropdowns.forEach(dd => {
            if (dd.id !== dropdownId) {
                dd.style.display = 'none';
                dd.classList.remove('active');
            }
        });
        
        // Toggle current dropdown
        if (dropdown.style.display === 'block' || dropdown.classList.contains('active')) {
            dropdown.style.display = 'none';
            dropdown.classList.remove('active');
        } else {
            dropdown.style.display = 'block';
            dropdown.classList.add('active');
        }
    };
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.dropdown-content');
    const filterButtons = document.querySelectorAll('.filter-chip');
    
    let clickedInsideDropdown = false;
    let clickedInsideFilterButton = false;
    
    // Check if click was inside a dropdown
    dropdowns.forEach(dropdown => {
        if (dropdown.contains(event.target)) {
            clickedInsideDropdown = true;
        }
    });
    
    // Check if click was on a filter button
    filterButtons.forEach(button => {
        if (button.contains(event.target)) {
            clickedInsideFilterButton = true;
        }
    });
    
    // Close all dropdowns if clicked outside
    if (!clickedInsideDropdown && !clickedInsideFilterButton) {
        dropdowns.forEach(dropdown => {
            dropdown.style.display = 'none';
        });
    }
});

