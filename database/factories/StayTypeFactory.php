<?php

namespace Database\Factories;

use App\Models\Hotel;
use App\Models\StayType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StayType>
 */
class StayTypeFactory extends Factory
{
    protected $model = StayType::class;

    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'name' => $this->faker->word() . ' Package',
            'description' => $this->faker->sentence(),
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'nights' => $this->faker->numberBetween(1, 14),
            'included_board_type' => $this->faker->randomElement(['RO', 'BB', 'HB', 'FB', 'AI']),
            'is_active' => true,
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function weeklong(): self
    {
        return $this->state(fn (array $attributes) => [
            'nights' => 7,
            'name' => '7-Night Package',
            'code' => '7NIGHT',
        ]);
    }
}
