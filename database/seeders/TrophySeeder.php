<?php

namespace Database\Seeders;

use App\Models\Trophy;
use App\Models\User;
use Illuminate\Database\Seeder;

class TrophySeeder extends Seeder
{
    public function run(): void
    {
        $judge = User::where('email', 'judge@villageshow.org')->sole();
        $steward = User::where('email', 'chief.steward@villageshow.org')->sole();

        Trophy::create([
            'name' => 'The Edward and Sheila Garnett Trophy',
            'description' => 'Most points in the Show by a Calverley resident and/or Calverley Allotment Holder.',
        ])->showClasses()->attach(range(1, 86));

        Trophy::create([
            'name' => 'The J Aubrey Grimshaw Rose Bowl',
            'description' => 'Best individual exhibit in the Flowers & Vegetables sections',
            'is_points_based' => false,
            'judge_id' => $judge->id,
            'steward_id' => $steward->id,
        ]);

        Trophy::create([
            'name' => 'The A Dixon Trophy',
            'description' => 'Most points in Classes 3-49 for a person who has not previously won a trophy or medal in these classes',
        ])->showClasses()->attach(range(3, 49));

        Trophy::create([
            'name' => 'The "C.R.A.G." Rose Bowl',
            'description' => 'Most points in Classes 32-36 by a Calverley resident and/or Calverley Allotment Holder showing the greatest variety in garden flowers.',
        ])->showClasses()->attach(range(31, 36));

        Trophy::create([
            'name' => 'The Alf Parker Rose Bowl',
            'description' => 'Best Rose',
            'is_points_based' => false,
            'judge_id' => $judge->id,
            'steward_id' => $steward->id,
        ]);

        Trophy::create([
            'name' => 'The Alan Saul Memorial Trophy',
            'description' => 'Best Gladioli',
            'is_points_based' => false,
            'judge_id' => $judge->id,
            'steward_id' => $steward->id,
        ]);

        Trophy::create([
            'name' => 'The Councillor Wylde Cup',
            'description' => 'Most points in the Flowers section',
        ])->showClasses()->attach(range(31, 49));

        Trophy::create([
            'name' => 'The Dr N Hughes Cup',
            'description' => 'Most points in the Vegetables Section',
        ])->showClasses()->attach(range(2, 24));

        Trophy::create([
            'name' => "The National Vegetable Society's Medal",
            'description' => 'Best exhibit in the Vegetables section',
            'is_points_based' => false,
            'judge_id' => $judge->id,
            'steward_id' => $steward->id,
        ]);

        Trophy::create([
            'name' => 'The Bannister Trophy : Calverley Allotment Champion',
            'description' => 'Best trug in Class 1',
        ])->showClasses()->attach([1]);

        Trophy::create([
            'name' => 'The Scott Trophy for Baking',
            'description' => 'Most points in Baking/Preserves sections',
        ])->showClasses()->attach(range(50, 63));

        Trophy::create([
            'name' => 'The Gibson Wells Trophy for Handicrafts',
            'description' => 'Best exhibit in the Handicrafts section',
            'is_points_based' => false,
            'judge_id' => $judge->id,
            'steward_id' => $steward->id,
        ]);

        Trophy::create([
            'name' => 'The Paine Trophy for Art',
            'description' => 'Best exhibit in Art section',
            'is_points_based' => false,
            'judge_id' => $judge->id,
            'steward_id' => $steward->id,
        ]);

        Trophy::create([
            'name' => 'The Trustees Fancy Trophy',
            'description' => 'Favorite exhibit in the Show chosen by the CHS Trustees',
            'is_points_based' => false,
            'judge_id' => $judge->id,
            'steward_id' => $steward->id,
        ]);

        Trophy::create([
            'name' => 'The President\'s Junior Award',
            'description' => 'Best exhibit in Junior Classes',
            'is_points_based' => false,
            'judge_id' => $judge->id,
            'steward_id' => $steward->id,
        ]);
    }
}
