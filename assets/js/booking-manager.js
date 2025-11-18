/**
 * Booking Manager JavaScript
 * Extracted from class-resbs-booking-manager.php
 * 
 * @package RealEstate_Booking_Suite
 */

function updateBookingStatus(bookingId, status) {
    fetch(resbs_booking_manager.ajax_url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=resbs_update_booking_status&booking_id=' + encodeURIComponent(bookingId) + '&status=' + encodeURIComponent(status) + '&nonce=' + encodeURIComponent(resbs_booking_manager.update_nonce)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(resbs_booking_manager.messages.status_updated);
            location.reload();
        } else {
            alert(data.data && data.data.message ? data.data.message : resbs_booking_manager.messages.error_updating);
        }
    })
    .catch(error => {
        alert(resbs_booking_manager.messages.error_updating);
    });
}

function deleteBooking(bookingId) {
    if (confirm(resbs_booking_manager.messages.confirm_delete)) {
        fetch(resbs_booking_manager.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=resbs_delete_booking&booking_id=' + encodeURIComponent(bookingId) + '&nonce=' + encodeURIComponent(resbs_booking_manager.delete_nonce)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.data && data.data.message ? data.data.message : resbs_booking_manager.messages.error_deleting);
            }
        })
        .catch(error => {
            alert(resbs_booking_manager.messages.error_deleting);
        });
    }
}

