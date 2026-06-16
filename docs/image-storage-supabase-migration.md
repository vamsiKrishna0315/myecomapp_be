# YumEat Image Storage Migration Tracker

## Project Status

Current Phase: Phase 3 - Filament Upload Migration
Status: In progress; implementation complete, verification pending
Last Updated: 2026-06-14

---

## Architecture Decision Records (ADR)

### Decision 001

Date: 2026-06-14
Decision: Move toward a backend-owned media abstraction where Laravel stores provider-neutral media keys, generates public URLs, and the frontend only consumes URL fields from API payloads.
Reason: The current codebase already exposes appended URL accessors in several models, but the frontend still contains fallback logic that reconstructs asset paths and sometimes hardcodes `/storage`. That creates duplicated URL logic and makes future migration to Supabase Storage, S3, or R2 brittle.
Impact: The backend becomes the single source of truth for media addressing. Frontend code should stop inferring storage locations and should render only URL fields returned by API responses.
Alternatives considered: Keep frontend path reconstruction; keep local public disk as the long-term source of truth; introduce a separate media microservice. All three were rejected because they preserve storage coupling in the wrong layer.

### Decision 002

Date: 2026-06-14
Decision: Restrict media uploads to a closed set of public categories backed by an enum and a shared path policy.
Reason: Uploads must not accept arbitrary user-controlled directories. The application should own the allowed object-key layout so local disk and Supabase stay in parity, while private media classes remain documented but unwritable until the privacy policy is defined.
Impact: Upload callers must use `MediaCategory` values. Public media categories are `products`, `categories`, `banners`, `flash-banners`, `why-us`, `stores`, `cut-types`, `product-cuts`, `seo`, and `reviews`. Document categories are `drivers`, `licenses`, `insurance`, `registrations`, and `user-documents`.
Alternatives considered: Accept raw directory strings; infer directories from filenames; allow all categories by default. Each would weaken path ownership and increase migration risk.

### Decision 003

Date: 2026-06-14
Decision: Share path normalization and object-key composition across both media providers.
Reason: LocalProvider and SupabaseProvider must generate the same object key structure, reject traversal segments, and normalize leading slashes consistently so the migration can switch providers without changing the database contract.
Impact: `publicUrl`, `delete`, and `exists` operate on normalized object keys only. The two providers now differ only in transport, not in path semantics.
Alternatives considered: Duplicate normalization per provider; let the service and provider each define their own path rules; keep path cleanup in model accessors. All of these would allow drift.

### Decision 004

Date: 2026-06-14
Decision: Centralize Filament upload wiring through a reusable helper that delegates file persistence and deletion to `MediaService`.
Reason: Filament forms should describe the field intent, not storage paths. The helper keeps upload behavior consistent across resources while preserving the current local provider.
Impact: Resource schemas now attach a shared helper instead of hardcoding `disk('public')` and `directory(...)`. The helper uses `MediaCategory` to choose the object-key prefix and keeps database values provider-neutral.
Alternatives considered: Duplicate the upload hooks in every form; keep `disk('public')` and `directory(...)` in each resource; create custom per-resource save logic. Each would reintroduce drift.

---

## Current Frontend Contract

Document:

The frontend currently consumes a mixed contract:

- It prefers API-provided `*_url` fields when present.
- It falls back to local path reconstruction through `assetUrl(path)` in multiple components.
- One helper still appends `/storage/` manually, which ties the UI to the current Laravel public-disk layout.

How images are currently displayed:

- Home page renders banner, category, product, and why-us images from `siteData`.
- Product and category screens render `product.primary_image_url` or fallback to `assetUrl(product.primary_image)`.
- Orders screen renders `item.product?.primary_image_url`, then `product?.primary_image_url`, then `assetUrl(product.primary_image)`.
- Navbar renders category images from `cat.category_image_url || assetUrl(cat.category_image)`.
- Search results render `product.primary_image_url || getAssetUrl(product.primary_image)`.

Use of `image_url` fields:

- `primary_image_url` on products.
- `category_image_url` on categories.
- `banner_path_url` on banners.
- `image_url` on product cuts.
- `icon_url` on cut types.

Use of `assetUrl()`:

- Provided by `components/Context/SiteDataContext.jsx`.
- Also duplicated by `components/ProductData/Search.jsx`.
- It is used as a fallback whenever the backend does not provide a URL field.

Hardcoded storage paths:

- `components/ProductData/Search.jsx:95-100` appends `/storage/` manually.
- `components/LandingPage/HomePage.jsx:798` builds `assetUrl(\`storage/${banner.image}\`)`.
- `database/seeders/data/site-data.php` seeds many relative `images/...` paths that assume a local asset layout.

Components affected:

