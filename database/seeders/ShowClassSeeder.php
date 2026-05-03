<?php

namespace Database\Seeders;

use App\Models\ShowClass;
use App\Models\ShowSection;
use Illuminate\Database\Seeder;

class ShowClassSeeder extends Seeder
{
    public function run(): void
    {
        $fruitVeg = ShowSection::where('name', 'Fruit&Veg')->firstOrFail();

        ShowClass::create([
            'show_section_id' => $fruitVeg->id,
            'name' => 'Calverley Master Gardener',
            'description' => 'Calverley Master Gardener - for Calverley allotment holders Trug/Basket max 45cmx30cm. Min 6 different fruit or vegetables from your plot arranged for effect with a small vase of mixed flowers from your plot or garden. CHS vase provided.',
            'max_entries_per_exhibitor' => 5,
            'sort_order' => 1,
        ]);

        ShowClass::create([
            'show_section_id' => $fruitVeg->id,
            'name' => 'Garden Trug',
            'description' => 'Garden Trug – Open to all Trug/Basket max 45cmx30cm. Min 6 different fruit or vegetables from your plot/garden arranged for effect with optional herbs or flowers as garnish.',
            'max_entries_per_exhibitor' => 5,
            'sort_order' => 2,
        ]);
    }
}
