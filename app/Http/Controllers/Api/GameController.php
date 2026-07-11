<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameStat;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class GameController extends Controller
{
    /**
     * Возвращает список игр с фильтрацией.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $sort = $request->query('sort', 'playtime');
        $platform = $request->query('platform', 'all');

        // Получаем последние записи для каждой игры
        $latestStats = GameStat::query()
            ->select('game_id', DB::raw('MAX(date) as max_date'))
            ->groupBy('game_id')
            ->get()
            ->pluck('max_date', 'game_id');

        if ($latestStats->isEmpty()) {
            return response()->json(['games' => []]);
        }

        $gameIds = $latestStats->keys()->toArray();

        $query = GameStat::query()
            ->with('game')
            ->whereIn('game_id', $gameIds)
            ->where(function ($query) use ($latestStats) {
                foreach ($latestStats as $gameId => $date) {
                    $query->orWhere(function ($q) use ($gameId, $date) {
                        $q->where('game_id', $gameId)->where('date', $date);
                    });
                }
            });

        // Фильтр по платформе
        if ($platform !== 'all') {
            $query->where(function ($q) use ($platform) {
                match ($platform) {
                    'windows' => $q->where('windows_minutes', '>', 0),
                    'deck' => $q->where('deck_minutes', '>', 0),
                    'linux' => $q->where('linux_minutes', '>', 0),
                    'mac' => $q->where('mac_minutes', '>', 0),
                    default => null,
                };
            });
        }

        // Поиск по названию игры
        if ($search) {
            $query->whereHas('game', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        // Сортировка
        match ($sort) {
            'name' => $query->orderBy(
                Game::select('name')
                    ->whereColumn('games.id', 'game_stats.game_id')
                    ->limit(1),
                'asc'
            ),
            'lastPlayed' => $query->orderByDesc('last_played_at'),
            default => $query->orderByDesc('total_minutes'), // playtime
        };

        $stats = $query->get();

        $gradients = [
            'bg-gradient-to-br from-blue-600 to-cyan-500',
            'bg-gradient-to-br from-green-600 to-emerald-500',
            'bg-gradient-to-br from-purple-600 to-pink-500',
            'bg-gradient-to-br from-yellow-600 to-orange-500',
            'bg-gradient-to-br from-red-600 to-rose-500',
            'bg-gradient-to-br from-indigo-600 to-violet-500',
        ];

        $games = [];
        foreach ($stats as $index => $stat) {
            $game = $stat->game;
            $abbreviation = $this->generateAbbreviation($game->name);
            $totalHours = (int) round($stat->total_minutes / 60);
            $timeAgo = $this->formatTimeAgo($stat->last_played_at);
            $gradient = $gradients[$index % count($gradients)];

            $games[] = [
                'id' => $game->id,
                'name' => $game->name,
                'abbreviation' => $abbreviation,
                'gradient' => $gradient,
                'total_time' => $totalHours . 'h',
                'last_played' => $timeAgo,
                'platforms' => [
                    'windows' => $stat->windows_minutes > 0,
                    'deck' => $stat->deck_minutes > 0,
                    'linux' => $stat->linux_minutes > 0,
                    'mac' => $stat->mac_minutes > 0,
                ],
            ];
        }

        return response()->json(['games' => $games]);
    }

    /**
     * Возвращает детали игры.
     */
    public function show(Game $game): JsonResponse
    {
        $latestStat = $game->gameStats()->latest('date')->first();

        $totalPlaytimeHours = $latestStat ? (int) round($latestStat->total_minutes / 60) : 0;
        $lastPlayed = $latestStat ? $this->formatTimeAgo($latestStat->last_played_at) : 'Never';

        return response()->json([
            'id' => $game->id,
            'name' => $game->name,
            'abbreviation' => $this->generateAbbreviation($game->name),
            'total_playtime_hours' => $totalPlaytimeHours,
            'last_played' => $lastPlayed,
        ]);
    }

    /**
     * Возвращает распределение по платформам для конкретной игры.
     */
    public function platformBreakdown(Game $game): JsonResponse
    {
        $latestStat = $game->gameStats()->latest('date')->first();

        if (!$latestStat) {
            return response()->json(['platforms' => []]);
        }

        $windowsMinutes = $latestStat->windows_minutes;
        $linuxMinutes = $latestStat->linux_minutes;
        $macMinutes = $latestStat->mac_minutes;
        $deckMinutes = $latestStat->deck_minutes;
        $disconnectedMinutes = $latestStat->disconnected_minutes;

        $linuxDesktopMinutes = $linuxMinutes - $deckMinutes;

        $platforms = [];
        if ($windowsMinutes > 0) {
            $platforms[] = [
                'name' => 'Windows',
                'hours' => (int) round($windowsMinutes / 60),
                'percentage' => 0,
                'color' => 'bg-blue-500',
            ];
        }
        if ($deckMinutes > 0) {
            $platforms[] = [
                'name' => 'Steam Deck',
                'hours' => (int) round($deckMinutes / 60),
                'percentage' => 0,
                'color' => 'bg-green-500',
            ];
        }
        if ($linuxDesktopMinutes > 0) {
            $platforms[] = [
                'name' => 'Linux',
                'hours' => (int) round($linuxDesktopMinutes / 60),
                'percentage' => 0,
                'color' => 'bg-purple-500',
            ];
        }
        if ($macMinutes > 0) {
            $platforms[] = [
                'name' => 'macOS',
                'hours' => (int) round($macMinutes / 60),
                'percentage' => 0,
                'color' => 'bg-orange-500',
            ];
        }
        if ($disconnectedMinutes > 0) {
            $platforms[] = [
                'name' => 'Offline',
                'hours' => (int) round($disconnectedMinutes / 60),
                'percentage' => 0,
                'color' => 'bg-gray-500',
            ];
        }

        // Считаем общее время для процентов
        $totalMinutes = $windowsMinutes + $linuxDesktopMinutes + $macMinutes + $deckMinutes + $disconnectedMinutes;
        if ($totalMinutes > 0) {
            foreach ($platforms as &$platform) {
                $platform['percentage'] = (int) round(($platform['hours'] * 60 / $totalMinutes) * 100);
            }
        }

        // Сортировка по убыванию hours
        usort($platforms, fn($a, $b) => $b['hours'] <=> $a['hours']);

        return response()->json(['platforms' => $platforms]);
    }

    /**
     * Возвращает историю игрового времени за последние 7 дней.
     */
    public function playtimeHistory(Game $game): JsonResponse
    {
        $stats = $game->gameStats()
            ->orderBy('date', 'asc')
            ->limit(7)
            ->get();

        if ($stats->isEmpty()) {
            return response()->json(['history' => []]);
        }

        $maxHours = $stats->max('total_minutes') / 60;

        $history = [];
        foreach ($stats as $stat) {
            $hours = $stat->total_minutes / 60;
            $platform = $this->determinePlatform($stat);
            $color = $this->platformColor($platform);
            $percentage = $maxHours > 0 ? (int) round(($hours / $maxHours) * 100) : 0;

            $history[] = [
                'day' => Carbon::parse($stat->date)->format('D'),
                'hours' => round($hours, 1),
                'platform' => $platform,
                'color' => $color,
                'percentage' => $percentage,
            ];
        }

        return response()->json(['history' => $history]);
    }

    /**
     * Возвращает последние 5 сессий (записей) для игры.
     */
    public function recentSessions(Game $game): JsonResponse
    {
        $stats = $game->gameStats()
            ->orderBy('date', 'desc')
            ->limit(5)
            ->get();

        $sessions = [];
        foreach ($stats as $stat) {
            $hours = floor($stat->total_minutes / 60);
            $minutes = $stat->total_minutes % 60;
            $duration = $hours > 0 ? sprintf('%dh %dm', $hours, $minutes) : sprintf('%dm', $minutes);

            $platform = $this->determinePlatform($stat);
            $platformClass = $this->platformCssClass($platform);

            $sessions[] = [
                'id' => $stat->id,
                'date' => $this->formatTimeAgo($stat->last_played_at),
                'duration' => $duration,
                'platform' => $platform,
                'platform_class' => $platformClass,
                'time_of_day' => '',
                'notes' => '',
            ];
        }

        return response()->json(['sessions' => $sessions]);
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
     * Возвращает цвет платформы.
     */
    private function platformColor(string $platform): string
    {
        return match ($platform) {
            'Windows' => 'bg-blue-500',
            'Steam Deck' => 'bg-green-500',
            'Linux' => 'bg-purple-500',
            'macOS' => 'bg-orange-500',
            'Offline' => 'bg-gray-500',
            default => 'bg-gray-300',
        };
    }

    /**
     * Возвращает CSS класс для платформы.
     */
    private function platformCssClass(string $platform): string
    {
        return match ($platform) {
            'Windows' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
            'Steam Deck' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
            'Linux' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300',
            'macOS' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300',
            'Offline' => 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-300',
            default => 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-300',
        };
    }
}