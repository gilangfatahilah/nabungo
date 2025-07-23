<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtPayment extends Model
{
  protected $fillable = [
    'user_id',
    'debt_id',
    'transaction_id',
    'amount',
    'payment_date',
    'notes',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function debt()
  {
    return $this->belongsTo(Debt::class);
  }

  public function transaction()
  {
    return $this->belongsTo(Transaction::class);
  }
}
