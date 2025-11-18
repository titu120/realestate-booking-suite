/**
 * Favorites Toast Notification JavaScript
 * Extracted from class-resbs-favorites.php
 * 
 * @package RealEstate_Booking_Suite
 */

// Toast notification function
function showToastNotification(message, type) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.resbs-toast-notification');
    existingToasts.forEach(toast => toast.remove());
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'resbs-toast-notification resbs-toast-' + (type || 'success');
    const messageSpan = document.createElement('span');
    messageSpan.className = 'resbs-toast-message';
    messageSpan.textContent = message;
    const closeBtn = document.createElement('button');
    closeBtn.className = 'resbs-toast-close';
    closeBtn.textContent = '×';
    toast.appendChild(messageSpan);
    toast.appendChild(closeBtn);
    
    // Add to body
    document.body.appendChild(toast);
    
    // Show toast with animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Auto-hide after 3 seconds
    const autoHide = setTimeout(() => {
        hideToast(toast);
    }, 3000);
    
    // Close button click
    toast.querySelector('.resbs-toast-close').addEventListener('click', () => {
        clearTimeout(autoHide);
        hideToast(toast);
    });
}

function hideToast(toast) {
    toast.classList.remove('show');
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 300);
}

