<?php

declare(strict_types=1);

namespace App\Services\Steam;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

final class ArtworkStorage
{
    /**
     * @param array{url:string,source:string} $asset
     * @return array{url:string,local_path:string,local_url:string,source:string,width:?int,height:?int,mime:?string}|null
     */
    public function store(int $appId, string $type, array $asset): ?array
    {
        $url = $asset['url'] ?? null;
        if (!is_string($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $response = Http::withHeaders([
            'User-Agent' => 'SteamStat/1.0',
            'Accept' => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        ])
            ->timeout(20)
            ->retry(2, 500, throw: false)
            ->get($url);

        if (!$response->successful()) {
            return null;
        }

        $body = $response->body();
        if ($body === '') {
            return null;
        }

        $mime = $this->detectMime($body, $response->header('Content-Type'));
        if ($mime === null || !str_starts_with($mime, 'image/')) {
            return null;
        }

        [$width, $height] = $this->dimensions($body);
        $extension = $this->extensionFor($mime, $url);
        $path = sprintf('games/%d/%s.%s', $appId, str_replace('_', '-', $type), $extension);

        Storage::disk('public')->put($path, $body);

        $localUrl = Storage::disk('public')->url($path)
            . '?v=' . substr(sha1($body), 0, 12);

        return [
            'url' => $url,
            'local_path' => $path,
            'local_url' => $localUrl,
            'source' => (string) ($asset['source'] ?? 'unknown'),
            'width' => $width,
            'height' => $height,
            'mime' => $mime,
        ];
    }

    private function detectMime(string $body, ?string $header): ?string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = finfo_buffer($finfo, $body);
                finfo_close($finfo);
                if (is_string($mime) && str_starts_with($mime, 'image/')) {
                    return $mime;
                }
            }
        }

        if (is_string($header) && $header !== '') {
            $mime = trim(explode(';', $header, 2)[0]);
            if (str_starts_with($mime, 'image/')) {
                return $mime;
            }
        }

        return null;
    }

    /**
     * @return array{0:?int,1:?int}
     */
    private function dimensions(string $body): array
    {
        $size = @getimagesizefromstring($body);
        if (!is_array($size)) {
            return [null, null];
        }

        return [(int) $size[0], (int) $size[1]];
    }

    private function extensionFor(string $mime, string $url): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/svg+xml' => 'svg',
            'image/x-icon', 'image/vnd.microsoft.icon' => 'ico',
            'image/avif' => 'avif',
            default => $this->extensionFromUrl($url) ?? 'img',
        };
    }

    private function extensionFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path)) {
            return null;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return preg_match('/^[a-z0-9]{2,5}$/', $extension) === 1 ? $extension : null;
    }
}
