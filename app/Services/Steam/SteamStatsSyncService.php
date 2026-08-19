<?php

declare(strict_types=1);

namespace App\Services\Steam;

use App\Dto\Steam\SteamGameDto;
use App\Dto\Steam\SteamPlaytimeTotalsDto;
use App\Integrations\Steam\SteamApiClient;
use App\Models\Game;
use App\Models\GameStat;
use App\Models\PlaytimeSnapshot;
use App\Models\SummaryStat;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class SteamStatsSyncService
{
    public function __construct(
        private readonly SteamApiClient $steamApi,
    ) {}

    public function sync(?CarbonImmutable $capturedAt = null): void
    {
        $timezone = (string) config('steam-stat.timezone', config('app.timezone', 'UTC'));
        $activityAt = $capturedAt?->setTimezone($timezone) ?? CarbonImmutable::now($timezone);
        $capturedAtUtc = $activityAt->utc();
        $dateString = $activityAt->toDateString();

        $games = $this->steamApi->getPlayedGames();
        $totals = SteamPlaytimeTotalsDto::fromGames($games);

        DB::transaction(function () use ($games, $totals, $capturedAtUtc, $dateString): void {
            $databaseGames = $this->syncGames($games);

            $this->syncPlaytimeSnapshots($games, $databaseGames, $capturedAtUtc);
            $this->syncLegacyGameStats($games, $databaseGames, $dateString);
            $this->syncLegacySummaryStats($totals, $dateString);
        });
    }

    /**
     * @param Collection<int, SteamGameDto> $games
     * @return Collection<int, Game>
     */
    private function syncGames(Collection $games): Collection
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

        return Game::query()
            ->whereIn('app_id', $games->pluck('appId'))
            ->get()
            ->keyBy('app_id');
    }

    /**
     * Store sparse observations. An unchanged Steam counter does not create a row.
     * Negative deltas are preserved as counter corrections, while activity queries
     * can clamp them to zero.
     *
     * @param Collection<int, SteamGameDto> $games
     * @param Collection<int, Game> $databaseGames
     */
    private function syncPlaytimeSnapshots(
        Collection $games,
        Collection $databaseGames,
        CarbonImmutable $capturedAt,
    ): void {
        foreach ($games as $game) {
            $databaseGame = $databaseGames->get($game->appId);

            if (! $databaseGame instanceof Game) {
                continue;
            }

            $previous = PlaytimeSnapshot::query()
                ->where('game_id', $databaseGame->id)
                ->latest('captured_at')
                ->latest('id')
                ->first();

            if ($previous !== null && $this->snapshotMatches($previous, $game)) {
                continue;
            }

            $deltas = [
                'total' => $previous === null ? 0 : $game->totalMinutes - (int) $previous->total_minutes,
                'windows' => $previous === null ? 0 : $game->windowsMinutes - (int) $previous->windows_minutes,
                'linux' => $previous === null ? 0 : $game->linuxMinutes - (int) $previous->linux_minutes,
                'mac' => $previous === null ? 0 : $game->macMinutes - (int) $previous->mac_minutes,
                'deck' => $previous === null ? 0 : $game->deckMinutes - (int) $previous->deck_minutes,
                'disconnected' => $previous === null ? 0 : $game->disconnectedMinutes - (int) $previous->disconnected_minutes,
            ];

            PlaytimeSnapshot::query()->create([
                'game_id' => $databaseGame->id,
                'captured_at' => $capturedAt,
                'total_minutes' => $game->totalMinutes,
                'windows_minutes' => $game->windowsMinutes,
                'linux_minutes' => $game->linuxMinutes,
                'mac_minutes' => $game->macMinutes,
                'deck_minutes' => $game->deckMinutes,
                'disconnected_minutes' => $game->disconnectedMinutes,
                'delta_total_minutes' => $deltas['total'],
                'delta_windows_minutes' => $deltas['windows'],
                'delta_linux_minutes' => $deltas['linux'],
                'delta_mac_minutes' => $deltas['mac'],
                'delta_deck_minutes' => $deltas['deck'],
                'delta_disconnected_minutes' => $deltas['disconnected'],
                'has_counter_correction' => collect($deltas)->contains(fn (int $delta): bool => $delta < 0),
                'last_played_at' => $game->lastPlayedAt,
            ]);
        }
    }

    private function snapshotMatches(PlaytimeSnapshot $snapshot, SteamGameDto $game): bool
    {
        return (int) $snapshot->total_minutes === $game->totalMinutes
            && (int) $snapshot->windows_minutes === $game->windowsMinutes
            && (int) $snapshot->linux_minutes === $game->linuxMinutes
            && (int) $snapshot->mac_minutes === $game->macMinutes
            && (int) $snapshot->deck_minutes === $game->deckMinutes
            && (int) $snapshot->disconnected_minutes === $game->disconnectedMinutes;
    }

    /**
     * Keep the existing daily table correct while the application is migrated to snapshots.
     * The daily delta is always measured from the latest observation before that day,
     * so repeated syncs on the same day accumulate instead of overwriting the delta.
     *
     * @param Collection<int, SteamGameDto> $games
     * @param Collection<int, Game> $databaseGames
     */
    private function syncLegacyGameStats(
        Collection $games,
        Collection $databaseGames,
        string $dateString,
    ): void {
        foreach ($games as $game) {
            $databaseGame = $databaseGames->get($game->appId);

            if (! $databaseGame instanceof Game) {
                continue;
            }

            $currentDay = GameStat::query()
                ->where('game_id', $databaseGame->id)
                ->where('date', $dateString)
                ->first();

            if ($currentDay !== null
                && (int) $currentDay->total_minutes === $game->totalMinutes
                && (int) $currentDay->windows_minutes === $game->windowsMinutes
                && (int) $currentDay->linux_minutes === $game->linuxMinutes
                && (int) $currentDay->mac_minutes === $game->macMinutes
                && (int) $currentDay->deck_minutes === $game->deckMinutes
                && (int) $currentDay->disconnected_minutes === $game->disconnectedMinutes
            ) {
                continue;
            }

            $baseline = GameStat::query()
                ->where('game_id', $databaseGame->id)
                ->where('date', '<', $dateString)
                ->orderByDesc('date')
                ->first();

            GameStat::updateOrCreate(
                [
                    'game_id' => $databaseGame->id,
                    'date' => $dateString,
                ],
                [
                    'total_minutes' => $game->totalMinutes,
                    'windows_minutes' => $game->windowsMinutes,
                    'linux_minutes' => $game->linuxMinutes,
                    'mac_minutes' => $game->macMinutes,
                    'deck_minutes' => $game->deckMinutes,
                    'disconnected_minutes' => $game->disconnectedMinutes,
                    'delta_total_minutes' => $baseline === null ? 0 : $game->totalMinutes - (int) $baseline->total_minutes,
                    'delta_windows_minutes' => $baseline === null ? 0 : $game->windowsMinutes - (int) $baseline->windows_minutes,
                    'delta_linux_minutes' => $baseline === null ? 0 : $game->linuxMinutes - (int) $baseline->linux_minutes,
                    'delta_mac_minutes' => $baseline === null ? 0 : $game->macMinutes - (int) $baseline->mac_minutes,
                    'delta_deck_minutes' => $baseline === null ? 0 : $game->deckMinutes - (int) $baseline->deck_minutes,
                    'delta_disconnected_minutes' => $baseline === null ? 0 : $game->disconnectedMinutes - (int) $baseline->disconnected_minutes,
                    'last_played_at' => $game->lastPlayedAt,
                ]
            );
        }
    }

    private function syncLegacySummaryStats(SteamPlaytimeTotalsDto $totals, string $dateString): void
    {
        $currentDay = SummaryStat::query()->where('date', $dateString)->first();

        if ($currentDay !== null
            && (int) $currentDay->total_minutes === $totals->totalMinutes
            && (int) $currentDay->windows_minutes === $totals->windowsMinutes
            && (int) $currentDay->linux_minutes === $totals->linuxMinutes
            && (int) $currentDay->mac_minutes === $totals->macMinutes
            && (int) $currentDay->deck_minutes === $totals->deckMinutes
            && (int) $currentDay->disconnected_minutes === $totals->disconnectedMinutes
        ) {
            return;
        }

        $baseline = SummaryStat::query()
            ->where('date', '<', $dateString)
            ->orderByDesc('date')
            ->first();

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
                'delta_total_minutes' => $baseline === null ? 0 : $totals->totalMinutes - (int) $baseline->total_minutes,
                'delta_windows_minutes' => $baseline === null ? 0 : $totals->windowsMinutes - (int) $baseline->windows_minutes,
                'delta_linux_minutes' => $baseline === null ? 0 : $totals->linuxMinutes - (int) $baseline->linux_minutes,
                'delta_linux_desktop_minutes' => $baseline === null ? 0 : $totals->linuxDesktopMinutes - (int) $baseline->linux_desktop_minutes,
                'delta_mac_minutes' => $baseline === null ? 0 : $totals->macMinutes - (int) $baseline->mac_minutes,
                'delta_deck_minutes' => $baseline === null ? 0 : $totals->deckMinutes - (int) $baseline->deck_minutes,
                'delta_disconnected_minutes' => $baseline === null ? 0 : $totals->disconnectedMinutes - (int) $baseline->disconnected_minutes,
                'delta_unclassified_minutes' => $baseline === null ? 0 : $totals->unclassifiedMinutes - (int) $baseline->unclassified_minutes,
            ]
        );
    }
}
