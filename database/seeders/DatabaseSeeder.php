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
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 🔹 Seed accounts
        $accountIds = [];
        foreach (['Cash', 'Bank BCA', 'OVO', 'ShopeePay'] as $i => $accName) {
            $accountIds[] = DB::table('accounts')->insertGetId([
                'user_id' => $userId,
                'name' => $accName,
                'type' => $i === 0 ? 'cash' : ($i === 1 ? 'bank' : 'ewallet'),
                'balance' => 0,
                'notes' => $faker->sentence(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 🔹 Seed categories
        $categoryIds = [];
        foreach (['Salary' => 'income', 'Food' => 'expense', 'Transport' => 'expense', 'Investment' => 'income'] as $name => $type) {
            $categoryIds[] = DB::table('categories')->insertGetId([
                'user_id' => $userId,
                'name' => $name,
                'type' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 🔹 Seed budgets
        foreach ($categoryIds as $catId) {
            DB::table('budgets')->insert([
                'user_id' => $userId,
                'category_id' => $catId,
                'month' => now()->startOfMonth(),
                'amount' => $faker->numberBetween(500000, 5000000),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 🔹 Seed transactions (uncomment jika perlu test data banyak)
        // for ($i = 0; $i < 5000; $i++) {
        //     $type = $faker->randomElement(['income', 'expense', 'transfer']);
        //     $accountId = $faker->randomElement($accountIds);
        //     $accountTargetId = $type === 'transfer' ? $faker->randomElement(array_diff($accountIds, [$accountId])) : null;

        //     $transactionId = DB::table('transactions')->insertGetId([
        //         'user_id' => $userId,
        //         'category_id' => $type !== 'transfer' ? $faker->randomElement($categoryIds) : null,
        //         'account_id' => $accountId,
        //         'account_target_id' => $accountTargetId,
        //         'type' => $type,
        //         'amount' => $faker->numberBetween(10000, 2000000),
        //         'description' => $faker->sentence(),
        //         'transaction_date' => $faker->dateTimeBetween('-1 years', 'now'),
        //         'proof_file' => null,
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);

        //     // 🔹 Seed account_histories
        //     DB::table('account_histories')->insert([
        //         'user_id' => $userId,
        //         'account_id' => $accountId,
        //         'transaction_id' => $transactionId,
        //         'type' => $type === 'income' ? 'credit' : 'debit',
        //         'amount' => $faker->numberBetween(10000, 2000000),
        //         'balance_before' => $faker->numberBetween(100000, 5000000),
        //         'balance_after' => $faker->numberBetween(500000, 10000000),
        //         'notes' => $faker->sentence(),
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ]);
        // }
    }
}
