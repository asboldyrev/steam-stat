<?php

declare(strict_types=1);

namespace App\Integrations\Steam;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class SteamStoreClient
{
    public function fetchGameMetadata(int $appId): ?array
    {
        $coverUrl = $this->fetchCoverFromAppDetails($appId)
            ?? $this->fetchCoverFromStorePage($appId);

        if ($coverUrl === null) {
            return null;
        }

        return [
            'cover_url' => $coverUrl,
        ];
    }

    private function fetchCoverFromAppDetails(int $appId): ?string
    {
        $response = $this->jsonClient()->get('https://store.steampowered.com/api/appdetails', [
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

        // Prefer large artwork. capsule_image/capsule_imagev5 are intentionally last:
        // they are small store capsules and become visibly blurry on desktop cards.
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

    private function fetchCoverFromStorePage(int $appId): ?string
    {
        $response = $this->htmlClient()->get("https://store.steampowered.com/app/{$appId}/", [
            'l' => 'english',
        ]);

        if (!$response->successful()) {
            return null;
        }

        $html = $response->body();

        $patterns = [
            '/<meta\s+property=["\']og:image["\']\s+content=["\']([^"\']+)["\']/i',
            '/<meta\s+content=["\']([^"\']+)["\']\s+property=["\']og:image["\']/i',
            '/<link\s+rel=["\']image_src["\']\s+href=["\']([^"\']+)["\']/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $matches) === 1) {
                $url = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5);
                if (filter_var($url, FILTER_VALIDATE_URL) !== false) {
                    return $url;
                }
            }
        }

        return null;
    }

    private function jsonClient(): PendingRequest
    {
        return Http::acceptJson()
            ->timeout(10)
            ->retry(2, 500, throw: false);
    }

    private function htmlClient(): PendingRequest
    {
        return Http::withHeaders([
            'Accept' => 'text/html,application/xhtml+xml',
            'User-Agent' => 'Mozilla/5.0 (compatible; SteamStat/1.0)',
        ])
            ->timeout(10)
            ->retry(1, 500, throw: false);
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
