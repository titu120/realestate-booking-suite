/**
 * Admin Contact Messages JavaScript
 * Extracted from inline script in class-resbs-admin-contact-messages.php
 * 
 * @package RealEstate_Booking_Suite
 */

(function() {
    'use strict';
    
    function updateStatus(id, status, nonce) {
        if (status && confirm(resbs_contact_admin.update_confirm || 'Are you sure you want to update the status?')) {
            const url = new URL(window.location.href);
            url.searchParams.set('action', 'update_status');
            url.searchParams.set('id', id);
            url.searchParams.set('status', status);
            url.searchParams.set('_wpnonce', nonce);
            window.location.href = url.toString();
        }
    }
    
    function filterByStatus() {
        const status = document.getElementById('status-filter').value;
        const url = new URL(window.location.href);
        if (status) {
            url.searchParams.set('status', status);
        } else {
            url.searchParams.delete('status');
        }
        window.location.href = url.toString();
    }
    
    function showFullMessage(id, message) {
        document.getElementById('fullMessageContent').textContent = message;
        document.getElementById('fullMessageModal').classList.remove('hidden');
        document.getElementById('fullMessageModal').classList.add('flex');
    }
    
    function closeFullMessageModal() {
        document.getElementById('fullMessageModal').classList.add('hidden');
        document.getElementById('fullMessageModal').classList.remove('flex');
    }
    
    // Make functions globally available
    window.resbsUpdateStatus = updateStatus;
    window.resbsFilterByStatus = filterByStatus;
    window.resbsShowFullMessage = showFullMessage;
    window.resbsCloseFullMessageModal = closeFullMessageModal;
})();

