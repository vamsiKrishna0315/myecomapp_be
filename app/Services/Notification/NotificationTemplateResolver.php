<?php

declare(strict_types=1);

namespace App\Services\Notification;

use App\Enums\NotificationEventType;
use App\Models\NotificationEventMapping;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Log;

final class NotificationTemplateResolver
{
    public function resolve(
        NotificationEventType $eventType,
        ?string $referenceModel = null,
        ?int $referenceId = null,
    ): ?NotificationTemplate {
        $mapping = NotificationEventMapping::query()
            ->where('event_type', $eventType->value)
            ->where('status', 1)
            ->when(
                $referenceModel,
                fn($query) => $query->where('reference_model', $referenceModel)
            )
            ->when(
                $referenceId,
                fn($query) => $query->where('reference_id', $referenceId)
            )
            ->with('template')
            ->first();

        Log::info('Resolved notification template.', [
            'event_type' => $eventType->value,
            'mapping' => $mapping?->toArray(),
            'template' => $mapping?->template?->toArray(),
        ]);

        return $mapping?->template;
    }
}
