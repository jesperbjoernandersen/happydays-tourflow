<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HotelAgePolicy>
 */
class HotelAgePolicyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'hotel_id' => HotelFactory::new(),
            'name' => 'Standard Policy',
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ];
    }

    /**
     * Indicate that infants are up to 1 year old.
     */
    public function infantUnderOne(): static
    {
        return $this->state(fn (array $attributes) => [
            'infant_max_age' => 1,
        ]);
    }

    /**
     * Indicate that children are up to 16 years old.
     */
    public function childUnderSixteen(): static
    {
        return $this->state(fn (array $attributes) => [
            'child_max_age' => 16,
        ]);
    }
}
