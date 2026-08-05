<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use Illuminate\Database\Seeder;

class LabelTestSeeder extends Seeder
{
    public function run(): void
    {
        $exhibitor = Exhibitor::factory()->create([
            'first_name' => 'Label',
            'last_name' => 'Test',
            'full_name' => 'Label Test',
            'sort_name' => 'Test, Label',
        ]);

        ShowClass::each(function (ShowClass $class) use ($exhibitor) {
            Entry::create([
                'exhibitor_id' => $exhibitor->id,
                'show_class_id' => $class->id,
            ]);
        });
    }
}
