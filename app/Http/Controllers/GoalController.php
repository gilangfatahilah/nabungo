<?php

namespace App\Http\Controllers;

use App\Helpers\FilterParser;
use App\Helpers\QueryFilters;
use App\Http\Requests\Goal\StoreRequest;
use App\Http\Requests\Goal\UpdateRequest;
use App\Models\Account;
use App\Models\Goal;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GoalController extends Controller
{
  public function __construct(private TransactionService $transactionService) {}

  /**
   * Validate account owner.
   */
  protected function authorizeAccess(Goal $goal)
  {
    if ($goal->user_id !== Auth::id()) {
      abort(403, 'Unauthorized');
    }
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $schema = FilterParser::getFilterSchema(Goal::class);

    $filters = FilterParser::parseFilters($request->get('filters', []), $schema);

    $query = Goal::query()
      ->with('account:id,name,type,balance')->where('user_id', Auth::id());

    if (!empty($filters)) {
      $query = QueryFilters::apply($query, $filters, $schema);
    }

    if ($request->filled('search')) {
      $query->where('title', 'LIKE', '%' . $request->get('search') . '%');
    }

    $goals = $query->orderBy('created_at', 'desc')
      ->paginate($request->get('per_page', 10))
      ->withQueryString();

    $filterSchema = FilterParser::prepareSchemaForFrontend($schema);

    return Inertia::render('goal/Index', [
      'goals' => $goals,
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
    DB::beginTransaction();
    try {
      $validated = $request->validated();

      $account = Account::create([
        'user_id' => Auth::id(),
        'name'    => '[Goal] ' . $validated['title'],
        'type'    => 'goal',
        'balance' => 0,
      ]);

      Goal::create([
        'user_id'       => Auth::id(),
        'account_id'    => $account->id,
        'title'         => $validated['title'],
        'target_amount' => $validated['target_amount'],
        'saved_amount'  => 0,
        'due_date'      => $validated['due_date'],
        'notes'         => $validated['notes'] ?? null,
        'status'        => 'ongoing',
      ]);

      DB::commit();
      return to_route('goal.index');
    } catch (\Throwable $th) {
      DB::rollBack();
      report($th);
      dd($th->getMessage());
      return back()->withErrors(['error' => 'Gagal membuat goal.']);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateRequest $request, Goal $goal)
  {
    $this->authorizeAccess($goal);

    DB::beginTransaction();
    try {
      $validated = $request->validated();

      $goal->update([
        'title'         => $validated['title'] ?? $goal->title,
        'target_amount' => $validated['target_amount'] ?? $goal->target_amount,
        'due_date'      => $validated['due_date'] ?? $goal->due_date,
        'notes'         => $validated['notes'] ?? $goal->notes,
        'status'        => $validated['status'] ?? $goal->status,
      ]);

      DB::commit();
      return to_route('goal.index');
    } catch (\Throwable $th) {
      DB::rollBack();
      report($th);
      return back()->withErrors(['error' => 'Gagal memperbarui goal.']);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Goal $goal)
  {
    $this->authorizeAccess($goal);

    DB::beginTransaction();
    try {
      $fundTransactions = $goal->incomingTransactions()->get();

      foreach ($fundTransactions as $transaction) {
        $this->transactionService->rollbackBalance($transaction);
        $transaction->delete();
      }

      if ($goal->account) {
        $goal->account->delete();
      }

      $goal->delete();

      DB::commit();
      return to_route('goal.index');
    } catch (\Throwable $th) {
      DB::rollBack();
      report($th);
      return back()->withErrors(['error' => 'Gagal menghapus goal.']);
    }
  }

  public function multipleDestroy(Request $request)
  {
    $ids = $request->input('ids', []);

    if (empty($ids)) {
      return back()->withErrors(['error' => 'Tidak ada goals yang dipilih.']);
    }

    DB::beginTransaction();
    try {
      $goals = Goal::whereIn('id', $ids)
        ->where('user_id', Auth::id())
        ->get();

      foreach ($goals as $goal) {
        foreach ($goal->incomingTransactions as $transaction) {
          $this->transactionService->rollbackBalance($transaction);
          $transaction->delete();
        }

        if ($goal->account) {
          $goal->account->delete();
        }

        $goal->delete();
      }

      DB::commit();
      return to_route('goal.index');
    } catch (\Throwable $th) {
      DB::rollBack();
      report($th);
      return back()->withErrors(['error' => 'Gagal menghapus goals.']);
    }
  }
}
