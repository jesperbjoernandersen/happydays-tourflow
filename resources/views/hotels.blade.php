@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Hotels</h1>
            <p class="text-gray-600">Manage hotel properties and room inventory</p>
        </div>
        <button class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
            + Add Hotel
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="h-40 bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-gray-900">Grand Plaza Hotel</h3>
                <p class="text-sm text-gray-500 mt-1">Copenhagen, Denmark</p>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-sm text-gray-600">45 Rooms</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="h-40 bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center">
                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-gray-900">Seaside Resort</h3>
                <p class="text-sm text-gray-500 mt-1">Aarhus, Denmark</p>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-sm text-gray-600">32 Rooms</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="h-40 bg-gradient-to-r from-yellow-500 to-yellow-600 flex items-center justify-center">
                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-gray-900">Nordic Lodge</h3>
                <p class="text-sm text-gray-500 mt-1">Aalborg, Denmark</p>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-sm text-gray-600">28 Rooms</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Maintenance</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
