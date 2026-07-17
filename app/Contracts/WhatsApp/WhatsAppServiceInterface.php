<?php

declare(strict_types=1);

namespace App\Contracts\WhatsApp;

interface WhatsAppServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function sendHelloWorld(string $recipientPhone, ?string $languageCode = null): array;

    /**
     * @return array<string, mixed>
     */
    public function sendOtpTemplate(
        string $recipientPhone,
        string $otp,
        ?string $templateName = null,
        ?string $languageCode = null,
    ): array;

    public function sendWelcomeTemplate(
        string $recipientPhone,
        ?string $templateName = null,
        ?string $languageCode = null,
    ): array;

    /**
     * @return array<string, mixed>
     */
    public function sendOrderCreatedTemplate(
        \App\Models\Orders $order,
        ?string $templateName = null,
        ?string $languageCode = null,
    ): array;

    /**
     * @return array<string, mixed>
     */
    public function sendEtaTemplate(
        \App\Models\Orders $order,
        ?string $templateName = null,
        ?string $languageCode = null,
    ): array;

    /**
     * @return array<string, mixed>
     */
    public function sendDeliveredTemplate(
        \App\Models\Orders $order,
        ?string $templateName = null,
        ?string $languageCode = null,
    ): array;

    /**
     * @return array<string, mixed>
     */
    public function sendOrderCancelledTemplate(
        \App\Models\Orders $order,
        ?string $templateName = null,
        ?string $languageCode = null,
    ): array;
}
