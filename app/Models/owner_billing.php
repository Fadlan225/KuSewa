<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class owner_billing extends Model
{
  protected $fillable = [
    'user_id',
    'billing_code',
    'periode',
    'service_fee_per_transaction',
    'total_transactions',
    'total_amount',
    'status',
    'due_date',
    'paid_at',
  ];

  protected $casts = [
    'due_date' => 'date',
    'paid_at' => 'datetime',
    'service_fee_per_transaction' => 'decimal:2',
    'total_amount' => 'decimal:2',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }
}