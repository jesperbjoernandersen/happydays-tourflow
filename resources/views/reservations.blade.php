@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Reservations</h1>
            <p class="text-gray-600">View and manage all reservations</p>
        </div>
        <a href="/booking" class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
            + New Reservation
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Booking ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-in</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check-out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#BK-001</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">John Smith</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Room 101</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Feb 10, 2026</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Feb 14, 2026</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Confirmed</span>
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#BK-002</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jane Doe</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Room 205</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Feb 11, 2026</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Feb 13, 2026</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
