<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Queries\GameStats\GetLatestGameStats;
use Illuminate\Http\JsonResponse;

final class GetTopGames
{
    public function __construct(
        private readonly GetLatestGameStats $latestGameStats,
    ) {}

    public function execute(): JsonResponse
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

        $games = [];
        foreach ($stats as $index => $stat) {
            $game = $stat->game;
            $totalHours = (int) round($stat->total_minutes / 60);
            $gradient = $gradients[$index % count($gradients)];

            $games[] = [
                'game_id' => $game->id,
                'game_name' => $game->name,
                'icon_url' => $game->iconUrlLarge(),
                'total_time' => $totalHours,
                'last_played' => $stat->last_played_at,
                'gradient' => $gradient,
            ];
        }

        return response()->json(['games' => $games]);
    }
}
