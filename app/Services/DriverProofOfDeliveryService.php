<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MediaCategory;
use App\Models\Driver;
use App\Models\Orders;
use App\Models\ProofOfDelivery;
use App\Services\Media\MediaService;
use Illuminate\Http\UploadedFile;

final class DriverProofOfDeliveryService
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function upload(Driver $driver, string $orderId, UploadedFile $image): ?array
    {
        $order = $this->findDriverOrder($driver, $orderId);

        if (! $order) {
            return null;
        }

        $path = $this->mediaService->upload($image, MediaCategory::ProofOfDelivery);

        $proofOfDelivery = ProofOfDelivery::query()->create([
            'order_id' => $order->id,
            'driver_id' => $driver->id,
            'order_status_id' => $order->current_status_id,
            'status_code' => $order->current_status_code,
            'image_path' => $path,
            'status' => 1,
        ]);

        return [
            'id' => $proofOfDelivery->id,
            'order_id' => $order->order_number,
            'image_url' => $this->mediaService->publicUrl($path),
            'uploaded_at' => $proofOfDelivery->created_at?->toIso8601String(),
        ];
    }

    private function findDriverOrder(Driver $driver, string $orderId): ?Orders
    {
        return Orders::query()
            ->where('order_number', $orderId)
            ->where('driver_id', $driver->id)
            ->where('status', 1)
            ->where('is_cancelled', false)
            ->first();
    }
}
