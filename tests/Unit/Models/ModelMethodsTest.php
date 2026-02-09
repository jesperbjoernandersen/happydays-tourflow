<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\Allotment;
use App\Models\RateRule;
use App\Models\RoomType;
use Carbon\Carbon;

class ModelMethodsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function booking_generates_unique_reference()
    {
        $booking = Booking::factory()->create();
        $reference = $booking->generateBookingReference();

        $this->assertNotEmpty($reference);
        $this->assertStringStartsWith('BK', $reference);
        $this->assertEquals(16, strlen($reference)); // BK + YYYYMMDD + 6 char hash
    }

    /** @test */
    public function booking_total_guests_computed_from_guests()
    {
        $booking = Booking::factory()->create();
        BookingGuest::factory()->count(3)->create(['booking_id' => $booking->id]);

        $this->assertEquals(3, $booking->total_guests);
    }

    /** @test */
    public function booking_nights_computed_from_dates()
    {
        $booking = Booking::factory()->create([
            'check_in_date' => Carbon::parse('2024-06-01'),
            'check_out_date' => Carbon::parse('2024-06-05'),
        ]);

        $this->assertEquals(4, $booking->nights);
    }

    /** @test */
    public function booking_price_per_night_computed()
    {
        $booking = Booking::factory()->create([
            'check_in_date' => Carbon::parse('2024-06-01'),
            'check_out_date' => Carbon::parse('2024-06-05'),
            'total_price' => 400.00,
        ]);

        $this->assertEquals(100.00, $booking->price_per_night);
    }

    /** @test */
    public function booking_guest_calculates_age_at_check_in()
    {
        $booking = Booking::factory()->create([
            'check_in_date' => Carbon::parse('2024-06-01'),
        ]);

        $guest = BookingGuest::factory()->create([
            'booking_id' => $booking->id,
            'birthdate' => Carbon::parse('2015-06-01'),
        ]);

        $this->assertEquals(9, $guest->getAgeAtCheckIn());
    }

    /** @test */
    public function booking_guest_calculates_age_with_null_birthdate()
    {
        $booking = Booking::factory()->create([
            'check_in_date' => Carbon::parse('2024-06-01'),
        ]);

        $guest = BookingGuest::factory()->create([
            'booking_id' => $booking->id,
            'birthdate' => null,
        ]);

        $this->assertNull($guest->getAgeAtCheckIn());
    }

    /** @test */
    public function allotment_remaining_calculated_correctly()
    {
        $allotment = Allotment::factory()->create([
            'quantity' => 10,
            'allocated' => 3,
        ]);

        $this->assertEquals(7, $allotment->remaining);
    }

    /** @test */
    public function allotment_remaining_not_negative()
    {
        $allotment = Allotment::factory()->create([
            'quantity' => 5,
            'allocated' => 10,
        ]);

        $this->assertEquals(0, $allotment->remaining);
    }

    /** @test */
    public function allotment_is_available_when_not_stop_sell_and_has_remaining()
    {
        $available = Allotment::factory()->create([
            'quantity' => 10,
            'allocated' => 5,
            'stop_sell' => false,
        ]);

        $this->assertTrue($available->is_available);
    }

    /** @test */
    public function allotment_is_not_available_when_stop_sell()
    {
        $notAvailable = Allotment::factory()->create([
            'quantity' => 10,
            'allocated' => 5,
            'stop_sell' => true,
        ]);

        $this->assertFalse($notAvailable->is_available);
    }

    /** @test */
    public function allotment_is_not_available_when_no_remaining()
    {
        $notAvailable = Allotment::factory()->create([
            'quantity' => 5,
            'allocated' => 5,
            'stop_sell' => false,
        ]);

        $this->assertFalse($notAvailable->is_available);
    }

    /** @test */
    public function room_type_max_total_occupancy_includes_extra_beds()
    {
        $roomType = RoomType::factory()->create([
            'max_occupancy' => 4,
            'extra_bed_slots' => 2,
        ]);

        $this->assertEquals(6, $roomType->max_total_occupancy);
    }

    /** @test */
    public function room_type_is_hotel_room()
    {
        $hotelRoom = RoomType::factory()->create([
            'room_type' => 'hotel',
        ]);

        $this->assertTrue($hotelRoom->isHotelRoom());
        $this->assertFalse($hotelRoom->isHouse());
    }

    /** @test */
    public function room_type_is_house()
    {
        $house = RoomType::factory()->create([
            'room_type' => 'house',
            'hotel_id' => null,
        ]);

        $this->assertTrue($house->isHouse());
        $this->assertFalse($house->isHotelRoom());
    }

    /** @test */
    public function rate_rule_calculates_price_correctly()
    {
        $rateRule = RateRule::factory()->create([
            'base_price' => 100.00,
            'price_per_adult' => 50.00,
            'price_per_child' => 25.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 20.00,
            'single_use_supplement' => 30.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 40.00,
        ]);

        // 2 adults, 1 child, 1 extra bed, double occupancy
        $price = $rateRule->calculatePrice(
            adults: 2,
            children: 1,
            infants: 0,
            extraBeds: 1,
            isSingleUse: false
        );

        // Base 100 + adult(2*50=100) + child 25 + extra bed 20 = 245
        $this->assertEquals(245.00, $price);
    }

    /** @test */
    public function rate_rule_calculates_price_with_single_use_supplement()
    {
        $rateRule = RateRule::factory()->create([
            'base_price' => 100.00,
            'price_per_adult' => 50.00,
            'price_per_child' => 25.00,
            'price_per_infant' => 0.00,
            'price_per_extra_bed' => 20.00,
            'single_use_supplement' => 30.00,
            'included_occupancy' => 2,
            'price_per_extra_person' => 40.00,
        ]);

        // Single use with 1 adult
        $price = $rateRule->calculatePrice(
            adults: 1,
            isSingleUse: true
        );

        // Base 100 + adult(1*50=50) + single use 30 = 180
        $this->assertEquals(180.00, $price);
    }

    /** @test */
    public function allotment_scope_for_date_works()
    {
        $allotment = Allotment::factory()->create([
            'date' => Carbon::parse('2024-06-15'),
        ]);

        $found = Allotment::forDate(Carbon::parse('2024-06-15'))->first();

        $this->assertNotNull($found);
        $this->assertEquals($allotment->id, $found->id);
    }

    /** @test */
    public function allotment_scope_available_works()
    {
        $available = Allotment::factory()->create([
            'quantity' => 10,
            'allocated' => 5,
            'stop_sell' => false,
        ]);

        $notAvailable = Allotment::factory()->create([
            'quantity' => 5,
            'allocated' => 5,
            'stop_sell' => false,
        ]);

        $availableAllotments = Allotment::available()->get();

        $this->assertTrue($availableAllotments->contains($available));
        $this->assertFalse($availableAllotments->contains($notAvailable));
    }

    /** @test */
    public function booking_pending_scope_works()
    {
        $pending = Booking::factory()->create(['status' => 'pending']);
        $confirmed = Booking::factory()->create(['status' => 'confirmed']);

        $pendingBookings = Booking::pending()->get();

        $this->assertTrue($pendingBookings->contains($pending));
        $this->assertFalse($pendingBookings->contains($confirmed));
    }

    /** @test */
    public function booking_confirmed_scope_works()
    {
        $confirmed = Booking::factory()->create(['status' => 'confirmed']);
        $pending = Booking::factory()->create(['status' => 'pending']);

        $confirmedBookings = Booking::confirmed()->get();

        $this->assertTrue($confirmedBookings->contains($confirmed));
        $this->assertFalse($confirmedBookings->contains($pending));
    }
}
