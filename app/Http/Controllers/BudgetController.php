<?php

namespace App\Http\Controllers;

use App\Http\Requests\Budget\StoreRequest;
use App\Http\Requests\Budget\UpdateRequest;
use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BudgetController extends Controller
{
  use AuthorizesRequests;

  public function __construct(private BudgetService $service) {}

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $data = $this->service->getFilteredBudgets($request);

    return Inertia::render('budget/Index', [
      'budgets'      => $data['budgets'],
      'filters'      => $data['filters'],
      'filterSchema' => $data['filterSchema'],
      'query'        => $request->query(),
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreRequest $request)
  {
    $this->service->create($request->validated());

    return to_route('budget.index');
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateRequest $request, Budget $budget)
  {
    $this->authorize('update', $budget);

    $this->service->update($budget, $request->validated());

    return to_route('budget.index');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Budget $budget)
  {
    $this->authorize('delete', $budget);

    $this->service->delete($budget);

    return to_route('budget.index');
  }

  public function multipleDestroy(Request $request)
  {
    $request->validate([
      'ids'   => 'required|array',
      'ids.*' => 'integer|exists:budgets,id',
    ]);

    $this->service->deleteMany($request->input('ids'));

    return to_route('budget.index');
  }

  public function getBudgetsByCategory(Request $request)
  {
    $request->validate([
      'category_id' => 'required|integer|exists:categories,id',
      'month'       => 'required|date_format:Y-m',
    ]);

    $result = $this->service->getBudgetsByCategory(
      $request->integer('category_id'),
      $request->get('month')
    );

    if ($result['budgets']->isEmpty()) {
      return response()->json([
        'message' => 'No budgets found for this category in the specified month.',
      ], 404);
    }

    return response()->json([
      'data'        => $result['budgets'],
      'total'       => $result['total'],
      'month'       => $result['month'],
      'category_id' => $result['category_id'],
    ]);
  }
}

