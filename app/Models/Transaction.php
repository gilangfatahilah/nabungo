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
