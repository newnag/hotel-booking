@extends('layouts.admin')

@section('title', 'จัดการห้องประชุม')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">จัดการห้องประชุม</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">หน้าแรก</a></li>
                    <li class="breadcrumb-item active">จัดการห้องประชุม</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session('error') }}
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">รายการห้องประชุม</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> เพิ่มห้องประชุมใหม่
                    </a>
                </div>
            </div>

            <div class="card-body">
                {{-- Filters --}}
                <form method="GET" action="{{ route('admin.rooms.index') }}" class="mb-3">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <input type="text" 
                                       name="search" 
                                       class="form-control" 
                                       placeholder="ค้นหาชื่อห้องหรือคำอธิบาย..."
                                       value="{{ $filters['search'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <select name="is_active" class="form-control">
                                    <option value="">ทุกสถานะ</option>
                                    <option value="1" {{ ($filters['is_active'] ?? '') === '1' ? 'selected' : '' }}>เปิดใช้งาน</option>
                                    <option value="0" {{ ($filters['is_active'] ?? '') === '0' ? 'selected' : '' }}>ปิดใช้งาน</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-search"></i> ค้นหา
                            </button>
                            <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> รีเซ็ต
                            </a>
                        </div>
                    </div>
                </form>

                {{-- Rooms Table --}}
                @if($rooms->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 80px">รูปภาพ</th>
                                    <th>ชื่อห้อง</th>
                                    <th>คำอธิบาย</th>
                                    <th style="width: 100px" class="text-center">ความจุ</th>
                                    <th style="width: 100px" class="text-center">สถานะ</th>
                                    <th style="width: 200px" class="text-center">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rooms as $room)
                                    <tr>
                                        <td class="text-center">
                                            @if($room->image_path)
                                                <img src="{{ asset('storage/' . $room->image_path) }}" 
                                                     alt="{{ $room->name }}" 
                                                     class="img-thumbnail"
                                                     style="max-width: 60px; max-height: 60px;">
                                            @else
                                                <div class="bg-secondary text-white d-flex align-items-center justify-content-center" 
                                                     style="width: 60px; height: 60px;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $room->name }}</strong>
                                        </td>
                                        <td>
                                            {{ Str::limit($room->description, 100) }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">
                                                <i class="fas fa-users"></i> {{ $room->max_capacity }} ที่นั่ง
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if($room->is_active)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle"></i> เปิดใช้งาน
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-times-circle"></i> ปิดใช้งาน
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group">
                                                <a href="{{ route('admin.rooms.show', $room) }}" 
                                                   class="btn btn-sm btn-info"
                                                   title="ดูรายละเอียด">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.rooms.edit', $room) }}" 
                                                   class="btn btn-sm btn-warning"
                                                   title="แก้ไข">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.rooms.toggle', $room) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm {{ $room->is_active ? 'btn-secondary' : 'btn-success' }}"
                                                            title="{{ $room->is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger"
                                                        onclick="confirmDelete({{ $room->id }}, '{{ $room->name }}')"
                                                        title="ลบ">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                            <form id="delete-form-{{ $room->id }}" 
                                                  action="{{ route('admin.rooms.destroy', $room) }}" 
                                                  method="POST" 
                                                  class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $rooms->appends($filters)->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> ไม่พบห้องประชุมในระบบ
                        <a href="{{ route('admin.rooms.create') }}" class="alert-link">คลิกที่นี่เพื่อเพิ่มห้องประชุมใหม่</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
function confirmDelete(roomId, roomName) {
    if (confirm('คุณแน่ใจหรือไม่ที่จะลบห้องประชุม "' + roomName + '"?\n\nการลบห้องประชุมจะส่งผลกระทบต่อการจองในอดีต')) {
        document.getElementById('delete-form-' + roomId).submit();
    }
}
</script>
@endpush
