<?php

namespace App\Providers;

use App\Integrations\Steam\SteamApiClient;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            SteamApiClient::class,
            function ($app): SteamApiClient {
                return new SteamApiClient(
                    http: $app->make(HttpFactory::class),
                    apiKey: (string) config('services.steam.api_key'),
                    steamId: (string) config('services.steam.steam_id'),
                    baseUrl: (string) config(
                        'services.steam.base_url',
                        'https://api.steampowered.com',
                    ),
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
