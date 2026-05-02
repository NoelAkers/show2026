<?php

namespace Database\Factories;

use App\Models\Entry;
use App\Models\Result;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Result>
 */
class ResultFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entry_id' => Entry::factory(),
            'entered_by_user_id' => null,
            'placement' => fake()->randomElement(['1st', '2nd', '3rd', 'highly_commended', null]),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
