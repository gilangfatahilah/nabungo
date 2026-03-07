<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);

        $incomeCategories = [
            'Salary',
            'Freelance',
            'Business Income',
            'Investment Returns',
            'Rental Income',
            'Bonus',
            'Gift',
            'Other Income',
        ];

        $expenseCategories = [
            'Food & Dining',
            'Transportation',
            'Shopping',
            'Entertainment',
            'Bills & Utilities',
            'Healthcare',
            'Education',
            'Rent/Mortgage',
            'Insurance',
            'Groceries',
            'Travel',
            'Personal Care',
            'Gifts & Donations',
            'Other Expense',
        ];

        $name = $type === 'income'
            ? fake()->randomElement($incomeCategories)
            : fake()->randomElement($expenseCategories);

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'type' => $type,
        ];
    }

    /**
     * Indicate that the category is for income.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
            'name' => fake()->randomElement([
                'Salary',
                'Freelance',
                'Business Income',
                'Investment Returns',
                'Rental Income',
            ]),
        ]);
    }

    /**
     * Indicate that the category is for expense.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
            'name' => fake()->randomElement([
                'Food & Dining',
                'Transportation',
                'Shopping',
                'Entertainment',
                'Bills & Utilities',
            ]),
        ]);
    }

    /**
     * Create a category with a specific name.
     */
    public function withName(string $name): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => $name,
        ]);
    }
}
