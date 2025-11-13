<?php

namespace Tests\Feature\Notifications;

use App\Events\BookingCreated;
use App\Jobs\SendLineNotificationJob;
use App\Models\Booking;
use App\Models\LineNotification;
use App\Models\MeetingRoom;
use App\Models\User;
use App\Services\LineNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LineNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Set Line API config for testing
        config(['services.line.channel_access_token' => 'test_token']);
        config(['services.line.channel_secret' => 'test_secret']);

        // Seed users and rooms
        $this->artisan('db:seed', ['--class' => 'UserSeeder']);
        $this->artisan('db:seed', ['--class' => 'RoomSeeder']);
    }

    /** @test */
    public function booking_created_event_is_dispatched_when_booking_is_created()
    {
        Event::fake([BookingCreated::class]);

        $guest = User::factory()->create(['role' => 'guest']);
        $room = MeetingRoom::first();

        $booking = Booking::create([
            'booking_ref' => Booking::generateBookingRef(),
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'attendee_count' => 10,
            'status' => 'confirmed',
        ]);

        event(new BookingCreated($booking));

        Event::assertDispatched(BookingCreated::class, function ($event) use ($booking) {
            return $event->booking->id === $booking->id;
        });
    }

    /** @test */
    public function notification_jobs_are_queued_when_booking_is_created()
    {
        Queue::fake();

        $guest = User::factory()->create(['role' => 'guest']);
        $room = MeetingRoom::first();

        $booking = Booking::create([
            'booking_ref' => Booking::generateBookingRef(),
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'attendee_count' => 10,
            'status' => 'confirmed',
        ]);

        event(new BookingCreated($booking));

        // Should dispatch jobs for both staff and guest
        Queue::assertPushed(SendLineNotificationJob::class, 2);
    }

    /** @test */
    public function line_notification_is_created_for_staff_with_line_id()
    {
        Http::fake([
            'https://api.line.me/*' => Http::response(['status' => 'success'], 200),
        ]);

        $staff = User::where('role', 'staff')->first();
        $staff->update(['line_user_id' => 'U1234567890abcdef1234567890abcdef']);

        $guest = User::factory()->create(['role' => 'guest']);
        $room = MeetingRoom::first();

        $booking = Booking::create([
            'booking_ref' => Booking::generateBookingRef(),
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'attendee_count' => 10,
            'decoration_theme' => 'Birthday Party',
            'status' => 'confirmed',
        ]);

        $lineService = app(LineNotificationService::class);
        $lineService->sendBookingCreatedNotification($booking);

        $this->assertDatabaseHas('line_notifications', [
            'booking_id' => $booking->id,
            'recipient_id' => $staff->id,
            'recipient_type' => 'staff',
            'notification_type' => 'booking_created',
        ]);
    }

    /** @test */
    public function line_notification_is_created_for_guest_with_line_id()
    {
        Http::fake([
            'https://api.line.me/*' => Http::response(['status' => 'success'], 200),
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => 'U1234567890abcdef1234567890abcdef',
        ]);

        $room = MeetingRoom::first();

        $booking = Booking::create([
            'booking_ref' => Booking::generateBookingRef(),
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'attendee_count' => 10,
            'status' => 'confirmed',
        ]);

        $lineService = app(LineNotificationService::class);
        $lineService->sendBookingConfirmation($booking);

        $this->assertDatabaseHas('line_notifications', [
            'booking_id' => $booking->id,
            'recipient_id' => $guest->id,
            'recipient_type' => 'guest',
            'notification_type' => 'booking_confirmed',
        ]);
    }

    /** @test */
    public function notification_is_not_sent_to_guest_without_line_id()
    {
        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => null,
        ]);

        $room = MeetingRoom::first();

        $booking = Booking::create([
            'booking_ref' => Booking::generateBookingRef(),
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'attendee_count' => 10,
            'status' => 'confirmed',
        ]);

        $lineService = app(LineNotificationService::class);
        $lineService->sendBookingConfirmation($booking);

        $this->assertDatabaseMissing('line_notifications', [
            'booking_id' => $booking->id,
            'recipient_id' => $guest->id,
        ]);
    }

    /** @test */
    public function notification_is_marked_as_sent_when_line_api_succeeds()
    {
        Http::fake([
            'https://api.line.me/*' => Http::response(['status' => 'success'], 200),
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => 'U1234567890abcdef1234567890abcdef',
        ]);

        $room = MeetingRoom::first();

        $booking = Booking::create([
            'booking_ref' => Booking::generateBookingRef(),
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'attendee_count' => 10,
            'status' => 'confirmed',
        ]);

        $lineService = app(LineNotificationService::class);
        $lineService->sendBookingConfirmation($booking);

        $notification = LineNotification::where('booking_id', $booking->id)->first();

        $this->assertEquals('sent', $notification->status);
        $this->assertNotNull($notification->sent_at);
    }

    /** @test */
    public function notification_is_marked_as_failed_when_line_api_fails()
    {
        Http::fake([
            'https://api.line.me/*' => Http::response(['message' => 'Invalid access token'], 401),
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => 'U1234567890abcdef1234567890abcdef',
        ]);

        $room = MeetingRoom::first();

        $booking = Booking::create([
            'booking_ref' => Booking::generateBookingRef(),
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'attendee_count' => 10,
            'status' => 'confirmed',
        ]);

        $lineService = app(LineNotificationService::class);
        $lineService->sendBookingConfirmation($booking);

        $notification = LineNotification::where('booking_id', $booking->id)->first();

        $this->assertEquals('failed', $notification->status);
        $this->assertNotNull($notification->error_message);
    }

    /** @test */
    public function admin_can_view_notification_history()
    {
        $admin = User::where('role', 'admin')->first();
        $guest = User::factory()->create(['role' => 'guest']);
        $room = MeetingRoom::first();

        $booking = Booking::create([
            'booking_ref' => Booking::generateBookingRef(),
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'attendee_count' => 10,
            'status' => 'confirmed',
        ]);

        LineNotification::create([
            'booking_id' => $booking->id,
            'recipient_id' => $guest->id,
            'recipient_type' => 'guest',
            'notification_type' => 'booking_confirmed',
            'message' => 'Test notification',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.notifications.history'));

        $response->assertStatus(200);
        $response->assertSee('ประวัติการแจ้งเตือน');
    }

    /** @test */
    public function admin_can_view_notification_settings()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->get(route('admin.notifications.settings'));

        $response->assertStatus(200);
        $response->assertSee('ตั้งค่าการแจ้งเตือน');
    }

    /** @test */
    public function staff_cannot_access_notification_management()
    {
        $staff = User::where('role', 'staff')->first();

        $response = $this->actingAs($staff)
            ->get(route('admin.notifications.history'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_retry_failed_notification()
    {
        Http::fake([
            'https://api.line.me/*' => Http::response(['status' => 'success'], 200),
        ]);

        $admin = User::where('role', 'admin')->first();
        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => 'U1234567890abcdef1234567890abcdef',
        ]);
        $room = MeetingRoom::first();

        $booking = Booking::create([
            'booking_ref' => Booking::generateBookingRef(),
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(10, 0),
            'end_datetime' => now()->addDays(1)->setTime(12, 0),
            'attendee_count' => 10,
            'status' => 'confirmed',
        ]);

        $notification = LineNotification::create([
            'booking_id' => $booking->id,
            'recipient_id' => $guest->id,
            'recipient_type' => 'guest',
            'notification_type' => 'booking_confirmed',
            'message' => 'Test notification',
            'status' => 'failed',
            'error_message' => 'Test error',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.notifications.retry', $notification));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $notification->refresh();
        $this->assertEquals('sent', $notification->status);
    }

    /** @test */
    public function notification_message_includes_booking_details()
    {
        $guest = User::factory()->create([
            'role' => 'guest',
            'name' => 'John Doe',
            'line_user_id' => 'U1234567890abcdef1234567890abcdef',
        ]);

        $room = MeetingRoom::first();

        $booking = Booking::create([
            'booking_ref' => Booking::generateBookingRef(),
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(1)->setTime(14, 0),
            'end_datetime' => now()->addDays(1)->setTime(16, 0),
            'attendee_count' => 20,
            'decoration_theme' => 'Corporate Meeting',
            'status' => 'confirmed',
        ]);

        Http::fake([
            'https://api.line.me/*' => Http::response(['status' => 'success'], 200),
        ]);

        $lineService = app(LineNotificationService::class);
        $lineService->sendBookingConfirmation($booking);

        $notification = LineNotification::where('booking_id', $booking->id)->first();

        $this->assertStringContainsString($guest->name, $notification->message);
        $this->assertStringContainsString($room->name, $notification->message);
        $this->assertStringContainsString('20', $notification->message); // attendee count
        $this->assertStringContainsString('Corporate Meeting', $notification->message);
    }
}
