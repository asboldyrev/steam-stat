<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Steam\SteamStatsSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('steam:fetch-stats')]
#[Description('Fetch Steam playtime statistics and save to database')]
class SteamFetchStats extends Command
{
    public function handle(SteamStatsSyncService $syncService): int
    {
        $this->info('Fetching Steam statistics...');

        try {
            $syncService->sync();
            $this->info('Statistics saved successfully.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to fetch statistics: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}