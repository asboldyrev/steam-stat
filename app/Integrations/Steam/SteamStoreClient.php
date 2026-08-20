<?php

declare(strict_types=1);

namespace App\Integrations\Steam;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class SteamStoreClient
{
    /**
     * @return array<string, array{url:string,source:string}>
     */
    public function fetchArtwork(int $appId): array
    {
        $response = $this->client()->get('https://store.steampowered.com/api/appdetails', [
            'appids' => $appId,
            'l' => 'english',
        ]);

        if (!$response->successful()) {
            return [];
        }

        $payload = $response->json((string) $appId);
        if (!is_array($payload) || ($payload['success'] ?? false) !== true) {
            return [];
        }

        $data = $payload['data'] ?? null;
        if (!is_array($data)) {
            return [];
        }

        $result = [];

        $header = $this->firstString($data, ['header_image']);
        if ($header !== null) {
            $result['header'] = ['url' => $header, 'source' => 'steam-store'];
        }

        $capsule = $this->firstString($data, ['capsule_image', 'capsule_imagev5']);
        if ($capsule !== null) {
            $result['capsule'] = ['url' => $capsule, 'source' => 'steam-store'];
        }

        // appdetails does not expose the real Steam library hero/logo/client icon fields.
        // background is still useful as a last-resort hero, but original SteamGridDB
        // metadata is preferred by the artwork synchronizer when it is available.
        $hero = $this->firstString($data, ['background_raw', 'background']);
        if ($hero !== null) {
            $result['hero'] = ['url' => $hero, 'source' => 'steam-store'];
        }

        return $result;
    }

    /**
     * Kept for compatibility with the old metadata synchronizer while artwork migration
     * is being rolled out.
     *
     * @return array{cover_url:string}|null
     */
    public function fetchGameMetadata(int $appId): ?array
    {
        $artwork = $this->fetchArtwork($appId);
        $url = $artwork['header']['url'] ?? null;

        return is_string($url) ? ['cover_url' => $url] : null;
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(10)
            ->retry(2, 500, throw: false);
    }

    /**
     * @param list<string> $keys
     */
    private function firstString(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $data[$key] ?? null;
            if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false) {
                return $value;
            }
        }

        return null;
    }
}
