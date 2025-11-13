<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\MeetingRoom;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarService
{
    /**
     * Format bookings for FullCalendar display
     */
    public function formatBookingsForCalendar(?Carbon $start = null, ?Carbon $end = null, ?int $roomId = null): array
    {
        $query = Booking::with(['user', 'room']);

        if ($start && $end) {
            $query->betweenDates($start, $end);
        }

        if ($roomId) {
            $query->where('room_id', $roomId);
        }

        $bookings = $query->get();

        return $bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'title' => sprintf(
                    '%s - %s',
                    $booking->room->name,
                    $booking->user->name
                ),
                'start' => $booking->start_datetime->toIso8601String(),
                'end' => $booking->end_datetime->toIso8601String(),
                'backgroundColor' => $this->getStatusColor($booking->status),
                'borderColor' => $this->getStatusColor($booking->status),
                'extendedProps' => [
                    'booking_ref' => $booking->booking_ref,
                    'room_name' => $booking->room->name,
                    'guest_name' => $booking->user->name,
                    'attendee_count' => $booking->attendee_count,
                    'status' => $booking->status,
                    'decoration_theme' => $booking->decoration_theme,
                    'notes' => $booking->notes,
                    'start_datetime' => $booking->start_datetime->format('Y-m-d H:i:s'),
                    'end_datetime' => $booking->end_datetime->format('Y-m-d H:i:s'),
                ],
            ];
        })->toArray();
    }

    /**
     * Get bookings grouped by room for a specific date
     */
    public function getBookingsByRoomForDate(Carbon $date): Collection
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        return MeetingRoom::active()
            ->with(['bookings' => function ($query) use ($startOfDay, $endOfDay) {
                $query->betweenDates($startOfDay, $endOfDay)
                    ->with('user')
                    ->orderBy('start_datetime');
            }])
            ->get();
    }

    /**
     * Get booking statistics for a date range
     */
    public function getBookingStatistics(Carbon $start, Carbon $end): array
    {
        $bookings = Booking::betweenDates($start, $end)->get();

        return [
            'total' => $bookings->count(),
            'confirmed' => $bookings->where('status', 'confirmed')->count(),
            'completed' => $bookings->where('status', 'completed')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
            'total_attendees' => $bookings->sum('attendee_count'),
            'totalRevenue' => 0, // Placeholder for future revenue calculation
            'averageAttendees' => $bookings->avg('attendee_count'),
            'mostBookedRoom' => $this->getMostBookedRoom($bookings),
            'popularThemes' => $this->getPopularThemes($bookings),
        ];
    }

    /**
     * Get available time slots for a room on a specific date
     */
    public function getAvailableTimeSlots(int $roomId, Carbon $date): array
    {
        $businessHours = config('booking.business_hours');
        $startTime = Carbon::parse($businessHours['start']);
        $endTime = Carbon::parse($businessHours['end']);

        $bookings = Booking::where('room_id', $roomId)
            ->betweenDates($date->copy()->startOfDay(), $date->copy()->endOfDay())
            ->whereIn('status', ['confirmed', 'completed'])
            ->orderBy('start_datetime')
            ->get();

        $slots = [];
        $currentTime = $date->copy()->setTimeFrom($startTime);
        $dayEnd = $date->copy()->setTimeFrom($endTime);

        foreach ($bookings as $booking) {
            if ($currentTime->lt($booking->start_datetime)) {
                $slots[] = [
                    'start' => $currentTime->toIso8601String(),
                    'end' => $booking->start_datetime->toIso8601String(),
                    'available' => true,
                ];
            }
            $currentTime = $booking->end_datetime->copy();
        }

        if ($currentTime->lt($dayEnd)) {
            $slots[] = [
                'start' => $currentTime->toIso8601String(),
                'end' => $dayEnd->toIso8601String(),
                'available' => true,
            ];
        }

        return $slots;
    }

    /**
     * Get color code based on booking status
     */
    protected function getStatusColor(string $status): string
    {
        return match ($status) {
            'confirmed' => '#28a745', // Green
            'completed' => '#17a2b8', // Blue
            'cancelled' => '#dc3545', // Red
            default => '#6c757d', // Grey
        };
    }

    /**
     * Get most booked room from bookings collection
     */
    protected function getMostBookedRoom(Collection $bookings): ?string
    {
        if ($bookings->isEmpty()) {
            return null;
        }

        $roomCounts = $bookings->groupBy('room_id')
            ->map(function ($group) {
                return [
                    'name' => $group->first()->room->name,
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('count');

        return $roomCounts->first()['name'] ?? null;
    }

    /**
     * Get popular decoration themes from bookings
     */
    protected function getPopularThemes(Collection $bookings): array
    {
        $themes = $bookings->whereNotNull('decoration_theme')
            ->pluck('decoration_theme')
            ->countBy()
            ->sortDesc()
            ->take(5)
            ->toArray();

        return $themes;
    }
}
