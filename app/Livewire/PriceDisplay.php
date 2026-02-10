<?php

namespace App\Livewire;

use App\Domain\ValueObjects\PriceBreakdown;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * PriceDisplay Livewire Component
 *
 * Displays the calculated price breakdown for a booking.
 * Shows base price, supplements, and total with per-night breakdown.
 */
class PriceDisplay extends Component
{
    /**
     * The price breakdown data as array (for Livewire serialization).
     */
    public ?array $priceBreakdownData = null;

    /**
     * The actual PriceBreakdown object (computed, not serialized).
     */
    #[Computed]
    public ?PriceBreakdown $priceBreakdown = null;

    /**
     * Whether to show per-night breakdown.
     */
    public bool $showPerNight = false;

    /**
     * Whether to show detailed breakdown.
     */
    public bool $showDetails = true;

    /**
     * Whether this is a summary display (compact mode).
     */
    public bool $isSummary = false;

    /**
     * Mount the component with optional price breakdown.
     */
    public function mount(?PriceBreakdown $priceBreakdown = null, bool $showPerNight = false, bool $showDetails = true, bool $isSummary = false): void
    {
        $this->priceBreakdownData = $priceBreakdown?->toArray() ?? null;
        $this->priceBreakdown = $priceBreakdown;
        $this->showPerNight = $showPerNight;
        $this->showDetails = $showDetails;
        $this->isSummary = $isSummary;
    }

    /**
     * Set the price breakdown programmatically.
     */
    public function setPriceBreakdown(PriceBreakdown $breakdown): void
    {
        $this->priceBreakdownData = $breakdown->toArray();
        $this->priceBreakdown = $breakdown;
        $this->dispatch('priceUpdated', $breakdown->toArray());
    }

    /**
     * Hydrate priceBreakdown from priceBreakdownData (called after Livewire refresh).
     */
    public function hydrate(): void
    {
        if ($this->priceBreakdownData && !$this->priceBreakdown) {
            $this->priceBreakdown = PriceBreakdown::fromArray($this->priceBreakdownData);
        }
    }

    /**
     * Toggle per-night display.
     */
    public function togglePerNight(): void
    {
        $this->showPerNight = !$this->showPerNight;
    }

    /**
     * Toggle detailed view.
     */
    public function toggleDetails(): void
    {
        $this->showDetails = !$this->showDetails;
    }

    /**
     * Check if there are any supplements to display.
     */
    public function hasSupplements(): bool
    {
        if (!$this->priceBreakdown) {
            return false;
        }

        return $this->priceBreakdown->getAdultSupplement() > 0
            || $this->priceBreakdown->getChildSupplement() > 0
            || $this->priceBreakdown->getInfantSupplement() > 0
            || $this->priceBreakdown->getExtraBedSupplement() > 0
            || $this->priceBreakdown->getSingleUseSupplement() > 0
            || $this->priceBreakdown->getExtraOccupancyCharge() > 0;
    }

    /**
     * Get the pricing model display label.
     */
    public function getPricingModelLabel(): string
    {
        if (!$this->priceBreakdown) {
            return '';
        }

        if ($this->priceBreakdown->isOccupancyBased()) {
            return 'Per Person Pricing';
        }

        return 'Fixed Price (up to ' . $this->priceBreakdown->getBaseOccupancy() . ' guests)';
    }

    /**
     * Get the pricing model description.
     */
    public function getPricingModelDescription(): string
    {
        if (!$this->priceBreakdown) {
            return '';
        }

        if ($this->priceBreakdown->isOccupancyBased()) {
            return 'Price is calculated per person based on the number of guests.';
        }

        return 'The base price includes up to ' . $this->priceBreakdown->getBaseOccupancy() . ' guests. Additional guests incur a supplement.';
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.price-display');
    }
}
