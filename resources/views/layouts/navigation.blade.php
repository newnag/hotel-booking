<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <i class="fas fa-door-open me-2 text-primary"></i>
            <strong>{{ config('app.name', 'Hotel Booking') }}</strong>
        </a>
        
        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="navbar-collapse" id="navbarNav">
            <!-- Left Side -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        แดชบอร์ด
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('guest.booking.search') ? 'active' : '' }}" href="{{ route('guest.booking.search') }}">
                        <i class="fas fa-search me-1"></i>
                        ค้นหาห้อง
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('guest.booking.history') ? 'active' : '' }}" href="{{ route('guest.booking.history') }}">
                        <i class="fas fa-history me-1"></i>
                        ประวัติการจอง
                    </a>
                </li>
                
                @if(Auth::user()->isStaff() || Auth::user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('staff.*') || request()->routeIs('admin.*') ? 'active' : '' }}" 
                           href="{{ Auth::user()->isAdmin() ? route('admin.dashboard') : route('staff.dashboard') }}">
                            <i class="fas fa-tools me-1"></i>
                            จัดการหลังบ้าน
                        </a>
                    </li>
                @endif
            </ul>
            
            <!-- Right Side -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i>
                        {{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user-edit me-2"></i>
                                โปรไฟล์
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>
                                    ออกจากระบบ
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
