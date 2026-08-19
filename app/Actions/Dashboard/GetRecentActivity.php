<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Dto\Dashboard\RecentActivityDto;
use App\Models\GameStat;
use App\Support\Games\GameNameAbbreviator;
use App\Support\Games\GamePlatformDetector;
use Illuminate\Support\Facades\DB;

final class GetRecentActivity
{
    /**
     * @return list<RecentActivityDto>
     */
    public function execute(): array
    {
        $latestStats = GameStat::query()
            ->select('game_id', DB::raw('MAX(last_played_at) as last_played'))
            ->whereNotNull('last_played_at')
            ->groupBy('game_id')
            ->orderByDesc('last_played')
            ->limit(3)
            ->get();

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
                ->with('game')
                ->where('game_id', $stat->game_id)
                ->where('last_played_at', $stat->last_played)
                ->first();

            if ($gameStat === null || $gameStat->game === null || $gameStat->last_played_at === null) {
                continue;
            }

            $game = $gameStat->game;

            $activities[] = new RecentActivityDto(
                gameId: $game->id,
                gameName: $game->name,
                abbreviation: GameNameAbbreviator::generateAbbreviation($game->name),
                iconUrl: $game->iconUrlLarge(),
                durationMinutes: max(0, (int) $gameStat->delta_total_minutes),
                platform: GamePlatformDetector::determinePlatform($gameStat),
                lastPlayed: $gameStat->last_played_at->timestamp,
                gradient: $gradients[$index % count($gradients)],
            );
        }

        return $activities;
    }
}
