<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

final class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Addresses - Optional for updates
            'delivery_address_id' => 'sometimes|required|integer|exists:addresses,id',
            'billing_address_id' => 'nullable|integer|exists:addresses,id',

            // Delivery Details - Optional for updates
            'delivery_date' => 'sometimes|required|date|after_or_equal:today',
            'delivery_time_slot' => 'sometimes|required|string|max:100',

            // Order Details - Optional
            'special_instructions' => 'nullable|string|max:1000',
            'coupon_code' => 'nullable|string|max:50|exists:coupons,code',

            // Payment & Billing - Optional for updates
            'billing_type_id' => 'sometimes|required|integer|exists:billing_types,id',
            'payment_method' => 'nullable|string|max:50',
            'payment_status' => 'nullable|integer|in:0,1,2,3',

            // Status updates
            'current_status_id' => 'nullable|integer|exists:order_statuses,id',
            'current_status_code' => 'nullable|string|max:50',
            'is_cancelled' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'delivery_address_id' => 'delivery address',
            'billing_address_id' => 'billing address',
            'delivery_date' => 'delivery date',
            'delivery_time_slot' => 'delivery time slot',
            'billing_type_id' => 'billing type',
            'coupon_code' => 'coupon code',
            'current_status_id' => 'order status',
        ];
    }

    /**
     * Get custom error messages for validator.
     */
    public function messages(): array
    {
        return [
            'delivery_address_id.exists' => 'The selected delivery address is invalid.',
            'billing_address_id.exists' => 'The selected billing address is invalid.',
            'delivery_date.after_or_equal' => 'Delivery date must be today or a future date.',
            'billing_type_id.exists' => 'The selected billing type is invalid.',
            'coupon_code.exists' => 'The coupon code is invalid or expired.',
            'current_status_id.exists' => 'The selected order status is invalid.',
            'payment_status.in' => 'Invalid payment status value.',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $customer = auth('customer-api')->user();

            // Custom validation: Check if delivery address belongs to authenticated customer
            if ($this->has('delivery_address_id')) {
                $address = \App\Models\Address::find($this->delivery_address_id);

                if ($address && $address->customer_id !== $customer->id) {
                    $validator->errors()->add(
                        'delivery_address_id',
                        'The selected delivery address does not belong to you.'
                    );
                }
            }

            // Custom validation: Check if billing address belongs to authenticated customer
            if ($this->has('billing_address_id')) {
                $address = \App\Models\Address::find($this->billing_address_id);

                if ($address && $address->customer_id !== $customer->id) {
                    $validator->errors()->add(
                        'billing_address_id',
                        'The selected billing address does not belong to you.'
                    );
                }
            }
        });
    }

    /**
     * Handle a failed validation attempt.
     *
     * @return void
     *
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
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
