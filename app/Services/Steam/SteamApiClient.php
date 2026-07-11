<?php

declare(strict_types=1);

namespace App\Services\Steam;

use App\Data\SteamGameData;
use App\Exceptions\SteamApiException;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Throwable;
use App\Data\SteamPlaytimeTotalsData;

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
     * Получить библиотеку пользователя.
     *
     * @return Collection<int, SteamGameData>
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
                /*
                 * При закрытом профиле Steam иногда возвращает пустой response,
                 * а не полноценную ошибку.
                 */
                $games = [];
            }

            if (! is_array($games)) {
                throw SteamApiException::invalidResponse(
                    'The "response.games" field is not an array.'
                );
            }

            return collect($games)
                ->filter(fn(mixed $game): bool => is_array($game))
                ->map(fn(array $game): SteamGameData => $this->mapGame($game))
                ->sortByDesc(
                    fn(SteamGameData $game): int => $game->totalMinutes
                )
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
     * Получить только игры, в которые пользователь когда-либо играл.
     *
     * @return Collection<int, SteamGameData>
     */
    public function getPlayedGames(): Collection
    {
        return $this->getOwnedGames()
            ->filter(
                fn(SteamGameData $game): bool => $game->totalMinutes > 0
            )
            ->values();
    }

    /**
     * Получить конкретную игру.
     */
    public function getGame(int $appId): ?SteamGameData
    {
        return $this->getOwnedGames()
            ->first(
                fn(SteamGameData $game): bool => $game->appId === $appId
            );
    }

    /**
     * Общие показатели по всей библиотеке.
     */
    public function getTotals(): SteamPlaytimeTotalsData
    {
        return SteamPlaytimeTotalsData::fromGames(
            $this->getPlayedGames()
        );
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

    private function mapGame(array $game): SteamGameData
    {
        $lastPlayedTimestamp = $this->integer($game, 'rtime_last_played');

        return new SteamGameData(
            appId: $this->integer($game, 'appid'),
            name: $this->string($game, 'name', 'Unknown game'),
            totalMinutes: $this->integer($game, 'playtime_forever'),
            windowsMinutes: $this->integer(
                $game,
                'playtime_windows_forever'
            ),
            linuxMinutes: $this->integer(
                $game,
                'playtime_linux_forever'
            ),
            macMinutes: $this->integer(
                $game,
                'playtime_mac_forever'
            ),
            deckMinutes: $this->integer(
                $game,
                'playtime_deck_forever'
            ),
            disconnectedMinutes: $this->integer(
                $game,
                'playtime_disconnected'
            ),
            lastPlayedAt: $lastPlayedTimestamp > 0
                ? CarbonImmutable::createFromTimestampUTC($lastPlayedTimestamp)
                : null,
            iconUrl: $this->nullableString($game, 'img_icon_url'),
            hasCommunityVisibleStats: (bool) (
                $game['has_community_visible_stats'] ?? false
            ),
        );
    }

    private function integer(
        array $data,
        string $key,
        int $default = 0,
    ): int {
        $value = $data[$key] ?? $default;

        return is_numeric($value)
            ? max(0, (int) $value)
            : $default;
    }

    private function string(
        array $data,
        string $key,
        string $default = '',
    ): string {
        $value = $data[$key] ?? $default;

        return is_string($value) && $value !== ''
            ? $value
            : $default;
    }

    private function nullableString(
        array $data,
        string $key,
    ): ?string {
        $value = $data[$key] ?? null;

        return is_string($value) && $value !== ''
            ? $value
            : null;
    }
}
