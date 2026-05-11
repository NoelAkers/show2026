<?php

namespace Database\Seeders;

use App\Models\PrizeLevel;
use Illuminate\Database\Seeder;

class PrizeLevelSeeder extends Seeder
{
    public function run(): void
    {
        PrizeLevel::create([
            'name' => 'Standard',
            'first_place_pence' => 100,
            'second_place_pence' => 50,
            'third_place_pence' => 25,
        ]);

        PrizeLevel::create([
            'name' => 'Top',
            'first_place_pence' => 500,
            'second_place_pence' => 250,
            'third_place_pence' => 100,
        ]);
    }
}
