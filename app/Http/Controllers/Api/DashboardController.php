<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameStat;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

final class DashboardController extends Controller
{
    /**
     * Возвращает общую статистику для карточек дашборда.
     */
    public function stats(): JsonResponse
    {
        // Получаем последние записи для каждой игры
        $latestStats = GameStat::query()
            ->select('game_id', DB::raw('MAX(date) as max_date'))
            ->groupBy('game_id')
            ->get()
            ->pluck('max_date', 'game_id');

        if ($latestStats->isEmpty()) {
            return response()->json([
                'total_playtime_hours' => 0,
                'games_count' => 0,
                'steam_deck_hours' => 0,
                'steam_deck_percentage' => 0,
            ]);
        }

        $gameIds = $latestStats->keys()->toArray();

        // Получаем сами записи
        $stats = GameStat::query()
            ->whereIn('game_id', $gameIds)
            ->where(function ($query) use ($latestStats) {
                foreach ($latestStats as $gameId => $date) {
                    $query->orWhere(function ($q) use ($gameId, $date) {
                        $q->where('game_id', $gameId)->where('date', $date);
                    });
                }
            })
            ->get();

        $totalMinutes = $stats->sum('total_minutes');
        $deckMinutes = $stats->sum('deck_minutes');

        // Игры с total_minutes > 0
        $gamesWithPlaytime = $stats->where('total_minutes', '>', 0)
            ->pluck('game_id')
            ->unique()
            ->count();

        $totalHours = (int) round($totalMinutes / 60);
        $deckHours = (int) round($deckMinutes / 60);
        $deckPercentage = $totalHours > 0 ? (int) round(($deckHours / $totalHours) * 100) : 0;

        return response()->json([
            'total_playtime_hours' => $totalHours,
            'games_count' => $gamesWithPlaytime,
            'steam_deck_hours' => $deckHours,
            'steam_deck_percentage' => $deckPercentage,
        ]);
    }

