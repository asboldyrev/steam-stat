<?php

declare(strict_types=1);

namespace App\Enums;

enum GamePlatform: string
{
    case Windows = 'windows';
    case SteamDeck = 'steam_deck';
    case Linux = 'linux';
    case MacOS = 'macos';
    case Offline = 'offline';

    public function label(): string
    {
        return match ($this) {
            self::Windows => 'Windows',
            self::SteamDeck => 'Steam Deck',
            self::Linux => 'Linux',
            self::MacOS => 'macOS',
            self::Offline => 'Offline',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Windows => 'bg-blue-500',
            self::SteamDeck => 'bg-green-500',
            self::Linux => 'bg-purple-500',
            self::MacOS => 'bg-orange-500',
            self::Offline => 'bg-gray-500',
        };
    }

    /**
     * @return array{
     *     name: string,
     *     minutes: int|float,
     *     color: string
     * }
     */
    public function toArray(int|float $minutes): array
    {
        return [
            'name' => $this->label(),
            'minutes' => $minutes,
            'color' => $this->color(),
        ];
    }
}
