<?php

namespace App\Services;

use App\Helpers\FilterParser;
use App\Helpers\QueryFilters;
use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class AccountService
{
    /**
     * Retrieve a paginated, filtered list of accounts for the authenticated user.
     *
     * @return array{ accounts: LengthAwarePaginator, filters: array, filterSchema: array }
     */
    public function getFilteredAccounts(Request $request): array
    {
        $schema  = FilterParser::getFilterSchema(Account::class);
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

        return [
            'accounts'     => $accounts,
            'filters'      => $filters,
            'filterSchema' => FilterParser::prepareSchemaForFrontend($schema),
        ];
    }

    /**
     * Create a new account for the authenticated user.
     */
    public function create(array $data): Account
    {
        return Account::create([
            'user_id' => Auth::id(),
            'name'    => $data['name'],
            'type'    => $data['type'],
            'balance' => $data['balance'] ?? 0,
            'notes'   => $data['notes'] ?? null,
        ]);
    }

    /**
     * Update the given account.
     */
    public function update(Account $account, array $data): Account
    {
        $account->update($data);
        return $account;
    }

    /**
     * Delete the given account.
     */
    public function delete(Account $account): void
    {
        $account->delete();
    }

    /**
     * Delete multiple accounts by IDs.
     */
    public function deleteMany(array $ids): void
    {
        Account::whereIn('id', $ids)->delete();
    }

    /**
     * Get account options for select inputs, optionally filtered by type.
     */
    public function getOptions(Request $request): array
    {
        $query = Account::query()->select('id', 'name', 'balance')
            ->where('user_id', Auth::id());

        if ($request->filled('type')) {
            $query->whereIn('type', $request->input('type'));
        }

        return $query->get()->map(function ($account) {
            return [
                'label' => $account->name . ' - (Rp' . number_format($account->balance, 2) . ')',
                'value' => $account->id,
            ];
        })->toArray();
    }

    /**
     * Get income/expense summary for an account in a given month/year.
     */
    public function getTransactionSummary(Account $account, int $month, int $year): array
    {
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

        return [
            'income'  => $totalIncome + $totalTransferIn,
            'expense' => $totalExpense + $totalTransferOut,
        ];
    }
}
