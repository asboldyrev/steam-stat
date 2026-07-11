<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SummaryStat;
use App\Services\Steam\SteamStatsSyncService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SyncController extends Controller
{
    public function __construct(
        private readonly SteamStatsSyncService $syncService,
    ) {}

    /**
     * Возвращает время последней синхронизации.
     */
    public function last(): JsonResponse
    {
        $latest = SummaryStat::query()->max('date');

        if (!$latest) {
            return response()->json(['last_sync' => 'Never']);
        }

        $date = Carbon::parse($latest);
        $diff = $date->diffForHumans();

        return response()->json(['last_sync' => $diff]);
    }

    /**
     * Запускает синхронизацию.
     */
    public function trigger(Request $request): JsonResponse
    {
        try {
            $this->syncService->sync();

            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}