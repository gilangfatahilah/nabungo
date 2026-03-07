<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement([
                'Cash Wallet',
                'Main Bank Account',
                'Savings Account',
                'Emergency Fund',
                'Investment Account',
            ]),
            'type' => fake()->randomElement(['cash', 'bank', 'ewallet', 'asset', 'liability', 'goal']),
            'balance' => fake()->randomFloat(2, 0, 10000),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    /**
     * Indicate that the account is a cash account.
     */
    public function cash(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'cash',
            'name' => 'Cash Wallet',
        ]);
    }

    /**
     * Indicate that the account is a bank account.
     */
    public function bank(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'bank',
            'name' => fake()->company() . ' Bank Account',
        ]);
    }

    /**
     * Indicate that the account is an e-wallet.
     */
    public function ewallet(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'ewallet',
            'name' => fake()->randomElement(['GoPay', 'OVO', 'Dana', 'ShopeePay']),
        ]);
    }

    /**
     * Indicate that the account is an asset.
     */
    public function asset(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'asset',
            'name' => fake()->randomElement(['Real Estate', 'Investment Portfolio', 'Gold', 'Vehicle']),
        ]);
    }

    /**
     * Indicate that the account is a liability.
     */
    public function liability(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'liability',
            'name' => fake()->randomElement(['Credit Card', 'Loan', 'Mortgage']),
            'balance' => fake()->randomFloat(2, -10000, 0),
        ]);
    }

    /**
     * Indicate that the account is a goal account.
     */
    public function goal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'goal',
            'name' => '[Goal] ' . fake()->randomElement(['New Car', 'Vacation', 'Emergency Fund', 'House Down Payment']),
        ]);
    }

    /**
     * Indicate that the account has a specific balance.
     */
    public function withBalance(float $balance): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $balance,
        ]);
    }
}
