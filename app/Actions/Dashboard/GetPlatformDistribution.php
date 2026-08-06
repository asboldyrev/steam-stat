<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Enums\GamePlatform;
use App\Models\SummaryStat;
use App\Queries\GameStats\GetLatestGameStats;

final class GetPlatformDistribution
{
    public function __construct() {}

    /**
     * Возвращает распределение по платформам.
     */
    public function execute(): array
    {
        $stat = SummaryStat::orderByDesc('date')->first();

        $windowsMinutes = $stat->windows_minutes;
        $linuxMinutes = $stat->linux_minutes;
        $macMinutes = $stat->mac_minutes;
        $deckMinutes = $stat->deck_minutes;
        $disconnectedMinutes = $stat->disconnected_minutes;

        $linuxDesktopMinutes = $linuxMinutes - $deckMinutes;

        $platforms = $platforms = [
            GamePlatform::Windows->toArray($windowsMinutes),
            GamePlatform::SteamDeck->toArray($deckMinutes),
            GamePlatform::Linux->toArray($linuxDesktopMinutes),
            GamePlatform::MacOS->toArray($macMinutes),
            GamePlatform::Offline->toArray($disconnectedMinutes),
        ];;

        $totalMinutes = array_sum(array_column($platforms, 'minutes'));

        $result = [];
        foreach ($platforms as $platform) {
            $hours = (int) round($platform['minutes'] / 60);
            $percentage = $totalMinutes > 0 ? (int) round(($platform['minutes'] / $totalMinutes) * 100) : 0;
            $result[] = [
                'name' => $platform['name'],
                'hours' => $hours,
                'percentage' => $percentage,
                'color' => $platform['color'],
            ];
        }

        // Сортировка по убыванию percentage
        usort($result, fn($a, $b) => $b['percentage'] <=> $a['percentage']);

        return $result;
    }
}
