<?php

declare(strict_types=1);

namespace App\Dto\Dashboard;

use JsonSerializable;

final readonly class RecentActivityDto implements JsonSerializable
{
    public function __construct(
        public int $gameId,
        public string $gameName,
        public string $abbreviation,
        public ?string $iconUrl,
        public int $durationMinutes,
        public string $platform,
        public int $lastPlayed,
        public string $gradient,
    ) {}

    /**
     * @return array<string, int|string|null>
     */
    public function jsonSerialize(): array
    {
        return [
            'game_id' => $this->gameId,
            'game_name' => $this->gameName,
            'abbreviation' => $this->abbreviation,
            'icon_url' => $this->iconUrl,
            'duration_minutes' => $this->durationMinutes,
            'platform' => $this->platform,
            'last_played' => $this->lastPlayed,
            'gradient' => $this->gradient,
        ];
    }
}
