<?php

namespace Tests\Unit\ValueObjects;

use App\Domain\ValueObjects\GuestCategory;
use App\Models\HotelAgePolicy;
use Database\Factories\HotelAgePolicyFactory;
use InvalidArgumentException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GuestCategoryTest extends TestCase
{
    use RefreshDatabase;

    private HotelAgePolicy $agePolicy;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a standard age policy for testing
        $this->agePolicy = HotelAgePolicyFactory::new()->create([
            'infant_max_age' => 2,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);
    }

    /** @test */
    public function it_can_be_created_with_infant_constant(): void
    {
        $category = new GuestCategory(GuestCategory::INFANT);

        $this->assertEquals(GuestCategory::INFANT, $category->getCategory());
    }

    /** @test */
    public function it_can_be_created_with_child_constant(): void
    {
        $category = new GuestCategory(GuestCategory::CHILD);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_can_be_created_with_adult_constant(): void
    {
        $category = new GuestCategory(GuestCategory::ADULT);

        $this->assertEquals(GuestCategory::ADULT, $category->getCategory());
    }

    /** @test */
    public function it_throws_exception_for_invalid_category(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid guest category: INVALID');

        new GuestCategory('INVALID');
    }

    /** @test */
    public function it_returns_infant_for_age_under_infant_max(): void
    {
        $category = GuestCategory::fromAge(0, $this->agePolicy);
        $category2 = GuestCategory::fromAge(1, $this->agePolicy);
        $category3 = GuestCategory::fromAge(2, $this->agePolicy);

        $this->assertEquals(GuestCategory::INFANT, $category->getCategory());
        $this->assertEquals(GuestCategory::INFANT, $category2->getCategory());
        $this->assertEquals(GuestCategory::INFANT, $category3->getCategory());
    }

    /** @test */
    public function it_returns_child_for_age_between_infant_and_child_max(): void
    {
        $category = GuestCategory::fromAge(3, $this->agePolicy);
        $category2 = GuestCategory::fromAge(11, $this->agePolicy);
        $category3 = GuestCategory::fromAge(12, $this->agePolicy);

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
        $this->assertEquals(GuestCategory::CHILD, $category2->getCategory());
        $this->assertEquals(GuestCategory::CHILD, $category3->getCategory());
    }

    /** @test */
    public function it_returns_adult_for_age_above_child_max(): void
    {
        $category = GuestCategory::fromAge(13, $this->agePolicy);
        $category2 = GuestCategory::fromAge(17, $this->agePolicy);
        $category3 = GuestCategory::fromAge(18, $this->agePolicy);
        $category4 = GuestCategory::fromAge(100, $this->agePolicy);

        $this->assertEquals(GuestCategory::ADULT, $category->getCategory());
        $this->assertEquals(GuestCategory::ADULT, $category2->getCategory());
        $this->assertEquals(GuestCategory::ADULT, $category3->getCategory());
        $this->assertEquals(GuestCategory::ADULT, $category4->getCategory());
    }

    /** @test */
    public function it_throws_exception_for_negative_age(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Age cannot be negative');

        GuestCategory::fromAge(-1, $this->agePolicy);
    }

    /** @test */
    public function it_creates_infant_statically(): void
    {
        $category = GuestCategory::infant();

        $this->assertEquals(GuestCategory::INFANT, $category->getCategory());
    }

    /** @test */
    public function it_creates_child_statically(): void
    {
        $category = GuestCategory::child();

        $this->assertEquals(GuestCategory::CHILD, $category->getCategory());
    }

    /** @test */
    public function it_creates_adult_statically(): void
    {
        $category = GuestCategory::adult();

        $this->assertEquals(GuestCategory::ADULT, $category->getCategory());
    }

    /** @test */
    public function it_checks_if_infant(): void
    {
        $infant = GuestCategory::infant();
        $child = GuestCategory::child();
        $adult = GuestCategory::adult();

        $this->assertTrue($infant->isInfant());
        $this->assertFalse($child->isInfant());
        $this->assertFalse($adult->isInfant());
    }

    /** @test */
    public function it_checks_if_child(): void
    {
        $infant = GuestCategory::infant();
        $child = GuestCategory::child();
        $adult = GuestCategory::adult();

        $this->assertFalse($infant->isChild());
        $this->assertTrue($child->isChild());
        $this->assertFalse($adult->isChild());
    }

    /** @test */
    public function it_checks_if_adult(): void
    {
        $infant = GuestCategory::infant();
        $child = GuestCategory::child();
        $adult = GuestCategory::adult();

        $this->assertFalse($infant->isAdult());
        $this->assertFalse($child->isAdult());
        $this->assertTrue($adult->isAdult());
    }

    /** @test */
    public function it_implements_to_string(): void
    {
        $category = GuestCategory::adult();

        $this->assertEquals('ADULT', (string) $category);
    }

    /** @test */
    public function it_works_with_different_age_policies(): void
    {
        // Policy with infant up to 1 year
        $strictPolicy = HotelAgePolicyFactory::new()->create([
            'infant_max_age' => 1,
            'child_max_age' => 16,
            'adult_min_age' => 18,
        ]);

        $infant = GuestCategory::fromAge(1, $strictPolicy);
        $this->assertEquals(GuestCategory::INFANT, $infant->getCategory());

        $child = GuestCategory::fromAge(2, $strictPolicy);
        $this->assertEquals(GuestCategory::CHILD, $child->getCategory());

        // Policy where infant age is high (almost all children become infants until they exceed)
        $highInfantPolicy = HotelAgePolicyFactory::new()->create([
            'infant_max_age' => 5,
            'child_max_age' => 12,
            'adult_min_age' => 18,
        ]);

        $baby = GuestCategory::fromAge(3, $highInfantPolicy);
        $this->assertEquals(GuestCategory::INFANT, $baby->getCategory());

        $childAge = GuestCategory::fromAge(6, $highInfantPolicy);
        $this->assertEquals(GuestCategory::CHILD, $childAge->getCategory());
    }

    /** @test */
    public function it_compares_by_value(): void
    {
        $category1 = GuestCategory::adult();
        $category2 = GuestCategory::adult();
        $category3 = GuestCategory::child();

        // Same category
        $this->assertEquals($category1->getCategory(), $category2->getCategory());

        // Different category
        $this->assertNotEquals($category1->getCategory(), $category3->getCategory());
    }
}
