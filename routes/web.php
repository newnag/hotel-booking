<?php

use App\Http\Controllers\Admin\BookingManagementController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\StatusController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Guest\BookingController;
use App\Http\Controllers\Guest\DashboardController;
use App\Http\Controllers\Guest\ProfileController as GuestProfileController;
use App\Http\Controllers\LineAuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomStatusBoardController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Room Status Board - Public display
Route::get('/room-status', [RoomStatusBoardController::class, 'index'])->name('room-status.board');
Route::get('/api/room-status', [RoomStatusBoardController::class, 'apiStatus'])->name('room-status.api');
Route::get('/room-status/{id}', [RoomStatusBoardController::class, 'showRoom'])->name('room-status.show');
Route::get('/api/room-status/{id}', [RoomStatusBoardController::class, 'apiRoomStatus'])->name('room-status.api.room');

// Guest routes - booking system for logged-in guests
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Guest booking routes
Route::middleware(['auth', 'verified'])->prefix('guest')->name('guest.')->group(function () {
    // Room search and booking
    Route::get('/booking/search', [BookingController::class, 'search'])->name('booking.search');
    Route::post('/booking/search', [BookingController::class, 'searchResults'])
        ->middleware('throttle:booking-search')
        ->name('booking.search.results');
    Route::get('/booking/create/{room}', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking', [BookingController::class, 'store'])
        ->middleware('throttle:booking-create')
        ->name('booking.store');

    // Booking management
    Route::get('/booking/history', [BookingController::class, 'history'])->name('booking.history');
    Route::get('/booking/{id}', [BookingController::class, 'show'])->name('booking.show');
    Route::delete('/booking/{id}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');
});

// Staff routes (accessible by both staff and admin)
Route::prefix('staff')->middleware(['auth', 'role:staff,admin'])->name('staff.')->group(function () {
    // Dashboard
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    // Calendar views (admin can access staff routes)
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // Status dashboard
    Route::get('/status', [StatusController::class, 'overview'])->name('status.overview');
    Route::get('/status/room/{room}', [StatusController::class, 'roomDetail'])->name('status.room');

    // Booking management
    Route::get('/bookings', [BookingManagementController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [BookingManagementController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{id}/status', [BookingManagementController::class, 'updateStatus'])->name('bookings.update-status');

    // AJAX routes for calendar
    Route::get('/calendar/events', [CalendarController::class, 'events'])
        ->middleware('throttle:calendar')
        ->name('calendar.events');
    Route::get('/calendar/available-slots', [CalendarController::class, 'availableSlots'])
        ->middleware('throttle:calendar')
        ->name('calendar.available-slots');
});

// Admin routes - system configuration and management
Route::middleware(['auth', 'role:admin', 'throttle:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Admin dashboard
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');

    // Calendar views (admin can access staff routes)
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

    // Status dashboard
    Route::get('/status', [StatusController::class, 'overview'])->name('status.overview');
    Route::get('/status/room/{room}', [StatusController::class, 'roomDetail'])->name('status.room');

    // Booking management
    Route::get('/bookings', [BookingManagementController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [BookingManagementController::class, 'show'])->name('bookings.show');
    Route::patch('/bookings/{id}/status', [BookingManagementController::class, 'updateStatus'])->name('bookings.update-status');
    Route::delete('/bookings/{id}', [BookingManagementController::class, 'destroy'])->name('bookings.destroy');

    // AJAX routes for calendar
    Route::get('/calendar/events', [CalendarController::class, 'events'])
        ->middleware('throttle:calendar')
        ->name('calendar.events');
    Route::get('/calendar/available-slots', [CalendarController::class, 'availableSlots'])
        ->middleware('throttle:calendar')
        ->name('calendar.available-slots');

    // Meeting room management
    Route::resource('rooms', RoomController::class);
    Route::post('/rooms/{room}/toggle', [RoomController::class, 'toggle'])->name('rooms.toggle');

    // Line notification management
    Route::get('/notifications/history', [NotificationController::class, 'history'])->name('notifications.history');
    Route::get('/notifications/settings', [NotificationController::class, 'settings'])->name('notifications.settings');
    Route::put('/notifications/settings', [NotificationController::class, 'updateSettings'])->name('notifications.settings.update');
    Route::post('/notifications/{notification}/retry', [NotificationController::class, 'retry'])
        ->middleware('throttle:notification-retry')
        ->name('notifications.retry');
    Route::post('/notifications/test', [NotificationController::class, 'test'])
        ->middleware('throttle:notification-retry')
        ->name('notifications.test');

    // User management (Guest accounts)
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Staff management
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
});

// Guest profile routes
Route::middleware(['auth'])->prefix('guest')->name('guest.')->group(function () {
    Route::get('/profile', [GuestProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [GuestProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [GuestProfileController::class, 'updatePassword'])->name('profile.password');
});

// LINE Login OAuth routes
Route::middleware(['auth'])->prefix('line')->name('line.')->group(function () {
    Route::get('/redirect', [LineAuthController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [LineAuthController::class, 'callback'])->name('callback');
    Route::post('/unlink', [LineAuthController::class, 'unlink'])->name('unlink');
});

require __DIR__.'/auth.php';
