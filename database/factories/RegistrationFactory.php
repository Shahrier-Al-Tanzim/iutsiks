<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $registrationType = fake()->randomElement(['individual', 'team']);
        
        return [
            'event_id' => Event::factory(),
            'user_id' => User::factory(),
            'registration_type' => $registrationType,
            'team_name' => $registrationType === 'team' ? fake()->words(2, true) : null,
            'team_members_json' => $registrationType === 'team' ? json_encode([fake()->name(), fake()->name()]) : null,
            'individual_name' => $registrationType === 'individual' ? fake()->name() : null,
            'payment_required' => fake()->boolean(),
            'payment_amount' => fake()->randomElement([0, 50, 100, 200]),
            'payment_status' => fake()->randomElement(['pending', 'verified', 'rejected']),
            'payment_method' => fake()->randomElement(['bkash', 'nagad', 'bank_transfer']),
            'transaction_id' => fake()->uuid(),
            'payment_date' => fake()->date(),
            'admin_notes' => null,
            'status' => fake()->randomElement(['pending', 'approved', 'rejected', 'cancelled']),
            'registered_at' => now(),
        ];
    }
}
