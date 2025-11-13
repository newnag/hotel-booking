@extends('layouts.admin')

@section('title', 'จัดการพนักงาน')

@section('page-title', 'จัดการพนักงาน')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">จัดการพนักงาน</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">รายชื่อพนักงาน</h3>
        <div class="card-tools">
            <a href="{{ route('admin.staff.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> เพิ่มพนักงานใหม่
            </a>
        </div>
    </div>
    <div class="card-body">
        {{-- Search and Filter Form --}}
        <form action="{{ route('admin.staff.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อหรืออีเมล..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-control">
                        <option value="">-- บทบาททั้งหมด --</option>
                        <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>พนักงาน</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>ผู้ดูแลระบบ</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                </div>
                <div class="col-md-3 text-right">
                    <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> รีเซ็ต
                    </a>
                </div>
            </div>
        </form>

        {{-- Staff Table --}}
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อ</th>
                        <th>อีเมล</th>
                        <th>เบอร์โทร</th>
                        <th>บทบาท</th>
                        <th>Line</th>
                        <th>สมัครเมื่อ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staff as $member)
                        <tr>
                            <td>{{ $member->id }}</td>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->email }}</td>
                            <td>{{ $member->phone ?? '-' }}</td>
                            <td>
                                @if($member->role === 'admin')
                                    <span class="badge badge-danger">
                                        <i class="fas fa-user-shield"></i> ผู้ดูแลระบบ
                                    </span>
                                @else
                                    <span class="badge badge-primary">
                                        <i class="fas fa-user-tie"></i> พนักงาน
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($member->line_user_id)
                                    <span class="badge badge-success">
                                        <i class="fab fa-line"></i> เชื่อมต่อแล้ว
                                    </span>
                                @else
                                    <span class="badge badge-secondary">ยังไม่เชื่อมต่อ</span>
                                @endif
                            </td>
                            <td>{{ $member->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.staff.edit', $member) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> แก้ไข
                                </a>
                                @if($member->id !== auth()->id())
                                    <form action="{{ route('admin.staff.destroy', $member) }}" method="POST" class="d-inline" 
                                          onsubmit="return confirm('แน่ใจหรือไม่ว่าต้องการลบพนักงานนี้?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> ลบ
                                        </button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="fas fa-lock"></i> ไม่สามารถลบตัวเองได้
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">ไม่พบข้อมูลพนักงาน</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $staff->links() }}
        </div>
    </div>
</div>
@endsection
