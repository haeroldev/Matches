<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtTokenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Cookie;

class AuthController extends Controller
{
    public function showLogin(Request $request, JwtTokenService $tokens): View|RedirectResponse
    {
        $user = $tokens->authenticate($request);

        if ($user !== null) {
            return redirect()->route($user->isAdmin() ? 'dashboard' : 'matches.index');
        }

        return view('auth.login');
    }

    public function showSignup(Request $request, JwtTokenService $tokens): View|RedirectResponse
    {
        $user = $tokens->authenticate($request);

        if ($user !== null) {
            return redirect()->route($user->isAdmin() ? 'dashboard' : 'matches.index');
        }

        return view('auth.signup');
    }

    public function signup(Request $request, JwtTokenService $tokens): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'player',
        ]);

        return redirect()
            ->route('matches.index')
            ->withCookie($this->jwtCookie($tokens->issue($user), $tokens));
    }

    public function login(Request $request, JwtTokenService $tokens): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $user = User::query()->where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        return redirect()
            ->route($user->isAdmin() ? 'dashboard' : 'matches.index')
            ->withCookie($this->jwtCookie($tokens->issue($user), $tokens));
    }

    public function logout(Request $request, JwtTokenService $tokens): RedirectResponse
    {
        $tokens->revoke($request->user());

        return redirect()
            ->route('login')
            ->withCookie($this->jwtCookie('', $tokens, true));
    }

    private function jwtCookie(string $value, JwtTokenService $tokens, bool $expired = false): Cookie
    {
        return cookie(
            'jwt',
            $value,
            $expired ? -1 : $tokens->ttlInMinutes(),
            '/',
            null,
            request()->isSecure(),
            true,
            false,
            'lax',
        );
    }
}
