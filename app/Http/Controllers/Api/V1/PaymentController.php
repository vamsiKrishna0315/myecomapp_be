<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Orders;
use App\Services\Payments\PaymentManager;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Jobs\SendWhatsAppOrderCreatedJob;
use Illuminate\Support\Facades\Log;


final class PaymentController extends ResponseController
{
    /**
     * Verify payment after completion.
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'razorpay_order_id' => 'required|string',
                'razorpay_payment_id' => 'required|string',
                'razorpay_signature' => 'required|string',
                'order_uuid' => 'nullable|string',
                'provider' => 'nullable|string',
            ]);

            $provider = $validated['provider'] ?? 'razorpay';
            $gateway = PaymentManager::gateway($provider);

            $paymentData = [
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
            ];

            $isValid = $gateway->verifyPayment($paymentData);
            Log::info('Payment verification result', ['is_valid' => $isValid, 'payment_data' => $paymentData]);
            if ($isValid) {
                // Find and update the order
                $order = Orders::where('uuid', $validated['order_uuid'])->first();

                if ($order) {
                    $order->update([
                        'payment_status' => 1, // 1 = Paid
                        'razorpay_payment_id' => $validated['razorpay_payment_id'],
                    ]);

                     SendWhatsAppOrderCreatedJob::dispatch($order->id);
                }

                return $this->returnResponse([
                    'verified' => true,
                    'message' => 'Payment verified successfully',
                    'order' => $order,
                ], 'Payment verified successfully.');
            }

            return $this->sendError('Payment verification failed.', 400);

        } catch (Exception $e) {
            return $this->sendError('Payment verification error: '.$e->getMessage(), 500);
        }
    }
}
