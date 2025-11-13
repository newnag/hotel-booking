@props(['booking'])

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>
            <strong>{{ __('Booking Ref') }}:</strong> {{ $booking->booking_ref }}
        </span>
        
        @if($booking->status === 'confirmed')
            <span class="badge badge-success">{{ __('Confirmed') }}</span>
        @elseif($booking->status === 'cancelled')
            <span class="badge badge-danger">{{ __('Cancelled') }}</span>
        @else
            <span class="badge badge-secondary">{{ __('Completed') }}</span>
        @endif
    </div>
    
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p class="mb-2">
                    <i class="fas fa-door-open text-primary"></i>
                    <strong>{{ __('Room') }}:</strong> {{ $booking->room->name }}
                </p>
                <p class="mb-2">
                    <i class="fas fa-user text-primary"></i>
                    <strong>{{ __('Guest') }}:</strong> {{ $booking->user->name }}
                </p>
                <p class="mb-2">
                    <i class="fas fa-users text-primary"></i>
                    <strong>{{ __('Attendees') }}:</strong> {{ $booking->attendee_count }}
                </p>
            </div>
            
            <div class="col-md-6">
                <p class="mb-2">
                    <i class="fas fa-calendar-alt text-primary"></i>
                    <strong>{{ __('Start') }}:</strong> 
                    {{ $booking->start_datetime->format('d M Y H:i') }}
                </p>
                <p class="mb-2">
                    <i class="fas fa-calendar-check text-primary"></i>
                    <strong>{{ __('End') }}:</strong> 
                    {{ $booking->end_datetime->format('d M Y H:i') }}
                </p>
                @if($booking->decoration_theme)
                    <p class="mb-2">
                        <i class="fas fa-palette text-primary"></i>
                        <strong>{{ __('Theme') }}:</strong> {{ $booking->decoration_theme }}
                    </p>
                @endif
            </div>
        </div>
        
        @if($booking->notes)
            <div class="mt-2">
                <p class="mb-1"><strong>{{ __('Notes') }}:</strong></p>
                <p class="text-muted">{{ $booking->notes }}</p>
            </div>
        @endif
    </div>
    
    <div class="card-footer">
        {{ $slot }}
    </div>
</div>
