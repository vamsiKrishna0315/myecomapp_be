<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use Illuminate\Foundation\Http\FormRequest;

final class OrderItemRequest extends FormRequest
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
            'product_id' => 'required|integer|exists:products,id',
            'category_id' => 'required|integer|exists:categories,id',
            'cut_id' => 'nullable|integer|exists:product_cuts,id',
            'product_name' => 'nullable|string|max:255',
            'cut_name' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100',

            // Pricing
            'unit_price' => 'required|numeric|min:0',
            'price_per_kg' => 'nullable|numeric|min:0',
            'price_per_piece' => 'nullable|numeric|min:0',

            // Quantity/Weight
            'quantity' => 'required|numeric|min:0.001',
            'weight' => 'nullable|numeric|min:0',
            'actual_weight' => 'nullable|numeric|min:0',
            'ordered_weight' => 'nullable|numeric|min:0',
            'weight_unit' => 'nullable|string|in:kg,g,lb,piece',

            // Totals
            'total_price' => 'required|numeric|min:0',
            'line_subtotal' => 'nullable|numeric|min:0',
            'line_discount' => 'nullable|numeric|min:0',
            'line_tax' => 'nullable|numeric|min:0',
            'line_total' => 'nullable|numeric|min:0',

            // Preparation
            'preparation_style' => 'nullable|string|max:255',
            'is_cleaned' => 'nullable|boolean',
            'is_skinless' => 'nullable|boolean',
            'special_instructions' => 'nullable|string|max:500',

            // Status
            'order_item_status' => 'nullable|integer|in:0,1,2,3,4,5',
            'status' => 'nullable|integer|in:0,1',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'product',
            'category_id' => 'category',
            'cut_id' => 'cut',
            'unit_price' => 'unit price',
            'total_price' => 'total price',
            'weight_unit' => 'weight unit',
        ];
    }

    /**
     * Get custom error messages for validator.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required for each item.',
            'product_id.exists' => 'The selected product does not exist.',
            'category_id.required' => 'Category is required for each item.',
            'category_id.exists' => 'The selected category does not exist.',
            'cut_id.exists' => 'The selected cut does not exist.',
            'quantity.required' => 'Quantity is required for each item.',
            'quantity.min' => 'Quantity must be at least 0.001.',
            'unit_price.required' => 'Unit price is required for each item.',
            'unit_price.min' => 'Unit price cannot be negative.',
            'total_price.required' => 'Total price is required for each item.',
            'total_price.min' => 'Total price cannot be negative.',
            'weight_unit.in' => 'Weight unit must be one of: kg, g, lb, or piece.',
            'special_instructions.max' => 'Special instructions cannot exceed 500 characters.',
        ];
    }
}
