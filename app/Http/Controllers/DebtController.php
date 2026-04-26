<?php

namespace App\Http\Controllers;

use App\Http\Requests\Debt\StoreRequest;
use App\Http\Requests\Debt\UpdateRequest;
use App\Models\Debt;
use App\Services\DebtService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DebtController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private DebtService $service) {}

    /**
     * Display a listing of debts.
     */
    public function index(Request $request)
    {
        $data = $this->service->getFilteredDebts($request);

        return Inertia::render('debt/Index', [
            'debts'        => $data['debts'],
            'filters'      => $data['filters'],
            'filterSchema' => $data['filterSchema'],
            'query'        => $request->query(),
        ]);
    }

    /**
     * Store a new debt.
     */
    public function store(StoreRequest $request)
    {
        $this->authorize('create', Debt::class);

        try {
            $this->service->create($request->validated());
            return to_route('debt.index');
        } catch (\Throwable $th) {
            report($th);
            return back()->withErrors(['error' => 'Gagal membuat hutang.']);
        }
    }

    /**
     * Update an existing debt.
     */
    public function update(UpdateRequest $request, Debt $debt)
    {
        $this->authorize('update', $debt);

        try {
            $this->service->update($debt, $request->validated());
            return to_route('debt.index');
        } catch (\Throwable $th) {
            report($th);
            return back()->withErrors(['error' => 'Gagal memperbarui hutang.']);
        }
    }

    /**
     * Delete a debt.
     */
    public function destroy(Debt $debt)
    {
        $this->authorize('delete', $debt);

        try {
            $this->service->delete($debt);
            return to_route('debt.index');
        } catch (\Throwable $th) {
            report($th);
            return back()->withErrors(['error' => 'Gagal menghapus hutang.']);
        }
    }

    /**
     * Bulk delete debts.
     */
    public function multipleDestroy(Request $request)
    {
        $this->authorize('create', Debt::class);

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->withErrors(['error' => 'Tidak ada hutang yang dipilih.']);
        }

        try {
            $this->service->deleteMany($ids);
            return to_route('debt.index');
        } catch (\Throwable $th) {
            report($th);
            return back()->withErrors(['error' => 'Gagal menghapus hutang.']);
        }
    }
}
