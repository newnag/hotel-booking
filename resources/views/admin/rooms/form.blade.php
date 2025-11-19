@extends('layouts.admin')

@section('title', $room ? 'แก้ไขห้องประชุม' : 'เพิ่มห้องประชุมใหม่')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $room ? 'แก้ไขห้องประชุม' : 'เพิ่มห้องประชุมใหม่' }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าแรก</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">จัดการห้องประชุม</a></li>
                    <li class="breadcrumb-item active">{{ $room ? 'แก้ไข' : 'เพิ่มใหม่' }}</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-door-open"></i>
                            {{ $room ? 'แก้ไขข้อมูลห้องประชุม' : 'กรอกข้อมูลห้องประชุม' }}
                        </h3>
                    </div>

                    <form action="{{ $room ? route('admin.rooms.update', $room) : route('admin.rooms.store') }}" 
                          method="POST" 
                          enctype="multipart/form-data">
                        @csrf
                        @if($room)
                            @method('PUT')
                        @endif

                        <div class="card-body">
                            {{-- Room Name --}}
                            <div class="form-group">
                                <label for="name">
                                    ชื่อห้องประชุม <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $room->name ?? '') }}"
                                       placeholder="เช่น Conference Room A"
                                       required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Description --}}
                            <div class="form-group">
                                <label for="description">คำอธิบาย</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="4"
                                          placeholder="อธิบายรายละเอียดห้องประชุม อุปกรณ์ หรือข้อมูลที่สำคัญ...">{{ old('description', $room->description ?? '') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Capacity --}}
                            <div class="form-group">
                                <label for="max_capacity">
                                    ความจุ (จำนวนที่นั่ง) <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control @error('max_capacity') is-invalid @enderror" 
                                       id="max_capacity" 
                                       name="max_capacity" 
                                       value="{{ old('max_capacity', $room->max_capacity ?? '') }}"
                                       min="1"
                                       max="1000"
                                       placeholder="เช่น 50"
                                       required>
                                @error('max_capacity')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                                <small class="form-text text-muted">
                                    จำนวนผู้เข้าร่วมประชุมสูงสุดที่สามารถรองรับได้
                                </small>
                            </div>

                            {{-- Location --}}
                            <div class="form-group">
                                <label for="location">ตำแหน่ง/สถานที่</label>
                                <input type="text" 
                                       class="form-control @error('location') is-invalid @enderror" 
                                       id="location" 
                                       name="location" 
                                       value="{{ old('location', $room->location ?? '') }}"
                                       placeholder="เช่น ชั้น 5 อาคาร A">
                                @error('location')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Hourly Rate --}}
                            <div class="form-group">
                                <label for="hourly_rate">ราคาต่อชั่วโมง (บาท)</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('hourly_rate') is-invalid @enderror" 
                                           id="hourly_rate" 
                                           name="hourly_rate" 
                                           value="{{ old('hourly_rate', $room->hourly_rate ?? '0') }}"
                                           min="0"
                                           step="0.01"
                                           placeholder="0.00">
                                    <div class="input-group-append">
                                        <span class="input-group-text">฿</span>
                                    </div>
                                    @error('hourly_rate')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">
                                    ราคาค่าเช่าห้องต่อชั่วโมง (ถ้าไม่มีค่าใช้จ่าย ให้ใส่ 0)
                                </small>
                            </div>

                            {{-- Image Upload --}}
                            <div class="form-group">
                                <label for="image">รูปภาพห้องประชุม</label>
                                
                                @if($room && $room->image_path)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $room->image_path) }}" 
                                             alt="{{ $room->name }}" 
                                             class="img-thumbnail"
                                             style="max-width: 300px; max-height: 200px;">
                                        <p class="text-muted small">รูปภาพปัจจุบัน</p>
                                    </div>
                                @endif

                                <div class="custom-file">
                                    <input type="file" 
                                           class="custom-file-input @error('image') is-invalid @enderror" 
                                           id="image" 
                                           name="image"
                                           accept="image/jpeg,image/jpg,image/png,image/webp"
                                           onchange="previewImage(event)">
                                    <label class="custom-file-label" for="image">
                                        {{ $room ? 'เลือกรูปใหม่ (ถ้าต้องการเปลี่ยน)' : 'เลือกรูปภาพ' }}
                                    </label>
                                    @error('image')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <small class="form-text text-muted">
                                    ไฟล์ประเภท JPEG, JPG, PNG, WEBP (ขนาดไม่เกิน 2MB)
                                </small>

                                {{-- Image Preview --}}
                                <div id="imagePreview" class="mt-2" style="display: none;">
                                    <img id="preview" src="" alt="Preview" class="img-thumbnail" style="max-width: 300px; max-height: 200px;">
                                </div>
                            </div>

                            {{-- Active Status --}}
                            <div class="form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="is_active" 
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $room->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">
                                        เปิดใช้งานห้องประชุม
                                    </label>
                                </div>
                                <small class="form-text text-muted">
                                    ห้องที่ปิดใช้งานจะไม่แสดงในระบบการจอง
                                </small>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ $room ? 'บันทึกการแก้ไข' : 'สร้างห้องประชุม' }}
                            </button>
                            <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> ยกเลิก
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-md-4">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle"></i> คำแนะนำ
                        </h3>
                    </div>
                    <div class="card-body">
                        <p><strong>การตั้งชื่อห้องประชุม:</strong></p>
                        <ul>
                            <li>ใช้ชื่อที่สื่อความหมายชัดเจน</li>
                            <li>ควรระบุขนาดหรือประเภทของห้อง</li>
                            <li>ตัวอย่าง: "ห้องประชุมใหญ่ A", "ห้อง VIP 1"</li>
                        </ul>

                        <p><strong>คำอธิบาย:</strong></p>
                        <ul>
                            <li>ระบุอุปกรณ์ที่มีในห้อง (โปรเจคเตอร์, ไวท์บอร์ด)</li>
                            <li>บอกลักษณะพิเศษของห้อง</li>
                            <li>ข้อมูลที่ผู้จองควรทราบ</li>
                        </ul>

                        <p><strong>รูปภาพ:</strong></p>
                        <ul>
                            <li>ควรใช้รูปที่แสดงห้องชัดเจน</li>
                            <li>ขนาดไฟล์ไม่เกิน 2MB</li>
                            <li>รูปจะช่วยให้ผู้จองตัดสินใจได้ง่าย</li>
                        </ul>
                    </div>
                </div>

                @if($room)
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar"></i> สถิติการใช้งาน
                            </h3>
                        </div>
                        <div class="card-body">
                            <p><strong>จำนวนการจอง:</strong> {{ $room->bookings()->count() }} ครั้ง</p>
                            <p><strong>การจองที่กำลังจะมาถึง:</strong> 
                                {{ $room->bookings()->where('start_datetime', '>', now())->whereIn('status', ['confirmed'])->count() }} ครั้ง
                            </p>
                            <p class="text-muted small mb-0">
                                <i class="fas fa-info-circle"></i> 
                                หากมีการจองในอนาคต ควรระมัดระวังในการแก้ไขข้อมูลห้อง
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
// Custom file input label
$('.custom-file-input').on('change', function() {
    let fileName = $(this).val().split('\\').pop();
    $(this).next('.custom-file-label').html(fileName);
});

// Image preview
function previewImage(event) {
    const input = event.target;
    const preview = document.getElementById('preview');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
