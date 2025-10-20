// Enhanced Single Property JavaScript Functions
// This file contains all JavaScript functionality for the single property template

// Global variables - will be populated by PHP
let galleryImages = [];
let currentImageIndex = 0;
let propertyTitle = '';
let propertyAddress = '';
let videoUrl = '';
let virtualTour = '';
let floorPlans = '';
let contactSuccessMessage = '';
let mortgageDefaultDownPayment = 20;
let mortgageDefaultInterestRate = 6.5;
let mortgageDefaultLoanTerm = 30;

// Initialize variables from WordPress localized script data
function initializeVariables() {
    // Check if resbsPropertyData is available (from wp_localize_script)
    if (typeof resbsPropertyData !== 'undefined') {
        if (resbsPropertyData.galleryImages && Array.isArray(resbsPropertyData.galleryImages)) {
            galleryImages = resbsPropertyData.galleryImages;
        }
        if (resbsPropertyData.propertyTitle) {
            propertyTitle = resbsPropertyData.propertyTitle;
        }
        if (resbsPropertyData.propertyAddress) {
            propertyAddress = resbsPropertyData.propertyAddress;
        }
        if (resbsPropertyData.videoUrl) {
            videoUrl = resbsPropertyData.videoUrl;
        }
        if (resbsPropertyData.virtualTour) {
            virtualTour = resbsPropertyData.virtualTour;
        }
        if (resbsPropertyData.floorPlans) {
            floorPlans = resbsPropertyData.floorPlans;
        }
        if (resbsPropertyData.contactSuccessMessage) {
            contactSuccessMessage = resbsPropertyData.contactSuccessMessage;
        }
        if (resbsPropertyData.mortgageDefaultDownPayment) {
            mortgageDefaultDownPayment = resbsPropertyData.mortgageDefaultDownPayment;
        }
        if (resbsPropertyData.mortgageDefaultInterestRate) {
            mortgageDefaultInterestRate = resbsPropertyData.mortgageDefaultInterestRate;
        }
        if (resbsPropertyData.mortgageDefaultLoanTerm) {
            mortgageDefaultLoanTerm = resbsPropertyData.mortgageDefaultLoanTerm;
        }
    }
    
    // Fallback to window variables for backward compatibility
    if (window.galleryImages && Array.isArray(window.galleryImages)) {
        galleryImages = window.galleryImages;
    }
    if (window.propertyTitle) {
        propertyTitle = window.propertyTitle;
    }
    if (window.propertyAddress) {
        propertyAddress = window.propertyAddress;
    }
    if (window.videoUrl) {
        videoUrl = window.videoUrl;
    }
    if (window.virtualTour) {
        virtualTour = window.virtualTour;
    }
    if (window.floorPlans) {
        floorPlans = window.floorPlans;
    }
    if (window.contactSuccessMessage) {
        contactSuccessMessage = window.contactSuccessMessage;
    }
    if (window.mortgageDefaultDownPayment) {
        mortgageDefaultDownPayment = window.mortgageDefaultDownPayment;
    }
    if (window.mortgageDefaultInterestRate) {
        mortgageDefaultInterestRate = window.mortgageDefaultInterestRate;
    }
    if (window.mortgageDefaultLoanTerm) {
        mortgageDefaultLoanTerm = window.mortgageDefaultLoanTerm;
    }
}

// Tab switching functionality
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
    const targetTab = document.getElementById(tabName + '-tab');
    if (targetTab) {
        targetTab.classList.remove('hidden');
    }
    
    // Add active class to clicked button
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    if (activeButton) {
        activeButton.classList.add('tw-tab-active');
        activeButton.classList.remove('text-gray-600');
    }

    // Location tab functionality
    if (tabName === 'location') {
        // Location tab specific functionality can be added here
    }
}

// Mobile Menu Toggle
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    if (menu) {
        menu.classList.toggle('hidden');
    }
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
    
    if (event && event.target) {
        event.target.classList.add('filter-active');
        event.target.classList.remove('bg-gray-100', 'text-gray-700');
    }
    
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

