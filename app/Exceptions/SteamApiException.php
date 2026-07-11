<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

final class SteamApiException extends RuntimeException
{
    public static function invalidConfiguration(string $parameter): self
    {
        return new self(
            sprintf('Steam API configuration parameter "%s" is missing.', $parameter)
        );
    }

    public static function invalidResponse(
        string $message,
        ?Throwable $previous = null,
    ): self {
        return new self(
            message: 'Steam API returned an invalid response: ' . $message,
            previous: $previous,
        );
    }
}
