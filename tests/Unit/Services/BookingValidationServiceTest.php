<?php

namespace Tests\Unit\Services;

use App\Services\BookingValidationService;
use App\Services\AvailabilityService;
use App\Models\Allotment;
use App\Models\RoomType;
use App\Models\StayType;
use App\Models\HotelAgePolicy;
use App\Models\BookingGuest;
use App\Domain\ValueObjects\ValidationResult;
use App\Domain\ValueObjects\AvailabilityResult;
use Carbon\Carbon;
use Database\Factories\AllotmentFactory;
use Database\Factories\RoomTypeFactory;
use Database\Factories\StayTypeFactory;
use Database\Factories\HotelAgePolicyFactory;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingValidationServiceTest extends TestCase
{
    private BookingValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BookingValidationService();
    }

    // ==================== VALIDATION RESULT TESTS ====================

    public function test_validation_result_is_valid_when_no_errors(): void
    {
        $result = ValidationResult::valid();

        $this->assertTrue($result->isValid());
        $this->assertEmpty($result->getErrors());
        $this->assertEmpty($result->getWarnings());
    }

    public function test_validation_result_is_invalid_with_errors(): void
    {
        $errors = [
            ['field' => 'test', 'message' => 'Test error'],
        ];
        $result = ValidationResult::invalid($errors);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }

    public function test_validation_result_can_have_warnings(): void
    {
        $warnings = [
            ['field' => 'guests', 'message' => 'Near max occupancy'],
        ];
        $result = ValidationResult::valid($warnings);

        $this->assertTrue($result->isValid());
        $this->assertCount(1, $result->getWarnings());
    }

    public function test_validation_result_converts_to_array(): void
    {
        $errors = [['field' => 'test', 'message' => 'Error']];
        $warnings = [['field' => 'guests', 'message' => 'Warning']];

        $result = new ValidationResult(false, $errors, $warnings);
        $array = $result->toArray();

        $this->assertArrayHasKey('isValid', $array);
        $this->assertArrayHasKey('errors', $array);
        $this->assertArrayHasKey('warnings', $array);
        $this->assertFalse($array['isValid']);
    }

    // ==================== STAY DURATION VALIDATION TESTS ====================

    public function test_it_passes_validation_for_valid_stay_duration(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        // Mock the availability service to return available
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::available([['available' => 10]], 10)
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertTrue($result->isValid(), 'Validation should pass for valid stay duration');
    }

    public function test_it_fails_when_stay_does_not_match_package_nights(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 5, // Package requires 7 nights
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'total_price' => 1000.00,
        ];

        $result = $this->service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'nights'));
    }

    public function test_it_fails_when_nights_is_zero_or_negative(): void
    {
        $stayType = StayTypeFactory::new()->create();
        $roomType = RoomTypeFactory::new()->create();

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 0,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'total_price' => 1000.00,
        ];

        $result = $this->service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'nights'));
    }

    // ==================== GUEST COUNT VALIDATION TESTS ====================

    public function test_it_passes_validation_for_valid_guest_count(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create([
            'max_occupancy' => 4,
            'extra_bed_slots' => 1,
        ]);

        // Mock the availability service to return available
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::available([['available' => 10]], 10)
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'Adult', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
                ['name' => 'Child', 'birthdate' => '2015-01-01', 'guest_category' => 'child'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertTrue($result->isValid());
    }

    public function test_it_fails_when_no_adult_present(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'Child', 'birthdate' => '2015-01-01', 'guest_category' => 'child'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $this->service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'guests', 'At least 1 adult is required'));
    }

    public function test_it_fails_when_guests_exceed_max_occupancy(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create([
            'max_occupancy' => 2,
        ]);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
                ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
                ['name' => 'Adult 3', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $this->service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'guests', 'Maximum 2 guests allowed'));
    }

    public function test_it_fails_when_extra_beds_exceed_limit(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create([
            'extra_bed_slots' => 1,
        ]);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'Adult', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 2, // Room only has 1 extra bed slot
            'total_price' => 1000.00,
        ];

        $result = $this->service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'extra_beds', 'Maximum 1 extra beds allowed'));
    }

    public function test_it_shows_warning_when_near_max_occupancy(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create([
            'max_occupancy' => 4,
        ]);

        // Mock the availability service to return available
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::available([['available' => 10]], 10)
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'Adult 1', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
                ['name' => 'Adult 2', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
                ['name' => 'Adult 3', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertTrue($result->isValid());
        $this->assertTrue($this->hasWarning($result, 'guests', 'Near max occupancy'));
    }

    // ==================== DATE AVAILABILITY VALIDATION TESTS ====================

    public function test_it_passes_validation_when_dates_are_available(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        // Mock the availability service to return available
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::available([['available' => 10]], 10)
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertTrue($result->isValid());
    }

    public function test_it_fails_when_checkin_date_is_in_past(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->subDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'total_price' => 1000.00,
        ];

        $result = $this->service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'check_in_date', 'Check-in date cannot be in the past'));
    }

    public function test_it_fails_when_dates_have_stop_sell(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        // Mock the availability service to return unavailable with stop sell
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::unavailable('Stop sell is active', [
                ['date' => '2026-02-11', 'reason' => 'Stop sell is active'],
            ])
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'check_in_date', 'Stop sell is active'));
    }

    public function test_it_fails_when_dates_are_sold_out(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        // Mock the availability service to return unavailable with sold out
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::unavailable('No rooms available', [
                ['date' => '2026-02-11', 'reason' => 'No rooms available'],
            ])
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'check_in_date', 'No rooms available'));
    }

    // ==================== AGE POLICY VALIDATION TESTS ====================

    public function test_it_passes_validation_for_valid_guest_ages(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        // Mock the availability service to return available
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::available([['available' => 10]], 10)
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'Adult', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
                ['name' => 'Child', 'birthdate' => '2015-01-01', 'guest_category' => 'child'],
                ['name' => 'Infant', 'birthdate' => '2023-01-01', 'guest_category' => 'infant'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertTrue($result->isValid());
    }

    public function test_it_fails_when_guest_birthdate_is_missing(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'Guest', 'birthdate' => null, 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $this->service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'guests.0.birthdate', 'Birthdate is required'));
    }

    public function test_it_fails_when_guest_category_is_invalid(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'Guest', 'birthdate' => '1990-01-01', 'guest_category' => 'teen'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $this->service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'guests.0.guest_category', 'Invalid guest category'));
    }

    // ==================== PRICING VALIDATION TESTS ====================

    public function test_it_passes_validation_for_valid_price(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        // Mock the availability service to return available
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::available([['available' => 10]], 10)
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertTrue($result->isValid());
    }

    public function test_it_fails_when_price_is_zero(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 0,
        ];

        $result = $this->service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'total_price', 'Price must be greater than 0'));
    }

    public function test_it_fails_when_price_is_negative(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => -100.00,
        ];

        $result = $this->service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'total_price', 'Price must be greater than 0'));
    }

    // ==================== MIN/MAX STAY VALIDATION TESTS ====================

    public function test_it_fails_when_min_stay_not_met(): void
    {
        $stayType = StayTypeFactory::new()->withNights(3)->create();
        $roomType = RoomTypeFactory::new()->create();

        // Mock the availability service to return unavailable with min stay error
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::unavailable('Minimum stay of 5 nights required', [
                ['date' => '2026-02-11', 'reason' => 'Minimum stay of 5 nights required'],
            ])
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 3,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'check_in_date', 'Minimum stay'));
    }

    public function test_it_fails_when_max_stay_exceeded(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();

        // Mock the availability service to return unavailable with max stay error
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::unavailable('Maximum stay of 5 nights allowed', [
                ['date' => '2026-02-11', 'reason' => 'Maximum stay of 5 nights allowed'],
            ])
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 7,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'check_in_date', 'Maximum stay'));
    }

    // ==================== CTA/CTD VALIDATION TESTS ====================

    public function test_it_fails_when_cta_is_set_on_checkin_date(): void
    {
        $stayType = StayTypeFactory::new()->withNights(3)->create();
        $roomType = RoomTypeFactory::new()->create();

        // Mock the availability service to return unavailable with CTA error
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::unavailable('Close to arrival (CTA)', [
                ['date' => '2026-02-11', 'reason' => 'Close to arrival (CTA)'],
            ])
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 3,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'check_in_date', 'Close to arrival'));
    }

    public function test_it_fails_when_ctd_is_set_on_checkout_date(): void
    {
        $stayType = StayTypeFactory::new()->withNights(3)->create();
        $roomType = RoomTypeFactory::new()->create();

        // Mock the availability service to return unavailable with CTD error
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::unavailable('Close to departure (CTD)', [
                ['date' => '2026-02-13', 'reason' => 'Close to departure (CTD)'],
            ])
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => Carbon::now()->addDay(),
            'nights' => 3,
            'guests' => [
                ['name' => 'John', 'birthdate' => '1990-01-01', 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validate($data);

        $this->assertFalse($result->isValid());
        $this->assertTrue($this->hasError($result, 'check_in_date', 'Close to departure'));
    }

    // ==================== FULL BOOKING VALIDATION WITH AGE POLICY TESTS ====================

    public function test_it_validates_against_hotel_age_policy(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();
        $checkInDate = Carbon::now()->addYear();

        $agePolicy = HotelAgePolicyFactory::new()->create([
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        // Mock the availability service to return available
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::available([['available' => 10]], 10)
        );

        $service = new BookingValidationService($mockAvailabilityService);

        // Guest classified as adult but too young
        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => $checkInDate,
            'nights' => 7,
            'guests' => [
                ['name' => 'Teen', 'birthdate' => Carbon::now()->addYears(-16)->format('Y-m-d'), 'guest_category' => 'adult'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validateFullBooking($data, $agePolicy);

        $this->assertFalse($result->isValid());
    }

    public function test_it_passes_validation_with_valid_hotel_age_policy(): void
    {
        $stayType = StayTypeFactory::new()->withNights(7)->create();
        $roomType = RoomTypeFactory::new()->create();
        $checkInDate = Carbon::now()->addYear();

        $agePolicy = HotelAgePolicyFactory::new()->create([
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        // Mock the availability service to return available
        $mockAvailabilityService = $this->createMock(AvailabilityService::class);
        $mockAvailabilityService->method('checkAvailability')->willReturn(
            AvailabilityResult::available([['available' => 10]], 10)
        );

        $service = new BookingValidationService($mockAvailabilityService);

        $data = [
            'stay_type' => $stayType,
            'room_type' => $roomType,
            'check_in_date' => $checkInDate,
            'nights' => 7,
            'guests' => [
                ['name' => 'Adult', 'birthdate' => Carbon::now()->addYears(-30)->format('Y-m-d'), 'guest_category' => 'adult'],
                ['name' => 'Child', 'birthdate' => Carbon::now()->addYears(-8)->format('Y-m-d'), 'guest_category' => 'child'],
                ['name' => 'Infant', 'birthdate' => Carbon::now()->addMonths(-6)->format('Y-m-d'), 'guest_category' => 'infant'],
            ],
            'extra_beds' => 0,
            'total_price' => 1000.00,
        ];

        $result = $service->validateFullBooking($data, $agePolicy);

        $this->assertTrue($result->isValid());
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if result has a specific error
     */
    private function hasError(ValidationResult $result, string $field, ?string $message = null): bool
    {
        foreach ($result->getErrors() as $error) {
            if ($error['field'] === $field) {
                if ($message === null || str_contains($error['message'], $message)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Check if result has a specific warning
     */
    private function hasWarning(ValidationResult $result, string $field, ?string $message = null): bool
    {
        foreach ($result->getWarnings() as $warning) {
            if ($warning['field'] === $field) {
                if ($message === null || str_contains($warning['message'], $message)) {
                    return true;
                }
            }
        }
        return false;
    }
}
