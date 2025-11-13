@extends('layouts.admin')

@section('title', 'ตั้งค่าการแจ้งเตือน')

@section('page-title', 'ตั้งค่าการแจ้งเตือน Line')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">ตั้งค่าการแจ้งเตือน</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">การตั้งค่าระบบ</h3>
                </div>
                <form action="{{ route('admin.notifications.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="card-body">
                        <div class="alert {{ $lineConfigured ? 'alert-success' : 'alert-warning' }}">
                            <h5>
                                <i class="icon fas {{ $lineConfigured ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i>
                                สถานะ Line API
                            </h5>
                            @if($lineConfigured)
                                <p class="mb-0">เชื่อมต่อกับ Line Messaging API สำเร็จ</p>
                            @else
                                <p class="mb-0">
                                    ยังไม่ได้ตั้งค่า Line API<br>
                                    <small>กรุณาตั้งค่า LINE_CHANNEL_ACCESS_TOKEN และ LINE_CHANNEL_SECRET ในไฟล์ .env</small>
                                </p>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="enabled" name="enabled" value="1" checked>
                                <label class="custom-control-label" for="enabled">
                                    เปิดใช้งานการแจ้งเตือน Line
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                เมื่อปิดใช้งาน ระบบจะไม่ส่งการแจ้งเตือนใดๆ ผ่าน Line
                            </small>
                        </div>

                        <hr>

                        <h5>ประเภทการแจ้งเตือน</h5>
                        <p class="text-muted">เลือกประเภทการแจ้งเตือนที่ต้องการส่ง</p>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" 
                                       id="type_booking_created" name="notification_types[booking_created]" value="1" checked>
                                <label class="custom-control-label" for="type_booking_created">
                                    <i class="fas fa-plus-circle text-info"></i> การจองใหม่ (แจ้งพนักงาน)
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" 
                                       id="type_booking_confirmed" name="notification_types[booking_confirmed]" value="1" checked>
                                <label class="custom-control-label" for="type_booking_confirmed">
                                    <i class="fas fa-check-circle text-success"></i> ยืนยันการจอง (แจ้งผู้จอง)
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" 
                                       id="type_booking_cancelled" name="notification_types[booking_cancelled]" value="1" checked>
                                <label class="custom-control-label" for="type_booking_cancelled">
                                    <i class="fas fa-times-circle text-danger"></i> ยกเลิกการจอง (แจ้งผู้จอง)
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" 
                                       id="type_booking_reminder" name="notification_types[booking_reminder]" value="1" checked>
                                <label class="custom-control-label" for="type_booking_reminder">
                                    <i class="fas fa-bell text-warning"></i> แจ้งเตือนล่วงหน้า
                                </label>
                            </div>
                        </div>

                        <hr>

                        <h5>พนักงานที่ได้รับการแจ้งเตือน</h5>
                        <p class="text-muted">เลือกพนักงานที่จะได้รับการแจ้งเตือนเมื่อมีการจองใหม่</p>

                        @if($staffUsers->isEmpty())
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> ยังไม่มีพนักงานในระบบ
                            </div>
                        @else
                            @foreach($staffUsers as $staff)
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" 
                                               id="staff_{{ $staff->id }}" 
                                               name="staff_recipients[]" 
                                               value="{{ $staff->id }}"
                                               {{ $staff->line_user_id ? 'checked' : 'disabled' }}>
                                        <label class="custom-control-label" for="staff_{{ $staff->id }}">
                                            {{ $staff->name }} 
                                            <small class="text-muted">({{ $staff->email }})</small>
                                            @if($staff->line_user_id)
                                                <span class="badge badge-success">
                                                    <i class="fab fa-line"></i> เชื่อมต่อ Line แล้ว
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    <i class="fab fa-line"></i> ยังไม่เชื่อมต่อ Line
                                                </span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> บันทึกการตั้งค่า
                        </button>
                        <a href="{{ route('admin.notifications.history') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> กลับ
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Test notification card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">ทดสอบการส่งการแจ้งเตือน</h3>
                </div>
                <form action="{{ route('admin.notifications.test') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label>ผู้รับ</label>
                            <select name="recipient_id" class="form-control" required>
                                <option value="">-- เลือกผู้รับ --</option>
                                @foreach($staffUsers->where('line_user_id', '!=', null) as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                แสดงเฉพาะผู้ใช้ที่เชื่อมต่อ Line แล้ว
                            </small>
                        </div>

                        <div class="form-group">
                            <label>ข้อความ</label>
                            <textarea name="message" class="form-control" rows="4" 
                                      placeholder="กรอกข้อความที่ต้องการทดสอบส่ง" required>🔔 ทดสอบการส่งการแจ้งเตือน
                                      
ระบบแจ้งเตือนทำงานปกติ ✅</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info btn-block" {{ !$lineConfigured ? 'disabled' : '' }}>
                            <i class="fas fa-paper-plane"></i> ส่งการแจ้งเตือนทดสอบ
                        </button>
                        @if(!$lineConfigured)
                            <small class="text-danger d-block mt-2">
                                <i class="fas fa-exclamation-triangle"></i> 
                                กรุณาตั้งค่า Line API ก่อนทดสอบ
                            </small>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Info card -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">คำแนะนำ</h3>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-info-circle"></i> การเชื่อมต่อ Line</h6>
                    <p class="text-sm">
                        ผู้ใช้ต้องเชื่อมต่อบัญชี Line กับระบบก่อนจึงจะได้รับการแจ้งเตือน
                    </p>

                    <h6><i class="fas fa-cog"></i> การตั้งค่า Line API</h6>
                    <p class="text-sm">
                        1. สร้าง Line Messaging API Channel<br>
                        2. คัดลอก Channel Access Token<br>
                        3. เพิ่มใน .env ไฟล์<br>
                        4. Restart Laravel server
                    </p>

                    <h6><i class="fas fa-bell"></i> การส่งการแจ้งเตือน</h6>
                    <p class="text-sm">
                        การแจ้งเตือนจะถูกส่งผ่าน Queue เพื่อไม่ให้กระทบต่อประสิทธิภาพ
                    </p>

                    <a href="{{ route('admin.notifications.history') }}" class="btn btn-sm btn-default btn-block">
                        <i class="fas fa-history"></i> ดูประวัติการแจ้งเตือน
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด!',
                text: '{{ session('error') }}',
            });
        @endif
    </script>
@endsection
