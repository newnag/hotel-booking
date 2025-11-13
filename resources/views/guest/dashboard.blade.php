@extends('layouts.app')

@section('title', 'แดชบอร์ด')

@push('styles')
<style>
    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .dashboard-header h2 {
        margin: 0;
        font-weight: bold;
    }
    
    .quick-action-card {
        border: none;
        border-radius: 15px;
        transition: all 0.3s ease;
        height: 100%;
        overflow: hidden;
    }
    
    .quick-action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .quick-action-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .action-card-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .action-card-secondary {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .action-card-success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 3px solid #667eea;
        display: inline-block;
    }
    
    .booking-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .booking-card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transform: translateY(-3px);
    }
    
    .booking-card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1rem 1.5rem;
        font-weight: bold;
    }
    
    .booking-info-row {
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .booking-info-row:last-child {
        border-bottom: none;
    }
    
    .booking-info-label {
        font-weight: 600;
        color: #666;
        margin-bottom: 0.25rem;
    }
    
    .booking-info-value {
        color: #333;
        font-size: 1.1rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        background: #f8f9fa;
        border-radius: 15px;
        margin: 2rem 0;
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: #dee2e6;
        margin-bottom: 1rem;
    }
    
    .stats-card {
        border: none;
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        color: white;
        margin-bottom: 1.5rem;
    }
    
    .stats-card h3 {
        font-size: 2.5rem;
        font-weight: bold;
        margin: 0;
    }
    
    .stats-card p {
        margin: 0;
        opacity: 0.9;
    }
    
    .table-modern {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    
    .table-modern thead {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .table-modern thead th {
        border: none;
        padding: 1rem;
        font-weight: 600;
    }
    
    .table-modern tbody tr {
        transition: background-color 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .badge-large {
        padding: 0.5rem 1rem;
        font-size: 0.9rem;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2><i class="fas fa-home me-2"></i>ยินดีต้อนรับ, {{ auth()->user()->name }}!</h2>
                <p class="mb-0 mt-2" style="opacity: 0.9;">
                    <i class="far fa-calendar-alt me-2"></i>
                    {{ now()->locale('th')->translatedFormat('l, d F Y') }}
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="{{ route('guest.booking.search') }}" class="btn btn-light btn-lg">
                    <i class="fas fa-plus-circle me-2"></i>จองห้องใหม่
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stats-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <h3>{{ $upcomingBookings->count() }}</h3>
                <p><i class="far fa-calendar-check me-2"></i>การจองที่กำลังมาถึง</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3>{{ $recentBookings->count() }}</h3>
                <p><i class="fas fa-history me-2"></i>ประวัติการจองล่าสุด</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>{{ auth()->user()->bookings()->where('status', 'confirmed')->count() }}</h3>
                <p><i class="fas fa-check-circle me-2"></i>การจองทั้งหมด</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="section-title">
                <i class="fas fa-bolt me-2"></i>เมนูด่วน
            </h3>
        </div>
        <div class="col-md-4">
            <a href="{{ route('guest.booking.search') }}" class="text-decoration-none">
                <div class="card quick-action-card action-card-primary">
                    <div class="card-body text-center p-4">
                        <div class="quick-action-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5 class="mb-0">ค้นหาห้องว่าง</h5>
                        <p class="mb-0 mt-2" style="opacity: 0.9;">ค้นหาและจองห้องประชุม</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('guest.booking.history') }}" class="text-decoration-none">
                <div class="card quick-action-card action-card-secondary">
                    <div class="card-body text-center p-4">
                        <div class="quick-action-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <h5 class="mb-0">ประวัติการจอง</h5>
                        <p class="mb-0 mt-2" style="opacity: 0.9;">ดูประวัติการจองทั้งหมด</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('profile.edit') }}" class="text-decoration-none">
                <div class="card quick-action-card action-card-success">
                    <div class="card-body text-center p-4">
                        <div class="quick-action-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h5 class="mb-0">โปรไฟล์</h5>
                        <p class="mb-0 mt-2" style="opacity: 0.9;">จัดการข้อมูลส่วนตัว</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Upcoming Bookings -->
    <div class="row mb-5">
        <div class="col-12">
            <h3 class="section-title">
                <i class="far fa-calendar-alt me-2"></i>การจองที่กำลังจะมาถึง
            </h3>
        </div>
        <div class="col-12">
            @if($upcomingBookings->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="far fa-calendar-times"></i>
                    </div>
                    <h4>ไม่มีการจองที่กำลังจะมาถึง</h4>
                    <p class="text-muted mb-4">เริ่มต้นจองห้องประชุมเพื่อการประชุมที่มีประสิทธิภาพ</p>
                    <a href="{{ route('guest.booking.search') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-plus-circle me-2"></i>จองห้องประชุมเลย
                    </a>
                </div>
            @else
                @foreach($upcomingBookings as $booking)
                    <div class="booking-card">
                        <div class="booking-card-header">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <i class="fas fa-door-open me-2"></i>
                                    {{ $booking->room->name }}
                                    <span class="badge bg-light text-dark ms-2">{{ $booking->booking_ref }}</span>
                                </div>
                                <div class="col-md-4 text-md-end mt-2 mt-md-0">
                                    @if($booking->status === 'confirmed')
                                        <span class="badge bg-success badge-large">
                                            <i class="fas fa-check-circle me-1"></i>ยืนยันแล้ว
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="booking-info-row">
                                        <div class="booking-info-label">
                                            <i class="far fa-calendar me-2"></i>วันที่
                                        </div>
                                        <div class="booking-info-value">
                                            {{ $booking->start_datetime->locale('th')->translatedFormat('d M Y') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="booking-info-row">
                                        <div class="booking-info-label">
                                            <i class="far fa-clock me-2"></i>เวลา
                                        </div>
                                        <div class="booking-info-value">
                                            {{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="booking-info-row">
                                        <div class="booking-info-label">
                                            <i class="fas fa-users me-2"></i>จำนวนผู้เข้าร่วม
                                        </div>
                                        <div class="booking-info-value">
                                            {{ $booking->attendee_count }} คน
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="booking-info-row">
                                        <div class="booking-info-label">
                                            <i class="fas fa-map-marker-alt me-2"></i>สถานที่
                                        </div>
                                        <div class="booking-info-value">
                                            {{ $booking->room->location }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            @if($booking->decoration_theme)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-paint-brush me-2"></i>
                                            <strong>ธีมการตกแต่ง:</strong> {{ $booking->decoration_theme }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            <div class="row mt-3">
                                <div class="col-12 text-end">
                                    <a href="{{ route('guest.booking.show', $booking->id) }}" class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>ดูรายละเอียด
                                    </a>
                                    @if($booking->start_datetime > now())
                                        <form action="{{ route('guest.booking.cancel', $booking->id) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการยกเลิกการจองนี้?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-times-circle me-2"></i>ยกเลิกการจอง
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="row">
        <div class="col-12">
            <h3 class="section-title">
                <i class="fas fa-history me-2"></i>ประวัติการจองล่าสุด
            </h3>
        </div>
        <div class="col-12">
            @if($recentBookings->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h4>ยังไม่มีประวัติการจอง</h4>
                    <p class="text-muted">ประวัติการจองของคุณจะแสดงที่นี่</p>
                </div>
            @else
                <div class="card table-modern">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>รหัสการจอง</th>
                                <th><i class="fas fa-door-open me-2"></i>ห้องประชุม</th>
                                <th><i class="far fa-calendar me-2"></i>วันที่ & เวลา</th>
                                <th><i class="fas fa-info-circle me-2"></i>สถานะ</th>
                                <th class="text-center"><i class="fas fa-cog me-2"></i>การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBookings as $booking)
                                <tr>
                                    <td class="fw-bold">{{ $booking->booking_ref }}</td>
                                    <td>
                                        <i class="fas fa-door-closed me-2 text-primary"></i>
                                        {{ $booking->room->name }}
                                    </td>
                                    <td>
                                        {{ $booking->start_datetime->format('d/m/Y H:i') }}<br>
                                        <small class="text-muted">
                                            <i class="fas fa-arrow-right me-1"></i>
                                            ถึง {{ $booking->end_datetime->format('H:i') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($booking->status === 'confirmed')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>ยืนยันแล้ว
                                            </span>
                                        @elseif($booking->status === 'cancelled')
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle me-1"></i>ยกเลิกแล้ว
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-flag-checkered me-1"></i>เสร็จสิ้น
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('guest.booking.show', $booking->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye me-1"></i>ดู
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('guest.booking.history') }}" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-list me-2"></i>ดูการจองทั้งหมด
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
