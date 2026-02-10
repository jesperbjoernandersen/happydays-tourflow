<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\BookingGuest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingGuest>
 */
class BookingGuestFactory extends Factory
{
    protected $model = BookingGuest::class;

    public function definition(): array
    {
        $category = $this->faker->randomElement(['infant', 'child', 'adult']);

        return [
            'booking_id' => Booking::factory(),
            'name' => $this->faker->name(),
            'birthdate' => $this->generateBirthdate($category),
            'guest_category' => $category,
        ];
    }

    /**
     * Generate birthdate based on category.
     */
    private function generateBirthdate(string $category): Carbon
    {
        $baseDate = Carbon::now();

        return match ($category) {
            'infant' => $baseDate->subMonths($this->faker->numberBetween(1, 23)),
            'child' => $baseDate->subYears($this->faker->numberBetween(2, 11)),
            'teen' => $baseDate->subYears($this->faker->numberBetween(12, 17)),
            default => $baseDate->subYears($this->faker->numberBetween(18, 80)),
        };
    }

    public function adult(): self
    {
        return $this->state(fn (array $attributes) => [
            'birthdate' => Carbon::now()->subYears($this->faker->numberBetween(18, 80)),
            'guest_category' => 'adult',
        ]);
    }

    public function child(): self
    {
        return $this->state(fn (array $attributes) => [
            'birthdate' => Carbon::now()->subYears($this->faker->numberBetween(2, 11)),
            'guest_category' => 'child',
        ]);
    }

    public function infant(): self
    {
        return $this->state(fn (array $attributes) => [
            'birthdate' => Carbon::now()->subMonths($this->faker->numberBetween(1, 23)),
            'guest_category' => 'infant',
        ]);
    }
}
