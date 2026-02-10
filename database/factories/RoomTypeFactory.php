<?php

namespace Database\Factories;

use App\Models\RoomType;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RoomType>
 */
class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        // Get a random hotel or create one if none exists
        $hotel = Hotel::inRandomOrder()->first() ?? Hotel::factory()->create();

        return [
            'hotel_id' => $hotel->id,
            'name' => $this->faker->word() . ' Room',
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'room_type' => $this->faker->randomElement(['hotel', 'house']),
            'base_occupancy' => $this->faker->numberBetween(1, 2),
            'max_occupancy' => $this->faker->numberBetween(2, 6),
            'extra_bed_slots' => $this->faker->numberBetween(0, 2),
            'single_use_supplement' => $this->faker->randomFloat(2, 0, 50),
            'is_active' => true,
        ];
    }

    public function hotel(): self
    {
        return $this->state(fn (array $attributes) => [
            'room_type' => 'hotel',
        ]);
    }

    public function house(): self
    {
        return $this->state(fn (array $attributes) => [
            'room_type' => 'house',
        ]);
    }
}
