<?php

namespace App\Livewire;

use App\Domain\ValueObjects\PriceBreakdown;
use App\Models\RoomType;
use App\Models\StayType;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * BookingConfirmation Livewire Component
 *
 * Final confirmation screen before a booking is submitted.
 * Displays booking summary, guest list, pricing breakdown,
 * and requires terms acceptance before confirming.
 */
class BookingConfirmation extends Component
{
    /**
     * The stay type ID for the booking.
     */
    public ?int $stayTypeId = null;

    /**
     * The room type ID for the booking.
     */
    public ?int $roomTypeId = null;

    /**
     * Check-in date string (Y-m-d).
     */
    public string $checkinDate = '';

    /**
     * Check-out date string (Y-m-d).
     */
    public string $checkoutDate = '';

    /**
     * Number of adults.
     */
    public int $adults = 1;

    /**
     * Number of children.
     */
    public int $children = 0;

    /**
     * Number of infants.
     */
    public int $infants = 0;

    /**
     * Guest data array from the guest information step.
     * Each entry: ['name' => '', 'birthdate' => '', 'age' => int, 'category' => string]
     */
    public array $guests = [];

    /**
     * Price breakdown data as array (for Livewire serialization).
     */
    public ?array $priceBreakdownData = null;

    /**
     * Whether terms and conditions have been accepted.
     */
    public bool $termsAccepted = false;

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [
            'termsAccepted' => ['accepted'],
            'stayTypeId' => ['required', 'integer'],
            'roomTypeId' => ['required', 'integer'],
            'checkinDate' => ['required', 'date'],
            'checkoutDate' => ['required', 'date', 'after:checkinDate'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['required', 'integer', 'min:0'],
            'infants' => ['required', 'integer', 'min:0'],
            'guests' => ['required', 'array', 'min:1'],
            'priceBreakdownData' => ['required', 'array'],
        ];
    }

    /**
     * Custom validation messages.
     */
    protected function messages(): array
    {
        return [
            'termsAccepted.accepted' => 'You must accept the terms and conditions before confirming.',
        ];
    }

    /**
     * Mount the component with booking data.
     */
    public function mount(
        ?int $stayTypeId = null,
        ?int $roomTypeId = null,
        string $checkinDate = '',
        string $checkoutDate = '',
        int $adults = 1,
        int $children = 0,
        int $infants = 0,
        array $guests = [],
        ?PriceBreakdown $priceBreakdown = null,
        ?array $priceBreakdownData = null,
    ): void {
        $this->stayTypeId = $stayTypeId;
        $this->roomTypeId = $roomTypeId;
        $this->checkinDate = $checkinDate;
        $this->checkoutDate = $checkoutDate;
        $this->adults = max(1, $adults);
        $this->children = max(0, $children);
        $this->infants = max(0, $infants);
        $this->guests = $guests;

        if ($priceBreakdown) {
            $this->priceBreakdownData = $priceBreakdown->toArray();
        } elseif ($priceBreakdownData) {
            $this->priceBreakdownData = $priceBreakdownData;
        }
    }

    /**
     * Get the StayType model.
     */
    #[Computed]
    public function stayType(): ?StayType
    {
        if (!$this->stayTypeId) {
            return null;
        }

        return StayType::find($this->stayTypeId);
    }

    /**
     * Get the RoomType model.
     */
    #[Computed]
    public function roomType(): ?RoomType
    {
        if (!$this->roomTypeId) {
            return null;
        }

        return RoomType::find($this->roomTypeId);
    }

    /**
     * Get the PriceBreakdown value object from stored data.
     */
    #[Computed]
    public function priceBreakdown(): ?PriceBreakdown
    {
        if (!$this->priceBreakdownData) {
            return null;
        }

        return PriceBreakdown::fromArray($this->priceBreakdownData);
    }

    /**
     * Get the total guest count.
     */
    #[Computed]
    public function totalGuests(): int
    {
        return $this->adults + $this->children + $this->infants;
    }

    /**
     * Get the stay duration in nights.
     */
    #[Computed]
    public function nights(): int
    {
        if (empty($this->checkinDate) || empty($this->checkoutDate)) {
            return 0;
        }

        try {
            $checkin = Carbon::parse($this->checkinDate);
            $checkout = Carbon::parse($this->checkoutDate);

            return max(0, $checkin->diffInDays($checkout));
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Format a date for display.
     */
    public function formatDate(string $date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            return Carbon::parse($date)->format('D, d M Y');
        } catch (\Exception $e) {
            return $date;
        }
    }

    /**
     * Get guest category badge colour class.
     */
    public function getCategoryBadgeClass(string $category): string
    {
        return match (strtoupper($category)) {
            'ADULT' => 'bg-blue-100 text-blue-800',
            'CHILD' => 'bg-green-100 text-green-800',
            'INFANT' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Confirm the booking after validating terms acceptance.
     */
    public function confirmBooking(): void
    {
        $this->validate();

        $bookingData = [
            'stay_type_id' => $this->stayTypeId,
            'room_type_id' => $this->roomTypeId,
            'checkin_date' => $this->checkinDate,
            'checkout_date' => $this->checkoutDate,
            'adults' => $this->adults,
            'children' => $this->children,
            'infants' => $this->infants,
            'guests' => $this->guests,
            'price_breakdown' => $this->priceBreakdownData,
            'terms_accepted' => true,
        ];

        $this->dispatch('bookingConfirmed', $bookingData);
    }

    /**
     * Go back to the editing step.
     */
    public function backToEdit(): void
    {
        $this->dispatch('backToEdit');
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.booking-confirmation');
    }
}
