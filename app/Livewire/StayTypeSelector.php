<?php

namespace App\Livewire;

use App\Models\StayType;
use Livewire\Component;

class StayTypeSelector extends Component
{
    public $selectedStayTypeId = null;
    public $hotelId = null;
    public $stayTypes = [];
    public $showValidation = false;

    protected $listeners = [
        'filterByHotel' => 'filterByHotel',
    ];

    public function mount($hotelId = null)
    {
        $this->hotelId = $hotelId;
        $this->loadStayTypes();
    }

    public function loadStayTypes()
    {
        $query = StayType::with(['hotel', 'rateRules'])
            ->where('is_active', true);

        if ($this->hotelId) {
            $query->where('hotel_id', $this->hotelId);
        }

        $this->stayTypes = $query->orderBy('nights')
            ->orderBy('name')
            ->get()
            ->map(function ($stayType) {
                return [
                    'id' => $stayType->id,
                    'name' => $stayType->name,
                    'description' => $stayType->description,
                    'nights' => $stayType->nights,
                    'included_board_type' => $stayType->included_board_type,
                    'hotel_name' => $stayType->hotel->name ?? null,
                    'price_hint' => $this->getPriceHint($stayType),
                ];
            })
            ->toArray();
    }

    protected function getPriceHint(StayType $stayType)
    {
        $minPrice = $stayType->rateRules()
            ->where('is_active', true)
            ->min('price_per_night');

        if ($minPrice) {
            return 'From €' . number_format($minPrice, 2) . '/night';
        }

        return 'Contact for pricing';
    }

    public function selectStayType($stayTypeId)
    {
        $this->selectedStayTypeId = $stayTypeId;
        $this->showValidation = false;

        $this->dispatch('stayTypeSelected', stayTypeId: $stayTypeId);
    }

    public function validateSelection()
    {
        if (!$this->selectedStayTypeId) {
            $this->showValidation = true;
            return false;
        }

        return true;
    }

    public function filterByHotel($hotelId)
    {
        $this->hotelId = $hotelId;
        $this->selectedStayTypeId = null;
        $this->showValidation = false;
        $this->loadStayTypes();
    }

    public function getSelectedStayTypeProperty()
    {
        if (!$this->selectedStayTypeId) {
            return null;
        }

        return collect($this->stayTypes)->firstWhere('id', $this->selectedStayTypeId);
    }

    public function render()
    {
        return view('livewire.stay-type-selector');
    }
}