- `components/LandingPage/HomePage.jsx`
- `components/ProductData/Search.jsx`
- `components/Category/CategoryPageClient.jsx`
- `components/ProductPage/ProductDetailPageClient.jsx`
- `components/Navbar/Navbar.jsx`
- `app/orders/page.js`
- `components/Context/SiteDataContext.jsx`

Code references:

- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\components\Context\SiteDataContext.jsx:22-28`
- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\components\ProductData\Search.jsx:95-100`
- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\components\LandingPage\HomePage.jsx:585-585`
- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\components\LandingPage\HomePage.jsx:798-798`
- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\components\LandingPage\HomePage.jsx:1010-1010`
- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\components\LandingPage\HomePage.jsx:1113-1113`
- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\components\LandingPage\HomePage.jsx:1215-1215`
- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\components\LandingPage\HomePage.jsx:1307-1307`
- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\components\Category\CategoryPageClient.jsx:208-208`
- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\components\ProductPage\ProductDetailPageClient.jsx:208-208`
- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\components\Navbar\Navbar.jsx:241-241`
- `C:\Users\ADMIN\Herd\Licious-clone\licious-clone-nextjs\app\orders\page.js:280-283`

---

## Current Backend Image Architecture

Document:

The backend currently stores image references as relative paths in the database and converts them to public URLs through model accessors. The API layer returns Eloquent models directly, so appended URL attributes become part of the JSON contract automatically.

Database tables and columns:

- `products.primary_image`, `products.images`
- `categories.category_image`
- `banners.banner_path`
- `flash_banners.image`
- `why_us.image`
- `cut_types.icon`
- `product_cuts.image`
- `stores.logo`, `stores.favicon`
- `meta_tags.og_image`, `meta_tags.twitter_image`
- `drivers.profile_image`, `drivers.driver_license_image`, `drivers.vehicle_registration_image`, `drivers.insurance_image`
- `order_reviews.images`

Current storage locations:

- Filament uploads write to the `public` disk.
- Laravel `public` disk is configured to `storage/app/public`.
- `storage:link` exposes that disk at `/storage`.

Upload controllers:

- The image upload flow is mostly driven by Filament `FileUpload` components instead of custom controller upload endpoints.
- `SiteController` only reads and returns image-bearing models.

Services:

- `app/Console/Commands/MigrateMediaToPublic.php` is a one-off migration command that copies media to the public disk and rewrites DB paths.

Traits:

- `app/Traits/HasMetaTag.php` is SEO-related and uses `url()` for canonical links, not media storage.

Helpers:

- Frontend helpers `assetUrl()` and `getAssetUrl()` still reconstruct storage URLs.

Filesystem configuration:

- `config/filesystems.php` defines `local`, `public`, and `s3` disks.
- The active default disk is still `local` unless overridden by `FILESYSTEM_DISK`.
- The `public` disk URL is hardwired to `APP_URL/storage`.

API Resources:

- There is no dedicated `app/Http/Resources` layer for these media payloads.
- Controllers return models through `ResponseController::returnResponse()`.

Accessors:

- Product, Category, Banner, ProductCut, and CutType each expose URL-style accessors.
- These accessors currently call `Storage::disk('public')->url(...)`.

Transformers:

- No standalone transformer layer was found for image URL generation.

For every discovery:

File: `C:\Users\ADMIN\Herd\laravel-project\app\Models\Product.php:92-321`
Purpose: Product catalog model and public image accessors.
Current behavior: Appends `primary_image_url` and `images_urls`; URLs are generated with `Storage::disk('public')->url(...)`.
Issues: Tightly coupled to the public disk and local storage conventions.
Recommended change: Move URL generation behind a media service or storage adapter and keep the accessor contract stable.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Models\Category.php:24-68`
Purpose: Category model and category image URL accessor.
Current behavior: Appends `category_image_url` from the public disk.
Issues: Same public-disk coupling as products.
Recommended change: Preserve `category_image_url` but generate it through the storage abstraction.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Models\Banner.php:12-32`
Purpose: Homepage banner model.
Current behavior: Appends `banner_path_url` from the public disk.
Issues: Same storage coupling and no provider abstraction.
Recommended change: Keep the JSON field but generate URLs via a provider-neutral media service.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Models\ProductCut.php:61-176`
Purpose: Product cut model and image URL accessor.
Current behavior: Appends `image_url` from the public disk.
Issues: Public-disk dependency.
Recommended change: Route through the same media abstraction used by products and categories.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Models\CutType.php:28-62`
Purpose: Cut type model and icon URL accessor.
Current behavior: Appends `icon_url`; leaves absolute URLs untouched, otherwise generates a public-disk URL.
Issues: Mixed contract, because the field may be a path or a full URL.
Recommended change: Normalize storage keys and resolve all public URLs in one place.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Http\Controllers\Api\V1\Site\SiteController.php:122-161`
Purpose: Site-wide API payload assembler.
Current behavior: Returns Eloquent models directly for banners, categories, why-us, store, feedback, meta tags, flash banners, and products.
Issues: Frontend receives a mixed payload and still compensates for missing or relative image URLs.
Recommended change: Keep payload shape stable but ensure all media-bearing models emit canonical URL fields.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Http\Controllers\Api\V1\Products\ProductsController.php:16-29`
Purpose: Product list and product detail API.
Current behavior: Returns product models with loaded relations.
Issues: URL generation is implicit and model-driven rather than explicit in a resource layer.
Recommended change: Add a media-safe resource layer only if needed; otherwise keep model accessors stable and provider-neutral.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Http\Controllers\Api\V1\Category\CategoryController.php:17-83`
Purpose: Category listing/search API.
Current behavior: Returns categories and nested products directly.
Issues: Same implicit serialization risk and storage coupling.
Recommended change: Preserve endpoint shape during migration while moving image resolution to the backend media layer.

