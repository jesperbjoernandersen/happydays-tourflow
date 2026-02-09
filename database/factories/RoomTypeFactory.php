<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomType>
 */
class RoomTypeFactory extends Factory
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
            'name' => fake()->randomElement(['Standard Room', 'Deluxe Room', 'Suite', 'Family Room', 'Superior Room']),
            'code' => strtoupper(fake()->unique()->lexify('RT???001')),
            'room_type' => 'hotel',
            'base_occupancy' => fake()->numberBetween(1, 2),
            'max_occupancy' => fake()->numberBetween(2, 4),
            'extra_bed_slots' => fake()->numberBetween(0, 2),
            'single_use_supplement' => fake()->randomFloat(2, 0, 50),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that this is a hotel room.
     */
    public function hotelRoom(): static
    {
        return $this->state(fn (array $attributes) => [
            'room_type' => 'hotel',
        ]);
    }

    /**
     * Indicate that this is a standalone house.
     */
    public function house(): static
    {
        return $this->state(fn (array $attributes) => [
            'room_type' => 'house',
            'hotel_id' => null,
            'name' => 'Villa',
        ]);
    }

    /**
     * Indicate that the room type is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate a specific occupancy.
     */
    public function withOccupancy(int $base, int $max): static
    {
        return $this->state(fn (array $attributes) => [
            'base_occupancy' => $base,
            'max_occupancy' => $max,
        ]);
    }
}
