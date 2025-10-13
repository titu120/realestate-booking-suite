// Single Property JavaScript Functions

// Gallery images from PHP - will be populated by PHP
let galleryImages = [];
let currentImageIndex = 0;

// Wait for window.galleryImages to be available
function initializeGalleryImages() {
    if (window.galleryImages && Array.isArray(window.galleryImages) && window.galleryImages.length > 0) {
        galleryImages = window.galleryImages;
        console.log('Gallery images initialized:', galleryImages.length);
        console.log('Gallery images:', galleryImages);
    } else {
        console.error('Gallery images not available from window.galleryImages');
        console.log('window.galleryImages:', window.galleryImages);
        console.log('No gallery images available - check PHP gallery processing');
        
        // Try to get images from gallery elements on the page as fallback
        const galleryImgs = document.querySelectorAll('.gallery-img');
        if (galleryImgs.length > 0) {
            galleryImages = Array.from(galleryImgs).map(img => img.src);
            console.log('Fallback: Found gallery images from DOM:', galleryImages);
        }
    }
}

// Debug: Log when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Initialize gallery images
    initializeGalleryImages();
    
    // Test if imageViewer modal exists
    const imageViewer = document.getElementById('imageViewer');
    if (imageViewer) {
        console.log('Image viewer modal found');
    } else {
        console.error('Image viewer modal NOT found');
    }
    
    // Test if viewerImage element exists
    const viewerImage = document.getElementById('viewerImage');
    if (viewerImage) {
        console.log('Viewer image element found');
    } else {
        console.error('Viewer image element NOT found');
    }
    
    // Test function for debugging
    window.testImageViewer = function() {
        console.log('Testing image viewer...');
        console.log('Gallery images:', galleryImages);
        console.log('Gallery images length:', galleryImages ? galleryImages.length : 0);
        
        if (galleryImages && galleryImages.length > 0) {
            console.log('Opening first image for testing...');
            openImageViewer(0);
        } else {
            console.error('No gallery images available for testing');
            alert('No gallery images available. Check console for details.');
        }
    };
});

// WORKING TAB FUNCTION - COPIED FROM YOUR WORKING HTML
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('tab-active');
        button.classList.add('text-gray-600');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Add active class to clicked button
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    activeButton.classList.add('tab-active');
    activeButton.classList.remove('text-gray-600');

    // Initialize map if location tab is opened
    if (tabName === 'location' && !window.mapInitialized) {
        initMap();
    }
}

// Mobile Menu Toggle
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}

