<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Dto\Dashboard\DashboardStatsDto;
use App\Models\Game;
use App\Models\SummaryStat;

final class GetDashboardStats
{
    public function execute(): DashboardStatsDto
    {
        $stat = SummaryStat::query()->orderByDesc('date')->first();

        $totalMinutes = (int) ($stat?->total_minutes ?? 0);
        $deckMinutes = (int) ($stat?->deck_minutes ?? 0);
        $gamesCount = Game::query()->count();

        return new DashboardStatsDto(
            totalPlaytimeHours: (int) round($totalMinutes / 60),
            gamesCount: $gamesCount,
            steamDeckHours: (int) round($deckMinutes / 60),
            steamDeckPercentage: $totalMinutes > 0
                ? (int) round(($deckMinutes / $totalMinutes) * 100)
                : 0,
        );
    }
}
