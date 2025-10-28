// Simple Archive JavaScript functionality
// Extracted from simple-archive.php template

// Make functions globally accessible for onclick handlers
window.toggleMobileMenu = function() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('active');
};

// Dropdown Toggle
let activeDropdown = null;

window.toggleDropdown = function(dropdownId) {
    const dropdown = document.getElementById(dropdownId);
    const allDropdowns = document.querySelectorAll('.dropdown-content');
    const button = event.target.closest('.filter-chip');

    // Close all other dropdowns
    allDropdowns.forEach(dd => {
        if (dd.id !== dropdownId) {
            dd.style.display = 'none';
        }
    });

    // Toggle current dropdown
    if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
        activeDropdown = null;
    } else {
        dropdown.style.display = 'block';
        activeDropdown = dropdown;
        
        // Position dropdown relative to button
        if (button) {
            const buttonRect = button.getBoundingClientRect();
            const containerRect = document.querySelector('.dropdowns-container').getBoundingClientRect();
            
            dropdown.style.position = 'absolute';
            dropdown.style.left = (buttonRect.left - containerRect.left) + 'px';
            dropdown.style.top = '100%';
            dropdown.style.right = 'auto';
            dropdown.style.width = 'auto';
            dropdown.style.minWidth = '200px';
        }
    }
}

// Change Layout (Grid/Column)
window.changeLayout = function(layout) {
    const gridBtn = document.getElementById('gridBtn');
    const columnBtn = document.getElementById('columnBtn');
    const propertyGrid = document.getElementById('propertyGrid');

    if (layout === 'grid') {
        gridBtn.classList.add('active');
        columnBtn.classList.remove('active');
        propertyGrid.className = 'property-grid';
        propertyGrid.style.gridTemplateColumns = '1fr';

        // For larger screens, use 2 columns
        if (window.innerWidth >= 768) {
            propertyGrid.style.gridTemplateColumns = 'repeat(2, 1fr)';
        }
    } else {
        columnBtn.classList.add('active');
        gridBtn.classList.remove('active');
        propertyGrid.className = 'property-grid';
        propertyGrid.style.gridTemplateColumns = '1fr';
    }
}

// Toggle View (List/Map)
window.toggleView = function(view) {
    const viewBtns = document.querySelectorAll('.view-btn');
    viewBtns.forEach(btn => {
        btn.classList.remove('active');
    });

    event.target.closest('.view-btn').classList.add('active');

    // This would typically change the entire layout
    console.log('Switching to', view, 'view');
}

// Highlight Property on Map Click
window.highlightProperty = function(propertyId) {
    // Remove previous highlights
    document.querySelectorAll('.property-card').forEach(card => {
        card.classList.remove('ring-4', 'ring-teal-500');
    });

    document.querySelectorAll('.map-marker').forEach(marker => {
        marker.classList.remove('active');
    });

    // Add highlight to selected property
    const propertyCard = document.querySelector(`.property-card[data-property-id="${propertyId}"]`);
    const mapMarker = document.querySelector(`.map-marker[data-property-id="${propertyId}"]`);

    if (propertyCard) {
        propertyCard.classList.add('ring-4', 'ring-teal-500');
        propertyCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    if (mapMarker) {
        mapMarker.classList.add('active');
    }
}

// Initialize all functionality when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdowns = document.querySelectorAll('.dropdown-content');
        const filterButtons = document.querySelectorAll('.filter-chip');

        let clickedOnButton = false;
        filterButtons.forEach(button => {
            if (button.contains(event.target)) {
                clickedOnButton = true;
            }
        });

        if (!clickedOnButton) {
            let clickedInsideDropdown = false;
            dropdowns.forEach(dropdown => {
                if (dropdown.contains(event.target)) {
                    clickedInsideDropdown = true;
                }
            });

            if (!clickedInsideDropdown) {
                dropdowns.forEach(dropdown => {
                    dropdown.style.display = 'none';
                });
                activeDropdown = null;
            }
        }
    });

    // Property Card Hover - Highlight Map Marker
    document.querySelectorAll('.property-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            const propertyId = this.getAttribute('data-property-id');
            const marker = document.querySelector(`.map-marker[data-property-id="${propertyId}"]`);
            if (marker) {
                marker.classList.add('active');
            }
        });

        card.addEventListener('mouseleave', function() {
            const propertyId = this.getAttribute('data-property-id');
            const marker = document.querySelector(`.map-marker[data-property-id="${propertyId}"]`);
            if (marker && !this.classList.contains('ring-4')) {
                marker.classList.remove('active');
            }
        });
    });

    // Search Functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('.property-card').forEach(card => {
                const text = card.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });

            // Update results count
            const visibleCards = document.querySelectorAll('.property-card[style="display: block;"]').length;
            const allCards = document.querySelectorAll('.property-card').length;
            const resultsCount = document.getElementById('resultsCount');
            if (resultsCount) {
                resultsCount.textContent = searchTerm ? visibleCards : allCards;
            }
        });
    }

    // Heart/Favorite Toggle
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const heart = this.querySelector('i');
            heart.classList.toggle('far');
            heart.classList.toggle('fas');
            heart.classList.toggle('text-red-500');
        });
    });

    // Smooth Scroll
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Intersection Observer for Property Cards Animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    entry.target.style.transition = 'all 0.6s ease';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.property-card').forEach(card => {
        observer.observe(card);
    });

    // Initialize layout for different screen sizes
    window.addEventListener('resize', function() {
        const gridBtn = document.getElementById('gridBtn');
        if (gridBtn && gridBtn.classList.contains('active')) {
            changeLayout('grid');
        }
    });

    // Initialize layout on page load
    changeLayout('grid');
});

// Loading Animation on Page Load
window.addEventListener('load', function() {
    document.body.classList.add('page-transition');
});
