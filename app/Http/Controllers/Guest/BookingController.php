<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\MeetingRoom;
use App\Services\BookingService;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    protected BookingService $bookingService;

    protected RoomAvailabilityService $availabilityService;

    public function __construct(
        BookingService $bookingService,
        RoomAvailabilityService $availabilityService
    ) {
        $this->bookingService = $bookingService;
        $this->availabilityService = $availabilityService;
    }

    /**
     * Display room search form.
     */
    public function search()
    {
        $decorationThemes = config('booking.decoration_themes', []);

        return view('guest.booking.search', compact('decorationThemes'));
    }

    /**
     * Search for available rooms.
     */
    public function searchResults(Request $request)
    {
        $request->validate([
            'attendee_count' => 'required|integer|min:1',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        $availableRooms = $this->availabilityService->searchAvailableRooms(
            $request->attendee_count,
            $request->start_datetime,
            $request->end_datetime
        );

        $decorationThemes = config('booking.decoration_themes', []);

        return view('guest.booking.results', compact(
            'availableRooms',
            'decorationThemes'
        ))->with([
            'searchParams' => $request->only(['attendee_count', 'start_datetime', 'end_datetime']),
        ]);
    }

    /**
     * Show booking creation form for a specific room.
     */
    public function create(Request $request, MeetingRoom $room)
    {
        $request->validate([
            'attendee_count' => 'required|integer|min:1',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        // Verify room is still available
        if (! $this->availabilityService->isRoomAvailable(
            $room->id,
            $request->start_datetime,
            $request->end_datetime
        )) {
            return redirect()->route('guest.booking.search')
                ->with('error', __('This room is no longer available for the selected time.'));
        }

        $decorationThemes = config('booking.decoration_themes', []);

        return view('guest.booking.create', compact('room', 'decorationThemes'))
            ->with([
                'attendeeCount' => $request->attendee_count,
                'startDatetime' => $request->start_datetime,
                'endDatetime' => $request->end_datetime,
            ]);
    }

    /**
     * Store a new booking.
     */
    public function store(BookingRequest $request)
    {
        try {
            $booking = $this->bookingService->createBooking(
                $request->user(),
                $request->validated()
            );

            return redirect()->route('guest.booking.show', $booking)
                ->with('success', __('Booking created successfully! Reference: :ref', [
                    'ref' => $booking->booking_ref,
                ]));
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display booking history.
     */
    public function history(Request $request)
    {
        // Optimized with eager loading to prevent N+1 queries
        $bookings = $request->user()
            ->bookings()
            ->with(['room:id,name,description,location,max_capacity', 'user:id,name,email'])
            ->select('id', 'booking_ref', 'user_id', 'room_id', 'start_datetime', 'end_datetime', 'status', 'attendee_count', 'created_at')
            ->orderBy('start_datetime', 'desc')
            ->paginate(15);

        return view('guest.booking.history', compact('bookings'));
    }

    /**
     * Display a specific booking.
     */
    public function show($id)
    {
        $booking = \App\Models\Booking::with(['room', 'user'])
            ->where('id', $id)
            ->firstOrFail();

        // Check ownership
        if ($booking->user_id !== auth()->id() && ! auth()->user()->isStaff()) {
            abort(403, __('You do not have permission to view this booking.'));
        }

        return view('guest.booking.show', compact('booking'));
    }

    /**
     * Cancel a booking.
     */
    public function cancel($id)
    {
        $booking = \App\Models\Booking::findOrFail($id);

        // Check ownership
        if ($booking->user_id !== auth()->id()) {
            abort(403, __('You do not have permission to cancel this booking.'));
        }

        // Check if booking can be cancelled (not in the past)
        if ($booking->start_datetime < now()) {
            return redirect()->back()
                ->with('error', __('Cannot cancel a booking that has already started.'));
        }

        if ($this->bookingService->cancelBooking($booking)) {
            return redirect()->route('guest.booking.history')
                ->with('success', __('Booking cancelled successfully.'));
        }

        return redirect()->back()
            ->with('error', __('Failed to cancel booking. Please try again.'));
    }
}
