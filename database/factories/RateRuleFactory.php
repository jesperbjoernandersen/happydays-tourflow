<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RateRule>
 */
class RateRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+30 days');
        $endDate = clone $startDate;
        $endDate->modify('+30 days');

        return [
            'rate_plan_id' => RatePlanFactory::new(),
            'stay_type_id' => StayTypeFactory::new(),
            'room_type_id' => RoomTypeFactory::new(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'base_price' => fake()->randomFloat(2, 100, 500),
            'price_per_adult' => fake()->randomFloat(2, 25, 100),
            'price_per_child' => fake()->randomFloat(2, 10, 50),
            'price_per_infant' => 0,
            'price_per_extra_bed' => fake()->randomFloat(2, 15, 40),
            'single_use_supplement' => fake()->randomFloat(2, 10, 30),
            'included_occupancy' => fake()->numberBetween(1, 2),
            'price_per_extra_person' => fake()->randomFloat(2, 20, 80),
        ];
    }

    /**
     * Indicate a specific date range.
     */
    public function withDateRange($startDate, $endDate): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    /**
     * Indicate that there's no extra bed charge.
     */
    public function noExtraBedCharge(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_per_extra_bed' => 0,
        ]);
    }

    /**
     * Indicate that infants are free.
     */
    public function infantsFree(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_per_infant' => 0,
        ]);
    }
}
