<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Allotment>
 */
class AllotmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_type_id' => RoomTypeFactory::new(),
            'date' => fake()->dateTimeBetween('now', '+60 days'),
            'quantity' => fake()->numberBetween(5, 20),
            'allocated' => fake()->numberBetween(0, 10),
            'price_override' => null,
            'cta' => false,
            'ctd' => false,
            'min_stay' => 0,
            'max_stay' => 0,
            'release_days' => 0,
            'stop_sell' => false,
        ];
    }

    /**
     * Indicate a specific date.
     */
    public function forDate($date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Indicate that this is close to arrival (CTA).
     */
    public function cta(): static
    {
        return $this->state(fn (array $attributes) => [
            'cta' => true,
        ]);
    }

    /**
     * Indicate that this is close to departure (CTD).
     */
    public function ctd(): static
    {
        return $this->state(fn (array $attributes) => [
            'ctd' => true,
        ]);
    }

    /**
     * Indicate a stop sell.
     */
    public function stopSell(): static
    {
        return $this->state(fn (array $attributes) => [
            'stop_sell' => true,
        ]);
    }

    /**
     * Indicate specific availability.
     */
    public function withAvailability(int $quantity, int $allocated): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
            'allocated' => $allocated,
        ]);
    }

    /**
     * Indicate a minimum stay requirement.
     */
    public function withMinStay(int $nights): static
    {
        return $this->state(fn (array $attributes) => [
            'min_stay' => $nights,
        ]);
    }

    /**
     * Indicate a maximum stay requirement.
     */
    public function withMaxStay(int $nights): static
    {
        return $this->state(fn (array $attributes) => [
            'max_stay' => $nights,
        ]);
    }
}
