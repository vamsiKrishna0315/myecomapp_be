<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class CategoriesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Category Information')
                    ->description('Enter the category details below.')
                    ->schema([
                        TextInput::make('category_name')
                            ->label('Category Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter category name')
                            ->columnSpanFull(),

                        TextInput::make('category_type')
                            ->label('Category Type')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter category type (e.g., Product, Service, Food, etc.)')
                            ->columnSpanFull(),

                        FileUpload::make('category_image')
                            ->label('Category Image')
                            ->image()
                            ->disk('public')
                            ->directory('categories')
                            ->imagePreviewHeight('100')
                            ->panelAspectRatio('2:1')
                            ->enableOpen()
                            ->enableDownload()
                            ->columnSpanFull(),

                        Toggle::make('status')
                            ->label('Active Status')
                            ->helperText('Enable to make this category active')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->columnSpanFull(),

                        Toggle::make('is_live')
                            ->label('Show Live')
                            ->helperText('Enable to show this category on the website')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ]);
    }
}
