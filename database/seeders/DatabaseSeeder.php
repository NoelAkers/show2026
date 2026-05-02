<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use App\Models\ShowSection;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@show.local',
        ]);

        $judge1 = User::factory()->judge()->create([
            'name' => 'Judge One',
            'email' => 'judge1@show.local',
        ]);

        $judge2 = User::factory()->judge()->create([
            'name' => 'Judge Two',
            'email' => 'judge2@show.local',
        ]);

        $sections = ShowSection::factory()->count(3)->create()->each(function (ShowSection $section, int $index) {
            $section->update(['sort_order' => $index + 1]);

            $classCount = rand(5, 10);
            $classes = ShowClass::factory()->count($classCount)->create([
                'show_section_id' => $section->id,
            ])->each(function (ShowClass $class, int $i) {
                $class->update(['sort_order' => $i + 1]);
            });
        });

        $sections[0]->judges()->attach($judge1->id);
        $sections[1]->judges()->attach($judge2->id);
        $sections[2]->judges()->attach([$judge1->id, $judge2->id]);

        $exhibitors = collect([
            ...Exhibitor::factory()->adult()->resident()->count(5)->make()->all(),
            ...Exhibitor::factory()->adult()->nonResident()->count(5)->make()->all(),
            ...Exhibitor::factory()->junior()->resident()->count(5)->make()->all(),
            ...Exhibitor::factory()->junior()->nonResident()->count(5)->make()->all(),
        ])->each->save();

        $allClasses = ShowClass::all();

        foreach ($exhibitors as $exhibitor) {
            $targetCount = rand(3, 8);
            $classes = $allClasses->random(min($targetCount, $allClasses->count()));

            foreach ($classes as $class) {
                Entry::factory()->create([
                    'exhibitor_id' => $exhibitor->id,
                    'show_class_id' => $class->id,
                ]);
            }
        }
    }
}
