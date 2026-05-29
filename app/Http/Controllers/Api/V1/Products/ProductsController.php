<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Products;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

final class ProductsController extends ResponseController
{
    /**
     * Get a list of products.
     */
    public function index(): JsonResponse
    {
        $products = Product::with(['category.meta', 'meta', 'activeProductCuts'])
            ->visibleForCatalog()
            ->get();

        return $this->returnResponse($products, 'Products retrieved successfully.');
    }

    public function fetchOne(Product $product): JsonResponse
    {
        $product->load(['category.meta', 'cuttypes', 'meta']);

        return $this->returnResponse($product, 'Product retrieved successfully.');
    }
}
