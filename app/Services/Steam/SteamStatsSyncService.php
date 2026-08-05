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

            // Получаем последнюю запись для этой игры
            $lastStat = GameStat::query()
                ->where('game_id', $dbGame->id)
                ->orderByDesc('date')
                ->first();

            // Если данные не изменились — пропускаем
            if ($lastStat !== null
                && (int) $lastStat->total_minutes === $game->totalMinutes
                && (int) $lastStat->windows_minutes === $game->windowsMinutes
                && (int) $lastStat->linux_minutes === $game->linuxMinutes
                && (int) $lastStat->mac_minutes === $game->macMinutes
                && (int) $lastStat->deck_minutes === $game->deckMinutes
                && (int) $lastStat->disconnected_minutes === $game->disconnectedMinutes
            ) {
                continue;
            }

            // Вычисляем delta
            $deltaTotal = $lastStat ? $game->totalMinutes - (int) $lastStat->total_minutes : 0;
            $deltaWindows = $lastStat ? $game->windowsMinutes - (int) $lastStat->windows_minutes : 0;
            $deltaLinux = $lastStat ? $game->linuxMinutes - (int) $lastStat->linux_minutes : 0;
            $deltaMac = $lastStat ? $game->macMinutes - (int) $lastStat->mac_minutes : 0;
            $deltaDeck = $lastStat ? $game->deckMinutes - (int) $lastStat->deck_minutes : 0;
            $deltaDisconnected = $lastStat ? $game->disconnectedMinutes - (int) $lastStat->disconnected_minutes : 0;

            // Создаём новую запись (или обновляем запись за сегодня если уже есть)
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
                    'delta_total_minutes' => $deltaTotal,
                    'delta_windows_minutes' => $deltaWindows,
                    'delta_linux_minutes' => $deltaLinux,
                    'delta_mac_minutes' => $deltaMac,
                    'delta_deck_minutes' => $deltaDeck,
                    'delta_disconnected_minutes' => $deltaDisconnected,
                    'last_played_at' => $game->lastPlayedAt,
                ]
            );
        }
    }

    private function syncSummaryStats(SteamPlaytimeTotalsData $totals, string $dateString): void
    {
        // Получаем последнюю запись суммарной статистики
        $lastStat = SummaryStat::query()
            ->orderByDesc('date')
            ->first();

        // Если данные не изменились — пропускаем
        if ($lastStat !== null
            && (int) $lastStat->total_minutes === $totals->totalMinutes
            && (int) $lastStat->windows_minutes === $totals->windowsMinutes
            && (int) $lastStat->linux_minutes === $totals->linuxMinutes
            && (int) $lastStat->mac_minutes === $totals->macMinutes
            && (int) $lastStat->deck_minutes === $totals->deckMinutes
            && (int) $lastStat->disconnected_minutes === $totals->disconnectedMinutes
        ) {
            return;
        }

        // Вычисляем delta
        $deltaTotal = $lastStat ? $totals->totalMinutes - (int) $lastStat->total_minutes : 0;
        $deltaWindows = $lastStat ? $totals->windowsMinutes - (int) $lastStat->windows_minutes : 0;
        $deltaLinux = $lastStat ? $totals->linuxMinutes - (int) $lastStat->linux_minutes : 0;
        $deltaLinuxDesktop = $lastStat ? $totals->linuxDesktopMinutes - (int) $lastStat->linux_desktop_minutes : 0;
        $deltaMac = $lastStat ? $totals->macMinutes - (int) $lastStat->mac_minutes : 0;
        $deltaDeck = $lastStat ? $totals->deckMinutes - (int) $lastStat->deck_minutes : 0;
        $deltaDisconnected = $lastStat ? $totals->disconnectedMinutes - (int) $lastStat->disconnected_minutes : 0;
        $deltaUnclassified = $lastStat ? $totals->unclassifiedMinutes - (int) $lastStat->unclassified_minutes : 0;

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
                'delta_total_minutes' => $deltaTotal,
                'delta_windows_minutes' => $deltaWindows,
                'delta_linux_minutes' => $deltaLinux,
                'delta_linux_desktop_minutes' => $deltaLinuxDesktop,
                'delta_mac_minutes' => $deltaMac,
                'delta_deck_minutes' => $deltaDeck,
                'delta_disconnected_minutes' => $deltaDisconnected,
                'delta_unclassified_minutes' => $deltaUnclassified,
            ]
        );
    }
}