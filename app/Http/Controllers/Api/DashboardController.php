<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Dashboard\GetDashboardStats;
use App\Actions\Dashboard\GetPlatformDistribution;
use App\Actions\Dashboard\GetRecentActivity;
use App\Actions\Dashboard\GetTopGames;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class DashboardController extends Controller
{
    /**
     * Возвращает общую статистику для карточек дашборда.
     */
    public function stats(GetDashboardStats $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    /**
     * Возвращает распределение по платформам.
     */
    public function platformDistribution(GetPlatformDistribution $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    /**
     * Возвращает последние 3 игры по last_played_at.
     *
     * duration_hours — время последней сессии (последнего дня игры) в часах, округлённое.
     * total_hours — суммарное время всех сессий (всех дней) для игры в часах, округлённое.
     */
    public function recentActivity(GetRecentActivity $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    /**
     * Возвращает топ-3 игры по общему времени.
     */
    public function topGames(GetTopGames $action): JsonResponse
    {
        return response()->json($action->execute());
    }
}
