<?php

namespace Database\Factories;

use App\Models\PrizeLevel;
use App\Models\ShowClass;
use App\Models\ShowSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShowClass>
 */
class ShowClassFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'show_section_id' => ShowSection::factory(),
            'prize_level_id' => PrizeLevel::factory(),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'max_entries_per_exhibitor' => 3,
            'sort_order' => 0,
        ];
    }
}
