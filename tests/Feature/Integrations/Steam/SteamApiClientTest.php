<?php

declare(strict_types=1);

namespace Tests\Feature\Integrations\Steam;

use App\Dto\Steam\SteamGameDto;
use App\Integrations\Steam\SteamApiClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class SteamApiClientTest extends TestCase
{
    public function test_it_maps_owned_games_to_dtos(): void
    {
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

        $client = new SteamApiClient(
            http: app('http'),
            apiKey: 'test-key',
            steamId: '76561198000000000',
        );

        $games = $client->getOwnedGames();

        self::assertCount(1, $games);
        self::assertInstanceOf(SteamGameDto::class, $games->first());
        self::assertSame(123, $games->first()->appId);
        self::assertSame(180, $games->first()->totalMinutes);
        self::assertSame(30, $games->first()->linuxDesktopMinutes());
        self::assertTrue($games->first()->hasCommunityVisibleStats);

        Http::assertSentCount(1);
    }
}
