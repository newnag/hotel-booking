/**
 * Calendar.js - FullCalendar integration for meeting room bookings
 */

document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    if (!calendarEl) return;

    let currentRoomId = 'all';

    // Initialize FullCalendar
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        timeZone: 'Asia/Bangkok',
        locale: 'th',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        editable: false,
        selectable: false,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        events: function(info, successCallback, failureCallback) {
            // Fetch events from server
            const params = new URLSearchParams({
                start: info.startStr,
                end: info.endStr
            });

            if (currentRoomId !== 'all') {
                params.append('room_id', currentRoomId);
            }

            fetch(`${calendarEventsUrl}?${params.toString()}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error fetching calendar events:', error);
                    failureCallback(error);
                });
        },
        eventClick: function(info) {
            showBookingModal(info.event);
        },
        eventContent: function(arg) {
            return {
                html: `<div class="fc-event-main-frame">
                    <div class="fc-event-time">${arg.timeText}</div>
                    <div class="fc-event-title-container">
                        <div class="fc-event-title fc-sticky">${arg.event.title}</div>
                    </div>
                </div>`
            };
        },
        loading: function(isLoading) {
            if (isLoading) {
                // Show loading indicator
                calendarEl.style.opacity = '0.5';
            } else {
                calendarEl.style.opacity = '1';
            }
        }
    });

    calendar.render();

    // Room filter functionality
    const roomFilters = document.querySelectorAll('.room-filter');
    roomFilters.forEach(filter => {
        filter.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Update active state
            roomFilters.forEach(f => f.classList.remove('active'));
            this.classList.add('active');
            
            // Update current room filter
            currentRoomId = this.dataset.roomId;
            
            // Refetch events
            calendar.refetchEvents();
        });
    });

    // Show booking details in modal
    function showBookingModal(event) {
        const modal = document.getElementById('bookingModal');
        const modalBody = document.getElementById('bookingModalBody');
        const viewBtn = document.getElementById('viewBookingBtn');
        
        if (!modal || !modalBody || !viewBtn) return;

        const props = event.extendedProps;
        
        // Build modal content
        const content = `
            <div class="booking-details">
                <dl class="row">
                    <dt class="col-sm-4">Booking Reference:</dt>
                    <dd class="col-sm-8">${props.booking_ref}</dd>
                    
                    <dt class="col-sm-4">Room:</dt>
                    <dd class="col-sm-8">${props.room_name}</dd>
                    
                    <dt class="col-sm-4">Guest:</dt>
                    <dd class="col-sm-8">${props.guest_name}</dd>
                    
                    <dt class="col-sm-4">Date & Time:</dt>
                    <dd class="col-sm-8">
                        ${formatDateTimeFromString(props.start_datetime)} - ${formatTimeFromString(props.end_datetime)}
                    </dd>
                    
                    <dt class="col-sm-4">Attendees:</dt>
                    <dd class="col-sm-8">${props.attendee_count} people</dd>
                    
                    ${props.decoration_theme ? `
                    <dt class="col-sm-4">Theme:</dt>
                    <dd class="col-sm-8">${props.decoration_theme}</dd>
                    ` : ''}
                    
                    <dt class="col-sm-4">Status:</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-${getStatusBadgeClass(props.status)}">
                            ${capitalizeFirst(props.status)}
                        </span>
                    </dd>
                    
                    ${props.notes ? `
                    <dt class="col-sm-4">Notes:</dt>
                    <dd class="col-sm-8">${props.notes}</dd>
                    ` : ''}
                </dl>
            </div>
        `;
        
        modalBody.innerHTML = content;
        viewBtn.href = bookingShowUrl.replace(':id', event.id);
        
        // Show modal (Bootstrap 4)
        $(modal).modal('show');
    }

    // Helper functions
    function formatDateTime(date) {
        return new Date(date).toLocaleString('th-TH', {
            timeZone: 'Asia/Bangkok',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    }

    function formatTime(date) {
        return new Date(date).toLocaleTimeString('th-TH', {
            timeZone: 'Asia/Bangkok',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    }

    function formatDateTimeFromString(dateString) {
        // Format: 'Y-m-d H:i:s' from database (already in Asia/Bangkok timezone)
        const date = new Date(dateString.replace(' ', 'T') + '+07:00');
        return date.toLocaleString('th-TH', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    }

    function formatTimeFromString(dateString) {
        // Format: 'Y-m-d H:i:s' from database (already in Asia/Bangkok timezone)
        const date = new Date(dateString.replace(' ', 'T') + '+07:00');
        return date.toLocaleTimeString('th-TH', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    }

    function getStatusBadgeClass(status) {
        const classes = {
            'confirmed': 'success',
            'cancelled': 'danger',
            'completed': 'info'
        };
        return classes[status] || 'secondary';
    }

    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
});
