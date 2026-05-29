<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Driver\DriverLocationController;
use App\Http\Controllers\Api\V1\Driver\DriverOrderController;
use App\Http\Controllers\Api\V1\Driver\DriverOrderStatusController;
use App\Http\Controllers\Api\V1\OtpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Driver API Routes
|--------------------------------------------------------------------------
|
| These are sample API endpoints that the Driver Web App expects.
| Implement these endpoints with your actual business logic.
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

        // Cancel order
        Route::post('/orders/{orderId}/cancel', function (Request $request, string $orderId) {
            // TODO: Cancel order
            // Only allowed before acceptance
            // 1. Check order status
            // 2. Validate cancellation reason
            // 3. Request customer confirmation
            // 4. If returned, reassign to same store

            $request->validate([
                'reason' => 'required|string',
                'notes' => 'nullable|string',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Order cancelled successfully',
                'requires_customer_confirmation' => true,
            ]);
        });

        // Get driver profile
        Route::get('/profile', function (Request $request) {
            // TODO: Return authenticated driver's profile

            $driver = $request->user();

            return response()->json([
                'id' => $driver->id,
                'name' => $driver->name,
                'phone' => $driver->mobile,
                'email' => $driver->email,
                'vehicle_type' => $driver->vehicle_type ?? 'Bike',
                'vehicle_number' => $driver->vehicle_number ?? 'Not provided',
                'total_deliveries' => 127,
                'rating' => '4.8',
            ]);
        });

        // Update driver profile
        Route::put('/profile', function (Request $request) {
            // TODO: Update driver profile

            $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email',
                'vehicle_type' => 'sometimes|string',
                'vehicle_number' => 'sometimes|string',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
            ]);
        });

    });
});
