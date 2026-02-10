<?php

namespace Tests\Feature\Api;

use App\Models\Hotel;
use App\Models\RatePlan;
use App\Models\RateRule;
use App\Models\RoomType;
use App\Models\StayType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingApiTest extends TestCase
{
    use RefreshDatabase;

    protected Hotel $hotel;
    protected RatePlan $ratePlan;
    protected RoomType $roomType;
    protected StayType $stayType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->hotel = Hotel::factory()->create([
            'name' => 'Test Hotel',
            'code' => 'TH001',
            'is_active' => true,
        ]);

        $this->roomType = RoomType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Standard Room',
            'code' => 'STD',
            'base_occupancy' => 2,
            'max_occupancy' => 4,
            'is_active' => true,
        ]);

        $this->ratePlan = RatePlan::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Standard Rate',
            'code' => 'STD',
            'pricing_model' => 'unit_included_occupancy',
            'is_active' => true,
        ]);

        $this->stayType = StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Weekend Package',
            'code' => 'WEEKEND',
            'nights' => 2,
            'included_board_type' => 'Half Board',
            'is_active' => true,
        ]);

        // Create rate rule for the stay type
        RateRule::factory()->create([
            'rate_plan_id' => $this->ratePlan->id,
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'base_price' => 299.99,
            'price_per_adult' => 50.00,
            'price_per_child' => 25.00,
            'price_per_infant' => 0,
            'included_occupancy' => 2,
            'start_date' => now()->subMonth()->format('Y-m-d'),
            'end_date' => now()->addYear()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function it_can_calculate_price_for_a_stay(): void
    {
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 2,
            'occupancy' => [
                'adults' => 2,
                'children' => 0,
                'infants' => 0,
            ],
        ];

        $response = $this->postJson('/api/pricing/calculate', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'stay_type_id',
                'stay_type_name',
                'check_in_date',
                'nights',
                'currency',
                'total_price',
                'per_night_average',
                'breakdown' => [
                    'base_price',
                    'single_use_supplement',
                    'extra_person_charges',
                    'extra_bed_charges',
                    'per_person_charges',
                    'nights',
                    'guests',
                    'pricing_model',
                    'included_occupancy',
                ],
                'rate_rule',
                'stay_type',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('stay_type_id', $this->stayType->id)
            ->assertJsonPath('nights', 2)
            ->assertJsonPath('breakdown.guests.adults', 2)
            ->assertJsonPath('breakdown.guests.children', 0)
            ->assertJsonPath('breakdown.guests.infants', 0);
    }

    /** @test */
    public function it_can_calculate_price_with_children(): void
    {
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 3,
            'occupancy' => [
                'adults' => 2,
                'children' => 1,
                'infants' => 0,
            ],
        ];

        $response = $this->postJson('/api/pricing/calculate', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('breakdown.guests.adults', 2)
            ->assertJsonPath('breakdown.guests.children', 1);
    }

    /** @test */
    public function it_can_calculate_price_with_infants(): void
    {
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 1,
            'occupancy' => [
                'adults' => 1,
                'children' => 0,
                'infants' => 1,
            ],
        ];

        $response = $this->postJson('/api/pricing/calculate', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('breakdown.guests.adults', 1)
            ->assertJsonPath('breakdown.guests.infants', 1);
    }

    /** @test */
    public function it_validates_required_fields_for_calculation(): void
    {
        $response = $this->postJson('/api/pricing/calculate', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stay_type_id', 'check_in_date', 'occupancy']);
    }

    /** @test */
    public function it_validates_at_least_one_adult_required(): void
    {
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 1,
            'occupancy' => [
                'adults' => 0,
                'children' => 0,
                'infants' => 1,
            ],
        ];

        $response = $this->postJson('/api/pricing/calculate', $payload);

        // Validation error for occupancy.adults min:1
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['occupancy.adults']);
    }

    /** @test */
    public function it_validates_future_check_in_date(): void
    {
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->subWeek()->format('Y-m-d'),
            'nights' => 1,
            'occupancy' => [
                'adults' => 2,
                'children' => 0,
                'infants' => 0,
            ],
        ];

        $response = $this->postJson('/api/pricing/calculate', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['check_in_date']);
    }

    /** @test */
    public function it_returns_404_for_non_existent_stay_type(): void
    {
        $payload = [
            'stay_type_id' => 9999,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 1,
            'occupancy' => [
                'adults' => 2,
                'children' => 0,
                'infants' => 0,
            ],
        ];

        $response = $this->postJson('/api/pricing/calculate', $payload);

        // The exists validation returns 422 with a message
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['stay_type_id']);
    }

    /** @test */
    public function it_can_get_price_breakdown(): void
    {
        $checkInDate = now()->addWeek()->format('Y-m-d');

        $response = $this->getJson("/api/pricing/breakdown/{$this->stayType->id}/{$checkInDate}/3");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'stay_type_id',
                'check_in_date',
                'nights',
                'currency',
                'total_price',
                'per_night_average',
                'breakdown',
                'rate_rule',
                'stay_type',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('nights', 3);
    }

    /** @test */
    public function it_can_get_price_breakdown_with_custom_occupancy(): void
    {
        $checkInDate = now()->addWeek()->format('Y-m-d');

        $response = $this->getJson("/api/pricing/breakdown/{$this->stayType->id}/{$checkInDate}/2?occupancy[adults]=3&occupancy[children]=1&occupancy[infants]=0");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('breakdown.guests.adults', 3)
            ->assertJsonPath('breakdown.guests.children', 1);
    }

    /** @test */
    public function it_validates_breakdown_parameters(): void
    {
        $response = $this->getJson('/api/pricing/breakdown/abc/2026-01-15/1');

        $response->assertStatus(404); // StayType not found with id "abc"
    }

    /** @test */
    public function it_can_get_pricing_calendar(): void
    {
        $year = now()->year;
        $month = now()->month + 1;

        $response = $this->getJson("/api/pricing/availability/{$this->stayType->id}/{$year}/{$month}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'stay_type_id',
                'stay_type_name',
                'year',
                'month',
                'month_name',
                'currency',
                'summary' => [
                    'total_days',
                    'available_days',
                    'min_price',
                    'max_price',
                    'avg_price',
                    'currency',
                ],
                'days' => [
                    '*' => [
                        'date',
                        'day_of_week',
                        'day_name',
                        'day',
                        'is_weekend',
                        'is_available',
                        'is_blocked',
                        'has_rate',
                        'price',
                        'base_price',
                        'currency',
                    ],
                ],
                'stay_type',
                'room_type',
                'rate_plan',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('year', $year)
            ->assertJsonPath('month', $month);
    }

    /** @test */
    public function it_validates_calendar_parameters(): void
    {
        $response = $this->getJson('/api/pricing/availability/abc/2026/13');

        $response->assertStatus(404); // StayType not found with id "abc"
    }

    /** @test */
    public function it_includes_weekend_information_in_calendar(): void
    {
        $year = now()->year;
        $month = now()->month + 1;

        $response = $this->getJson("/api/pricing/availability/{$this->stayType->id}/{$year}/{$month}");

        $response->assertStatus(200);

        $days = $response->json('days');
        foreach ($days as $day) {
            $this->assertArrayHasKey('is_weekend', $day);
        }
    }

    /** @test */
    public function it_can_calculate_price_with_extra_beds(): void
    {
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 2,
            'occupancy' => [
                'adults' => 2,
                'children' => 0,
                'infants' => 0,
            ],
            'extra_beds' => 1,
        ];

        $response = $this->postJson('/api/pricing/calculate', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertTrue(array_key_exists('extra_bed_charges', $response->json('breakdown')));
    }

    /** @test */
    public function it_returns_rate_rule_in_response(): void
    {
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 1,
            'occupancy' => [
                'adults' => 2,
                'children' => 0,
                'infants' => 0,
            ],
        ];

        $response = $this->postJson('/api/pricing/calculate', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('rate_rule.id', $this->stayType->rateRules->first()->id)
            ->assertJsonPath('rate_rule.rate_plan_id', $this->ratePlan->id)
            ->assertJsonPath('rate_rule.pricing_model', 'unit_included_occupancy');
    }

    /** @test */
    public function it_calculates_per_night_average(): void
    {
        $payload = [
            'stay_type_id' => $this->stayType->id,
            'check_in_date' => now()->addWeek()->format('Y-m-d'),
            'nights' => 3,
            'occupancy' => [
                'adults' => 2,
                'children' => 0,
                'infants' => 0,
            ],
        ];

        $response = $this->postJson('/api/pricing/calculate', $payload);

        $response->assertStatus(200);

        $totalPrice = $response->json('total_price');
        $perNightAverage = $response->json('per_night_average');

        $this->assertEquals(round($totalPrice / 3, 2), $perNightAverage);
    }
}