// Image Viewer Functions
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
    const viewerImage = document.getElementById('viewerImage');
    const imageViewer = document.getElementById('imageViewer');
    
    if (viewerImage && imageViewer) {
        viewerImage.src = imageUrl;
        imageViewer.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeImageViewer() {
    const imageViewer = document.getElementById('imageViewer');
    if (imageViewer) {
        imageViewer.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
}

function nextImage() {
    if (galleryImages && galleryImages.length > 0) {
        currentImageIndex = (currentImageIndex + 1) % galleryImages.length;
        const viewerImage = document.getElementById('viewerImage');
        if (viewerImage) {
            viewerImage.src = galleryImages[currentImageIndex];
        }
    }
}

function prevImage() {
    if (galleryImages && galleryImages.length > 0) {
        currentImageIndex = (currentImageIndex - 1 + galleryImages.length) % galleryImages.length;
        const viewerImage = document.getElementById('viewerImage');
        if (viewerImage) {
            viewerImage.src = galleryImages[currentImageIndex];
        }
    }
}

// Keyboard navigation for image viewer
document.addEventListener('keydown', function(e) {
    const imageViewer = document.getElementById('imageViewer');
    if (imageViewer && imageViewer.classList.contains('active')) {
        if (e.key === 'ArrowRight') nextImage();
        if (e.key === 'ArrowLeft') prevImage();
        if (e.key === 'Escape') closeImageViewer();
    }
});

// Contact Modal Functions
function openContactModal() {
    const contactModal = document.getElementById('contactModal');
    if (contactModal) {
        contactModal.classList.remove('hidden');
        contactModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

function closeContactModal() {
    const contactModal = document.getElementById('contactModal');
    if (contactModal) {
        contactModal.classList.add('hidden');
        contactModal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }
}

// Form Submission Functions
function submitContactForm(event) {
    event.preventDefault();
    const formData = new FormData(event.target);

    // Add property ID and nonce from localized data
    const propertyId = (typeof resbsPropertyData !== 'undefined' && resbsPropertyData.propertyId) ? resbsPropertyData.propertyId : document.querySelector('input[name="property_id"]')?.value;
    const nonce = (typeof resbsPropertyData !== 'undefined' && resbsPropertyData.nonce) ? resbsPropertyData.nonce : document.querySelector('input[name="nonce"]')?.value;
    
    if (propertyId) formData.append('property_id', propertyId);
    if (nonce) formData.append('nonce', nonce);
    formData.append('action', 'submit_contact_message');

    // Show loading state
    const submitButton = event.target.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    submitButton.disabled = true;

    // Submit via AJAX
    const ajaxUrl = (typeof resbsPropertyData !== 'undefined' && resbsPropertyData.ajaxUrl) ? resbsPropertyData.ajaxUrl : '/wp-admin/admin-ajax.php';
    
    fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let successMessage = data.data.message || contactSuccessMessage || 'Thank you! Your message has been sent to the agent.';
            alert(successMessage);
            closeContactModal();
            event.target.reset();
        } else {
            alert('Error: ' + (data.data || 'Failed to send message'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error: Failed to send message. Please try again.');
    })
    .finally(() => {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    });
}

// Mortgage Calculator Functions
function updateDownPayment(value) {
    const downPaymentValue = document.getElementById('downPaymentValue');
    if (downPaymentValue) {
        downPaymentValue.textContent = value + '%';
    }
}

function calculateMortgageNow() {
    try {
        // Get all input values
        const priceElement = document.getElementById('propertyPrice');
        const downPaymentElement = document.getElementById('downPayment');
        const interestRateElement = document.getElementById('interestRate');
        const loanTermElement = document.getElementById('loanTerm');
        const monthlyPaymentElement = document.getElementById('monthlyPayment');

        if (!priceElement || !downPaymentElement || !interestRateElement || !loanTermElement || !monthlyPaymentElement) {
            return;
        }

        let price = priceElement.value.replace(/[$,]/g, '');
        let downPayment = downPaymentElement.value;
        let interestRate = interestRateElement.value;
        let loanTerm = loanTermElement.value;

        // Convert to numbers
        price = parseFloat(price) || 0;
        downPayment = parseFloat(downPayment) || 0;
        interestRate = parseFloat(interestRate) || 0;
        loanTerm = parseFloat(loanTerm) || 30;

        // If property price is too low, use default
        if (price < 1000) {
            price = 500000;
            priceElement.value = '$500,000';
        }

        // Calculate mortgage
        const downPaymentAmount = (price * downPayment) / 100;
        const loanAmount = price - downPaymentAmount;
        const monthlyRate = interestRate / 100 / 12;
        const numberOfPayments = loanTerm * 12;

        let monthlyPayment = 0;

        // Check if we have valid values
        if (loanAmount <= 0) {
            monthlyPayment = 0;
        } else if (monthlyRate > 0 && loanAmount > 0) {
            monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, numberOfPayments)) / (Math.pow(1 + monthlyRate, numberOfPayments) - 1);
        } else if (loanAmount > 0) {
            monthlyPayment = loanAmount / numberOfPayments;
        }

        // Ensure minimum payment display
        if (monthlyPayment < 1 && loanAmount > 0) {
            monthlyPayment = loanAmount / numberOfPayments;
        }

        // Display result
        const formattedPayment = '$' + Math.round(monthlyPayment).toLocaleString();
        monthlyPaymentElement.textContent = formattedPayment;

        // Force update to make sure it changes
        setTimeout(function() {
            monthlyPaymentElement.textContent = formattedPayment;
        }, 50);

    } catch (error) {
        const monthlyPaymentElement = document.getElementById('monthlyPayment');
        if (monthlyPaymentElement) {
            monthlyPaymentElement.textContent = 'ERROR';
        }
    }
}

// Enhanced Media Actions Functions
function downloadAllImages() {
    if (galleryImages && galleryImages.length > 0) {
        // Create a zip file with all images
        if (typeof JSZip !== 'undefined') {
            const zip = new JSZip();
            let loadedImages = 0;

            galleryImages.forEach((imageUrl, index) => {
                fetch(imageUrl)
                    .then(response => response.blob())
                    .then(blob => {
                        const fileName = `property_image_${index + 1}.jpg`;
                        zip.file(fileName, blob);
                        loadedImages++;

                        if (loadedImages === galleryImages.length) {
                            zip.generateAsync({type: "blob"}).then(function(content) {
                                const link = document.createElement('a');
                                link.href = URL.createObjectURL(content);
                                link.download = `${propertyTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_images.zip`;
                                link.click();
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error downloading image:', error);
                        window.open(imageUrl, '_blank');
                    });
            });
        } else {
            // Fallback: open each image in new tab
            galleryImages.forEach(imageUrl => {
                window.open(imageUrl, '_blank');
            });
        }
    } else {
        alert('No images available for download.');
    }
}

function downloadVideo() {
    if (videoUrl) {
        const link = document.createElement('a');
        link.href = videoUrl;
        link.download = `${propertyTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_video.mp4`;
        link.click();
    } else {
        alert('No video available for download.');
    }
}

function downloadFloorPlan() {
    if (floorPlans) {
        const link = document.createElement('a');
        link.href = floorPlans;
        link.download = `${propertyTitle.replace(/[^a-z0-9]/gi, '_').toLowerCase()}_floor_plan.jpg`;
        link.click();
    } else {
        alert('No floor plan available for download.');
    }
}

function shareMedia() {
    const shareData = {
        title: propertyTitle,
        text: `Check out this property: ${propertyTitle}`,
        url: window.location.href
    };

    if (navigator.share) {
        navigator.share(shareData);
    } else {
        navigator.clipboard.writeText(shareData.url).then(() => {
            alert('Property link copied to clipboard!');
        });
    }
}

function shareVirtualTour() {
    if (virtualTour) {
        const shareData = {
            title: `${propertyTitle} - Virtual Tour`,
            text: `Take a virtual tour of this property: ${propertyTitle}`,
            url: virtualTour
        };

        if (navigator.share) {
            navigator.share(shareData);
        } else {
            navigator.clipboard.writeText(virtualTour).then(() => {
                alert('Virtual tour link copied to clipboard!');
            });
        }
    } else {
        alert('No virtual tour available.');
    }
}

function printMedia() {
    const printWindow = window.open('', '_blank');
    const mediaContent = `
        <html>
        <head>
            <title>${propertyTitle} - Media</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .media-section { margin-bottom: 20px; }
                .image-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
                .image-item { text-align: center; }
                .image-item img { max-width: 100%; height: auto; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>${propertyTitle}</h1>
                <p>${propertyAddress}</p>
            </div>
            ${galleryImages.map((img, index) =>
                `<div class="image-item">
                    <img src="${img}" alt="Property Image ${index + 1}">
                    <p>Image ${index + 1}</p>
                </div>`
            ).join('')}
        </body>
        </html>
    `;

    printWindow.document.write(mediaContent);
    printWindow.document.close();
    printWindow.print();
}

// Existing functions (keeping for compatibility)
function shareProperty() {
    shareMedia();
}

function printPage() {
    window.print();
}

// Notification function for user feedback
function showNotification(message, type = 'info') {
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;

    const colors = {
        success: 'bg-green-500 text-white',
        error: 'bg-red-500 text-white',
        info: 'bg-blue-500 text-white',
        warning: 'bg-yellow-500 text-black'
    };

    notification.className += ` ${colors[type] || colors.info}`;
    notification.innerHTML = `
        <div class="flex items-center space-x-2">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">×</button>
        </div>
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }, 5000);
}

// Booking Form Submission
window.submitBookingForm = function(event) {
    event.preventDefault();

    const form = document.getElementById('booking-form');
    const submitBtn = document.getElementById('booking-submit-btn');

    if (!form || !submitBtn) {
        return;
    }

    const formData = new FormData(form);

    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Submitting...';
    submitBtn.disabled = true;

    // Submit via AJAX
    const ajaxUrl = (typeof resbsPropertyData !== 'undefined' && resbsPropertyData.ajaxUrl) ? resbsPropertyData.ajaxUrl : '/wp-admin/admin-ajax.php';
    
    fetch(ajaxUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showBookingMessage('success', 'Tour scheduled successfully! We will contact you soon to confirm your appointment.');
            form.reset();
        } else {
            showBookingMessage('error', data.message || 'Something went wrong. Please try again.');
        }
    })
    .catch(error => {
        showBookingMessage('error', 'Network error. Please check your connection and try again.');
    })
    .finally(() => {
        submitBtn.innerHTML = '<i class="fas fa-calendar-check mr-2"></i>Schedule Tour';
        submitBtn.disabled = false;
    });
}

function showBookingMessage(type, message) {
    const existingMessage = document.querySelector('.booking-message');
    if (existingMessage) {
        existingMessage.remove();
    }

    const messageDiv = document.createElement('div');
    messageDiv.className = `booking-message p-4 rounded-lg mb-4 ${type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'}`;
    messageDiv.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
    `;

    const form = document.getElementById('booking-form');
    if (form && form.parentNode) {
        form.parentNode.insertBefore(messageDiv, form.nextSibling);
    }

    setTimeout(() => {
        if (messageDiv.parentNode) {
            messageDiv.remove();
        }
    }, 5000);
}

// Utility Functions
function shareProperty() {
    if (navigator.share) {
        navigator.share({
            title: propertyTitle || 'Property Details',
            text: 'Check out this amazing property!',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href);
    }
}

function saveFavorite() {
    // Property saved to favorites functionality
}

function exportPDF() {
    // PDF export functionality
}

function requestCustomPlan() {
    // Custom plan request functionality
}

function scheduleTour() {
    // Tour scheduling functionality
}

// Simple Image Popup - Fallback solution
function showImagePopup(imageSrc) {
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
    
    const img = document.createElement('img');
    img.src = imageSrc;
    img.style.cssText = `
        max-width: 90%;
        max-height: 90%;
        object-fit: contain;
        border-radius: 8px;
    `;
    
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
    
    overlay.onclick = function(e) {
        if (e.target === overlay || e.target === closeBtn) {
            document.body.removeChild(overlay);
            document.body.style.overflow = 'auto';
        }
    };
}

// Load JSZip library for zip functionality
function loadJSZip() {
    if (!window.JSZip) {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js';
        document.head.appendChild(script);
    }
}

// Initialize everything on page load
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables from PHP
    initializeVariables();
    
    // Load JSZip if needed
    loadJSZip();
    
    // Initialize gallery images
    if (!galleryImages || galleryImages.length === 0) {
        const galleryImgs = document.querySelectorAll('.gallery-img');
        if (galleryImgs.length > 0) {
            galleryImages = Array.from(galleryImgs).map(img => img.src);
        }
    }
    
    // Initialize mortgage calculator
    const downPaymentSlider = document.getElementById('downPayment');
    if (downPaymentSlider) {
        const downPaymentValue = mortgageDefaultDownPayment || 20;
        downPaymentSlider.value = downPaymentValue;
        updateDownPayment(downPaymentValue);
    }

    const interestRateInput = document.getElementById('interestRate');
    if (interestRateInput) {
        const interestRateValue = mortgageDefaultInterestRate || 6.5;
        interestRateInput.value = interestRateValue;
    }

    const loanTermSelect = document.getElementById('loanTerm');
    if (loanTermSelect) {
        const loanTermValue = mortgageDefaultLoanTerm || 30;
        loanTermSelect.value = loanTermValue;
    }

    // Calculate initial mortgage
    setTimeout(function() {
        calculateMortgageNow();
    }, 100);

    // Initialize tabs - ensure overview is active by default
    const overviewTab = document.getElementById('overview-tab');
    if (overviewTab) {
        overviewTab.classList.remove('hidden');
    }
    
    // Hide all other tabs
    document.querySelectorAll('.tw-tab-content').forEach(content => {
        if (content.id !== 'overview-tab') {
            content.classList.add('hidden');
        }
    });
    
    // Add click handlers to gallery images
    document.querySelectorAll('.gallery-img').forEach((img, index) => {
        img.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (galleryImages && galleryImages.length > 0) {
                openImageViewer(index);
            } else if (window.galleryImages && window.galleryImages.length > 0) {
                galleryImages = window.galleryImages;
                openImageViewer(index);
            } else {
                showImagePopup(img.src);
            }
        });
    });

    // Booking form event listeners
    const submitBtn = document.getElementById('booking-submit-btn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            window.submitBookingForm(e);
        });
    }
});

// Close modals when clicking outside
window.onclick = function(event) {
    const contactModal = document.getElementById('contactModal');
    if (event.target === contactModal) {
        closeContactModal();
    }
    
    const imageViewer = document.getElementById('imageViewer');
    if (event.target === imageViewer) {
        closeImageViewer();
    }
}

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
