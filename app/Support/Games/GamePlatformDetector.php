<?php

namespace App\Support\Games;

use App\Models\GameStat;

class GamePlatformDetector
{
    /**
     * Определяет платформу с максимальным временем.
     */
    public static function determinePlatform(GameStat $stat): string
    {
        $platforms = [
            'Windows' => $stat->windows_minutes,
            'Steam Deck' => $stat->deck_minutes,
            'Linux' => $stat->linux_minutes - $stat->deck_minutes,
            'macOS' => $stat->mac_minutes,
            'Offline' => $stat->disconnected_minutes,
        ];

        arsort($platforms);
        return array_key_first($platforms) ?? 'Unknown';
    }
}
