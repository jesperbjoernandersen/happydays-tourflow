<?php

namespace Database\Seeders;

use App\Models\RatePlan;
use App\Models\RateRule;
use App\Models\RoomType;
use App\Models\StayType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PricingRulesSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     *
     * Creates comprehensive pricing rules for different scenarios:
     * - Seasonal pricing (high, low, shoulder seasons)
     * - Special event pricing (Christmas, Easter, local festivals)
     * - Length of stay discounts (7+, 14+, 21+ nights)
     * - Early bird discounts (60+, 30+ days ahead)
     */
    public function run(): void
    {
        $this->command->info('Creating pricing rules seed data...');

        // Get rate plans for each hotel
        $ratePlans = RatePlan::all();
        $stayTypes = StayType::all();
        $roomTypes = RoomType::whereNotNull('hotel_id')->get();

        // ==========================================
        // 1. SEASONAL PRICING RULES
        // ==========================================

        $this->command->info('Creating seasonal pricing rules...');

        // High Season (June-August) - Premium pricing (+25%)
        $this->createSeasonalRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: 'High Season Premium',
            code: 'SEASON-HIGH',
            startMonth: 6,
            endMonth: 8,
            basePriceMultiplier: 1.25,
            description: 'High season pricing - peak summer rates'
        );

        // Low Season (November-March) - Discounted pricing (-20%)
        $this->createSeasonalRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: 'Low Season Discount',
            code: 'SEASON-LOW',
            startMonth: 11,
            endMonth: 3,
            basePriceMultiplier: 0.80,
            description: 'Low season pricing - winter discount rates'
        );

        // Shoulder Season (April-May, September-October) - Standard pricing
        $this->createSeasonalRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: 'Shoulder Season Standard',
            code: 'SEASON-SHOULDER',
            startMonth: 4,
            endMonth: 5,
            basePriceMultiplier: 1.00,
            description: 'Shoulder season pricing - standard rates'
        );

        // Fall Shoulder Season (September-October)
        $this->createSeasonalRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: 'Fall Shoulder Season',
            code: 'SEASON-FALL-SHOULDER',
            startMonth: 9,
            endMonth: 10,
            basePriceMultiplier: 1.00,
            description: 'Fall shoulder season pricing - standard rates'
        );

        // ==========================================
        // 2. SPECIAL EVENT PRICING RULES
        // ==========================================

        $this->command->info('Creating special event pricing rules...');

        // Christmas/New Year - High premium (+50%)
        $this->createDateRangeRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: 'Christmas/New Year Premium',
            code: 'EVENT-XMAS-NY',
            startDate: '2026-12-20',
            endDate: '2027-01-05',
            basePriceMultiplier: 1.50,
            description: 'Christmas and New Year premium pricing'
        );

        // Easter - Medium premium (+30%)
        $this->createDateRangeRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: 'Easter Premium',
            code: 'EVENT-EASTER',
            startDate: '2027-04-14',
            endDate: '2027-04-18',
            basePriceMultiplier: 1.30,
            description: 'Easter holiday premium pricing'
        );

        // Local Festival - Variable premium (+20%)
        $this->createDateRangeRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: 'Barcelona Summer Festival',
            code: 'EVENT-BCN-SUMMER',
            startDate: '2026-08-20',
            endDate: '2026-08-25',
            basePriceMultiplier: 1.20,
            description: 'Barcelona summer festival - local event pricing'
        );

        // Paris Fashion Week - High premium (+40%)
        $this->createDateRangeRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: 'Paris Fashion Week',
            code: 'EVENT-PARIS-FW',
            startDate: '2027-09-26',
            endDate: '2027-10-01',
            basePriceMultiplier: 1.40,
            description: 'Paris Fashion Week premium pricing'
        );

        // ==========================================
        // 3. LENGTH OF STAY DISCOUNTS
        // ==========================================

        $this->command->info('Creating length of stay discount rules...');

        // 7+ nights - 5% discount
        $this->createLengthOfStayRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: '7+ Nights Discount',
            code: 'LOS-7-DAYS',
            minNights: 7,
            discountPercent: 5,
            description: '5% discount for stays of 7 or more nights'
        );

        // 14+ nights - 10% discount
        $this->createLengthOfStayRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: '14+ Nights Discount',
            code: 'LOS-14-DAYS',
            minNights: 14,
            discountPercent: 10,
            description: '10% discount for stays of 14 or more nights'
        );

        // 21+ nights - 15% discount
        $this->createLengthOfStayRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: '21+ Nights Discount',
            code: 'LOS-21-DAYS',
            minNights: 21,
            discountPercent: 15,
            description: '15% discount for stays of 21 or more nights'
        );

        // ==========================================
        // 4. EARLY BIRD DISCOUNTS
        // ==========================================

        $this->command->info('Creating early bird discount rules...');

        // 60+ days ahead - 10% off
        $this->createEarlyBirdRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: '60+ Days Early Bird',
            code: 'EARLY-60-DAYS',
            minDaysAhead: 60,
            discountPercent: 10,
            description: '10% discount for bookings made 60+ days in advance'
        );

        // 30+ days ahead - 5% off
        $this->createEarlyBirdRules(
            ratePlans: $ratePlans,
            stayTypes: $stayTypes,
            roomTypes: $roomTypes,
            name: '30+ Days Early Bird',
            code: 'EARLY-30-DAYS',
            minDaysAhead: 30,
            discountPercent: 5,
            description: '5% discount for bookings made 30+ days in advance'
        );

        $this->command->info('Pricing rules seed data created successfully!');
    }

    /**
     * Create seasonal pricing rules for a date range pattern
     */
    protected function createSeasonalRules(
        $ratePlans,
        $stayTypes,
        $roomTypes,
        string $name,
        string $code,
        int $startMonth,
        int $endMonth,
        float $basePriceMultiplier,
        string $description
    ): void {
        // Use 2027 as the reference year for seasonal patterns
        $year = 2027;

        // Handle wrap-around months (e.g., Nov-March)
        if ($startMonth > $endMonth) {
            // First period: startMonth to December
            $firstPeriodEnd = \Carbon\Carbon::create($year, 12, 31);
            $firstPeriodStart = \Carbon\Carbon::create($year, $startMonth, 1);

            // Second period: January to endMonth
            $secondPeriodStart = \Carbon\Carbon::create($year, 1, 1);
            $secondPeriodEnd = \Carbon\Carbon::create($year, $endMonth, \Carbon\Carbon::create($year, $endMonth)->daysInMonth);

            $periods = [
                [$firstPeriodStart, $firstPeriodEnd],
                [$secondPeriodStart, $secondPeriodEnd],
            ];
        } else {
            // Normal period within the same year
            $startDate = \Carbon\Carbon::create($year, $startMonth, 1);
            $endDate = \Carbon\Carbon::create($year, $endMonth, \Carbon\Carbon::create($year, $endMonth)->daysInMonth);
            $periods = [[$startDate, $endDate]];
        }

        foreach ($periods as $period) {
            [$startDate, $endDate] = $period;

            foreach ($ratePlans as $ratePlan) {
                // Determine base price based on pricing model
                $basePrice = $this->getBasePriceForRatePlan($ratePlan);

                // Create rule for rate plan
                RateRule::create([
                    'rate_plan_id' => $ratePlan->id,
                    'stay_type_id' => null, // Applies to all stay types
                    'room_type_id' => null, // Applies to all room types
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'base_price' => $basePrice * $basePriceMultiplier,
                    'price_per_adult' => 25.00 * $basePriceMultiplier,
                    'price_per_child' => 15.00 * $basePriceMultiplier,
                    'price_per_infant' => 5.00 * $basePriceMultiplier,
                    'price_per_extra_bed' => 20.00 * $basePriceMultiplier,
                    'single_use_supplement' => 0,
                    'included_occupancy' => null,
                    'price_per_extra_person' => 30.00 * $basePriceMultiplier,
                ]);

                // Create rules for each stay type
                foreach ($stayTypes as $stayType) {
                    $stayBasePrice = $this->getBasePriceForStayType($stayType);
                    RateRule::create([
                        'rate_plan_id' => $ratePlan->id,
                        'stay_type_id' => $stayType->id,
                        'room_type_id' => null,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'base_price' => $stayBasePrice * $basePriceMultiplier,
                        'price_per_adult' => 0,
                        'price_per_child' => 0,
                        'price_per_infant' => 0,
                        'price_per_extra_bed' => 0,
                        'single_use_supplement' => 0,
                        'included_occupancy' => null,
                        'price_per_extra_person' => 0,
                    ]);
                }

                // Create rules for each room type
                foreach ($roomTypes as $roomType) {
                    $roomBasePrice = $this->getBasePriceForRoomType($roomType);
                    RateRule::create([
                        'rate_plan_id' => $ratePlan->id,
                        'stay_type_id' => null,
                        'room_type_id' => $roomType->id,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'base_price' => $roomBasePrice * $basePriceMultiplier,
                        'price_per_adult' => 0,
                        'price_per_child' => 0,
                        'price_per_infant' => 0,
                        'price_per_extra_bed' => 0,
                        'single_use_supplement' => 0,
                        'included_occupancy' => $roomType->base_occupancy,
                        'price_per_extra_person' => 35.00 * $basePriceMultiplier,
                    ]);
                }
            }
        }
    }

    /**
     * Create special event pricing rules for specific date ranges
     */
    protected function createDateRangeRules(
        $ratePlans,
        $stayTypes,
        $roomTypes,
        string $name,
        string $code,
        string $startDate,
        string $endDate,
        float $basePriceMultiplier,
        string $description
    ): void {
        foreach ($ratePlans as $ratePlan) {
            $basePrice = $this->getBasePriceForRatePlan($ratePlan);

            RateRule::create([
                'rate_plan_id' => $ratePlan->id,
                'stay_type_id' => null,
                'room_type_id' => null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'base_price' => $basePrice * $basePriceMultiplier,
                'price_per_adult' => 30.00 * $basePriceMultiplier,
                'price_per_child' => 18.00 * $basePriceMultiplier,
                'price_per_infant' => 5.00 * $basePriceMultiplier,
                'price_per_extra_bed' => 25.00 * $basePriceMultiplier,
                'single_use_supplement' => 0,
                'included_occupancy' => null,
                'price_per_extra_person' => 40.00 * $basePriceMultiplier,
            ]);

            // Stay type specific rules
            foreach ($stayTypes as $stayType) {
                $stayBasePrice = $this->getBasePriceForStayType($stayType);
                RateRule::create([
                    'rate_plan_id' => $ratePlan->id,
                    'stay_type_id' => $stayType->id,
                    'room_type_id' => null,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'base_price' => $stayBasePrice * $basePriceMultiplier,
                    'price_per_adult' => 0,
                    'price_per_child' => 0,
                    'price_per_infant' => 0,
                    'price_per_extra_bed' => 0,
                    'single_use_supplement' => 0,
                    'included_occupancy' => null,
                    'price_per_extra_person' => 0,
                ]);
            }
        }
    }

    /**
     * Create length of stay discount rules
     */
    protected function createLengthOfStayRules(
        $ratePlans,
        $stayTypes,
        $roomTypes,
        string $name,
        string $code,
        int $minNights,
        int $discountPercent,
        string $description
    ): void {
        // Create rules that span the entire year for LOS discounts
        $startDate = '2027-01-01';
        $endDate = '2027-12-31';
        $multiplier = 1 - ($discountPercent / 100);

        foreach ($ratePlans as $ratePlan) {
            $basePrice = $this->getBasePriceForRatePlan($ratePlan);

            RateRule::create([
                'rate_plan_id' => $ratePlan->id,
                'stay_type_id' => null,
                'room_type_id' => null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'base_price' => $basePrice * $multiplier,
                'price_per_adult' => 25.00 * $multiplier,
                'price_per_child' => 15.00 * $multiplier,
                'price_per_infant' => 5.00 * $multiplier,
                'price_per_extra_bed' => 20.00 * $multiplier,
                'single_use_supplement' => 0,
                'included_occupancy' => null,
                'price_per_extra_person' => 30.00 * $multiplier,
            ]);

            // Stay type specific
            foreach ($stayTypes as $stayType) {
                $stayBasePrice = $this->getBasePriceForStayType($stayType);
                RateRule::create([
                    'rate_plan_id' => $ratePlan->id,
                    'stay_type_id' => $stayType->id,
                    'room_type_id' => null,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'base_price' => $stayBasePrice * $multiplier,
                    'price_per_adult' => 0,
                    'price_per_child' => 0,
                    'price_per_infant' => 0,
                    'price_per_extra_bed' => 0,
                    'single_use_supplement' => 0,
                    'included_occupancy' => null,
                    'price_per_extra_person' => 0,
                ]);
            }

            // Room type specific
            foreach ($roomTypes as $roomType) {
                $roomBasePrice = $this->getBasePriceForRoomType($roomType);
                RateRule::create([
                    'rate_plan_id' => $ratePlan->id,
                    'stay_type_id' => null,
                    'room_type_id' => $roomType->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'base_price' => $roomBasePrice * $multiplier,
                    'price_per_adult' => 0,
                    'price_per_child' => 0,
                    'price_per_infant' => 0,
                    'price_per_extra_bed' => 0,
                    'single_use_supplement' => 0,
                    'included_occupancy' => $roomType->base_occupancy,
                    'price_per_extra_person' => 35.00 * $multiplier,
                ]);
            }
        }
    }

    /**
     * Create early bird discount rules
     */
    protected function createEarlyBirdRules(
        $ratePlans,
        $stayTypes,
        $roomTypes,
        string $name,
        string $code,
        int $minDaysAhead,
        int $discountPercent,
        string $description
    ): void {
        // Create rules that span the entire year for early bird discounts
        $startDate = '2027-01-01';
        $endDate = '2027-12-31';
        $multiplier = 1 - ($discountPercent / 100);

        foreach ($ratePlans as $ratePlan) {
            $basePrice = $this->getBasePriceForRatePlan($ratePlan);

            RateRule::create([
                'rate_plan_id' => $ratePlan->id,
                'stay_type_id' => null,
                'room_type_id' => null,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'base_price' => $basePrice * $multiplier,
                'price_per_adult' => 25.00 * $multiplier,
                'price_per_child' => 15.00 * $multiplier,
                'price_per_infant' => 5.00 * $multiplier,
                'price_per_extra_bed' => 20.00 * $multiplier,
                'single_use_supplement' => 0,
                'included_occupancy' => null,
                'price_per_extra_person' => 30.00 * $multiplier,
            ]);

            // Stay type specific
            foreach ($stayTypes as $stayType) {
                $stayBasePrice = $this->getBasePriceForStayType($stayType);
                RateRule::create([
                    'rate_plan_id' => $ratePlan->id,
                    'stay_type_id' => $stayType->id,
                    'room_type_id' => null,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'base_price' => $stayBasePrice * $multiplier,
                    'price_per_adult' => 0,
                    'price_per_child' => 0,
                    'price_per_infant' => 0,
                    'price_per_extra_bed' => 0,
                    'single_use_supplement' => 0,
                    'included_occupancy' => null,
                    'price_per_extra_person' => 0,
                ]);
            }

            // Room type specific
            foreach ($roomTypes as $roomType) {
                $roomBasePrice = $this->getBasePriceForRoomType($roomType);
                RateRule::create([
                    'rate_plan_id' => $ratePlan->id,
                    'stay_type_id' => null,
                    'room_type_id' => $roomType->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'base_price' => $roomBasePrice * $multiplier,
                    'price_per_adult' => 0,
                    'price_per_child' => 0,
                    'price_per_infant' => 0,
                    'price_per_extra_bed' => 0,
                    'single_use_supplement' => 0,
                    'included_occupancy' => $roomType->base_occupancy,
                    'price_per_extra_person' => 35.00 * $multiplier,
                ]);
            }
        }
    }

    /**
     * Get base price for a rate plan
     */
    protected function getBasePriceForRatePlan(RatePlan $ratePlan): float
    {
        // Different base prices based on rate plan characteristics
        if (str_contains($ratePlan->code, 'FAMILY')) {
            return 450.00;
        }
        if (str_contains($ratePlan->code, 'COUPLE')) {
            return 550.00;
        }
        if (str_contains($ratePlan->code, 'DOUBLE-FIXED')) {
            return 350.00;
        }
        return 400.00;
    }

    /**
     * Get base price for a stay type
     */
    protected function getBasePriceForStayType(StayType $stayType): float
    {
        // Different base prices based on board type
        $nights = $stayType->nights;
        
        switch ($stayType->included_board_type) {
            case 'AI': // All Inclusive
                return 150.00 * $nights;
            case 'HB': // Half Board
                return 100.00 * $nights;
            case 'BB': // Bed & Breakfast
                return 70.00 * $nights;
            default:
                return 80.00 * $nights;
        }
    }

    /**
     * Get base price for a room type
     */
    protected function getBasePriceForRoomType(RoomType $roomType): float
    {
        // Different base prices based on room type
        $baseOccupancy = $roomType->base_occupancy;
        
        switch ($roomType->room_type) {
            case 'hotel':
                if ($baseOccupancy === 1) {
                    return 120.00; // Single
                } elseif ($baseOccupancy === 2) {
                    return 200.00; // Double
                } else {
                    return 350.00; // Family/Suite
                }
            case 'house':
                return 450.00; // Vacation house
            default:
                return 200.00;
        }
    }
}
