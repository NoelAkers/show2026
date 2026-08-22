<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Exhibitor;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'exhibitor_id' => Exhibitor::factory(),
            'amount_pence' => fake()->numberBetween(50, 2000),
            'type' => TransactionType::CashReceipt,
        ];
    }

    public function cashReceipt(): static
    {
        return $this->state(fn (array $attributes) => ['type' => TransactionType::CashReceipt]);
    }

    public function cashPayment(): static
    {
        return $this->state(fn (array $attributes) => ['type' => TransactionType::CashPayment]);
    }

    public function cardPayment(): static
    {
        return $this->state(fn (array $attributes) => ['type' => TransactionType::CardPayment]);
    }
}
