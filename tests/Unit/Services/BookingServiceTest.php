<?php

namespace Tests\Unit\Services;

use App\Events\BookingCreated;
use App\Models\Booking;
use App\Models\MeetingRoom;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BookingService;
    }

    /** @test */
    public function it_creates_booking_successfully()
    {
        Event::fake();
        Log::spy();

        $user = User::factory()->create();
        $room = MeetingRoom::factory()->create(['max_capacity' => 50]);

        $data = [
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDays(1)->addHours(2)->format('Y-m-d H:i:s'),
            'attendee_count' => 20,
            'decoration_theme' => 'Corporate',
            'notes' => 'Test booking',
        ];

        $booking = $this->service->createBooking($user, $data);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals($user->id, $booking->user_id);
        $this->assertEquals($room->id, $booking->room_id);
        $this->assertEquals('confirmed', $booking->status);
        $this->assertNotNull($booking->booking_ref);

        Event::assertDispatched(BookingCreated::class);
    }

    /** @test */
    public function it_throws_exception_when_room_not_available()
    {
        $user = User::factory()->create();
        $room = MeetingRoom::factory()->create();

        // Create existing booking
        Booking::factory()->create([
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'status' => 'confirmed',
        ]);

        $data = [
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(11, 0)->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDays(1)->setTime(13, 0)->format('Y-m-d H:i:s'),
            'attendee_count' => 10,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Room is not available');

        $this->service->createBooking($user, $data);
    }

    /** @test */
    public function it_throws_exception_when_attendee_count_exceeds_capacity()
    {
        $user = User::factory()->create();
        $room = MeetingRoom::factory()->create(['max_capacity' => 10]);

        $data = [
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDays(1)->addHours(2)->format('Y-m-d H:i:s'),
            'attendee_count' => 20,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('exceeds room capacity');

        $this->service->createBooking($user, $data);
    }

    /** @test */
    public function is_room_available_returns_true_when_no_conflicts()
    {
        $room = MeetingRoom::factory()->create();

        $isAvailable = $this->service->isRoomAvailable(
            $room->id,
            now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i:s'),
            now()->addDays(1)->setTime(12, 0)->format('Y-m-d H:i:s')
        );

        $this->assertTrue($isAvailable);
    }

    /** @test */
    public function is_room_available_returns_false_when_has_conflict()
    {
        $room = MeetingRoom::factory()->create();

        Booking::factory()->create([
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'status' => 'confirmed',
        ]);

        $isAvailable = $this->service->isRoomAvailable(
            $room->id,
            now()->addDays(1)->setTime(11, 0)->format('Y-m-d H:i:s'),
            now()->addDays(1)->setTime(13, 0)->format('Y-m-d H:i:s')
        );

        $this->assertFalse($isAvailable);
    }

    /** @test */
    public function is_room_available_ignores_cancelled_bookings()
    {
        $room = MeetingRoom::factory()->create();

        Booking::factory()->create([
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'status' => 'cancelled',
        ]);

        $isAvailable = $this->service->isRoomAvailable(
            $room->id,
            now()->addDays(1)->setTime(11, 0)->format('Y-m-d H:i:s'),
            now()->addDays(1)->setTime(13, 0)->format('Y-m-d H:i:s')
        );

        $this->assertTrue($isAvailable);
    }

    /** @test */
    public function is_room_available_can_exclude_specific_booking()
    {
        $room = MeetingRoom::factory()->create();

        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'status' => 'confirmed',
        ]);

        $isAvailable = $this->service->isRoomAvailable(
            $room->id,
            now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i:s'),
            now()->addDays(1)->setTime(12, 0)->format('Y-m-d H:i:s'),
            $booking->id
        );

        $this->assertTrue($isAvailable);
    }

    /** @test */
    public function get_user_booking_history_returns_paginated_results()
    {
        $user = User::factory()->create();
        $room = MeetingRoom::factory()->create();

        Booking::factory()->count(15)->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
        ]);

        $history = $this->service->getUserBookingHistory($user, 10);

        $this->assertEquals(15, $history->total());
        $this->assertEquals(10, $history->perPage());
        $this->assertCount(10, $history->items());
    }

    /** @test */
    public function cancel_booking_updates_status_to_cancelled()
    {
        Log::spy();

        $booking = Booking::factory()->create(['status' => 'confirmed']);

        $result = $this->service->cancelBooking($booking);

        $this->assertTrue($result);
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
    }

    /** @test */
    public function get_upcoming_bookings_returns_only_future_confirmed_bookings()
    {
        $user = User::factory()->create();
        $room = MeetingRoom::factory()->create();

        $futureConfirmed = Booking::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'status' => 'confirmed',
            'start_datetime' => now()->addDays(5),
        ]);

        $pastConfirmed = Booking::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'status' => 'confirmed',
            'start_datetime' => now()->subDays(5),
        ]);

        $futureCancelled = Booking::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'status' => 'cancelled',
            'start_datetime' => now()->addDays(5),
        ]);

        $upcomingBookings = $this->service->getUpcomingBookings($user);

        $this->assertCount(1, $upcomingBookings);
        $this->assertTrue($upcomingBookings->contains($futureConfirmed));
        $this->assertFalse($upcomingBookings->contains($pastConfirmed));
        $this->assertFalse($upcomingBookings->contains($futureCancelled));
    }

    /** @test */
    public function create_booking_generates_unique_booking_ref()
    {
        $user = User::factory()->create();
        $room = MeetingRoom::factory()->create(['max_capacity' => 50]);

        $data1 = [
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDays(1)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'attendee_count' => 20,
        ];

        $data2 = [
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(2)->setTime(10, 0)->format('Y-m-d H:i:s'),
            'end_datetime' => now()->addDays(2)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'attendee_count' => 20,
        ];

        Event::fake();

        $booking1 = $this->service->createBooking($user, $data1);
        $booking2 = $this->service->createBooking($user, $data2);

        $this->assertNotEquals($booking1->booking_ref, $booking2->booking_ref);
    }
}
