<?php

declare(strict_types=1);

namespace App\Filament\Resources\FlashBanners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class FlashBannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required(),
                FileUpload::make('image')
                    ->label('Image')
                    ->image()
                    ->disk('public')
                    ->directory('flash-banners')
                    ->imagePreviewHeight('100')
                    ->panelAspectRatio('2:1')
                    ->enableOpen()
                    ->enableDownload()
                    ->required(),
                TextInput::make('redirect_link')
                    ->label('Redirect Link'),
                Toggle::make('is_live')
                    ->label('Show Live')
                    ->default(true)
                    ->onColor('success')
                    ->offColor('danger'),
                Toggle::make('status')
                    ->label('Status')
                    ->default(true)
                    ->onColor('success')
                    ->offColor('danger'),
            ]);
    }
}
