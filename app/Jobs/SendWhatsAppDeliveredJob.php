<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\WhatsApp\WhatsAppServiceInterface;
use App\Models\Orders;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppDeliveredJob implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly int $orderId,
        public readonly ?string $templateName = null,
        public readonly ?string $languageCode = null,
    ) {}

    public function handle(
        WhatsAppServiceInterface $whatsAppService,
    ): void {

        $order = Orders::with([
            'customer',
            'items',
        ])->find($this->orderId);

        if (! $order) {
            Log::warning('Order not found for Delivered WhatsApp.', [
                'order_id' => $this->orderId,
            ]);

            return;
        }

        $result = $whatsAppService->sendDeliveredTemplate(
            order: $order,
            templateName: $this->templateName,
            languageCode: $this->languageCode,
        );

        Log::info('Delivered WhatsApp sent.', [
            'order_id' => $order->id,
            'message_id' => $result['message_id'] ?? null,
        ]);
    }
}
