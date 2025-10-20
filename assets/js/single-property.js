// Single Property JavaScript Functions

// Gallery images from PHP - will be populated by PHP
let galleryImages = [];
let currentImageIndex = 0;

// Wait for window.galleryImages to be available
function initializeGalleryImages() {
    if (window.galleryImages && Array.isArray(window.galleryImages) && window.galleryImages.length > 0) {
        galleryImages = window.galleryImages;
    } else {
        // Try to get images from gallery elements on the page as fallback
        const galleryImgs = document.querySelectorAll('.gallery-img');
        if (galleryImgs.length > 0) {
            galleryImages = Array.from(galleryImgs).map(img => img.src);
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize gallery images
    initializeGalleryImages();
});

// WORKING TAB FUNCTION - COPIED FROM YOUR WORKING HTML
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tw-tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all buttons
    document.querySelectorAll('.tw-tab-button').forEach(button => {
        button.classList.remove('tw-tab-active');
        button.classList.add('text-gray-600');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-tab').classList.remove('hidden');
    
    // Add active class to clicked button
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    activeButton.classList.add('tw-tab-active');
    activeButton.classList.remove('text-gray-600');

    // Location tab functionality (no map initialization needed)
    if (tabName === 'location') {
        console.log('Location tab clicked');
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
    const sections = document.querySelectorAll('[data-category]');
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
    
    // Filter section titles
    sections.forEach(section => {
        if (category === 'all' || section.dataset.category === category) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
}

// Image Viewer - Enhanced with fallback
function openImageViewer(index) {
    // Try to get images from window.galleryImages if local array is empty
    if (!galleryImages || galleryImages.length === 0) {
        if (window.galleryImages && window.galleryImages.length > 0) {
            galleryImages = window.galleryImages;
        } else {
            alert('No images available to display.');
            return;
        }
    }
    
    if (index >= galleryImages.length) {
        return;
    }
    
    const imageUrl = galleryImages[index];
    currentImageIndex = index;
    document.getElementById('viewerImage').src = imageUrl;
    document.getElementById('imageViewer').classList.add('active');
    document.body.style.overflow = 'hidden';
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

// Map functionality removed - using iframe maps only

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
            
            // Try the main image viewer first
            if (galleryImages && galleryImages.length > 0) {
                openImageViewer(index);
            } else if (window.galleryImages && window.galleryImages.length > 0) {
                galleryImages = window.galleryImages;
                openImageViewer(index);
            } else {
                // Fallback to simple popup
                showImagePopup(img.src);
            }
        });
    });
});

// Mortgage Calculator Function
function calculateMortgagePayment() {
    console.log('Button clicked!');

    // Get values
    var price = document.getElementById('propertyPrice').value;
    var downPayment = document.getElementById('downPayment').value;
    var interestRate = document.getElementById('interestRate').value;
    var loanTerm = document.getElementById('loanTerm').value;

    console.log('Input values:', {price: price, downPayment: downPayment, interestRate: interestRate, loanTerm: loanTerm});

    // Convert to numbers
    price = parseFloat(price) || 0;
    downPayment = parseFloat(downPayment) || 0;
    interestRate = parseFloat(interestRate) || 0;
    loanTerm = parseFloat(loanTerm) || 30;

    // Handle very low property prices
    if (price < 1000) {
        console.log('Property price too low for realistic calculation:', price);
        document.getElementById('monthlyPayment').textContent = '$0';
        return;
    }

    // Calculate
    var downPaymentAmount = (price * downPayment) / 100;
    var loanAmount = price - downPaymentAmount;
    var monthlyRate = interestRate / 100 / 12;
    var numberOfPayments = loanTerm * 12;

    var monthlyPayment = 0;
    if (monthlyRate > 0 && loanAmount > 0) {
        monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
    } else if (loanAmount > 0) {
        monthlyPayment = loanAmount / numberOfPayments;
    }

    console.log('Calculation result:', monthlyPayment);

    // Display result
    document.getElementById('monthlyPayment').textContent = '$' + Math.round(monthlyPayment).toLocaleString();
    console.log('Display updated to:', document.getElementById('monthlyPayment').textContent);
}

// Close modals when clicking outside
window.onclick = function(event) {
    const contactModal = document.getElementById('contactModal');
    if (event.target === contactModal) {
        closeContactModal();
    }
}
