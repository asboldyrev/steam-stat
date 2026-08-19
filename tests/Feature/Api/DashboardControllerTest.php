<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_stats_returns_zero_values_without_synced_data(): void
    {
        $response = $this->getJson('/api/dashboard/stats');

        $response
            ->assertOk()
            ->assertExactJson([
                'total_playtime_hours' => 0,
                'games_count' => 0,
                'steam_deck_hours' => 0,
                'steam_deck_percentage' => 0,
            ]);
    }

    public function test_platform_distribution_returns_empty_array_without_synced_data(): void
    {
        $this->getJson('/api/dashboard/platform-distribution')
            ->assertOk()
            ->assertExactJson([]);
    }
}
