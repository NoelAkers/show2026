<?php

namespace Database\Factories;

use App\Models\ShowSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShowSection>
 */
class ShowSectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'sort_order' => 0,
        ];
    }
}
