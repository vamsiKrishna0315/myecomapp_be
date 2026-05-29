<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\OrderStatusTracking;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class UpdateOrderStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;

    protected $nextStatusCode;

    protected $additionalData;

    /**
     * Create a new job instance.
     */
    public function __construct(Orders $order, string $nextStatusCode, array $additionalData = [])
    {
        $this->order = $order;
        $this->nextStatusCode = $nextStatusCode;
        $this->additionalData = $additionalData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            DB::beginTransaction();

            // Get the next status details
            $nextStatus = OrderStatuses::where('code', $this->nextStatusCode)
                ->where('status', 1)
                ->first();

            if (! $nextStatus) {
                Log::error('Next status not found', [
                    'status_code' => $this->nextStatusCode,
                ]);

                return;
            }

            // Create new tracking record
            $trackingData = [
                'order_id' => $this->order->id,
                'customer_id' => $this->order->customer_id,
                'driver_id' => $this->additionalData['driver_id'] ?? $this->order->driver_id ?? null,
                'store_vendor_id' => $this->additionalData['store_vendor_id'] ?? $this->order->storeVendorAssignment?->store_vendor_id ?? null,
                'order_status_id' => $nextStatus->id,
                'status_code' => $nextStatus->code,
                'status_name' => $nextStatus->name,
                'description' => "Status updated to {$nextStatus->name}",
                'lat' => null,
                'lng' => null,
                'status' => 1,
            ];

            $orderStatusTracking = OrderStatusTracking::create($trackingData);

            $this->order->update([
                'current_status_id' => $nextStatus->id,
                'current_status_code' => $nextStatus->code,
            ]);

            $this->updateOrderTimestamps($this->order, $nextStatus->code);

            DB::commit();

            Log::info('Order status updated successfully', [
                'order_uuid' => $this->order->uuid,
                'order_id' => $this->order->id,
                'new_status' => $nextStatus->code,
                'tracking_id' => $orderStatusTracking->id,
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to update order status', [
                'order_uuid' => $this->order->uuid,
                'next_status_code' => $this->nextStatusCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('UpdateOrderStatusJob failed', [
            'order_uuid' => $this->order->uuid,
            'next_status_code' => $this->nextStatusCode,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Update order timestamps based on status code.
     */
    private function updateOrderTimestamps(Orders $order, string $statusCode): void
    {
        $updates = [];

        switch ($statusCode) {
            case 'confirmed':
                $updates['confirmed_at'] = now();
                break;
            case 'delivered':
                $updates['delivered_at'] = now();
                break;
            case 'cancelled':
                $updates['cancelled_at'] = now();
                $updates['is_cancelled'] = true;
                break;
            case 'failed':
                $updates['failed_at'] = now();
                break;
            case 'returned':
                $updates['returned_at'] = now();
                break;
        }

        if (! empty($updates)) {
            $order->update($updates);
        }
    }
}