File: `C:\Users\ADMIN\Herd\laravel-project\config\filesystems.php:14-79`
Purpose: Filesystem disks and storage link configuration.
Current behavior: `public` disk maps to `storage/app/public` and `/storage`; `s3` is defined but not the active media source of truth.
Issues: App is effectively locked to local public disk semantics.
Recommended change: Introduce a provider-backed media disk or adapter and make the provider configurable.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Console\Commands\MigrateMediaToPublic.php:22-175`
Purpose: One-off migration from private/default storage to public disk.
Current behavior: Copies media between local/public disks and rewrites DB paths for categories, banners, product cuts, products, and cut types.
Issues: This is a local-disk migration, not a cloud-storage migration; path normalization rules are brittle and filename-based.
Recommended change: Replace with a storage-provider migration tool that can move from local/public to Supabase, S3, or R2.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Products\Schemas\ProductsForm.php:131-145`
Purpose: Admin upload form for product media.
Current behavior: Uploads `primary_image` and `images` to the `public` disk under `products`.
Issues: Hardcoded disk and directory assumptions.
Recommended change: Switch to a configurable media service or custom Filament upload action.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Categories\Schemas\CategoriesForm.php:36-45`
Purpose: Admin upload form for category media.
Current behavior: Uploads category images to `public/categories`.
Issues: Hardcoded local-public storage path.
Recommended change: Route through provider-neutral storage.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Banners\Schemas\BannerForm.php:21-30`
Purpose: Admin upload form for banner media.
Current behavior: Uploads to `public/banners`.
Issues: Hardcoded local-public storage path.
Recommended change: Use provider-neutral storage keys and URL generation.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\FlashBanners\Schemas\FlashBannerForm.php:21-30`
Purpose: Admin upload form for flash banner media.
Current behavior: Uploads to `public/flash-banners`.
Issues: Hardcoded local-public storage path.
Recommended change: Use provider-neutral storage keys and URL generation.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\WhyUs\Schemas\WhyUsForm.php:34-40`
Purpose: Admin upload form for why-us media.
Current behavior: Uploads to `public/why-us`.
Issues: Hardcoded local-public storage path.
Recommended change: Use provider-neutral storage keys and URL generation.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\ProductCuts\Schemas\ProductCutForm.php:85-89`
Purpose: Admin upload form for product cut media.
Current behavior: Uploads to `public/product-cuts`.
Issues: Hardcoded local-public storage path.
Recommended change: Use provider-neutral storage keys and URL generation.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\CutTypes\Schemas\CutTypeForm.php:22-23`
Purpose: Admin form for cut type icon path.
Current behavior: Stores raw icon text, not an explicit storage-managed upload.
Issues: Path consistency is not enforced.
Recommended change: Convert to a proper upload flow or a validated media key.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Drivers\Schemas\DriverForm.php:181-227`
Purpose: Driver document uploads.
Current behavior: Uses public visibility and hardcoded subdirectories under `drivers/...`.
Issues: Same provider coupling, plus mixed document types.
Recommended change: Keep public/private intent explicit per asset class and centralize storage handling.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Drivers\Tables\DriversTable.php:22-26`
Purpose: Driver table preview.
Current behavior: Uses a local hardcoded fallback image at `/images/default-avatar.png`.
Issues: Local asset fallback is fine for UI, but should not be mistaken for media storage.
Recommended change: Keep as a UI placeholder only.

File: `C:\Users\ADMIN\Herd\laravel-project\app\Http\Controllers\Api\V1\ResponseController.php:19-25`
Purpose: API response envelope.
Current behavior: Returns `success`, `message`, and `data`.
Issues: None for media itself.
Recommended change: Keep stable.

