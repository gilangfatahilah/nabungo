<?php

namespace App\Http\Controllers;

use App\Helpers\FilterParser;
use App\Helpers\QueryFilters;
use App\Models\Account;
use App\Http\Requests\Account\StoreRequest;
use App\Http\Requests\Account\UpdateRequest;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AccountController extends Controller
{
  /**
   * Validate account owner.
   */
  protected function authorizeAccess(Account $account)
  {
    if ($account->user_id !== Auth::id()) {
      abort(403, 'Unauthorized');
    }
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $schema = FilterParser::getFilterSchema(Account::class);
    $filters = FilterParser::parseFilters($request->get('filters', []), $schema);

    $query = Account::query()
      ->where('user_id', Auth::id());

    if ($request->filled('search')) {
      $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
    }

    if (!empty($filters)) {
      $query = QueryFilters::apply($query, $filters, $schema);
    }

    $accounts = $query->orderBy('created_at', 'desc')
      ->paginate($request->get('per_page', 10))
      ->withQueryString();

    $filterSchema = FilterParser::prepareSchemaForFrontend($schema);

    return Inertia::render('account/Index', [
      'accounts' => $accounts,
      'filters' => $filters,
      'filterSchema' => $filterSchema,
      'query' => $request->query(),
    ]);
  }


  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreRequest $request)
  {
    $validated = $request->validated();
    Account::create([
      'user_id' => Auth::id(),
      'name' => $validated['name'],
      'type' => $validated['type'],
      'balance' => $validated['balance'] ?? 0,
      'notes' => $validated['notes'] ?? null,
    ]);

    return to_route('account.index');
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateRequest $request, Account $account)
  {
    $this->authorizeAccess($account);

    $account->update($request->validated());
    return to_route('account.index');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Account $account)
  {
    $this->authorizeAccess($account);

    $account->delete();

    return to_route('account.index');
  }

  /**
   * Remove the multiple resource from storage.
   */
  public function multipleDestroy(Request $request)
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'integer|exists:accounts,id',
    ]);

    Account::whereIn('id', $request->input('ids'))->delete();
    return to_route('account.index');
  }

  public function options(Request $request)
  {
    $query = Account::query()->select('id', 'name', 'balance');

    if ($request->filled('type')) {
      $query->whereIn('type', $request->input('type'));
    }

    $accounts = $query->get()->map(function ($account) {
      return [
        'label' => $account->name . " - (Rp" . number_format($account->balance, 2) . ")",
        'value' => $account->id
      ];
    });

    return response()->json([
      'success' => true,
      'data' => $accounts,
    ]);
  }

  public function transactionSummary(Account $account, Request $request)
  {
    $this->authorizeAccess($account);

    $month = $request->input('month', now()->month);
    $year = $request->input('year', now()->year);

    $totalIncome = Transaction::where('account_id', $account->id)
      ->where('type', 'income')
      ->whereMonth('transaction_date', $month)
      ->whereYear('transaction_date', $year)
      ->sum('amount');

    $totalExpense = Transaction::where('account_id', $account->id)
      ->where('type', 'expense')
      ->whereMonth('transaction_date', $month)
      ->whereYear('transaction_date', $year)
      ->sum('amount');

    $totalTransferIn = Transaction::where('account_target_id', $account->id)
      ->where('type', 'transfer')
      ->whereMonth('transaction_date', $month)
      ->whereYear('transaction_date', $year)
      ->sum('amount');

    $totalTransferOut = Transaction::where('account_id', $account->id)
      ->where('type', 'transfer')
      ->whereMonth('transaction_date', $month)
      ->whereYear('transaction_date', $year)
      ->sum('amount');

    return response()->json([
      'success' => true,
      'data' => [
        'income' => $totalIncome + $totalTransferIn,
        'expense' => $totalExpense + $totalTransferOut,
      ],
    ]);
  }
}
