@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Guest Check-out</h1>
        <p class="text-gray-600">Process guest departures</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
        <form class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Room Number</label>
                <div class="flex gap-4">
                    <input type="text" placeholder="Enter room number..." class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <button type="button" class="px-6 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
                        Find Room
                    </button>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">Pending Charges</h3>
                        <p class="mt-1 text-sm text-yellow-700">Room service: €45.00 - Confirm before check-out</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <button type="button" class="w-full px-6 py-3 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
                    Process Check-out & Generate Invoice
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
