<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HotelsAndPoliciesSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Hotels
        $hotels = [
            [
                'name' => 'Sunset Beach Resort',
                'code' => 'SUNSET-BEACH',
                'address' => '123 Ocean Drive',
                'city' => 'Barcelona',
                'country' => 'Spain',
                'email' => 'info@sunsetbeach.com',
                'phone' => '+34 934 123 456',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mountain View Hotel',
                'code' => 'MOUNTAIN-VIEW',
                'address' => '456 Alpine Road',
                'city' => 'Innsbruck',
                'country' => 'Austria',
                'email' => 'welcome@mountainview.at',
                'phone' => '+43 512 789 012',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Grand Palace Hotel',
                'code' => 'GRAND-PALACE',
                'address' => '789 Royal Boulevard',
                'city' => 'Paris',
                'country' => 'France',
                'email' => 'contact@grandpalace.fr',
                'phone' => '+33 1 234 567 89',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lakeside Inn',
                'code' => 'LAKESIDE-INN',
                'address' => '321 Lake Shore Way',
                'city' => 'Zurich',
                'country' => 'Switzerland',
                'email' => 'hello@lakesideinn.ch',
                'phone' => '+41 44 123 45 67',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cozy City Apartments',
                'code' => 'COZY-CITY',
                'address' => '654 Urban Street',
                'city' => 'Berlin',
                'country' => 'Germany',
                'email' => 'stay@cozycity.de',
                'phone' => '+49 30 987 654 32',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('hotels')->insert($hotels);

        // Create Age Policies for each hotel
        $agePolicies = [
            // Sunset Beach Resort policies
            [
                'hotel_id' => 1,
                'name' => 'Standard Policy',
                'infant_max_age' => 2,
                'child_max_age' => 12,
                'adult_min_age' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 1,
                'name' => 'Generous Family Policy',
                'infant_max_age' => 4,
                'child_max_age' => 16,
                'adult_min_age' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mountain View Hotel policies
            [
                'hotel_id' => 2,
                'name' => 'Standard Policy',
                'infant_max_age' => 2,
                'child_max_age' => 12,
                'adult_min_age' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Grand Palace Hotel policies
            [
                'hotel_id' => 3,
                'name' => 'Strict Adult Only',
                'infant_max_age' => 0,
                'child_max_age' => 16,
                'adult_min_age' => 21,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 3,
                'name' => 'Standard Policy',
                'infant_max_age' => 2,
                'child_max_age' => 12,
                'adult_min_age' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Lakeside Inn policies
            [
                'hotel_id' => 4,
                'name' => 'Strict Policy',
                'infant_max_age' => 1,
                'child_max_age' => 16,
                'adult_min_age' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Cozy City Apartments policies
            [
                'hotel_id' => 5,
                'name' => 'Standard Policy',
                'infant_max_age' => 2,
                'child_max_age' => 12,
                'adult_min_age' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('hotel_age_policies')->insert($agePolicies);

        // Create Room Types for hotels
        $roomTypes = [
            // Sunset Beach Resort rooms
            [
                'hotel_id' => 1,
                'name' => 'Single Room',
                'code' => 'SUNSET-SGL',
                'room_type' => 'hotel',
                'base_occupancy' => 1,
                'max_occupancy' => 1,
                'extra_bed_slots' => 0,
                'single_use_supplement' => 25.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 1,
                'name' => 'Double Room',
                'code' => 'SUNSET-DBL',
                'room_type' => 'hotel',
                'base_occupancy' => 2,
                'max_occupancy' => 2,
                'extra_bed_slots' => 1,
                'single_use_supplement' => 15.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 1,
                'name' => 'Family Suite',
                'code' => 'SUNSET-FAM',
                'room_type' => 'hotel',
                'base_occupancy' => 2,
                'max_occupancy' => 4,
                'extra_bed_slots' => 2,
                'single_use_supplement' => 0.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Mountain View Hotel rooms
            [
                'hotel_id' => 2,
                'name' => 'Double Room',
                'code' => 'MOUNT-DBL',
                'room_type' => 'hotel',
                'base_occupancy' => 2,
                'max_occupancy' => 2,
                'extra_bed_slots' => 1,
                'single_use_supplement' => 20.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 2,
                'name' => 'Family Room',
                'code' => 'MOUNT-FAM',
                'room_type' => 'hotel',
                'base_occupancy' => 2,
                'max_occupancy' => 4,
                'extra_bed_slots' => 2,
                'single_use_supplement' => 0.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Grand Palace Hotel rooms
            [
                'hotel_id' => 3,
                'name' => 'Deluxe Suite',
                'code' => 'PALACE-SUITE',
                'room_type' => 'hotel',
                'base_occupancy' => 2,
                'max_occupancy' => 3,
                'extra_bed_slots' => 1,
                'single_use_supplement' => 50.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => 3,
                'name' => 'Executive Suite',
                'code' => 'PALACE-EXEC',
                'room_type' => 'hotel',
                'base_occupancy' => 2,
                'max_occupancy' => 4,
                'extra_bed_slots' => 2,
                'single_use_supplement' => 75.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Lakeside Inn rooms (inactive hotel)
            [
                'hotel_id' => 4,
                'name' => 'Standard Double',
                'code' => 'LAKE-DBL',
                'room_type' => 'hotel',
                'base_occupancy' => 2,
                'max_occupancy' => 2,
                'extra_bed_slots' => 0,
                'single_use_supplement' => 10.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Cozy City Apartments rooms
            [
                'hotel_id' => 5,
                'name' => 'Studio Apartment',
                'code' => 'COZY-STUDIO',
                'room_type' => 'hotel',
                'base_occupancy' => 2,
                'max_occupancy' => 2,
                'extra_bed_slots' => 1,
                'single_use_supplement' => 0.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Vacation Houses (without hotel)
            [
                'hotel_id' => null,
                'name' => 'Beach Vacation House',
                'code' => 'VH-BEACH-01',
                'room_type' => 'house',
                'base_occupancy' => 4,
                'max_occupancy' => 6,
                'extra_bed_slots' => 2,
                'single_use_supplement' => 0.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'hotel_id' => null,
                'name' => 'Mountain Cabin',
                'code' => 'VH-MTN-01',
                'room_type' => 'house',
                'base_occupancy' => 4,
                'max_occupancy' => 6,
                'extra_bed_slots' => 2,
                'single_use_supplement' => 0.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('room_types')->insert($roomTypes);
    }
}
