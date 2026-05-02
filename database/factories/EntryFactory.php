<?php

namespace Database\Factories;

use App\Models\Entry;
use App\Models\Exhibitor;
use App\Models\ShowClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Entry>
 */
class EntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'show_class_id' => ShowClass::factory(),
            'exhibitor_id' => Exhibitor::factory(),
        ];
    }
}
