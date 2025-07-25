<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Http\Requests\Account\StoreRequest;
use App\Http\Requests\Account\UpdateRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AccountController extends Controller
{
  /**
   * Validate account owner.
   */
  protected function authorizeAccess(Account $account)
  {
    if ($account->user_id !== Auth::id()) {
      abort(403, 'Unauthorized');
    }
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = Account::query()
      ->where('user_id', Auth::id());

    if ($request->filled('search')) {
      $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
    }

    $accounts = $query->orderBy('created_at', 'desc')
      ->paginate($request->get('per_page', 10))
      ->withQueryString();

    return Inertia::render('account/Index', [
      'accounts' => $accounts,
      'query' => $request->query(),
    ]);
  }


  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreRequest $request)
  {
    $validated = $request->validated();
    Account::create([
      'user_id' => Auth::id(),
      'name' => $validated['name'],
      'type' => $validated['type'],
      'balance' => $validated['balance'] ?? 0,
      'notes' => $validated['notes'] ?? null,
    ]);

    return to_route('account.index');
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateRequest $request, Account $account)
  {
    $this->authorizeAccess($account);

    $account->update($request->validated());
    return to_route('account.index');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Account $account)
  {
    $this->authorizeAccess($account);

    $account->delete();

    return to_route('account.index');
  }

  /**
   * Remove the multiple resource from storage.
   */
  public function multipleDestroy(Request $request)
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'integer|exists:accounts,id',
    ]);

    Account::whereIn('id', $request->input('ids'))->delete();
    return to_route('account.index');
  }
}
