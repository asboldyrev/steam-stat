<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SummaryStat extends Model
{
    protected $fillable = [
        'date',
        'games_count',
        'total_minutes',
        'windows_minutes',
        'linux_minutes',
        'linux_desktop_minutes',
        'mac_minutes',
        'deck_minutes',
        'disconnected_minutes',
        'unclassified_minutes',
        'delta_total_minutes',
        'delta_windows_minutes',
        'delta_linux_minutes',
        'delta_linux_desktop_minutes',
        'delta_mac_minutes',
        'delta_deck_minutes',
        'delta_disconnected_minutes',
        'delta_unclassified_minutes',
    ];
}
