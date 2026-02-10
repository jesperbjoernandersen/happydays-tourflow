<?php

namespace Tests\Feature\Api\Availability;

use App\Models\Hotel;
use App\Models\StayType;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\RateRule;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BulkAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;
    private RoomType $roomType;
    private RatePlan $ratePlan;
    private RateRule $rateRule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::factory()->create([
            'name' => 'Test Hotel',
            'code' => 'TEST-HOTEL',
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
            'name' => 'Best Available Rate',
            'code' => 'BAR',
            'pricing_model' => 'unit_included_occupancy',
            'is_active' => true,
        ]);

        RateRule::factory()->create([
            'rate_plan_id' => $this->ratePlan->id,
            'start_date' => today()->subMonth(),
            'end_date' => today()->addYear(),
            'base_price' => 100.00,
            'included_occupancy' => 2,
        ]);
    }

    private function createStayTypeWithRate(int $nights = 1): StayType
    {
        $stayType = StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'nights' => $nights,
            'is_active' => true,
        ]);

        RateRule::factory()->create([
            'rate_plan_id' => $this->ratePlan->id,
            'stay_type_id' => $stayType->id,
            'start_date' => today()->subMonth(),
            'end_date' => today()->addYear(),
            'base_price' => 100.00,
        ]);

        return $stayType;
    }

    /** @test */
    public function it_returns_results_for_multiple_requests()
    {
        $stayType1 = $this->createStayTypeWithRate();
        $stayType2 = $this->createStayTypeWithRate();

        $response = $this->postJson('/api/availability/check', [
            'requests' => [
                [
                    'stay_type_id' => $stayType1->id,
                    'check_in_date' => today()->addDay()->format('Y-m-d'),
                    'nights' => 2,
                ],
                [
                    'stay_type_id' => $stayType2->id,
                    'check_in_date' => today()->addDay()->format('Y-m-d'),
                    'nights' => 3,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'total_requests',
                'successful_requests',
                'failed_requests',
                'available_count',
                'unavailable_count',
                'results',
            ])
            ->assertJson([
                'success' => true,
                'total_requests' => 2,
            ]);

        $data = $response->json();
        $this->assertCount(2, $data['results']);
    }

    /** @test */
    public function it_handles_mixed_availability_results()
    {
        $stayType1 = $this->createStayTypeWithRate(2); // Requires 2 nights minimum
        $stayType2 = $this->createStayTypeWithRate(1);

        $response = $this->postJson('/api/availability/check', [
            'requests' => [
                [
                    'stay_type_id' => $stayType1->id,
                    'check_in_date' => today()->addDay()->format('Y-m-d'),
                    'nights' => 1, // Below minimum stay
                ],
                [
                    'stay_type_id' => $stayType2->id,
                    'check_in_date' => today()->addDay()->format('Y-m-d'),
                    'nights' => 2,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $data = $response->json();

        // First should be unavailable (minimum stay not met)
        $this->assertFalse($data['results'][0]['is_available']);

        // Second should be available
        $this->assertTrue($data['results'][1]['is_available']);
    }

    /** @test */
    public function it_validates_requests_array()
    {
        $response = $this->postJson('/api/availability/check', [
            'requests' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['requests']);
    }

    /** @test */
    public function it_validates_stay_type_id()
    {
        $response = $this->postJson('/api/availability/check', [
            'requests' => [
                [
                    'stay_type_id' => 99999,
                    'check_in_date' => today()->addDay()->format('Y-m-d'),
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['requests.0.stay_type_id']);
    }

    /** @test */
    public function it_validates_check_in_date()
    {
        $stayType = $this->createStayTypeWithRate();

        $response = $this->postJson('/api/availability/check', [
            'requests' => [
                [
                    'stay_type_id' => $stayType->id,
                    'check_in_date' => 'invalid-date',
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['requests.0.check_in_date']);
    }

    /** @test */
    public function it_validates_check_in_date_not_in_past()
    {
        $stayType = $this->createStayTypeWithRate();

        $response = $this->postJson('/api/availability/check', [
            'requests' => [
                [
                    'stay_type_id' => $stayType->id,
                    'check_in_date' => today()->subDay()->format('Y-m-d'),
                ],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['requests.0.check_in_date']);
    }

    /** @test */
    public function it_limits_maximum_requests()
    {
        // Create 51 requests (exceeds limit of 50)
        $requests = [];
        for ($i = 0; $i < 51; $i++) {
            $requests[] = [
                'stay_type_id' => $this->createStayTypeWithRate()->id,
                'check_in_date' => today()->addDay()->format('Y-m-d'),
            ];
        }

        $response = $this->postJson('/api/availability/check', [
            'requests' => $requests,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['requests']);
    }

    /** @test */
    public function it_requires_at_least_one_request()
    {
        $response = $this->postJson('/api/availability/check', [
            'requests' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['requests']);
    }

    /** @test */
    public function it_handles_occupancy_in_bulk_requests()
    {
        $stayType = $this->createStayTypeWithRate();

        $response = $this->postJson('/api/availability/check', [
            'requests' => [
                [
                    'stay_type_id' => $stayType->id,
                    'check_in_date' => today()->addDay()->format('Y-m-d'),
                    'nights' => 2,
                    'occupancy' => [
                        'adults' => 3,
                        'children' => 1,
                        'infants' => 0,
                    ],
                ],
            ],
        ]);

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(3, $data['results'][0]['occupancy']['adults'] ?? null);
    }

    /** @test */
    public function it_returns_summary_counts()
    {
        $stayType1 = $this->createStayTypeWithRate(2); // Requires 2 nights
        $stayType2 = $this->createStayTypeWithRate(1);

        $response = $this->postJson('/api/availability/check', [
            'requests' => [
                [
                    'stay_type_id' => $stayType1->id,
                    'check_in_date' => today()->addDay()->format('Y-m-d'),
                    'nights' => 1, // Below minimum
                ],
                [
                    'stay_type_id' => $stayType2->id,
                    'check_in_date' => today()->addDay()->format('Y-m-d'),
                    'nights' => 2,
                ],
            ],
        ]);

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertEquals(2, $data['total_requests']);
        $this->assertEquals(1, $data['successful_requests']);
        $this->assertEquals(1, $data['failed_requests']);
        $this->assertEquals(1, $data['available_count']);
        $this->assertEquals(1, $data['unavailable_count']);
    }

    /** @test */
    public function it_can_optionaly_specify_room_type_and_rate_plan()
    {
        $stayType = $this->createStayTypeWithRate();

        $response = $this->postJson('/api/availability/check', [
            'requests' => [
                [
                    'stay_type_id' => $stayType->id,
                    'check_in_date' => today()->addDay()->format('Y-m-d'),
                    'nights' => 2,
                    'room_type_id' => $this->roomType->id,
                    'rate_plan_id' => $this->ratePlan->id,
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'results' => [
                    [
                        'stay_type_id',
                        'check_in_date',
                        'is_available',
                        'total_price',
                    ],
                ],
            ]);
    }
}
