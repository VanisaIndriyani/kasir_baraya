<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $table = 'transactions';

    const UPDATED_AT = null;

    protected $fillable = [
        'invoice',
        'order_type',
        'platform',
        'payment_method',
        'total',
        'paid_amount',
        'change_amount',
        'created_at',
    ];

    protected $casts = [
        'total' => 'integer',
        'paid_amount' => 'integer',
        'change_amount' => 'integer',
        'created_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id', 'id');
    }
}

