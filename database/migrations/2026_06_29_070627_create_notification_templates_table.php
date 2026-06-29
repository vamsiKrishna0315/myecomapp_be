<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table): void {
            $table->id();

            $table->string('channel', 50)
                ->comment('whatsapp, sms, email, push');

            $table->string('provider', 50)
                ->comment('meta, twilio, firebase, etc.');

            $table->string('name', 150)
                ->comment('Display name shown in Filament');

            $table->string('provider_template_name', 150)
                ->unique()
                ->comment('Template name configured in provider');

            $table->string('category', 50)
                ->comment('authentication, utility, marketing');

            $table->string('language', 20)
                ->default('en_US');

            $table->text('description')
                ->nullable();

            $table->tinyInteger('status')
                ->default(1)
                ->comment('0 = Inactive, 1 = Active');

            $table->timestamps();

            $table->index(['channel', 'provider']);
        });
    }
};
