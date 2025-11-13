<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\MeetingRoom;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class RoomAvailabilityService
{
    /**
     * Search for available rooms by capacity and date range.
     */
    public function searchAvailableRooms(
        int $minCapacity,
        string $startDatetime,
        string $endDatetime
    ): Collection {
        // Get all active rooms with sufficient capacity
        $rooms = MeetingRoom::active()
            ->withCapacity($minCapacity)
            ->get();

        // Filter out rooms that have conflicting bookings
        return $rooms->filter(function ($room) use ($startDatetime, $endDatetime) {
            return $this->isRoomAvailable($room->id, $startDatetime, $endDatetime);
        });
    }

    /**
     * Check if a room is available for the given time slot.
     */
    public function isRoomAvailable(
        int $roomId,
        string $startDatetime,
        string $endDatetime,
        ?int $excludeBookingId = null
    ): bool {
        $query = Booking::where('room_id', $roomId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startDatetime, $endDatetime) {
                // Check for overlapping bookings
                // Case 1: New booking starts during existing booking
                $q->whereBetween('start_datetime', [$startDatetime, $endDatetime])
                    // Case 2: New booking ends during existing booking
                    ->orWhereBetween('end_datetime', [$startDatetime, $endDatetime])
                    // Case 3: New booking completely encompasses existing booking
                    ->orWhere(function ($q2) use ($startDatetime, $endDatetime) {
                        $q2->where('start_datetime', '<=', $startDatetime)
                            ->where('end_datetime', '>=', $endDatetime);
                    });
            });

        if ($excludeBookingId) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->count() === 0;
    }

    /**
     * Get all active rooms.
     */
    public function getAllActiveRooms(): Collection
    {
        return Cache::remember('meeting_rooms.active', now()->addHours(24), function () {
            return MeetingRoom::active()
                ->orderBy('max_capacity', 'desc')
                ->get();
        });
    }

    /**
     * Clear active rooms cache.
     * Call this when a room is created, updated, or deleted.
     */
    public function clearActiveRoomsCache(): void
    {
        Cache::forget('meeting_rooms.active');
    }

    /**
     * Get room availability status for a specific date.
     */
    public function getRoomDayAvailability(int $roomId, string $date): array
    {
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay = Carbon::parse($date)->endOfDay();

        $bookings = Booking::where('room_id', $roomId)
            ->where('status', '!=', 'cancelled')
            ->whereBetween('start_datetime', [$startOfDay, $endOfDay])
            ->orderBy('start_datetime')
            ->get(['start_datetime', 'end_datetime']);

        $slots = [];
        $businessStart = config('booking.business_hours.start', '08:00');
        $businessEnd = config('booking.business_hours.end', '20:00');

        $currentTime = Carbon::parse($date.' '.$businessStart);
        $endTime = Carbon::parse($date.' '.$businessEnd);

        foreach ($bookings as $booking) {
            if ($currentTime < $booking->start_datetime) {
                $slots[] = [
                    'start' => $currentTime->toDateTimeString(),
                    'end' => $booking->start_datetime->toDateTimeString(),
                    'available' => true,
                ];
            }
            $currentTime = $booking->end_datetime > $currentTime ? $booking->end_datetime : $currentTime;
        }

        // Add remaining time slot if any
        if ($currentTime < $endTime) {
            $slots[] = [
                'start' => $currentTime->toDateTimeString(),
                'end' => $endTime->toDateTimeString(),
                'available' => true,
            ];
        }

        return $slots;
    }

    /**
     * Get booking conflicts for a room in a date range.
     */
    public function getBookingConflicts(
        int $roomId,
        string $startDatetime,
        string $endDatetime
    ): Collection {
        return Booking::with(['user'])
            ->where('room_id', $roomId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startDatetime, $endDatetime) {
                $q->whereBetween('start_datetime', [$startDatetime, $endDatetime])
                    ->orWhereBetween('end_datetime', [$startDatetime, $endDatetime])
                    ->orWhere(function ($q2) use ($startDatetime, $endDatetime) {
                        $q2->where('start_datetime', '<=', $startDatetime)
                            ->where('end_datetime', '>=', $endDatetime);
                    });
            })
            ->get();
    }
}
