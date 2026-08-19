<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Game;
use App\Models\PlaytimeSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_overview_aggregates_period_and_previous_period(): void
    {
        config(['steam-stat.timezone' => 'UTC']);

        $game = Game::query()->create([
            'app_id' => 10,
            'name' => 'Game One',
            'icon_url' => null,
            'has_community_visible_stats' => true,
        ]);

        $this->snapshot($game->id, '2026-08-17 12:00:00', 30, 30, 0, 0);
        $this->snapshot($game->id, '2026-08-18 12:00:00', 60, 40, 20, 10);
        $this->snapshot($game->id, '2026-08-19 12:00:00', 90, 30, 60, 20);

        $response = $this->getJson('/api/activity?from=2026-08-18&to=2026-08-19');

        $response->assertOk()
            ->assertJsonPath('period.from', '2026-08-18')
            ->assertJsonPath('period.to', '2026-08-19')
            ->assertJsonPath('period.days', 2)
            ->assertJsonPath('summary.total_minutes', 150)
            ->assertJsonPath('summary.active_days', 2)
            ->assertJsonPath('summary.games_count', 1)
            ->assertJsonPath('summary.average_minutes_per_active_day', 75)
            ->assertJsonPath('previous.total_minutes', 30)
            ->assertJsonPath('daily.0.minutes', 60)
            ->assertJsonPath('daily.1.minutes', 90)
            ->assertJsonPath('games.0.game_id', $game->id)
            ->assertJsonPath('games.0.minutes', 150)
            ->assertJsonPath('platforms.0.name', 'Windows');
    }

    public function test_activity_overview_can_filter_by_game(): void
    {
        config(['steam-stat.timezone' => 'UTC']);

        $first = Game::query()->create([
            'app_id' => 10,
            'name' => 'Game One',
            'icon_url' => null,
            'has_community_visible_stats' => true,
        ]);
        $second = Game::query()->create([
            'app_id' => 20,
            'name' => 'Game Two',
            'icon_url' => null,
            'has_community_visible_stats' => true,
        ]);

        $this->snapshot($first->id, '2026-08-19 12:00:00', 45, 45, 0, 0);
        $this->snapshot($second->id, '2026-08-19 13:00:00', 90, 90, 0, 0);

        $response = $this->getJson('/api/activity?from=2026-08-19&to=2026-08-19&game='.$first->id);

        $response->assertOk()
            ->assertJsonPath('summary.total_minutes', 45)
            ->assertJsonPath('summary.games_count', 1)
            ->assertJsonCount(1, 'games')
            ->assertJsonPath('games.0.game_id', $first->id);
    }

    public function test_activity_period_validation_rejects_invalid_range(): void
    {
        $this->getJson('/api/activity?from=2026-08-20&to=2026-08-19')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('to');
    }

    private function snapshot(
        int $gameId,
        string $capturedAt,
        int $deltaTotal,
        int $deltaWindows,
        int $deltaLinux,
        int $deltaDeck,
    ): PlaytimeSnapshot {
        return PlaytimeSnapshot::query()->create([
            'game_id' => $gameId,
            'captured_at' => CarbonImmutable::parse($capturedAt, 'UTC'),
            'total_minutes' => 1000,
            'windows_minutes' => 500,
            'linux_minutes' => 500,
            'mac_minutes' => 0,
            'deck_minutes' => 200,
            'disconnected_minutes' => 0,
            'delta_total_minutes' => $deltaTotal,
            'delta_windows_minutes' => $deltaWindows,
            'delta_linux_minutes' => $deltaLinux,
            'delta_mac_minutes' => 0,
            'delta_deck_minutes' => $deltaDeck,
            'delta_disconnected_minutes' => 0,
            'has_counter_correction' => false,
            'last_played_at' => CarbonImmutable::parse($capturedAt, 'UTC'),
        ]);
    }
}
