<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>สถานะห้องประชุม - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .status-board {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .board-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .board-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
            margin: 0;
        }
        
        .current-time {
            font-size: 1.5rem;
            color: #666;
            margin-top: 10px;
        }
        
        .room-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .room-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .room-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 8px;
            height: 100%;
        }
        
        .room-card.status-available::before {
            background: #28a745;
        }
        
        .room-card.status-occupied::before {
            background: #dc3545;
        }
        
        .room-card.status-reserved::before {
            background: #17a2b8;
        }
        
        .room-card.status-warning::before {
            background: #ffc107;
        }
        
        .room-name {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .room-capacity {
            color: #666;
            margin-bottom: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 1.3rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 15px 0;
        }
        
        .status-badge.bg-success {
            background: #28a745 !important;
            color: white;
        }
        
        .status-badge.bg-danger {
            background: #dc3545 !important;
            color: white;
        }
        
        .status-badge.bg-info {
            background: #17a2b8 !important;
            color: white;
        }
        
        .status-badge.bg-warning {
            background: #ffc107 !important;
            color: #333;
        }
        
        .booking-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .booking-info strong {
            color: #667eea;
        }
        
        .time-display {
            font-size: 1.2rem;
            font-weight: bold;
            color: #dc3545;
        }
        
        .next-booking {
            border-top: 2px dashed #dee2e6;
            margin-top: 15px;
            padding-top: 15px;
        }
        
        .location-badge {
            background: #e9ecef;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            color: #666;
        }
        
        .last-updated {
            text-align: center;
            color: white;
            margin-top: 20px;
            font-size: 0.9rem;
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
    <div class="status-board">
        <!-- Header -->
        <div class="board-header text-center">
            <h1 class="board-title">
                <i class="fas fa-door-open me-3"></i>
                สถานะห้องประชุม
            </h1>
            <div class="current-time" id="currentTime">
                <i class="far fa-clock me-2"></i>
                <span id="timeDisplay"></span>
            </div>
        </div>
        
        <!-- Room Status Cards -->
        <div class="row" id="roomStatusContainer">
            @foreach($roomStatuses as $roomStatus)
                <div class="col-lg-6 col-xl-4">
                    <div class="room-card status-{{ $roomStatus['status'] }}" data-room-id="{{ $roomStatus['room']->id }}">
                        <div class="room-name">
                            <i class="fas fa-door-closed me-2"></i>
                            {{ $roomStatus['room']->name }}
                        </div>
                        
                        <div class="room-capacity">
                            <i class="fas fa-users me-2"></i>
                            ความจุ: {{ $roomStatus['room']->max_capacity }} คน
                            @if($roomStatus['room']->location)
                                <span class="location-badge ms-2">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $roomStatus['room']->location }}
                                </span>
                            @endif
                        </div>
                        
                        <div class="text-center">
                            <span class="status-badge bg-{{ $roomStatus['status_color'] }}">
                                @if($roomStatus['status'] === 'available')
                                    <i class="fas fa-check-circle me-2"></i>
                                @elseif($roomStatus['status'] === 'occupied')
                                    <i class="fas fa-times-circle me-2"></i>
                                @elseif($roomStatus['status'] === 'reserved')
                                    <i class="fas fa-calendar-check me-2"></i>
                                @endif
                                {{ $roomStatus['status_text'] }}
                            </span>
                        </div>
                        
                        @if($roomStatus['current_booking'])
                            <div class="booking-info">
                                <div class="mb-2">
                                    <strong>ผู้ใช้งานขณะนี้:</strong>
                                    <div class="mt-1">
                                        <i class="fas fa-user me-2"></i>
                                        {{ $roomStatus['current_user'] }}
                                    </div>
                                </div>
                                <div>
                                    <strong>สิ้นสุดเวลา:</strong>
                                    <div class="time-display mt-1">
                                        <i class="far fa-clock me-2"></i>
                                        {{ $roomStatus['end_time']->format('H:i') }} น.
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        @if($roomStatus['next_booking'])
                            <div class="booking-info next-booking">
                                <div class="mb-2">
                                    <strong>การจองถัดไป:</strong>
                                    <div class="mt-1">
                                        <i class="fas fa-user me-2"></i>
                                        {{ $roomStatus['next_user'] }}
                                    </div>
                                </div>
                                <div>
                                    <strong>เวลาเริ่มต้น:</strong>
                                    <div class="mt-1" style="color: #667eea; font-weight: bold;">
                                        <i class="far fa-clock me-2"></i>
                                        {{ $roomStatus['next_start_time']->format('H:i') }} น.
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="last-updated">
            <i class="fas fa-sync-alt me-2 pulse"></i>
            อัพเดทอัตโนมัติทุก 30 วินาที | อัพเดทล่าสุด: <span id="lastUpdated"></span>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
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
            document.getElementById('timeDisplay').textContent = now.toLocaleString('th-TH', options);
        }
        
        // Update room statuses via API
        async function updateRoomStatuses() {
            try {
                const response = await fetch('/api/room-status');
                const data = await response.json();
                
                // Update last updated time
                const updatedAt = new Date(data.updated_at);
                document.getElementById('lastUpdated').textContent = updatedAt.toLocaleTimeString('th-TH');
                
                // Update room cards
                data.rooms.forEach(room => {
                    const card = document.querySelector(`[data-room-id="${room.id}"]`);
                    if (!card) return;
                    
                    // Update status classes
                    card.className = `room-card status-${room.status}`;
                    
                    // Update status badge
                    const badge = card.querySelector('.status-badge');
                    badge.className = `status-badge bg-${room.status_color}`;
                    
                    let icon = 'fa-check-circle';
                    if (room.status === 'occupied') {
                        icon = 'fa-times-circle';
                    } else if (room.status === 'reserved') {
                        icon = 'fa-calendar-check';
                    }
                    badge.innerHTML = `<i class="fas ${icon} me-2"></i>${room.status_text}`;
                });
                
            } catch (error) {
                console.error('Error updating room statuses:', error);
            }
        }
        
        // Initialize
        updateCurrentTime();
        updateRoomStatuses();
        
        // Update time every second
        setInterval(updateCurrentTime, 1000);
        
        // Update room statuses every 30 seconds
        setInterval(updateRoomStatuses, 30000);
    </script>
</body>
</html>
