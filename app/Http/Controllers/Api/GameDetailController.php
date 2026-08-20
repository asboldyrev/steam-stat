<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Games\GetGameDetail;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class GameDetailController extends Controller
{
    public function __invoke(Request $request, Game $game, GetGameDetail $action): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        return response()->json($action->execute(
            $game,
            $validated['from'] ?? null,
            $validated['to'] ?? null,
        ));
    }
}
