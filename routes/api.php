<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\SyncController;

Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
Route::get('/dashboard/platform-distribution', [DashboardController::class, 'platformDistribution']);
Route::get('/dashboard/recent-activity', [DashboardController::class, 'recentActivity']);
Route::get('/dashboard/top-games', [DashboardController::class, 'topGames']);

Route::get('/games', [GameController::class, 'index']);
Route::get('/games/{game}', [GameController::class, 'show']);
Route::get('/games/{game}/platform-breakdown', [GameController::class, 'platformBreakdown']);
Route::get('/games/{game}/playtime-history', [GameController::class, 'playtimeHistory']);
Route::get('/games/{game}/recent-sessions', [GameController::class, 'recentSessions']);

Route::get('/sync/last', [SyncController::class, 'last']);
Route::post('/sync/trigger', [SyncController::class, 'trigger']);
