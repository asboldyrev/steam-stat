<?php

declare(strict_types=1);

namespace App\Queries\GameStats;

use App\Models\GameStat;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

final class GetLatestGameStats
{
    /**
     * @return Collection<int, GameStat>
     */
    public function execute(int|null $limit = null): Collection
    {
        $query = GameStat::query()
            ->joinSub(
                GameStat::query()
                    ->select([
                        'game_id',
                        DB::raw('MAX(date) as max_date'),
                    ])
                    ->groupBy('game_id'),
                'latest_stats',
                function ($join): void {
                    $join
                        ->on('game_stats.game_id', '=', 'latest_stats.game_id')
                        ->on('game_stats.date', '=', 'latest_stats.max_date');
                },
            )
            ->select('game_stats.*')
            ->with('game');

        if ($limit) {
            $query = $query->limit($limit);
        }

        return $query->get();
    }
}
