<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StayTypesSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Stay Types (7-night packages)
        $stayTypes = [
            [
                'hotel_id' => 1, // Sunset Beach Resort - Barcelona
                'name' => 'Sunny Week',
                'description' => 'Barcelona beach package - 7 nights of sun, sea, and relaxation on the Mediterranean coast. All Inclusive package with unlimited dining and beverages.',
                'code' => 'ST-SUNNY-WEEK',
                'nights' => 7,
                'included_board_type' => 'AI', // All Inclusive
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 2, // Mountain View Hotel - Innsbruck
                'name' => 'Mountain Retreat',
                'description' => 'Alpine package - 7 nights in the heart of the mountains. Perfect for hiking, skiing, or simply enjoying the fresh mountain air. Half Board included.',
                'code' => 'ST-MOUNTAIN-RETREAT',
                'nights' => 7,
                'included_board_type' => 'HB', // Half Board
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 3, // Grand Palace Hotel - Paris
                'name' => 'City Break Paris',
                'description' => 'Urban package - 7 nights in the City of Light. Explore Paris at your own pace with Bed & Breakfast included.',
                'code' => 'ST-CITY-BREAK-PARIS',
                'nights' => 7,
                'included_board_type' => 'BB', // Bed & Breakfast
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 1, // Sunset Beach Resort - Barcelona
                'name' => 'Family Fun',
                'description' => 'Kid-friendly package - 7 nights of family entertainment with kids club, pool activities, and family-friendly dining. All Inclusive.',
                'code' => 'ST-FAMILY-FUN',
                'nights' => 7,
                'included_board_type' => 'AI', // All Inclusive
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 3, // Grand Palace Hotel - Paris
                'name' => 'Romantic Getaway',
                'description' => 'Couples package - 7 nights of romance in Paris. Candlelit dinners, spa treatments, and champagne welcome. Half Board.',
                'code' => 'ST-ROMANTIC-GETAWAY',
                'nights' => 7,
                'included_board_type' => 'HB', // Half Board
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('stay_types')->insert($stayTypes);

        // Create Rate Plans
        $ratePlans = [
            // Occupancy-based pricing per person
            [
                'hotel_id' => 1,
                'name' => 'Standard Occupancy Rate',
                'code' => 'RP-STANDARD-OCC',
                'description' => 'Pricing based on number of occupants. Per-person rate varies by age category.',
                'pricing_model' => 'occupancy_based',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 1,
                'name' => 'Family Package Rate',
                'code' => 'RP-FAMILY-PACKAGE',
                'description' => 'Special family rates with reduced child pricing.',
                'pricing_model' => 'occupancy_based',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 2,
                'name' => 'Standard Occupancy Rate',
                'code' => 'RP-STANDARD-OCC',
                'description' => 'Pricing based on number of occupants. Per-person rate varies by age category.',
                'pricing_model' => 'occupancy_based',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 2,
                'name' => 'Couple\'s Retreat Rate',
                'code' => 'RP-COUPLE-REATREAT',
                'description' => 'Special rate for couples with romantic extras included.',
                'pricing_model' => 'occupancy_based',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 3,
                'name' => 'Standard Occupancy Rate',
                'code' => 'RP-STANDARD-OCC',
                'description' => 'Pricing based on number of occupants. Per-person rate varies by age category.',
                'pricing_model' => 'occupancy_based',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Unit included occupancy (fixed price for 2)
            [
                'hotel_id' => 1,
                'name' => 'Double Occupancy Fixed',
                'code' => 'RP-DOUBLE-FIXED',
                'description' => 'Fixed price for double occupancy (2 adults). Extra persons charged separately.',
                'pricing_model' => 'unit_included_occupancy',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 2,
                'name' => 'Double Occupancy Fixed',
                'code' => 'RP-DOUBLE-FIXED',
                'description' => 'Fixed price for double occupancy (2 adults). Extra persons charged separately.',
                'pricing_model' => 'unit_included_occupancy',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 3,
                'name' => 'Double Occupancy Fixed',
                'code' => 'RP-DOUBLE-FIXED',
                'description' => 'Fixed price for double occupancy (2 adults). Extra persons charged separately.',
                'pricing_model' => 'unit_included_occupancy',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('rate_plans')->insert($ratePlans);

        // Create Rate Rules for Stay Types + Rate Plans
        // Sunny Week (stay_type_id: 1) - Barcelona
        $rateRules = [
            // Sunny Week - Standard Occupancy Rate
            [
                'rate_plan_id' => 1,
                'stay_type_id' => 1,
                'room_type_id' => null, // Applies to all room types
                'start_date' => '2026-04-01',
                'end_date' => '2026-10-31',
                'base_price' => 0, // Not used for occupancy-based
                'price_per_adult' => 150.00,
                'price_per_child' => 75.00,
                'price_per_infant' => 0.00,
                'price_per_extra_bed' => 50.00,
                'single_use_supplement' => 200.00,
                'included_occupancy' => null,
                'price_per_extra_person' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Sunny Week - Double Occupancy Fixed
            [
                'rate_plan_id' => 6,
                'stay_type_id' => 1,
                'room_type_id' => 2, // Double Room
                'start_date' => '2026-04-01',
                'end_date' => '2026-10-31',
                'base_price' => 1200.00, // Fixed for 2 persons
                'price_per_adult' => 0,
                'price_per_child' => 0,
                'price_per_infant' => 0,
                'price_per_extra_bed' => 0,
                'single_use_supplement' => 0,
                'included_occupancy' => 2,
                'price_per_extra_person' => 100.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mountain Retreat (stay_type_id: 2) - Innsbruck
            [
                'rate_plan_id' => 3,
                'stay_type_id' => 2,
                'room_type_id' => null,
                'start_date' => '2026-05-01',
                'end_date' => '2026-10-31',
                'base_price' => 0,
                'price_per_adult' => 120.00,
                'price_per_child' => 60.00,
                'price_per_infant' => 0.00,
                'price_per_extra_bed' => 40.00,
                'single_use_supplement' => 180.00,
                'included_occupancy' => null,
                'price_per_extra_person' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mountain Retreat - Double Occupancy Fixed
            [
                'rate_plan_id' => 7,
                'stay_type_id' => 2,
                'room_type_id' => 4, // Double Room
                'start_date' => '2026-05-01',
                'end_date' => '2026-10-31',
                'base_price' => 980.00, // Fixed for 2 persons
                'price_per_adult' => 0,
                'price_per_child' => 0,
                'price_per_infant' => 0,
                'price_per_extra_bed' => 0,
                'single_use_supplement' => 0,
                'included_occupancy' => 2,
                'price_per_extra_person' => 80.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // City Break Paris (stay_type_id: 3) - Paris
            [
                'rate_plan_id' => 5,
                'stay_type_id' => 3,
                'room_type_id' => null,
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'base_price' => 0,
                'price_per_adult' => 180.00,
                'price_per_child' => 90.00,
                'price_per_infant' => 0.00,
                'price_per_extra_bed' => 60.00,
                'single_use_supplement' => 250.00,
                'included_occupancy' => null,
                'price_per_extra_person' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // City Break Paris - Double Occupancy Fixed
            [
                'rate_plan_id' => 8,
                'stay_type_id' => 3,
                'room_type_id' => 6, // Deluxe Suite
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'base_price' => 1500.00, // Fixed for 2 persons
                'price_per_adult' => 0,
                'price_per_child' => 0,
                'price_per_infant' => 0,
                'price_per_extra_bed' => 0,
                'single_use_supplement' => 0,
                'included_occupancy' => 2,
                'price_per_extra_person' => 120.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Family Fun (stay_type_id: 4) - Barcelona
            [
                'rate_plan_id' => 2,
                'stay_type_id' => 4,
                'room_type_id' => 3, // Family Suite
                'start_date' => '2026-04-01',
                'end_date' => '2026-10-31',
                'base_price' => 0,
                'price_per_adult' => 140.00,
                'price_per_child' => 50.00,
                'price_per_infant' => 0.00,
                'price_per_extra_bed' => 30.00,
                'single_use_supplement' => 0.00,
                'included_occupancy' => null,
                'price_per_extra_person' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Romantic Getaway (stay_type_id: 5) - Paris
            [
                'rate_plan_id' => 4,
                'stay_type_id' => 5,
                'room_type_id' => 7, // Executive Suite
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'base_price' => 0,
                'price_per_adult' => 200.00,
                'price_per_child' => 0.00,
                'price_per_infant' => 0.00,
                'price_per_extra_bed' => 80.00,
                'single_use_supplement' => 0.00,
                'included_occupancy' => null,
                'price_per_extra_person' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Romantic Getaway - Double Occupancy Fixed (Suite only)
            [
                'rate_plan_id' => 8,
                'stay_type_id' => 5,
                'room_type_id' => 7, // Executive Suite
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'base_price' => 1800.00, // Fixed for 2 persons
                'price_per_adult' => 0,
                'price_per_child' => 0,
                'price_per_infant' => 0,
                'price_per_extra_bed' => 0,
                'single_use_supplement' => 0,
                'included_occupancy' => 2,
                'price_per_extra_person' => 150.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('rate_rules')->insert($rateRules);
    }
}
