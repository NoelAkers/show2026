<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\Result;
use App\Models\ShowClass;
use Illuminate\Database\Seeder;

class ResultSeeder extends Seeder
{
    public function run(): void
    {
        Result::truncate();

        $placements = ['1st', '2nd', '3rd', 'highly_commended'];

        ShowClass::has('entries')->with('entries')->each(function (ShowClass $class) use ($placements): void {
            $class->entries->shuffle()->take(4)->each(function (Entry $entry, int $index) use ($placements): void {
                Result::create([
                    'entry_id' => $entry->id,
                    'placement' => $placements[$index],
                ]);
            });
        });
    }
}
