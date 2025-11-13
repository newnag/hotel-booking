@extends('layouts.admin')

@section('title', 'แก้ไขข้อมูลพนักงาน')

@section('page-title', 'แก้ไขข้อมูลพนักงาน')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.staff.index') }}">จัดการพนักงาน</a></li>
    <li class="breadcrumb-item active">แก้ไข</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ข้อมูลพนักงาน</h3>
            </div>
            <form action="{{ route('admin.staff.update', $staff) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">ชื่อ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $staff->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">อีเมล <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $staff->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone', $staff->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="line_user_id">Line User ID</label>
                        <input type="text" class="form-control @error('line_user_id') is-invalid @enderror" 
                               id="line_user_id" name="line_user_id" value="{{ old('line_user_id', $staff->line_user_id) }}">
                        @error('line_user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">สำหรับรับการแจ้งเตือนผ่าน Line</small>
                    </div>

                    <div class="form-group">
                        <label for="role">บทบาท <span class="text-danger">*</span></label>
                        <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required
                                @if($staff->id === auth()->id()) disabled @endif>
                            <option value="">-- เลือกบทบาท --</option>
                            <option value="staff" {{ old('role', $staff->role) === 'staff' ? 'selected' : '' }}>พนักงาน</option>
                            <option value="admin" {{ old('role', $staff->role) === 'admin' ? 'selected' : '' }}>ผู้ดูแลระบบ</option>
                        </select>
                        @if($staff->id === auth()->id())
                            <input type="hidden" name="role" value="{{ $staff->role }}">
                            <small class="form-text text-muted text-warning">
                                <i class="fas fa-info-circle"></i> ไม่สามารถเปลี่ยนบทบาทของตัวเองได้
                            </small>
                        @endif
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    <h5>เปลี่ยนรหัสผ่าน</h5>
                    <p class="text-muted">ปล่อยว่างไว้หากไม่ต้องการเปลี่ยน</p>

                    <div class="form-group">
                        <label for="password">รหัสผ่านใหม่</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
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
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ข้อมูลเพิ่มเติม</h3>
            </div>
            <div class="card-body">
                <p>
                    <strong>บทบาทปัจจุบัน:</strong> 
                    @if($staff->role === 'admin')
                        <span class="badge badge-danger">
                            <i class="fas fa-user-shield"></i> ผู้ดูแลระบบ
                        </span>
                    @else
                        <span class="badge badge-primary">
                            <i class="fas fa-user-tie"></i> พนักงาน
                        </span>
                    @endif
                </p>
                <p><strong>สมัครเมื่อ:</strong> {{ $staff->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>อัพเดทล่าสุด:</strong> {{ $staff->updated_at->format('d/m/Y H:i') }}</p>
                
                @if($staff->line_user_id)
                    <p>
                        <strong>สถานะ Line:</strong> 
                        <span class="badge badge-success">
                            <i class="fab fa-line"></i> เชื่อมต่อแล้ว
                        </span>
                    </p>
                @endif

                @if($staff->id === auth()->id())
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i> 
                        คุณกำลังแก้ไขบัญชีของตัวเอง
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
