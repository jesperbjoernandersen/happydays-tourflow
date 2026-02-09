<?php

namespace Tests\Unit\Services;

use App\Services\AgeClassificationService;
use App\Models\HotelAgePolicy;
use App\Domain\ValueObjects\GuestCategory;
use Carbon\Carbon;
use Database\Factories\HotelAgePolicyFactory;
use InvalidArgumentException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgeClassificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private AgeClassificationService $service;
    private HotelAgePolicy $standardPolicy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AgeClassificationService();

        // Standard policy: infant <= 2, child <= 12, adult >= 18
        $this->standardPolicy = HotelAgePolicyFactory::new()->create([
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);
    }

    /** @test */
    public function it_classifies_newborn_as_infant(): void
    {
        $birthdate = Carbon::parse('2023-01-01');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::INFANT, $category->getCategory());
    }

    /** @test */
    public function it_classifies_age_1_as_infant(): void
    {
        $birthdate = Carbon::parse('2023-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::INFANT, $category->getCategory());
    }

    /** @test */
    public function it_classifies_age_2_as_child_because_infant_max_is_exclusive(): void
    {
        // With infant_max_age = 2 (exclusive), age 2 should be CHILD, not INFANT
        $birthdate = Carbon::parse('2022-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_classifies_age_3_as_child(): void
    {
        $birthdate = Carbon::parse('2021-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_classifies_age_12_as_adult_because_child_max_is_exclusive(): void
    {
        // With child_max_age = 12 (exclusive), age 12 should be ADULT, not CHILD
        $birthdate = Carbon::parse('2012-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::ADULT, $category->getCategory());
    }

    /** @test */
    public function it_classifies_age_13_as_adult(): void
    {
        $birthdate = Carbon::parse('2011-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::ADULT, $category->getCategory());
    }

    /** @test */
    public function it_classifies_teenager_as_adult(): void
    {
        $birthdate = Carbon::parse('2006-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::ADULT, $category->getCategory());
    }

    /** @test */
    public function it_accepts_string_dates(): void
    {
        $category = $this->service->classify('2022-06-15', '2024-06-15', $this->standardPolicy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_accepts_carbon_instances(): void
    {
        $birthdate = Carbon::parse('2022-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_accepts_carbon_immutable_instances(): void
    {
        $birthdate = \Carbon\CarbonImmutable::parse('2022-06-15');
        $checkinDate = \Carbon\CarbonImmutable::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_rejects_null_birthdate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('birthdate cannot be null');

        $this->service->classify(null, Carbon::parse('2024-06-15'), $this->standardPolicy);
    }

    /** @test */
    public function it_rejects_null_checkin_date(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('checkin_date cannot be null');

        $this->service->classify(Carbon::parse('2022-06-15'), null, $this->standardPolicy);
    }

    /** @test */
    public function it_rejects_invalid_birthdate(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid birthdate');

        $this->service->classify('not-a-date', Carbon::parse('2024-06-15'), $this->standardPolicy);
    }

    /** @test */
    public function it_rejects_birthdate_in_future_relative_to_checkin(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Birthdate cannot be in the future relative to check-in date');

        $this->service->classify(Carbon::parse('2025-01-01'), Carbon::parse('2024-06-15'), $this->standardPolicy);
    }

    /** @test */
    public function it_rejects_birthdate_same_as_checkin_date_for_adult(): void
    {
        // A baby born on the check-in date is 0 years old
        $birthdate = Carbon::parse('2024-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::INFANT, $category->getCategory());
    }

    /** @test */
    public function it_calculates_age_correctly_before_birthday(): void
    {
        // Born Dec 2020, check-in Jun 2024 = 3 years old
        $birthdate = Carbon::parse('2020-12-01');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_calculates_age_correctly_after_birthday(): void
    {
        // Born Jan 2021, check-in Jun 2024 = 3 years old
        $birthdate = Carbon::parse('2021-01-01');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_works_with_policy_without_infant_max_age(): void
    {
        $policy = HotelAgePolicyFactory::new()->create([
            'infant_max_age' => null,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        // Age 0 without infant classification should be CHILD
        $birthdate = Carbon::parse('2024-01-01');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $policy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_works_with_policy_without_child_max_age_but_with_adult_min_age(): void
    {
        // Policy with only adult_min_age, no child_max_age
        // According to requirements: if child_max_age not set, use adult_min_age as threshold
        $policy = HotelAgePolicyFactory::new()->create([
            'infant_max_age' => 2,
            'child_max_age' => null,
            'adult_min_age' => 18,
        ]);

        // Age 10 should be CHILD (since < adult_min_age of 18)
        $birthdate = Carbon::parse('2014-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $policy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());

        // Age 18 should be ADULT (since >= adult_min_age)
        $birthdate = Carbon::parse('2006-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $policy);

        $this->assertEquals(GuestCategory::ADULT, $category->getCategory());
    }

    /** @test */
    public function it_works_with_strict_infant_policy(): void
    {
        // Infant: 0 <= age < 1 (exclusive)
        $policy = HotelAgePolicyFactory::new()->infantUnderOne()->create([
            'child_max_age' => 16,
            'adult_min_age' => 18,
        ]);

        // Age 0 (born today) = INFANT
        $birthdate = Carbon::parse('2024-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $policy);

        $this->assertEquals(GuestCategory::INFANT, $category->getCategory());

        // Age 1 = CHILD (since infant_max_age = 1 is exclusive)
        $birthdate = Carbon::parse('2023-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $policy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_works_with_high_infant_max_age(): void
    {
        // Infant: 0 <= age < 5
        $policy = HotelAgePolicyFactory::new()->create([
            'infant_max_age' => 5,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        // Age 3 = INFANT
        $birthdate = Carbon::parse('2021-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $policy);

        $this->assertEquals(GuestCategory::INFANT, $category->getCategory());

        // Age 5 = CHILD (since infant_max_age = 5 is exclusive)
        $birthdate = Carbon::parse('2019-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $policy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_returns_guest_category_value_object(): void
    {
        $category = $this->service->classify(
            Carbon::parse('2020-06-15'),
            Carbon::parse('2024-06-15'),
            $this->standardPolicy
        );

        $this->assertInstanceOf(GuestCategory::class, $category);
        $this->assertEquals('CHILD', $category->getCategory());
    }

    /** @test */
    public function it_can_check_category_type(): void
    {
        $infant = $this->service->classify(
            Carbon::parse('2024-01-01'),
            Carbon::parse('2024-06-15'),
            $this->standardPolicy
        );
        $this->assertTrue($infant->isInfant());
        $this->assertFalse($infant->isChild());
        $this->assertFalse($infant->isAdult());

        $child = $this->service->classify(
            Carbon::parse('2015-06-15'),
            Carbon::parse('2024-06-15'),
            $this->standardPolicy
        );
        $this->assertFalse($child->isInfant());
        $this->assertTrue($child->isChild());
        $this->assertFalse($child->isAdult());

        $adult = $this->service->classify(
            Carbon::parse('1990-06-15'),
            Carbon::parse('2024-06-15'),
            $this->standardPolicy
        );
        $this->assertFalse($adult->isInfant());
        $this->assertFalse($adult->isChild());
        $this->assertTrue($adult->isAdult());
    }

    /** @test */
    public function it_implements_to_string(): void
    {
        $category = $this->service->classify(
            Carbon::parse('1990-06-15'),
            Carbon::parse('2024-06-15'),
            $this->standardPolicy
        );

        $this->assertEquals('ADULT', (string) $category);
    }

    /** @test */
    public function it_handles_very_old_person(): void
    {
        // Born 100 years ago
        $birthdate = Carbon::parse('1924-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::ADULT, $category->getCategory());
    }

    /** @test */
    public function it_rejects_birthdate_too_far_in_past(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Birthdate is too far in the past');

        // Born 160 years ago
        $birthdate = Carbon::parse('1864-06-15');
        $checkinDate = Carbon::parse('2024-06-15');

        $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);
    }

    /** @test */
    public function it_calculates_age_at_checkin_date_not_booking_date(): void
    {
        // Scenario: Booking made in 2023, but check-in is in 2024
        // Age should be calculated at check-in date (2024-06-15), not booking date

        $birthdate = Carbon::parse('2020-06-15');
        $bookingDate = Carbon::parse('2023-12-01'); // Would be age 3 here
        $checkinDate = Carbon::parse('2024-06-15'); // Age is 4 here

        // Verify our dates: on booking date, age would be 3
        $ageAtBooking = $bookingDate->year - $birthdate->year;
        if ($bookingDate->month < $birthdate->month || 
            ($bookingDate->month === $birthdate->month && $bookingDate->day < $birthdate->day)) {
            $ageAtBooking--;
        }
        $this->assertEquals(3, $ageAtBooking);

        // On check-in date, age is 4
        $ageAtCheckin = $checkinDate->year - $birthdate->year;
        if ($checkinDate->month < $birthdate->month || 
            ($checkinDate->month === $birthdate->month && $checkinDate->day < $birthdate->day)) {
            $ageAtCheckin--;
        }
        $this->assertEquals(4, $ageAtCheckin);

        // With standard policy (infant < 2, child < 12), age 4 should be CHILD
        $category = $this->service->classify($birthdate, $checkinDate, $this->standardPolicy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_handles_edge_case_where_age_is_exactly_infant_max_age(): void
    {
        // infant_max_age = 2 (exclusive), so age 2 should be CHILD
        $policy = HotelAgePolicyFactory::new()->create([
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        $birthdate = Carbon::parse('2022-06-15');
        $checkinDate = Carbon::parse('2024-06-15');
        $this->assertEquals(2, $birthdate->diffInYears($checkinDate));

        $category = $this->service->classify($birthdate, $checkinDate, $policy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_handles_edge_case_where_age_is_exactly_child_max_age(): void
    {
        // child_max_age = 12 (exclusive), so age 12 should be ADULT
        $policy = HotelAgePolicyFactory::new()->create([
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        $birthdate = Carbon::parse('2012-06-15');
        $checkinDate = Carbon::parse('2024-06-15');
        $this->assertEquals(12, $birthdate->diffInYears($checkinDate));

        $category = $this->service->classify($birthdate, $checkinDate, $policy);

        $this->assertEquals(GuestCategory::ADULT, $category->getCategory());
    }
}
