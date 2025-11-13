<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    protected CalendarService $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Display the booking calendar
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $today = Carbon::today();
        $bookingsToday = $this->calendarService->getBookingsByRoomForDate($today);

        return view('admin.calendar.index', compact('bookingsToday'));
    }

    /**
     * Get calendar events for AJAX requests
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function events(Request $request)
    {
        $start = $request->has('start') ? Carbon::parse($request->start) : null;
        $end = $request->has('end') ? Carbon::parse($request->end) : null;
        $roomId = $request->get('room_id');

        $events = $this->calendarService->formatBookingsForCalendar($start, $end, $roomId);

        return response()->json($events);
    }

    /**
     * Get available time slots for a room on a specific date
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableSlots(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:meeting_rooms,id',
            'date' => 'required|date',
        ]);

        $roomId = $request->get('room_id');
        $date = Carbon::parse($request->get('date'));

        $slots = $this->calendarService->getAvailableTimeSlots($roomId, $date);

        return response()->json(['slots' => $slots]);
    }
}
