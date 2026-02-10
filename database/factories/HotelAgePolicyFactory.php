<?php

namespace Database\Factories;

use App\Models\HotelAgePolicy;
use App\Models\Hotel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HotelAgePolicy>
 */
class HotelAgePolicyFactory extends Factory
{
    protected $model = HotelAgePolicy::class;

    public function definition(): array
    {
        return [
            'hotel_id' => Hotel::factory(),
            'name' => $this->faker->word() . ' Policy',
            'infant_max_age' => $this->faker->numberBetween(1, 3),
            'child_max_age' => $this->faker->numberBetween(10, 16),
            'adult_min_age' => $this->faker->numberBetween(16, 21),
        ];
    }

    public function withInfantMaxAge(int $age): self
    {
        return $this->state(fn (array $attributes) => [
            'infant_max_age' => $age,
        ]);
    }

    public function withChildMaxAge(int $age): self
    {
        return $this->state(fn (array $attributes) => [
            'child_max_age' => $age,
        ]);
    }

    public function withAdultMinAge(int $age): self
    {
        return $this->state(fn (array $attributes) => [
            'adult_min_age' => $age,
        ]);
    }

    public function forHotel(Hotel $hotel): self
    {
        return $this->state(fn (array $attributes) => [
            'hotel_id' => $hotel->id,
        ]);
    }
}
