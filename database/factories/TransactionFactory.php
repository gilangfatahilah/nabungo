<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense', 'transfer']);
        $user = User::factory();

        $state = [
            'user_id' => $user,
            'account_id' => Account::factory()->state(['user_id' => $user]),
            'type' => $type,
            'amount' => fake()->randomFloat(2, 10, 1000),
            'description' => fake()->optional()->sentence(),
            'transaction_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
        ];

        // Add category for income/expense
        if (in_array($type, ['income', 'expense'])) {
            $state['category_id'] = Category::factory()->state([
                'user_id' => $user,
                'type' => $type,
            ]);
        }

        // Add target account for transfer
        if ($type === 'transfer') {
            $state['account_target_id'] = Account::factory()->state(['user_id' => $user]);
        }

        return $state;
    }

    /**
     * Indicate that the transaction is an income.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'category_id' => Category::factory()->income()->state([
                'user_id' => $attributes['user_id'],
            ]),
            'account_target_id' => null,
            'description' => fake()->randomElement([
                'Salary payment',
                'Freelance project completed',
                'Bonus received',
                'Investment return',
            ]),
        ]);
    }

    /**
     * Indicate that the transaction is an expense.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'category_id' => Category::factory()->expense()->state([
                'user_id' => $attributes['user_id'],
            ]),
            'account_target_id' => null,
            'description' => fake()->randomElement([
                'Grocery shopping',
                'Restaurant dinner',
                'Gas station',
                'Online shopping',
                'Utility bill payment',
            ]),
        ]);
    }

    /**
     * Indicate that the transaction is a transfer.
     */
    public function transfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'transfer',
            'category_id' => null,
            'account_target_id' => Account::factory()->state([
                'user_id' => $attributes['user_id'],
            ]),
            'description' => 'Transfer between accounts',
        ]);
    }

    /**
     * Set a specific amount for the transaction.
     */
    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }

    /**
     * Set a specific date for the transaction.
     */
    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_date' => $date,
        ]);
    }

    /**
     * Set today as the transaction date.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_date' => now()->format('Y-m-d'),
        ]);
    }

    /**
     * Set a specific description.
     */
    public function withDescription(string $description): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => $description,
        ]);
    }
}
