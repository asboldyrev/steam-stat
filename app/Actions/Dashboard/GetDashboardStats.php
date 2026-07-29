<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Queries\GameStats\GetLatestGameStats;

final class GetDashboardStats
{
    public function __construct(
        private readonly GetLatestGameStats $latestGameStats,
    ) {}

    /**
     * @return array{
     *     total_playtime_hours: int,
     *     games_count: int,
     *     steam_deck_hours: int,
     *     steam_deck_percentage: int
     * }
     */
    public function execute(): array
    {
        $stats = $this->latestGameStats->execute();

        $totalMinutes = (int) $stats->sum('total_minutes');
        $deckMinutes = (int) $stats->sum('deck_minutes');

        $gamesCount = $stats
            ->where('total_minutes', '>', 0)
            ->pluck('game_id')
            ->unique()
            ->count();

        $totalHours = (int) round($totalMinutes / 60);
        $deckHours = (int) round($deckMinutes / 60);

        $deckPercentage = $totalMinutes > 0
            ? (int) round(($deckMinutes / $totalMinutes) * 100)
            : 0;

        return [
            'total_playtime_hours' => $totalHours,
            'games_count' => $gamesCount,
            'steam_deck_hours' => $deckHours,
            'steam_deck_percentage' => $deckPercentage,
        ];
    }
}
