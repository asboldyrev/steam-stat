<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\GameStat;
use App\Models\SummaryStat;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('steam:migrate-stats-to-deltas')]
#[Description('Migrate existing stats records: calculate delta fields from consecutive records')]
class MigrateStatsToDeltas extends Command
{
    public function handle(): int
    {
        $this->info('Migrating game stats to deltas...');
        $this->migrateGameStats();

        $this->info('Migrating summary stats to deltas...');
        $this->migrateSummaryStats();

        $this->info('Migration completed successfully.');
        return self::SUCCESS;
    }

    private function migrateGameStats(): void
    {
        // Получаем все уникальные game_id
        $gameIds = GameStat::query()
            ->select('game_id')
            ->distinct()
            ->pluck('game_id');

        $bar = $this->output->createProgressBar($gameIds->count());
        $bar->start();

        $totalDeleted = 0;

        foreach ($gameIds as $gameId) {
            // Получаем все записи для игры, отсортированные по дате
            $stats = GameStat::query()
                ->where('game_id', $gameId)
                ->orderBy('date', 'asc')
                ->get();

            $prevStat = null;
            foreach ($stats as $stat) {
                $deltaTotal = $prevStat ? $stat->total_minutes - $prevStat->total_minutes : 0;
                $deltaWindows = $prevStat ? $stat->windows_minutes - $prevStat->windows_minutes : 0;
                $deltaLinux = $prevStat ? $stat->linux_minutes - $prevStat->linux_minutes : 0;
                $deltaMac = $prevStat ? $stat->mac_minutes - $prevStat->mac_minutes : 0;
                $deltaDeck = $prevStat ? $stat->deck_minutes - $prevStat->deck_minutes : 0;
                $deltaDisconnected = $prevStat ? $stat->disconnected_minutes - $prevStat->disconnected_minutes : 0;

                $stat->update([
                    'delta_total_minutes' => $deltaTotal,
                    'delta_windows_minutes' => $deltaWindows,
                    'delta_linux_minutes' => $deltaLinux,
                    'delta_mac_minutes' => $deltaMac,
                    'delta_deck_minutes' => $deltaDeck,
                    'delta_disconnected_minutes' => $deltaDisconnected,
                ]);

                $prevStat = $stat;
            }

            // Удаляем дубли с нулевыми дельтами (кроме первой записи)
            if ($stats->isNotEmpty()) {
                $firstDate = $stats->first()->date;

                $deletedCount = GameStat::query()
                    ->where('game_id', $gameId)
                    ->where('date', '>', $firstDate)
                    ->where('delta_total_minutes', 0)
                    ->where('delta_windows_minutes', 0)
                    ->where('delta_linux_minutes', 0)
                    ->where('delta_mac_minutes', 0)
                    ->where('delta_deck_minutes', 0)
                    ->where('delta_disconnected_minutes', 0)
                    ->delete();

                $totalDeleted += $deletedCount;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($totalDeleted > 0) {
            $this->info("Удалено дублирующих записей с нулевыми дельтами: {$totalDeleted}");
        } else {
            $this->info("Дублирующих записей с нулевыми дельтами не найдено.");
        }
    }

    private function migrateSummaryStats(): void
    {
        $stats = SummaryStat::query()
            ->orderBy('date', 'asc')
            ->get();

        $bar = $this->output->createProgressBar($stats->count());
        $bar->start();

        $prevStat = null;
        foreach ($stats as $stat) {
            $deltaTotal = $prevStat ? $stat->total_minutes - $prevStat->total_minutes : 0;
            $deltaWindows = $prevStat ? $stat->windows_minutes - $prevStat->windows_minutes : 0;
            $deltaLinux = $prevStat ? $stat->linux_minutes - $prevStat->linux_minutes : 0;
            $deltaLinuxDesktop = $prevStat ? $stat->linux_desktop_minutes - $prevStat->linux_desktop_minutes : 0;
            $deltaMac = $prevStat ? $stat->mac_minutes - $prevStat->mac_minutes : 0;
            $deltaDeck = $prevStat ? $stat->deck_minutes - $prevStat->deck_minutes : 0;
            $deltaDisconnected = $prevStat ? $stat->disconnected_minutes - $prevStat->disconnected_minutes : 0;
            $deltaUnclassified = $prevStat ? $stat->unclassified_minutes - $prevStat->unclassified_minutes : 0;

            $stat->update([
                'delta_total_minutes' => $deltaTotal,
                'delta_windows_minutes' => $deltaWindows,
                'delta_linux_minutes' => $deltaLinux,
                'delta_linux_desktop_minutes' => $deltaLinuxDesktop,
                'delta_mac_minutes' => $deltaMac,
                'delta_deck_minutes' => $deltaDeck,
                'delta_disconnected_minutes' => $deltaDisconnected,
                'delta_unclassified_minutes' => $deltaUnclassified,
            ]);

            $prevStat = $stat;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
    }
}
