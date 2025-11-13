@extends('layouts.app')

@section('title', 'ประวัติการจอง')

@push('styles')
<style>
    .history-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        text-align: center;
    }
    
    .history-header h2 {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .history-header p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
    }
    
    .stats-row {
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: white;
        border: none;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        height: 100%;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    }
    
    .stat-card-total {
        border-left: 4px solid #667eea;
    }
    
    .stat-card-confirmed {
        border-left: 4px solid #38ef7d;
    }
    
    .stat-card-completed {
        border-left: 4px solid #00f2fe;
    }
    
    .stat-card-cancelled {
        border-left: 4px solid #f5576c;
    }
    
    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    
    .stat-icon-total { color: #667eea; }
    .stat-icon-confirmed { color: #38ef7d; }
    .stat-icon-completed { color: #00f2fe; }
    .stat-icon-cancelled { color: #f5576c; }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
    }
    
    .stat-label {
        color: #666;
        font-size: 0.95rem;
    }
    
    .filter-tabs {
        background: white;
        border-radius: 15px;
        padding: 1rem;
        margin-bottom: 2rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    
    .filter-btn {
        border: 2px solid #e0e0e0;
        color: #666;
        border-radius: 25px;
        padding: 0.5rem 1.5rem;
        margin: 0.25rem;
        font-weight: 600;
        transition: all 0.3s ease;
        background: white;
    }
    
    .filter-btn:hover {
        border-color: #667eea;
        color: #667eea;
        background: #f8f9fa;
    }
    
    .filter-btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
        color: white;
    }
    
    .booking-history-card {
        background: white;
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }
    
    .booking-history-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    }
    
    .booking-card-header {
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 3px solid #e0e0e0;
    }
    
    .booking-card-header.status-confirmed {
        border-bottom-color: #38ef7d;
        background: linear-gradient(135deg, #d4fcdf 0%, #e8f5e9 100%);
    }
    
    .booking-card-header.status-completed {
        border-bottom-color: #00f2fe;
        background: linear-gradient(135deg, #e0f7fa 0%, #e1f5fe 100%);
    }
    
    .booking-card-header.status-cancelled {
        border-bottom-color: #f5576c;
        background: linear-gradient(135deg, #ffebee 0%, #fce4ec 100%);
    }
    
    .booking-card-header.status-pending {
        border-bottom-color: #ffa726;
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
    }
    
    .booking-id {
        font-size: 0.9rem;
        color: #666;
        font-weight: 600;
    }
    
    .booking-room-name {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        margin: 0.5rem 0;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .status-badge.confirmed {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .status-badge.completed {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    
    .status-badge.cancelled {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .status-badge.pending {
        background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);
        color: white;
    }
    
    .booking-card-body {
        padding: 1.5rem;
    }
    
    .booking-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .booking-info-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 10px;
    }
    
    .booking-info-item i {
        font-size: 1.5rem;
        margin-right: 1rem;
        color: #667eea;
        width: 30px;
        text-align: center;
    }
    
    .booking-info-label {
        display: block;
        font-size: 0.85rem;
        color: #666;
        font-weight: 600;
    }
    
    .booking-info-value {
        display: block;
        color: #333;
        font-weight: 600;
    }
    
    .booking-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    
    .btn-view-details {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        border: none;
        border-radius: 25px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(79, 172, 254, 0.4);
    }
    
    .btn-view-details:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(79, 172, 254, 0.6);
        color: white;
    }
    
    .btn-cancel-booking {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        border-radius: 25px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(245, 87, 108, 0.4);
    }
    
    .btn-cancel-booking:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(245, 87, 108, 0.6);
        color: white;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }
    
    .empty-state-icon {
        font-size: 6rem;
        color: #667eea;
        margin-bottom: 2rem;
    }
    
    .empty-state h3 {
        font-size: 2rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 1rem;
    }
    
    .empty-state p {
        font-size: 1.2rem;
        color: #666;
        margin-bottom: 2rem;
    }
    
    .empty-state-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 50px;
        padding: 1rem 3rem;
        font-size: 1.2rem;
        font-weight: 600;
        color: white;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }
    
    .empty-state-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
        color: white;
    }
    
    .pagination {
        justify-content: center;
        margin-top: 2rem;
    }
    
    .pagination .page-link {
        border-radius: 10px;
        margin: 0 0.25rem;
        border: 2px solid #e0e0e0;
        color: #667eea;
        font-weight: 600;
    }
    
    .pagination .page-link:hover {
        background: #667eea;
        border-color: #667eea;
        color: white;
    }
    
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
    }
    
    .timeline-indicator {
        width: 4px;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
        border-radius: 0 4px 4px 0;
    }
    
    .timeline-indicator.upcoming {
        background: linear-gradient(180deg, #38ef7d 0%, #11998e 100%);
    }
    
    .timeline-indicator.past {
        background: linear-gradient(180deg, #00f2fe 0%, #4facfe 100%);
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="history-header">
        <h2><i class="fas fa-history me-3"></i>ประวัติการจองของฉัน</h2>
        <p>ดูรายละเอียดและจัดการการจองห้องประชุมทั้งหมดของคุณ</p>
    </div>

    @if($bookings->isEmpty())
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h3>ยังไม่มีประวัติการจอง</h3>
            <p>คุณยังไม่เคยจองห้องประชุม เริ่มต้นจองห้องประชุมได้เลยตอนนี้!</p>
            <a href="{{ route('guest.booking.search') }}" class="btn empty-state-btn">
                <i class="fas fa-search me-2"></i>ค้นหาห้องประชุม
            </a>
        </div>
    @else
        <!-- Stats Cards -->
        <div class="row stats-row">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stat-card stat-card-total">
                    <div class="text-center">
                        <div class="stat-icon stat-icon-total">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div class="stat-number">{{ $bookings->total() }}</div>
                        <div class="stat-label">การจองทั้งหมด</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stat-card stat-card-confirmed">
                    <div class="text-center">
                        <div class="stat-icon stat-icon-confirmed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-number">{{ $bookings->where('status', 'confirmed')->count() }}</div>
                        <div class="stat-label">ยืนยันแล้ว</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stat-card stat-card-completed">
                    <div class="text-center">
                        <div class="stat-icon stat-icon-completed">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div class="stat-number">{{ $bookings->where('status', 'completed')->count() }}</div>
                        <div class="stat-label">เสร็จสิ้น</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card stat-card stat-card-cancelled">
                    <div class="text-center">
                        <div class="stat-icon stat-icon-cancelled">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-number">{{ $bookings->where('status', 'cancelled')->count() }}</div>
                        <div class="stat-label">ยกเลิก</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Cards -->
        @foreach($bookings as $booking)
            <div class="booking-history-card" style="position: relative;">
                <div class="timeline-indicator {{ $booking->start_datetime > now() ? 'upcoming' : 'past' }}"></div>
                
                <div class="booking-card-header status-{{ $booking->status }}">
                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <div>
                            <div class="booking-id">
                                <i class="fas fa-hashtag"></i> {{ $booking->id }}
                            </div>
                            <h3 class="booking-room-name">{{ $booking->room->name }}</h3>
                        </div>
                        <div>
                            <span class="status-badge {{ $booking->status }}">
                                @if($booking->status === 'confirmed')
                                    <i class="fas fa-check-circle me-1"></i>ยืนยันแล้ว
                                @elseif($booking->status === 'completed')
                                    <i class="fas fa-check-double me-1"></i>เสร็จสิ้น
                                @elseif($booking->status === 'cancelled')
                                    <i class="fas fa-times-circle me-1"></i>ยกเลิก
                                @else
                                    <i class="fas fa-clock me-1"></i>รอดำเนินการ
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="booking-card-body">
                    <div class="booking-info-grid">
                        <div class="booking-info-item">
                            <i class="far fa-calendar"></i>
                            <div>
                                <span class="booking-info-label">วันที่</span>
                                <span class="booking-info-value">{{ $booking->start_datetime->locale('th')->translatedFormat('d M Y') }}</span>
                            </div>
                        </div>
                        <div class="booking-info-item">
                            <i class="far fa-clock"></i>
                            <div>
                                <span class="booking-info-label">เวลา</span>
                                <span class="booking-info-value">{{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}</span>
                            </div>
                        </div>
                        <div class="booking-info-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <span class="booking-info-label">จำนวนผู้เข้าร่วม</span>
                                <span class="booking-info-value">{{ $booking->attendee_count }} คน</span>
                            </div>
                        </div>
                        <div class="booking-info-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div>
                                <span class="booking-info-label">สถานที่</span>
                                <span class="booking-info-value">{{ $booking->room->location ?? 'ไม่ระบุ' }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-actions">
                        <a href="{{ route('guest.booking.show', $booking->id) }}" class="btn btn-view-details">
                            <i class="fas fa-eye me-2"></i>ดูรายละเอียด
                        </a>
                        
                        @if($booking->status === 'confirmed' && $booking->start_datetime > now())
                            <form action="{{ route('guest.booking.cancel', $booking->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการยกเลิกการจองนี้?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-cancel-booking">
                                    <i class="fas fa-times me-2"></i>ยกเลิกการจอง
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    @endif
</div>
@endsection
