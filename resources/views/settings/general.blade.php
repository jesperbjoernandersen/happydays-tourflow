@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">General Settings</h1>
        <p class="text-gray-600">Configure system-wide settings</p>
    </div>

    <div class="bg-white rounded-lg shadow max-w-4xl">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Hotel Information</h2>
        </div>
        <div class="p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Hotel Name</label>
                <input type="text" value="Grand Plaza Hotel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                <input type="text" value="123 Main Street, Copenhagen" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" value="+45 12 34 56 78" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" value="info@grandplaza.dk" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Booking Settings</h2>
        </div>
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stay (nights)</label>
                    <input type="number" value="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Advance Booking (days)</label>
                    <input type="number" value="365" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cancellation Window (hours)</label>
                    <input type="number" value="24" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Currency</label>
                    <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option>EUR (€)</option>
                        <option selected>DKK (kr)</option>
                        <option>USD ($)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-end">
            <button class="px-6 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
                Save Changes
            </button>
        </div>
    </div>
</div>
@endsection
