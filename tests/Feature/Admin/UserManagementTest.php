<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed users
        $this->artisan('db:seed', ['--class' => 'UserSeeder']);
    }

    /** @test */
    public function admin_can_view_users_list()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
        $response->assertViewHas('users');
    }

    /** @test */
    public function staff_cannot_access_user_management()
    {
        $staff = User::where('role', 'staff')->first();

        $response = $this->actingAs($staff)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_user_management()
    {
        $guest = User::factory()->create(['role' => 'guest']);

        $response = $this->actingAs($guest)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_search_users_by_name()
    {
        $admin = User::where('role', 'admin')->first();
        $guest = User::factory()->create([
            'role' => 'guest',
            'name' => 'John Doe',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index', ['search' => 'John']));

        $response->assertStatus(200);
        $response->assertSee('John Doe');
    }

    /** @test */
    public function admin_can_filter_users_by_line_status()
    {
        $admin = User::where('role', 'admin')->first();

        User::factory()->create([
            'role' => 'guest',
            'line_user_id' => 'U1234567890abcdef1234567890abcdef',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.index', ['line_status' => 'connected']));

        $response->assertStatus(200);
        $response->assertSee('เชื่อมต่อแล้ว');
    }

    /** @test */
    public function admin_can_view_user_edit_form()
    {
        $admin = User::where('role', 'admin')->first();
        $guest = User::factory()->create(['role' => 'guest']);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.edit', $guest));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
        $response->assertSee($guest->email);
    }

    /** @test */
    public function admin_cannot_edit_non_guest_users()
    {
        $admin = User::where('role', 'admin')->first();
        $staff = User::where('role', 'staff')->first();

        $response = $this->actingAs($admin)
            ->get(route('admin.users.edit', $staff));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function admin_can_update_guest_user()
    {
        $admin = User::where('role', 'admin')->first();
        $guest = User::factory()->create(['role' => 'guest']);

        $response = $this->actingAs($admin)
            ->put(route('admin.users.update', $guest), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'phone' => '0812345678',
                'line_user_id' => 'U1234567890abcdef1234567890abcdef',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $guest->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function admin_can_update_user_password()
    {
        $admin = User::where('role', 'admin')->first();
        $guest = User::factory()->create(['role' => 'guest']);

        $response = $this->actingAs($admin)
            ->put(route('admin.users.update', $guest), [
                'name' => $guest->name,
                'email' => $guest->email,
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('admin.users.index'));

        $guest->refresh();
        $this->assertTrue(\Hash::check('newpassword123', $guest->password));
    }

    /** @test */
    public function admin_can_delete_guest_user_without_future_bookings()
    {
        $admin = User::where('role', 'admin')->first();
        $guest = User::factory()->create(['role' => 'guest']);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $guest));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $guest->refresh();
        $this->assertNotNull($guest->deleted_at);
    }

    /** @test */
    public function admin_cannot_delete_user_with_future_bookings()
    {
        $this->artisan('db:seed', ['--class' => 'RoomSeeder']);

        $admin = User::where('role', 'admin')->first();
        $guest = User::factory()->create(['role' => 'guest']);
        $room = \App\Models\MeetingRoom::first();

        // Create future booking
        \App\Models\Booking::create([
            'booking_ref' => \App\Models\Booking::generateBookingRef(),
            'user_id' => $guest->id,
            'room_id' => $room->id,
            'start_datetime' => now()->addDays(7)->setTime(10, 0),
            'end_datetime' => now()->addDays(7)->setTime(12, 0),
            'attendee_count' => 10,
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $guest));

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $guest->id,
        ]);
    }

    /** @test */
    public function validation_fails_for_invalid_email()
    {
        $admin = User::where('role', 'admin')->first();
        $guest = User::factory()->create(['role' => 'guest']);

        $response = $this->actingAs($admin)
            ->put(route('admin.users.update', $guest), [
                'name' => 'Test User',
                'email' => 'invalid-email',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function validation_fails_for_duplicate_email()
    {
        $admin = User::where('role', 'admin')->first();
        $guest1 = User::factory()->create(['role' => 'guest', 'email' => 'existing@example.com']);
        $guest2 = User::factory()->create(['role' => 'guest']);

        $response = $this->actingAs($admin)
            ->put(route('admin.users.update', $guest2), [
                'name' => $guest2->name,
                'email' => 'existing@example.com',
            ]);

        $response->assertSessionHasErrors('email');
    }
}
