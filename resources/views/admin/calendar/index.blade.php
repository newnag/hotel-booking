@extends('layouts.admin')

@section('title', __('Booking Calendar'))

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
<style>
    .fc-event {
        cursor: pointer;
    }
    .fc-daygrid-event {
        white-space: normal !important;
    }
</style>
@endpush

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('Booking Calendar') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('staff.dashboard') }}">
                            {{ __('Dashboard') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">{{ __('Calendar') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <!-- Room Filter -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Filter by Room') }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-pills flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link active room-filter" data-room-id="all">
                                    <i class="fas fa-door-open"></i> {{ __('All Rooms') }}
                                </a>
                            </li>
                            @foreach(\App\Models\MeetingRoom::active()->get() as $room)
                                <li class="nav-item">
                                    <a href="#" class="nav-link room-filter" data-room-id="{{ $room->id }}">
                                        <i class="fas fa-door-closed"></i> {{ $room->name }}
                                        <span class="badge badge-info float-right">{{ $room->max_capacity }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Today's Bookings -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Today\'s Bookings') }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                            @forelse($bookingsToday as $room)
                                @foreach($room->bookings as $booking)
                                    <li class="item">
                                        <div class="product-info">
                                            <a href="{{ auth()->user()->isAdmin() ? route('admin.bookings.show', $booking->id) : route('staff.bookings.show', $booking->id) }}" class="product-title">
                                                {{ $booking->booking_ref }}
                                                @if($booking->status === 'confirmed')
                                                    <span class="badge badge-success float-right">{{ ucfirst($booking->status) }}</span>
                                                @else
                                                    <span class="badge badge-info float-right">{{ ucfirst($booking->status) }}</span>
                                                @endif
                                            </a>
                                            <span class="product-description">
                                                <strong>{{ $room->name }}</strong><br>
                                                {{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}<br>
                                                <i class="fas fa-user"></i> {{ $booking->user->name }}
                                            </span>
                                        </div>
                                    </li>
                                @endforeach
                            @empty
                                <li class="item">
                                    <div class="product-info text-center text-muted py-3">
                                        {{ __('No bookings today') }}
                                    </div>
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <div class="card card-primary">
                    <div class="card-body p-0">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Booking Detail Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Booking Details') }}</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="bookingModalBody">
                <!-- Content loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                <a href="#" id="viewBookingBtn" class="btn btn-primary">{{ __('View Full Details') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script>
    const calendarEventsUrl = "{{ auth()->user()->isAdmin() ? route('admin.calendar.events') : route('staff.calendar.events') }}";
    const bookingShowUrl = "{{ auth()->user()->isAdmin() ? route('admin.bookings.show', ':id') : route('staff.bookings.show', ':id') }}";
</script>
@if(app()->environment('production') || config('app.vite_enabled', true))
    @vite(['resources/js/calendar.js'])
@endif
@endpush
