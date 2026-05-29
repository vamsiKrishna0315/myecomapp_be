<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Services\DriverOrderStatusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class DriverOrderStatusController extends ResponseController
{
    public function __construct(private readonly DriverOrderStatusService $driverOrderStatusService) {}

    public function index(Request $request, string $orderId): JsonResponse
    {
        try {
            $payload = $this->driverOrderStatusService->getAvailableStatuses($request->user(), $orderId);
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            return $this->sendError(
                collect($errors)->flatten()->first() ?? 'Unable to load order statuses.',
                422,
                $errors,
            );
        }

        if (! $payload) {
            return $this->sendError('Order not found.', 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order statuses retrieved successfully',
            'data' => $payload,
        ]);
    }

    public function update(Request $request, string $orderId): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|string|in:accepted,driver_accepted,driver_at_store,picked_up,driver_picked_up,out_for_delivery,driver_nearby,driver_reached,delivered',
        ]);

        try {
            $order = $this->driverOrderStatusService->updateStatus(
                $request->user(),
                $orderId,
                (string) $validated['status'],
            );
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            return $this->sendError(
                collect($errors)->flatten()->first() ?? 'Unable to update order status.',
                422,
                $errors,
            );
        }

        if (! $order) {
            return $this->sendError('Order not found.', 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'order' => $order,
        ]);
    }
}
