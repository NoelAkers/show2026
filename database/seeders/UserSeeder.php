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
            'name' => 'Judge TBC',
            'email' => 'judge@villageshow.org',
            'password' => Hash::make(env('SEEDER_JUDGE_PASSWORD')),
        ]);

        User::factory()->judge()->create([
            'name' => 'Stephen Ryan',
            'email' => 'stephen.ryan@villageshow.org',
            'password' => Hash::make(env('SEEDER_JUDGE_PASSWORD')),
        ]);

        User::factory()->judge()->create([
            'name' => 'Andrew Carter',
            'email' => 'andrew.carter@villageshow.org',
            'password' => Hash::make(env('SEEDER_JUDGE_PASSWORD')),
        ]);

        User::factory()->judge()->create([
            'name' => 'Peter Hoskins',
            'email' => 'peter.hoskins@villageshow.org',
            'password' => Hash::make(env('SEEDER_JUDGE_PASSWORD')),
        ]);

        User::factory()->judge()->create([
            'name' => 'Lorna Muir',
            'email' => 'lorna.muir@villageshow.org',
            'password' => Hash::make(env('SEEDER_JUDGE_PASSWORD')),
        ]);

        User::factory()->judge()->create([
            'name' => 'Linda Wood',
            'email' => 'linda.wood@villageshow.org',
            'password' => Hash::make(env('SEEDER_JUDGE_PASSWORD')),
        ]);

        User::factory()->judge()->create([
            'name' => 'Anne Akers',
            'email' => 'anne.akers@villageshow.org',
            'password' => Hash::make(env('SEEDER_JUDGE_PASSWORD')),
        ]);

        User::factory()->helper()->create([
            'name' => 'Generic Helper',
            'email' => 'helper@villageshow.org',
            'password' => Hash::make(env('SEEDER_HELPER_PASSWORD')),
        ]);

        User::factory()->steward()->create([
            'name' => 'Head Steward',
            'email' => 'chief.steward@villageshow.org',
            'password' => Hash::make(env('SEEDER_STEWARD_PASSWORD')),
        ]);
    }
}
