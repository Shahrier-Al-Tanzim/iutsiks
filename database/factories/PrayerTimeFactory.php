<?php

namespace Database\Factories;

use App\Models\PrayerTime;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PrayerTime>
 */
class PrayerTimeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PrayerTime::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->date(),
            'fajr' => '05:30:00',
            'dhuhr' => '12:15:00',
            'asr' => '15:45:00',
            'maghrib' => '18:30:00',
            'isha' => '20:00:00',
            'location' => 'IOT Masjid',
            'updated_by' => User::factory(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the prayer times are for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => today()->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the prayer times are for a specific date.
     */
    public function forDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Set custom prayer times.
     */
    public function withTimes(array $times): static
    {
        return $this->state(fn (array $attributes) => array_merge($attributes, $times));
    }

    /**
     * Set a specific location.
     */
    public function atLocation(string $location): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => $location,
        ]);
    }

    /**
     * Add notes to the prayer times.
     */
    public function withNotes(string $notes): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => $notes,
        ]);
    }
}