<?php

namespace App\Http\Controllers;

use App\Http\Requests\Transaction\StoreRequest;
use App\Http\Requests\Transaction\UpdateRequest;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class TransactionController extends Controller
{
  use AuthorizesRequests;

  public function __construct(private TransactionService $service) {}

  /**
   * Display a listing of transactions with filters.
   */
  public function index(Request $request)
  {
    try {
      $data = $this->service->getFilteredTransactions($request);

      return Inertia::render('transaction/Index', [
        'transactions' => $data['transactions'],
        'filters'      => $data['filters'],
        'filterSchema' => $data['filterSchema'],
        'query'        => $request->query(),
      ]);
    } catch (ValidationException $e) {
      return back()->withErrors([
        'filters' => 'Invalid filter format: ' . $e->getMessage(),
      ]);
    } catch (\Exception $e) {
      Log::error('Transaction index error: ' . $e->getMessage(), [
        'filters' => $request->get('filters', []),
      ]);

      return back()->withErrors([
        'filters' => 'An error occurred while processing filters.',
      ]);
    }
  }

  public function store(StoreRequest $request)
  {
    try {
      $this->service->create($request->validated());
      return redirect()->back()
        ->with('success', 'Transaksi berhasil ditambahkan');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => $e->getMessage()]);
    }
  }

  public function update(UpdateRequest $request, Transaction $transaction)
  {
    $this->authorize('update', $transaction);

    try {
      $this->service->update($transaction, $request->validated());
      return to_route('transaction.index')
        ->with('success', 'Transaksi berhasil diperbarui');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => $e->getMessage()]);
    }
  }

  public function destroy(Transaction $transaction)
  {
    $this->authorize('delete', $transaction);

    try {
      $this->service->delete($transaction);
      return to_route('transaction.index')
        ->with('success', 'Transaksi berhasil dihapus');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => $e->getMessage()]);
    }
  }

  /**
   * Remove multiple resources from storage.
   */
  public function multipleDestroy(Request $request)
  {
    $ids = $request->input('ids', []);

    if (empty($ids)) {
      return back()->withErrors(['error' => 'Tidak ada transaksi yang dipilih.']);
    }

    try {
      $this->service->deleteMany($ids);
      return to_route('transaction.index')
        ->with('success', 'Transaksi berhasil dihapus.');
    } catch (\Throwable $th) {
      report($th);
      return back()->withErrors(['error' => 'Gagal menghapus transaksi.']);
    }
  }
}
