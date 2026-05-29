<?php

namespace App\Filament\Resources\ProductGrades\Pages;

use App\Filament\Resources\ProductGrades\ProductGradeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductGrade extends EditRecord
{
    protected static string $resource = ProductGradeResource::class;

    protected function afterSave(): void
    {
        $this->redirect(static::getResource()::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
