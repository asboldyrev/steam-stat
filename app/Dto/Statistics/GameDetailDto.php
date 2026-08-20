<?php

declare(strict_types=1);

namespace App\Dto\Statistics;

use JsonSerializable;

final readonly class GameDetailDto implements JsonSerializable
{
    /**
     * @param array<string,mixed> $artwork
     * @param array{total_minutes:int,windows_minutes:int,deck_minutes:int,linux_minutes:int,mac_minutes:int,unclassified_minutes:int,disconnected_minutes:int} $lifetime
     * @param array{from:string,to:string,days:int} $period
     * @param array{total_minutes:int,active_days:int,average_minutes_per_active_day:int} $summary
     * @param list<array{date:string,minutes:int,games_count:int}> $daily
     * @param list<array{name:string,minutes:int,percentage:int}> $platforms
     */
    public function __construct(
        public int $id,
        public int $appId,
        public string $name,
        public array $artwork,
        public ?string $iconUrl,
        public ?string $coverUrl,
        public string $storeUrl,
        public ?string $lastPlayedAt,
        public array $lifetime,
        public array $period,
        public array $summary,
        public array $daily,
        public array $platforms,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'app_id' => $this->appId,
            'name' => $this->name,
            'artwork' => $this->artwork,
            'icon_url' => $this->iconUrl,
            'cover_url' => $this->coverUrl,
            'store_url' => $this->storeUrl,
            'last_played_at' => $this->lastPlayedAt,
            'lifetime' => $this->lifetime,
            'period' => $this->period,
            'summary' => $this->summary,
            'daily' => $this->daily,
            'platforms' => $this->platforms,
        ];
    }
}
