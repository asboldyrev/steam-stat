<?php

declare(strict_types=1);

namespace App\Data;

use Carbon\CarbonImmutable;
use JsonSerializable;

final readonly class SteamGameData implements JsonSerializable
{
    public function __construct(
        public int $appId,
        public string $name,
        public int $totalMinutes,
        public int $windowsMinutes,
        public int $linuxMinutes,
        public int $macMinutes,
        public int $deckMinutes,
        public int $disconnectedMinutes,
        public ?CarbonImmutable $lastPlayedAt,
        public ?string $iconUrl,
        public bool $hasCommunityVisibleStats,
    ) {}

    /**
     * Приблизительное время на обычных Linux-компьютерах.
     *
     * Steam Deck работает на Linux, поэтому deckMinutes может также
     * входить в linuxMinutes. Это вычисление следует считать оценочным.
     */
    public function linuxDesktopMinutes(): int
    {
        return max(0, $this->linuxMinutes - $this->deckMinutes);
    }

    /**
     * Время, которое не удалось распределить по известным платформам.
     *
     * Показатель является диагностическим и не обязательно будет равен нулю:
     * старые сессии могли не иметь платформенной детализации.
     */
    public function unclassifiedMinutes(): int
    {
        $classified = $this->windowsMinutes
            + $this->macMinutes
            + $this->linuxDesktopMinutes()
            + $this->deckMinutes;

        return max(0, $this->totalMinutes - $classified);
    }

    public function totalHours(): float
    {
        return round($this->totalMinutes / 60, 2);
    }

    public function deckHours(): float
    {
        return round($this->deckMinutes / 60, 2);
    }

    public function windowsHours(): float
    {
        return round($this->windowsMinutes / 60, 2);
    }

    public function iconUrlLarge(): ?string
    {
        if ($this->iconUrl === null) {
            return null;
        }

        return sprintf(
            'https://media.steampowered.com/steamcommunity/public/images/apps/%d/%s.jpg',
            $this->appId,
            $this->iconUrl,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'app_id' => $this->appId,
            'name' => $this->name,

            'playtime' => [
                'total_minutes' => $this->totalMinutes,
                'windows_minutes' => $this->windowsMinutes,
                'linux_minutes' => $this->linuxMinutes,
                'linux_desktop_minutes' => $this->linuxDesktopMinutes(),
                'mac_minutes' => $this->macMinutes,
                'deck_minutes' => $this->deckMinutes,
                'disconnected_minutes' => $this->disconnectedMinutes,
                'unclassified_minutes' => $this->unclassifiedMinutes(),
            ],

            'last_played_at' => $this->lastPlayedAt?->toIso8601String(),
            'icon_url' => $this->iconUrlLarge(),
            'has_community_visible_stats' => $this->hasCommunityVisibleStats,
        ];
    }
}
