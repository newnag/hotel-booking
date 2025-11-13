<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\LineNotification;
use App\Models\MeetingRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'booking_ref',
            'user_id',
            'room_id',
            'start_datetime',
            'end_datetime',
            'attendee_count',
            'decoration_theme',
            'status',
            'notes',
        ];

        $booking = new Booking;

        $this->assertEquals($fillable, $booking->getFillable());
    }

    /** @test */
    public function it_auto_generates_booking_ref_on_creation()
    {
        $booking = Booking::factory()->create(['booking_ref' => null]);

        $this->assertNotNull($booking->booking_ref);
        $this->assertStringStartsWith('BK', $booking->booking_ref);
        $this->assertEquals(13, strlen($booking->booking_ref)); // BK + YYYYMMDD + 3 digits
    }

    /** @test */
    public function it_does_not_override_provided_booking_ref()
    {
        $customRef = 'CUSTOM123';
        $booking = Booking::factory()->create(['booking_ref' => $customRef]);

        $this->assertEquals($customRef, $booking->booking_ref);
    }

    /** @test */
    public function it_casts_datetime_fields()
    {
        $booking = Booking::factory()->create([
            'start_datetime' => '2025-11-15 10:00:00',
            'end_datetime' => '2025-11-15 12:00:00',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $booking->start_datetime);
        $this->assertInstanceOf(\Carbon\Carbon::class, $booking->end_datetime);
    }

    /** @test */
    public function it_casts_attendee_count_to_integer()
    {
        $booking = Booking::factory()->create(['attendee_count' => '25']);

        $this->assertIsInt($booking->attendee_count);
        $this->assertEquals(25, $booking->attendee_count);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $user = User::factory()->create();
        $booking = Booking::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $booking->user);
        $this->assertEquals($user->id, $booking->user->id);
    }

    /** @test */
    public function it_belongs_to_a_meeting_room()
    {
        $room = MeetingRoom::factory()->create();
        $booking = Booking::factory()->create(['room_id' => $room->id]);

        $this->assertInstanceOf(MeetingRoom::class, $booking->room);
        $this->assertEquals($room->id, $booking->room->id);
    }

    /** @test */
    public function it_has_many_line_notifications()
    {
        $booking = Booking::factory()->create();
        $notification1 = LineNotification::factory()->create(['booking_id' => $booking->id]);
        $notification2 = LineNotification::factory()->create(['booking_id' => $booking->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $booking->lineNotifications);
        $this->assertCount(2, $booking->lineNotifications);
        $this->assertTrue($booking->lineNotifications->contains($notification1));
        $this->assertTrue($booking->lineNotifications->contains($notification2));
    }

    /** @test */
    public function confirmed_scope_filters_confirmed_bookings_only()
    {
        $confirmedBooking = Booking::factory()->create(['status' => 'confirmed']);
        $cancelledBooking = Booking::factory()->create(['status' => 'cancelled']);
        $completedBooking = Booking::factory()->create(['status' => 'completed']);

        $confirmedBookings = Booking::confirmed()->get();

        $this->assertCount(1, $confirmedBookings);
        $this->assertTrue($confirmedBookings->contains($confirmedBooking));
        $this->assertFalse($confirmedBookings->contains($cancelledBooking));
        $this->assertFalse($confirmedBookings->contains($completedBooking));
    }

    /** @test */
    public function upcoming_scope_filters_future_bookings_only()
    {
        $futureBooking = Booking::factory()->create([
            'start_datetime' => now()->addDays(5),
        ]);
        $pastBooking = Booking::factory()->create([
            'start_datetime' => now()->subDays(5),
        ]);

        $upcomingBookings = Booking::upcoming()->get();

        $this->assertCount(1, $upcomingBookings);
        $this->assertTrue($upcomingBookings->contains($futureBooking));
        $this->assertFalse($upcomingBookings->contains($pastBooking));
    }

    /** @test */
    public function between_dates_scope_filters_by_date_range()
    {
        $bookingInRange = Booking::factory()->create([
            'start_datetime' => '2025-11-15 10:00:00',
            'end_datetime' => '2025-11-15 12:00:00',
        ]);
        $bookingBeforeRange = Booking::factory()->create([
            'start_datetime' => '2025-11-10 10:00:00',
            'end_datetime' => '2025-11-10 12:00:00',
        ]);
        $bookingAfterRange = Booking::factory()->create([
            'start_datetime' => '2025-11-25 10:00:00',
            'end_datetime' => '2025-11-25 12:00:00',
        ]);

        $bookings = Booking::betweenDates('2025-11-14 00:00:00', '2025-11-20 23:59:59')->get();

        $this->assertCount(1, $bookings);
        $this->assertTrue($bookings->contains($bookingInRange));
        $this->assertFalse($bookings->contains($bookingBeforeRange));
        $this->assertFalse($bookings->contains($bookingAfterRange));
    }

    /** @test */
    public function generate_booking_ref_creates_unique_reference()
    {
        // Create actual bookings to test uniqueness
        $booking1 = Booking::factory()->create();
        $booking2 = Booking::factory()->create();

        $this->assertNotEquals($booking1->booking_ref, $booking2->booking_ref);
        $this->assertStringStartsWith('BK', $booking1->booking_ref);
        $this->assertStringStartsWith('BK', $booking2->booking_ref);
    }

    /** @test */
    public function generate_booking_ref_increments_sequence_for_same_day()
    {
        $booking1 = Booking::factory()->create();
        $booking2 = Booking::factory()->create();

        $seq1 = intval(substr($booking1->booking_ref, -3));
        $seq2 = intval(substr($booking2->booking_ref, -3));

        $this->assertEquals($seq1 + 1, $seq2);
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $booking = Booking::factory()->create();
        $bookingId = $booking->id;

        $booking->delete();

        $this->assertSoftDeleted('bookings', ['id' => $bookingId]);
        $this->assertNotNull($booking->fresh()->deleted_at);
    }

    /** @test */
    public function it_can_combine_scopes()
    {
        $confirmedFutureBooking = Booking::factory()->create([
            'status' => 'confirmed',
            'start_datetime' => now()->addDays(3),
        ]);
        $confirmedPastBooking = Booking::factory()->create([
            'status' => 'confirmed',
            'start_datetime' => now()->subDays(3),
        ]);
        $completedFutureBooking = Booking::factory()->create([
            'status' => 'completed',
            'start_datetime' => now()->addDays(3),
        ]);

        $bookings = Booking::confirmed()->upcoming()->get();

        $this->assertCount(1, $bookings);
        $this->assertTrue($bookings->contains($confirmedFutureBooking));
        $this->assertFalse($bookings->contains($confirmedPastBooking));
        $this->assertFalse($bookings->contains($completedFutureBooking));
    }
}
