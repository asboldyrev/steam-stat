<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Steam;

use App\Dto\Steam\SteamGameDto;
use App\Dto\Steam\SteamPlaytimeTotalsDto;
use PHPUnit\Framework\TestCase;

final class SteamPlaytimeTotalsDtoTest extends TestCase
{
    public function test_it_calculates_totals_from_games(): void
    {
        $games = collect([
            new SteamGameDto(
                appId: 1,
                name: 'Game One',
                totalMinutes: 120,
                windowsMinutes: 60,
                linuxMinutes: 60,
                macMinutes: 0,
                deckMinutes: 30,
                disconnectedMinutes: 0,
                lastPlayedAt: null,
                iconUrl: null,
                hasCommunityVisibleStats: true,
            ),
            new SteamGameDto(
                appId: 2,
                name: 'Game Two',
                totalMinutes: 90,
                windowsMinutes: 0,
                linuxMinutes: 90,
                macMinutes: 0,
                deckMinutes: 60,
                disconnectedMinutes: 0,
                lastPlayedAt: null,
                iconUrl: null,
                hasCommunityVisibleStats: false,
            ),
        ]);

        $totals = SteamPlaytimeTotalsDto::fromGames($games);

        self::assertSame(2, $totals->gamesCount);
        self::assertSame(210, $totals->totalMinutes);
        self::assertSame(60, $totals->windowsMinutes);
        self::assertSame(150, $totals->linuxMinutes);
        self::assertSame(60, $totals->linuxDesktopMinutes);
        self::assertSame(90, $totals->deckMinutes);
        self::assertSame(0, $totals->unclassifiedMinutes);
    }
}
