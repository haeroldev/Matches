<?php

namespace Tests;

use App\Models\User;
use App\Services\JwtTokenService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function authenticateAsAdmin(): User
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->withUnencryptedCookie('jwt', app(JwtTokenService::class)->issue($admin));

        return $admin;
    }
}
