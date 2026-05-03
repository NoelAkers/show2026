<?php

namespace Database\Seeders;

use App\Models\ShowSection;
use Illuminate\Database\Seeder;

class ShowSectionSeeder extends Seeder
{
    public function run(): void
    {
        ShowSection::create(['name' => 'Fruit&Veg', 'description' => 'Fruit and Vegetables', 'sort_order' => 1]);
        ShowSection::create(['name' => 'Flowers', 'description' => 'Flowers', 'sort_order' => 3]);
    }
}
