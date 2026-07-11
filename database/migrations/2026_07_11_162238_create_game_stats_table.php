<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedBigInteger('total_minutes')->default(0);
            $table->unsignedBigInteger('windows_minutes')->default(0);
            $table->unsignedBigInteger('linux_minutes')->default(0);
            $table->unsignedBigInteger('mac_minutes')->default(0);
            $table->unsignedBigInteger('deck_minutes')->default(0);
            $table->unsignedBigInteger('disconnected_minutes')->default(0);
            $table->timestamp('last_played_at')->nullable();
            $table->timestamps();

            $table->unique(['game_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_stats');
    }
};
