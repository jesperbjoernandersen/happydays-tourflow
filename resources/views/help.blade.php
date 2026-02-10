@extends('layouts.app')

@section('content')
<div class="p-6 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Help Center</h1>
        <p class="text-gray-600">Get help with using the HappyDays Tourflow system</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Getting Started</h2>
            <ul class="space-y-3">
                <li><a href="#" class="text-blue-600 hover:text-blue-900">System Overview</a></li>
                <li><a href="#" class="text-blue-600 hover:text-blue-900">Creating Your First Booking</a></li>
                <li><a href="#" class="text-blue-600 hover:text-blue-900">Managing Room Types</a></li>
                <li><a href="#" class="text-blue-600 hover:text-blue-900">Setting Up Pricing Rules</a></li>
            </ul>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Common Tasks</h2>
            <ul class="space-y-3">
                <li><a href="#" class="text-blue-600 hover:text-blue-900">Processing Check-in/Check-out</a></li>
                <li><a href="#" class="text-blue-600 hover:text-blue-900">Managing Reservations</a></li>
                <li><a href="#" class="text-blue-600 hover:text-blue-900">Running Reports</a></li>
                <li><a href="#" class="text-blue-600 hover:text-blue-900">User Management</a></li>
            </ul>
        </div>
    </div>
</div>
@endsection
