<?php

namespace App\Services;

use App\Helpers\FilterParser;
use App\Helpers\QueryFilters;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DebtService
{
    public function __construct(private TransactionService $transactionService) {}

    /**
     * Retrieve a paginated, filtered list of debts for the authenticated user.
     *
     * @return array{ debts: LengthAwarePaginator, filters: array, filterSchema: array }
     */
    public function getFilteredDebts(Request $request): array
    {
        $schema  = FilterParser::getFilterSchema(Debt::class);
        $filters = FilterParser::parseFilters($request->get('filters', []), $schema);

        $query = Debt::query()
            ->with(['payments' => fn($q) => $q->orderBy('payment_date', 'desc')])
            ->withCount('payments')
            ->where('user_id', Auth::id());

        if (!empty($filters)) {
            $query = QueryFilters::apply($query, $filters, $schema);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'LIKE', '%' . $request->get('search') . '%')
                  ->orWhere('contact_name', 'LIKE', '%' . $request->get('search') . '%');
            });
        }

        $debts = $query
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return [
            'debts'        => $debts,
            'filters'      => $filters,
            'filterSchema' => FilterParser::prepareSchemaForFrontend($schema),
        ];
    }

    /**
     * Create a new debt record.
     */
    public function create(array $data): Debt
    {
        return Debt::create([
            'user_id'       => Auth::id(),
            'title'         => $data['title'],
            'type'          => $data['type'],
            'amount'        => $data['amount'],
            'paid_amount'   => 0,
            'contact_name'  => $data['contact_name'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'due_date'      => $data['due_date'] ?? null,
            'notes'         => $data['notes'] ?? null,
            'status'        => 'unpaid',
        ]);
    }

    /**
     * Update an existing debt.
     */
    public function update(Debt $debt, array $data): Debt
    {
        $debt->update([
            'title'         => $data['title']         ?? $debt->title,
            'type'          => $data['type']           ?? $debt->type,
            'amount'        => $data['amount']         ?? $debt->amount,
            'contact_name'  => $data['contact_name']  ?? $debt->contact_name,
            'contact_phone' => $data['contact_phone'] ?? $debt->contact_phone,
            'due_date'      => $data['due_date']       ?? $debt->due_date,
            'notes'         => $data['notes']          ?? $debt->notes,
        ]);

        // Recalculate status after a possible amount change
        $this->recalculateStatus($debt);

        return $debt;
    }

    /**
     * Delete a debt and all its payments.
     * If a payment has a linked transaction, that transaction is rolled back first.
     */
    public function delete(Debt $debt): void
    {
        DB::transaction(function () use ($debt) {
            foreach ($debt->payments as $payment) {
                $this->rollbackPaymentTransaction($payment);
            }
            $debt->delete();
        });
    }

    /**
     * Bulk delete debts.
     */
    public function deleteMany(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $debts = Debt::whereIn('id', $ids)
                ->where('user_id', Auth::id())
                ->with('payments')
                ->get();

            foreach ($debts as $debt) {
                foreach ($debt->payments as $payment) {
                    $this->rollbackPaymentTransaction($payment);
                }
                $debt->delete();
            }
        });
    }

    /**
     * Record a payment against a debt.
     *
     * If `account_id` is provided, an expense transaction is automatically
     * created and linked to the DebtPayment.
     */
    public function createPayment(Debt $debt, array $data): DebtPayment
    {
        return DB::transaction(function () use ($debt, $data) {
            $transactionId = null;

            // Optionally create a linked expense transaction
            if (!empty($data['account_id'])) {
                $transaction = $this->transactionService->create([
                    'account_id'       => $data['account_id'],
                    'type'             => 'expense',
                    'amount'           => $data['amount'],
                    'description'      => 'Debt payment: ' . $debt->title,
                    'transaction_date' => $data['payment_date'],
                    'category_id'      => null,
                ]);
                $transactionId = $transaction->id;
            }

            $payment = DebtPayment::create([
                'user_id'        => Auth::id(),
                'debt_id'        => $debt->id,
                'transaction_id' => $transactionId,
                'amount'         => $data['amount'],
                'payment_date'   => $data['payment_date'],
                'notes'          => $data['notes'] ?? null,
            ]);

            // Update the debt's paid_amount and status
            $debt->increment('paid_amount', $data['amount']);
            $debt->refresh();
            $this->recalculateStatus($debt);

            return $payment;
        });
    }

    /**
     * Delete a payment record and rollback any linked transaction.
     */
    public function deletePayment(DebtPayment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $debt   = $payment->debt;
            $amount = $payment->amount;

            $this->rollbackPaymentTransaction($payment);

            // Decrement paid_amount
            $debt->decrement('paid_amount', min($amount, $debt->paid_amount));
            $debt->refresh();
            $this->recalculateStatus($debt);
        });
    }

    /**
     * Recalculate and persist the debt status based on paid_amount vs amount.
     */
    protected function recalculateStatus(Debt $debt): void
    {
        if ($debt->paid_amount <= 0) {
            $status = 'unpaid';
        } elseif ($debt->paid_amount >= $debt->amount) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        $debt->update(['status' => $status]);
    }

    /**
     * If the payment has a linked transaction, roll it back via TransactionService.
     */
    protected function rollbackPaymentTransaction(DebtPayment $payment): void
    {
        if ($payment->transaction_id) {
            $transaction = Transaction::find($payment->transaction_id);
            if ($transaction) {
                $this->transactionService->delete($transaction);
            }
        }
        $payment->delete();
    }
}
