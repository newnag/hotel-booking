<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\MeetingRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RoomManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $staff;

    protected User $guest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->staff = User::factory()->staff()->create();
        $this->guest = User::factory()->guest()->create();
    }

    /** @test */
    public function admin_can_view_rooms_index()
    {
        MeetingRoom::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.rooms.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.rooms.index');
        $response->assertViewHas('rooms');
    }

    /** @test */
    public function staff_cannot_access_room_management()
    {
        $response = $this->actingAs($this->staff)
            ->get(route('admin.rooms.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_room_management()
    {
        $response = $this->actingAs($this->guest)
            ->get(route('admin.rooms.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function rooms_can_be_filtered_by_status()
    {
        MeetingRoom::factory()->create(['is_active' => true, 'name' => 'Active Room']);
        MeetingRoom::factory()->create(['is_active' => false, 'name' => 'Inactive Room']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.rooms.index', ['is_active' => 1]));

        $response->assertStatus(200);
        $response->assertSee('Active Room');
    }

    /** @test */
    public function rooms_can_be_searched_by_name()
    {
        MeetingRoom::factory()->create(['name' => 'Conference Room A']);
        MeetingRoom::factory()->create(['name' => 'Meeting Room B']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.rooms.index', ['search' => 'Conference']));

        $response->assertStatus(200);
        $response->assertSee('Conference Room A');
    }

    /** @test */
    public function admin_can_view_create_room_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.rooms.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.rooms.form');
    }

    /** @test */
    public function admin_can_create_new_room()
    {
        Storage::fake('public');

        $data = [
            'name' => 'New Conference Room',
            'description' => 'A spacious conference room',
            'max_capacity' => 50,
            'is_active' => true,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), $data);

        $response->assertRedirect(route('admin.rooms.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('meeting_rooms', [
            'name' => 'New Conference Room',
            'max_capacity' => 50,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function admin_can_create_room_with_image()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('room.jpg', 800, 600);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), [
                'name' => 'Room with Image',
                'description' => 'Test room',
                'max_capacity' => 30,
                'is_active' => true,
                'image' => $image,
            ]);

        $response->assertRedirect(route('admin.rooms.index'));

        $room = MeetingRoom::where('name', 'Room with Image')->first();
        $this->assertNotNull($room);
        $this->assertNotNull($room->image_path);
        $this->assertTrue(Storage::disk('public')->exists($room->image_path));
    }

    /** @test */
    public function room_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), []);

        $response->assertSessionHasErrors(['name', 'max_capacity']);
    }

    /** @test */
    public function room_name_must_be_unique()
    {
        MeetingRoom::factory()->create(['name' => 'Existing Room']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), [
                'name' => 'Existing Room',
                'max_capacity' => 20,
            ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function capacity_must_be_positive_integer()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), [
                'name' => 'Test Room',
                'max_capacity' => -5,
            ]);

        $response->assertSessionHasErrors('max_capacity');
    }

    /** @test */
    public function image_must_be_valid_image_file()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.rooms.store'), [
                'name' => 'Test Room',
                'max_capacity' => 30,
                'image' => $file,
            ]);

        $response->assertSessionHasErrors('image');
    }

    /** @test */
    public function admin_can_view_room_details()
    {
        $room = MeetingRoom::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.rooms.show', $room));

        $response->assertStatus(200);
        $response->assertViewIs('admin.rooms.show');
        $response->assertViewHas('room', $room);
        $response->assertViewHas('statistics');
    }

    /** @test */
    public function admin_can_view_edit_room_form()
    {
        $room = MeetingRoom::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.rooms.edit', $room));

        $response->assertStatus(200);
        $response->assertViewIs('admin.rooms.form');
        $response->assertViewHas('room', $room);
    }

    /** @test */
    public function admin_can_update_room()
    {
        $room = MeetingRoom::factory()->create([
            'name' => 'Old Name',
            'max_capacity' => 30,
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.rooms.update', $room), [
                'name' => 'Updated Name',
                'description' => 'Updated description',
                'max_capacity' => 50,
                'is_active' => true,
            ]);

        $response->assertRedirect(route('admin.rooms.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('meeting_rooms', [
            'id' => $room->id,
            'name' => 'Updated Name',
            'max_capacity' => 50,
        ]);
    }

    /** @test */
    public function admin_can_update_room_image()
    {
        Storage::fake('public');

        $oldImage = UploadedFile::fake()->image('old-room.jpg');
        $room = MeetingRoom::factory()->create();

        // Set initial image
        $oldImagePath = $oldImage->store('rooms', 'public');
        $room->update(['image_path' => $oldImagePath]);
        $room->refresh();

        $newImage = UploadedFile::fake()->image('new-room.jpg');

        $response = $this->actingAs($this->admin)
            ->put(route('admin.rooms.update', $room), [
                'name' => $room->name,
                'max_capacity' => $room->max_capacity,
                'is_active' => $room->is_active,
                'image' => $newImage,
            ]);

        $response->assertRedirect(route('admin.rooms.index'));

        $room->refresh();
        $this->assertNotEquals($oldImagePath, $room->image_path);
        $this->assertTrue(Storage::disk('public')->exists($room->image_path));
        // Old image should be deleted
        $this->assertFalse(Storage::disk('public')->exists($oldImagePath));
    }

    /** @test */
    public function admin_can_toggle_room_status()
    {
        $room = MeetingRoom::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.rooms.toggle', $room));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $room->refresh();
        $this->assertFalse($room->is_active);
    }

    /** @test */
    public function admin_can_delete_room_without_future_bookings()
    {
        Storage::fake('public');

        $room = MeetingRoom::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.rooms.destroy', $room));

        $response->assertRedirect(route('admin.rooms.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('meeting_rooms', ['id' => $room->id]);
    }

    /** @test */
    public function admin_cannot_delete_room_with_future_bookings()
    {
        $room = MeetingRoom::factory()->create();

        // Create future confirmed booking
        Booking::factory()->create([
            'room_id' => $room->id,
            'status' => 'confirmed',
            'start_datetime' => now()->addDays(1),
            'end_datetime' => now()->addDays(1)->addHours(2),
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.rooms.destroy', $room));

        $response->assertRedirect(route('admin.rooms.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('meeting_rooms', ['id' => $room->id]);
    }

    /** @test */
    public function admin_can_delete_room_with_past_bookings()
    {
        $room = MeetingRoom::factory()->create();

        // Create past booking
        $booking = Booking::factory()->create([
            'room_id' => $room->id,
            'status' => 'completed',
            'start_datetime' => now()->subDays(1),
            'end_datetime' => now()->subDays(1)->addHours(2),
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.rooms.destroy', $room));

        $response->assertRedirect(route('admin.rooms.index'));
        $response->assertSessionHas('success');

        // Delete bookings first, then check room deletion
        $booking->forceDelete();
        $this->assertDatabaseMissing('meeting_rooms', ['id' => $room->id]);
    }

    /** @test */
    public function deleting_room_removes_associated_image()
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('room.jpg');

        $room = MeetingRoom::factory()->create();
        // Set image path
        $imagePath = $image->store('rooms', 'public');
        $room->update(['image_path' => $imagePath]);
        $room->refresh();

        $this->assertTrue(Storage::disk('public')->exists($imagePath));

        $this->actingAs($this->admin)
            ->delete(route('admin.rooms.destroy', $room));

        $this->assertFalse(Storage::disk('public')->exists($imagePath));
    }
}
