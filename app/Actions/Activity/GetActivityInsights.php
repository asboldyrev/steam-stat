<?php

declare(strict_types=1);

namespace App\Actions\Activity;

use App\Dto\Activity\ActivityInsightsDto;
use App\Models\PlaytimeSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class GetActivityInsights
{
    public function execute(?string $from = null, ?string $to = null, ?int $gameId = null): ActivityInsightsDto
    {
        $timezone = (string) config('steam-stat.timezone', config('app.timezone', 'UTC'));
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $toDate = $to !== null ? CarbonImmutable::parse($to, $timezone)->startOfDay() : $today;
        $fromDate = $from !== null ? CarbonImmutable::parse($from, $timezone)->startOfDay() : $toDate->subDays(364);

        $snapshots = $this->snapshotsForPeriod($fromDate, $toDate, $gameId);
        $activity = $snapshots->filter(fn (PlaytimeSnapshot $snapshot): bool => $snapshot->activityMinutes() > 0);

        $daily = $activity
            ->groupBy(fn (PlaytimeSnapshot $snapshot): string => $snapshot->captured_at->setTimezone($timezone)->toDateString())
            ->map(fn (Collection $items): int => (int) $items->sum(fn (PlaytimeSnapshot $snapshot): int => $snapshot->activityMinutes()));

        return new ActivityInsightsDto(
            heatmap: $this->heatmap($daily, $fromDate, $toDate),
            weekdays: $this->weekdays($daily, $timezone),
            hours: $this->hours($activity, $timezone),
            records: $this->records($activity, $daily, $toDate, $timezone),
            hourlyApproximate: true,
        );
    }

    /** @return Collection<int, PlaytimeSnapshot> */
    private function snapshotsForPeriod(CarbonImmutable $from, CarbonImmutable $to, ?int $gameId): Collection
    {
        return PlaytimeSnapshot::query()
            ->with('game:id,name')
            ->where('captured_at', '>=', $from->utc()->format('Y-m-d H:i:s'))
            ->where('captured_at', '<', $to->addDay()->utc()->format('Y-m-d H:i:s'))
            ->when($gameId !== null, fn ($query) => $query->where('game_id', $gameId))
            ->orderBy('captured_at')
            ->get();
    }

    /**
     * @param Collection<string, int> $daily
     * @return list<array{date:string,minutes:int}>
     */
    private function heatmap(Collection $daily, CarbonImmutable $from, CarbonImmutable $to): array
    {
        $result = [];

        for ($date = $from; $date->lte($to); $date = $date->addDay()) {
            $key = $date->toDateString();
            $result[] = [
                'date' => $key,
                'minutes' => (int) $daily->get($key, 0),
            ];
        }

        return $result;
    }

    /**
     * @param Collection<string, int> $daily
     * @return list<array{weekday:int,name:string,minutes:int}>
     */
    private function weekdays(Collection $daily, string $timezone): array
    {
        $names = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $minutes = array_fill(0, 7, 0);

        foreach ($daily as $date => $value) {
            $weekday = CarbonImmutable::parse($date, $timezone)->dayOfWeekIso - 1;
            $minutes[$weekday] += (int) $value;
        }

        return collect($minutes)
            ->map(fn (int $value, int $weekday): array => [
                'weekday' => $weekday + 1,
                'name' => $names[$weekday],
                'minutes' => $value,
            ])
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, PlaytimeSnapshot> $activity
     * @return list<array{hour:int,minutes:int}>
     */
    private function hours(Collection $activity, string $timezone): array
    {
        $minutes = array_fill(0, 24, 0);

        foreach ($activity as $snapshot) {
            $hour = $snapshot->captured_at->setTimezone($timezone)->hour;
            $minutes[$hour] += $snapshot->activityMinutes();
        }

        return collect($minutes)
            ->map(fn (int $value, int $hour): array => [
                'hour' => $hour,
                'minutes' => $value,
            ])
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, PlaytimeSnapshot> $activity
     * @param Collection<string, int> $daily
     * @return array{
     *     best_day:?array{date:string,minutes:int},
     *     best_game:?array{game_id:int,name:string,minutes:int},
     *     longest_streak_days:int,
     *     current_streak_days:int
     * }
     */
    private function records(
        Collection $activity,
        Collection $daily,
        CarbonImmutable $toDate,
        string $timezone,
    ): array {
        $bestDayDate = $daily->sortDesc()->keys()->first();
        $bestDay = $bestDayDate !== null
            ? ['date' => $bestDayDate, 'minutes' => (int) $daily->get($bestDayDate)]
            : null;

        $games = $activity
            ->groupBy('game_id')
            ->map(function (Collection $items): array {
                /** @var PlaytimeSnapshot $snapshot */
                $snapshot = $items->first();

                return [
                    'game_id' => (int) $snapshot->game_id,
                    'name' => $snapshot->game->name,
                    'minutes' => (int) $items->sum(fn (PlaytimeSnapshot $item): int => $item->activityMinutes()),
                ];
            })
            ->sortByDesc('minutes')
            ->values();

        $activeDates = $daily
            ->filter(fn (int $minutes): bool => $minutes > 0)
            ->keys()
            ->map(fn (string $date): CarbonImmutable => CarbonImmutable::parse($date, $timezone)->startOfDay())
            ->sort()
            ->values();

        $longest = 0;
        $currentRun = 0;
        $previous = null;

        foreach ($activeDates as $date) {
            if ($previous !== null && $previous->addDay()->equalTo($date)) {
                $currentRun++;
            } else {
                $currentRun = 1;
            }

            $longest = max($longest, $currentRun);
            $previous = $date;
        }

        $current = 0;
        if ($activeDates->isNotEmpty()) {
            $cursor = $toDate;
            $activeDateStrings = $activeDates->map(fn (CarbonImmutable $date): string => $date->toDateString())->flip();

            while ($activeDateStrings->has($cursor->toDateString())) {
                $current++;
                $cursor = $cursor->subDay();
            }
        }

        return [
            'best_day' => $bestDay,
            'best_game' => $games->first(),
            'longest_streak_days' => $longest,
            'current_streak_days' => $current,
        ];
    }
}
