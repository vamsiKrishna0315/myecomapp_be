<?php

namespace App\Filament\Resources\ProductGrades\Pages;

use App\Filament\Resources\ProductGrades\ProductGradeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductGrades extends ListRecords
{
    protected static string $resource = ProductGradeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
