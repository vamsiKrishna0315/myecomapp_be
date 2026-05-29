<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreOrderRequest extends FormRequest
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
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.category_id' => 'required|integer|exists:categories,id',
            'items.*.cut_id' => 'nullable|integer|exists:product_cuts,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.weight' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.total_price' => 'required|numeric|min:0',
            'items.*.special_instructions' => 'nullable|string|max:500',

            'delivery_address_id' => 'required|integer|exists:addresses,id',
            'billing_address_id' => 'nullable|integer|exists:addresses,id',

            'delivery_date' => 'required|date|after_or_equal:today',
            'delivery_time_slot' => 'required|string',

            'special_instructions' => 'nullable|string|max:1000',
            'coupon_code' => 'nullable|string|max:50',

            'billing_type_id' => 'required|integer|exists:billing_types,id',
            'payment_method' => 'nullable|string|max:50',
        ];
    }

    /**
     * Get custom error messages for validator.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one item is required to place an order.',
            'items.*.product_id.required' => 'Product ID is required for each item.',
            'items.*.category_id.required' => 'Category ID is required for each item.',
            'delivery_address_id.required' => 'Delivery address is required.',
            'delivery_date.required' => 'Delivery date is required.',
            'delivery_date.after_or_equal' => 'Delivery date must be today or a future date.',
        ];
    }
}
