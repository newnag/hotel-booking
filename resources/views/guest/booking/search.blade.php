@extends('layouts.app')

@section('title', 'ค้นหาห้องประชุม')

@push('styles')
<style>
    .search-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 2rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        text-align: center;
    }
    
    .search-header h2 {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .search-header p {
        font-size: 1.1rem;
        opacity: 0.9;
        margin: 0;
    }
    
    .search-form-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .search-form-card .card-body {
        padding: 3rem;
    }
    
    .form-label-modern {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.75rem;
        font-size: 1.05rem;
    }
    
    .form-control-modern {
        border: 2px solid #e0e0e0;
        border-radius: 10px;
        padding: 0.875rem 1rem;
        font-size: 1rem;
        transition: all 0.3s ease;
    }
    
    .form-control-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .input-icon {
        position: relative;
    }
    
    .input-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #667eea;
        font-size: 1.2rem;
    }
    
    .input-icon .form-control-modern {
        padding-left: 45px;
    }
    
    .form-help-text {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
    }
    
    .form-help-text i {
        margin-right: 0.5rem;
        color: #667eea;
    }
    
    .search-button {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 50px;
        padding: 1rem 3rem;
        font-size: 1.2rem;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }
    
    .search-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.6);
    }
    
    .tips-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    
    .tips-card-header {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        padding: 1.5rem;
        font-size: 1.3rem;
        font-weight: 600;
    }
    
    .tips-card-header i {
        margin-right: 0.75rem;
        font-size: 1.5rem;
    }
    
    .tips-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .tips-list li {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }
    
    .tips-list li:last-child {
        border-bottom: none;
    }
    
    .tips-list li:hover {
        background-color: #f8f9fa;
    }
    
    .tips-list li:before {
        content: '✓';
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
        border-radius: 50%;
        margin-right: 1rem;
        font-weight: bold;
        flex-shrink: 0;
    }
    
    .quick-select-section {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .quick-select-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
    }
    
    .quick-select-btn {
        border: 2px solid #667eea;
        color: #667eea;
        border-radius: 25px;
        padding: 0.5rem 1.5rem;
        margin: 0.25rem;
        transition: all 0.3s ease;
    }
    
    .quick-select-btn:hover {
        background: #667eea;
        color: white;
    }
    
    .feature-highlight {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 15px;
        margin-bottom: 2rem;
        text-align: center;
    }
    
    .feature-highlight h4 {
        margin: 0;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="search-header">
        <h2><i class="fas fa-search me-3"></i>ค้นหาห้องประชุมที่ว่าง</h2>
        <p>ค้นหาและจองห้องประชุมที่เหมาะสมกับความต้องการของคุณ</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Feature Highlight -->
            <div class="feature-highlight">
                <h4>
                    <i class="fas fa-bolt me-2"></i>
                    จองได้ทันที ไม่ต้องรอ 24 ชั่วโมง!
                </h4>
            </div>

            <!-- Search Form -->
            <div class="card search-form-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('guest.booking.search.results') }}" id="searchForm">
                        @csrf

                        <!-- Number of Attendees -->
                        <div class="mb-4">
                            <label for="attendee_count" class="form-label-modern">
                                <i class="fas fa-users me-2"></i>
                                จำนวนผู้เข้าร่วมประชุม
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-icon">
                                <i class="fas fa-user-friends"></i>
                                <input type="number" 
                                       class="form-control form-control-modern @error('attendee_count') is-invalid @enderror" 
                                       id="attendee_count" 
                                       name="attendee_count" 
                                       value="{{ old('attendee_count') }}" 
                                       min="1" 
                                       placeholder="เช่น 10"
                                       required>
                                @error('attendee_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-help-text">
                                <i class="fas fa-info-circle"></i>
                                ระบุจำนวนผู้เข้าร่วมประชุมโดยประมาณ
                            </div>
                            
                            <!-- Quick Select Buttons -->
                            <div class="mt-3">
                                <button type="button" class="btn quick-select-btn btn-sm" onclick="setAttendees(5)">
                                    <i class="fas fa-user me-1"></i>5 คน
                                </button>
                                <button type="button" class="btn quick-select-btn btn-sm" onclick="setAttendees(10)">
                                    <i class="fas fa-users me-1"></i>10 คน
                                </button>
                                <button type="button" class="btn quick-select-btn btn-sm" onclick="setAttendees(20)">
                                    <i class="fas fa-users me-1"></i>20 คน
                                </button>
                                <button type="button" class="btn quick-select-btn btn-sm" onclick="setAttendees(50)">
                                    <i class="fas fa-users me-1"></i>50 คน
                                </button>
                            </div>
                        </div>

                        <!-- Start Date & Time -->
                        <div class="mb-4">
                            <label for="start_datetime" class="form-label-modern">
                                <i class="fas fa-calendar-plus me-2"></i>
                                วันที่และเวลาเริ่มต้น
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-icon">
                                <i class="far fa-calendar-alt"></i>
                                <input type="datetime-local" 
                                       class="form-control form-control-modern @error('start_datetime') is-invalid @enderror" 
                                       id="start_datetime" 
                                       name="start_datetime" 
                                       value="{{ old('start_datetime') }}" 
                                       min="{{ now()->format('Y-m-d\TH:i') }}"
                                       required>
                                @error('start_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-help-text">
                                <i class="fas fa-check-circle"></i>
                                จองได้ทันที ไม่ต้องจองล่วงหน้า
                            </div>
                        </div>

                        <!-- End Date & Time -->
                        <div class="mb-4">
                            <label for="end_datetime" class="form-label-modern">
                                <i class="fas fa-calendar-check me-2"></i>
                                วันที่และเวลาสิ้นสุด
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-icon">
                                <i class="far fa-calendar-times"></i>
                                <input type="datetime-local" 
                                       class="form-control form-control-modern @error('end_datetime') is-invalid @enderror" 
                                       id="end_datetime" 
                                       name="end_datetime" 
                                       value="{{ old('end_datetime') }}" 
                                       required>
                                @error('end_datetime')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-help-text">
                                <i class="fas fa-hourglass-half"></i>
                                ระยะเวลาการจอง: ขั้นต่ำ 1 ชั่วโมง, สูงสุด 8 ชั่วโมง
                            </div>
                            
                            <!-- Quick Duration Buttons -->
                            <div class="mt-3">
                                <button type="button" class="btn quick-select-btn btn-sm" onclick="setDuration(1)">
                                    <i class="far fa-clock me-1"></i>1 ชม.
                                </button>
                                <button type="button" class="btn quick-select-btn btn-sm" onclick="setDuration(2)">
                                    <i class="far fa-clock me-1"></i>2 ชม.
                                </button>
                                <button type="button" class="btn quick-select-btn btn-sm" onclick="setDuration(3)">
                                    <i class="far fa-clock me-1"></i>3 ชม.
                                </button>
                                <button type="button" class="btn quick-select-btn btn-sm" onclick="setDuration(4)">
                                    <i class="far fa-clock me-1"></i>4 ชม.
                                </button>
                            </div>
                        </div>

                        <!-- Search Button -->
                        <div class="text-center pt-3">
                            <button type="submit" class="btn btn-primary search-button">
                                <i class="fas fa-search me-2"></i>
                                ค้นหาห้องว่าง
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tips Section -->
            <div class="card tips-card mt-4">
                <div class="tips-card-header">
                    <i class="fas fa-lightbulb"></i>
                    คำแนะนำในการจองห้องประชุม
                </div>
                <div class="card-body p-0">
                    <ul class="tips-list">
                        <li>จองได้ทันที ไม่ต้องรอนาน - เหมาะสำหรับการประชุมฉุกเฉิน</li>
                        <li>สามารถจองล่วงหน้าได้สูงสุด 90 วัน</li>
                        <li>เลือกห้องที่มีความจุเหมาะสมกับจำนวนผู้เข้าร่วม</li>
                        <li>สามารถเลือกธีมการตกแต่งได้หลังจากเลือกห้อง</li>
                        <li>เวลาทำการ: 08:00 - 20:00 น. (จันทร์ - ศุกร์)</li>
                    </ul>
                </div>
            </div>

            <!-- Available Rooms Section -->
            @if($rooms->count() > 0)
            <div class="card tips-card mt-4">
                <div class="tips-card-header">
                    <i class="fas fa-door-open"></i>
                    ห้องประชุมที่มีให้บริการ ({{ $rooms->count() }} ห้อง)
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ชื่อห้อง</th>
                                    <th class="text-center">ความจุ</th>
                                    <th class="text-center">ราคา/ชม.</th>
                                    <th>สถานที่</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rooms as $room)
                                <tr>
                                    <td>
                                        <strong>{{ $room->name }}</strong>
                                        @if($room->description)
                                        <br>
                                        <small class="text-muted">{{ Str::limit($room->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge" style="background-color: #17a2b8; color: white; font-size: 0.95rem; padding: 0.5rem 0.75rem;">
                                            <i class="fas fa-users"></i> {{ $room->max_capacity }} คน
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($room->hourly_rate > 0)
                                        <span class="badge" style="background-color: #28a745; color: white; font-size: 0.95rem; padding: 0.5rem 0.75rem;">
                                            <i class="fas fa-money-bill-wave"></i> {{ number_format($room->hourly_rate, 2) }} ฿
                                        </span>
                                        @else
                                        <span class="badge" style="background-color: #6c757d; color: white; font-size: 0.95rem; padding: 0.5rem 0.75rem;">
                                            <i class="fas fa-gift"></i> ฟรี
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt text-primary"></i>
                                        {{ $room->location ?? 'N/A' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>หมายเหตุ:</strong> ราคาและความพร้อมของห้องอาจแตกต่างกันตามวันและเวลาที่ต้องการจอง กรุณากรอกข้อมูลด้านบนเพื่อค้นหาห้องว่าง
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Set attendee count
function setAttendees(count) {
    document.getElementById('attendee_count').value = count;
}

// Set duration
function setDuration(hours) {
    const startInput = document.getElementById('start_datetime');
    if (!startInput.value) {
        alert('กรุณาเลือกวันที่และเวลาเริ่มต้นก่อน');
        return;
    }
    
    const startTime = new Date(startInput.value);
    const endTime = new Date(startTime.getTime() + hours * 60 * 60 * 1000);
    
    const year = endTime.getFullYear();
    const month = String(endTime.getMonth() + 1).padStart(2, '0');
    const day = String(endTime.getDate()).padStart(2, '0');
    const hour = String(endTime.getHours()).padStart(2, '0');
    const minutes = String(endTime.getMinutes()).padStart(2, '0');
    
    document.getElementById('end_datetime').value = `${year}-${month}-${day}T${hour}:${minutes}`;
}

// Auto-set end time to 2 hours after start time
document.getElementById('start_datetime').addEventListener('change', function() {
    const startTime = new Date(this.value);
    const endTime = new Date(startTime.getTime() + 2 * 60 * 60 * 1000); // Add 2 hours
    
    const year = endTime.getFullYear();
    const month = String(endTime.getMonth() + 1).padStart(2, '0');
    const day = String(endTime.getDate()).padStart(2, '0');
    const hours = String(endTime.getHours()).padStart(2, '0');
    const minutes = String(endTime.getMinutes()).padStart(2, '0');
    
    document.getElementById('end_datetime').value = `${year}-${month}-${day}T${hours}:${minutes}`;
});

// Form validation
document.getElementById('searchForm').addEventListener('submit', function(e) {
    const attendeeCount = document.getElementById('attendee_count').value;
    const startDatetime = document.getElementById('start_datetime').value;
    const endDatetime = document.getElementById('end_datetime').value;
    
    if (!attendeeCount || !startDatetime || !endDatetime) {
        e.preventDefault();
        alert('กรุณากรอกข้อมูลให้ครบถ้วน');
        return;
    }
    
    const start = new Date(startDatetime);
    const end = new Date(endDatetime);
    const diffHours = (end - start) / (1000 * 60 * 60);
    
    if (diffHours < 1) {
        e.preventDefault();
        alert('ระยะเวลาการจองต้องอย่างน้อย 1 ชั่วโมง');
        return;
    }
    
    if (diffHours > 8) {
        e.preventDefault();
        alert('ระยะเวลาการจองไม่เกิน 8 ชั่วโมง');
        return;
    }
});
</script>
@endpush
@endsection
