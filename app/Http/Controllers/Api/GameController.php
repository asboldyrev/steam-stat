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
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $sort = $request->query('sort', 'playtime');
        $platform = $request->query('platform', 'all');

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

        if ($search) {
            $query->whereHas('game', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%');
            });
        }

        match ($sort) {
            'name' => $query->orderBy(
                Game::select('name')
                    ->whereColumn('games.id', 'game_stats.game_id')
                    ->limit(1),
                'asc'
            ),
            'last_played' => $query->orderByDesc('last_played_at'),
            default => $query->orderByDesc('total_minutes'),
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
            $gradient = $gradients[$index % count($gradients)];

            $games[] = [
                'id' => $game->id,
                'app_id' => $game->app_id,
                'name' => $game->name,
                'abbreviation' => $abbreviation,
                'artwork' => $game->artwork ?? [],
                'gradient' => $gradient,
                'total_time' => $totalHours,
                'last_played' => $stat->last_played_at?->timestamp,
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

    public function show(Game $game): JsonResponse
    {
        $latestStat = $game->gameStats()->latest('date')->first();
        $totalPlaytimeHours = $latestStat ? (int) round($latestStat->total_minutes / 60) : 0;

        return response()->json([
            'id' => $game->id,
            'app_id' => $game->app_id,
            'name' => $game->name,
            'abbreviation' => $this->generateAbbreviation($game->name),
            'artwork' => $game->artwork ?? [],
            'store_url' => $game->storeUrl(),
            'total_playtime_hours' => $totalPlaytimeHours,
            'last_played' => $latestStat?->last_played_at?->timestamp,
        ]);
    }

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
        $linuxDesktopMinutes = max(0, $linuxMinutes - $deckMinutes);

        $platforms = [];
        if ($windowsMinutes > 0) $platforms[] = ['name' => 'Windows', 'hours' => (int) round($windowsMinutes / 60), 'percentage' => 0, 'color' => 'bg-blue-500'];
        if ($deckMinutes > 0) $platforms[] = ['name' => 'Steam Deck', 'hours' => (int) round($deckMinutes / 60), 'percentage' => 0, 'color' => 'bg-green-500'];
        if ($linuxDesktopMinutes > 0) $platforms[] = ['name' => 'Linux', 'hours' => (int) round($linuxDesktopMinutes / 60), 'percentage' => 0, 'color' => 'bg-purple-500'];
        if ($macMinutes > 0) $platforms[] = ['name' => 'macOS', 'hours' => (int) round($macMinutes / 60), 'percentage' => 0, 'color' => 'bg-orange-500'];
        if ($disconnectedMinutes > 0) $platforms[] = ['name' => 'Offline', 'hours' => (int) round($disconnectedMinutes / 60), 'percentage' => 0, 'color' => 'bg-gray-500'];

        $totalMinutes = $windowsMinutes + $linuxDesktopMinutes + $macMinutes + $deckMinutes + $disconnectedMinutes;
        if ($totalMinutes > 0) {
            foreach ($platforms as &$platform) {
                $platform['percentage'] = (int) round(($platform['hours'] * 60 / $totalMinutes) * 100);
            }
        }

        usort($platforms, fn($a, $b) => $b['hours'] <=> $a['hours']);

        return response()->json(['platforms' => $platforms]);
    }

    public function playtimeHistory(Game $game): JsonResponse
    {
        $stats = $game->gameStats()->orderBy('date', 'asc')->limit(7)->get();

        if ($stats->isEmpty()) {
            return response()->json(['history' => []]);
        }

        $maxMinutes = $stats->max('delta_total_minutes');
        $history = [];
        foreach ($stats as $stat) {
            $platform = $this->determinePlatform($stat);
            $color = $this->platformColor($platform);
            $minutes = $stat->delta_total_minutes;
            $percentage = $maxMinutes > 0 ? (int) round(($minutes / $maxMinutes) * 100) : 0;

            $history[] = [
                'day' => Carbon::parse($stat->date),
                'minutes' => $minutes,
                'platform' => $platform,
                'color' => $color,
                'percentage' => $percentage,
            ];
        }

        return response()->json(['history' => $history]);
    }

    private function generateAbbreviation(string $name): string
    {
        $words = preg_split('/\s+/u', trim($name));
        if (count($words) === 1) {
            return mb_strtoupper(mb_substr($words[0], 0, 3, 'UTF-8'), 'UTF-8');
        }

        $abbr = '';
        foreach ($words as $word) {
            $firstChar = mb_substr($word, 0, 1, 'UTF-8');
            if ($firstChar !== '' && preg_match('/[A-Za-z]/u', $firstChar)) {
                $abbr .= mb_strtoupper($firstChar, 'UTF-8');
            }
            if (mb_strlen($abbr, 'UTF-8') >= 3) break;
        }

        return mb_strlen($abbr, 'UTF-8') > 0 ? $abbr : '???';
    }

    private function determinePlatform(GameStat $stat): string
    {
        $platforms = [
            'Windows' => $stat->windows_minutes,
            'Steam Deck' => $stat->deck_minutes,
            'Linux' => max(0, $stat->linux_minutes - $stat->deck_minutes),
            'macOS' => $stat->mac_minutes,
            'Offline' => $stat->disconnected_minutes,
        ];

        arsort($platforms);
        return array_key_first($platforms) ?? 'Unknown';
    }

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
}
