<?php

namespace Tests\Unit\Livewire;

use App\Domain\ValueObjects\PriceBreakdown;
use App\Livewire\BookingConfirmation;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\StayType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BookingConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private Hotel $hotel;
    private StayType $stayType;
    private RoomType $roomType;
    private array $guests;
    private PriceBreakdown $priceBreakdown;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hotel = Hotel::create([
            'name' => 'Test Hotel',
            'code' => 'TST',
            'address' => '123 Test St',
            'city' => 'Test City',
            'country' => 'DK',
        ]);

        $this->stayType = StayType::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Weekend Stay',
            'code' => 'WKD',
            'nights' => 2,
            'is_active' => true,
        ]);

        $this->roomType = RoomType::create([
            'hotel_id' => $this->hotel->id,
            'name' => 'Double Room',
            'code' => 'DBL',
            'room_type' => 'hotel',
            'base_occupancy' => 2,
            'max_occupancy' => 4,
            'extra_bed_slots' => 1,
            'single_use_supplement' => 25.00,
            'is_active' => true,
        ]);

        $this->guests = [
            ['name' => 'John Doe', 'birthdate' => '1990-05-15', 'age' => 35, 'category' => 'ADULT'],
            ['name' => 'Jane Smith', 'birthdate' => '1992-08-20', 'age' => 33, 'category' => 'ADULT'],
            ['name' => 'Tommy Jones', 'birthdate' => '2015-03-10', 'age' => 11, 'category' => 'CHILD'],
        ];

        $this->priceBreakdown = new PriceBreakdown(
            basePrice: 200.00,
            childSupplement: 25.00,
            currency: 'EUR',
            nights: 2,
            baseOccupancy: 2,
            adults: 2,
            children: 1,
        );
    }

    /** @test */
    public function it_renders_successfully(): void
    {
        Livewire::test(BookingConfirmation::class)
            ->assertStatus(200);
    }

    /** @test */
    public function it_mounts_with_provided_values(): void
    {
        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'children' => 1,
            'infants' => 0,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ]);

        $component->assertSet('stayTypeId', $this->stayType->id);
        $component->assertSet('roomTypeId', $this->roomType->id);
        $component->assertSet('checkinDate', '2026-03-15');
        $component->assertSet('checkoutDate', '2026-03-17');
        $component->assertSet('adults', 2);
        $component->assertSet('children', 1);
        $component->assertSet('infants', 0);
        $component->assertCount('guests', 3);
        $component->assertSet('termsAccepted', false);
    }

    /** @test */
    public function it_displays_stay_type_name(): void
    {
        Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ])
            ->assertSee('Weekend Stay');
    }

    /** @test */
    public function it_displays_room_type_name(): void
    {
        Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ])
            ->assertSee('Double Room');
    }

    /** @test */
    public function it_displays_guest_names(): void
    {
        Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'children' => 1,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ])
            ->assertSee('John Doe')
            ->assertSee('Jane Smith')
            ->assertSee('Tommy Jones');
    }

    /** @test */
    public function it_displays_guest_ages(): void
    {
        Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'children' => 1,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ])
            ->assertSee('35')
            ->assertSee('33')
            ->assertSee('11');
    }

    /** @test */
    public function it_displays_guest_categories(): void
    {
        Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'children' => 1,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ])
            ->assertSee('Adult')
            ->assertSee('Child');
    }

    /** @test */
    public function it_displays_total_price(): void
    {
        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'children' => 1,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ]);

        // Verify the pricing section is rendered with the total
        $storedData = $component->get('priceBreakdownData');
        $reconstructed = PriceBreakdown::fromArray($storedData);
        $component->assertSee($reconstructed->formatTotalPrice());
    }

    /** @test */
    public function it_calculates_nights_correctly(): void
    {
        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ]);

        $component->assertSet('nights', 2);
    }

    /** @test */
    public function it_calculates_total_guests_correctly(): void
    {
        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'children' => 1,
            'infants' => 1,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ]);

        $component->assertSet('totalGuests', 4);
    }

    /** @test */
    public function it_requires_terms_acceptance_before_confirming(): void
    {
        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ]);

        // Try to confirm without accepting terms
        $component->call('confirmBooking');
        $component->assertHasErrors(['termsAccepted']);
    }

    /** @test */
    public function it_emits_booking_confirmed_event_when_terms_accepted(): void
    {
        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'children' => 1,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ]);

        $component->set('termsAccepted', true);
        $component->call('confirmBooking');

        $component->assertDispatched('bookingConfirmed');
    }

    /** @test */
    public function it_emits_booking_confirmed_with_correct_data(): void
    {
        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'children' => 1,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ]);

        $component->set('termsAccepted', true);
        $component->call('confirmBooking');

        $component->assertDispatched('bookingConfirmed', function ($eventName, $data) {
            $payload = $data[0];

            return $payload['stay_type_id'] === $this->stayType->id
                && $payload['room_type_id'] === $this->roomType->id
                && $payload['checkin_date'] === '2026-03-15'
                && $payload['checkout_date'] === '2026-03-17'
                && $payload['adults'] === 2
                && $payload['children'] === 1
                && $payload['terms_accepted'] === true
                && count($payload['guests']) === 3;
        });
    }

    /** @test */
    public function it_emits_back_to_edit_event(): void
    {
        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ]);

        $component->call('backToEdit');
        $component->assertDispatched('backToEdit');
    }

    /** @test */
    public function it_accepts_price_breakdown_data_as_array(): void
    {
        $priceData = $this->priceBreakdown->toArray();

        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'guests' => $this->guests,
            'priceBreakdownData' => $priceData,
        ]);

        // Verify the price breakdown data was stored (check key fields)
        $storedData = $component->get('priceBreakdownData');
        $this->assertNotNull($storedData);
        $this->assertIsArray($storedData);
        $this->assertArrayHasKey('base_price', $storedData);
        $this->assertArrayHasKey('currency', $storedData);
    }

    /** @test */
    public function it_shows_terms_error_message(): void
    {
        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ]);

        $component->call('confirmBooking');
        $component->assertSee('You must accept the terms and conditions before confirming.');
    }

    /** @test */
    public function it_displays_checkin_and_checkout_dates(): void
    {
        Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ])
            ->assertSee('Sun, 15 Mar 2026')
            ->assertSee('Tue, 17 Mar 2026');
    }

    /** @test */
    public function it_handles_zero_nights_gracefully(): void
    {
        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '',
            'checkoutDate' => '',
            'adults' => 1,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ]);

        $component->assertSet('nights', 0);
    }

    /** @test */
    public function it_displays_confirm_booking_button(): void
    {
        Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ])
            ->assertSee('Confirm Booking')
            ->assertSee('Back to Edit');
    }

    /** @test */
    public function it_does_not_emit_booking_confirmed_without_terms(): void
    {
        $component = Livewire::test(BookingConfirmation::class, [
            'stayTypeId' => $this->stayType->id,
            'roomTypeId' => $this->roomType->id,
            'checkinDate' => '2026-03-15',
            'checkoutDate' => '2026-03-17',
            'adults' => 2,
            'guests' => $this->guests,
            'priceBreakdown' => $this->priceBreakdown,
        ]);

        $component->set('termsAccepted', false);
        $component->call('confirmBooking');

        $component->assertNotDispatched('bookingConfirmed');
    }
}
