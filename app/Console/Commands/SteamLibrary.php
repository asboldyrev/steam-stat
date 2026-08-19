<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Dto\Steam\SteamGameDto;
use App\Dto\Steam\SteamPlaytimeTotalsDto;
use App\Integrations\Steam\SteamApiClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('steam:library')]
#[Description('Show Steam library playtime statistics')]
class SteamLibrary extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SteamApiClient $steam): int
    {
        $games = $steam->getPlayedGames();
        $totals = SteamPlaytimeTotalsDto::fromGames($games);

        $this->table(
            [
                'App ID',
                'Игра',
                'Всего',
                'Windows',
                'Linux PC',
                'Steam Deck',
                'Последний запуск',
            ],
            $games
                ->map(fn (SteamGameDto $game): array => [
                    $game->appId,
                    $game->name,
                    $this->formatMinutes($game->totalMinutes),
                    $this->formatMinutes($game->windowsMinutes),
                    $this->formatMinutes($game->linuxDesktopMinutes()),
                    $this->formatMinutes($game->deckMinutes),
                    $game->lastPlayedAt?->format('d.m.Y H:i') ?? '—',
                ])
                ->all()
        );

        $this->newLine();

        $this->info(
            sprintf(
                'Всего: %s | Steam Deck: %s | Windows: %s',
                $this->formatMinutes($totals->totalMinutes),
                $this->formatMinutes($totals->deckMinutes),
                $this->formatMinutes($totals->windowsMinutes),
            )
        );

        return self::SUCCESS;
    }

    private function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%d ч %02d мин', $hours, $remainingMinutes);
    }
}
