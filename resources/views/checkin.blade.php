@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Guest Check-in</h1>
        <p class="text-gray-600">Process guest arrivals</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Booking Reference</label>
                <div class="flex gap-4">
                    <input type="text" placeholder="Enter booking ID..." class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" class="px-6 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
                        Find Booking
                    </button>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">Or scan guest ID</h3>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Scan passport or ID card</p>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
