<?php

namespace Tests\Feature;

use App\Models\FightMatch;
use App\Models\User;
use App\Services\JwtTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Welcome back')
            ->assertSee('Sign in')
            ->assertSee(route('signup'));
        $this->get(route('signup'))
            ->assertOk()
            ->assertSee('Join as a player')
            ->assertSee('Create player account');
        $this->get(route('matches.index'))->assertRedirect(route('login'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_signup_creates_a_player_and_automatically_signs_them_in(): void
    {
        $this->post(route('signup.store'), [
            'name' => 'New Player',
            'email' => 'new-player@example.com',
            'password' => 'player-password',
            'password_confirmation' => 'player-password',
            'role' => 'admin',
        ])->assertRedirect(route('matches.index'))
            ->assertCookie('jwt');

        $player = User::query()->where('email', 'new-player@example.com')->firstOrFail();
        $this->assertSame('player', $player->role);
        $this->assertTrue(password_verify('player-password', $player->password));

        $this->withUnencryptedCookie('jwt', app(JwtTokenService::class)->issue($player))
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    public function test_signup_rejects_duplicate_emails_and_unconfirmed_or_short_passwords(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $this->from(route('signup'))->post(route('signup.store'), [
            'name' => 'New Player',
            'email' => 'existing@example.com',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertRedirect(route('signup'))
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_players_can_search_matches_but_cannot_access_admin_pages_or_mutations(): void
    {
        $player = User::factory()->create(['role' => 'player']);
        $match = FightMatch::create([
            'fighter_1' => 'Fighter A',
            'fighter_2' => 'Fighter B',
            'winner' => 'Fighter A',
            'description' => 'A recorded match',
        ]);
        $token = app(JwtTokenService::class)->issue($player);
        $this->assertSame($player->id, app(JwtTokenService::class)->authenticate(
            Request::create('/matches', 'GET', cookies: ['jwt' => $token]),
        )?->id);
        $this->withUnencryptedCookie('jwt', $token);

        $this->get(route('matches.index', ['search' => 'Fighter A']))
            ->assertOk()
            ->assertSee('A recorded match')
            ->assertDontSee('Add Match')
            ->assertDontSee('Edit match');
        $this->get(route('dashboard'))->assertForbidden();
        $this->get(route('fighters.index'))->assertForbidden();
        $this->get(route('matches.create'))->assertForbidden();
        $this->get(route('matches.edit', $match))->assertForbidden();
        $this->post(route('matches.store'), [
            'fighter_1' => 'Fighter C',
            'fighter_2' => 'Fighter D',
            'winner' => 'Fighter C',
            'description' => 'Should not be saved',
        ])->assertForbidden();
        $this->put(route('matches.update', $match), [
            'fighter_1' => 'Changed',
            'fighter_2' => 'Fighter B',
            'winner' => 'Changed',
            'description' => 'Should not be saved',
        ])->assertForbidden();
        $this->delete(route('matches.destroy', $match))->assertForbidden();
    }

    public function test_admins_can_access_admin_pages(): void
    {
        $this->authenticateAsAdmin();

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('matches.index'))->assertOk();
        $this->get(route('fighters.index'))->assertOk();
        $this->get(route('matches.create'))->assertOk();
    }

    public function test_successful_login_issues_a_jwt_and_redirects_by_role(): void
    {
        $player = User::factory()->create([
            'email' => 'player@example.com',
            'password' => 'secret-password',
            'role' => 'player',
        ]);
        $this->post(route('login.store'), [
            'email' => $player->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('matches.index'))
            ->assertCookie('jwt');

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
        $this->post(route('login.store'), [
            'email' => $admin->email,
            'password' => 'secret-password',
        ])->assertRedirect(route('dashboard'))
            ->assertCookie('jwt');
    }

    public function test_invalid_credentials_do_not_issue_a_jwt(): void
    {
        $this->from(route('login'))->post(route('login.store'), [
            'email' => 'unknown@example.com',
            'password' => 'incorrect',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('email')
            ->assertCookieMissing('jwt');
    }

    public function test_logout_revokes_the_token(): void
    {
        $player = User::factory()->create(['role' => 'player']);
        $token = app(JwtTokenService::class)->issue($player);
        $this->withUnencryptedCookie('jwt', $token)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->withUnencryptedCookie('jwt', $token)
            ->get(route('matches.index'))
            ->assertRedirect(route('login'));
    }

    public function test_tampered_and_expired_tokens_are_rejected(): void
    {
        $player = User::factory()->create(['role' => 'player']);
        $token = app(JwtTokenService::class)->issue($player);
        $parts = explode('.', $token);
        $parts[2][0] = $parts[2][0] === 'A' ? 'B' : 'A';
        $this->withUnencryptedCookie('jwt', implode('.', $parts))
            ->get(route('matches.index'))
            ->assertRedirect(route('login'));

        $this->travel(61)->minutes();
        $this->withUnencryptedCookie('jwt', $token)
            ->get(route('matches.index'))
            ->assertRedirect(route('login'));
    }
}
