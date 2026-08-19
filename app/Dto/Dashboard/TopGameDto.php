<?php

declare(strict_types=1);

namespace App\Dto\Dashboard;

use Carbon\CarbonInterface;
use JsonSerializable;

final readonly class TopGameDto implements JsonSerializable
{
    public function __construct(
        public int $gameId,
        public string $gameName,
        public ?string $iconUrl,
        public int $totalTime,
        public ?CarbonInterface $lastPlayed,
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
            'icon_url' => $this->iconUrl,
            'total_time' => $this->totalTime,
            'last_played' => $this->lastPlayed?->toIso8601String(),
            'gradient' => $this->gradient,
        ];
    }
}
