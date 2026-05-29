<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Pages\OrderTrackingPage;
use App\Filament\Widgets\CustomerGrowthChartWidget;
use App\Filament\Widgets\CustomerStatsOverviewWidget;
use App\Filament\Widgets\DriverAnalyticsWidget;
use App\Filament\Widgets\DriverStatsOverviewWidget;
use App\Filament\Widgets\NewCustomersWidget;
use App\Filament\Widgets\OrdersStatsOverviewWidget;
use App\Filament\Widgets\TopCustomersWidget;
use App\Filament\Widgets\TopSellingProductsWidget;
use App\Filament\Widgets\TopStoreVendorsWidget;
use App\Filament\Widgets\TotalOrdersWidget;
use App\Http\Middleware\RedirectBasedOnUserRole;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

final class StoreVendorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('store_vendor')
            ->path('store_vendor')
            ->login()
            ->homeUrl('/store_vendor/dashboard')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                OrderTrackingPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                OrdersStatsOverviewWidget::class,
                TotalOrdersWidget::class,
                CustomerStatsOverviewWidget::class,
                CustomerGrowthChartWidget::class,
                TopCustomersWidget::class,
                NewCustomersWidget::class,
                DriverStatsOverviewWidget::class,
                TopStoreVendorsWidget::class,
                DriverAnalyticsWidget::class,
                TopSellingProductsWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                RedirectBasedOnUserRole::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
            ]);
    }
}
