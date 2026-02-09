<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Hotel;
use App\Models\HotelAgePolicy;
use App\Models\StayType;
use App\Models\RoomType;
use App\Models\RatePlan;
use App\Models\RateRule;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\Allotment;

class ModelRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function hotel_has_many_age_policies()
    {
        $hotel = Hotel::factory()->create();
        $agePolicy = HotelAgePolicy::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertTrue($hotel->agePolicies->contains($agePolicy));
        $this->assertEquals(1, $hotel->agePolicies->count());
    }

    /** @test */
    public function hotel_has_many_stay_types()
    {
        $hotel = Hotel::factory()->create();
        $stayType = StayType::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertTrue($hotel->stayTypes->contains($stayType));
        $this->assertEquals(1, $hotel->stayTypes->count());
    }

    /** @test */
    public function hotel_has_many_room_types()
    {
        $hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertTrue($hotel->roomTypes->contains($roomType));
        $this->assertEquals(1, $hotel->roomTypes->count());
    }

    /** @test */
    public function hotel_has_many_rate_plans()
    {
        $hotel = Hotel::factory()->create();
        $ratePlan = RatePlan::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertTrue($hotel->ratePlans->contains($ratePlan));
        $this->assertEquals(1, $hotel->ratePlans->count());
    }

    /** @test */
    public function hotel_has_many_bookings()
    {
        $hotel = Hotel::factory()->create();
        $booking = Booking::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertTrue($hotel->bookings->contains($booking));
        $this->assertEquals(1, $hotel->bookings->count());
    }

    /** @test */
    public function hotel_age_policy_belongs_to_hotel()
    {
        $hotel = Hotel::factory()->create();
        $agePolicy = HotelAgePolicy::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertEquals($hotel->id, $agePolicy->hotel->id);
    }

    /** @test */
    public function hotel_age_policy_has_many_stay_types()
    {
        $agePolicy = HotelAgePolicy::factory()->create();
        $stayType = StayType::factory()->create(['hotel_age_policy_id' => $agePolicy->id]);

        $this->assertTrue($agePolicy->stayTypes->contains($stayType));
    }

    /** @test */
    public function stay_type_belongs_to_hotel()
    {
        $hotel = Hotel::factory()->create();
        $stayType = StayType::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertEquals($hotel->id, $stayType->hotel->id);
    }

    /** @test */
    public function stay_type_belongs_to_age_policy()
    {
        $agePolicy = HotelAgePolicy::factory()->create();
        $stayType = StayType::factory()->create(['hotel_age_policy_id' => $agePolicy->id]);

        $this->assertEquals($agePolicy->id, $stayType->agePolicy->id);
    }

    /** @test */
    public function stay_type_has_many_bookings()
    {
        $stayType = StayType::factory()->create();
        $booking = Booking::factory()->create(['stay_type_id' => $stayType->id]);

        $this->assertTrue($stayType->bookings->contains($booking));
    }

    /** @test */
    public function room_type_belongs_to_hotel()
    {
        $hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertEquals($hotel->id, $roomType->hotel->id);
    }

    /** @test */
    public function room_type_can_be_null_for_standalone_houses()
    {
        $roomType = RoomType::factory()->create([
            'hotel_id' => null,
            'room_type' => 'house'
        ]);

        $this->assertNull($roomType->hotel);
    }

    /** @test */
    public function room_type_has_many_bookings()
    {
        $roomType = RoomType::factory()->create();
        $booking = Booking::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertTrue($roomType->bookings->contains($booking));
    }

    /** @test */
    public function room_type_has_many_allotments()
    {
        $roomType = RoomType::factory()->create();
        $allotment = Allotment::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertTrue($roomType->allotments->contains($allotment));
    }

    /** @test */
    public function rate_plan_belongs_to_hotel()
    {
        $hotel = Hotel::factory()->create();
        $ratePlan = RatePlan::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertEquals($hotel->id, $ratePlan->hotel->id);
    }

    /** @test */
    public function rate_plan_has_many_rate_rules()
    {
        $ratePlan = RatePlan::factory()->create();
        $rateRule = RateRule::factory()->create(['rate_plan_id' => $ratePlan->id]);

        $this->assertTrue($ratePlan->rateRules->contains($rateRule));
    }

    /** @test */
    public function rate_rule_belongs_to_rate_plan()
    {
        $ratePlan = RatePlan::factory()->create();
        $rateRule = RateRule::factory()->create(['rate_plan_id' => $ratePlan->id]);

        $this->assertEquals($ratePlan->id, $rateRule->ratePlan->id);
    }

    /** @test */
    public function rate_rule_belongs_to_stay_type()
    {
        $stayType = StayType::factory()->create();
        $rateRule = RateRule::factory()->create(['stay_type_id' => $stayType->id]);

        $this->assertEquals($stayType->id, $rateRule->stayType->id);
    }

    /** @test */
    public function rate_rule_belongs_to_room_type()
    {
        $roomType = RoomType::factory()->create();
        $rateRule = RateRule::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertEquals($roomType->id, $rateRule->roomType->id);
    }

    /** @test */
    public function booking_belongs_to_stay_type()
    {
        $stayType = StayType::factory()->create();
        $booking = Booking::factory()->create(['stay_type_id' => $stayType->id]);

        $this->assertEquals($stayType->id, $booking->stayType->id);
    }

    /** @test */
    public function booking_belongs_to_room_type()
    {
        $roomType = RoomType::factory()->create();
        $booking = Booking::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertEquals($roomType->id, $booking->roomType->id);
    }

    /** @test */
    public function booking_belongs_to_hotel()
    {
        $hotel = Hotel::factory()->create();
        $booking = Booking::factory()->create(['hotel_id' => $hotel->id]);

        $this->assertEquals($hotel->id, $booking->hotel->id);
    }

    /** @test */
    public function booking_has_many_guests()
    {
        $booking = Booking::factory()->create();
        $guest = BookingGuest::factory()->create(['booking_id' => $booking->id]);

        $this->assertTrue($booking->guests->contains($guest));
    }

    /** @test */
    public function booking_guest_belongs_to_booking()
    {
        $booking = Booking::factory()->create();
        $guest = BookingGuest::factory()->create(['booking_id' => $booking->id]);

        $this->assertEquals($booking->id, $guest->booking->id);
    }

    /** @test */
    public function allotment_belongs_to_room_type()
    {
        $roomType = RoomType::factory()->create();
        $allotment = Allotment::factory()->create(['room_type_id' => $roomType->id]);

        $this->assertEquals($roomType->id, $allotment->roomType->id);
    }
}
