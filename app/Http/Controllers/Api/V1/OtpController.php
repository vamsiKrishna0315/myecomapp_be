<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Services\DriverOtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class OtpController extends ResponseController
{
    protected $otpService;

    public function __construct(DriverOtpService $otpService)
    {
        $this->otpService = $otpService;
    }

    public function send(Request $request)
    {
        Log::info('OTP Send Request: ', $request->all());
        $result = $this->otpService->sendOtp($request->all());

        return response()->json($result);
    }

    public function verify(Request $request)
    {
        $result = $this->otpService->verifyOtp($request->all());

        return response()->json($result);
    }

    public function forgotPassword(Request $request)
    {
        $result = $this->otpService->forgotPassword($request->all());

        return response()->json($result);
    }
}
