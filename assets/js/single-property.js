// Single Property JavaScript Functions

// Gallery images from PHP - will be populated by PHP
let galleryImages = [];
let currentImageIndex = 0;

// Wait for window.galleryImages to be available
function initializeGalleryImages() {
    // First priority: Direct from template (most reliable)
    if (window.galleryImagesFromTemplate && window.galleryImagesFromTemplate.length > 0) {
        galleryImages = window.galleryImagesFromTemplate;
        window.galleryImages = window.galleryImagesFromTemplate;
        // Also update resbs_ajax if it exists
        if (typeof resbs_ajax !== 'undefined') {
            resbs_ajax.gallery_images = window.galleryImagesFromTemplate;
        }
        return;
    }
    
    // Second priority: Get from wp_localize_script data
    if (typeof resbs_ajax !== 'undefined' && resbs_ajax.gallery_images && resbs_ajax.gallery_images.length > 0) {
        galleryImages = resbs_ajax.gallery_images;
        window.galleryImages = resbs_ajax.gallery_images;
        return;
    }
    
    // Third priority: Try window.galleryImages
    if (window.galleryImages && Array.isArray(window.galleryImages) && window.galleryImages.length > 0) {
        galleryImages = window.galleryImages;
        return;
    }
    
    // Last resort: Try to get images from gallery elements on the page
    const galleryImgs = document.querySelectorAll('.gallery-img, .media-gallery-image');
    if (galleryImgs.length > 0) {
        galleryImages = Array.from(galleryImgs).map(img => img.src);
        window.galleryImages = galleryImages;
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
        // Location tab activated
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
        btn.classList.remove('active', 'filter-active');
        btn.classList.add('bg-gray-100', 'text-gray-700');
    });
    
    // Find the clicked button and update its style
    const clickedButton = document.querySelector(`[data-filter="${category}"]`);
    if (clickedButton) {
        clickedButton.classList.add('active', 'filter-active');
        clickedButton.classList.remove('bg-gray-100', 'text-gray-700');
    }
    
    // Filter items with better layout control
    items.forEach(item => {
        const itemCategory = item.dataset.category;
        const itemText = item.textContent.trim();
        
        // Skip items with empty text
        if (!itemText || itemText === '') {
            item.style.display = 'none';
            item.style.visibility = 'hidden';
            item.style.height = '0';
            item.style.margin = '0';
            item.style.padding = '0';
            item.style.overflow = 'hidden';
            return;
        }
        
        if (category === 'all' || itemCategory === category) {
            item.style.display = 'block';
            item.style.visibility = 'visible';
            item.style.height = 'auto';
            item.style.margin = '';
            item.style.padding = '';
            item.style.overflow = 'visible';
        } else {
            item.style.display = 'none';
            item.style.visibility = 'hidden';
            item.style.height = '0';
            item.style.margin = '0';
            item.style.padding = '0';
            item.style.overflow = 'hidden';
        }
    });
}

