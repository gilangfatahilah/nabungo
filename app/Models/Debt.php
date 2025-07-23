<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Debt extends Model
{
  protected $fillable = [
    'user_id',
    'title',
    'type',
    'amount',
    'paid_amount',
    'contact_name',
    'contact_phone',
    'due_date',
    'notes',
    'status',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function payments()
  {
    return $this->hasMany(DebtPayment::class);
  }
}
