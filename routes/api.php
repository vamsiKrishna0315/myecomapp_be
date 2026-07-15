<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\RegistrationController;
use App\Http\Controllers\Api\V1\CustomerAddressController;
use App\Http\Controllers\Api\V1\CustomerAuthController;
use App\Http\Controllers\Api\V1\CustomerProfileController;
use App\Http\Controllers\Api\V1\NearbyVendorController;
use App\Http\Controllers\Api\V1\OtpController;
use App\Http\Controllers\Api\V1\VendorGamificationController;
use App\Http\Controllers\Api\V1\WhatsAppController;
use App\Http\Controllers\Api\V1\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

include __DIR__.'/driver-api-sample.php';

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1/customer')->group(function () {

    Route::post('/send-otp', [OtpController::class, 'send']);
    Route::post('/verify-otp', [OtpController::class, 'verify']);
    Route::post('/forgot-password', [OtpController::class, 'forgotPassword']);

    Route::post('/register', [RegistrationController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login'])->name('customer.login');
    Route::get('/products', [App\Http\Controllers\Api\V1\Products\ProductsController::class, 'index']);
    Route::post('/category', [App\Http\Controllers\Api\V1\Category\CategoryController::class, 'index']);
    Route::get('/order-statuses', [App\Http\Controllers\Api\V1\Order\OrderStatusesController::class, 'index']);
    Route::get('/site/site-data', [App\Http\Controllers\Api\V1\Site\SiteController::class, 'getAllSiteData']);
    Route::get('/site/store-contact', [App\Http\Controllers\Api\V1\Site\StoreContactController::class, 'getStoreContact']);
    Route::post('/site/contact-inquiry', [App\Http\Controllers\Api\V1\Site\ContactInquiryController::class, 'store']);
    Route::post('/nearby-vendors', NearbyVendorController::class);

    Route::get('/products/{product}', [App\Http\Controllers\Api\V1\Products\ProductsController::class, 'fetchOne']);

    // Referral coupon eligibility check - public so guests opening a share link can be checked pre-login
    Route::get('/referral/check', [App\Http\Controllers\Api\V1\Coupon\ReferralController::class, 'check'])->middleware('throttle:20,1');

    Route::middleware(['auth:customer-api', 'throttle:60,1'])->group(function () {

        // Authentication
        Route::post('/logout', [CustomerAuthController::class, 'logout']);
        Route::post('/refresh', [CustomerAuthController::class, 'refresh']);

        // Profile
        Route::get('/profile', [CustomerProfileController::class, 'profile']);
        Route::put('/profile', [CustomerProfileController::class, 'updateProfile']);

        // Addresses
        Route::get('/addresses', [CustomerAddressController::class, 'index']);
        Route::post('/address', [CustomerAddressController::class, 'store']);
        Route::patch('/address/{address}', [CustomerAddressController::class, 'update']);

        // Contact Inquiries
        Route::get('/contact-inquiries', [App\Http\Controllers\Api\V1\Site\ContactInquiryController::class, 'getCustomerInquiries']);

        // Dashboard
        Route::get('/dashboard', [CustomerAuthController::class, 'dashboard']);

        // Get Order
        Route::get('/orders', [App\Http\Controllers\Api\V1\Order\OrderController::class, 'index']);
        Route::post('/order', [App\Http\Controllers\Api\V1\Order\OrderController::class, 'store']);
        Route::get('/order/{uuid}', [App\Http\Controllers\Api\V1\Order\OrderController::class, 'fetchOne']);

        // Order Status Tracking
        Route::get('/orders/{uuid}/track', [App\Http\Controllers\Api\V1\Order\OrderTrackingDetailsController::class, 'getTrackingDetails']);
        Route::put('/order/{uuid}/cancel', [App\Http\Controllers\Api\V1\Order\OrderCancellationController::class, '__invoke']);
        // Route::put('/orders/{uuid}/return', [\App\Http\Controllers\Api\V1\Order\OrderController::class, 'returnOrder']);

        // Order Status Management
        Route::post('/order-statuses/{uuid?}/remaining', [App\Http\Controllers\Api\V1\Order\OrderStatusesController::class, 'getRestOfTheStatuses']);
        Route::put('/order/{uuid}/status/next-step', [App\Http\Controllers\Api\V1\Order\OrderStatusesController::class, 'nextStep']);
        Route::patch('/order/{uuid}/status', [App\Http\Controllers\Api\V1\Order\OrderStatusesController::class, 'updateStatus']);
        // Cart
        Route::get('/cart', [App\Http\Controllers\Api\V1\Cart\CartController::class, 'index']);
        Route::post('/cart', [App\Http\Controllers\Api\V1\Cart\CartController::class, 'store']);
        Route::patch('/cart/{id}', [App\Http\Controllers\Api\V1\Cart\CartController::class, 'update']);
        Route::delete('/cart/{id}', [App\Http\Controllers\Api\V1\Cart\CartController::class, 'delete']);

        // Coupon
        Route::post('/coupon/validate', [App\Http\Controllers\Api\V1\Coupon\CouponController::class, 'validateCoupon'])->middleware('throttle:10,1');

        // Referral coupon
        Route::post('/referral/generate', [App\Http\Controllers\Api\V1\Coupon\ReferralController::class, 'generate']);
        Route::get('/referral/analytics', [App\Http\Controllers\Api\V1\Coupon\ReferralController::class, 'analytics']);

        // Payment
        Route::post('/payment/verify', [App\Http\Controllers\Api\V1\PaymentController::class, 'verify']);

    });
});

Route::prefix('v1/whatsapp')->group(function () {
    Route::post('/hello-world', [WhatsAppController::class, 'sendHelloWorld']);
    Route::post('/otp/send', [WhatsAppController::class, 'queueOtp']);

    // New routes
    Route::get('/webhook', [WhatsAppWebhookController::class, 'verify']);
    Route::post('/webhook', [WhatsAppWebhookController::class, 'receive']);
});

// V1 API Routes for Store Vendors - Gamification
Route::prefix('v1/vendor')->middleware(['auth:api', 'throttle:60,1'])->group(function () {

    // Gamification endpoints
    Route::get('/gamification/profile', [VendorGamificationController::class, 'profile']);
    Route::get('/gamification/leaderboard', [VendorGamificationController::class, 'leaderboard']);
    Route::get('/gamification/points', [VendorGamificationController::class, 'pointHistory']);
    Route::get('/gamification/badges', [VendorGamificationController::class, 'badges']);
});

// ...existing code...
