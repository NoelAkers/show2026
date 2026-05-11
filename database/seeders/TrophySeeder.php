<?php

namespace Database\Seeders;

use App\Models\Trophy;
use Illuminate\Database\Seeder;

class TrophySeeder extends Seeder
{
    public function run(): void
    {
        // All classes (1–86)
        $all = range(1, 86);

        Trophy::create([
            'name' => 'The Edward and Sheila Garnett Trophy',
            'description' => 'Most points in the Show by a Calverley resident and/or Calverley Allotment Holder.',
        ])->showClasses()->attach($all);

        // Awarded for best individual exhibit — judged subjectively, not by points
        Trophy::create([
            'name' => 'The J Aubrey Grimshaw Rose Bowl',
            'description' => 'Best individual exhibit in the Flowers and Vegetables Sections. (This may be awarded to a single item in Classes 1 and 2).',
        ]);

        // Classes 3–49 (Fruit & Veg classes 3–24, Giant Veg 25–30, Flowers 31–49)
        Trophy::create([
            'name' => 'The A Dixon Trophy',
            'description' => 'Most points in Classes 3-49 for a person who has not previously won a trophy or medal in these classes',
        ])->showClasses()->attach(range(3, 49));

        // Classes 31–36 (first six Flowers classes)
        Trophy::create([
            'name' => 'The "C.R.A.G." Rose Bowl',
            'description' => 'Most points in Classes 31-36 by a Calverley resident and/or Calverley Allotment Holder showing the greatest variety in garden flowers.',
        ])->showClasses()->attach(range(31, 36));
    }
}
