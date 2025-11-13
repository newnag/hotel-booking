@extends('layouts.admin')

@section('title', __('Status Overview'))

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('Status Overview') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('staff.dashboard') }}">
                            {{ __('Dashboard') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">{{ __('Status') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $todayStats['total'] }}</h3>
                        <p>{{ __('Today\'s Bookings') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $todayStats['confirmed'] }}</h3>
                        <p>{{ __('Confirmed Today') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $monthStats['total'] }}</h3>
                        <p>{{ __('This Month') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $todayStats['total_attendees'] }}</h3>
                        <p>{{ __('Total Attendees Today') }}</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Status -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Room Status') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($rooms as $room)
                                @php
                                    $currentBooking = $room->bookings->first(function($b) {
                                        return $b->start_datetime <= now() && $b->end_datetime >= now();
                                    });
                                    $isOccupied = $currentBooking !== null;
                                @endphp
                                <div class="col-lg-4 col-md-6">
                                    <div class="card card-{{ $isOccupied ? 'danger' : 'success' }}">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <i class="fas fa-door-{{ $isOccupied ? 'closed' : 'open' }}"></i>
                                                {{ $room->name }}
                                            </h3>
                                            <div class="card-tools">
                                                <span class="badge badge-{{ $isOccupied ? 'danger' : 'success' }}">
                                                    {{ $isOccupied ? __('Occupied') : __('Available') }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-2">
                                                <i class="fas fa-users"></i> <strong>{{ __('Capacity') }}:</strong> {{ $room->max_capacity }}
                                            </p>
                                            <p class="mb-2">
                                                <i class="fas fa-map-marker-alt"></i> <strong>{{ __('Location') }}:</strong> {{ $room->location }}
                                            </p>
                                            
                                            @if($isOccupied)
                                                <div class="alert alert-warning mb-2">
                                                    <strong>{{ __('Current Booking') }}:</strong><br>
                                                    {{ $currentBooking->user->name }}<br>
                                                    {{ $currentBooking->start_datetime->format('H:i') }} - {{ $currentBooking->end_datetime->format('H:i') }}
                                                </div>
                                            @endif

                                            <p class="mb-0">
                                                <strong>{{ __('Today\'s Bookings') }}:</strong> {{ $room->bookings->count() }}
                                            </p>
                                        </div>
                                        <div class="card-footer">
                                            <a href="{{ route('admin.status.room', $room->id) }}" class="btn btn-sm btn-primary">
                                                <i class="fas fa-info-circle"></i> {{ __('View Details') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Today\'s Schedule') }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Time') }}</th>
                                    <th>{{ __('Room') }}</th>
                                    <th>{{ __('Guest') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todayBookings as $booking)
                                    <tr>
                                        <td>{{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}</td>
                                        <td>{{ $booking->room->name }}</td>
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
                                            {{ __('No bookings today') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Upcoming Bookings') }}</h3>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Room') }}</th>
                                    <th>{{ __('Guest') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcomingBookings as $booking)
                                    <tr>
                                        <td>{{ $booking->start_datetime->format('M d, H:i') }}</td>
                                        <td>{{ $booking->room->name }}</td>
                                        <td>{{ $booking->user->name }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">
                                            {{ __('No upcoming bookings') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-primary float-right">
                            {{ __('View All Bookings') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
