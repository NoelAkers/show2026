<?php

namespace Database\Seeders;

use App\Models\Exhibitor;
use Illuminate\Database\Seeder;

class ExhibitorSeeder extends Seeder
{
    public function run(): void
    {
        // 30 exhibitors: 70% residents (21), ~5% juniors (2)
        Exhibitor::factory()->adult()->resident()->count(19)->create();
        Exhibitor::factory()->adult()->nonResident()->count(9)->create();
        Exhibitor::factory()->junior()->resident()->count(2)->create();
    }
}
