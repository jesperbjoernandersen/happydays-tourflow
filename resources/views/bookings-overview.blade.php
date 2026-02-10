@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Bookings Overview</h1>
            <p class="text-gray-600">Detailed booking management and status tracking</p>
        </div>
        <div class="flex items-center space-x-4">
            <select class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                <option>All Status</option>
                <option>Confirmed</option>
                <option>Pending</option>
                <option>Checked-in</option>
                <option>Checked-out</option>
                <option>Cancelled</option>
            </select>
            <input type="text" placeholder="Search bookings..." class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
        </div>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#BK-2026-001</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                <span class="text-blue-600 font-medium text-sm">JS</span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">John Smith</div>
                                <div class="text-xs text-gray-500">john@email.com</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Room 101</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Feb 10, 2026</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Feb 14, 2026</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">€556.00</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Checked-in</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 hover:text-blue-900 cursor-pointer">View</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#BK-2026-002</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                <span class="text-green-600 font-medium text-sm">JD</span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">Jane Doe</div>
                                <div class="text-xs text-gray-500">jane@email.com</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Room 205</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Feb 11, 2026</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Feb 13, 2026</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">€258.00</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Confirmed</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 hover:text-blue-900 cursor-pointer">View</td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#BK-2026-003</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-8 w-8 rounded-full bg-purple-100 flex items-center justify-center mr-3">
                                <span class="text-purple-600 font-medium text-sm">MW</span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">Michael Wilson</div>
                                <div class="text-xs text-gray-500">mike@email.com</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Suite 301</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Feb 12, 2026</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Feb 18, 2026</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">€1,494.00</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Pending</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 hover:text-blue-900 cursor-pointer">View</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between mt-6">
        <div class="text-sm text-gray-500">
            Showing 1-3 of 47 bookings
        </div>
        <div class="flex items-center space-x-2">
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Previous</button>
            <button class="px-3 py-1 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">Next</button>
        </div>
    </div>
</div>
@endsection
