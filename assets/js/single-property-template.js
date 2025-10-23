/**
 * Single Property Template JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

// Test function to verify JavaScript is loading
window.testJavaScript = function() {
    alert('JavaScript is working!');
    console.log('JavaScript test function called');
}

// Gallery images - Will be populated by PHP
let galleryImages = [];
let currentImageIndex = 0;

// Mobile Menu Toggle - Make it global
window.toggleMobileMenu = function() {
    const menu = document.getElementById('mobileMenu');
    if (menu) {
        menu.classList.toggle('active');
    }
}

// Tab Switching - Make it global
window.switchTab = function(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });

    // Remove active class from all buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });

    // Show selected tab content
    const targetTab = document.getElementById(tabName + '-tab');
    if (targetTab) {
        targetTab.classList.add('active');
    }

    // Add active class to clicked button
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    if (activeButton) {
        activeButton.classList.add('active');
    }

    // Initialize map if location tab is opened
    if (tabName === 'location' && !window.mapInitialized) {
        if (typeof initMap === 'function') {
            initMap();
        }
    }
}

// Filter Amenities - Make it global
window.filterAmenities = function(category) {
    const items = document.querySelectorAll('.amenity-item');
    const buttons = document.querySelectorAll('.filter-btn');

    // Update button styles
    buttons.forEach(btn => {
        btn.classList.remove('active');
    });

    if (event && event.target) {
        event.target.classList.add('active');
    }

    // Filter items
    items.forEach(item => {
        if (category === 'all' || item.dataset.category === category) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

// Image Viewer - Make them global
window.openImageViewer = function(index) {
    currentImageIndex = index;
    const viewerImage = document.getElementById('viewerImage');
    const imageViewer = document.getElementById('imageViewer');
    if (viewerImage && imageViewer && galleryImages[index]) {
        viewerImage.src = galleryImages[index];
        imageViewer.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

window.closeImageViewer = function() {
    const imageViewer = document.getElementById('imageViewer');
    if (imageViewer) {
        imageViewer.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

window.nextImage = function() {
    currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
    const viewerImage = document.getElementById('viewerImage');
    if (viewerImage && galleryImages[currentImageIndex]) {
        viewerImage.src = galleryImages[currentImageIndex];
    }
}

window.prevImage = function() {
    currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
    const viewerImage = document.getElementById('viewerImage');
    if (viewerImage && galleryImages[currentImageIndex]) {
        viewerImage.src = galleryImages[currentImageIndex];
    }
}

// Keyboard navigation for image viewer
document.addEventListener('keydown', function(e) {
    if (document.getElementById('imageViewer').classList.contains('active')) {
        if (e.key === 'ArrowRight') nextImage();
        if (e.key === 'ArrowLeft') prevImage();
        if (e.key === 'Escape') closeImageViewer();
    }
});

// Contact Modal - Make them global
window.openContactModal = function() {
    const contactModal = document.getElementById('contactModal');
    if (contactModal) {
        contactModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

window.closeContactModal = function() {
    const contactModal = document.getElementById('contactModal');
    if (contactModal) {
        contactModal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

window.submitContactForm = function(e) {
    e.preventDefault();
    alert('Thank you for your message! Our agent will contact you shortly.');
    closeContactModal();
}

// Mortgage Calculator - Make them global
window.updateDownPayment = function(value) {
    const downPaymentValue = document.getElementById('downPaymentValue');
    if (downPaymentValue) {
        downPaymentValue.textContent = value + '%';
    }
}

window.calculateMortgage = function() {
    const priceElement = document.getElementById('propertyPrice');
    const downPaymentElement = document.getElementById('downPayment');
    const interestRateElement = document.getElementById('interestRate');
    const loanTermElement = document.getElementById('loanTerm');
    const monthlyPaymentElement = document.getElementById('monthlyPayment');

    if (!priceElement || !downPaymentElement || !interestRateElement || !loanTermElement || !monthlyPaymentElement) {
        return;
    }

    const priceStr = priceElement.value.replace(/[$,]/g, '');
    const price = parseFloat(priceStr);
    const downPaymentPercent = parseFloat(downPaymentElement.value);
    const interestRate = parseFloat(interestRateElement.value);
    const loanTerm = parseFloat(loanTermElement.value);

    if (isNaN(price) || isNaN(downPaymentPercent) || isNaN(interestRate) || isNaN(loanTerm)) {
        return;
    }

    const downPayment = price * (downPaymentPercent / 100);
    const loanAmount = price - downPayment;
    const monthlyRate = (interestRate / 100) / 12;
    const numberOfPayments = loanTerm * 12;

    const monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) /
                          (Math.pow(1 + monthlyRate, numberOfPayments) - 1);

    monthlyPaymentElement.textContent = '$' + monthlyPayment.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// Search Reviews - Make them global
window.searchReviews = function() {
    const searchElement = document.getElementById('reviewSearch');
    if (!searchElement) return;
    
    const searchTerm = searchElement.value.toLowerCase();
    const reviews = document.querySelectorAll('.review-item');

    reviews.forEach(review => {
        const text = review.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            review.style.display = 'block';
        } else {
            review.style.display = 'none';
        }
    });
}

// Sort Reviews - Make it global
window.sortReviews = function() {
    const sortElement = document.getElementById('reviewSort');
    if (!sortElement) return;
    
    const sortValue = sortElement.value;
    alert('Sorting reviews by: ' + sortValue);
    // Implementation would depend on your data structure
}

// Like Review - Make it global
window.likeReview = function(button) {
    const span = button.querySelector('span');
    if (span) {
        const currentCount = parseInt(span.textContent.match(/\d+/)[0]);
        span.textContent = `Helpful (${currentCount + 1})`;
        button.classList.add('text-emerald-500');
    }
}

// Utility Functions - Make them global
window.shareProperty = function() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            text: 'Check out this amazing property!',
            url: window.location.href
        });
    } else {
        alert('Share link copied to clipboard!');
        navigator.clipboard.writeText(window.location.href);
    }
}

window.saveFavorite = function() {
    alert('Property saved to your favorites!');
}

window.printPage = function() {
    window.print();
}

window.exportPDF = function() {
    alert('PDF export will be downloaded shortly...');
    // In production, you'd implement actual PDF generation
}

window.downloadFloorPlan = function() {
    alert('Floor plan downloading...');
}

window.requestCustomPlan = function() {
    alert('Request sent! Our team will contact you shortly.');
}

// Direct booking form submission - Make it global
window.handleDirectBooking = function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(e.target);
    const bookingData = {
        name: formData.get('bookingName'),
        email: formData.get('bookingEmail'),
        phone: formData.get('bookingPhone'),
        date: formData.get('bookingDate'),
        time: formData.get('bookingTime'),
        message: formData.get('bookingMessage')
    };
    
    // Basic validation
    if (!bookingData.name || !bookingData.email || !bookingData.date || !bookingData.time) {
        alert('Please fill in all required fields.');
        return;
    }
    
    // Show success message
    alert('Property viewing scheduled successfully! We will contact you to confirm the appointment.');
    
    // Reset form
    e.target.reset();
}

window.requestVirtualTour = function() {
    alert('Virtual tour request sent! We will contact you shortly to arrange your virtual tour.');
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Single Property Template JavaScript loaded');
    
    // Test if functions are working
    console.log('switchTab function available:', typeof window.switchTab === 'function');
    console.log('All tab buttons found:', document.querySelectorAll('.tab-button').length);
    
    // Initialize mortgage calculator
    if (typeof window.calculateMortgage === 'function') {
        window.calculateMortgage();
    }
    
    // Initialize gallery images from PHP data
    if (window.galleryImagesFromPHP && window.galleryImagesFromPHP.length > 0) {
        initGalleryImages(window.galleryImagesFromPHP);
    }
    
    // Add click event listeners to tab buttons as backup
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            console.log('Tab clicked:', tabName);
            if (typeof window.switchTab === 'function') {
                window.switchTab(tabName);
            } else {
                console.error('switchTab function not found!');
            }
        });
    });
});

// Close modals when clicking outside
window.onclick = function(event) {
    const contactModal = document.getElementById('contactModal');
    const tourModal = document.getElementById('tourModal');
    if (event.target === contactModal) {
        closeContactModal();
    }
    if (event.target === tourModal) {
        closeTourModal();
    }
}

// Initialize gallery images from PHP data
function initGalleryImages(images) {
    galleryImages = images;
}

// Initialize gallery images from PHP data (called from template)
function initializeFromPHP() {
    // This will be called from the template with PHP data
    const galleryImagesFromPHP = window.galleryImagesFromPHP || [];
    if (galleryImagesFromPHP.length > 0) {
        initGalleryImages(galleryImagesFromPHP);
    }
}