---

## Storage Audit

Identified Storage facade usage:

- `C:\Users\ADMIN\Herd\laravel-project\app\Console\Commands\MigrateMediaToPublic.php:56,66,74,76,89,96,99`
- `C:\Users\ADMIN\Herd\laravel-project\app\Models\Banner.php:31`
- `C:\Users\ADMIN\Herd\laravel-project\app\Models\Category.php:67`
- `C:\Users\ADMIN\Herd\laravel-project\app\Models\CutType.php:62`
- `C:\Users\ADMIN\Herd\laravel-project\app\Models\Product.php:310,320`
- `C:\Users\ADMIN\Herd\laravel-project\app\Models\ProductCut.php:176`

Identified `store()` / `storeAs()` / `move()` usage:

- No direct `store()`, `storeAs()`, or `move()` calls were found in the audited Laravel application code under `app/`, `config/`, `database/`, `resources/`, `routes/`, and `tests/`.
- File uploads are currently driven by Filament `FileUpload` components, which are configured per resource form.

Identified `asset()` usage:

- No relevant backend image URL generation via `asset()` was found in the audited application code.

Identified `url()` usage that affects media or image-adjacent output:

- `C:\Users\ADMIN\Herd\laravel-project\app\Models\Category.php:58`
- `C:\Users\ADMIN\Herd\laravel-project\app\Models\Product.php:163`
- `C:\Users\ADMIN\Herd\laravel-project\app\Models\MetaTag.php:139`
- `C:\Users\ADMIN\Herd\laravel-project\app\Models\CutType.php:58-62`
- `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Drivers\Tables\DriversTable.php:26`
- `C:\Users\ADMIN\Herd\laravel-project\resources\views\welcome.blade.php:14,28`

Identified `public_path()` / `storage_path()` usage:

- `C:\Users\ADMIN\Herd\laravel-project\config\filesystems.php:37,45,79`
- `C:\Users\ADMIN\Herd\laravel-project\config\cache.php:53-54`
- `C:\Users\ADMIN\Herd\laravel-project\config\logging.php:65,72,129`
- `C:\Users\ADMIN\Herd\laravel-project\config\session.php:65`
- `C:\Users\ADMIN\Herd\laravel-project\resources\views\welcome.blade.php:14`

Risk note:

- The current `public` disk assumes Laravel-managed object URLs and local filesystem paths. Supabase migration must remove that assumption from all runtime code paths.

---

## Database Audit

| Table | Column | Current Format | Example | Migration Needed |
| ----- | ------ | -------------- | ------- | ---------------- |
| `products` | `primary_image` | relative storage path string | `products/chicken-breast.jpg` | Yes, normalize to provider-neutral object key |
| `products` | `images` | JSON array of relative storage paths | `["products/a.jpg","products/b.jpg"]` | Yes, normalize each item |
| `categories` | `category_image` | relative storage path string | `categories/chicken.png` | Yes |
| `banners` | `banner_path` | relative storage path string | `banners/home-banner.jpg` | Yes |
| `flash_banners` | `image` | relative storage path string | `flash-banners/free-delivery.jpg` | Yes |
| `why_us` | `image` | relative storage path string | `why-us/farm-fresh.jpg` | Yes |
| `cut_types` | `icon` | raw string, may be path or absolute URL | `/images/icon.svg` or `https://...` | Yes, standardize |
| `product_cuts` | `image` | relative storage path string | `product-cuts/chop.jpg` | Yes |
| `stores` | `logo` | relative storage path string | `images/logo.png` | Yes |
| `stores` | `favicon` | relative storage path string | `images/favicon.png` | Yes |
| `meta_tags` | `og_image` | relative path or absolute URL | `/images/og-home.jpg` | Yes, normalize |
| `meta_tags` | `twitter_image` | relative path or absolute URL | `/images/twitter-home.jpg` | Yes, normalize |
| `drivers` | `profile_image` | relative storage path string | `drivers/profiles/a.jpg` | Yes |
| `drivers` | `driver_license_image` | relative storage path string or PDF path | `drivers/licenses/license.pdf` | Yes |
| `drivers` | `vehicle_registration_image` | relative storage path string or PDF path | `drivers/registrations/reg.pdf` | Yes |
| `drivers` | `insurance_image` | relative storage path string or PDF path | `drivers/insurance/policy.pdf` | Yes |
| `order_reviews` | `images` | JSON array of relative storage paths | `["reviews/1.jpg","reviews/2.jpg"]` | Yes |

Source references:

