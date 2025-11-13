<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\MeetingRoom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeetingRoomTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'name',
            'description',
            'max_capacity',
            'location',
            'is_active',
            'image_path',
        ];

        $room = new MeetingRoom;

        $this->assertEquals($fillable, $room->getFillable());
    }

    /** @test */
    public function it_casts_is_active_to_boolean()
    {
        $room = MeetingRoom::factory()->create(['is_active' => 1]);

        $this->assertIsBool($room->is_active);
        $this->assertTrue($room->is_active);
    }

    /** @test */
    public function it_casts_max_capacity_to_integer()
    {
        $room = MeetingRoom::factory()->create(['max_capacity' => '50']);

        $this->assertIsInt($room->max_capacity);
        $this->assertEquals(50, $room->max_capacity);
    }

    /** @test */
    public function it_has_many_bookings()
    {
        $room = MeetingRoom::factory()->create();
        $booking1 = Booking::factory()->create(['room_id' => $room->id]);
        $booking2 = Booking::factory()->create(['room_id' => $room->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $room->bookings);
        $this->assertCount(2, $room->bookings);
        $this->assertTrue($room->bookings->contains($booking1));
        $this->assertTrue($room->bookings->contains($booking2));
    }

    /** @test */
    public function active_scope_filters_active_rooms_only()
    {
        $activeRoom = MeetingRoom::factory()->create(['is_active' => true]);
        $inactiveRoom = MeetingRoom::factory()->create(['is_active' => false]);

        $activeRooms = MeetingRoom::active()->get();

        $this->assertCount(1, $activeRooms);
        $this->assertTrue($activeRooms->contains($activeRoom));
        $this->assertFalse($activeRooms->contains($inactiveRoom));
    }

    /** @test */
    public function with_capacity_scope_filters_by_minimum_capacity()
    {
        $smallRoom = MeetingRoom::factory()->create(['max_capacity' => 10]);
        $mediumRoom = MeetingRoom::factory()->create(['max_capacity' => 20]);
        $largeRoom = MeetingRoom::factory()->create(['max_capacity' => 50]);

        $roomsWithCapacity = MeetingRoom::withCapacity(15)->get();

        $this->assertCount(2, $roomsWithCapacity);
        $this->assertFalse($roomsWithCapacity->contains($smallRoom));
        $this->assertTrue($roomsWithCapacity->contains($mediumRoom));
        $this->assertTrue($roomsWithCapacity->contains($largeRoom));
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $room = MeetingRoom::factory()->create();
        $roomId = $room->id;

        $room->delete();

        $this->assertSoftDeleted('meeting_rooms', ['id' => $roomId]);
        $this->assertNotNull($room->fresh()->deleted_at);
    }

    /** @test */
    public function it_can_be_force_deleted()
    {
        $room = MeetingRoom::factory()->create();
        $roomId = $room->id;

        $room->forceDelete();

        $this->assertDatabaseMissing('meeting_rooms', ['id' => $roomId]);
    }

    /** @test */
    public function it_can_combine_scopes()
    {
        $activeSmallRoom = MeetingRoom::factory()->create([
            'is_active' => true,
            'max_capacity' => 10,
        ]);
        $activeLargeRoom = MeetingRoom::factory()->create([
            'is_active' => true,
            'max_capacity' => 50,
        ]);
        $inactiveLargeRoom = MeetingRoom::factory()->create([
            'is_active' => false,
            'max_capacity' => 50,
        ]);

        $rooms = MeetingRoom::active()->withCapacity(20)->get();

        $this->assertCount(1, $rooms);
        $this->assertTrue($rooms->contains($activeLargeRoom));
        $this->assertFalse($rooms->contains($activeSmallRoom));
        $this->assertFalse($rooms->contains($inactiveLargeRoom));
    }
}
