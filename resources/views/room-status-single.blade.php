<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $room->name }} - สถานะห้องประชุม</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            padding: 15px;
            overflow: hidden;
        }
        
        .room-display {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .main-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .room-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .room-name {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .room-info {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        .current-time {
            font-size: 1.1rem;
            color: white;
            text-align: center;
            padding: 12px;
            background: rgba(0,0,0,0.2);
            flex-shrink: 0;
        }
        
        .content-wrapper {
            flex: 1;
            display: flex;
            overflow: hidden;
        }
        
        .status-section {
            flex: 0 0 45%;
            padding: 25px;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .status-indicator {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: pulse-slow 3s infinite;
        }
        
        .status-indicator.status-available {
            background: #28a745;
        }
        
        .status-indicator.status-occupied {
            background: #dc3545;
        }
        
        .status-indicator.status-reserved {
            background: #17a2b8;
        }
        
        .status-indicator.status-warning {
            background: #ffc107;
        }
        
        .status-indicator i {
            font-size: 4rem;
            color: white;
        }
        
        .status-text {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 15px;
        }
        
        .status-text.text-success {
            color: #28a745;
        }
        
        .status-text.text-danger {
            color: #dc3545;
        }
        
        .status-text.text-info {
            color: #17a2b8;
        }
        
        .status-text.text-warning {
            color: #ffc107;
        }
        
        .current-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin: 15px 0;
        }
        
        .info-label {
            font-size: 0.95rem;
            color: #666;
            margin-bottom: 8px;
        }
        
        .info-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        
        .time-display {
            font-size: 2rem;
            font-weight: bold;
            color: #dc3545;
        }
        
        .schedule-section {
            flex: 0 0 55%;
            padding: 25px;
            background: #f8f9fa;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .schedule-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .schedule-list {
            flex: 1;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .schedule-list::-webkit-scrollbar {
            width: 8px;
        }
        
        .schedule-list::-webkit-scrollbar-track {
            background: #e9ecef;
            border-radius: 10px;
        }
        
        .schedule-list::-webkit-scrollbar-thumb {
            background: #667eea;
            border-radius: 10px;
        }
        
        .schedule-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .schedule-item.active {
            border-left: 5px solid #dc3545;
            background: #fff5f5;
        }
        
        .schedule-time {
            font-size: 1.1rem;
            font-weight: bold;
            color: #667eea;
            min-width: 140px;
        }
        
        .schedule-user {
            font-size: 1rem;
            color: #333;
            flex: 1;
            padding-left: 15px;
        }
        
        .current-badge {
            background: #dc3545;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .footer {
            text-align: center;
            padding: 12px;
            color: #666;
            font-size: 0.85rem;
            flex-shrink: 0;
        }
        
        @keyframes pulse-slow {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 15px 50px rgba(0,0,0,0.3);
            }
        }
        
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="room-display">
        <div class="main-card">
            <!-- Header -->
            <div class="room-header">
                <div class="room-name">
                    <i class="fas fa-door-open me-3"></i>
                    {{ $room->name }}
                </div>
                <div class="room-info">
                    <i class="fas fa-users me-2"></i>
                    ความจุ: {{ $room->max_capacity }} คน
                    @if($room->location)
                        <span class="ms-3">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            {{ $room->location }}
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="current-time">
                <i class="far fa-clock me-2"></i>
                <span id="currentTime"></span>
            </div>
            
            <!-- Content Wrapper - Split view -->
            <div class="content-wrapper">
                <!-- Status Section -->
                <div class="status-section">
                    <div>
                        <div class="status-indicator status-{{ $status }}" id="statusIndicator">
                            @if($status === 'available')
                                <i class="fas fa-check-circle"></i>
                            @elseif($status === 'occupied')
                                <i class="fas fa-times-circle"></i>
                            @elseif($status === 'reserved')
                                <i class="fas fa-calendar-check"></i>
                            @endif
                        </div>
                        
                        <div class="status-text text-{{ $statusColor }}" id="statusText">
                            {{ $statusText }}
                        </div>
                        
                        @if($currentBooking)
                            <div class="current-info">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="info-label">
                                            <i class="fas fa-user me-2"></i>
                                            ผู้ใช้งานขณะนี้
                                        </div>
                                        <div class="info-value" id="currentUser">
                                            {{ $currentBooking->user->name }}
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <div class="info-label">
                                            <i class="far fa-clock me-2"></i>
                                            ใช้งานจนถึง
                                        </div>
                                        <div class="time-display" id="endTime">
                                            {{ $currentBooking->end_datetime->format('H:i') }} น.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($nextBooking && $status === 'available')
                            <div class="current-info">
                                <div class="info-label">
                                    <i class="fas fa-clock me-2"></i>
                                    การจองถัดไป
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <div class="info-label">ผู้จอง</div>
                                        <div class="info-value" id="nextUser">
                                            {{ $nextBooking->user->name }}
                                        </div>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <div class="info-label">เวลาเริ่มต้น</div>
                                        <div class="info-value" style="color: #667eea;" id="nextStartTime">
                                            {{ $nextBooking->start_datetime->format('H:i') }} น.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Schedule Section -->
                @if($todayBookings->count() > 0)
                    <div class="schedule-section">
                        <div class="schedule-title">
                            <i class="fas fa-calendar-day me-2"></i>
                            ตารางการจองวันนี้
                        </div>
                        
                        <div class="schedule-list" id="scheduleList">
                            @foreach($todayBookings as $booking)
                                @php
                                    $isCurrent = $currentBooking && $booking->id === $currentBooking->id;
                                @endphp
                                <div class="schedule-item {{ $isCurrent ? 'active' : '' }}">
                                    <div class="schedule-time">
                                        <i class="far fa-clock me-2"></i>
                                        {{ $booking->start_datetime->format('H:i') }} - {{ $booking->end_datetime->format('H:i') }}
                                    </div>
                                    <div class="schedule-user">
                                        <i class="fas fa-user me-2"></i>
                                        {{ $booking->user->name }}
                                    </div>
                                    @if($isCurrent)
                                        <span class="current-badge">
                                            <i class="fas fa-circle me-1"></i>
                                            กำลังใช้งาน
                                        </span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            <!-- Footer -->
            <div class="footer">
                <i class="fas fa-sync-alt me-2 pulse"></i>
                อัพเดทอัตโนมัติทุก 30 วินาที | 
                อัพเดทล่าสุด: <span id="lastUpdated"></span>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const roomId = {{ $room->id }};
        
        // Update current time display
        function updateCurrentTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            };
            document.getElementById('currentTime').textContent = now.toLocaleString('th-TH', options);
        }
        
        // Update room status via API
        async function updateRoomStatus() {
            try {
                const response = await fetch(`/api/room-status/${roomId}`);
                const data = await response.json();
                
                // Update last updated time
                const updatedAt = new Date(data.updated_at);
                document.getElementById('lastUpdated').textContent = updatedAt.toLocaleTimeString('th-TH');
                
                // Update status indicator
                const indicator = document.getElementById('statusIndicator');
                indicator.className = `status-indicator status-${data.status}`;
                
                let iconClass = 'fa-check-circle';
                if (data.status === 'occupied') {
                    iconClass = 'fa-times-circle';
                } else if (data.status === 'reserved') {
                    iconClass = 'fa-calendar-check';
                }
                indicator.innerHTML = `<i class="fas ${iconClass}"></i>`;
                
                // Update status text
                const statusText = document.getElementById('statusText');
                statusText.textContent = data.status_text;
                statusText.className = `status-text text-${data.status_color}`;
                
            } catch (error) {
                console.error('Error updating room status:', error);
            }
        }
        
        // Initialize
        updateCurrentTime();
        updateRoomStatus();
        
        // Update time every second
        setInterval(updateCurrentTime, 1000);
        
        // Update room status every 30 seconds
        setInterval(updateRoomStatus, 30000);
    </script>
</body>
</html>
