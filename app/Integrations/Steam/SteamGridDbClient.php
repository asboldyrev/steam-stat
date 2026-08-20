<?php

declare(strict_types=1);

namespace App\Integrations\Steam;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class SteamGridDbClient
{
    /**
     * @return array<string, array{url:string,source:string}|null>
     */
    public function fetchArtwork(int $appId): array
    {
        $original = $this->fetchOriginalSteamAssets($appId);

        return [
            'header' => $original['header'] ?? $this->fetchAsset('grids', $appId, [
                'dimensions' => '920x430,460x215',
                'types' => 'static',
            ]),
            'capsule' => $original['capsule'] ?? $this->fetchAsset('grids', $appId, [
                'dimensions' => '600x900,660x930',
                'types' => 'static',
            ]),
            'hero' => $original['hero'] ?? $this->fetchAsset('heroes', $appId, [
                'types' => 'static',
            ]),
            'logo' => $original['logo'] ?? $this->fetchAsset('logos', $appId, [
                'types' => 'static',
            ]),
            'icon' => $original['icon'] ?? $this->fetchAsset('icons', $appId, [
                'types' => 'static',
            ]),
            'client_icon' => $original['client_icon'] ?? null,
        ];
    }

    public function fetchWideCover(int $appId): ?string
    {
        return $this->fetchArtwork($appId)['header']['url'] ?? null;
    }

    /**
     * SteamGridDB exposes the same metadata used by its “View Original Steam Assets” UI
     * through a public game endpoint. This endpoint is not part of the documented v2 API,
     * so it is isolated here and every field is treated as optional.
     *
     * @return array<string, array{url:string,source:string}>
     */
    private function fetchOriginalSteamAssets(int $appId): array
    {
        $gameId = $this->resolveGameId($appId);
        if ($gameId === null) {
            return [];
        }

        $response = Http::acceptJson()
            ->withHeaders(['Referer' => 'https://www.steamgriddb.com/'])
            ->timeout(10)
            ->retry(1, 500, throw: false)
            ->get("https://www.steamgriddb.com/api/public/game/{$gameId}");

        if (!$response->successful()) {
            return [];
        }

        $metadata = $response->json('data.platforms.steam.metadata');
        if (!is_array($metadata)) {
            return [];
        }

        $aliases = [
            'header' => ['header', 'header_image'],
            'capsule' => ['library_600x900_2x', 'library_600x900', 'capsule', 'capsule_image'],
            'hero' => ['library_hero', 'hero'],
            'logo' => ['logo', 'logo_2x'],
            'icon' => ['icon'],
            'client_icon' => ['clienticon', 'client_icon'],
        ];

        $result = [];
        foreach ($aliases as $type => $keys) {
            $value = $this->firstValue($metadata, $keys);
            if ($value === null) {
                continue;
            }

            $url = $this->originalAssetUrl($appId, $type, $value);
            if ($url !== null) {
                $result[$type] = ['url' => $url, 'source' => 'steam-original'];
            }
        }

        return $result;
    }

    private function resolveGameId(int $appId): ?int
    {
        $apiKey = $this->apiKey();
        if ($apiKey === '') {
            return null;
        }

        $response = $this->client($apiKey)
            ->get(sprintf('https://www.steamgriddb.com/api/v2/games/steam/%d', $appId));

        if (!$response->successful()) {
            return null;
        }

        $id = $response->json('data.id');

        return is_numeric($id) ? (int) $id : null;
    }

    /**
     * @param array<string,string> $params
     * @return array{url:string,source:string}|null
     */
    private function fetchAsset(string $type, int $appId, array $params = []): ?array
    {
        $apiKey = $this->apiKey();
        if ($apiKey === '') {
            return null;
        }

        $response = $this->client($apiKey)->get(
            sprintf('https://www.steamgriddb.com/api/v2/%s/steam/%d', $type, $appId),
            $params + [
                'nsfw' => 'false',
                'humor' => 'false',
            ],
        );

        if (!$response->successful()) {
            return null;
        }

        $items = $response->json('data');
        if (!is_array($items) || $items === []) {
            return null;
        }

        usort($items, static function (mixed $left, mixed $right): int {
            if (!is_array($left) || !is_array($right)) {
                return 0;
            }

            $leftPixels = ((int) ($left['width'] ?? 0)) * ((int) ($left['height'] ?? 0));
            $rightPixels = ((int) ($right['width'] ?? 0)) * ((int) ($right['height'] ?? 0));
            if ($leftPixels !== $rightPixels) {
                return $rightPixels <=> $leftPixels;
            }

            $leftScore = ((int) ($left['upvotes'] ?? 0)) - ((int) ($left['downvotes'] ?? 0));
            $rightScore = ((int) ($right['upvotes'] ?? 0)) - ((int) ($right['downvotes'] ?? 0));

            return $rightScore <=> $leftScore;
        });

        foreach ($items as $item) {
            if (!is_array($item) || ($item['nsfw'] ?? false) === true || ($item['humor'] ?? false) === true) {
                continue;
            }

            $url = $item['url'] ?? null;
            if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL) !== false) {
                return ['url' => $url, 'source' => 'steamgriddb'];
            }
        }

        return null;
    }

    /**
     * @param list<string> $keys
     */
    private function firstValue(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $data[$key] ?? null;
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }

    private function originalAssetUrl(int $appId, string $type, string $value): ?string
    {
        if (filter_var($value, FILTER_VALIDATE_URL) !== false) {
            return $value;
        }

        if ($type === 'client_icon' && preg_match('/^[a-f0-9]{40}$/i', $value) === 1) {
            return sprintf(
                'https://cdn.cloudflare.steamstatic.com/steamcommunity/public/images/apps/%d/%s.ico',
                $appId,
                $value,
            );
        }

        if ($type === 'icon' && preg_match('/^[a-f0-9]{40}$/i', $value) === 1) {
            return sprintf(
                'https://cdn.cloudflare.steamstatic.com/steamcommunity/public/images/apps/%d/%s.jpg',
                $appId,
                $value,
            );
        }

        // Some SteamGridDB metadata revisions expose the presence/hash of an original
        // asset rather than its full URL. In that case use the same official Steam paths
        // used by projects such as Hydra and Steam ROM Manager, but only because the
        // original-asset metadata explicitly confirmed that this type exists.
        return match ($type) {
            'header' => "https://steamcdn-a.akamaihd.net/steam/apps/{$appId}/header.jpg",
            'capsule' => "https://cdn.cloudflare.steamstatic.com/steam/apps/{$appId}/library_600x900.jpg",
            'hero' => "https://steamcdn-a.akamaihd.net/steam/apps/{$appId}/library_hero.jpg",
            'logo' => "https://cdn.cloudflare.steamstatic.com/steam/apps/{$appId}/logo.png",
            default => null,
        };
    }

    private function apiKey(): string
    {
        return (string) config('steam-stat.steamgriddb.api_key', '');
    }

    private function client(string $apiKey): PendingRequest
    {
        return Http::acceptJson()
            ->withToken($apiKey)
            ->timeout(10)
            ->retry(2, 500, throw: false);
    }
}
