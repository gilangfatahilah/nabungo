<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Account extends Model
{
  use LogsActivity;

  protected $fillable = [
    'user_id',
    'name',
    'type',
    'balance',
    'notes',
  ];

  public static function filterableFields(): array
  {
    return [
      [
        'key' => 'name',
        'label' => 'Name',
        'type' => 'string',
        'operators' => ['=', '!=', 'like', 'not like'],
      ],
      [
        'key' => 'type',
        'label' => 'Type',
        'type' => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => [
          ['label' => 'Cash', 'value' => 'cash'],
          ['label' => 'Bank', 'value' => 'bank'],
          ['label' => 'E Wallet', 'value' => 'ewallet'],
          ['label' => 'Asset', 'value' => 'asset'],
          ['label' => 'Liability', 'value' => 'liability'],
        ],
      ],
      [
        'key' => 'balance',
        'label' => 'Amount',
        'type' => 'number',
        'operators' => ['=', '<', '<=', '>', '>=', '<>', '<=>'],
      ],
    ];
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->useLogName('system')
      ->logOnly(['name', 'type', 'balance', 'notes'])
      ->setDescriptionForEvent(fn(string $eventName) => "{$this->name} has been {$eventName}")
      ->logOnlyDirty()
      ->dontSubmitEmptyLogs();
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function transactions()
  {
    return $this->hasMany(Transaction::class);
  }

  public function goal()
  {
    return $this->hasOne(Goal::class, 'account_id');
  }

  public function histories()
  {
    return $this->hasMany(AccountHistory::class);
  }
}
