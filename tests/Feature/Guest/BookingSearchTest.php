<?php

namespace Tests\Feature\Guest;

use App\Models\Booking;
use App\Models\MeetingRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->guest()->create([
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function search_returns_only_active_rooms()
    {
        $activeRoom = MeetingRoom::factory()->create(['is_active' => true]);
        $inactiveRoom = MeetingRoom::factory()->inactive()->create();

        $startDatetime = now()->addDays(2)->setHour(10)->setMinute(0);
        $endDatetime = $startDatetime->copy()->addHours(2);

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 10,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);
        $response->assertSee($activeRoom->name);
        $response->assertDontSee($inactiveRoom->name);
    }

    /** @test */
    public function search_returns_rooms_with_sufficient_capacity()
    {
        $smallRoom = MeetingRoom::factory()->small()->create(['max_capacity' => 20]);
        $largeRoom = MeetingRoom::factory()->large()->create(['max_capacity' => 100]);

        $startDatetime = now()->addDays(2)->setHour(10)->setMinute(0);
        $endDatetime = $startDatetime->copy()->addHours(2);

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 50,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);
        $response->assertDontSee($smallRoom->name);
        $response->assertSee($largeRoom->name);
    }

    /** @test */
    public function search_excludes_rooms_with_conflicting_bookings()
    {
        $room1 = MeetingRoom::factory()->create();
        $room2 = MeetingRoom::factory()->create();

        $searchStart = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);
        $searchEnd = $searchStart->copy()->addHours(2);

        // Create booking that conflicts with search time
        Booking::factory()->forRoom($room1)->create([
            'start_datetime' => $searchStart,
            'end_datetime' => $searchEnd,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 10,
                'start_datetime' => $searchStart->format('Y-m-d H:i:s'),
                'end_datetime' => $searchEnd->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);
        $response->assertDontSee($room1->name);
        $response->assertSee($room2->name);
    }

    /** @test */
    public function search_includes_rooms_with_cancelled_bookings()
    {
        $room = MeetingRoom::factory()->create();

        $searchStart = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);
        $searchEnd = $searchStart->copy()->addHours(2);

        // Create cancelled booking
        Booking::factory()->forRoom($room)->cancelled()->create([
            'start_datetime' => $searchStart,
            'end_datetime' => $searchEnd,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 10,
                'start_datetime' => $searchStart->format('Y-m-d H:i:s'),
                'end_datetime' => $searchEnd->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);
        $response->assertSee($room->name);
    }

    /** @test */
    public function search_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), []);

        $response->assertSessionHasErrors(['attendee_count', 'start_datetime', 'end_datetime']);
    }

    /** @test */
    public function search_validates_attendee_count_minimum()
    {
        $startDatetime = now()->addDays(2)->setHour(10)->setMinute(0);
        $endDatetime = $startDatetime->copy()->addHours(2);

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 0,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
            ]);

        $response->assertSessionHasErrors('attendee_count');
    }

    /** @test */
    public function search_validates_start_datetime_is_in_future()
    {
        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 10,
                'start_datetime' => now()->subHour()->format('Y-m-d H:i:s'),
                'end_datetime' => now()->format('Y-m-d H:i:s'),
            ]);

        $response->assertSessionHasErrors('start_datetime');
    }

    /** @test */
    public function search_validates_end_datetime_is_after_start()
    {
        $startDatetime = now()->addDays(2)->setHour(10)->setMinute(0);

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 10,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $startDatetime->subHour()->format('Y-m-d H:i:s'),
            ]);

        $response->assertSessionHasErrors('end_datetime');
    }

    /** @test */
    public function search_handles_partial_overlap_correctly()
    {
        $room = MeetingRoom::factory()->create();

        // Existing booking: 10:00 - 12:00
        $existingStart = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);
        $existingEnd = $existingStart->copy()->addHours(2);

        Booking::factory()->forRoom($room)->create([
            'start_datetime' => $existingStart,
            'end_datetime' => $existingEnd,
            'status' => 'confirmed',
        ]);

        // Search 1: 09:00 - 11:00 (overlaps with existing)
        $search1Start = $existingStart->copy()->subHour();
        $search1End = $existingStart->copy()->addHour();

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 10,
                'start_datetime' => $search1Start->format('Y-m-d H:i:s'),
                'end_datetime' => $search1End->format('Y-m-d H:i:s'),
            ]);

        $response->assertDontSee($room->name);

        // Search 2: 08:00 - 09:30 (no overlap)
        $search2Start = $existingStart->copy()->subHours(2);
        $search2End = $existingStart->copy()->subMinutes(30);

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 10,
                'start_datetime' => $search2Start->format('Y-m-d H:i:s'),
                'end_datetime' => $search2End->format('Y-m-d H:i:s'),
            ]);

        $response->assertSee($room->name);
    }

    /** @test */
    public function search_shows_no_results_message_when_no_rooms_available()
    {
        // Create room but don't meet capacity requirement
        MeetingRoom::factory()->create(['max_capacity' => 10]);

        $startDatetime = now()->addDays(2)->setHour(10)->setMinute(0);
        $endDatetime = $startDatetime->copy()->addHours(2);

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 100,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);
        $response->assertSee('No Rooms Available');
    }

    /** @test */
    public function search_displays_all_available_rooms()
    {
        $rooms = MeetingRoom::factory()->count(3)->create([
            'is_active' => true,
            'max_capacity' => 50,
        ]);

        $startDatetime = now()->addDays(2)->setHour(10)->setMinute(0);
        $endDatetime = $startDatetime->copy()->addHours(2);

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 20,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);

        foreach ($rooms as $room) {
            $response->assertSee($room->name);
        }
    }
}
