<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Http\Requests\Category\StoreRequest;
use App\Http\Requests\Category\UpdateRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CategoryController extends Controller
{
  /**
   * Validate account owner.
   */
  protected function authorizeAccess(Category $category)
  {
    if ($category->user_id !== Auth::id()) {
      abort(403, 'Unauthorized');
    }
  }

  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    $query = Category::query()
      ->where('user_id', Auth::id());

    if ($request->filled('search')) {
      $query->where('name', 'LIKE', '%' . $request->get('search') . '%')->orWhere('type', 'LIKE', '%' . $request->get('search') . '%');
    }

    $categories = $query->orderBy('created_at', 'desc')
      ->paginate($request->get('per_page', 10))
      ->withQueryString();

    return Inertia::render('category/Index', [
      'categories' => $categories,
      'query' => $request->query(),
    ]);
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(StoreRequest $request)
  {
    $validated = $request->validated();
    Category::create([
      'user_id' => Auth::id(),
      'name' => $validated['name'],
      'type' => $validated['type'],
    ]);

    return to_route('category.index');
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(UpdateRequest $request, Category $category)
  {
    $this->authorizeAccess($category);

    $category->update($request->validated());
    return to_route('category.index');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Category $category)
  {
    $this->authorizeAccess($category);

    $category->delete();
    return to_route('category.index');
  }

  public function multipleDestroy(Request $request)
  {
    $request->validate([
      'ids' => 'required|array',
      'ids.*' => 'integer|exists:categories,id',
    ]);

    Category::whereIn('id', $request->input('ids'))->delete();

    return to_route('category.index');
  }

  public function options(Request $request)
  {
    $query = Category::query()->select('id', 'name');

    if ($request->filled('type')) {
      $query->where('type', $request->type);
    }

    $categories = $query->get()->map(function ($category) {
      return [
        'label' => $category->name,
        'value' => $category->id,
      ];
    });

    return response()->json([
      'success' => true,
      'data' => $categories,
    ]);
  }
}
