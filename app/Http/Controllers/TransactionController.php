<?php

namespace App\Http\Controllers;

use App\Helpers\FilterParser;
use App\Http\Requests\Transaction\StoreRequest;
use App\Http\Requests\Transaction\UpdateRequest;
use App\Models\Account;
use App\Models\Transaction;
use App\Helpers\QueryFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TransactionController extends Controller
{
  /**
   * Update Balance Helper for each transactions.
   * @param array $data
   * @return void
   */
  private function updateBalances(array $data): void
  {
    $account = Account::find($data['account_id']);

    if ($data['type'] === 'income') {
      $account->increment('balance', $data['amount']);
    }

    if ($data['type'] === 'expense') {
      $account->decrement('balance', $data['amount']);
    }

    if ($data['type'] === 'transfer') {
      $accountTarget = Account::find($data['account_target_id']);

      $account->decrement('balance', $data['amount']);
      $accountTarget->increment('balance', $data['amount']);
    }
  }

  /**
   *
   * @param \App\Models\Transaction $transaction
   * @return void
   */
  private function rollbackBalance(Transaction $transaction): void
  {
    $account = Account::find($transaction->account_id);
    $targetAccount = $transaction->type === 'transfer' && $transaction->account_target_id
      ? Account::find($transaction->account_target_id)
      : null;

    if ($transaction->type === 'income') {
      $account->decrement('balance', $transaction->amount);
    }

    if ($transaction->type === 'expense') {
      $account->increment('balance', $transaction->amount);
    }

    if ($transaction->type === 'transfer') {
      $account->increment('balance', $transaction->amount);
      $targetAccount?->decrement('balance', $transaction->amount);
    }
  }

  /**
   * Display a listing of transactions with filters
   */
  public function index(Request $request)
  {
    try {
      $schema = FilterParser::getFilterSchema(Transaction::class);

      $filters = FilterParser::parseFilters(
        $request->get('filters', []),
        $schema
      );

      $query = Transaction::query()
        ->with([
          'account:id,name',
          'accountTarget:id,name',
          'category:id,name'
        ])
        ->where('user_id', auth()->id());

      if (!empty($filters)) {
        $query = QueryFilters::apply($query, $filters, $schema);
      }

      $transactions = $query
        ->orderBy('created_at', 'desc')
        ->paginate($request->get('per_page', 10))
        ->withQueryString();

      $filterSchema = FilterParser::prepareFilterSchemaForFrontend($schema);

      return Inertia::render('transaction/Index', [
        'transactions' => $transactions,
        'filters' => $filters,
        'filterSchema' => $filterSchema,
        'query' => $request->query(),
        'meta' => [
          'total_filters' => count($filters),
          'has_filters' => !empty($filters),
        ]
      ]);
    } catch (ValidationException $e) {
      return back()->withErrors([
        'filters' => 'Invalid filter format: ' . $e->getMessage()
      ]);
    } catch (\Exception $e) {
      Log::error('Transaction filter error: ' . $e->getMessage(), [
        'filters' => $request->get('filters', []),
        'user_id' => auth()->id()
      ]);

      return back()->withErrors([
        'filters' => 'An error occurred while processing filters.'
      ]);
    }
  }


  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreRequest $request)
  {
    $validated = $request->validated();
    $account = Account::find($validated['account_id']);

    if (
      ($validated['type'] === 'expense' || $validated['type'] === 'transfer')
      && $account->balance < $validated['amount']
    ) {
      return back()->withErrors(['error' => 'Saldo akun tidak mencukupi untuk melakukan transaksi.']);
    }

    if ($validated['type'] === 'transfer' && $validated['account_id'] == $validated['account_target_id']) {
      return back()->withErrors(['error' => 'Akun sumber dan tujuan tidak boleh sama.']);
    }

    DB::beginTransaction();
    try {
      Transaction::create([
        'user_id' => Auth::id(),
        'category_id' => $validated['type'] !== 'transfer' ? $validated['category_id'] : null,
        'account_id' => $validated['account_id'],
        'account_target_id' => $validated['type'] === 'transfer' ? $validated['account_target_id'] : null,
        'type' => $validated['type'],
        'amount' => $validated['amount'],
        'transaction_date' => $validated['transaction_date'],
        'description' =>  $validated['description']
      ]);

      $this->updateBalances($validated);

      DB::commit();
      return to_route('transaction.index');
    } catch (\Throwable $th) {
      DB::rollBack();
      report($th);
      return back()->withErrors(['error' => 'Gagal menyimpan  transaksi.']);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateRequest $request, Transaction $transaction)
  {
    $validated = $request->validated();

    DB::beginTransaction();
    try {
      $this->rollbackBalance($transaction);

      $newAccount = Account::findOrFail($validated['account_id']);

      if (
        in_array($validated['type'], ['expense', 'transfer']) &&
        $newAccount->balance < $validated['amount']
      ) {
        DB::rollBack();
        return back()->withErrors(['error' => 'Saldo akun tidak mencukupi untuk melakukan update transaksi.']);
      }

      $transaction->update([
        'category_id' => $validated['category_id'],
        'account_id' => $validated['account_id'],
        'account_target_id' => $validated['type'] === 'transfer' ? $validated['account_target_id'] : null,
        'type' => $validated['type'],
        'amount' => $validated['amount'],
        'transaction_date' => $validated['transaction_date'],
        'description' => $validated['description']
      ]);

      $this->updateBalances($validated);

      DB::commit();
      return to_route('transaction.index');
    } catch (\Throwable $th) {
      DB::rollBack();
      report($th);
      return back()->withErrors(['error' => 'Gagal memperbarui transaksi.']);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Transaction $transaction)
  {
    DB::beginTransaction();
    try {
      $this->rollbackBalance($transaction);
      $transaction->delete();

      DB::commit();
      return to_route('transaction.index');
    } catch (\Throwable $th) {
      DB::rollBack();
      report($th);
      return back()->withErrors(['error' => 'Gagal menghapus transaksi.']);
    }
  }

  /**
   * Remove multiple resources from storage.
   */
  public function multipleDestroy(Request $request)
  {
    $transactionIds = $request->input('ids', []);

    if (empty($transactionIds)) {
      return back()->withErrors(['error' => 'Tidak ada transaksi yang dipilih.']);
    }

    DB::beginTransaction();
    try {
      foreach ($transactionIds as $id) {
        $transaction = Transaction::findOrFail($id);
        $this->rollbackBalance($transaction);
        $transaction->delete();
      }

      DB::commit();
      return to_route('transaction.index');
    } catch (\Throwable $th) {
      DB::rollBack();
      report($th);
      return back()->withErrors(['error' => 'Gagal menghapus transaksi.']);
    }
  }
}
