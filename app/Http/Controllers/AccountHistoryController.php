<?php

namespace App\Http\Controllers;

use App\Helpers\FilterParser;
use App\Helpers\QueryFilters;
use App\Models\AccountHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AccountHistoryController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    try {
      $schema = FilterParser::getFilterSchema(AccountHistory::class);

      $filters = FilterParser::parseFilters(
        $request->get('filters', []),
        $schema
      );

      $query = AccountHistory::query()
        ->with(['account:id,name', 'transaction:id,amount,type,description,transaction_date'])
        ->where('user_id', Auth::id());


      if ($request->filled('search')) {
        $query->where('notes', 'LIKE', '%' . $request->get('search') . '%');
      }

      if (!empty($filters)) {
        $query = QueryFilters::apply($query, $filters, $schema);
      }

      $histories = $query
        ->orderBy('created_at', 'desc')
        ->paginate($request->get('per_page', 10))
        ->withQueryString();

      $filterSchema = FilterParser::prepareSchemaForFrontend($schema);

      return inertia('account-history/Index', [
        'histories' => $histories,
        'filters' => $filters,
        'filterSchema' => $filterSchema,
        'query' => $request->query(),
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

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    //
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    //
  }

  /**
   * Display the specified resource.
   */
  public function show(string $id)
  {
    //
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    //
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    //
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    //
  }
}
