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
        Schema::create('notification_event_mappings', function (Blueprint $table): void {
            $table->id();

            $table->string('event_type', 50)
                ->comment('otp, order_status, payment, refund');

            $table->string('reference_model', 100)
                ->nullable()
                ->comment('OrderStatus, PaymentStatus, RefundStatus');

            $table->unsignedBigInteger('reference_id')
                ->nullable();

            $table->foreignId('notification_template_id')
                ->constrained('notification_templates')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->tinyInteger('status')
                ->default(1)
                ->comment('0 = Inactive, 1 = Active');

            $table->timestamps();

            $table->index(
                ['event_type', 'reference_model', 'reference_id'],
                'idx_notification_event'
            );
        });
    }
};
