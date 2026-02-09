<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('now', '+60 days');
        $checkOut = clone $checkIn;
        $checkOut->modify('+' . fake()->numberBetween(3, 14) . ' days');

        return [
            'booking_reference' => $this->generateReference(),
            'stay_type_id' => StayTypeFactory::new(),
            'room_type_id' => RoomTypeFactory::new(),
            'hotel_id' => HotelFactory::new(),
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'total_price' => fake()->randomFloat(2, 200, 2000),
            'currency' => 'EUR',
            'status' => 'pending',
            'hotel_age_policy_snapshot' => null,
            'rate_rule_snapshot' => null,
            'price_breakdown_json' => null,
            'guest_count' => fake()->numberBetween(1, 4),
            'notes' => null,
        ];
    }

    /**
     * Generate a unique booking reference.
     */
    protected function generateReference(): string
    {
        $prefix = 'BK';
        $date = now()->format('Ymd');
        $random = strtoupper(fake()->lexify('??????'));
        return $prefix . $date . $random;
    }

    /**
     * Indicate a pending booking.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    /**
     * Indicate a confirmed booking.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
        ]);
    }

    /**
     * Indicate a cancelled booking.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    /**
     * Indicate a completed booking.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'check_in_date' => Carbon::now()->subDays(7),
            'check_out_date' => Carbon::now()->subDays(3),
        ]);
    }

    /**
     * Indicate specific dates.
     */
    public function withDates($checkIn, $checkOut): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
        ]);
    }
}
