<?php

declare(strict_types=1);

namespace App\Filament\Traits;

use Filament\Facades\Filament;

trait ChecksWidgetPermissions
{
    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return false;
        }

        // Extract widget name from class name
        $widgetName = class_basename(static::class);
        $permissionName = 'View:'.$widgetName;

        return $user->can($permissionName);
    }

    public static function canAccess(): bool
    {
        return static::canView();
    }
}
