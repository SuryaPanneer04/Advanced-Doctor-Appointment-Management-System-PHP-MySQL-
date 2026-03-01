// js/script.js

// SweetAlert Wrappers for reuse
function showSuccess(title, text) {
    Swal.fire({
        icon: 'success',
        title: title,
        text: text,
        confirmButtonColor: '#4F46E5',
        timer: 3000,
        timerProgressBar: true
    });
}

function showError(title, text) {
    Swal.fire({
        icon: 'error',
        title: title,
        text: text,
        confirmButtonColor: '#EF4444'
    });
}

function confirmAction(title, text, confirmText, callback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#6B7280',
        confirmButtonText: confirmText
    }).then((result) => {
        if (result.isConfirmed) {
            callback();
        }
    });
}

// Function to handle form data via ajax using fetch API, returning promise
async function submitFormAjax(url, formData) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error submitting form:', error);
        return { status: 'error', message: 'An unexpected error occurred.' };
    }
}

// General UI interactions
document.addEventListener('DOMContentLoaded', () => {
    // Add smooth active state to nav links based on current URL
    const currentLocation = location.href;
    const menuItem = document.querySelectorAll('.nav-links a');
    const menuLength = menuItem.length;
    for (let i = 0; i < menuLength; i++) {
        if (menuItem[i].href === currentLocation) {
            menuItem[i].className += " active";
        }
    }
});
