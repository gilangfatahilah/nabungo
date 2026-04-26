<?php

namespace App\Http\Controllers;

use App\Http\Requests\Debt\StorePaymentRequest;
use App\Models\Debt;
use App\Models\DebtPayment;
use App\Services\DebtService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DebtPaymentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private DebtService $service) {}

    /**
     * Record a new payment for the given debt.
     */
    public function store(StorePaymentRequest $request, Debt $debt)
    {
        $this->authorize('update', $debt);

        try {
            $this->service->createPayment($debt, $request->validated());
            return to_route('debt.index');
        } catch (\Throwable $th) {
            report($th);
            return back()->withErrors(['error' => $th->getMessage() ?: 'Gagal mencatat pembayaran.']);
        }
    }

    /**
     * Delete a payment record (and roll back linked transaction if any).
     */
    public function destroy(DebtPayment $debtPayment)
    {
        $this->authorize('update', $debtPayment->debt);

        try {
            $this->service->deletePayment($debtPayment);
            return to_route('debt.index');
        } catch (\Throwable $th) {
            report($th);
            return back()->withErrors(['error' => 'Gagal menghapus pembayaran.']);
        }
    }
}
