<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'HappyDays Tourflow') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Alpine.js for dropdown functionality -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="bg-gray-50 text-gray-900 font-sans antialiased" x-data="{ mobileMenuOpen: false }">
        <!-- Sticky Navigation -->
        <nav class="sticky top-0 z-50 bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="flex items-center space-x-2">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background-color: #fbba00;">
                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                            </div>
                            <span class="text-xl font-bold text-gray-900">HappyDays</span>
                        </a>
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:flex items-center space-x-1">
                        <!-- Home -->
                        <a href="/" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors">
                            Home
                        </a>

                        <!-- Booking Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @mouseleave="open = false">
                            <button 
                                @mouseenter="open = true"
                                @click="open = !open"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors flex items-center space-x-1"
                            >
                                <span>Booking</span>
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div 
                                x-show="open"
                                x-transition:enter="dropdown-enter"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="absolute left-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2"
                                style="display: none;"
                            >
                                <a href="/booking" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    New Booking
                                </a>
                                <a href="/reservations" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Reservations
                                </a>
                                <a href="/availability" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Availability
                                </a>
                            </div>
                        </div>

                        <!-- Hotels Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @mouseleave="open = false">
                            <button 
                                @mouseenter="open = true"
                                @click="open = !open"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors flex items-center space-x-1"
                            >
                                <span>Hotels</span>
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div 
                                x-show="open"
                                x-transition:enter="dropdown-enter"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="absolute left-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2"
                                style="display: none;"
                            >
                                <a href="/hotels" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Hotel List
                                </a>
                                <a href="/room-types" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Room Types
                                </a>
                                <a href="/policies" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Policies
                                </a>
                            </div>
                        </div>

                        <!-- Pricing Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @mouseleave="open = false">
                            <button 
                                @mouseenter="open = true"
                                @click="open = !open"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors flex items-center space-x-1"
                            >
                                <span>Pricing</span>
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div 
                                x-show="open"
                                x-transition:enter="dropdown-enter"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="absolute left-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2"
                                style="display: none;"
                            >
                                <a href="/pricing-rules" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Pricing Rules
                                </a>
                                <a href="/discounts" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Discounts
                                </a>
                                <a href="/offers" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Offers
                                </a>
                            </div>
                        </div>

                        <!-- Reports Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @mouseleave="open = false">
                            <button 
                                @mouseenter="open = true"
                                @click="open = !open"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors flex items-center space-x-1"
                            >
                                <span>Reports</span>
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div 
                                x-show="open"
                                x-transition:enter="dropdown-enter"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="absolute left-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2"
                                style="display: none;"
                            >
                                <a href="/analytics" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Analytics
                                </a>
                                <a href="/bookings-overview" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Bookings Overview
                                </a>
                                <a href="/revenue" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Revenue
                                </a>
                            </div>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="relative" x-data="{ open: false }" @click.outside="open = false" @mouseleave="open = false">
                            <button 
                                @mouseenter="open = true"
                                @click="open = !open"
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors flex items-center space-x-1"
                            >
                                <span>Settings</span>
                                <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div 
                                x-show="open"
                                x-transition:enter="dropdown-enter"
                                x-transition:leave="transition ease-in duration-150"
                                x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="absolute left-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2"
                                style="display: none;"
                            >
                                <a href="/settings/general" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    General
                                </a>
                                <a href="/settings/users" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Users
                                </a>
                                <a href="/settings/integrations" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                    Integrations
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Right side actions -->
                    <div class="hidden md:flex items-center space-x-4">
                        <button class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors" style="background-color: #bf311a;">
                            Book Now
                        </button>
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button 
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="inline-flex items-center justify-center p-2 rounded-lg text-gray-700 hover:text-gray-900 hover:bg-gray-100 transition-colors"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                                <path x-show="mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" style="display: none;"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div 
                x-show="mobileMenuOpen"
                x-transition:enter="dropdown-enter"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="md:hidden bg-white border-t border-gray-200"
                style="display: none;"
            >
                <div class="px-4 py-3 space-y-1">
                    <a href="/" class="block px-4 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                        Home
                    </a>
                    
                    <!-- Mobile Booking -->
                    <div x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            class="w-full flex items-center justify-between px-4 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
                        >
                            <span>Booking</span>
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" class="pl-4 mt-1 space-y-1" style="display: none;">
                            <a href="/booking" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                New Booking
                            </a>
                            <a href="/reservations" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Reservations
                            </a>
                            <a href="/availability" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Availability
                            </a>
                        </div>
                    </div>

                    <!-- Mobile Hotels -->
                    <div x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            class="w-full flex items-center justify-between px-4 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
                        >
                            <span>Hotels</span>
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" class="pl-4 mt-1 space-y-1" style="display: none;">
                            <a href="/hotels" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Hotel List
                            </a>
                            <a href="/room-types" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Room Types
                            </a>
                            <a href="/policies" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Policies
                            </a>
                        </div>
                    </div>

                    <!-- Mobile Pricing -->
                    <div x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            class="w-full flex items-center justify-between px-4 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
                        >
                            <span>Pricing</span>
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" class="pl-4 mt-1 space-y-1" style="display: none;">
                            <a href="/pricing-rules" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Pricing Rules
                            </a>
                            <a href="/discounts" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Discounts
                            </a>
                            <a href="/offers" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Offers
                            </a>
                        </div>
                    </div>

                    <!-- Mobile Reports -->
                    <div x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            class="w-full flex items-center justify-between px-4 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
                        >
                            <span>Reports</span>
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" class="pl-4 mt-1 space-y-1" style="display: none;">
                            <a href="/analytics" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Analytics
                            </a>
                            <a href="/bookings-overview" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Bookings Overview
                            </a>
                            <a href="/revenue" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Revenue
                            </a>
                        </div>
                    </div>

                    <!-- Mobile Settings -->
                    <div x-data="{ open: false }">
                        <button 
                            @click="open = !open"
                            class="w-full flex items-center justify-between px-4 py-2 text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg"
                        >
                            <span>Settings</span>
                            <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" class="pl-4 mt-1 space-y-1" style="display: none;">
                            <a href="/settings/general" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                General
                            </a>
                            <a href="/settings/users" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Users
                            </a>
                            <a href="/settings/integrations" class="block px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                                Integrations
                            </a>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-gray-200">
                        <button class="w-full px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors" style="background-color: #bf311a;">
                            Book Now
                        </button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Brand Info -->
                    <div class="col-span-1">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: #fbba00;">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                                </svg>
                            </div>
                            <span class="text-lg font-bold text-gray-900">HappyDays</span>
                        </div>
                        <p class="text-sm text-gray-600">
                            Your trusted partner for seamless tour and hotel booking experiences.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div class="col-span-1">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Quick Links</h3>
                        <ul class="space-y-2">
                            <li><a href="/booking" class="text-sm text-gray-600 hover:text-gray-900">New Booking</a></li>
                            <li><a href="/hotels" class="text-sm text-gray-600 hover:text-gray-900">Hotels</a></li>
                            <li><a href="/offers" class="text-sm text-gray-600 hover:text-gray-900">Special Offers</a></li>
                            <li><a href="/analytics" class="text-sm text-gray-600 hover:text-gray-900">Analytics</a></li>
                        </ul>
                    </div>

                    <!-- Support -->
                    <div class="col-span-1">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Support</h3>
                        <ul class="space-y-2">
                            <li><a href="/help" class="text-sm text-gray-600 hover:text-gray-900">Help Center</a></li>
                            <li><a href="/contact" class="text-sm text-gray-600 hover:text-gray-900">Contact Us</a></li>
                            <li><a href="/faq" class="text-sm text-gray-600 hover:text-gray-900">FAQ</a></li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div class="col-span-1">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Contact</h3>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li>Email: info@happydays.com</li>
                            <li>Phone: +1 (555) 123-4567</li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex flex-col md:flex-row justify-between items-center">
                        <p class="text-sm text-gray-600">
                            &copy; {{ date('Y') }} HappyDays Tourflow. All rights reserved.
                        </p>
                        <div class="flex space-x-4 mt-4 md:mt-0">
                            <a href="/privacy" class="text-sm text-gray-600 hover:text-gray-900">Privacy Policy</a>
                            <a href="/terms" class="text-sm text-gray-600 hover:text-gray-900">Terms of Service</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </body>
</html>
