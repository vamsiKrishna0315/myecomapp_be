<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Actions\Customer\UpdateCustomer;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Responses\CustomerResponse;

final class CustomerProfileController extends ResponseController
{
    /**
     * Get customer profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $customer = auth('customer-api')->user();

        return CustomerResponse::success($customer, 'Profile data retrieved successfully', 200);
    }

    /**
     * Update customer profile
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $customer = auth('customer-api')->user();
        $data = array_merge($request->validated(), ['customer' => $customer]);
        $updatedCustomer = (new UpdateCustomer())->execute($data);

        return CustomerResponse::success($updatedCustomer, 'Profile updated successfully', 200);
    }
}
