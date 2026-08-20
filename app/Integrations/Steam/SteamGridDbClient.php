<?php

declare(strict_types=1);

namespace App\Integrations\Steam;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

final class SteamGridDbClient
{
    public function fetchWideCover(int $appId): ?string
    {
        $apiKey = (string) config('steam-stat.steamgriddb.api_key', '');
        if ($apiKey === '') {
            return null;
        }

        $response = $this->client($apiKey)->get(
            sprintf('https://www.steamgriddb.com/api/v2/grids/steam/%d', $appId),
            [
                'dimensions' => '920x430,460x215',
                'types' => 'static',
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
            if (!is_array($item)) {
                continue;
            }

            if (($item['nsfw'] ?? false) === true || ($item['humor'] ?? false) === true) {
                continue;
            }

            $url = $item['url'] ?? null;
            if (is_string($url) && filter_var($url, FILTER_VALIDATE_URL) !== false) {
                return $url;
            }
        }

        return null;
    }

    private function client(string $apiKey): PendingRequest
    {
        return Http::acceptJson()
            ->withToken($apiKey)
            ->timeout(10)
            ->retry(2, 500, throw: false);
    }
}
