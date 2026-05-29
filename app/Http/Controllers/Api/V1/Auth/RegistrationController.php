<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Customer\CreateCustomer;
use App\Http\Controllers\Api\V1\ResponseController;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Responses\CustomerAuthResponse;

final class RegistrationController extends ResponseController
{
    /**
     * Register a new customer
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $customer = (new CreateCustomer())->execute($request->validated());
        $token = auth('customer-api')->login($customer);

        return CustomerAuthResponse::success($customer, $token, 'Registration successful', 201);
    }
}
