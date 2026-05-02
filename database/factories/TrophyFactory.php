<?php

namespace Database\Factories;

use App\Models\Trophy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trophy>
 */
class TrophyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
