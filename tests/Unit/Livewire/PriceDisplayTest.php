<?php

namespace Tests\Unit\Livewire;

use App\Livewire\PriceDisplay;
use App\Domain\ValueObjects\PriceBreakdown;
use PHPUnit\Framework\TestCase;

/**
 * PriceDisplay Component Unit Test
 *
 * Tests the PriceDisplay Livewire component logic without view rendering.
 */
class PriceDisplayTest extends TestCase
{
    /** @test */
    public function it_creates_instance_with_defaults(): void
    {
        $component = new PriceDisplay();
        $component->mount();

        $this->assertNull($component->priceBreakdown);
        $this->assertFalse($component->showPerNight);
        $this->assertTrue($component->showDetails);
        $this->assertFalse($component->isSummary);
    }

    /** @test */
    public function it_mounts_with_price_breakdown(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            nights: 2,
            adults: 2,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertEquals($breakdown, $component->priceBreakdown);
        $this->assertFalse($component->showPerNight);
        $this->assertTrue($component->showDetails);
        $this->assertFalse($component->isSummary);
    }

    /** @test */
    public function it_mounts_with_show_per_night(): void
    {
        $component = new PriceDisplay();
        $component->mount(null, true);

        $this->assertTrue($component->showPerNight);
    }

    /** @test */
    public function it_mounts_with_custom_options(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            nights: 3,
            adults: 2,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown, true, false, true);

        $this->assertEquals($breakdown, $component->priceBreakdown);
        $this->assertTrue($component->showPerNight);
        $this->assertFalse($component->showDetails);
        $this->assertTrue($component->isSummary);
    }

    /** @test */
    public function it_set_price_breakdown(): void
    {
        $component = new PriceDisplay();
        $component->mount();

        $breakdown = new PriceBreakdown(
            basePrice: 300,
            nights: 5,
            adults: 2,
            baseOccupancy: 2
        );

        $component->setPriceBreakdown($breakdown);

        $this->assertEquals($breakdown, $component->priceBreakdown);
        $this->assertEquals($breakdown->toArray(), $component->priceBreakdownData);
    }

    /** @test */
    public function it_toggle_per_night(): void
    {
        $component = new PriceDisplay();
        $component->mount();

        $this->assertFalse($component->showPerNight);

        $component->togglePerNight();
        $this->assertTrue($component->showPerNight);

        $component->togglePerNight();
        $this->assertFalse($component->showPerNight);
    }

    /** @test */
    public function it_toggle_details(): void
    {
        $component = new PriceDisplay();
        $component->mount();

        $this->assertTrue($component->showDetails);

        $component->toggleDetails();
        $this->assertFalse($component->showDetails);

        $component->toggleDetails();
        $this->assertTrue($component->showDetails);
    }

    /** @test */
    public function it_has_supplements_false_without_supplements(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            nights: 2,
            adults: 2,
            children: 0,
            baseOccupancy: 2,
            adultSupplement: 0,
            childSupplement: 0
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertFalse($component->hasSupplements());
    }

    /** @test */
    public function it_has_supplements_true_with_adult_supplement(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            adultSupplement: 25,
            nights: 2,
            adults: 3,
            children: 0,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertTrue($component->hasSupplements());
    }

    /** @test */
    public function it_has_supplements_true_with_extra_bed_supplement(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            extraBedSupplement: 30,
            nights: 2,
            adults: 2,
            children: 0,
            baseOccupancy: 2,
            extraBeds: 1
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertTrue($component->hasSupplements());
    }

    /** @test */
    public function it_has_supplements_true_with_single_use_supplement(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            singleUseSupplement: 50,
            nights: 2,
            adults: 1,
            children: 0,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertTrue($component->hasSupplements());
    }

    /** @test */
    public function it_has_supplements_true_with_child_supplement(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            childSupplement: 15,
            nights: 2,
            adults: 2,
            children: 1,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertTrue($component->hasSupplements());
    }

    /** @test */
    public function it_has_supplements_true_with_infant_supplement(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            infantSupplement: 10,
            nights: 2,
            adults: 2,
            infants: 1,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertTrue($component->hasSupplements());
    }

    /** @test */
    public function it_has_supplements_true_with_extra_occupancy_charge(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            extraOccupancyCharge: 20,
            nights: 2,
            adults: 3,
            children: 0,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertTrue($component->hasSupplements());
    }

    /** @test */
    public function it_has_supplements_false_when_null_breakdown(): void
    {
        $component = new PriceDisplay();
        $component->mount();

        $this->assertFalse($component->hasSupplements());
    }

    /** @test */
    public function it_pricing_model_label_occupancy_based(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            pricingModel: PriceBreakdown::MODEL_OCCUPANCY_BASED,
            nights: 2,
            adults: 2,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertEquals('Per Person Pricing', $component->getPricingModelLabel());
    }

    /** @test */
    public function it_pricing_model_label_unit_included_occupancy(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            pricingModel: PriceBreakdown::MODEL_UNIT_INCLUDED_OCCUPANCY,
            nights: 2,
            adults: 2,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertEquals('Fixed Price (up to 2 guests)', $component->getPricingModelLabel());
    }

    /** @test */
    public function it_pricing_model_label_with_custom_base_occupancy(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            pricingModel: PriceBreakdown::MODEL_UNIT_INCLUDED_OCCUPANCY,
            nights: 2,
            adults: 2,
            baseOccupancy: 4
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertEquals('Fixed Price (up to 4 guests)', $component->getPricingModelLabel());
    }

    /** @test */
    public function it_pricing_model_label_empty_when_null(): void
    {
        $component = new PriceDisplay();
        $component->mount();

        $this->assertEquals('', $component->getPricingModelLabel());
    }

    /** @test */
    public function it_pricing_model_description_occupancy_based(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            pricingModel: PriceBreakdown::MODEL_OCCUPANCY_BASED,
            nights: 2,
            adults: 2,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertEquals(
            'Price is calculated per person based on the number of guests.',
            $component->getPricingModelDescription()
        );
    }

    /** @test */
    public function it_pricing_model_description_unit_included_occupancy(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            pricingModel: PriceBreakdown::MODEL_UNIT_INCLUDED_OCCUPANCY,
            nights: 2,
            adults: 2,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);

        $this->assertEquals(
            'The base price includes up to 2 guests. Additional guests incur a supplement.',
            $component->getPricingModelDescription()
        );
    }

    /** @test */
    public function it_pricing_model_description_empty_when_null(): void
    {
        $component = new PriceDisplay();
        $component->mount();

        $this->assertEquals('', $component->getPricingModelDescription());
    }

    /** @test */
    public function it_hydrate_recreates_breakdown_from_data(): void
    {
        $breakdown = new PriceBreakdown(
            basePrice: 200,
            adultSupplement: 25,
            nights: 2,
            adults: 3,
            baseOccupancy: 2
        );

        $component = new PriceDisplay();
        $component->mount($breakdown);
        $component->priceBreakdownData = $breakdown->toArray();
        $component->priceBreakdown = null;

        $component->hydrate();

        $this->assertNotNull($component->priceBreakdown);
        // Verify the data is correctly restored
        $this->assertEquals(200, $component->priceBreakdown->getBasePrice());
        $this->assertEquals(2, $component->priceBreakdown->getNights());
        $this->assertEquals(3, $component->priceBreakdown->getAdults());
        $this->assertEquals(2, $component->priceBreakdown->getBaseOccupancy());
    }
}
