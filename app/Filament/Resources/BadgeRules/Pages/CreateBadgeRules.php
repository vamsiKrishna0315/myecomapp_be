<?php

declare(strict_types=1);

namespace App\Filament\Resources\BadgeRules\Pages;

use App\Filament\Resources\BadgeRules\BadgeRulesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBadgeRules extends CreateRecord
{
    protected static string $resource = BadgeRulesResource::class;
}
