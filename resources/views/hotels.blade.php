@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Hotels</h1>
            <p class="text-gray-600">Manage hotel properties and room inventory</p>
        </div>
        <a href="{{ route('hotels.create') }}" class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
            + Add Hotel
        </a>
    </div>

    @if($hotels->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($hotels as $hotel)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="h-40 bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                <svg class="w-16 h-16 text-white opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-gray-900">{{ $hotel->name }}</h3>
                <p class="text-sm text-gray-500 mt-1">{{ $hotel->city }}, {{ $hotel->country }}</p>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-sm text-gray-600">{{ $hotel->room_types_count }} Rooms</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $hotel->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                        {{ $hotel->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>
                <div class="mt-4 flex space-x-2">
                    <a href="{{ route('hotels.show', $hotel) }}" class="flex-1 text-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        View Details
                    </a>
                    <a href="{{ route('hotels.edit', $hotel) }}" class="flex-1 text-center px-3 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        Edit
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $hotels->links() }}
    </div>
    @else
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No hotels</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding your first hotel.</p>
            <div class="mt-6">
                <a href="{{ route('hotels.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
                    + Add Hotel
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
