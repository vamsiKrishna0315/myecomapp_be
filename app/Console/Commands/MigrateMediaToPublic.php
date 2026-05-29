<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Banner;
use App\Models\Category;
use App\Models\CutType;
use App\Models\Product;
use App\Models\ProductCut;
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

        // Helper to migrate a single path
        $migratePath = function (?string $path, string $targetDir) use ($dry, &$moved, &$skipped, &$missing) {
            if (! $path) {
                return [null, false];
            }

            // Ignore absolute/external URLs and data URIs
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
                $skipped++;

                return [$path, false];
            }

            // If already present on public as-is, keep
            if (Storage::disk('public')->exists($path)) {
                $skipped++;

                return [$path, false];
            }

            $filename = basename($path);
            $dest = mb_trim($targetDir, '/').'/'.$filename;

            // If destination already exists, keep destination
            if (Storage::disk('public')->exists($dest)) {
                $skipped++;

                return [$dest, true];
            }

            // Try to locate source on possible disks
            $sourceDisk = null;
            if (Storage::disk('local')->exists($path)) {
                $sourceDisk = 'local';
            } elseif (Storage::disk('public')->exists($path)) {
                $sourceDisk = 'public';
            }

            if (! $sourceDisk) {
                $this->warn("Missing source file: {$path}");
                $missing++;

                // Still standardize path to destination for DB if you wish; keep original
                return [$path, false];
            }

            if (! $dry) {
                $stream = Storage::disk($sourceDisk)->readStream($path);
                if (! $stream) {
                    $this->warn("Failed to read source: {$sourceDisk}:{$path}");
                    $missing++;

                    return [$path, false];
                }
                Storage::disk('public')->put($dest, $stream);
                if ($sourceDisk === 'local') {
                    // Optionally delete original on local/private
                    Storage::disk('local')->delete($path);
                }
            }

            $moved++;

            return [$dest, true];
        };

        // Categories
        Category::whereNotNull('category_image')->chunkById(200, function ($rows) use ($migratePath, &$updated) {
            foreach ($rows as $row) {
                [$new, $changed] = $migratePath($row->category_image, 'categories');
                if ($changed && $new !== $row->category_image) {
                    $row->category_image = $new;
                    $row->save();
                    $updated++;
                }
            }
        });

        // Banners
        Banner::whereNotNull('banner_path')->chunkById(200, function ($rows) use ($migratePath, &$updated) {
            foreach ($rows as $row) {
                [$new, $changed] = $migratePath($row->banner_path, 'banners');
                if ($changed && $new !== $row->banner_path) {
                    $row->banner_path = $new;
                    $row->save();
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
                    $row->save();
                    $updated++;
                }
            }
        });

        // Products (primary and images array)
        Product::chunkById(100, function ($rows) use ($migratePath, &$updated) {
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
                    $row->save();
                    $updated++;
                }
            }
        });

        // Cut Types icon (string, can be URL)
        CutType::whereNotNull('icon')->chunkById(200, function ($rows) use ($migratePath, &$updated) {
            foreach ($rows as $row) {
                [$new, $changed] = $migratePath($row->icon, 'cut-types');
                if ($changed && $new !== $row->icon) {
                    $row->icon = $new;
                    $row->save();
                    $updated++;
                }
            }
        });

        $this->info("Done. Moved: {$moved}, Updated rows: {$updated}, Skipped: {$skipped}, Missing: {$missing}");

        return self::SUCCESS;
    }
}
