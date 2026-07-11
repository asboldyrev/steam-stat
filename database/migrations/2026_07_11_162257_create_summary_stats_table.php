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
        Schema::create('summary_stats', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique();
            $table->unsignedInteger('games_count')->default(0);
            $table->unsignedBigInteger('total_minutes')->default(0);
            $table->unsignedBigInteger('windows_minutes')->default(0);
            $table->unsignedBigInteger('linux_minutes')->default(0);
            $table->unsignedBigInteger('linux_desktop_minutes')->default(0);
            $table->unsignedBigInteger('mac_minutes')->default(0);
            $table->unsignedBigInteger('deck_minutes')->default(0);
            $table->unsignedBigInteger('disconnected_minutes')->default(0);
            $table->unsignedBigInteger('unclassified_minutes')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summary_stats');
    }
};
