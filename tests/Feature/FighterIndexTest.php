<?php

namespace Tests\Feature;

use App\Models\FightMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FighterIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticateAsAdmin();
    }

    public function test_it_lists_unique_fighter_names_from_both_sides_of_matches(): void
    {
        FightMatch::create([
            'fighter_1' => 'Dalusapi',
            'fighter_2' => 'Talisay',
            'winner' => 'Dalusapi',
            'description' => 'First match',
        ]);

        FightMatch::create([
            'fighter_1' => 'DALUSAPI ',
            'fighter_2' => 'Banaba',
            'winner' => 'Banaba',
            'description' => 'Second match',
        ]);

        $this->get(route('fighters.index'))
            ->assertOk()
            ->assertSee('3 fighters')
            ->assertSeeInOrder(['Banaba', 'Dalusapi', 'Talisay'])
            ->assertDontSee('DALUSAPI')
            ->assertSee('Unique fighter names recorded in match history.');
    }

    public function test_it_shows_an_empty_state_when_there_are_no_fighters(): void
    {
        $this->get(route('fighters.index'))
            ->assertOk()
            ->assertSee('0 fighters')
            ->assertSee('No fighters found.');
    }

    public function test_create_and_edit_forms_suggest_existing_fighter_names(): void
    {
        $match = FightMatch::create([
            'fighter_1' => 'DALUSAPI',
            'fighter_2' => 'Talisay',
            'winner' => 'Talisay',
            'description' => 'Recorded match',
        ]);

        $this->get(route('matches.create'))
            ->assertOk()
            ->assertSee('id="fighter_1_suggestions"', false)
            ->assertSee('value="Dalusapi"', false)
            ->assertSee('value="Talisay"', false);

        $this->get(route('matches.edit', $match))
            ->assertOk()
            ->assertSee('id="fighter_2_suggestions"', false)
            ->assertSee('value="Dalusapi"', false)
            ->assertSee('value="Talisay"', false);
    }

    public function test_match_filters_suggest_fighters_from_the_maintenance_list(): void
    {
        FightMatch::create([
            'fighter_1' => 'DALUSAPI',
            'fighter_2' => 'Talisay',
            'winner' => 'Talisay',
            'description' => 'Recorded match',
        ]);

        $this->get(route('matches.index'))
            ->assertOk()
            ->assertSee('id="fighter_1_suggestions"', false)
            ->assertSee('id="fighter_2_suggestions"', false)
            ->assertSee('value="Dalusapi"', false)
            ->assertSee('value="Talisay"', false);
    }

    public function test_new_match_adds_only_new_unique_names_to_the_fighter_list(): void
    {
        FightMatch::create([
            'fighter_1' => 'Dalusapi',
            'fighter_2' => 'Talisay',
            'winner' => 'Dalusapi',
            'description' => 'First match',
        ]);

        $this->post(route('matches.store'), [
            'fighter_1' => 'DALUSAPI',
            'fighter_2' => 'Newcomer',
            'winner' => 'Newcomer',
            'description' => 'Second match',
        ])->assertRedirect(route('matches.index'));

        $this->get(route('fighters.index'))
            ->assertOk()
            ->assertSee('3 fighters')
            ->assertSeeInOrder(['Dalusapi', 'Newcomer', 'Talisay'])
            ->assertDontSee('DALUSAPI');
    }

    public function test_fighter_maintenance_list_is_paginated(): void
    {
        foreach (range(1, 11) as $number) {
            FightMatch::create([
                'fighter_1' => sprintf('Fighter %02d', ($number * 2) - 1),
                'fighter_2' => sprintf('Fighter %02d', $number * 2),
                'winner' => sprintf('Fighter %02d', ($number * 2) - 1),
                'description' => "Match {$number}",
            ]);
        }

        $this->get(route('fighters.index'))
            ->assertOk()
            ->assertSee('22 fighters')
            ->assertSee('page=2', false)
            ->assertSee('Fighter 01')
            ->assertDontSee('Fighter 21');

        $this->get(route('fighters.index', ['page' => 2]))
            ->assertOk()
            ->assertSee('22 fighters')
            ->assertSee('Fighter 21')
            ->assertSee('Fighter 22')
            ->assertDontSee('Fighter 01');
    }
}
