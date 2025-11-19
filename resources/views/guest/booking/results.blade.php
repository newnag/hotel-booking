@extends('layouts.app')

@section('title', 'ห้องประชุมที่ว่าง')

@push('styles')
<style>
    .results-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
    }
    
    .results-header h2 {
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
    }
    
    .search-criteria-card {
        background: rgba(255, 255, 255, 0.95);
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .search-criteria-header {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        border-radius: 15px 15px 0 0;
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .search-criteria-body {
        padding: 1.5rem;
    }
    
    .criteria-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        background: #f8f9fa;
        border-radius: 10px;
        margin-bottom: 0.75rem;
    }
    
    .criteria-item:last-child {
        margin-bottom: 0;
    }
    
    .criteria-item i {
        font-size: 1.5rem;
        margin-right: 1rem;
        color: #667eea;
        width: 30px;
        text-align: center;
    }
    
    .criteria-label {
        font-weight: 600;
        color: #666;
        margin-right: 0.5rem;
    }
    
    .criteria-value {
        color: #333;
        font-weight: 500;
    }
    
    .modify-search-btn {
        background: white;
        color: #667eea;
        border: 2px solid #667eea;
        border-radius: 25px;
        padding: 0.5rem 1.5rem;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .modify-search-btn:hover {
        background: #667eea;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .results-count-badge {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 1.25rem 2rem;
        border-radius: 15px;
        font-size: 1.3rem;
        font-weight: bold;
        box-shadow: 0 5px 20px rgba(56, 239, 125, 0.3);
        margin-bottom: 2rem;
        display: inline-flex;
        align-items: center;
    }
    
    .results-count-badge i {
        font-size: 2rem;
        margin-right: 1rem;
    }
    
    .room-available-card {
        border: none;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        height: 100%;
        background: white;
    }
    
    .room-available-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
    }
    
    .room-status-badge {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .room-status-badge i {
        font-size: 1.5rem;
        margin-right: 0.75rem;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.6; }
    }
    
    .room-card-body {
        padding: 2rem;
    }
    
    .room-name {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        margin-bottom: 1.5rem;
    }
    
    .room-details {
        margin-bottom: 1.5rem;
    }
    
    .room-detail-item {
        display: flex;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .room-detail-item:last-child {
        border-bottom: none;
    }
    
    .room-detail-item i {
        width: 30px;
        text-align: center;
        color: #667eea;
        font-size: 1.2rem;
        margin-right: 1rem;
    }
    
    .room-detail-label {
        color: #666;
        margin-right: 0.5rem;
    }
    
    .room-detail-value {
        color: #333;
        font-weight: 600;
    }
    
    .book-room-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 50px;
        padding: 1rem;
        font-size: 1.1rem;
        font-weight: 600;
        width: 100%;
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
    
    .book-room-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    }
    
    .book-room-btn i {
        margin-right: 0.5rem;
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
        color: #ffa726;
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
        background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);
        border: none;
        border-radius: 50px;
        padding: 1rem 3rem;
        font-size: 1.2rem;
        font-weight: 600;
        color: white;
        box-shadow: 0 5px 15px rgba(255, 167, 38, 0.4);
        transition: all 0.3s ease;
    }
    
    .empty-state-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 167, 38, 0.6);
        color: white;
    }
    
    .floor-badge {
        display: inline-block;
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="results-header">
        <h2><i class="fas fa-door-open me-3"></i>ห้องประชุมที่ว่าง</h2>
    </div>

    <!-- Search Criteria Card -->
    <div class="card search-criteria-card">
        <div class="search-criteria-header">
            <i class="fas fa-search me-2"></i>
            เงื่อนไขการค้นหา
        </div>
        <div class="search-criteria-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="criteria-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <span class="criteria-label">จำนวนผู้เข้าร่วม:</span>
                            <span class="criteria-value">{{ $searchParams['attendee_count'] }} คน</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="criteria-item">
                        <i class="far fa-calendar-alt"></i>
                        <div>
                            <span class="criteria-label">เริ่มต้น:</span>
                            <span class="criteria-value">{{ \Carbon\Carbon::parse($searchParams['start_datetime'])->locale('th')->translatedFormat('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="criteria-item">
                        <i class="far fa-calendar-check"></i>
                        <div>
                            <span class="criteria-label">สิ้นสุด:</span>
                            <span class="criteria-value">{{ \Carbon\Carbon::parse($searchParams['end_datetime'])->locale('th')->translatedFormat('d M Y H:i') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-3">
                <a href="{{ route('guest.booking.search') }}" class="btn modify-search-btn">
                    <i class="fas fa-edit me-2"></i>แก้ไขการค้นหา
                </a>
            </div>
        </div>
    </div>

    <!-- Results -->
    @if($availableRooms->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="fas fa-calendar-times"></i>
            </div>
            <h3>ไม่พบห้องว่าง</h3>
            <p>ขออภัย ไม่มีห้องประชุมที่ตรงตามเงื่อนไขของคุณในช่วงเวลาที่เลือก</p>
            <a href="{{ route('guest.booking.search') }}" class="btn empty-state-btn">
                <i class="fas fa-search me-2"></i>ลองค้นหาวันเวลาอื่น
            </a>
        </div>
    @else
        <!-- Results Count -->
        <div class="text-center mb-4">
            <div class="results-count-badge">
                <i class="fas fa-check-circle"></i>
                <span>พบห้องว่าง <strong>{{ $availableRooms->count() }}</strong> ห้อง</span>
            </div>
        </div>

        <!-- Room Cards -->
        <div class="row">
            @foreach($availableRooms as $room)
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card room-available-card">
                        <div class="room-status-badge">
                            <i class="fas fa-check-circle"></i>
                            <span>ห้องว่าง</span>
                        </div>
                        <div class="room-card-body">
                            <h3 class="room-name">{{ $room->name }}</h3>
                            
                            <div class="room-details">
                                <div class="room-detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span class="room-detail-label">ตำแหน่ง:</span>
                                    <span class="room-detail-value">{{ $room->location ?? 'N/A' }}</span>
                                </div>
                                <div class="room-detail-item">
                                    <i class="fas fa-users"></i>
                                    <span class="room-detail-label">ความจุสูงสุด:</span>
                                    <span class="room-detail-value">{{ $room->max_capacity }} คน</span>
                                </div>
                                @if($room->hourly_rate > 0)
                                <div class="room-detail-item">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span class="room-detail-label">ราคา:</span>
                                    <span class="room-detail-value">{{ number_format($room->hourly_rate, 2) }} บาท/ชั่วโมง</span>
                                </div>
                                @endif
                                @if($room->description)
                                <div class="room-detail-item">
                                    <i class="fas fa-info-circle"></i>
                                    <span class="room-detail-value">{{ Str::limit($room->description, 100) }}</span>
                                </div>
                                @endif
                            </div>

                            <form action="{{ route('guest.booking.create', $room) }}" method="GET">
                                <input type="hidden" name="attendee_count" value="{{ $searchParams['attendee_count'] }}">
                                <input type="hidden" name="start_datetime" value="{{ $searchParams['start_datetime'] }}">
                                <input type="hidden" name="end_datetime" value="{{ $searchParams['end_datetime'] }}">
                                <button type="submit" class="btn btn-primary book-room-btn">
                                    <i class="fas fa-calendar-check"></i>จองห้องนี้
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
