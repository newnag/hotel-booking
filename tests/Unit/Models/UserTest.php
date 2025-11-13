<?php

namespace Tests\Unit\Models;

use App\Models\Booking;
use App\Models\LineNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'name',
            'email',
            'password',
            'role',
            'phone',
            'line_user_id',
            'language',
        ];

        $user = new User;

        $this->assertEquals($fillable, $user->getFillable());
    }

    /** @test */
    public function it_hides_password_and_remember_token()
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    /** @test */
    public function it_casts_email_verified_at_to_datetime()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }

    /** @test */
    public function it_hashes_password_automatically()
    {
        $plainPassword = 'password123';
        $user = User::factory()->create(['password' => $plainPassword]);

        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($plainPassword, $user->password));
    }

    /** @test */
    public function it_has_many_bookings()
    {
        $user = User::factory()->create();
        $booking1 = Booking::factory()->create(['user_id' => $user->id]);
        $booking2 = Booking::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->bookings);
        $this->assertCount(2, $user->bookings);
        $this->assertTrue($user->bookings->contains($booking1));
        $this->assertTrue($user->bookings->contains($booking2));
    }

    /** @test */
    public function it_has_many_line_notifications()
    {
        $user = User::factory()->create();
        $notification1 = LineNotification::factory()->create(['recipient_id' => $user->id]);
        $notification2 = LineNotification::factory()->create(['recipient_id' => $user->id]);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $user->lineNotifications);
        $this->assertCount(2, $user->lineNotifications);
        $this->assertTrue($user->lineNotifications->contains($notification1));
        $this->assertTrue($user->lineNotifications->contains($notification2));
    }

    /** @test */
    public function is_guest_returns_true_for_guest_role()
    {
        $guestUser = User::factory()->create(['role' => 'guest']);
        $staffUser = User::factory()->create(['role' => 'staff']);
        $adminUser = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($guestUser->isGuest());
        $this->assertFalse($staffUser->isGuest());
        $this->assertFalse($adminUser->isGuest());
    }

    /** @test */
    public function is_staff_returns_true_for_staff_and_admin_roles()
    {
        $guestUser = User::factory()->create(['role' => 'guest']);
        $staffUser = User::factory()->create(['role' => 'staff']);
        $adminUser = User::factory()->create(['role' => 'admin']);

        $this->assertFalse($guestUser->isStaff());
        $this->assertTrue($staffUser->isStaff());
        $this->assertTrue($adminUser->isStaff());
    }

    /** @test */
    public function is_admin_returns_true_for_admin_role_only()
    {
        $guestUser = User::factory()->create(['role' => 'guest']);
        $staffUser = User::factory()->create(['role' => 'staff']);
        $adminUser = User::factory()->create(['role' => 'admin']);

        $this->assertFalse($guestUser->isAdmin());
        $this->assertFalse($staffUser->isAdmin());
        $this->assertTrue($adminUser->isAdmin());
    }

    /** @test */
    public function it_uses_soft_deletes()
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertSoftDeleted('users', ['id' => $userId]);
        $this->assertNotNull($user->fresh()->deleted_at);
    }

    /** @test */
    public function it_can_be_force_deleted()
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->forceDelete();

        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    /** @test */
    public function it_can_check_multiple_role_conditions()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isStaff());
        $this->assertFalse($user->isGuest());
    }

    /** @test */
    public function factory_creates_valid_user()
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => $user->email,
        ]);
    }
}
