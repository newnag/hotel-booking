@extends('layouts.admin')

@section('title', 'แดชบอร์ดผู้ดูแลระบบ')

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">แดชบอร์ด</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item active">แดชบอร์ด</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <!-- Quick Stats -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ \App\Models\Booking::where('status', 'confirmed')->count() }}</h3>
                        <p>การจองที่ยืนยันแล้ว</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.bookings.index', ['status' => 'confirmed']) : route('staff.bookings.index', ['status' => 'confirmed']) }}" class="small-box-footer">
                        ดูเพิ่มเติม <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ \App\Models\MeetingRoom::active()->count() }}</h3>
                        <p>ห้องประชุมที่ใช้งานได้</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.status.overview') : route('staff.status.overview') }}" class="small-box-footer">
                        ดูเพิ่มเติม <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ \App\Models\Booking::whereDate('start_datetime', today())->count() }}</h3>
                        <p>การจองวันนี้</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.calendar.index') : route('staff.calendar.index') }}" class="small-box-footer">
                        ดูเพิ่มเติม <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ \App\Models\User::where('role', 'guest')->count() }}</h3>
                        <p>ผู้ใช้งานทั้งหมด</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="#" class="small-box-footer">
                        ดูเพิ่มเติม <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-bolt mr-1"></i>
                            เมนูด่วน
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-12">
                                <a href="{{ auth()->user()->isAdmin() ? route('admin.calendar.index') : route('staff.calendar.index') }}" class="btn btn-app bg-primary">
                                    <i class="fas fa-calendar-alt"></i> ปฏิทิน
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <a href="{{ auth()->user()->isAdmin() ? route('admin.status.overview') : route('staff.status.overview') }}" class="btn btn-app bg-success">
                                    <i class="fas fa-chart-line"></i> ภาพรวมสถานะ
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <a href="{{ auth()->user()->isAdmin() ? route('admin.bookings.index') : route('staff.bookings.index') }}" class="btn btn-app bg-info">
                                    <i class="fas fa-list"></i> รายการจองทั้งหมด
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 col-12">
                                <a href="#" class="btn btn-app bg-warning">
                                    <i class="fas fa-door-closed"></i> จัดการห้องประชุม
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header border-transparent">
                        <h3 class="card-title">การจองล่าสุด</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table m-0">
                                <thead>
                                <tr>
                                    <th>รหัสการจอง</th>
                                    <th>ห้องประชุม</th>
                                    <th>ผู้จอง</th>
                                    <th>วันที่/เวลา</th>
                                    <th>สถานะ</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse(\App\Models\Booking::with(['user', 'room'])->latest()->take(8)->get() as $booking)
                                    <tr>
                                        <td>
                                            <a href="{{ auth()->user()->isAdmin() ? route('admin.bookings.show', $booking->id) : route('staff.bookings.show', $booking->id) }}">
                                                {{ $booking->booking_ref }}
                                            </a>
                                        </td>
                                        <td>{{ $booking->room?->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->user?->name ?? 'N/A' }}</td>
                                        <td>{{ $booking->start_datetime->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($booking->status === 'confirmed')
                                                <span class="badge badge-success">ยืนยันแล้ว</span>
                                            @elseif($booking->status === 'cancelled')
                                                <span class="badge badge-danger">ยกเลิกแล้ว</span>
                                            @else
                                                <span class="badge badge-info">{{ ucfirst($booking->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            ยังไม่มีการจอง
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer clearfix">
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.bookings.index') : route('staff.bookings.index') }}" class="btn btn-sm btn-secondary float-right">
                            ดูการจองทั้งหมด
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">สถานะห้องประชุม</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="products-list product-list-in-card pl-2 pr-2">
                            @foreach(\App\Models\MeetingRoom::active()->get() as $room)
                                @php
                                    $today = \Carbon\Carbon::today();
                                    $todayBookingsCount = $room->bookings()
                                        ->whereBetween('start_datetime', [$today, $today->copy()->endOfDay()])
                                        ->whereIn('status', ['confirmed', 'completed'])
                                        ->count();
                                @endphp
                                <li class="item">
                                    <div class="product-info">
                                        <a href="{{ auth()->user()->isAdmin() ? route('admin.status.room', $room->id) : route('staff.status.room', $room->id) }}" class="product-title">
                                            {{ $room->name }}
                                            <span class="badge badge-info float-right">{{ $todayBookingsCount }} วันนี้</span>
                                        </a>
                                        <span class="product-description">
                                            <i class="fas fa-users"></i> ความจุ: {{ $room->max_capacity }} คน
                                            <br>
                                            <i class="fas fa-map-marker-alt"></i> {{ $room->location }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="card-footer text-center">
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.status.overview') : route('staff.status.overview') }}" class="uppercase">ดูห้องประชุมทั้งหมด</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
