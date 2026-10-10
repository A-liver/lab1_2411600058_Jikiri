<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Demo accounts (password for all: "password").
        // role is not mass-assignable, so forceFill is used on purpose.
        $users = [
            ['name' => 'Admin',     'email' => 'admin@example.com',   'role' => User::ROLE_ADMIN],
            ['name' => 'Manager',   'email' => 'manager@example.com', 'role' => User::ROLE_MANAGER],
            ['name' => 'Test User', 'email' => 'test@example.com',    'role' => User::ROLE_STAFF],
        ];

        foreach ($users as $data) {
            $user = User::firstOrNew(['email' => $data['email']]);
            $user->forceFill([
                'name' => $data['name'],
                'role' => $data['role'],
                'password' => 'password', // hashed automatically by the model cast
                'email_verified_at' => now(),
            ])->save();
        }

        $this->call([
            ProductSeeder::class,
        ]);
    }
}