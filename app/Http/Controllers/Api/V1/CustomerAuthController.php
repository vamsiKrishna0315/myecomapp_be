<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Customer;
use App\Models\Orders;
use Illuminate\Http\Request;

final class CustomerAuthController extends ResponseController
{
    /**
     * Get authenticated customer dashboard data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard(Request $request)
    {
        // Get authenticated customer from JWT token
        $customer = auth('customer-api')->user();

        // Get customer statistics
        $totalOrders = Orders::where('customer_id', $customer->id)->count();
        $pendingOrders = Orders::where('customer_id', $customer->id)
            ->whereHas('orderStatusTracking', function ($query) {
                $query->where('status', 'pending');
            })
            ->count();

        $completedOrders = Orders::where('customer_id', $customer->id)
            ->whereHas('orderStatusTracking', function ($query) {
                $query->where('status', 'delivered');
            })
            ->count();

        // Get recent orders
        $recentOrders = Orders::where('customer_id', $customer->id)
            ->with(['orderStatusTracking' => function ($query) {
                $query->latest()->first();
            }])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number ?? "ORD-{$order->id}",
                    'total_amount' => $order->total_amount,
                    'status' => $order->orderStatusTracking?->first()?->status ?? 'pending',
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                ];
            });

        // Get customer addresses
        $addresses = $customer->addresses()
            ->where('status', 1)
            ->get()
            ->map(function ($address) {
                return [
                    'id' => $address->id,
                    'type' => $address->type,
                    'address_line_1' => $address->address_line_1,
                    'address_line_2' => $address->address_line_2,
                    'city' => $address->city,
                    'state' => $address->state,
                    'postal_code' => $address->postal_code,
                    'is_default' => $address->is_default,
                ];
            });

        $data = [
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
            'dashboard' => [
                'total_orders' => $totalOrders,
                'pending_orders' => $pendingOrders,
                'completed_orders' => $completedOrders,
                'total_addresses' => $addresses->count(),
            ],
            'recent_orders' => $recentOrders,
            'addresses' => $addresses,
        ];

        return $this->returnResponse($data, 'Dashboard data retrieved successfully', 200);
    }

    /**
     * Refresh JWT token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $token = auth('customer-api')->refresh();

        $data = [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('customer-api')->factory()->getTTL() * 60,
        ];

        return $this->returnResponse($data, 'Token refreshed successfully', 200);
    }

    /**
     * Logout (invalidate token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('customer-api')->logout();

        return $this->returnResponse([], 'Successfully logged out', 200);
    }
}
