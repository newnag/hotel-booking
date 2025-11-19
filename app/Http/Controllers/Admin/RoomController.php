<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RoomRequest;
use App\Models\MeetingRoom;
use App\Services\RoomAvailabilityService;
use App\Services\RoomService;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected RoomService $roomService;

    protected RoomAvailabilityService $availabilityService;

    public function __construct(RoomService $roomService, RoomAvailabilityService $availabilityService)
    {
        $this->roomService = $roomService;
        $this->availabilityService = $availabilityService;
    }

    /**
     * Display a listing of rooms
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $filters = [
            'search' => $request->get('search'),
            'is_active' => $request->get('is_active'),
            'order_by' => $request->get('order_by', 'name'),
            'order_dir' => $request->get('order_dir', 'asc'),
        ];

        $rooms = $this->roomService->getPaginatedRooms($filters, 15);

        return view('admin.rooms.index', compact('rooms', 'filters'));
    }

    /**
     * Show the form for creating a new room
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.rooms.form', ['room' => null]);
    }

    /**
     * Store a newly created room
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(RoomRequest $request)
    {
        $data = $request->only(['name', 'description', 'max_capacity', 'location', 'hourly_rate', 'is_active']);

        // Add image file if uploaded
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $request->file('image');
        }

        $room = $this->roomService->createRoom($data);

        // Clear active rooms cache
        $this->availabilityService->clearActiveRoomsCache();

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'ห้องประชุม "'.$room->name.'" ถูกสร้างเรียบร้อยแล้ว');
    }

    /**
     * Display the specified room
     *
     * @return \Illuminate\View\View
     */
    public function show(MeetingRoom $room)
    {
        $statistics = $this->roomService->getRoomStatistics($room);

        // Get recent bookings
        $recentBookings = $room->bookings()
            ->with(['user'])
            ->latest('start_datetime')
            ->take(10)
            ->get();

        // Get upcoming bookings
        $upcomingBookings = $room->bookings()
            ->with(['user'])
            ->where('start_datetime', '>', now())
            ->whereIn('status', ['confirmed'])
            ->orderBy('start_datetime')
            ->take(5)
            ->get();

        return view('admin.rooms.show', compact('room', 'statistics', 'recentBookings', 'upcomingBookings'));
    }

    /**
     * Show the form for editing the specified room
     *
     * @return \Illuminate\View\View
     */
    public function edit(MeetingRoom $room)
    {
        return view('admin.rooms.form', compact('room'));
    }

    /**
     * Update the specified room
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(RoomRequest $request, MeetingRoom $room)
    {
        $data = $request->only(['name', 'description', 'max_capacity', 'location', 'hourly_rate', 'is_active']);

        // Add image file if uploaded
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $data['image'] = $request->file('image');
        }

        $this->roomService->updateRoom($room, $data);

        // Clear active rooms cache
        $this->availabilityService->clearActiveRoomsCache();

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'ห้องประชุม "'.$room->name.'" ถูกอัพเดทเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified room
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(MeetingRoom $room)
    {
        // Check if room can be deleted
        if (! $this->roomService->canDeleteRoom($room)) {
            return redirect()
                ->route('admin.rooms.index')
                ->with('error', 'ไม่สามารถลบห้องประชุมที่มีการจองในอนาคตได้');
        }

        $roomName = $room->name;
        $this->roomService->deleteRoom($room);

        // Clear active rooms cache
        $this->availabilityService->clearActiveRoomsCache();

        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'ห้องประชุม "'.$roomName.'" ถูกลบเรียบร้อยแล้ว');
    }

    /**
     * Toggle room active status
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggle(MeetingRoom $room)
    {
        $this->roomService->toggleRoomStatus($room);

        // Clear active rooms cache
        $this->availabilityService->clearActiveRoomsCache();

        $status = $room->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน';

        return redirect()
            ->back()
            ->with('success', 'เปลี่ยนสถานะห้องประชุม "'.$room->name.'" เป็น'.$status.'เรียบร้อยแล้ว');
    }
}
