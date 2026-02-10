@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Reservations</h1>
            <p class="text-gray-600">Manage all bookings and reservations</p>
        </div>
        <a href="{{ route('booking') }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
            + New Booking
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" action="{{ route('reservations') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Booking ref or guest name" 
                    class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700 mb-1">Hotel</label>
                <select name="hotel_id" class="w-full rounded-lg border-gray-300 border px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    <option value="">All Hotels</option>
                    @foreach($hotels as $hotel)
                        <option value="{{ $hotel->id }}" {{ request('hotel_id') == $hotel->id ? 'selected' : '' }}>{{ $hotel->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-none">
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #fbba00;">
                    Filter
                </button>
            </div>
        </form>
    </div>

    @if($bookings->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Ref</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hotel</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($bookings as $booking)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-medium text-gray-900">{{ $booking->booking_reference }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $booking->guests->first()->name ?? 'N/A' }}</div>
                        <div class="text-sm text-gray-500">{{ $booking->guests->first()->email ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $booking->hotel->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $booking->check_in_date->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $booking->check_out_date->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                            @switch($booking->status)
                                @case('pending') bg-yellow-100 text-yellow-800 @break
                                @case('confirmed') bg-blue-100 text-blue-800 @break
                                @case('checked_in') bg-green-100 text-green-800 @break
                                @case('completed') bg-gray-100 text-gray-800 @break
                                @case('cancelled') bg-red-100 text-red-800 @break
                            @endswitch">
                            {{ ucfirst(str_replace('_', ' ', $booking->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('reservations.show', $booking) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                        @if($booking->status === 'pending' || $booking->status === 'confirmed')
                            <form action="{{ route('reservations.cancel', $booking) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $bookings->appends(request()->query())->links() }}
    </div>
    @else
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No reservations found</h3>
            <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or create a new booking.</p>
            <div class="mt-6">
                <a href="{{ route('booking') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
                    + New Booking
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
