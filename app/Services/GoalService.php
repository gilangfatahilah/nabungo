<?php

namespace App\Services;

use App\Helpers\FilterParser;
use App\Helpers\QueryFilters;
use App\Models\Account;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GoalService
{
    public function __construct(private TransactionService $transactionService) {}

    /**
     * Retrieve a paginated, filtered list of goals for the authenticated user.
     *
     * @return array{ goals: LengthAwarePaginator, filters: array, filterSchema: array }
     */
    public function getFilteredGoals(Request $request): array
    {
        $schema  = FilterParser::getFilterSchema(Goal::class);
        $filters = FilterParser::parseFilters($request->get('filters', []), $schema);

        $query = Goal::query()
            ->with('account:id,name,type,balance')
            ->where('user_id', Auth::id());

        if (!empty($filters)) {
            $query = QueryFilters::apply($query, $filters, $schema);
        }

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%' . $request->get('search') . '%');
        }

        $goals = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return [
            'goals'        => $goals,
            'filters'      => $filters,
            'filterSchema' => FilterParser::prepareSchemaForFrontend($schema),
        ];
    }

    /**
     * Create a new goal along with its dedicated account.
     */
    public function create(array $data): Goal
    {
        return DB::transaction(function () use ($data) {
            $account = Account::create([
                'user_id' => Auth::id(),
                'name'    => '[Goal] ' . $data['title'],
                'type'    => 'goal',
                'balance' => 0,
            ]);

            return Goal::create([
                'user_id'       => Auth::id(),
                'account_id'    => $account->id,
                'title'         => $data['title'],
                'target_amount' => $data['target_amount'],
                'saved_amount'  => 0,
                'due_date'      => $data['due_date'],
                'notes'         => $data['notes'] ?? null,
                'status'        => 'ongoing',
            ]);
        });
    }

    /**
     * Update the given goal.
     */
    public function update(Goal $goal, array $data): Goal
    {
        return DB::transaction(function () use ($goal, $data) {
            $goal->update([
                'title'         => $data['title']         ?? $goal->title,
                'target_amount' => $data['target_amount'] ?? $goal->target_amount,
                'due_date'      => $data['due_date']      ?? $goal->due_date,
                'notes'         => $data['notes']         ?? $goal->notes,
            ]);

            return $goal;
        });
    }

    /**
     * Cancel the given goal (sets status to cancelled without deleting).
     */
    public function cancel(Goal $goal): Goal
    {
        $goal->update(['status' => 'cancelled']);
        return $goal;
    }

    /**
     * Delete the given goal along with its associated transactions and account.
     */
    public function delete(Goal $goal): void
    {
        DB::transaction(function () use ($goal) {
            foreach ($goal->incomingTransactions()->get() as $transaction) {
                $this->transactionService->rollbackBalance($transaction);
                $transaction->delete();
            }

            foreach ($goal->outgoingTransactions()->get() as $transaction) {
                $this->transactionService->rollbackBalance($transaction);
                $transaction->delete();
            }

            $account = $goal->account;

            $goal->delete();

            if ($account) {
                $account->delete();
            }
        });
    }

    /**
     * Delete multiple goals by IDs, cleaning up related transactions and accounts.
     */
    public function deleteMany(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $goals = Goal::whereIn('id', $ids)
                ->where('user_id', Auth::id())
                ->get();

            foreach ($goals as $goal) {
                foreach ($goal->incomingTransactions as $transaction) {
                    $this->transactionService->rollbackBalance($transaction);
                    $transaction->delete();
                }

                foreach ($goal->outgoingTransactions as $transaction) {
                    $this->transactionService->rollbackBalance($transaction);
                    $transaction->delete();
                }

                $account = $goal->account;

                $goal->delete();

                if ($account) {
                    $account->delete();
                }
            }
        });
    }
}
