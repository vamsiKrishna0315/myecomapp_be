<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Actions\Order\CreateOrder;
use App\Http\Controllers\Api\V1\ResponseController;
use App\Http\Requests\Api\V1\Order\StoreOrderRequest;
use App\Models\Orders;
use App\Services\Payments\PaymentManager;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

final class OrderController extends ResponseController
{
    /**
     * Get a list of orders.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $searchParam = $request->input('search_param');
        $sort = $request->input('sort', 'desc');
        $groupByStatus = $request->boolean('orders_by_status');

        // dd(auth('customer-api')->id());

        $ordersQuery = Orders::with(['customer', 'items', 'billingAddress', 'deliveryAddress', 'driver', 'review', 'currentStatus'])
            ->where('customer_id', auth('customer-api')->id())
            ->where('status', 1)
            ->when($searchParam, function ($query, $searchParam) {
                return $query->where(function ($q) use ($searchParam) {
                    $q->where('order_number', 'like', '%'.$searchParam.'%')
                        ->orWhereDate('created_at', $searchParam);
                });
            })
            ->orderBy('created_at', $sort === 'asc' ? 'asc' : 'desc');

        if ($groupByStatus) {
            $orders = $ordersQuery->get();
            $groupedOrders = $orders->groupBy(function ($order) {
                return $order->currentStatus ? $order->currentStatus->name : 'No Status';
            })->map(function ($group) {
                return $group->values();
            });

            return $this->returnResponse($groupedOrders, 'Orders retrieved successfully.');
        }

        $orders = $ordersQuery->skip($offset)->take($limit)->get();

        if ($orders->isEmpty()) {
            return $this->returnResponse([], 'No orders found.', 404);
        }

        return $this->returnResponse($orders, 'Orders retrieved successfully.');
    }

    /**
     * Get a single order by UUID.
     */
    public function fetchOne(string $uuid): JsonResponse
    {
        $order = Orders::with(['customer', 'items', 'billingAddress', 'deliveryAddress', 'driver', 'review', 'currentStatus'])
            ->where('customer_id', auth('customer-api')->id())
            ->where('uuid', $uuid)
            ->where('status', 1)
            ->first();

        if (! $order) {
            return $this->returnResponse([], 'Order not found.', 404);
        }

        return $this->returnResponse($order, 'Order retrieved successfully.');
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $customer = auth('customer-api')->user();
            Log::info("Creating order for customer ID: {$customer->id}");

            $orderData = array_merge($request->validated(), [
                'customer_id' => $customer->id,
            ]);

            $provider = $request->validated('provider', 'razorpay');
            $gateway = PaymentManager::gateway((string) $provider);

            // Create payment order first to ensure payment is possible
            //$payment = $gateway->createOrderFromData($orderData);

            Log::info('Payment order creation initiated', [
                'provider' => $provider,
                'order_data' => $orderData,
            ]);
            // Only create database order if payment creation succeeds
            $order = app(CreateOrder::class)->executeInTransaction($orderData);

            // Update payment order with actual order ID for Razorpay
            if ($provider === 'razorpay' && !empty($orderData['razorpay_order_id'])) {
                Log::info('Frontend Razorpay Order ID', [
                    'razorpay_order_id' => $orderData['razorpay_order_id'] ?? null,
                ]);

                $order->update([
                    'razorpay_order_id' => $orderData['razorpay_order_id'],
                ]);
            }

            return $this->returnResponse([
                'order' => $order->fresh(),
                'payment_provider' => $provider,
            ], 'Order created successfully.', 201);

        } catch (InvalidArgumentException $e) {
            return $this->sendError($e->getMessage(), 422);
        } catch (Exception $e) {
            return $this->sendError('Failed to create order: '.$e->getMessage(), 500);
        }
    }
}
