@extends('layouts.app')

@push('styles')
<style>
    .line-connect-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    
    .line-connect-header {
        background: linear-gradient(135deg, #00B900 0%, #00C300 100%);
        color: white;
        padding: 1.5rem;
        font-size: 1.3rem;
        font-weight: 600;
    }
    
    .line-connect-header i {
        margin-right: 0.75rem;
        font-size: 1.5rem;
    }
    
    .line-connect-body {
        padding: 2rem;
    }
    
    .line-status-connected {
        background: linear-gradient(135deg, #d4fcdf 0%, #e8f5e9 100%);
        border-left: 5px solid #38ef7d;
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
    }
    
    .line-status-disconnected {
        background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
        border-left: 5px solid #ffa726;
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
    }
    
    .btn-line-connect {
        background: linear-gradient(135deg, #00B900 0%, #00C300 100%);
        border: none;
        border-radius: 50px;
        padding: 1rem 2.5rem;
        font-size: 1.1rem;
        font-weight: 600;
        color: white;
        box-shadow: 0 5px 15px rgba(0, 185, 0, 0.4);
        transition: all 0.3s ease;
    }
    
    .btn-line-connect:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 185, 0, 0.6);
        color: white;
    }
    
    .btn-line-disconnect {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        border-radius: 50px;
        padding: 0.75rem 2rem;
        font-weight: 600;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 3px 10px rgba(245, 87, 108, 0.4);
    }
    
    .btn-line-disconnect:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(245, 87, 108, 0.6);
        color: white;
    }
    
    .line-user-info {
        background: #f8f9fa;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        margin-bottom: 1rem;
    }
    
    .line-user-info strong {
        color: #00B900;
    }
    
    .line-benefits {
        list-style: none;
        padding: 0;
        margin: 1.5rem 0;
    }
    
    .line-benefits li {
        padding: 0.75rem 0;
        display: flex;
        align-items: center;
        color: #666;
    }
    
    .line-benefits li:before {
        content: '✓';
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: #00B900;
        color: white;
        border-radius: 50%;
        margin-right: 1rem;
        font-weight: bold;
        flex-shrink: 0;
    }
</style>
@endpush

@section('header')
    <h2>โปรไฟล์</h2>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            {{-- LINE Connection Card --}}
            <div class="card line-connect-card">
                <div class="line-connect-header">
                    <i class="fab fa-line"></i>
                    การเชื่อมต่อกับ LINE
                </div>
                <div class="line-connect-body">
                    @if(auth()->user()->line_user_id)
                        {{-- Connected State --}}
                        <div class="line-status-connected">
                            <h5 class="mb-2">
                                <i class="fas fa-check-circle me-2"></i>
                                เชื่อมต่อกับ LINE เรียบร้อยแล้ว
                            </h5>
                            <p class="mb-0">คุณจะได้รับการแจ้งเตือนการจองผ่าน LINE Messaging</p>
                        </div>
                        
                        <div class="line-user-info">
                            <p class="mb-2">
                                <strong><i class="fas fa-user me-2"></i>LINE User ID:</strong> 
                                <code>{{ auth()->user()->line_user_id }}</code>
                            </p>
                            <p class="mb-0">
                                <strong><i class="far fa-clock me-2"></i>เชื่อมต่อเมื่อ:</strong> 
                                {{ auth()->user()->line_linked_at->locale('th')->translatedFormat('d F Y H:i') }} น.
                            </p>
                        </div>
                        
                        <form action="{{ route('line.unlink') }}" method="POST" class="d-inline"
                              onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการยกเลิกการเชื่อมต่อกับ LINE?\nคุณจะไม่ได้รับการแจ้งเตือนผ่าน LINE อีกต่อไป');">
                            @csrf
                            <button type="submit" class="btn btn-line-disconnect">
                                <i class="fas fa-unlink me-2"></i>ยกเลิกการเชื่อมต่อ
                            </button>
                        </form>
                    @else
                        {{-- Disconnected State --}}
                        <div class="line-status-disconnected">
                            <h5 class="mb-2">
                                <i class="fas fa-info-circle me-2"></i>
                                ยังไม่ได้เชื่อมต่อกับ LINE
                            </h5>
                            <p class="mb-0">เชื่อมต่อบัญชี LINE เพื่อรับการแจ้งเตือนการจองโดยอัตโนมัติ</p>
                        </div>
                        
                        <h6 class="mb-3">
                            <i class="fas fa-gift me-2"></i>
                            ประโยชน์ที่คุณจะได้รับ:
                        </h6>
                        <ul class="line-benefits">
                            <li>รับการแจ้งเตือนการยืนยันการจองทันที</li>
                            <li>รับการเตือนก่อนถึงเวลาประชุม</li>
                            <li>แจ้งเตือนเมื่อการจองถูกยกเลิกหรือเปลี่ยนแปลง</li>
                            <li>ติดตามสถานะการจองได้สะดวกผ่าน LINE</li>
                        </ul>
                        
                        <a href="{{ route('line.redirect') }}" class="btn btn-line-connect">
                            <i class="fab fa-line me-2"></i>
                            เชื่อมต่อกับ LINE
                        </a>
                        
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>วิธีการ:</strong> กดปุ่มด้านบน → ล็อกอินด้วย LINE → อนุญาตให้เข้าถึงข้อมูล → ระบบจะเชื่อมต่อบัญชีโดยอัตโนมัติ
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">ข้อมูลโปรไฟล์</h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">เปลี่ยนรหัสผ่าน</h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">ลบบัญชี</h5>
                </div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
