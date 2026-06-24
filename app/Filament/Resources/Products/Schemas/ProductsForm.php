<?php

declare(strict_types=1);

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\MediaCategory;
use App\Support\Filament\MediaUpload;
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

                                Section::make('Units')
                                    ->schema([
                                        Select::make('allowed_units')
                                            ->label('Allowed Units')
                                            ->multiple()
                                            ->options([
                                                'gram' => 'Gram (g)',
                                                'kg' => 'Kilogram (kg)',
                                                'piece' => 'Piece',
                                            ])
                                            ->default(['kg'])
                                            ->required()
                                            ->live()
                                            ->helperText('Select which units customers can order in. Your price will be converted automatically.'),

                                        Select::make('base_price_unit')
                                            ->label('Base Price Unit')
                                            ->options([
                                                'gram' => 'Gram (g)',
                                                'kg' => 'Kilogram (kg)',
                                                'piece' => 'Piece',
                                            ])
                                            ->default('kg')
                                            ->required()
                                            ->live()
                                            ->helperText('Select which unit the price field represents. Prices for other allowed units will auto-calculate.')
                                            ->rule(function ($get) {
                                                return function ($attribute, $value, $fail) use ($get) {
                                                    $allowedUnits = (array) $get('allowed_units');
                                                    if (! in_array($value, $allowedUnits, true)) {
                                                        $fail('Base Price Unit must be one of the Allowed Units. Please select an allowed unit.');
                                                    }
                                                };
                                            }),

                                        // Weight Per Piece Type (Standard or Custom)
                                        Select::make('grams_per_piece_type')
                                            ->label('Weight Per Piece')
                                            ->options([
                                                'standard' => 'Standard (100g)',
                                                'custom' => 'Custom Weight',
                                            ])
                                            ->default('standard')
                                            ->required(fn ($get) => in_array('piece', (array) $get('allowed_units'), true))
                                            ->visible(fn ($get) => in_array('piece', (array) $get('allowed_units'), true))
                                            ->live()
                                            ->helperText('Choose standard 100g per piece or enter a custom weight.')
                                            ->afterStateUpdated(function ($state, $set) {
                                                if ($state === 'standard') {
                                                    $set('grams_per_piece', 100);
                                                }
                                            }),

                                        TextInput::make('grams_per_piece')
                                            ->label('Custom Weight Per Piece (grams)')
                                            ->numeric()
                                            ->minValue(0.001)
                                            ->step(0.001)
                                            ->nullable()
                                            ->required(fn ($get) => in_array('piece', (array) $get('allowed_units'), true) && $get('grams_per_piece_type') === 'custom')
                                            ->visible(fn ($get) => in_array('piece', (array) $get('allowed_units'), true) && $get('grams_per_piece_type') === 'custom')
                                            ->placeholder('e.g., 250 for 250g per piece')
                                            ->helperText('Enter the exact weight in grams for each piece.'),
                                    ]),
                            ]),

                        Section::make('Media')
                            ->columnSpan(1)
                            ->schema([
                                MediaUpload::configure(
                                    FileUpload::make('primary_image')
                                        ->image()
                                        ->label('Primary Image'),
                                    MediaCategory::Products
                                ),

                                MediaUpload::configure(
                                    FileUpload::make('images')
                                        ->multiple()
                                        ->image()
                                        ->label('Additional Images'),
                                    MediaCategory::Products
                                ),
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
