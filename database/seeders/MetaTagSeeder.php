<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\MetaTag;
use Illuminate\Database\Seeder;

final class MetaTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metaTags = [
            [
                'metaable_type' => 'App\Models\Page',
                'metaable_id' => 1,
                'meta_title' => 'Premium Fresh Meat & Poultry Online | Quality Non-Veg Delivery',
                'meta_description' => 'Order fresh, high-quality chicken, mutton, fish, and seafood online. Farm-fresh, hygienically processed meat delivered to your doorstep. Same-day delivery available.',
                'meta_keywords' => 'fresh meat online, chicken delivery, mutton home delivery, fish delivery, seafood online, non veg online, halal meat, fresh poultry',
                'og_title' => 'Premium Fresh Meat & Poultry Online | Quality Non-Veg Delivery',
                'og_description' => 'Order fresh, high-quality chicken, mutton, fish, and seafood online. Farm-fresh, hygienically processed meat delivered to your doorstep.',
                'og_image' => '/images/og-home.jpg',
                'og_type' => 'website',
                'twitter_card' => 'summary_large_image',
                'twitter_title' => 'Premium Fresh Meat & Poultry Online',
                'twitter_description' => 'Order fresh, high-quality chicken, mutton, fish, and seafood online. Farm-fresh meat delivered to your doorstep.',
                'twitter_image' => '/images/twitter-home.jpg',
                'canonical_url' => config('app.url'),
                'noindex' => false,
                'nofollow' => false,
                'json_ld' => json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'LocalBusiness',
                    'name' => 'Fresh Meat Store',
                    'description' => 'Premium quality fresh meat and poultry delivery',
                    'image' => config('app.url').'/images/logo.png',
                ]),
                'status' => 1,
                'show_live' => 1,
            ],
            [
                'metaable_type' => 'App\Models\Category',
                'metaable_id' => 1,
                'meta_title' => 'Fresh Chicken Online | Premium Quality Poultry Delivery',
                'meta_description' => 'Buy fresh chicken online - whole chicken, chicken breast, drumsticks, wings. Antibiotic-free, farm-fresh poultry delivered fresh daily.',
                'meta_keywords' => 'fresh chicken, chicken breast, chicken drumsticks, whole chicken, chicken wings, poultry online, fresh poultry delivery',
                'og_title' => 'Fresh Chicken Online | Premium Quality Poultry',
                'og_description' => 'Buy fresh chicken online - whole chicken, chicken breast, drumsticks, wings. Antibiotic-free, farm-fresh poultry.',
                'og_image' => '/images/categories/chicken.jpg',
                'og_type' => 'product.group',
                'twitter_card' => 'summary_large_image',
                'twitter_title' => 'Fresh Chicken Online',
                'twitter_description' => 'Premium quality chicken delivered fresh daily',
                'twitter_image' => '/images/categories/chicken.jpg',
                'canonical_url' => config('app.url').'/category/chicken',
                'noindex' => false,
                'nofollow' => false,
                'json_ld' => json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'ProductGroup',
                    'name' => 'Fresh Chicken',
                    'description' => 'Premium quality fresh chicken and poultry products',
                ]),
                'status' => 1,
                'show_live' => 1,
            ],
            [
                'metaable_type' => 'App\Models\Category',
                'metaable_id' => 2,
                'meta_title' => 'Fresh Mutton Online | Premium Goat Meat & Lamb Delivery',
                'meta_description' => 'Order fresh mutton online - goat meat, lamb chops, mutton curry cut, boneless mutton. Halal certified, hygienically processed, delivered fresh.',
                'meta_keywords' => 'fresh mutton, goat meat, lamb meat, mutton curry cut, boneless mutton, halal mutton, mutton online delivery',
                'og_title' => 'Fresh Mutton Online | Premium Goat Meat',
                'og_description' => 'Order fresh mutton online - goat meat, lamb chops, mutton curry cut. Halal certified and hygienically processed.',
                'og_image' => '/images/categories/mutton.jpg',
                'og_type' => 'product.group',
                'twitter_card' => 'summary_large_image',
                'twitter_title' => 'Fresh Mutton & Lamb Online',
                'twitter_description' => 'Premium quality mutton and lamb delivered fresh',
                'twitter_image' => '/images/categories/mutton.jpg',
                'canonical_url' => config('app.url').'/category/mutton',
                'noindex' => false,
                'nofollow' => false,
                'json_ld' => json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'ProductGroup',
                    'name' => 'Fresh Mutton & Lamb',
                    'description' => 'Premium quality fresh mutton and lamb products',
                ]),
                'status' => 1,
                'show_live' => 1,
            ],
            [
                'metaable_type' => 'App\Models\Category',
                'metaable_id' => 3,
                'meta_title' => 'Fresh Fish & Seafood Online | Ocean Fresh Delivery',
                'meta_description' => 'Buy fresh fish and seafood online - pomfret, salmon, prawns, crabs, lobster. Ocean-fresh, cleaned and cut, delivered on ice to your doorstep.',
                'meta_keywords' => 'fresh fish online, seafood delivery, prawns online, salmon fish, pomfret fish, crabs online, fresh lobster, fish delivery',
                'og_title' => 'Fresh Fish & Seafood Online | Ocean Fresh',
                'og_description' => 'Buy fresh fish and seafood - pomfret, salmon, prawns, crabs. Ocean-fresh, cleaned and delivered on ice.',
                'og_image' => '/images/categories/seafood.jpg',
                'og_type' => 'product.group',
                'twitter_card' => 'summary_large_image',
                'twitter_title' => 'Fresh Fish & Seafood Online',
                'twitter_description' => 'Ocean-fresh fish and seafood delivered on ice',
                'twitter_image' => '/images/categories/seafood.jpg',
                'canonical_url' => config('app.url').'/category/seafood',
                'noindex' => false,
                'nofollow' => false,
                'json_ld' => json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'ProductGroup',
                    'name' => 'Fresh Fish & Seafood',
                    'description' => 'Premium quality fresh fish and seafood products',
                ]),
                'status' => 1,
                'show_live' => 1,
            ],
            [
                'metaable_type' => 'App\Models\Page',
                'metaable_id' => 2,
                'meta_title' => 'About Us | Premium Fresh Meat & Seafood Delivery',
                'meta_description' => 'Learn about our commitment to delivering farm-fresh, hygienically processed meat and seafood. Quality guaranteed, delivered fresh to your doorstep.',
                'meta_keywords' => 'about us, fresh meat delivery, quality meat, farm fresh, hygienic processing',
                'og_title' => 'About Us | Premium Fresh Meat Delivery',
                'og_description' => 'Learn about our commitment to quality and freshness',
                'og_image' => '/images/about-us.jpg',
                'og_type' => 'website',
                'twitter_card' => 'summary_large_image',
                'twitter_title' => 'About Us | Fresh Meat Delivery',
                'twitter_description' => 'Quality and freshness guaranteed',
                'twitter_image' => '/images/about-us.jpg',
                'canonical_url' => config('app.url').'/about-us',
                'noindex' => false,
                'nofollow' => false,
                'json_ld' => null,
                'status' => 1,
                'show_live' => 1,
            ],
        ];

        foreach ($metaTags as $tag) {
            MetaTag::create($tag);
        }
    }
}
