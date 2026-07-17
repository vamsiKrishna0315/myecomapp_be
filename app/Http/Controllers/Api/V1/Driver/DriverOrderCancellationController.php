<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Http\Requests\Api\V1\Driver\CancelDriverOrderRequest;
use App\Services\DriverOrderCancellationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

final class DriverOrderCancellationController extends ResponseController
{
    public function __construct(private readonly DriverOrderCancellationService $driverOrderCancellationService) {}

    public function __invoke(CancelDriverOrderRequest $request, string $orderId): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->driverOrderCancellationService->cancel(
                $request->user(),
                $orderId,
                (string) $validated['reason'],
                $validated['notes'] ?? null,
            );
        } catch (ValidationException $exception) {
            $errors = $exception->errors();

            return $this->sendError(
                collect($errors)->flatten()->first() ?? 'Unable to cancel order.',
                422,
                $errors,
            );
        }

        if (! $result) {
            return $this->sendError('Order not found.', 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order cancelled successfully',
            'requires_customer_confirmation' => $result['requires_customer_confirmation'],
        ]);
    }
}
