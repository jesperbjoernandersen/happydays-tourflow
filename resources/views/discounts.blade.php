@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Discounts</h1>
            <p class="text-gray-600">Manage promotional discounts and special rates</p>
        </div>
        <button class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
            + Add Discount
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Early Bird</h3>
                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-800">Active</span>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-2">10%</div>
            <p class="text-sm text-gray-500">Book 30+ days in advance</p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400">Uses: 156 this month</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Corporate Rate</h3>
                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-green-100 text-green-800">Active</span>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-2">15%</div>
            <p class="text-sm text-gray-500">Verified corporate partners</p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400">Uses: 89 this month</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Last Minute</h3>
                <span class="text-xs font-semibold px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">Scheduled</span>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-2">20%</div>
            <p class="text-sm text-gray-500">Book within 48 hours</p>
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-xs text-gray-400">Starts: Mar 1, 2026</p>
            </div>
        </div>
    </div>
</div>
@endsection
