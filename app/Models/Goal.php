<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
  protected $fillable = [
    'user_id',
    'title',
    'target_amount',
    'saved_amount',
    'due_date',
    'notes',
    'status',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }
}
