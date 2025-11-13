@props(['room'])

<div class="card h-100">
    @if($room->is_active)
        <div class="card-header bg-success text-white">
            <i class="fas fa-check-circle"></i> พร้อมใช้งาน
        </div>
    @else
        <div class="card-header bg-secondary text-white">
            <i class="fas fa-times-circle"></i> ไม่พร้อมใช้งาน
        </div>
    @endif
    
    <div class="card-body">
        <h5 class="card-title">{{ $room->name }}</h5>
        <p class="card-text text-muted">{{ Str::limit($room->description, 100) }}</p>
        
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="badge bg-primary text-white" style="font-size: 0.9rem; padding: 8px 12px;">
                <i class="fas fa-users"></i> จำนวนผู้เข้าร่วมสูงสุด: {{ $room->max_capacity }}
            </span>
        </div>
        
        <div class="mb-2">
            <i class="fas fa-map-marker-alt text-primary"></i>
            <span class="text-muted">{{ $room->location }}</span>
        </div>
    </div>
    
    <div class="card-footer">
        {{ $slot }}
    </div>
</div>
