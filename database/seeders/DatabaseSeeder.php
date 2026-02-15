<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            // Production: hanya user demo tanpa Faker
            $this->seedProduction();
        } else {
            // Local/staging: seed lengkap dengan Faker
            $this->seedDevelopment();
        }
    }

    private function seedProduction(): void
    {
        // Hanya buat 1 user demo
        DB::table('users')->insert([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedDevelopment(): void
    {
        $faker = \Faker\Factory::create();

        $userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 🔹 Seed accounts with initial balance
        $accountIds = [];
        $accountBalances = []; // Track balances

        foreach (['Cash', 'Bank BCA', 'OVO', 'ShopeePay'] as $i => $accName) {
            $initialBalance = $faker->numberBetween(1000000, 5000000);
            $accountId = DB::table('accounts')->insertGetId([
                'user_id' => $userId,
                'name' => $accName,
                'type' => $i === 0 ? 'cash' : ($i === 1 ? 'bank' : 'ewallet'),
                'balance' => $initialBalance,
                'notes' => $faker->sentence(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $accountIds[] = $accountId;
            $accountBalances[$accountId] = $initialBalance;
        }

        // 🔹 Seed categories
        $categoryIds = [];
        $incomeCategories = [];
        $expenseCategories = [];

        foreach (['Salary' => 'income', 'Food' => 'expense', 'Transport' => 'expense', 'Investment' => 'income'] as $name => $type) {
            $categoryId = DB::table('categories')->insertGetId([
                'user_id' => $userId,
                'name' => $name,
                'type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $categoryIds[] = $categoryId;

            if ($type === 'income') {
                $incomeCategories[] = $categoryId;
            } else {
                $expenseCategories[] = $categoryId;
            }
        }

        // 🔹 Seed budgets
        foreach ($expenseCategories as $catId) {
            DB::table('budgets')->insert([
                'user_id' => $userId,
                'category_id' => $catId,
                'month' => now()->startOfMonth(),
                'amount' => $faker->numberBetween(500000, 5000000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 🔹 Seed transactions with proper balance calculation
        for ($i = 0; $i < 1000; $i++) {
            $type = $faker->randomElement(['income', 'expense', 'transfer']);
            $accountId = $faker->randomElement($accountIds);
            $amount = $faker->numberBetween(10000, 500000);

            $accountTargetId = null;
            $categoryId = null;

            // Set category based on type
            if ($type === 'income') {
                $categoryId = $faker->randomElement($incomeCategories);
            } elseif ($type === 'expense') {
                $categoryId = $faker->randomElement($expenseCategories);
            } elseif ($type === 'transfer') {
                // For transfer, select different target account
                $accountTargetId = $faker->randomElement(array_diff($accountIds, [$accountId]));
            }

            $transactionId = DB::table('transactions')->insertGetId([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'account_id' => $accountId,
                'account_target_id' => $accountTargetId,
                'type' => $type,
                'amount' => $amount,
                'description' => $faker->sentence(),
                'transaction_date' => $faker->dateTimeBetween('-1 years', 'now'),
                'proof_file' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 🔹 Create account_histories based on transaction type
            if ($type === 'transfer') {
                // Transfer: 2 histories (out from source, in to target)

                // 1. Debit from source account
                $balanceBefore = $accountBalances[$accountId];
                $balanceAfter = $balanceBefore - $amount;

                DB::table('account_histories')->insert([
                    'user_id' => $userId,
                    'account_id' => $accountId,
                    'transaction_id' => $transactionId,
                    'type' => 'out', // ✅ Fixed: use 'out' instead of 'debit'
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'notes' => 'Transfer to ' . DB::table('accounts')->where('id', $accountTargetId)->value('name'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $accountBalances[$accountId] = $balanceAfter;

                // 2. Credit to target account
                $balanceBefore = $accountBalances[$accountTargetId];
                $balanceAfter = $balanceBefore + $amount;

                DB::table('account_histories')->insert([
                    'user_id' => $userId,
                    'account_id' => $accountTargetId,
                    'transaction_id' => $transactionId,
                    'type' => 'in', // ✅ Fixed: use 'in' instead of 'credit'
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'notes' => 'Transfer from ' . DB::table('accounts')->where('id', $accountId)->value('name'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $accountBalances[$accountTargetId] = $balanceAfter;

            } else {
                // Income or Expense: 1 history
                $balanceBefore = $accountBalances[$accountId];

                if ($type === 'income') {
                    $balanceAfter = $balanceBefore + $amount;
                    $historyType = 'in'; // ✅ Fixed: use 'in' instead of 'credit'
                } else { // expense
                    $balanceAfter = $balanceBefore - $amount;
                    $historyType = 'out'; // ✅ Fixed: use 'out' instead of 'debit'
                }

                DB::table('account_histories')->insert([
                    'user_id' => $userId,
                    'account_id' => $accountId,
                    'transaction_id' => $transactionId,
                    'type' => $historyType,
                    'amount' => $amount, // ✅ Fixed: use same amount as transaction
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'notes' => $type === 'income' ? 'Income received' : 'Expense payment',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $accountBalances[$accountId] = $balanceAfter;
            }
        }

        // 🔹 Update final account balances
        foreach ($accountBalances as $accountId => $balance) {
            DB::table('accounts')
                ->where('id', $accountId)
                ->update(['balance' => $balance]);
        }
    }
}
