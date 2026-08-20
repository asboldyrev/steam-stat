<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Games\GetGameLibrary;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class GameLibraryController extends Controller
{
    public function __invoke(Request $request, GetGameLibrary $action): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'sort' => ['nullable', Rule::in(['playtime', 'name', 'last_played'])],
            'platform' => ['nullable', Rule::in(['all', 'windows', 'deck', 'linux', 'mac'])],
        ]);

        return response()->json($action->execute(
            search: isset($validated['search']) ? trim((string) $validated['search']) : null,
            sort: (string) ($validated['sort'] ?? 'playtime'),
            platform: (string) ($validated['platform'] ?? 'all'),
        ));
    }
}
