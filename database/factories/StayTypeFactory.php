<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StayType>
 */
class StayTypeFactory extends Factory
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
            'name' => 'Standard Package',
            'description' => fake()->sentence(),
            'code' => strtoupper(fake()->unique()->lexify('ST???001')),
            'nights' => fake()->numberBetween(3, 14),
            'included_board_type' => fake()->randomElement(['AI', 'HB', 'BB', 'FB', 'RO']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the stay type is all-inclusive.
     */
    public function allInclusive(): static
    {
        return $this->state(fn (array $attributes) => [
            'included_board_type' => 'AI',
            'name' => 'All Inclusive Package',
        ]);
    }

    /**
     * Indicate that the stay type is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate a specific number of nights.
     */
    public function withNights(int $nights): static
    {
        return $this->state(fn (array $attributes) => [
            'nights' => $nights,
            'name' => "{$nights}-Night Package",
        ]);
    }
}
