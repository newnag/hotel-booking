<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed users
        $this->artisan('db:seed', ['--class' => 'UserSeeder']);
    }

    /** @test */
    public function admin_can_view_staff_list()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.staff.index');
        $response->assertViewHas('staff');
    }

    /** @test */
    public function staff_cannot_access_staff_management()
    {
        $staff = User::where('role', 'staff')->first();

        $response = $this->actingAs($staff)
            ->get(route('admin.staff.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_search_staff_by_name()
    {
        $admin = User::where('role', 'admin')->first();
        User::factory()->create([
            'role' => 'staff',
            'name' => 'Jane Smith',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.index', ['search' => 'Jane']));

        $response->assertStatus(200);
        $response->assertSee('Jane Smith');
    }

    /** @test */
    public function admin_can_filter_staff_by_role()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.index', ['role' => 'admin']));

        $response->assertStatus(200);
        $response->assertSee('Admin');
    }

    /** @test */
    public function admin_can_view_create_staff_form()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.staff.create');
    }

    /** @test */
    public function admin_can_create_new_staff()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->post(route('admin.staff.store'), [
                'name' => 'New Staff',
                'email' => 'newstaff@example.com',
                'phone' => '0899999999',
                'role' => 'staff',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name' => 'New Staff',
            'email' => 'newstaff@example.com',
            'role' => 'staff',
        ]);
    }

    /** @test */
    public function admin_can_create_new_admin()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->post(route('admin.staff.store'), [
                'name' => 'New Admin',
                'email' => 'newadmin@example.com',
                'role' => 'admin',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect(route('admin.staff.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'newadmin@example.com',
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function password_is_hashed_when_creating_staff()
    {
        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin)
            ->post(route('admin.staff.store'), [
                'name' => 'Test Staff',
                'email' => 'teststaff@example.com',
                'role' => 'staff',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $staff = User::where('email', 'teststaff@example.com')->first();
        $this->assertTrue(Hash::check('password123', $staff->password));
    }

    /** @test */
    public function validation_fails_for_invalid_role()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->post(route('admin.staff.store'), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'role' => 'guest', // Invalid role for staff creation
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertSessionHasErrors('role');
    }

    /** @test */
    public function admin_can_view_staff_edit_form()
    {
        $admin = User::where('role', 'admin')->first();
        $staff = User::where('role', 'staff')->first();

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.edit', $staff));

        $response->assertStatus(200);
        $response->assertViewIs('admin.staff.edit');
        $response->assertSee($staff->email);
    }

    /** @test */
    public function admin_cannot_edit_guest_users()
    {
        $admin = User::where('role', 'admin')->first();
        $guest = User::factory()->create(['role' => 'guest']);

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.edit', $guest));

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function admin_can_update_staff()
    {
        $admin = User::where('role', 'admin')->first();
        $staff = User::where('role', 'staff')->first();

        $response = $this->actingAs($admin)
            ->put(route('admin.staff.update', $staff), [
                'name' => 'Updated Staff Name',
                'email' => $staff->email,
                'role' => 'staff',
            ]);

        $response->assertRedirect(route('admin.staff.index'));

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Updated Staff Name',
        ]);
    }

    /** @test */
    public function admin_cannot_change_own_role()
    {
        $admin = User::where('role', 'admin')->first();
        $originalRole = $admin->role;

        $response = $this->actingAs($admin)
            ->put(route('admin.staff.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'staff', // Try to demote self
            ]);

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('error');

        $admin->refresh();
        $this->assertEquals($originalRole, $admin->role);
    }

    /** @test */
    public function admin_can_update_staff_password()
    {
        $admin = User::where('role', 'admin')->first();
        $staff = User::where('role', 'staff')->first();

        $response = $this->actingAs($admin)
            ->put(route('admin.staff.update', $staff), [
                'name' => $staff->name,
                'email' => $staff->email,
                'role' => 'staff',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('admin.staff.index'));

        $staff->refresh();
        $this->assertTrue(Hash::check('newpassword123', $staff->password));
    }

    /** @test */
    public function admin_can_delete_staff()
    {
        $admin = User::where('role', 'admin')->first();
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($admin)
            ->delete(route('admin.staff.destroy', $staff));

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('success');

        $staff->refresh();
        $this->assertNotNull($staff->deleted_at);
    }

    /** @test */
    public function admin_cannot_delete_self()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->delete(route('admin.staff.destroy', $admin));

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    /** @test */
    public function cannot_delete_last_admin()
    {
        // Keep only one admin in database
        $lastAdmin = User::where('role', 'admin')->first();

        // Delete other admins (if any)
        User::where('role', 'admin')
            ->where('id', '!=', $lastAdmin->id)
            ->delete();

        $response = $this->actingAs($lastAdmin)
            ->delete(route('admin.staff.destroy', $lastAdmin));

        $response->assertRedirect(route('admin.staff.index'));
        $response->assertSessionHas('error');

        // Admin should not be deleted (even soft delete)
        $lastAdmin->refresh();
        $this->assertNull($lastAdmin->deleted_at);
    }

    /** @test */
    public function password_is_optional_when_updating_staff()
    {
        $admin = User::where('role', 'admin')->first();
        $staff = User::where('role', 'staff')->first();
        $originalPassword = $staff->password;

        $response = $this->actingAs($admin)
            ->put(route('admin.staff.update', $staff), [
                'name' => 'Updated Name',
                'email' => $staff->email,
                'role' => 'staff',
                // No password provided
            ]);

        $response->assertRedirect(route('admin.staff.index'));

        $staff->refresh();
        $this->assertEquals($originalPassword, $staff->password);
    }

    /** @test */
    public function validation_fails_for_password_mismatch()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->post(route('admin.staff.store'), [
                'name' => 'Test Staff',
                'email' => 'test@example.com',
                'role' => 'staff',
                'password' => 'password123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function validation_requires_password_when_creating_staff()
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)
            ->post(route('admin.staff.store'), [
                'name' => 'Test Staff',
                'email' => 'test@example.com',
                'role' => 'staff',
                // No password provided
            ]);

        $response->assertSessionHasErrors('password');
    }
}