- `C:\Users\ADMIN\Herd\laravel-project\database\migrations\2025_10_18_055814_create_products_table.php:16-58`
- `C:\Users\ADMIN\Herd\laravel-project\database\migrations\2025_12_06_145934_add_is_live_and_category_image_to_categories_table.php:16-19`
- `C:\Users\ADMIN\Herd\laravel-project\database\migrations\2025_10_17_174612_create_banners_table.php:14-23`
- `C:\Users\ADMIN\Herd\laravel-project\database\migrations\2025_12_09_155146_create_flash_banners_table.php:16-24`
- `C:\Users\ADMIN\Herd\laravel-project\database\migrations\2025_10_18_051654_create_why_us_table.php:14-23`
- `C:\Users\ADMIN\Herd\laravel-project\database\migrations\2025_10_18_063903_create_product_cuts_table.php:47-90`
- `C:\Users\ADMIN\Herd\laravel-project\database\migrations\2025_10_17_180528_create_stores_table.php:14-41`
- `C:\Users\ADMIN\Herd\laravel-project\database\migrations\2025_11_23_000001_create_meta_tags_table.php:16-46`
- `C:\Users\ADMIN\Herd\laravel-project\database\migrations\2025_10_18_151526_create_create_drivers_tables_table.php:14-45`
- `C:\Users\ADMIN\Herd\laravel-project\database\migrations\2025_10_19_052637_create_order_reviews_table.php:16-27`

---

## API Contract Audit

### Endpoint: `GET /api/v1/customer/site/site-data`

Current response: JSON envelope with `success`, `message`, `data`; `data` contains `banners`, `categories`, `why_us`, `store_data`, `store_contact_info`, `feedback`, `meta_tags`, `flash_banners`, `tax`, `products`.
Image fields: `banner_path_url`, `category_image_url`, `primary_image_url`, `images_urls`, `image_url`, `icon_url`, `logo`, `favicon`, `og_image`, `twitter_image`.
Where the URL is generated: Model accessors and some frontend fallback helpers.
Frontend dependency: Home page consumes this endpoint as the primary site-data bootstrap.
Migration impact: Highest. This endpoint is the main contract for homepage, category tiles, and featured products.

### Endpoint: `GET /api/v1/customer/products`

Current response: JSON envelope with a product collection.
Image fields: `primary_image_url`, `images_urls`, `category_image_url` on loaded relations.
Where the URL is generated: Product and relation accessors.
Frontend dependency: Product lists, search, landing page sections, and SEO helpers.
Migration impact: High. Must preserve the `primary_image_url` field.

### Endpoint: `GET /api/v1/customer/products/{product}`

Current response: JSON envelope with one product record.
Image fields: `primary_image_url`, `images_urls`, `category_image_url`, `icon_url` on related models.
Where the URL is generated: Product accessors.
Frontend dependency: Product detail page and metadata generation.
Migration impact: High.

### Endpoint: `POST /api/v1/customer/category`

Current response: JSON envelope with category collection and nested products.
Image fields: `category_image_url`, `primary_image_url`, `images_urls`.
Where the URL is generated: Category and Product accessors.
Frontend dependency: Category page, search flow, and home page category sections.
Migration impact: High.

Source references:

- `C:\Users\ADMIN\Herd\laravel-project\routes\api.php:37-45`
- `C:\Users\ADMIN\Herd\laravel-project\app\Http\Controllers\Api\V1\ResponseController.php:19-25`
- `C:\Users\ADMIN\Herd\laravel-project\app\Http\Controllers\Api\V1\Site\SiteController.php:122-161`
- `C:\Users\ADMIN\Herd\laravel-project\app\Http\Controllers\Api\V1\Products\ProductsController.php:16-29`
- `C:\Users\ADMIN\Herd\laravel-project\app\Http\Controllers\Api\V1\Category\CategoryController.php:17-83`

---

## Technical Debt & Risks

- Inconsistent image formats exist across the codebase: relative path strings, absolute URLs, and path strings with and without leading slashes.
- Hardcoded `/storage` assumptions still exist in frontend fallback code.
- The backend currently assumes a local public filesystem at runtime, which is incompatible with a clean Supabase-first design.
- Some assets are seeded as local project-relative paths, which is fine for demo data but not for production media resolution.
- `CutType.icon` is especially risky because it accepts both raw URLs and storage paths.
- `MetaTag.og_image` and `MetaTag.twitter_image` are currently stored as raw strings and may bypass a single media policy.
- Driver documents mix public image handling and document handling, which will require a clear public/private policy before moving to object storage.
- The one-off migration command is filename-based and does not yet provide a provider abstraction.

---

## Target Architecture

Bucket structure:

- `products/`
- `categories/`
- `banners/`
- `flash-banners/`
- `why-us/`
- `cut-types/`
- `stores/`
- `drivers/`
- `reviews/`
- `seo/`

