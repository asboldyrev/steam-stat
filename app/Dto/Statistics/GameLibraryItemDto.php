<?php

declare(strict_types=1);

namespace App\Dto\Statistics;

use JsonSerializable;

final readonly class GameLibraryItemDto implements JsonSerializable
{
    /**
     * @param array<string,mixed> $artwork
     * @param array{windows:bool,deck:bool,linux:bool,mac:bool} $platforms
     */
    public function __construct(
        public int $id,
        public int $appId,
        public string $name,
        public string $abbreviation,
        public array $artwork,
        public int $totalMinutes,
        public ?string $lastPlayedAt,
        public array $platforms,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'app_id' => $this->appId,
            'name' => $this->name,
            'abbreviation' => $this->abbreviation,
            'artwork' => $this->artwork,
            'total_minutes' => $this->totalMinutes,
            'last_played_at' => $this->lastPlayedAt,
            'platforms' => $this->platforms,
        ];
    }
}
