<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Steam\SyncGameStoreMetadata;
use App\Models\Game;
use Illuminate\Console\Command;

final class SyncSteamStoreMetadata extends Command
{
    protected $signature = 'steam:sync-store-metadata {--force : Refresh metadata even when it is still fresh}';

    protected $description = 'Cache optional Steam Store metadata for tracked games';

    public function handle(SyncGameStoreMetadata $action): int
    {
        $force = (bool) $this->option('force');
        $updated = 0;

        Game::query()
            ->orderBy('id')
            ->chunkById(50, function ($games) use ($action, $force, &$updated): void {
                foreach ($games as $game) {
                    if ($action->execute($game, $force)) {
                        $updated++;
                    }
                }
            });

        $this->info("Steam Store metadata refreshed for {$updated} games.");

        return self::SUCCESS;
    }
}
