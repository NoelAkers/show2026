<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Noel Akers',
            'email' => 'noel@oleana.co.uk',
        ]);

        User::factory()->judge()->create([
            'name' => 'Anne Akers',
            'email' => 'anne.akers@oleana.co.uk',
        ]);
    }
}
