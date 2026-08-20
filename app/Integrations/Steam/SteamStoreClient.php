<?php

declare(strict_types=1);

namespace App\Integrations\Steam;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class SteamStoreClient
{
    public function __construct(private readonly SteamGridDbClient $steamGridDbClient) {}

    public function fetchGameMetadata(int $appId): ?array
    {
        $coverUrl = $this->fetchCoverFromAppDetails($appId)
            ?? $this->steamGridDbClient->fetchWideCover($appId);

        if ($coverUrl === null) {
            return null;
        }

        return [
            'cover_url' => $coverUrl,
        ];
    }

    private function fetchCoverFromAppDetails(int $appId): ?string
    {
        $response = $this->client()->get('https://store.steampowered.com/api/appdetails', [
            'appids' => $appId,
            'l' => 'english',
        ]);

        if (!$response->successful()) {
            return null;
        }

        $payload = $response->json((string) $appId);
        if (!is_array($payload) || ($payload['success'] ?? false) !== true) {
            return null;
        }

        $data = $payload['data'] ?? null;
        if (!is_array($data)) {
            return null;
        }

        return $this->firstString($data, [
            'header_image',
            'background_raw',
            'background',
        ]) ?? $this->firstScreenshot($data)
            ?? $this->firstString($data, [
                'capsule_image',
                'capsule_imagev5',
            ]);
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(10)
            ->retry(2, 500, throw: false);
    }

    private function firstString(array $data, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $data[$key] ?? null;
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    private function firstScreenshot(array $data): ?string
    {
        $screenshots = $data['screenshots'] ?? null;
        if (!is_array($screenshots)) {
            return null;
        }

        foreach ($screenshots as $screenshot) {
            if (!is_array($screenshot)) {
                continue;
            }

            $path = $screenshot['path_full'] ?? null;
            if (is_string($path) && $path !== '') {
                return $path;
            }
        }

        return null;
    }
}
