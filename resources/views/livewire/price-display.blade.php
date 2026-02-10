@php
    /** @var \App\Livewire\PriceDisplay $this */
    $priceBreakdown = $this->priceBreakdown;
    $showPerNight = $this->showPerNight;
    $showDetails = $this->showDetails;
    $isSummary = $this->isSummary;
@endphp

<div class="price-display {{ $isSummary ? 'price-display--summary' : 'price-display--full' }}">
    @if (!$priceBreakdown)
        {{-- Empty State --}}
        <div class="price-display__empty">
            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-gray-600 text-center">
                {{ __('Select dates and guests to see pricing') }}
            </p>
        </div>
    @else
        {{-- Pricing Model Badge --}}
        <div class="price-display__model-badge mb-4">
            @if ($priceBreakdown->isOccupancyBased())
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                    </svg>
                    {{ __('Per Person Pricing') }}
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                        <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Fixed Price up to :count guests', ['count' => $priceBreakdown->getBaseOccupancy()]) }}
                </span>
            @endif
        </div>

        {{-- Total Price Highlight --}}
        <div class="price-display__total bg-gray-50 rounded-lg p-4 mb-4">
            <div class="flex justify-between items-baseline">
                <span class="text-gray-600 font-medium">
                    {{ __('Total Price') }}
                    @if ($priceBreakdown->getNights() > 1)
                        <span class="text-sm text-gray-500">
                            ({{ $priceBreakdown->getNights() }} {{ __('nights') }})
                        </span>
                    @endif
                </span>
                <span class="text-2xl font-bold text-gray-900">
                    {{ $priceBreakdown->formatTotalPrice() }}
                </span>
            </div>

            {{-- Per Night Price (if multi-night stay) --}}
            @if ($priceBreakdown->getNights() > 1)
                <div class="mt-2 text-right">
                    <button
                        wire:click="togglePerNight"
                        class="text-sm text-blue-600 hover:text-blue-800 focus:outline-none"
                    >
                        {{ $showPerNight ? __('Hide') : __('Show') }}
                        {{ __('per night: :price', ['price' => $priceBreakdown->formatPerNightTotalPrice()]) }}
                    </button>
                </div>
            @endif
        </div>

        {{-- Toggle Details --}}
        @if ($this->hasSupplements())
            <div class="mb-3">
                <button
                    wire:click="toggleDetails"
                    class="text-sm text-gray-600 hover:text-gray-800 flex items-center focus:outline-none"
                >
                    <svg class="w-4 h-4 mr-1 transition-transform {{ $showDetails ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                    {{ $showDetails ? __('Hide details') : __('Show details') }}
                </button>
            </div>
        @endif

        {{-- Detailed Breakdown --}}
        @if ($showDetails && $this->hasSupplements())
            <div class="price-display__breakdown">
                {{-- Base Price --}}
                <div class="price-row price-row--base">
                    <span class="price-label">
                        {{ __('Room rate') }}
                        @if ($priceBreakdown->getNights() > 1)
                            <span class="text-xs text-gray-500">
                                ({{ $priceBreakdown->getNights() }} × {{ $priceBreakdown->formatPerNightBasePrice() }})
                            </span>
                        @endif
                    </span>
                    <span class="price-value text-gray-900">
                        {{ $priceBreakdown->formatBasePrice() }}
                    </span>
                </div>

                {{-- Adult Supplement --}}
                @if ($priceBreakdown->getAdultSupplement() > 0)
                    <div class="price-row price-row--supplement">
                        <span class="price-label">
                            {{ __('Adults') }}
                            <span class="text-xs text-gray-500">
                                ({{ $priceBreakdown->getAdults() }} × {{ $priceBreakdown->getNights() }} {{ __('nights') }})
                            </span>
                        </span>
                        <span class="price-value text-orange-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getAdultSupplement()) }}
                        </span>
                    </div>
                @endif

                {{-- Child Supplement --}}
                @if ($priceBreakdown->getChildSupplement() > 0)
                    <div class="price-row price-row--supplement">
                        <span class="price-label">
                            {{ __('Children') }}
                            <span class="text-xs text-gray-500">
                                ({{ $priceBreakdown->getChildren() }} × {{ $priceBreakdown->getNights() }} {{ __('nights') }})
                            </span>
                        </span>
                        <span class="price-value text-orange-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getChildSupplement()) }}
                        </span>
                    </div>
                @endif

                {{-- Infant Supplement --}}
                @if ($priceBreakdown->getInfantSupplement() > 0)
                    <div class="price-row price-row--supplement">
                        <span class="price-label">
                            {{ __('Infants') }}
                            <span class="text-xs text-gray-500">
                                ({{ $priceBreakdown->getInfants() }} × {{ $priceBreakdown->getNights() }} {{ __('nights') }})
                            </span>
                        </span>
                        <span class="price-value text-orange-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getInfantSupplement()) }}
                        </span>
                    </div>
                @endif

                {{-- Extra Bed Supplement --}}
                @if ($priceBreakdown->getExtraBedSupplement() > 0)
                    <div class="price-row price-row--supplement">
                        <span class="price-label">
                            {{ __('Extra beds') }}
                            <span class="text-xs text-gray-500">
                                ({{ $priceBreakdown->getExtraBeds() }} × {{ $priceBreakdown->getNights() }} {{ __('nights') }})
                            </span>
                        </span>
                        <span class="price-value text-orange-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getExtraBedSupplement()) }}
                        </span>
                    </div>
                @endif

                {{-- Single Use Supplement --}}
                @if ($priceBreakdown->getSingleUseSupplement() > 0)
                    <div class="price-row price-row--supplement">
                        <span class="price-label">
                            {{ __('Single use supplement') }}
                        </span>
                        <span class="price-value text-red-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getSingleUseSupplement()) }}
                        </span>
                    </div>
                @endif

                {{-- Extra Occupancy Charge --}}
                @if ($priceBreakdown->getExtraOccupancyCharge() > 0)
                    <div class="price-row price-row--supplement">
                        <span class="price-label">
                            {{ __('Extra occupancy') }}
                        </span>
                        <span class="price-value text-orange-600">
                            +{{ $priceBreakdown->formatAmount($priceBreakdown->getExtraOccupancyCharge()) }}
                        </span>
                    </div>
                @endif
            </div>
        @endif

        {{-- Per Night Breakdown Display --}}
        @if ($showPerNight && $priceBreakdown->getNights() > 1)
            <div class="price-display__per-night mt-4 border-t pt-4">
                <h4 class="text-sm font-medium text-gray-700 mb-2">
                    {{ __('Price breakdown per night') }}
                </h4>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">{{ __('Base rate per night') }}</span>
                        <span class="font-medium">{{ $priceBreakdown->formatPerNightBasePrice() }}</span>
                    </div>
                    @if ($priceBreakdown->getAdultSupplement() > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('Adult supplements') }}</span>
                            <span class="text-orange-600">+{{ $priceBreakdown->formatAmount($priceBreakdown->getAdultSupplement() / $priceBreakdown->getNights()) }}</span>
                        </div>
                    @endif
                    @if ($priceBreakdown->getChildSupplement() > 0)
                        <div class="flex justify-between">
                            <span class="text-gray-600">{{ __('Child supplements') }}</span>
                            <span class="text-orange-600">+{{ $priceBreakdown->formatAmount($priceBreakdown->getChildSupplement() / $priceBreakdown->getNights()) }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between border-t pt-1 mt-1">
                        <span class="font-medium text-gray-900">{{ __('Total per night') }}</span>
                        <span class="font-bold text-gray-900">{{ $priceBreakdown->formatPerNightTotalPrice() }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- What's Included --}}
        <div class="price-display__included mt-4">
            <h4 class="text-sm font-medium text-gray-700 mb-2">
                {{ __('What\'s included') }}
            </h4>
            <ul class="text-sm text-gray-600 space-y-1">
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    {{ __('Accommodation for :guests guests', ['guests' => $priceBreakdown->getBaseOccupancy()]) }}
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                    {{ __(':nights nights stay', ['nights' => $priceBreakdown->getNights()]) }}
                </li>
                @if ($priceBreakdown->getInfants() > 0)
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Infant care (no extra charge)') }}
                    </li>
                @endif
                @if ($priceBreakdown->getExtraBeds() > 0)
                    <li class="flex items-start">
                        <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                        {{ __('Extra bed(s) included') }}
                    </li>
                @endif
            </ul>
        </div>

        {{-- Pricing Model Info --}}
        <div class="price-display__info mt-4 p-3 bg-blue-50 rounded-lg">
            <p class="text-xs text-blue-700">
                <strong>{{ $this->getPricingModelLabel() }}:</strong>
                {{ $this->getPricingModelDescription() }}
            </p>
        </div>
    @endif
</div>

<style>
    .price-display {
        @apply w-full;
    }

    .price-display--summary {
        @apply text-sm;
    }

    .price-display__empty {
        @apply py-8 text-center;
    }

    .price-display__breakdown {
        @apply space-y-2;
    }

    .price-row {
        @apply flex justify-between items-center py-2 border-b border-gray-100 last:border-0;
    }

    .price-row--base {
        @apply bg-gray-50 px-2 rounded;
    }

    .price-label {
        @apply text-gray-600 text-sm;
    }

    .price-value {
        @apply font-medium text-sm;
    }
</style>
