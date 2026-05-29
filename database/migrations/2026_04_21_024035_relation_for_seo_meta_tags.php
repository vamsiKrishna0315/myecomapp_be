<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meta_tags', function (Blueprint $table) {

            $table->string('robots')
                ->default('index, follow')
                ->after('canonical_url');

            // Sitemap support
            $table->decimal('priority', 2, 1)
                ->default(0.5)
                ->after('robots');

            $table->string('changefreq')
                ->nullable()
                ->after('priority');
        });
    }

    public function down(): void
    {
        Schema::table('meta_tags', function (Blueprint $table) {
            $table->dropColumn([
                'robots',
                'priority',
                'changefreq',
            ]);
        });
    }
};
