<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Steam\SyncGameStoreMetadata;
use App\Models\Game;
use Illuminate\Console\Command;

final class SyncSteamStoreMetadata extends Command
{
    protected $signature = 'steam:sync-store-metadata
        {--force : Refresh metadata even when it is still fresh}
        {--delay=750 : Delay between Store API requests in milliseconds}';

    protected $description = 'Cache optional Steam Store metadata for tracked games';

    public function handle(SyncGameStoreMetadata $action): int
    {
        $force = (bool) $this->option('force');
        $delayMs = max(0, (int) $this->option('delay'));
        $updated = 0;
        $attempted = 0;

        Game::query()
            ->orderBy('id')
            ->chunkById(50, function ($games) use ($action, $force, $delayMs, &$updated, &$attempted): void {
                foreach ($games as $game) {
                    $before = $game->store_metadata_synced_at;
                    $refreshed = $action->execute($game, $force);

                    if (!$force && $before?->isAfter(now()->subDays(30))) {
                        continue;
                    }

                    $attempted++;
                    if ($refreshed) {
                        $updated++;
                    }

                    if ($delayMs > 0) {
                        usleep($delayMs * 1000);
                    }
                }
            });

        $this->info("Steam Store metadata refreshed for {$updated} of {$attempted} attempted games.");

        return self::SUCCESS;
    }
}
