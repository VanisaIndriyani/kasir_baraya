<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $table = 'transaction_items';

    public $timestamps = false;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name',
        'price',
        'qty',
        'subtotal',
    ];

    protected $casts = [
        'transaction_id' => 'integer',
        'product_id' => 'integer',
        'price' => 'integer',
        'qty' => 'integer',
        'subtotal' => 'integer',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}

