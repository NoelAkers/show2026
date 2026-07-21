<?php

namespace Database\Seeders;

use App\Models\Exhibitor;
use Illuminate\Database\Seeder;

class ExhibitorSeeder extends Seeder
{
    public function run(): void
    {
        // 60 exhibitors: 70% residents (42), ~20% juniors (12)
        Exhibitor::factory()->adult()->resident()->count(34)->create();
        Exhibitor::factory()->adult()->nonResident()->count(14)->create();
        Exhibitor::factory()->junior()->resident()->count(9)->create();
        Exhibitor::factory()->junior()->nonResident()->count(3)->create();
    }
}
