<?php

declare(strict_types=1);

namespace App\Dto\Dashboard;

use JsonSerializable;

final readonly class PlatformDistributionItemDto implements JsonSerializable
{
    public function __construct(
        public string $name,
        public int $hours,
        public int $percentage,
        public string $color,
    ) {}

    /**
     * @return array{name: string, hours: int, percentage: int, color: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'hours' => $this->hours,
            'percentage' => $this->percentage,
            'color' => $this->color,
        ];
    }
}
