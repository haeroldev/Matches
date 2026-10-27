<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $name = config('auth.bootstrap_admin.name');
        $email = config('auth.bootstrap_admin.email');
        $password = config('auth.bootstrap_admin.password');

        if ($name === null && $email === null && $password === null) {
            $this->command?->warn('No admin account was seeded. Set ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD to provision one.');

            return;
        }

        if (! is_string($name) || trim($name) === '' || ! is_string($email) || trim($email) === '' || ! is_string($password) || $password === '') {
            throw new \InvalidArgumentException('Set ADMIN_NAME, ADMIN_EMAIL, and ADMIN_PASSWORD together to seed an admin account.');
        }

        User::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => $password, 'role' => 'admin'],
        );
    }
}
