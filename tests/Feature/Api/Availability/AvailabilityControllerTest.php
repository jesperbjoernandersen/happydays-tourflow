<?php

namespace Tests\Feature\Api\Availability;

use App\Models\Hotel;
use App\Models\StayType;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\RateRule;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AvailabilityControllerTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;
    private StayType $stayType;
    private RoomType $roomType;
    private RatePlan $ratePlan;
    private RateRule $rateRule;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->hotel = Hotel::factory()->create([
            'name' => 'Test Hotel',
            'code' => 'TEST-HOTEL',
            'currency' => 'EUR',
        ]);

        $this->stayType = StayType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Weekend Package',
            'code' => 'WEEKEND-PKG',
            'nights' => 2,
            'included_board_type' => 'half_board',
            'is_active' => true,
        ]);

        $this->roomType = RoomType::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Standard Room',
            'code' => 'STD',
            'base_occupancy' => 2,
            'max_occupancy' => 3,
            'is_active' => true,
        ]);

        $this->ratePlan = RatePlan::factory()->create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Best Available Rate',
            'code' => 'BAR',
            'pricing_model' => 'unit_included_occupancy',
            'is_active' => true,
        ]);

        $this->rateRule = RateRule::factory()->create([
            'rate_plan_id' => $this->ratePlan->id,
            'stay_type_id' => $this->stayType->id,
            'room_type_id' => $this->roomType->id,
            'start_date' => today()->subMonth(),
            'end_date' => today()->addYear(),
            'base_price' => 150.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 25.00,
        ]);
    }

    /** @test */
    public function it_returns_availability_status_for_valid_request()
    {
        $response = $this->getJson("/api/availability/{$this->stayType->id}?check_in_date=" . today()->addDay()->format('Y-m-d') . "&nights=2&occupancy[adults]=2");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'is_available',
                'stay_type_id',
                'stay_type_name',
                'check_in_date',
                'check_out_date',
                'nights',
                'currency',
                'total_price',
                'per_night_average',
                'available_dates',
                'minimum_stay_met',
                'maximum_stay_met',
                'occupancy',
                'stay_type',
                'room_type',
            ])
            ->assertJson([
                'success' => true,
                'stay_type_id' => $this->stayType->id,
                'stay_type_name' => $this->stayType->name,
            ]);
    }

    /** @test */
    public function it_returns_error_when_check_in_date_is_missing()
    {
        $response = $this->getJson("/api/availability/{$this->stayType->id}");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'check_in_date query parameter is required',
            ]);
    }

    /** @test */
    public function it_returns_error_when_check_in_date_is_in_past()
    {
        $response = $this->getJson("/api/availability/{$this->stayType->id}?check_in_date=" . today()->subDay()->format('Y-m-d'));

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Check-in date cannot be in the past',
            ]);
    }

    /** @test */
    public function it_returns_error_when_stay_type_not_found()
    {
        $response = $this->getJson("/api/availability/99999?check_in_date=" . today()->addDay()->format('Y-m-d'));

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Stay type not found',
            ]);
    }

    /** @test */
    public function it_returns_not_available_when_no_rate_rules_exist()
    {
        RateRule::where('rate_plan_id', $this->ratePlan->id)->delete();

        $response = $this->getJson("/api/availability/{$this->stayType->id}?check_in_date=" . today()->addDay()->format('Y-m-d') . "&nights=2");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'is_available' => false,
            ]);
    }

    /** @test */
    public function it_returns_not_available_when_minimum_stay_not_met()
    {
        // Stay type requires 2 nights minimum
        $response = $this->getJson("/api/availability/{$this->stayType->id}?check_in_date=" . today()->addDay()->format('Y-m-d') . "&nights=1");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => "Minimum stay requirement is {$this->stayType->nights} nights",
            ]);
    }

    /** @test */
    public function it_respects_occupancy_limits()
    {
        // Room max occupancy is 3
        $response = $this->getJson("/api/availability/{$this->stayType->id}?check_in_date=" . today()->addDay()->format('Y-m-d') . "&nights=2&occupancy[adults]=4");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Room capacity exceeded. Maximum occupancy is 3 guests',
            ]);
    }

    /** @test */
    public function it_validates_nights_parameter()
    {
        $response = $this->getJson("/api/availability/{$this->stayType->id}?check_in_date=" . today()->addDay()->format('Y-m-d') . "&nights=0");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Nights must be between 1 and 365',
            ]);
    }

    /** @test */
    public function it_validates_adults_parameter()
    {
        $response = $this->getJson("/api/availability/{$this->stayType->id}?check_in_date=" . today()->addDay()->format('Y-m-d') . "&nights=2&occupancy[adults]=0");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'At least one adult is required',
            ]);
    }

    /** @test */
    public function it_can_calculate_price_for_occupancy()
    {
        $response = $this->getJson("/api/availability/{$this->stayType->id}?check_in_date=" . today()->addDay()->format('Y-m-d') . "&nights=2&occupancy[adults]=2");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_price',
                'per_night_average',
            ]);

        $data = $response->json();
        $this->assertGreaterThan(0, $data['total_price']);
    }

    /** @test */
    public function it_can_handle_extra_occupancy_with_pricing()
    {
        // With 3 adults (2 included + 1 extra at 25)
        $response = $this->getJson("/api/availability/{$this->stayType->id}?check_in_date=" . today()->addDay()->format('Y-m-d') . "&nights=2&occupancy[adults]=3");

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertTrue($data['is_available']);
        $this->assertGreaterThan(0, $data['total_price']);
    }
}
