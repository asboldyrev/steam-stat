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
}
