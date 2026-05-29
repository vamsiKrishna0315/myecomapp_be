<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Responses\CustomerAuthResponse;
use App\Models\Customer;

final class LoginController extends ResponseController
{
    /**
     * Login and generate JWT token for customer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->filled('email')
            ? ['email' => $request->email, 'password' => $request->password]
            : ['mobile' => $request->mobile, 'password' => $request->password];

        if (! $token = auth('customer-api')->attempt($credentials)) {
            return $this->sendError('Invalid credentials', 401);
        }
        $customer = auth('customer-api')->user();
        $customer = Customer::with(['addresses', 'cartItems'])->find($customer['id']);
        if (! $customer->isActive()) {
            auth('customer-api')->logout();

            return $this->sendError('Your account is inactive. Please contact support.', 403);
        }

        return CustomerAuthResponse::success($customer, $token, 'Login successful', 200);
    }
}
