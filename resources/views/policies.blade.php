@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Policies</h1>
            <p class="text-gray-600">Configure hotel policies and age classifications</p>
        </div>
        <button class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
            + Add Policy
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Age Classifications</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-900">Infant</span>
                        <p class="text-sm text-gray-500">0 - 1 years</p>
                    </div>
                    <span class="text-sm font-medium text-green-600">Free</span>
                </div>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-900">Child</span>
                        <p class="text-sm text-gray-500">2 - 17 years</p>
                    </div>
                    <span class="text-sm font-medium text-blue-600">Child Rate</span>
                </div>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <span class="font-medium text-gray-900">Adult</span>
                        <p class="text-sm text-gray-500">18+ years</p>
                    </div>
                    <span class="text-sm font-medium text-gray-900">Full Rate</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Booking Rules</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <span class="text-gray-700">Minimum Stay</span>
                    <span class="font-medium text-gray-900">1 Night</span>
                </div>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <span class="text-gray-700">Maximum Advance Booking</span>
                    <span class="font-medium text-gray-900">365 Days</span>
                </div>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <span class="text-gray-700">Cancellation Deadline</span>
                    <span class="font-medium text-gray-900">24 Hours</span>
                </div>
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <span class="text-gray-700">No-Show Policy</span>
                    <span class="font-medium text-gray-900">First Night Charged</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
