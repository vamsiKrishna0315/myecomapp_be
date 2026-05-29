<?php

namespace App\Filament\Resources\ProductGrades\Pages;

use App\Filament\Resources\ProductGrades\ProductGradeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductGrade extends CreateRecord
{
    protected static string $resource = ProductGradeResource::class;

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }

    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
