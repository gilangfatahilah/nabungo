<?php

namespace App\Http\Controllers;

use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
  use AuthorizesRequests;

  public function __construct(private CategoryService $service) {}

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $data = $this->service->getFilteredCategories($request);

    return Inertia::render('category/Index', [
      'categories' => $data['categories'],
      'query'      => $request->query(),
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreRequest $request)
  {
    $this->service->create($request->validated());

    return to_route('category.index');
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateRequest $request, Category $category)
  {
    $this->authorize('update', $category);

    $this->service->update($category, $request->validated());

    return to_route('category.index');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Category $category)
  {
    $this->authorize('delete', $category);

    $this->service->delete($category);

    return to_route('category.index');
  }

  public function multipleDestroy(Request $request)
  {
    $request->validate([
      'ids'   => 'required|array',
      'ids.*' => 'integer|exists:categories,id',
    ]);

    $this->service->deleteMany($request->input('ids'));

    return to_route('category.index');
  }

  public function options(Request $request)
  {
    return response()->json([
      'success' => true,
      'data'    => $this->service->getOptions($request),
    ]);
  }
}
