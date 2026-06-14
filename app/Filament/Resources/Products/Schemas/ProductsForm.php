<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Support\ProductSlugSupport;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ProductsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Basic Information - Full Width
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $set, $get) {
                                        if (blank($get('slug'))) {
                                            $set('slug', ProductSlugSupport::slugify((string) $state));
                                        }
                                    }),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->live(onBlur: true)
                                    ->dehydrateStateUsing(fn ($state, $get) => ProductSlugSupport::slugify(
                                        filled($state) ? (string) $state : (string) $get('name')
                                    ))
                                    ->helperText(fn ($get) => ProductSlugSupport::qualitySummary(
                                        filled($get('slug')) ? (string) $get('slug') : (string) $get('name')
                                    ))
                                    ->rule('regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'),

                                Select::make('category_id')
                                    ->relationship('category', 'category_name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('category_name')
                                            ->required()
                                            ->maxLength(100),
                                        TextInput::make('category_type')
                                            ->maxLength(100),
                                    ])
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(1),

                                // Cut Type Multi-Select
                                Select::make('cuttype_ids')
                                    ->label('Cut Types')
                                    ->multiple()
                                    ->options(fn () => \App\Models\CutType::where('show_live', 1)->where('status', 1)->pluck('name', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->helperText('Select one or more cut types (only active & live shown).'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                RichEditor::make('description')
                                    ->label('Short Description'),

                                RichEditor::make('long_description')
                                    ->label('Long Description'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // Pricing & Inventory and Media side by side
                Grid::make(3)
                    ->schema([
                        Section::make('Pricing & Inventory')
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('price')
                                            ->numeric()
                                            ->required()
                                            ->prefix('₹'),

                                        TextInput::make('cost')
                                            ->numeric()
                                            ->prefix('₹'),

                                        TextInput::make('compare_at_price')
                                            ->label('Compare at Price')
                                            ->numeric()
                                            ->prefix('₹'),
                                    ]),

                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('stock_quantity')
                                            ->label('Stock Quantity')
                                            ->numeric()
                                            ->integer()
                                            ->default(0),

                                        TextInput::make('low_stock_threshold')
                                            ->label('Low Stock Threshold')
                                            ->numeric()
                                            ->integer()
                                            ->default(10),

                                        Toggle::make('track_inventory')
                                            ->label('Track Inventory')
                                            ->default(true),
                                    ]),
                            ]),

                        Section::make('Media')
                            ->columnSpan(1)
                            ->schema([
                                FileUpload::make('primary_image')
                                    ->image()
                                    ->disk('public')
                                    ->directory('products')
                                    ->label('Primary Image'),

                                FileUpload::make('images')
                                    ->multiple()
                                    ->image()
                                    ->disk('public')
                                    ->directory('products')
                                    ->label('Additional Images'),
                            ]),
                    ])
                    ->columnSpanFull(),

                // SEO & Visibility - Full Width
                Section::make('SEO & Visibility')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label('Meta Title')
                                    ->maxLength(255),

                                TextInput::make('meta_description')
                                    ->label('Meta Description')
                                    ->maxLength(255),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_visible')
                                    ->label('Visible')
                                    ->default(true),

                                DatePicker::make('published_at')
                                    ->label('Publish Date')
                                    ->date()
                                    ->format('Y-m-d'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
