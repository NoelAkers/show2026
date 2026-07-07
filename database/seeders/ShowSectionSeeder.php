<?php

namespace Database\Seeders;

use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShowSectionSeeder extends Seeder
{
    public function run(): void
    {
        ShowSection::create(['sort_order' => 1, 'name' => 'Fruit & Veg', 'description' => 'Fruit & Vegetables']);
        ShowSection::create(['sort_order' => 2, 'name' => 'Giant Veg', 'description' => 'Battle of the Giant Veg!']);
        ShowSection::create(['sort_order' => 3, 'name' => 'Flowers', 'description' => 'Flowers']);
        ShowSection::create(['sort_order' => 4, 'name' => 'Celebrating Calverley in Bloom', 'description' => 'Fun floral classes']);
        ShowSection::create(['sort_order' => 5, 'name' => 'Baking', 'description' => 'Baking']);
        ShowSection::create(['sort_order' => 6, 'name' => 'Preserves', 'description' => 'Preserves']);
        ShowSection::create(['sort_order' => 7, 'name' => 'Handicrafts', 'description' => 'Handicrafts']);
        ShowSection::create(['sort_order' => 8, 'name' => 'Art', 'description' => 'Art']);
        ShowSection::create(['sort_order' => 9, 'name' => 'Photography', 'description' => 'Photography']);
        ShowSection::create(['sort_order' => 10, 'name' => 'Juniors', 'description' => 'Classes for juniors: under 11 and 11-15']);

        $sectionJudges = [
            'Fruit & Veg' => 'Stephen Ryan',
            'Giant Veg' => 'Stephen Ryan',
            'Flowers' => 'Stephen Ryan',
            'Celebrating Calverley in Bloom' => 'Andrew Carter',
            'Baking' => 'Peter Hoskins',
            'Preserves' => 'Peter Hoskins',
            'Handicrafts' => 'Lorna Muir',
            'Art' => 'Linda Wood',
            'Photography' => 'Anne Akers',
            'Juniors' => 'Judge TBC',
        ];

        ShowSection::all()->each(function (ShowSection $section) use ($sectionJudges) {
            $judge = User::where('name', $sectionJudges[$section->name])->sole();
            $section->judges()->attach($judge);
        });

        $sectionStewards = [
            'Fruit & Veg' => 'Head Steward',
            'Giant Veg' => 'Head Steward',
            'Flowers' => 'Head Steward',
            'Celebrating Calverley in Bloom' => 'Head Steward',
            'Baking' => 'Head Steward',
            'Preserves' => 'Head Steward',
            'Handicrafts' => 'Head Steward',
            'Art' => 'Head Steward',
            'Photography' => 'Head Steward',
            'Juniors' => 'Head Steward',
        ];

        ShowSection::all()->each(function (ShowSection $section) use ($sectionStewards) {
            $steward = User::where('name', $sectionStewards[$section->name])->sole();
            $section->stewards()->attach($steward);
        });

    }
}