    /**
     * Возвращает распределение по платформам.
     */
    public function platformDistribution(): JsonResponse
    {
        // Получаем последние записи для каждой игры
        $latestStats = GameStat::query()
            ->select('game_id', DB::raw('MAX(date) as max_date'))
            ->groupBy('game_id')
            ->get()
            ->pluck('max_date', 'game_id');

        if ($latestStats->isEmpty()) {
            return response()->json(['platforms' => []]);
        }

        $stats = GameStat::query()
            ->whereIn('game_id', $latestStats->keys()->toArray())
            ->where(function ($query) use ($latestStats) {
                foreach ($latestStats as $gameId => $date) {
                    $query->orWhere(function ($q) use ($gameId, $date) {
                        $q->where('game_id', $gameId)->where('date', $date);
                    });
                }
            })
            ->get();

        $windowsMinutes = $stats->sum('windows_minutes');
        $linuxMinutes = $stats->sum('linux_minutes');
        $macMinutes = $stats->sum('mac_minutes');
        $deckMinutes = $stats->sum('deck_minutes');
        $disconnectedMinutes = $stats->sum('disconnected_minutes');

        $linuxDesktopMinutes = $linuxMinutes - $deckMinutes;

        $platforms = [
            [
                'name' => 'Windows',
                'minutes' => $windowsMinutes,
                'color' => 'bg-blue-500',
            ],
            [
                'name' => 'Steam Deck',
                'minutes' => $deckMinutes,
                'color' => 'bg-green-500',
            ],
            [
                'name' => 'Linux',
                'minutes' => $linuxDesktopMinutes,
                'color' => 'bg-purple-500',
            ],
            [
                'name' => 'macOS',
                'minutes' => $macMinutes,
                'color' => 'bg-orange-500',
            ],
            [
                'name' => 'Offline',
                'minutes' => $disconnectedMinutes,
                'color' => 'bg-gray-500',
            ],
        ];

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

    /**
     * Возвращает последние 3 игры по last_played_at.
     */
    public function recentActivity(): JsonResponse
    {
        // Получаем последние записи по last_played_at, группируем по game_id
        $latestStats = GameStat::query()
            ->select('game_id', DB::raw('MAX(last_played_at) as last_played'))
            ->whereNotNull('last_played_at')
            ->groupBy('game_id')
            ->orderByDesc('last_played')
            ->limit(3)
            ->get();

        if ($latestStats->isEmpty()) {
            return response()->json(['activities' => []]);
        }

        $activities = [];
        $gradients = [
            'from-blue-500 to-cyan-400',
            'from-green-500 to-emerald-400',
            'from-purple-500 to-pink-400',
            'from-yellow-500 to-orange-400',
            'from-red-500 to-rose-400',
            'from-indigo-500 to-violet-400',
        ];

        foreach ($latestStats as $index => $stat) {
            $gameStat = GameStat::query()
                ->where('game_id', $stat->game_id)
                ->where('last_played_at', $stat->last_played)
                ->first();

            if (!$gameStat) {
                continue;
            }

            $game = $gameStat->game;
            $abbreviation = $this->generateAbbreviation($game->name);
            $durationHours = (int) round($gameStat->total_minutes / 60);
            $platform = $this->determinePlatform($gameStat);
            $timeAgo = $this->formatTimeAgo($gameStat->last_played_at);
            $gradient = $gradients[$index % count($gradients)];

            $activities[] = [
                'game_id' => $game->id,
                'game_name' => $game->name,
                'abbreviation' => $abbreviation,
                'icon_url' => $game->iconUrlLarge(),
                'duration_hours' => $durationHours,
                'platform' => $platform,
                'time_ago' => $timeAgo,
                'gradient' => $gradient,
            ];
        }

        return response()->json(['activities' => $activities]);
    }

    /**
     * Возвращает топ-3 игры по общему времени.
     */
    public function topGames(): JsonResponse
    {
        // Получаем последние записи для каждой игры
        $latestStats = GameStat::query()
            ->select('game_id', DB::raw('MAX(date) as max_date'))
            ->groupBy('game_id')
            ->get()
            ->pluck('max_date', 'game_id');

        if ($latestStats->isEmpty()) {
            return response()->json(['games' => []]);
        }

        $stats = GameStat::query()
            ->whereIn('game_id', $latestStats->keys()->toArray())
            ->where(function ($query) use ($latestStats) {
                foreach ($latestStats as $gameId => $date) {
                    $query->orWhere(function ($q) use ($gameId, $date) {
                        $q->where('game_id', $gameId)->where('date', $date);
                    });
                }
            })
            ->orderByDesc('total_minutes')
            ->limit(3)
            ->get();

        $gradients = [
            'from-blue-600 to-blue-400',
            'from-green-600 to-emerald-400',
            'from-purple-600 to-pink-400',
            'from-yellow-600 to-orange-400',
            'from-red-600 to-rose-400',
            'from-indigo-600 to-violet-400',
        ];

        $games = [];
        foreach ($stats as $index => $stat) {
            $game = $stat->game;
            $totalHours = (int) round($stat->total_minutes / 60);
            $timeAgo = $this->formatTimeAgo($stat->last_played_at);
            $gradient = $gradients[$index % count($gradients)];

            $games[] = [
                'game_id' => $game->id,
                'game_name' => $game->name,
                'icon_url' => $game->iconUrlLarge(),
                'total_time' => $totalHours . 'h',
                'last_played' => $timeAgo,
                'gradient' => $gradient,
            ];
        }

        return response()->json(['games' => $games]);
    }

    /**
     * Генерирует аббревиатуру из названия игры.
     */
    private function generateAbbreviation(string $name): string
    {
        $words = preg_split('/\s+/', trim($name));
        if (count($words) === 1) {
            return strtoupper(substr($words[0], 0, 3));
        }

        $abbr = '';
        foreach ($words as $word) {
            if (preg_match('/[A-Za-z]/', $word[0] ?? '')) {
                $abbr .= strtoupper($word[0]);
            }
            if (strlen($abbr) >= 3) {
                break;
            }
        }

        return strlen($abbr) > 0 ? $abbr : '???';
    }

    /**
     * Определяет платформу с максимальным временем.
     */
    private function determinePlatform(GameStat $stat): string
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

    /**
     * Форматирует дату в относительное время.
     */
    private function formatTimeAgo(Carbon $date): string
    {
        if ($date->isToday()) {
            return 'Today';
        }

        if ($date->isYesterday()) {
            return 'Yesterday';
        }

        $daysDiff = $date->diffInDays(Carbon::now());
        return $daysDiff . ' days ago';
    }
}
