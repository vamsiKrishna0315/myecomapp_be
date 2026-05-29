<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('category_name')
                    ->label('Category Name')
                    ->sortable()
                    ->searchable()
                    ->weight('bold')
                    ->copyable()
                    ->copyMessage('Category name copied!')
                    ->wrap(),

                TextColumn::make('category_type')
                    ->label('Category Type')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'product' => 'primary',
                        'service' => 'success',
                        'food' => 'warning',
                        'electronics' => 'info',
                        'clothing' => 'secondary',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                IconColumn::make('status')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($state): string => $state ? 'Active' : 'Inactive')
                    ->sortable(),

                IconColumn::make('is_live')
                    ->label('Live Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-eye')
                    ->falseIcon('heroicon-o-eye-slash'),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->tooltip(fn ($state): string => $state ? $state->format('F j, Y \a\t g:i A') : ''),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->tooltip(fn ($state): string => $state ? $state->format('F j, Y \a\t g:i A') : ''),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->default(1),

                SelectFilter::make('category_type')
                    ->label('Category Type')
                    ->options(Category::getCategoryTypes())
                    ->multiple()
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->tooltip('View category details'),
                EditAction::make()
                    ->tooltip('Edit category'),
            ])
            ->toolbarActions([
                CreateAction::make()
                    ->label('New Category')
                    ->icon('heroicon-o-plus'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }
}
