@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pricing Rules</h1>
            <p class="text-gray-600">Configure pricing rules and rate management</p>
        </div>
        <button class="px-4 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
            + Add Pricing Rule
        </button>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rule Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adjustment</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Weekend Surcharge</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Percentage</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">All</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">+15%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Fri-Sun</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Early Bird Discount</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Percentage</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">All</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600">-10%</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">30+ days advance</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">High Season Premium</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Fixed Amount</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Standard</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">+€25</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Jun 1 - Aug 31</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
