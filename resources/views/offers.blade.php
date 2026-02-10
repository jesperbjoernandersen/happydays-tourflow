@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Special Offers</h1>
            <p class="text-gray-600">Manage promotional packages and special deals</p>
        </div>
        <button class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
            + Create Offer
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-orange-400 to-red-500 flex items-center justify-center">
                <svg class="w-12 h-12 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-gray-900">Summer Escape</h3>
 Package                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">Includes breakfast, spa access, and late checkout</p>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Valid until Aug 31, 2026</span>
                    <span class="font-medium text-gray-900">€349/night</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                <svg class="w-12 h-12 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-gray-900">Romantic Getaway</h3>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">Champagne, roses, and room upgrade included</p>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Valid until Dec 31, 2026</span>
                    <span class="font-medium text-gray-900">€299/night</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-green-400 to-green-600 flex items-center justify-center">
                <svg class="w-12 h-12 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-gray-900">Family Fun Package</h3>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">Kids eat free, game room access, family activities</p>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Starts Apr 1, 2026</span>
                    <span class="font-medium text-gray-900">€199/night</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="h-32 bg-gradient-to-r from-purple-400 to-purple-600 flex items-center justify-center">
                <svg class="w-12 h-12 text-white opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="font-semibold text-gray-900">Weekend Bliss</h3>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                </div>
                <p class="text-sm text-gray-600 mb-4">Early check-in, late checkout, welcome drink</p>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-500">Valid every weekend</span>
                    <span class="font-medium text-gray-900">€179/night</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
