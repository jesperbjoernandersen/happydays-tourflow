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
        // Get or create required related models
        $hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->for($hotel)->create();
        $stayType = StayType::factory()->for($hotel)->create();
        
        // Generate dates based on stay type
        $checkInDate = Carbon::today()->addDays($this->faker->numberBetween(1, 30));
        $checkOutDate = $checkInDate->copy()->addDays($stayType->nights);
        $totalPrice = $this->faker->randomFloat(2, 100, 5000);

        return [
            'booking_reference' => strtoupper(Str::random(10)),
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'hotel_id' => $hotel->id,
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
            'total_price' => $totalPrice,
            'currency' => $this->faker->currencyCode(),
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled']),
            'hotel_age_policy_snapshot' => [
                'infant_age_limit' => 2,
                'child_age_limit' => 12,
                'teen_age_limit' => 18,
            ],
            'rate_rule_snapshot' => [
                'name' => 'Standard Rate',
                'discount_percentage' => 0,
            ],
            'price_breakdown_json' => [
                'room_rate' => $totalPrice * 0.8,
                'taxes' => $totalPrice * 0.2,
                'extras' => 0,
            ],
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

    public function checkedIn(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'checked_in',
        ]);
    }

    public function checkedOut(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'checked_out',
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }

    public function withDates(Carbon $checkInDate, Carbon $checkOutDate): self
    {
        return $this->state(fn (array $attributes) => [
            'check_in_date' => $checkInDate,
            'check_out_date' => $checkOutDate,
        ]);
    }

    public function withTotalPrice(float $price): self
    {
        return $this->state(fn (array $attributes) => [
            'total_price' => $price,
        ]);
    }

    public function forRoomType(RoomType $roomType): self
    {
        return $this->state(fn (array $attributes) => [
            'room_type_id' => $roomType->id,
            'hotel_id' => $roomType->hotel_id,
        ]);
    }

    public function forStayType(StayType $stayType): self
    {
        return $this->state(fn (array $attributes) => [
            'stay_type_id' => $stayType->id,
        ]);
    }

    /**
     * Create a booking with allocated allotments for all dates in the stay.
     */
    public function withAllocatedAllotments(): self
    {
        return $this->afterCreating(function (Booking $booking) {
            $dates = $this->generateStayDates($booking->check_in_date, $booking->check_out_date);
            
            foreach ($dates as $date) {
                \App\Models\Allotment::create([
                    'room_type_id' => $booking->room_type_id,
                    'date' => $date,
                    'quantity' => 10,
                    'allocated' => 1, // This booking's allocation
                    'stop_sell' => false,
                ]);
            }
        });
    }

    /**
     * Generate all dates for the stay (check-in to check-out).
     */
    private function generateStayDates(Carbon $checkinDate, Carbon $checkoutDate): array
    {
        $dates = [];
        $nights = $checkinDate->diffInDays($checkoutDate);

        for ($i = 0; $i < $nights; $i++) {
            $dates[] = $checkinDate->copy()->addDays($i);
        }

        return $dates;
    }
}
