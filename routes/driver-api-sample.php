<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Driver\DriverLocationController;
use App\Http\Controllers\Api\V1\Driver\DriverOrderCancellationController;
use App\Http\Controllers\Api\V1\Driver\DriverOrderController;
use App\Http\Controllers\Api\V1\Driver\DriverOrderStatusController;
use App\Http\Controllers\Api\V1\Driver\DriverProfileController;
use App\Http\Controllers\Api\V1\Driver\DriverProofOfDeliveryController;
use App\Http\Controllers\Api\V1\OtpController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Driver API Routes
|--------------------------------------------------------------------------
|
| API endpoints consumed by the Driver Web App (resources/views/driver).
|
*/

Route::prefix('driver')->group(function () {

    // Authentication Routes (Public)

    Route::post('/send-otp', [OtpController::class, 'send']);
    Route::post('/verify-otp', [OtpController::class, 'verify']);
    Route::post('/forgot-password', [OtpController::class, 'forgotPassword']);

    // Protected Routes (Require Authentication)
    Route::middleware('auth:driver-api')->group(function () {

        // Get all orders grouped by status
        Route::get('/orders', [DriverOrderController::class, 'index']);

        // Get single order details
        Route::get('/orders/{orderId}', [DriverOrderController::class, 'show']);

        // Live map payload for a single order
        Route::get('/orders/{orderId}/map', [DriverLocationController::class, 'showMap']);

        Route::get('/orders/{orderId}/statuses', [DriverOrderStatusController::class, 'index']);

        // Update driver current location and capture trip breadcrumbs
        Route::post('/location', [DriverLocationController::class, 'update']);

        Route::put('/orders/{orderId}/status', [DriverOrderStatusController::class, 'update']);

        // Cancel order (only allowed before driver acceptance)
        Route::post('/orders/{orderId}/cancel', DriverOrderCancellationController::class);

        // Upload proof-of-delivery photo
        Route::post('/orders/{orderId}/proof-of-delivery', DriverProofOfDeliveryController::class);

        // Get driver profile
        Route::get('/profile', [DriverProfileController::class, 'show']);

        // Update driver profile
        Route::put('/profile', [DriverProfileController::class, 'update']);

    });
});
