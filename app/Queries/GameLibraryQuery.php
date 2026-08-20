<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Game;
use App\Models\PlaytimeSnapshot;
use Illuminate\Database\Eloquent\Builder;

final class GameLibraryQuery
{
    /**
     * @return Builder<Game>
     */
    public function build(?string $search, string $sort, string $platform): Builder
    {
        $query = Game::query()
            ->with('latestPlaytimeSnapshot')
            ->whereHas('latestPlaytimeSnapshot');

        if ($search !== null && $search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($platform !== 'all') {
            $query->whereHas('latestPlaytimeSnapshot', function (Builder $snapshot) use ($platform): void {
                match ($platform) {
                    'windows' => $snapshot->where('windows_minutes', '>', 0),
                    'deck' => $snapshot->where('deck_minutes', '>', 0),
                    'linux' => $snapshot->whereColumn('linux_minutes', '>', 'deck_minutes'),
                    'mac' => $snapshot->where('mac_minutes', '>', 0),
                    default => null,
                };
            });
        }

        $latestValue = static fn (string $column) => PlaytimeSnapshot::query()
            ->select($column)
            ->whereColumn('playtime_snapshots.game_id', 'games.id')
            ->orderByDesc('captured_at')
            ->limit(1);

        return match ($sort) {
            'name' => $query->orderBy('name'),
            'last_played' => $query->orderByDesc($latestValue('last_played_at'))->orderBy('name'),
            default => $query->orderByDesc($latestValue('total_minutes'))->orderBy('name'),
        };
    }
}
