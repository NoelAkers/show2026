<?php

namespace Database\Seeders;

use App\Models\ShowSection;
use Illuminate\Database\Seeder;

class ShowSectionSeeder extends Seeder
{
    public function run(): void
    {
        ShowSection::create(['sort_order' => 1, 'name' => 'Fruit & Veg', 'description' => 'Fruit & Vegetables']);
        ShowSection::create(['sort_order' => 2, 'name' => 'Giant Veg', 'description' => 'Battle of the Giant Veg!']);
        ShowSection::create(['sort_order' => 3, 'name' => 'Flowers', 'description' => 'Flowers']);
        ShowSection::create(['sort_order' => 4, 'name' => 'Baking', 'description' => 'Baking']);
        ShowSection::create(['sort_order' => 5, 'name' => 'Preserves', 'description' => 'Preserves']);
        ShowSection::create(['sort_order' => 6, 'name' => 'Handicrafts', 'description' => 'Handicrafts']);
        ShowSection::create(['sort_order' => 7, 'name' => 'Art', 'description' => 'Art']);
        ShowSection::create(['sort_order' => 8, 'name' => 'Photography', 'description' => 'Photography']);
        ShowSection::create(['sort_order' => 9, 'name' => 'Juniors', 'description' => 'Classes for juniors: under 11 and 11-15']);
    }
}
