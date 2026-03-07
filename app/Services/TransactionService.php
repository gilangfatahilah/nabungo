<?php

namespace App\Services;

use App\Helpers\FilterParser;
use App\Helpers\QueryFilters;
use App\Models\Account;
use App\Models\AccountHistory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionService
{
  /**
   * Retrieve a paginated, filtered list of transactions for the authenticated user.
   *
   * @return array{ transactions: LengthAwarePaginator, filters: array, filterSchema: array }
   */
  public function getFilteredTransactions(Request $request): array
  {
    $schema  = FilterParser::getFilterSchema(Transaction::class);
    $filters = FilterParser::parseFilters($request->get('filters', []), $schema);

    $query = Transaction::query()
      ->with([
        'account:id,name',
        'accountTarget:id,name',
        'category:id,name',
      ])
      ->where('user_id', Auth::id());

    if (!empty($filters)) {
      $query = QueryFilters::apply($query, $filters, $schema);
    }

    $transactions = $query
      ->orderBy('transaction_date', 'desc')
      ->paginate($request->get('per_page', 10))
      ->withQueryString();

    return [
      'transactions' => $transactions,
      'filters'      => $filters,
      'filterSchema' => FilterParser::prepareSchemaForFrontend($schema),
    ];
  }

  /**
   * Create a history record for the transaction.
   *
   * @param int $accountId
   * @param int $transactionId
   * @param string $type
   * @param float $amount
   * @param float $before
   * @param float $after
   * @param string $note
   */
  protected function createHistory(
    int $accountId,
    int $transactionId,
    string $type,
    float $amount,
    float $before,
    float $after,
    string $note
  ): void {
    AccountHistory::create([
      'user_id'         => Auth::id(),
      'account_id'      => $accountId,
      'transaction_id'  => $transactionId,
      'type'            => $type,
      'amount'          => $amount,
      'balance_before'  => $before,
      'balance_after'   => $after,
      'notes'            => $note,
    ]);
  }


  /**
   * Check and update goal.
   * @param \App\Models\Account $account
   * @param float $amount
   * @param string $direction
   * @return void
   */
  protected function updateGoalSavedAmount(Account $account, float $amount, string $direction): void
  {
    if ($account->type !== 'goal') {
      return;
    }

    $goal = $account->goal;

    if (!$goal) {
      return;
    }

    if ($direction === 'in') {
      $goal->increment('saved_amount', $amount);
    } elseif ($direction === 'out') {
      $goal->decrement('saved_amount', $amount);
    }
  }


  /**
   * Update Balance Helper for each transactions.
   * @param array $data
   * @return void
   */
  public function applyBalance(Transaction $transaction): void
  {
    $account = Account::find($transaction->account_id);

    if ($transaction->type === 'income') {
      $before = $account->balance;
      $account->increment('balance', $transaction->amount);
      $this->createHistory(
        $account->id,
        $transaction->id,
        'in',
        $transaction->amount,
        $before,
        $account->balance,
        $transaction->description ?? 'Income Transaction'
      );

      // Update Goal Balance
      $this->updateGoalSavedAmount($account, $transaction->amount, 'in');
    } elseif ($transaction->type === 'expense') {
      $before = $account->balance;
      $account->decrement('balance', $transaction->amount);
      $this->createHistory(
        $account->id,
        $transaction->id,
        'out',
        $transaction->amount,
        $before,
        $account->balance,
        $transaction->description ?? 'Expense Transaction'
      );

      // Update Goal Balance
      $this->updateGoalSavedAmount($account, $transaction->amount, 'out');
    } elseif ($transaction->type === 'transfer') {
      $accountTarget = Account::findOrFail($transaction->account_target_id);

      $beforeSource = $account->balance;
      $account->decrement('balance', $transaction->amount);
      $this->createHistory(
        $account->id,
        $transaction->id,
        'out',
        $transaction->amount,
        $beforeSource,
        $account->balance,
        'Transfer to account ' . $accountTarget->name
      );

      // Update Goal Balance
      $this->updateGoalSavedAmount($account, $transaction->amount, 'out');

      $beforeTarget = $accountTarget->balance;
      $accountTarget->increment('balance', $transaction->amount);
      $this->createHistory(
        $accountTarget->id,
        $transaction->id,
        'in',
        $transaction->amount,
        $beforeTarget,
        $accountTarget->balance,
        'Transfer from account ' . $account->name
      );

      // Update Goal Balance
      $this->updateGoalSavedAmount($accountTarget, $transaction->amount, 'in');
    }
  }

  /**
   *
   * @param \App\Models\Transaction $transaction
   * @return void
   */
  public function rollbackBalance(Transaction $transaction): void
  {
    $account = Account::find($transaction->account_id);

    if ($transaction->type === 'income') {
      $account->decrement('balance', $transaction->amount);
    } elseif ($transaction->type === 'expense') {
      $account->increment('balance', $transaction->amount);
    } elseif ($transaction->type === 'transfer') {
      $accountTarget = Account::find($transaction->account_target_id);

      $account->increment('balance', $transaction->amount);
      $accountTarget->decrement('balance', $transaction->amount);
    }
  }

  protected function validateTransaction(array $data)
  {
    $account = Account::findOrFail($data['account_id']);

    if (in_array($data['type'], ['expense', 'transfer']) && $account->balance < $data['amount']) {
      throw new \Exception("Saldo akun tidak mencukupi untuk transaksi.");
    }

    if ($data['type'] === 'transfer' && $data['account_id'] === $data['account_target_id']) {
      throw new \Exception("Akun sumber dan tujuan tidak boleh sama.");
    }
  }

  public function deleteMany(array $ids): void
  {
    DB::transaction(function () use ($ids) {
      $transactions = Transaction::whereIn('id', $ids)->get();

      foreach ($transactions as $transaction) {
        $this->rollbackBalance($transaction);

        $transaction->delete();
      }
    });
  }

  public function create(array $data): Transaction
  {
    return DB::transaction(function () use ($data) {
      $this->validateTransaction($data);

      $transaction = Transaction::create([
        'category_id'      => $data['category_id'] ?? null,
        'account_id'       => $data['account_id'],
        'account_target_id' => $data['account_target_id'] ?? null,
        'type'             => $data['type'],
        'amount'           => $data['amount'],
        'description'      => $data['description'] ?? null,
        'transaction_date' => $data['transaction_date'],
        'user_id'          => Auth::id(),
      ]);
      $this->applyBalance($transaction);

      return $transaction;
    });
  }

  public function update(Transaction $transaction, array $data): Transaction
  {
    return DB::transaction(function () use ($transaction, $data) {
      $this->rollbackBalance($transaction);

      $this->validateTransaction($data);

      $transaction->update($data);

      $this->applyBalance($transaction);

      return $transaction;
    });
  }

  public function delete(Transaction $transaction): void
  {
    DB::transaction(function () use ($transaction) {
      $this->rollbackBalance($transaction);
      $transaction->delete();
    });
  }
}
