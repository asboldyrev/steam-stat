<?php

declare(strict_types=1);

namespace App\Actions\Games;

use App\Actions\Activity\GetActivityOverview;
use App\Dto\Statistics\GameDetailDto;
use App\Models\Game;
use App\Models\PlaytimeSnapshot;

final class GetGameDetail
{
    public function __construct(private readonly GetActivityOverview $activityOverview) {}

    public function execute(Game $game, ?string $from = null, ?string $to = null): GameDetailDto
    {
        /** @var PlaytimeSnapshot|null $latest */
        $latest = $game->latestPlaytimeSnapshot()->first();
        $activity = $this->activityOverview->execute($from, $to, $game->id);

        $windows = max(0, (int) ($latest?->windows_minutes ?? 0));
        $linux = max(0, (int) ($latest?->linux_minutes ?? 0));
        $deck = max(0, (int) ($latest?->deck_minutes ?? 0));
        $mac = max(0, (int) ($latest?->mac_minutes ?? 0));
        $total = max(0, (int) ($latest?->total_minutes ?? 0));
        $linuxDesktop = max(0, $linux - $deck);
        $classified = $windows + $linux + $mac;

        return new GameDetailDto(
            id: (int) $game->id,
            appId: (int) $game->app_id,
            name: $game->name,
            iconUrl: $game->iconUrlLarge(),
            coverUrl: $game->coverUrl(),
            storeUrl: $game->storeUrl(),
            lastPlayedAt: $latest?->last_played_at?->toIso8601String(),
            lifetime: [
                'total_minutes' => $total,
                'windows_minutes' => $windows,
                'deck_minutes' => $deck,
                'linux_minutes' => $linuxDesktop,
                'mac_minutes' => $mac,
                'unclassified_minutes' => max(0, $total - $classified),
                'disconnected_minutes' => max(0, (int) ($latest?->disconnected_minutes ?? 0)),
            ],
            period: $activity->period,
            summary: [
                'total_minutes' => $activity->summary['total_minutes'],
                'active_days' => $activity->summary['active_days'],
                'average_minutes_per_active_day' => $activity->summary['average_minutes_per_active_day'],
            ],
            daily: $activity->daily,
            platforms: $activity->platforms,
        );
    }
}
