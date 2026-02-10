<div class="space-y-6">
    {{-- Header --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-2">
            <svg class="inline-block w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Guest Information
        </h3>
        <p class="text-sm text-gray-600">
            Please provide the following information for all guests. Age will be calculated based on the check-in date.
        </p>
    </div>

    {{-- Guest Summary --}}
    <div class="bg-blue-50 rounded-lg border border-blue-200 p-4">
        <h4 class="text-sm font-semibold text-blue-800 mb-2">Booking Summary</h4>
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <span class="text-blue-600">Check-in:</span>
                <span class="font-medium text-blue-900 ml-1">
                    @if($checkinDate)
                        {{ \Carbon\Carbon::parse($checkinDate)->format('M j, Y') }}
                    @else
                        <span class="text-gray-400">Not selected</span>
                    @endif
                </span>
            </div>
            <div>
                <span class="text-blue-600">Adults:</span>
                <span class="font-medium text-blue-900 ml-1">{{ $adults }}</span>
            </div>
            <div>
                <span class="text-blue-600">Children:</span>
                <span class="font-medium text-blue-900 ml-1">{{ $children }}</span>
            </div>
            <div>
                <span class="text-blue-600">Infants:</span>
                <span class="font-medium text-blue-900 ml-1">{{ $infants }}</span>
            </div>
            <div>
                <span class="text-blue-600">Total Guests:</span>
                <span class="font-medium text-blue-900 ml-1">{{ $this->totalGuests }}</span>
            </div>
        </div>
    </div>

    {{-- Guest Information Form --}}
    <div class="space-y-4">
        @foreach($guests as $index => $guest)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h4 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-sm mr-2">
                        {{ $index + 1 }}
                    </span>
                    Guest {{ $index + 1 }}
                    @if(isset($guest['category']) && $guest['category'])
                        <span class="ml-auto px-3 py-1 text-xs font-semibold rounded-full 
                            @if($guest['category'] === 'INFANT') bg-purple-100 text-purple-700
                            @elseif($guest['category'] === 'CHILD') bg-green-100 text-green-700
                            @else bg-gray-100 text-gray-700 @endif">
                            {{ $guest['category'] }}
                        </span>
                    @endif
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Guest Name --}}
                    <div>
                        <label for="guests.{{ $index }}.name" class="block text-sm font-medium text-gray-700 mb-1">
                            Full Name
                        </label>
                        <input
                            type="text"
                            id="guests.{{ $index }}.name"
                            wire:model="guests.{{ $index }}.name"
                            placeholder="Enter guest's full name"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('guests.' . $index . '.name') border-red-500 @enderror"
                        >
                        @error('guests.' . $index . '.name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Birthdate --}}
                    <div>
                        <label for="guests.{{ $index }}.birthdate" class="block text-sm font-medium text-gray-700 mb-1">
                            Birthdate
                        </label>
                        <div class="relative">
                            <input
                                type="date"
                                id="guests.{{ $index }}.birthdate"
                                wire:model="guests.{{ $index }}.birthdate"
                                wire:change="updatedGuestsBirthdate($event.target.value, {{ $index }})"
                                min="{{ $minBirthdate->format('Y-m-d') }}"
                                max="{{ $maxBirthdate->format('Y-m-d') }}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('guests.' . $index . '.birthdate') border-red-500 @enderror"
                            >
                            <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        @error('guests.' . $index . '.birthdate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Age Display (if birthdate is entered) --}}
                @if(!empty($guest['birthdate']))
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm text-gray-600">Age at check-in:</span>
                                <span class="ml-2 text-lg font-semibold text-gray-900">
                                    {{ $guest['age'] ?? '--' }} years
                                </span>
                            </div>
                            @if(isset($guest['category']) && $guest['category'])
                                <span class="px-3 py-1 text-sm font-semibold rounded-full 
                                    @if($guest['category'] === 'INFANT') bg-purple-100 text-purple-700
                                    @elseif($guest['category'] === 'CHILD') bg-green-100 text-green-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    {{ $guest['category'] }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Validation Summary --}}
    @if($errors->any())
        <div class="bg-red-50 rounded-lg border border-red-200 p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-semibold text-red-800">Please fix the following errors:</h4>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Submit Button --}}
    <div class="flex justify-end">
        <button
            type="button"
            wire:click="submitGuests"
            class="px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors"
        >
            <svg class="inline-block w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Continue with {{ $this->totalGuests }} Guest{{ $this->totalGuests != 1 ? 's' : '' }}
        </button>
    </div>

    {{-- Help Text --}}
    <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-gray-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-gray-600">
                <p class="font-medium text-gray-700 mb-1">Age Classification Guidelines</p>
                <p>Guests are classified based on their age at the check-in date:</p>
                <ul class="mt-2 space-y-1 text-gray-500">
                    <li>• <strong>INFANT:</strong> Under 2 years</li>
                    <li>• <strong>CHILD:</strong> 2 to 17 years</li>
                    <li>• <strong>ADULT:</strong> 18 years and older</li>
                </ul>
            </div>
        </div>
    </div>
</div>
