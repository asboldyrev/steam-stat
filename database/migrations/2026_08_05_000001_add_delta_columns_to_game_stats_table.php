<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_stats', function (Blueprint $table) {
            $table->bigInteger('delta_total_minutes')->default(0)->after('total_minutes');
            $table->bigInteger('delta_windows_minutes')->default(0)->after('windows_minutes');
            $table->bigInteger('delta_linux_minutes')->default(0)->after('linux_minutes');
            $table->bigInteger('delta_mac_minutes')->default(0)->after('mac_minutes');
            $table->bigInteger('delta_deck_minutes')->default(0)->after('deck_minutes');
            $table->bigInteger('delta_disconnected_minutes')->default(0)->after('disconnected_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('game_stats', function (Blueprint $table) {
            $table->dropColumn([
                'delta_total_minutes',
                'delta_windows_minutes',
                'delta_linux_minutes',
                'delta_mac_minutes',
                'delta_deck_minutes',
                'delta_disconnected_minutes',
            ]);
        });
    }
};
