<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Debt;
use App\Models\Goal;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Get the date format expression based on database driver.
     */
    private function dateFormat(string $column, string $format): string
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            return "strftime('{$format}', {$column})";
        }

        if ($driver === 'pgsql') {
            $pgFormat = str_replace(
                ['%Y', '%m', '%d'],
                ['YYYY', 'MM', 'DD'],
                $format
            );
            return "to_char({$column}, '{$pgFormat}')";
        }

        return "DATE_FORMAT({$column}, '{$format}')";
    }

    /**
     * Get summary cards data for a given period.
     */
    public function getSummary(int $userId, Carbon $from, Carbon $to): array
    {
        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $expense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $net     = $income - $expense;
        $savingsRate = $income > 0 ? round(($net / $income) * 100, 1) : 0;

        // Compare with previous period of same length
        $periodDays = $from->diffInDays($to) + 1;
        $prevTo     = $from->copy()->subDay();
        $prevFrom   = $prevTo->copy()->subDays($periodDays - 1);

        $prevIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$prevFrom, $prevTo])
            ->sum('amount');

        $prevExpense = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$prevFrom, $prevTo])
            ->sum('amount');

        $prevNet         = $prevIncome - $prevExpense;
        $prevSavingsRate = $prevIncome > 0 ? round(($prevNet / $prevIncome) * 100, 1) : 0;

        $pctChange = fn($curr, $prev) => $prev != 0
            ? round((($curr - $prev) / abs($prev)) * 100, 1)
            : 0;

        return [
            'income' => [
                'value'  => round($income, 2),
                'change' => $pctChange($income, $prevIncome),
                'trend'  => $income >= $prevIncome ? 'up' : 'down',
            ],
            'expense' => [
                'value'  => round($expense, 2),
                'change' => $pctChange($expense, $prevExpense),
                'trend'  => $expense <= $prevExpense ? 'up' : 'down',
            ],
            'net' => [
                'value'  => round($net, 2),
                'change' => $pctChange($net, $prevNet),
                'trend'  => $net >= $prevNet ? 'up' : 'down',
            ],
            'savingsRate' => [
                'value'  => $savingsRate,
                'change' => round($savingsRate - $prevSavingsRate, 1),
                'trend'  => $savingsRate >= $prevSavingsRate ? 'up' : 'down',
            ],
        ];
    }

    /**
     * Get spending breakdown by category for a given period.
     */
    public function getCategoryBreakdown(int $userId, Carbon $from, Carbon $to): array
    {
        $rows = Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$from, $to])
            ->selectRaw('categories.id, categories.name, SUM(transactions.amount) as total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get();

        $totalExpense = $rows->sum('total');

        return $rows->map(fn($r) => [
            'category_id'   => $r->id,
            'category_name' => $r->name,
            'total'         => round($r->total, 2),
            'percentage'    => $totalExpense > 0
                ? round(($r->total / $totalExpense) * 100, 1)
                : 0,
        ])->values()->toArray();
    }

    /**
     * Get daily/weekly/monthly cash flow timeseries for a given period.
     *
     * @param string $groupBy  'daily' | 'weekly' | 'monthly'
     */
    public function getCashFlow(int $userId, Carbon $from, Carbon $to, string $groupBy = 'daily'): array
    {
        if ($groupBy === 'monthly') {
            $formatExpr = $this->dateFormat('transaction_date', '%Y-%m-01');
        } else {
            $formatExpr = $this->dateFormat('transaction_date', '%Y-%m-%d');
        }

        $rows = Transaction::query()
            ->where('user_id', $userId)
            ->whereIn('type', ['income', 'expense'])
            ->whereBetween('transaction_date', [$from, $to])
            ->selectRaw("
                {$formatExpr} as date,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            ")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        if ($groupBy === 'weekly') {
            // Group by ISO week
            $grouped = $rows->groupBy(fn($r) => Carbon::parse($r->date)->startOfWeek()->format('Y-m-d'));
            return $grouped->map(fn($items, $weekStart) => [
                'date'    => $weekStart,
                'income'  => round($items->sum('income'), 2),
                'expense' => round($items->sum('expense'), 2),
            ])->values()->toArray();
        }

        return $rows->map(fn($r) => [
            'date'    => $r->date,
            'income'  => round($r->income, 2),
            'expense' => round($r->expense, 2),
        ])->values()->toArray();
    }

    /**
     * Get budget vs actual spending for a given period (month-level).
     */
    public function getBudgetVsActual(int $userId, Carbon $from, Carbon $to): array
    {
        // Collect all months in range
        $months = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor->lte($to->copy()->startOfMonth())) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        // Get budgets for those months
        $budgets = Budget::query()
            ->with('category:id,name')
            ->where('user_id', $userId)
            ->whereIn(DB::raw($this->dateFormat('month', '%Y-%m')), $months)
            ->get();

        if ($budgets->isEmpty()) {
            return [];
        }

        // Aggregate actual expense per category in range
        $actuals = Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$from, $to])
            ->whereIn('transactions.category_id', $budgets->pluck('category_id')->unique())
            ->selectRaw('transactions.category_id, SUM(transactions.amount) as actual')
            ->groupBy('transactions.category_id')
            ->pluck('actual', 'category_id');

        $monthCount = count($months);

        // Group budgets by category, scale budget by period length
        $result = $budgets->groupBy('category_id')->map(function ($items) use ($actuals, $monthCount) {
            $category = $items->first()->category;
            // Use average monthly budget × number of months in the selected period
            $avgMonthlyBudget = $items->avg('amount');
            $budgeted = round($avgMonthlyBudget * $monthCount, 2);
            $actual   = round($actuals->get($category->id, 0), 2);
            $usage    = $budgeted > 0 ? round(($actual / $budgeted) * 100, 1) : 0;

            return [
                'category_id'   => $category->id,
                'category_name' => $category->name,
                'budgeted'      => round($budgeted, 2),
                'actual'        => $actual,
                'remaining'     => round($budgeted - $actual, 2),
                'usage'         => $usage,
                'status'        => $usage >= 100 ? 'over' : ($usage >= 80 ? 'warning' : 'ok'),
            ];
        })->values()->toArray();

        // Sort: over budget first, then by usage desc
        usort($result, fn($a, $b) => $b['usage'] <=> $a['usage']);

        return $result;
    }

    /**
     * Get top N transactions (income and expense) for a given period.
     */
    public function getTopTransactions(int $userId, Carbon $from, Carbon $to, int $limit = 10): array
    {
        $base = Transaction::query()
            ->with(['category:id,name', 'account:id,name'])
            ->where('user_id', $userId)
            ->whereBetween('transaction_date', [$from, $to]);

        $topIncome = (clone $base)
            ->where('type', 'income')
            ->orderByDesc('amount')
            ->limit($limit)
            ->get()
            ->map(fn($t) => [
                'id'               => $t->id,
                'type'             => $t->type,
                'amount'           => round($t->amount, 2),
                'description'      => $t->description,
                'transaction_date' => $t->transaction_date,
                'category'         => $t->category?->name,
                'account'          => $t->account?->name,
            ])->values()->toArray();

        $topExpense = (clone $base)
            ->where('type', 'expense')
            ->orderByDesc('amount')
            ->limit($limit)
            ->get()
            ->map(fn($t) => [
                'id'               => $t->id,
                'type'             => $t->type,
                'amount'           => round($t->amount, 2),
                'description'      => $t->description,
                'transaction_date' => $t->transaction_date,
                'category'         => $t->category?->name,
                'account'          => $t->account?->name,
            ])->values()->toArray();

        return [
            'income'  => $topIncome,
            'expense' => $topExpense,
        ];
    }

    /**
     * Get account balance trend using AccountHistory, grouped by month.
     */
    public function getAccountTrends(int $userId, Carbon $from, Carbon $to): array
    {
        $accounts = Account::where('user_id', $userId)
            ->orderByDesc('balance')
            ->get(['id', 'name', 'type', 'balance']);

        $formatExpr = $this->dateFormat('created_at', '%Y-%m-01');

        $histories = DB::table('account_histories')
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$from, $to->copy()->endOfDay()])
            ->selectRaw("account_id, {$formatExpr} as month, balance_after")
            ->orderBy('created_at')
            ->get()
            ->groupBy('account_id');

        $months = [];
        $cursor = $from->copy()->startOfMonth();
        while ($cursor->lte($to->copy()->startOfMonth())) {
            $months[] = $cursor->format('Y-m-d');
            $cursor->addMonth();
        }

        return $accounts->map(function ($account) use ($histories, $months) {
            $accountHistory = $histories->get($account->id, collect());

            // Last known balance per month
            $balanceByMonth = $accountHistory->groupBy('month')->map(fn($items) =>
                round($items->last()->balance_after, 2)
            );

            $trend = [];
            $lastKnown = null;
            foreach ($months as $month) {
                $val = $balanceByMonth->get($month, $lastKnown);
                $trend[] = ['date' => $month, 'balance' => $val ?? 0];
                if ($val !== null) $lastKnown = $val;
            }

            return [
                'account_id'   => $account->id,
                'account_name' => $account->name,
                'account_type' => $account->type,
                'current'      => round($account->balance, 2),
                'trend'        => $trend,
            ];
        })->values()->toArray();
    }

    /**
     * Get goal progress snapshot for the user.
     */
    public function getGoalSnapshot(int $userId): array
    {
        $goals = Goal::where('user_id', $userId)
            ->with('account:id,name')
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('created_at')
            ->get();

        return $goals->map(function ($goal) {
            $progress    = $goal->target_amount > 0
                ? round(($goal->saved_amount / $goal->target_amount) * 100, 1)
                : 0;
            $remaining   = max(0, $goal->target_amount - $goal->saved_amount);
            $dueDate     = $goal->due_date ? Carbon::parse($goal->due_date) : null;
            $daysLeft    = $dueDate ? Carbon::now()->diffInDays($dueDate, false) : null;

            // Estimate months to finish based on average monthly savings (last 3 months)
            $avgMonthlySavings = Transaction::where('account_id', $goal->account_id)
                ->where('type', 'income')
                ->where('transaction_date', '>=', Carbon::now()->subMonths(3))
                ->sum('amount') / 3;

            $monthsToFinish = ($avgMonthlySavings > 0 && $remaining > 0)
                ? ceil($remaining / $avgMonthlySavings)
                : null;

            return [
                'id'                => $goal->id,
                'title'             => $goal->title,
                'account'           => $goal->account?->name,
                'target_amount'     => round($goal->target_amount, 2),
                'saved_amount'      => round($goal->saved_amount, 2),
                'remaining'         => round($remaining, 2),
                'progress'          => $progress,
                'status'            => $goal->status,
                'due_date'          => $goal->due_date,
                'days_left'         => $daysLeft,
                'months_to_finish'  => $monthsToFinish,
            ];
        })->values()->toArray();
    }

    /**
     * Get debt and receivable summary.
     */
    public function getDebtSummary(int $userId): array
    {
        $debts = Debt::where('user_id', $userId)
            ->where('status', '!=', 'paid')
            ->get();

        $totalDebt       = $debts->where('type', 'debt')->sum('amount');
        $paidDebt        = $debts->where('type', 'debt')->sum('paid_amount');
        $totalReceivable = $debts->where('type', 'receivable')->sum('amount');
        $paidReceivable  = $debts->where('type', 'receivable')->sum('paid_amount');

        $overdue = $debts->filter(function ($d) {
            return $d->due_date && Carbon::parse($d->due_date)->isPast();
        })->values()->map(fn($d) => [
            'id'           => $d->id,
            'title'        => $d->title,
            'type'         => $d->type,
            'remaining'    => round($d->amount - $d->paid_amount, 2),
            'due_date'     => $d->due_date,
            'contact_name' => $d->contact_name,
        ])->toArray();

        return [
            'debt' => [
                'total'     => round($totalDebt, 2),
                'paid'      => round($paidDebt, 2),
                'remaining' => round($totalDebt - $paidDebt, 2),
            ],
            'receivable' => [
                'total'     => round($totalReceivable, 2),
                'paid'      => round($paidReceivable, 2),
                'remaining' => round($totalReceivable - $paidReceivable, 2),
            ],
            'overdue' => $overdue,
        ];
    }

    /**
     * Get all transactions for the period (for CSV export).
     */
    public function getTransactionsForExport(int $userId, Carbon $from, Carbon $to): \Illuminate\Database\Eloquent\Collection
    {
        return Transaction::query()
            ->with(['category:id,name', 'account:id,name', 'accountTarget:id,name'])
            ->where('user_id', $userId)
            ->whereBetween('transaction_date', [$from, $to])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }
}
