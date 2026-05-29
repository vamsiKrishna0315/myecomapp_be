<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;

final class CustomerAuthResponse
{
    /**
     * Generate customer login/registration response with token.
     */
    public static function success(Customer $customer, string $token, string $message = 'Success', int $code = 200): JsonResponse
    {
        $data = [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('customer-api')->factory()->getTTL() * 60,
            'customer' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'mobile' => $customer->mobile,
                'dob' => $customer->dob?->format('Y-m-d'),
                'status' => $customer->status,
            ],
            'cart' => [
                'items_count' => $customer->cartItems->count(),
                'total_amount' => $customer->cartItems->sum('total_price'),
                'items' => $customer->cartItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_cut_id' => $item->product_cut_id,
                        'quantity' => $item->quantity,
                        'quantity_unit' => $item->quantity_unit,
                        'weight' => $item->weight,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                        'special_instructions' => $item->special_instructions,
                        // 'status' => $item->status,
                    ];
                }),
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }
}
