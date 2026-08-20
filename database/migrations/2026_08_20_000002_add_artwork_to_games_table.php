<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->json('artwork')->nullable()->after('cover_url');
            $table->timestamp('artwork_synced_at')->nullable()->after('store_metadata_synced_at');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table): void {
            $table->dropColumn(['artwork', 'artwork_synced_at']);
        });
    }
};
