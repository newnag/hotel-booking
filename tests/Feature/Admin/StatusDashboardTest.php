<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\MeetingRoom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusDashboardTest extends TestCase
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
    public function staff_can_view_status_overview()
    {
        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.overview'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.status.overview');
    }

    /** @test */
    public function admin_can_view_status_overview()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.status.overview'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.status.overview');
    }

    /** @test */
    public function guest_cannot_access_status_overview()
    {
        $response = $this->actingAs($this->guest)
            ->get(route('staff.status.overview'));

        $response->assertStatus(403);
    }

    /** @test */
    public function status_overview_shows_today_statistics()
    {
        $today = Carbon::today();

        Booking::factory()->count(3)->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => $today->copy()->setHour(10),
            'end_datetime' => $today->copy()->setHour(12),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.overview'));

        $response->assertStatus(200);
        $response->assertViewHas('todayStats');
        $todayStats = $response->viewData('todayStats');
        $this->assertEquals(3, $todayStats['total']);
    }

    /** @test */
    public function status_overview_shows_monthly_statistics()
    {
        $thisMonth = Carbon::now()->startOfMonth();

        Booking::factory()->count(5)->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => $thisMonth->copy()->addDays(5)->setHour(10),
            'end_datetime' => $thisMonth->copy()->addDays(5)->setHour(12),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.overview'));

        $response->assertStatus(200);
        $response->assertViewHas('monthStats');
        $monthStats = $response->viewData('monthStats');
        $this->assertEquals(5, $monthStats['total']);
    }

    /** @test */
    public function status_overview_shows_room_availability()
    {
        $room1 = MeetingRoom::factory()->create(['name' => 'Available Room']);
        $room2 = MeetingRoom::factory()->create(['name' => 'Occupied Room']);

        // Create booking for room2 that is currently active
        Booking::factory()->create([
            'room_id' => $room2->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::now()->subHour(),
            'end_datetime' => Carbon::now()->addHour(),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.overview'));

        $response->assertStatus(200);
        $response->assertSee('Available Room');
        $response->assertSee('Occupied Room');
    }

    /** @test */
    public function staff_can_view_room_detail_page()
    {
        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.room', $this->room->id));

        $response->assertStatus(200);
        $response->assertViewIs('admin.status.room-detail');
        $response->assertViewHas('room');
    }

    /** @test */
    public function room_detail_shows_todays_bookings()
    {
        $today = Carbon::today();

        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => $today->copy()->setHour(14),
            'end_datetime' => $today->copy()->setHour(16),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.room', $this->room->id));

        $response->assertStatus(200);
        $response->assertSee($booking->booking_ref);
        $response->assertSee($this->guest->name);
    }

    /** @test */
    public function room_detail_shows_available_time_slots()
    {
        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.room', $this->room->id));

        $response->assertStatus(200);
        $response->assertViewHas('availableSlots');
        $slots = $response->viewData('availableSlots');
        $this->assertIsArray($slots);
        $this->assertNotEmpty($slots);
    }

    /** @test */
    public function room_detail_shows_upcoming_bookings()
    {
        $futureBooking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::now()->addDays(3)->setHour(10),
            'end_datetime' => Carbon::now()->addDays(3)->setHour(12),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.room', $this->room->id));

        $response->assertStatus(200);
        $response->assertSee($futureBooking->booking_ref);
    }

    /** @test */
    public function room_detail_shows_booking_history()
    {
        $pastBooking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::now()->subDays(5)->setHour(10),
            'end_datetime' => Carbon::now()->subDays(5)->setHour(12),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.room', $this->room->id));

        $response->assertStatus(200);
        $response->assertSee($pastBooking->booking_ref);
    }

    /** @test */
    public function status_overview_displays_todays_schedule()
    {
        $today = Carbon::today();

        $booking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => $today->copy()->setHour(9),
            'end_datetime' => $today->copy()->setHour(11),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.overview'));

        $response->assertStatus(200);
        $response->assertViewHas('todayBookings');
        $todayBookings = $response->viewData('todayBookings');
        $this->assertTrue($todayBookings->contains($booking));
    }

    /** @test */
    public function status_overview_displays_upcoming_bookings()
    {
        $upcomingBooking = Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::now()->addDays(2)->setHour(14),
            'end_datetime' => Carbon::now()->addDays(2)->setHour(16),
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.overview'));

        $response->assertStatus(200);
        $response->assertViewHas('upcomingBookings');
        $upcomingBookings = $response->viewData('upcomingBookings');
        $this->assertTrue($upcomingBookings->contains($upcomingBooking));
    }

    /** @test */
    public function room_detail_returns_404_for_nonexistent_room()
    {
        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.room', 99999));

        $response->assertStatus(404);
    }

    /** @test */
    public function status_overview_shows_cancelled_bookings_separately()
    {
        $confirmed = Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::today()->setHour(10),
            'end_datetime' => Carbon::today()->setHour(12),
            'status' => 'confirmed',
        ]);

        $cancelled = Booking::factory()->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::today()->setHour(14),
            'end_datetime' => Carbon::today()->setHour(16),
            'status' => 'cancelled',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.overview'));

        $response->assertStatus(200);
        $todayStats = $response->viewData('todayStats');
        $this->assertEquals(1, $todayStats['confirmed']);
        $this->assertEquals(1, $todayStats['cancelled']);
    }

    /** @test */
    public function room_detail_pagination_works_for_history()
    {
        // Create 20 past bookings
        Booking::factory()->count(20)->create([
            'room_id' => $this->room->id,
            'user_id' => $this->guest->id,
            'start_datetime' => Carbon::now()->subDays(10)->setHour(10),
            'end_datetime' => Carbon::now()->subDays(10)->setHour(12),
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->staff)
            ->get(route('staff.status.room', $this->room->id));

        $response->assertStatus(200);
        $response->assertViewHas('bookingHistory');
        $history = $response->viewData('bookingHistory');
        $this->assertEquals(15, $history->perPage()); // Default pagination
    }
}
