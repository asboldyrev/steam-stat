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
    public function stats(GetDashboardStats $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function platformDistribution(GetPlatformDistribution $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function recentActivity(GetRecentActivity $action): JsonResponse
    {
        return response()->json($action->execute());
    }

    public function topGames(GetTopGames $action): JsonResponse
    {
        return response()->json($action->execute());
    }
}
