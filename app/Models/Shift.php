<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = [
        'store_id',
        'terminal_id',
        'user_id',
        'opening_float',
        'status',
        'opened_at',
        'closed_at',
        'closing_count',
        'expected_cash',
        'variance',
        'cash_mismatch_reason_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opening_float' => 'decimal:2',
            'closing_count' => 'decimal:2',
            'expected_cash' => 'decimal:2',
            'variance' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function terminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class);
    }

    public function cashMismatchReason(): BelongsTo
    {
        return $this->belongsTo(CashMismatchReason::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function supplierPayments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    public function cashSalesTotal(): float
    {
        return (float) $this->sales()
            ->where('status', 'completed')
            ->join('payments', 'payments.sale_id', '=', 'sales.id')
            ->where('payments.method', 'cash')
            ->sum('payments.amount');
    }

    public function cashRefundsTotal(): float
    {
        return (float) \App\Models\SaleReturn::whereIn('sale_id', $this->sales()->pluck('id'))->sum('total_refunded');
    }

    public function cashSupplierPaymentsTotal(): float
    {
        return (float) $this->supplierPayments()->where('method', 'cash')->sum('amount');
    }

    public function computedExpectedCash(): float
    {
        return round(
            (float) $this->opening_float
            + $this->cashSalesTotal()
            - $this->cashRefundsTotal()
            - $this->cashSupplierPaymentsTotal(),
            2
        );
    }

    /** X-Report (open shift) / Z-Report (closed shift) sales summary block. */
    public function salesSummary(): array
    {
        $completedSales = $this->sales()->where('status', 'completed');
        $saleIds = (clone $completedSales)->pluck('id');
        $refunds = \App\Models\SaleReturn::whereIn('sale_id', $saleIds);

        return [
            'count' => (clone $completedSales)->count(),
            'total' => (float) (clone $completedSales)->sum('total_amount'),
            'tax' => (float) (clone $completedSales)->sum('tax_amount'),
            'discounts' => (float) (clone $completedSales)->sum('discount_amount'),
            'refunds_count' => (clone $refunds)->count(),
            'refunds_total' => (float) (clone $refunds)->sum('total_refunded'),
        ];
    }

    /** Payments received during this shift, grouped by method. */
    public function paymentsByMethod(): array
    {
        $totals = \App\Models\Payment::whereIn('sale_id', $this->sales()->where('status', 'completed')->pluck('id'))
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->pluck('total', 'method');

        return collect(['cash', 'card', 'transfer'])
            ->mapWithKeys(fn ($method) => [$method => (float) ($totals[$method] ?? 0)])
            ->all();
    }
}
