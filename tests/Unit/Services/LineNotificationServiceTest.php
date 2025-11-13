<?php

namespace Tests\Unit\Services;

use App\Models\Booking;
use App\Models\LineNotification;
use App\Models\MeetingRoom;
use App\Models\User;
use App\Services\LineNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LineNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LineNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Set fake Line API token for testing BEFORE instantiating service
        config(['services.line.channel_access_token' => 'fake-token-for-testing']);

        $this->service = new LineNotificationService;
    }

    /** @test */
    public function it_sends_booking_created_notification_to_all_staff()
    {
        Http::fake([
            'api.line.me/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $staffUser = User::factory()->create([
            'role' => 'staff',
            'line_user_id' => 'LINE_STAFF_123',
        ]);

        $adminUser = User::factory()->create([
            'role' => 'admin',
            'line_user_id' => 'LINE_ADMIN_456',
        ]);

        $booking = Booking::factory()->create();

        $this->service->sendBookingCreatedNotification($booking);

        // Should create 2 notifications (staff + admin)
        $this->assertEquals(2, LineNotification::count());
        $this->assertDatabaseHas('line_notifications', [
            'booking_id' => $booking->id,
            'recipient_id' => $staffUser->id,
            'recipient_type' => 'staff',
            'notification_type' => 'booking_created',
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('line_notifications', [
            'booking_id' => $booking->id,
            'recipient_id' => $adminUser->id,
            'recipient_type' => 'staff',
            'notification_type' => 'booking_created',
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function it_skips_staff_without_line_id()
    {
        Http::fake();

        $staffWithLine = User::factory()->create([
            'role' => 'staff',
            'line_user_id' => 'LINE_STAFF_123',
        ]);

        $staffWithoutLine = User::factory()->create([
            'role' => 'staff',
            'line_user_id' => null,
        ]);

        $booking = Booking::factory()->create();

        $this->service->sendBookingCreatedNotification($booking);

        // Should create 2 notifications (one sent, one failed)
        $this->assertEquals(2, LineNotification::count());

        $this->assertDatabaseHas('line_notifications', [
            'recipient_id' => $staffWithLine->id,
            'status' => 'sent',
        ]);

        $this->assertDatabaseHas('line_notifications', [
            'recipient_id' => $staffWithoutLine->id,
            'status' => 'failed',
        ]);
    }

    /** @test */
    public function it_sends_booking_confirmation_to_guest()
    {
        Http::fake([
            'api.line.me/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => 'LINE_GUEST_789',
        ]);

        $booking = Booking::factory()->create(['user_id' => $guest->id]);

        $this->service->sendBookingConfirmation($booking);

        $this->assertDatabaseHas('line_notifications', [
            'booking_id' => $booking->id,
            'recipient_id' => $guest->id,
            'recipient_type' => 'guest',
            'notification_type' => 'booking_confirmed',
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function it_skips_confirmation_for_guest_without_line_id()
    {
        Log::spy();

        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => null,
        ]);

        $booking = Booking::factory()->create(['user_id' => $guest->id]);

        $this->service->sendBookingConfirmation($booking);

        $this->assertEquals(0, LineNotification::count());
    }

    /** @test */
    public function it_sends_booking_cancellation_notification()
    {
        Http::fake([
            'api.line.me/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => 'LINE_GUEST_789',
        ]);

        $booking = Booking::factory()->create(['user_id' => $guest->id]);

        $this->service->sendBookingCancellationNotification($booking);

        $this->assertDatabaseHas('line_notifications', [
            'booking_id' => $booking->id,
            'recipient_id' => $guest->id,
            'notification_type' => 'booking_cancelled',
            'status' => 'sent',
        ]);
    }

    /** @test */
    public function it_handles_failed_line_api_response()
    {
        Http::fake([
            'api.line.me/*' => Http::response(['message' => 'Invalid user ID'], 400),
        ]);

        Log::spy();

        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => 'INVALID_LINE_ID',
        ]);

        $booking = Booking::factory()->create(['user_id' => $guest->id]);

        $this->service->sendBookingConfirmation($booking);

        $notification = LineNotification::first();
        $this->assertEquals('failed', $notification->status);
        $this->assertStringContainsString('Invalid user ID', $notification->error_message);
    }

    /** @test */
    public function it_can_retry_failed_notification()
    {
        Http::fake([
            'api.line.me/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => 'LINE_GUEST_789',
        ]);

        $notification = LineNotification::factory()->failed()->create([
            'recipient_id' => $guest->id,
            'message' => 'Test message',
        ]);

        $result = $this->service->retryNotification($notification);

        $this->assertTrue($result);
        $notification->refresh();
        $this->assertEquals('sent', $notification->status);
        $this->assertNotNull($notification->sent_at);
    }

    /** @test */
    public function retry_fails_if_notification_not_in_failed_status()
    {
        $notification = LineNotification::factory()->sent()->create();

        $result = $this->service->retryNotification($notification);

        $this->assertFalse($result);
    }

    /** @test */
    public function retry_fails_if_recipient_has_no_line_id()
    {
        Log::spy();

        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => null,
        ]);

        $notification = LineNotification::factory()->failed()->create([
            'recipient_id' => $guest->id,
        ]);

        $result = $this->service->retryNotification($notification);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_sends_test_notification()
    {
        Http::fake([
            'api.line.me/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $user = User::factory()->create([
            'role' => 'staff',
            'line_user_id' => 'LINE_STAFF_123',
        ]);

        $notification = $this->service->sendTestNotification($user, 'Test message');

        $this->assertInstanceOf(LineNotification::class, $notification);
        $this->assertNull($notification->booking_id);
        $this->assertEquals($user->id, $notification->recipient_id);
        $this->assertEquals('Test message', $notification->message);
        $this->assertEquals('sent', $notification->status);
    }

    /** @test */
    public function it_handles_line_api_exception()
    {
        Http::fake(function () {
            throw new \Exception('Network error');
        });

        Log::spy();

        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => 'LINE_GUEST_789',
        ]);

        $booking = Booking::factory()->create(['user_id' => $guest->id]);

        $this->service->sendBookingConfirmation($booking);

        $notification = LineNotification::first();
        $this->assertEquals('failed', $notification->status);
        $this->assertStringContainsString('Network error', $notification->error_message);
    }

    /** @test */
    public function it_gracefully_handles_missing_line_api_config()
    {
        // Remove Line API token
        config(['services.line.channel_access_token' => null]);

        // Recreate service instance to pick up the null config
        $this->service = new LineNotificationService;

        Log::spy();

        $guest = User::factory()->create([
            'role' => 'guest',
            'line_user_id' => 'LINE_GUEST_789',
        ]);

        $booking = Booking::factory()->create(['user_id' => $guest->id]);

        $this->service->sendBookingConfirmation($booking);

        $notification = LineNotification::first();
        $this->assertEquals('failed', $notification->status);
        $this->assertEquals('Line API not configured', $notification->error_message);
    }

    /** @test */
    public function booking_created_message_contains_booking_details()
    {
        Http::fake([
            'api.line.me/*' => Http::response(['status' => 'ok'], 200),
        ]);

        $staff = User::factory()->create([
            'role' => 'staff',
            'line_user_id' => 'LINE_STAFF_123',
        ]);

        $guest = User::factory()->create(['name' => 'John Doe']);
        $room = MeetingRoom::factory()->create(['name' => 'Conference Room A']);

        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'attendee_count' => 10,
            'decoration_theme' => 'Modern',
        ]);

        $this->service->sendBookingCreatedNotification($booking);

        $notification = LineNotification::where('recipient_id', $staff->id)->first();

        $this->assertStringContainsString('John Doe', $notification->message);
        $this->assertStringContainsString('Conference Room A', $notification->message);
        $this->assertStringContainsString('10', $notification->message);
        $this->assertStringContainsString('Modern', $notification->message);
    }
}
