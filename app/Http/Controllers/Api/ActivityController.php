<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Activity\GetActivityInsights;
use App\Actions\Activity\GetActivityOverview;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityPeriodRequest;
use Illuminate\Http\JsonResponse;

final class ActivityController extends Controller
{
    public function index(ActivityPeriodRequest $request, GetActivityOverview $action): JsonResponse
    {
        $validated = $request->validated();

        return response()->json($action->execute(
            from: $validated['from'] ?? null,
            to: $validated['to'] ?? null,
            gameId: isset($validated['game']) ? (int) $validated['game'] : null,
        ));
    }

    public function insights(ActivityPeriodRequest $request, GetActivityInsights $action): JsonResponse
    {
        $validated = $request->validated();

        return response()->json($action->execute(
            from: $validated['from'] ?? null,
            to: $validated['to'] ?? null,
            gameId: isset($validated['game']) ? (int) $validated['game'] : null,
        ));
    }
}
