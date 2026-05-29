<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Contracts\WhatsApp\WhatsAppServiceInterface;
use App\Exceptions\WhatsApp\WhatsAppDeliveryException;
use App\Http\Requests\Api\V1\WhatsApp\SendWhatsAppHelloWorldRequest;
use App\Http\Requests\Api\V1\WhatsApp\SendWhatsAppOtpRequest;
use App\Jobs\SendWhatsAppOtpJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

final class WhatsAppController extends ResponseController
{
    public function queueOtp(SendWhatsAppOtpRequest $request): JsonResponse
    {
        $payload = $request->validated();

        SendWhatsAppOtpJob::dispatch(
            recipientPhone: $payload['recipient_phone'],
            otp: $payload['otp'],
            templateName: $payload['template_name'] ?? null,
            languageCode: $payload['language_code'] ?? null,
        );

        Log::info('Queued WhatsApp OTP job dispatched.', [
            'recipient_phone' => $payload['recipient_phone'],
            'template_name' => $payload['template_name'] ?? config('whatsapp.templates.otp'),
            'queue' => config('whatsapp.queue'),
        ]);

        return $this->returnResponse([
            'queued' => true,
            'queue' => config('whatsapp.queue'),
        ], 'WhatsApp OTP dispatch queued.', 202);
    }

    public function sendHelloWorld(
        SendWhatsAppHelloWorldRequest $request,
        WhatsAppServiceInterface $whatsAppService,
    ): JsonResponse {
        $payload = $request->validated();

        try {
            $result = $whatsAppService->sendHelloWorld(
                recipientPhone: $payload['recipient_phone'] ?? "8121123312",
                languageCode: $payload['language_code'] ?? null,
            );

            return $this->returnResponse($result, 'WhatsApp hello_world template sent successfully.');
        } catch (WhatsAppDeliveryException $exception) {
            Log::error('WhatsApp hello_world template send failed.', [
                'recipient_phone' => $payload['recipient_phone'],
                'error' => $exception->getMessage(),
                'context' => $exception->context(),
            ]);

            return $this->sendError(
                $exception->getMessage(),
                502,
                $exception->context(),
            );
        }
    }
}
