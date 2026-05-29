<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\WhatsApp;

final class SendWhatsAppHelloWorldRequest extends BaseWhatsAppRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'recipient_phone' => ['required', 'string', 'regex:/^\+?\d{10,15}$/'],
            'language_code' => ['nullable', 'string', 'max:10'],
        ];
    }
}
