<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );

        $user = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
        );

        $user2 = User::updateOrCreate(
            ['email' => 'user2@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
        );

        $this->command?->info("Seeded users: {$admin->email}, {$user->email}, {$user2->email}");
    }
}
