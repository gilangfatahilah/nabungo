<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
  protected $fillable = [
    'user_id',
    'account_id',
    'title',
    'target_amount',
    'saved_amount',
    'due_date',
    'notes',
    'status',
  ];

  protected $appends = ['progress', 'deadline'];

  public static function filterableFields(): array
  {
    return [
      [
        'key' => 'account_id',
        'label' => 'Account',
        'type' => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => Account::where('type', 'goal')->get()->map(function ($account) {
          return ['label' => $account->name, 'value' => $account->id];
        })->toArray(),
      ],
      [
        'key' => 'title',
        'label' => 'Title',
        'type' => 'string',
        'operators' => ['=', '!=', 'like', 'not like'],
      ],
      [
        'key' => 'target_amount',
        'label' => 'Target Amount',
        'type' => 'number',
        'operators' => ['=', '!=', '<', '>', '<=', '>='],
      ],
      [
        'key' => 'saved_amount',
        'label' => 'Saved Amount',
        'type' => 'number',
        'operators' => ['=', '!=', '<', '>', '<=', '>='],
      ],
      [
        'key' => 'due_date',
        'label' => 'Due Date',
        'type' => 'date',
        'operators' => ['=', '!=', '>', '<', 'between', 'not between'],
      ],
      [
        'key' => 'status',
        'label' => 'Status',
        'type' => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => [
          ['label' => 'Ongoing', 'value' => 'ongoing'],
          ['label' => 'Cancelled', 'value' => 'cancelled'],
          ['label' => 'Achieved', 'value' => 'achieved'],
        ],
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

  public function incomingTransactions()
  {
    return $this->hasMany(Transaction::class, 'account_target_id', 'account_id');
  }

  public function outgoingTransactions()
  {
    return $this->hasMany(Transaction::class, 'account_id', 'account_id');
  }

  protected function progress(): Attribute
  {
    return Attribute::make(
      get: function () {
        return $this->target_amount > 0
          ? (int) round((($this->saved_amount ?? 0) / $this->target_amount) * 100)
          : 0;
      }
    );
  }


  protected function deadline(): Attribute
  {
    return Attribute::make(
      get: function () {
        if (!$this->due_date) {
          return 'No deadline';
        }

        $dueDate = Carbon::parse($this->due_date);
        $today   = Carbon::today();

        $daysRemaining = $today->diffInDays($dueDate, false);

        if ($daysRemaining < 0) {
          return abs($daysRemaining) . ' days overdue';
        }

        return $daysRemaining . ' days';
      }
    );
  }
}
