<?php

declare(strict_types=1);

namespace Tests\Feature\Services\Steam;

use App\Integrations\Steam\SteamApiClient;
use App\Models\Game;
use App\Models\GameStat;
use App\Models\PlaytimeSnapshot;
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
        $this->configureSteam();

        Http::fake([
            'https://api.steampowered.com/*' => Http::response($this->steamResponse(180, 60, 120, 90)),
        ]);

        $service = $this->app->make(SteamStatsSyncService::class);
        $service->sync(CarbonImmutable::parse('2026-08-19 12:00:00'));

        Http::assertSentCount(1);
        self::assertSame(1, Game::query()->count());
        self::assertSame(1, PlaytimeSnapshot::query()->count());
        self::assertSame(1, GameStat::query()->count());
        self::assertSame(1, SummaryStat::query()->count());
        self::assertSame(180, (int) SummaryStat::query()->value('total_minutes'));
        self::assertSame(0, (int) PlaytimeSnapshot::query()->value('delta_total_minutes'));
    }

    public function test_unchanged_sync_does_not_create_duplicate_snapshot(): void
    {
        $this->configureSteam();

        Http::fake([
            'https://api.steampowered.com/*' => Http::response($this->steamResponse(180, 60, 120, 90)),
        ]);

        $service = $this->app->make(SteamStatsSyncService::class);
        $service->sync(CarbonImmutable::parse('2026-08-19 12:00:00'));
        $service->sync(CarbonImmutable::parse('2026-08-19 12:15:00'));

        self::assertSame(1, PlaytimeSnapshot::query()->count());
        self::assertSame(1, GameStat::query()->count());
        self::assertSame(1, SummaryStat::query()->count());
    }

    public function test_multiple_syncs_on_same_day_accumulate_daily_delta(): void
    {
        $this->configureSteam();

        Http::fake([
            'https://api.steampowered.com/*' => Http::sequence()
                ->push($this->steamResponse(100, 100, 0, 0))
                ->push($this->steamResponse(130, 130, 0, 0))
                ->push($this->steamResponse(160, 160, 0, 0)),
        ]);

        $service = $this->app->make(SteamStatsSyncService::class);

        $service->sync(CarbonImmutable::parse('2026-08-18 23:45:00'));
        $service->sync(CarbonImmutable::parse('2026-08-19 10:00:00'));
        $service->sync(CarbonImmutable::parse('2026-08-19 20:00:00'));

        $game = Game::query()->firstOrFail();
        $dailyStat = GameStat::query()
            ->where('game_id', $game->id)
            ->where('date', '2026-08-19')
            ->firstOrFail();
        $summary = SummaryStat::query()->where('date', '2026-08-19')->firstOrFail();
        $snapshots = PlaytimeSnapshot::query()->orderBy('captured_at')->get();

        self::assertCount(3, $snapshots);
        self::assertSame([0, 30, 30], $snapshots->pluck('delta_total_minutes')->map(fn ($value): int => (int) $value)->all());
        self::assertSame(60, (int) $dailyStat->delta_total_minutes);
        self::assertSame(60, (int) $summary->delta_total_minutes);
    }

    public function test_counter_decrease_is_marked_as_correction(): void
    {
        $this->configureSteam();

        Http::fake([
            'https://api.steampowered.com/*' => Http::sequence()
                ->push($this->steamResponse(200, 200, 0, 0))
                ->push($this->steamResponse(180, 180, 0, 0)),
        ]);

        $service = $this->app->make(SteamStatsSyncService::class);
        $service->sync(CarbonImmutable::parse('2026-08-18 12:00:00'));
        $service->sync(CarbonImmutable::parse('2026-08-19 12:00:00'));

        $snapshot = PlaytimeSnapshot::query()->latest('captured_at')->firstOrFail();

        self::assertSame(-20, (int) $snapshot->delta_total_minutes);
        self::assertTrue($snapshot->has_counter_correction);
        self::assertSame(0, $snapshot->activityMinutes());
    }

    private function configureSteam(): void
    {
        config([
            'services.steam.api_key' => 'test-key',
            'services.steam.steam_id' => '76561198000000000',
            'services.steam.base_url' => 'https://api.steampowered.com',
        ]);

        $this->app->forgetInstance(SteamApiClient::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function steamResponse(
        int $totalMinutes,
        int $windowsMinutes,
        int $linuxMinutes,
        int $deckMinutes,
    ): array {
        return [
            'response' => [
                'games' => [
                    [
                        'appid' => 123,
                        'name' => 'Example Game',
                        'playtime_forever' => $totalMinutes,
                        'playtime_windows_forever' => $windowsMinutes,
                        'playtime_linux_forever' => $linuxMinutes,
                        'playtime_mac_forever' => 0,
                        'playtime_deck_forever' => $deckMinutes,
                        'playtime_disconnected' => 0,
                        'rtime_last_played' => 1720000000,
                        'img_icon_url' => 'iconhash',
                        'has_community_visible_stats' => true,
                    ],
                ],
            ],
        ];
    }
}
