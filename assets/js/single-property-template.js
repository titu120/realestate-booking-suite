/**
 * Single Property Template JavaScript
 * 
 * @package RealEstate_Booking_Suite
 */

// Test function to verify JavaScript is loading
window.testJavaScript = function() {
    alert('JavaScript is working!');
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
    // Ensure galleryImages is populated - check all sources
    if (!galleryImages || galleryImages.length === 0) {
        if (window.galleryImagesFromTemplate && window.galleryImagesFromTemplate.length > 0) {
            galleryImages = window.galleryImagesFromTemplate;
            window.galleryImages = window.galleryImagesFromTemplate;
        } else if (typeof resbs_ajax !== 'undefined' && resbs_ajax.gallery_images && resbs_ajax.gallery_images.length > 0) {
            galleryImages = resbs_ajax.gallery_images;
            window.galleryImages = resbs_ajax.gallery_images;
        } else if (window.galleryImages && window.galleryImages.length > 0) {
            galleryImages = window.galleryImages;
        } else {
            // Fallback: Extract from DOM
            const galleryImgs = document.querySelectorAll('.gallery-img, .media-gallery-image');
            if (galleryImgs.length > 0) {
                galleryImages = Array.from(galleryImgs).map(img => img.src);
                window.galleryImages = galleryImages;
            } else {
                console.error('No gallery images found');
                return;
            }
        }
    }
    
    // Ensure index is valid
    if (index < 0 || index >= galleryImages.length) {
        index = 0;
    }
    
    currentImageIndex = index;
    const viewerImage = document.getElementById('viewerImage');
    const imageViewer = document.getElementById('imageViewer');
    
    if (!viewerImage || !imageViewer) {
        console.error('Image viewer elements not found');
        return;
    }
    
    if (galleryImages[index]) {
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
    // Ensure galleryImages is populated
    if (!galleryImages || galleryImages.length === 0) {
        if (typeof resbs_ajax !== 'undefined' && resbs_ajax.gallery_images && resbs_ajax.gallery_images.length > 0) {
            galleryImages = resbs_ajax.gallery_images;
        } else if (window.galleryImages && window.galleryImages.length > 0) {
            galleryImages = window.galleryImages;
        } else {
            return;
        }
    }
    
    currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
    const viewerImage = document.getElementById('viewerImage');
    if (viewerImage && galleryImages[currentImageIndex]) {
        viewerImage.src = galleryImages[currentImageIndex];
    }
}

window.prevImage = function() {
    // Ensure galleryImages is populated
    if (!galleryImages || galleryImages.length === 0) {
        if (typeof resbs_ajax !== 'undefined' && resbs_ajax.gallery_images && resbs_ajax.gallery_images.length > 0) {
            galleryImages = resbs_ajax.gallery_images;
        } else if (window.galleryImages && window.galleryImages.length > 0) {
            galleryImages = window.galleryImages;
        } else {
            return;
        }
    }
    
    currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
    const viewerImage = document.getElementById('viewerImage');
    if (viewerImage && galleryImages[currentImageIndex]) {
        viewerImage.src = galleryImages[currentImageIndex];
    }
}

// Keyboard navigation for image viewer
document.addEventListener('keydown', function(e) {
    const imageViewer = document.getElementById('imageViewer');
    if (imageViewer && imageViewer.classList.contains('active')) {
        if (e.key === 'ArrowRight') {
            e.preventDefault();
            if (typeof window.nextImage === 'function') {
                window.nextImage();
            }
        }
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            if (typeof window.prevImage === 'function') {
                window.prevImage();
            }
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            if (typeof window.closeImageViewer === 'function') {
                window.closeImageViewer();
            }
        }
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
    // Initialize mortgage calculator
    if (typeof window.calculateMortgage === 'function') {
        window.calculateMortgage();
    }
    
    // Initialize gallery images from PHP data - Check multiple sources (priority order)
    if (window.galleryImagesFromTemplate && window.galleryImagesFromTemplate.length > 0) {
        // First priority: Direct from template (most reliable)
        galleryImages = window.galleryImagesFromTemplate;
        window.galleryImages = window.galleryImagesFromTemplate;
        // Also update resbs_ajax if it exists
        if (typeof resbs_ajax !== 'undefined') {
            resbs_ajax.gallery_images = window.galleryImagesFromTemplate;
        }
    } else if (typeof resbs_ajax !== 'undefined' && resbs_ajax.gallery_images && resbs_ajax.gallery_images.length > 0) {
        // Second priority: Get from wp_localize_script data
        galleryImages = resbs_ajax.gallery_images;
        window.galleryImages = resbs_ajax.gallery_images;
    } else if (window.galleryImagesFromPHP && window.galleryImagesFromPHP.length > 0) {
        // Third priority: Fallback to window.galleryImagesFromPHP
        initGalleryImages(window.galleryImagesFromPHP);
    } else {
        // Last resort: Extract images from DOM
        const galleryImgs = document.querySelectorAll('.gallery-img, .media-gallery-image');
        if (galleryImgs.length > 0) {
            galleryImages = Array.from(galleryImgs).map(img => img.src);
            window.galleryImages = galleryImages;
        }
    }
    
    // Debug: Log gallery images count (remove in production)
    if (galleryImages && galleryImages.length > 0) {
        console.log('Gallery images initialized:', galleryImages.length, 'images');
    } else {
        console.warn('No gallery images found!');
    }
    
    // Add click event listeners to gallery images
    document.querySelectorAll('.gallery-img').forEach((img, index) => {
        // Remove existing onclick and add event listener
        img.removeAttribute('onclick');
        img.style.cursor = 'pointer';
        img.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (galleryImages && galleryImages.length > 0) {
                openImageViewer(index);
            } else if (window.galleryImages && window.galleryImages.length > 0) {
                galleryImages = window.galleryImages;
                openImageViewer(index);
            } else {
                // Fallback: use image src directly
                const allImgs = Array.from(document.querySelectorAll('.gallery-img')).map(i => i.src);
                if (allImgs.length > 0) {
                    galleryImages = allImgs;
                    window.galleryImages = allImgs;
                    openImageViewer(index);
                }
            }
        });
    });
    
    // Add click event listeners to gallery overlay
    document.querySelectorAll('.gallery-overlay').forEach((overlay, index) => {
        overlay.removeAttribute('onclick');
        overlay.style.cursor = 'pointer';
        overlay.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (galleryImages && galleryImages.length > 0) {
                openImageViewer(4); // Start from index 4 (the "+More" overlay)
            } else if (window.galleryImages && window.galleryImages.length > 0) {
                galleryImages = window.galleryImages;
                openImageViewer(4);
            }
        });
    });
    
    // Add click event listeners to tab buttons as backup
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            if (typeof window.switchTab === 'function') {
                window.switchTab(tabName);
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
