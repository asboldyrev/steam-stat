<?php

declare(strict_types=1);

namespace App\Dto\Activity;

use JsonSerializable;

final readonly class ActivityInsightsDto implements JsonSerializable
{
    /**
     * @param list<array{date:string,minutes:int}> $heatmap
     * @param list<array{weekday:int,name:string,minutes:int}> $weekdays
     * @param list<array{hour:int,minutes:int}> $hours
     * @param array{
     *     best_day:?array{date:string,minutes:int},
     *     best_game:?array{game_id:int,name:string,minutes:int},
     *     longest_streak_days:int,
     *     current_streak_days:int
     * } $records
     */
    public function __construct(
        public array $heatmap,
        public array $weekdays,
        public array $hours,
        public array $records,
        public bool $hourlyApproximate,
    ) {}

    public function jsonSerialize(): array
    {
        return [
            'heatmap' => $this->heatmap,
            'weekdays' => $this->weekdays,
            'hours' => $this->hours,
            'records' => $this->records,
            'hourly_approximate' => $this->hourlyApproximate,
        ];
    }
}
