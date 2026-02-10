<?php

namespace Database\Factories;

use App\Models\RateRule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RateRule>
 */
class RateRuleFactory extends Factory
{
    protected $model = RateRule::class;

    public function definition(): array
    {
        return [
            'base_price' => $this->faker->randomFloat(2, 50, 500),
            'price_per_adult' => $this->faker->randomFloat(2, 0, 100),
            'price_per_child' => $this->faker->randomFloat(2, 0, 50),
            'price_per_infant' => 0,
            'price_per_extra_bed' => $this->faker->randomFloat(2, 0, 30),
            'single_use_supplement' => $this->faker->randomFloat(2, 0, 50),
            'included_occupancy' => $this->faker->numberBetween(1, 4),
            'price_per_extra_person' => $this->faker->randomFloat(2, 10, 50),
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ];
    }

    public function withBasePrice(float $price): self
    {
        return $this->state(fn (array $attributes) => [
            'base_price' => $price,
        ]);
    }

    public function withSingleUseSupplement(float $supplement): self
    {
        return $this->state(fn (array $attributes) => [
            'single_use_supplement' => $supplement,
        ]);
    }

    public function withIncludedOccupancy(int $occupancy): self
    {
        return $this->state(fn (array $attributes) => [
            'included_occupancy' => $occupancy,
        ]);
    }

    public function withPricePerExtraPerson(float $price): self
    {
        return $this->state(fn (array $attributes) => [
            'price_per_extra_person' => $price,
        ]);
    }

    public function withPricePerAdult(float $price): self
    {
        return $this->state(fn (array $attributes) => [
            'price_per_adult' => $price,
        ]);
    }

    public function withPricePerChild(float $price): self
    {
        return $this->state(fn (array $attributes) => [
            'price_per_child' => $price,
        ]);
    }

    public function withPricePerInfant(float $price): self
    {
        return $this->state(fn (array $attributes) => [
            'price_per_infant' => $price,
        ]);
    }

    public function withPricePerExtraBed(float $price): self
    {
        return $this->state(fn (array $attributes) => [
            'price_per_extra_bed' => $price,
        ]);
    }

    public function forDates(Carbon $startDate, Carbon $endDate): self
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
