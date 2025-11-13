@extends('layouts.app')

@section('title', 'รายละเอียดการจอง')

@push('styles')
<style>
    .booking-detail-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .booking-detail-header h2 {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .booking-ref-display {
        background: rgba(255, 255, 255, 0.2);
        padding: 1rem 1.5rem;
        border-radius: 10px;
        display: inline-block;
        margin-top: 1rem;
    }
    
    .booking-ref-display .ref-label {
        font-size: 0.9rem;
        opacity: 0.9;
        margin-bottom: 0.25rem;
    }
    
    .booking-ref-display .ref-number {
        font-size: 1.5rem;
        font-weight: bold;
        font-family: monospace;
    }
    
    .status-alert {
        border: none;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    }
    
    .status-alert.confirmed {
        background: linear-gradient(135deg, #d4fcdf 0%, #e8f5e9 100%);
        border-left: 5px solid #38ef7d;
    }
    
    .status-alert.cancelled {
        background: linear-gradient(135deg, #ffebee 0%, #fce4ec 100%);
        border-left: 5px solid #f5576c;
    }
    
    .status-alert.completed {
        background: linear-gradient(135deg, #e0f7fa 0%, #e1f5fe 100%);
        border-left: 5px solid #00f2fe;
    }
    
    .status-alert h4 {
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .status-alert.confirmed h4 { color: #11998e; }
    .status-alert.cancelled h4 { color: #d32f2f; }
    .status-alert.completed h4 { color: #0277bd; }
    
    .info-card {
        background: white;
        border: none;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .info-card-header {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 1.5rem;
        font-size: 1.3rem;
        font-weight: 600;
    }
    
    .info-card-header i {
        margin-right: 0.75rem;
    }
    
    .info-card-body {
        padding: 2rem;
    }
    
    .info-section {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .info-section:last-child {
        margin-bottom: 0;
    }
    
    .info-section-title {
        font-size: 1.1rem;
        font-weight: bold;
        color: #667eea;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
    }
    
    .info-section-title i {
        font-size: 1.5rem;
        margin-right: 0.75rem;
        color: #667eea;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
    
    .info-item {
        background: white;
        padding: 1.25rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .info-label {
        display: flex;
        align-items: center;
        font-size: 0.9rem;
        color: #666;
        font-weight: 600;
        margin-bottom: 0.75rem;
    }
    
    .info-label i {
        font-size: 1.2rem;
        margin-right: 0.5rem;
        color: #667eea;
    }
    
    .info-value {
        font-size: 1.3rem;
        font-weight: bold;
        color: #333;
    }
    
    .info-value.large {
        font-size: 1.8rem;
        color: #667eea;
    }
    
    .info-subtext {
        font-size: 0.95rem;
        color: #666;
        margin-top: 0.5rem;
    }
    
    .room-display {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .room-name {
        font-size: 1.8rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 0.75rem;
    }
    
    .room-description {
        color: #666;
        margin-bottom: 1rem;
        line-height: 1.6;
    }
    
    .room-location {
        display: flex;
        align-items: center;
        color: #667eea;
        font-weight: 600;
    }
    
    .room-location i {
        margin-right: 0.5rem;
        font-size: 1.2rem;
    }
    
    .status-badge-large {
        display: inline-block;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .status-badge-large.confirmed {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }
    
    .status-badge-large.cancelled {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }
    
    .status-badge-large.completed {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }
    
    .notes-display {
        background: #fffbf0;
        border-left: 4px solid #ffa726;
        padding: 1.5rem;
        border-radius: 10px;
        margin-top: 1rem;
    }
    
    .notes-display p {
        margin: 0;
        color: #666;
        line-height: 1.6;
    }
    
    .meta-info {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        margin-top: 1.5rem;
    }
    
    .meta-info small {
        color: #666;
        line-height: 1.8;
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 2rem;
    }
    
    .btn-back {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
        border-radius: 25px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-back:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
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
    
    .btn-print {
        background: white;
        color: #4facfe;
        border: 2px solid #4facfe;
        border-radius: 25px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-print:hover {
        background: #4facfe;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
    }
    
    .timeline-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);
        color: white;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }
    
    .timeline-badge i {
        margin-right: 0.5rem;
    }
    
    .timeline-badge.upcoming {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    
    .timeline-badge.past {
        background: linear-gradient(135deg, #95a5a6 0%, #7f8c8d 100%);
    }
    
    @media print {
        .action-buttons,
        .btn-back,
        .btn-cancel-booking,
        .btn-print {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="booking-detail-header">
        <h2><i class="fas fa-ticket-alt me-3"></i>รายละเอียดการจอง</h2>
        <div class="booking-ref-display">
            <div class="ref-label">หมายเลขอ้างอิง</div>
            <div class="ref-number">{{ $booking->booking_ref }}</div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <!-- Status Alert -->
            @if($booking->status === 'confirmed')
                <div class="status-alert confirmed">
                    <h4><i class="fas fa-check-circle me-2"></i>การจองได้รับการยืนยันแล้ว</h4>
                    <p class="mb-0">การจองของคุณได้รับการยืนยันเรียบร้อยแล้ว พร้อมให้บริการตามวันเวลาที่กำหนด</p>
                    @if($booking->start_datetime > now())
                        <div class="timeline-badge upcoming mt-2">
                            <i class="fas fa-clock"></i>
                            กำลังจะมาถึงใน {{ $booking->start_datetime->diffForHumans() }}
                        </div>
                    @endif
                </div>
            @elseif($booking->status === 'cancelled')
                <div class="status-alert cancelled">
                    <h4><i class="fas fa-times-circle me-2"></i>การจองถูกยกเลิกแล้ว</h4>
                    <p class="mb-0">การจองนี้ได้ถูกยกเลิกเรียบร้อยแล้ว</p>
                </div>
            @else
                <div class="status-alert completed">
                    <h4><i class="fas fa-check-double me-2"></i>การจองเสร็จสิ้นแล้ว</h4>
                    <p class="mb-0">การจองนี้ได้ดำเนินการเสร็จสิ้นแล้ว</p>
                    <div class="timeline-badge past mt-2">
                        <i class="fas fa-history"></i>
                        เสร็จสิ้นเมื่อ {{ $booking->end_datetime->diffForHumans() }}
                    </div>
                </div>
            @endif

            <!-- Main Information Card -->
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-info-circle"></i>
                    ข้อมูลการจอง
                </div>
                <div class="info-card-body">
                    <!-- Booking Reference & Status -->
                    <div class="info-section">
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-hashtag"></i>
                                    หมายเลขอ้างอิง
                                </div>
                                <div class="info-value large">{{ $booking->booking_ref }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-flag"></i>
                                    สถานะ
                                </div>
                                <div>
                                    @if($booking->status === 'confirmed')
                                        <span class="status-badge-large confirmed">
                                            <i class="fas fa-check-circle me-2"></i>ยืนยันแล้ว
                                        </span>
                                    @elseif($booking->status === 'cancelled')
                                        <span class="status-badge-large cancelled">
                                            <i class="fas fa-times-circle me-2"></i>ยกเลิก
                                        </span>
                                    @else
                                        <span class="status-badge-large completed">
                                            <i class="fas fa-check-double me-2"></i>เสร็จสิ้น
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Room Information -->
                    <div class="info-section">
                        <div class="info-section-title">
                            <i class="fas fa-door-open"></i>
                            ห้องประชุม
                        </div>
                        <div class="room-display">
                            <div class="room-name">{{ $booking->room->name }}</div>
                            @if($booking->room->description)
                                <div class="room-description">{{ $booking->room->description }}</div>
                            @endif
                            <div class="room-location">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $booking->room->location ?? 'ไม่ระบุสถานที่' }}
                            </div>
                        </div>
                    </div>

                    <!-- Date & Time -->
                    <div class="info-section">
                        <div class="info-section-title">
                            <i class="far fa-calendar-alt"></i>
                            วันที่และเวลา
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-calendar-plus"></i>
                                    เริ่มต้น
                                </div>
                                <div class="info-value">{{ $booking->start_datetime->format('H:i') }}</div>
                                <div class="info-subtext">{{ $booking->start_datetime->locale('th')->translatedFormat('l, d F Y') }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-calendar-check"></i>
                                    สิ้นสุด
                                </div>
                                <div class="info-value">{{ $booking->end_datetime->format('H:i') }}</div>
                                <div class="info-subtext">{{ $booking->end_datetime->locale('th')->translatedFormat('l, d F Y') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="info-section">
                        <div class="info-section-title">
                            <i class="fas fa-clipboard-list"></i>
                            รายละเอียดเพิ่มเติม
                        </div>
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">
                                    <i class="fas fa-users"></i>
                                    จำนวนผู้เข้าร่วม
                                </div>
                                <div class="info-value">{{ $booking->attendee_count }} คน</div>
                            </div>
                            @if($booking->decoration_theme)
                                <div class="info-item">
                                    <div class="info-label">
                                        <i class="fas fa-palette"></i>
                                        ธีมการตกแต่ง
                                    </div>
                                    <div class="info-value" style="font-size: 1.1rem;">{{ $booking->decoration_theme }}</div>
                                </div>
                            @endif
                        </div>

                        @if($booking->notes)
                            <div class="notes-display">
                                <div class="info-label mb-2">
                                    <i class="fas fa-sticky-note"></i>
                                    หมายเหตุเพิ่มเติม
                                </div>
                                <p>{{ $booking->notes }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Meta Information -->
                    <div class="meta-info">
                        <small>
                            <i class="fas fa-user me-2"></i><strong>ผู้จอง:</strong> {{ $booking->user->name }} ({{ $booking->user->email }})<br>
                            <i class="far fa-clock me-2"></i><strong>วันที่จอง:</strong> {{ $booking->created_at->locale('th')->translatedFormat('d M Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('guest.booking.history') }}" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i>กลับไปประวัติ
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

                <button onclick="window.print()" class="btn btn-print">
                    <i class="fas fa-print me-2"></i>พิมพ์
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
