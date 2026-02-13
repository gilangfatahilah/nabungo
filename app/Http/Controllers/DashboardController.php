<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Get the date format expression based on database driver.
     * Supports MySQL and SQLite.
     */
    private function getDateFormatExpression(string $column, string $format): string
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite uses strftime
            return "strftime('{$format}', {$column})";
        }

        // MySQL/MariaDB uses DATE_FORMAT
        return "DATE_FORMAT({$column}, '{$format}')";
    }

    /**
     * Display the financial dashboard.
     */
    public function index()
    {
        $userId = Auth::id();
        $now = Carbon::now();
        $currentMonth = $now->copy()->startOfMonth();
        $previousMonth = $now->copy()->subMonth()->startOfMonth();

        // Get summary cards data
        $cardData = $this->getCardData($userId, $currentMonth, $previousMonth);

        // Get networth chart data (last 12 months)
        $networthData = $this->getNetworthData($userId);

        // Get account balance distribution
        $accountData = $this->getAccountData($userId);

        // Get income vs expense comparison (last 12 months)
        $incomeExpenseData = $this->getIncomeExpenseData($userId);

        return Inertia::render('dashboard/Index', [
            'cardData' => $cardData,
            'networthData' => $networthData,
            'accountData' => $accountData,
            'incomeExpenseData' => $incomeExpenseData,
        ]);
    }

    /**
     * Get data for summary cards.
     */
    private function getCardData(int $userId, Carbon $currentMonth, Carbon $previousMonth): array
    {
        // Total Balance (Net Worth)
        $totalBalance = Account::where('user_id', $userId)->sum('balance');

        // Previous month total balance (approximate from transactions)
        $currentMonthNetChange = Transaction::where('user_id', $userId)
            ->where('transaction_date', '>=', $currentMonth)
            ->selectRaw("
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) -
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as net_change
            ")
            ->value('net_change') ?? 0;

        $previousBalance = $totalBalance - $currentMonthNetChange;
        $balanceChange = $previousBalance > 0
            ? round((($totalBalance - $previousBalance) / $previousBalance) * 100, 1)
            : 0;

        // Current month income
        $currentIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->where('transaction_date', '>=', $currentMonth)
            ->sum('amount');

        $previousIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$previousMonth, $currentMonth])
            ->sum('amount');

        $incomeChange = $previousIncome > 0
            ? round((($currentIncome - $previousIncome) / $previousIncome) * 100, 1)
            : 0;

        // Current month expense
        $currentExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $currentMonth)
            ->sum('amount');

        $previousExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$previousMonth, $currentMonth])
            ->sum('amount');

        $expenseChange = $previousExpense > 0
            ? round((($currentExpense - $previousExpense) / $previousExpense) * 100, 1)
            : 0;

        // Savings rate
        $savingsRate = $currentIncome > 0
            ? round((($currentIncome - $currentExpense) / $currentIncome) * 100, 1)
            : 0;

        $previousSavingsRate = $previousIncome > 0
            ? round((($previousIncome - $previousExpense) / $previousIncome) * 100, 1)
            : 0;

        $savingsRateChange = $savingsRate - $previousSavingsRate;

        return [
            'totalBalance' => [
                'value' => $totalBalance,
                'change' => $balanceChange,
                'trend' => $balanceChange >= 0 ? 'up' : 'down',
            ],
            'income' => [
                'value' => $currentIncome,
                'change' => $incomeChange,
                'trend' => $incomeChange >= 0 ? 'up' : 'down',
            ],
            'expense' => [
                'value' => $currentExpense,
                'change' => $expenseChange,
                'trend' => $expenseChange <= 0 ? 'up' : 'down', // Lower expense is better
            ],
            'savingsRate' => [
                'value' => $savingsRate,
                'change' => $savingsRateChange,
                'trend' => $savingsRateChange >= 0 ? 'up' : 'down',
            ],
        ];
    }

    /**
     * Get networth trend data for the last 12 months.
     */
    private function getNetworthData(int $userId): array
    {
        $months = collect();
        $now = Carbon::now();

        // Get current total balance
        $currentBalance = Account::where('user_id', $userId)->sum('balance');

        // Build monthly data going backwards
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();

            $months->push([
                'date' => $monthStart->format('Y-m-d'),
                'month' => $monthStart->format('M Y'),
            ]);
        }

        // Calculate net worth for each month by working backwards from current balance
        $result = [];
        $runningBalance = $currentBalance;

        // Get all transactions grouped by month
        $dateFormat = $this->getDateFormatExpression('transaction_date', '%Y-%m');
        $transactions = Transaction::where('user_id', $userId)
            ->where('transaction_date', '>=', $now->copy()->subMonths(11)->startOfMonth())
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw("
                {$dateFormat} as month,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            ")
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // Calculate networth for each month (working backwards)
        $monthlyNetworthReverse = [];
        for ($i = 0; $i < 12; $i++) {
            $monthKey = $now->copy()->subMonths($i)->format('Y-m');
            $monthData = $transactions->get($monthKey);

            $monthlyNetworthReverse[$i] = $runningBalance;

            if ($monthData) {
                // Subtract the net change of this month to get previous month's balance
                $netChange = $monthData->income - $monthData->expense;
                $runningBalance -= $netChange;
            }
        }

        // Reverse to get chronological order
        for ($i = 11; $i >= 0; $i--) {
            $monthInfo = $months[11 - $i];
            $result[] = [
                'date' => $monthInfo['date'],
                'networth' => round($monthlyNetworthReverse[$i], 2),
            ];
        }

        return $result;
    }

    /**
     * Get account balance distribution data.
     */
    private function getAccountData(int $userId): array
    {
        $accounts = Account::where('user_id', $userId)
            ->where('balance', '>', 0)
            ->orderByDesc('balance')
            ->get(['id', 'name', 'type', 'balance']);

        $totalBalance = $accounts->sum('balance');

        return $accounts->map(function ($account) use ($totalBalance) {
            return [
                'id' => $account->id,
                'name' => $account->name,
                'type' => $account->type,
                'balance' => round($account->balance, 2),
                'percentage' => $totalBalance > 0
                    ? round(($account->balance / $totalBalance) * 100, 1)
                    : 0,
            ];
        })->values()->toArray();
    }

    /**
     * Get income vs expense comparison data for the last 12 months.
     */
    private function getIncomeExpenseData(int $userId): array
    {
        $now = Carbon::now();
        $startDate = $now->copy()->subMonths(11)->startOfMonth();

        $dateFormat = $this->getDateFormatExpression('transaction_date', '%Y-%m-01');
        $transactions = Transaction::where('user_id', $userId)
            ->where('transaction_date', '>=', $startDate)
            ->whereIn('type', ['income', 'expense'])
            ->selectRaw("
                {$dateFormat} as date,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            ")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $result = [];
        for ($i = 11; $i >= 0; $i--) {
            $monthDate = $now->copy()->subMonths($i)->startOfMonth()->format('Y-m-d');
            $data = $transactions->get($monthDate);

            $result[] = [
                'date' => $monthDate,
                'income' => $data ? round($data->income, 2) : 0,
                'expense' => $data ? round($data->expense, 2) : 0,
            ];
        }

        return $result;
    }
}
