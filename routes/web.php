<?php

declare(strict_types=1);

use App\Http\Controllers\DriverController;
use App\Http\Controllers\GooglePlacesController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

// Redirect to Filament admin login
Route::get('/login', fn () => redirect('/admin/login'))->name('login');
Route::get('/register', fn () => redirect('/admin/register'))->name('register');

Route::get('/', fn (): View => view('welcome'));

// Google Places API routes
Route::prefix('api/google-places')->group(function () {
    Route::get('/test', [GooglePlacesController::class, 'test']);
    Route::get('/search', [GooglePlacesController::class, 'search']);
    Route::get('/details', [GooglePlacesController::class, 'details']);
});

// Driver Web App Routes
Route::prefix('driver')->name('driver.')->group(function () {
    // Auth routes
    Route::get('/', [DriverController::class, 'login'])->name('login');
    Route::get('/otp', [DriverController::class, 'otp'])->name('otp');
    Route::get('/forgot-password', [DriverController::class, 'forgotPassword'])->name('forgot-password');

    // App routes (protected - middleware can be added later)
    Route::get('/dashboard', [DriverController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders/{orderId}', [DriverController::class, 'orderDetail'])->name('order-detail');
    Route::get('/orders/{orderId}/map', [DriverController::class, 'orderMap'])->name('order-map');
    Route::get('/profile', [DriverController::class, 'profile'])->name('profile');
});




Route::get('/logs', function () {
    $logFile = storage_path('logs/laravel.log');

    if (! File::exists($logFile)) {
        return response('Log file not found.');
    }

    return response(File::get($logFile))
        ->header('Content-Type', 'text/plain');
});

Route::get('/logs/clear', function () {
    $logFile = storage_path('logs/laravel.log');

    File::put($logFile, '');

    return 'Logs cleared successfully.';
});

Route::get('/webhook-test-log', function () {
    return response(
        file_exists(storage_path('logs/webhook-test.log'))
            ? file_get_contents(storage_path('logs/webhook-test.log'))
            : 'No webhook log found.'
    )->header('Content-Type', 'text/plain');
});
