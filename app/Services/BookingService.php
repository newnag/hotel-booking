<?php

namespace App\Services;

use App\Events\BookingCreated;
use App\Models\Booking;
use App\Models\MeetingRoom;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingService
{
    /**
     * Create a new booking.
     *
     * @throws \Exception
     */
    public function createBooking(User $user, array $data): Booking
    {
        try {
            DB::beginTransaction();

            // Validate room availability
            $room = MeetingRoom::findOrFail($data['room_id']);

            if (! $this->isRoomAvailable($room->id, $data['start_datetime'], $data['end_datetime'])) {
                throw new \Exception(__('Room is not available for the selected time slot.'));
            }

            // Validate attendee count against room capacity
            if ($data['attendee_count'] > $room->max_capacity) {
                throw new \Exception(__('Attendee count exceeds room capacity.'));
            }

            // Create booking
            $booking = Booking::create([
                'booking_ref' => Booking::generateBookingRef(),
                'user_id' => $user->id,
                'room_id' => $data['room_id'],
                'start_datetime' => $data['start_datetime'],
                'end_datetime' => $data['end_datetime'],
                'attendee_count' => $data['attendee_count'],
                'decoration_theme' => $data['decoration_theme'] ?? null,
                'status' => 'confirmed',
                'notes' => $data['notes'] ?? null,
            ]);

            DB::commit();

            // Dispatch event for notifications
            event(new BookingCreated($booking));

            Log::info('Booking created', [
                'booking_id' => $booking->id,
                'booking_ref' => $booking->booking_ref,
                'user_id' => $user->id,
            ]);

            return $booking;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Booking creation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
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
                $q->whereBetween('start_datetime', [$startDatetime, $endDatetime])
                    ->orWhereBetween('end_datetime', [$startDatetime, $endDatetime])
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
     * Get user's booking history.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getUserBookingHistory(User $user, int $perPage = 10)
    {
        return Booking::with(['room'])
            ->where('user_id', $user->id)
            ->orderBy('start_datetime', 'desc')
            ->paginate($perPage);
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Booking $booking): bool
    {
        try {
            $booking->update(['status' => 'cancelled']);

            Log::info('Booking cancelled', [
                'booking_id' => $booking->id,
                'booking_ref' => $booking->booking_ref,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Booking cancellation failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get upcoming bookings for a user.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUpcomingBookings(User $user)
    {
        return Booking::with(['room'])
            ->where('user_id', $user->id)
            ->where('status', 'confirmed')
            ->where('start_datetime', '>', now())
            ->orderBy('start_datetime', 'asc')
            ->get();
    }
}
