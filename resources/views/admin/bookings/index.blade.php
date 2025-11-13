@extends('layouts.admin')

@section('title', __('All Bookings'))

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('All Bookings') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('staff.dashboard') }}">
                            {{ __('Dashboard') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active">{{ __('Bookings') }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <!-- Filters -->
                <div class="card collapsed-card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Filters') }}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.bookings.index') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('Status') }}</label>
                                        <select name="status" class="form-control">
                                            <option value="">{{ __('All Statuses') }}</option>
                                            @foreach($statuses as $status)
                                                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                                    {{ ucfirst($status) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('Room') }}</label>
                                        <select name="room_id" class="form-control">
                                            <option value="">{{ __('All Rooms') }}</option>
                                            @foreach($rooms as $room)
                                                <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                                    {{ $room->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{{ __('Start Date') }}</label>
                                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{{ __('End Date') }}</label>
                                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>{{ __('Search') }}</label>
                                        <input type="text" name="search" class="form-control" placeholder="{{ __('Ref or Guest') }}" value="{{ request('search') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> {{ __('Apply Filters') }}
                                    </button>
                                    <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> {{ __('Clear') }}
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bookings Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('Booking List') }}</h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">{{ $bookings->total() }} {{ __('total') }}</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ __('Booking Ref') }}</th>
                                        <th>{{ __('Room') }}</th>
                                        <th>{{ __('Guest') }}</th>
                                        <th>{{ __('Date & Time') }}</th>
                                        <th>{{ __('Attendees') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bookings as $booking)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.bookings.show', $booking->id) }}">
                                                    {{ $booking->booking_ref }}
                                                </a>
                                            </td>
                                            <td>{{ $booking->room->name }}</td>
                                            <td>
                                                {{ $booking->user->name }}<br>
                                                <small class="text-muted">{{ $booking->user->email }}</small>
                                            </td>
                                            <td>
                                                {{ $booking->start_datetime->format('M d, Y') }}<br>
                                                <small class="text-muted">{{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}</small>
                                            </td>
                                            <td>{{ $booking->attendee_count }}</td>
                                            <td>
                                                @if($booking->status === 'confirmed')
                                                    <span class="badge badge-success">{{ ucfirst($booking->status) }}</span>
                                                @elseif($booking->status === 'cancelled')
                                                    <span class="badge badge-danger">{{ ucfirst($booking->status) }}</span>
                                                @else
                                                    <span class="badge badge-info">{{ ucfirst($booking->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-info" title="{{ __('View') }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($booking->status === 'confirmed')
                                                        <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" class="btn btn-sm btn-success" title="{{ __('Complete') }}">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                {{ __('No bookings found') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($bookings->hasPages())
                        <div class="card-footer clearfix">
                            {{ $bookings->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
