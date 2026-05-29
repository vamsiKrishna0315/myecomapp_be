<?php

declare(strict_types=1);

namespace App\Filament\Resources\FlashBanners;

use App\Filament\Resources\FlashBanners\Pages\CreateFlashBanner;
use App\Filament\Resources\FlashBanners\Pages\EditFlashBanner;
use App\Filament\Resources\FlashBanners\Pages\ListFlashBanners;
use App\Filament\Resources\FlashBanners\Schemas\FlashBannerForm;
use App\Filament\Resources\FlashBanners\Tables\FlashBannersTable;
use App\Models\FlashBanner;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class FlashBannerResource extends Resource
{
    protected static ?string $model = FlashBanner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function canAccess(): bool
    {
        return Filament::auth()->user()?->can('ViewAny:FlashBanner') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return FlashBannerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FlashBannersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFlashBanners::route('/'),
            'create' => CreateFlashBanner::route('/create'),
            'edit' => EditFlashBanner::route('/{record}/edit'),
        ];
    }
}
