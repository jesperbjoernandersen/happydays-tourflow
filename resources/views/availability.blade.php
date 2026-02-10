@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Availability</h1>
            <p class="text-gray-600">Check room availability and manage allocations</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-7 gap-4 text-center">
            <div class="font-medium text-gray-500 py-2">Mon</div>
            <div class="font-medium text-gray-500 py-2">Tue</div>
            <div class="font-medium text-gray-500 py-2">Wed</div>
            <div class="font-medium text-gray-500 py-2">Thu</div>
            <div class="font-medium text-gray-500 py-2">Fri</div>
            <div class="font-medium text-gray-500 py-2">Sat</div>
            <div class="font-medium text-gray-500 py-2">Sun</div>
            
            @for($i = 1; $i <= 28; $i++)
                <div class="border rounded-lg p-3 {{ $i >= 10 && $i <= 14 ? 'bg-green-100 border-green-300' : 'bg-white' }}">
                    <div class="text-sm font-medium text-gray-900">{{ $i }}</div>
                    <div class="text-xs text-gray-500">{{ $i >= 10 && $i <= 14 ? '23/45' : '45/45' }}</div>
                </div>
            @endfor
        </div>
    </div>
</div>
@endsection