Naming conventions:

- Use UUID-based or content-hash-based object names where possible.
- Keep folder prefixes stable per media domain.
- Store only provider-neutral object keys in the database.
- Avoid encoding environment or hostname into the stored path.

File permissions:

- Public catalog media should be publicly readable from object storage.
- Sensitive driver documents should be treated as private or access-controlled until the product explicitly requires public exposure.

Public vs private assets:

- Public: product images, category images, banners, flash banners, why-us images, store logo/favicon, cut type icons, SEO open graph images if publicly intended.
- Private or restricted: driver license, insurance, vehicle registration, potentially customer-uploaded review images until policy is defined.

URL generation strategy:

- The backend should generate final public URLs through a media service or storage adapter.
- Eloquent accessors may remain, but they must delegate to the service instead of calling `Storage::disk('public')->url(...)` directly.
- Frontend and mobile apps should only consume URL fields returned by API responses.

CDN readiness:

- The storage provider should be swappable without changing the database contract.
- Public URLs should be CDN-friendly and cacheable.
- Prefer stable, origin-independent URLs from the API layer.

Future migration strategy to S3/R2:

- Introduce a `MediaService` abstraction in Laravel.
- Store canonical object keys in the database.
- Make the storage disk/provider configurable.
- Add provider-specific adapters for Supabase, S3, and R2.
- Keep API field names stable during the migration.

---

## Migration Plan

Phase 1 - Audit
[x] Completed
[ ] In progress
[ ] Not started

Tasks:

- [x] Inventory all image-related backend models, controllers, forms, and config.
- [x] Inventory frontend image consumers and fallback URL builders.
- [x] Document current API contract and storage coupling.
- [x] Record risks and target architecture in the migration tracker.

Phase 2 - Supabase Integration
[x] Completed
[ ] In progress
[ ] Not started

Tasks:

- [x] Add Supabase Storage configuration and environment variables.
- [x] Introduce a storage provider abstraction in Laravel.
- [x] Decide public and private bucket layout.
- [x] Map existing `public` disk behavior to provider-neutral object keys.
- [x] Add a central media category enum and path policy.
- [x] Restrict uploads to approved media categories only.
- [x] Keep LocalProvider and SupabaseProvider aligned on object-key structure.
- [x] Add tests for URL generation, upload, delete, exists, invalid paths, and provider selection.

Phase 2C - Media Storage Policy, Path Ownership, and Provider Parity
[x] Completed
[ ] In progress
[ ] Not started

Tasks:

- [x] Create a centralized media path/category definition.
- [x] Ensure MediaService rejects arbitrary upload directories.
- [x] Verify LocalProvider and SupabaseProvider generate the same object key structure.
- [x] Add or update tests for URL generation, upload, delete, exists, invalid paths, and provider switching.

Phase 3 - Filament Upload Migration
[ ] Completed
[x] In progress
[ ] Not started

Audit findings:

- `app/Filament/Resources/Products/Schemas/ProductsForm.php` now routes primary and gallery uploads through the shared media helper with `MediaCategory::Products`.
- `app/Filament/Resources/Categories/Schemas/CategoriesForm.php` now routes category images through the shared media helper with `MediaCategory::Categories`.
- `app/Filament/Resources/Banners/Schemas/BannerForm.php` now routes banner uploads through the shared media helper with `MediaCategory::Banners`.
- `app/Filament/Resources/FlashBanners/Schemas/FlashBannerForm.php` now routes flash banner uploads through the shared media helper with `MediaCategory::FlashBanners`.
- `app/Filament/Resources/WhyUs/Schemas/WhyUsForm.php` now routes why-us images through the shared media helper with `MediaCategory::WhyUs`.
- `app/Filament/Resources/ProductCuts/Schemas/ProductCutForm.php` now routes product cut images through the shared media helper with `MediaCategory::ProductCuts`.
- `app/Filament/Resources/Stores/Schemas/StoreForm.php` now routes store logo and favicon uploads through the shared media helper with `MediaCategory::Stores`.
- `app/Filament/Resources/Drivers/Schemas/DriverForm.php` now routes driver profile and document uploads through the shared media helper with `MediaCategory::Drivers`, `MediaCategory::Licenses`, `MediaCategory::Registrations`, and `MediaCategory::Insurance`.
- `app/Filament/Resources/Users/Schemas/UsersForm.php` now routes address proof uploads through the shared media helper with `MediaCategory::UserDocuments`.
- `MediaService`, `LocalProvider`, `SupabaseProvider`, and `MediaUpload` now emit upload traces that include provider, category, bucket or disk, and normalized object keys so banner and product uploads can be traced end to end.
- Banner reads now flow through a single `BannerMediaService` so the admin table and site API resolve banner URLs from the same provider-aware code path.
- Banner deletes now route through a model observer that removes the banner object from the active media provider when the record is deleted.

