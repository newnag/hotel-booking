<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomAvailabilityController extends Controller
{
    protected RoomAvailabilityService $availabilityService;

    public function __construct(RoomAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Search for available rooms.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'attendee_count' => 'required|integer|min:1',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $availableRooms = $this->availabilityService->searchAvailableRooms(
                $request->attendee_count,
                $request->start_datetime,
                $request->end_datetime
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'total_rooms' => $availableRooms->count(),
                    'rooms' => $availableRooms->map(function ($room) {
                        return [
                            'id' => $room->id,
                            'name' => $room->name,
                            'description' => $room->description,
                            'location' => $room->location,
                            'capacity' => $room->max_capacity,
                            'image_path' => $room->image_path,
                            'is_active' => $room->is_active,
                        ];
                    }),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search available rooms',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all rooms with their current availability status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $rooms = \App\Models\MeetingRoom::where('is_active', true)
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_rooms' => $rooms->count(),
                    'rooms' => $rooms->map(function ($room) {
                        return [
                            'id' => $room->id,
                            'name' => $room->name,
                            'description' => $room->description,
                            'location' => $room->location,
                            'capacity' => $room->max_capacity,
                            'image_path' => $room->image_path,
                            'is_active' => $room->is_active,
                        ];
                    }),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve rooms',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a specific room is available for a time slot.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request, $roomId)
    {
        $validator = Validator::make($request->all(), [
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $room = \App\Models\MeetingRoom::findOrFail($roomId);

            $isAvailable = $this->availabilityService->isRoomAvailable(
                $roomId,
                $request->start_datetime,
                $request->end_datetime
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'room_id' => $room->id,
                    'room_name' => $room->name,
                    'is_available' => $isAvailable,
                    'time_slot' => [
                        'start_datetime' => $request->start_datetime,
                        'end_datetime' => $request->end_datetime,
                    ],
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check room availability',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
