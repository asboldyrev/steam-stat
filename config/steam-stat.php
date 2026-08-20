<?php

declare(strict_types=1);

return [
    'timezone' => env('STEAM_STAT_TIMEZONE', env('APP_TIMEZONE', 'UTC')),

    'steamgriddb' => [
        'api_key' => env('STEAMGRIDDB_API_KEY'),
    ],
];
