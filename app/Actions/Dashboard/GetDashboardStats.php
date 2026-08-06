<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\Game;
use App\Models\SummaryStat;

final class GetDashboardStats
{
    public function __construct() {}

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
        $stat = SummaryStat::orderByDesc('date')->first();

        $totalMinutes = (int) $stat->total_minutes;
        $deckMinutes = (int) $stat->deck_minutes;

        $gamesCount = Game::query()->count();

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
