@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">New Booking</h1>
            <p class="text-gray-600">Create a new guest reservation</p>
        </div>
    </div>

    <form action="{{ route('booking.store') }}" method="POST" class="space-y-6">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Guest Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Guest Information</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Guest Name *</label>
                        <input type="text" name="guest_name" required 
                            class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="guest_email" 
                            class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="guest_phone" 
                            class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>
            </div>

            <!-- Stay Details -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Stay Details</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hotel *</label>
                        <select name="hotel_id" id="hotel_id" required 
                            class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Select a hotel</option>
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->id }}">{{ $hotel->name }} - {{ $hotel->city }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stay Type *</label>
                        <select name="stay_type_id" id="stay_type_id" required 
                            class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Select a stay type</option>
                            @foreach($stayTypes as $stayType)
                                <option value="{{ $stayType->id }}" data-hotel="{{ $stayType->hotel_id }}">
                                    {{ $stayType->name }} - {{ $stayType->hotel->name }} ({{ $stayType->nights }} nights)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Type</label>
                        <select name="room_type_id" id="room_type_id" 
                            class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">Any available room</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Details -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Booking Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Date *</label>
                    <input type="date" name="check_in_date" id="check_in_date" required 
                        min="{{ date('Y-m-d') }}"
                        class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Number of Nights *</label>
                    <input type="number" name="nights" id="nights" required min="1" value="1" 
                        class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Adults *</label>
                    <input type="number" name="adults" required min="1" value="2" 
                        class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Children</label>
                <input type="number" name="children_count" min="0" value="0" 
                    class="w-full md:w-48 rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" 
                    class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                    placeholder="Special requests or notes..."></textarea>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-3 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
                Create Booking
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Filter stay types by selected hotel
    document.getElementById('hotel_id').addEventListener('change', function() {
        const hotelId = this.value;
        const stayTypeSelect = document.getElementById('stay_type_id');
        const stayTypeOptions = stayTypeSelect.querySelectorAll('option[data-hotel]');
        
        stayTypeSelect.value = '';
        
        stayTypeOptions.forEach(option => {
            if (!hotelId || option.dataset.hotel === hotelId) {
                option.style.display = '';
                if (!hotelId) option.disabled = true;
                else option.disabled = false;
            } else {
                option.style.display = 'none';
            }
        });
    });

    // Update nights when stay type is selected
    document.getElementById('stay_type_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.dataset.nights) {
            document.getElementById('nights').value = selectedOption.dataset.nights;
        }
    });
</script>
@endpush
@endsection
