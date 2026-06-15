<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Show Admin',
            'email' => 'admin@villageshow.org',
            'password' => Hash::make(env('SEEDER_ADMIN_PASSWORD')),
        ]);

        User::factory()->judge()->create([
            'name' => 'TBC',
            'email' => 'tbc@villageshow.org',
            'password' => Hash::make(env('SEEDER_JUDGE_PASSWORD')),
        ]);

        User::factory()->helper()->create([
            'name' => 'Generic Helper',
            'email' => 'helper@villageshow.org',
            'password' => Hash::make(env('SEEDER_HELPER_PASSWORD')),
        ]);
    }
}
