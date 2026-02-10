@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">New Booking</h1>
            <p class="text-gray-600">Create a new guest reservation</p>
        </div>
        <a href="/booking" class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
            Start Booking
        </a>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Booking System</h3>
                <p class="mt-1 text-sm text-gray-500">Use the date and occupancy selector to start a new booking.</p>
            </div>
        </div>
    </div>
</div>
@endsection
