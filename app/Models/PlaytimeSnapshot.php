<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\UtcImmutableDateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaytimeSnapshot extends Model
{
    protected $fillable = [
        'game_id',
        'captured_at',
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
        'has_counter_correction',
        'last_played_at',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => UtcImmutableDateTime::class,
            'last_played_at' => UtcImmutableDateTime::class,
            'has_counter_correction' => 'boolean',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function activityMinutes(): int
    {
        return max(0, (int) $this->delta_total_minutes);
    }
}
