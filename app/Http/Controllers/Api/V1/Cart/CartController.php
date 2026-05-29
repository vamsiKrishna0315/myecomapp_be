<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Cart;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Models\CartItem;
use App\Models\CutType;
use App\Models\CuttypeProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

final class CartController extends ResponseController
{
    /**
     * List authenticated customer's cart items.
     */
    public function index()
    {
        $customer = auth('customer-api')->user();
        if (! $customer) {
            return $this->sendError('Unauthenticated. Please login first.', 401);
        }

        $items = CartItem::query()
            ->where('customer_id', $customer->id)
            ->with(['product.category', 'cuttype'])
            ->get();

        $data = [
            'items' => $items,
            'count' => $items->count(),
        ];

        return $this->returnResponse($data, 'Cart items retrieved successfully');
    }

    public function store(Request $request)
    {
        Log::info('CartController@store called', $request->all());
        $customer = auth('customer-api')->user();
        if (! $customer) {
            return $this->sendError('Unauthenticated. Please login first.', 401);
        }

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            // Prefer new pivot key name, keep backward compatibility
            'cuttype_id' => ['nullable', 'integer', 'exists:cut_types,id'],
            'product_cut_id' => ['nullable', 'integer'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'quantity_unit' => ['required', 'in:kg,piece,gram'],
            'special_instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        $product = Product::find($data['product_id']);
        // Resolve selected cut type (pivot) if provided
        $cuttypeId = $data['cuttype_id'] ?? $data['product_cut_id'] ?? null;
        if (! empty($cuttypeId)) {
            $exists = CuttypeProduct::query()
                ->where('product_id', $product->id)
                ->where('cuttype_id', $cuttypeId)
                ->exists();
            Log::info('Checking CuttypeProduct mapping', ['product_id' => $product->id, 'cuttype_id' => $cuttypeId, 'exists' => $exists]);
            if (! $exists) {
                return $this->sendError('Invalid cut type for the specified product.', 422);
            }
        }

        // Preserve original unit; compute weight in kg for pricing
        $origUnit = $data['quantity_unit'];
        $quantity = (float) $data['quantity'];
        $weightKg = null;
        if ($origUnit === 'gram') {
            $weightKg = $quantity / 1000.0; // convert grams to kg
        } elseif ($origUnit === 'kg') {
            $weightKg = $quantity; // already kg
        } // piece => weight stays null

        // Pricing now relies on Product price (CutType pivot has no price)
        $unitPrice = (float) $product->price;
        if ($unitPrice <= 0) {
            return $this->sendError('Product does not have a valid price.', 422);
        }

        // Use computed kg weight for gram/kg, null for piece
        $weight = $weightKg;

        $existing = CartItem::query()
            ->where('customer_id', $customer->id)
            ->where('product_id', $product->id)
            ->where('cuttype_id', $cuttypeId)
            ->where('quantity_unit', $origUnit)
            ->first();

        if ($existing) {
            // Sum quantity in original unit; accumulate weight in kg when applicable
            $existing->quantity = (float) $existing->quantity + (float) $quantity;
            $existing->unit_price = $unitPrice;
            $existing->weight = $weight !== null ? (float) ($existing->weight ?? 0) + (float) $weight : $existing->weight;
            $existing->special_instructions = $data['special_instructions'] ?? $existing->special_instructions;
            $existing->save();

            return $this->returnResponse($existing->load(['product.category', 'cuttype']), 'Cart updated');
        }

        $item = CartItem::create([
            'customer_id' => $customer->id,
            'product_id' => $product->id,
            'product_cut_id' => null,
            'cuttype_id' => $cuttypeId,
            'quantity' => (float) $quantity,
            'quantity_unit' => $origUnit,
            'weight' => $weight,
            'unit_price' => $unitPrice,
            'total_price' => 0, // calculated on saving via model
            'special_instructions' => $data['special_instructions'] ?? null,
            // 'status' => 1,
            'session_id' => null,
        ]);

        return $this->returnResponse($item->load(['product.category', 'cuttype']), 'Item added to cart');
    }

    public function delete($id)
    {
        $customer = auth('customer-api')->user();
        if (! $customer) {
            return $this->sendError('Unauthenticated. Please login first.', 401);
        }

        // Handle array of IDs for batch delete
        if (is_array($id)) {
            CartItem::whereIn('id', $id)
                ->where('customer_id', $customer->id)
                ->delete();

            return $this->returnResponse(null, 'Cart items removed successfully.');
        }

        // Single item delete (existing logic)
        $item = CartItem::where('id', $id)
            ->where('customer_id', $customer->id)
            ->first();

        if (! $item) {
            return $this->sendError('Cart item not found.', 404);
        }

        $item->delete();

        return $this->returnResponse(null, 'Cart item removed successfully.');
    }

    /**
     * Update a cart item: quantity/unit/cuttype/instructions.
     */
    public function update(Request $request, $id)
    {
        $customer = auth('customer-api')->user();
        if (! $customer) {
            return $this->sendError('Unauthenticated. Please login first.', 401);
        }

        $item = CartItem::query()
            ->where('id', $id)
            ->where('customer_id', $customer->id)
            ->first();

        if (! $item) {
            return $this->sendError('Cart item not found.', 404);
        }

        $data = $request->validate([
            'quantity' => ['sometimes', 'numeric', 'min:0.001'],
            'quantity_unit' => ['sometimes', 'in:kg,piece,gram'],
            'cuttype_id' => ['sometimes', 'nullable', 'integer', 'exists:cut_types,id'],
            'special_instructions' => ['sometimes', 'nullable', 'string', 'max:1000'],
        ]);

        $product = Product::find($item->product_id);
        if (! $product) {
            return $this->sendError('Product not found for this cart item.', 422);
        }

        // If cuttype is being changed, verify mapping for this product
        if (array_key_exists('cuttype_id', $data) && ! empty($data['cuttype_id'])) {
            $exists = CuttypeProduct::query()
                ->where('product_id', $product->id)
                ->where('cuttype_id', $data['cuttype_id'])
                ->exists();
            if (! $exists) {
                return $this->sendError('Invalid cut type for the specified product.', 422);
            }
            $item->cuttype_id = $data['cuttype_id'];
        }

        // Determine final unit and quantity
        $finalUnit = $data['quantity_unit'] ?? $item->quantity_unit;
        $finalQty = array_key_exists('quantity', $data) ? (float) $data['quantity'] : (float) $item->quantity;

        // Compute weight in kg for gram/kg
        if ($finalUnit === 'gram') {
            $item->weight = $finalQty / 1000.0;
        } elseif ($finalUnit === 'kg') {
            $item->weight = $finalQty;
        } else {
            $item->weight = null; // piece
        }

        $item->quantity_unit = $finalUnit;
        $item->quantity = $finalQty;
        $item->unit_price = (float) $product->price;

        if (array_key_exists('special_instructions', $data)) {
            $item->special_instructions = $data['special_instructions'];
        }

        $item->save();

        return $this->returnResponse($item->load(['product.category', 'cuttype']), 'Cart item updated');
    }
}
