<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Game extends Model
{
    protected $fillable = [
        'app_id',
        'name',
        'icon_url',
        'has_community_visible_stats',
    ];

    public function gameStats(): HasMany
    {
        return $this->hasMany(GameStat::class);
    }

    public function latestGameStat(): HasOne
    {
        return $this->hasOne(GameStat::class)->latestOfMany('date');
    }

    public function playtimeSnapshots(): HasMany
    {
        return $this->hasMany(PlaytimeSnapshot::class);
    }

    public function latestPlaytimeSnapshot(): HasOne
    {
        return $this->hasOne(PlaytimeSnapshot::class)->latestOfMany('captured_at');
    }

    public function iconUrlLarge(): ?string
    {
        if ($this->icon_url === null) {
            return null;
        }

        return sprintf(
            'https://media.steampowered.com/steamcommunity/public/images/apps/%d/%s.jpg',
            $this->app_id,
            $this->icon_url,
        );
    }

    public function storeUrl(): string
    {
        return 'https://store.steampowered.com/app/' . $this->app_id;
    }
}