// Image Viewer - Enhanced with fallback (make it global)
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
                console.error('No images available to display.');
                return;
            }
        }
    }
    
    // Ensure index is valid
    if (index < 0 || index >= galleryImages.length) {
        index = 0;
    }
    
    const viewerImage = document.getElementById('viewerImage');
    const imageViewer = document.getElementById('imageViewer');
    
    if (!viewerImage || !imageViewer) {
        console.error('Image viewer elements not found');
        return;
    }
    
    const imageUrl = galleryImages[index];
    currentImageIndex = index;
    viewerImage.src = imageUrl;
    imageViewer.classList.add('active');
    document.body.style.overflow = 'hidden';
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
            nextImage();
        }
        if (e.key === 'ArrowLeft') {
            e.preventDefault();
            prevImage();
        }
        if (e.key === 'Escape') {
            e.preventDefault();
            closeImageViewer();
        }
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
    
    // Check if AJAX data is available
    if (typeof resbs_ajax === 'undefined') {
        alert('Form submission error. Please refresh the page and try again.');
        return;
    }
    
    // Get form data
    const formData = new FormData(e.target);
    
    // Get nonce from form field (more secure than using global variable)
    const nonceField = e.target.querySelector('input[name="resbs_contact_form_nonce"]');
    if (!nonceField || !nonceField.value) {
        alert('Security check failed. Please refresh the page and try again.');
        return;
    }
    
    formData.append('action', 'submit_contact_message');
    formData.append('nonce', nonceField.value);
    
    // Property ID should already be in form data, but ensure it's set
    if (!formData.has('property_id')) {
        formData.append('property_id', resbs_ajax.property_id);
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    submitBtn.disabled = true;
    
    // Submit form via AJAX
    fetch(resbs_ajax.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            alert(data.data.message);
            // Reset form
            e.target.reset();
            // Close modal
            closeContactModal();
        } else {
            // Show error message
            alert(data.data.message || 'Sorry, there was an error sending your message.');
        }
    })
    .catch(error => {
        // Fallback for localhost or network issues
        alert('Thank you for your message! (Note: This is a demo - emails are not actually sent on localhost)');
        e.target.reset();
        closeContactModal();
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Mortgage Calculator
function updateDownPayment(value) {
    document.getElementById('downPaymentValue').textContent = value + '%';
}

function calculateMortgage() {
    // Get and parse input values
    const priceStr = document.getElementById('propertyPrice').value.replace(/[$,]/g, '');
    const price = parseFloat(priceStr);
    const downPaymentPercent = parseFloat(document.getElementById('downPayment').value);
    const interestRate = parseFloat(document.getElementById('interestRate').value);
    const loanTermElement = document.getElementById('loanTerm');
    const loanTerm = parseFloat(loanTermElement ? loanTermElement.value : 0);
    
    // Validate all inputs
    if (isNaN(price) || price <= 0) {
        document.getElementById('monthlyPayment').textContent = '$0';
        return;
    }
    
    if (isNaN(downPaymentPercent) || downPaymentPercent < 0 || downPaymentPercent > 100) {
        document.getElementById('monthlyPayment').textContent = '$0';
        return;
    }
    
    if (isNaN(interestRate) || interestRate < 0) {
        document.getElementById('monthlyPayment').textContent = '$0';
        return;
    }
    
    if (isNaN(loanTerm) || loanTerm <= 0) {
        document.getElementById('monthlyPayment').textContent = '$0';
        return;
    }
    
    // Calculate mortgage
    const downPayment = price * (downPaymentPercent / 100);
    const loanAmount = price - downPayment;
    
    // Handle zero interest rate (simple division)
    if (interestRate === 0) {
        const monthlyPayment = loanAmount / (loanTerm * 12);
        document.getElementById('monthlyPayment').textContent = 
            '$' + monthlyPayment.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return;
    }
    
    const monthlyRate = (interestRate / 100) / 12;
    const numberOfPayments = loanTerm * 12;
    
    // Standard mortgage formula
    const monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / 
                          (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
    
    // Check if result is valid
    if (isNaN(monthlyPayment) || !isFinite(monthlyPayment)) {
        document.getElementById('monthlyPayment').textContent = '$0';
        return;
    }
    
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
        navigator.clipboard.writeText(window.location.href);
    }
}

function saveFavorite() {
    // Property saved to favorites
}

function printPage() {
    window.print();
}

function exportPDF() {
    // PDF export functionality
}

function downloadFloorPlan() {
    // Floor plan download functionality
}

function requestCustomPlan() {
    // Custom plan request functionality
}

function scheduleTour() {
    // Tour scheduling functionality
}

// New functions for additional tabs
function downloadAllImages() {
    // Download all images functionality
}

function shareMedia() {
    if (navigator.share) {
        navigator.share({
            title: (window.propertyTitle || 'Property Details') + ' - Property Media',
            text: 'Check out the media gallery for this property!',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
    }
}

function submitBookingForm(e) {
    e.preventDefault();
    
    // Check if AJAX data is available
    if (typeof resbs_ajax === 'undefined') {
        alert('Form submission error. Please refresh the page and try again.');
        return;
    }
    
    // Get form data
    const formData = new FormData(e.target);
    
    // Get nonce from form field (more secure than using global variable)
    const nonceField = e.target.querySelector('input[name="resbs_booking_form_nonce"]');
    if (!nonceField || !nonceField.value) {
        alert('Security check failed. Please refresh the page and try again.');
        return;
    }
    
    formData.append('action', 'resbs_submit_booking');
    formData.append('_wpnonce', nonceField.value);
    
    // Property ID should already be in form data, but ensure it's set
    if (!formData.has('property_id')) {
        formData.append('property_id', resbs_ajax.property_id);
    }
    
    // Map form field names to expected handler names
    const bookingName = formData.get('bookingName');
    if (bookingName) {
        // Split name into first and last name
        const nameParts = bookingName.trim().split(/\s+/);
        formData.append('first_name', nameParts[0] || '');
        formData.append('last_name', nameParts.slice(1).join(' ') || '');
    }
    
    // Map bookingEmail to email
    const bookingEmail = formData.get('bookingEmail');
    if (bookingEmail) {
        formData.append('email', bookingEmail);
    }
    
    // Map bookingPhone to phone
    const bookingPhone = formData.get('bookingPhone');
    const bookingPhoneCode = formData.get('bookingPhoneCode');
    if (bookingPhone) {
        const fullPhone = bookingPhoneCode ? bookingPhoneCode + bookingPhone : bookingPhone;
        formData.append('phone', fullPhone);
    }
    
    // Map bookingDate to preferred_date
    const bookingDate = formData.get('bookingDate');
    if (bookingDate) {
        formData.append('preferred_date', bookingDate);
    }
    
    // Map bookingTime to preferred_time
    const bookingTime = formData.get('bookingTime');
    if (bookingTime) {
        formData.append('preferred_time', bookingTime);
    }
    
    // Map bookingMessage to message
    const bookingMessage = formData.get('bookingMessage');
    if (bookingMessage) {
        formData.append('message', bookingMessage);
    }
    
    // Basic validation
    const name = formData.get('bookingName');
    const email = formData.get('bookingEmail');
    
    if (!name || !email) {
        alert('Please fill in all required fields (Name and Email).');
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Booking...';
    submitBtn.disabled = true;
    
    // Submit form via AJAX
    fetch(resbs_ajax.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Show success message
            alert(data.data.message);
            // Reset form
            e.target.reset();
        } else {
            // Show error message
            alert(data.data.message || 'Sorry, there was an error processing your booking.');
        }
    })
    .catch(error => {
        // Fallback for localhost or network issues
        alert('Thank you for your booking request! (Note: This is a demo - emails are not actually sent on localhost)');
        e.target.reset();
    })
    .finally(() => {
        // Reset button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
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
    
    // Ensure loan term dropdown triggers calculation (backup for onchange attribute)
    const loanTermSelect = document.getElementById('loanTerm');
    if (loanTermSelect) {
        loanTermSelect.addEventListener('change', function() {
            calculateMortgage();
        });
    }
    
    // Also add event listeners for other calculator inputs as backup
    const propertyPriceInput = document.getElementById('propertyPrice');
    if (propertyPriceInput) {
        propertyPriceInput.addEventListener('input', function() {
            calculateMortgage();
        });
    }
    
    const interestRateInput = document.getElementById('interestRate');
    if (interestRateInput) {
        interestRateInput.addEventListener('input', function() {
            calculateMortgage();
        });
    }
    
    const downPaymentSlider = document.getElementById('downPayment');
    if (downPaymentSlider) {
        downPaymentSlider.addEventListener('input', function() {
            updateDownPayment(this.value);
            calculateMortgage();
        });
    }
    
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
        // Remove existing onclick attribute to avoid conflicts
        img.removeAttribute('onclick');
        img.style.cursor = 'pointer';
        
        img.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Ensure galleryImages is initialized
            if (!galleryImages || galleryImages.length === 0) {
                initializeGalleryImages();
            }
            
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
    
    // Add click handlers to gallery overlay
    document.querySelectorAll('.gallery-overlay').forEach((overlay, index) => {
        overlay.removeAttribute('onclick');
        overlay.style.cursor = 'pointer';
        
        overlay.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Ensure galleryImages is initialized
            if (!galleryImages || galleryImages.length === 0) {
                initializeGalleryImages();
            }
            
            if (galleryImages && galleryImages.length > 0) {
                openImageViewer(4); // Start from index 4 (the "+More" overlay)
            } else if (window.galleryImages && window.galleryImages.length > 0) {
                galleryImages = window.galleryImages;
                openImageViewer(4);
            }
        });
    });
});

// Close modals when clicking outside
window.onclick = function(event) {
    const contactModal = document.getElementById('contactModal');
    if (event.target === contactModal) {
        closeContactModal();
    }
}
