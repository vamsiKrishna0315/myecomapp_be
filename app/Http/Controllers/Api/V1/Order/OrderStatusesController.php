<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Models\Orders;
use App\Models\OrderStatuses;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class OrderStatusesController extends ResponseController
{
    /**
     * Get a list of order statuses.
     */
    public function index(): JsonResponse
    {
        $orderStatuses = OrderStatuses::select('id', 'code', 'name', 'color', 'description', 'sequence', 'is_final', 'is_cancellable', 'display_order')
            ->where('status', 1)
            ->orderBy('sequence', 'asc')
            ->get()
            ->map(function ($status) {
                return [
                    'id' => $status->id,
                    'code' => $status->code,
                    'name' => $status->name,
                    'color' => $status->color,
                    'description' => $status->description,
                    'sequence' => $status->sequence,
                    'is_final' => (bool) $status->is_final,
                    'is_cancellable' => (bool) $status->is_cancellable,
                    'display_order' => $status->display_order,
                ];
            });

        return $this->returnResponse($orderStatuses, 'Order statuses retrieved successfully.');
    }

    public function getRestOfTheStatuses(Request $request, ?string $uuid = null): JsonResponse
    {
        try {
            $currentStatusCode = $request->input('current_status_code');

            if ($uuid) {
                $validator = \Illuminate\Support\Facades\Validator::make(['uuid' => $uuid], [
                    'uuid' => 'uuid',
                ]);

                if ($validator->fails()) {
                    return $this->sendError('Invalid UUID format provided.', 400);
                }
            }

            if (! $currentStatusCode) {
                if (! $uuid) {
                    return $this->sendError('Either current_status_code or order UUID is required.', 400);
                }

                $order = Orders::select('current_status_code')
                    ->where('uuid', $uuid)
                    ->where('customer_id', auth('customer-api')->id())
                    ->where('status', 1)
                    ->first();

                if (! $order) {
                    return $this->sendError('Order not found or access denied.', 404);
                }

                $currentStatusCode = $order->current_status_code;

                if (! $currentStatusCode) {
                    return $this->sendError('Order does not have a current status.', 400);
                }
            }

            $currentStatus = OrderStatuses::where('code', $currentStatusCode)
                ->where('status', 1)
                ->first();

            if (! $currentStatus) {
                return $this->sendError('Invalid status code provided.', 404);
            }

            // Get negative status codes from model
            $negativeStatusCodes = OrderStatuses::negativeStatusCodes();

            // Get all completed statuses (sequence less than current status, excluding negative statuses)
            $completedStatuses = OrderStatuses::select('id', 'code', 'name', 'color', 'description', 'sequence', 'is_final', 'is_cancellable', 'display_order')
                ->where('sequence', '<', $currentStatus->sequence)
                ->whereNotIn('code', $negativeStatusCodes)
                ->where('status', 1)
                ->orderBy('sequence', 'asc')
                ->get()
                ->map(function ($status) {
                    return [
                        'id' => $status->id,
                        'code' => $status->code,
                        'name' => $status->name,
                        'color' => $status->color,
                        'description' => $status->description,
                        'sequence' => $status->sequence,
                        'is_final' => (bool) $status->is_final,
                        'is_cancellable' => (bool) $status->is_cancellable,
                        'display_order' => $status->display_order,
                    ];
                });

            // Get all statuses with sequence greater than current status (excluding negative statuses)
            $remainingStatuses = OrderStatuses::select('id', 'code', 'name', 'color', 'description', 'sequence', 'is_final', 'is_cancellable', 'display_order')
                ->where('sequence', '>', $currentStatus->sequence)
                ->whereNotIn('code', $negativeStatusCodes)
                ->where('status', 1)
                ->orderBy('sequence', 'asc')
                ->get()
                ->map(function ($status) {
                    return [
                        'id' => $status->id,
                        'code' => $status->code,
                        'name' => $status->name,
                        'color' => $status->color,
                        'description' => $status->description,
                        'sequence' => $status->sequence,
                        'is_final' => (bool) $status->is_final,
                        'is_cancellable' => (bool) $status->is_cancellable,
                        'display_order' => $status->display_order,
                    ];
                });

            // Get negative statuses separately
            $negativeStatuses = OrderStatuses::select('id', 'code', 'name', 'color', 'description', 'sequence', 'is_final', 'is_cancellable', 'display_order')
                ->whereIn('code', $negativeStatusCodes)
                ->where('status', 1)
                ->orderBy('sequence', 'asc')
                ->get()
                ->map(function ($status) {
                    return [
                        'id' => $status->id,
                        'code' => $status->code,
                        'name' => $status->name,
                        'color' => $status->color,
                        'description' => $status->description,
                        'sequence' => $status->sequence,
                        'is_final' => (bool) $status->is_final,
                        'is_cancellable' => (bool) $status->is_cancellable,
                        'display_order' => $status->display_order,
                    ];
                });

            $responseData = [
                'current_status' => [
                    'code' => $currentStatus->code,
                    'name' => $currentStatus->name,
                    'sequence' => $currentStatus->sequence,
                ],
                'completed_statuses' => $completedStatuses,
                'remaining_statuses' => $remainingStatuses,
                'negative_statuses' => $negativeStatuses,
                'total_completed' => $completedStatuses->count(),
                'total_remaining' => $remainingStatuses->count(),
                'total_negative' => $negativeStatuses->count(),
            ];

            return $this->returnResponse($responseData, 'Remaining statuses retrieved successfully.');

        } catch (Exception $e) {
            return $this->sendError('Failed to retrieve remaining statuses: '.$e->getMessage(), 500);
        }
    }

    public function nextStep(Request $request, string $uuid): JsonResponse
    {
        try {
            // basic validation for optional current_status_code (must exist in statuses if provided)
            $request->validate([
                'current_status_code' => 'nullable|string|exists:order_statuses,code',
            ]);

            // Validate UUID format before DB lookup
            $uuidValidator = \Illuminate\Support\Facades\Validator::make(['uuid' => $uuid], [
                'uuid' => 'uuid',
            ]);
            if ($uuidValidator->fails()) {
                return $this->sendError('Invalid UUID format provided.', 400);
            }

            // Fetch order (auth check + only active orders)
            $order = Orders::with('currentStatus')
                ->where('uuid', $uuid)
                ->where('customer_id', auth('customer-api')->id())
                ->where('status', 1)
                ->first();

            if (! $order) {
                return $this->sendError('Order not found or access denied.', 404);
            }

            // Determine currentStatusCode:
            // Priority: request param (if provided) -> order's current_status_code
            $requestedStatusCode = $request->input('current_status_code');
            $orderStatusCode = $order->current_status_code; // may be null for brand-new orders

            if ($requestedStatusCode) {
                // If client provided a status, ensure it matches the order's actual status to avoid race/tamper
                if ($orderStatusCode && $requestedStatusCode !== $orderStatusCode) {
                    return $this->sendError('Provided current_status_code does not match order\'s current status.', 409);
                }
                $currentStatusCode = $requestedStatusCode;
            } else {
                if (! $orderStatusCode) {
                    return $this->sendError('Order does not have a current status and no current_status_code provided.', 400);
                }
                $currentStatusCode = $orderStatusCode;
            }

            // fetch the OrderStatuses record for the current status code (fallback to relation if present)
            $currentStatus = $order->currentStatus;
            if (! $currentStatus || $currentStatus->code !== $currentStatusCode) {
                $currentStatus = OrderStatuses::where('code', $currentStatusCode)->first();
            }
            if (! $currentStatus) {
                return $this->sendError('Order current status not found.', 404);
            }

            // If already final, stop
            if ((bool) $currentStatus->is_final) {
                return $this->sendError('Cannot update order status. Order has reached final status.', 400);
            }

            // categorize statuses
            $vendorStatuses = ['confirmed', 'processing', 'ready_for_pickup'];
            $driverStatuses = ['assigned_to_driver', 'driver_accepted', 'driver_at_store', 'driver_picked_up', 'driver_nearby', 'driver_reached'];
            $finalStatuses = ['delivered', 'completed', 'failed', 'returned', 'cancelled', 'deleted'];

            if (in_array($currentStatusCode, $finalStatuses)) {
                return $this->sendError('Cannot update order status. Order has reached a final status: '.$currentStatusCode, 400);
            }

            // Build validation rules depending on the status category
            $validationRules = [];
            $validationMessages = [];

            if (in_array($currentStatusCode, $vendorStatuses)) {
                // you require store_vendor_id for vendor statuses
                $validationRules['store_vendor_id'] = 'required|integer|exists:store_vendor_orders,id';
                $validationMessages['store_vendor_id.required'] = 'Store vendor ID is required for vendor status updates.';
            }

            if (in_array($currentStatusCode, $driverStatuses)) {
                // require both store_vendor_id and driver_id for driver statuses
                $validationRules['store_vendor_id'] = 'required|integer|exists:store_vendor_orders,id';
                $validationRules['driver_id'] = 'required|integer|exists:drivers,id';
                $validationMessages['driver_id.required'] = 'Driver ID is required for driver status updates.';
            }

            // Validate request using built rules (only runs if rules were added)
            if (! empty($validationRules)) {
                try {
                    $request->validate($validationRules, $validationMessages);
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return $this->sendError('Validation failed: '.implode(', ', $e->validator->errors()->all()), 422);
                }
            }

            if (in_array($currentStatusCode, $vendorStatuses)) {
                $storeVendorId = $request->input('store_vendor_id');
                $assignedStoreVendorOrder = \App\Models\StoreVendorOrders::where('order_id', $order->id)
                    ->where('status', 1)
                    ->first();
                if (! $assignedStoreVendorOrder || $assignedStoreVendorOrder->store_vendor_id !== $storeVendorId) {
                    return $this->sendError('Store vendor ID does not match the assigned vendor for this order.', 403);
                }
            } elseif (in_array($currentStatusCode, $driverStatuses)) {
                $driverId = $request->input('driver_id');
                if ($order->driver_id === null) {
                    $order->update(['driver_id' => $driverId]);
                }
                if ($order->driver_id !== $driverId) {

                    return $this->sendError('Driver ID does not match the assigned driver for this order.', 403);
                }
            }

            // Find the next status by sequence
            $nextStatus = OrderStatuses::where('sequence', '>', $currentStatus->sequence)
                ->where('status', 1)
                ->whereNotIn('code', OrderStatuses::negativeStatusCodes())
                ->orderBy('sequence', 'asc')
                ->first();

            Log::info('Next status fetched', [
                'order_id' => $order->id,
                'current_status_code' => $currentStatusCode,
                'next_status_code' => $nextStatus ? $nextStatus->code : null,
            ]);

            if (! $nextStatus) {
                return $this->sendError('No next status available. Order may have reached final status.', 400);
            }

            // Gather any additional data to pass to the action
            $additionalData = [];
            if ($request->has('store_vendor_id')) {
                $additionalData['store_vendor_id'] = $request->input('store_vendor_id');
            }
            if ($request->has('driver_id')) {
                $additionalData['driver_id'] = $request->input('driver_id');
            }

            Log::info('Dispatching UpdateOrderStatusAction', [
                'order_id' => $order->id,
                'next_status_code' => $nextStatus->code,
                'additional_data' => $additionalData,
            ]);

            try {
                (new \App\Actions\Order\UpdateOrderStatusAction())->execute($order, $nextStatus->code, $additionalData);
            } catch (Exception $e) {
                return $this->sendError('Failed to update order status: '.$e->getMessage(), 500);
            }

            $responseData = [
                'order' => [
                    'uuid' => $order->uuid,
                    'order_number' => $order->order_number,
                    'current_status' => [
                        'id' => $currentStatus->id,
                        'code' => $currentStatus->code,
                        'name' => $currentStatus->name,
                        'sequence' => $currentStatus->sequence,
                    ],
                ],
                'next_status' => [
                    'id' => $nextStatus->id,
                    'code' => $nextStatus->code,
                    'name' => $nextStatus->name,
                    'color' => $nextStatus->color,
                    'sequence' => $nextStatus->sequence,
                    'is_final' => (bool) $nextStatus->is_final,
                ],
                'validation_applied' => [
                    'required_fields' => array_keys($validationRules),
                    'status_category' => in_array($currentStatusCode, $vendorStatuses) ? 'vendor' : (in_array($currentStatusCode, $driverStatuses) ? 'driver' : 'general'),
                ],
                'message' => 'Status update initiated successfully',
            ];

            return $this->returnResponse($responseData, 'Order status update initiated successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to update order status: '.$e->getMessage(), 500);
        }
    }

    /**
     * Update order status to a specific status code.
     */
    public function updateStatus(Request $request, string $uuid): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'status_code' => 'required|string|exists:order_statuses,code',
                'store_vendor_id' => 'nullable|integer|exists:store_vendor_orders,id',
                'driver_id' => 'nullable|integer|exists:drivers,id',
            ]);

            $statusCode = $request->input('status_code');

            // Validate UUID format
            $uuidValidator = \Illuminate\Support\Facades\Validator::make(['uuid' => $uuid], [
                'uuid' => 'uuid',
            ]);
            if ($uuidValidator->fails()) {
                return $this->sendError('Invalid UUID format provided.', 400);
            }

            // Fetch order
            $order = Orders::with('currentStatus')
                ->where('uuid', $uuid)
                ->where('customer_id', auth('customer-api')->id())
                ->where('status', 1)
                ->first();

            if (! $order) {
                return $this->sendError('Order not found or access denied.', 404);
            }

            // Check if the requested status exists
            $newStatus = OrderStatuses::where('code', $statusCode)->where('status', 1)->first();
            if (! $newStatus) {
                return $this->sendError('Invalid status code provided.', 400);
            }

            // Validate additional requirements based on status
            $vendorStatuses = ['confirmed', 'processing', 'ready_for_pickup'];
            $driverStatuses = ['assigned_to_driver', 'driver_accepted', 'driver_at_store', 'driver_picked_up', 'driver_nearby', 'driver_reached'];

            if (in_array($statusCode, $vendorStatuses) && ! $request->has('store_vendor_id')) {
                return $this->sendError('Store vendor ID is required for vendor status updates.', 422);
            }

            if (in_array($statusCode, $driverStatuses) && (! $request->has('store_vendor_id') || ! $request->has('driver_id'))) {
                return $this->sendError('Store vendor ID and driver ID are required for driver status updates.', 422);
            }

            // Validate store vendor assignment if provided
            if ($request->has('store_vendor_id')) {
                $storeVendorId = $request->input('store_vendor_id');
                $assignedStoreVendorOrder = \App\Models\StoreVendorOrders::where('order_id', $order->id)
                    ->where('status', 1)
                    ->first();
                if (! $assignedStoreVendorOrder || $assignedStoreVendorOrder->store_vendor_id !== $storeVendorId) {
                    return $this->sendError('Store vendor ID does not match the assigned vendor for this order.', 403);
                }
            }

            // Validate driver assignment if provided
            if ($request->has('driver_id')) {
                $driverId = $request->input('driver_id');
                if ($order->driver_id === null) {
                    $order->update(['driver_id' => $driverId]);
                } elseif ($order->driver_id !== $driverId) {
                    return $this->sendError('Driver ID does not match the assigned driver for this order.', 403);
                }
            }

            // Gather additional data
            $additionalData = [];
            if ($request->has('store_vendor_id')) {
                $additionalData['store_vendor_id'] = $request->input('store_vendor_id');
            }
            if ($request->has('driver_id')) {
                $additionalData['driver_id'] = $request->input('driver_id');
            }

            // Update the order status
            (new \App\Actions\Order\UpdateOrderStatusAction())->execute($order, $statusCode, $additionalData);

            return $this->returnResponse([
                'order' => [
                    'uuid' => $order->uuid,
                    'order_number' => $order->order_number,
                    'current_status' => [
                        'id' => $newStatus->id,
                        'code' => $newStatus->code,
                        'name' => $newStatus->name,
                        'sequence' => $newStatus->sequence,
                    ],
                ],
                'message' => 'Order status updated successfully',
            ], 'Order status updated successfully.');

        } catch (Exception $e) {
            return $this->sendError('Failed to update order status: '.$e->getMessage(), 500);
        }
    }
}
