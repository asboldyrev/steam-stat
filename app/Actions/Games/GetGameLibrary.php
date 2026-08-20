<?php

declare(strict_types=1);

namespace App\Actions\Games;

use App\Dto\Statistics\GameLibraryDto;
use App\Dto\Statistics\GameLibraryItemDto;
use App\Models\Game;
use App\Models\PlaytimeSnapshot;
use App\Queries\GameLibraryQuery;

final class GetGameLibrary
{
    public function __construct(private readonly GameLibraryQuery $query) {}

    public function execute(?string $search = null, string $sort = 'playtime', string $platform = 'all'): GameLibraryDto
    {
        $games = $this->query
            ->build($search, $sort, $platform)
            ->get()
            ->map(function (Game $game): GameLibraryItemDto {
                /** @var PlaytimeSnapshot $snapshot */
                $snapshot = $game->latestPlaytimeSnapshot;
                $linuxDesktopMinutes = max(0, (int) $snapshot->linux_minutes - (int) $snapshot->deck_minutes);

                return new GameLibraryItemDto(
                    id: (int) $game->id,
                    appId: (int) $game->app_id,
                    name: $game->name,
                    abbreviation: $this->abbreviation($game->name),
                    artwork: is_array($game->artwork) ? $game->artwork : [],
                    totalMinutes: max(0, (int) $snapshot->total_minutes),
                    lastPlayedAt: $snapshot->last_played_at?->toIso8601String(),
                    platforms: [
                        'windows' => (int) $snapshot->windows_minutes > 0,
                        'deck' => (int) $snapshot->deck_minutes > 0,
                        'linux' => $linuxDesktopMinutes > 0,
                        'mac' => (int) $snapshot->mac_minutes > 0,
                    ],
                );
            })
            ->values()
            ->all();

        return new GameLibraryDto($games);
    }

    private function abbreviation(string $name): string
    {
        $words = preg_split('/\s+/u', trim($name)) ?: [];
        if (count($words) === 1) {
            return mb_strtoupper(mb_substr($words[0] ?? '', 0, 3, 'UTF-8'), 'UTF-8');
        }

        $abbreviation = '';
        foreach ($words as $word) {
            $first = mb_substr($word, 0, 1, 'UTF-8');
            if ($first !== '' && preg_match('/[\p{L}\p{N}]/u', $first) === 1) {
                $abbreviation .= mb_strtoupper($first, 'UTF-8');
            }

            if (mb_strlen($abbreviation, 'UTF-8') >= 3) {
                break;
            }
        }

        return $abbreviation !== '' ? $abbreviation : '???';
    }
}
