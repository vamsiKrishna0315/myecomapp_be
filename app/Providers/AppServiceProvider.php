<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\WhatsApp\WhatsAppServiceInterface;
use App\Services\WhatsApp\WhatsAppService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(WhatsAppServiceInterface::class, WhatsAppService::class);
    }

    public function boot(): void
    {
        $this->bootModelsDefaults();
    }

    private function bootModelsDefaults(): void
    {
        Model::unguard();
    }
}
