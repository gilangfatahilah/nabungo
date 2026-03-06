<?php

namespace App\Http\Controllers;

use App\Helpers\FilterParser;
use App\Helpers\QueryFilters;
use App\Http\Requests\Budget\StoreRequest;
use App\Http\Requests\Budget\UpdateRequest;
use App\Models\Account;
use App\Models\Budget;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BudgetController extends Controller
{


  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $schema = FilterParser::getFilterSchema(Budget::class);

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
      $end = Carbon::parse($request->get('end_month'))->endOfDay();

      $query->whereBetween('month', [$start, $end]);
    }

    $budgets = $query->orderBy('created_at', 'desc')
      ->paginate($request->get('per_page', 10))
      ->withQueryString();

    // Calculate usage and format expense data for frontend
    $budgets->getCollection()->transform(function ($budget) {
      $totalExpense = $budget->total_expense ?? 0;
      $budget->total_expense = $totalExpense;
      $budget->usage = $budget->calculateUsage($totalExpense);
      return $budget;
    });

    $filterSchema = FilterParser::prepareSchemaForFrontend($schema);

    return Inertia::render('budget/Index', [
      'budgets' => $budgets,
      'query' => $request->query(),
      'filters' => $filters,
      'filterSchema' => $filterSchema,
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreRequest $request)
  {
    $validated = $request->validated();
    Budget::create([
      'user_id' => Auth::id(),
      'category_id' => $validated['category_id'],
      'month' =>  Carbon::parse($validated['month'])->setTime(12, 0, 0),
      'amount' => $validated['amount']
    ]);

    return to_route('budget.index');
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateRequest $request, Budget $budget)
  {
    $this->authorize('update', $budget);
    $validated = $request->validated();

    if (isset($validated['month'])) {
      $validated['month'] = Carbon::parse($validated['month'])->setTime(12, 0, 0);
    }

    $budget->update($validated);
    return to_route('budget.index');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Budget $budget)
  {
    $this->authorize('delete', $budget);

    $budget->delete();
    return to_route('budget.index');
  }

  public function multipleDestroy(Request $request)
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'integer|exists:budgets,id',
    ]);

    Budget::whereIn('id', $request->input('ids'))->delete();

    return to_route('budget.index');
  }

  public function getBudgetsByCategory(Request $request)
  {
    $request->validate([
      'category_id' => 'required|integer|exists:categories,id',
      'month' => 'required|date_format:Y-m',
    ]);

    $month = Carbon::parse($request->get('month'))->setTime(12, 0, 0);
    $budgets = Budget::where('user_id', Auth::id())
      ->where('category_id', $request->get('category_id'))
      ->whereMonth('month', $month->month)
      ->whereYear('month', $month->year)
      ->get(['id', 'amount']);

    if ($budgets->isEmpty()) {
      return response()->json([
        'message' => 'No budgets found for this category in the specified month.',
      ], 404);
    }

    return response()->json([
      'data' => $budgets,
      'total' => $budgets->sum('amount'),
      'month' => $month->format('Y-m'),
      'category_id' => $request->get('category_id'),
    ]);
  }
}
