<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeetingRoom>
 */
class MeetingRoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roomNames = [
            'ห้องแกรนด์บอลรูม',
            'ห้องดอกบัว',
            'ห้องกล้วยไม้',
            'ห้องราชพฤกษ์',
            'ห้องบัวหลวง',
            'ห้องสายน้ำผึ้ง',
        ];

        return [
            'name' => fake()->unique()->randomElement($roomNames),
            'description' => fake()->paragraph(3),
            'max_capacity' => fake()->numberBetween(10, 200),
            'location' => fake()->randomElement([
                'ชั้น 1 อาคารหลัก',
                'ชั้น 2 อาคารหลัก',
                'ชั้น 3 อาคารหลัก',
                'ชั้น 4 อาคารใหม่',
                'ชั้น 5 อาคารใหม่',
            ]),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the room is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create a small meeting room.
     */
    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_capacity' => fake()->numberBetween(10, 30),
        ]);
    }

    /**
     * Create a medium meeting room.
     */
    public function medium(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_capacity' => fake()->numberBetween(31, 80),
        ]);
    }

    /**
     * Create a large meeting room.
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'max_capacity' => fake()->numberBetween(81, 200),
        ]);
    }
}
