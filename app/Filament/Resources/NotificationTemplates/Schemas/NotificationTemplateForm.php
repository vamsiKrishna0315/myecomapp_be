<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationTemplates\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class NotificationTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        Select::make('channel')
                            ->label('Channel')
                            ->options([
                                'whatsapp' => 'WhatsApp',
                                'sms' => 'SMS',
                                'email' => 'Email',
                                'push' => 'Push Notification',
                            ])
                            ->default('whatsapp')
                            ->required()
                            ->native(false),

                        Select::make('provider')
                            ->label('Provider')
                            ->options([
                                'meta' => 'Meta',
                                'twilio' => 'Twilio',
                                'firebase' => 'Firebase',
                            ])
                            ->default('meta')
                            ->required()
                            ->native(false),

                        Select::make('category')
                            ->label('Category')
                            ->options([
                                'authentication' => 'Authentication',
                                'utility' => 'Utility',
                                'marketing' => 'Marketing',
                            ])
                            ->required()
                            ->native(false),
                    ])
                    ->columns(3),

                Section::make('Template Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Template Name')
                            ->required()
                            ->maxLength(150),

                        TextInput::make('provider_template_name')
                            ->label('Provider Template Name')
                            ->helperText('Must exactly match the template name configured in Meta.')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(150),

                        Select::make('language')
                            ->label('Language')
                            ->options([
                                'en_US' => 'English (US)',
                                'en_GB' => 'English (UK)',
                                'te_IN' => 'Telugu',
                                'hi_IN' => 'Hindi',
                            ])
                            ->default('en_US')
                            ->required()
                            ->searchable()
                            ->native(false),
                    ])
                    ->columns(3),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                1 => 'Active',
                                0 => 'Inactive',
                            ])
                            ->default(1)
                            ->required()
                            ->native(false),
                    ])
                    ->columns(1),
            ]);
    }
}
