<?php

namespace Tests\Unit\Services;

use App\Services\PricingService;
use App\Models\RatePlan;
use App\Models\RateRule;
use App\Models\RoomType;
use App\Models\StayType;
use Carbon\Carbon;
use Database\Factories\RatePlanFactory;
use Database\Factories\RateRuleFactory;
use Database\Factories\RoomTypeFactory;
use Database\Factories\StayTypeFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * PricingServiceTest - Edge Cases
 *
 * Tests comprehensive edge cases for pricing calculations:
 * - Single Use Pricing (supplement ON TOP of base price)
 * - Extra Occupancy (guests > included_occupancy)
 * - Mixed Pricing Models (occupancy_based, unit_included_occupancy)
 * - Zero/Negative Pricing (infant pricing, price floor at 0)
 * - Currency Handling (2 decimal rounding)
 */
class PricingServiceTest extends TestCase
{
    private PricingService $pricingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pricingService = new PricingService();
        
        // Run migrations manually for tests
        $this->artisan('migrate');
    }

    // ==================== SINGLE USE PRICING EDGE CASES ====================

    /**
     * EDGE CASE: Single use with max occupancy
     * When 1 adult books room with single_use_supplement > 0
     * Supplement should apply ON TOP of base price, not instead of
     */
    public function test_single_use_supplement_applies_on_top_of_base_price(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 4,
            'single_use_supplement' => 0,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'single_use_supplement' => 50.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 25.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $guests = [
            ['name' => 'Single Adult', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            2, // 2 nights
            $guests,
            0
        );

        $this->assertFalse(isset($result['error']), 'Should not have error: ' . ($result['error'] ?? ''));
        
        // Base price: 100 * 2 nights = 200
        // Single use supplement: 50 * 2 nights = 100
        // Total: 300 (supplement ON TOP of base, not instead of)
        $this->assertEquals(300.00, $result['total_price']);
        $this->assertEquals(200.00, $result['breakdown']['base_price']);
        $this->assertEquals(100.00, $result['breakdown']['single_use_supplement']);
    }

    public function test_single_use_not_applied_when_multiple_guests(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 4,
            'single_use_supplement' => 50.00,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'single_use_supplement' => 50.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 25.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $guests = [
            ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            2,
            $guests,
            0
        );

        $this->assertFalse(isset($result['error']));
        
        // No single use supplement when 2 adults
        $this->assertEquals(0.00, $result['breakdown']['single_use_supplement']);
        $this->assertEquals(200.00, $result['total_price']); // Base only
    }

    public function test_single_use_from_room_type_fallback(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 4,
            'single_use_supplement' => 40.00,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        // Rate rule has no single use supplement
        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'single_use_supplement' => 0, // No supplement in rate rule
            'included_occupancy' => 2,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $guests = [
            ['name' => 'Single Adult', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            1,
            $guests,
            0
        );

        // Should use room type's single use supplement as fallback
        $this->assertEquals(140.00, $result['total_price']);
    }

    // ==================== EXTRA OCCUPANCY EDGE CASES ====================

    /**
     * EDGE CASE: Extra person when room is full
     * When total guests > included_occupancy
     */
    public function test_extra_person_charges_when_exceeding_included_occupancy(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 4,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'included_occupancy' => 2, // Base price includes 2 guests
            'price_per_extra_person' => 30.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        // 3 guests (2 included + 1 extra)
        $guests = [
            ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Child', 'birthdate' => '2015-01-01', 'guest_category' => 'child'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            2,
            $guests,
            0
        );

        // Base: 100 * 2 = 200 (includes 2 guests)
        // Extra person: 30 * 1 * 2 = 60 (1 extra guest for 2 nights)
        $this->assertEquals(260.00, $result['total_price']);
        $this->assertEquals(200.00, $result['breakdown']['base_price']);
        $this->assertEquals(60.00, $result['breakdown']['extra_person_charges']);
    }

    public function test_infants_count_toward_total_occupancy(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 4,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 30.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        // 2 adults + 1 infant = 3 total (infant counts!)
        $guests = [
            ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Infant', 'birthdate' => '2024-01-01', 'guest_category' => 'infant'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            1,
            $guests,
            0
        );

        // Infant counts toward occupancy, so 1 extra person charge
        $this->assertEquals(130.00, $result['total_price']);
        $this->assertEquals(100.00, $result['breakdown']['base_price']);
        $this->assertEquals(30.00, $result['breakdown']['extra_person_charges']);
    }

    public function test_multiple_extra_persons_charged_correctly(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 6,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 150.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 25.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        // 5 guests total (2 included + 3 extra)
        $guests = [
            ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Child 1', 'birthdate' => '2015-01-01', 'guest_category' => 'child'],
            ['name' => 'Child 2', 'birthdate' => '2016-01-01', 'guest_category' => 'child'],
            ['name' => 'Infant', 'birthdate' => '2024-01-01', 'guest_category' => 'infant'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            3,
            $guests,
            0
        );

        // Base: 150 * 3 = 450 (includes 2 guests)
        // Extra persons: 25 * 3 * 3 = 225 (3 extra guests for 3 nights)
        $this->assertEquals(675.00, $result['total_price']);
    }

    // ==================== ZERO INFANT PRICING EDGE CASES ====================

    /**
     * EDGE CASE: Zero infant pricing
     * Infants typically cost 0, but still count for occupancy
     */
    public function test_infant_pricing_is_zero(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 4,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'occupancy_based',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'price_per_adult' => 50.00,
            'price_per_child' => 25.00,
            'price_per_infant' => 0.00, // Infants are free
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $guests = [
            ['name' => 'Adult', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Infant', 'birthdate' => '2024-01-01', 'guest_category' => 'infant'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            1,
            $guests,
            0
        );

        // Adults: 50 * 2 = 100
        // Infants: 0 * 1 = 0
        $this->assertEquals(200.00, $result['total_price']); // Base + 2 adults
        $this->assertEquals(0.00, $result['breakdown']['per_person_charges'] - 
            (50.00 * 2)); // Infant charge should be 0
    }

    public function test_infants_only_booking_has_zero_price(): void
    {
        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'occupancy_based',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 50.00,
            'price_per_adult' => 0,
            'price_per_child' => 0,
            'price_per_infant' => 0,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $roomType = RoomTypeFactory::new()->create();

        // Edge case: Only infants (but we require at least 1 adult, so this tests validation)
        $guests = [
            ['name' => 'Infant 1', 'birthdate' => '2024-01-01', 'guest_category' => 'infant'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            1,
            $guests,
            0
        );

        // Should fail validation - at least one adult required
        $this->assertTrue(isset($result['error']));
        $this->containsString('adult', $result['error']);
    }

    // ==================== LARGE GROUP EDGE CASES ====================

    /**
     * EDGE CASE: Large groups (8+ guests)
     */
    public function test_large_group_pricing(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 10,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 200.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 20.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        // 8 guests total
        $guests = [
            ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 3', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 4', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Child 1', 'birthdate' => '2015-01-01', 'guest_category' => 'child'],
            ['name' => 'Child 2', 'birthdate' => '2015-01-01', 'guest_category' => 'child'],
            ['name' => 'Infant 1', 'birthdate' => '2024-01-01', 'guest_category' => 'infant'],
            ['name' => 'Infant 2', 'birthdate' => '2024-01-01', 'guest_category' => 'infant'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            7, // Week stay
            $guests,
            0
        );

        // Base: 200 * 7 = 1400 (includes 2 guests)
        // Extra persons: 20 * 6 * 7 = 840 (6 extra guests)
        $this->assertEquals(2240.00, $result['total_price']);
        $this->assertEquals(6, $result['breakdown']['guests']['total'] - $result['breakdown']['included_occupancy']);
    }

    // ==================== MIXED PRICING MODELS ====================

    // MODELS ====================

    /**
     * EDGE CASE: Rate rule with included_occupancy for UNIT_INCLUDED model
     */
    public function test_unit_included_model_with_included_occupancy(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 4,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 150.00,
            'included_occupancy' => 2, // Base price covers up to 2 guests
            'price_per_extra_person' => 35.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        // Exactly 2 guests (within included occupancy)
        $guests = [
            ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            3,
            $guests,
            0
        );

        // Just base price, no extras
        $this->assertEquals(450.00, $result['total_price']); // 150 * 3
        $this->assertEquals(0.00, $result['breakdown']['extra_person_charges']);
    }

    public function test_occupancy_based_model_per_person_pricing(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 1,
            'max_occupancy' => 4,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'occupancy_based',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 80.00, // Base for single occupancy
            'price_per_adult' => 40.00,
            'price_per_child' => 20.00,
            'price_per_infant' => 0.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $guests = [
            ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Child', 'birthdate' => '2015-01-01', 'guest_category' => 'child'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            2,
            $guests,
            0
        );

        // Base: 80 * 2 = 160 (single occupancy)
        // Per person: (40 * 2 + 20 * 1) * 2 = 200 (2 adults + 1 child)
        // Total: 360
        $this->assertEquals(360.00, $result['total_price']);
        $this->assertEquals(160.00, $result['breakdown']['base_price']);
        $this->assertEquals(200.00, $result['breakdown']['per_person_charges']);
    }

    // ==================== CURRENCY HANDLING ====================

    /**
     * EDGE CASE: Currency rounding to 2 decimal places
     */
    public function test_pricing_rounds_to_two_decimal_places(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 4,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        // Use prices that could result in rounding issues
        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 99.99,
            'included_occupancy' => 2,
            'price_per_extra_person' => 33.33,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $guests = [
            ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 3', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            3,
            $guests,
            0
        );

        // Base: 99.99 * 3 = 299.97
        // Extra: 33.33 * 1 * 3 = 99.99
        // Total: 399.96
        $this->assertEquals(399.96, $result['total_price']);
        $this->assertEquals(299.97, $result['breakdown']['base_price']);
        $this->assertEquals(99.99, $result['breakdown']['extra_person_charges']);
    }

    public function test_currency_field_is_set(): void
    {
        $roomType = RoomTypeFactory::new()->create();
        $ratePlan = RatePlanFactory::new()->create();
        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $guests = [
            ['name' => 'Adult', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            1,
            $guests,
            0
        );

        $this->assertArrayHasKey('currency', $result);
        $this->assertEquals('EUR', $result['currency']);
    }

    // ==================== EXTRA BED EDGE CASES ====================

    public function test_extra_bed_charges(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 4,
            'extra_bed_slots' => 2,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'included_occupancy' => 2,
            'price_per_extra_bed' => 15.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $guests = [
            ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            2,
            $guests,
            2 // 2 extra beds
        );

        // Base: 100 * 2 = 200
        // Extra beds: 15 * 2 * 2 = 60
        $this->assertEquals(260.00, $result['total_price']);
        $this->assertEquals(60.00, $result['breakdown']['extra_bed_charges']);
    }

    public function test_zero_extra_beds(): void
    {
        $roomType = RoomTypeFactory::new()->create();
        $ratePlan = RatePlanFactory::new()->create();
        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 100.00,
            'price_per_extra_bed' => 15.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $guests = [
            ['name' => 'Adult', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            1,
            $guests,
            0 // No extra beds
        );

        $this->assertEquals(0.00, $result['breakdown']['extra_bed_charges']);
    }

    // ==================== NEGATIVE PRICE PROTECTION ====================

    /**
     * EDGE CASE: Ensure total price doesn't become negative
     */
    public function test_price_never_goes_negative(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 4,
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        // Edge case: Very low base price, high discounts scenario
        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'base_price' => 10.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 0, // Could theoretically be negative in some systems
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $guests = [
            ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            null,
            Carbon::today(),
            1,
            $guests,
            0
        );

        // Price should be at minimum 0
        $this->assertGreaterThanOrEqual(0, $result['total_price']);
    }

    // ==================== STAY TYPE INTEGRATION ====================

    public function test_price_calculation_with_stay_type(): void
    {
        $roomType = RoomTypeFactory::new()->create([
            'base_occupancy' => 2,
            'max_occupancy' => 4,
        ]);

        $stayType = StayTypeFactory::new()->create([
            'nights' => 7, // Package stay
        ]);

        $ratePlan = RatePlanFactory::new()->create([
            'pricing_model' => 'unit_included_occupancy',
        ]);

        $rateRule = RateRuleFactory::new()->create([
            'rate_plan_id' => $ratePlan->id,
            'rate_rule_type' => 'stay_type', // This could be a field
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'base_price' => 600.00, // Package price for 7 nights
            'included_occupancy' => 2,
            'price_per_extra_person' => 30.00,
            'start_date' => Carbon::today()->subDay(),
            'end_date' => Carbon::today()->addYear(),
        ]);

        $guests = [
            ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ['name' => 'Child', 'birthdate' => '2015-01-01', 'guest_category' => 'child'],
        ];

        $result = $this->pricingService->calculatePrice(
            $ratePlan,
            $roomType,
            $stayType,
            Carbon::today(),
            7,
            $guests,
            0
        );

        // Package base: 600 (includes 2 guests)
        // Extra person: 30 * 1 = 30
        $this->assertEquals(630.00, $result['total_price']);
    }

    // ==================== HELPER METHODS ====================

    private function containsString(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '$haystack' contains '$needle'"
        );
    }
}
