<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\FlashBanner;
use App\Models\MetaTag;
use App\Models\Page;
use App\Models\Store;
use App\Models\StoreContactInfo;
use App\Models\WhyUs;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class SiteDataSeeder extends Seeder
{
    public function run(): void
    {
        $data = require database_path('seeders/data/site-data.php');

        foreach ($data['pages'] ?? [] as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                Arr::only($pageData, ['title', 'slug', 'type', 'status'])
            );
        }

        foreach ($data['banners'] ?? [] as $bannerData) {
            DB::table('banners')->updateOrInsert(
                ['banner_name' => $bannerData['banner_name']],
                $bannerData
            );
        }

        foreach ($data['why_us'] ?? [] as $whyUsData) {
            WhyUs::updateOrCreate(
                ['event_title' => $whyUsData['event_title']],
                Arr::only($whyUsData, ['event_title', 'description', 'year', 'image', 'show_live', 'status'])
            );
        }

        $storeData = $data['store'] ?? null;
        if (is_array($storeData)) {
            $store = Store::updateOrCreate(
                ['id' => $storeData['id'] ?? 1],
                Arr::except($storeData, ['id'])
            );

            $contactInfoData = $data['store_contact_info'] ?? null;
            if (is_array($contactInfoData)) {
                StoreContactInfo::updateOrCreate(
                    ['store_id' => $store->id],
                    Arr::except($contactInfoData, ['store_id']) + ['store_id' => $store->id]
                );
            }
        }

        foreach ($data['feedback'] ?? [] as $feedbackData) {
            Feedback::updateOrCreate(
                [
                    'description' => $feedbackData['description'],
                    'rating' => $feedbackData['rating'],
                ],
                Arr::only($feedbackData, ['description', 'rating', 'status', 'show_live'])
            );
        }

        foreach ($data['flash_banners'] ?? [] as $flashBannerData) {
            FlashBanner::updateOrCreate(
                ['name' => $flashBannerData['name']],
                Arr::only($flashBannerData, ['name', 'redirect_link', 'image', 'status', 'is_live'])
            );
        }

        foreach ($data['meta_tags'] ?? [] as $metaTagData) {
            $metaableType = $metaTagData['metaable_type'] ?? null;
            $metaable = null;

            if ($metaableType === Page::class && filled($metaTagData['page_slug'] ?? null)) {
                $metaable = Page::query()->where('slug', $metaTagData['page_slug'])->first();
            }

            if (! $metaable) {
                continue;
            }

            MetaTag::updateOrCreate(
                [
                    'metaable_type' => $metaableType,
                    'metaable_id' => $metaable->getKey(),
                ],
                Arr::only($metaTagData, [
                    'meta_title',
                    'meta_description',
                    'meta_keywords',
                    'og_title',
                    'og_description',
                    'og_image',
                    'og_type',
                    'twitter_card',
                    'twitter_title',
                    'twitter_description',
                    'twitter_image',
                    'canonical_url',
                    'noindex',
                    'nofollow',
                    'json_ld',
                ]) + [
                    'metaable_type' => $metaableType,
                    'metaable_id' => $metaable->getKey(),
                    'status' => true,
                    'show_live' => true,
                ]
            );
        }
    }
}
