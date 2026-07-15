<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\CouponType;
use App\Models\CouponSetting;
use BackedEnum;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class ReferralSettings extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    protected string $view = 'filament.pages.referral-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Share;

    public static function getNavigationLabel(): string
    {
        return 'Referral Settings';
    }

    public function getTitle(): string
    {
        return 'Referral Settings';
    }

    public function mount(): void
    {
        $settings = CouponSetting::current();

        $this->form->fill([
            'referral_discount_type' => $settings->referral_discount_type,
            'referral_discount_value' => $settings->referral_discount_value,
            'referral_min_order_amount' => $settings->referral_min_order_amount,
            'referral_max_discount_amount' => $settings->referral_max_discount_amount,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        Select::make('referral_discount_type')
                            ->label('Discount Type')
                            ->options([
                                CouponType::Percentage->value => 'Percentage',
                                CouponType::FixedAmount->value => 'Fixed Amount',
                            ])
                            ->default(CouponType::Percentage->value)
                            ->required()
                            ->live()
                            ->columnSpan(1),

                        TextInput::make('referral_discount_value')
                            ->label(fn (Get $get): string => $get('referral_discount_type') === CouponType::Percentage->value
                                ? 'Percentage (%)'
                                : 'Fixed Amount (₹)'
                            )
                            ->numeric()
                            ->minValue(0)
                            ->suffix(fn (Get $get): string => $get('referral_discount_type') === CouponType::Percentage->value ? '%' : '₹')
                            ->rules([
                                fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                    if ($get('referral_discount_type') === CouponType::Percentage->value && $value > 100) {
                                        $fail('Percentage cannot be greater than 100%.');
                                    }
                                },
                            ])
                            ->required()
                            ->columnSpan(1),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('referral_min_order_amount')
                            ->label('Minimum Order Amount')
                            ->helperText('Minimum order value required to apply a referral coupon.')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->default(0)
                            ->required()
                            ->columnSpan(1),

                        TextInput::make('referral_max_discount_amount')
                            ->label('Maximum Discount Amount')
                            ->helperText('Discount cap, applies only to percentage-based referral coupons.')
                            ->numeric()
                            ->minValue(0)
                            ->prefix('₹')
                            ->visible(fn (Get $get): bool => $get('referral_discount_type') === CouponType::Percentage->value)
                            ->columnSpan(1),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $setting = CouponSetting::query()->first();

        if ($setting) {
            $setting->update($state);
        } else {
            CouponSetting::create($state);
        }

        Notification::make()
            ->title('Referral settings saved.')
            ->success()
            ->send();
    }
}
