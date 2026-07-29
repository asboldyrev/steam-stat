<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Enums\GamePlatform;
use App\Queries\GameStats\GetLatestGameStats;
use Illuminate\Http\JsonResponse;

final class GetPlatformDistribution
{
    public function __construct(
        private readonly GetLatestGameStats $latestGameStats,
    ) {}

    /**
     * Возвращает распределение по платформам.
     */
    public function execute(): JsonResponse
    {
        $stats = $this->latestGameStats->execute();

        $windowsMinutes = $stats->sum('windows_minutes');
        $linuxMinutes = $stats->sum('linux_minutes');
        $macMinutes = $stats->sum('mac_minutes');
        $deckMinutes = $stats->sum('deck_minutes');
        $disconnectedMinutes = $stats->sum('disconnected_minutes');

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

        return response()->json(['platforms' => $result]);
    }
}
