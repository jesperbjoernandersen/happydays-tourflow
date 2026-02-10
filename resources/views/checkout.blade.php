@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Check-out</h1>
            <p class="text-gray-600">Manage guest check-outs</p>
        </div>
    </div>

    <!-- Quick Search -->
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="search" placeholder="Search by booking reference or guest name" 
                    class="w-full rounded-lg border-gray-300 border px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #fbba00;">
                Search
            </button>
        </form>
    </div>

    <!-- Today's Expected Check-outs -->
    @if($todaysCheckouts->count() > 0)
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Expected Check-outs Today</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($todaysCheckouts as $booking)
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-orange-500">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $booking->guests->first()->name ?? 'Guest' }}</h3>
                        <p class="text-sm text-gray-500">{{ $booking->booking_reference }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        Checked In
                    </span>
                </div>
                <div class="mt-3 text-sm text-gray-600">
                    <p>{{ $booking->hotel->name }}</p>
                    <p>Room: {{ $booking->roomType->name ?? 'N/A' }}</p>
                    <p>Check-in: {{ $booking->check_in_date->format('M d, Y') }}</p>
                    <p class="font-medium text-orange-600">Check-out: {{ $booking->check_out_date->format('M d, Y') }} (Today)</p>
                </div>
                <div class="mt-4">
                    <form action="{{ route('checkout.store', $booking) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #ef4444;">
                            Check Out Guest
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Currently Checked In Guests -->
    <div>
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Currently Checked In</h2>
        
        @if($currentGuests->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($currentGuests as $booking)
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold text-gray-900">{{ $booking->guests->first()->name ?? 'Guest' }}</h3>
                        <p class="text-sm text-gray-500">{{ $booking->booking_reference }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        In House
                    </span>
                </div>
                <div class="mt-3 text-sm text-gray-600">
                    <p>{{ $booking->hotel->name }}</p>
                    <p>Room: {{ $booking->roomType->name ?? 'N/A' }}</p>
                    <p>Check-in: {{ $booking->check_in_date->format('M d, Y') }}</p>
                    <p class="font-medium text-gray-900">Check-out: {{ $booking->check_out_date->format('M d, Y') }}</p>
                </div>
                <div class="mt-4">
                    <form action="{{ route('checkout.store', $booking) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #ef4444;">
                            Check Out
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No guests currently checked in</h3>
            <p class="mt-1 text-sm text-gray-500">All guests have checked out.</p>
        </div>
        @endif
    </div>
</div>
@endsection