Phase 3 tasks:

- [x] Wire Filament uploads through the media service helper.
- [x] Preserve provider-neutral object keys for all uploads.
- [x] Keep existing records and URLs working without changing API contracts.
- [x] Add tests for the upload helper and migrated resources.

Phase 4 - Existing Image Migration
[ ] Not started
[ ] In progress
[ ] Completed

Tasks:

- [ ] Build a migration command for current media records.
- [ ] Migrate product, category, banner, flash banner, why-us, cut type, store, driver, and review assets.
- [ ] Verify all legacy paths resolve after migration.
- [ ] Keep a rollback map for pre-migration paths.

Phase 5 - Frontend Simplification
[ ] Not started
[ ] In progress
[ ] Completed

Tasks:

- [ ] Remove frontend `assetUrl()` fallback logic for media URLs.
- [ ] Remove manual `/storage` concatenation.
- [ ] Standardize on backend-provided image URL fields.
- [ ] Update any SEO helpers that still assume relative paths.

---

## Change Log

2026-06-14

## Completed:

- Created the migration tracker for YumEat image storage.
- Completed the initial storage and frontend contract audit.
- Documented the target Supabase-first architecture.
- Started Phase 2 implementation planning for media infrastructure and abstraction.
- Added the Phase 2 media configuration and provider abstraction layer.
- Implemented the backend media service binding and provider selection.
- Added local and Supabase media provider implementations.
- Added unit coverage for media provider selection, upload, URL generation, and deletion flows.
- Hardened provider path normalization to reject traversal segments in media keys.
- Added a shared media category enum and path policy for upload ownership.
- Added public and private media categories to the upload policy.
- Kept both providers aligned on the same normalized object-key structure.
- Added a reusable Filament upload helper backed by `MediaService`.
- Migrated product, category, banner, flash banner, why-us, product cut, store, driver, and user document upload forms to the shared helper.
- Added unit coverage for the Filament helper, common public categories, and expanded media service coverage for private-category uploads.

## Files modified:

- `C:\Users\ADMIN\Herd\laravel-project\docs\image-storage-supabase-migration.md`
- `C:\Users\ADMIN\Herd\laravel-project\config\media.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Services\Media\MediaProviderInterface.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Services\Media\MediaService.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Services\Media\Providers\LocalProvider.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Services\Media\Providers\SupabaseProvider.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Providers\AppServiceProvider.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Enums\MediaCategory.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Services\Media\MediaPathPolicy.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Support\Filament\MediaUpload.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Products\Schemas\ProductsForm.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Categories\Schemas\CategoriesForm.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Banners\Schemas\BannerForm.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\FlashBanners\Schemas\FlashBannerForm.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\WhyUs\Schemas\WhyUsForm.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\ProductCuts\Schemas\ProductCutForm.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Stores\Schemas\StoreForm.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Drivers\Schemas\DriverForm.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Users\Schemas\UsersForm.php`
- `C:\Users\ADMIN\Herd\laravel-project\tests\Unit\Services\Media\MediaServiceTest.php`
- `C:\Users\ADMIN\Herd\laravel-project\tests\Unit\Support\Filament\MediaUploadTest.php`

## Reason:

- Establish a permanent source of truth before any code changes.
- Capture the current storage behavior, API contract, and migration plan.
- Record the approved Phase 2 scope and the files that will be introduced.
- Introduce a backend-only media abstraction while preserving current local-disk behavior by default.
- Document the implementation state after landing the media abstraction work.
- Close the traversal/path normalization gap in the new media layer.
- Finish Phase 3 with a shared Filament upload helper and provider parity guarantees.
- Remove Filament path/directory assumptions while preserving local storage behavior.

## Verification:

- Reviewed backend models, controllers, config, migrations, Filament forms, and the frontend image consumers.
- Searched for `Storage::`, `store()`, `storeAs()`, `move()`, `asset()`, `url()`, `public_path()`, and `storage_path()` usage.
- Confirmed the active API routes for products, category lookup, and site data.
- Confirmed there is no existing `config/media.php`.
- Confirmed `AppServiceProvider` already owns service binding and is the safest registration point for the new media service.
- Code implementation is present in the repo; runtime verification is still pending in this shell because the `php` executable is not available on PATH.
- Phase 3 code has been migrated, but these changes still need runtime verification in a PHP-enabled shell.

## Security Considerations

