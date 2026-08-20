<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\Game;
use App\Models\PlaytimeSnapshot;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class GameLibraryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_library_uses_latest_snapshot_and_new_contract(): void
    {
        $game = $this->game(10, 'Game One', [
            'header' => ['local_url' => '/storage/games/10/header.jpg'],
        ]);

        $this->snapshot($game->id, '2026-08-18 12:00:00', 120, 120, 0, 0, 0);
        $this->snapshot($game->id, '2026-08-20 12:00:00', 300, 180, 120, 60, 0);

        $this->getJson('/api/games')
            ->assertOk()
            ->assertJsonCount(1, 'games')
            ->assertJsonPath('games.0.id', $game->id)
            ->assertJsonPath('games.0.total_minutes', 300)
            ->assertJsonPath('games.0.platforms.windows', true)
            ->assertJsonPath('games.0.platforms.deck', true)
            ->assertJsonPath('games.0.platforms.linux', true)
            ->assertJsonPath('games.0.artwork.header.local_url', '/storage/games/10/header.jpg');
    }

    public function test_linux_filter_means_linux_desktop_not_deck_overlap(): void
    {
        $deckOnly = $this->game(10, 'Deck Only');
        $linuxDesktop = $this->game(20, 'Linux Desktop');

        // Steam's linux counter includes Deck, so equal linux/deck means no Linux desktop time.
        $this->snapshot($deckOnly->id, '2026-08-20 12:00:00', 200, 0, 200, 200, 0);
        $this->snapshot($linuxDesktop->id, '2026-08-20 12:00:00', 300, 0, 300, 100, 0);

        $this->getJson('/api/games?platform=linux')
            ->assertOk()
            ->assertJsonCount(1, 'games')
            ->assertJsonPath('games.0.id', $linuxDesktop->id);
    }

    public function test_library_search_and_sort_are_applied_to_latest_snapshot(): void
    {
        $alpha = $this->game(10, 'Alpha Game');
        $beta = $this->game(20, 'Beta Game');

        $this->snapshot($alpha->id, '2026-08-19 12:00:00', 100, 100, 0, 0, 0);
        $this->snapshot($alpha->id, '2026-08-20 12:00:00', 600, 600, 0, 0, 0);
        $this->snapshot($beta->id, '2026-08-20 13:00:00', 300, 300, 0, 0, 0);

        $this->getJson('/api/games?sort=playtime')
            ->assertOk()
            ->assertJsonPath('games.0.id', $alpha->id)
            ->assertJsonPath('games.1.id', $beta->id);

        $this->getJson('/api/games?search=Beta')
            ->assertOk()
            ->assertJsonCount(1, 'games')
            ->assertJsonPath('games.0.id', $beta->id);
    }

    private function game(int $appId, string $name, array $artwork = []): Game
    {
        return Game::query()->create([
            'app_id' => $appId,
            'name' => $name,
            'icon_url' => null,
            'artwork' => $artwork,
            'has_community_visible_stats' => true,
        ]);
    }

    private function snapshot(
        int $gameId,
        string $capturedAt,
        int $total,
        int $windows,
        int $linux,
        int $deck,
        int $mac,
    ): PlaytimeSnapshot {
        return PlaytimeSnapshot::query()->create([
            'game_id' => $gameId,
            'captured_at' => CarbonImmutable::parse($capturedAt, 'UTC'),
            'total_minutes' => $total,
            'windows_minutes' => $windows,
            'linux_minutes' => $linux,
            'mac_minutes' => $mac,
            'deck_minutes' => $deck,
            'disconnected_minutes' => 0,
            'delta_total_minutes' => 0,
            'delta_windows_minutes' => 0,
            'delta_linux_minutes' => 0,
            'delta_mac_minutes' => 0,
            'delta_deck_minutes' => 0,
            'delta_disconnected_minutes' => 0,
            'has_counter_correction' => false,
            'last_played_at' => CarbonImmutable::parse($capturedAt, 'UTC'),
        ]);
    }
}
