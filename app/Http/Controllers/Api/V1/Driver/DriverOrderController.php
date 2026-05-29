<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Services\DriverOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class DriverOrderController extends Controller
{
    public function __construct(private readonly DriverOrderService $driverOrderService) {}

    public function index(Request $request): JsonResponse
    {
        $driver = $request->user();

        return response()->json(
            $this->driverOrderService->getDashboardOrders($driver)
        );
    }

    public function show(Request $request, string $orderId): JsonResponse
    {
        $driver = $request->user();
        $order = $this->driverOrderService->getOrderDetails($driver, $orderId);

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json($order);
    }
}
