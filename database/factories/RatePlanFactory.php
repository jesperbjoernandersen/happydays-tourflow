<?php

namespace Database\Factories;

use App\Models\RatePlan;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RatePlan>
 */
class RatePlanFactory extends Factory
{
    protected $model = RatePlan::class;

    public function definition(): array
    {
        // Get a random hotel or create one if none exists
        $hotel = Hotel::inRandomOrder()->first() ?? Hotel::factory()->create();

        return [
            'hotel_id' => $hotel->id,
            'name' => $this->faker->word() . ' Plan',
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'description' => $this->faker->sentence(),
            'pricing_model' => $this->faker->randomElement(['occupancy_based', 'unit_included_occupancy']),
            'is_active' => true,
        ];
    }

    public function occupancyBased(): self
    {
        return $this->state(fn (array $attributes) => [
            'pricing_model' => 'occupancy_based',
        ]);
    }

    public function unitIncludedOccupancy(): self
    {
        return $this->state(fn (array $attributes) => [
            'pricing_model' => 'unit_included_occupancy',
        ]);
    }
}
