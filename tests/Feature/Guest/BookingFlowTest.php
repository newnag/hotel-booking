<?php

namespace Tests\Feature\Guest;

use App\Models\Booking;
use App\Models\MeetingRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected MeetingRoom $room;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user (guest)
        $this->user = User::factory()->guest()->create([
            'email_verified_at' => now(),
        ]);

        // Create a test room
        $this->room = MeetingRoom::factory()->create([
            'name' => 'Test Conference Room',
            'max_capacity' => 50,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function guest_can_view_dashboard()
    {
        $response = $this->actingAs($this->user)
            ->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee('Search Available Rooms');
    }

    /** @test */
    public function guest_can_access_room_search_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('guest.booking.search'));

        $response->assertStatus(200);
        $response->assertSee('Search Available Meeting Rooms');
        $response->assertSee('Number of Attendees');
    }

    /** @test */
    public function guest_can_search_for_available_rooms()
    {
        $startDatetime = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);
        $endDatetime = $startDatetime->copy()->addHours(2);

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 30,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
            ]);

        $response->assertStatus(200);
        $response->assertSee('Available Meeting Rooms');
        $response->assertSee($this->room->name);
    }

    /** @test */
    public function guest_can_view_booking_creation_form()
    {
        $startDatetime = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);
        $endDatetime = $startDatetime->copy()->addHours(2);

        $response = $this->actingAs($this->user)
            ->get(route('guest.booking.create', $this->room).'?'.http_build_query([
                'attendee_count' => 30,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
            ]));

        $response->assertStatus(200);
        $response->assertSee($this->room->name);
        $response->assertSee('Create Booking');
        $response->assertSee('Decoration Theme');
    }

    /** @test */
    public function guest_can_create_booking()
    {
        $startDatetime = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);
        $endDatetime = $startDatetime->copy()->addHours(2);

        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.store'), [
                'room_id' => $this->room->id,
                'attendee_count' => 30,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
                'decoration_theme' => 'ธีมธุรกิจ (Business Theme)',
                'notes' => 'Important client meeting',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'user_id' => $this->user->id,
            'room_id' => $this->room->id,
            'attendee_count' => 30,
            'status' => 'confirmed',
        ]);
    }

    /** @test */
    public function guest_can_view_booking_history()
    {
        // Create some bookings
        $booking = Booking::factory()->forUser($this->user)->forRoom($this->room)->create();

        $response = $this->actingAs($this->user)
            ->get(route('guest.booking.history'));

        $response->assertStatus(200);
        $response->assertSee('My Booking History');
        $response->assertSee($booking->booking_ref);
    }

    /** @test */
    public function guest_can_view_specific_booking()
    {
        $booking = Booking::factory()->forUser($this->user)->forRoom($this->room)->create();

        $response = $this->actingAs($this->user)
            ->get(route('guest.booking.show', $booking->id));

        $response->assertStatus(200);
        $response->assertSee($booking->booking_ref);
        $response->assertSee($this->room->name);
    }

    /** @test */
    public function guest_can_cancel_future_booking()
    {
        $booking = Booking::factory()
            ->forUser($this->user)
            ->forRoom($this->room)
            ->tomorrow()
            ->confirmed()
            ->create();

        $response = $this->actingAs($this->user)
            ->delete(route('guest.booking.cancel', $booking->id));

        $response->assertRedirect(route('guest.booking.history'));
        $response->assertSessionHas('success');

        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
    }

    /** @test */
    public function guest_cannot_book_unavailable_room()
    {
        $startDatetime = now()->addDays(2)->setHour(10)->setMinute(0)->setSecond(0);
        $endDatetime = $startDatetime->copy()->addHours(2);

        // Create existing booking
        Booking::factory()->forRoom($this->room)->create([
            'start_datetime' => $startDatetime,
            'end_datetime' => $endDatetime,
            'status' => 'confirmed',
        ]);

        // Try to book the same time slot
        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.store'), [
                'room_id' => $this->room->id,
                'attendee_count' => 30,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
            ]);

        $response->assertSessionHas('error');
    }

    /** @test */
    public function guest_cannot_book_with_invalid_data()
    {
        // Past datetime
        $response = $this->actingAs($this->user)
            ->post(route('guest.booking.store'), [
                'room_id' => $this->room->id,
                'attendee_count' => 30,
                'start_datetime' => now()->subHour()->format('Y-m-d H:i:s'),
                'end_datetime' => now()->format('Y-m-d H:i:s'),
            ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function guest_cannot_view_another_users_booking()
    {
        $otherUser = User::factory()->guest()->create();
        $booking = Booking::factory()->forUser($otherUser)->forRoom($this->room)->create();

        $response = $this->actingAs($this->user)
            ->get(route('guest.booking.show', $booking->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_booking_features()
    {
        $response = $this->get(route('guest.booking.search'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('guest.booking.history'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function complete_booking_flow_works_end_to_end()
    {
        // 1. Guest logs in and views dashboard
        $this->actingAs($this->user)
            ->get(route('dashboard'))
            ->assertStatus(200);

        // 2. Guest searches for rooms
        $startDatetime = now()->addDays(3)->setHour(14)->setMinute(0)->setSecond(0);
        $endDatetime = $startDatetime->copy()->addHours(3);

        $this->actingAs($this->user)
            ->post(route('guest.booking.search.results'), [
                'attendee_count' => 40,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
            ])
            ->assertStatus(200)
            ->assertSee($this->room->name);

        // 3. Guest creates booking
        $this->actingAs($this->user)
            ->post(route('guest.booking.store'), [
                'room_id' => $this->room->id,
                'attendee_count' => 40,
                'start_datetime' => $startDatetime->format('Y-m-d H:i:s'),
                'end_datetime' => $endDatetime->format('Y-m-d H:i:s'),
                'decoration_theme' => 'ธีมสีฟ้า - ขาว',
                'notes' => 'Company annual meeting',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        // 4. Verify booking exists
        $booking = Booking::where('user_id', $this->user->id)->latest()->first();
        $this->assertNotNull($booking);
        $this->assertEquals('confirmed', $booking->status);

        // 5. Guest views booking
        $this->actingAs($this->user)
            ->get(route('guest.booking.show', $booking->id))
            ->assertStatus(200)
            ->assertSee($booking->booking_ref);

        // 6. Guest views booking history
        $this->actingAs($this->user)
            ->get(route('guest.booking.history'))
            ->assertStatus(200)
            ->assertSee($booking->booking_ref);
    }
}
