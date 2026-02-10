@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-600">Welcome to HappyDays Tourflow PMS</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Today's Check-ins</span>
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $todayCheckIns ?? 0 }}</div>
            <a href="{{ route('checkin') }}" class="text-sm text-blue-600 hover:underline mt-2 block">View all</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Today's Check-outs</span>
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $todayCheckOuts ?? 0 }}</div>
            <a href="{{ route('checkout') }}" class="text-sm text-blue-600 hover:underline mt-2 block">View all</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Available Rooms</span>
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-gray-900">{{ $availableRooms ?? 0 }}</div>
            <a href="{{ route('availability') }}" class="text-sm text-blue-600 hover:underline mt-2 block">View availability</a>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Revenue (MTD)</span>
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-3xl font-bold text-gray-900">€{{ number_format($totalRevenue ?? 0, 0) }}</div>
            <a href="{{ route('revenue') }}" class="text-sm text-blue-600 hover:underline mt-2 block">View report</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Bookings -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Bookings</h2>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentBookings ?? [] as $booking)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-900">
                            #{{ $booking->id }} - {{ $booking->guests->first()->name ?? 'Guest' }}
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ date('M d', strtotime($booking->check_in)) }} - {{ date('M d, Y', strtotime($booking->check_out)) }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-medium text-gray-900">€{{ number_format($booking->total_price, 2) }}</div>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $booking->status === 'confirmed' ? 'bg-yellow-100 text-yellow-800' : 
                               ($booking->status === 'checked_in' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ ucfirst($booking->status) }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No recent bookings
                </div>
                @endforelse
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <a href="{{ route('reservations') }}" class="text-sm text-blue-600 hover:underline">View all reservations →</a>
            </div>
        </div>

        <!-- Upcoming Check-ins -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Upcoming Check-ins</h2>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($upcomingCheckIns ?? [] as $booking)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                            <span class="text-blue-600 font-medium text-sm">
                                {{ strtoupper(substr($booking->guests->first()->name ?? 'G', 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">
                                {{ $booking->guests->first()->name ?? 'Guest' }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ date('D, M d', strtotime($booking->check_in)) }}
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('checkin') }}" class="px-3 py-1 text-xs font-medium text-white rounded-lg bg-green-600 hover:bg-green-700">
                        Check-in
                    </a>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    No upcoming check-ins
                </div>
                @endforelse
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <a href="{{ route('reservations') }}" class="text-sm text-blue-600 hover:underline">View all bookings →</a>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('booking') }}" class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow">
            <svg class="w-8 h-8 mx-auto mb-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span class="text-sm font-medium text-gray-900">New Booking</span>
        </a>
        <a href="{{ route('availability') }}" class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow">
            <svg class="w-8 h-8 mx-auto mb-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-sm font-medium text-gray-900">Availability</span>
        </a>
        <a href="{{ route('analytics') }}" class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow">
            <svg class="w-8 h-8 mx-auto mb-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span class="text-sm font-medium text-gray-900">Reports</span>
        </a>
        <a href="{{ route('hotels') }}" class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow">
            <svg class="w-8 h-8 mx-auto mb-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <span class="text-sm font-medium text-gray-900">Hotels</span>
        </a>
    </div>
</div>
@endsection
