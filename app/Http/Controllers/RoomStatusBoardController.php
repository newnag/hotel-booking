<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\MeetingRoom;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoomStatusBoardController extends Controller
{
    /**
     * Display the room status board
     */
    public function index()
    {
        $now = Carbon::now();
        $rooms = MeetingRoom::active()->get();

        $roomStatuses = $rooms->map(function ($room) use ($now) {
            // Get current booking (happening now)
            $currentBooking = $room->bookings()
                ->where('status', 'confirmed')
                ->where('start_datetime', '<=', $now)
                ->where('end_datetime', '>', $now)
                ->with('user')
                ->first();

            // Get next booking (upcoming)
            $nextBooking = $room->bookings()
                ->where('status', 'confirmed')
                ->where('start_datetime', '>', $now)
                ->orderBy('start_datetime', 'asc')
                ->with('user')
                ->first();

            // Determine status
            $status = 'available'; // ว่าง
            $statusText = 'ว่าง';
            $statusColor = 'success';
            $currentUser = null;
            $endTime = null;
            $nextStartTime = null;
            $nextUser = null;

            if ($currentBooking) {
                $status = 'occupied'; // ไม่ว่าง/ถูกจอง
                $statusText = 'ไม่ว่าง';
                $statusColor = 'danger';
                $currentUser = $currentBooking->user->name;
                $endTime = $currentBooking->end_datetime;
            } elseif ($nextBooking) {
                $nextStartTime = $nextBooking->start_datetime;
                $nextUser = $nextBooking->user->name;
                
                $minutesUntilNext = $now->diffInMinutes($nextStartTime);
                
                if ($minutesUntilNext <= 30) {
                    // Coming soon (within 30 minutes)
                    $status = 'reserved';
                    $statusText = 'จองแล้ว (ใกล้ถึงเวลา)';
                    $statusColor = 'warning';
                } else {
                    // Reserved but not soon
                    $status = 'reserved';
                    $statusText = 'จองแล้ว';
                    $statusColor = 'info';
                }
            }

            return [
                'room' => $room,
                'status' => $status,
                'status_text' => $statusText,
                'status_color' => $statusColor,
                'current_booking' => $currentBooking,
                'current_user' => $currentUser,
                'end_time' => $endTime,
                'next_booking' => $nextBooking,
                'next_start_time' => $nextStartTime,
                'next_user' => $nextUser,
            ];
        });

        return view('room-status-board', compact('roomStatuses'));
    }

    /**
     * API endpoint for real-time status updates
     */
    public function apiStatus()
    {
        $now = Carbon::now();
        $rooms = MeetingRoom::active()->get();

        $roomStatuses = $rooms->map(function ($room) use ($now) {
            $currentBooking = $room->bookings()
                ->where('status', 'confirmed')
                ->where('start_datetime', '<=', $now)
                ->where('end_datetime', '>', $now)
                ->with('user')
                ->first();

            $nextBooking = $room->bookings()
                ->where('status', 'confirmed')
                ->where('start_datetime', '>', $now)
                ->orderBy('start_datetime', 'asc')
                ->with('user')
                ->first();

            $status = 'available';
            $statusText = 'ว่าง';
            $statusColor = 'success';

            if ($currentBooking) {
                $status = 'occupied';
                $statusText = 'ไม่ว่าง';
                $statusColor = 'danger';
            } elseif ($nextBooking) {
                $minutesUntilNext = $now->diffInMinutes($nextBooking->start_datetime);
                
                if ($minutesUntilNext <= 30) {
                    $status = 'reserved';
                    $statusText = 'จองแล้ว (ใกล้ถึงเวลา)';
                    $statusColor = 'warning';
                } else {
                    $status = 'reserved';
                    $statusText = 'จองแล้ว';
                    $statusColor = 'info';
                }
            }

            return [
                'id' => $room->id,
                'name' => $room->name,
                'status' => $status,
                'status_text' => $statusText,
                'status_color' => $statusColor,
                'current_user' => $currentBooking ? $currentBooking->user->name : null,
                'end_time' => $currentBooking ? $currentBooking->end_datetime->format('H:i') : null,
                'next_start_time' => $nextBooking ? $nextBooking->start_datetime->format('H:i') : null,
                'next_user' => $nextBooking ? $nextBooking->user->name : null,
            ];
        });

        return response()->json([
            'rooms' => $roomStatuses,
            'updated_at' => $now->toIso8601String(),
        ]);
    }

    /**
     * Display single room status
     */
    public function showRoom($id)
    {
        $room = MeetingRoom::findOrFail($id);
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();

        // Get current booking
        $currentBooking = $room->bookings()
            ->where('status', 'confirmed')
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>', $now)
            ->with('user')
            ->first();

        // Get next booking
        $nextBooking = $room->bookings()
            ->where('status', 'confirmed')
            ->where('start_datetime', '>', $now)
            ->orderBy('start_datetime', 'asc')
            ->with('user')
            ->first();

        // Get today's bookings
        $todayBookings = $room->bookings()
            ->where('status', 'confirmed')
            ->whereBetween('start_datetime', [$today, $endOfDay])
            ->with('user')
            ->orderBy('start_datetime')
            ->get();

        // Determine status
        $status = 'available';
        $statusText = 'ว่าง';
        $statusColor = 'success';

        if ($currentBooking) {
            $status = 'occupied';
            $statusText = 'ไม่ว่าง';
            $statusColor = 'danger';
        } elseif ($nextBooking) {
            $minutesUntilNext = $now->diffInMinutes($nextBooking->start_datetime);
            
            if ($minutesUntilNext <= 30) {
                // Coming soon (within 30 minutes)
                $status = 'reserved';
                $statusText = 'จองแล้ว (ใกล้ถึงเวลา)';
                $statusColor = 'warning';
            } else {
                // Reserved but not soon
                $status = 'reserved';
                $statusText = 'จองแล้ว';
                $statusColor = 'info';
            }
        }

        return view('room-status-single', compact(
            'room',
            'status',
            'statusText',
            'statusColor',
            'currentBooking',
            'nextBooking',
            'todayBookings'
        ));
    }

    /**
     * API endpoint for single room status
     */
    public function apiRoomStatus($id)
    {
        $room = MeetingRoom::findOrFail($id);
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        $endOfDay = $now->copy()->endOfDay();

        $currentBooking = $room->bookings()
            ->where('status', 'confirmed')
            ->where('start_datetime', '<=', $now)
            ->where('end_datetime', '>', $now)
            ->with('user')
            ->first();

        $nextBooking = $room->bookings()
            ->where('status', 'confirmed')
            ->where('start_datetime', '>', $now)
            ->orderBy('start_datetime', 'asc')
            ->with('user')
            ->first();

        $todayBookings = $room->bookings()
            ->where('status', 'confirmed')
            ->whereBetween('start_datetime', [$today, $endOfDay])
            ->with('user')
            ->orderBy('start_datetime')
            ->get()
            ->map(function ($booking) use ($now) {
                return [
                    'user' => $booking->user->name,
                    'start_time' => $booking->start_datetime->format('H:i'),
                    'end_time' => $booking->end_datetime->format('H:i'),
                    'is_current' => $booking->start_datetime <= $now && $booking->end_datetime > $now,
                ];
            });

        $status = 'available';
        $statusText = 'ว่าง';
        $statusColor = 'success';

        if ($currentBooking) {
            $status = 'occupied';
            $statusText = 'ไม่ว่าง';
            $statusColor = 'danger';
        } elseif ($nextBooking) {
            $minutesUntilNext = $now->diffInMinutes($nextBooking->start_datetime);
            
            if ($minutesUntilNext <= 30) {
                $status = 'reserved';
                $statusText = 'จองแล้ว (ใกล้ถึงเวลา)';
                $statusColor = 'warning';
            } else {
                $status = 'reserved';
                $statusText = 'จองแล้ว';
                $statusColor = 'info';
            }
        }

        return response()->json([
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'capacity' => $room->max_capacity,
                'location' => $room->location,
            ],
            'status' => $status,
            'status_text' => $statusText,
            'status_color' => $statusColor,
            'current_booking' => $currentBooking ? [
                'user' => $currentBooking->user->name,
                'end_time' => $currentBooking->end_datetime->format('H:i'),
            ] : null,
            'next_booking' => $nextBooking ? [
                'user' => $nextBooking->user->name,
                'start_time' => $nextBooking->start_datetime->format('H:i'),
            ] : null,
            'today_bookings' => $todayBookings,
            'updated_at' => $now->toIso8601String(),
        ]);
    }
}
