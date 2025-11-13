@extends('layouts.admin')

@section('title', 'รายละเอียดการจอง - ' . $booking->booking_ref)

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">รายละเอียดการจอง</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">
                        <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('staff.dashboard') }}">
                            แดชบอร์ด
                        </a>
                    </li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">การจอง</a></li>
                    <li class="breadcrumb-item active">{{ $booking->booking_ref }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <!-- Booking Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ข้อมูลการจอง</h3>
                        <div class="card-tools">
                            @if($booking->status === 'confirmed')
                                <span class="badge badge-success">ยืนยันแล้ว</span>
                            @elseif($booking->status === 'cancelled')
                                <span class="badge badge-danger">ยกเลิกแล้ว</span>
                            @else
                                <span class="badge badge-info">เสร็จสิ้น</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-4">รหัสการจอง</dt>
                            <dd class="col-sm-8">{{ $booking->booking_ref }}</dd>

                            <dt class="col-sm-4">ห้องประชุม</dt>
                            <dd class="col-sm-8">
                                <a href="{{ route('admin.status.room', $booking->room->id) }}">
                                    {{ $booking->room->name }}
                                </a>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> {{ $booking->room->location }}
                                    | <i class="fas fa-users"></i> ความจุ: {{ $booking->room->max_capacity }} คน
                                </small>
                            </dd>

                            <dt class="col-sm-4">ผู้จอง</dt>
                            <dd class="col-sm-8">
                                {{ $booking->user->name }}<br>
                                <small class="text-muted">
                                    <i class="fas fa-envelope"></i> {{ $booking->user->email }}
                                    @if($booking->user->phone)
                                        <br><i class="fas fa-phone"></i> {{ $booking->user->phone }}
                                    @endif
                                </small>
                            </dd>

                            <dt class="col-sm-4">วันที่และเวลา</dt>
                            <dd class="col-sm-8">
                                <strong>{{ $booking->start_datetime->locale('th')->translatedFormat('l, d F Y') }}</strong><br>
                                {{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}
                                <span class="badge badge-secondary">{{ $booking->start_datetime->diffInMinutes($booking->end_datetime) }} นาที</span>
                            </dd>

                            <dt class="col-sm-4">จำนวนผู้เข้าร่วม</dt>
                            <dd class="col-sm-8">{{ $booking->attendee_count }} คน</dd>

                            @if($booking->decoration_theme)
                                <dt class="col-sm-4">ธีมการตกแต่ง</dt>
                                <dd class="col-sm-8">{{ $booking->decoration_theme }}</dd>
                            @endif

                            @if($booking->notes)
                                <dt class="col-sm-4">หมายเหตุ</dt>
                                <dd class="col-sm-8">{{ $booking->notes }}</dd>
                            @endif

                            <dt class="col-sm-4">สร้างเมื่อ</dt>
                            <dd class="col-sm-8">{{ $booking->created_at->format('d/m/Y H:i') }}</dd>

                            <dt class="col-sm-4">อัพเดทล่าสุด</dt>
                            <dd class="col-sm-8">{{ $booking->updated_at->format('d/m/Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>

                <!-- Line Notifications -->
                @if($booking->lineNotifications->isNotEmpty())
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">ประวัติการแจ้งเตือน LINE</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ประเภท</th>
                                        <th>สถานะ</th>
                                        <th>ส่งเมื่อ</th>
                                        <th>ข้อผิดพลาด</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($booking->lineNotifications as $notification)
                                        <tr>
                                            <td>
                                                @if($notification->notification_type === 'booking_created')
                                                    สร้างการจอง
                                                @elseif($notification->notification_type === 'booking_confirmed')
                                                    ยืนยันการจอง
                                                @elseif($notification->notification_type === 'booking_cancelled')
                                                    ยกเลิกการจอง
                                                @elseif($notification->notification_type === 'booking_reminder')
                                                    แจ้งเตือนล่วงหน้า
                                                @else
                                                    {{ ucfirst($notification->notification_type) }}
                                                @endif
                                            </td>
                                            <td>
                                                @if($notification->status === 'sent')
                                                    <span class="badge badge-success">ส่งแล้ว</span>
                                                @elseif($notification->status === 'failed')
                                                    <span class="badge badge-danger">ส่งไม่สำเร็จ</span>
                                                @else
                                                    <span class="badge badge-warning">รอดำเนินการ</span>
                                                @endif
                                            </td>
                                            <td>{{ $notification->sent_at ? $notification->sent_at->format('d/m/Y H:i') : '-' }}</td>
                                            <td>{{ $notification->error_message ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-4">
                <!-- Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">การดำเนินการ</h3>
                    </div>
                    <div class="card-body">
                        @if($booking->status === 'confirmed')
                            <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="mb-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-check"></i> ทำเครื่องหมายเสร็จสิ้น
                                </button>
                            </form>

                            <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST" class="mb-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="cancelled">
                                <button type="submit" class="btn btn-warning btn-block" onclick="return confirm('คุณแน่ใจหรือไม่?')">
                                    <i class="fas fa-times"></i> ยกเลิกการจอง
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('admin.calendar.index') }}" class="btn btn-info btn-block">
                            <i class="fas fa-calendar"></i> ดูปฏิทิน
                        </a>

                        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-list"></i> กลับไปรายการ
                        </a>

                        @if(auth()->user()->role === 'admin')
                            <hr>
                            <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-block" onclick="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบการจองนี้? การดำเนินการนี้ไม่สามารถย้อนกลับได้')">
                                    <i class="fas fa-trash"></i> ลบการจอง
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Timeline -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">ไทม์ไลน์</h3>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <div>
                                <i class="fas fa-calendar-check bg-success"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $booking->start_datetime->format('d/m H:i') }}</span>
                                    <h3 class="timeline-header">เริ่มการจอง</h3>
                                </div>
                            </div>
                            <div>
                                <i class="fas fa-calendar-times bg-danger"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $booking->end_datetime->format('d/m H:i') }}</span>
                                    <h3 class="timeline-header">สิ้นสุดการจอง</h3>
                                </div>
                            </div>
                            <div>
                                <i class="fas fa-clock bg-gray"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
