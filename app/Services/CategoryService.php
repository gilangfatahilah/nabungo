<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CategoryService
{
    /**
     * Retrieve a paginated list of categories for the authenticated user.
     *
     * @return array{ categories: LengthAwarePaginator }
     */
    public function getFilteredCategories(Request $request): array
    {
        $query = Category::query()
            ->where('user_id', Auth::id());

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%')
                  ->orWhere('type', 'LIKE', '%' . $request->get('search') . '%');
        }

        $categories = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 10))
            ->withQueryString();

        return [
            'categories' => $categories,
        ];
    }

    /**
     * Create a new category for the authenticated user.
     */
    public function create(array $data): Category
    {
        return Category::create([
            'user_id' => Auth::id(),
            'name'    => $data['name'],
            'type'    => $data['type'],
        ]);
    }

    /**
     * Update the given category.
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);
        return $category;
    }

    /**
     * Delete the given category.
     */
    public function delete(Category $category): void
    {
        $category->delete();
    }

    /**
     * Delete multiple categories by IDs.
     */
    public function deleteMany(array $ids): void
    {
        Category::whereIn('id', $ids)->delete();
    }

    /**
     * Get category options for select inputs, optionally filtered by type.
     */
    public function getOptions(Request $request): array
    {
        $query = Category::query()
            ->select('id', 'name')
            ->where('user_id', Auth::id());

        if ($request->filled('type')) {
            $query->where('type', $request->get('type'));
        }

        return $query->get()->map(function ($category) {
            return [
                'label' => $category->name,
                'value' => $category->id,
            ];
        })->toArray();
    }
}
