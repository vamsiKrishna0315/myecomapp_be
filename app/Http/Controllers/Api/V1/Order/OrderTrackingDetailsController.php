<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Order;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Models\Orders;
use App\Models\OrderStatusTracking;
use App\Services\DriverTripTrackingService;
use Illuminate\Http\JsonResponse;

final class OrderTrackingDetailsController extends ResponseController
{
    public function __construct(private readonly DriverTripTrackingService $driverTripTrackingService) {}

    /**
     * Get tracking details for a specific order.
     */
    public function getTrackingDetails(string $uuid): JsonResponse
    {
        $order = Orders::query()
            ->with([
                'driver',
                'deliveryAddress',
                'customer',
                'storeVendorAssignment.storeVendor',
                'tripLocations' => fn ($query) => $query->orderByDesc('recorded_at')->limit(240),
            ])
            ->where('uuid', $uuid)
            ->where('customer_id', auth('customer-api')->id())
            ->where('status', 1)
            ->first();

        if (! $order) {
            return $this->sendError('Order not found or access denied.', 404);
        }

        $orderTrackingDetails = OrderStatusTracking::with(['orderStatus', 'driver'])
            ->where('order_id', $order->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $orderTrackingDetails) {
            return $this->sendError('Tracking details not found.', 404);
        }

        $trackingData = [
            'id' => $orderTrackingDetails->id,
            'status_code' => $orderTrackingDetails->status_code,
            'status_name' => $orderTrackingDetails->status_name,
            'status_color' => $orderTrackingDetails->orderStatus->color ?? null,
            'description' => $orderTrackingDetails->description,
            'timestamp' => $orderTrackingDetails->created_at,
            'driver_name' => $orderTrackingDetails->driver->name ?? null,
            'driver_mobile' => $orderTrackingDetails->driver->mobile ?? null,
            'driver_vehicle' => $orderTrackingDetails->driver->vehicle_number ?? null,
            'location' => [
                'lat' => $orderTrackingDetails->lat,
                'lng' => $orderTrackingDetails->lng,
            ],
            'live_trip' => $this->driverTripTrackingService->buildCustomerTrackingPayload($order),
        ];

        return $this->returnResponse($trackingData, 'Tracking details retrieved successfully.');
    }
}
