@extends('layouts.admin')

@section('title', 'แก้ไขข้อมูลผู้ใช้')

@section('page-title', 'แก้ไขข้อมูลผู้ใช้')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">จัดการผู้ใช้</a></li>
    <li class="breadcrumb-item active">แก้ไข</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ข้อมูลผู้ใช้</h3>
            </div>
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">ชื่อ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">อีเมล <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="phone">เบอร์โทรศัพท์</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="line_user_id">Line User ID</label>
                        <input type="text" class="form-control @error('line_user_id') is-invalid @enderror" 
                               id="line_user_id" name="line_user_id" value="{{ old('line_user_id', $user->line_user_id) }}">
                        @error('line_user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">สำหรับรับการแจ้งเตือนผ่าน Line</small>
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
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
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
                <p><strong>Role:</strong> <span class="badge badge-info">{{ ucfirst($user->role) }}</span></p>
                <p><strong>สมัครเมื่อ:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                <p><strong>อัพเดทล่าสุด:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
                
                @if($user->line_user_id)
                    <p>
                        <strong>สถานะ Line:</strong> 
                        <span class="badge badge-success">
                            <i class="fab fa-line"></i> เชื่อมต่อแล้ว
                        </span>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
