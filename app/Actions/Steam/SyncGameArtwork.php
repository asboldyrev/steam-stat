<?php

declare(strict_types=1);

namespace App\Actions\Steam;

use App\Integrations\Steam\SteamGridDbClient;
use App\Integrations\Steam\SteamStoreClient;
use App\Models\Game;
use App\Services\Steam\ArtworkStorage;
use Carbon\CarbonImmutable;

final class SyncGameArtwork
{
    private const TYPES = [
        'header',
        'capsule',
        'hero',
        'logo',
        'icon',
        'client_icon',
    ];

    public function __construct(
        private readonly SteamGridDbClient $steamGridDbClient,
        private readonly SteamStoreClient $steamStoreClient,
        private readonly ArtworkStorage $storage,
    ) {}

    public function execute(Game $game, bool $force = false): bool
    {
        if (!$force && $game->artwork_synced_at?->isAfter(now()->subDays(30))) {
            return false;
        }

        $appId = (int) $game->app_id;
        $steamGridDb = $this->steamGridDbClient->fetchArtwork($appId);
        $steamStore = $this->steamStoreClient->fetchArtwork($appId);
        $previous = is_array($game->artwork) ? $game->artwork : [];
        $artwork = [];

        foreach (self::TYPES as $type) {
            $candidate = $this->candidate($type, $steamGridDb, $steamStore, $game);
            if ($candidate === null) {
                if (isset($previous[$type]) && is_array($previous[$type])) {
                    $artwork[$type] = $previous[$type];
                }
                continue;
            }

            $stored = $this->storage->store($appId, $type, $candidate);
            $artwork[$type] = $stored ?? [
                'url' => $candidate['url'],
                'source' => $candidate['source'],
                'local_path' => null,
                'local_url' => null,
                'width' => null,
                'height' => null,
                'mime' => null,
            ];
        }

        if ($artwork === []) {
            return false;
        }

        $game->forceFill([
            'artwork' => $artwork,
            // Keep the old field populated during the transition so older API code does
            // not break. It can be removed after the artwork migration is verified.
            'cover_url' => $artwork['header']['url'] ?? $game->cover_url,
            'artwork_synced_at' => CarbonImmutable::now('UTC'),
        ])->save();

        return true;
    }

    /**
     * Original Steam assets exposed by SteamGridDB are preferred. If SteamGridDB has no
     * original for a type, its curated artwork is preferred over Store API fallbacks;
     * this avoids generic Steam placeholder images returned for some legacy/unlisted apps.
     *
     * @param array<string, array{url:string,source:string}|null> $steamGridDb
     * @param array<string, array{url:string,source:string}> $steamStore
     * @return array{url:string,source:string}|null
     */
    private function candidate(string $type, array $steamGridDb, array $steamStore, Game $game): ?array
    {
        $grid = $steamGridDb[$type] ?? null;
        if (is_array($grid) && ($grid['source'] ?? null) === 'steam-original') {
            return $grid;
        }

        if (is_array($grid)) {
            return $grid;
        }

        $store = $steamStore[$type] ?? null;
        if (is_array($store)) {
            return $store;
        }

        if ($type === 'icon') {
            $icon = $game->iconUrlLarge();
            if ($icon !== null) {
                return ['url' => $icon, 'source' => 'steam-owned-games'];
            }
        }

        return null;
    }
}
