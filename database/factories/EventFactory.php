<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Fest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fest_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraphs(2, true),
            'event_date' => fake()->dateTimeBetween('now', '+1 month'),
            'event_time' => fake()->time(),
            'type' => fake()->randomElement(['quiz', 'lecture', 'donation', 'competition', 'workshop']),
            'registration_type' => fake()->randomElement(['individual', 'team', 'both', 'on_spot']),
            'location' => fake()->address(),
            'max_participants' => fake()->numberBetween(10, 100),
            'fee_amount' => fake()->randomElement([0, 50, 100, 200]),
            'registration_deadline' => fake()->dateTimeBetween('now', '+3 weeks'),
            'status' => fake()->randomElement(['draft', 'published', 'completed']),
            'author_id' => User::factory(),
            'image' => null,
        ];
    }
}
