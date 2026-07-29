<?php

declare(strict_types=1);

namespace App\Services\Steam;

use App\Data\SteamGameData;
use App\Data\SteamPlaytimeTotalsData;
use App\Models\Game;
use App\Models\GameStat;
use App\Models\SummaryStat;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class SteamStatsSyncService
{
    public function __construct(
        private readonly SteamApiClient $steamApi,
    ) {}

    /**
     * Запросить статистику из Steam API и сохранить в БД.
     * Если за указанную дату уже есть записи — обновить их.
     */
    public function sync(?CarbonImmutable $date = null): void
    {
        $date ??= CarbonImmutable::today();
        $dateString = $date->toDateString();

        $games = $this->steamApi->getPlayedGames();
        $totals = $this->steamApi->getTotals();

        $this->syncGames($games);
        $this->syncGameStats($games, $dateString);
        $this->syncSummaryStats($totals, $dateString);
    }

    /**
     * @param Collection<int, SteamGameData> $games
     */
    private function syncGames(Collection $games): void
    {
        foreach ($games as $game) {
            Game::updateOrCreate(
                ['app_id' => $game->appId],
                [
                    'name' => $game->name,
                    'icon_url' => $game->iconUrl,
                    'has_community_visible_stats' => $game->hasCommunityVisibleStats,
                ]
            );
        }
    }

    /**
     * @param Collection<int, SteamGameData> $games
     */
    private function syncGameStats(Collection $games, string $dateString): void
    {
        foreach ($games as $game) {
            $dbGame = Game::query()->where('app_id', $game->appId)->first();

            if ($dbGame === null) {
                continue;
            }

            GameStat::updateOrCreate(
                [
                    'game_id' => $dbGame->id,
                    'date' => $dateString,
                ],
                [
                    'total_minutes' => $game->totalMinutes,
                    'windows_minutes' => $game->windowsMinutes,
                    'linux_minutes' => $game->linuxMinutes,
                    'mac_minutes' => $game->macMinutes,
                    'deck_minutes' => $game->deckMinutes,
                    'disconnected_minutes' => $game->disconnectedMinutes,
                    'last_played_at' => $game->lastPlayedAt,
                ]
            );
        }
    }

    private function syncSummaryStats(SteamPlaytimeTotalsData $totals, string $dateString): void
    {
        SummaryStat::updateOrCreate(
            ['date' => $dateString],
            [
                'games_count' => $totals->gamesCount,
                'total_minutes' => $totals->totalMinutes,
                'windows_minutes' => $totals->windowsMinutes,
                'linux_minutes' => $totals->linuxMinutes,
                'linux_desktop_minutes' => $totals->linuxDesktopMinutes,
                'mac_minutes' => $totals->macMinutes,
                'deck_minutes' => $totals->deckMinutes,
                'disconnected_minutes' => $totals->disconnectedMinutes,
                'unclassified_minutes' => $totals->unclassifiedMinutes,
            ]
        );
    }
}
