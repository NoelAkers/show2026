<?php

namespace Database\Factories;

use App\Models\PrizeLevel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrizeLevel>
 */
class PrizeLevelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'first_place_pence' => 100,
            'second_place_pence' => 50,
            'third_place_pence' => 25,
        ];
    }
}
