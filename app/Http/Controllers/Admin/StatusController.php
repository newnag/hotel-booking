<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MeetingRoom;
use App\Services\CalendarService;
use Carbon\Carbon;

class StatusController extends Controller
{
    protected CalendarService $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Display booking status overview dashboard
     *
     * @return \Illuminate\View\View
     */
    public function overview()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $nextMonth = Carbon::now()->endOfMonth();

        // Get statistics
        $todayStats = $this->calendarService->getBookingStatistics($today, $today->copy()->endOfDay());
        $monthStats = $this->calendarService->getBookingStatistics($thisMonth, $nextMonth);

        // Get today's bookings
        $todayBookings = Booking::with(['user:id,name,email', 'room:id,name,location'])
            ->select('id', 'booking_ref', 'user_id', 'room_id', 'start_datetime', 'end_datetime', 'status', 'attendee_count')
            ->betweenDates($today, $today->copy()->endOfDay())
            ->orderBy('start_datetime')
            ->get();

        // Get upcoming bookings (next 7 days)
        $upcomingBookings = Booking::with(['user:id,name,email', 'room:id,name,location'])
            ->select('id', 'booking_ref', 'user_id', 'room_id', 'start_datetime', 'end_datetime', 'status', 'attendee_count')
            ->betweenDates(Carbon::now(), Carbon::now()->addDays(7))
            ->confirmed()
            ->orderBy('start_datetime')
            ->limit(10)
            ->get();

        // Get recent bookings
        $recentBookings = Booking::with(['user:id,name,email', 'room:id,name,location'])
            ->select('id', 'booking_ref', 'user_id', 'room_id', 'start_datetime', 'end_datetime', 'status', 'attendee_count', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get room availability for today
        $rooms = MeetingRoom::active()->with(['bookings' => function ($query) use ($today) {
            $query->betweenDates($today, $today->copy()->endOfDay())
                ->whereIn('status', ['confirmed', 'completed']);
        }])->get();

        return view('admin.status.overview', compact(
            'todayStats',
            'monthStats',
            'todayBookings',
            'upcomingBookings',
            'recentBookings',
            'rooms'
        ));
    }

    /**
     * Display detailed status for a specific room
     *
     * @return \Illuminate\View\View
     */
    public function roomDetail(int $roomId)
    {
        $room = MeetingRoom::findOrFail($roomId);
        $today = Carbon::today();

        // Get today's bookings for this room
        $todayBookings = Booking::where('room_id', $roomId)
            ->with('user')
            ->betweenDates($today, $today->copy()->endOfDay())
            ->orderBy('start_datetime')
            ->get();

        // Get available time slots
        $availableSlots = $this->calendarService->getAvailableTimeSlots($roomId, $today);

        // Get upcoming bookings (next 7 days)
        $upcomingBookings = Booking::where('room_id', $roomId)
            ->with('user')
            ->betweenDates(Carbon::now(), Carbon::now()->addDays(7))
            ->confirmed()
            ->orderBy('start_datetime')
            ->get();

        // Get booking history (last 30 days)
        $bookingHistory = Booking::where('room_id', $roomId)
            ->with('user')
            ->betweenDates(Carbon::now()->subDays(30), Carbon::now())
            ->orderBy('start_datetime', 'desc')
            ->paginate(15);

        return view('admin.status.room-detail', compact(
            'room',
            'todayBookings',
            'availableSlots',
            'upcomingBookings',
            'bookingHistory'
        ));
    }
}
