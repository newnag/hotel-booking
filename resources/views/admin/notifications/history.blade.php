@extends('layouts.admin')

@section('title', 'ประวัติการแจ้งเตือน')

@section('page-title', 'ประวัติการแจ้งเตือน Line')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">ประวัติการแจ้งเตือน</li>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">กรองข้อมูล</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.notifications.history') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>สถานะ</label>
                            <select name="status" class="form-control">
                                <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>รอส่ง</option>
                                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>ส่งแล้ว</option>
                                <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>ล้มเหลว</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>ประเภท</label>
                            <select name="type" class="form-control">
                                <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                                <option value="booking_created" {{ request('type') == 'booking_created' ? 'selected' : '' }}>สร้างการจอง</option>
                                <option value="booking_confirmed" {{ request('type') == 'booking_confirmed' ? 'selected' : '' }}>ยืนยันการจอง</option>
                                <option value="booking_cancelled" {{ request('type') == 'booking_cancelled' ? 'selected' : '' }}>ยกเลิกการจอง</option>
                                <option value="booking_reminder" {{ request('type') == 'booking_reminder' ? 'selected' : '' }}>แจ้งเตือน</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>ผู้รับ</label>
                            <select name="recipient_type" class="form-control">
                                <option value="all" {{ request('recipient_type') == 'all' ? 'selected' : '' }}>ทั้งหมด</option>
                                <option value="staff" {{ request('recipient_type') == 'staff' ? 'selected' : '' }}>พนักงาน</option>
                                <option value="guest" {{ request('recipient_type') == 'guest' ? 'selected' : '' }}>ผู้จอง</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>จากวันที่</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>ถึงวันที่</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>รหัสการจอง</label>
                            <input type="number" name="booking_id" class="form-control" placeholder="กรอกรหัสการจอง" value="{{ request('booking_id') }}">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">รายการแจ้งเตือน ({{ $notifications->total() }} รายการ)</h3>
            <div class="card-tools">
                <a href="{{ route('admin.notifications.settings') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-cog"></i> ตั้งค่า
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>วันที่/เวลา</th>
                            <th>การจอง</th>
                            <th>ผู้รับ</th>
                            <th>ประเภท</th>
                            <th>ข้อความ</th>
                            <th>สถานะ</th>
                            <th>การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $notification)
                            <tr>
                                <td>{{ $notification->id }}</td>
                                <td>
                                    <small>{{ $notification->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    @if($notification->booking)
                                        <a href="{{ route('admin.bookings.show', $notification->booking_id) }}">
                                            #{{ $notification->booking_id }}<br>
                                            <small class="text-muted">{{ $notification->booking->room->name }}</small>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $notification->recipient->name }}<br>
                                    <small class="text-muted">
                                        @if($notification->recipient_type === 'staff')
                                            <i class="fas fa-user-tie"></i> พนักงาน
                                        @else
                                            <i class="fas fa-user"></i> ผู้จอง
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <small>
                                        @switch($notification->notification_type)
                                            @case('booking_created')
                                                <i class="fas fa-plus-circle text-info"></i> สร้างการจอง
                                                @break
                                            @case('booking_confirmed')
                                                <i class="fas fa-check-circle text-success"></i> ยืนยันการจอง
                                                @break
                                            @case('booking_cancelled')
                                                <i class="fas fa-times-circle text-danger"></i> ยกเลิกการจอง
                                                @break
                                            @case('booking_reminder')
                                                <i class="fas fa-bell text-warning"></i> แจ้งเตือน
                                                @break
                                        @endswitch
                                    </small>
                                </td>
                                <td>
                                    <small class="text-truncate" style="max-width: 200px; display: inline-block;" 
                                           title="{{ $notification->message }}">
                                        {{ Str::limit($notification->message, 50) }}
                                    </small>
                                </td>
                                <td>
                                    @switch($notification->status)
                                        @case('sent')
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> ส่งแล้ว
                                            </span>
                                            @if($notification->sent_at)
                                                <br><small class="text-muted">{{ $notification->sent_at->format('d/m/Y H:i') }}</small>
                                            @endif
                                            @break
                                        @case('failed')
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times"></i> ล้มเหลว
                                            </span>
                                            @if($notification->error_message)
                                                <br><small class="text-danger" title="{{ $notification->error_message }}">
                                                    {{ Str::limit($notification->error_message, 30) }}
                                                </small>
                                            @endif
                                            @break
                                        @case('pending')
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> รอส่ง
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    @if($notification->status === 'failed')
                                        <form action="{{ route('admin.notifications.retry', $notification) }}" 
                                              method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" 
                                                    title="ส่งซ้ำ"
                                                    onclick="return confirm('คุณต้องการส่งการแจ้งเตือนนี้อีกครั้งหรือไม่?')">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    ไม่พบข้อมูลการแจ้งเตือน
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($notifications->hasPages())
            <div class="card-footer clearfix">
                {{ $notifications->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@stop

@section('css')
    <style>
        .table-responsive {
            overflow-x: auto;
        }
        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
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
