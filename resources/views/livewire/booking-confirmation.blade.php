@php
    /** @var \App\Livewire\BookingConfirmation $this */
    $stayType = $this->stayType;
    $roomType = $this->roomType;
    $priceBreakdown = $this->priceBreakdown;
    $nights = $this->nights;
    $totalGuests = $this->totalGuests;
@endphp

<div class="booking-confirmation max-w-3xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">{{ __('Confirm Your Booking') }}</h2>
        <p class="mt-1 text-sm text-gray-600">{{ __('Please review your booking details before confirming.') }}</p>
    </div>

    {{-- Booking Summary --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {{ __('Booking Summary') }}
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Stay Type --}}
            @if ($stayType)
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Stay Type') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        {{ $stayType->name }}
                        @if ($stayType->nights)
                            <span class="text-gray-500">({{ $stayType->nights }} {{ __('nights') }})</span>
                        @endif
                    </dd>
                </div>
            @endif

            {{-- Room Type --}}
            @if ($roomType)
                <div>
                    <dt class="text-sm font-medium text-gray-500">{{ __('Room Type') }}</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $roomType->name }}</dd>
                </div>
            @endif

            {{-- Check-in --}}
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('Check-in') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $this->formatDate($checkinDate) }}</dd>
            </div>

            {{-- Check-out --}}
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('Check-out') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $this->formatDate($checkoutDate) }}</dd>
            </div>

            {{-- Duration --}}
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('Duration') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $nights }} {{ trans_choice('night|nights', $nights) }}</dd>
            </div>

            {{-- Guests --}}
            <div>
                <dt class="text-sm font-medium text-gray-500">{{ __('Guests') }}</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $adults }} {{ trans_choice('adult|adults', $adults) }}
                    @if ($children > 0)
                        , {{ $children }} {{ trans_choice('child|children', $children) }}
                    @endif
                    @if ($infants > 0)
                        , {{ $infants }} {{ trans_choice('infant|infants', $infants) }}
                    @endif
                    <span class="text-gray-500">({{ $totalGuests }} {{ __('total') }})</span>
                </dd>
            </div>
        </div>
    </div>

    {{-- Guest List --}}
    @if (count($guests) > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                {{ __('Guest List') }}
            </h3>

            <div class="overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Age') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Category') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($guests as $guest)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $guest['name'] ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    @if (isset($guest['age']) && $guest['age'] !== null)
                                        {{ $guest['age'] }} {{ __('years') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if (isset($guest['category']) && $guest['category'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getCategoryBadgeClass($guest['category']) }}">
                                            {{ ucfirst(strtolower($guest['category'])) }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Pricing Breakdown --}}
    @if ($priceBreakdown)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                {{ __('Pricing Breakdown') }}
            </h3>

            <div class="space-y-2">
                {{-- Base Price --}}
                <div class="flex justify-between items-center py-2">
                    <span class="text-sm text-gray-600">
                        {{ __('Room rate') }}
                        @if ($priceBreakdown->getNights() > 1)
                            <span class="text-xs text-gray-500">
                                ({{ $priceBreakdown->getNights() }} × {{ $priceBreakdown->formatPerNightBasePrice() }})
                            </span>
                        @endif
                    </span>
                    <span class="text-sm font-medium text-gray-900">{{ $priceBreakdown->formatBasePrice() }}</span>
                </div>

                {{-- Adult Supplement --}}
                @if ($priceBreakdown->getAdultSupplement() > 0)
                    <div class="flex justify-between items-center py-2 border-t border-gray-100">
                        <span class="text-sm text-gray-600">{{ __('Adult supplement') }}</span>
                        <span class="text-sm font-medium text-orange-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getAdultSupplement()) }}
                        </span>
                    </div>
                @endif

                {{-- Child Supplement --}}
                @if ($priceBreakdown->getChildSupplement() > 0)
                    <div class="flex justify-between items-center py-2 border-t border-gray-100">
                        <span class="text-sm text-gray-600">{{ __('Child supplement') }}</span>
                        <span class="text-sm font-medium text-orange-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getChildSupplement()) }}
                        </span>
                    </div>
                @endif

                {{-- Infant Supplement --}}
                @if ($priceBreakdown->getInfantSupplement() > 0)
                    <div class="flex justify-between items-center py-2 border-t border-gray-100">
                        <span class="text-sm text-gray-600">{{ __('Infant supplement') }}</span>
                        <span class="text-sm font-medium text-orange-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getInfantSupplement()) }}
                        </span>
                    </div>
                @endif

                {{-- Extra Bed Supplement --}}
                @if ($priceBreakdown->getExtraBedSupplement() > 0)
                    <div class="flex justify-between items-center py-2 border-t border-gray-100">
                        <span class="text-sm text-gray-600">{{ __('Extra beds') }}</span>
                        <span class="text-sm font-medium text-orange-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getExtraBedSupplement()) }}
                        </span>
                    </div>
                @endif

                {{-- Single Use Supplement --}}
                @if ($priceBreakdown->getSingleUseSupplement() > 0)
                    <div class="flex justify-between items-center py-2 border-t border-gray-100">
                        <span class="text-sm text-gray-600">{{ __('Single use supplement') }}</span>
                        <span class="text-sm font-medium text-red-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getSingleUseSupplement()) }}
                        </span>
                    </div>
                @endif

                {{-- Extra Occupancy --}}
                @if ($priceBreakdown->getExtraOccupancyCharge() > 0)
                    <div class="flex justify-between items-center py-2 border-t border-gray-100">
                        <span class="text-sm text-gray-600">{{ __('Extra occupancy charge') }}</span>
                        <span class="text-sm font-medium text-orange-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getExtraOccupancyCharge()) }}
                        </span>
                    </div>
                @endif

                {{-- Total --}}
                <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 mt-2">
                    <span class="text-base font-semibold text-gray-900">{{ __('Total') }}</span>
                    <span class="text-xl font-bold text-gray-900">{{ $priceBreakdown->formatTotalPrice() }}</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Terms & Conditions --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start">
            <div class="flex items-center h-5">
                <input
                    id="terms"
                    wire:model.live="termsAccepted"
                    type="checkbox"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
            </div>
            <div class="ml-3 text-sm">
                <label for="terms" class="font-medium text-gray-700">
                    {{ __('I accept the terms and conditions') }}
                </label>
                <p class="text-gray-500">
                    {{ __('By confirming this booking, you agree to our terms and conditions, cancellation policy, and privacy policy.') }}
                </p>
            </div>
        </div>

        @error('termsAccepted')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-between items-center">
        <button
            wire:click="backToEdit"
            type="button"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            {{ __('Back to Edit') }}
        </button>

        <button
            wire:click="confirmBooking"
            type="button"
            class="inline-flex items-center px-6 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
            @unless ($termsAccepted) disabled @endunless
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ __('Confirm Booking') }}
        </button>
    </div>
</div>
