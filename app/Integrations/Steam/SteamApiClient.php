<?php

declare(strict_types=1);

namespace App\Integrations\Steam;

use App\Dto\Steam\SteamGameDto;
use App\Exceptions\SteamApiException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Throwable;

final class SteamApiClient
{
    private const OWNED_GAMES_ENDPOINT = '/IPlayerService/GetOwnedGames/v1/';

    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $apiKey,
        private readonly string $steamId,
        private readonly string $baseUrl = 'https://api.steampowered.com',
    ) {
        if ($this->apiKey === '') {
            throw SteamApiException::invalidConfiguration('api_key');
        }

        if ($this->steamId === '') {
            throw SteamApiException::invalidConfiguration('steam_id');
        }
    }

    /**
     * @return Collection<int, SteamGameDto>
     *
     * @throws SteamApiException
     */
    public function getOwnedGames(
        bool $includeFreeGames = true,
        string $language = 'russian',
    ): Collection {
        try {
            $response = $this->request()
                ->get(self::OWNED_GAMES_ENDPOINT, [
                    'key' => $this->apiKey,
                    'steamid' => $this->steamId,
                    'include_appinfo' => true,
                    'include_played_free_games' => $includeFreeGames,
                    'include_extended_appinfo' => true,
                    'language' => $language,
                    'format' => 'json',
                ])
                ->throw();

            $games = $response->json('response.games');

            if ($games === null) {
                $games = [];
            }

            if (! is_array($games)) {
                throw SteamApiException::invalidResponse(
                    'The "response.games" field is not an array.'
                );
            }

            return collect($games)
                ->filter(fn (mixed $game): bool => is_array($game))
                ->map(fn (array $game): SteamGameDto => $this->mapGame($game))
                ->sortByDesc(fn (SteamGameDto $game): int => $game->totalMinutes)
                ->values();
        } catch (SteamApiException $exception) {
            throw $exception;
        } catch (ConnectionException | RequestException $exception) {
            throw SteamApiException::invalidResponse(
                $exception->getMessage(),
                $exception,
            );
        } catch (Throwable $exception) {
            throw SteamApiException::invalidResponse(
                $exception->getMessage(),
                $exception,
            );
        }
    }

    /**
     * @return Collection<int, SteamGameDto>
     */
    public function getPlayedGames(): Collection
    {
        return $this->getOwnedGames()
            ->filter(fn (SteamGameDto $game): bool => $game->totalMinutes > 0)
            ->values();
    }

    public function getGame(int $appId): ?SteamGameDto
    {
        return $this->getOwnedGames()
            ->first(fn (SteamGameDto $game): bool => $game->appId === $appId);
    }

    private function request(): PendingRequest
    {
        return $this->http
            ->baseUrl(rtrim($this->baseUrl, '/'))
            ->acceptJson()
            ->asJson()
            ->connectTimeout(10)
            ->timeout(30)
            ->retry(
                times: 3,
                sleepMilliseconds: 500,
                when: function (Throwable $exception): bool {
                    return $exception instanceof ConnectionException
                        || (
                            $exception instanceof RequestException
                            && $exception->response->serverError()
                        );
                },
                throw: false,
            );
    }

    /**
     * @param array<string, mixed> $game
     */
    private function mapGame(array $game): SteamGameDto
    {
        $lastPlayedTimestamp = $this->integer($game, 'rtime_last_played');

        return new SteamGameDto(
            appId: $this->integer($game, 'appid'),
            name: $this->string($game, 'name', 'Unknown game'),
            totalMinutes: $this->integer($game, 'playtime_forever'),
            windowsMinutes: $this->integer($game, 'playtime_windows_forever'),
            linuxMinutes: $this->integer($game, 'playtime_linux_forever'),
            macMinutes: $this->integer($game, 'playtime_mac_forever'),
            deckMinutes: $this->integer($game, 'playtime_deck_forever'),
            disconnectedMinutes: $this->integer($game, 'playtime_disconnected'),
            lastPlayedAt: $lastPlayedTimestamp > 0
                ? CarbonImmutable::createFromTimestampUTC($lastPlayedTimestamp)
                : null,
            iconUrl: $this->nullableString($game, 'img_icon_url'),
            hasCommunityVisibleStats: (bool) ($game['has_community_visible_stats'] ?? false),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function integer(array $data, string $key, int $default = 0): int
    {
        $value = $data[$key] ?? $default;

        return is_numeric($value) ? max(0, (int) $value) : $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function string(array $data, string $key, string $default = ''): string
    {
        $value = $data[$key] ?? $default;

        return is_string($value) && $value !== '' ? $value : $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function nullableString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }
}