// Filter Amenities
function filterAmenities(category) {
    const items = document.querySelectorAll('.amenity-item');
    const buttons = document.querySelectorAll('.filter-btn');
    
    // Update button styles
    buttons.forEach(btn => {
        btn.classList.remove('filter-active');
        btn.classList.add('bg-gray-100', 'text-gray-700');
    });
    
    event.target.classList.add('filter-active');
    event.target.classList.remove('bg-gray-100', 'text-gray-700');
    
    // Filter items
    items.forEach(item => {
        if (category === 'all' || item.dataset.category === category) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Image Viewer - Enhanced with fallback
function openImageViewer(index) {
    console.log('Opening image viewer for index:', index);
    console.log('Gallery images available:', galleryImages);
    console.log('Gallery images length:', galleryImages ? galleryImages.length : 0);
    
    // Try to get images from window.galleryImages if local array is empty
    if (!galleryImages || galleryImages.length === 0) {
        if (window.galleryImages && window.galleryImages.length > 0) {
            galleryImages = window.galleryImages;
            console.log('Using window.galleryImages:', galleryImages);
        } else {
            console.error('No gallery images available!');
            alert('No images available to display.');
            return;
        }
    }
    
    if (index >= galleryImages.length) {
        console.error('Image index out of range:', index, 'Max:', galleryImages.length - 1);
        return;
    }
    
    const imageUrl = galleryImages[index];
    console.log('Setting image URL:', imageUrl);
    
    currentImageIndex = index;
    document.getElementById('viewerImage').src = imageUrl;
    document.getElementById('imageViewer').classList.add('active');
    document.body.style.overflow = 'hidden';
    
    console.log('Image viewer opened successfully');
}


function closeImageViewer() {
    document.getElementById('imageViewer').classList.remove('active');
    document.body.style.overflow = 'auto';
}

function nextImage() {
    currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
    document.getElementById('viewerImage').src = galleryImages[currentImageIndex];
}

function prevImage() {
    currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
    document.getElementById('viewerImage').src = galleryImages[currentImageIndex];
}

// Keyboard navigation for image viewer
document.addEventListener('keydown', function(e) {
    if (document.getElementById('imageViewer').classList.contains('active')) {
        if (e.key === 'ArrowRight') nextImage();
        if (e.key === 'ArrowLeft') prevImage();
        if (e.key === 'Escape') closeImageViewer();
    }
});

// Contact Modal
function openContactModal() {
    document.getElementById('contactModal').classList.remove('hidden');
    document.getElementById('contactModal').classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeContactModal() {
    document.getElementById('contactModal').classList.add('hidden');
    document.getElementById('contactModal').classList.remove('flex');
    document.body.style.overflow = 'auto';
}

function submitContactForm(e) {
    e.preventDefault();
    console.log('Thank you for your message! The agent will contact you shortly.');
    closeContactModal();
}

// Mortgage Calculator
function updateDownPayment(value) {
    document.getElementById('downPaymentValue').textContent = value + '%';
}

function calculateMortgage() {
    const priceStr = document.getElementById('propertyPrice').value.replace(/[$,]/g, '');
    const price = parseFloat(priceStr);
    const downPaymentPercent = parseFloat(document.getElementById('downPayment').value);
    const interestRate = parseFloat(document.getElementById('interestRate').value);
    const loanTerm = parseFloat(document.getElementById('loanTerm').value);
    
    const downPayment = price * (downPaymentPercent / 100);
    const loanAmount = price - downPayment;
    const monthlyRate = (interestRate / 100) / 12;
    const numberOfPayments = loanTerm * 12;
    
    const monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / 
                          (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
    
    document.getElementById('monthlyPayment').textContent = 
        '$' + monthlyPayment.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Initialize map
let map;
function initMap() {
    if (window.mapInitialized) return;
    
    // Map initialization will be handled by PHP variables
    if (window.propertyLatitude && window.propertyLongitude) {
        map = L.map('map').setView([window.propertyLatitude, window.propertyLongitude], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
        
        L.marker([window.propertyLatitude, window.propertyLongitude]).addTo(map)
            .bindPopup('<b>' + window.propertyTitle + '</b><br>' + window.propertyAddress)
            .openPopup();
    }
    
    window.mapInitialized = true;
}

// Utility Functions
function shareProperty() {
    if (navigator.share) {
        navigator.share({
            title: window.propertyTitle || 'Property Details',
            text: 'Check out this amazing property!',
            url: window.location.href
        });
    } else {
        console.log('Share link copied to clipboard!');
        navigator.clipboard.writeText(window.location.href);
    }
}

function saveFavorite() {
    console.log('Property saved to your favorites!');
}

function printPage() {
    window.print();
}

function exportPDF() {
    console.log('PDF export will be downloaded shortly...');
}

function downloadFloorPlan() {
    console.log('Floor plan downloading...');
}

function requestCustomPlan() {
    console.log('Request sent! Our team will contact you shortly.');
}

function scheduleTour() {
    console.log('Tour scheduling form will open...');
}

// New functions for additional tabs
function downloadAllImages() {
    console.log('Downloading all property images...');
}

function shareMedia() {
    if (navigator.share) {
        navigator.share({
            title: (window.propertyTitle || 'Property Details') + ' - Property Media',
            text: 'Check out the media gallery for this property!',
            url: window.location.href
        });
    } else {
        console.log('Media share link copied to clipboard!');
        navigator.clipboard.writeText(window.location.href);
    }
}

function submitBookingForm(e) {
    e.preventDefault();
    console.log('Thank you for your booking request! Our agent will contact you shortly to confirm your tour.');
}

// Simple Image Popup - Fallback solution
function showImagePopup(imageSrc) {
    // Create popup overlay
    const overlay = document.createElement('div');
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    `;
    
    // Create image element
    const img = document.createElement('img');
    img.src = imageSrc;
    img.style.cssText = `
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 8px;
    `;
    
    // Create close button
    const closeBtn = document.createElement('button');
    closeBtn.innerHTML = '×';
    closeBtn.style.cssText = `
        position: absolute;
        top: 20px;
        right: 30px;
        background: none;
        border: none;
        color: white;
        font-size: 40px;
        cursor: pointer;
        z-index: 10000;
    `;
    
    overlay.appendChild(img);
    overlay.appendChild(closeBtn);
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';
    
    // Close on click
    overlay.onclick = function(e) {
        if (e.target === overlay || e.target === closeBtn) {
            document.body.removeChild(overlay);
            document.body.style.overflow = 'auto';
        }
    };
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    calculateMortgage();
    
    // Initialize tabs - ensure overview is active by default
    const overviewTab = document.getElementById('overview-tab');
    if (overviewTab) {
        overviewTab.classList.remove('hidden');
    }
    
    // Hide all other tabs
    document.querySelectorAll('.tab-content').forEach(content => {
        if (content.id !== 'overview-tab') {
            content.classList.add('hidden');
        }
    });
    
    // Add click handlers to gallery images as fallback
    document.querySelectorAll('.gallery-img').forEach((img, index) => {
        img.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Gallery image clicked, index:', index);
            console.log('Image src:', img.src);
            console.log('Gallery images available:', galleryImages);
            console.log('Window gallery images:', window.galleryImages);
            
            // Try the main image viewer first
            if (galleryImages && galleryImages.length > 0) {
                console.log('Using main image viewer');
                openImageViewer(index);
            } else if (window.galleryImages && window.galleryImages.length > 0) {
                console.log('Using window gallery images');
                galleryImages = window.galleryImages;
                openImageViewer(index);
            } else {
                console.log('Using fallback popup');
                // Fallback to simple popup
                showImagePopup(img.src);
            }
        });
    });
    
    // Debug: Log all gallery images found
    console.log('Total gallery images found:', document.querySelectorAll('.gallery-img').length);
    document.querySelectorAll('.gallery-img').forEach((img, index) => {
        console.log(`Gallery image ${index}:`, img.src);
    });
});

// Close modals when clicking outside
window.onclick = function(event) {
    const contactModal = document.getElementById('contactModal');
    if (event.target === contactModal) {
        closeContactModal();
    }
}
