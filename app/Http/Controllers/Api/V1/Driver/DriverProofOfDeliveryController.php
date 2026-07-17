<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Http\Requests\Api\V1\Driver\UploadProofOfDeliveryRequest;
use App\Services\DriverProofOfDeliveryService;
use Illuminate\Http\JsonResponse;

final class DriverProofOfDeliveryController extends ResponseController
{
    public function __construct(private readonly DriverProofOfDeliveryService $driverProofOfDeliveryService) {}

    public function __invoke(UploadProofOfDeliveryRequest $request, string $orderId): JsonResponse
    {
        $result = $this->driverProofOfDeliveryService->upload(
            $request->user(),
            $orderId,
            $request->file('image'),
        );

        if (! $result) {
            return $this->sendError('Order not found.', 404);
        }

        return $this->returnResponse($result, 'Proof of delivery uploaded successfully');
    }
}
