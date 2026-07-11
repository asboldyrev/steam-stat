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
    ];
}
