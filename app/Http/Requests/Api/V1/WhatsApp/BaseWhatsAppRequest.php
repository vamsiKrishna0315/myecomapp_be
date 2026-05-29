<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\WhatsApp;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseWhatsAppRequest extends FormRequest
{
    final public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $recipientPhone = $this->input('recipient_phone');

        if (! is_string($recipientPhone)) {
            return;
        }

        $normalizedPhone = preg_replace('/[\s-]+/', '', trim($recipientPhone));

        if ($normalizedPhone !== null && preg_match('/^\d{10}$/', $normalizedPhone) === 1) {
            $normalizedPhone = '+91'.$normalizedPhone;
        }

        $this->merge([
            'recipient_phone' => $normalizedPhone,
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()->toArray(),
            ], 422)
        );
    }
}
