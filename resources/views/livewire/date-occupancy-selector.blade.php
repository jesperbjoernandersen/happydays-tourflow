<div class="space-y-6">
    {{-- Date Selection Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <svg class="inline-block w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Check-in Date
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Date Input --}}
            <div class="relative">
                <label for="checkinDate" class="block text-sm font-medium text-gray-700 mb-1">
                    Select Check-in Date
                </label>
                <div class="relative">
                    <input
                        type="date"
                        id="checkinDate"
                        wire:model="checkinDate"
                        min="{{ now()->format('Y-m-d') }}"
                        max="{{ $maxDate->format('Y-m-d') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('checkinDate') border-red-500 @enderror"
                    >
                    <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                @error('checkinDate')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Quick Date Selection Buttons --}}
            <div class="flex items-end gap-2">
                <button
                    type="button"
                    wire:click="setToday"
                    class="px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
                >
                    Today
                </button>
                <button
                    type="button"
                    wire:click="setTomorrow"
                    class="px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors"
                >
                    Tomorrow
                </button>
            </div>
        </div>

        {{-- Stay Duration Info --}}
        @if($stayType && $stayType->nights)
            <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Stay Duration:</span>
                    <span class="text-sm font-semibold text-gray-900">{{ $stayType->nights }} night{{ $stayType->nights > 1 ? 's' : '' }}</span>
                </div>
                @if($checkinDate)
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-sm text-gray-600">Check-out:</span>
                        <span class="text-sm font-semibold text-gray-900">{{ $checkoutDate }}</span>
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Occupancy Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <svg class="inline-block w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Guests
        </h3>

        {{-- Adults Counter --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Adults
                <span class="text-gray-400 font-normal">(18+ years)</span>
            </label>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="decrementAdults"
                    @disabled($adults <= 1)
                    class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </button>
                <span class="w-12 text-center text-xl font-semibold text-gray-900">{{ $adults }}</span>
                <button
                    type="button"
                    wire:click="incrementAdults"
                    @disabled($adults >= $maxAdults)
                    class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>
            @error('adults')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Children Counter --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Children
                <span class="text-gray-400 font-normal">(0-17 years)</span>
            </label>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="decrementChildren"
                    @disabled($children <= 0)
                    class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </button>
                <span class="w-12 text-center text-xl font-semibold text-gray-900">{{ $children }}</span>
                <button
                    type="button"
                    wire:click="incrementChildren"
                    :disabled="{{ $children }} >= {{ $maxChildren }} || {{ $totalGuests }} >= {{ $maxOccupancy }}"
                    class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>
            @error('children')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Infants Counter --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Infants
                <span class="text-gray-400 font-normal">(Under 2 years, free)</span>
            </label>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    wire:click="decrementInfants"
                    @disabled($infants <= 0)
                    class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                    </svg>
                </button>
                <span class="w-12 text-center text-xl font-semibold text-gray-900">{{ $infants }}</span>
                <button
                    type="button"
                    wire:click="incrementInfants"
                    class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition-colors"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
            </div>
            @error('infants')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Extra Beds Counter (if supported) --}}
        @if($supportsExtraBeds)
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Extra Beds
                    <span class="text-gray-400 font-normal">({{ $extraBedSlots }} available)</span>
                </label>
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        wire:click="decrementExtraBeds"
                        @disabled($extraBeds <= 0)
                        class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    <span class="w-12 text-center text-xl font-semibold text-gray-900">{{ $extraBeds }}</span>
                    <button
                        type="button"
                        wire:click="incrementExtraBeds"
                        @disabled($extraBeds >= $extraBedSlots)
                        class="w-10 h-10 rounded-full border border-gray-300 flex items-center justify-center text-gray-600 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </button>
                </div>
                @error('extraBeds')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endif

        {{-- Total Guests Display --}}
        <div class="mt-4 p-3 bg-blue-50 rounded-lg">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-blue-800">Total Guests:</span>
                <span class="text-lg font-bold text-blue-900">{{ $totalGuests }} guest{{ $totalGuests != 1 ? 's' : '' }}</span>
            </div>
            @if($roomType && $roomType->max_occupancy)
                <div class="flex items-center justify-between mt-1">
                    <span class="text-xs text-blue-600">Maximum occupancy:</span>
                    <span class="text-xs text-blue-700">{{ $roomType->max_occupancy }} guests</span>
                </div>
            @endif
        </div>

        {{-- Exceeds Occupancy Warning --}}
        @if($exceedsOccupancy)
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-sm text-red-700">
                        Total guests ({{ $totalGuests }}) exceeds maximum occupancy ({{ $maxOccupancy }}).
                        Please reduce the number of guests.
                    </span>
                </div>
            </div>
        @endif
    </div>

    {{-- Selection Summary --}}
    @if($checkinDate && !$errors->any())
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h4 class="text-md font-semibold text-gray-900 mb-3">Your Selection</h4>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Check-in:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ \Carbon\Carbon::parse($checkinDate)->format('l, M j, Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Check-out:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ \Carbon\Carbon::parse($checkoutDate)->format('l, M j, Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Duration:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $nights }} night{{ $nights > 1 ? 's' : '' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Guests:</span>
                    <span class="font-medium text-gray-900 ml-2">
                        {{ $adults }} adult{{ $adults != 1 ? 's' : '' }}
                        @if($children > 0), {{ $children }} child{{ $children != 1 ? 'ren' : '' }}@endif
                        @if($infants > 0), {{ $infants }} infant{{ $infants != 1 ? 's' : '' }}@endif
                    </span>
                </div>
                @if($extraBeds > 0)
                    <div>
                        <span class="text-gray-500">Extra beds:</span>
                        <span class="font-medium text-gray-900 ml-2">{{ $extraBeds }}</span>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
