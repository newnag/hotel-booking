@extends('layouts.admin')

@section('title', 'รายละเอียดห้องประชุม - ' . $room->name)

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $room->name }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าแรก</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">จัดการห้องประชุม</a></li>
                    <li class="breadcrumb-item active">{{ $room->name }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            {{-- Room Info --}}
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ข้อมูลห้องประชุม</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.rooms.edit', $room) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i> แก้ไข
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($room->image_path)
                            <img src="{{ asset('storage/' . $room->image_path) }}" 
                                 alt="{{ $room->name }}" 
                                 class="img-fluid mb-3">
                        @else
                            <div class="bg-secondary text-white text-center py-5 mb-3">
                                <i class="fas fa-image fa-3x"></i>
                                <p class="mt-2">ไม่มีรูปภาพ</p>
                            </div>
                        @endif

                        <dl>
                            <dt>ชื่อห้อง</dt>
                            <dd>{{ $room->name }}</dd>

                            <dt>ความจุ</dt>
                            <dd>
                                <span class="badge badge-info">
                                    <i class="fas fa-users"></i> {{ $room->max_capacity }} ที่นั่ง
                                </span>
                            </dd>

                            <dt>สถานะ</dt>
                            <dd>
                                @if($room->is_active)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> เปิดใช้งาน
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-times-circle"></i> ปิดใช้งาน
                                    </span>
                                @endif
                            </dd>

                            @if($room->description)
                                <dt>คำอธิบาย</dt>
                                <dd>{{ $room->description }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- Statistics --}}
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">สถิติการใช้งาน</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="text-primary">
                                    <h3>{{ $statistics['total_bookings'] }}</h3>
                                </div>
                                <small>การจองทั้งหมด</small>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-success">
                                    <h3>{{ $statistics['upcoming_bookings'] }}</h3>
                                </div>
                                <small>กำลังจะมาถึง</small>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="text-info">
                                    <h3>{{ $statistics['completed_bookings'] }}</h3>
                                </div>
                                <small>เสร็จสิ้นแล้ว</small>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-warning">
                                    <h3>{{ $statistics['total_attendees'] }}</h3>
                                </div>
                                <small>ผู้เข้าร่วมทั้งหมด</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Upcoming Bookings --}}
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">การจองที่กำลังจะมาถึง</h3>
                    </div>
                    <div class="card-body">
                        @if($upcomingBookings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>รหัสการจอง</th>
                                            <th>ผู้จอง</th>
                                            <th>วันที่-เวลา</th>
                                            <th>จำนวนคน</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upcomingBookings as $booking)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.bookings.show', $booking) }}">
                                                        {{ $booking->booking_ref }}
                                                    </a>
                                                </td>
                                                <td>{{ $booking->user->name }}</td>
                                                <td>
                                                    <small>
                                                        {{ $booking->start_datetime->format('d/m/Y H:i') }}
                                                        -
                                                        {{ $booking->end_datetime->format('H:i') }}
                                                    </small>
                                                </td>
                                                <td>{{ $booking->attendee_count }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $booking->status === 'confirmed' ? 'success' : 'secondary' }}">
                                                        {{ $booking->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">ไม่มีการจองที่กำลังจะมาถึง</p>
                        @endif
                    </div>
                </div>

                {{-- Recent Bookings --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ประวัติการจองล่าสุด</h3>
                    </div>
                    <div class="card-body">
                        @if($recentBookings->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                        <tr>
                                            <th>รหัสการจอง</th>
                                            <th>ผู้จอง</th>
                                            <th>วันที่-เวลา</th>
                                            <th>จำนวนคน</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentBookings as $booking)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.bookings.show', $booking) }}">
                                                        {{ $booking->booking_ref }}
                                                    </a>
                                                </td>
                                                <td>{{ $booking->user->name }}</td>
                                                <td>
                                                    <small>
                                                        {{ $booking->start_datetime->format('d/m/Y H:i') }}
                                                        -
                                                        {{ $booking->end_datetime->format('H:i') }}
                                                    </small>
                                                </td>
                                                <td>{{ $booking->attendee_count }}</td>
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'pending' => 'warning',
                                                            'confirmed' => 'success',
                                                            'completed' => 'info',
                                                            'cancelled' => 'danger'
                                                        ];
                                                    @endphp
                                                    <span class="badge badge-{{ $statusColors[$booking->status] ?? 'secondary' }}">
                                                        {{ $booking->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted">ยังไม่มีประวัติการจอง</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
