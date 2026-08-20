<?php

declare(strict_types=1);

namespace App\Dto\Statistics;

use JsonSerializable;

final readonly class GameLibraryDto implements JsonSerializable
{
    /** @param list<GameLibraryItemDto> $games */
    public function __construct(public array $games) {}

    public function jsonSerialize(): array
    {
        return [
            'games' => array_map(
                static fn (GameLibraryItemDto $game): array => $game->jsonSerialize(),
                $this->games,
            ),
        ];
    }
}
