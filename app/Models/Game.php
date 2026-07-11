<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'app_id',
        'name',
        'icon_url',
        'has_community_visible_stats',
    ];

    public function gameStats()
    {
        return $this->hasMany(GameStat::class);
    }

    public function latestGameStat()
    {
        return $this->hasOne(GameStat::class)->latest('date');
    }

    // TODO убрать дубль (SteamGameData)
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

    // TODO убрать дубль (SteamGameData)
    public function getUrl(): string
    {
        return 'https://store.steampowered.com/app/' . $this->app_id;
    }
}
