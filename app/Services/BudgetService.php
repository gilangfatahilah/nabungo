<?php

namespace App\Services;

use App\Helpers\FilterParser;
use App\Helpers\QueryFilters;
use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class BudgetService
{
    /**
     * Retrieve a paginated, filtered list of budgets for the authenticated user.
     *
     * @return array{ budgets: LengthAwarePaginator, filters: array, filterSchema: array }
     */
    public function getFilteredBudgets(Request $request): array
    {
        $schema  = FilterParser::getFilterSchema(Budget::class);
        $filters = FilterParser::parseFilters($request->get('filters', []), $schema);

        $query = Budget::query()
            ->with('category')
            ->withExpenseData()
            ->where('user_id', Auth::id());

        if (!empty($filters)) {
            $query = QueryFilters::apply($query, $filters, $schema);
        }

        if ($request->filled('search')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->get('search') . '%');
            });
        }

        if ($request->filled('start_month') && $request->filled('end_month')) {
            $start = Carbon::parse($request->get('start_month'))->startOfDay();
            $end   = Carbon::parse($request->get('end_month'))->endOfDay();
            $query->whereBetween('month', [$start, $end]);
        }

        $budgets = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        $budgets->getCollection()->transform(function ($budget) {
            $totalExpense          = $budget->total_expense ?? 0;
            $budget->total_expense = $totalExpense;
            $budget->usage         = $budget->calculateUsage($totalExpense);
            return $budget;
        });

        return [
            'budgets'      => $budgets,
            'filters'      => $filters,
            'filterSchema' => FilterParser::prepareSchemaForFrontend($schema),
        ];
    }

    /**
     * Create a new budget for the authenticated user.
     */
    public function create(array $data): Budget
    {
        return Budget::create([
            'user_id'     => Auth::id(),
            'category_id' => $data['category_id'],
            'month'       => Carbon::parse($data['month'])->setTime(12, 0, 0),
            'amount'      => $data['amount'],
        ]);
    }

    /**
     * Update the given budget.
     */
    public function update(Budget $budget, array $data): Budget
    {
        if (isset($data['month'])) {
            $data['month'] = Carbon::parse($data['month'])->setTime(12, 0, 0);
        }

        $budget->update($data);
        return $budget;
    }

    /**
     * Delete the given budget.
     */
    public function delete(Budget $budget): void
    {
        $budget->delete();
    }

    /**
     * Delete multiple budgets by IDs.
     */
    public function deleteMany(array $ids): void
    {
        Budget::whereIn('id', $ids)->delete();
    }

    /**
     * Get budgets for a specific category and month.
     */
    public function getBudgetsByCategory(int $categoryId, string $month): array
    {
        $parsedMonth = Carbon::parse($month)->setTime(12, 0, 0);

        $budgets = Budget::where('user_id', Auth::id())
            ->where('category_id', $categoryId)
            ->whereMonth('month', $parsedMonth->month)
            ->whereYear('month', $parsedMonth->year)
            ->get(['id', 'amount']);

        return [
            'budgets'     => $budgets,
            'total'       => $budgets->sum('amount'),
            'month'       => $parsedMonth->format('Y-m'),
            'category_id' => $categoryId,
        ];
    }
}
