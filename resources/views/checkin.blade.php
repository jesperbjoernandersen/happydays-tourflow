@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Check-in</h1>
            <p class="text-gray-600">Manage guest check-ins</p>
        </div>
    </div>

    <!-- Quick Search -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" action="{{ route('guests.search') }}" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="reference" placeholder="Search by booking reference or guest name" 
                    class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #fbba00;">
                Search
            </button>
        </form>
    </div>

    <!-- Today's Check-ins -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Today's Check-ins</h2>
        
        @if($todaysCheckins->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($todaysCheckins as $booking)
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $booking->guests->first()->name ?? 'Guest' }}</h3>
                        <p class="text-sm text-gray-500">{{ $booking->booking_reference }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        Confirmed
                    </span>
                </div>
                <div class="mt-3 text-sm text-gray-600">
                    <p>{{ $booking->hotel->name }}</p>
                    <p>{{ $booking->roomType->name ?? 'Room TBD' }}</p>
                    <p>Check-in: {{ $booking->check_in_date->format('M d, Y') }}</p>
                    <p>Check-out: {{ $booking->check_out_date->format('M d, Y') }}</p>
                </div>
                <div class="mt-4">
                    <form action="{{ route('checkin.store', $booking) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #22c55e;">
                            Check In Guest
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No check-ins scheduled for today</h3>
            <p class="mt-1 text-sm text-gray-500">All clear!</p>
        </div>
        @endif
    </div>

    <!-- Upcoming Check-ins -->
    @if($upcomingCheckins->count() > 0)
    <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Check-ins (Next 7 Days)</h2>
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hotel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($upcomingCheckins as $booking)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $booking->booking_reference }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $booking->guests->first()->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $booking->hotel->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $booking->check_in_date->format('M d, Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                Confirmed
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
