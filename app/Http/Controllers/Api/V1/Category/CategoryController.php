<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Category;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CategoryController extends ResponseController
{
    /**
     * Get a list of categories.
     */
    public function index(Request $request): JsonResponse
    {
        $categoryName = $request->input('category_name');

        if ($categoryName) {
            // Support both array and comma-separated string
            if (is_string($categoryName)) {
                $categoryNames = array_map('trim', explode(',', $categoryName));
            } elseif (is_array($categoryName)) {
                $categoryNames = $categoryName;
            } else {
                $categoryNames = [$categoryName];
            }

            $categories = Category::with([
                'meta',
                'products' => function ($query) {
                    $query->visibleForCatalog()->with(['cuttypes', 'meta']);
                },
            ])
                ->where('status', 1)
                ->whereIn('category_name', $categoryNames)
                ->get();

            // If no categories found, search in product names
            if ($categories->isEmpty()) {
                $categories = Category::with([
                    'meta',
                    'products' => function ($q) use ($categoryNames) {
                        $q->visibleForCatalog()
                        ->where(function ($query) use ($categoryNames) {
                            foreach ($categoryNames as $name) {
                                $query->orWhere('name', 'LIKE', "%{$name}%");
                            }
                        })
                        ->with(['cuttypes', 'meta']);
                    },
                ])
                    ->where('status', 1)
                    ->whereHas('products', function ($q) use ($categoryNames) {
                        $q->visibleForCatalog()
                            ->where(function ($query) use ($categoryNames) {
                                foreach ($categoryNames as $name) {
                                    $query->orWhere('name', 'LIKE', "%{$name}%");
                                }
                            });
                    })
                    ->get();
            }

            if ($categories->isEmpty()) {
                return $this->returnResponse([], 'Category not found.', 404);
            }

            return $this->returnResponse($categories, 'Products of selected categories retrieved successfully.');
        }
        $categories = Category::with([
            'meta',
            'products' => function ($query) {
                $query->visibleForCatalog()->with(['cuttypes', 'meta']);
            },
        ])
            ->where('status', 1)
            ->get();

        return $this->returnResponse($categories, 'Categories retrieved successfully.');

    }
}
