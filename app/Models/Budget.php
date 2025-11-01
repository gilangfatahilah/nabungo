<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class Budget extends Model
{
  protected $fillable = [
    'user_id',
    'category_id',
    'month',
    'amount',
  ];

  protected $appends = ['usage', 'total_expense'];

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
   * Append budge usage for each category & month.
   * @return Attribute
   */
  protected function usage(): Attribute
  {
    return Attribute::make(
      get: function () {
        $startOfMonth = Carbon::parse($this->month)->startOfMonth();
        $endOfMonth = Carbon::parse($this->month)->endOfMonth();

        $totalExpense = Transaction::where('type', 'expense')
          ->where('category_id', $this->category_id)
          ->where('user_id', $this->user_id)
          ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
          ->sum('amount');

        if ($this->amount == 0) {
          return $totalExpense > 0 ? 100 : 0;
        }

        return (int) round(($totalExpense / $this->amount) * 100);
      }
    );
  }

  protected function totalExpense(): Attribute
  {
    return Attribute::make(
      get: function () {
        $startOfMonth = Carbon::parse($this->month)->startOfMonth();
        $endOfMonth = Carbon::parse($this->month)->endOfMonth();

        $totalExpense = Transaction::where('type', 'expense')
          ->where('category_id', $this->category_id)
          ->where('user_id', $this->user_id)
          ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
          ->sum('amount');

        return $totalExpense;
      }
    );
  }
}
