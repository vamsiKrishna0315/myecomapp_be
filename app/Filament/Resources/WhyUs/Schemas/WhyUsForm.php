<?php

declare(strict_types=1);

namespace App\Filament\Resources\WhyUs\Schemas;

use App\Enums\MediaCategory;
use App\Support\Filament\MediaUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class WhyUsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Description')
                    ->required()
                    ->maxLength(255),

                TextInput::make('year')
                    ->label('Year')
                    ->numeric()
                    ->helperText('Optional')
                    ->minValue(1900)
                    ->maxValue(2100),

                MediaUpload::configure(
                    FileUpload::make('image')
                        ->label('Image')
                        ->image()
                        ->maxSize(2048) // 2MB
                        ->helperText('Upload images (max 2MB)'),
                    MediaCategory::WhyUs
                ),

                Toggle::make('show_live')
                    ->label('Show Live')
                    ->helperText('Display on website')
                    ->default(false),

                Toggle::make('status')
                    ->label('Active Status')
                    ->helperText('Enable/Disable this item')
                    ->default(true),
            ]);
    }
}
