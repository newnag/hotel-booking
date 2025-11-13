<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Meeting Room Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .hero-section {
            color: white;
            padding: 60px 0;
        }
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin: 15px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 20px;
        }
        .btn-custom {
            padding: 12px 40px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s;
        }
        .btn-primary-custom {
            background: #667eea;
            border: none;
            color: white;
        }
        .btn-primary-custom:hover {
            background: #5568d3;
            transform: scale(1.05);
        }
        .btn-outline-custom {
            border: 2px solid white;
            color: white;
        }
        .btn-outline-custom:hover {
            background: white;
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero-section text-center">
            <h1 class="display-3 fw-bold mb-4">
                <i class="fas fa-door-open me-3"></i>
                ระบบแคนคูนอัจฉริยะ
            </h1>
            <p class="lead mb-5">จองห้องประชุมของคุณได้ง่ายๆ รวดเร็ว และสะดวกสบาย</p>
            
            <div class="d-flex justify-content-center gap-3 mb-5">
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn btn-primary-custom btn-custom">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        แดชบอร์ด
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary-custom btn-custom">
                        <i class="fas fa-sign-in-alt me-2"></i>
                        เข้าสู่ระบบ
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-custom btn-custom">
                        <i class="fas fa-user-plus me-2"></i>
                        สมัครสมาชิก
                    </a>
                @endauth
                <a href="{{ route('room-status.board') }}" class="btn btn-outline-custom btn-custom">
                    <i class="fas fa-tv me-2"></i>
                    สถานะห้องประชุม
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-search feature-icon"></i>
                    <h4 class="fw-bold mb-3">ค้นหาห้องประชุม</h4>
                    <p class="text-muted">ค้นหาห้องประชุมที่เหมาะสมกับความต้องการของคุณ ด้วยระบบค้นหาที่ง่ายและรวดเร็ว</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-calendar-check feature-icon"></i>
                    <h4 class="fw-bold mb-3">จองทันที</h4>
                    <p class="text-muted">จองห้องประชุมได้ทันที พร้อมรับการยืนยันผ่าน Line Notification</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card text-center">
                    <i class="fas fa-history feature-icon"></i>
                    <h4 class="fw-bold mb-3">ติดตามสถานะ</h4>
                    <p class="text-muted">ตรวจสอบประวัติการจองและสถานะห้องประชุมได้ตลอดเวลา</p>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="feature-card">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-clock text-primary me-2"></i>
                        เวลาทำการ
                    </h5>
                    <p class="mb-0"><i class="far fa-clock me-2"></i>จันทร์ - ศุกร์: 08:00 - 17:00</p>
                    <p class="mb-0"><i class="far fa-clock me-2"></i>เสาร์ - อาทิตย์: 09:00 - 17:00</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="feature-card">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        ข้อกำหนดการจอง
                    </h5>
                    <p class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>จองได้ทันที ไม่ต้องรอนาน</p>
                    <p class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>ยกเลิกได้ฟรีก่อน 24 ชั่วโมง</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
