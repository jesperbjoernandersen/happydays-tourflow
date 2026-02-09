<?php

namespace Tests\Unit\Services;

use App\Services\PricingService;
use App\Models\RateRule;
use App\Models\RoomType;
use App\Models\StayType;
use App\Models\RatePlan;
use App\Enums\PricingModel;
use App\Domain\ValueObjects\Occupancy;
use App\Domain\ValueObjects\PriceBreakdown;
use Carbon\Carbon;
use Database\Factories\RatePlanFactory;
use Database\Factories\RateRuleFactory;
use Database\Factories\RoomTypeFactory;
use Database\Factories\StayTypeFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PricingService();
    }

    /** @test */
    public function it_returns_zero_price_breakdown_when_no_rate_rule_found(): void
    {
        $stayType = StayTypeFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 2, children: 0, infants: 0, extraBeds: 0);
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        $this->assertInstanceOf(PriceBreakdown::class, $result);
        $this->assertEquals(0, $result->getTotalPrice()->getAmount());
    }

    // ==================== OCCUPANCY_BASED MODEL TESTS ====================

    /** @test */
    public function it_calculates_occupancy_based_price_for_standard_booking(): void
    {
        // Create entities first
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();
        
        // Create rate plan with occupancy-based pricing
        $ratePlan = RatePlanFactory::new()->occupancyBased()->create([
            'pricing_model' => 'occupancy_based',
        ]);

        // Set checkin date first
        $checkinDate = Carbon::now()->addDay();
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(60);

        // Create rate rule with the SAME stayType and roomType
        $rateRule = RateRuleFactory::new()->withDateRange($startDate, $endDate)->create([
            'rate_plan_id' => $ratePlan->id,
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'base_price' => 100.00,
            'price_per_adult' => 25.00,
            'price_per_child' => 15.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 20.00,
            'single_use_supplement' => 0.00,
            'included_occupancy' => 1,
            'price_per_extra_person' => 0.00,
        ]);

        $occupancy = new Occupancy(adults: 2, children: 1, infants: 0, extraBeds: 0);

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        // Expected: (100 + 2*25 + 1*15) * 7 = 700 + 350 + 105 = 1155
        $this->assertEquals(1155.00, $result->getTotalPrice()->getAmount());
        $this->assertEquals(700.00, $result->getBasePrice()->getAmount());
        $this->assertEquals(350.00, $result->getAdultSupplement()->getAmount());
        $this->assertEquals(105.00, $result->getChildSupplement()->getAmount());
        $this->assertEquals(0, $result->getInfantSupplement()->getAmount());
        $this->assertEquals(0, $result->getExtraBedSupplement()->getAmount());
        $this->assertEquals(0, $result->getSingleUseSupplement()->getAmount());
    }

    /** @test */
    public function it_applies_single_use_supplement_for_single_guest(): void
    {
        $ratePlan = RatePlanFactory::new()->occupancyBased()->create([
            'pricing_model' => 'occupancy_based',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'price_per_adult' => 0.00,
            'price_per_child' => 0.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 0.00,
            'single_use_supplement' => 30.00,
        ]);

        $stayType = StayTypeFactory::new()->withNights(5)->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 1, children: 0, infants: 0, extraBeds: 0);
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        // Expected: (100 + 30) * 5 = 130 * 5 = 650
        $this->assertEquals(650.00, $result->getTotalPrice()->getAmount());
        $this->assertEquals(150.00, $result->getSingleUseSupplement()->getAmount());
    }

    /** @test */
    public function it_does_not_apply_single_use_supplement_for_multiple_guests(): void
    {
        $ratePlan = RatePlanFactory::new()->occupancyBased()->create([
            'pricing_model' => 'occupancy_based',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'price_per_adult' => 0.00,
            'price_per_child' => 0.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 0.00,
            'single_use_supplement' => 30.00,
        ]);

        $stayType = StayTypeFactory::new()->withNights(5)->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 2, children: 0, infants: 0, extraBeds: 0);
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        // Expected: 100 * 5 = 500 (no single use supplement)
        $this->assertEquals(500.00, $result->getTotalPrice()->getAmount());
        $this->assertEquals(0, $result->getSingleUseSupplement()->getAmount());
    }

    /** @test */
    public function it_calculates_price_with_extra_beds(): void
    {
        $ratePlan = RatePlanFactory::new()->occupancyBased()->create([
            'pricing_model' => 'occupancy_based',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'price_per_adult' => 20.00,
            'price_per_child' => 10.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 25.00,
            'single_use_supplement' => 0.00,
        ]);

        $stayType = StayTypeFactory::new()->withNights(3)->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 2, children: 1, infants: 0, extraBeds: 1);
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        // Expected: (100 + 2*20 + 1*10 + 1*25) * 3 = 175 * 3 = 525
        $this->assertEquals(525.00, $result->getTotalPrice()->getAmount());
        $this->assertEquals(75.00, $result->getExtraBedSupplement()->getAmount());
    }

    /** @test */
    public function it_handles_infant_pricing(): void
    {
        $ratePlan = RatePlanFactory::new()->occupancyBased()->create([
            'pricing_model' => 'occupancy_based',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'price_per_adult' => 25.00,
            'price_per_child' => 15.00,
            'price_per_infant' => 5.00,
            'price_per_extra_bed' => 0.00,
            'single_use_supplement' => 0.00,
        ]);

        $stayType = StayTypeFactory::new()->withNights(1)->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 2, children: 0, infants: 1, extraBeds: 0);
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        // Expected: 100 + 2*25 + 1*5 = 155
        $this->assertEquals(155.00, $result->getTotalPrice()->getAmount());
        $this->assertEquals(5.00, $result->getInfantSupplement()->getAmount());
    }

    // ==================== UNIT_INCLUDED_OCCUPANCY MODEL TESTS ====================

    /** @test */
    public function it_calculates_unit_included_occupancy_price_for_standard_booking(): void
    {
        $ratePlan = RatePlanFactory::new()->unitIncludedOccupancy()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 500.00, // Fixed price for up to 2 guests
            'price_per_adult' => 0.00,
            'price_per_child' => 0.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 0.00,
            'single_use_supplement' => 0.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 50.00,
        ]);

        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 2, children: 0, infants: 0, extraBeds: 0);
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        // Expected: 500 * 7 = 3500 (within included occupancy)
        $this->assertEquals(3500.00, $result->getTotalPrice()->getAmount());
        $this->assertEquals(3500.00, $result->getBasePrice()->getAmount());
        $this->assertEquals(0, $result->getExtraOccupancyCharge()->getAmount());
    }

    /** @test */
    public function it_applies_extra_person_charge_when_exceeding_included_occupancy(): void
    {
        $ratePlan = RatePlanFactory::new()->unitIncludedOccupancy()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 500.00, // Fixed price for up to 2 guests
            'price_per_adult' => 0.00,
            'price_per_child' => 0.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 0.00,
            'single_use_supplement' => 0.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 50.00,
        ]);

        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 2, children: 1, infants: 0, extraBeds: 0); // 3 guests
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        // Expected: (500 + 1*50) * 7 = 550 * 7 = 3850
        $this->assertEquals(3850.00, $result->getTotalPrice()->getAmount());
        $this->assertEquals(3500.00, $result->getBasePrice()->getAmount());
        $this->assertEquals(350.00, $result->getExtraOccupancyCharge()->getAmount());
    }

    /** @test */
    public function it_handles_multiple_extra_persons(): void
    {
        $ratePlan = RatePlanFactory::new()->unitIncludedOccupancy()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 500.00,
            'price_per_adult' => 0.00,
            'price_per_child' => 0.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 0.00,
            'single_use_supplement' => 0.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 50.00,
        ]);

        $stayType = StayTypeFactory::new()->withNights(1)->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 5, children: 0, infants: 0, extraBeds: 0); // 5 guests = 3 extra
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        // Expected: 500 + 3*50 = 650
        $this->assertEquals(650.00, $result->getTotalPrice()->getAmount());
        $this->assertEquals(150.00, $result->getExtraOccupancyCharge()->getAmount());
    }

    // ==================== FIND APPLICABLE RATE RULE TESTS ====================

    /** @test */
    public function it_finds_rate_rule_within_valid_date_range(): void
    {
        $stayType = StayTypeFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();

        $ratePlan = RatePlanFactory::new()->create();

        $rateRule = RateRule::factory()->create([
            'rate_plan_id' => $ratePlan->id,
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(15),
            'base_price' => 100.00,
        ]);

        $checkinDate = Carbon::now()->addDays(10); // Within range

        $result = $this->service->findApplicableRateRule($stayType, $roomType, $checkinDate);

        $this->assertNotNull($result);
        $this->assertEquals($rateRule->id, $result->id);
    }

    /** @test */
    public function it_returns_null_when_checkin_date_is_before_start_date(): void
    {
        $stayType = StayTypeFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();

        $ratePlan = RatePlanFactory::new()->create();

        RateRule::factory()->create([
            'rate_plan_id' => $ratePlan->id,
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(20),
            'base_price' => 100.00,
        ]);

        $checkinDate = Carbon::now()->addDays(5); // Before start date

        $result = $this->service->findApplicableRateRule($stayType, $roomType, $checkinDate);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_when_checkin_date_is_after_end_date(): void
    {
        $stayType = StayTypeFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();

        $ratePlan = RatePlanFactory::new()->create();

        RateRule::factory()->create([
            'rate_plan_id' => $ratePlan->id,
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(10),
            'base_price' => 100.00,
        ]);

        $checkinDate = Carbon::now()->addDays(15); // After end date

        $result = $this->service->findApplicableRateRule($stayType, $roomType, $checkinDate);

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_null_when_rate_plan_is_inactive(): void
    {
        $stayType = StayTypeFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();

        $ratePlan = RatePlanFactory::new()->inactive()->create();

        RateRule::factory()->create([
            'rate_plan_id' => $ratePlan->id,
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(15),
            'base_price' => 100.00,
        ]);

        $checkinDate = Carbon::now()->addDays(10);

        $result = $this->service->findApplicableRateRule($stayType, $roomType, $checkinDate);

        $this->assertNull($result);
    }

    // ==================== EDGE CASES ====================

    /** @test */
    public function it_calculates_price_with_stay_duration_multiplication(): void
    {
        $ratePlan = RatePlanFactory::new()->occupancyBased()->create([
            'pricing_model' => 'occupancy_based',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'price_per_adult' => 10.00,
            'price_per_child' => 5.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 0.00,
            'single_use_supplement' => 0.00,
        ]);

        $stayType = StayTypeFactory::new()->withNights(14)->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 2, children: 2, infants: 0, extraBeds: 0);
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        // Expected: (100 + 2*10 + 2*5) * 14 = 130 * 14 = 1820
        $this->assertEquals(1820.00, $result->getTotalPrice()->getAmount());
        $this->assertEquals(1400.00, $result->getBasePrice()->getAmount());
        $this->assertEquals(280.00, $result->getAdultSupplement()->getAmount());
        $this->assertEquals(140.00, $result->getChildSupplement()->getAmount());
    }

    /** @test */
    public function it_handles_single_night_stay(): void
    {
        $ratePlan = RatePlanFactory::new()->occupancyBased()->create([
            'pricing_model' => 'occupancy_based',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 150.00,
            'price_per_adult' => 0.00,
            'price_per_child' => 0.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 0.00,
            'single_use_supplement' => 0.00,
        ]);

        $stayType = StayTypeFactory::new()->withNights(1)->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 2, children: 0, infants: 0, extraBeds: 0);
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        $this->assertEquals(150.00, $result->getTotalPrice()->getAmount());
    }

    /** @test */
    public function it_returns_breakdown_as_array(): void
    {
        $ratePlan = RatePlanFactory::new()->occupancyBased()->create([
            'pricing_model' => 'occupancy_based',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'price_per_adult' => 25.00,
            'price_per_child' => 15.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 20.00,
            'single_use_supplement' => 30.00,
        ]);

        $stayType = StayTypeFactory::new()->withNights(2)->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 1, children: 0, infants: 0, extraBeds: 0);
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        $breakdown = $result->breakdownJson();

        $this->assertArrayHasKey('base_price', $breakdown);
        $this->assertArrayHasKey('adult_supplement', $breakdown);
        $this->assertArrayHasKey('child_supplement', $breakdown);
        $this->assertArrayHasKey('infant_supplement', $breakdown);
        $this->assertArrayHasKey('extra_bed_supplement', $breakdown);
        $this->assertArrayHasKey('single_use_supplement', $breakdown);
        $this->assertArrayHasKey('extra_occupancy_charge', $breakdown);
        $this->assertArrayHasKey('total_price', $breakdown);
        $this->assertArrayHasKey('currency', $breakdown);
    }

    /** @test */
    public function it_uses_default_included_occupancy_when_not_set(): void
    {
        $ratePlan = RatePlanFactory::new()->unitIncludedOccupancy()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 500.00,
            'included_occupancy' => null, // Not set
            'price_per_extra_person' => 50.00,
        ]);

        $stayType = StayTypeFactory::new()->withNights(1)->create();
        $roomType = RoomTypeFactory::new()->create();
        $occupancy = new Occupancy(adults: 2, children: 0, infants: 0, extraBeds: 0);
        $checkinDate = Carbon::now()->addDay();

        $result = $this->service->calculatePrice($stayType, $roomType, $occupancy, $checkinDate);

        // Default included_occupancy should be 2
        $this->assertEquals(500.00, $result->getTotalPrice()->getAmount());
    }
}
