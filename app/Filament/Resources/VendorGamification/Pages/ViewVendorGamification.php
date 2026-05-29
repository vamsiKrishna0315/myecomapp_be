<?php

declare(strict_types=1);

namespace App\Filament\Resources\VendorGamification\Pages;

use App\Filament\Resources\VendorGamification\VendorGamificationResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification;
use App\Models\GamificationBadgeRule;

class ViewVendorGamification extends ViewRecord
{
    protected static string $resource = VendorGamificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label('Edit')
                ->icon('heroicon-o-pencil')
                ->url(fn ($record) => static::getResource()::getUrl('edit', ['record' => $record])),
            Action::make('sync_badges')
                ->label('Sync Badges')
                ->icon('heroicon-o-arrow-path')
                ->action(function ($record) {
                    $awarded = $this->checkAndAwardBadges($record);
                    
                    if ($awarded > 0) {
                        Notification::make()
                            ->success()
                            ->title('Badges Synced')
                            ->body("{$awarded} new badge(s) awarded!")
                            ->send();
                    } else {
                        Notification::make()
                            ->info()
                            ->title('Badges Up to Date')
                            ->body('All badges are already synced.')
                            ->send();
                    }
                    
                    // Refresh the page to show updated badges
                    redirect($this->getResource()::getUrl('view', ['record' => $record]));
                })
                ->color('success'),
            DeleteAction::make(),
        ];
    }

    /**
     * Check and award badges to vendor based on achievements (using database rules)
     */
    private function checkAndAwardBadges($vendor): int
    {
        // Get all active badge rules from database
        $badgeRules = GamificationBadgeRule::getActiveRules();

        $awarded = 0;

        foreach ($badgeRules as $badgeRule) {
            // Check if vendor qualifies for this badge
            if ($badgeRule->userQualifies($vendor)) {
                // Get or create the badge in the badges table
                $badgeModel = \QCod\Gamify\Badge::firstOrCreate([
                    'name' => $badgeRule->name,
                ], [
                    'description' => $badgeRule->description,
                    'icon' => $badgeRule->icon,
                    'level' => $badgeRule->level,
                ]);

                // Award badge if not already awarded
                if (!$vendor->badges()->where('badge_id', $badgeModel->id)->exists()) {
                    $vendor->badges()->attach($badgeModel->id);
                    $awarded++;
                }
            }
        }

        return $awarded;
    }
}

