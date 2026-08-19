<div class="grid grid-cols-2 gap-4 p-6">
    <div class="bg-surface-hover rounded-lg p-4">
        <div class="text-xs text-ink-muted">Today's Sales</div>
        <div class="text-xl font-semibold text-ink mt-1"><x-money :amount="$stats['todays_sales_total']" /></div>
    </div>
    <div class="bg-surface-hover rounded-lg p-4">
        <div class="text-xs text-ink-muted">Transactions Today</div>
        <div class="text-xl font-semibold text-ink mt-1">{{ $stats['todays_sales_count'] }}</div>
    </div>
    <div class="bg-surface-hover rounded-lg p-4">
        <div class="text-xs text-ink-muted">Active Products</div>
        <div class="text-xl font-semibold text-ink mt-1">{{ $stats['total_products'] }}</div>
    </div>
    <div class="bg-surface-hover rounded-lg p-4">
        <div class="text-xs text-ink-muted">Low Stock Items</div>
        <div class="text-xl font-semibold {{ $stats['low_stock_count'] > 0 ? 'text-red-400' : 'text-ink' }} mt-1">{{ $stats['low_stock_count'] }}</div>
    </div>
</div>

<div class="px-6 pb-6">
    <p class="text-xs font-semibold text-ink-subtle uppercase tracking-wider mb-2">Recent Sales</p>
    @if ($recentSales->isEmpty())
        <p class="text-sm text-ink-muted">No sales yet today.</p>
    @else
        <div class="space-y-2">
            @foreach ($recentSales as $sale)
                <a href="{{ route('sales.show', $sale) }}" class="flex items-center justify-between text-sm px-3 py-2 rounded-md bg-surface-hover hover:bg-surface">
                    <span class="text-accent-400">{{ $sale->invoice_number }}</span>
                    <span class="text-ink-muted">{{ $sale->customer?->name ?? 'Walk-in' }}</span>
                    <span class="text-ink"><x-money :amount="$sale->total_amount" /></span>
                </a>
            @endforeach
        </div>
    @endif
</div>
