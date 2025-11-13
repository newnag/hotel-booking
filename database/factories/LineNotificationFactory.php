<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\LineNotification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LineNotification>
 */
class LineNotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LineNotification::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'booking_id' => Booking::factory(),
            'recipient_id' => User::factory(),
            'recipient_type' => fake()->randomElement(['guest', 'staff']),
            'notification_type' => fake()->randomElement(['booking_created', 'booking_cancelled', 'booking_reminder']),
            'message' => fake()->sentence(),
            'status' => 'pending',
            'sent_at' => null,
            'error_message' => null,
        ];
    }

    /**
     * Indicate that the notification has been sent.
     */
    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Indicate that the notification has failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => 'Failed to send notification',
        ]);
    }
}
