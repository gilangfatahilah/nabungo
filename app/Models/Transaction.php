<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
  protected $fillable = [
    'user_id',
    'category_id',
    'account_id',
    'account_target_id',
    'type',
    'amount',
    'description',
    'transaction_date',
    'proof_file',
  ];

  public static function filterableFields(): array
  {
    return [
      [
        'key' => 'description',
        'label' => 'Description',
        'type' => 'string',
        'operators' => ['=', '!=', 'like', 'not like'],
      ],
      [
        'key' => 'type',
        'label' => 'Type',
        'type' => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => [
          ['label' => 'Income', 'value' => 'income'],
          ['label' => 'Expense', 'value' => 'expense'],
          ['label' => 'Transfer', 'value' => 'transfer'],
        ],
      ],
      [
        'key' => 'transaction_date',
        'label' => 'Date',
        'type' => 'date',
        'operators' => ['=', '!=', '>', '<', 'between', 'not between'],
      ],
      [
        'key' => 'amount',
        'label' => 'Amount',
        'type' => 'number',
        'operators' => ['=', '!=', '<', '>', '<=', '>='],
      ],
      [
        'key' => 'category_id',
        'label' => 'Category',
        'type' => 'enum',
        'operators' => ['=', '!=', 'in', 'not in', 'is null', 'is not null'],
        'enumOptions' => Category::all()->map(function ($category) {
          return ['label' => $category->name, 'value' => $category->id];
        })->toArray(),
      ],
      [
        'key' => 'account_id',
        'label' => 'Account',
        'type' => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => Account::all()->map(function ($account) {
          return ['label' => $account->name, 'value' => $account->id];
        })->toArray(),
      ],
      [
        'key' => 'account_target_id',
        'label' => 'Target Account',
        'type' => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => Account::all()->map(function ($account) {
          return ['label' => $account->name, 'value' => $account->id];
        })->toArray(),
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

  public function account()
  {
    return $this->belongsTo(Account::class, 'account_id');
  }

  public function accountTarget()
  {
    return $this->belongsTo(Account::class, 'account_target_id');
  }

  public function histories()
  {
    return $this->hasMany(AccountHistory::class);
  }

  public function debtPayment()
  {
    return $this->hasOne(DebtPayment::class);
  }
}
