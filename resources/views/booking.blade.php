@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">New Booking</h1>
        <p class="text-gray-600">Create a new hotel reservation</p>
    </div>

    <form action="{{ route('booking.store') }}" method="POST" class="space-y-8">
        @csrf
        
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">1. Select Hotel & Room Type</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hotel</label>
                    <select name="hotel_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a hotel...</option>
                        @if(isset($hotels))
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->id }}">{{ $hotel->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room Type</label>
                    <select name="room_type_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select a room type...</option>
                        @foreach($roomTypes ?? [] as $roomType)
                            <option value="{{ $roomType->id }}" 
                                    data-price="{{ $roomType->base_price }}"
                                    data-max="{{ $roomType->max_occupancy }}">
                                {{ $roomType->name }} (€{{ $roomType->base_price }}/night)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">2. Stay Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-in Date</label>
                    <input type="date" name="check_in" required min="{{ date('Y-m-d') }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check-out Date</label>
                    <input type="date" name="check_out" required min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">3. Guest Information</h2>
                <button type="button" onclick="addGuest()" 
                        class="px-3 py-1 text-sm font-medium text-white rounded-lg hover:opacity-90"
                        style="background-color: #fbba00;">
                    + Add Guest
                </button>
            </div>
            
            <div id="guests-container" class="space-y-4">
                <div class="guest-entry grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Guest Name</label>
                        <input type="text" name="guests[0][name]" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Birthdate</label>
                        <input type="date" name="guests[0][birthdate]" required 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="guests[0][is_child]" value="1" id="guest0_child" class="mr-2">
                        <label for="guest0_child" class="text-sm text-gray-700">Child (under 18)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" 
                    class="px-8 py-3 text-lg font-medium text-white rounded-lg hover:opacity-90 transition-colors"
                    style="background-color: #bf311a;">
                Create Booking
            </button>
        </div>
    </form>
</div>

<script>
    let guestCount = 1;

    function addGuest() {
        const container = document.getElementById('guests-container');
        const entry = document.createElement('div');
        entry.className = 'guest-entry grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded-lg';
        entry.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Guest Name</label>
                <input type="text" name="guests[${guestCount}][name]" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Birthdate</label>
                <input type="date" name="guests[${guestCount}][birthdate]" required 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="flex items-center">
                <input type="checkbox" name="guests[${guestCount}][is_child]" value="1" id="guest${guestCount}_child" class="mr-2">
                <label for="guest${guestCount}_child" class="text-sm text-gray-700">Child (under 18)</label>
                <button type="button" onclick="this.closest('.guest-entry').remove()" class="ml-auto text-red-600 hover:text-red-900 text-sm font-medium">Remove</button>
            </div>
        `;
        container.appendChild(entry);
        guestCount++;
    }
</script>

@if(session('success'))
<script>
    alert('{{ session('success') }}');
</script>
@endif

@if($errors->any())
<script>
    alert('Please fix the errors in the form');
</script>
@endif
@endsection
