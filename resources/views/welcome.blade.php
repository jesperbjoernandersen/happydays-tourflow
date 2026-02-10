@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome to HappyDays Tourflow</h1>
        <p class="text-gray-600 mt-2">Your hotel management system dashboard</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Today's Check-ins</p>
                    <p class="text-2xl font-bold text-gray-900">12</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Bookings</p>
                    <p class="text-2xl font-bold text-gray-900">47</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Revenue Today</p>
                    <p class="text-2xl font-bold text-gray-900">€4,250</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Available Rooms</p>
                    <p class="text-2xl font-bold text-gray-900">23/45</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-2 gap-4">
                <a href="/booking" class="flex items-center p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-blue-500 hover:bg-blue-50 transition-colors">
                    <div class="p-2 rounded-lg bg-blue-100 text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium text-gray-700">New Booking</span>
                </a>
                <a href="/checkin" class="flex items-center p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-green-500 hover:bg-green-50 transition-colors">
                    <div class="p-2 rounded-lg bg-green-100 text-green-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium text-gray-700">Check-in Guest</span>
                </a>
                <a href="/checkout" class="flex items-center p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-orange-500 hover:bg-orange-50 transition-colors">
                    <div class="p-2 rounded-lg bg-orange-100 text-orange-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium text-gray-700">Check-out Guest</span>
                </a>
                <a href="/guests/search" class="flex items-center p-4 rounded-lg border-2 border-dashed border-gray-200 hover:border-purple-500 hover:bg-purple-50 transition-colors">
                    <div class="p-2 rounded-lg bg-purple-100 text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <span class="ml-3 font-medium text-gray-700">Find Guest</span>
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full bg-green-500 mr-3"></div>
                        <span class="text-sm text-gray-700">New booking #1234 - John Doe</span>
                    </div>
                    <span class="text-xs text-gray-500">2 min ago</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full bg-blue-500 mr-3"></div>
                        <span class="text-sm text-gray-700">Check-in completed - Room 204</span>
                    </div>
                    <span class="text-xs text-gray-500">15 min ago</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100">
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full bg-yellow-500 mr-3"></div>
                        <span class="text-sm text-gray-700">Payment received - €350</span>
                    </div>
                    <span class="text-xs text-gray-500">32 min ago</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <div class="flex items-center">
                        <div class="w-2 h-2 rounded-full bg-red-500 mr-3"></div>
                        <span class="text-sm text-gray-700">Room 108 - Maintenance request</span>
                    </div>
                    <span class="text-xs text-gray-500">1 hour ago</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
