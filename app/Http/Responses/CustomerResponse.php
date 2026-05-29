<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;

final class CustomerResponse
{
    /**
     * Generate customer profile response.
     */
    public static function success(Customer $customer, string $message = 'Success', int $code = 200): JsonResponse
    {
        $data = [
            'id' => $customer->id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'full_name' => $customer->full_name,
            'email' => $customer->email,
            'mobile' => $customer->mobile,
            'dob' => $customer->dob?->format('Y-m-d'),
            'status' => $customer->status,
            'created_at' => $customer->created_at->format('Y-m-d H:i:s'),
        ];

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
