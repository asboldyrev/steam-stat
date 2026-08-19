<?php

declare(strict_types=1);

namespace App\Dto\Steam;

use Illuminate\Support\Collection;
use JsonSerializable;

final readonly class SteamPlaytimeTotalsDto implements JsonSerializable
{
    public function __construct(
        public int $gamesCount,
        public int $totalMinutes,
        public int $windowsMinutes,
        public int $linuxMinutes,
        public int $linuxDesktopMinutes,
        public int $macMinutes,
        public int $deckMinutes,
        public int $disconnectedMinutes,
        public int $unclassifiedMinutes,
    ) {}

    /**
     * @param Collection<int, SteamGameDto> $games
     */
    public static function fromGames(Collection $games): self
    {
        $totalMinutes = 0;
        $windowsMinutes = 0;
        $linuxMinutes = 0;
        $linuxDesktopMinutes = 0;
        $macMinutes = 0;
        $deckMinutes = 0;
        $disconnectedMinutes = 0;
        $unclassifiedMinutes = 0;

        foreach ($games as $game) {
            $totalMinutes += $game->totalMinutes;
            $windowsMinutes += $game->windowsMinutes;
            $linuxMinutes += $game->linuxMinutes;
            $linuxDesktopMinutes += $game->linuxDesktopMinutes();
            $macMinutes += $game->macMinutes;
            $deckMinutes += $game->deckMinutes;
            $disconnectedMinutes += $game->disconnectedMinutes;
            $unclassifiedMinutes += $game->unclassifiedMinutes();
        }

        return new self(
            gamesCount: $games->count(),
            totalMinutes: $totalMinutes,
            windowsMinutes: $windowsMinutes,
            linuxMinutes: $linuxMinutes,
            linuxDesktopMinutes: $linuxDesktopMinutes,
            macMinutes: $macMinutes,
            deckMinutes: $deckMinutes,
            disconnectedMinutes: $disconnectedMinutes,
            unclassifiedMinutes: $unclassifiedMinutes,
        );
    }

    public function totalHours(): float
    {
        return $this->minutesToHours($this->totalMinutes);
    }

    public function windowsHours(): float
    {
        return $this->minutesToHours($this->windowsMinutes);
    }

    public function linuxHours(): float
    {
        return $this->minutesToHours($this->linuxMinutes);
    }

    public function linuxDesktopHours(): float
    {
        return $this->minutesToHours($this->linuxDesktopMinutes);
    }

    public function macHours(): float
    {
        return $this->minutesToHours($this->macMinutes);
    }

    public function deckHours(): float
    {
        return $this->minutesToHours($this->deckMinutes);
    }

    public function disconnectedHours(): float
    {
        return $this->minutesToHours($this->disconnectedMinutes);
    }

    public function unclassifiedHours(): float
    {
        return $this->minutesToHours($this->unclassifiedMinutes);
    }

    public function windowsPercentage(): float
    {
        return $this->percentage($this->windowsMinutes);
    }

    public function linuxDesktopPercentage(): float
    {
        return $this->percentage($this->linuxDesktopMinutes);
    }

    public function macPercentage(): float
    {
        return $this->percentage($this->macMinutes);
    }

    public function deckPercentage(): float
    {
        return $this->percentage($this->deckMinutes);
    }

    public function unclassifiedPercentage(): float
    {
        return $this->percentage($this->unclassifiedMinutes);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'games_count' => $this->gamesCount,
            'minutes' => [
                'total' => $this->totalMinutes,
                'windows' => $this->windowsMinutes,
                'linux' => $this->linuxMinutes,
                'linux_desktop' => $this->linuxDesktopMinutes,
                'mac' => $this->macMinutes,
                'deck' => $this->deckMinutes,
                'disconnected' => $this->disconnectedMinutes,
                'unclassified' => $this->unclassifiedMinutes,
            ],
            'hours' => [
                'total' => $this->totalHours(),
                'windows' => $this->windowsHours(),
                'linux' => $this->linuxHours(),
                'linux_desktop' => $this->linuxDesktopHours(),
                'mac' => $this->macHours(),
                'deck' => $this->deckHours(),
                'disconnected' => $this->disconnectedHours(),
                'unclassified' => $this->unclassifiedHours(),
            ],
            'percentages' => [
                'windows' => $this->windowsPercentage(),
                'linux_desktop' => $this->linuxDesktopPercentage(),
                'mac' => $this->macPercentage(),
                'deck' => $this->deckPercentage(),
                'unclassified' => $this->unclassifiedPercentage(),
            ],
        ];
    }

    private function minutesToHours(int $minutes): float
    {
        return round($minutes / 60, 2);
    }

    private function percentage(int $minutes): float
    {
        if ($this->totalMinutes <= 0) {
            return 0.0;
        }

        return round(($minutes / $this->totalMinutes) * 100, 2);
    }
}
