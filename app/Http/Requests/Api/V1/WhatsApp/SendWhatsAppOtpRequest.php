<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\WhatsApp;

final class SendWhatsAppOtpRequest extends BaseWhatsAppRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'recipient_phone' => ['required', 'string', 'regex:/^\+?\d{10,15}$/'],
            'otp' => ['required', 'digits:6'],
            'template_name' => ['nullable', 'string', 'max:255'],
            'language_code' => ['nullable', 'string', 'max:10'],
        ];
    }
}
