<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Services\BookingService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display the guest dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get upcoming bookings
        $upcomingBookings = $this->bookingService->getUpcomingBookings($user);

        // Get recent booking history (last 5)
        $recentBookings = $this->bookingService->getUserBookingHistory($user, 5);

        return view('guest.dashboard', compact('upcomingBookings', 'recentBookings'));
    }
}
