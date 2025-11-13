<?php

namespace App\Services;

use App\Models\MeetingRoom;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RoomService
{
    /**
     * Get all rooms with optional filtering
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRooms(array $filters = [])
    {
        $query = MeetingRoom::query();

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Filter by minimum capacity
        if (isset($filters['min_capacity'])) {
            $query->where('capacity', '>=', $filters['min_capacity']);
        }

        // Search by name
        if (isset($filters['search'])) {
            $query->where('name', 'like', '%'.$filters['search'].'%');
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get paginated rooms for admin listing
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedRooms(array $filters = [], int $perPage = 15)
    {
        $query = MeetingRoom::query();

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Search by name or description
        if (isset($filters['search']) && ! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        // Order by
        $orderBy = $filters['order_by'] ?? 'name';
        $orderDir = $filters['order_dir'] ?? 'asc';
        $query->orderBy($orderBy, $orderDir);

        return $query->paginate($perPage);
    }

    /**
     * Create a new room
     */
    public function createRoom(array $data): MeetingRoom
    {
        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image_path'] = $this->uploadImage($data['image']);
            unset($data['image']);
        }

        return MeetingRoom::create($data);
    }

    /**
     * Update an existing room
     */
    public function updateRoom(MeetingRoom $room, array $data): MeetingRoom
    {
        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Delete old image
            if ($room->image_path) {
                $this->deleteImage($room->image_path);
            }

            $data['image_path'] = $this->uploadImage($data['image']);
            unset($data['image']);
        }

        $room->update($data);

        return $room->fresh();
    }

    /**
     * Delete a room
     */
    public function deleteRoom(MeetingRoom $room): bool
    {
        // Delete all related bookings first (to handle foreign key constraints)
        $room->bookings()->forceDelete();

        // Delete image if exists
        if ($room->image_path) {
            $this->deleteImage($room->image_path);
        }

        return $room->forceDelete(); // Permanently delete instead of soft delete
    }

    /**
     * Toggle room active status
     */
    public function toggleRoomStatus(MeetingRoom $room): MeetingRoom
    {
        $room->update(['is_active' => ! $room->is_active]);

        return $room->fresh();
    }

    /**
     * Check if room can be deleted (no future bookings)
     */
    public function canDeleteRoom(MeetingRoom $room): bool
    {
        // Check for future confirmed bookings
        $futureBookings = $room->bookings()
            ->where('start_datetime', '>', now())
            ->whereIn('status', ['confirmed'])
            ->count();

        return $futureBookings === 0;
    }

    /**
     * Get room statistics
     */
    public function getRoomStatistics(MeetingRoom $room): array
    {
        return [
            'total_bookings' => $room->bookings()->count(),
            'upcoming_bookings' => $room->bookings()
                ->where('start_datetime', '>', now())
                ->whereIn('status', ['confirmed'])
                ->count(),
            'completed_bookings' => $room->bookings()
                ->where('status', 'completed')
                ->count(),
            'total_attendees' => $room->bookings()
                ->where('status', 'completed')
                ->sum('attendee_count'),
        ];
    }

    /**
     * Upload room image
     */
    protected function uploadImage(UploadedFile $file): string
    {
        return $file->store('rooms', 'public');
    }

    /**
     * Delete room image
     */
    protected function deleteImage(string $path): bool
    {
        return Storage::disk('public')->delete($path);
    }
}
