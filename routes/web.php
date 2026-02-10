<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// Booking Routes
Route::get('/booking', function () {
    return view('booking');
})->name('booking');

// Hotel Routes
Route::get('/hotels', function () {
    return view('hotels');
})->name('hotels');

Route::get('/room-types', function () {
    return view('room-types');
})->name('room-types');

Route::get('/policies', function () {
    return view('policies');
})->name('policies');

// Pricing Routes
Route::get('/pricing-rules', function () {
    return view('pricing-rules');
})->name('pricing-rules');

Route::get('/discounts', function () {
    return view('discounts');
})->name('discounts');

Route::get('/offers', function () {
    return view('offers');
})->name('offers');

// Reports Routes
Route::get('/analytics', function () {
    return view('analytics');
})->name('analytics');

Route::get('/bookings-overview', function () {
    return view('bookings-overview');
})->name('bookings-overview');

Route::get('/revenue', function () {
    return view('revenue');
})->name('revenue');

// Settings Routes
Route::prefix('settings')->group(function () {
    Route::get('/general', function () {
        return view('settings.general');
    })->name('settings.general');
    
    Route::get('/users', function () {
        return view('settings.users');
    })->name('settings.users');
    
    Route::get('/integrations', function () {
        return view('settings.integrations');
    })->name('settings.integrations');
});

// Additional Pages
Route::get('/reservations', function () {
    return view('reservations');
})->name('reservations');

Route::get('/availability', function () {
    return view('availability');
})->name('availability');

// Placeholder views for routes that don't exist yet
view()->composer('*', function ($view) {
    $views = ['booking', 'hotels', 'room-types', 'policies', 'pricing-rules', 'discounts', 'offers', 'analytics', 'bookings-overview', 'revenue', 'reservations', 'availability', 'settings.general', 'settings.users', 'settings.integrations', 'help', 'contact', 'faq', 'privacy', 'terms'];
    foreach ($views as $name) {
        if (!view()->exists($name) && !view()->exists(str_replace('.', '/', $name))) {
            view()->exists($name) || view()->exists(str_replace('.', '/', $name)) || 
                view()->share('_placeholder_' . str_replace(['/', '.'], '_', $name), true);
        }
    }
});
