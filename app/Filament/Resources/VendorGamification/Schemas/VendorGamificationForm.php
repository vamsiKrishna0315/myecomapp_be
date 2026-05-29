<?php

declare(strict_types=1);

namespace App\Filament\Resources\VendorGamification\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;

class VendorGamificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vendor Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->disabled()
                                    ->label('Vendor Name'),
                                TextInput::make('email')
                                    ->disabled()
                                    ->label('Email'),
                                TextInput::make('store_name')
                                    ->disabled()
                                    ->label('Store')
                                    ->formatStateUsing(fn ($record) => $record?->store?->name ?? 'No store assigned'),
                                TextInput::make('reputation')
                                    ->numeric()
                                    ->label('Reputation Points')
                                    ->helperText('Manually adjust reputation if needed')
                                    ->default(0),
                            ]),
                    ]),

                Section::make('Performance Stats')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('orders_count')
                                    ->label('Total Orders')
                                    ->content(fn ($record) => $record ? \App\Models\StoreVendorOrders::where('store_vendor_id', $record->id)->count() : 0),
                                Placeholder::make('badges_count')
                                    ->label('Badges Earned')
                                    ->content(fn ($record) => $record ? $record->badges->count() : 0),
                                Placeholder::make('rank')
                                    ->label('Current Rank')
                                    ->content(function ($record) {
                                        if (!$record) return 'N/A';
                                        $rank = \App\Models\User::where('user_role', 'store_vendor')
                                            ->where('reputation', '>', $record->reputation ?? 0)
                                            ->count() + 1;
                                        return "#$rank";
                                    }),
                            ]),
                    ]),

                Section::make('Earned Badges')
                    ->schema([
                        Placeholder::make('badges_info')
                            ->label('Badges')
                            ->content(function ($record) {
                                if (!$record || $record->badges->count() === 0) {
                                    return 'No badges earned yet';
                                }
                                
                                $badgesList = $record->badges->map(function ($badge) {
                                    return "🏆 {$badge->name} (Level {$badge->level}) - Earned " . $badge->pivot->created_at->diffForHumans();
                                })->join('<br>');
                                
                                return $badgesList;
                            }),
                    ]),

                Section::make('Recent Points History')
                    ->schema([
                        Placeholder::make('points_history')
                            ->label('Recent Activity')
                            ->content(function ($record) {
                                if (!$record) return 'No activity yet';
                                
                                $recentPoints = $record->reputations()->latest()->take(10)->get();
                                
                                if ($recentPoints->isEmpty()) {
                                    return 'No points earned yet';
                                }
                                
                                $pointsList = $recentPoints->map(function ($point) {
                                    return "✅ +{$point->point} points - {$point->name} - " . $point->created_at->diffForHumans();
                                })->join('<br>');
                                
                                return $pointsList;
                            }),
                    ]),
            ]);
    }
}
