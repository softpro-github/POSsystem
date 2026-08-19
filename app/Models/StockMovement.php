<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'product_id',
        'store_id',
        'type',
        'adjustment_reason_id',
        'quantity',
        'balance_after',
        'reference_type',
        'reference_id',
        'user_id',
        'note',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function adjustmentReason(): BelongsTo
    {
        return $this->belongsTo(AdjustmentReason::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
