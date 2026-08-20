<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\GameDetailController;
use App\Http\Controllers\Api\GameLibraryController;
use App\Http\Controllers\Api\SyncController;

Route::get('/activity', [ActivityController::class, 'index']);
Route::get('/activity/insights', [ActivityController::class, 'insights']);

Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
Route::get('/dashboard/platform-distribution', [DashboardController::class, 'platformDistribution']);
Route::get('/dashboard/recent-activity', [DashboardController::class, 'recentActivity']);
Route::get('/dashboard/top-games', [DashboardController::class, 'topGames']);

Route::get('/games', GameLibraryController::class);
Route::get('/games/{game}/detail', GameDetailController::class);

Route::get('/sync/last', [SyncController::class, 'last']);
Route::post('/sync/trigger', [SyncController::class, 'trigger']);
