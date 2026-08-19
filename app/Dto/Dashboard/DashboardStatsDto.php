<?php

declare(strict_types=1);

namespace App\Dto\Dashboard;

use JsonSerializable;

final readonly class DashboardStatsDto implements JsonSerializable
{
    public function __construct(
        public int $totalPlaytimeHours,
        public int $gamesCount,
        public int $steamDeckHours,
        public int $steamDeckPercentage,
    ) {}

    /**
     * @return array<string, int>
     */
    public function jsonSerialize(): array
    {
        return [
            'total_playtime_hours' => $this->totalPlaytimeHours,
            'games_count' => $this->gamesCount,
            'steam_deck_hours' => $this->steamDeckHours,
            'steam_deck_percentage' => $this->steamDeckPercentage,
        ];
    }
}
