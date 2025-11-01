<?php

namespace App\Http\Controllers;

use App\Helpers\FilterParser;
use App\Helpers\QueryFilters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
  protected array $filterableFields;

  public function __construct()
  {
    $this->filterableFields = [
      [
        'key' => 'created_at',
        'label' => 'Date',
        'type' => 'date',
        'operators' => ['=', '!=', '>', '<', 'between', 'not between'],
      ],
      [
        'key' => 'description',
        'label' => 'Description',
        'type' => 'string',
        'operators' => ['=', '!=', 'like', 'not like'],
      ],
      [
        'key' => 'subject_type',
        'label' => 'Subject',
        'type' => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => [
          ['label' => 'Account', 'value' => 'App\Models\Account'],
          ['label' => 'Budget', 'value' => 'App\Models\Budget'],
          ['label' => 'Category', 'value' => 'App\Models\Category'],
          ['label' => 'Transaction', 'value' => 'App\Models\Transaction'],
          ['label' => 'Goal', 'value' => 'App\Models\Goal'],
          ['label' => 'Debt', 'value' => 'App\Models\Debt'],
        ],
      ],
      [
        'key' => 'event',
        'label' => 'Event',
        'type' => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => [
          ['label' => 'Created', 'value' => 'created'],
          ['label' => 'Updated', 'value' => 'updated'],
          ['label' => 'Deleted', 'value' => 'deleted'],
        ],
      ],
    ];
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {

    $schema = collect($this->filterableFields)->keyBy('key')->toArray();
    $filters = FilterParser::parseFilters($request->get('filters', []), $schema);

    $query = Activity::query()->with('causer')->where('causer_id', Auth::id());

    // Optional: search in description or subject
    if ($request->filled('search')) {
      $query->where(function ($q) use ($request) {
        $q->where('description', 'like', "%{$request->search}%")
          ->orWhere('event', 'like', "%{$request->search}%");
      });
    }

    if (!empty($filters)) {
      $query = QueryFilters::apply($query, $filters, $schema);
    }

    $logs = $query->orderBy('created_at', 'desc')
      ->paginate($request->get('per_page', 10))
      ->withQueryString();

    $filterSchema = FilterParser::prepareSchemaForFrontend($schema);

    return Inertia::render('activity-log/Index', [
      'logs' => $logs,
      'filters' => $filters,
      'filterSchema' => $filterSchema,
      'query' => $request->query()
    ]);
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
