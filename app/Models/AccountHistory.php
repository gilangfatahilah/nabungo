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
    'balance_after',
    'notes',
  ];

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
