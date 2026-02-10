@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Integrations</h1>
        <p class="text-gray-600">Connect with external services and APIs</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Payment Gateway</h3>
                        <p class="text-sm text-gray-500">Stripe, PayPal, etc.</p>
                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Connected</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Process online payments securely through multiple providers.</p>
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <span class="text-xs text-gray-500">Last sync: 2 hours ago</span>
                <button class="text-sm text-blue-600 hover:text-blue-900 font-medium">Configure</button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Email Service</h3>
                        <p class="text-sm text-gray-500">Send automated emails</p>
                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Connected</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Send booking confirmations and notifications via email.</p>
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <span class="text-xs text-gray-500">Last sync: 30 min ago</span>
                <button class="text-sm text-blue-600 hover:text-blue-900 font-medium">Configure</button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">Channel Manager</h3>
                        <p class="text-sm text-gray-500">OTA connections</p>
                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Setup Required</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Sync availability with Booking.com, Expedia, and other OTAs.</p>
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <span class="text-xs text-gray-500">Not connected</span>
                <button class="text-sm font-medium" style="color: #bf311a;">Connect</button>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center mr-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900">SMS Service</h3>
                        <p class="text-sm text-gray-500">Text message notifications</p>
                    </div>
                </div>
                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Not Connected</span>
            </div>
            <p class="text-sm text-gray-600 mb-4">Send SMS notifications for confirmations and reminders.</p>
            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <span class="text-xs text-gray-500">Not connected</span>
                <button class="text-sm font-medium" style="color: #bf311a;">Connect</button>
            </div>
        </div>
    </div>
</div>
@endsection
