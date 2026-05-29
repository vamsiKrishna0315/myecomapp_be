<?php

declare(strict_types=1);

namespace App\Filament\Resources\Banners\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class BannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('banner_name')
                    ->label('Banner Name')
                    ->required(),
                FileUpload::make('banner_path')
                    ->label('Banner')
                    ->image()
                    ->disk('public')
                    ->directory('banners')
                    ->imagePreviewHeight('100')
                    ->panelAspectRatio('2:1')
                    ->enableOpen()
                    ->enableDownload()
                    ->required(),
                TextInput::make('redirect_link')
                    ->label('Redirect Link'),
                Toggle::make('show_live')
                    ->label('Show Live')
                    ->default(true)
                    ->onColor('success')
                    ->offColor('danger'),
            ]);
    }
}
