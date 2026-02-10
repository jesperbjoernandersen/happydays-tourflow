<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\RoomType;
use App\Models\StayType;
use App\Models\Hotel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        return [
            'booking_reference' => 'BK' . Carbon::now()->format('Ymd') . strtoupper(Str::random(6)),
            'stay_type_id' => StayType::factory(),
            'room_type_id' => RoomType::factory(),
            'hotel_id' => Hotel::factory(),
            'check_in_date' => Carbon::now()->addDays(7),
            'check_out_date' => Carbon::now()->addDays(10),
            'total_price' => $this->faker->randomFloat(2, 100, 5000),
            'currency' => 'EUR',
            'status' => 'confirmed',
            'hotel_age_policy_snapshot' => null,
            'rate_rule_snapshot' => null,
            'price_breakdown_json' => null,
            'guest_count' => $this->faker->numberBetween(1, 4),
            'notes' => null,
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function confirmed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    public function withSnapshots(): self
    {
        return $this->state(fn (array $attributes) => [
            'hotel_age_policy_snapshot' => [
                'infant_age_max' => 2,
                'child_age_max' => 12,
                'teen_age_max' => 18,
            ],
            'rate_rule_snapshot' => [
                'base_rate' => 100,
                'adult_supplement' => 25,
                'child_discount' => 0.5,
            ],
            'price_breakdown_json' => [
                'room_total' => 300,
                'extras' => 50,
                'taxes' => 35,
                'total' => 385,
            ],
        ]);
    }
}
