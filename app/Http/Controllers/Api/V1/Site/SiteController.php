<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Site;

use App\Http\Controllers\Api\V1\ResponseController;
use App\Models\Category;
use App\Models\Combo;
use App\Models\FlashBanner;
use App\Models\Feedback;
use App\Models\MetaTag;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreContactInfo;
use App\Models\WhyUs;
use App\Services\Banners\BannerMediaService;

final class SiteController extends ResponseController
{
    public function __construct(
        private readonly BannerMediaService $bannerMediaService
    ) {}

    /**
     * Get active banners
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBanners()
    {
        return $this->bannerMediaService->activeBanners();
    }

    /**
     * Get site why Us?
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWhyUs()
    {
        $whyUs = WhyUs::where('status', 1)
            ->where('show_live', 1)
            ->get();

        return $whyUs ?? [];
    }

    /**
     * Get site about Us
     *
     * @return \Illuminate\Http\JsonResponse
     */
    // public function getAboutUs()
    // {
    //     $aboutUs = \App\Models\AboutUs::where('status', 1)
    //                      ->where('show_live', 1)
    //                      ->first();
    //     return $aboutUs ?? [];
    // }

    /**
     * Get site contact Us
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStoreData()
    {
        $storeData = Store::where('status', 1)
                        //  ->where('show_live', 1)
            ->first();

        return $storeData ?? [];
    }

    /**
     * Get site terms and conditions and policies
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTermsAndConditions() {}

    /**
     * Get site feedback
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getfeedback()
    {
        $feedback = Feedback::where('status', 1)
            ->where('show_live', 1)
            ->get();

        return $feedback ?? [];
    }

    /**
     * Get site FAQs
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFAQs() {}

    /**
     * Get site metaTags
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMetaTags()
    {
        $metaTags = MetaTag::with('metaable')
            ->where('status', 1)
            ->get();

        return $metaTags ?? [];
    }

    /**
     * Get all site data in a single DB query
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllSiteData()
    {
        $categories = Category::with([
            'meta',
            'products' => function ($q) {
                $q->visibleForCatalog()->with(['cuttypes', 'meta']);
            },
        ])
            ->where('status', 1)
            ->where('is_live', 1)
            ->get();

        $comboCategory = $categories->first(function (Category $category): bool {
            return mb_strtolower((string) $category->category_type) === 'combo'
                || $category->slug === 'combo';
        });

        if ($comboCategory) {
            $combos = Combo::with('comboItems')
                ->get()
                ->map(function (Combo $combo): array {
                    return [
                        'id' => $combo->id,
                        'name' => $combo->name,
                        'description' => $combo->description,
                        'total_price' => $combo->total_price,
                        'currency' => $combo->currency,
                        'status' => $combo->status,
                        'is_visible' => $combo->is_visible,
                        'display_order' => $combo->display_order,
                        'created_at' => $combo->created_at,
                        'updated_at' => $combo->updated_at,
                        'product_ids' => $combo->comboItems
                            ->pluck('product_id')
                            ->unique()
                            ->values()
                            ->all(),
                        'weights' => $combo->comboItems
                            ->pluck('weight')
                            ->values()
                            ->all(),
                        'weight_units' => $combo->comboItems
                            ->pluck('weight_unit')
                            ->values()
                            ->all(),
                        'quantities' => $combo->comboItems
                            ->pluck('quantity')
                            ->values()
                            ->all(),
                    ];
                })
                ->values();

            $comboCategory->setAttribute('combos', $combos);
        }

        $data = [
            'banners' => $this->bannerMediaService->activeBanners(),
            'categories' => $categories,
            'why_us' => WhyUs::where('status', 1)
                ->where('show_live', 1)
                ->get(),
            'store_data' => Store::where('status', 1)
                ->first(),
            'store_contact_info' => StoreContactInfo::where('status', 1)
                ->where('show_live', 1)
                ->first(),
            'feedback' => Feedback::where('status', 1)
                ->where('show_live', 1)
                ->get(),
            'meta_tags' => MetaTag::with('metaable')
                ->where('status', 1)
                ->get(),
            'flash_banners' => FlashBanner::where('status', 1)
                ->where('is_live', 1)
                ->latest()
                ->first(),
            'tax' => 18,
            'products' => Product::with(['category.meta', 'cuttypes', 'meta'])
                ->visibleForCatalog()
                ->get(),
        ];

        return $this->returnResponse($data, 'Site data retrieved successfully');
    }
}
