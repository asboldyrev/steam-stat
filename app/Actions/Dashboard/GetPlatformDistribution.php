<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Dto\Dashboard\PlatformDistributionItemDto;
use App\Enums\GamePlatform;
use App\Models\SummaryStat;

final class GetPlatformDistribution
{
    /**
     * @return list<PlatformDistributionItemDto>
     */
    public function execute(): array
    {
        $stat = SummaryStat::query()->orderByDesc('date')->first();

        if ($stat === null) {
            return [];
        }

        $platforms = [
            GamePlatform::Windows->toArray((int) $stat->windows_minutes),
            GamePlatform::SteamDeck->toArray((int) $stat->deck_minutes),
            GamePlatform::Linux->toArray(max(0, (int) $stat->linux_minutes - (int) $stat->deck_minutes)),
            GamePlatform::MacOS->toArray((int) $stat->mac_minutes),
            GamePlatform::Offline->toArray((int) $stat->disconnected_minutes),
        ];

        $totalMinutes = array_sum(array_column($platforms, 'minutes'));

        $result = array_map(
            fn (array $platform): PlatformDistributionItemDto => new PlatformDistributionItemDto(
                name: $platform['name'],
                hours: (int) round($platform['minutes'] / 60),
                percentage: $totalMinutes > 0
                    ? (int) round(($platform['minutes'] / $totalMinutes) * 100)
                    : 0,
                color: $platform['color'],
            ),
            $platforms,
        );

        usort(
            $result,
            fn (PlatformDistributionItemDto $left, PlatformDistributionItemDto $right): int =>
                $right->percentage <=> $left->percentage,
        );

        return $result;
    }
}
