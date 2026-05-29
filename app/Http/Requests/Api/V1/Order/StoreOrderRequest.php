<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1\Order;

use App\Models\CuttypeProduct;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Log;

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
            // Order Items - Required
            'items' => 'required|array|min:1|max:50',
            'items.*' => 'required|array',

            // Item validation using OrderItemRequest rules
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.category_id' => 'required|integer|exists:categories,id',
            'items.*.cut_id' => 'required|integer|exists:cut_types,id',
            'items.*.product_name' => 'nullable|string|max:255',
            'items.*.cut_name' => 'nullable|string|max:255',
            'items.*.sku' => 'nullable|string|max:100',

            // Item Pricing
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.price_per_kg' => 'nullable|numeric|min:0',
            'items.*.price_per_piece' => 'nullable|numeric|min:0',

            // Item Quantity/Weight
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.weight' => 'nullable|numeric|min:0',
            'items.*.actual_weight' => 'nullable|numeric|min:0',
            'items.*.ordered_weight' => 'nullable|numeric|min:0',
            'items.*.weight_unit' => 'nullable|string|in:kg,g,lb,piece',

            // Item Totals
            'items.*.total_price' => 'required|numeric|min:0',
            'items.*.line_subtotal' => 'nullable|numeric|min:0',
            'items.*.line_discount' => 'nullable|numeric|min:0',
            'items.*.line_tax' => 'nullable|numeric|min:0',
            'items.*.line_total' => 'nullable|numeric|min:0',

            // Item Preparation
            'items.*.preparation_style' => 'nullable|string|max:255',
            'items.*.is_cleaned' => 'nullable|boolean',
            'items.*.is_skinless' => 'nullable|boolean',
            'items.*.special_instructions' => 'nullable|string|max:500',

            // Addresses - Required
            'delivery_address_id' => 'required|integer|exists:addresses,id',
            'billing_address_id' => 'nullable|integer|exists:addresses,id',

            // Delivery Details - Required
            'delivery_date' => 'required|date|after_or_equal:today',
            'delivery_time_slot' => 'required|string|max:100',

            // Order Details - Optional
            'special_instructions' => 'nullable|string|max:1000',
            'coupon_code' => 'nullable|string|max:50',

            // Payment & Billing - Required
            'billing_type_id' => 'required|integer|exists:billing_types,id',
            'payment_method' => 'nullable|string|max:50',
            'provider' => 'nullable|string|in:razorpay,phonepe,paytm,cod',

            // Order Totals - Optional (will be calculated)
            'subtotal' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|integer|in:1,2',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'delivery_charge' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'items' => 'order items',
            'delivery_address_id' => 'delivery address',
            'billing_address_id' => 'billing address',
            'delivery_date' => 'delivery date',
            'delivery_time_slot' => 'delivery time slot',
            'billing_type_id' => 'billing type',
            'provider' => 'payment provider',
            'coupon_code' => 'coupon code',
            'items.*.product_id' => 'product',
            'items.*.category_id' => 'category',
            'items.*.quantity' => 'quantity',
            'items.*.unit_price' => 'unit price',
            'items.*.total_price' => 'total price',
        ];
    }

    /**
     * Get custom error messages for validator.
     */
    public function messages(): array
    {
        return [
            // Order level messages
            'items.required' => 'At least one item is required to place an order.',
            'items.min' => 'At least one item is required to place an order.',
            'items.max' => 'You cannot add more than 50 items in a single order.',

            // Delivery messages
            'delivery_address_id.required' => 'Delivery address is required.',
            'delivery_address_id.exists' => 'The selected delivery address is invalid.',
            'billing_address_id.exists' => 'The selected billing address is invalid.',

            'delivery_date.required' => 'Delivery date is required.',
            'delivery_date.after_or_equal' => 'Delivery date must be today or a future date.',
            'delivery_time_slot.required' => 'Delivery time slot is required.',

            // Payment messages
            'billing_type_id.required' => 'Billing type is required.',
            'billing_type_id.exists' => 'The selected billing type is invalid.',
            'provider.in' => 'Payment provider must be one of razorpay, phonepe, or paytm.',

            'coupon_code' => 'nullable|string|max:50',

            // Item level messages
            'items.*.product_id.required' => 'Product is required for each item.',
            'items.*.product_id.exists' => 'One or more selected products do not exist.',
            'items.*.category_id.required' => 'Category is required for each item.',
            'items.*.category_id.exists' => 'One or more selected categories do not exist.',
            'items.*.cut_id.exists' => 'One or more selected cuts do not exist.',
            'items.*.cut_id.required' => 'Cut is required for each item.',
            'items.*.cut_id' => 'The selected cut is not available for this product.',

            'items.*.quantity.required' => 'Quantity is required for each item.',
            'items.*.quantity.min' => 'Quantity must be at least 0.001 for each item.',

            'items.*.unit_price.required' => 'Unit price is required for each item.',
            'items.*.unit_price.min' => 'Unit price cannot be negative.',

            'items.*.total_price.required' => 'Total price is required for each item.',
            'items.*.total_price.min' => 'Total price cannot be negative.',

            'items.*.weight_unit.in' => 'Weight unit must be one of: kg, g, lb, or piece.',
            'items.*.special_instructions.max' => 'Item special instructions cannot exceed 500 characters.',

            'special_instructions.max' => 'Special instructions cannot exceed 1000 characters.',
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
            // Custom validation: Check if delivery address belongs to authenticated customer
            // if ($this->has('delivery_address_id')) {
            //     $customer = auth('customer-api')->user();
            //     $address = \App\Models\Address::find($this->delivery_address_id);

            //     if ($address && $address->customer_id !== $customer->id) {
            //         $validator->errors()->add(
            //             'delivery_address_id',
            //             'The selected delivery address does not belong to you.'
            //         );
            //     }
            // }

            // Custom validation: Check if billing address belongs to authenticated customer
            if ($this->has('billing_address_id')) {
                // $customer = auth('customer-api')->user();
                // $address = \App\Models\Address::find($this->billing_address_id);

                // if ($address && $address->customer_id !== $customer->id) {
                //     $validator->errors()->add(
                //         'billing_address_id',
                //         'The selected billing address does not belong to you.'
                //     );
                // }
            }

            // Custom validation: Verify item totals match calculations
            if ($this->has('items')) {
                foreach ($this->items as $index => $item) {
                    if (isset($item['quantity']) && isset($item['unit_price']) && isset($item['total_price'])) {
                        $calculatedTotal = $item['quantity'] * $item['unit_price'];
                        $providedTotal = $item['total_price'];

                        // Allow for small floating point differences
                        if (abs($calculatedTotal - $providedTotal) > 0.01) {
                            $validator->errors()->add(
                                "items.{$index}.total_price",
                                'Item total price does not match quantity × unit price calculation.'
                            );
                        }
                    }

                    // Custom validation: Check if product has the selected cut
                    if (isset($item['product_id']) && isset($item['cut_id'])) {
                        Log::info("Validating cut_id {$item['cut_id']} for product_id {$item['product_id']}");
                        $cutExists = CuttypeProduct::where('product_id', $item['product_id'])
                            ->where('cuttype_id', $item['cut_id'])
                            ->exists();

                        if (! $cutExists) {
                            $validator->errors()->add(
                                "items.{$index}.cut_id",
                                'The selected cut is not available for this product.'
                            );
                        }
                    }
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
