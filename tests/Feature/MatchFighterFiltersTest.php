<?php

namespace Tests\Feature;

use App\Models\FightMatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchFighterFiltersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authenticateAsAdmin();
    }

    public function test_it_filters_each_fighter_by_attributes_and_leg_color(): void
    {
        FightMatch::create([
            'fighter_1' => 'Dalusapi',
            'fighter_1_palong' => true,
            'fighter_1_lambe' => false,
            'fighter_1_leg_color' => 'White',
            'fighter_2' => 'Talisay',
            'fighter_2_palong' => false,
            'fighter_2_lambe' => true,
            'fighter_2_leg_color' => 'Dark',
            'winner' => 'Dalusapi',
            'description' => 'Target fight',
        ]);

        FightMatch::create([
            'fighter_1' => 'Dalusapi',
            'fighter_1_palong' => false,
            'fighter_1_lambe' => false,
            'fighter_1_leg_color' => 'Yellow',
            'fighter_2' => 'Talisay',
            'fighter_2_palong' => false,
            'fighter_2_lambe' => false,
            'fighter_2_leg_color' => 'Green',
            'winner' => 'Talisay',
            'description' => 'Other attributes',
        ]);

        $this->get(route('matches.index', [
            'fighter_1' => 'Dalusapi',
            'fighter_2' => 'Talisay',
            'filter_fighters' => 1,
            'fighter_1_palong' => 1,
            'fighter_1_leg_color' => 'White',
            'fighter_2_lambe' => 1,
            'fighter_2_leg_color' => 'Dark',
        ]))
            ->assertOk()
            ->assertSee('Target fight')
            ->assertDontSee('Other attributes')
            ->assertSee('Showing 1-1 of 1');
    }

    public function test_fighter_name_filters_match_the_exact_name_only(): void
    {
        FightMatch::create([
            'fighter_1' => ' Dalusapi ',
            'fighter_2' => ' Talisay ',
            'winner' => 'Dalusapi',
            'description' => 'Exact names',
        ]);

        FightMatch::create([
            'fighter_1' => 'Dalusapi Jr',
            'fighter_2' => 'Talisay',
            'winner' => 'Talisay',
            'description' => 'Longer fighter A name',
        ]);

        FightMatch::create([
            'fighter_1' => 'Dalusapi',
            'fighter_2' => 'Talisay II',
            'winner' => 'Talisay II',
            'description' => 'Longer fighter B name',
        ]);

        $this->get(route('matches.index', [
            'fighter_1' => ' DALUSAPI ',
            'fighter_2' => ' Talisay ',
        ]))
            ->assertOk()
            ->assertSee('Exact names')
            ->assertDontSee('Longer fighter A name')
            ->assertDontSee('Longer fighter B name')
            ->assertSee('Showing 1-1 of 1');
    }

    public function test_win_rates_are_calculated_from_the_filtered_fights(): void
    {
        $matchingAttributes = [
            'fighter_1' => 'Dalusapi',
            'fighter_1_palong' => true,
            'fighter_1_lambe' => false,
            'fighter_1_leg_color' => 'White',
            'fighter_2' => 'Talisay',
            'fighter_2_palong' => false,
            'fighter_2_lambe' => true,
            'fighter_2_leg_color' => 'Dark',
        ];

        FightMatch::create($matchingAttributes + [
            'winner' => 'Dalusapi',
            'description' => 'Filtered Dalusapi win',
        ]);
        FightMatch::create($matchingAttributes + [
            'winner' => 'Talisay',
            'description' => 'Filtered Talisay win',
        ]);
        FightMatch::create([
            'fighter_1' => 'Dalusapi',
            'fighter_1_palong' => false,
            'fighter_1_lambe' => false,
            'fighter_1_leg_color' => 'White',
            'fighter_2' => 'Talisay',
            'fighter_2_palong' => false,
            'fighter_2_lambe' => true,
            'fighter_2_leg_color' => 'Dark',
            'winner' => 'Dalusapi',
            'description' => 'Excluded Dalusapi win',
        ]);

        $this->get(route('matches.index', [
            'filter_fighters' => 1,
            'fighter_1' => 'Dalusapi',
            'fighter_2' => 'Talisay',
            'fighter_1_palong' => 1,
            'fighter_1_lambe' => 0,
            'fighter_1_leg_color' => 'White',
            'fighter_2_palong' => 0,
            'fighter_2_lambe' => 1,
            'fighter_2_leg_color' => 'Dark',
        ]))
            ->assertOk()
            ->assertSee('Win rate for filtered fights')
            ->assertSee('50.0%')
            ->assertSee('Based on 2 filtered fights.');
    }

    public function test_filtered_win_rates_include_draws_in_the_total(): void
    {
        $matchingAttributes = [
            'fighter_1' => 'Dalusapi',
            'fighter_1_palong' => true,
            'fighter_1_lambe' => false,
            'fighter_1_leg_color' => 'White',
            'fighter_2' => 'Talisay',
            'fighter_2_palong' => false,
            'fighter_2_lambe' => true,
            'fighter_2_leg_color' => 'Dark',
        ];

        foreach (range(1, 8) as $fight) {
            FightMatch::create($matchingAttributes + [
                'winner' => 'Dalusapi',
                'description' => "Filtered Dalusapi win {$fight}",
            ]);
        }

        FightMatch::create($matchingAttributes + [
            'winner' => 'Talisay',
            'description' => 'Filtered Talisay win',
        ]);
        FightMatch::create($matchingAttributes + [
            'winner' => 'Draw',
            'description' => 'Filtered draw',
        ]);
        FightMatch::create([
            'fighter_1' => 'Dalusapi',
            'fighter_1_palong' => false,
            'fighter_1_lambe' => false,
            'fighter_1_leg_color' => 'White',
            'fighter_2' => 'Talisay',
            'fighter_2_palong' => false,
            'fighter_2_lambe' => true,
            'fighter_2_leg_color' => 'Dark',
            'winner' => 'Dalusapi',
            'description' => 'Excluded win',
        ]);

        $this->get(route('matches.index', [
            'filter_fighters' => 1,
            'fighter_1' => 'Dalusapi',
            'fighter_2' => 'Talisay',
            'fighter_1_palong' => 1,
            'fighter_1_lambe' => 0,
            'fighter_1_leg_color' => 'White',
            'fighter_2_palong' => 0,
            'fighter_2_lambe' => 1,
            'fighter_2_leg_color' => 'Dark',
        ]))
            ->assertOk()
            ->assertSeeInOrder([
                'Dalusapi',
                '80.0%',
                '8 wins',
                'Talisay',
                '10.0%',
                '1 win',
                'Draw',
                '10.0%',
                '1 draw',
            ])
            ->assertSee('Based on 10 filtered fights.');
    }

    public function test_unchecked_trait_filters_match_fighters_without_those_traits(): void
    {
        FightMatch::create([
            'fighter_1' => 'Dalusapi',
            'fighter_1_palong' => false,
            'fighter_1_lambe' => false,
            'fighter_2' => 'Talisay',
            'fighter_2_palong' => false,
            'fighter_2_lambe' => false,
            'winner' => 'Dalusapi',
            'description' => 'No traits',
        ]);

        FightMatch::create([
            'fighter_1' => 'Dalusapi',
            'fighter_1_palong' => true,
            'fighter_1_lambe' => false,
            'fighter_2' => 'Talisay',
            'fighter_2_palong' => false,
            'fighter_2_lambe' => false,
            'winner' => 'Dalusapi',
            'description' => 'Has Palong',
        ]);

        $this->get(route('matches.index', [
            'filter_fighters' => 1,
            'fighter_1' => 'Dalusapi',
            'fighter_2' => 'Talisay',
        ]))
            ->assertOk()
            ->assertSee('No traits')
            ->assertDontSee('Has Palong');
    }

    public function test_it_saves_fighter_attributes_when_creating_a_match(): void
    {
        $this->post(route('matches.store'), [
            'fighter_1' => 'Dalusapi',
            'fighter_1_palong' => '1',
            'fighter_1_lambe' => '1',
            'fighter_1_leg_color' => 'Green',
            'fighter_2' => 'Talisay',
            'fighter_2_leg_color' => 'Dark',
            'winner' => 'Dalusapi',
            'description' => 'Fight with fighter attributes',
        ])->assertRedirect(route('matches.index'));

        $this->assertDatabaseHas('matches', [
            'fighter_1' => 'Dalusapi',
            'fighter_1_palong' => true,
            'fighter_1_lambe' => true,
            'fighter_1_leg_color' => 'Green',
            'fighter_2' => 'Talisay',
            'fighter_2_palong' => false,
            'fighter_2_lambe' => false,
            'fighter_2_leg_color' => 'Dark',
        ]);
    }

    public function test_fighter_attributes_are_available_on_create_and_edit_forms(): void
    {
        $this->get(route('matches.create'))
            ->assertOk()
            ->assertSee('Fighter A name')
            ->assertSee('Not specified');

        $match = FightMatch::create([
            'fighter_1' => 'Dalusapi',
            'fighter_1_palong' => true,
            'fighter_1_leg_color' => 'Green',
            'fighter_2' => 'Talisay',
            'winner' => 'Dalusapi',
            'description' => 'Recorded fight',
        ]);

        $this->get(route('matches.edit', $match))
            ->assertOk()
            ->assertSee('Fighter B name')
            ->assertSee('Green')
            ->assertSee('With Palong');
    }
}
