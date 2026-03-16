<?php

namespace App\Http\Controllers;

use App\Http\Requests\Goal\StoreRequest;
use App\Http\Requests\Goal\UpdateRequest;
use App\Models\Goal;
use App\Services\GoalService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GoalController extends Controller
{
  use AuthorizesRequests;

  public function __construct(private GoalService $service) {}



  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $data = $this->service->getFilteredGoals($request);

    return Inertia::render('goal/Index', [
      'goals'        => $data['goals'],
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
    $this->authorize('create', Goal::class);

    try {
      $this->service->create($request->validated());
      return to_route('goal.index');
    } catch (\Throwable $th) {
      report($th);
      return back()->withErrors(['error' => 'Gagal membuat goal.']);
    }
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateRequest $request, Goal $goal)
  {
    $this->authorize('update', $goal);

    try {
      $this->service->update($goal, $request->validated());
      return to_route('goal.index');
    } catch (\Throwable $th) {
      report($th);
      return back()->withErrors(['error' => 'Gagal memperbarui goal.']);
    }
  }

  /**
   * Cancel the specified goal (marks as cancelled without deleting).
   */
  public function cancel(Goal $goal)
  {
    $this->authorize('update', $goal);

    try {
      $this->service->cancel($goal);
      return to_route('goal.index');
    } catch (\Throwable $th) {
      report($th);
      return back()->withErrors(['error' => 'Gagal membatalkan goal.']);
    }
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Goal $goal)
  {
    $this->authorize('delete', $goal);

    try {
      $this->service->delete($goal);
      return to_route('goal.index');
    } catch (\Throwable $th) {
      report($th);
      return back()->withErrors(['error' => 'Gagal menghapus goal.']);
    }
  }

  public function multipleDestroy(Request $request)
  {
    $this->authorize('create', Goal::class);

    $ids = $request->input('ids', []);

    if (empty($ids)) {
      return back()->withErrors(['error' => 'Tidak ada goals yang dipilih.']);
    }

    try {
      $this->service->deleteMany($ids);
      return to_route('goal.index');
    } catch (\Throwable $th) {
      report($th);
      return back()->withErrors(['error' => 'Gagal menghapus goals.']);
    }
  }
}
