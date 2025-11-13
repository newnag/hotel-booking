@props(['booking'])

{{-- This component will be used by FullCalendar to render booking events --}}
<div class="fc-event-content" 
     data-booking-id="{{ $booking->id }}"
     data-booking-ref="{{ $booking->booking_ref }}"
     data-room-name="{{ $booking->room->name }}"
     data-user-name="{{ $booking->user->name }}"
     data-start="{{ $booking->start_datetime->toIso8601String() }}"
     data-end="{{ $booking->end_datetime->toIso8601String() }}"
     data-status="{{ $booking->status }}">
    
    <div class="fc-event-title">
        {{ $booking->room->name }}
    </div>
    <div class="fc-event-time">
        {{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}
    </div>
    <div class="fc-event-attendees">
        <i class="fas fa-users"></i> {{ $booking->attendee_count }}
    </div>
</div>
