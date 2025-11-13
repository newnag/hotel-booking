/**
 * Booking Module
 * Handles room search, availability checking, and booking interactions
 */

// Date/Time helper functions
function formatDateTime(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${year}-${month}-${day}T${hours}:${minutes}`;
}

// Auto-fill end datetime based on start datetime
document.addEventListener('DOMContentLoaded', function() {
    const startInput = document.getElementById('start_datetime');
    const endInput = document.getElementById('end_datetime');

    if (startInput && endInput) {
        startInput.addEventListener('change', function() {
            const startTime = new Date(this.value);
            
            if (!isNaN(startTime.getTime())) {
                // Default to 2 hours duration
                const endTime = new Date(startTime.getTime() + 2 * 60 * 60 * 1000);
                endInput.value = formatDateTime(endTime);
                
                // Set minimum end time to start time
                endInput.min = this.value;
            }
        });

        // Ensure end time is always after start time
        endInput.addEventListener('change', function() {
            const startTime = new Date(startInput.value);
            const endTime = new Date(this.value);
            
            if (endTime <= startTime) {
                alert('End time must be after start time');
                this.value = '';
            }
        });
    }
});

// Booking confirmation
function confirmBooking() {
    return confirm('Are you sure you want to create this booking?');
}

// Booking cancellation
function confirmCancellation() {
    return confirm('Are you sure you want to cancel this booking? This action cannot be undone.');
}

// Character counter for notes textarea
document.addEventListener('DOMContentLoaded', function() {
    const notesTextarea = document.getElementById('notes');
    
    if (notesTextarea) {
        const maxLength = notesTextarea.getAttribute('maxlength');
        const counter = document.createElement('small');
        counter.className = 'form-text text-muted float-end';
        counter.id = 'notes-counter';
        notesTextarea.parentNode.appendChild(counter);
        
        function updateCounter() {
            const remaining = maxLength - notesTextarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
            
            if (remaining < 100) {
                counter.classList.add('text-warning');
                counter.classList.remove('text-muted');
            } else {
                counter.classList.add('text-muted');
                counter.classList.remove('text-warning');
            }
        }
        
        notesTextarea.addEventListener('input', updateCounter);
        updateCounter();
    }
});

// Room search form validation
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('form[action*="booking/search"]');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const attendeeCount = document.getElementById('attendee_count');
            const startDatetime = document.getElementById('start_datetime');
            const endDatetime = document.getElementById('end_datetime');
            
            // Validate attendee count
            if (attendeeCount && parseInt(attendeeCount.value) < 1) {
                e.preventDefault();
                alert('Please enter a valid number of attendees (minimum 1)');
                attendeeCount.focus();
                return false;
            }
            
            // Validate datetime selection
            if (startDatetime && endDatetime) {
                const start = new Date(startDatetime.value);
                const end = new Date(endDatetime.value);
                const now = new Date();
                const minAdvanceHours = 0; // Can book immediately
                
                // Check if booking is in the future
                if (start <= now) {
                    e.preventDefault();
                    alert('Start time must be in the future');
                    startDatetime.focus();
                    return false;
                }
                
                // Check if end is after start
                if (end <= start) {
                    e.preventDefault();
                    alert('End time must be after start time');
                    endDatetime.focus();
                    return false;
                }
                
                // Check minimum and maximum duration
                const durationMinutes = (end - start) / (60 * 1000);
                const minDuration = 60; // 1 hour
                const maxDuration = 480; // 8 hours
                
                if (durationMinutes < minDuration) {
                    e.preventDefault();
                    alert(`Minimum booking duration is ${minDuration} minutes (${minDuration/60} hour)`);
                    endDatetime.focus();
                    return false;
                }
                
                if (durationMinutes > maxDuration) {
                    e.preventDefault();
                    alert(`Maximum booking duration is ${maxDuration} minutes (${maxDuration/60} hours)`);
                    endDatetime.focus();
                    return false;
                }
            }
            
            return true;
        });
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        confirmBooking,
        confirmCancellation,
        formatDateTime
    };
}
