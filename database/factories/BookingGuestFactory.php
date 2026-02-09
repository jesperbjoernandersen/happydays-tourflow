<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingGuest>
 */
class BookingGuestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $age = fake()->numberBetween(18, 65);
        $birthYear = Carbon::now()->year - $age;

        return [
            'booking_id' => BookingFactory::new(),
            'name' => fake()->name(),
            'birthdate' => Carbon::create($birthYear, fake()->month(), fake()->dayOfMonth()),
            'guest_category' => 'adult',
        ];
    }

    /**
     * Indicate an adult guest.
     */
    public function adult(): static
    {
        return $this->state(fn (array $attributes) => [
            'guest_category' => 'adult',
            'birthdate' => Carbon::now()->subYears(fake()->numberBetween(18, 70)),
        ]);
    }

    /**
     * Indicate a child guest.
     */
    public function child(): static
    {
        return $this->state(fn (array $attributes) => [
            'guest_category' => 'child',
            'birthdate' => Carbon::now()->subYears(fake()->numberBetween(3, 17)),
        ]);
    }

    /**
     * Indicate an infant guest.
     */
    public function infant(): static
    {
        return $this->state(fn (array $attributes) => [
            'guest_category' => 'infant',
            'birthdate' => Carbon::now()->subYears(2)->addMonths(fake()->numberBetween(0, 11)),
        ]);
    }
}
