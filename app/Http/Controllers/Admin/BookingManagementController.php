<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingManagementController extends Controller
{
    /**
     * Display a listing of all bookings
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'room']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_datetime', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_datetime', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_ref', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'start_datetime');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $bookings = $query->paginate(20)->withQueryString();

        // Get filter options
        $rooms = \App\Models\MeetingRoom::active()->get();
        $statuses = ['confirmed', 'cancelled', 'completed'];

        return view('admin.bookings.index', compact('bookings', 'rooms', 'statuses'));
    }

    /**
     * Display the specified booking
     *
     * @return \Illuminate\View\View
     */
    public function show(int $id)
    {
        $booking = Booking::with(['user', 'room', 'lineNotifications'])
            ->findOrFail($id);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Update booking status
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:confirmed,cancelled,completed',
        ]);

        $booking = Booking::findOrFail($id);
        $oldStatus = $booking->status;
        $booking->status = $request->status;
        $booking->save();

        return redirect()->back()->with('success', sprintf(
            'Booking status updated from %s to %s',
            $oldStatus,
            $request->status
        ));
    }

    /**
     * Delete the specified booking
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $booking = Booking::findOrFail($id);
        $bookingRef = $booking->booking_ref;
        $booking->delete();

        return redirect()->route('admin.bookings.index')
            ->with('success', sprintf('Booking %s has been deleted', $bookingRef));
    }
}
