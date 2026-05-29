<?php

declare(strict_types=1);

namespace App\Filament\Resources\VendorGamification\Pages;

use App\Filament\Resources\VendorGamification\VendorGamificationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;
use App\Models\GamificationBadgeRule;

class ListVendorGamification extends ListRecords
{
    protected static string $resource = VendorGamificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync_badges')
                ->label('Sync All Badges')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $vendors = \App\Models\User::where('user_role', 'store_vendor')->get();
                    $badgeCount = 0;
                    
                    foreach ($vendors as $vendor) {
                        $badgeCount += $this->checkAndAwardBadges($vendor);
                    }

                    Notification::make()
                        ->success()
                        ->title('Badges Synced')
                        ->body("Badges synced! {$badgeCount} badges awarded across all vendors.")
                        ->send();
                })
                ->requiresConfirmation()
                ->color('success'),
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
