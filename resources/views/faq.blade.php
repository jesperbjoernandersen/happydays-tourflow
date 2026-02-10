@extends('layouts.app')

@section('content')
<div class="p-6 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Frequently Asked Questions</h1>
        <p class="text-gray-600">Find answers to common questions</p>
    </div>

    <div class="space-y-4">
        <div class="bg-white rounded-lg shadow">
            <button class="w-full px-6 py-4 text-left flex items-center justify-between">
                <span class="font-medium text-gray-900">How do I create a new booking?</span>
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        <div class="bg-white rounded-lg shadow">
            <button class="w-full px-6 py-4 text-left flex items-center justify-between">
                <span class="font-medium text-gray-900">How do I manage room availability?</span>
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        <div class="bg-white rounded-lg shadow">
            <button class="w-full px-6 py-4 text-left flex items-center justify-between">
                <span class="font-medium text-gray-900">How do I set up pricing rules?</span>
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>

        <div class="bg-white rounded-lg shadow">
            <button class="w-full px-6 py-4 text-left flex items-center justify-between">
                <span class="font-medium text-gray-900">How do I run reports?</span>
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </div>
    </div>
</div>
@endsection
