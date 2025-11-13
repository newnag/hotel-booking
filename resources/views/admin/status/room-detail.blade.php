@extends('layouts.admin')

@section('title', $room->name . ' - ' . __('Room Details'))

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $room->name }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('staff.dashboard') }}">
                            {{ __('Dashboard') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.status.overview') }}">{{ __('Status') }}</a></li>
                    <li class="breadcrumb-item active">{{ $room->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Room Info -->
        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-body box-profile">
                        <h3 class="profile-username text-center">{{ $room->name }}</h3>
                        <p class="text-muted text-center">{{ $room->location }}</p>

                        <ul class="list-group list-group-unbordered mb-3">
                            <li class="list-group-item">
                                <b>{{ __('Max Capacity') }}</b>
                                <a class="float-right">{{ $room->max_capacity }} {{ __('people') }}</a>
                            </li>
                            <li class="list-group-item">
                                <b>{{ __('Status') }}</b>
                                <a class="float-right">
                                    @if($room->is_active)
                                        <span class="badge badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ __('Inactive') }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="list-group-item">
                                <b>{{ __('Today\'s Bookings') }}</b>
                                <a class="float-right">{{ $todayBookings->count() }}</a>
                            </li>
                        </ul>

                        @if($room->description)
                            <p class="text-muted">
                                <strong>{{ __('Description') }}:</strong><br>
                                {{ $room->description }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Today's Schedule -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Today\'s Schedule') }}</h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">{{ now()->format('l, F d, Y') }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($todayBookings->isEmpty())
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                {{ __('No bookings scheduled for today') }}
                            </div>
                        @else
                            <div class="timeline">
                                @foreach($todayBookings as $booking)
                                    <div>
                                        <i class="fas fa-{{ $booking->status === 'confirmed' ? 'check' : 'times' }} bg-{{ $booking->status === 'confirmed' ? 'success' : 'danger' }}"></i>
                                        <div class="timeline-item">
                                            <span class="time">
                                                <i class="fas fa-clock"></i> 
                                                {{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}
                                            </span>
                                            <h3 class="timeline-header">
                                                <a href="{{ route('admin.bookings.show', $booking->id) }}">{{ $booking->booking_ref }}</a>
                                                - {{ $booking->user->name }}
                                            </h3>
                                            <div class="timeline-body">
                                                <p class="mb-1">
                                                    <i class="fas fa-users"></i> {{ $booking->attendee_count }} {{ __('attendees') }}
                                                </p>
                                                @if($booking->decoration_theme)
                                                    <p class="mb-1">
                                                        <i class="fas fa-palette"></i> {{ $booking->decoration_theme }}
                                                    </p>
                                                @endif
                                                @if($booking->notes)
                                                    <p class="mb-0 text-muted">{{ Str::limit($booking->notes, 100) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div>
                                    <i class="fas fa-clock bg-gray"></i>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Available Time Slots -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Available Time Slots Today') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($availableSlots as $slot)
                                <div class="col-md-3 col-sm-4 col-6 mb-2">
                                    <div class="badge badge-{{ $slot['available'] ? 'success' : 'secondary' }} p-2 w-100">
                                        {{ $slot['start'] }} - {{ $slot['end'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Bookings -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Upcoming Bookings (Next 7 Days)') }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Booking Ref') }}</th>
                                    <th>{{ __('Date & Time') }}</th>
                                    <th>{{ __('Guest') }}</th>
                                    <th>{{ __('Attendees') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingBookings as $booking)
                                    <tr>
                                        <td>{{ $booking->booking_ref }}</td>
                                        <td>
                                            {{ $booking->start_datetime->format('M d, Y') }}<br>
                                            <small class="text-muted">{{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}</small>
                                        </td>
                                        <td>{{ $booking->user->name }}</td>
                                        <td>{{ $booking->attendee_count }}</td>
                                        <td>
                                            @if($booking->status === 'confirmed')
                                                <span class="badge badge-success">{{ ucfirst($booking->status) }}</span>
                                            @else
                                                <span class="badge badge-info">{{ ucfirst($booking->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            {{ __('No upcoming bookings') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking History -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Booking History (Last 30 Days)') }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Booking Ref') }}</th>
                                    <th>{{ __('Date & Time') }}</th>
                                    <th>{{ __('Guest') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bookingHistory as $booking)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.bookings.show', $booking->id) }}">
                                                {{ $booking->booking_ref }}
                                            </a>
                                        </td>
                                        <td>{{ $booking->start_datetime->format('M d, Y H:i') }}</td>
                                        <td>{{ $booking->user->name }}</td>
                                        <td>
                                            @if($booking->status === 'confirmed')
                                                <span class="badge badge-success">{{ ucfirst($booking->status) }}</span>
                                            @elseif($booking->status === 'cancelled')
                                                <span class="badge badge-danger">{{ ucfirst($booking->status) }}</span>
                                            @else
                                                <span class="badge badge-info">{{ ucfirst($booking->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            {{ __('No booking history') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($bookingHistory->hasPages())
                        <div class="card-footer clearfix">
                            {{ $bookingHistory->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
