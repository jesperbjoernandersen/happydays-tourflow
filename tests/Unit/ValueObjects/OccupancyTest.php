<?php

namespace Tests\Unit\ValueObjects;

use App\Domain\ValueObjects\Occupancy;
use InvalidArgumentException;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OccupancyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_be_created_with_valid_values(): void
    {
        $occupancy = new Occupancy(2, 1, 0, 1);

        $this->assertEquals(2, $occupancy->getAdults());
        $this->assertEquals(1, $occupancy->getChildren());
        $this->assertEquals(0, $occupancy->getInfants());
        $this->assertEquals(1, $occupancy->getExtraBeds());
    }

    /** @test */
    public function it_has_default_values(): void
    {
        $occupancy = new Occupancy();

        $this->assertEquals(1, $occupancy->getAdults());
        $this->assertEquals(0, $occupancy->getChildren());
        $this->assertEquals(0, $occupancy->getInfants());
        $this->assertEquals(0, $occupancy->getExtraBeds());
    }

    /** @test */
    public function it_calculates_total_people(): void
    {
        $occupancy = new Occupancy(2, 3, 1, 0);

        $this->assertEquals(5, $occupancy->total()); // adults + children
    }

    /** @test */
    public function it_calculates_sleeps_count(): void
    {
        $occupancy = new Occupancy(2, 3, 1, 1);

        $this->assertEquals(6, $occupancy->sleeps()); // adults + children + extra beds
    }

    /** @test */
    public function it_validates_at_least_one_adult(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('At least one adult is required');

        new Occupancy(0, 1, 0, 0);
    }

    /** @test */
    public function it_validates_negative_adults(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Adults cannot be negative');

        new Occupancy(-1, 0, 0, 0);
    }

    /** @test */
    public function it_validates_negative_children(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Children cannot be negative');

        new Occupancy(1, -1, 0, 0);
    }

    /** @test */
    public function it_validates_negative_infants(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Infants cannot be negative');

        new Occupancy(1, 0, -1, 0);
    }

    /** @test */
    public function it_validates_negative_extra_beds(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Extra beds cannot be negative');

        new Occupancy(1, 0, 0, -1);
    }

    /** @test */
    public function it_adds_an_adult(): void
    {
        $original = new Occupancy(1, 0, 0, 0);
        $result = $original->addAdult();

        $this->assertEquals(1, $original->getAdults()); // Original unchanged
        $this->assertEquals(2, $result->getAdults());
    }

    /** @test */
    public function it_adds_a_child(): void
    {
        $original = new Occupancy(1, 0, 0, 0);
        $result = $original->addChild();

        $this->assertEquals(0, $original->getChildren()); // Original unchanged
        $this->assertEquals(1, $result->getChildren());
    }

    /** @test */
    public function it_adds_an_infant(): void
    {
        $original = new Occupancy(1, 0, 0, 0);
        $result = $original->addInfant();

        $this->assertEquals(0, $original->getInfants()); // Original unchanged
        $this->assertEquals(1, $result->getInfants());
    }

    /** @test */
    public function it_adds_an_extra_bed(): void
    {
        $original = new Occupancy(1, 0, 0, 0);
        $result = $original->addExtraBed();

        $this->assertEquals(0, $original->getExtraBeds()); // Original unchanged
        $this->assertEquals(1, $result->getExtraBeds());
    }

    /** @test */
    public function it_checks_if_valid(): void
    {
        $validOccupancy = new Occupancy(1, 0, 0, 0);

        $this->assertTrue($validOccupancy->isValid());
    }

    /** @test */
    public function it_implements_to_string(): void
    {
        $occupancy = new Occupancy(2, 1, 1, 1);

        $this->assertEquals('2 adults, 1 child, 1 infant, 1 extra bed', (string) $occupancy);
    }

    /** @test */
    public function it_formats_correctly_for_singular_values(): void
    {
        $occupancy = new Occupancy(1, 1, 1, 1);

        $this->assertEquals('1 adult, 1 child, 1 infant, 1 extra bed', (string) $occupancy);
    }

    /** @test */
    public function it_formats_correctly_with_only_adults(): void
    {
        $occupancy = new Occupancy(3, 0, 0, 0);

        $this->assertEquals('3 adults', (string) $occupancy);
    }

    /** @test */
    public function it_is_immutable(): void
    {
        $original = new Occupancy(2, 1, 0, 0);

        $result = $original->addAdult()->addChild()->addInfant()->addExtraBed();

        // Original should be unchanged
        $this->assertEquals(2, $original->getAdults());
        $this->assertEquals(1, $original->getChildren());
        $this->assertEquals(0, $original->getInfants());
        $this->assertEquals(0, $original->getExtraBeds());

        // Result should have the new values
        $this->assertEquals(3, $result->getAdults());
        $this->assertEquals(2, $result->getChildren());
        $this->assertEquals(1, $result->getInfants());
        $this->assertEquals(1, $result->getExtraBeds());
    }

    /** @test */
    public function it_compares_by_value(): void
    {
        $occupancy1 = new Occupancy(2, 1, 0, 0);
        $occupancy2 = new Occupancy(2, 1, 0, 0);
        $occupancy3 = new Occupancy(2, 2, 0, 0);

        // Same values
        $this->assertEquals($occupancy1->getAdults(), $occupancy2->getAdults());
        $this->assertEquals($occupancy1->getChildren(), $occupancy2->getChildren());

        // Different values
        $this->assertNotEquals($occupancy1->getChildren(), $occupancy3->getChildren());
    }
}
