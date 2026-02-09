<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RatePlan>
 */
class RatePlanFactory extends Factory
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
            'name' => 'Standard Rate',
            'code' => strtoupper(fake()->unique()->lexify('RP???001')),
            'description' => fake()->sentence(),
            'pricing_model' => fake()->randomElement(['occupancy_based', 'unit_included_occupancy']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate occupancy-based pricing.
     */
    public function occupancyBased(): static
    {
        return $this->state(fn (array $attributes) => [
            'pricing_model' => 'occupancy_based',
            'name' => 'Occupancy Based Rate',
        ]);
    }

    /**
     * Indicate unit included occupancy pricing.
     */
    public function unitIncludedOccupancy(): static
    {
        return $this->state(fn (array $attributes) => [
            'pricing_model' => 'unit_included_occupancy',
            'name' => 'Unit Included Occupancy Rate',
        ]);
    }

    /**
     * Indicate that the rate plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
