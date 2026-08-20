<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Steam\SyncGameArtwork;
use App\Models\Game;
use Illuminate\Console\Command;

final class SyncSteamArtwork extends Command
{
    protected $signature = 'steam:sync-artwork
        {--force : Refresh artwork even when it is still fresh}
        {--game= : Sync one Steam app id only}
        {--delay=750 : Delay between games in milliseconds}';

    protected $description = 'Fetch, cache and persist Steam/SteamGridDB artwork for tracked games';

    public function handle(SyncGameArtwork $action): int
    {
        $force = (bool) $this->option('force');
        $delayMs = max(0, (int) $this->option('delay'));
        $appId = $this->option('game');
        $updated = 0;
        $attempted = 0;

        $query = Game::query()->orderBy('id');
        if ($appId !== null && $appId !== '') {
            $query->where('app_id', (int) $appId);
        }

        $query->chunkById(25, function ($games) use ($action, $force, $delayMs, &$updated, &$attempted): void {
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
