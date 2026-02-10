@extends('layouts.app')

@section('content')
<div class="p-6 max-w-4xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Contact Us</h1>
        <p class="text-gray-600">Get in touch with our support team</p>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                <select class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <option>General Inquiry</option>
                    <option>Technical Support</option>
                    <option>Billing Question</option>
                    <option>Feature Request</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="Describe your issue or question..."></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="your@email.com">
            </div>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white rounded-lg hover:opacity-90 transition-colors" style="background-color: #bf311a;">
                Send Message
            </button>
        </form>
    </div>
</div>
@endsection
