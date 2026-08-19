<?php

declare(strict_types=1);

namespace App\Actions\Activity;

use App\Dto\Activity\ActivityOverviewDto;
use App\Models\PlaytimeSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final class GetActivityOverview
{
    public function execute(?string $from = null, ?string $to = null, ?int $gameId = null): ActivityOverviewDto
    {
        $timezone = (string) config('steam-stat.timezone', config('app.timezone', 'UTC'));
        $today = CarbonImmutable::now($timezone)->startOfDay();
        $toDate = $to !== null ? CarbonImmutable::parse($to, $timezone)->startOfDay() : $today;
        $fromDate = $from !== null
            ? CarbonImmutable::parse($from, $timezone)->startOfDay()
            : $toDate->subDays(29);

        $days = $fromDate->diffInDays($toDate) + 1;
        $previousTo = $fromDate->subDay();
        $previousFrom = $previousTo->subDays($days - 1);

        $currentSnapshots = $this->snapshotsForPeriod($fromDate, $toDate, $timezone, $gameId);
        $previousSnapshots = $this->snapshotsForPeriod($previousFrom, $previousTo, $timezone, $gameId);

        $daily = $this->daily($currentSnapshots, $fromDate, $toDate, $timezone);

        return new ActivityOverviewDto(
            period: [
                'from' => $fromDate->toDateString(),
                'to' => $toDate->toDateString(),
                'days' => $days,
            ],
            summary: $this->summary($currentSnapshots, $timezone),
            previous: $this->summary($previousSnapshots, $timezone),
            daily: $daily,
            games: $this->games($currentSnapshots),
            platforms: $this->platforms($currentSnapshots),
        );
    }

    /**
     * @return Collection<int, PlaytimeSnapshot>
     */
    private function snapshotsForPeriod(
        CarbonImmutable $from,
        CarbonImmutable $to,
        string $timezone,
        ?int $gameId,
    ): Collection {
        $fromUtc = $from->utc();
        $toUtcExclusive = $to->addDay()->utc();

        return PlaytimeSnapshot::query()
            ->with('game:id,name,icon_url,app_id')
            ->where('captured_at', '>=', $fromUtc->format('Y-m-d H:i:s'))
            ->where('captured_at', '<', $toUtcExclusive->format('Y-m-d H:i:s'))
            ->when($gameId !== null, fn ($query) => $query->where('game_id', $gameId))
            ->orderBy('captured_at')
            ->get();
    }

    /**
     * @param Collection<int, PlaytimeSnapshot> $snapshots
     * @return array{total_minutes:int,active_days:int,games_count:int,average_minutes_per_active_day:int}
     */
    private function summary(Collection $snapshots, string $timezone): array
    {
        $activity = $snapshots->filter(fn (PlaytimeSnapshot $snapshot): bool => $snapshot->activityMinutes() > 0);
        $totalMinutes = (int) $activity->sum(fn (PlaytimeSnapshot $snapshot): int => $snapshot->activityMinutes());
        $activeDays = $activity
            ->map(fn (PlaytimeSnapshot $snapshot): string => $snapshot->captured_at->setTimezone($timezone)->toDateString())
            ->unique()
            ->count();

        return [
            'total_minutes' => $totalMinutes,
            'active_days' => $activeDays,
            'games_count' => $activity->pluck('game_id')->unique()->count(),
            'average_minutes_per_active_day' => $activeDays > 0 ? (int) round($totalMinutes / $activeDays) : 0,
        ];
    }

    /**
     * @param Collection<int, PlaytimeSnapshot> $snapshots
     * @return list<array{date:string,minutes:int,games_count:int}>
     */
    private function daily(
        Collection $snapshots,
        CarbonImmutable $from,
        CarbonImmutable $to,
        string $timezone,
    ): array {
        $byDate = $snapshots
            ->filter(fn (PlaytimeSnapshot $snapshot): bool => $snapshot->activityMinutes() > 0)
            ->groupBy(fn (PlaytimeSnapshot $snapshot): string => $snapshot->captured_at->setTimezone($timezone)->toDateString());

        $result = [];
        for ($date = $from; $date->lte($to); $date = $date->addDay()) {
            $key = $date->toDateString();
            /** @var Collection<int, PlaytimeSnapshot> $items */
            $items = $byDate->get($key, collect());

            $result[] = [
                'date' => $key,
                'minutes' => (int) $items->sum(fn (PlaytimeSnapshot $snapshot): int => $snapshot->activityMinutes()),
                'games_count' => $items->pluck('game_id')->unique()->count(),
            ];
        }

        return $result;
    }

    /**
     * @param Collection<int, PlaytimeSnapshot> $snapshots
     * @return list<array{game_id:int,name:string,icon_url:?string,minutes:int}>
     */
    private function games(Collection $snapshots): array
    {
        return $snapshots
            ->filter(fn (PlaytimeSnapshot $snapshot): bool => $snapshot->activityMinutes() > 0)
            ->groupBy('game_id')
            ->map(function (Collection $items): array {
                /** @var PlaytimeSnapshot $snapshot */
                $snapshot = $items->first();

                return [
                    'game_id' => (int) $snapshot->game_id,
                    'name' => $snapshot->game->name,
                    'icon_url' => $snapshot->game->iconUrlLarge(),
                    'minutes' => (int) $items->sum(fn (PlaytimeSnapshot $item): int => $item->activityMinutes()),
                ];
            })
            ->sortByDesc('minutes')
            ->values()
            ->all();
    }

    /**
     * @param Collection<int, PlaytimeSnapshot> $snapshots
     * @return list<array{name:string,minutes:int,percentage:int}>
     */
    private function platforms(Collection $snapshots): array
    {
        $minutes = [
            'Windows' => 0,
            'Steam Deck' => 0,
            'Linux' => 0,
            'macOS' => 0,
            'Unclassified' => 0,
        ];

        foreach ($snapshots as $snapshot) {
            $total = max(0, (int) $snapshot->delta_total_minutes);
            if ($total === 0) {
                continue;
            }

            $windows = max(0, (int) $snapshot->delta_windows_minutes);
            $linux = max(0, (int) $snapshot->delta_linux_minutes);
            $deck = max(0, (int) $snapshot->delta_deck_minutes);
            $mac = max(0, (int) $snapshot->delta_mac_minutes);
            $linuxDesktop = max(0, $linux - $deck);
            $classified = $windows + $linux + $mac;

            $minutes['Windows'] += $windows;
            $minutes['Steam Deck'] += $deck;
            $minutes['Linux'] += $linuxDesktop;
            $minutes['macOS'] += $mac;
            $minutes['Unclassified'] += max(0, $total - $classified);
        }

        $total = array_sum($minutes);

        return collect($minutes)
            ->filter(fn (int $value): bool => $value > 0)
            ->map(fn (int $value, string $name): array => [
                'name' => $name,
                'minutes' => $value,
                'percentage' => $total > 0 ? (int) round(($value / $total) * 100) : 0,
            ])
            ->sortByDesc('minutes')
            ->values()
            ->all();
    }
}
