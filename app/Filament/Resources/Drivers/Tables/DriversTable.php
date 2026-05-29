<?php

namespace App\Filament\Resources\Drivers\Tables;

use App\Enums\VehicleType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DriversTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_image')
                    ->label('Photo')
                    ->circular()
                    ->size(50)
                    ->defaultImageUrl(url('/images/default-avatar.png')),
                
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->label('Driver Name'),
                
                TextColumn::make('mobile')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Mobile number copied!')
                    ->label('Mobile'),
                
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email copied!')
                    ->label('Email'),
                
                TextColumn::make('vehicle_type')
                    ->badge()
                    ->formatStateUsing(function (int $state) {
                        return match ($state) {
                            VehicleType::Bike->value => 'Bike',
                            VehicleType::Car->value => 'Car',
                            VehicleType::Van->value => 'Van',
                            default => 'Unknown',
                        };
                    })
                    ->color(function (int $state) {
                        return match ($state) {
                            VehicleType::Bike->value => 'warning',
                            VehicleType::Car->value => 'info',
                            VehicleType::Van->value => 'success',
                            default => 'gray',
                        };
                    })
                    ->label('Vehicle Type'),
                
                TextColumn::make('vehicle_number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->label('Vehicle No.'),
                
                TextColumn::make('license_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('License No.'),
                
                IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(function ($state) {
                        return $state ? 'Verified Driver' : 'Not Verified';
                    }),
                
                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->tooltip(function ($state) {
                        return $state ? 'Available for Orders' : 'Not Available';
                    }),
                
                IconColumn::make('status')
                    ->label('Status')
                    ->icon(function (int $state) {
                        return match ($state) {
                            1 => 'heroicon-o-check-circle',
                            0 => 'heroicon-o-x-circle',
                            default => 'heroicon-o-question-mark-circle',
                        };
                    })
                    ->color(function (int $state) {
                        return match ($state) {
                            1 => 'success',
                            0 => 'danger',
                            default => 'gray',
                        };
                    })
                    ->tooltip(function (int $state) {
                        return match ($state) {
                            1 => 'Active Driver',
                            0 => 'Inactive Driver',
                            default => 'Unknown Status',
                        };
                    }),
                
                TextColumn::make('rating')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->badge()
                    ->color(function (float $state) {
                        if ($state >= 4.5) return 'success';
                        if ($state >= 3.5) return 'warning';
                        if ($state >= 2.0) return 'danger';
                        return 'gray';
                    })
                    ->formatStateUsing(function (float $state) {
                        return number_format($state, 1) . '/5.0';
                    })
                    ->label('Rating'),
                
                TextColumn::make('total_deliveries')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->label('Deliveries'),
                
                TextColumn::make('experience_years')
                    ->numeric()
                    ->sortable()
                    ->suffix(' yrs')
                    ->label('Experience')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('license_expiry_date')
                    ->date()
                    ->sortable()
                    ->color(function ($state) {
                        if (!$state) return 'gray';
                        $daysUntilExpiry = now()->diffInDays($state, false);
                        if ($daysUntilExpiry < 0) return 'danger';
                        if ($daysUntilExpiry <= 30) return 'warning';
                        return 'success';
                    })
                    ->tooltip(function ($state) {
                        if (!$state) return 'No expiry date set';
                        $daysUntilExpiry = now()->diffInDays($state, false);
                        if ($daysUntilExpiry < 0) return 'License expired!';
                        if ($daysUntilExpiry <= 30) return "Expires in {$daysUntilExpiry} days";
                        return 'License valid';
                    })
                    ->label('License Expiry')
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Registered'),
                
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Last Updated'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1)
                    ->native(false)
                    ->label('Account Status'),
                
                SelectFilter::make('vehicle_type')
                    ->options([
                        VehicleType::Bike->value => 'Bike',
                        VehicleType::Car->value => 'Car',
                        VehicleType::Van->value => 'Van',
                    ])
                    ->native(false)
                    ->label('Vehicle Type'),
                
                SelectFilter::make('is_verified')
                    ->options([
                        1 => 'Verified',
                        0 => 'Not Verified',
                    ])
                    ->native(false)
                    ->label('Verification Status'),
                
                SelectFilter::make('is_available')
                    ->options([
                        1 => 'Available',
                        0 => 'Not Available',
                    ])
                    ->native(false)
                    ->label('Availability Status'),
                
                SelectFilter::make('rating')
                    ->options([
                        '4.5' => '4.5+ Stars (Excellent)',
                        '3.5' => '3.5+ Stars (Good)',
                        '2.0' => '2.0+ Stars (Average)',
                        '0' => 'Below 2.0 Stars (Poor)',
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['value']) || $data['value'] === null) {
                            return $query;
                        }
                        
                        $rating = (float) $data['value'];
                        if ($rating === 0.0) {
                            return $query->where('rating', '<', 2.0);
                        }
                        
                        return $query->where('rating', '>=', $rating);
                    })
                    ->native(false)
                    ->label('Rating Filter'),
            ])
            ->recordActions([
                EditAction::make()
                    ->tooltip('Edit driver details'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Delete Selected'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s')
            ->searchable()
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }
}