<div class="stay-type-selector">
    @if(empty($stayTypes))
        <div class="p-6 text-center text-gray-500">
            <p class="text-lg">No stay types available</p>
            <p class="text-sm mt-2">Please check back later or contact us for more information.</p>
        </div>
    @else
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach($stayTypes as $stayType)
                <div
                    wire:click="selectStayType({{ $stayType['id'] }})"
                    class="cursor-pointer rounded-xl border-2 p-5 transition-all duration-200 hover:shadow-lg
                        {{ $selectedStayTypeId === $stayType['id']
                            ? 'border-blue-500 bg-blue-50 shadow-md'
                            : 'border-gray-200 bg-white hover:border-blue-300 hover:bg-gray-50' }}"
                >
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $stayType['name'] }}
                        </h3>
                        @if($selectedStayTypeId === $stayType['id'])
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-500 text-white">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                        @endif
                    </div>

                    <!-- Description -->
                    @if($stayType['description'])
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                            {{ $stayType['description'] }}
                        </p>
                    @endif

                    <!-- Details Grid -->
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span class="font-medium text-gray-700">{{ $stayType['nights'] }} nights</span>
                        </div>
                        <div class="flex items-center text-sm">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span class="font-medium text-gray-700">
                                @switch($stayType['included_board_type'])
                                    @case('AI')
                                        All-Inclusive
                                        @break
                                    @case('HB')
                                        Half Board
                                        @break
                                    @case('BB')
                                        Bed & Breakfast
                                        @break
                                    @case('FB')
                                        Full Board
                                        @break
                                    @default
                                        {{ $stayType['included_board_type'] }}
                                @endswitch
                            </span>
                        </div>
                        @if($stayType['hotel_name'])
                            <div class="flex items-center text-sm">
                                <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <span class="text-gray-500">{{ $stayType['hotel_name'] }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Price Hint -->
                    <div class="pt-3 border-t border-gray-100">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            {{ $selectedStayTypeId === $stayType['id']
                                ? 'bg-blue-100 text-blue-700'
                                : 'bg-gray-100 text-gray-600' }}">
                            {{ $stayType['price_hint'] }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Validation Message -->
        @if($showValidation)
            <div class="mt-6 p-4 rounded-lg bg-red-50 border border-red-200">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-red-700 font-medium">
                        Please select a stay type before proceeding.
                    </p>
                </div>
            </div>
        @endif

        <!-- Selected Summary -->
        @if($this->selectedStayType && $selectedStayTypeId)
            <div class="mt-6 p-4 rounded-lg bg-green-50 border border-green-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <p class="font-medium text-green-800">Selected: {{ $this->selectedStayType['name'] }}</p>
                            <p class="text-sm text-green-600">
                                {{ $this->selectedStayType['nights'] }} nights &bull; {{ $this->selectedStayType['included_board_type'] }} &bull; {{ $this->selectedStayType['price_hint'] }}
                            </p>
                        </div>
                    </div>
                    <button
                        wire:click="$emit('proceedToNextStep', {{ $selectedStayTypeId }})"
                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
                    >
                        Continue
                    </button>
                </div>
            </div>
        @endif
    @endif
</div>
