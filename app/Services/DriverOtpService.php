<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Otps;
use Carbon\Carbon;
use App\Jobs\SendWhatsAppOtpJob;
use App\Jobs\SendWhatsAppWelcomeJob;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

final class DriverOtpService
{
    public function sendOtp(array $data): array
    {
        // 1. Validate phone number
        $phone = $data['phone'] ?? null;
        if (! $phone) {
            return [
                'success' => false,
                'message' => 'Phone number is required',
            ];
        }

        // 2. Generate 6-digit OTP
        $otp = rand(100000, 999999);

        // 3. Store OTP in DB with expiry (5 min)
        Otps::updateOrCreate(
            ['phone' => $phone],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(5),
            ]
        );

        SendWhatsAppOtpJob::dispatch(
             recipientPhone: '91' . ltrim($phone, '0'),
             otp: (string) $otp,
        );

        $response = [
            'success' => true,
            'message' => 'OTP sent successfully',
        ];

        if (! App::environment('production')) {
            $response['otp'] = $otp;
        }

        return $response;
    }

    public function verifyOtp(array $data): array
    {
        Log::info('OTP Verify Request: ', $data);
        $phone = $data['phone'] ?? null;
        $otp = $data['otp'] ?? null;
        $isCustomer = $data['is_customer'] ?? false;

        if (! $phone || ! $otp) {
            return [
                'success' => false,
                'message' => 'Phone and OTP are required',
            ];
        }

        // 1️⃣ Validate OTP
        $otpRecord = Otps::where('phone', $phone)
            ->where('otp', $otp)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (! $otpRecord) {
            return [
                'success' => false,
                'message' => 'Invalid or expired OTP',
            ];
        }

        // OTP used → delete
        $otpRecord->delete();

        // 2️⃣ CUSTOMER LOGIN
        if ($isCustomer === true) {

            $customer = Customer::where('mobile', $phone)->first();

            if (! $customer) {
                // create a new customer account
                $customer = Customer::create([
                    'first_name' => null,
                    'last_name' => null,
                    'mobile' => $phone,
                    'email' => null, // Use null instead of empty string for unique constraint
                    'password' => null,
                    'status' => 1,
                ]);

                SendWhatsAppWelcomeJob::dispatch(
                    recipientPhone: '91' . $customer->mobile,
                );

                return [
                    'success' => true,
                    'message' => 'Account created. Please complete your profile.',
                    'customer' => $customer,
                ];
            }

            if (! $customer->isActive()) {
                return [
                    'success' => false,
                    'message' => 'Your account is inactive. Please contact support.',
                ];
            }

            // Login via guard (password-less)
            $token = auth('customer-api')->login($customer);

            $customer = Customer::with(['addresses', 'cartItems'])->find($customer->id);

            return [
                'success' => true,
                'message' => 'Login successful',
                'token' => $token,
                'customer' => $customer,
            ];
        }

        // 3️⃣ DRIVER LOGIN
        $driver = Driver::where('mobile', $phone)->first();

        if (! $driver) {
            return [
                'success' => false,
                'message' => 'Driver not found',
            ];
        }

        $token = auth('driver-api')->login($driver);

        $totalDeliveries = method_exists($driver, 'deliveries')
            ? $driver->deliveries()->count()
            : 0;

        return [
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'phone' => $driver->mobile,
                'email' => $driver->email,
                'vehicle_type' => $driver->vehicle_type ?? 'Bike',
                'vehicle_number' => $driver->vehicle_number ?? '',
                'total_deliveries' => $totalDeliveries,
                'rating' => $driver->rating ?? '4.8',
            ],
        ];
    }

    public function forgotPassword(array $data): array
    {
        // TODO: Implement password reset logic
        // 1. Validate phone number
        // 2. Check if driver exists
        // 3. Send reset OTP
        return [
            'success' => true,
            'message' => 'Reset OTP sent successfully',
        ];
    }
}
