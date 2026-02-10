@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
            <p class="text-gray-600">Overview of key performance metrics</p>
        </div>
        <div class="flex items-center space-x-4">
            <select class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                <option>Last 7 days</option>
                <option selected>Last 30 days</option>
                <option>Last 90 days</option>
                <option>This year</option>
            </select>
            <button class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
                Export Report
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Revenue Overview</h2>
            <div class="h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                <span class="text-gray-500">Revenue Chart Placeholder</span>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Booking Trends</h2>
            <div class="h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                <span class="text-gray-500">Booking Trends Chart Placeholder</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Total Revenue</span>
                <span class="text-xs text-green-600 font-medium">+12.5%</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">€127,450</div>
            <div class="text-xs text-gray-400 mt-1">vs. last month</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Total Bookings</span>
                <span class="text-xs text-green-600 font-medium">+8.3%</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">342</div>
            <div class="text-xs text-gray-400 mt-1">vs. last month</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">Avg. Occupancy</span>
                <span class="text-xs text-red-600 font-medium">-2.1%</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">76.4%</div>
            <div class="text-xs text-gray-400 mt-1">vs. last month</div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-gray-500">ADR</span>
                <span class="text-xs text-green-600 font-medium">+5.7%</span>
            </div>
            <div class="text-2xl font-bold text-gray-900">€142.50</div>
            <div class="text-xs text-gray-400 mt-1">vs. last month</div>
        </div>
    </div>
</div>
@endsection
