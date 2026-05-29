<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use App\Enums\CouponType;

class CouponsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Coupon Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(50)
                                    ->label('Coupon Code')
                                    ->placeholder('e.g., SAVE20, WELCOME10')
                                    ->helperText('Unique code that customers will use')
                                    ->columnSpan(1),

                                Select::make('type')
                                    ->options([
                                        CouponType::Percentage->value => 'Percentage',
                                        CouponType::FixedAmount->value => 'Fixed Amount',
                                    ])
                                    ->required()
                                    ->default(CouponType::Percentage->value)
                                    ->live()
                                    ->label('Discount Type')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('value')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->label(fn (Get $get): string => 
                                        $get('type') == CouponType::Percentage->value 
                                            ? 'Percentage (%)' 
                                            : 'Fixed Amount (₹)'
                                    )
                                    ->placeholder(fn (Get $get): string => 
                                        $get('type') == CouponType::Percentage->value 
                                            ? 'e.g., 20 (for 20%)' 
                                            : 'e.g., 100 (for ₹100)'
                                    )
                                    ->suffix(fn (Get $get): string => 
                                        $get('type') == CouponType::Percentage->value ? '%' : '₹'
                                    )
                                    ->rules([
                                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                            if ($get('type') == CouponType::Percentage->value && $value > 100) {
                                                $fail('Percentage cannot be greater than 100%.');
                                            }
                                        },
                                    ])
                                    ->columnSpan(1),

                                TextInput::make('min_order_amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('₹')
                                    ->label('Minimum Order Amount')
                                    ->placeholder('e.g., 500')
                                    ->helperText('Minimum order value to apply coupon')
                                    ->columnSpan(1),

                                TextInput::make('max_discount_amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->prefix('₹')
                                    ->label('Maximum Discount Amount')
                                    ->placeholder('e.g., 1000')
                                    ->helperText('Maximum discount limit (for percentage coupons)')
                                    ->visible(fn (Get $get): bool => $get('type') == CouponType::Percentage->value)
                                    ->columnSpan(1),
                            ]),

                        Textarea::make('description')
                            ->maxLength(500)
                            ->label('Description')
                            ->placeholder('Brief description of the coupon offer')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                Section::make('Usage Limits')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('usage_limit')
                                    ->numeric()
                                    ->minValue(1)
                                    ->label('Total Usage Limit')
                                    ->placeholder('e.g., 100')
                                    ->helperText('Maximum number of times this coupon can be used')
                                    ->columnSpan(1),

                                TextInput::make('usage_per_customer')
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->label('Usage Per Customer')
                                    ->placeholder('e.g., 1')
                                    ->helperText('How many times one customer can use this coupon')
                                    ->columnSpan(1),
                            ]),

                        TextInput::make('used_count')
                            ->numeric()
                            ->default(0)
                            ->label('Used Count')
                            ->disabled()
                            ->helperText('Number of times this coupon has been used')
                            ->dehydrated(false),
                    ])
                    ->columns(1),

                Section::make('Validity Period')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('valid_from')
                                    ->required()
                                    ->label('Valid From')
                                    ->default(now()->toDateString())
                                    ->minDate(now()->subDay()->toDateString())
                                    ->live()
                                    ->afterStateUpdated(function (Set $set, $state) {
                                        if ($state) {
                                            $set('valid_until', now()->addDays(30)->toDateString());
                                        }
                                    })
                                    ->columnSpan(1),

                                DatePicker::make('valid_until')
                                    ->required()
                                    ->label('Valid Until')
                                    ->minDate(fn (Get $get): ?string => $get('valid_from') ? $get('valid_from') : now()->toDateString())
                                    ->live()
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columns(1),

                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->options([
                                1 => 'Active',
                                0 => 'Inactive',
                            ])
                            ->default(1)
                            ->required()
                            ->label('Status')
                            ->helperText('Active coupons can be used by customers'),
                    ])
                    ->columns(1),
            ]);
    }
}