- Uploads now accept only `MediaCategory` enum values and route to a controlled object-key prefix.
- Traversal segments such as `..` are rejected before provider calls.
- Absolute URI schemes are rejected by the shared path policy.
- Local and Supabase providers now normalize keys the same way, which reduces drift and accidental path spoofing.
- Private document categories are documented and wired for driver and user-document uploads.

## Test Coverage

- `tests/Unit/Services/Media/MediaServiceTest.php` now covers provider selection, URL generation, upload, delete, exists, invalid traversal paths, and private-category uploads.
- `tests/Unit/Support/Filament/MediaUploadTest.php` covers Filament helper wiring, common public categories, single-file uploads, multiple uploads, driver documents, and user-document uploads.
- The Supabase test verifies request URLs and object-key parity with the local provider.
- The local test verifies compatibility with the public disk behavior and storage cleanup.
- The Filament helper tests verify the upload helper keeps the storage path off the form definition.

## Remaining Blockers

- Full Laravel test execution is still pending in this shell because `php` is not available on PATH.
- Phase 3 now needs runtime verification in a PHP-enabled shell before the next phase is approved.

## Phase 3 Implementation Notes

- The upload destination is now determined through `MediaCategory` and a shared Filament helper instead of hardcoded directories.
- The current active provider remains local, so the migration is invisible to the frontend and API consumers.
- Driver and user-document uploads are routed through the same path policy and provider abstraction as public media.
- Supabase Storage does not require physical folders; keys such as `banners/<filename>` and `products/<filename>` are virtual prefixes created by the object key, and the logs now show the exact prefix being written.

---

## Handoff Notes For Next Agent

Current progress:

- Phase 3 upload wiring is implemented in the repo.
- The backend still uses the local provider by default.
- Existing image accessors and API contracts were left unchanged.
- Filament resources no longer hardcode upload directories for media-bearing fields.
- Upload tracing is now available in the media service and providers, including the final object key for Filament banner and product uploads.

Pending tasks:

- Run the new media and Filament helper tests in a PHP-enabled shell.
- Confirm the upload helper behaves correctly in the real Laravel runtime.
- Inspect the new logs if a Supabase upload appears to succeed without the object showing in the bucket UI.
- Decide whether any additional document categories need to be added before Phase 4.

Known blockers:

- Full Laravel test execution is still pending in this shell because `php` is not available on PATH.
- Phase 4 should not begin until Phase 3 runtime verification is complete.

Important architectural decisions:

- Keep Laravel as the owner of media logic.

## 2026-06-15

## Completed:

- Added provider-level and service-level media upload logs for Filament traces.
- Documented that Supabase "folders" are virtual object-key prefixes, not physical directories.
- Added compatibility for `SUPABASE_BUCKET` as a fallback for `SUPABASE_PUBLIC_BUCKET` so the configured bucket name is respected during uploads.
- Centralized banner retrieval through a banner-specific media service and observer so banner URLs and deletes use one provider-aware path.
- Switched the banner admin table to render the provider-aware banner URL instead of the raw storage key.

## Files modified:

- `C:\Users\ADMIN\Herd\laravel-project\app\Services\Media\MediaService.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Services\Media\Providers\LocalProvider.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Services\Media\Providers\SupabaseProvider.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Support\Filament\MediaUpload.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Services\Banners\BannerMediaService.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Observers\BannerObserver.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Http\Controllers\Api\V1\Site\SiteController.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Models\Banner.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Filament\Resources\Banners\Tables\BannersTable.php`
- `C:\Users\ADMIN\Herd\laravel-project\app\Providers\AppServiceProvider.php`
- `C:\Users\ADMIN\Herd\laravel-project\tests\Unit\Services\Banners\BannerMediaServiceTest.php`
- `C:\Users\ADMIN\Herd\laravel-project\docs\image-storage-supabase-migration.md`
- `C:\Users\ADMIN\Herd\laravel-project\config\media.php`

## Reason:

- Make banner and product upload targets observable during live Filament uploads.
- Clarify that `banners/...` and `products/...` are the object-key prefixes that appear in Supabase Storage.
- Prevent uploads from silently falling back to the default Supabase bucket when the legacy env var name is used.
- Keep banner retrieval and banner delete cleanup centralized so both the admin table and site API use the same banner-specific code path.

## Verification:

- Reviewed the current Phase 3 tracker sections.
- Added trace logging without changing database schema, API contracts, or upload destinations.
- Verified the banner retrieval path now flows through a single banner service for the API and admin table.
- Keep frontend consumers URL-only.
- Preserve existing API field names where possible to minimize breaking changes.
- Treat storage provider choice as replaceable.

Recommended next steps:

1. Run the media and Filament helper tests in a PHP-enabled shell.
2. Review the helper behavior for any remaining upload surfaces.
3. Approve the Phase 4 media migration once runtime verification is green.
