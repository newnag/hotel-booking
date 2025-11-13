<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\MeetingRoom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarViewTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected User $admin;

    protected User $guest;

    protected MeetingRoom $room;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->staff()->create();
        $this->admin = User::factory()->admin()->create();
        $this->guest = User::factory()->guest()->create();
        $this->room = MeetingRoom::factory()->create();
    }

    /** @test */
    public function staff_can_view_calendar_page()
    {
        $response = $this->actingAs($this->staff)
            ->get(route('staff.calendar.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.calendar.index');
    }

    /** @test */
    public function admin_can_view_calendar_page()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.calendar.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.calendar.index');
    }

    /** @test */
    public function guest_cannot_access_calendar()
    {
        $response = $this->actingAs($this->guest)
            ->get(route('staff.calendar.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_calendar()
    {
        $response = $this->get(route('staff.calendar.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function calendar_displays_todays_bookings()
    {
        $today = Carbon::today();

        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => $today->copy()->setHour(10),
            'end_datetime' => $today->copy()->setHour(12),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.calendar.index'));

        $response->assertStatus(200);
        $response->assertSee($booking->booking_ref);
    }

    /** @test */
    public function calendar_events_endpoint_returns_json()
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::now()->addDays(5)->setHour(14),
            'end_datetime' => Carbon::now()->addDays(5)->setHour(16),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.calendar.events', [
                'start' => $start->toIso8601String(),
                'end' => $end->toIso8601String(),
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'title',
                'start',
                'end',
                'backgroundColor',
                'borderColor',
                'extendedProps',
            ],
        ]);
    }

    /** @test */
    public function calendar_events_can_be_filtered_by_room()
    {
        $room1 = MeetingRoom::factory()->create(['name' => 'Room A']);
        $room2 = MeetingRoom::factory()->create(['name' => 'Room B']);

        $booking1 = Booking::factory()->create([
            'room_id' => $room1->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::now()->addDays(3),
            'end_datetime' => Carbon::now()->addDays(3)->addHours(2),
        ]);

        $booking2 = Booking::factory()->create([
            'room_id' => $room2->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::now()->addDays(3),
            'end_datetime' => Carbon::now()->addDays(3)->addHours(2),
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.calendar.events', [
                'start' => Carbon::now()->startOfMonth()->toIso8601String(),
                'end' => Carbon::now()->endOfMonth()->toIso8601String(),
                'room_id' => $room1->id,
            ]));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(1, $data);
        $this->assertEquals($booking1->id, $data[0]['id']);
    }

    /** @test */
    public function available_slots_endpoint_returns_time_slots()
    {
        $date = Carbon::tomorrow();

        $response = $this->actingAs($this->staff)
            ->get(route('staff.calendar.available-slots', [
                'room_id' => $this->room->id,
                'date' => $date->toDateString(),
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'slots' => [
                '*' => ['start', 'end', 'available'],
            ],
        ]);
    }

    /** @test */
    public function available_slots_shows_occupied_times()
    {
        $date = Carbon::tomorrow();

        Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => $date->copy()->setHour(10)->setMinute(0),
            'end_datetime' => $date->copy()->setHour(12)->setMinute(0),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.calendar.available-slots', [
                'room_id' => $this->room->id,
                'date' => $date->toDateString(),
            ]));

        $response->assertStatus(200);
        $slots = $response->json('slots');

        $this->assertNotEmpty($slots);

        // Verify that we have available slots around the booking
        // Booking is 10:00-12:00, so there should be no slots starting at 10:00
        $overlappingSlots = collect($slots)->filter(function ($slot) use ($date) {
            $slotStart = Carbon::parse($slot['start']);
            $slotEnd = Carbon::parse($slot['end']);
            $bookingStart = $date->copy()->setHour(10)->setMinute(0);
            $bookingEnd = $date->copy()->setHour(12)->setMinute(0);

            // Check if slot overlaps with booking
            return $slotStart->lt($bookingEnd) && $slotEnd->gt($bookingStart);
        });

        // There should be no available slots overlapping with the booking
        $this->assertEmpty($overlappingSlots);
    }

    /** @test */
    public function calendar_page_shows_room_filter_options()
    {
        $room1 = MeetingRoom::factory()->create(['name' => 'Conference Room A']);
        $room2 = MeetingRoom::factory()->create(['name' => 'Conference Room B']);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.calendar.index'));

        $response->assertStatus(200);
        $response->assertSee('Conference Room A');
        $response->assertSee('Conference Room B');
    }

    /** @test */
    public function calendar_events_include_extended_properties()
    {
        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::now()->addDays(2),
            'end_datetime' => Carbon::now()->addDays(2)->addHours(2),
            'decoration_theme' => 'งานแต่งงาน',
            'notes' => 'Important meeting',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.calendar.events', [
                'start' => Carbon::now()->startOfMonth()->toIso8601String(),
                'end' => Carbon::now()->endOfMonth()->toIso8601String(),
            ]));

        $response->assertStatus(200);
        $data = $response->json();

        $event = collect($data)->firstWhere('id', $booking->id);
        $this->assertNotNull($event);
        $this->assertEquals($booking->booking_ref, $event['extendedProps']['bookingRef']);
        $this->assertEquals('งานแต่งงาน', $event['extendedProps']['decorationTheme']);
        $this->assertArrayHasKey('notes', $event['extendedProps'] ?? []);
    }

    /** @test */
    public function calendar_shows_different_colors_for_booking_statuses()
    {
        $confirmedBooking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::now()->addDays(1),
            'end_datetime' => Carbon::now()->addDays(1)->addHours(2),
            'status' => 'confirmed',
        ]);

        $cancelledBooking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::now()->addDays(2),
            'end_datetime' => Carbon::now()->addDays(2)->addHours(2),
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.calendar.events', [
                'start' => Carbon::now()->startOfMonth()->toIso8601String(),
                'end' => Carbon::now()->endOfMonth()->toIso8601String(),
            ]));

        $response->assertStatus(200);
        $data = $response->json();

        $confirmed = collect($data)->firstWhere('id', $confirmedBooking->id);
        $cancelled = collect($data)->firstWhere('id', $cancelledBooking->id);

        $this->assertNotEquals($confirmed['backgroundColor'], $cancelled['backgroundColor']);
    }
}
