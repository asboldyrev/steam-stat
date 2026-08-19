<?php

declare(strict_types=1);

namespace App\Casts;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Stores database timestamps as UTC and always hydrates them as UTC values,
 * regardless of the application's display/activity timezone.
 *
 * @implements CastsAttributes<CarbonImmutable|null, CarbonImmutable|DateTimeInterface|string|null>
 */
final class UtcImmutableDateTime implements CastsAttributes
{
    public function get(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): ?CarbonImmutable {
        if ($value === null || $value === '') {
            return null;
        }

        return CarbonImmutable::parse((string) $value, 'UTC');
    }

    public function set(
        Model $model,
        string $key,
        mixed $value,
        array $attributes,
    ): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return CarbonImmutable::instance($value)
                ->utc()
                ->format('Y-m-d H:i:s');
        }

        if (is_string($value)) {
            return CarbonImmutable::parse($value)
                ->utc()
                ->format('Y-m-d H:i:s');
        }

        throw new InvalidArgumentException(sprintf(
            'The %s attribute must be a date-time value, string, or null.',
            $key,
        ));
    }
}
