<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchupWinRateTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_standalone_head_to_head_panel_is_removed(): void
    {
        $this->get(route('matches.index', [
            'fighter_a' => 'Dalusapi',
            'opponent' => 'Talisay',
        ]))
            ->assertOk()
            ->assertDontSee('Head-to-head')
            ->assertDontSee('Matchup win rate')
            ->assertDontSee('name="opponent"');
    }
}
