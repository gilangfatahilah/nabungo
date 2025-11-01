<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountHistory extends Model
{
  protected $fillable = [
    'user_id',
    'account_id',
    'transaction_id',
    'type',
    'amount',
    'balance_before',
    'balance_after',
    'notes',
  ];

  public static function filterableFields(): array
  {
    return [
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
        'key' => 'type',
        'label' => 'Type',
        'type' => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => [
          ['label' => 'In', 'value' => 'in'],
          ['label' => 'Out', 'value' => 'out'],
        ],
      ],
      [
        'key' => 'amount',
        'label' => 'Amount',
        'type' => 'number',
        'operators' => ['=', '!=', '<', '>', '<=', '>='],
      ],
      [
        'key' => 'notes',
        'label' => 'Notes',
        'type' => 'string',
        'operators' => ['=', '!=', 'like', 'not like'],
      ],
    ];
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function account()
  {
    return $this->belongsTo(Account::class);
  }

  public function transaction()
  {
    return $this->belongsTo(Transaction::class);
  }
}
