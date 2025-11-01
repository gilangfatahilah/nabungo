<?php

namespace App\Http\Controllers;

use App\Helpers\FilterParser;
use App\Http\Requests\Transaction\StoreRequest;
use App\Http\Requests\Transaction\UpdateRequest;
use App\Models\Transaction;
use App\Helpers\QueryFilters;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TransactionController extends Controller
{
  public function __construct(private TransactionService $service) {}

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
        ->where('user_id', Auth::id());

      if (!empty($filters)) {
        $query = QueryFilters::apply($query, $filters, $schema);
      }

      $transactions = $query
        ->orderBy('transaction_date', 'desc')
        ->paginate($request->get('per_page', 10))
        ->withQueryString();

      $filterSchema = FilterParser::prepareSchemaForFrontend($schema);

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
        'user_id' => Auth::id(),
      ]);

      return back()->withErrors([
        'filters' => 'An error occurred while processing filters.'
      ]);
    }
  }

  public function store(StoreRequest $request)
  {
    try {
      $this->service->create($request->validated());
      return to_route('transaction.index')
        ->with('success', 'Transaksi berhasil ditambahkan');
    } catch (\Exception $e) {
      return back()->withErrors(['error' => $e->getMessage()]);
    }
  }

  public function update(UpdateRequest $request, Transaction $transaction)
  {
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
