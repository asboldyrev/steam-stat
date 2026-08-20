<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Steam\SyncGameArtwork;
use App\Models\Game;
use Illuminate\Console\Command;

final class SyncSteamStoreMetadata extends Command
{
    protected $signature = 'steam:sync-store-metadata
        {--force : Refresh artwork even when it is still fresh}
        {--delay=750 : Delay between games in milliseconds}';

    protected $description = 'Deprecated alias for steam:sync-artwork';

    public function handle(SyncGameArtwork $action): int
    {
        $this->warn('steam:sync-store-metadata is deprecated; use steam:sync-artwork instead.');

        $force = (bool) $this->option('force');
        $delayMs = max(0, (int) $this->option('delay'));
        $updated = 0;
        $attempted = 0;

        Game::query()
            ->orderBy('id')
            ->chunkById(25, function ($games) use ($action, $force, $delayMs, &$updated, &$attempted): void {
                foreach ($games as $game) {
                    if (!$force && $game->artwork_synced_at?->isAfter(now()->subDays(30))) {
                        continue;
                    }

                    $attempted++;
                    if ($action->execute($game, $force)) {
                        $updated++;
                    }

                    if ($delayMs > 0) {
                        usleep($delayMs * 1000);
                    }
                }
            });

        $this->info("Artwork refreshed for {$updated} of {$attempted} attempted games.");

        return self::SUCCESS;
    }
}
