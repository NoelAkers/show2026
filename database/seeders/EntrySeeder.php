<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowSection;
use Illuminate\Database\Seeder;

class EntrySeeder extends Seeder
{
    public function run(): void
    {
        $exhibitors = Exhibitor::all();
        $juniorSection = ShowSection::where('name', 'Juniors')->firstOrFail();
        $sections = ShowSection::with('showClasses')->get();

        foreach ($exhibitors as $exhibitor) {
            $targetCount = random_int(2, 20);
            $isSingleSection = random_int(1, 100) <= 60;

            // Adults may not enter the Junior section
            $eligible = $exhibitor->isAdult()
                ? $sections->reject(fn ($s) => $s->id === $juniorSection->id)
                : $sections;

            $eligibleClasses = $isSingleSection
                ? $eligible->random()->showClasses
                : $eligible->flatMap->showClasses;

            $classCounts = [];

            for ($i = 0; $i < $targetCount; $i++) {
                // Classes still under their per-exhibitor cap
                $available = $eligibleClasses->filter(
                    fn ($c) => ($classCounts[$c->id] ?? 0) < $c->max_entries_per_exhibitor
                );

                if ($available->isEmpty()) {
                    break;
                }

                // 2% chance: add a second/third entry to an already-entered class
                $alreadyEntered = $available->filter(fn ($c) => isset($classCounts[$c->id]));

                if ($alreadyEntered->isNotEmpty() && random_int(1, 100) <= 2) {
                    $class = $alreadyEntered->random();
                } else {
                    $fresh = $available->filter(fn ($c) => ! isset($classCounts[$c->id]));
                    $class = $fresh->isNotEmpty() ? $fresh->random() : $available->random();
                }

                Entry::create([
                    'exhibitor_id' => $exhibitor->id,
                    'show_class_id' => $class->id,
                ]);

                $classCounts[$class->id] = ($classCounts[$class->id] ?? 0) + 1;
            }
        }
    }
}
