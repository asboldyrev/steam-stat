<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Dto\Dashboard\TopGameDto;
use App\Queries\GameStats\GetLatestGameStats;

final class GetTopGames
{
    public function __construct(
        private readonly GetLatestGameStats $latestGameStats,
    ) {}

    /**
     * @return list<TopGameDto>
     */
    public function execute(): array
    {
        $stats = $this->latestGameStats->execute(limit: 3);
        $gradients = [
            'from-blue-600 to-blue-400',
            'from-green-600 to-emerald-400',
            'from-purple-600 to-pink-400',
            'from-yellow-600 to-orange-400',
            'from-red-600 to-rose-400',
            'from-indigo-600 to-violet-400',
        ];

        return $stats
            ->values()
            ->map(
                fn ($stat, int $index): TopGameDto => new TopGameDto(
                    gameId: $stat->game->id,
                    gameName: $stat->game->name,
                    iconUrl: $stat->game->iconUrlLarge(),
                    totalTime: (int) round($stat->total_minutes / 60),
                    lastPlayed: $stat->last_played_at,
                    gradient: $gradients[$index % count($gradients)],
                )
            )
            ->all();
    }
}
