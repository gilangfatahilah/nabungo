<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Budget extends Model
{
  protected $fillable = [
    'user_id',
    'category_id',
    'month',
    'amount',
  ];

  public static function filterableFields(): array
  {
    return [
      [
        'key' => 'category_id',
        'label' => 'Category',
        'type' => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => Category::where('type', 'expense')->get()->map(function ($category) {
          return ['label' => $category->name, 'value' => $category->id];
        })->toArray(),
      ],
      [
        'key' => 'month',
        'label' => 'Month',
        'type' => 'date',
        'operators' => ['=', '!=', '>', '<', 'between', 'not between'],
      ],
      [
        'key' => 'amount',
        'label' => 'Amount',
        'type' => 'number',
        'operators' => ['=', '!=', '<', '>', '<=', '>='],
      ],
    ];
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function category()
  {
    return $this->belongsTo(Category::class);
  }

  /**
   * Calculate usage percentage for a budget.
   * Call this method explicitly instead of using accessor to avoid N+1 queries.
   *
   * @param float|null $totalExpense Pre-calculated expense total
   * @return int
   */
  public function calculateUsage(?float $totalExpense = null): int
  {
    if ($totalExpense === null) {
      $totalExpense = $this->calculateTotalExpense();
    }

    if ($this->amount == 0) {
      return $totalExpense > 0 ? 100 : 0;
    }

    return (int) round(($totalExpense / $this->amount) * 100);
  }

  /**
   * Calculate total expense for this budget period and category.
   * Call this method explicitly instead of using accessor to avoid N+1 queries.
   *
   * @return float
   */
  public function calculateTotalExpense(): float
  {
    $startOfMonth = Carbon::parse($this->month)->startOfMonth();
    $endOfMonth = Carbon::parse($this->month)->endOfMonth();

    return (float) Transaction::where('type', 'expense')
      ->where('category_id', $this->category_id)
      ->where('user_id', $this->user_id)
      ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
      ->sum('amount');
  }

  /**
   * Scope to eager load budget expenses using subquery.
   * Use this when loading multiple budgets to avoid N+1 queries.
   * Cross-database compatible (PostgreSQL, MySQL, SQLite).
   *
   * @param \Illuminate\Database\Eloquent\Builder $query
   * @return \Illuminate\Database\Eloquent\Builder
   */
  public function scopeWithExpenseData($query)
  {
    return $query->addSelect([
      'total_expense' => Transaction::selectRaw('COALESCE(SUM(amount), 0)')
        ->whereColumn('category_id', 'budgets.category_id')
        ->whereColumn('user_id', 'budgets.user_id')
        ->where('type', 'expense')
        ->whereRaw('EXTRACT(YEAR FROM transaction_date) = EXTRACT(YEAR FROM budgets.month)')
        ->whereRaw('EXTRACT(MONTH FROM transaction_date) = EXTRACT(MONTH FROM budgets.month)')
    ]);
  }
}
