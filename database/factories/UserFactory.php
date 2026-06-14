<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Exhibitor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::Exhibitor,
            'phone' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
        ]);
    }

    public function judge(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Judge,
        ]);
    }

    public function helper(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Helper,
        ]);
    }

    public function exhibitor(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Exhibitor,
        ]);
    }

    public function steward(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Steward,
        ]);
    }

    public function withExhibitor(): static
    {
        return $this->exhibitor()->afterCreating(function (User $user): void {
            Exhibitor::factory()->create([
                'user_id' => $user->id,
            ]);
        });
    }
}
