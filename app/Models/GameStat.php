<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameStat extends Model
{
    protected $fillable = [
        'game_id',
        'date',
        'total_minutes',
        'windows_minutes',
        'linux_minutes',
        'mac_minutes',
        'deck_minutes',
        'disconnected_minutes',
        'delta_total_minutes',
        'delta_windows_minutes',
        'delta_linux_minutes',
        'delta_mac_minutes',
        'delta_deck_minutes',
        'delta_disconnected_minutes',
        'last_played_at',
    ];

    protected $casts = [
        'last_played_at' => 'datetime',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }
}
