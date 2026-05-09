<?php

namespace Database\Factories;

use App\Models\Exhibitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exhibitor>
 */
class ExhibitorFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'full_name' => "{$firstName} {$lastName}",
            'sort_name' => "{$lastName}, {$firstName}",
            'email' => fake()->optional()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'address' => fake()->optional()->address(),
            'type' => 'adult',
            'is_resident' => false,
            'has_paid' => false,
            'amount_paid_pence' => 0,
            'is_novice' => true,
        ];
    }

    public function adult(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'adult']);
    }

    public function junior(): static
    {
        return $this->state(fn (array $attributes) => ['type' => 'junior']);
    }

    public function resident(): static
    {
        return $this->state(fn (array $attributes) => ['is_resident' => true]);
    }

    public function nonResident(): static
    {
        return $this->state(fn (array $attributes) => ['is_resident' => false]);
    }

    public function novice(): static
    {
        return $this->state(fn (array $attributes) => ['is_novice' => true]);
    }

    public function notNovice(): static
    {
        return $this->state(fn (array $attributes) => ['is_novice' => false]);
    }
}
