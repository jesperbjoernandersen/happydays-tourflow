<?php

namespace Tests\Unit\Actions;

use App\Actions\CreateBookingSnapshot;
use App\Models\Booking;
use App\Models\BookingGuest;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\StayType;
use Carbon\Carbon;
use Database\Factories\BookingFactory;
use Database\Factories\HotelFactory;
use Database\Factories\RoomTypeFactory;
use Database\Factories\StayTypeFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Tests for CreateBookingSnapshot action.
 */
class CreateBookingSnapshotTest extends TestCase
{
    use DatabaseMigrations;

    private CreateBookingSnapshot $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = new CreateBookingSnapshot();
    }

    /** @test */
    public function it_creates_booking_with_all_data(): void
    {
        // Arrange
        $hotel = HotelFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();
        $stayType = StayTypeFactory::new()->create([
            'hotel_id' => $hotel->id,
        ]);

        $guests = [
            ['name' => 'John Doe', 'birthdate' => '1990-05-15'],
            ['name' => 'Jane Doe', 'birthdate' => '1992-08-20'],
        ];

        $data = [
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'hotel_id' => $hotel->id,
            'check_in_date' => '2024-06-01',
            'check_out_date' => '2024-06-08',
            'total_price' => 1500.00,
            'currency' => 'EUR',
            'guests' => $guests,
            'rate_rule_snapshot' => ['base_rate' => 100],
            'hotel_age_policy_snapshot' => ['infant_age_max' => 2],
            'price_breakdown_json' => ['room_total' => 1000, 'extras' => 500],
        ];

        // Act
        $booking = $this->action->execute($data);

        // Assert
        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertNotNull($booking->id);
        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals($stayType->id, $booking->stay_type_id);
        $this->assertEquals($roomType->id, $booking->room_type_id);
        $this->assertEquals($hotel->id, $booking->hotel_id);
        $this->assertEquals(1500.00, $booking->total_price);
        $this->assertEquals('EUR', $booking->currency);
        $this->assertEquals(2, $booking->guest_count);
        $this->assertEquals('2024-06-01', $booking->check_in_date->format('Y-m-d'));
        $this->assertEquals('2024-06-08', $booking->check_out_date->format('Y-m-d'));

        // Verify snapshots were stored
        $this->assertNotNull($booking->rate_rule_snapshot);
        $this->assertNotNull($booking->hotel_age_policy_snapshot);
        $this->assertNotNull($booking->price_breakdown_json);
    }

    /** @test */
    public function it_generates_unique_booking_reference(): void
    {
        // Arrange
        $hotel = HotelFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();
        $stayType = StayTypeFactory::new()->create([
            'hotel_id' => $hotel->id,
        ]);

        $data = [
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'hotel_id' => $hotel->id,
            'check_in_date' => '2024-06-01',
            'check_out_date' => '2024-06-08',
            'total_price' => 1000.00,
            'currency' => 'EUR',
            'guests' => [
                ['name' => 'Test User', 'birthdate' => '1990-01-01'],
            ],
        ];

        // Act
        $booking1 = $this->action->execute($data);
        $booking2 = $this->action->execute($data);

        // Assert
        $this->assertNotEquals($booking1->booking_reference, $booking2->booking_reference);

        // Verify format: BK + YYYYMMDD + 6 chars
        $this->assertMatchesRegularExpression('/^BK\d{8}[A-Z0-9]{6}$/', $booking1->booking_reference);
        $this->assertMatchesRegularExpression('/^BK\d{8}[A-Z0-9]{6}$/', $booking2->booking_reference);
    }

    /** @test */
    public function it_creates_guest_records_with_correct_categories(): void
    {
        // Arrange
        $hotel = HotelFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();
        $stayType = StayTypeFactory::new()->create([
            'hotel_id' => $hotel->id,
        ]);

        // Calculate ages based on current date
        $adultBirthdate = Carbon::now()->subYears(30)->format('Y-m-d');
        $childBirthdate = Carbon::now()->subYears(8)->format('Y-m-d');
        $infantBirthdate = Carbon::now()->subMonths(18)->format('Y-m-d');

        $guests = [
            ['name' => 'Adult Guest', 'birthdate' => $adultBirthdate],
            ['name' => 'Child Guest', 'birthdate' => $childBirthdate],
            ['name' => 'Infant Guest', 'birthdate' => $infantBirthdate],
        ];

        $data = [
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'hotel_id' => $hotel->id,
            'check_in_date' => '2024-06-01',
            'check_out_date' => '2024-06-08',
            'total_price' => 1500.00,
            'currency' => 'EUR',
            'guests' => $guests,
        ];

        // Act
        $booking = $this->action->execute($data);

        // Assert
        $this->assertCount(3, $booking->guests);

        $adultGuest = $booking->guests->where('name', 'Adult Guest')->first();
        $childGuest = $booking->guests->where('name', 'Child Guest')->first();
        $infantGuest = $booking->guests->where('name', 'Infant Guest')->first();

        $this->assertEquals('adult', $adultGuest->guest_category);
        $this->assertEquals('child', $childGuest->guest_category);
        $this->assertEquals('infant', $infantGuest->guest_category);
    }

    /** @test */
    public function it_throws_exception_when_stay_type_id_is_missing(): void
    {
        // Arrange
        $data = [
            'room_type_id' => 1,
            'hotel_id' => 1,
            'check_in_date' => '2024-06-01',
            'check_out_date' => '2024-06-08',
            'total_price' => 1000.00,
            'guests' => [['name' => 'Test', 'birthdate' => '1990-01-01']],
        ];

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required field: stay_type_id');

        $this->action->execute($data);
    }

    /** @test */
    public function it_throws_exception_when_guests_are_empty(): void
    {
        // Arrange
        $data = [
            'stay_type_id' => 1,
            'room_type_id' => 1,
            'hotel_id' => 1,
            'check_in_date' => '2024-06-01',
            'check_out_date' => '2024-06-08',
            'total_price' => 1000.00,
            'guests' => [],
        ];

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Guests must be a non-empty array');

        $this->action->execute($data);
    }

    /** @test */
    public function it_throws_exception_when_total_price_is_negative(): void
    {
        // Arrange
        $data = [
            'stay_type_id' => 1,
            'room_type_id' => 1,
            'hotel_id' => 1,
            'check_in_date' => '2024-06-01',
            'check_out_date' => '2024-06-08',
            'total_price' => -100.00,
            'guests' => [['name' => 'Test', 'birthdate' => '1990-01-01']],
        ];

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Total price must be a non-negative number');

        $this->action->execute($data);
    }

    /** @test */
    public function it_throws_exception_when_checkout_is_before_checkin(): void
    {
        // Arrange
        $data = [
            'stay_type_id' => 1,
            'room_type_id' => 1,
            'hotel_id' => 1,
            'check_in_date' => '2024-06-10',
            'check_out_date' => '2024-06-01',
            'total_price' => 1000.00,
            'guests' => [['name' => 'Test', 'birthdate' => '1990-01-01']],
        ];

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Check-out date must be after check-in date');

        $this->action->execute($data);
    }

    /** @test */
    public function it_uses_default_currency_when_not_provided(): void
    {
        // Arrange
        $hotel = HotelFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();
        $stayType = StayTypeFactory::new()->create([
            'hotel_id' => $hotel->id,
        ]);

        $data = [
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'hotel_id' => $hotel->id,
            'check_in_date' => '2024-06-01',
            'check_out_date' => '2024-06-08',
            'total_price' => 1000.00,
            'guests' => [['name' => 'Test', 'birthdate' => '1990-01-01']],
            // currency not provided
        ];

        // Act
        $booking = $this->action->execute($data);

        // Assert
        $this->assertEquals('EUR', $booking->currency);
    }

    /** @test */
    public function it_creates_booking_with_null_snapshots_when_not_provided(): void
    {
        // Arrange
        $hotel = HotelFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();
        $stayType = StayTypeFactory::new()->create([
            'hotel_id' => $hotel->id,
        ]);

        $data = [
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'hotel_id' => $hotel->id,
            'check_in_date' => '2024-06-01',
            'check_out_date' => '2024-06-08',
            'total_price' => 1000.00,
            'guests' => [['name' => 'Test', 'birthdate' => '1990-01-01']],
            // No snapshots provided
        ];

        // Act
        $booking = $this->action->execute($data);

        // Assert
        $this->assertNull($booking->rate_rule_snapshot);
        $this->assertNull($booking->hotel_age_policy_snapshot);
        $this->assertNull($booking->price_breakdown_json);
    }

    /** @test */
    public function it_rolls_back_transaction_on_failure(): void
    {
        // Arrange
        $hotel = HotelFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();
        $stayType = StayTypeFactory::new()->create([
            'hotel_id' => $hotel->id,
        ]);

        $initialBookingCount = Booking::count();

        $data = [
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'hotel_id' => $hotel->id,
            'check_in_date' => '2024-06-01',
            'check_out_date' => '2024-06-01', // Same day checkout should fail
            'total_price' => 1000.00,
            'guests' => [['name' => 'Test', 'birthdate' => '1990-01-01']],
        ];

        // Act & Assert
        $this->expectException(InvalidArgumentException::class);

        try {
            $this->action->execute($data);
        } catch (InvalidArgumentException $e) {
            // Verify no booking was created
            $this->assertEquals($initialBookingCount, Booking::count());
            throw $e;
        }
    }

    /** @test */
    public function it_handles_multiple_guests_with_mixed_categories(): void
    {
        // Arrange
        $hotel = HotelFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();
        $stayType = StayTypeFactory::new()->create([
            'hotel_id' => $hotel->id,
        ]);

        // Teenager (between child and adult)
        $teenBirthdate = Carbon::now()->subYears(15)->format('Y-m-d');

        $guests = [
            ['name' => 'Adult 1', 'birthdate' => Carbon::now()->subYears(25)->format('Y-m-d')],
            ['name' => 'Adult 2', 'birthdate' => Carbon::now()->subYears(22)->format('Y-m-d')],
            ['name' => 'Teen', 'birthdate' => $teenBirthdate],
            ['name' => 'Child', 'birthdate' => Carbon::now()->subYears(5)->format('Y-m-d')],
        ];

        $data = [
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'hotel_id' => $hotel->id,
            'check_in_date' => '2024-06-01',
            'check_out_date' => '2024-06-08',
            'total_price' => 2500.00,
            'currency' => 'USD',
            'guests' => $guests,
        ];

        // Act
        $booking = $this->action->execute($data);

        // Assert
        $this->assertEquals(4, $booking->guest_count);

        $teenGuest = $booking->guests->where('name', 'Teen')->first();
        $this->assertEquals('teen', $teenGuest->guest_category);
    }

    /** @test */
    public function it_saves_notes_when_provided(): void
    {
        // Arrange
        $hotel = HotelFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();
        $stayType = StayTypeFactory::new()->create([
            'hotel_id' => $hotel->id,
        ]);

        $data = [
            'stay_type_id' => $stayType->id,
            'room_type_id' => $roomType->id,
            'hotel_id' => $hotel->id,
            'check_in_date' => '2024-06-01',
            'check_out_date' => '2024-06-08',
            'total_price' => 1000.00,
            'guests' => [['name' => 'Test', 'birthdate' => '1990-01-01']],
            'notes' => 'Special request: Late check-in',
        ];

        // Act
        $booking = $this->action->execute($data);

        // Assert
        $this->assertEquals('Special request: Late check-in', $booking->notes);
    }
}
