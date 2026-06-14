<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Media\MediaProviderInterface;
use App\Services\Media\MediaPathPolicy;
use App\Services\Media\MediaService;
use App\Services\Media\Providers\LocalProvider;
use App\Services\Media\Providers\SupabaseProvider;
use App\Contracts\WhatsApp\WhatsAppServiceInterface;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WhatsAppServiceInterface::class, WhatsAppService::class);
        $this->registerMediaServices();
    }

    public function boot(): void
    {
        $this->bootModelsDefaults();
    }

    private function bootModelsDefaults(): void
    {
        Model::unguard();
    }

    private function registerMediaServices(): void
    {
        $this->app->singleton(MediaPathPolicy::class);
        $this->app->singleton(LocalProvider::class);
        $this->app->singleton(SupabaseProvider::class);

        $this->app->singleton(MediaProviderInterface::class, function ($app): MediaProviderInterface {
            return match (strtolower((string) config('media.default', 'local'))) {
                'supabase' => $app->make(SupabaseProvider::class),
                default => $app->make(LocalProvider::class),
            };
        });

        $this->app->singleton(MediaService::class, function ($app): MediaService {
            return new MediaService(
                $app->make(MediaProviderInterface::class),
                $app->make(MediaPathPolicy::class)
            );
        });
    }
}
