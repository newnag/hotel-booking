@extends('layouts.admin')

@section('title', 'จัดการผู้ใช้ (Guest)')

@section('page-title', 'จัดการผู้ใช้ (Guest)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">จัดการผู้ใช้</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">รายชื่อผู้ใช้</h3>
    </div>
    <div class="card-body">
        {{-- Search and Filter Form --}}
        <form action="{{ route('admin.users.index') }}" method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อหรืออีเมล..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="line_status" class="form-control">
                        <option value="">-- สถานะ Line --</option>
                        <option value="connected" {{ request('line_status') === 'connected' ? 'selected' : '' }}>เชื่อมต่อแล้ว</option>
                        <option value="not_connected" {{ request('line_status') === 'not_connected' ? 'selected' : '' }}>ยังไม่เชื่อมต่อ</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                </div>
                <div class="col-md-3 text-right">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> รีเซ็ต
                    </a>
                </div>
            </div>
        </form>

        {{-- Users Table --}}
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อ</th>
                        <th>อีเมล</th>
                        <th>เบอร์โทร</th>
                        <th>Line</th>
                        <th>สมัครเมื่อ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>
                                @if($user->line_user_id)
                                    <span class="badge badge-success">
                                        <i class="fab fa-line"></i> เชื่อมต่อแล้ว
                                    </span>
                                @else
                                    <span class="badge badge-secondary">ยังไม่เชื่อมต่อ</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-edit"></i> แก้ไข
                                </a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" 
                                      onsubmit="return confirm('แน่ใจหรือไม่ว่าต้องการลบผู้ใช้นี้?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> ลบ
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">ไม่พบข้อมูลผู้ใช้</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-3">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
