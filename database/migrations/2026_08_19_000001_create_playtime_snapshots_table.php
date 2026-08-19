<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playtime_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->timestamp('captured_at');
            $table->unsignedBigInteger('total_minutes')->default(0);
            $table->unsignedBigInteger('windows_minutes')->default(0);
            $table->unsignedBigInteger('linux_minutes')->default(0);
            $table->unsignedBigInteger('mac_minutes')->default(0);
            $table->unsignedBigInteger('deck_minutes')->default(0);
            $table->unsignedBigInteger('disconnected_minutes')->default(0);
            $table->bigInteger('delta_total_minutes')->default(0);
            $table->bigInteger('delta_windows_minutes')->default(0);
            $table->bigInteger('delta_linux_minutes')->default(0);
            $table->bigInteger('delta_mac_minutes')->default(0);
            $table->bigInteger('delta_deck_minutes')->default(0);
            $table->bigInteger('delta_disconnected_minutes')->default(0);
            $table->boolean('has_counter_correction')->default(false);
            $table->timestamp('last_played_at')->nullable();
            $table->timestamps();

            $table->index(['game_id', 'captured_at']);
            $table->index('captured_at');
        });

        DB::table('game_stats')
            ->orderBy('id')
            ->chunkById(500, function ($stats): void {
                $rows = [];

                foreach ($stats as $stat) {
                    $capturedAt = $stat->updated_at
                        ?? $stat->created_at
                        ?? ($stat->date . ' 23:59:59');
                    $deltas = [
                        (int) $stat->delta_total_minutes,
                        (int) $stat->delta_windows_minutes,
                        (int) $stat->delta_linux_minutes,
                        (int) $stat->delta_mac_minutes,
                        (int) $stat->delta_deck_minutes,
                        (int) $stat->delta_disconnected_minutes,
                    ];

                    $rows[] = [
                        'game_id' => $stat->game_id,
                        'captured_at' => $capturedAt,
                        'total_minutes' => $stat->total_minutes,
                        'windows_minutes' => $stat->windows_minutes,
                        'linux_minutes' => $stat->linux_minutes,
                        'mac_minutes' => $stat->mac_minutes,
                        'deck_minutes' => $stat->deck_minutes,
                        'disconnected_minutes' => $stat->disconnected_minutes,
                        'delta_total_minutes' => $stat->delta_total_minutes,
                        'delta_windows_minutes' => $stat->delta_windows_minutes,
                        'delta_linux_minutes' => $stat->delta_linux_minutes,
                        'delta_mac_minutes' => $stat->delta_mac_minutes,
                        'delta_deck_minutes' => $stat->delta_deck_minutes,
                        'delta_disconnected_minutes' => $stat->delta_disconnected_minutes,
                        'has_counter_correction' => collect($deltas)->contains(fn (int $delta): bool => $delta < 0),
                        'last_played_at' => $stat->last_played_at,
                        'created_at' => $stat->created_at ?? $capturedAt,
                        'updated_at' => $stat->updated_at ?? $capturedAt,
                    ];
                }

                if ($rows !== []) {
                    DB::table('playtime_snapshots')->insert($rows);
                }
            }, column: 'id');
    }

    public function down(): void
    {
        Schema::dropIfExists('playtime_snapshots');
    }
};
