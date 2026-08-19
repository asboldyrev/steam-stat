<?php

declare(strict_types=1);

namespace App\Dto\Activity;

use JsonSerializable;

final readonly class ActivityOverviewDto implements JsonSerializable
{
    /**
     * @param array{from:string,to:string,days:int} $period
     * @param array{total_minutes:int,active_days:int,games_count:int,average_minutes_per_active_day:int} $summary
     * @param array{total_minutes:int,active_days:int,games_count:int,average_minutes_per_active_day:int} $previous
     * @param list<array{date:string,minutes:int,games_count:int}> $daily
     * @param list<array{game_id:int,name:string,icon_url:?string,minutes:int}> $games
     * @param list<array{name:string,minutes:int,percentage:int}> $platforms
     */
    public function __construct(
        public array $period,
        public array $summary,
        public array $previous,
        public array $daily,
        public array $games,
        public array $platforms,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'period' => $this->period,
            'summary' => $this->summary,
            'previous' => $this->previous,
            'daily' => $this->daily,
            'games' => $this->games,
            'platforms' => $this->platforms,
        ];
    }
}
