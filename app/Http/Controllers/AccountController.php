<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\StoreRequest;
use App\Http\Requests\Account\UpdateRequest;
use App\Models\Account;
use App\Services\AccountService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
  use AuthorizesRequests;

  public function __construct(private AccountService $service) {}

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $data = $this->service->getFilteredAccounts($request);

    return Inertia::render('account/Index', [
      'accounts'     => $data['accounts'],
      'filters'      => $data['filters'],
      'filterSchema' => $data['filterSchema'],
      'query'        => $request->query(),
    ]);
  }


  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreRequest $request)
  {
    $this->service->create($request->validated());

    return to_route('account.index');
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateRequest $request, Account $account)
  {
    $this->authorize('update', $account);

    $this->service->update($account, $request->validated());

    return to_route('account.index');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Account $account)
  {
    $this->authorize('delete', $account);

    $this->service->delete($account);

    return to_route('account.index');
  }

  /**
   * Remove multiple resources from storage.
   */
  public function multipleDestroy(Request $request)
  {
    $request->validate([
      'ids'   => 'required|array',
      'ids.*' => 'integer|exists:accounts,id',
    ]);

    $this->service->deleteMany($request->input('ids'));

    return to_route('account.index');
  }

  public function options(Request $request)
  {
    return response()->json([
      'success' => true,
      'data'    => $this->service->getOptions($request),
    ]);
  }

  public function transactionSummary(Account $account, Request $request)
  {
    $this->authorize('view', $account);

    $month   = $request->input('month', now()->month);
    $year    = $request->input('year', now()->year);
    $summary = $this->service->getTransactionSummary($account, $month, $year);

    return response()->json([
      'success' => true,
      'data'    => $summary,
    ]);
  }
}
