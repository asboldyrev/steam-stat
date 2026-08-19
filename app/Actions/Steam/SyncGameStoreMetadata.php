<?php

declare(strict_types=1);

namespace App\Actions\Steam;

use App\Integrations\Steam\SteamStoreClient;
use App\Models\Game;
use Carbon\CarbonImmutable;

final class SyncGameStoreMetadata
{
    public function __construct(private readonly SteamStoreClient $client) {}

    public function execute(Game $game, bool $force = false): bool
    {
        if (!$force && $game->store_metadata_synced_at?->isAfter(now()->subDays(30))) {
            return false;
        }

        $metadata = $this->client->fetchGameMetadata((int) $game->app_id);
        if ($metadata === null) {
            // A transient Store API failure must not make the game look successfully
            // refreshed for the next 30 days. Leave the previous artwork untouched and
            // allow the next scheduled/manual run to retry it.
            return false;
        }

        $game->forceFill([
            'cover_url' => $metadata['cover_url'] ?? $game->cover_url,
            'store_metadata_synced_at' => CarbonImmutable::now('UTC'),
        ])->save();

        return true;
    }
}
