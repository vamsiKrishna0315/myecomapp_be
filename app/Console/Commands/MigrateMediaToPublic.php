<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Category;
use App\Models\CutType;
use App\Models\Driver;
use App\Models\FlashBanner;
use App\Models\MetaTag;
use App\Models\OrderReview;
use App\Models\Product;
use App\Models\ProductCut;
use App\Models\Store;
use App\Models\WhyUs;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

final class MigrateMediaToPublic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:migrate-to-public {--dry : Run without writing changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move existing media from private/default disk to the public disk and update DB paths';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry');

        $this->info('Starting media migration to public disk'.($dry ? ' (dry-run)' : ''));

        $moved = 0;
        $skipped = 0;
        $missing = 0;
        $updated = 0;

        $persist = static function (object $row) use ($dry): void {
            if (! $dry) {
                $row->save();
            }
        };

        // Helper to migrate a single path
        $migratePath = function (?string $path, string $targetDir) use ($dry, &$moved, &$skipped, &$missing) {
            if (! $path) {
                return [null, false];
            }

            $originalPath = $path;

            // Ignore absolute/external URLs and data URIs
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
                $skipped++;

                return [$path, false];
            }

            $path = mb_trim(str_replace('\\', '/', (string) $path));
            $appUrl = (string) config('app.url');
            if ($appUrl !== '' && str_starts_with($path, $appUrl)) {
                $path = (string) mb_substr($path, mb_strlen($appUrl));
            }

            $path = mb_ltrim($path, '/');
            if (str_starts_with($path, 'storage/')) {
                $path = (string) mb_substr($path, mb_strlen('storage/'));
            }
            if (str_starts_with($path, 'public/')) {
                $path = (string) mb_substr($path, mb_strlen('public/'));
            }

            $path = mb_trim($path, '/');
            if ($path === '') {
                $skipped++;

                return [$originalPath, false];
            }

            // If already present on public as-is, keep
            if (Storage::disk('public')->exists($path)) {
                if ($path !== $originalPath) {
                    return [$path, true];
                }

                $skipped++;

                return [$originalPath, false];
            }

            $filename = basename($path);
            $dest = mb_trim($targetDir, '/').'/'.$filename;

            // If destination already exists, keep destination
            if (Storage::disk('public')->exists($dest)) {
                return [$dest, $dest !== $originalPath];
            }

            // Try to locate source on possible disks
            $sourceDisk = null;
            $sourcePath = $path;
            if (Storage::disk('local')->exists($path)) {
                $sourceDisk = 'local';
            } elseif (Storage::disk('public')->exists($path)) {
                $sourceDisk = 'public';
            }

            if (! $sourceDisk) {
                $this->warn("Missing source file: {$path}");
                $missing++;

                // Still standardize path to destination for DB if you wish; keep original
                return [$originalPath, false];
            }

            if (! $dry) {
                $stream = Storage::disk($sourceDisk)->readStream($sourcePath);
                if (! $stream) {
                    $this->warn("Failed to read source: {$sourceDisk}:{$sourcePath}");
                    $missing++;

                    return [$originalPath, false];
                }
                Storage::disk('public')->put($dest, $stream);
                if ($sourceDisk === 'local') {
                    // Optionally delete original on local/private
                    Storage::disk('local')->delete($sourcePath);
                }
            }

            $moved++;

            return [$dest, $dest !== $originalPath];
        };

        // Categories
        Category::whereNotNull('category_image')->chunkById(200, function ($rows) use ($migratePath, &$updated) {
            foreach ($rows as $row) {
                [$new, $changed] = $migratePath($row->category_image, 'categories');
                if ($changed && $new !== $row->category_image) {
                    $row->category_image = $new;
                    $updated++;
                    if (! ((bool) $this->option('dry'))) {
                        $row->save();
                    }
                }
            }
        });

        // Banners
        Banner::whereNotNull('banner_path')->chunkById(200, function ($rows) use ($migratePath, &$updated) {
            foreach ($rows as $row) {
                [$new, $changed] = $migratePath($row->banner_path, 'banners');
                if ($changed && $new !== $row->banner_path) {
                    $row->banner_path = $new;
                    $updated++;
                    if (! ((bool) $this->option('dry'))) {
                        $row->save();
                    }
                }
            }
        });

        // Flash Banners
        FlashBanner::whereNotNull('image')->chunkById(200, function ($rows) use ($migratePath, &$updated, $persist) {
            foreach ($rows as $row) {
                [$new, $changed] = $migratePath($row->image, 'flash-banners');
                if ($changed && $new !== $row->image) {
                    $row->image = $new;
                    $persist($row);
                    $updated++;
                }
            }
        });

        // Why Us
        WhyUs::whereNotNull('image')->chunkById(200, function ($rows) use ($migratePath, &$updated, $persist) {
            foreach ($rows as $row) {
                [$new, $changed] = $migratePath($row->image, 'why-us');
                if ($changed && $new !== $row->image) {
                    $row->image = $new;
                    $persist($row);
                    $updated++;
                }
            }
        });

        // Product Cuts
        ProductCut::whereNotNull('image')->chunkById(200, function ($rows) use ($migratePath, &$updated) {
            foreach ($rows as $row) {
                [$new, $changed] = $migratePath($row->image, 'product-cuts');
                if ($changed && $new !== $row->image) {
                    $row->image = $new;
                    $updated++;
                    if (! ((bool) $this->option('dry'))) {
                        $row->save();
                    }
                }
            }
        });

        // Products (primary and images array)
        Product::chunkById(100, function ($rows) use ($migratePath, &$updated, $persist) {
            foreach ($rows as $row) {
                $changedAny = false;

                if (! empty($row->primary_image)) {
                    [$new, $changed] = $migratePath($row->primary_image, 'products');
                    if ($changed && $new !== $row->primary_image) {
                        $row->primary_image = $new;
                        $changedAny = true;
                    }
                }

                $images = $row->images;
                if (is_array($images) && ! empty($images)) {
                    $newImages = [];
                    foreach ($images as $img) {
                        [$new, $changed] = $migratePath($img, 'products');
                        $newImages[] = $changed ? $new : $img;
                        $changedAny = $changedAny || $changed;
                    }
                    $row->images = $newImages;
                }

                if ($changedAny) {
                    $persist($row);
                    $updated++;
                }
            }
        });

        // Cut Types icon (string, can be URL)
        CutType::whereNotNull('icon')->chunkById(200, function ($rows) use ($migratePath, &$updated, $persist) {
            foreach ($rows as $row) {
                [$new, $changed] = $migratePath($row->icon, 'cut-types');
                if ($changed && $new !== $row->icon) {
                    $row->icon = $new;
                    $persist($row);
                    $updated++;
                }
            }
        });

        // Stores logo and favicon
        Store::chunkById(100, function ($rows) use ($migratePath, &$updated, $persist) {
            foreach ($rows as $row) {
                $changedAny = false;

                if (! empty($row->logo)) {
                    [$newLogo, $changedLogo] = $migratePath((string) $row->logo, 'stores');
                    if ($changedLogo && $newLogo !== $row->logo) {
                        $row->logo = $newLogo;
                        $changedAny = true;
                    }
                }

                if (! empty($row->favicon)) {
                    [$newFavicon, $changedFavicon] = $migratePath((string) $row->favicon, 'stores');
                    if ($changedFavicon && $newFavicon !== $row->favicon) {
                        $row->favicon = $newFavicon;
                        $changedAny = true;
                    }
                }

                if ($changedAny) {
                    $persist($row);
                    $updated++;
                }
            }
        });

        // Drivers profile and document images
        Driver::chunkById(100, function ($rows) use ($migratePath, &$updated, $persist) {
            foreach ($rows as $row) {
                $changedAny = false;

                if (! empty($row->profile_image)) {
                    [$new, $changed] = $migratePath((string) $row->profile_image, 'drivers');
                    if ($changed && $new !== $row->profile_image) {
                        $row->profile_image = $new;
                        $changedAny = true;
                    }
                }

                if (! empty($row->driver_license_image)) {
                    [$new, $changed] = $migratePath((string) $row->driver_license_image, 'licenses');
                    if ($changed && $new !== $row->driver_license_image) {
                        $row->driver_license_image = $new;
                        $changedAny = true;
                    }
                }

                if (! empty($row->vehicle_registration_image)) {
                    [$new, $changed] = $migratePath((string) $row->vehicle_registration_image, 'registrations');
                    if ($changed && $new !== $row->vehicle_registration_image) {
                        $row->vehicle_registration_image = $new;
                        $changedAny = true;
                    }
                }

                if (! empty($row->insurance_image)) {
                    [$new, $changed] = $migratePath((string) $row->insurance_image, 'insurance');
                    if ($changed && $new !== $row->insurance_image) {
                        $row->insurance_image = $new;
                        $changedAny = true;
                    }
                }

                if ($changedAny) {
                    $persist($row);
                    $updated++;
                }
            }
        });

        // Meta tags OG and Twitter images (absolute external URLs are kept)
        MetaTag::chunkById(100, function ($rows) use ($migratePath, &$updated, $persist) {
            foreach ($rows as $row) {
                $changedAny = false;

                if (! empty($row->og_image)) {
                    [$new, $changed] = $migratePath((string) $row->og_image, 'seo');
                    if ($changed && $new !== $row->og_image) {
                        $row->og_image = $new;
                        $changedAny = true;
                    }
                }

                if (! empty($row->twitter_image)) {
                    [$new, $changed] = $migratePath((string) $row->twitter_image, 'seo');
                    if ($changed && $new !== $row->twitter_image) {
                        $row->twitter_image = $new;
                        $changedAny = true;
                    }
                }

                if ($changedAny) {
                    $persist($row);
                    $updated++;
                }
            }
        });

        // Order review gallery images (JSON array)
        OrderReview::whereNotNull('images')->chunkById(100, function ($rows) use ($migratePath, &$updated, $persist) {
            foreach ($rows as $row) {
                $images = $row->images;

                if (is_string($images)) {
                    $decoded = json_decode($images, true);
                    $images = is_array($decoded) ? $decoded : [];
                }

                if (! is_array($images) || $images === []) {
                    continue;
                }

                $changedAny = false;
                $newImages = [];

                foreach ($images as $imagePath) {
                    if (! is_string($imagePath) || $imagePath === '') {
                        continue;
                    }

                    [$new, $changed] = $migratePath($imagePath, 'reviews');
                    $newImages[] = $changed ? $new : $imagePath;
                    $changedAny = $changedAny || $changed;
                }

                if ($changedAny) {
                    $row->images = $newImages;
                    $persist($row);
                    $updated++;
                }
            }
        });

        $this->info("Done. Moved: {$moved}, Updated rows: {$updated}, Skipped: {$skipped}, Missing: {$missing}");

        return self::SUCCESS;
    }
}
