<?php

namespace Database\Factories;

use App\Models\Allotment;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Allotment>
 */
class AllotmentFactory extends Factory
{
    protected $model = Allotment::class;

    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),
            'date' => Carbon::tomorrow(),
            'quantity' => $this->faker->numberBetween(1, 20),
            'allocated' => $this->faker->numberBetween(0, 10),
            'price_override' => $this->faker->optional()->randomFloat(2, 50, 500),
            'cta' => $this->faker->boolean(10),
            'ctd' => $this->faker->boolean(10),
            'min_stay' => $this->faker->numberBetween(1, 7),
            'max_stay' => $this->faker->optional()->numberBetween(7, 28),
            'release_days' => $this->faker->optional()->numberBetween(1, 14),
            'stop_sell' => $this->faker->boolean(10),
        ];
    }

    public function withQuantity(int $quantity): self
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => $quantity,
        ]);
    }

    public function withAllocated(int $allocated): self
    {
        return $this->state(fn (array $attributes) => [
            'allocated' => $allocated,
        ]);
    }

    public function stopped(): self
    {
        return $this->state(fn (array $attributes) => [
            'stop_sell' => true,
        ]);
    }

    public function available(): self
    {
        return $this->state(fn (array $attributes) => [
            'stop_sell' => false,
        ]);
    }

    public function forDate(Carbon $date): self
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    /**
     * Create an available allotment (not sold out, not stop sell)
     */
    public static function createAvailable(int $quantity = 10, int $allocated = 0): self
    {
        return static::new()
            ->withQuantity($quantity)
            ->withAllocated($allocated)
            ->available();
    }

    /**
     * Create a sold out allotment
     */
    public static function createSoldOut(int $quantity = 5): self
    {
        return static::createAvailable($quantity, $quantity);
    }

    /**
     * Create a stop sell allotment
     */
    public static function createStopSell(int $quantity = 10): self
    {
        return static::createAvailable($quantity, 0)->stopped();
    }

    /**
     * Create a close to arrival allotment
     */
    public static function createCloseToArrival(int $quantity = 10): self
    {
        return static::createAvailable($quantity, 0)->state(fn (array $attributes) => [
            'cta' => true,
            'ctd' => false,
            'stop_sell' => false,
        ]);
    }

    /**
     * Create a close to departure allotment
     */
    public static function createCloseToDeparture(int $quantity = 10): self
    {
        return static::createAvailable($quantity, 0)->state(fn (array $attributes) => [
            'cta' => false,
            'ctd' => true,
            'stop_sell' => false,
        ]);
    }

    /**
     * Create allotments for a date range
     *
     * @param RoomType $roomType
     * @param Carbon $startDate
     * @param int $nights
     * @param array $attributes Additional attributes for each allotment
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function createForDateRange(RoomType $roomType, Carbon $startDate, int $nights, array $attributes = [])
    {
        $allotments = collect();
        $currentDate = $startDate->copy();

        for ($i = 0; $i < $nights; $i++) {
            $date = $currentDate->copy();
            $allotmentAttributes = array_merge([
                'room_type_id' => $roomType->id,
                'date' => $date,
                'quantity' => 10,
                'allocated' => 0,
                'stop_sell' => false,
                'cta' => false,
                'ctd' => false,
                'min_stay' => 0,
                'max_stay' => null,
            ], $attributes);

            $allotments->push(Allotment::create($allotmentAttributes));
            $currentDate->addDay();
        }

        return $allotments;
    }
}
