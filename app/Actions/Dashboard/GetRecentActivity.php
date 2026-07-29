<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Models\GameStat;
use App\Support\Games\GameNameAbbreviator;
use App\Support\Games\GamePlatformDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class GetRecentActivity
{
    public function execute(): JsonResponse
    {
        // Получаем последние записи по last_played_at, группируем по game_id
        $latestStats = GameStat::query()
            ->select('game_id', DB::raw('MAX(last_played_at) as last_played'))
            ->whereNotNull('last_played_at')
            ->groupBy('game_id')
            ->orderByDesc('last_played')
            ->limit(3)
            ->get();

        if ($latestStats->isEmpty()) {
            return response()->json(['activities' => []]);
        }

        $activities = [];
        $gradients = [
            'from-blue-500 to-cyan-400',
            'from-green-500 to-emerald-400',
            'from-purple-500 to-pink-400',
            'from-yellow-500 to-orange-400',
            'from-red-500 to-rose-400',
            'from-indigo-500 to-violet-400',
        ];

        foreach ($latestStats as $index => $stat) {
            $gameStat = GameStat::query()
                ->where('game_id', $stat->game_id)
                ->where('last_played_at', $stat->last_played)
                ->first();

            if (!$gameStat) {
                continue;
            }

            $game = $gameStat->game;
            $abbreviation = GameNameAbbreviator::generateAbbreviation($game->name);
            $durationHours = (int) round($gameStat->total_minutes / 60);
            $platform = GamePlatformDetector::determinePlatform($gameStat);
            $gradient = $gradients[$index % count($gradients)];

            $activities[] = [
                'game_id' => $game->id,
                'game_name' => $game->name,
                'abbreviation' => $abbreviation,
                'icon_url' => $game->iconUrlLarge(),
                'duration_hours' => $durationHours,
                'platform' => $platform,
                'last_played' => $gameStat->last_played_at->timestamp,
                'gradient' => $gradient,
            ];
        }

        return response()->json(['activities' => $activities]);
    }
}
