<?php

namespace App\Filament\Resources\Feedback\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class FeedbackForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // TODO: Uncomment when customers table is created
                /*
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                */
                
                Textarea::make('description')
                    ->label('Feedback Description')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),

                Select::make('rating')
                    ->label('Rating')
                    ->options([
                        1 => '⭐ One Star',
                        2 => '⭐⭐ Two Stars',
                        3 => '⭐⭐⭐ Three Stars',
                        4 => '⭐⭐⭐⭐ Four Stars',
                        5 => '⭐⭐⭐⭐⭐ Five Stars',
                    ])
                    ->required(),

                Toggle::make('show_live')
                    ->label('Show on Website')
                    ->helperText('Display this feedback on the website')
                    ->default(false),

                Toggle::make('status')
                    ->label('Active Status')
                    ->helperText('Enable/Disable this feedback')
                    ->default(true),
            ]);
    }
}
