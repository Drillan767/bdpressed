<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $rolesList = Role::all();

        if ($rolesList->isEmpty()) {
            Role::create(['name' => 'admin']);
            Role::create(['name' => 'user']);
        }

        if (!User::where('email', 'oddejade@gmail.com')->exists()) {
            $jade = User::create([
                'email' => 'oddejade@gmail.com',
                'email_verified_at' => '2024-12-26 18:47:56',
                'password' => '$2y$12$PLd3oXDBFF0VJ4Dvo7X/J.Gfy2tvDuHQeyxHTPSWeo3C28rGHtB/W',
            ]);

            $jade->assignRole('admin');
        }

        if (!User::where('email', 'jlevarato@pm.me')->exists()) {
            $dev = User::create([
                'email' => 'jlevarato@pm.me',
                'email_verified_at' => '2024-12-26 18:47:56',
                'password' => '$2y$12$hr82tr90Q6k.DprW1FL8XeI.2Uij6.CmDiWvlZHmdgApb1np2Ao6u',
            ]);

            $dev->assignRole('admin');
        }
    }
}
