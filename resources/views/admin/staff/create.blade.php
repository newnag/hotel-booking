@extends('layouts.admin')

@section('title', 'เพิ่มพนักงานใหม่')

@section('page-title', 'เพิ่มพนักงานใหม่')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.staff.index') }}">จัดการพนักงาน</a></li>
    <li class="breadcrumb-item active">เพิ่มใหม่</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ข้อมูลพนักงาน</h3>
            </div>
            <form action="{{ route('admin.staff.store') }}" method="POST">
                @csrf
                
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">ชื่อ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">อีเมล <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="line_user_id">Line User ID</label>
                        <input type="text" class="form-control @error('line_user_id') is-invalid @enderror" 
                               id="line_user_id" name="line_user_id" value="{{ old('line_user_id') }}">
                        @error('line_user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">สำหรับรับการแจ้งเตือนผ่าน Line</small>
                    </div>

                    <div class="form-group">
                        <label for="role">บทบาท <span class="text-danger">*</span></label>
                        <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="">-- เลือกบทบาท --</option>
                            <option value="staff" {{ old('role') === 'staff' ? 'selected' : '' }}>พนักงาน</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>ผู้ดูแลระบบ</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <div class="form-group">
                        <label for="password">รหัสผ่าน <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> บันทึก
                    </button>
                    <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> ยกเลิก
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card bg-info">
            <div class="card-header">
                <h3 class="card-title">คำแนะนำ</h3>
            </div>
            <div class="card-body">
                <p><strong>บทบาทพนักงาน (Staff):</strong></p>
                <ul>
                    <li>ดูปฏิทินการจอง</li>
                    <li>ดูสถานะห้องประชุม</li>
                    <li>ดูรายละเอียดการจอง</li>
                </ul>

                <p class="mt-3"><strong>บทบาทผู้ดูแลระบบ (Admin):</strong></p>
                <ul>
                    <li>ทุกสิทธิ์ของพนักงาน</li>
                    <li>จัดการห้องประชุม</li>
                    <li>จัดการผู้ใช้และพนักงาน</li>
                    <li>ตั้งค่าการแจ้งเตือน</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
