<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Debt extends Model
{
  use LogsActivity;

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

  protected $appends = ['remaining_amount', 'progress', 'formatted_due_date'];

  protected $casts = [
    'due_date'    => 'date',
    'amount'      => 'decimal:2',
    'paid_amount' => 'decimal:2',
  ];

  public static function filterableFields(): array
  {
    return [
      [
        'key'      => 'title',
        'label'    => 'Title',
        'type'     => 'string',
        'operators' => ['=', '!=', 'like', 'not like'],
      ],
      [
        'key'      => 'type',
        'label'    => 'Type',
        'type'     => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => [
          ['label' => 'Debt (I owe)', 'value' => 'debt'],
          ['label' => 'Receivable (Owed to me)', 'value' => 'receivable'],
        ],
      ],
      [
        'key'      => 'status',
        'label'    => 'Status',
        'type'     => 'enum',
        'operators' => ['=', '!=', 'in', 'not in'],
        'enumOptions' => [
          ['label' => 'Unpaid', 'value' => 'unpaid'],
          ['label' => 'Partial', 'value' => 'partial'],
          ['label' => 'Paid', 'value' => 'paid'],
        ],
      ],
      [
        'key'      => 'amount',
        'label'    => 'Amount',
        'type'     => 'number',
        'operators' => ['=', '!=', '<', '>', '<=', '>='],
      ],
      [
        'key'      => 'due_date',
        'label'    => 'Due Date',
        'type'     => 'date',
        'operators' => ['=', '!=', '>', '<', 'between', 'not between'],
      ],
    ];
  }

  public function getActivitylogOptions(): LogOptions
  {
    return LogOptions::defaults()
      ->useLogName('system')
      ->logOnly(['title', 'type', 'amount', 'status'])
      ->setDescriptionForEvent(fn(string $eventName) => "{$this->title} has been {$eventName}")
      ->logOnlyDirty()
      ->dontSubmitEmptyLogs();
  }

  protected function remainingAmount(): Attribute
  {
    return Attribute::make(
      get: fn() => max(0, $this->amount - $this->paid_amount)
    );
  }

  protected function progress(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->amount > 0
        ? (int) round(($this->paid_amount / $this->amount) * 100)
        : 0
    );
  }

  protected function formattedDueDate(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->due_date ? $this->due_date->format('d M Y') : '-'
    );
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function payments()
  {
    return $this->hasMany(DebtPayment::class);
  }
}
