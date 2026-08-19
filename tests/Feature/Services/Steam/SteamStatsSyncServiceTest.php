<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Steam;

use App\Integrations\Steam\SteamApiClient;
use App\Models\Game;
use App\Models\GameStat;
use App\Models\SummaryStat;
use App\Services\Steam\SteamStatsSyncService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class SteamStatsSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_fetches_owned_games_only_once(): void
    {
        config([
            'services.steam.api_key' => 'test-key',
            'services.steam.steam_id' => '76561198000000000',
            'services.steam.base_url' => 'https://api.steampowered.com',
        ]);

        $this->app->forgetInstance(SteamApiClient::class);

        Http::fake([
            'https://api.steampowered.com/*' => Http::response([
                'response' => [
                    'games' => [
                        [
                            'appid' => 123,
                            'name' => 'Example Game',
                            'playtime_forever' => 180,
                            'playtime_windows_forever' => 60,
                            'playtime_linux_forever' => 120,
                            'playtime_mac_forever' => 0,
                            'playtime_deck_forever' => 90,
                            'playtime_disconnected' => 0,
                            'rtime_last_played' => 1720000000,
                            'img_icon_url' => 'iconhash',
                            'has_community_visible_stats' => true,
                        ],
                    ],
                ],
            ]),
        ]);

        $service = $this->app->make(SteamStatsSyncService::class);
        $service->sync(CarbonImmutable::parse('2026-08-19'));

        Http::assertSentCount(1);
        self::assertSame(1, Game::query()->count());
        self::assertSame(1, GameStat::query()->count());
        self::assertSame(1, SummaryStat::query()->count());
        self::assertSame(180, (int) SummaryStat::query()->value('total_minutes'));
    }
}
