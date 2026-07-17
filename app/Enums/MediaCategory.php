<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaCategory: string
{
    case Products = 'products';
    case Categories = 'categories';
    case Banners = 'banners';
    case FlashBanners = 'flash-banners';
    case WhyUs = 'why-us';
    case Stores = 'stores';
    case CutTypes = 'cut-types';
    case ProductCuts = 'product-cuts';
    case Seo = 'seo';
    case Reviews = 'reviews';

    case Drivers = 'drivers';
    case Licenses = 'licenses';
    case Insurance = 'insurance';
    case Registrations = 'registrations';
    case UserDocuments = 'user-documents';
    case ProofOfDelivery = 'proof-of-delivery';

    /**
     * @return array<int, self>
     */
    public static function publicCategories(): array
    {
        return [
            self::Products,
            self::Categories,
            self::Banners,
            self::FlashBanners,
            self::WhyUs,
            self::Stores,
            self::CutTypes,
            self::ProductCuts,
            self::Seo,
            self::Reviews,
        ];
    }

    /**
     * @return array<int, self>
     */
    public static function privateCategories(): array
    {
        return [
            self::Drivers,
            self::Licenses,
            self::Insurance,
            self::Registrations,
            self::UserDocuments,
            self::ProofOfDelivery,
        ];
    }

    /**
     * @return array<int, self>
     */
    public static function allCategories(): array
    {
        return [
            ...self::publicCategories(),
            ...self::privateCategories(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function publicValues(): array
    {
        return array_map(static fn (self $category): string => $category->value, self::publicCategories());
    }

    /**
     * @return array<int, string>
     */
    public static function privateValues(): array
    {
        return array_map(static fn (self $category): string => $category->value, self::privateCategories());
    }

    /**
     * @return array<int, string>
     */
    public static function allValues(): array
    {
        return array_map(static fn (self $category): string => $category->value, self::allCategories());
    }

    public function isPublic(): bool
    {
        return in_array($this, self::publicCategories(), true);
    }

    public function isPrivate(): bool
    {
        return in_array($this, self::privateCategories(), true);
    }
}
