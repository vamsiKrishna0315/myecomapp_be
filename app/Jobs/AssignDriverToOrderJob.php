<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Orders;
use App\Models\OrderStatuses;
use App\Models\OrderStatusTracking;
use App\Services\DeliveryPlanningService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class AssignDriverToOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Orders::query()
            ->with(['deliveryAddress', 'storeVendorAssignment.storeVendor', 'items.product', 'items.cut'])
            ->whereKey($this->orderId)
            ->where('status', 1)
            ->where('is_cancelled', false)
            ->whereNull('driver_id')
            ->whereNotIn('current_status_code', ['delivered', 'completed', 'cancelled', 'returned', 'failed', 'deleted'])
            ->first();

        if (! $order) {
            Log::warning('AssignDriverToOrderJob: order not found', [
                'order_id' => $this->orderId,
            ]);

            return;
        }

        $planner = app(DeliveryPlanningService::class);
        $plan = $planner->planDriverAssignmentForOrder($order);

        if (! $plan) {
            Log::warning('AssignDriverToOrderJob: no available driver found', [
                'order_id' => $order->id,
            ]);

            return;
        }

        DB::transaction(function () use ($order, $plan): void {
            $lockedOrder = Orders::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedOrder || $lockedOrder->driver_id !== null) {
                return;
            }

            $lockedOrder->update([
                'driver_id' => $plan['driver_id'],
            ]);

            $assignedStatus = OrderStatuses::query()
                ->where('code', 'assigned_to_driver')
                ->where('status', 1)
                ->first();

            if ($assignedStatus && ! OrderStatusTracking::query()->where('order_id', $lockedOrder->id)->where('status_code', 'assigned_to_driver')->exists()) {
                OrderStatusTracking::create([
                    'order_id' => $lockedOrder->id,
                    'customer_id' => $lockedOrder->customer_id,
                    'driver_id' => $plan['driver_id'],
                    'store_vendor_id' => $lockedOrder->storeVendorAssignment?->store_vendor_id,
                    'order_status_id' => $assignedStatus->id,
                    'status_code' => $assignedStatus->code,
                    'status_name' => $assignedStatus->name,
                    'description' => sprintf(
                        'Driver assigned for early dispatch. Prep: %d min, driver to vendor ETA: %s min.',
                        $plan['preparation_minutes'],
                        $plan['best_driver']['eta_to_vendor_minutes'] ?? 'n/a'
                    ),
                    'status' => 1,
                ]);
            }
        });

        Log::info('AssignDriverToOrderJob: driver assigned using delivery plan', [
            'order_id' => $order->id,
            'driver_id' => $plan['driver_id'],
            'preparation_minutes' => $plan['preparation_minutes'],
            'driver_to_vendor_eta_minutes' => $plan['best_driver']['eta_to_vendor_minutes'] ?? null,
            'estimated_delivery_minutes' => $plan['dispatch_preview']['estimated_delivery_minutes'] ?? null,
        ]);
    }
}
