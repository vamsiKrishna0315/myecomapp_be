<?php

declare(strict_types=1);

use App\Filament\Resources\WhyUs\Schemas\WhyUsForm;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

it('requires description and keeps year optional in the why us form', function (): void {
    $schema = WhyUsForm::configure(Schema::make());

    $description = $schema->getComponentByStatePath('description');
    $year = $schema->getComponentByStatePath('year');

    expect($description)->toBeInstanceOf(Textarea::class)
        ->and($description->getValidationRules())->toContain('required');

    expect($year)->not->toBeNull()
        ->and($year->getValidationRules())->toContain('nullable')
        ->and($year->getValidationRules())->not->toContain('required');
});
