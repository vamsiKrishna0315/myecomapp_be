<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\WhatsApp\WhatsAppServiceInterface;
use App\Exceptions\WhatsApp\WhatsAppDeliveryException;
use App\Models\Orders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SendWhatsAppOrderCreatedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries;

    public int $timeout;

    public bool $failOnTimeout = true;

    /**
     * @var array<int, int>
     */
    public array $backoff;

    public function __construct(
        public readonly int $orderId,
        public readonly ?string $templateName = null,
        public readonly ?string $languageCode = null,
    ) {
        $this->onQueue((string) config('whatsapp.queue'));
        $this->tries = (int) config('whatsapp.job_retry.tries');
        $this->timeout = (int) config('whatsapp.job_retry.timeout');
        $this->backoff = config('whatsapp.job_retry.backoff', [30, 60, 120]);
    }

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [
            new ThrottlesExceptions(
                (int) config('whatsapp.job_retry.max_exceptions'),
                5 * 60
            ),
        ];
    }

    /**
     * @throws WhatsAppDeliveryException
     */
    public function handle(
        WhatsAppServiceInterface $whatsAppService,
    ): void {

        $order = Orders::with([
            'customer',
            'items',
        ])->find($this->orderId);

        if (! $order) {
            Log::warning('Order not found for WhatsApp notification.', [
                'order_id' => $this->orderId,
            ]);

            return;
        }

        $result = $whatsAppService->sendOrderCreatedTemplate(
            order: $order,
            templateName: $this->templateName,
            languageCode: $this->languageCode,
        );

        Log::info('Queued Order Created WhatsApp sent successfully.', [
            'order_id' => $order->id,
            'message_id' => $result['message_id'] ?? null,
            'job_id' => $this->job?->getJobId(),
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Queued Order Created WhatsApp failed.', [
            'order_id' => $this->orderId,
            'job_id' => $this->job?->getJobId(),
            'error' => $exception->getMessage(),
            'context' => $exception instanceof WhatsAppDeliveryException
                ? $exception->context()
                : [],
        ]);
    }
}
