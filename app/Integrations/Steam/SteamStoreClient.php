<?php

declare(strict_types=1);

namespace App\Integrations\Steam;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class SteamStoreClient
{
    public function fetchGameMetadata(int $appId): ?array
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

        $coverUrl = $this->firstString($data, [
            'capsule_imagev5',
            'capsule_image',
            'header_image',
        ]);

        return [
            'cover_url' => $coverUrl,
        ];
    }

    private function client(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(10)
            ->retry(2, 250, throw: false);
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
}
